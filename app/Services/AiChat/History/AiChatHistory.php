<?php

namespace App\Services\AiChat\History;

use App\Models\ChatHistory;
use Illuminate\Support\Arr;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;

class AiChatHistory
{
    /**
     * Prior turns from this chat session, as messages the agent can read for
     * context. Without this, every message starts the agent from a blank
     * slate, so a follow-up like "continue" has nothing to continue.
     *
     * @return Message[]
     */
    public function getConversationHistory(string $sessionId, string $excludeJobId): array
    {
        $turns = ChatHistory::where('user_chat_session_id', $sessionId)
            ->where('job_id', '!=', $excludeJobId)
            ->orderBy('created_at')
            ->get()
            ->groupBy('job_id');

        $messages = [];

        foreach ($turns as $rows) {
            $userRow = $rows->firstWhere('role', 'user');
            $assistantRow = $rows->firstWhere('role', 'assistant');

            // Providers like Gemini require strict user/assistant alternation.
            // A turn that never got a real answer (failed, or still in flight)
            // would leave a user message with no reply to pair it with, so the
            // whole turn is left out rather than only skipping the assistant half.
            if (! $userRow || ! $assistantRow || $assistantRow->job_status !== 'completed') {
                continue;
            }

            $userContent = $userRow->message ?? $userRow->user_input;
            $answerText = $this->extractText($assistantRow->message);

            if (blank($userContent) || $answerText === '') {
                continue;
            }

            $messages[] = new UserMessage($userContent);
            $messages[] = new AssistantMessage($answerText);
        }

        return $messages;
    }

    /**
     * Assistant messages are stored as UI blocks (text, table, chart, ...);
     * only the text blocks are meaningful to feed back to the model.
     */
    private function extractText(mixed $blocks): string
    {
        return collect(Arr::wrap($blocks))
            ->filter(fn ($block) => Arr::get($block, 'ui_type') === 'text')
            ->map(fn ($block) => Arr::get($block, 'data.content', ''))
            ->filter()
            ->implode("\n");
    }

    public function saveUserInput(string $userInput, string $sessionId, string $jobId, ?string $messageForAgent = null): void
    {
        ChatHistory::create([
            'user_chat_session_id' => $sessionId,
            'role' => 'user',
            'user_input' => $userInput,
            // Kept separately from user_input (which the UI displays) so a later
            // turn in this session can recall exactly what the agent was told,
            // PDF contents included, without dumping that into the chat bubble.
            'message' => $messageForAgent,
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
