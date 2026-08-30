<?php

namespace App\Services\AiAgent\NeuronAi;

use App\Http\Requests\Api\AiAgent\AiAgentSendMessageRequest;
use App\Neuron\Agents\BrowserAgent;
use App\Neuron\Middleware\ToolProgressMiddleware;
use App\Services\AiAgent\AiAgentInterface;
use App\Services\AiChat\History\AiChatHistory;
use NeuronAI\Agent\Nodes\ToolNode;
use NeuronAI\Chat\Messages\UserMessage;

/**
 * https://www.neuron-ai.dev/
 */
class NeuronAiAgent implements AiAgentInterface
{
    private ?string $jobId = null;

    public function __construct(private AiChatHistory $aiChatHistory) {}

    public function setJobId(string $jobId): void
    {
        $this->jobId = $jobId;
    }

    public function sendMessage(AiAgentSendMessageRequest $request): array
    {
        $agent = BrowserAgent::make();

        if ($this->jobId !== null) {
            $agent->addMiddleware(
                ToolNode::class,
                new ToolProgressMiddleware($this->jobId, $request->sessionId),
            );
        }

        // Without the earlier turns, every message starts the agent from a
        // blank slate, so a follow-up like "continue" or "retry" has nothing
        // to go on beyond the words in that one message.
        $history = $this->jobId !== null
            ? $this->aiChatHistory->getConversationHistory($request->sessionId, $this->jobId)
            : [];

        $answer = $agent
            ->chat([...$history, new UserMessage($request->message)])
            ->getMessage()
            ->getContent();

        return [
            'output' => [
                [
                    'ui_type' => 'text',
                    'data' => [
                        'content' => $answer,
                    ],
                ],
            ],
        ];
    }
}
