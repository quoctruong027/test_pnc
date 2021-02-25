=== UpStroke: WooCommerce One Click Upsells ===
Contributors: WooFunnels
Tested up to: 5.6
Stable tag: 2.2.9
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html


== Change log ==
= 2.2.9 (15/01/2021) =
* Improvement: Added filter for prevent default variation selection on page load. 
* Improvement: Future compatibility with funnel builder.
* Improvement: Fixed skip offer rules to work on any product match instead of all products. 
* Improvement: setup custom tax address based on order to handle edge cases of wrong tax calculations during upsell.
* Fixed: Compatibility issues with thrive builder. 
* Fixed: Compatibility issues with divi builder. 
* Fixed: Square integration to prevent tokenization when gateway is off from settings. 
* Fixed: An issue with slider images order not working in-sync with the variation selection. 
* Fixed: An issue when order going to primary order status even when funnel doesn't run. 
* Fixed: scheduled thankyou action hook running on unpaid orders. 
* Fixed: Export/import feature not copying all offer settings.  
* Fixed: An issue with Braintree Paypal tokenization failing when initialized from cart/product page instead of checkout. 
* Fixed: An issue with variation selection attributes not showing in the order declared in WC attributes.

= 2.2.8 (09/12/2020) =
* Compatible with WordPress 5.6
* Fixed: Offer pages has JS issues after WordPress 5.6 update, fixed.
* Fixed: Upsell page fixes with Oxygen builder.
* Fixed: FB eCommerce events flow improved for certain scenarios.


= 2.2.7 (26/11/2020) =
* Added: Compatibility with Woodmart theme for the elementor widgets.
* Fixed: Issues with WPML while generating funnel translations.
* Fixed: Compatibility with Multiple table rate shipping plugins.
* Fixed: Admin breadcrumb link broken.


= 2.2.6 (23/11/2020) =
* Added: Six more pre-designed elementor templates.
* Modified: Revert back new order creation in case of order status normalising before offer gets accepted.
* Added: Compatibility with WooCommerce Multi Currency Premium plugin in shipping calculation.
* Fixed: Issue with Affiliate WP Plugin in calculating lifetime commissions.
* Fixed: Incorrect shipping cost calculations due to thousand separator settings.
* Fixed: Issue with offer payment failing with Paypal when digital product in offer and shipping is disabled.
* Fixed: Pinterest tracking not working.
* Fixed: Quantity and variation selector dropdowns widget alignments.
* Improved: Breadcrumb UI
* Fixed: Offer payments failing in Paypal due to country parameter modified by WPML.
* Added: Action hook for sidebar modifications.
* Added: Compatibility with EU VAT plugin for tax exemption.


= 2.2.5 (26/10/2020) =
* Fixed: Braintree PayPal upsells payments not working in case of multiple merchant IDs are configured.
* Fixed: Issue with WPML regarding click on button not working.
* Fixed: Incorrect tracking total getting attributed to referrals by AffiliateWP plugin.


= 2.2.4 (07/10/2020) =
* Fixed: Issue with Affiliate WP compatibility for multiple upsells.
* Fixed: PHP notice with Braintree CC gateway integration in some PHP versions.
* Fixed: Issue with Square payment gateway compatibility in creating a new order.
* Fixed: Facebook purchase pixels were not getting fired when PageView event was unchecked.
* Fixed: jQuery migrate deprecation notices.

= 2.2.3 (10/09/2020) =
* Fixed: Issue in FB pixels when traffic event params setting are checked in.

= 2.2.2 (09/09/2020) =
* Compatible with WooCommerce 4.5
* Added: Create a new rule of custom order meta. 
* Added: Compatibility with "WooSwatches - Woocommerce Color or Image Variation Swatches" plugin. 
* Added: Post type support for UX builder provided by Flatsome theme.
* Improved: Improve permalink behaviour of offers, to support an empty post type base.  
* Improved: Order Behavior improvement on edge case with batching an item with the order. 
* Fixed: Issue of unminified js showing on front-end to track UTM params. 
* Fixed: Issue of offer confirmation styling not showing dynamically on custom pages.
* Fixed: Issue with Pinterest settings are not showing to users. 
* Fixed; Issue with wc membership compatibility not working on certain cases. 
* Fixed: Issue with flat rate shipping amount with pricing output. 
* Fixed: Issue with templates not getting imported from the cloud on some specific WPML setups. 
* Fixed: Issue with square payment when a product is no longer synced with the square gateway. 
* Fixed: Issues with script loading in backend admin screens. 
* Fixed: PageView event settings rolled back. 
* Fixed: CSS issues in reports metabox. 

= 2.2.1 (14/08/2020) =
* Fixed: A label name for the admin setting of discounting

