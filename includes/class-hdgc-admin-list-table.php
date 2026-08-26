<?php

namespace htrxuan\hdgc;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class HDGC_Admin_List_Table extends \WP_List_Table
{

    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'gift_card',
            'plural'   => 'gift_cards',
            'ajax'     => false,
        ));
    }

    public function get_columns()
    {
        return array(
            'code'            => __('Code', 'hdwebmobile-gift-cards'),
            'balance'         => __('Balance', 'hdwebmobile-gift-cards'),
            'status'          => __('Status', 'hdwebmobile-gift-cards'),
            'purchaser_email' => __('Purchaser', 'hdwebmobile-gift-cards'),
            'created_at'      => __('Issued', 'hdwebmobile-gift-cards'),
            'expires_at'      => __('Expires', 'hdwebmobile-gift-cards'),
        );
    }

    protected function get_sortable_columns()
    {
        return array(
            'code'            => array('code', false),
            'balance'         => array('balance', false),
            'status'          => array('status', false),
            'purchaser_email' => array('purchaser_email', false),
            'created_at'      => array('created_at', true),
            'expires_at'      => array('expires_at', false),
        );
    }

    protected function extra_tablenav($which)
    {
        if ('top' !== $which) {
            return;
        }

        // Read-only filter param, same as core WP_List_Table screens -- no state
        // change occurs from reading it, so nonce verification doesn't apply here.
        $current_status = isset($_GET['status']) ? sanitize_text_field(wp_unslash($_GET['status'])) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $statuses        = array(
            'all'      => __('All statuses', 'hdwebmobile-gift-cards'),
            'active'   => __('Active', 'hdwebmobile-gift-cards'),
            'disabled' => __('Disabled', 'hdwebmobile-gift-cards'),
        );
        ?>
        <div class="alignleft actions">
            <select name="status">
                <?php foreach ($statuses as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($current_status, $value); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <?php submit_button(__('Filter', 'hdwebmobile-gift-cards'), '', 'filter_action', false); ?>
        </div>
        <?php
    }

    public function column_code($item)
    {
        return '<code>' . esc_html($item->code) . '</code>';
    }

    public function column_balance($item)
    {
        return wp_kses_post(wc_price($item->balance, array('currency' => $item->currency)))
            . ' <span class="description">' . esc_html__('of', 'hdwebmobile-gift-cards') . ' ' . wp_kses_post(wc_price($item->initial_amount, array('currency' => $item->currency))) . '</span>';
    }

    public function column_status($item)
    {
        $labels = array(
            'active'   => __('Active', 'hdwebmobile-gift-cards'),
            'disabled' => __('Disabled', 'hdwebmobile-gift-cards'),
        );

        $label = isset($labels[$item->status]) ? $labels[$item->status] : $item->status;

        return sprintf('<span class="hdgc-status-%s">%s</span>', esc_attr($item->status), esc_html($label));
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'purchaser_email':
                return $item->purchaser_email ? esc_html($item->purchaser_email) : '&mdash;';
            case 'created_at':
                return esc_html($item->created_at);
            case 'expires_at':
                return $item->expires_at ? esc_html($item->expires_at) : esc_html__('Never', 'hdwebmobile-gift-cards');
            default:
                return '';
        }
    }

    public function prepare_items()
    {
        $per_page = 20;
        $paged    = $this->get_pagenum();

        // Read-only filter/search/sort params for this list table -- same pattern as core
        // WP_List_Table screens; nothing here changes state, so no nonce is needed.
        $status  = isset($_GET['status']) ? sanitize_text_field(wp_unslash($_GET['status'])) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $search  = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $orderby = isset($_GET['orderby']) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : 'created_at'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $order   = isset($_GET['order']) ? sanitize_text_field(wp_unslash($_GET['order'])) : 'desc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $result = HDGC_Repository::get_for_list_table(array(
            'status'   => $status,
            's'        => $search,
            'per_page' => $per_page,
            'paged'    => $paged,
            'orderby'  => $orderby,
            'order'    => $order,
        ));

        $this->items = $result['items'];

        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());

        $this->set_pagination_args(array(
            'total_items' => $result['total'],
            'per_page'    => $per_page,
            'total_pages' => ceil($result['total'] / $per_page),
        ));
    }
}
