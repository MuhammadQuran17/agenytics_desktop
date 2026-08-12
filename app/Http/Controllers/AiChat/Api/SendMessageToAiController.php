<?php

namespace App\Http\Controllers\AiChat\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AiAgent\AiAgentSendMessageRequest;
use App\Services\AiChat\Contracts\MessageProcessingContract;
use App\Services\Subscription\Exceptions\NoFreePromptsAndNoSubscriptionException;
use Illuminate\Http\JsonResponse;

/**
 * It sends a message to the AI API and saves the response to the database.
 */
class SendMessageToAiController extends Controller
{
    public function __invoke(
        AiAgentSendMessageRequest $request,
        MessageProcessingContract $messageProcessing,
    ): JsonResponse {

        try {
            return response()->json($messageProcessing->processMessage($request));
        } catch (NoFreePromptsAndNoSubscriptionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

    }
}
