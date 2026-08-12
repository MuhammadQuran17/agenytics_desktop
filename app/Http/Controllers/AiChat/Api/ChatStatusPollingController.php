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
            default => $this->processingResponse(),
        };
    }

    private function findChatHistoryByJobId(string $jobId): ?ChatHistory
    {
        // Find the assistant response for this job (completed or failed)
        $assistantResponse = ChatHistory::where('job_id', $jobId)
            ->where('role', 'assistant')
            ->whereIn('job_status', ['completed', 'failed'])
            ->first();

        if ($assistantResponse) {
            return $assistantResponse;
        }

        // If no assistant response yet, check if job exists at all (for "not found" vs "processing")
        return ChatHistory::where('job_id', $jobId)->first();
    }

    private function completedResponse(ChatHistory $chatHistory): JsonResponse
    {
        Log::info("Polling: Completed successfully for job {$chatHistory->job_id}, with ai response chat history id: {$chatHistory->id}");

        return response()->json([
            'status' => 'completed',
            'response' => ChatMessageDTO::fromModel($chatHistory)['content'],
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

    private function processingResponse(): JsonResponse
    {
        return response()->json([
            'status' => 'processing',
        ]);
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
