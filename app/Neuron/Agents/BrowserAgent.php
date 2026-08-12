<?php

declare(strict_types=1);

namespace App\Neuron\Agents;

use NeuronAI\Agent\Agent;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Laravel\Facades\AIProvider;
use NeuronAI\MCP\McpConnector;
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
                'You are a helpful assistant that can control a real web browser through the provided tools to look things up and answer the user.',
            ],
            steps: [
                'Use the browser tools to navigate to pages and read their content when the user asks about the web.',
            ],
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
        return [
            ...McpConnector::make([
                'url' => config('services.playwright_mcp.url'),
            ])->exclude(['browser_close', 'browser_tabs'])->tools(),
        ];
    }
}
