<?php

namespace App\Providers;

use Roots\Acorn\ServiceProvider;
use App\Services\DonationService;

class DonationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DonationService::class, function () {
            global $wpdb;
            return new DonationService($wpdb);
        });
    }

    public function boot()
    {
        add_action('rest_api_init', [$this, 'registerDonationRoutes']);
    }

    public function registerDonationRoutes()
    {
        register_rest_route('charitym3/v1', '/donations/charge', [
            'methods' => 'POST',
            'callback' => [$this, 'handleDonationCharge'],
            'permission_callback' => '__return_true', // Publicly accessible, but protected by nonce
            'args' => [
                'amount' => ['required' => true, 'validate_callback' => 'is_numeric'],
                'currency' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
                'paymentMethodId' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
                'email' => ['required' => true, 'validate_callback' => 'is_email', 'sanitize_callback' => 'sanitize_email'],
                'name' => ['required' => false, 'sanitize_callback' => 'sanitize_text_field'],
                'frequency' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
                'campaignId' => ['required' => false, 'validate_callback' => 'is_numeric'],
                // Add nonce validation here in args if desired for automatic handling
                // 'nonce' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
            ],
        ]);
    }

    public function handleDonationCharge(\WP_REST_Request $request)
    {
        // Manual nonce check if not handled in args
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_nonce_invalid', __('Nonce is invalid.', 'charity-m3'), ['status' => 403]);
        }

        /** @var DonationService $donationService */
        $donationService = $this->app->make(DonationService::class);

        $amount = (int) $request->get_param('amount');
        $currency = $request->get_param('currency');
        $paymentMethodId = $request->get_param('paymentMethodId');
        $email = $request->get_param('email');

        $metadata = [
            'donor_name' => $request->get_param('name'),
            'frequency' => $request->get_param('frequency'),
            'campaign_id' => $request->get_param('campaignId'),
        ];

        $result = $donationService->processStripeDonation($amount, $currency, $paymentMethodId, $email, $metadata);

        if (is_wp_error($result)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
                'code' => $result->get_error_code(),
            ], 400); // Bad Request or appropriate status code
        }

        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Thank you for your donation!', 'charity-m3'),
            'donation' => $result, // The saved donation record
        ], 200);
    }
}
