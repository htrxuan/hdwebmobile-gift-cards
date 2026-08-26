<?php

namespace htrxuan\hdgc;

if (!defined('ABSPATH')) {
    exit;
}

class HDGC_MyAccount
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
        add_action('woocommerce_account_dashboard', array($this, 'render_gift_cards_section'));
    }

    public function render_gift_cards_section()
    {
        $user = wp_get_current_user();
        if (!$user || !$user->exists() || empty($user->user_email)) {
            return;
        }

        $gift_cards = HDGC_Repository::find_for_account($user->user_email);

        if (empty($gift_cards)) {
            return;
        }
        ?>
        <h2><?php esc_html_e('My Gift Cards', 'hdwebmobile-gift-cards'); ?></h2>
        <table class="woocommerce-table shop_table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Code', 'hdwebmobile-gift-cards'); ?></th>
                    <th><?php esc_html_e('Balance', 'hdwebmobile-gift-cards'); ?></th>
                    <th><?php esc_html_e('Expires', 'hdwebmobile-gift-cards'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gift_cards as $gift_card) : ?>
                    <tr>
                        <td><code><?php echo esc_html($gift_card->code); ?></code></td>
                        <td><?php echo wp_kses_post(wc_price($gift_card->balance, array('currency' => $gift_card->currency))); ?></td>
                        <td><?php echo $gift_card->expires_at ? esc_html(date_i18n(get_option('date_format'), strtotime($gift_card->expires_at))) : esc_html__('Never', 'hdwebmobile-gift-cards'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="description"><?php esc_html_e('Enter a code above in the "Have a coupon?" field at checkout to redeem it.', 'hdwebmobile-gift-cards'); ?></p>
        <?php
    }
}
