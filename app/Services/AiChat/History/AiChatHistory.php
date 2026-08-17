<?php

namespace App\Services\AiChat\History;

use App\Models\ChatHistory;
use Illuminate\Support\Arr;

class AiChatHistory
{
    public function saveUserInput(string $userInput, string $sessionId, string $jobId): void
    {
        ChatHistory::create([
            'user_chat_session_id' => $sessionId,
            'role' => 'user',
            'user_input' => $userInput,
            'job_id' => $jobId,
            'job_status' => 'processing',
        ]);
    }

    public function saveAssistantResponse(array $response, string $sessionId, string $jobId): void
    {
        $chatHistory = ChatHistory::updateOrCreate(
            ['job_id' => $jobId, 'role' => 'assistant'],
            [
                'user_chat_session_id' => $sessionId,
                'job_status' => 'completed',
                'message' => Arr::get($response, 'output'),
                'error' => Arr::get($response, 'error'),
            ],
        );

        $chatHistory->steps()->where('status', 'in_progress')->update(['status' => 'done']);
    }
}
