<?php

namespace App\Providers;

use Roots\Acorn\ServiceProvider;
use App\Services\DonationService; // To fetch data
use App\Services\SubscriberService; // To fetch data

class GraphQLServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Ensure WPGraphQL is active
        if (!function_exists('register_graphql_object_type')) {
            return;
        }

        add_action('graphql_register_types', [$this, 'registerTypes']);
    }

    public function registerTypes()
    {
        $this->registerDonationType();
        $this->registerSubscriberType();
        $this->registerRootQueryFields();
        // Note: CPTs ('program', 'impact_story') are often auto-exposed by WPGraphQL's CPT extension
        // if 'show_in_graphql' is set to true during registration. We'd add custom fields here if needed.
    }

    private function registerDonationType()
    {
        register_graphql_object_type('Donation', [
            'description' => __('A donation record', 'charity-m3'),
            'fields' => [
                'id' => ['type' => 'ID', 'description' => __('Donation ID', 'charity-m3')],
                'donorName' => ['type' => 'String', 'description' => __('Donor\'s name', 'charity-m3')],
                'donorEmail' => ['type' => 'String', 'description' => __('Donor\'s email (requires auth)', 'charity-m3')],
                'amount' => ['type' => 'Int', 'description' => __('Donation amount in cents', 'charity-m3')],
                'currency' => ['type' => 'String', 'description' => __('Currency code', 'charity-m3')],
                'frequency' => ['type' => 'String', 'description' => __('Donation frequency', 'charity-m3')],
                'status' => ['type' => 'String', 'description' => __('Payment status', 'charity-m3')],
                'donatedAt' => ['type' => 'String', 'description' => __('Date of donation', 'charity-m3')],
            ],
            // Resolve functions to control data access
            'resolveField' => function ($source, $args, $context, $info) {
                // Example: Protect sensitive fields
                if ($info->fieldName === 'donorEmail' && !current_user_can('manage_options')) {
                    return 'hidden@example.com';
                }
                $fieldName = lcfirst($info->fieldName);
                return $source->$fieldName ?? null;
            }
        ]);
    }

    private function registerSubscriberType()
    {
        register_graphql_object_type('Subscriber', [
            'description' => __('A newsletter subscriber', 'charity-m3'),
            'fields' => [
                'id' => ['type' => 'ID'],
                'email' => ['type' => 'String'],
                'name' => ['type' => 'String'],
                'status' => ['type' => 'String'],
                'subscribedAt' => ['type' => 'String'],
            ],
             'resolveField' => function ($source, $args, $context, $info) {
                // All subscriber fields should be protected
                if (!current_user_can('manage_options')) {
                    return null;
                }
                $fieldName = lcfirst($info->fieldName);
                return $source->$fieldName ?? null;
            }
        ]);
    }

    private function registerRootQueryFields()
    {
        // Add a 'donations' connection to the RootQuery
        register_graphql_connection([
            'fromType' => 'RootQuery',
            'toType' => 'Donation',
            'fromFieldName' => 'donations',
            'connectionArgs' => [
                'status' => ['type' => 'String'],
                'search' => ['type' => 'String'],
            ],
            'resolve' => function ($source, $args, $context, $info) {
                if (!current_user_can('manage_options')) {
                    return null; // Or throw a GraphQL error
                }

                $donationService = $this->app->make(DonationService::class);

                $resolver_args = [
                    'per_page' => $args['first'] ?? 10,
                    'page' => 1, // Basic pagination, WPGraphQL connection handles cursor logic
                    'status' => $args['where']['status'] ?? '',
                    'search' => $args['where']['search'] ?? '',
                ];

                $donations = $donationService->getDonations($resolver_args);

                $connection = new \WPGraphQL\Data\Connection\MySql\Connection(new \WPGraphQL\Data\Connection\MySql\MySqlResolver(
                    $source, $args, $context, $info, 'Donation'
                ));
                $connection->set_query_data($donations['items']);
                $connection->set_total($donations['total_items']);

                return $connection;
            },
        ]);

        // Add a 'subscribers' connection to the RootQuery
        register_graphql_connection([
            'fromType' => 'RootQuery',
            'toType' => 'Subscriber',
            'fromFieldName' => 'subscribers',
            'connectionArgs' => [
                'status' => ['type' => 'String'],
            ],
            'resolve' => function ($source, $args, $context, $info) {
                if (!current_user_can('manage_options')) {
                    return null;
                }

                $subscriberService = $this->app->make(SubscriberService::class);
                $resolver_args = [
                    'per_page' => $args['first'] ?? 10,
                    'status' => $args['where']['status'] ?? '',
                ];
                $subscribers = $subscriberService->getSubscribers($resolver_args);

                $connection = new \WPGraphQL\Data\Connection\MySql\Connection(new \WPGraphQL\Data\Connection\MySql\MySqlResolver(
                    $source, $args, $context, $info, 'Subscriber'
                ));
                $connection->set_query_data($subscribers['items']);
                $connection->set_total($subscribers['total_items']);

                return $connection;
            },
        ]);
    }
}
