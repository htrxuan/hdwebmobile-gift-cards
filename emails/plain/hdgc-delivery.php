<?php

/**
 * Gift card delivery email (plain text).
 *
 * @var object $gift_card
 * @var string $email_heading
 * @var string $additional_content
 * @var string $gift_card_code
 * @var string $gift_card_balance
 * @var string $checkout_url
 */

if (!defined('ABSPATH')) {
    exit;
}

echo esc_html(wp_strip_all_tags($email_heading)) . "\n\n";

esc_html_e('You have a gift card to spend in our store:', 'hdwebmobile-gift-cards');
echo "\n\n" . esc_html($gift_card_code) . "\n\n";

esc_html_e('Balance:', 'hdwebmobile-gift-cards');
echo ' ' . esc_html(wp_strip_all_tags($gift_card_balance)) . "\n\n";

esc_html_e('To use it, add items to your cart and enter this code in the "Have a coupon?" field at checkout.', 'hdwebmobile-gift-cards');
echo "\n\n";

esc_html_e('Shop now:', 'hdwebmobile-gift-cards');
echo "\n" . esc_html($checkout_url) . "\n\n";

if ($additional_content) {
    echo esc_html(wp_strip_all_tags($additional_content)) . "\n";
}
