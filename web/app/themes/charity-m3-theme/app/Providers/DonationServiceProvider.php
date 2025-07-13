<?php

namespace App\Providers;

use Roots\Acorn\ServiceProvider;
use App\Services\DonationService;
use App\Services\StripeWebhookService; // Add this

class DonationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DonationService::class, function ($app) {
            global $wpdb;
            return new DonationService($wpdb);
        });

        $this->app->singleton(StripeWebhookService::class, function ($app) {
            // It depends on DonationService
            return new StripeWebhookService($app->make(DonationService::class));
        });
    }

    public function boot()
    {
        add_action('rest_api_init', [$this, 'registerDonationRoutes']);
        add_action('rest_api_init', [$this, 'registerWebhookRoutes']);

        // Handle form post for creating customer portal session
        add_action('admin_post_nopriv_charity_m3_create_customer_portal_session', [$this, 'redirectToLogin']);
        add_action('admin_post_charity_m3_create_customer_portal_session', [$this, 'handleCreateCustomerPortalSession']);
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

    public function redirectToLogin()
    {
        // Redirect non-logged-in users to the login page
        wp_safe_redirect(wp_login_url(home_url('/my-account/'))); // Redirect back to My Account after login
        exit;
    }

    public function handleCreateCustomerPortalSession()
    {
        if (!isset($_POST['_wpnonce_customer_portal']) || !wp_verify_nonce(sanitize_key($_POST['_wpnonce_customer_portal']), 'create_customer_portal_session_nonce')) {
            wp_die(__('Security check failed.', 'charity-m3'));
        }

        if (!is_user_logged_in()) {
            $this->redirectToLogin();
        }

        $user_id = get_current_user_id();
        $stripe_customer_id = get_user_meta($user_id, 'stripe_customer_id', true);

        if (empty($stripe_customer_id)) {
            wp_die(__('No subscription data found for your account.', 'charity-m3'));
        }

        if (empty(getenv('STRIPE_SECRET_KEY'))) {
            wp_die(__('Stripe is not configured.', 'charity-m3'));
        }

        try {
            \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

            // Create a Customer Portal session
            $session = \Stripe\BillingPortal\Session::create([
                'customer' => $stripe_customer_id,
                'return_url' => home_url('/my-account/'), // Where to redirect after session
            ]);

            // Redirect to the session URL
            wp_safe_redirect($session->url);
            exit;

        } catch (\Exception $e) {
            wp_die(__('Could not create a portal session. Please contact support.', 'charity-m3') . ' Error: ' . $e->getMessage());
        }
    }

    public function registerWebhookRoutes()
    {
        register_rest_route('charitym3/v1', '/stripe-webhooks', [
            'methods' => 'POST',
            'callback' => function (\WP_REST_Request $request) {
                // Resolve the service from the container
                $webhookService = $this->app->make(StripeWebhookService::class);
                return $webhookService->handle($request);
            },
            'permission_callback' => '__return_true', // Permission is handled by signature verification
        ]);
    }
}
