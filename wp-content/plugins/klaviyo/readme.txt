=== Klaviyo ===
Contributors: klaviyo, bialecki, bawhalley
Tags: analytics, email, marketing, klaviyo, woocommerce
Requires at least: 3.8
Tested up to: 5.5.1
Stable tag: 2.3.6

Easily integrate Klaviyo with your WooCommerce stores. Makes it simple to send abandoned cart emails, add a newsletter sign up to your site and more.

== Description ==

When you control the customer experience you have the ability to deliver personalized, memorable experiences that lead to lasting, high-value relationships with customers. Klaviyo helps businesses create memorable experiences across owned marketing channels—email, SMS, web and in-app notifications—by listening for and understanding cues from visitors, subscribers, and customers, and turning that information into valuable, relevant messages.

In fact, ecommerce stores switching to Klaviyo see an average 67x increase in ROI—that’s real growth driven by channels owned and controlled by the business.

Join brands you love like Chubbies, Huckberry, and ColourPop growing faster & building amazing customer experiences across their owned marketing channels by listening with Klaviyo.

####Listen to and understand your customers
* Powerful Integrations
    * Klaviyo offers 100+ pre-built integrations ranging from shipping solutions to rewards programs. Connect everything with Klaviyo and send more personal, relevant messages across every touchpoint on the customer journey.
    * Top integrations include: Facebook, ShipStation, Swell.io, Yotpo, Smile, Privy, and more.
* Dynamic Forms and Personalization
    * Leverage Klaviyo’s free Form Builder to collect more information about your customers and grow your email list, without touching a single line of code. Build fly-outs, pop-ups, and embeds. Target forms to specific segments, devices, or pages. Use the library of pre-built forms to get started quickly.
* Centralized Customer Profiles
    * The customer profile serves as the central hub for everything about your customers—giving a unified, single view of their actions, preferences, behaviors, and history.

####Analyze their behaviors and preferences
* Best-in-Class Segmentation
    * Businesses can find the perfect audience using Klaviyo’s best-in-class segmentation. Target customers based on any event, profile, metric, and even location or date. Use segmentation to build important groups of customers like VIPs, engaged profiles, and churn risks.
* Unparalleled Data Science
    * Predictive analytics like churn risk, customer lifetime value, gender prediction and smart send time are baked right into Klaviyo—saving time and helping to earn more money.
* Growth-centric Reporting
    * Klaviyo reporting and analytics focuses on your growth. Digging into a single campaign email performance and building dashboards to stay focused on important business metrics is quick and easy.

####Act more personally to build stronger relationships
* Personalized Automation
    * Use Klaviyo’s Flow Builder to automate touch points across the entire customer journey and use the pre-built templates to get started quickly before further targeting and personalizing every message.
    * Optimize every contact with customers through A/B and split testing.
* Social Advertising
    * Seamlessly sync customer lists and segments to Facebook and Instagram in order to target the right audience on social media to help grow your business.
* Targeted Campaigns
    * Build emails using pre-made templates or create your own with HTML. Pull in product recommendations and other dynamic data to personalize each message. Ensure every campaign is maximized by targeting it to the perfect audience.
* SMS
    * Send timely text messages to your customers that prefer receiving texts over email. Let Klaviyo manage compliance seamlessly on the customer profile so you’re always communicating on the right channel. Leverage a pay-as-you-go model to maximize ROI and never spend more than you need for SMS.

== Installation ==

Integrating Klaviyo and your WooCommerce store is a quick, two-step process:

