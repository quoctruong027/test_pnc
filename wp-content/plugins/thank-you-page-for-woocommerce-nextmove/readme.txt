=== WooCommerce Thank You Page - NextMove ===
Contributors: xlplugins
Tags: WooCommerce, WooCommerce Thank You, WooCommerce Thank You Page, XLPlugins
Requires at least: 4.2.1
Tested up to: 5.4.1
Stable tag: 1.14.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==
The only plugin in WooCommerce that empowers you to build profit-pulling Thank You Pages with plug & play components. It's for store owners who want to get repeat orders on autopilot.


== About the Team ==
Next Move is backed and supported by a strong team of developers, support engineers and marketers from XLPlugins.
The team is constantly updating the plugin and ensuring its compatibility with the latest WooCommerce versions.
We believe trust comes before transactions. Hence all the plugins we create help our customers build trust with their buyers and then win over their wallets.


== Installation ==
1. Install 'WooCommerce Thank You Page - NextMove' Plugin.
2. Activate the Plugin.
3. Go to XlPlugins -> NextMove
4. Activate Thank You Page.

== Changelog ==

= 1.14.0 =
* Added: Compatible with WordPress 5.4
* Added: Compatible with WooCommerce 4.1
* Added: Join us component: LinkedIn and Youtube links added.
* Added: 'jilt_post_registration_html' merge tag for jilt registration on order received.
* Added: 'xlwcty_allowed_order_status' filter added for custom WC order statuses.
* Improved: Set Coupon Blur Intensity to 3 by default to make coupon not clearly visible.
* Improved: Customer info component: Billing/ Shipping address will now be showing in the same format.
* Fixed: Google analytics JS events on thank you page, showing some data error, fixed.
* Fixed: Map component: Map marker wasn't displaying when map is already loaded, fixed.
* Fixed: Thank you page link, when opened without order id or key, then redirecting to home url.
* Fixed: Smart bribe component: Coupon wasn't displaying after share a post, fixed.
* Fixed: Unwanted Data show on Thankyou Page From (WooCommerce Product Wizard) now fixed.
* Fixed: Handling done during catching PHP error.


= 1.13.0 =
* Added: Compatible with WooCommerce 3.9
* Added: Mollie payment gateway compatibility added, NextMove thank you page now triggers after mollie payment gateway order.
* Fixed: JS issues with Vitrine theme on the admin component screen, fixed.
* Fixed: Static image, text, html & video component shortcodes aren't working, fixed.
* Fixed: eCommerce JS events were not firing, fixed.


= 1.12.4 =
* Added: Compatible with WordPress 5.3
* Added: Compatible with WooCommerce 3.8
* Fixed: WP CLI: Warning when tries to activate resolved.


= 1.12.3 =
* Fixed: PHP error with Flatsome theme, resolved now.
* Fixed: PHP error on thank you page when using Dynamic Coupon component in some cases, resolved now.


= 1.12.2 =
* Fixed: PHP notice is coming on customer info component, issue corrected.
* Fixed: Order contains exactly rule corrected.


= 1.12.1 =
* Fixed: Component: Customer info, causing fatal error with WooCommerce 3.7.0 version, resolved now.


= 1.12.0 =
* Added: Merge Tag: {{order_coupon}}, displays the coupon code used in the order.
* Added: Background color setting added in additional component.
* Added: Setting for displaying order downloads added in order details component.
* Added: Language localisation .pot file added.
* Fixed: Cached AeroCheckout pages displayed as option in rules, resolved now.
* Fixed: Item count rule not working for less than and equal to condition, resolved now.
* Fixed: License activation on-boarding process had an issue with WooCommerce admin plugin, resolved now.


= 1.11.1 =
* Improved: Specific product component: Show post count updated as per the count of the selected products.
* Fixed: One PHP notice was coming, fixed.


= 1.11.0 =
* Added: Individual components shortcodes introduced. Use them while making your page using a builder like Elementor.
* Added: Option to redirect to custom thank you page after NextMove rules validated (Even can use NextMove components shortcodes on the custom page).
* Added: New Rule 'UpStroke funnel' is added. Only works if UpStroke WooCommerce One Click Upsell plugin is active.
* Added: New Rule 'AeroCheckout page' is added. Only works if Aero: Custom WooCommerce Checkout Pages plugin is active.


= 1.10.4 =
* Added: New merge tag {{order_total_raw}} added, it gives order total raw value.
* Improved: Download file button text, now changed to Download, earlier it was file name which sometimes breaks the design.
* Fixed: Valid Thank you page condition added to process NextMove functioning further.


