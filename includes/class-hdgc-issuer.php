<?php

namespace htrxuan\hdgc;

if (!defined('ABSPATH')) {
    exit;
}

class HDGC_Issuer
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
        add_action('woocommerce_order_status_completed', array($this, 'issue_for_order'));
    }

    /**
     * Issues one gift card per unit purchased of any gift-card product on the order.
     * Guarded by an order-meta flag so a status transitioning back through "completed"
     * again (e.g. after a manual status edit) never issues duplicate codes.
     */
    public function issue_for_order($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order || $order->get_meta('_hdgc_issued')) {
            return;
        }

        $issued_any = false;

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!HDGC_Product::is_gift_card_product($product)) {
                continue;
            }

            $qty        = max(1, (int) $item->get_quantity());
            $unit_price = $qty > 0 ? ((float) $item->get_total() / $qty) : (float) $product->get_price();
            $expires_at = $this->calculate_expiry();

            for ($i = 0; $i < $qty; $i++) {
                $gift_card = HDGC_Repository::create(
                    $unit_price,
                    $order->get_currency(),
                    $order_id,
                    $order->get_billing_email(),
                    $expires_at
                );

                if ($gift_card) {
                    $this->send_delivery_email($gift_card);
                    $issued_any = true;
                }
            }
        }

        if ($issued_any) {
            $order->update_meta_data('_hdgc_issued', 'yes');
            $order->save();
        }
    }

    public function issue_manual($amount, $email, $expires_at = null)
    {
        $gift_card = HDGC_Repository::create(
            $amount,
            get_woocommerce_currency(),
            null,
            $email,
            $expires_at
        );

        if ($gift_card) {
            $this->send_delivery_email($gift_card);
        }

        return $gift_card;
    }

    private function calculate_expiry()
    {
        $options = HDGC_Admin::get_options();
        $days    = (int) $options['expiry_days'];

        if ($days <= 0) {
            return null;
        }

        $expires = current_datetime()->modify("+{$days} days");

        return $expires->format('Y-m-d H:i:s');
    }

    private function send_delivery_email($gift_card)
    {
        $emails = WC()->mailer()->get_emails();
        $email  = isset($emails['hdgc_delivery']) ? $emails['hdgc_delivery'] : new HDGC_Email();
        $email->trigger($gift_card);
    }
}