= 2.2.0 (14/08/2020) =
* Compatible with WordPress 5.5
* Compatible with WooCommerce 4.4
* Added: New Elementor templates - Persuader, Magnetic, Presenter added.
* Added: Ability to import/ export funnels.
* Added: New shortcodes for Product image slider, Product Title, Product Short Descriptions.
* Added: Ability to customize offer pages using any page builder from the native post edit screen.
* Added: Two new links for 'accept' and 'reject' to set up in any page builder. http://imgwxl.com/am/Screenshot-at-20-38-08.png
* Added: New rule to run funnel only on guest users.
* Added: New rule operator in product tag and category for "matches none of".
* Added: Compatibility with Ultimate affiliate pro plugin.
* Added: Compatibility with "Autocomplete WooCommerce Orders" WooCommerce addon for PayPal.
* Added: A feature in discounting to apply a discount on the dynamic sale price of the product.
* Added: A timeline view of funnel ran single order admin screen.
* Improved: Back-end admin UI improved for offer, design and settings pages.
* Improved: Optimize funnel setup process to speed up cart/checkout loading.
* Improved: Google analytics integration, admin can add multiple google ads conversion IDs to track.
* Improved: Analytics tracking opened up Order and Offer data variables to allow any tracking script to use the real data.
* Improved: Funnel listing now have date column sortable.
* Improved: Compatibility with WPML, it will now allow the store owner to create duplicate funnels of a different language in one click.
* Improved: Elementor widget option to change icon colour in the accept button widget.
* Fixed: Footer link colour not updating on offer pages.
* Fixed: Elementor Accept button icon placement Issue.
* Fixed: Membership getting expired in case of order cancel and refund.
* Fixed: Issue for not showing offer refund meta box when 0 amount primary order with a free trial subscription.
* Fixed: MySQL error comes up in a rare scenario, fixed.
* Fixed: Currency code for the GA events is not setting up correctly.
* Fixed: Issue with Yoast SEO plugin related to pixels.
* Fixed: Issue while HTML encoding in funnel's name.
* Fixed: Issue in preventing mails while processing cancellation when email set to fire on funnel ends.
* Fixed: Issue of JS error while elementor timer hits zero on non-offer pages.
* Fixed: Offer confirmation sidebar styling issue with admin bar.
* fixed: Issue with non-SKU products is getting over-ridden in Google Analytics.
* Fixed: Issue in JS on IEv11 on the offer page.
* Fixed: Order status going to complete in case of mixed carts by upsells.
* Fixed: Issue with bundle product compatibility to apply a discount on regular price instead of the sale price.

= 2.1.7 (20/03/2020) =
* Added: A new field added to control fonts in customizer templates.
* Improved: Compatibility with membership plugin improved with cases when user not logged in.
* Fixed: issue with emojis in the product item description failing in charge request for Authotize.net CIM gateway.
* Fixed: PayPal transaction-related meta was not getting saved properly for the delayed IPN cases.
* Fixed: Duplicate images coming in the slider for shortcode and elementor widget.
* Fixed: Handle case when PHP error coming due to transient folder is empty.
* Fixed: Compatibility issue with the Amazon FBA plugin for the delayed PayPal IPN cases.
* Fixed: Compatibility issue with WC Germanized gateway regarding sending mails.


= 2.1.6 (23/01/2020) =
* Added: Compatibility with WooCommerce version 3.9.
* Improved: Added traffic & UTM parameters for tracking data for google ads.
* Fixed: Correct coupon name was not showing in rules in some cases.
* Fixed: Dynamic shipping not getting calculated correctly when variable products in the offer.
* Fixed: Compatibility issues with WC Germanized addon in sending mails after funnel ends.
* Fixed: Compatibility issues with Generatepress theme and GeneratePress premium addon.
* Fixed: Upsell Offer payments for Braintree CC failing for the cards requiring 3ds in some cases.

= 2.1.5 (20/12/2019) =
* Improved: Handling when custom page assigned with the offer no longer exists.
* Fixed: PHP warning on checkout pages in some case while no shipping application in the carts since the last update.
* Fixed: In some case PayPal IPN creating issues with order status management.
* Fixed: Issue of "CartTokenNonce" consumed in Square payment gateway integration.

= 2.1.4 (07/12/2019) =
* Fixed: Stripe Integration not working properly for non-deciman currencies like (JPN YN)
* Fixed: PHP warning on virtual carts since last update.

= 2.1.3 (28/11/2019) =
* Added: Square payment gateway integration.
* Added: Compatibility with WC hide shipping method plugin.
* Fixed: PHP notice when state descriptor is missing in stripe settings.
* Fixed: Authorize.CIM gateway card validation errors were not showing to the customer.

= 2.1.2 (14/11/2019) =
* Added: Future compatibility with Woofunnels A/B experiment plugin.
* Added: Compatibility with WordPress version 5.3.0.
* Fixed: Handle few cases when the cart is unavailable during angelleye operations.
* Fixed: Same order ID was passing in offer purchase tracking data for google Ads.

