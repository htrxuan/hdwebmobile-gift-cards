<?php

namespace htrxuan\hdgc;

if (!defined('ABSPATH')) {
    exit;
}

class HDGC_Admin
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        require_once HDGC_PLUGIN_DIR . 'includes/class-hdgc-hub.php';
        add_filter('hdwebmobile_hub_tabs', array($this, 'register_hub_tabs'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('admin_post_hdgc_issue_manual', array($this, 'handle_issue_manual'));
    }

    public function register_hub_tabs($tabs)
    {
        $tabs['gift-cards'] = array(
            'label'  => __('Gift Cards', 'hdwebmobile-gift-cards'),
            'order'  => 60,
            'render' => array($this, 'render_gift_cards_page'),
        );
        $tabs['gift-cards-settings'] = array(
            'label'  => __('Gift Cards Settings', 'hdwebmobile-gift-cards'),
            'order'  => 70,
            'render' => array($this, 'render_settings_page'),
        );
        return $tabs;
    }

    public function render_gift_cards_page()
    {
        require_once HDGC_PLUGIN_DIR . 'includes/class-hdgc-admin-list-table.php';

        $table = new HDGC_Admin_List_Table();
        $table->prepare_items();
        ?>
        <p><?php esc_html_e('Sell digital gift cards redeemed through WooCommerce\'s own native coupon field -- no custom checkout UI, no file uploads. Check "This is a gift card" on any simple product to sell one; codes issued so far are listed below.', 'hdwebmobile-gift-cards'); ?></p>
        <?php $this->render_issue_manual_form(); ?>

        <form method="get">
            <input type="hidden" name="page" value="hdwebmobile" />
            <input type="hidden" name="tab" value="gift-cards" />
            <?php
            $table->search_box(__('Search code or email', 'hdwebmobile-gift-cards'), 'hdgc-gift-card-search');
            $table->display();
            ?>
        </form>
        <?php
    }

    private function render_issue_manual_form()
    {
        ?>
        <details style="margin:1em 0;">
            <summary style="cursor:pointer;font-weight:600;"><?php esc_html_e('Issue a gift card manually', 'hdwebmobile-gift-cards'); ?></summary>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:1em;padding:1em;background:#fff;border:1px solid #dcdcde;max-width:500px;">
                <input type="hidden" name="action" value="hdgc_issue_manual" />
                <?php wp_nonce_field('hdgc_issue_manual'); ?>
                <p>
                    <label for="hdgc-amount"><?php esc_html_e('Amount', 'hdwebmobile-gift-cards'); ?></label><br />
                    <input type="number" step="0.01" min="0.01" required id="hdgc-amount" name="amount" class="regular-text" />
                </p>
                <p>
                    <label for="hdgc-email"><?php esc_html_e('Recipient email', 'hdwebmobile-gift-cards'); ?></label><br />
                    <input type="email" required id="hdgc-email" name="email" class="regular-text" />
                </p>
                <?php submit_button(__('Issue Gift Card', 'hdwebmobile-gift-cards'), 'primary', 'submit', false); ?>
            </form>
        </details>
        <?php
    }

    public function handle_issue_manual()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to do this.', 'hdwebmobile-gift-cards'));
        }

        check_admin_referer('hdgc_issue_manual');

        $amount = isset($_POST['amount']) ? wc_format_decimal(sanitize_text_field(wp_unslash($_POST['amount']))) : 0;
        $email  = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';

        if ($amount > 0 && is_email($email)) {
            require_once HDGC_PLUGIN_DIR . 'includes/class-hdgc-issuer.php';
            HDGC_Issuer::get_instance()->issue_manual($amount, $email);
        }

        wp_safe_redirect(admin_url('admin.php?page=hdwebmobile&tab=gift-cards&issued=1'));
        exit;
    }

    public function render_settings_page()
    {
        ?>
        <p><?php esc_html_e('Optionally set a default expiry period for newly-issued gift cards.', 'hdwebmobile-gift-cards'); ?></p>
        <form method="post" action="options.php">
            <?php
            settings_fields('hdgc_option_group');
            do_settings_sections('hdgc-settings');
            submit_button();
            ?>
        </form>
        <?php
    }

    public function page_init()
    {
        register_setting(
            'hdgc_option_group',
            'hdgc_options',
            array(
                'type'              => 'array',
                'sanitize_callback' => array($this, 'sanitize'),
                'default'           => array(),
            )
        );

        add_settings_section(
            'hdgc_section_general',
            __('General', 'hdwebmobile-gift-cards'),
            '__return_false',
            'hdgc-settings'
        );

        add_settings_field('expiry_days', __('Expire gift cards after (days)', 'hdwebmobile-gift-cards'), array($this, 'expiry_days_callback'), 'hdgc-settings', 'hdgc_section_general');
    }

    public static function get_options()
    {
        $defaults = array(
            'expiry_days' => 0,
        );

        return wp_parse_args(get_option('hdgc_options', array()), $defaults);
    }

    public function sanitize($input)
    {
        $new_input = array();

        $new_input['expiry_days'] = isset($input['expiry_days']) ? max(0, absint($input['expiry_days'])) : 0;

        return $new_input;
    }

    public function expiry_days_callback()
    {
        $options = self::get_options();
        printf(
            '<input type="number" min="0" name="hdgc_options[expiry_days]" value="%s" class="small-text" /> <span class="description">%s</span>',
            esc_attr($options['expiry_days']),
            esc_html__('0 = never expires', 'hdwebmobile-gift-cards')
        );
    }
}
