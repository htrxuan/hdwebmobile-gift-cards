<?php

/**
 * Plugin Name: HDWebmobile Gift Cards
 * Plugin URI: https://hdwebmobile.com/plugins/hdwebmobile-gift-cards/
 * Description: Sell digital gift cards redeemed through WooCommerce's own native coupon field -- no custom checkout UI, no file uploads.
 * Version: 1.0.0
 * Author: htrxuan - Han Tran
 * Author URI: https://hdwebmobile.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hdwebmobile-gift-cards
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.9
 */

namespace htrxuan\hdgc;

if (!defined('ABSPATH')) {
    exit;
}

// Define Constants
define('HDGC_VERSION', '1.0.0');
define('HDGC_DB_VERSION', '1.0.0');
define('HDGC_PLUGIN_FILE', __FILE__);
define('HDGC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HDGC_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once HDGC_PLUGIN_DIR . 'includes/class-hdgc-activator.php';

register_activation_hook(HDGC_PLUGIN_FILE, array(HDGC_Activator::class, 'activate'));
add_action('before_woocommerce_init', array(HDGC_Activator::class, 'declare_hpos_compatibility'));

add_action('plugins_loaded', function () {
    require_once HDGC_PLUGIN_DIR . 'includes/class-hdgc-core.php';
    HDGC_Core::get_instance();
});

add_filter('plugin_action_links_' . plugin_basename(HDGC_PLUGIN_FILE), function ($links) {
    $donate_link = '<a href="https://paypal.me/htrxuan/20" target="_blank" style="color:#d54e21;font-weight:bold;">' . __('Donate', 'hdwebmobile-gift-cards') . '</a>';
    array_unshift($links, $donate_link);
    return $links;
});
