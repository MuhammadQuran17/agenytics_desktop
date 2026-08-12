<?php

namespace App\Services\Subscription\Exceptions;

class NoFreePromptsAndNoSubscriptionException extends \Exception
{
    public function __construct()
    {
        parent::__construct('You have no free prompts left and are not subscribed. Please subscribe to a plan to continue.', 402);
    }
}
