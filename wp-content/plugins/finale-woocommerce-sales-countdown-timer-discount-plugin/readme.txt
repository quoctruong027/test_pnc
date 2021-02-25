=== Finale - WooCommerce Sales Countdown Timer & Discount Plugin ===
Contributors: XLPlugins
Tags: WooCommerce, WooCommerce Sales Countdown, WooCommerce Countdown Timer, WooCommerce Bulk Discount, WooCommerce Recurring Campaigns, WooCommerce Sales Scheduler, WooCommerce Pre Sale, WooCommerce Counter Bar, XLPlugins
Tested up to: 5.3.2
Stable tag: 2.17.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==
Finale lets you create scheduled one time or recurring campaigns. It induces urgency with visual elements such as Countdown Timer and Counter Bar to motivate users to place an order.

== Installation ==
Follow the below steps to install the plugin.
1. Upload the plugin files to the '/wp-content/plugins/finale-woocommerce-sales-countdown-timer-discount-plugin' directory.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to XLPlugins > Finale, to fill the settings

== Change log ==

= 2.17.1 =
* Added: Compatible with WooCommerce 3.9
* Fixed: Product selection in rules is not validating with WPML language, fixed.
* Fixed: Handling to avoid PHP warning in a rare case of cart loading on page load.
* Fixed: Admin JS error with 'ThePlus Elementor addon' plugin, resolved.


= 2.17.0 =
* Added: AeroCheckout page rule is added to show sticky header and footer element on the checkout page.
* Added: Compatibility added with plugin 'WP WebinarSystem Pro', added support of product type 'webinar'.
* Fixed: The campaign wasn't indexing for Finale deal pages addon when 'user is guest' rule is set. Fixed.
* Fixed: Compatibility issues with 'Breeze' plugin, caching not allowing countdown timer to change, fixed.


= 2.16.1 =
* Added: Compatible with WordPress 5.3
* Added: Compatible with WooCommerce 3.8
* Fixed: Expired Finale campaign meta keys in products are removing properly now.


= 2.16.0 =
* Added: Merge tag - new merge tag custom_countdown_timer added.
* Improved: Cron clearing database for inactive meta keys improved.
* Fixed: Incorrect page id fetched in rules when using WPML, resolved now.
* Fixed: JS error on product page in some cases, resolved now.


= 2.15.0 =
* Added: Cleaning Finale campaign product meta keys after campaign is finished.
* Added: Clearing Autoptimised cache after campaign is finished.
* Added: Compatible with multi currency switcher free & paid plugin by Villatheme.
* Improved: Product category rules optimized to speed up the performance.
* Fixed: Incorrect price range display issue resolved on variable products in case, taxes are enabled and entered exclusively.
* Fixed: Clever mega menu plugin's script causing JS conflicts on single Finale campaign page, resolved.
* Fixed: Handled a scenario when 1 cent difference was encountered with taxes enabled and Finale events enabled.
* Fixed: PHP error with OceanWP theme resolved now.


= 2.14.0 =
* Added: Learndash courses, lessons and topics rules added.
* Improved: X-Store theme modified their single product code in recent version, that caused positions mismatch, compatibility updated.
* Fixed: Yith Product Bundle Premium plugin has some code which was contradicting with Finale, resolved.
* Fixed: Sold Inventory not updating properly in case of more than 2 quantities purchased, fixed now.


= 2.13.1 =
* Added: Setting added to reload page when countdown timer hits zero.
* Added: Coupon name and coupon value merge tags added.
* Fixed: Events showing extra price of a product in the cart, fixed.
* Fixed: Incorrect prices displayed in the cart with WooCommerce currency switcher plugin, fixed.
* Fixed: Simple product not showing on sale when discount applied by campaign issue fixed.
* Fixed: Swift framework plugin conflict with finale fixed now.
* Fixed: Incorrect inventory displayed when different variations of the same product are purchased in the same order, resolved now.
* Fixed: Fixed a scenario when regular price in increased using events and originally product wasn't on sale.


= 2.13.0 =
* Added: Compatibility with WooCommerce Product Addon - By nmedia added.
* Added: Counter bar advanced setting new delay added to hide display until xx units left in stock.
* Added: Compatibility with Polylang plugin to support multilingual campaigns added.
* Added: Learndash course product type support added.
* Improved: Coupon display result while searching for coupons increased to 15 for better search results.
* Fixed: Strings were not translatable, resolved now.
* Fixed: JS error due to "massive VC add-on" plugin on edit single campaign page fixed.
* Fixed: Variable mismatched error while activating license with WordPress 5.0.1 or above.


