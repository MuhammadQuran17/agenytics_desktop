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
            'browser_click' => $this->describeClick($inputs),
            'browser_type' => $this->describeType($inputs),
            'browser_find' => 'Searching the page...',
            'browser_snapshot' => 'Reading the page...',
            'browser_wait_for' => 'Waiting...',
            'browser_press_key' => 'Pressing a key...',
            'browser_take_screenshot' => 'Taking a screenshot...',
            'browser_hover' => 'Hovering over '.($inputs['element'] ?? 'the page').'...',
            'browser_select_option' => 'Selecting an option...',
            'browser_drag' => 'Dragging...',
            'browser_fill_form' => 'Filling out the form...',
            'browser_run_code_unsafe' => $this->describeRunCode($inputs),
            'browser_network_requests', 'browser_network_request' => 'Checking network activity...',
            'browser_console_messages' => 'Checking the console...',
            default => 'Using '.$toolName.'...',
        };
    }

    /**
     * @param  array<string, mixed>  $inputs
     */
    private function describeClick(array $inputs): string
    {
        $element = $inputs['element'] ?? null;

        return $element ? 'Opening '.$element.'...' : 'Clicking...';
    }

    /**
     * @param  array<string, mixed>  $inputs
     */
    private function describeType(array $inputs): string
    {
        $text = $inputs['text'] ?? null;

        if ($text === null) {
            return 'Typing...';
        }

        return ($inputs['submit'] ?? false)
            ? "Searching for \"{$text}\"..."
            : "Typing \"{$text}\"...";
    }

    /**
     * The model sometimes reaches for raw Playwright code instead of the
     * dedicated click/type tools, which have no free-text description to
     * show. Try to pull a target name out of common Playwright locator
     * patterns so we're not always stuck with a generic message.
     *
     * @param  array<string, mixed>  $inputs
     */
    private function describeRunCode(array $inputs): string
    {
        $code = $inputs['code'] ?? '';

        if (preg_match('/getBy(?:Text|Role|Label|Title|Placeholder)\([^)]*name:\s*[\'"]([^\'"]+)[\'"]/', $code, $matches)
            || preg_match('/getBy(?:Text|Label|Title|Placeholder)\([\'"]([^\'"]+)[\'"]/', $code, $matches)) {
            return 'Interacting with "'.$matches[1].'"...';
        }

        if (str_contains($code, '.click(')) {
            return 'Clicking...';
        }

        return 'Interacting with the page...';
    }
}