= 1.10.3 =
* Added: Allow editing of Thank you page using Beaver builder, NextMove shortcode can be added to any Beaver block.
* Fixed: Order item rule quantity calculation corrected.
* Fixed: Verifying order key with order id on thank you page.


= 1.10.2 =
* Fixed: Thank you page breaking with WooCommerce Germanised 2.2.8 version, resolved now.
* Fixed: Conflict with UpStroke plugin fixed when order is canceled via PayPal.
* Fixed: Video autoplay not working in Google Chrome browser, fixed now.
* Fixed: NextMove link in admin bar showing to normal users, fixed now.
* Fixed: Refunded orders displaying in the search by default, fixed now.


= 1.10.1 =
* Added: Order download template added in order details component.
* Added: Allow editing of Thank you page using Elementor builder, NextMove shortcode can be added to any elementor block.
* Added: Dynamic Coupon and Smart Bribe: A new setting added to hide the component for repeat customers.
* Improved: Upsell products component now displays only "in stock" products.
* Fixed: Error on order details component with WooCommerce 3.3.0 and above version, resolved now.


= 1.10.0 =
* Fixed: PHP files inclusion sometimes caused PHP errors on some servers only, fixed now via changing inclusion syntax.
* Fixed: Recently viewed component: Recently viewed products are not marked in the user local storage session, resolved now.
* Fixed: PHP error was coming on a particular payment gateway, resolved now.
* Fixed: Facebook share URL within smart bribe component not parsing the merge tags, resolved now.
* Fixed: Product saved in rules causing PHP error if the product is removed, resolved now.
* Fixed: Admin thank you pages listing page, data fetching was ignoring pagination, resolved now.
* Fixed: Admin coupon expiry setting wasn't able to save blank value, resolved now.
* Fixed: License not activating with WordPress 5.0.1 or above.


= 1.9.0 =
* Improved: Merge tags label, styling can be done there.
* Improved: Trim and special characters decode function added to Thank you receipt URL, found some plugins modifies it.
* Fixed: Smart Bribe: Unlock coupon wasn't showing the correct coupon code, showing the parent coupon
* Fixed: Social Share: Component wasn't showing even Twitter share is enabled.


= 1.8.1 =
* Fixed: Showing correct coupon after unlock on WooCommerce Thank You page.


= 1.8.0 =
* Added: Compatibilities added to support NextMove Power Pack add-on.
* Added: Customer info component: Has some compatibility issues with WC Germanized plugin, fixed now.
* Improved: Recently viewed component: Set cookie using js now, earlier was done via PHP.
* Improved: Restricted NextMove components calling to require NextMove pages only.
* Improved: Coupons generated by Dynamic Coupon or Smart Bribe component, set 'usage_count' with 0 value.
* Improved: Compatibility added to get 'customer_email' like data on coupon object for less than WC 3.0 versions.
* Improved: Related product component: Default value in WooCommerce is 5, now changed as per user given input.
* Improved: 'get_parent_id' function returning notice on below WC 3.0 version, compatibility added.
* Improved: Order details component: Sometimes total field didn't come at last in a table that results in a small display.
* Fixed: 'order_date' merge tag was returning order's date in GMT0, now as per store timezone.
* Fixed: NextMove generated coupon on Thank You page, expiry re-updated when the Thank You page hit again. Fixed now.


= 1.7.0 =
* Security update: Prohibited direct access.
* Added: Compatible with WooCommerce Points and Rewards plugin.
* Added: htaccess file to block access in supportive xl folders inside uploads.
* Improved: NextMove Component builder UI now RTL compatible.


= 1.6.0 =
* Added: span html element on label in '{{order_meta}}' merge tag.
* Added: RTL CSS added (Thank you page output is RTL compatible).
* Added: Coupon blur intensity field added in global settings.
* Improved: Showing Google maps errors while geocoding address.
* Improved: Thank you page rules if product deleted from system was triggering PHP error, now scenario handled.
* Fixed: Order search in global settings.


= 1.5.0 =
* Added: Active WooCommerce Orders state called in NextMove summary to notify if the store has an active order to see the preview.
* Added: Admin notification when plugin update is available.
* Added: New option 'does not contain at least' added in 'Order item' rule.
* Added: Logging errors in case site faced any PHP Error.
* Added: 'id' attribute added in CMB2 group field to avoid issues with other plugins.
* Added: Force plugin transient removal and optin reset options added in xlplugins -> tools.
* Improved: customer_first_name or customer_last_name merge tags code updated.
* Improved: Order Details component: table tr td or th CSS display table-row and table-cell added as conflict with some plugins.
* Fixed: Force clear transients on Thank you page save, delete, activate or deactivate.


