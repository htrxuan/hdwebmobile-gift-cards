=== HDWebmobile Gift Cards ===
Contributors: htrxuan
Donate link: https://paypal.me/htrxuan/20
Tags: woocommerce, gift cards, store credit, coupon, digital products
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sell digital gift cards redeemed through WooCommerce's own native coupon field -- no custom checkout UI, no file uploads.

== Description ==

HDWebmobile Gift Cards turns any simple WooCommerce product into a digital gift card. Flag a product as a gift card, and completing an order for it issues a unique, randomly generated code by email -- one code per unit purchased. Customers redeem a code exactly the way they already redeem a discount coupon: by typing it into WooCommerce's own "Have a coupon?" field at checkout. There is no new redemption screen to learn, and it works on both the classic cart/checkout and the Block Checkout.

A leading competing gift-card plugin has a disclosed CVSS 9.8 unauthenticated arbitrary-file-upload vulnerability. This plugin has zero file-upload surface at all -- every gift card code is generated server-side with PHP's cryptographically secure random_bytes(), never accepted as user-supplied input for the code's value, closing off that entire vulnerability class by construction.

= Key Features =
* Turn any simple product into a gift card with a single checkbox -- automatically forced virtual, no shipping involved
* Completing an order issues one unique, cryptographically random code per unit purchased, emailed to the purchaser
* Redemption reuses WooCommerce's own native "Apply coupon" field -- works on classic Cart/Checkout and Block Checkout, no custom UI to build or maintain
* Partial redemption: if a cart total is smaller than the gift card's balance, only the amount actually used is deducted, leaving the correct remainder for next time
* Optional expiry, set in days from issuance, checked at redemption time
* "My Gift Cards" section on the customer's My Account dashboard showing remaining balance on any gift card they've purchased
* Admin list of every issued gift card (code, balance, status, purchaser, issued/expiry dates), with search and status filter
* Admin can manually issue a gift card of any amount to any email, for customer-service or refund scenarios
* Zero file-upload surface -- codes are always generated server-side, never accepted as arbitrary input

= Limitations (please read before installing) =
* Fixed value only -- a gift card's value is the product's own price; there is no customer-selectable/variable amount at purchase in this version
* No separate recipient-delivery flow -- the code is emailed to the purchaser only, with no "send to a friend's email with a gift message" field; the purchaser can forward the email themselves
* No redemption audit ledger -- only the current balance is stored, not a history of every order that redeemed against a code

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hdwebmobile-gift-cards` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress. WooCommerce must already be installed and active.
3. Go to **WooCommerce > Gift Cards Settings** to optionally set a default expiry period.

== How to Use ==

= 1. Create a gift card product =
Create (or edit) a simple product, check "This is a gift card" in the General tab of Product Data (Screenshot 1), and set its price -- that price becomes the gift card's value. Save; the product is automatically set to virtual.

= 2. A customer buys it =
When an order containing a gift-card product is marked Completed, this plugin issues one code per unit purchased and emails each one to the purchaser (Screenshot 2).

= 3. The code is redeemed like any coupon =
At checkout, the recipient enters the code in the "Have a coupon?" field (Screenshot 3) -- no separate gift-card field, no new page to learn.

= 4. Balances update automatically =
When the redeeming order completes, the gift card's balance is reduced by exactly the amount that order actually used. A card with balance remaining can be reused on a later order.

= 5. Manage gift cards from the admin =
Go to **WooCommerce > Gift Cards** (Screenshot 4) to search issued codes, filter by status, or manually issue a new gift card to any email address.

== Screenshots ==

1. The "This is a gift card" checkbox on the Product Data General tab.
2. The gift-card delivery email, showing the code and balance.
3. Redeeming a code through WooCommerce's own "Have a coupon?" field at checkout.
4. The admin Gift Cards list, with the manual-issue form expanded.

== Changelog ==

= 1.0.0 =
* Initial release: gift-card product checkbox, code issuance on order completion, redemption via WooCommerce's native coupon field, partial-balance tracking, optional expiry, My Account gift-card list, admin list table and manual issuance.
