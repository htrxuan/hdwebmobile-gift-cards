# HDWebmobile Gift Cards

Sell digital gift cards redeemed through WooCommerce's own native coupon field -- no custom checkout UI, no file uploads.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-gift-cards/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

HDWebmobile Gift Cards turns any simple WooCommerce product into a digital gift card. Flag a product as a gift card, and completing an order for it issues a unique, randomly generated code by email -- one code per unit purchased. Customers redeem a code exactly the way they already redeem a discount coupon: by typing it into WooCommerce's own "Have a coupon?" field at checkout. There is no new redemption screen to learn, and it works on both the classic cart/checkout and the Block Checkout.

A leading competing gift-card plugin has a disclosed CVSS 9.8 unauthenticated arbitrary-file-upload vulnerability. This plugin has zero file-upload surface at all -- every gift card code is generated server-side with PHP's cryptographically secure random_bytes(), never accepted as user-supplied input for the code's value, closing off that entire vulnerability class by construction.

## Features

* Turn any simple product into a gift card with a single checkbox -- automatically forced virtual, no shipping involved
* Completing an order issues one unique, cryptographically random code per unit purchased, emailed to the purchaser
* Redemption reuses WooCommerce's own native "Apply coupon" field -- works on classic Cart/Checkout and Block Checkout, no custom UI to build or maintain
* Partial redemption: if a cart total is smaller than the gift card's balance, only the amount actually used is deducted, leaving the correct remainder for next time
* Optional expiry, set in days from issuance, checked at redemption time
* "My Gift Cards" section on the customer's My Account dashboard showing remaining balance on any gift card they've purchased
* Admin list of every issued gift card (code, balance, status, purchaser, issued/expiry dates), with search and status filter
* Admin can manually issue a gift card of any amount to any email, for customer-service or refund scenarios
* Zero file-upload surface -- codes are always generated server-side, never accepted as arbitrary input

## Development

Standard WordPress plugin structure:

```
hdwebmobile-gift-cards.php    Bootstrap
includes/class-hdgc-activator.php
includes/class-hdgc-admin-list-table.php
includes/class-hdgc-admin.php
includes/class-hdgc-core.php
includes/class-hdgc-coupon.php
includes/class-hdgc-email.php
includes/class-hdgc-hub.php
includes/class-hdgc-issuer.php
includes/class-hdgc-myaccount.php
includes/class-hdgc-product.php
includes/class-hdgc-repository.php
```

Part of the [HDWebmobile](https://hdwebmobile.com/plugins/) suite of focused, single-purpose WooCommerce plugins.

## License

GPLv2 or later. See [LICENSE](LICENSE).

