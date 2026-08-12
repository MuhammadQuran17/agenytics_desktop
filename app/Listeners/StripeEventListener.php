<?php

namespace App\Listeners;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Laravel\Cashier\Events\WebhookReceived;

class StripeEventListener
{
    /**
     * Handle received Stripe webhooks.
     * checkout.session.completed is used because it has metadata and needed data for one time payment
     * In one time payment we have events: payment_intent.succeeded, mandate.updated, payment_intent.created, charge.succeeded, checkout.session.completed
     */
    public function handle(WebhookReceived $event): void
    {
        $this->logWebhookReceived($event);

        if ($event->payload['type'] === 'checkout.session.completed') {
            $this->handleCheckoutSessionCompleted($event);
        }
    }

    /**
     * This is where we credit purchased prompts to the user's balance
     */
    private function handleCheckoutSessionCompleted(WebhookReceived $event): void
    {
        $checkoutSession = $event->payload['data']['object'];

        // Get plan key from metadata (set during checkout creation and only for one time payment)
        $planKey = Arr::get($checkoutSession, 'metadata.plan_key');

        if (! $planKey) {
            logger()->debug("[Stripe] Subscription based purchase, no plan key in metadata");
            return;
        }

        // Find user by Stripe customer ID
        $user = User::where('stripe_id', Arr::get($checkoutSession, 'customer'))->firstOrFail();

        // Determine from config how many prompts to add based on plan key
        $promptsToAdd = config("subscription-plans.plans.$planKey")['prompts_to_add'];

        if ($promptsToAdd > 0) {
            $user->increment('purchased_prompts', $promptsToAdd);

            $user->purchased_prompts_expires_at = Carbon::now()->addMonths(config("subscription-plans.plans.$planKey")['lifetime_in_months']);
            $user->save();
        }
    }

    /**
     * Log structured information about the received Stripe webhook
     * Logs: event type, customer email, amount, metadata, Stripe Event ID, timestamp, related IDs, and outcome
     */
    private function logWebhookReceived(WebhookReceived $event): void
    {
        $payload = $event->payload;
        $object = $payload['data']['object'];

        $customerId = Arr::get($object, 'customer');
        $customerEmail = Arr::get($object, 'customer_details.email') ?? 'unknown';

        // Extract amount and currency (different formats for different object types)
        $amount = Arr::get($object, 'amount_total');
        $currency = Arr::get($object, 'currency');
        $amountFormatted = $amount ? number_format($amount / 100, 2) : 'N/A';

        $loggerText = "[Stripe] Webhook received: " . $payload['type'] . " with status:"
            . Arr::get($object, 'status') . " and customerId: $customerId, customerEmail: $customerEmail";

        logger()->debug($loggerText, [
            'event_type' => $payload['type'],
            'stripe_event_id' => $payload['id'],
            'received_at' => $payload['created'],
            'customer_email' => $customerEmail,
            'payment_amount' => "{$currency} {$amountFormatted}",
            'metadata' => Arr::get($object, 'metadata', []),
            'related_ids' => [
                'customer_id' => $customerId,
                'payment_intent_id' => Arr::get($object, 'payment_intent'),
                'subscription_id' => Arr::get($object, 'subscription'),
                'checkout_session_id' => Arr::get($object, 'id'),
            ],
            'payment_status' => Arr::get($object, 'payment_status') ?? 'N/A',
            'status' => Arr::get($object, 'status') ?? 'N/A',
        ]);
    }
}
