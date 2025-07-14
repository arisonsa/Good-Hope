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
        add_action('rest_api_init', [$this, 'registerEarmarkRoutes']);

        // Handle form post for creating customer portal session
        add_action('admin_post_nopriv_charity_m3_create_customer_portal_session', [$this, 'redirectToLogin']);
        add_action('admin_post_charity_m3_create_customer_portal_session', [$this, 'handleCreateCustomerPortalSession']);
    }

    public function registerDonationRoutes()
    {
        // Endpoint to create a payment intent and get a client secret for the Payment Element
        register_rest_route('charitym3/v1', '/donations/intent', [
            'methods' => 'POST',
            'callback' => [$this, 'handleCreatePaymentIntent'],
            'permission_callback' => fn () => is_user_logged_in(),
            'args' => [
                'amount' => ['required' => true, 'validate_callback' => 'is_numeric'],
                'currency' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
                'frequency' => [
                    'required' => true,
                    'validate_callback' => function ($param, $request, $key) {
                        return in_array($param, ['one-time', 'monthly']);
                    }
                ],
            ],
        ]);

        // Endpoint to check the status of a donation after redirect
        register_rest_route('charitym3/v1', '/donations/status/(?P<id>pi_[\w]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'handleGetDonationStatus'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_string($param) && strpos($param, 'pi_') === 0;
                    }
                ]
            ],
        ]);
    }

    public function handleCreatePaymentIntent(\WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        $amount = $params['amount'] ?? 0;
        $currency = $params['currency'] ?? 'usd';
        $frequency = $params['frequency'] ?? 'one-time';
        $name = sanitize_text_field($params['name'] ?? '');
        $email = sanitize_email($params['email'] ?? '');
        $onBehalfOf = sanitize_text_field($params['on_behalf_of'] ?? '');
        $earmark = sanitize_text_field($params['earmark'] ?? 'general_fund');
        $campaignId = isset($params['campaign_id']) ? absint($params['campaign_id']) : null;

        $metadata = [
            'name' => $name,
            'email' => $email,
            'on_behalf_of' => $onBehalfOf,
            'earmark' => $earmark,
            'frequency' => $frequency,
            'campaign_id' => $campaignId,
        ];

        $donationService = $this->app->make(DonationService::class);
        $clientSecret = $donationService->createPaymentIntent($amount, $currency, $frequency, $metadata);

        if (is_wp_error($clientSecret)) {
            return new \WP_REST_Response(['success' => false, 'message' => $clientSecret->get_error_message()], 400);
        }

        return new \WP_REST_Response(['success' => true, 'clientSecret' => $clientSecret], 200);
    }

    public function handleGetDonationStatus(\WP_REST_Request $request)
    {
        $paymentIntentId = $request->get_param('id');
        $donationService = $this->app->make(DonationService::class);

        try {
            $status = $donationService->getPaymentIntentStatus($paymentIntentId);
            $message = 'Your donation has been received. A confirmation email has been sent to you.';
            if ($status !== 'succeeded') {
                $message = 'Your donation is still processing. We will send a confirmation email once it is complete.';
            }
            return new \WP_REST_Response(['success' => true, 'status' => $status, 'message' => $message], 200);
        } catch (\Exception $e) {
            return new \WP_REST_Response(['success' => false, 'message' => $e->getMessage()], 500);
        }
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

    public function registerEarmarkRoutes()
    {
        register_rest_route('charitym3/v1', '/earmark-options', [
            'methods' => 'GET',
            'callback' => [$this, 'getEarmarkOptions'],
            'permission_callback' => '__return_true', // Publicly queryable
        ]);
    }

    public function getEarmarkOptions()
    {
        $programs = get_posts([
            'post_type' => 'program',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        if (empty($programs)) {
            return new \WP_REST_Response([], 200);
        }

        $options = array_map(function ($post) {
            return [
                'id' => 'program_' . $post->ID, // Unique value for earmark
                'label' => $post->post_title,
            ];
        }, $programs);

        // Add a default/general option
        array_unshift($options, ['id' => 'general_fund', 'label' => __('General Fund', 'charity-m3')]);

        return new \WP_REST_Response($options, 200);
    }
}
