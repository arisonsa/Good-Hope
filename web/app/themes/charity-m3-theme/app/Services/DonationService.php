<?php

    namespace App\Services;

    use wpdb;
    use Stripe\Stripe;
    use Stripe\PaymentIntent;
    use Stripe\Exception\ApiErrorException;

    class DonationService
    {
        private wpdb $db;
        private string $table_name;

        public function __construct(wpdb $db)
        {
            $this->db = $db;
            $this->table_name = $this->db->prefix . 'charity_m3_donations';

            $stripe_secret_key = getenv('STRIPE_SECRET_KEY');
            if ($stripe_secret_key) {
                Stripe::setApiKey($stripe_secret_key);
                Stripe::setAppInfo('Charity M3 WordPress Theme', '0.1.0', home_url());
            }
        }

        /**
         * Process a one-time or recurring donation via Stripe.
         */
        public function processStripeDonation(int $amount, string $currency, string $paymentMethodId, string $donorEmail, array $metadata = [])
        {
            if (empty(getenv('STRIPE_SECRET_KEY'))) {
                return new \WP_Error('stripe_not_configured', __('Stripe is not configured.', 'charity-m3'));
            }

            $frequency = $metadata['frequency'] ?? 'one-time';

            try {
                if ($frequency === 'monthly') {
                    return $this->createStripeSubscription($amount, $currency, $paymentMethodId, $donorEmail, $metadata);
                } else {
                    return $this->createOneTimeCharge($amount, $currency, $paymentMethodId, $donorEmail, $metadata);
                }
            } catch (ApiErrorException $e) {
                return new \WP_Error('stripe_api_error', $e->getMessage(), ['stripe_error_code' => $e->getStripeCode()]);
            } catch (\Exception $e) {
                return new \WP_Error('generic_error', $e->getMessage());
            }
        }

        private function createOneTimeCharge(int $amount, string $currency, string $paymentMethodId, string $donorEmail, array $metadata)
        {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
                'payment_method' => $paymentMethodId,
                'confirm' => true,
                'receipt_email' => $donorEmail,
                'description' => __('One-time donation', 'charity-m3'),
                'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
            ]);

            if ($paymentIntent->status === 'succeeded') {
                $donation_data = [
                    'donor_id'    => get_current_user_id() ?: null,
                    'donor_name'  => $metadata['donor_name'] ?? null,
                    'donor_email' => $donorEmail,
                'on_behalf_of' => $metadata['on_behalf_of'] ?? null,
                    'amount'      => $amount,
                    'currency'    => $currency,
                    'frequency'   => 'one-time',
                    'status'      => 'succeeded',
                    'gateway'     => 'stripe',
                'payment_method_type' => $paymentIntent->payment_method_types[0] ?? 'card',
                    'gateway_transaction_id' => $paymentIntent->id,
                    'campaign_id' => $metadata['campaign_id'] ?? null,
                'earmark' => $metadata['earmark'] ?? null,
                ];
                return $this->createDonationRecord($donation_data);
            } else {
                return new \WP_Error('payment_not_succeeded', __('Payment could not be completed.', 'charity-m3'));
            }
        }

        private function createStripeSubscription(int $amount, string $currency, string $paymentMethodId, string $donorEmail, array $metadata)
        {
            $customer = $this->findOrCreateStripeCustomer($donorEmail, $metadata['donor_name'] ?? null, $paymentMethodId);
            $price = $this->findOrCreateStripePrice($amount, $currency, 'month');

            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [['price' => $price->id]],
                'expand' => ['latest_invoice.payment_intent'],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            ]);

            $payment_intent = $subscription->latest_invoice->payment_intent;

            if ($payment_intent && $payment_intent->status === 'succeeded') {
                 $donation_data = [
                    'donor_id'    => get_current_user_id() ?: null,
                    'donor_name'  => $metadata['donor_name'] ?? null,
                    'donor_email' => $donorEmail,
                'on_behalf_of' => $metadata['on_behalf_of'] ?? null,
                    'amount'      => $amount,
                    'currency'    => $currency,
                    'frequency'   => 'monthly',
                    'status'      => 'succeeded', // For the initial payment
                    'gateway'     => 'stripe',
                'payment_method_type' => $payment_intent->payment_method_types[0] ?? 'card',
                    'gateway_transaction_id' => $payment_intent->id,
                    'stripe_customer_id' => $customer->id,
                    'stripe_subscription_id' => $subscription->id,
                    'campaign_id' => $metadata['campaign_id'] ?? null,
                'earmark' => $metadata['earmark'] ?? null,
                ];
                return $this->createDonationRecord($donation_data);
            } else {
                 return new \WP_Error('subscription_not_succeeded', __('Could not start subscription. Payment failed or requires action.', 'charity-m3'));
            }
        }

        private function findOrCreateStripeCustomer(string $email, ?string $name, string $paymentMethodId)
        {
            $existing_customers = \Stripe\Customer::all(['email' => $email, 'limit' => 1]);
            if (!empty($existing_customers->data)) {
                $customer = $existing_customers->data[0];
                \Stripe\PaymentMethod::attach($paymentMethodId, ['customer' => $customer->id]);
                \Stripe\Customer::update($customer->id, ['invoice_settings' => ['default_payment_method' => $paymentMethodId]]);
                return $customer;
            }

            return \Stripe\Customer::create([
                'email' => $email,
                'name' => $name,
                'payment_method' => $paymentMethodId,
                'invoice_settings' => [ 'default_payment_method' => $paymentMethodId ],
            ]);
        }

        private function findOrCreateStripePrice(int $amount, string $currency, string $interval)
        {
            $product_id = 'prod_charitym3_monthly_donation'; // A single product for all monthly donations

            try {
                $product = \Stripe\Product::retrieve($product_id);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $product = \Stripe\Product::create(['name' => 'Monthly Donation', 'id' => $product_id, 'type' => 'service']);
            }

            $prices = \Stripe\Price::all(['product' => $product->id, 'currency' => $currency, 'recurring' => ['interval' => $interval], 'unit_amount' => $amount, 'limit' => 1]);
            if (!empty($prices->data)) {
                return $prices->data[0];
            }

            return \Stripe\Price::create(['product' => $product->id, 'unit_amount' => $amount, 'currency' => $currency, 'recurring' => ['interval' => $interval]]);
        }

        public function createDonationRecord(array $data)
        {
            $result = $this->db->insert($this->table_name, $data);
            if ($result) {
                $donation_id = $this->db->insert_id;
                // If a logged-in user made this donation, link customer ID to them
                if (!empty($data['donor_id']) && !empty($data['stripe_customer_id'])) {
                    update_user_meta($data['donor_id'], 'stripe_customer_id', $data['stripe_customer_id']);
                }
                return (object) array_merge(['id' => $donation_id], $data);
            }
            error_log("CRITICAL: Stripe payment succeeded ({$data['gateway_transaction_id']}) but failed to save to database.");
            return new \WP_Error('db_insert_failed', __('Payment succeeded but could not save donation record.', 'charity-m3'));
        }

        public function getDonationBySubscriptionId(string $subscription_id)
        {
            return $this->db->get_row($this->db->prepare("SELECT * FROM {$this->table_name} WHERE stripe_subscription_id = %s ORDER BY id ASC LIMIT 1", $subscription_id));
        }

        public function findOrCreateStripeCustomer(string $email, ?string $name, string $paymentMethodId = null)
        {
            $existing_customers = \Stripe\Customer::all(['email' => $email, 'limit' => 1]);
            if (!empty($existing_customers->data)) {
                $customer = $existing_customers->data[0];
                if ($paymentMethodId) {
                    \Stripe\PaymentMethod::attach($paymentMethodId, ['customer' => $customer->id]);
                    \Stripe\Customer::update($customer->id, ['invoice_settings' => ['default_payment_method' => $paymentMethodId]]);
                }
                return $customer;
            }

            $params = [
                'email' => $email,
                'name' => $name,
            ];

            if ($paymentMethodId) {
                $params['payment_method'] = $paymentMethodId;
                $params['invoice_settings'] = ['default_payment_method' => $paymentMethodId];
            }

            return \Stripe\Customer::create($params);
        }

        public function findOrCreateStripePrice(int $amount, string $currency, string $interval)
        {
            $product_id = 'prod_charitym3_monthly_donation'; // A single product for all monthly donations

            try {
                $product = \Stripe\Product::retrieve($product_id);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $product = \Stripe\Product::create(['name' => 'Monthly Donation', 'id' => $product_id, 'type' => 'service']);
            }

            $prices = \Stripe\Price::all(['product' => $product->id, 'currency' => $currency, 'recurring' => ['interval' => $interval], 'unit_amount' => $amount, 'limit' => 1]);
            if (!empty($prices->data)) {
                return $prices->data[0];
            }

            return \Stripe\Price::create(['product' => $product->id, 'unit_amount' => $amount, 'currency' => $currency, 'recurring' => ['interval' => $interval]]);
        }

        public function getPaymentIntentStatus(string $paymentIntentId): string
        {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
            return $paymentIntent->status;
        }

    /**
     * Creates a Payment Intent for use with the Payment Element.
     *
     * @param int $amount Amount in cents.
     * @param string $currency e.g., 'usd'.
     * @param string $frequency 'one-time' or 'monthly'.
     * @return string|\WP_Error The client_secret on success, or \WP_Error on failure.
     */
    public function createPaymentIntent(int $amount, string $currency, string $frequency = 'one-time', array $metadata = [])
    {
        if (empty(getenv('STRIPE_SECRET_KEY'))) {
            return new \WP_Error('stripe_not_configured', __('Stripe is not configured.', 'charity-m3'));
        }

        try {
            $params = [
                'amount' => $amount,
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => $metadata,
            ];

            // If this is for a subscription, we need to set up the intent differently
            if ($frequency === 'monthly') {
                // For subscriptions, the initial setup might be different.
                // We might create a customer and an intent with setup_future_usage.
                // Or, the subscription creation itself will generate the first payment intent.
                // For Payment Element, we typically create a setup intent for subscriptions
                // or a payment intent with setup_future_usage.
                $params['setup_future_usage'] = 'on_session';
            }

            $paymentIntent = PaymentIntent::create($params);

            return $paymentIntent->client_secret;

        } catch (\Exception $e) {
            return new \WP_Error('payment_intent_error', $e->getMessage());
        }
    }

        /**
         * Get donations with pagination, filtering, and sorting.
         * (Similar to SubscriberService::getSubscribers)
         *
         * @param array $args
         * @return array An array containing 'items' (list of donations) and 'total_items'.
         */
        public function getDonations(array $args = []): array
        {
            $defaults = [
                'per_page' => 20,
                'page' => 1,
                'search' => '', // Search by email or transaction ID
                'status' => '',
                'orderby' => 'donated_at',
                'order' => 'DESC',
            ];
            $args = wp_parse_args($args, $defaults);

            $where_clauses = ['1=1'];
            $params = [];

            if (!empty($args['search'])) {
                $search_term = '%' . $this->db->esc_like(sanitize_text_field($args['search'])) . '%';
                $where_clauses[] = "(donor_email LIKE %s OR gateway_transaction_id LIKE %s OR donor_name LIKE %s)";
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
            }

            if (!empty($args['status'])) {
                $where_clauses[] = "status = %s";
                $params[] = sanitize_text_field($args['status']);
            }

            $where_sql = implode(' AND ', $where_clauses);

            $total_items = $this->db->get_var($this->db->prepare("SELECT COUNT(id) FROM $this->table_name WHERE $where_sql", $params));

            $offset = ($args['page'] - 1) * $args['per_page'];
            $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);

            $items = $this->db->get_results($this->db->prepare("SELECT * FROM $this->table_name WHERE $where_sql ORDER BY $orderby LIMIT %d OFFSET %d", array_merge($params, [$args['per_page'], $offset])));

            return [
                'items' => $items,
                'total_items' => (int) $total_items,
            ];
        }
    }
