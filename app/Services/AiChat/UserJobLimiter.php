<?php

namespace App\Services\AiChat;

use Illuminate\Support\Facades\DB;

// [TestCoverage] 100%
class UserJobLimiter
{
    public function canDispatchJob(string $userId): bool
    {
        return $this->getPendingJobsCount($userId) < config('ai.max_concurrent_jobs');
    }

    public function getPendingJobsCount(string $userId): int
    {
        return (int) DB::table('jobs')
            ->whereRaw('payload LIKE ?', ["%{$userId}%"])
            ->count();
    }
}
