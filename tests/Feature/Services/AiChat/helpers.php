<?php

use App\Models\User;
use App\Models\UserChat;
use App\Services\AiChat\Contracts\MessageProcessingContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

function makeUserWithPrompts(int $prompts): User
{
    return User::factory()->create(['free_prompts' => $prompts]);
}

function basePayload(?string $sessionId = null): array
{
    return [
        'message' => 'Test message',
        'sessionId' => $sessionId ?? \Illuminate\Support\Str::uuid()->toString(),
        'aiProvider' => 'openai',
        'autoErrorFixing' => false,
        'model' => 'gpt-4',
        'selectedDatabaseType' => 'postgresql',
    ];
}

// User should be loged in 
function postChatRequest($test, array $overrides = [], bool $useDefaults = true): TestResponse
{
    $payload = $useDefaults ? array_merge(basePayload(), $overrides) : $overrides;

    $user = Auth::user();
    
    // Create a user chat session if one doesn't exist and user is authenticated
    if ($user && ! empty($payload['sessionId'])) {
       createUserChat($payload['sessionId'], $user->id);
    }

    return $test->postJson(route('chat.send'), $payload);
}

function createUserChat(string $sessionId, string $userId): void
{
    UserChat::factory()->create([
        'session_id' => $sessionId,
        'user_id' => $userId,
    ]);
}

function mockMessageProcessor(callable $setup): void
{
    $mock = \Mockery::mock(MessageProcessingContract::class);
    $setup($mock->shouldReceive('processMessage'));
    app()->instance(MessageProcessingContract::class, $mock);
}

function mockMessageProcessorToReturn(array $mockResponse): void
{
    mockMessageProcessor(fn ($process) => $process->andReturn($mockResponse));
}

function mockMessageProcessorToThrow(\Throwable $exception): void
{
    mockMessageProcessor(fn ($process) => $process->andThrow($exception));
}

function insertJobsForUser($user, $count): int|array
{
    $jobIds = [];
    for ($i = 0; $i < $count; $i++) {
        $jobData = [
            'queue' => 'default',
            'payload' => DB::raw("'O:29:\"App\\\\Jobs\\\\ProcessAiChatMessage\":2:{s:38:\"*processorRequest\";a:6:{s:7:\"message\";s:12:\"adwwaadwadwa\";s:9:\"sessionId\";s:36:\"019a96c4-488f-73e8-8539-0189ac8d1fff\";s:15:\"autoErrorFixing\";b:1;s:10:\"aiProvider\";s:6:\"openai\";s:5:\"model\";s:7:\"gpt-4.1\";s:20:\"selectedDatabaseType\";s:10:\"postgresql\";}s:37:\"*userId\";s:36:\"".$user->id."\";}' "),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => \now()->getTimestamp(),
            'created_at' => \now()->getTimestamp(),
        ];

        $jobId = DB::table('jobs')->insertGetId($jobData);
        $jobIds[] = $jobId;
    }

    return $count === 1 ? $jobIds[0] : $jobIds;
}
