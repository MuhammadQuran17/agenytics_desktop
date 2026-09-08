<?php

namespace App\Jobs;

use App\Http\Requests\Api\AiAgent\AiAgentSendMessageRequest;
use App\Models\User;
use App\Services\AiAgent\NeuronAi\NeuronAiAgent;
use App\Services\AiChat\History\AiChatHistory;
use App\Services\Subscription\SubscriptionTrackUsage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessAiChatMessage implements ShouldQueue
{
    use Queueable;

    /**
     * Total attempts before giving up. Gemini outages of a couple of minutes
     * are common enough to plan for, so this is tuned to absorb roughly that
     * long automatically - the user never sees a failure for one of those.
     * Takes priority over the queue worker's own --tries flag, so this
     * applies the same way in dev and in the packaged app.
     */
    public int $tries = 6;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private array $request,
        private string $userId,
        private string $jobId,
    ) {}

    /**
     * Seconds to wait before each retry. The last value repeats for any
     * attempt beyond the array's length. Sums to 150s (2.5 minutes) of
     * deliberate waiting, on top of the attempts themselves.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 20, 30, 40, 50];
    }

    /**
     * Execute the job.
     */
    public function handle(
        NeuronAiAgent $neuronAiAgent,
        AiChatHistory $aiChatHistory,
        SubscriptionTrackUsage $subscriptionTrackUsage,
    ): void {

        $user = User::findOrFail($this->userId);

        $neuronAiAgent->setJobId($this->jobId);

        $response = $neuronAiAgent->sendMessage(
            new AiAgentSendMessageRequest($this->request),
        );

        $aiChatHistory->saveAssistantResponse(
            $response,
            $this->request['sessionId'],
            $this->jobId,
        );

        if (! Arr::get($response, 'error')) {
            $subscriptionTrackUsage->trackUsage($user);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('ProcessAiChatMessage job failed', [
            'job_id' => $this->jobId,
            'session_id' => $this->request['sessionId'] ?? null,
            'user_id' => $this->userId,
            'exception' => $exception?->getMessage(),
            'trace' => $exception?->getTraceAsString(),
        ]);

        // Save failed status to ChatHistory so frontend can detect it
        if (isset($this->request['sessionId'])) {
            $chatHistory = \App\Models\ChatHistory::updateOrCreate(
                ['job_id' => $this->jobId, 'role' => 'assistant'],
                [
                    'user_chat_session_id' => $this->request['sessionId'],
                    'job_status' => 'failed',
                    'error' => $exception === null
                        ? 'An unexpected error occurred while processing your message. Please try again later.'
                        : $exception->getMessage().' Please try again later.',
                ],
            );

            $chatHistory->steps()->where('status', 'in_progress')->update(['status' => 'done']);
        }
    }
}
