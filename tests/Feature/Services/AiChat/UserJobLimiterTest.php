<?php

use App\Services\AiChat\UserJobLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/helpers.php';

uses(RefreshDatabase::class);

describe('UserJobLimiter - Job Counting Logic', function () {
    it('returns true when user has no pending jobs (6 => 0)', function () {
        $user = makeUserWithPrompts(5);
        $limiter = app(UserJobLimiter::class);

        $canDispatch = $limiter->canDispatchJob((string) $user->id);

        expect($canDispatch)->toBeTrue();
    });

    it('returns false when user is at max concurrent jobs limit (2 => 2)', function () {
        config(['ai.max_concurrent_jobs' => 2]);

        $user = makeUserWithPrompts(5);
        $limiter = app(UserJobLimiter::class);

        insertJobsForUser($user, 2);

        $canDispatch = $limiter->canDispatchJob((string) $user->id);

        expect($canDispatch)->toBeFalse();
    });

    it('returns true when user is below max concurrent jobs limit (6 => 5)', function () {
        config(['ai.max_concurrent_jobs' => 6]);

        $user = makeUserWithPrompts(5);
        $limiter = app(UserJobLimiter::class);

        // Insert 5 jobs for the user
        insertJobsForUser($user, 5);

        $canDispatch = $limiter->canDispatchJob((string) $user->id);

        expect($canDispatch)->toBeTrue();
    });

    it('allows dispatching exactly up to the configured limit (3 => 2 + 1)', function () {
        config(['ai.max_concurrent_jobs' => 3]);

        $user = makeUserWithPrompts(10);
        $limiter = app(UserJobLimiter::class);

        // User should be able to dispatch up to limit
        insertJobsForUser($user, 2);
        expect($limiter->canDispatchJob((string) $user->id))->toBeTrue();

        insertJobsForUser($user, 1); // Now 3 jobs
        expect($limiter->canDispatchJob((string) $user->id))->toBeFalse();
    });

    it('counts pending jobs for a user accurately', function () {
        $user = makeUserWithPrompts(5);
        $limiter = app(UserJobLimiter::class);

        insertJobsForUser($user, 4);

        $count = $limiter->getPendingJobsCount((string) $user->id);

        expect($count)->toBe(4);
    });
});

describe('UserJobLimiter - Multi-User Isolation', function () {
    it('does not count jobs from other users', function () {
        config(['ai.max_concurrent_jobs' => 2]);

        $user1 = makeUserWithPrompts(5);
        $user2 = makeUserWithPrompts(5);
        $limiter = app(UserJobLimiter::class);

        // User 1 has 2 jobs (at limit)
        insertJobsForUser($user1, 2);

        // User 2 should still be able to dispatch
        expect($limiter->canDispatchJob((string) $user2->id))->toBeTrue();
    });

    it('each user has independent job count (3 => [3, 2])', function () {
        config(['ai.max_concurrent_jobs' => 3]);

        $user1 = makeUserWithPrompts(5);
        $user2 = makeUserWithPrompts(5);
        $limiter = app(UserJobLimiter::class);

        insertJobsForUser($user1, 3);
        insertJobsForUser($user2, 2);

        // User 1 is at limit, User 2 is not
        expect($limiter->canDispatchJob((string) $user1->id))->toBeFalse();
        expect($limiter->canDispatchJob((string) $user2->id))->toBeTrue();
    });

    it('removing one users job does not affect another users count', function () {
        config(['ai.max_concurrent_jobs' => 2]);

        $user1 = makeUserWithPrompts(5);
        $user2 = makeUserWithPrompts(5);
        $limiter = app(UserJobLimiter::class);

        $job1Id = insertJobsForUser($user1, 1);
        insertJobsForUser($user2, 2);

        // Delete user1's job
        DB::table('jobs')->where('id', $job1Id)->delete();

        // User 2 should still be at limit
        expect($limiter->canDispatchJob((string) $user2->id))->toBeFalse();
        // User 1 should now be able to dispatch
        expect($limiter->canDispatchJob((string) $user1->id))->toBeTrue();
    });
});

describe('UserJobLimiter - Configuration', function () {
    it('respects the default max concurrent jobs from config', function () {
        $max = config('ai.max_concurrent_jobs');

        expect($max)->toBeGreaterThan(0);
        expect($max)->toBeInt();
    });

    it('respects config changes during test', function () {
        $user = makeUserWithPrompts(5);
        $limiter = app(UserJobLimiter::class);

        // Start with limit of 2
        config(['ai.max_concurrent_jobs' => 2]);
        insertJobsForUser($user, 2);
        expect($limiter->canDispatchJob((string) $user->id))->toBeFalse();

        // Change limit to 3
        config(['ai.max_concurrent_jobs' => 3]);
        expect($limiter->canDispatchJob((string) $user->id))->toBeTrue();
    });
});

describe('UserJobLimiter - Integration with DefaultMessageProcessing', function () {
    it('throws AiChatJobLimitExceededException when user is at limit', function () {
        config(['ai.max_concurrent_jobs' => 2]);

        $user = makeUserWithPrompts(5);
        $this->actingAs($user);

        insertJobsForUser($user, 2);

        $response = postChatRequest($this);

        $response->assertStatus(429);
    });

    it('returns HTTP 429 Too Many Requests status code', function () {
        config(['ai.max_concurrent_jobs' => 1]);

        $user = makeUserWithPrompts(5);
        $this->actingAs($user);

        insertJobsForUser($user, 1);

        $response = postChatRequest($this);

        expect($response->status())->toBe(429);
    });
});

describe('UserJobLimiter - Real-world Scenarios', function () {
    it('prevents exceeding the configured limit', function () {
        config(['ai.max_concurrent_jobs' => 2]);

        $user = makeUserWithPrompts(10);
        $limiter = app(UserJobLimiter::class);

        // User has 0 jobs
        expect($limiter->canDispatchJob((string) $user->id))->toBeTrue();

        // Add 1 job
        insertJobsForUser($user, 1);
        expect($limiter->canDispatchJob((string) $user->id))->toBeTrue();

        // Add another job (now at limit of 2)
        insertJobsForUser($user, 1);
        expect($limiter->canDispatchJob((string) $user->id))->toBeFalse();
    });

    it('allows new jobs after previous jobs complete', function () {
        config(['ai.max_concurrent_jobs' => 2]);

        $user = makeUserWithPrompts(10);
        $limiter = app(UserJobLimiter::class);

        // Add 2 jobs
        $jobIds = insertJobsForUser($user, 2);

        // Should be at limit
        expect($limiter->canDispatchJob((string) $user->id))->toBeFalse();

        // Remove all jobs (simulating job completion)
        DB::table('jobs')->whereIn('id', $jobIds)->delete();

        // Should now be able to dispatch again
        expect($limiter->canDispatchJob((string) $user->id))->toBeTrue();
    });
});
