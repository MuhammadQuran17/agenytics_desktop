<?php

use App\Models\ChatHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/helpers.php';

uses(RefreshDatabase::class);

function createTurn(string $sessionId, string $jobId, string $userInput, ?string $agentMessage = null, string $assistantStatus = 'completed', ?array $answer = null): void
{
    ChatHistory::create([
        'user_chat_session_id' => $sessionId,
        'job_id' => $jobId,
        'role' => 'user',
        'user_input' => $userInput,
        'message' => $agentMessage,
        'job_status' => 'processing',
    ]);

    ChatHistory::create([
        'user_chat_session_id' => $sessionId,
        'job_id' => $jobId,
        'role' => 'assistant',
        'job_status' => $assistantStatus,
        'message' => $answer ?? [['ui_type' => 'text', 'data' => ['content' => "Answer for {$userInput}"]]],
    ]);
}

describe('Rating a response', function () {
    it('sets a rating and toggles it off when clicked again', function () {
        $user = makeUserWithPrompts(5);
        $sessionId = \Illuminate\Support\Str::uuid()->toString();
        createUserChat($sessionId, $user->id);

        createTurn($sessionId, 'job-1', 'Question', 'Question');

        $this->actingAs($user)
            ->postJson(route('chat.messages.rate', 'job-1'), ['rating' => 'good'])
            ->assertSuccessful()
            ->assertJson(['rating' => 'good']);

        expect(ChatHistory::where('job_id', 'job-1')->where('role', 'assistant')->value('rating'))->toBe('good');

        $this->actingAs($user)
            ->postJson(route('chat.messages.rate', 'job-1'), ['rating' => 'good'])
            ->assertSuccessful()
            ->assertJson(['rating' => null]);
    });

    it('rejects rating a response belonging to another user', function () {
        $owner = makeUserWithPrompts(5);
        $intruder = makeUserWithPrompts(5);
        $sessionId = \Illuminate\Support\Str::uuid()->toString();
        createUserChat($sessionId, $owner->id);

        createTurn($sessionId, 'job-1', 'Question', 'Question');

        $this->actingAs($intruder)
            ->postJson(route('chat.messages.rate', 'job-1'), ['rating' => 'bad'])
            ->assertForbidden();
    });
});