= 2.1.1 (05/11/2019) =
* Fixed: sandbox transaction URL in refund metabox even on the live setup.
* Fixed: Few font size controls on elementor were not working as expected.
* Fixed: Few customizer controls were not working since the last update.

= 2.1.0 (24/10/2019) =
* Added: New feature to jump to any offer in the upsell funnel.
* Improved: New UI with the WordPress native look and feel.
* Fixed: Google ads tracking settings were not showing since last update.
* Fixed: PHP warning when no email present during update checkout review calls.
* Fixed: Handled the case with paypal delayed IPNs causing the order to be on-hold for the orders having offers accepted.
* Fixed: Customizer settings for the offer confirmation panal was not working as expected.
* Fixed: Issue in IE v11 regarding the 'startwith' JS function.
* Fixed: Handled for the edge case in authorize.net CIM integration to handle stores with very high volume stores ( > 75000).

= 2.0.11 (27/09/2019) =
* Fixed: upsell payments with amount zero was not handled correctly for stripe integration.
* Fixed: Rule "Aerocheckot Page" was not working correctly for embed checkouts.
* Fixed: PHP warning during CLI requests.

= 2.0.10 (24/09/2019) =
* Fixed: Upsell Transactions for the Braintree gateway were failing in some cases, since the last update.

= 2.0.9 (19/09/2019) =
* Added: Add support for Strong Customer Authentication (SCA) for Payments through WooCommerce PayPal Powered by Braintree Gateway.
* Fixed: Query removed for checking customer data indexing status, causing backend slowness in high order volume stores.

= 2.0.8 (13/09/2019) =
* Added: Add support for Strong Customer Authentication (SCA) for Payments through WC Stripe Gateway.
* Fixed: VSL Template not showing accept/decline button on few cases.
* Fixed: Elementor Price and button widget settings corrected.
* Fixed: Handled few cases of 0 orders in order indexing.
* Fixed: Issue with prices when bundle product upsell quantity is greater than one.

= 2.0.7 (26/08/2019) =
* Fixed: Rules based on order data was not working since the last update.

= 2.0.6 (22/08/2019) =
* Added: A filter `add_filter('wfocu_allow_externals_on_customizer', '__return_true');` to use shortcodes on customizer templates.
* Fixed: Font Controls are not woring correctly for regular price in the elementor widget.
* Fixed: PHP error on paypal express checkout `setExpressCheckout` API call failture.

= 2.0.5 (06/08/2019) =
* Added: Compatibility with lazy loading feature of autoptimize plugin.
* Added: Google purchase event added along with transactions.
* Improved: localization file cleanup.
* Improved: stripe integration to handle few edge cases of payment failures.
* Fixed: Duplicate admin email was going on some cases.
* Fixed: Paypal taxes were not getting calculated on some specific price settings.
* Fixed: Handle scenario of duplicate e-commerce tracking events.
* Fixed: javascript error on non-offer pages elementor builders.
* Fixed: PHP error on wizard pages from WooCommerce admin.

= 2.0.4 (25/07/2019) =
* Improvement: Made view offer events unique per session.
* Improvement: Handled case when no variation selector present in the offer page with builder templates.
* Improvement: Tab behaviour improved for mobile devices.
* Fixed: Delayed Emails on bacs and cheque gateways orders were not going on some cases.
* Fixed: Modified few arguments to pass to stripe API during charge request.


= 2.0.3 (18/07/2019) =
* Fixed: Customizer compatibility with plugin "Thank You Page Customizer for WooCommerce"
* Fixed: Conflict with WeGlot plugin
* Fixed: Issue of alert popup getting overlaped with sidebar confirmation.
* Fixed: Shipping taxes were not getting applied correctly on some cases.
* Fixed: Google ads tracking data functionality not working as expected.


= 2.0.2 (05/07/2019) =
* Fixed: Ecommerce analytics tracking events were not working as expected on some cases.

= 2.0.1 (04/07/2019) =
* Improved: Better handling for the case when the offer was not getting saved because of large variations.
* Fixed: Compatibility with image optimization plugin WP Optimal.
* Fixed: Elementor templates grid was not visible on some setups.
* Fixed: Sometimes a PHP warning coming on single order edit page in the dashboard.
* Fixed: Non-prefixed Javascript files and stylesheet conflict resolved.
* Fixed: Few global settings were saving with backslashes.

= 2.0.0 (28/06/2019) =
* Added: Deep Integration with Elementor with 10 new Elementor widgets and 6 pre-build templates:
   Following new elementor widgets are created for UpStroke
    - Accept button
    - Reject Button
    - Accept link
    - Reject link
    - Product Title
    - Price Widget
    - Product Images
    - Product Short description
    - Quantitiy Selector
    - Variation Selector

