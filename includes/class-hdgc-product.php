<?php

namespace htrxuan\hdgc;

if (!defined('ABSPATH')) {
    exit;
}

class HDGC_Product
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
        add_action('woocommerce_product_options_general_product_data', array($this, 'render_checkbox'));

        // Priority 20: WC_Meta_Box_Product_Data::save() (WooCommerce core, also hooked here
        // at the default priority) sets _virtual straight from the "Virtual" checkbox in
        // $_POST. Running after it, rather than at the same default priority, is what lets
        // this override win instead of being silently overwritten back to "no".
        add_action('woocommerce_process_product_meta', array($this, 'save_checkbox'), 20);
    }

    public function render_checkbox()
    {
        global $post;

        echo '<div class="options_group">';

        woocommerce_wp_checkbox(array(
            'id'          => '_hdgc_is_gift_card',
            'label'       => __('This is a gift card', 'hdwebmobile-gift-cards'),
            'description' => __('The product will be forced virtual, and completing an order for it issues a redeemable gift-card code equal to its price.', 'hdwebmobile-gift-cards'),
            'value'       => get_post_meta($post->ID, '_hdgc_is_gift_card', true),
        ));

        echo '</div>';
    }

    public function save_checkbox($post_id)
    {
        $is_gift_card = isset($_POST['_hdgc_is_gift_card']) ? 'yes' : 'no'; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce core's own product-save screen already verifies the `save-post_{$post_id}` nonce before firing this hook.

        update_post_meta($post_id, '_hdgc_is_gift_card', $is_gift_card);

        if ('yes' === $is_gift_card) {
            $product = wc_get_product($post_id);
            if ($product && !$product->is_virtual()) {
                $product->set_virtual(true);
                $product->save();
            }
        }
    }

    public static function is_gift_card_product($product)
    {
        if (!$product instanceof \WC_Product) {
            return false;
        }

        return 'yes' === $product->get_meta('_hdgc_is_gift_card');
    }
}
