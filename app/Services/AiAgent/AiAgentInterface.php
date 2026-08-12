<?php

namespace App\Services\AiAgent;

use App\Http\Requests\Api\AiAgent\AiAgentSendMessageRequest;

interface AiAgentInterface
{
    public function sendMessage(AiAgentSendMessageRequest $request): array;
}