* Added: Offer Transaction Refunds; Introduced refunds of offer transactions created during the upsell funnel.

   We currently support offer refunds for these gateways:
    - Paypal standard
    - Paypal Express checkout by WooCommerce
    - Paypal Express checkout by angelleye
    - Paypal PRO by angelleye
    - Braintree by angelleye
    - Braintree by WooCommerce
    - Authorize.net CIM
    - Stripe
    - Mollie
    - WorldPay Online
    - Bluesnap

* Added: New Customer rules & skip offer setting added to allow store owners to skip offer/funnel based on past purchases of the customer.
* Added: Deep integration with the nextmove thank you page plugin, now store owners can configure their thank you page they want to show after the funnel directly from the funnel settings page.
* Added: New Rules added for Aerocheckout Page, Coupon Exists & Coupon Text match.
* Added: Support for the Google Ads Conversion tracking, UpStroke now sends google ads conversion event as well.
* Improved: Better onboarding experience for new customers by setting up two default funnels with different styles.
* Improved: No conflict mode in funnel builder to ensure that any external JS/CSS will no longer conflict with our Funnel builder JS.
* Improved: Users/coupon rules are now AJAX supported to ensure optimized Database queries during setup.
* Improved. Code refactoring in favour of WordPress VIP Coding-Standards.
* Improved. Few Security improvements to prevent XSS and CSRF attacks.
* Fixed: Handled the scenario for Paypal Delayed IPN. In that case, refunded primary-order was not working as expected.
* Fixed: Compatibility with WC Membership improved to handle delayed IPN and refund scenarios & few other improvements.
* Fixed: Customizer preview was not working as expected for default permalink state.
* Fixed: Order status "primary order accepted" is no longer cancelable.
* Fixed: Paypal Reference transactions options were still showing sometimes even when no PayPal gateway is unavailable in the settings.
* Fixed: Few hardcoded texts inside the plugin corrected in favour of translations.
* Fixed. Non-taxable shipping method having shipping taxes added in the total when shipping added using dynamic shipping during the offer.
* Fixed: Urgency bar inline style was not working as expected.
* Fixed: Additional compatibility with WooCommerce Bundle Product Type, it covers "price individually feature along with discounting".
* Fixed. Store product reviews came after wc3.4 were not showing on offer pages.
* Fixed: Subscriptions created through test gateway by woofunnels were not sustaining active status after the first renewal.
* Fixed. Countdown timer's mobile responsive CSS corrected.
* Fixed. CSS compatibility fix for the infusionSoft plugin.
* Fixed. Qty selector was not working correctly for the cases when the maximum quantity field left empty.
* Fixed. Handled Button/links multiple click events.

= 1.18.1 (17/04/2019) =


= 1.18.0 (29/03/2019) =
* Fixed : Authorize.net CIM upsell payments was not working since the last update of gateway plugin.
* Improved: Handle cases of multiple clicks on "Add to my Order" button.

= 1.17.0 (11/03/2019) =

* Added: Compatibility with "Lazy Load plugin" by WP-Rocket.
* Improved: Shortcode `wfocu_order_details_section` compatibility with "Custom Thank You Pages Per Product for WooCommerce"
* Improved: Modified request pattern to handle cases when admin-ajax was not working due to any reason.
* Fixed: Paypal Standard checkout was not working as expected on few edge cases.
* Fixed: Dynamic Product Reviews block was not showing on offer pages during the funnel.

= 2.0 beta(15/12/2018) =
= 1.16.0 (22/02/2019) =
* Added: Compatibility with 'Cost of Goods' product by Skyverge.
* Added: Compatibility with 'WooCommerce Memberships' product by Skyverge.
* Added: A new settings to add Javascript inside <head> tag on offer pages.
* Fixed: In Rule Engine Customer Role rule was not working when operator was  "not in"
* Fixed: Compatibility changes w.r.t PHP 7.3.
* Fixed: Few PHP warnings while using customizer.
* Fixed: Emails for customer and admins were not triggering as expected for the orders with BACS and Cheque as a payment method.
* Fixed: Orders were not getting cancelled when status is "primary Order status."
* Fixed: Offer payments error in Braintree CC on few setups with multi account condition

= 1.15.1 (22/01/2019) =
* Fixed: Adding items to the order was not working as expected  when dynamic shipping is turned on since yesterday's update.

= 1.15.0 (21/01/2019) =
* Fixed: Dynamic Shipping calculation error due to yesterday's update, it was fixed.
* Fixed: Sometimes global settings UI was not opening due to a JS error when no gateways enabled.
* Fixed: Fatal error during the funnel initiation when WC subscription installed version < v2.2.0
* Fixed: Paypal in-offer transactions were not working as expected on WPEngine hostings.
* Fixed: Dynamic shipping calculations were not taking taxes into account.
* Improved: Currency as a parameter was missing in google analytics purchase event data.


