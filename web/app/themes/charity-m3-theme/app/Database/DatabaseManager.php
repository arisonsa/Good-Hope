<?php

namespace App\Database;

class DatabaseManager
{
    /**
     * Create or update custom database tables.
     * This should be called on theme/plugin activation.
     */
    public static function runMigrations()
    {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        self::createSubscribersTable($wpdb);
        self::createNewsletterTrackingTable($wpdb);
        self::createDonationsTable($wpdb); // Add this call
        // CPT registration will handle the 'newsletter_campaigns' effective table (wp_posts, wp_postmeta)
    }

    /**
     * Create the donations table.
     */
    private static function createDonationsTable(\wpdb $wpdb)
    {
        $table_name = $wpdb->prefix . 'charity_m3_donations';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            donor_id BIGINT UNSIGNED NULL,
            donor_name VARCHAR(255) NULL,
            donor_email VARCHAR(255) NOT NULL,
            amount INT UNSIGNED NOT NULL,
            currency VARCHAR(10) NOT NULL,
            frequency VARCHAR(50) NOT NULL,
            status VARCHAR(50) NOT NULL,
            gateway VARCHAR(50) NOT NULL,
            gateway_transaction_id VARCHAR(255) NOT NULL,
            stripe_customer_id VARCHAR(255) NULL,
            stripe_subscription_id VARCHAR(255) NULL,
            campaign_id BIGINT UNSIGNED NULL,
            donated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY gateway_transaction_id (gateway_transaction_id),
            KEY donor_id (donor_id),
            KEY donor_email (donor_email),
            KEY status (status),
            KEY campaign_id (campaign_id),
            KEY stripe_customer_id (stripe_customer_id),
            KEY stripe_subscription_id (stripe_subscription_id)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * Create the subscribers table.
     */
    private static function createSubscribersTable(\wpdb $wpdb)
    {
        $table_name = $wpdb->prefix . 'charity_m3_subscribers';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL,
            name VARCHAR(255) NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            source VARCHAR(255) NULL,
            subscribed_at DATETIME NULL,
            unsubscribed_at DATETIME NULL,
            last_changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email),
            KEY status (status)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * Create the newsletter tracking table.
     */
    private static function createNewsletterTrackingTable(\wpdb $wpdb)
    {
        $table_name = $wpdb->prefix . 'charity_m3_newsletter_tracking';
        $charset_collate = $wpdb->get_charset_collate();

        // Assuming newsletter_campaign is a CPT, campaign_id will reference wp_posts.ID
        // Assuming subscribers table is named {$wpdb->prefix}charity_m3_subscribers
        $subscribers_table_name = $wpdb->prefix . 'charity_m3_subscribers';

        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            campaign_id BIGINT UNSIGNED NOT NULL,
            subscriber_id BIGINT UNSIGNED NOT NULL,
            action_type VARCHAR(50) NOT NULL,
            target_url TEXT NULL,
            ip_address VARCHAR(100) NULL,
            user_agent TEXT NULL,
            tracked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY campaign_id (campaign_id),
            KEY subscriber_id (subscriber_id),
            KEY action_type (action_type)
            -- Consider adding foreign key constraints if your MySQL version and setup support them well with dbDelta.
            -- dbDelta has limitations with foreign keys. Often managed at application level or via direct SQL for setup.
            -- Example (conceptual, dbDelta might ignore or mangle this):
            -- CONSTRAINT fk_campaign_id FOREIGN KEY (campaign_id) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE,
            -- CONSTRAINT fk_subscriber_id FOREIGN KEY (subscriber_id) REFERENCES {$subscribers_table_name}(id) ON DELETE CASCADE
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * Method to be called on theme/plugin deactivation to remove tables (optional).
     */
    public static function dropTables()
    {
        // global $wpdb;
        // $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . 'charity_m3_newsletter_tracking');
        // $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . 'charity_m3_subscribers');
        // Also consider removing CPT data if newsletter_campaign is a CPT and data should be wiped.
    }
}
