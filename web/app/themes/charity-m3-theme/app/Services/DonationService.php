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
            // Set Stripe API key from environment variables or WordPress options
            // This should be configured securely, e.g. via .env file in Bedrock
            // and accessed via getenv() or a config service.
            Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));
            Stripe::setAppInfo(
                'Charity M3 WordPress Theme',
                '0.1.0',
                'https://example.com' // Replace with theme URI
            );
        }

        /**
         * Process a donation using a payment method ID from Stripe.js.
         *
         * @param int    $amount   Amount in cents.
         * @param string $currency e.g., 'usd'.
         * @param string $paymentMethodId The ID from Stripe.js (e.g., 'pm_...').
         * @param string $donorEmail
         * @param array  $metadata Optional metadata for the donation.
         * @return object|\WP_Error The saved donation record on success, or \WP_Error on failure.
         */
        public function processStripeDonation(int $amount, string $currency, string $paymentMethodId, string $donorEmail, array $metadata = [])
        {
            if (empty(getenv('STRIPE_SECRET_KEY'))) {
                return new \WP_Error('stripe_not_configured', __('Stripe is not configured.', 'charity-m3'));
            }

            try {
                // Create a PaymentIntent to confirm the payment
                $paymentIntent = PaymentIntent::create([
                    'amount' => $amount,
                    'currency' => $currency,
                    'payment_method' => $paymentMethodId,
                    'confirmation_method' => 'manual', // We confirm it right away
                    'confirm' => true,
                    'receipt_email' => $donorEmail,
                    'description' => __('Donation to Charity M3', 'charity-m3'),
                    // If you create a Stripe Customer, you can attach the payment method to them for recurring payments
                ]);

                // If payment is successful, save the record to our database
                if ($paymentIntent->status === 'succeeded') {
                    $donation_data = [
                        'donor_name'  => $metadata['donor_name'] ?? null,
                        'donor_email' => $donorEmail,
                        'amount'      => $amount,
                        'currency'    => $currency,
                        'frequency'   => $metadata['frequency'] ?? 'one-time',
                        'status'      => 'succeeded',
                        'gateway'     => 'stripe',
                        'gateway_transaction_id' => $paymentIntent->id,
                        'campaign_id' => $metadata['campaign_id'] ?? null,
                        'donated_at'  => current_time('mysql', true),
                    ];

                    $result = $this->db->insert($this->table_name, $donation_data);

                    if ($result) {
                        $donation_id = $this->db->insert_id;
                        // You could return the full record from DB
                        return (object) array_merge(['id' => $donation_id], $donation_data);
                    } else {
                        // This is a critical error state: payment succeeded but DB insert failed.
                        // Needs logging and manual intervention.
                        error_log("CRITICAL: Stripe payment succeeded ({$paymentIntent->id}) but failed to save to database.");
                        return new \WP_Error('db_insert_failed', __('Payment succeeded but could not save donation record.', 'charity-m3'));
                    }
                } else {
                    // PaymentIntent was created but did not succeed (e.g., requires action)
                    // You would handle other statuses here (e.g., requires_action) if not using `confirm: true`
                    return new \WP_Error('payment_not_succeeded', __('Payment could not be completed.', 'charity-m3'), ['stripe_status' => $paymentIntent->status]);
                }

            } catch (ApiErrorException $e) {
                // Handle Stripe API errors (e.g., card declined)
                return new \WP_Error('stripe_api_error', $e->getMessage(), ['stripe_error_code' => $e->getStripeCode()]);
            } catch (\Exception $e) {
                // Handle other exceptions
                return new \WP_Error('generic_error', $e->getMessage());
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
