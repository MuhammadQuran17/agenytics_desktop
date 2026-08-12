<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Maximum Concurrent AI Chat Jobs Per User
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum number of concurrent AI chat message
    | processing jobs a single user can have in the queue. This prevents the
    | queue from being overwhelmed by a single user's requests.
    |
    */
    'max_concurrent_jobs' => (int) env('AI_MAX_CONCURRENT_JOBS', 5),
];

