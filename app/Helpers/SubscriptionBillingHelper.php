<?php

namespace App\Helpers;

// [TestCoverage] 100%
class SubscriptionBillingHelper
{
    public static function getPlansStripeProductIdsThatUseMeteredBilling(): ?array
    {
        $plan_key_and_productId = array_map(function ($plan) {
            return $plan['stripe_product_id'];
        }, self::getPlansThatUseMeteredBilling());

        return array_values($plan_key_and_productId);
    }

    public static function getPlansThatUseMeteredBilling(): ?array
    {
        return array_filter(config("subscription-plans.plans", []), function ($plan) {
            return isset($plan['stripe_metered_price_id']);
        });
    }
}