<?php

declare(strict_types=1);

namespace App\Neuron\Middleware;

use App\Models\ChatHistory;
use NeuronAI\Agent\Events\ToolCallEvent;
use NeuronAI\Workflow\Events\Event;
use NeuronAI\Workflow\Middleware\WorkflowMiddleware;
use NeuronAI\Workflow\NodeInterface;
use NeuronAI\Workflow\WorkflowState;

/**
 * Records a human-readable description of the tool the agent is currently
 * calling (e.g. "Opening youtube.com...") so the chat UI can show live
 * progress while the job is still processing, instead of just a spinner.
 */
class ToolProgressMiddleware implements WorkflowMiddleware
{
    public function __construct(
        private readonly string $jobId,
        private readonly string $sessionId,
    ) {}

    public function before(NodeInterface $node, Event $event, WorkflowState $state): void
    {
        if (! $event instanceof ToolCallEvent) {
            return;
        }

        $tools = $event->toolCallMessage->getTools();

        if ($tools === []) {
            return;
        }

        $tool = $tools[0];

        ChatHistory::updateOrCreate(
            ['job_id' => $this->jobId, 'role' => 'assistant'],
            [
                'user_chat_session_id' => $this->sessionId,
                'job_status' => 'processing',
                'progress_message' => $this->describe($tool->getName(), $tool->getInputs()),
            ],
        );
    }

    public function after(NodeInterface $node, Event $result, WorkflowState $state): void
    {
        //
    }

    /**
     * @param  array<string, mixed>  $inputs
     */
    private function describe(string $toolName, array $inputs): string
    {
        return match ($toolName) {
            'browser_navigate' => 'Opening '.($inputs['url'] ?? 'the page').'...',
            'browser_navigate_back' => 'Going back...',
            'browser_click' => 'Clicking...',
            'browser_type' => 'Typing...',
            'browser_find' => 'Searching the page...',
            'browser_snapshot' => 'Reading the page...',
            'browser_wait_for' => 'Waiting...',
            'browser_press_key' => 'Pressing a key...',
            'browser_take_screenshot' => 'Taking a screenshot...',
            default => 'Using '.$toolName.'...',
        };
    }
}
