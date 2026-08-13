<?php

namespace App\Services\AiAgent\NeuronAi;

use App\Http\Requests\Api\AiAgent\AiAgentSendMessageRequest;
use App\Neuron\Agents\BrowserAgent;
use App\Neuron\Middleware\ToolProgressMiddleware;
use App\Services\AiAgent\AiAgentInterface;
use NeuronAI\Agent\Nodes\ToolNode;
use NeuronAI\Chat\Messages\UserMessage;

/**
 * https://www.neuron-ai.dev/
 */
class NeuronAiAgent implements AiAgentInterface
{
    private ?string $jobId = null;

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

        $answer = $agent
            ->chat(new UserMessage($request->message))
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
