<?php

namespace App\Services\AiAgent\NeuronAi;

use App\Http\Requests\Api\AiAgent\AiAgentSendMessageRequest;
use App\Neuron\Agents\BrowserAgent;
use App\Services\AiAgent\AiAgentInterface;
use NeuronAI\Chat\Messages\UserMessage;

/**
 * https://www.neuron-ai.dev/
 */
class NeuronAiAgent implements AiAgentInterface
{
    public function sendMessage(AiAgentSendMessageRequest $request): array
    {
        $answer = BrowserAgent::make()
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
