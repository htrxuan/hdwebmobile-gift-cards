<?php

namespace htrxuan\hdgc;

if (!defined('ABSPATH')) {
    exit;
}

final class HDGC_Core
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
        $this->includes();
        $this->init_hooks();
    }

    private function __clone()
    {
    }

    private function includes()
    {
        require_once HDGC_PLUGIN_DIR . 'includes/class-hdgc-repository.php';
        require_once HDGC_PLUGIN_DIR . 'includes/class-hdgc-product.php';
        require_once HDGC_PLUGIN_DIR . 'includes/class-hdgc-issuer.php';
        require_once HDGC_PLUGIN_DIR . 'includes/class-hdgc-coupon.php';
        require_once HDGC_PLUGIN_DIR . 'includes/class-hdgc-myaccount.php';
        require_once HDGC_PLUGIN_DIR . 'includes/class-hdgc-admin.php';
    }

    private function init_hooks()
    {
        add_action('admin_notices', array($this, 'render_missing_woocommerce_notice'));
        add_action('admin_init', array(HDGC_Activator::class, 'maybe_upgrade_db'));

        if (!class_exists('WooCommerce')) {
            return;
        }

        add_filter('woocommerce_email_classes', array($this, 'register_email'));

        HDGC_Product::get_instance();
        HDGC_Issuer::get_instance();
        HDGC_Coupon::get_instance();
        HDGC_MyAccount::get_instance();

        // HDGC_Admin registers admin-menu/settings hooks itself, but its get_options() is
        // read from the frontend issuer/coupon classes too, the same reason
        // class-hdcart-core.php requires its Admin class unconditionally rather than only
        // when is_admin().
        HDGC_Admin::get_instance();
    }

    public function register_email($emails)
    {
        // WC_Emails::init() includes its own class-wc-email.php base class immediately
        // before applying this filter, so it's always safe to load our subclass here --
        // loading it any earlier (e.g. at plugins_loaded) would fatal on WC_Email not existing yet.
        require_once HDGC_PLUGIN_DIR . 'includes/class-hdgc-email.php';
        $emails['hdgc_delivery'] = new HDGC_Email();
        return $emails;
    }

    public function render_missing_woocommerce_notice()
    {
        $screen = get_current_screen();
        if (!$screen || 'plugins' !== $screen->id) {
            return;
        }

        if (!get_transient('hdgc_wc_missing_notice')) {
            return;
        }
        delete_transient('hdgc_wc_missing_notice');
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php esc_html_e('HDWebmobile Gift Cards requires WooCommerce to be installed and active. The plugin has been deactivated.', 'hdwebmobile-gift-cards'); ?>
            </p>
        </div>
        <?php
    }
}
