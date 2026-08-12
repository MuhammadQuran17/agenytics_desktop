<?php

namespace App\Services\AiChat\Contracts;

use App\Http\Requests\Api\AiAgent\AiAgentSendMessageRequest;

abstract class MessageProcessingContract
{
    abstract public function processMessage(AiAgentSendMessageRequest $request): array;
}