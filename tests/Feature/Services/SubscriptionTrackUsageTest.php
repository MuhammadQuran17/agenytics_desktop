<?php

use App\Models\User;
use App\Services\Subscription\SubscriptionTrackUsage;
use Carbon\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('SubscriptionTrackUsage', function () {
    describe('zeroingPurchasedPromptsIfExpired', function () {
        it('zeros out purchased prompts when they are expired', function () {
            $user = User::factory()->create([
                'purchased_prompts' => 10,
                'purchased_prompts_expires_at' => Carbon::now()->subDay(),
            ]);

            $this->actingAs($user);
            $service = new SubscriptionTrackUsage();
            $service->zeroingPurchasedPromptsIfExpired();

            $user->refresh();
            expect($user->purchased_prompts)->toBe(0);
        });

        it('keeps purchased prompts when they are not expired', function () {
            $user = User::factory()->create([
                'purchased_prompts' => 10,
                'purchased_prompts_expires_at' => Carbon::now()->addDay(),
            ]);

            $this->actingAs($user);
            $service = new SubscriptionTrackUsage();
            $service->zeroingPurchasedPromptsIfExpired();

            $user->refresh();
            expect($user->purchased_prompts)->toBe(10);
        });

        it('keeps purchased prompts when expiration date is null', function () {
            $user = User::factory()->create([
                'purchased_prompts' => 10,
                'purchased_prompts_expires_at' => null,
            ]);

            $this->actingAs($user);
            $service = new SubscriptionTrackUsage();
            $service->zeroingPurchasedPromptsIfExpired();

            $user->refresh();
            expect($user->purchased_prompts)->toBe(10);
        });
    });
});