= 1.14.0 (16/01/2019) =
* Improved: Handling of a case when offer URL get accessed directly without the funnel.
* Improved: Handling of a few special characters in variation switcher.
* Improved: PayPal in offer transaction: Better handling for errors and logging in order notes when payment fails.
* Fixed: Handling for the case when the variable product is purchasable, and still all the variations are out of stock.
* Fixed:  Progress bar element's Font size not getting applied for style#2.
* Fixed: Funnel duplication is not working correctly since the last update.
* Fixed: Conflict with 'thrive themes' regarding swal.
* Fixed: Multiple facebook events getting fired in case of more than once pixel ID configured.


= 1.13.0 (10/01/2019) =
* Added: New setting in funnel settings to display prices with OR without taxes on offer pages.
* Added: Added support/handling for multiple shipping packages in primary order.
* Added: Compatibility with 'Amazon Order Fullfillment' Addon.
* Improved: Upsell Report Data was not getting removed on permanent deletion of order.
* Improved: WPML compatibility improvements for the custom page search.
* Improved: Fallback for the case when user leaves funnel in between and doesn't reach to order-received page.
* Fixed: Calculation tooltip in offer builder page showing regular price including taxes, it supposed to show regular price only.
* Fixed: Regular Price with strike through was not visible for template except Style#1.
* Fixed: PayPal Express checkout issue of 'SHIPDISCAMT' not getting clear while making DoReferenceTransaction call, causing a mismatch in calculation during offer charge API requests.
* Fixed: Handling for few more special character for variations in an offer.
* Fixed: Offer was not getting skipped when variable product present in the offer is not purchasable.
* Fixed: order item rule was not fully compatible with wc > 3.2.

= 1.12.1 (06/12/2018) =
* Fixed: Remove Non-ASCII characters from product description from PayPal gateway call.


= 1.12.0 (05/12/2018) =
* Added: New settings inside facebook events to handle exclusion of taxes and shipping totals from the purchase event value.
* Added: Few More parameters in facebook pixel events (domain,event_hour,user_roles,plugin,event_day,event_month,transaction_id)
* Fixed: PHP Error in cron schedule to normalize order statuses.
* Fixed: "jQuery" not defined error on some websites, prevented usage of jQuery with native JS from UTM tracking functions.


= 1.11.3 (28/11/2018)=
* Added: Compatibility with the Nitro theme.
* Added: Compatibility with XLPlugins' NextMove Thank you page plugin to show additional orders on thank you page.
* Fixed: PayPal express checkout and standard Ajax endpoint issue resolved, making offer payments failing when reference transactions turned off.
* Fixed: Issue in Variation switcher, sometimes showing repeated attributes value.


= 1.11.2 (27/11/2018)=
* Fixed: Offer payments not working on paypal express checkout gateway when reference transactions are off.


= 1.11.1 (26/11/2018)=
* Fixed: Payment Gateways related errors are showing even if gateway is turned off.


= 1.11.0 (26/11/2018)=
* Added: Customizer compatibility with Jupiter theme.
* Improved: Dynamic shipping will now work without getting offer confirmation enabled.
* Fixed: Authorize CIM integration, upsells payments was not working for the recent version of WooCommerce Authorize CIM gateway version 2.10.2.
* Fixed: PayPal PDT handling to mark the parent order as completed on behalf of woocommerce as thank you page replaced by the funnel offer page.
* Fixed: Downsell offer were not showing when there is any disabled offer in between the funnel.
* Fixed: Customizer fields getting broken due to titan's framework wp-alpha-colorpicker sript.
* Fixed: Added polyfill js to handle issue about upsells not working in IE browser.