1. Install/activate Klaviyo's plugin.
2. Enable the [Woocommerce integration](https://www.klaviyo.com/integration/woocommerce) within your Klaviyo account.

For detailed instructions on integrating Klaviyo and WooCommerce please visit our [Help Center](https://help.klaviyo.com/hc/en-us/articles/115005255808-Integrate-with-WooCommerce).

== Changelog ==
= 2.3.6 2020-10-27 =
* Fix - Remove escaping backslashes from Started Checkout title property

= 2.3.5 2020-10-19 =
* Fix - Remove escaping backslashes from Viewed Product title property

= 2.3.4 2020-10-01 =
* Fix - Remove unused import.

= 2.3.3 2020-09-25 =
* Fix - Cart state issue with rebuild when composite products are present

= 2.3.2 2020-09-11 =
* Fix - Encode non-ascii started checkout event data
* Fix - Handle checkout without Klaviyo cookie

= 2.3.1 2020-09-08 =
* Fix - Update to fix fatal error for websites not using WooCommerce plugin

= 2.3.0 2020-09-07 =
* Update - Removing all external javascripts from the Checkout page

= 2.2.6 2020-09-04 =
* Fix - Update to add permission callback for all custom endpoints (Wordpress 5.5)

= 2.2.5 2020-08-20 =
* Fix - Rename undefined variable

= 2.2.4 2020-08-05 =
* Tweak - Update to be more defensive around global server variables

= 2.2.3 2020-06-23 =
* Fix - Identify call in checkout billing fields

= 2.2.2 2020-06-11 =
* Fix - Check for checkout variable
* Fix - Resolve register_rest_route_warning
* Dev - Increase max WP version to 5.4.2
* Dev - Increase max WC version to 4.2.0

= 2.2.1 2020-05-26 =
* Tweak - Small update to legacy signup form widget

= 2.2.0 2020-05-14 =
* Fix - Custom order and product count method

= 2.1.9 2020-05-12 =
* Fix - Security fix

= 2.1.8 2020-04-24 =
* Dev - Refactor API code for unit tests

= 2.1.7 2020-01-28 =
* Add new authentication for api

= 2.1.6 2020-01-27 =
* Fix - Revert authentication patch
* Fix - Making sure characters are encoded correctly on signup success

= 2.1.5 2020-01-22 =
* Fix - Improve authentication for custom api endpoints

= 2.1.4 2019-12-04 =
* Fix - Check index is set for subscribe checkbox during checkout
* Fix - Move klaviyo.js script to highest priority in footer and add missing single quotes around src

= 2.1.3 =
* Fix - Deactivate old Klaviyo plugins if active
* Fix - Check if Klaviyo Settings index exists
* Fix - Pluck product categories only if array

= 2.1.2 =
* Add support for latest api version (v3)

= 2.1.1 =
* Check for existing Klaviyo plugins avoiding incompatibility

= 2.1.0 =
* Move all javascript to external files
* Compatible with just WP

= 2.0.7 =
* Add widget for Klaviyo's built-in signup forms

= 2.0.6 =
* Be able to customize CSS for forms
* Fix issue with button text display

= 2.0.5 =
* Remove signupform js as it's included in klaviyo.js

= 2.0.4 =
* Add klaviyo.js

= 2.0.3 =
* Escape quotes in product titles

= 2.0.2 =
* Use new endpoint for checkout subscriptions

= 2.0.1 =
* Compatibility for PHP 7.2 and remove PHP warnings
* Add persistent cart URL for rebuilding abandoned carts
* Add support for composite product cart rebuild

= 2.0 =
* Bundles the Wordpress and Woocommerce plugin together as one.
* An option to Add a checkbox at the end of the billing form that can be configured to sync with a specified Klaviyo list. The text can be configured in the settings. Currently set to off by default.
* Install the Klaviyo pop-up code by clicking a checkbox in the admin UI
* Automatically adds the viewed product snippet to product pages.
* Adds product categories which can be segmented to the started checkout metric.
* Removes the old unused code and functions.
* Updates all deprecated WC and Wordpress functions/methods.
* Removes the description tag from the checkout started event.
* Captures first and last names to the started check out metric.

= 1.3.3 =
* Updating docs.

= 1.3.2 =
* Tested for support for Wordpress 4.8.

= 1.3 =
* Added HTTPS support for embedded form.
* Updated logo branding.
* Updated links.
* Updated previously deprecated functions.

= 1.2.0 =
* Updating to allow embedding an email sign up form.

= 1.1.2 =
* Updating docs.

= 1.1.1 =
* Fixing documentation a bit and one bug fix.

= 1.1 =
* Adding in automatic tracking of users if they log in or post a comment.

= 1.0 =
* Initial version
