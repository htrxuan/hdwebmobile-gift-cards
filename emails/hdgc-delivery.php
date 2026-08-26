<?php

/**
 * Gift card delivery email (HTML).
 *
 * @var object $gift_card
 * @var string $email_heading
 * @var string $additional_content
 * @var string $gift_card_code
 * @var string $gift_card_balance
 * @var string $checkout_url
 * @var bool $sent_to_admin
 * @var bool $plain_text
 * @var \htrxuan\hdgc\HDGC_Email $email
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_email_header', $email_heading, $email); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook, not ours to prefix
?>

<p><?php esc_html_e('You have a gift card to spend in our store:', 'hdwebmobile-gift-cards'); ?></p>

<p style="text-align: center; margin: 24px 0;">
    <span style="display:inline-block;font-size:1.4em;font-weight:bold;letter-spacing:0.05em;padding:0.5em 1em;border:2px dashed #999;border-radius:6px;">
        <?php echo esc_html($gift_card_code); ?>
    </span>
</p>

<p>
    <strong><?php esc_html_e('Balance:', 'hdwebmobile-gift-cards'); ?></strong>
    <?php echo wp_kses_post($gift_card_balance); ?>
</p>

<p><?php esc_html_e('To use it, add items to your cart and enter this code in the "Have a coupon?" field at checkout.', 'hdwebmobile-gift-cards'); ?></p>

<p style="text-align: center; margin: 24px 0;">
    <a href="<?php echo esc_url($checkout_url); ?>" class="button" style="background-color:#05A67D;color:#ffffff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;">
        <?php esc_html_e('Shop Now', 'hdwebmobile-gift-cards'); ?>
    </a>
</p>

<?php
if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}
?>

<?php
do_action('woocommerce_email_footer', $email); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook, not ours to prefix