= 2.12.3 =
* Improved: Speed optimised when no campaign is fetched from database.
* Improved: Custom ajax call made instead of wordpress admin ajax for countdown timer's time left.
* Fixed: CSS conflicts with themes on sticky header and footer sub heading.
* Fixed: Specific category page rules code improved.
* Fixed: Coupon usage limit reached error with unlimited usage, now resolved.
* Fixed: PHP error with the 7 theme resolved.


= 2.12.2 =
* Fixed: Woocommerce picker location plugin when active caused some js conflicts, resolved now.
* Fixed: Variable products discount price range now appearing correctly.
* Fixed: Product saved in campaign rules if deleted cause php error, resolved now.
* Fixed: Coupons applying automatically even if user usage limit is over.


= 2.12.1 =
* Added: Compatibility with wp fastest cache plugin, auto cleared cache at certain actions.
* Improved: Campaigns listing page, pagination improved as it was fetching all the posts.
* Fixed: Non-numeric value notice on cart page when the stock is not available fixed.
* Fixed: CMB2 group field meta boxes don't have the ID, which sometimes causes conflicts with other plugins, added.
* Fixed: Sometimes during execution, regular price was not fetched very early, that caused a warning, fixed now.
* Fixed: Price range in variable product not adding taxes.


= 2.12.0 =
* Improved: Saving transient occurring every time when cache is not set (optimization).
* Fixed: Kalyas theme timepicker js conflicting with Finale settings, resolved now.
* Fixed: Sticky footer display when sticky header set as hide.
* Fixed: Traveller theme iconpicker js conflicting with Finale settings, resolved now.


= 2.11.0 =
* Added: Allowing Finale pricing in manual order's creation in admin.
* Added: Finale Evergreen addon compatibility added.
* Added: Pagination added to Finale campaigns listing screen.
* Added: Composite product type compatibility added for Discounts.
* Added: Yith bundle product plugin compatibility added.
* Added: Run finale campaign to guest only, rule added.
* Added: Hide hours setting added for the countdown timer, after enable hrs will only display when available.
* Added: The Counter bar merge tags, allow them to execute on custom text element on a single product page.
* Improved: Countdown timer element is independent of Discounting.
* Improved: Highlight style in countdown timer, fewer gaps now.
* Improved: WCCT_Admin class renamed to XLWCCT_Admin as an old class name is used in WC conversion tracking plugin.
* Improved: Product taxonomies aside attribute in rules, now compatible with Finale Deal pages.
* Improved: All pages rule: now returning true if not single product page.
* Improved: Product is_on_sale checking code improved as sometimes price coming as a string in an array.
* Fixed: Sometimes multiple coupons notices come on a screen, fixed now.
* Fixed: Counter Bar element shortcode using a global $product for price calculations, fixed now.
* Fixed: WooCommerce settings page screen id is made dynamic so that called with every language.


= 2.10.1 =
* Added: Ability to add decimal discounts in Finale Campaign Discounts.
* Added: Product Attributes Taxonomies added under rules.
* Fixed: Non numeric value warning resolved for PHP 7.1+
* Fixed: Issue with Finale discounts when set discount on sale price and sale price is not set on a product.


= 2.10.0 =
* Added: Discount on Regular price and Sale Price options added in a Finale campaign.
* Added: Restricting Finale campaigns to avoid running on admin ajax calls.
* Improved: Condition added When no Finale campaign or no discount in Finale campaign then return given price.
* Improved: Stock rules condition modified to handle a condition when manage stock is off.
* Improved: Stock class: product object handling with 3rd party plugin when product object didn't exist.


= 2.9.0 =
* Added: Compatibility for cart item prices added for WooCommerce currency switcher plugin (author: realmag777).
* Added: Compatibility for cart item prices added for WooCommerce Multi currency plugin (author:villatheme).
* Added: Restrict Finale campaigns to run on various admin ajax calls.
* Improved: Condition added When no Finale campaign or no discount in Finale campaign then return given price.


= 2.8.0 =
* Improved: Handling of Finale prices over the cart to support extra product addon related plugins.
* Improved: Restrict Finale campaign fields to load on every dashboard page and optimize coupon and page lookup.
* Improved: Added handling when product parent doesn't set proper (i.e. 0 or wrong value which is not a product) by any dynamic product creation plugins, that was leading to fatal error.


