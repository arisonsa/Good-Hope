<?php

namespace App\Admin;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

use App\Services\DonationService;

class DonationsListTable extends \WP_List_Table
{
    private DonationService $donationService;

    public function __construct(DonationService $donationService)
    {
        $this->donationService = $donationService;
        parent::__construct([
            'singular' => __('Donation', 'charity-m3'),
            'plural'   => __('Donations', 'charity-m3'),
            'ajax'     => false
        ]);
    }

    public function prepare_items()
    {
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        // No bulk actions for donations for now (can be added later: refund, resend receipt, etc.)
        // $this->process_bulk_action();

        $per_page = $this->get_items_per_page('donations_per_page', 20);
        $current_page = $this->get_pagenum();

        $search_term = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';
        $status_filter = isset($_REQUEST['status']) ? sanitize_text_field(wp_unslash($_REQUEST['status'])) : '';

        $orderby = isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'donated_at';
        $order = isset($_REQUEST['order']) ? strtoupper(sanitize_key($_REQUEST['order'])) : 'DESC';
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $data = $this->donationService->getDonations([
            'per_page' => $per_page,
            'page'     => $current_page,
            'search'   => $search_term,
            'status'   => $status_filter,
            'orderby'  => $orderby,
            'order'    => $order,
        ]);

        $this->items = $data['items'];
        $this->set_pagination_args([
            'total_items' => $data['total_items'],
            'per_page'    => $per_page,
        ]);
    }

    public function get_columns()
    {
        return [
            // 'cb'       => '<input type="checkbox" />',
            'donor'    => __('Donor', 'charity-m3'),
            'amount'   => __('Amount', 'charity-m3'),
            'frequency' => __('Frequency', 'charity-m3'),
            'status'   => __('Status', 'charity-m3'),
            'gateway_transaction_id' => __('Transaction ID', 'charity-m3'),
            'donated_at' => __('Date', 'charity-m3'),
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'donor'      => ['donor_name', false],
            'amount'     => ['amount', false],
            'status'     => ['status', false],
            'donated_at' => ['donated_at', true], // Default sort
        ];
    }

    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'frequency':
            case 'status':
                return esc_html(ucfirst($item->$column_name));
            case 'gateway_transaction_id':
                // Could link to Stripe dashboard if desired
                return esc_html($item->$column_name);
            case 'donated_at':
                return esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $item->$column_name));
            default:
                return '---';
        }
    }

    protected function column_donor($item)
    {
        $donor_info = '<strong>' . esc_html($item->donor_name ?: __('Anonymous', 'charity-m3')) . '</strong>';
        $donor_info .= '<br><a href="mailto:' . esc_attr($item->donor_email) . '">' . esc_html($item->donor_email) . '</a>';

        // Add row actions if needed (e.g., View Details, Refund)
        // $actions = [ 'view' => '<a href="#">View Details</a>' ];
        // return $donor_info . $this->row_actions($actions);
        return $donor_info;
    }

    protected function column_amount($item)
    {
        // Amount is stored in cents, so format it correctly
        $amount_formatted = number_format($item->amount / 100, 2);
        return esc_html($amount_formatted . ' ' . strtoupper($item->currency));
    }

    public function no_items()
    {
        _e('No donations found.', 'charity-m3');
    }
}
