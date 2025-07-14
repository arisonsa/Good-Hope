<?php

namespace App\Providers;

use Roots\Acorn\ServiceProvider;
use App\Services\AnalyticsService;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AnalyticsService::class, function () {
            global $wpdb;
            return new AnalyticsService($wpdb);
        });
    }

    public function boot()
    {
        add_action('rest_api_init', [$this, 'registerAnalyticsRoutes']);
    }

    public function registerAnalyticsRoutes()
    {
        // Endpoint for donations over time
        register_rest_route('charitym3/v1', '/stats/donations-over-time', [
            'methods' => 'GET',
            'callback' => [$this, 'getDonationsOverTime'],
            'permission_callback' => fn() => current_user_can('manage_options'),
        ]);

        // Endpoint for subscribers over time
        register_rest_route('charitym3/v1', '/stats/subscribers-over-time', [
            'methods' => 'GET',
            'callback' => [$this, 'getSubscribersOverTime'],
            'permission_callback' => fn() => current_user_can('manage_options'),
        ]);

        // Endpoint for general donation stats
        register_rest_route('charitym3/v1', '/stats/donations', [
            'methods' => 'GET',
            'callback' => function (\WP_REST_Request $request) {
                $analyticsService = $this->app->make(AnalyticsService::class);
                return $analyticsService->get_donation_stats($request);
            },
            'permission_callback' => fn() => current_user_can('manage_options'),
        ]);
    }

    public function getDonationsOverTime(\WP_REST_Request $request)
    {
        /** @var AnalyticsService $analyticsService */
        $analyticsService = $this->app->make(AnalyticsService::class);
        $data = $analyticsService->getDonationsOverTime(
            $request->get_param('period') ?: 'day',
            $request->get_param('limit') ?: 30
        );
        return new \WP_REST_Response($data, 200);
    }

    public function getSubscribersOverTime(\WP_REST_Request $request)
    {
        /** @var AnalyticsService $analyticsService */
        $analyticsService = $this->app->make(AnalyticsService::class);
        $data = $analyticsService->getSubscribersOverTime(
            $request->get_param('period') ?: 'day',
            $request->get_param('limit') ?: 30
        );
        return new \WP_REST_Response($data, 200);
    }
}
