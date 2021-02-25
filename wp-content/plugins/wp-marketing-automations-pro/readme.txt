=== Autonami Marketing Automations Pro ===
Contributors: WooFunnels
Tested up to: 5.5.1
Stable tag: 1.2.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html


== Change log ==

= 1.2.2 (2020-09-25) =

* Added: New Merge tag '{{bwf_contact_id}}' added. Will return the unique contact id of every user. Usable in case of creating coupon with dynamic value. (#241)
* Improved: Update User Role: Action now supports role assignment as well. (#243)
* Improved: Learndash: Lesson selection rule, now showing course name aside of lesson name for better understanding. (#234)
* Fixed: Fluent Forms: After form submission, it is not redirecting to the correct page, fixed. (#230)


= 1.2.1 (2020-08-26) =

* Fixed: PHP error in case WooCommerce is not active. (#220)
* Fixed: Contact has 'Active Subscription' rule, wasn't working with 'Win-back campaign' event, fixed. (#225)


= 1.2.0 (2020-08-23) =

* Compatible with WordPress 5.5
* Compatible with WooCommerce 4.3 & 4.4
* Added: Learndash integration added. Events like a user is enrolled, user completed a course or lesson or topic. Actions like enroll a user in a course, add a user to a group etc. (#153)
* Added: New event: Webhook received. Receive any data via HTTP Post and perform actions in your site. (#141)
* Added: New Action: End Automation. Stop the automation execution i.e. deletes the schedule tasks of particular automation based on contact from any automation. (#117)
* Added: Ninja Form integration added. Now execute actions after a Ninja form is submitted. (#153)
* Added: Fluent Form integration added. Now execute actions after a Fluent form is submitted. (#172)
* Added: Caldera Form integration added. Now execute actions after a Caldera form is submitted. (#179)
* Added: New merge tag: Order again URL. Ability to add all order items to the cart with a single URL. (#164)
* Added: New Rule: Subscription Failed Attempt. (#168)
* Added: New Rule: Customer Purchased Products Category added. (#150)
* Added: New rule: BWF Contact added on Gravity form, Ninja Form, Elementor Form & Thrive Leads form submission events. (#148)
* Added: New event: Order Status Pending. Run, on orders which are left in pending state and are 10 mins older. (#146)
* Added: Compatibility with 'WooCommerce Sequential Order Numbers' plugin. Merge tag {{wc_sequential_order_number}} with output (Order Number & Order number Formatted) on order related event. (#138)
* Added: Compatibility with 'WooCommerce Advanced Shipment Tracking' plugin. Merge tag {{wc_advanced_shipment_tracking}} with output (tracking_number, tracking_provider, tracking_link & date_shipped) on order related event. (#186)
* Added: Compatibility with 'Handl UTM Grabber' plugin. Merge tag {{hand_utm_grabber_data}} with multiple outputs (like utm_campaign, utm_source etc) on cart abandonment event. (#211)
* Improved: Elementor forms have multiple cases like popup form, widget form etc. All are handled. (#188, #195)
* Improved: 'Send data to Zapier' & 'HTTP Post' actions, UI improved. (#199)
* Fixed: 'Send data to Zapier' action, sometimes data contain extra slashes, fixed. (#137)
* Fixed: UpStroke offer accepted event: item id and name merge tags issue fixed. Item SKU, new merge tag added. (#184)


= 1.1.0 (2020-03-25) =

* Added: ThriveLeads integration added. Now execute actions after a thrive leads form submission. (#87)
* Added: AffiliateWP integration: New Event: Affiliate status change added. (#82)
* Added: AffiliateWP integration: New Rule: Affiliate rate added. (#82)
* Added: AffiliateWP integration: New Merge Tags: {{affwp_affiliate_rate}} and {{affwp_affiliate_status}} added. (#82)
* Added: New action: Update WordPress user role. (#92)
* Added: Compatibility with 'WooCommerce Shipment Tracking' plugin. Merge tag {{wc_shipment_tracking}} with output (tracking_number, formatted_tracking_provider, formatted_tracking_link & date_shipped) on order related event. (#95)
* Added: Compatibility with 'Jetpack' plugin shipment feature. Merge tag {{wc_jetpack_shipment}} with output (carrier_name_full, package_name, tracking_number & tracking_link) on order related event. (#105)
* Added: New action: Cancel WC order associated subscription for order status change event only. (#127)
* Added: Autonami notice to install a plugin if Autonami is not installed or active. (#133)
* Improved: HTTP Post action, showing correct response code after execution. (#104)
* Improved: Create user action, now have the first name and last name optional fields as well. (#111)
* Improved: WC Add order note action now have the option to choose between customer note and private note. (#107)
* Fixed: Compatibility to run without WooCommerce. (#80)


= 1.0.2 (2020-01-07) =

* No change


= 1.0.1 (2020-01-07) =

* Added: Affiliate first name merge tag added.
* Added: Zapier 'Send data' action: new option 'send test data' coded.
* Added: Customer winback campaign, new settings added; UI & logic improved.
* Added: New rules 'Order is a renewal for WC subscription' added.
* Added: Batch Processes: Delete option added for completed processes.
* Improved: Time sync events like 'customer winback', 'affiliate digest' etc. are now showing 'last run' date on the event UI so that store owner can see when it last ran.
* Improved: Action UI spacing, descriptions, overall UX improved.
* Improved: Custom callback action code improved.
* Improved: Delete scheduled tasks of winback campaign automation of an user after a new order is placed.
* Improved: WooCommerce subscription renewal event auto validating subscription status before executing tasks.
* Fixed: AeroCheckout page id rule wasn't working, fixed.


= 1.0.0 (2019-11-25) =

* Public Release