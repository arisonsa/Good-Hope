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
}
