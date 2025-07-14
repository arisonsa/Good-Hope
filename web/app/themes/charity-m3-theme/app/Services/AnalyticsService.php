<?php

namespace App\Services;

use wpdb;

class AnalyticsService
{
    private wpdb $db;

    public function __construct(wpdb $db)
    {
        $this->db = $db;
    }

    /**
     * Get donation totals grouped by a specified time period.
     *
     * @param string $period 'day', 'week', 'month'
     * @param int $limit Number of periods to go back.
     * @return array
     */
    public function getDonationsOverTime(string $period = 'day', int $limit = 30): array
    {
        $transient_key = "charity_m3_donations_over_time_{$period}_{$limit}";
        $cached_data = get_transient($transient_key);
        if (false !== $cached_data) {
            return $cached_data;
        }

        $donations_table = $this->db->prefix . 'charity_m3_donations';
        $date_format = '%Y-%m-%d'; // Daily
        if ($period === 'month') {
            $date_format = '%Y-%m-01';
        } elseif ($period === 'week') {
            $date_format = '%Y-%u'; // Year and week number
        }

        $sql = $this->db->prepare(
            "SELECT
                DATE_FORMAT(donated_at, %s) as date_key,
                SUM(amount / 100) as total_amount,
                COUNT(id) as total_donations
             FROM {$donations_table}
             WHERE status = 'succeeded' AND donated_at >= %s
             GROUP BY date_key
             ORDER BY date_key ASC",
            $date_format,
            date('Y-m-d H:i:s', strtotime("-{$limit} {$period}"))
        );

        $results = $this->db->get_results($sql, ARRAY_A);
        set_transient($transient_key, $results, 3 * HOUR_IN_SECONDS); // Cache for 3 hours
        return $results;
    }

    /**
     * Get new subscriber counts grouped by a specified time period.
     *
     * @param string $period 'day', 'week', 'month'
     * @param int $limit Number of periods to go back.
     * @return array
     */
    public function getSubscribersOverTime(string $period = 'day', int $limit = 30): array
    {
        $transient_key = "charity_m3_subscribers_over_time_{$period}_{$limit}";
        $cached_data = get_transient($transient_key);
        if (false !== $cached_data) {
            return $cached_data;
        }

        $subscribers_table = $this->db->prefix . 'charity_m3_subscribers';
        $date_format = '%Y-%m-%d';
        if ($period === 'month') {
            $date_format = '%Y-%m-01';
        } elseif ($period === 'week') {
            $date_format = '%Y-%u';
        }

        $sql = $this->db->prepare(
            "SELECT
                DATE_FORMAT(created_at, %s) as date_key,
                COUNT(id) as total_subscribers
             FROM {$subscribers_table}
             WHERE created_at >= %s
             GROUP BY date_key
             ORDER BY date_key ASC",
            $date_format,
            date('Y-m-d H:i:s', strtotime("-{$limit} {$period}"))
        );

        $results = $this->db->get_results($sql, ARRAY_A);
        set_transient($transient_key, $results, 3 * HOUR_IN_SECONDS); // Cache for 3 hours
        return $results;
    }

    public function get_donation_stats(\WP_REST_Request $request)
    {
        $days = $request->get_param('days') ?? 30;
        $stats = $this->query_donation_stats($days);
        $stats['earmark_breakdown'] = $this->query_earmark_breakdown($days);
        return new \WP_REST_Response($stats, 200);
    }

    public function query_donation_stats(int $days = 30): array
    {
        $donations_table = $this->db->prefix . 'charity_m3_donations';
        $sql = $this->db->prepare(
            "SELECT
                SUM(amount / 100) as total_raised,
                COUNT(id) as total_donations,
                AVG(amount / 100) as average_donation,
                COUNT(DISTINCT donor_email) as unique_donors
            FROM {$donations_table}
            WHERE status = 'succeeded' AND donated_at >= %s",
            date('Y-m-d H:i:s', strtotime("-{$days} days"))
        );
        $stats = $this->db->get_row($sql, ARRAY_A);
        return array_map('floatval', $stats);
    }

    public function query_earmark_breakdown(int $days = 30): array
    {
        $donations_table = $this->db->prefix . 'charity_m3_donations';
        $sql = $this->db->prepare(
            "SELECT
                earmark,
                SUM(amount / 100) as total_amount,
                COUNT(id) as donation_count
            FROM {$donations_table}
            WHERE status = 'succeeded' AND donated_at >= %s
            GROUP BY earmark
            ORDER BY total_amount DESC",
            date('Y-m-d H:i:s', strtotime("-{$days} days"))
        );
        $results = $this->db->get_results($sql, ARRAY_A);
        return $results;
    }
}
