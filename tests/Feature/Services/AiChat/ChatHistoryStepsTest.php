<?php

use App\Models\ChatHistory;
use App\Models\ChatHistoryStep;
use App\Models\UserChat;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/helpers.php';

uses(RefreshDatabase::class);

it('includes the recorded tool-call steps for assistant messages in the chat page', function () {
    $user = makeUserWithPrompts(5);
    $userChat = UserChat::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $assistantMessage = ChatHistory::create([
        'user_chat_session_id' => $userChat->session_id,
        'job_id' => 'test-job-history',
        'job_status' => 'completed',
        'role' => 'assistant',
        'message' => 'Done!',
    ]);

    ChatHistoryStep::create([
        'chat_history_id' => $assistantMessage->id,
        'message' => 'Opened youtube.com',
        'status' => 'done',
    ]);
    ChatHistoryStep::create([
        'chat_history_id' => $assistantMessage->id,
        'message' => 'Searching for "music"...',
        'status' => 'done',
    ]);

    $response = $this->get(route('chat.index', $userChat->session_id));

    $response->assertInertia(function ($page) {
        $page->has('chatHistory', 1)
            ->where('chatHistory.0.steps', [
                ['message' => 'Opened youtube.com', 'status' => 'done'],
                ['message' => 'Searching for "music"...', 'status' => 'done'],
            ]);
    });
});

it('surfaces a failed turn\'s error so it survives a page reload, not just the live "Retry" banner', function () {
    $user = makeUserWithPrompts(5);
    $userChat = UserChat::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    ChatHistory::create([
        'user_chat_session_id' => $userChat->session_id,
        'job_id' => 'test-job-failed-reload',
        'role' => 'user',
        'user_input' => 'What is the weather?',
    ]);

    ChatHistory::create([
        'user_chat_session_id' => $userChat->session_id,
        'job_id' => 'test-job-failed-reload',
        'job_status' => 'failed',
        'role' => 'assistant',
        'error' => 'Gemini API Error: quota exceeded Please try again later.',
    ]);

    $response = $this->get(route('chat.index', $userChat->session_id));

    $response->assertInertia(function ($page) {
        $page->where('chatHistory.1.jobStatus', 'failed')
            ->where('chatHistory.1.error', 'Gemini API Error: quota exceeded Please try again later.');
    });
});

it('returns an empty steps list for assistant messages with no recorded steps', function () {
    $user = makeUserWithPrompts(5);
    $userChat = UserChat::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    ChatHistory::create([
        'user_chat_session_id' => $userChat->session_id,
        'job_id' => 'test-job-no-steps',
        'job_status' => 'completed',
        'role' => 'assistant',
        'message' => 'Done!',
    ]);

    $response = $this->get(route('chat.index', $userChat->session_id));

    $response->assertInertia(function ($page) {
        $page->where('chatHistory.0.steps', []);
    });
});
