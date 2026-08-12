<?php

namespace App\Exceptions;

use Exception;

class AiChatJobLimitExceededException extends Exception
{
    protected $message = 'You have reached the maximum number of concurrent prompts. Please wait until your previous prompts are done.';

    public function __construct()
    {
        parent::__construct($this->message, 429);
    }
}
