<?php

namespace App\Http\Controllers\AiChat\Api;

use App\DTO\ChatMessageDTO;
use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatStatusPollingController extends Controller
{
    /**
     * A job stuck on "processing" for longer than this was never going to
     * finish on its own - the worker that owned it crashed or the app was
     * closed before it could call failed(). This is set comfortably above
     * ProcessAiChatMessage's own worst case (~32.5 minutes of retries and
     * per-attempt timeouts) so legitimately-retrying jobs are never cut off.
     */
    private const STALE_PROCESSING_MINUTES = 40;

    /**
     * Check the status of a chat job.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jobId' => 'required|string',
        ]);

        $jobId = $validated['jobId'];
        $chatHistory = $this->findChatHistoryByJobId($jobId);

        if (! $chatHistory) {
            return $this->jobNotFoundResponse($jobId);
        }

        return match ($chatHistory->job_status) {
            'completed' => $this->completedResponse($chatHistory),
            'failed' => $this->failedResponse($chatHistory),
            default => $this->processingResponse($chatHistory),
        };
    }

    private function findChatHistoryByJobId(string $jobId): ?ChatHistory
    {
        // Prefer the assistant row (holds the progress/final response) over the
        // user row that's created for the same job_id when the message is sent.
        $assistantResponse = ChatHistory::where('job_id', $jobId)
            ->where('role', 'assistant')
            ->first();

        if ($assistantResponse) {
            return $assistantResponse;
        }

        // No assistant row yet (job hasn't started processing tools): fall back
        // to the user row, just to confirm the job exists at all.
        return ChatHistory::where('job_id', $jobId)->first();
    }

    private function completedResponse(ChatHistory $chatHistory): JsonResponse
    {
        Log::info("Polling: Completed successfully for job {$chatHistory->job_id}, with ai response chat history id: {$chatHistory->id}");

        return response()->json([
            'status' => 'completed',
            'response' => ChatMessageDTO::fromModel($chatHistory)['content'],
            'steps' => $chatHistory->steps->map->only(['message', 'status']),
            'jobId' => $chatHistory->job_id,
            'rating' => $chatHistory->rating,
        ]);
    }

    private function failedResponse(ChatHistory $chatHistory): JsonResponse
    {
        Log::error('Polling: Failed for job '.$chatHistory->job_id);

        return response()->json([
            'status' => 'failed',
            'error' => $chatHistory->error ?? 'An error occurred while processing your message.',
        ]);
    }

    private function processingResponse(ChatHistory $chatHistory): JsonResponse
    {
        if ($chatHistory->created_at->lt(now()->subMinutes(self::STALE_PROCESSING_MINUTES))) {
            return $this->markStaleAsFailed($chatHistory);
        }

        return response()->json([
            'status' => 'processing',
            'steps' => $chatHistory->role === 'assistant'
                ? $chatHistory->steps->map->only(['message', 'status'])
                : [],
        ]);
    }

    private function markStaleAsFailed(ChatHistory $chatHistory): JsonResponse
    {
        Log::error("Polling: job {$chatHistory->job_id} stuck in processing beyond ".self::STALE_PROCESSING_MINUTES.' minutes, marking failed');

        $assistantRow = $chatHistory->role === 'assistant'
            ? $chatHistory
            : ChatHistory::firstOrCreate(
                ['job_id' => $chatHistory->job_id, 'role' => 'assistant'],
                ['user_chat_session_id' => $chatHistory->user_chat_session_id]
            );

        $assistantRow->update([
            'job_status' => 'failed',
            'error' => 'Processing was interrupted and never completed. Please try again.',
        ]);

        return $this->failedResponse($assistantRow);
    }

    private function jobNotFoundResponse(string $jobId): JsonResponse
    {
        Log::error('Polling: Job not found for job '.$jobId);

        return response()->json([
            'status' => 'error',
            'message' => 'Job not found',
        ], 404);
    }
}
