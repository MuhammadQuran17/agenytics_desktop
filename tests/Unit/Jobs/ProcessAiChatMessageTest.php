<?php

use App\Jobs\ProcessAiChatMessage;
use App\Models\ChatHistory;
use App\Models\User;
use App\Models\UserChat;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('retries for about 2.5 minutes with backoff before giving up', function () {
    $job = new ProcessAiChatMessage(['message' => 'hi', 'sessionId' => 'session-1'], 'user-1', 'job-1');

    expect($job->tries)->toBe(6)
        ->and($job->backoff())->toBe([10, 20, 30, 40, 50])
        ->and(array_sum($job->backoff()))->toBe(150);
});

it('records a readable error and marks the job failed once retries are exhausted', function () {
    $user = User::factory()->create();
    $userChat = UserChat::factory()->create(['user_id' => $user->id]);

    $job = new ProcessAiChatMessage(['message' => 'hi', 'sessionId' => $userChat->session_id], $user->id, 'job-1');

    $job->failed(new Exception('Gemini API Error: quota exceeded'));

    $chatHistory = ChatHistory::where('job_id', 'job-1')->where('role', 'assistant')->first();

    expect($chatHistory)->not->toBeNull()
        ->and($chatHistory->job_status)->toBe('failed')
        ->and($chatHistory->error)->toBe('Gemini API Error: quota exceeded Please try again later.');
});

it('falls back to a generic message when failed() receives no exception', function () {
    $user = User::factory()->create();
    $userChat = UserChat::factory()->create(['user_id' => $user->id]);

    $job = new ProcessAiChatMessage(['message' => 'hi', 'sessionId' => $userChat->session_id], $user->id, 'job-2');

    $job->failed(null);

    $chatHistory = ChatHistory::where('job_id', 'job-2')->where('role', 'assistant')->first();

    expect($chatHistory->error)->toBe('An unexpected error occurred while processing your message. Please try again later.');
});
