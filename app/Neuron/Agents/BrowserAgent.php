<?php

declare(strict_types=1);

namespace App\Neuron\Agents;

use NeuronAI\Agent\Agent;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Laravel\Facades\AIProvider;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\Toolkits\ToolkitInterface;

class BrowserAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return AIProvider::driver('gemini');
    }

    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are a helpful assistant.',
            ],
            steps: [],
            output: [
                'Keep answers concise and to the point.',
            ],
        );
    }

    /**
     * @return ToolInterface[]|ToolkitInterface[]
     */
    protected function tools(): array
    {
        return [];
    }
}