= 1.4.3 =
* Added: New option 'Custom Marker Address' added to Map component.
* Improved: Allow 'autoplay', 'rel', 'showinfo' & 'controls' arguments in Youtube URL in video component.
* Improved: Allow 'autoplay' & 'loop' arguments in Vimeo URL in video component.
* Fixed: SKU call for order thank you URL triggering PHP error, resolved now.
* Fixed: Setup Coupon Expiry date and Coupon value on prepare output data in Smart Bribe or Dynamic Coupons component.


= 1.4.2 =
* Fixed: Fatal Error was throwing on PHP <= 5.4 during plugin activation.


= 1.4.1 =
* Added: WordPress File System API included to increase performance, saving 'thank you' pages data in files.
* Fixed: Preview link resulted in error, now fixed.


= 1.4.0 =
* Fixed: Issue after plugin activation, permalink structure doesn't work and demands reset.
* Added: Append lang parameter in preview form in case of WPML so that correct preview loaded.
* Improved: 'get_posts' call removed from 'plugins_loaded' hook.
* Improved: Appended 'order key' in 'Thank you' page preview, as some plugins requires order_key.
* Added: Flushing object cache on Thank You page save.
* Added: 2 new settings introduced to append order items sku's and order total in Thank You URL.
* Added: Compatibility added for The7 theme, removing sidebar on Thank You page.
* Added: Compatibility with WC Thank You plugin, was breaking 'NextMove Thank You' page.
* Added: Compatibility added for Infinite multi-purpose theme, page-title section removed.
* Added: Compatible with WooCommerce 3.3.2


= 1.3.1 =
* Improved: Thank You pages rules parsing performance improved.
* Added: Compatible with WooCommerce 3.3.1
* Added: Displayed Thank You page title on component builder page.
* Improved: NextMove Status: Permalink reset issue in case of maintenance mode, now fixed.


= 1.3.0 =
* Fixed: Hiding 'Thank You Page Builder' from admin menu on later priority or in case 'Admin Menu Editor' plugin used'.
* Improved: Saving default template to NextMove Thank you pages if not set.
* Improved: opendir, readdir, is_file related all functions removed for fetching components dynamically, as some server have restricted access.
* Fixed: Page loader on Component Builder page sometimes taking a lot of extra time. Now fixed.
* Fixed: 'Customer Order Count' rule wasn't working earlier, now fixed.
* Added: Thank You preview page auto reloads when component builder screen saved.
* Added: Compatible with WooCommerce 3.3


= 1.2.8 =
* Fixed: 4 merge tags 'order_date, order_payment_method, order_ip & customer_provided_note' were not rendering output on WC 3.0 or greater.
* Fixed: Admin component page CSS fixed, conflicting with other plugin.
* Improved: Troubleshoot UI screen merged with NextMove Setting to avoid confusion.
* Improved: Assign default template to NextMove Thank You page on plugin activation.


= 1.2.7 =
* Added: meta no-index for XLWCTY Campaign post type for search engines
* Fixed: Added sslverify=false in permalink status check call.
* Fixed: Resolved Fatal error during checkout for WC < 3.0.


= 1.2.6 =
* Fixed: Permalink status checks to show state invalid even if the page is OK. The issue in JSON parsing, special characters tackled now.
* Fixed: Issue in sustaining component choice made after reloading builder page.


= 1.2.5 =
* Improved: Added 'with_front' false condition in nextmove custom post type during registration.
* Added: Tools Export functionality added for quick debugging.
* Added: New feature for WooCommerce Subscription Upgrade or Downgrade added in Rule Builder.
* Improved: Rule builder now accepts product variations as well in order items rule.
* Added: Join us component: Instagram link added.
* Added: Compatibility with Jetpack Contact form on NextMove thank you page.
* Fixed: VC, Beaver Builder, Divi Builder, Elementor, etc. JS was causing NextMove Thank You page to break. Its fixed.
* Added: Compatibility with PHP 7.2
* Added: Upgraded to CMB2 2.3.0
* Added: Compatibility with Paybox payment gateway plugin.


= 1.2.4 =
* Fixed: Video component video height issue fixed.
* Improved: Caching & Transients added to speed up the page rendering.


= 1.2.3 =
* Fixed: Full Compatibility with WC < 3.0
* Fixed: Compatible with WooCommerce 3.2.5
* Improved: Modifies the way we declare page as xlwcty page, previously on wp hook, moved to parse request.
* Improved: Permalink 'status check' now also validates if nextmove's single post page is opened.
* Improved: Nocache constants fires without filter just like in WC v3.4
* Added: Support for Divi theme, 'Thank You' pages are now Full-width.
* Fixed: Embed tag top padding corrected for latest WordPress version in video component.
* Added: Compatibility with Klarna Payment Gateway.
* Added: Compatibility with 1-Click upsell plugin.


