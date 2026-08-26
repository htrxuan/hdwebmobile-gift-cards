<?php

namespace htrxuan\hdgc;

if (!defined('ABSPATH')) {
    exit;
}

class HDGC_Email extends \WC_Email
{

    public function __construct()
    {
        $this->id             = 'hdgc_delivery';
        $this->customer_email = true;
        $this->title          = __('Gift Card Delivery', 'hdwebmobile-gift-cards');
        $this->description    = __('Sent to a customer when a gift card is issued, whether from a completed order or issued manually by an admin.', 'hdwebmobile-gift-cards');
        $this->template_html  = 'hdgc-delivery.php';
        $this->template_plain = 'plain/hdgc-delivery.php';
        $this->template_base  = HDGC_PLUGIN_DIR . 'emails/';
        $this->placeholders   = array(
            '{gift_card_code}'    => '',
            '{gift_card_balance}' => '',
            '{checkout_url}'      => '',
        );

        parent::__construct();
    }

    public function get_default_subject()
    {
        return __('Your gift card is ready', 'hdwebmobile-gift-cards');
    }

    public function get_default_heading()
    {
        return __('Here is your gift card', 'hdwebmobile-gift-cards');
    }

    /**
     * @param object $gift_card Row from HDGC_Repository (code, balance, currency, ...).
     * @return bool True if the email was sent.
     */
    public function trigger($gift_card)
    {
        $this->setup_locale();

        $this->object                               = $gift_card;
        $this->recipient                            = $gift_card->purchaser_email;
        $this->placeholders['{gift_card_code}']      = $gift_card->code;
        $this->placeholders['{gift_card_balance}']   = wc_price($gift_card->balance, array('currency' => $gift_card->currency));
        $this->placeholders['{checkout_url}']        = wc_get_checkout_url();

        if (!$this->is_enabled() || !$this->get_recipient()) {
            $this->restore_locale();
            return false;
        }

        $sent = $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());

        $this->restore_locale();

        return $sent;
    }

    public function get_content_html()
    {
        return wc_get_template_html(
            $this->template_html,
            array(
                'gift_card'          => $this->object,
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'gift_card_code'     => $this->placeholders['{gift_card_code}'],
                'gift_card_balance'  => $this->placeholders['{gift_card_balance}'],
                'checkout_url'       => $this->placeholders['{checkout_url}'],
                'sent_to_admin'      => false,
                'plain_text'         => false,
                'email'              => $this,
            ),
            '',
            $this->template_base
        );
    }

    public function get_content_plain()
    {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'gift_card'          => $this->object,
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'gift_card_code'     => $this->placeholders['{gift_card_code}'],
                'gift_card_balance'  => $this->placeholders['{gift_card_balance}'],
                'checkout_url'       => $this->placeholders['{checkout_url}'],
                'sent_to_admin'      => false,
                'plain_text'         => true,
                'email'              => $this,
            ),
            '',
            $this->template_base
        );
    }

    public function get_default_additional_content()
    {
        return __('If you have any questions, just reply to this email — we\'re happy to help.', 'hdwebmobile-gift-cards');
    }
}
