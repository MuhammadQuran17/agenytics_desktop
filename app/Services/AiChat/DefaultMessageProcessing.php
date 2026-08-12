<?php

namespace App\Services\AiChat;

use App\Exceptions\AiChatJobLimitExceededException;
use App\Http\Requests\Api\AiAgent\AiAgentSendMessageRequest;
use App\Jobs\ProcessAiChatMessage;
use App\Services\AiChat\Contracts\MessageProcessingContract;
use App\Services\AiChat\History\AiChatHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

class DefaultMessageProcessing extends MessageProcessingContract
{
    public function __construct(
        private AiChatHistory $aiChatHistory,
        private UserJobLimiter $userJobLimiter,
    ) {}

    public function processMessage(AiAgentSendMessageRequest $request): array
    {
        $userId = Auth::user()->id;

        if (! $this->userJobLimiter->canDispatchJob($userId)) {
            throw new AiChatJobLimitExceededException;
        }

        $jobId = (string) Str::uuid();

        Bus::dispatch(new ProcessAiChatMessage($request->toArray(), $userId, $jobId));

        $this->aiChatHistory->saveUserInput($request->message, $request->sessionId, $jobId);

        return [
            'jobId' => $jobId,
            'sessionId' => $request->sessionId,
        ];
    }
}
