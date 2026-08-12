<?php

use App\Http\Requests\Api\AiAgent\AiAgentSendMessageRequest;
use App\Services\AiChat\Contracts\MessageProcessingContract;
use App\Services\Subscription\Exceptions\NoFreePromptsAndNoSubscriptionException;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__ . '/helpers.php';

uses(RefreshDatabase::class);

describe('SendMessageToAiController - NoFreePromptsAndNoSubscription', function () {
    it('returns 402 status code when NoFreePromptsAndNoSubscriptionException is thrown', function () {
        $user = makeUserWithPrompts(0);

        $this->actingAs($user);

        // Mock the message processing contract to throw the exception
        mockMessageProcessorToThrow(new NoFreePromptsAndNoSubscriptionException());

        $response = postChatRequest($this);

        $response->assertStatus(402);
        $response->assertHeader('Content-Type', 'application/json');
    });

    it('returns 200 status code when message is processed successfully', function () {
        $user = makeUserWithPrompts(5);

        $this->actingAs($user);

        $mockResponse = [
            'response' => 'Test response',
        ];

        mockMessageProcessorToReturn($mockResponse);

        $response = postChatRequest($this);

        $response->assertSuccessful();
        $response->assertJsonFragment($mockResponse);
    });


    it('passes AiAgentSendMessageRequest to message processing', function () {
        $user = makeUserWithPrompts(5);

        $this->actingAs($user);

        $mockResponse = [
            'response' => 'Test response',
        ];

        $this->instance(
            MessageProcessingContract::class,
            $this->mock(MessageProcessingContract::class, function ($mock) use ($mockResponse) {
                $mock->shouldReceive('processMessage')
                    ->with(\Mockery::type(AiAgentSendMessageRequest::class))
                    ->andReturn($mockResponse);
            })
        );

        $response = postChatRequest($this);

        $response->assertSuccessful();
    });

    it('only catches NoFreePromptsAndNoSubscriptionException and lets other exceptions propagate', function () {
        $user = makeUserWithPrompts(0);

        $this->actingAs($user);

        mockMessageProcessorToThrow(new \RuntimeException('Different exception'));

        // Other exceptions should propagate through (not caught by the controller)
        $this->withoutExceptionHandling();

        $response = postChatRequest($this);
    })->throws(\RuntimeException::class);
});

describe('SendMessageToAiController - Integration', function () {
    it('requires authentication to send messages', function () {
        $response = postChatRequest($this);

        $response->assertUnauthorized();
    });

    it('validates required request fields', function () {
        $user = makeUserWithPrompts(5);

        $this->actingAs($user);

        $response = postChatRequest($this, [
            // Missing required fields
        ], false);

        $response->assertUnprocessable();
    });

    it('validates message is not empty', function () {
        $user = makeUserWithPrompts(5);

        $this->actingAs($user);

        $response = postChatRequest($this, [
            'message' => '',
        ]);

        $response->assertUnprocessable();
    });

    it('allows null selectedSchemaId', function () {
        $user = makeUserWithPrompts(5);

        $this->actingAs($user);

        $mockResponse = [
            'response' => 'Test response',
        ];

        mockMessageProcessorToReturn($mockResponse);

        $response = postChatRequest($this, [
            'selectedSchemaId' => null,
        ]);

        $response->assertSuccessful();
    });
});