= 2.7.0 =
* Security update: Prohibited direct access.
* Added: htaccess file to block access in supportive xl folders inside uploads.


= 2.6.0 =
* Added: 'Post Type' a new rule added in the Pages head.
* Added: Admin notice when 'counter bar' in enabled & Finale 'inventory' is disabled in a Finale campaign.
* Added: Reducing back the Finale campaign 'sold unit' on Order's cancellation.
* Improved: XLCore loaded early in execution, to avoid PHP errors caused by feeds plugins.
* Improved: Helping text below fields for more clarity.
* Improved: Cache layer added in Finale rules execution.
* Improved: Not setting deals, goals on sticky header/ footer calls, results in increased performance.
* Fixed: Issue detected with ALI Drop Shipping Woo plugin, instead of returning an array on filter hook returning a blank value.
* Fixed: Fetching Finale campaigns on wc_get_product call causing PHP errors when wc_get_product called on non-products.
* Fixed: WooCommerce Multilingual plugin in recent release v4.3 modified their code that caused PHP error in Finale. Fixed now.
* Fixed: Increasing Finale campaign sold units hooked later in the code, to avoid the un-necessary sold unit increase for pending orders.


= 2.5.2 =
* Fixed: Pages rules are only for Sticky header or footer. Condition modified.


