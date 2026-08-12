<?php

use App\Helpers\SubscriptionBillingHelper;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function setupTestPlans(): void
{
    config(['subscription-plans.plans' => [
        'one_time_plan' => [
            'name' => 'One Time Plan',
            'stripe_product_id' => 'prod_123',
            'price_type' => 'one_time',
        ],
        'monthly_plan' => [
            'name' => 'Monthly Plan',
            'stripe_product_id' => 'prod_124',
            'stripe_metered_price_id' => 'price_123',
            'price_type' => 'monthly',
        ],
    ]]);
}

function setupOnlyOneTimePlans(): void
{
    config(['subscription-plans.plans' => [
        'one_time_plan' => [
            'name' => 'One Time Plan',
            'stripe_product_id' => 'prod_123',
            'price_type' => 'one_time',
        ],
    ]]);
}

describe('SubscriptionBillingHelper', function () {
    describe('getPlansThatUseMeteredBilling', function () {
        it('returns only plans with stripe_metered_price_id', function () {
            setupTestPlans();
                
            $plans = SubscriptionBillingHelper::getPlansThatUseMeteredBilling();

            expect($plans)->not->toBeNull();
            expect($plans)->toBeArray();

            foreach ($plans as $plan) {
                expect($plan)->toHaveKey('stripe_metered_price_id');
            }
        });

        it('does not include plans without stripe_metered_price_id', function () {
            setupOnlyOneTimePlans();

            $plans = SubscriptionBillingHelper::getPlansThatUseMeteredBilling();

            expect($plans)->toBeArray();
            expect($plans)->toBeEmpty();
        });

        it('returns empty array when no metered plans exist', function () {
            setupOnlyOneTimePlans();

            $plans = SubscriptionBillingHelper::getPlansThatUseMeteredBilling();

            expect($plans)->toBeArray();
            expect($plans)->toBeEmpty();
        });
    });

    describe('getPlansStripeProductIdsThatUseMeteredBilling', function () {
        it('returns stripe product ids from metered billing plans', function () {
            setupTestPlans();

            $productIds = SubscriptionBillingHelper::getPlansStripeProductIdsThatUseMeteredBilling();

            expect($productIds)->not->toBeNull();
            expect($productIds)->toBeArray();
            expect($productIds)->not->toBeEmpty();

            foreach ($productIds as $productId) {
                expect($productId)->toBeString();
            }
        });

        it('extracts only stripe_product_id from metered plans', function () {
            setupTestPlans();

            $meteredPlans = SubscriptionBillingHelper::getPlansThatUseMeteredBilling();
            $productIds = SubscriptionBillingHelper::getPlansStripeProductIdsThatUseMeteredBilling();

            $expectedProductIds = array_values(array_map(function ($plan) {
                return $plan['stripe_product_id'];
            }, $meteredPlans));

            expect($productIds)->toEqual($expectedProductIds);
        });

        it('returns empty array when no metered plans exist', function () {
            setupOnlyOneTimePlans();

            $productIds = SubscriptionBillingHelper::getPlansStripeProductIdsThatUseMeteredBilling();

            expect($productIds)->toBeArray();
            expect($productIds)->toBeEmpty();
        });

        it('preserves indexed values without null entries', function () {
            setupTestPlans();

            $productIds = SubscriptionBillingHelper::getPlansStripeProductIdsThatUseMeteredBilling();

            // Verify no null values exist
            foreach ($productIds as $id) {
                expect($id)->not->toBeNull();
            }

            // Verify array is indexed starting from 0
            expect(array_keys($productIds))->toEqual(range(0, count($productIds) - 1));
        });
    });

    describe('integration', function () {
        it('returns consistent results across multiple calls', function () {
            setupTestPlans();

            $metered1 = SubscriptionBillingHelper::getPlansThatUseMeteredBilling();
            $productIds1 = SubscriptionBillingHelper::getPlansStripeProductIdsThatUseMeteredBilling();

            $metered2 = SubscriptionBillingHelper::getPlansThatUseMeteredBilling();
            $productIds2 = SubscriptionBillingHelper::getPlansStripeProductIdsThatUseMeteredBilling();

            expect($metered1)->toEqual($metered2);
            expect($productIds1)->toEqual($productIds2);
        });
    });
});

