<?php

namespace App\Services\AiAgent\PrismPhp;

use App\Http\Requests\Api\AiAgent\AiAgentSendMessageRequest;
use App\Services\AiAgent\AiAgentInterface;

/**
 * https://prismphp.com/
 */
class PrismPhpAgent implements AiAgentInterface
{
    public function sendMessage(AiAgentSendMessageRequest $request): array
    {
        throw new \Exception('Not implemented yet');
    }
}