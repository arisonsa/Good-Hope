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
            'earmark' => __('Earmarked For', 'charity-m3'),
            'on_behalf_of' => __('On Behalf Of', 'charity-m3'),
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
            case 'status':
                return esc_html(ucfirst($item->status));
            case 'on_behalf_of':
                return esc_html($item->on_behalf_of ?: '—');
            case 'gateway_transaction_id':
                return esc_html($item->gateway_transaction_id);
            case 'donated_at':
                return esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $item->donated_at));
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
        return '<strong>' . esc_html($amount_formatted . ' ' . strtoupper($item->currency)) . '</strong>';
    }

    protected function column_frequency($item)
    {
        $output = esc_html(ucfirst($item->frequency));
        if ($item->stripe_subscription_id) {
            // Could link to Stripe subscription here
            $output .= '<br><small style="color: #777;">' . sprintf(__('Sub ID: %s', 'charity-m3'), esc_html($item->stripe_subscription_id)) . '</small>';
        }
        return $output;
    }

    protected function column_earmark($item)
    {
        if (empty($item->earmark) || $item->earmark === 'general_fund') {
            return __('General Fund', 'charity-m3');
        }

        if (str_starts_with($item->earmark, 'program_')) {
            $program_id = (int) str_replace('program_', '', $item->earmark);
            $program_title = get_the_title($program_id);
            if ($program_title) {
                return '<a href="' . get_edit_post_link($program_id) . '">' . esc_html($program_title) . '</a>';
            }
        }

        return esc_html($item->earmark); // Fallback
    }

    public function no_items()
    {
        _e('No donations found.', 'charity-m3');
    }
}
