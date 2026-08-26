<?php

namespace htrxuan\hdgc;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Redeems gift cards through WooCommerce's own native "Apply coupon" field (classic and
 * Block Checkout alike) by synthesizing a virtual WC_Coupon on the fly -- no custom
 * redemption UI. See WC_Coupon::__construct() / read_manual_coupon() in WooCommerce core.
 */
class HDGC_Coupon
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
        add_filter('woocommerce_get_shop_coupon_data', array($this, 'get_shop_coupon_data'), 10, 3);
        add_filter('woocommerce_coupon_is_valid', array($this, 'validate_coupon'), 10, 2);
        add_filter('woocommerce_coupon_error', array($this, 'custom_error_message'), 10, 3);
        add_action('woocommerce_order_status_completed', array($this, 'apply_redemptions'));
    }

    public function get_shop_coupon_data($data, $code, $coupon)
    {
        $gift_card = HDGC_Repository::find_by_code($this->normalize_code($code));

        if (!$gift_card) {
            return $data;
        }

        // Always return data for a code that exists in our table, even if it's expired,
        // disabled, or depleted -- this makes the coupon "exist" (WC_Coupon::get_virtual()
        // becomes true), so woocommerce_coupon_is_valid below gets a chance to reject it
        // with a specific reason via custom_error_message(), instead of a code we issued
        // falling through to WooCommerce's generic "does not exist" error.
        return array(
            'amount'        => $gift_card->balance,
            'discount_type' => 'fixed_cart',
            'individual_use' => false,
            'free_shipping'  => false,
            'exclude_sale_items' => false,
        );
    }

    public function validate_coupon($valid, $coupon)
    {
        $gift_card = HDGC_Repository::find_by_code($this->normalize_code($coupon->get_code()));

        if (!$gift_card) {
            return $valid;
        }

        if (!$this->is_effectively_active($gift_card)) {
            return false;
        }

        return $valid;
    }

    public function custom_error_message($message, $error_code, $coupon)
    {
        if (!is_a($coupon, 'WC_Coupon') || \WC_Coupon::E_WC_COUPON_INVALID_FILTERED !== $error_code) {
            return $message;
        }

        $gift_card = HDGC_Repository::find_by_code($this->normalize_code($coupon->get_code()));

        if (!$gift_card) {
            return $message;
        }

        if ('active' !== $gift_card->status) {
            return __('This gift card has been disabled.', 'hdwebmobile-gift-cards');
        }

        if ($gift_card->expires_at && strtotime($gift_card->expires_at) < time()) {
            return __('This gift card has expired.', 'hdwebmobile-gift-cards');
        }

        if ((float) $gift_card->balance <= 0) {
            return __('This gift card has already been fully redeemed.', 'hdwebmobile-gift-cards');
        }

        return $message;
    }

    /**
     * Decrements each redeemed gift card's balance by the amount WooCommerce actually
     * applied to this order (WC_Order_Item_Coupon::get_discount()) -- not the card's full
     * value -- so a partial redemption correctly leaves the remainder. Guarded by an
     * order-meta flag to prevent double-decrementing if this hook fires again.
     */
    public function apply_redemptions($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order || $order->get_meta('_hdgc_balance_applied')) {
            return;
        }

        $applied_any = false;

        foreach ($order->get_items('coupon') as $coupon_item) {
            $gift_card = HDGC_Repository::find_by_code($this->normalize_code($coupon_item->get_code()));
            if (!$gift_card) {
                continue;
            }

            $discount = (float) $coupon_item->get_discount();
            if ($discount > 0) {
                HDGC_Repository::adjust_balance($gift_card->id, -$discount);
                $applied_any = true;
            }
        }

        if ($applied_any) {
            $order->update_meta_data('_hdgc_balance_applied', 'yes');
            $order->save();
        }
    }

    private function is_effectively_active($gift_card)
    {
        if ('active' !== $gift_card->status) {
            return false;
        }

        if ((float) $gift_card->balance <= 0) {
            return false;
        }

        if ($gift_card->expires_at && strtotime($gift_card->expires_at) < time()) {
            return false;
        }

        return true;
    }

    private function normalize_code($code)
    {
        return strtoupper(trim((string) $code));
    }
}