= 2.5.1 =
* Added: Finale global settings page created. Added 3 settings ('Switch to builder's specific positions if product page built using a builder', 'Hide days in countdown timer if 0' & 'Hide multiple countdown timers per product').
* Fixed: Sale Price issue in case Finale Discounting was initially off and set through Finale Events.
* Fixed: Hide Finale in admin bar if user don't have 'manage_woocommerce' capability.


= 2.5.0 =
* Fixed: Variations prices are not coming with the discount in the price HTML, occurred after WC 3.4 update.
* Fixed: Code optimized to check for valid Finale campaign coupon during checkout.
* Improved: Code optimization done in Product rules.
* Improved: Restrict display of countdown timer to one on the single product page if multiple exist.
* Improved: wp_cache_flush function calls removed and code optimized to support caching.


= 2.4.6 =
* Fixed: exclude_dates attribute in {{current_date}} like merge tags were not working, now fixed.
* Fixed: Issue when multiple campaigns exist for coupons then we are terminating campaign running check for just one (first) coupon in the loop.
* Fixed: Force clear transients on Finale campaign save, delete, activate or deactivate.


= 2.4.5 =
* Fixed: Sticky Header or Footer is not showing when admin is not logged in.
* Fixed: Countdown Timer or Counter Bar on Finale Deal Pages detached from Campaign's respective element.
* Improved: Sticky Header or Footer status in WordPress admin bar 'Finale' node corrected.


= 2.4.4 =
* Fixed: XL core was missing in the previous version 2.4.3.


= 2.4.3 =
* Fixed: PHP fatal error vulnerability when checking compatible product types.
* Fixed: Sticky Headers and Footer elements were not getting hidden once closes for a provided time, a cookie was misconfigured.
* Fixed: Re-stored campaign listing view to orderby date but not the priority.
* Fixed: A scheduled campaign getting marked as finished while setting up data & further getting processed as a finished campaign
* Fixed: Coupons attached to campaigns are not getting expired even when campaign is not running for a new session.
* Added: Filter hook to modify Product sale start date and end date based on active Finale Campaign.

= 2.4.2 =
* Added: Filer hook to force data setup for a product. Finale Deal page has a condition where product data demands to get reset. This fix will provide a way to handle this issue.


= 2.4.1 =
* Fixed: A php warning was throwing on products grids, issue triggered by the previous update 2.4.0. 
* Improved: Campaign ordering updating in all the admin screens. Now order-by "campaign priority", previously order by "date created".  


= 2.4.0 =
* Fixed: Initiated Finale campaigns on wc_get_product function for a product.
* Added: Compatibility with Learndash plugin 'course' product type.
* Improved: Hide Finale link in admin bar for non admin users.
* Added: Product prices merge tags.
* Fixed: Porto theme modified their price calling code, Finale compatibility updated.bile.
* Improved: Reducing Finale inventory after sale and checking back again in 2 mins if order failed or canceled.
* Added: New field added to exclude product types to alter 'add to cart' text on shop/ grid.
* Added: Flushing object cache when Finale Campaign modified.


= 2.3.2 =
* Fixed: Reducing Finale inventory in-case order status pending-payment, failed or cancelled.
* Added: Shortcodes for finale discount prices.
* Added  Compatibility with WooCommerce Google Product Feed Plugin.


= 2.3.1 =
* Fixed: Single Campaign admin page, showing status 'Deactivated' in case campaign is Deactivated in quick view.
* Added: Compatible with Astra theme.
* Improved: Compatibility with WooCommerce Dynamic Pricing and discount plugin.
* Improved: Compatibility With Aelia WooCommerce Cache Handler plugin.
* Fixed: Issue in unflagging cart variable, wrong mini cart hook placed.
* Fixed: Issue with printing shortcode for the inventory, checking for the global product which is not available everytime.
* Fixed: Prevents multiple data setup for same product more than once, resulting issues in inventory setup.
* Fixed: Handling for the order metabox when campaigns no longer exists or deleted.


= 2.3.0 =
* Added: Compatibility of Finale discounts with Variable Subscription Products.
* Fixed: Issue of product meta not setting up properly when inventory custom qty is 0.
* Improved: Activate or De-activate campaign button added on single campaign page in sidebar.
* Added: Compatible with WooCommerce 3.3 and 3.3.1
* Added: 'On Backorder' stock status added in rules.
* Improved: 'All Pages' rule now considering all site pages and would work for sticky header or footer.
* Improved: 'Specific page' rule is-not condition would run on complete site pages except on selected pages.
* Added: Compatibility of Finale campaigns with Flatsome theme quick view feature.
* Added: Compatibility of Finale campaigns inventory with WooCommerce Subscription products.


= 2.2.2 =
* Added: Language Translation support: POT file placed in under languages folder.
* Removed: Upsells notifications are removed.


= 2.2.1 =
* Fixed: 'Add to Cart' button text on grid is changing for product types: simple, external and subscription.
* Fixed: 'Add to Cart' button text on single product page is changing for product types: simple, variable, external, grouped, bundle, subscription & variable-subscription.
* Fixed: PHP fatal error on cart when WooCommerce TM Extra Product Options Plugin is activated.
* Added: Compatibility with 'Min and Max Quantity for WooCommerce' plugin.
* Added: Campaign Pause period in minutes field added.
* Added: Compatibility with Aelia Currency Switcher Addon.


= 2.2.0 =
* Fixed: 'All Product Category Pages' rule now saving in database.
* Improved: Added google index off the tag on WCCT Header info data to not index for Google Search Console.
* Improved: Display Countdown Timer upfront with 00 days 00 hrs 00 mins until an actual left time came from ajax response.
* Improved: Query optimisation for variable products. Removed calling of wc_get_product function for variations while creating variable product price range.
* Fixed: 'Add to Cart' button text field was changing button text of variable products too, now fixed.
* Added: meta no-index for WCCT Campaign post type for search engines.
* Added: Setting to not run inventory campaigns over out of stock products are now taken care at variation level. Initially, it was working for the variable product as a whole.
* Fixed: When Finale modifies stock attributes like stock status and quantity using filters, it was getting saved in the actual product meta after checkout & hence overriding the product's actual state. We prevent making any change in the database by removing our filters not to run while stock getting reduced.
* Improved: Queries to get and set product meta for their initial stock state only recorded once per campaign run. Reduced the update post meta queries.
* Improved: All the get product meta queries get cached using XL_Cache for the inventory part.
* Improved: Do not match rules that belongs to pages when applying discounts/inventory/coupons/ on the products.
* Fixed: Rule for page is shop page was not working correctly.


= 2.1.4 =
* Added: Compatibility with php 7.2
* Added: Upgraded to CMB2 2.3.0
* Fixed: During Campaign duplicate, remove 'campaign_hash_id' meta key, otherwise email timer won't work.
* Added: Compatible with Techmarket theme.
* Improved: Campaign Start and End Date merge tags are now support format as arguments.


= 2.1.3.1 =
* Fixed: Width issue on Finale Campaigns listing page.
* Added: WordPress native blog page inclusion in Finale Rules.


= 2.1.3 =
* Fixed: Prevent vulnerability with infinite looping in price filters.
* Fixed: Removed php error when WooCommerce Membership plugin's discount & Finale Discount both are enabled.
* Added: Compatibility with the WooCommerce Deposit plugin.
* Fixed: Coupons in the finale campaigns were getting applied even if the respective campaign is scheduled.
* Fixed: Removed delay in the click of a button in sticky header/footer.
* Added: Compatible with Tucson theme.


= 2.1.2 =
* Added: New rules added for Product Category Pages, Term Pages & All Pages.
* Fixed: Shortcode for the inventory bar stopped working, validating global product which is not required.
* Fixed: Sometimes there was a fatal error when fetching campaign meta due to Post object found instead of id.
* Fixed: Coupons Custom error message modifier function was not working correctly. Returning boolean true in some case, error expected.


= 2.1.1 =
* Improved: Events for inventory now supports percentage values.
* Fixed: Non-numeric value PHP error resolved.
* Fixed: WC hold stock settings are impacting finale behavior and started considering hold stock while validating cart. Fixed previously.


= 2.1.0 =
* Fixed: PHP fatal error was coming on Handsome Checkout plugin admin page, now fixed.
* Added: New feature for custom inventory by range.
* Improved: Displaying 'Expires on Date Time' in campaign listing view for one-time campaigns.
* Improved: Optimized Some queries(wp query and get queries) that were duplicating, now cached.
* Added: Compatibility with WooCommerce TM Extra Product Options Plugin.
* Improved: Sticky header and footer are now more mobile optimized. Reduced extra spacing and font sizes, so that mobile standards meet.
* Added: Compatible with Boxshop theme.
* Fixed: Events Units was not working correctly if existing stock is selected in campaign settings.
* Fixed: Taking over the charge for the WooCoommerce setting for stock hold in case of finale is active, this critical bug was preventing users from checkout successfully.
* Fixed: Coupon success message was not getting removed once item + coupon gets removed.
* Fixed: Restricted Finale campaigns to run in the backend, causing change for price and inventory in product listing in the backend.


= 2.0.6  =
* Fixed: Sticky header close doesn't work while caching enabled.


= 2.0.5  =
* Fixed: Debug print was left in the event discount class.


= 2.0.4  =
* Fixed: Events rule for discount getting affected by the other activated campaigns and hence not working in some cases.
* Fixed: X-Store theme compatibility, there was a php warning showing because of a typo left.
* Added: Full Compatibility with qTranslateX plugin.
* Added: Full Compatibility with WooCommerce Currency Switcher By realmag777
* Fixed: Css fix for the admin area, tabs conflict with other plugins.


= 2.0.3 =
* Added: Compatible with WordPress 4.9
* Fixed: Countdown Timer display delay setting wasn't affecting the timer, now fixed.
* Improved: Reduced number of requests on frontend. Minified and Combined public css and js files.
* Improved: Sticky Footer close icon position changed to top and size increased a little.
* Fixed: Countdown Timer not re-initiating in case of cache plugins.
* Added: Campaign start date and end date merge tags added.


= 2.0.2 =
* Improved: Sticky Footer mobile text alignment always centered now.
* Fixed: Timer is getting refreshed on wc_fragment_refreshed function, sometimes results in starting back again.
* Added: Single product page positions compatibility with following themes (Oceanwp, Basel, Enfold, Porto, Revo, Aurum, Savoy, Sober).
* Fixed: warnings coming on wcct_merge_tags function due to typecasting, corrected now
* Fixed: Sticky header or footer auto appear after closed in case of caching. Fixed using cookie via JS
* Added: admin css for rules as some themes override calling of chosen.
* Fixed: PHP notice was throwing while applying coupon.
* Improved: Data setup improved to be compatible for the case when not all variations loaded on load of page but by AJAX.


= 2.0.1 =
* Improved: Sticky Header mobile text alignment always centered now.
* Improved: Countdown Timers 2px reduced from width, height, timer text & label text on mobile only
* Improved: Coupon's messages handling to not show same message more then once on a page.
* Improved: removed calling of 'display_page_options' function from cmb2 functions file.


= 2.0.0 =
* Added: Campaign duplicator functionality added.
* Improved: License updation code streamlined.
* Improved: XL Core to latest.
* Added: Compatibility with index campaign addon.
* Fixed: 'Add to Cart button Hide' during or after campaigns actions fixed for variable products.
* Fixed: Counter bar text wasn't displaying if no counter_bar merge tag was added.
* Fixed: PHP Notice threw on pages from 'product category' rule.
* Fixed: Events section - Sold units from & to range; if 'to' field left empty, than causing error.
* Fixed: Stock quantity rule has issue with variable products.
* Improved: Shortcodes to not render content when campaign is deactivated.
* Improved: Optimization in countdown timer refresh logic to prevent multiple calls for one single campaign.
* Added: Compatibility with WooCommerce Products Bundle Addons.
* Added: 'Hide on mobile, tablet & desktop' settings added for sticky header and footer.
* Added: 'Sub Headline hide on mobile' setting added for sticky header and footer.
* Added: 'Disable display of Countdown Timer' setting added for sticky header and footer.
* Added: 'Headline & Sub headline alignment' settings added for sticky header and footer.


= 1.3.1 =
* Fixed: Sticky Footer is not sliding up after last update, left a typo in the condition after resetting timers.
* Added: Compatibility with TheGem Theme


= 1.3 =
* Fixed: Events were getting processed of expired campaigns, creating issues with discounting.
* Added: ShortCode Attribute count added on [finale_campaign_grid], to limit products on the section.
* Fixed: Critical Bug in recurring campaign, Campaign start and end timings were not getting calculated correctly.
* Improved: Changes For Support With Caching plugins, Ajax data refresh of timers running over page to get the current state.
* Added: Settings added under coupons for better control over coupon success message visibility.

= 1.2.2 =

* Fixed: Issues with multiple inventory bars in one product page.
* Fixed: Campaign priority is not working.
* Improved: Holding transients to not work for now, creating issues with some caching plugin.
* Fixed: Coupons showing "invalid coupons" in backend campaign listing.
* Fixed: User session handling in campaign coupons when campaign meta state changes.
* Added: Filter hook 'wcct_restrict_coupon_notice' to restrict coupon notice on pages.
* Fixed: Counter Bar missing in info on top toolbar link.

= 1.2.1 =

* Added: WPML Compatibility against transients used in plugin.
* Added: Theme Compatibility with oxygen theme.
* Added: Minutes field added in recurring duration settings. This setting will allow users to set recurring duration in minutes too.
* Fixed: Handling for the non-existing coupons behavior with finale.
* Fixed: Php notice while applying coupon from header/footer
* Fixed: CSS fix in date-picker css for rule builder
* Added: Minified JS for admin screens added.
* Fixed: Critial bug of javascript conflict with chosen JS resolved.
* Improvement: Changes in filter calling of element over grids so that it would read enable/disable settings of a campaign.

= 1.2.0 =

* Fixed: Filter "wcct_skip_discounts" was not compatible with all the cases of variation products between WooCommerce's versions.
* Fixed: Issue in license activation post data on some servers.
* Fixed: PHP notice during checkout process for missing index.
* Added: Additional shortcode added to show products with deals on using our plugin.
* Improvement: handle merge tag percentage show for using ceil instead of number format.

= 1.1.9 =
* Fixed: wcct_init function was added for debug purpose, now removed.
* Fixed: sale_date_from and sale_sale_to modifiers removed, previously added in support with WooCommerce Sales Triggers by XLPlugins.

= 1.1.8 =
* Fixed: custom the_content hook missing do_shortcode

= 1.1.7 =
* Fixed: the_content filter hook replaced with wcct_the_content filter hook
* Fixed: Critical bug with coupon, not allowing users to add those coupons which are not associated with any campaign.
* Added: "NEW" settings added under advanced tab to modify timer labels in {{countdown_timer}} merge tag.

= 1.1.6 =
* Fixed: CMB2 Coupons select, not populating selections that were searched and selected.
* Fixed: Handling for conflict in chosen-js for all admin screens.

= 1.1.5 =
* Added: Merchandiser theme support added.
* Fixed: Critical bug with coupons, not allowing user to add coupon in cart if not used in any of finale campaign.

= 1.1.4 =
* Added: Compatibility with WPML.
* Added: Betheme, Eva theme & Wowmall theme support added.
* Added: Filter hook to skip discounts later by campaign or product attribute.
* Fixed: Issue with match_group hook, was not unsetting the flag after hook result.
* Fixed: One coupon in multiple campaigns with different campaign status creating bug in coupons.
* Fixed: Date interval calculation was not calculating total days left for admin area.
* Fixed: Handling for the case where discount was not getting applied due to expired event enable campaign.

= 1.1.3 =
* Fixed: Info notice printing over head, output buffering issue.

= 1.1.2 =
* Fixed: Removed product is_in_stock checking from 'Add to cart' button text.
* Fixed: wcst variable related notices

= 1.1.1 =
* Added: New Position "None" added for shortcode and hooks purposes.
* Fixed: Shortcode now to be work without campaign ID.
* Fixed: Custom Text box was not applying the_content filter, was unable to render merge tags output.
* Fixed: Improvement in shortcode help description, popup added for detailed info.

= 1.1.0 =

* Added: Introduced Coupons to work with Finale Campaigns, Fetched dynamic coupons to choosed from and its settings.
* Added: New and Improved backend setting UI and UX, stuffed with help links and discriptions and use cases in modal box.
* Added: Discounts New option "advanced" , provided repeatable option to set up disocouting blocks.
* Added: Inventory New Option "advanced" , provided repeatable options to set up custom inventory rules based on your current inventory.
* Added: New Element "Custom Box", backed by customizations to put your text over page using any positions.
* Added: New ShortCode For "Custom Box".
* Added: New option "delay" in Sticky Footer and Header to show element after specific settings to page load.
* Added: Quick View Meta box to show current status of the campaign post to let user know about his settings in just a single view.
* Added: Finale Toolbar link to give more accurate and necessary info about the running campaigns.
* Added: New meta-box in shop_order (edit order page) to show admin stats about finale campaigns running while this order.
* Fixed: Improvement in after campaign saved actions, used schedueled event to maintain campaign state on campaign save.
* Fixed: Improvement in shortcode rendering function to work with new args such as "debug" & "skip_rules" more efficiently.
* Fixed: Improvement in shortcode callbacks to handle and print error to let user know about the reason why shortcode fails.
* Fixed: Better data localized on the front-end, for debugging purposes.
* Fixed: Abstract function to show countdown timer on front end without the conflict with any other HTML or element.
* Fixed: merge tag {{countdown_timer}} now have the ability to be used inside any element, except countdown timer itself.
* Fixed: Critical Fix in cart item stock check, handled WC version and checks for every condition that might lead to failure.
* Fixed: handling with WooCommerce stock hold duration setting to not work when product is not managing stock.
* Fixed: Handling with `wp_kses_post` to sometime prevent rendering of data attr that lead to break the timers.
* Fixed: Optimized start page for xl, to make sure no Database query fires when choosing XL core.
* Fixed: More clean, robust and flexible data strucure that helps us in debugging.
* Fixed: Handling for add_to_cart text, taking first running campaigns data before.
* Fixed: Improvement in way actions during and after campaigns were getting called, respects campaign order now.

= 1.0.8 =
* Fixed: Critical Issue with persistent cache.
* Fixed: Removal of cache when campaign is saved in backend.

= 1.0.7 =
* Fixed: Critical Issue, cart_item_stock validation to check is_managing_stock before going to check session hold stock.
* Fixed: Do not show counter bar if product is found out of stock just before rendering.
* Fixed: Marking product is_on_sale if discount is on for all WooCommerce Versions.
* Fixed: `woocommerce_variation_is_in_stock` is now works for all WooCommerce Versions.

= 1.0.6 =
* Fixed: Discount Events handled for the all event rules to work within the rule bounds only.

= 1.0.5 =
* Fixed: Discount Events handling in case of 'units sold' type.

= 1.0.4 =
* Added: Settings tabs are now sustained, so user will be switched to the tab he was last working on.
* Fixed: Critical bug when user's time is greater than end time of campaign. Making browser window reloading again and again after page load.
* Added: Improvements in post box content, added user's time, timezone and campaign state.
* Added: debug param in shortcode to show error when system fails to show any output for shortcode.
* Fixed: Prevent submit button to be clicked when rules are setting up.

= 1.0.3 =
* Fixed: Critical Issue with variation product ranges when taxes are on.
* Fixed: Issue with variation product inventory goal is not working when product variable is not managing stock.
* Added: Better Logging for the discount,stock and other classes.
* Fixed: Hard logging using query param to help us troubleshoot the issue.

= 1.0.2 =
* Removed XL Optin modal code, not in use

= 1.0.1 =
* Removed setting of global $product on is_in_stock filter hook
* Handling on 'backorder' and 'is_in_stock' hook for WC less than 3.0 versions
* Manage stock status of/off handling for >= WC 3.0

= 1.0.0 =
* Public Release