=== WP Optin Wheel Pro ===
Contributors: studiowombat,maartenbelmans
Tags: optin, popup, wheel, fortune, gamification
Requires at least: 3.7
Tested up to: 5.5.3
Requires PHP: 5.6.0
Stable tag: 3.4.2

== Description ==

The pro version of WP Optin Wheel.

* [&raquo; More info](https://studiowombat.com/plugin/wp-optin-wheel/)
* [&raquo; Demos](http://demo.studiowombat.com/wheel-of-fortune-demo/)
* [&raquo; Documentation](https://studiowombat.com/kb-category/wp-optin-wheel/)

== Changelog ==

= version 3.4.2 =
 * Fix: fixed issue in the new SendInBlue API where users would be added without a list.

= version 3.4.1 =
 * Fix: fixed an issue with searching for product categories in the backend.
 * Update: verify new WP version compatibility.

= version 3.4.0 =
 * Update: SendInBlue Api v2 will be deprecated soon. This release contains options to use their active v3 of the API.
 * Fix: some minor fixes.

= version 3.3.9 =
 * Fix: fixed Mailchimp Birthday field type issues.

= version 3.3.8 =
 * Added: ability to add more dynamic codes in the emails sent by our plugin.
 * Update: removed ChatChamp integration as their service has discontinued for a while now.
 * Fix: removed "has_filter" function where it wasn't necessary, effectively reducing code.

= version 3.3.7 =
 * Added: new frontend hook so developers can edit form fields being created.
 * Added: class and ID attributes to the form elements so they can be targeted with CSS or JS.
 * Fix: added "SameSite" attribute to cookies to comply with new browser features.
 * Other: updated minimum PHP version to 5.6. Backward compatibility guaranteed with 3 minor releases.

= version 3.3.6 =
 * Fix: fix WordPress database table not being created on first run.

= version 3.3.5 =
 * Fix: fixed a bug when editing your wheel and changing slices from a low number to a high number in the backend.
 * Fix: the wheel had tiny gap between slices, which was notable when all slices were light in color. This is now fixed.

= version 3.3.4 =
 * Added: new options to show on WooCommerce pages: "order received" and "view order" page.
 * Fix: fixed an admin issue when uploading background images don't have a thumbnail.
 * Dev: added developer hook for the coupon bar.

= version 3.3.3 =
 * Fix: fixed an issue with Mailchimp groups not loading on 1st try when editing a wheel.
 * Fix: fixed some minor HTML changes.
 * Fix: fixed an issue with your wheel's email settings sometimes disappearing in the backend.

= version 3.3.2 =
 * Update: added segment ID (the number of the slice won/lost) to data sent to Zapier.
 * Update: verified Woo 4+ compatibility.
 * Fix: added "usage_count" to coupons. This should fix some coupons returning "expired" when they shouldn't.

= version 3.3.1=
 * Added: some more hooks to extend WooCommerce generated coupons.
 * Update: better styling for using images inside slices.
 * Update: primary email field is now type "email" for better mobile keyboard support.
 * Fix: removed a PHP warning in admin screen.
 * Fix: removed a PHP warning in the admin when connecting to ConvertKit.
 * Fix: fixed an issue with the GetResponse API.

= version 3.3.0 =
 * Update: you can now add variations to "include products" or "exclude products" in the coupon settings.
 * Update: allow more HTML tags in certain settings.

= version 3.2.9 =
 * Update: better admin UI to create wheels with the shortcode.
 * Update: added a few more filters so developers can extend even more parts of the plugin.
 * Fix: fixed an issue where custom HTML prizes weren't saved to the database.
 * Fix: fixed an issue with the notification email not being sent if you don't fill out a message & subject.
 * Fix: fixed a filter to change the "from" email address.

= version 3.2.8 =
 * Fix: fixed an issue with some coupon bars.

= version 3.2.7 =
 * Fix: fixed a javascript error in backend.
 * Update: Add filters to IP checking so developers can extend it when needed.

= version 3.2.6 =
 * Fix: fixed issue where the coupon bar would show if you won a prize that is not a coupon.
 * Fix: fixed issue with fetching custom fields in ConvertKit.
 * Update: added min/max values to some admin settings.

= version 3.2.5 =
 * Added: 2 new options: "hide on desktop" and "hide on tablet".
 * Added: filters to edit data sent to Zapier.
 * Update: refactor frontend javascript and save 3.5kb in size.
 * Update: added Woo compatibility tags.


= version 3.2.4 =
 * Added: new integration: Drip.
 * Fix: fixed a bug with list fields containing non-UTF8 characters.
 * Fix: Mailchimp's international phone number field now also supports proper masking.
 * Fix: Fix to display the wheel on product pages from a certain product category.

= version 3.2.3 =
 * Fix: fixed a bug where you couldn't select a widget type in the settings page.
 * Fix: fixed a bug in the settings page where you couldn't select suboptions of the "when to show" setting.
 * Fix: fixed a bug with ActiveCampaign email lists not containing custom fields.
 * Fix: WP_Error handling with all email marketing providers.

= version 3.2.2 =
 * Added: new layout option to disable the wheel shadows.
 * Added: new option to disable the wheel handles.
 * Added: new widget: "mini wheel".
 * Added: added filter to skip sending analytics from the wheel to your backend.
 This can be handy if you want to minimize admin-ajax traffic.
 * Fix: Fixed CSS in footer issue.
 * Fix: Fixed an issue where coupon bar wasn't available on all pages.
 * Fix: Fixed an issue with adding coupons automatically to the cart when it's empty.

= version 3.2.1 =
 * Fix: When editing your wheel, the custom colors disappeared (if any). This is now fixed.
 * Fix: fixed a bug with the background setting and custom uploaded backgrounds.
 * Fix: fixed a typo in the custom background CSS.
 * Update: improved performance of the admin. This is to be continued.
 * Update: replace unnecessary translation function _e() with echo.

= version 3.2.0 =
 * Fix: fixed a bug with the backend setting for a fixed cart coupon amount.
 * Fix: fixed a bug where email settings are shown in the backend when they shouldn't.
 * Fix: fixed a rare "flash of unstyled content" bug where the wheel would be visible a fraction of a second during page load.
 * Fix: fixed a bug with the "tick" sound.
 * Update: added some more useful error messages in the backend.
 * Update: better script & style loading on the frontend. It's more performant and keeps page sizes smaller.
 * Update: automatically disable the free plugin when it's still installed. This prevents a few possible errors.
 * Added: the setting "when to show the wheel" is now multi-select so you can define more than 1 condition when to show the wheel.
 * Added: you can now add up to 24 slices instead of 12.
 * Added: more email settings. Each specific slice type now has an email setting.
 * Added: possibility to send an email to yourself when someone turned the wheel.
 * Added: possibility to show confetti pop when a player won.
 * Added: you can now easily add your own backgrounds to the wheel.

= version 3.1.0 =
 * Update: added these Woo coupon settings: coupon type (fixed value or percentage), minimum spend, maximum spend, include/exclude for sales items.
 * Update: adding images to slices is now possible by including <img src="..."/> in the slice value.

= version 3.0.9 =
 * Fix: fixed a bug for finding Woo products with Arabic names.
 * Fix: converted all text in the admin dashboard to translateable strings.
 * Update: allow ID's to be added for 'on click' elements.

= version 3.0.8 =
 * Fix: fixed a bug with "is_product" function for WooCommerce.

= version 3.0.7 =
 * Added: zip code support for Mailchimp.
 * Fix: fixed a bug for input masking of fields of type: phone, date and birthday.
 * Fix: fixed a bug with the "All product pages" setting.
 * Update: updated email feature.

= version 3.0.6 =
 * Added: option for double opt-in in MailChimp.
 * Added: SendInBlue support.
 * Added: option to automatically add Woo coupon to cart.
 * Added: Developer-friendly WooCommerce coupons.
 * Fixed: Compatibility for PHP 7.2.
 * Update: enhanced HTML emailing.

= version 3.0.5 =
 * Fix: fixed a bug with the 'click' sound.
 * Fix: fixed a bug with replays where a prize would be won but the segment shows 'no prize'.

= version 3.0.4 =
 * Added: tick sound when playing (as an option).
 * Added: new integration: Newsletter2Go.

= version 3.0.3 =
 * Added: New tool: ChatFuel (for Facebook marketing).
 * Enhancement: You can now edit the chances to 1/4th of a percentage.

= version 3.0.2 =
 * Fix: fixed a bug with special characters being replaced after updating.
 * Fix: fixed a bug with woocommerce coupon shown on screen.
 * Fix: fixed a bug where some settings wouldn't update when clicking save.

= version 3.0.1 (major update - check your wheel & read upgrade notice) =

 * Added: new design features. You can now design any theme you want!
 * Added: ability to upload logo in the center of the wheel.
 * Added: Remarkety integration.
 * Added: Convertkit integration.
 * Added: tools for GDPR compliance.
 * Fixed: several small backend UI fixes.
 * Fix: small fix with handling HTML in slices in the upgrade routine.

= version 2.1.2 =
 * Fix: fix for coupons handed out with a life span of less than 1 hour.
 * Fix: fixed an issue with incorrectly identifying the woo shop page.

= version 2.1.1 =
 * Fix: fixed an issue in rare cases where winning segments would return null.

= version 2.1.0 (major release, verify wheels after updating) =
 * Fix: various small bugfixes and enhancements in the admin dashboard.
 * Added: Facebook Messenger support through ChatChamp.
 * Added: security option to log IP addresses for better security.
 * Added: better logging options.
 * Added: support for Mailster.
 * Fix: bugfix for Klaviyo.

= version 2.0.8 =
 * Bugfix: fixed a bug for standalone wheels.

= version 2.0.7 =
 * Added: added a 'consent checkbox' field in the form builder for GDPR.
 * Added: added a new occurance option 'none', so you can only show widgets, or program your own occurance.
 * Fix: fixed a bug with 'show on pages' setting in the backend.

= version 2.0.6 =
 * Added: support for Klaviyo.
 * Added: limit prizes: allow prizes to be won only X amount of times.
 * Fix: fixed an issue with converting string to int.

= version 2.0.5 =
 * Added: Free shipping option.
 * Added: Show the wheel only for logged in or logged out users.
 * Update: Woo coupon percentage is now a number field instead of a dropdown (so more options for you).
 * Fix: fixed coupon bar issue
 * Fix: fixed issue with the 'show on pages' setting in the backend.
 * Update: added some more developer hooks.

= version 2.0.4 =
 * Fixed: fix in Christmas theme.

= version 2.0.3 =
 * Added: new slice type: Text/HTML.
 * Updated: more developer-friendliness.

= version 2.0.2 =
 * Added: WooCommerce coupon bar for urgency.
 * Fixed: Minor MailChimp bugfix.

= version 2.0.1 =
 * Fix: backward compatibility bug.

= version 2.0.0 (WARNING: big update - test & verify) =
 * Added: 6 new themes.
 * Added: 8 new backgrounds.
 * Update: updated UI so it's more user-friendly to add a wheel.
 * Update: your design changes are visible in a realtime live-preview.
 * Update: Formbuilder supports dropdown fields for all autoresponders.
 * Update: Mailchimp formbuilder supports text, dropdown, date, birthday, number and phone number types.
 * Added: GetResponse integration.
 * Added: MailerLite integration.
 * Added: Zero BS CRM integration (via separate connector).
 * Added: WordPress database integration. Collect opt-ins straight to your own database.
 * Added: AWeber integration (via separate connector).
 * Update: ability to let users play without filling out an opt-in form (handy if you want to give prizes only).
 * Update: allow duplicate plays (users can play again, even when they already won).
 * Update: Updated form builder.
 * Added: added 'bubble' and 'pull-out' widgets ( = clickable buttons ) to open a wheel.
 * Added: new type of slice: redirect. Redirect users to a page, rather than show a prize.
 * Update: made the plugin expandable and developer-friendly.
 * Fix: fixed a bug in email templates.
 * Fix: some small theme CSS fixes.
 * And more ...

= version 1.2.6 =
 * Fix: fixed a bug with multiple excluded categories in Woo coupons.
 * Update: extension abilities for developers.
 * Update: UI changes.

= version 1.2.5 =
 * Added: winter/christmas theme.
 * Fixed: various small bugfixes.

= version 1.2.4 =
 * [Version release notes](https://studiowombat.com/wp-optin-wheel-1-2-4-release-notes/)
 * Added: anti-cheat engine.
 * Update: added exit-intent alike behavior on mobile.

= version 1.2.3 =
 * Added: support for MailChimp's birthday field.
 * Update: added an upper-right close icon, in favor of the lower right closing sentence.

= version 1.2.2 =
 * Update: include RTL language support.
 * Update: improved email sending behind the scenes.
 * Fix: fixed a bug with Javascript events.

= version 1.2.1 =
 * Added javascript events to hook into.
 * Allow some HTML in the segment label.
 * Added product categories to 'show on pages' option.

= version 1.2.0 =
 * Added ability to send coupon codes via email as opposed to showing on screen.
 * Added webhooks support for Zapier etc.

= version 1.1.9 =
 * Added Mailchimp groups.

= version 1.1.8 =
 * Added more content/design settings.
 * Added ability to show the wheel on every page refresh.

= version 1.1.7 =
 * Mobile UI fixes.
 * Bugfix in CSS of the standalone wheel.

= version 1.1.6 =
 * Added extra coupon options for WooCommerce.
 * Bugfix in iOS Safari 11.

= version 1.1.5 =
 * Added extra coupon options.
 * Enhanced iOS experience.
 * Small UI improvements on mobile.

= version 1.1.4 =
 * Added: added a new theme: Halloween.
 * Added: added a new theme: Black & White.
 * Fix: better 'ticking' animation when the wheel is turning.
 * Update: minor changes to the view on mobile.

= version 1.1.3 =
 * Added: added form builder. You can now add more than just an email field to the game's form.
 * Added: added demo content to get you started faster.
 * Added: UI improvements on the admin page.
 * Fix: Fixed an error for Mac users.
 * Fix: Fixed a bug with the email list dropdown field on the admin page.

= version 1.1.2 =
 * Added: added WPML compatiblity.

= version 1.1.1 =
 * Fix: small bugfix for WP versions before 4.7.

= version 1.1.0 =
 * Added: added a shortcode [wof_wheel] so you can display the game on any page.

= version 1.0.9 =
 * Fix: Added a noop for window.waitForFinalEvent as some plugins may interfere with tinymce in the admin.

= version 1.0.8 =
 * Fix: CSS changes in the admin screen to support older websites.

= version 1.0.7 =
 * Added: added 2 new pattern backgrounds.
 * Added: you can now change the background- and textcolor of the theme.

= version 1.0.6 =
 * Fix: fixed a bug with saving the Woo coupon setting.

= version 1.0.5 =
 * Added: added action hooks so developers can extend the plugin.

= version 1.0.4 =
 * Fix: the 'email placeholder' content field wasn't saving.

= version 1.0.3 =
 * Update: verified WP 4.8.2 compatibility.
 * Added: added WooCommerce integration.
 * Added: added log file (via a setting).

= version 1.0.2 =
 * Update: allow visitors to try again if they lost.
 * Added: added support for ActiveCampaign.

= version 1.0.1 =
 * Added: added support for Campaign Monitor

= version 1.0.0 =
 * Initial version