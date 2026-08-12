<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for subscription plans.
    | These plans are used for displaying on the frontend and duplicates products in Stripe.
    |
    */

    'plans' => [
        '100_prompts' => [
            'name' => '100 Prompts Plan',
            'description' => 'You will get 100 prompts for 6 months from last purchase. More details on how we calculated prompts price in FAQ.',
            'price' => 6.94,
            'prompts_to_add' => 100,
            'stripe_price_id' => env('STRIPE_100_PROMPTS_PRICE_ID', 'price_1SV80TE8U13QCN3Yzfnv4R4c'),
            'stripe_product_id' => env('STRIPE_100_PROMPTS_PRODUCT_ID', 'prod_TS24pqrkMsdUnl'),
            'price_type' => 'one_time',
            'lifetime_in_months' => 6,
        ],

        '200_prompts' => [
            'name' => '200 Prompts Plan',
            'description' => 'You will get 200 prompts for 6 months from last purchase. More details on how we calculated prompts price in FAQ.',
            'price' => 12.82,
            'prompts_to_add' => 200,
            'stripe_price_id' => env('STRIPE_200_PROMPTS_PRICE_ID', 'price_test'),
            'stripe_product_id' => env('STRIPE_200_PROMPTS_PRODUCT_ID', 'prod_test'),
            'price_type' => 'one_time',
            'lifetime_in_months' => 6,
        ],

        'basic_monthly' => [
            'name' => 'Basic Monthly Plan',
            'description' => 'You will get 200 prompts for 1 month + extra fee if you use more than 200 prompts. More details on how we calculated prompts price in FAQ.',
            'price' => 11.37,
            'stripe_metered_price_id' => env('STRIPE_BASIC_PRICE_METERED_ID', 'price_test'),
            'stripe_price_id' => env('STRIPE_BASIC_PRICE_ID', 'price_test'),
            'stripe_product_id' => env('STRIPE_BASIC_MONTHLY_PRODUCT_ID', 'prod_test'),
            'features' => [
                '$11.37 for 200 prompts',
                'then $0.079 per extra prompt',
            ],
            'price_type' => 'monthly',
        ],
    ],
];
