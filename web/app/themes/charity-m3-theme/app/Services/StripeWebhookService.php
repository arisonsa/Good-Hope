<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Event;

class StripeWebhookService
{
    private DonationService $donationService;

    public function __construct(DonationService $donationService)
    {
        $this->donationService = $donationService;
        // Set API key for webhook verification if not already set globally
        Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));
    }

    /**
     * Handle incoming Stripe webhook events.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function handle(\WP_REST_Request $request)
    {
        $payload = $request->get_body();
        $sig_header = $request->get_header('stripe_signature');
        $endpoint_secret = getenv('STRIPE_WEBHOOK_SECRET'); // Must be configured in .env

        if (empty($endpoint_secret)) {
            error_log('Stripe Webhook Secret is not configured.');
            return new \WP_Error('webhook_secret_missing', 'Webhook secret is not configured.', ['status' => 500]);
        }

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return new \WP_Error('invalid_payload', 'Invalid payload.', ['status' => 400]);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return new \WP_Error('invalid_signature', 'Invalid signature.', ['status' => 400]);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;
            // Add other event types to handle as needed (e.g., payment_failed, subscription_updated)
            default:
                // Unhandled event type
                error_log('Unhandled Stripe event type: ' . $event->type);
        }
    }

    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'charity_m3_donations';
        $existing_donation = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table_name} WHERE gateway_transaction_id = %s", $paymentIntent->id));

        if ($existing_donation) {
            error_log('Webhook received for already processed PaymentIntent: ' . $paymentIntent->id);
            return;
        }

        // The 'invoice' property is set on PaymentIntents created for subscriptions.
        // If it exists, it will be handled by the 'invoice.payment_succeeded' webhook.
        if (!empty($paymentIntent->invoice)) {
            return;
        }

        $metadata = $paymentIntent->metadata;
        $charge = $paymentIntent->charges->data[0] ?? null;

        $donation_data = [
            'donor_email' => $metadata->email ?? $charge->billing_details->email,
            'donor_name' => $metadata->name ?? $charge->billing_details->name,
            'on_behalf_of' => $metadata->on_behalf_of ?? null,
            'earmark' => $metadata->earmark ?? 'general_fund',
            'amount' => $paymentIntent->amount,
            'currency' => $paymentIntent->currency,
            'frequency' => $metadata->frequency ?? 'one-time',
            'status' => 'succeeded',
            'gateway' => 'stripe',
            'payment_method_type' => $charge->payment_method_details->type ?? 'unknown',
            'gateway_transaction_id' => $paymentIntent->id,
            'campaign_id' => $metadata->campaign_id ?? null,
        ];

        // This PI is for a subscription setup, not a one-time payment.
        if (($metadata->frequency ?? 'one-time') === 'monthly' && $paymentIntent->setup_future_usage) {
            $customer = $this->donationService->findOrCreateStripeCustomer($donation_data['donor_email'], $donation_data['donor_name'], $paymentIntent->payment_method);
            $price = $this->donationService->findOrCreateStripePrice($donation_data['amount'], $donation_data['currency'], 'month');

            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [['price' => $price->id]],
                'default_payment_method' => $paymentIntent->payment_method,
                'metadata' => [
                    'earmark' => $donation_data['earmark'],
                    'on_behalf_of' => $donation_data['on_behalf_of'],
                    'campaign_id' => $donation_data['campaign_id'],
                ]
            ]);

            $donation_data['stripe_customer_id'] = $customer->id;
            $donation_data['stripe_subscription_id'] = $subscription->id;
        }

        $this->donationService->createDonationRecord($donation_data);

        return new \WP_REST_Response(['status' => 'success'], 200);
    }

    /**
     * Handle the invoice.payment_succeeded event for subscriptions.
     * This creates a new donation record for a successful recurring payment.
     *
     * @param \Stripe\Invoice $invoice
     */
    protected function handleInvoicePaymentSucceeded($invoice)
    {
        // Check if this is for a subscription renewal
        if ($invoice->billing_reason !== 'subscription_cycle') {
            return;
        }

        $subscription_id = $invoice->subscription;
        if (!$subscription_id) {
            return;
        }

        // Find the original donation record to get our metadata
        $original_donation = $this->donationService->getDonationBySubscriptionId($subscription_id);
        if (!$original_donation) {
            error_log("Could not find original donation for subscription ID: {$subscription_id}");
            return;
        }

        // Create a new donation record for this renewal payment
        $this->donationService->createDonationRecord([
            'donor_id' => $original_donation->donor_id,
            'donor_name' => $original_donation->donor_name,
            'donor_email' => $original_donation->donor_email,
            'amount' => $invoice->amount_paid, // Amount is in cents from Stripe
            'currency' => $invoice->currency,
            'frequency' => 'monthly-renewal', // Or just 'monthly'
            'status' => 'succeeded',
            'gateway' => 'stripe',
            'gateway_transaction_id' => $invoice->payment_intent, // The PI for this invoice
            'stripe_customer_id' => $invoice->customer,
            'stripe_subscription_id' => $subscription_id,
            'campaign_id' => $original_donation->campaign_id,
        ]);
    }

    /**
     * Handle the customer.subscription.deleted event.
     * This can be used to update the status of the subscription in our system.
     *
     * @param \Stripe\Subscription $subscription
     */
    protected function handleSubscriptionDeleted($subscription)
    {
        // Find the original donation and maybe update its status or a related user meta
        $original_donation = $this->donationService->getDonationBySubscriptionId($subscription->id);
        if ($original_donation) {
            // Example: Log this event or update a related user's status
            // For now, we don't have a status on the donation record itself for this,
            // but one could be added (e.g., 'active', 'cancelled').
            error_log("Stripe subscription cancelled: {$subscription->id}");
        }
    }
}
