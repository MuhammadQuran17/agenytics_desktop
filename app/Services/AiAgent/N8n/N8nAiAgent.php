<?php

namespace App\Services\AiAgent\N8n;

use App\Http\Requests\Api\AiAgent\AiAgentSendMessageRequest;
use App\Services\AiAgent\AiAgentInterface;
use Illuminate\Support\Facades\Http;

class N8nAiAgent implements AiAgentInterface
{
    public function sendMessage(AiAgentSendMessageRequest $request): array
    {
        if (config('ai_responses.is_fake_response_enabled')) {
            $this->fakeResponse('success');

            return Http::post(config('services.n8n.url'), [
                'sessionId' => $request->sessionId,
                'chatInput' => $request->message,
            ])->json();
        }

        $response = Http::timeout(300)
            ->withBasicAuth(
                config('services.n8n.basic_auth.username'),
                config('services.n8n.basic_auth.password')
            )
            ->post(config('services.n8n.url'), [
                'sessionId' => $request->sessionId,
                'chatInput' => $request->message,
            ]);

        return $response->json();
    }

    /**
     * Fake response for testing purposes
     */
    private function fakeResponse(?string $type = 'success'): \Illuminate\Http\Client\Factory
    {
        return Http::fake([
            config('services.n8n.url') => Http::response(json_encode(config("ai_responses.fake_responses.$type")), 200, ['Content-Type' => 'application/json']),
        ]);
    }
}
