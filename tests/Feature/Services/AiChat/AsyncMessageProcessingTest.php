<?php

use App\Models\ChatHistory;
use App\Models\ChatHistoryStep;
use App\Models\UserChat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

require_once __DIR__.'/helpers.php';

uses(RefreshDatabase::class);

describe('Async Message Processing - Job Dispatch', function () {
    it('does not dispatch job when user has no free prompts', function () {
        Queue::fake();

        $user = makeUserWithPrompts(0);

        $this->actingAs($user);

        postChatRequest($this)->assertStatus(402);

        Queue::assertNothingPushed();
    });
});

describe('Chat Status Polling', function () {
    it('returns failed status with error message when job has failed', function () {
        $user = makeUserWithPrompts(5);
        $userChat = UserChat::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $jobId = 'test-job-failed';

        // Create a failed chat history
        ChatHistory::create([
            'user_chat_session_id' => $userChat->session_id,
            'job_id' => $jobId,
            'job_status' => 'failed',
            'role' => 'assistant',
        ]);

        $response = $this->postJson(route('chat.status'), ['jobId' => $jobId]);

        $response->assertSuccessful();
        $response->assertJson([
            'status' => 'failed',
            'error' => 'An error occurred while processing your message.',
        ]);
    });

    it('returns processing status when job is still running', function () {
        $user = makeUserWithPrompts(5);
        $userChat = UserChat::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $jobId = 'test-job-processing';

        // Create a processing chat history
        ChatHistory::create([
            'user_chat_session_id' => $userChat->session_id,
            'job_id' => $jobId,
            'job_status' => 'processing',
            'role' => 'user',
            'user_input' => 'Test message',
        ]);

        $response = $this->postJson(route('chat.status'), ['jobId' => $jobId]);

        $response->assertSuccessful();
        $response->assertJson([
            'status' => 'processing',
        ]);
    });

    it('returns the ordered tool-call log for a processing assistant job', function () {
        $user = makeUserWithPrompts(5);
        $userChat = UserChat::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $jobId = 'test-job-steps';

        $chatHistory = ChatHistory::create([
            'user_chat_session_id' => $userChat->session_id,
            'job_id' => $jobId,
            'job_status' => 'processing',
            'role' => 'assistant',
        ]);

        ChatHistoryStep::create([
            'chat_history_id' => $chatHistory->id,
            'message' => 'Opened youtube.com',
            'status' => 'done',
        ]);
        ChatHistoryStep::create([
            'chat_history_id' => $chatHistory->id,
            'message' => 'Searching for "music"...',
            'status' => 'in_progress',
        ]);

        $response = $this->postJson(route('chat.status'), ['jobId' => $jobId]);

        $response->assertSuccessful();
        $response->assertJson([
            'status' => 'processing',
            'steps' => [
                ['message' => 'Opened youtube.com', 'status' => 'done'],
                ['message' => 'Searching for "music"...', 'status' => 'in_progress'],
            ],
        ]);
    });

    it('returns completed status with response when job is done', function () {
        $user = makeUserWithPrompts(5);
        $userChat = UserChat::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $jobId = 'test-job-completed';

        // Create a completed chat history
        ChatHistory::create([
            'user_chat_session_id' => $userChat->session_id,
            'job_id' => $jobId,
            'job_status' => 'completed',
            'role' => 'assistant',
            'message' => 'Test response message',
            'response_type' => 'message',
        ]);

        $response = $this->postJson(route('chat.status'), ['jobId' => $jobId]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'status',
            'response',
        ]);
        expect($response->json('status'))->toBe('completed');
    });
});