= 1.2.2 =
* Added: Compatible with WordPress 4.9
* Improved: Detection logic of base post type on Thank You page. Earlier it was post now page.
* Added: Script or Iframe like tags support on HTML component.


= 1.2.1 =
* Fixed: Removed http call to check if thank you page is going to 404 not found. Firing on every page earlier.
* Fixed: Removed ajax call over builder page to check permalink current status.


= 1.2.0 =
* Improved: Support for caching plugins, passing nocache header and constants on thank you page load to prevent caching.
* Improved: Replaced history.back() with listing page URL for back icon in builder page.
* Fixed: Backward compatibility issue. Set priority on old existing thank you pages if not set.
* Improved: UX improved on listing page, showing messages in case of errors with solutions.
* Added: Support with woocommerce_is_order_received_page function
* Improved: Additional handling for the google maps to escape with any conflict in map js.
* Fixed: Templates were not getting modified in post edit page.
* Improved: Additional Component added as default component.
* Fixed: JS Error was coming over settings page.
* Fixed: get_coupons function running on all pages, it is limited to required page now.
* Added: 'order-received' query var set on thank you page.
* Added: Compatibility with 'woocommerce-google-adwords-conversion-tracking-tag' plugin.


= 1.1.2 =
* Fixed: Template was getting set to default after activation/deactivation of thank you page.
* Fixed: Image sizes settings were not working as expected.


= 1.1.1 =
* Fixed: google and facebook purchase events firing fixed on order page.
* Added: Compatible with WooCommerce 3.2


= 1.1.0 =
* Fixed: WordPress native transient function replaced with custom transient function. Issue occurred with cache plugins.
* Added: New Component called 'Additional Information' added, it will show content that plugins/themes will usually show on native thank you page.
* Fixed: Missing initialization of WC native shipping and payment classes.
* Added: New settings in customer information & order details component to show extra content above and below respective data.
* Added: New action link to "duplicate" your thank you pages from the listing page.
* Improved: To not push google and facebook events multiple times for an order.
* Added: Billing Email and Phone added in customer Information component.
* Fixed: Add to cart action restricted to simple purchasable product only.


= 1.0.5 =
* Added: New Filter Hooks to manage rules match process by applying before and after hooks.
* Added: Now now and No thanks option in XL notices.
* Added: NextMove mode indication on 'manage component' page whether live or sandbox


= 1.0.4 =
* Fixed: Facebook javascript events code done as when conditions met.
* Improved: Don't fire Facebook and Google Purchase events in Preview mode.


= 1.0.3 =
* Fixed: Compatibility in URL creation with WC 2.6 version.
* Fixed: Order details native table image css issue.
* Fixed: Issue when merge tag parsed more than once and thank you page appear blank.
* Fixed: Google map js deferred loading done
* Fixed: Google map data-icon issue with electro theme
* Fixed: Google map default map marker icon fixed.
* Fixed: Order details component native layout image width issue resolved.
* Added: Compatibility with few premium theme (electro, basel, hcode)
* Added: Custom share url in Smart Bribe & Social Share component
* Added: eCommerce javascript events on Thank you page (Facebook & Google Analytics)
* Fixed: template selection meta getting overridden by wp core when saving post.


= 1.0.2 =
* Added: Background color field in all components.
* Added: A couple of new settings included. To enable 'Free Shipping' on NextMove generated coupons when specific condition met.
* Added: NextMove mode: Live or Sandbox new settings added.
* Fixed: Compatibility fix with WPML.
* Fixed: Compatibility issues with YITH plugins related to chosen jquery.
* Improved: Admin all js compressed.


= 1.0.1 =
* Added: Smart Bribe: A new field share text for fb added.
* Added: A new merge tag '{{coupon_value}}'. Will work with coupon code and smart bribe component.
* Added: Description alignment field added in both smart bribe or social share component.
* Added: coupon_value and coupon_expiry merge tag capability in heading for dynamic coupons and smart bribe component.
* Improved: Front UX of user when coupon generation action initiated.
* Fixed: Date text now support localization 'date_i18n function' added.
* Fixed: Coupon Code: description alignment fixed.
* Improved: on frontend minified css or js files called.
* Fixed: Customer Info component: translation supported on Fxed texts.
* Fixed: Some UI improvements.


= 1.0.0 =
* Added: See how it appears help text added for each component.
* Fixed: Minor corrections for Coupon component.


= 1.0 rc1 =
* Fixed: Some Rule Builder Rules were not working, fixed now.


= 1.0 beta =
* Beta Release