= 1.10.0 (13/11/2018)=
* Added: New settings in the global settings under Facebook pixel to manage 'content_id' param in the purchase event.
* Added: New param 'content_name' added in the Facebook pixel purchase event.
* Added: New settings to enable Facebook pixel general event for the offer pages.
* Added: New settings to pass URL tracking info, referrals & Custom audience params (Customer's Town, Customer's State, Customer's Country, Payment Method, Shipping Method & Coupon usage details)
* Added: Authorize CIM Integration compatibility with WooCommerce Authorize CIM Gateway Addon v2.10.0.
* Improved: Compatibility of the Test Gateway with Subscription products.
* Fixed: Authorize CIM offer payments were failing due to API call exception for duplicate shipping ID. This issue is getting generated when only virtual/downloadable product(s) exist in the order.
* Fixed: Flat Shipping value was not getting saved as a float value, getting converted to absolute.
* Fixed: User Role rule was not working as expected.
* Fixed: Issue of order-details not showing when parent order is canceled in the funnel and more than one order created during the funnel.


= 1.9.0 (20/10/2018)=
* Added: New Feature to allow store admins to create, delete and apply presets on the templates provided by the UpStroke.
* Added: Ability to duplicate the funnels, including its offers, design and primary settings.
* Added: Compatibility with 3rd party free shipping add-ons like WooCommerce Advanced Free Shipping.
* Added: Compatibility with new product type "bundle" provided by WooCommerce Product Bundle addon.
* Fixed: Fixed Upsell payment failure cases for PayPal gateway when free shipping method has opted during the funnel.
* Fixed: Sometimes Paypal integration of upsell payment failing because item description contains emojis and HTML special chars.
* Fixed: Incompatibility with WC version 3.0


= 1.8.2 (11/10/2018)=
* Added: Compatibility with WooCommerce Miltilingual's Multiple Currency Feature.
* Fixed: PHP Fatal error when php version < 5.5  triggered by the last update.

= 1.8.1 (06/10/2018) =
* Added: Added compatibility for 'Learndash LMS' to allow courses as upsells in the upstroke funnels.
* Added: Support for WP native embed shortcode in our offer templates.

= 1.8.0 (06/10/2018) =
* Added: New setting at funnel level to allow store managers to change 'Upsell Processing message.'
* Added: Support for 'the_content' filter in "pattern interrupt" subheading section to make it work with shortcodes.
* Improvements: textual improvements in admin UIs.
* Fixed: PHP notice when custom CSS is added using the customizer.
* Fixed: Order total rule was not working as expected with '>=' type operations.
* Fixed: Affiliate wp compatibility: Referrals was only getting added on very first offer accepted.
* Fixed: Affiliate wp compatibility: Not considering product local level discount on products in offers.
* Fixed: JS notices on customizer because ACFs JS was not getting added.
* Fixed: Authorize.net CIM upwards and backward compatibility with gateway's different versions.
* Fixed: Stripe integration: Do not show 'save a card' checkbox during checkout when funnel already decided.
* Fixed: Orphaned Transients were getting created when WC session does not exist. Routine added to remove these transients.
* Fixed: PayPal integrations: Removed HTML tags from item description while passing request to PayPal, 'billingagreementid' is not getting received.

= 1.7.4 (20/09/2018) =
* Added: Compatibility with 'Buzzstorepro' and 'Easy Google fonts' for the customizer.
* Added: Compatibility with Affiliate WP to mark referrals on the upsell items.
* Improved: Loading of assets (JS & CSS) improved to reduce page load time.
* Improved: Update database for better reporting.
* Improved: Better logging even in case of javascript errors while accepting/denying offers.
* Fixed: In case of BACS and COD, emails were not getting fired as per the settings.
* Fixed: Prevent switching offers while configuring offers when offers are in the saving process.
* Fixed: `Srcset` removed from product slider images.

= 1.7.3 (10/09/2018) =
* Added: New event "offer skipped" introduced in reporting, with the reason of the skip of the offer by UpStroke.
* Improved: In some browsers, offer view event was not getting logged. Improved it by adding a fallback code to document.ready DOM event.
* Improved: When WooCommerce Checkout processed as post request and not as AJAX action, then funnels were not getting trigger because gateways integrations were getting initiated later.
* Improved: E-commerce tracking code improved to not fire events multiple times during the funnel.
* Fixed: In Braintree CC and PayPal, payment complete action was not getting full-filled as parent order token not created during the original order.
* Fixed: Stripe Fees and net payouts were not getting saved in the order meta when upsell payment accepted.
* Fixed: Global $product was un-setting and creating issues on upsell page for tabs area.

= 1.7.2 (01/09/2018)=
* Added: Compatibility for Advanced Shipping when WC version < 3.3.5 is used.
* Added: New Shortcode `wfocu_order_details_section` to output 'order_details' on custom thank you pages.
* Improved: Upsell Offer price was getting added as "discount" in order-receipts. Now the right offer price shows against the item purchased, and no additional "discount" row gets added.
* Improved: Additional logs included to trace any edge case issues.
* Fixed: On the change of variation, % is getting removed from "saving_percentage" merge tag output.
* Fixed: Upsell links generated using Page builders were not triggering PayPal in-context checkout.
* Fixed: Thrive architect conflict as it unhooks `template_include` filter on the pages created using thrive builder.
* Fixed: Removed 'wfocu-session-id' query param replaced it with 'wfocu-si' ,it prevents issues with certain host stopping any request to load when these params exist.

= 1.7.1 (29/08/2018) =
* Fixed: Buy Button Style #2 sometimes was not allowing users to skip the offer, even on clicking skip buttons.
* Fixed: PayPal integration without reference transaction was not moving order to processing/completed when subscription product in the order even when IPN response received as completed.
* Fixed: Changing offer post type slug was not getting reflected right away but demands resetting permalink.

= 1.7.0 (28/08/2018) =

* Fixed: Funnel validation failing when user's IP was getting changes in between the checkout process. 
* Improved: Product Image slider images now have thumbnail sizes instead of full. 
* Fixed: Authorize CIM Integration; Shipping Address filters were not working and moving orders to on-hold for manual review when upsell-offer accepted due to CustomerShippingID did not exist in the API call. 
* Fixed: PayPal Standard Integration: Order Item info is now getting passed to PayPal as a separate item but not as a single item. Resulting clean item totals in the PayPal transactional emails. 
* Improved: Totals of upsell-orders in order listing in admin interface was not accurate while using multi-currency environments. Now upsell order total shows the price in the base currency. 
* Fixed: PayPal Express Checkout Integration; When order does not needs shipping then prevent express checkout to send shipping info during upsell accept. 
* Fixed: Conflict with the themes/plugins having kirki framework. 
* Added: New merge tag `[wfocu_order_data key=""]` added to allow order metadata/data in customized offer pages. 
* Improved: Compatibility for php7.2
* Added: A new feature to allow quantity selection in offer pages & a new setting to control the maximum allowed quantity for purchase during upsell. 
* Fixed: Sometimes "Test Gateway By WooFunnels" is getting unset while choosing "Test Gateway" as a selected payment option during checkout, first experienced as incompatibility with "WooCommerce Germanized" plugin. 
* Fixed: Prevented Offer pages from getting indexed in search engines.
* Added: A new merge tag {{single_unit_price}} to show a single unit price when selling multiple quantities as a bundle. 
* Improved: Replaced the_content with 'wfocu_the_content' (custom) filter to escape any non-required text in offer templates.
* Fixed: PayPal Standard Integration: PayPal upsells orders to run and charge in-offer transaction even when parent order amount is zero.
* Improved: Do not die when funnel validation fails for any reason, rather redirect the customer to the order received page and logs the reason for the error. 
* Fixed: Item amount mismatch error on PayPal standard and express checkout during upsell charge when more than one quantity is selected. 

= 1.6.0 (07/08/2018) =
* Added: Releasing PayPal In-Offer Transactions ( Two click checkout for the PayPal supported gateways), this allows store owners to show and charge upsells for PayPal orders even if they do not reference transactions enabled in their PayPal account.
* Fixed: PayPal IPN of the parent order getting failed and moves order into on-hold after upsell is getting accepted before IPN response.
* Fixed: Rounding-off prices just after applying discounts and then calculating taxes and shipping on that prices, escaping issues generating when price difference of 0.01 is getting into effect for PayPal.
* Added: Changes to provide support for AngellEye PayPal Addon.
* Added: Changes to provide support for Subscription Addon.
* Fixed: Resetting permalink on plugin activation to prevent permalink issues.
* Added: Compatibility with wooMultiCurrency addon (https://codecanyon.net/item/woocommerce-multi-currency/20948446)
* Fixed: Sometimes offer-builder screen(s) were not getting open and loading infinitely on Mac/Safari OS.


= 1.5.3 (26/07/2018) =
* Fixed: Iframe videos coming full width on mobile. Fixed now.
* Compatible till WooCommmerce 3.4.4


= 1.5.2 (24/07/2018) =
* Fixed: An undefined function was using in PayPal method, fixed now.


= 1.5.1 (24/07/2018) =
* Fixed: Funnel offer is skipping if the offer contains a variable product and in offer settings 'skip offer' is checked.
* Fixed: Order_Date merge tag was returning Order date in GMT 0 timezone, fixed now.


= 1.5.0 (23/07/2018) =
* Added: Necessary PHP constants and headers to prevent caching of the offer pages.
* Added: UpStroke total sale for the order is now be showing on order listing table.
* Added: At Funnels listing page, added sidebar for useful information &amp; links.
* Added: PHP 7.2 compatibility.
* Improved: Allowed iframe tag in post context for the textarea inputs.
* Improved: Moved from cookie usage to transients and query params to prevent any issues caused by caching environment that do not let the funnel to initiate due to incorrect data setup.
* Improved: Handled ASCII character coming in variation selector as attribute names. Converted them to "_" string all through the from end page and restored to the default to save item meta properly.
* Improved: Database structure improved to support launch of new reporting addon.
* Improved: Success animation time increased for success popup while upsell successfully accepted, breaking animation on mobile devices and MACs.
* Fixed: Checking is_purchasable only after checking is_in_stock while validating offer before showing.
* Fixed: In some cases, DoReferenceTransaction call for PayPal passing IPN notify URL that later on moving order status to on-hold due to the failure of order amount mismatch.
* Fixed: Issue in rules, when any rule set that needs to be validated after the order was not working and resetting the session.
* Fixed: Javascript error throwing when serializing value of the variation form contains encoded params.
* Fixed: PayPal notice throwing on the purchase of variation during upsell accept.
* Fixed: PayPal Standard integration getting broken when billing agreement call is getting failed.
* Fixed: Handled AJAX on the offer pages to not show loader infinitely and redirect to thank you page on ajax failures.
* Fixed: Issue in `skip offer` setting, when a product exists in parent order was only working when exact order matches with an offer.
* Fixed: Bacs and cheque orders were not showing upsell from the last transient changeset as data was not getting set and funnel queried for these gateways. Fixed it by moving priorities.
* Fixed: Authorize CIM sometime throws an error when same user profile data use to create a token for a non-logged in user. Added better error handling to prevent the issue as well as prepare it to run the funnel based on the previous valid token.
* Fixed: Authorize NET CIM gateway api endpoint was not getting fetched from gateway settings during charging upsells.
* Fixed: PayPal upsell charge failing in some cases when dynamic shipping is on with the taxes enabled in the store.
* Fixed: Global settings for gateways were not set up properly on some servers due to inconsistent array keys behavior during localization of script.
* Fixed: PayPal Express Checkout: Issue while saving order_meta for the token as the gateway plugin is not saving it before payment_complete, Now we always create billing regardless of funnel decided or not to provide support for no-checkout page checkouts.


= 1.4.1 (06/07/2018) =
* Fixed: Debugging class param left open that caused PHP error, commented now.


= 1.4.0 (06/07/2018) =
* Fixed: PayPal Express Checkout Integration prevents checkout from cart to fire even when the gateway is not ppec_checkout.
* Fixed: Primary Order Status title was not coming to the filter in order screens.
* Fixed: Buy Button width to be full for product style #5 and #6.
* Fixed: Cart Item Rules sometimes do not have product id in the cart items to process, used product ID from cart data to instead.
* Fixed: Fatal Error throw sometimes when we do not have available payment methods in the admin, used `$woocommerce->payment_gateways->payment_gateways()` instead.
* Fixed: Google View event was firing on every page, restricted it only to offer pages/thankyou.
* Added: Order item meta added to the items added by the upstroke during funnel.
* Fixed: Test Gateway orders were going to complete instead of processing after order.
* Fixed: Flush Rewrite when saving global settings to prevent any 404 not found errors during offers.
* Fixed: Customizer was not working for the default permalink structure.
* Improved: Prevents inactive supported gateways to get saved while saving global settings.
* Added: Stripe 3DS Card flow covered, initiating funnel after completion and charging reusable token during funnel.
* Fixed: Funnels Transients getting created after product rule iteration once and stored for 6 hours, creating issues when multiple users were having the cart at the same time.
* Fixed: Added `woocommerce_checkout_order_processed` 3rd param as optional in our callbacks, reducing the chance of failure as hook initiated by 3rd party plugins without the 3rd parameter.
* Fixed: Set host url in allowed redirect url for customizer in case wp defined site url is different then home url: issue with wp engine.
* Fixed: In some cases, Funnel Offer is not opening as data in the cookie necessary for running the funnel is missing. Migrated to transients from cookies to handle the vulnerability.
* Fixed: Timers were not sustaining their initial timestamp and not starting the timer from when the user reloads the offer page.


= 1.3.0 (29/06/2018) =
* Added: Multiple notices regarding gateways configurations to let admin know the state of the setup.
* Added: A new field to make the store admin chose if reference transactions are enabled or not.
* Fixed: PayPal standard to not interfere when credentials are not set and reference transaction is not marked as enabled.
* Fixed: Parent Order e-commerce tracking now push events on thank you page even when no funnel runs.
* Fixed: Additional tab throwing a fatal error in some cases due to the global $product is getting unset.
* Fixed: Issue while saving gateways with all unchecked, the system will loading default gateways.
* Fixed: Prices after order batching was getting mismatched when formatted in thousand separator.


= 1.2.0 (26/06/2018) =
* Added: Compatibility with AliWooDropship plugin for variation switcher.
* Fixed: Gallery images to slide to the correct variation image on selection of variation.
* Improved: Customizer Usability related improvement, Added a new button to show preview.
* Improved: 'shop_thumbnail' image size instead of full size when using as thumbnail in the slider.
* Fixed: Prevent slider when a single image exists in the gallery.


= 1.1.0 (25/06/2018) =
* Added: WordPress native image align class's respective code done.
* Added: Countdown timer merge tag added with style and align attributes.
* Fixed: Case: create new order when offer accepted and cancel parent order; Refunds were not going proper, fixed now.
* Fixed: Product additional details tab values dynamic in case of variable product.
* Improved: Offer's Customizer view improved; Themes customizer assets rendering blocked on our page.
* Fixed: Offer total in the funnel builder was not taking flat rate shipping into account while creating new offer.


= 1.0.0 (18/06/2018) =
* Public Release