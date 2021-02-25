=== Advanced Shipment Tracking for WooCommerce  ===
Contributors: zorem
Tags: WooCommerce, delivery, shipping, shipment tracking, tracking
Requires at least: 5.0
Tested up to: 5.5.3
Requires PHP: 7.0
Stable tag: 4.0.1
License: GPLv2 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add shipment tracking information to your WooCommerce orders and provide your customers with an easy way to track their orders. 

== Description ==

Advanced Shipment Tracking (AST) for WooCommerce lets you add tracking information to orders and provides your customers an easy way to track their orders. AST provides powerful features that let WooCommerce shop owners to better manage and automate their post-shipping orders flow, reduce time spent on customer service and increase customer satisfaction.

Advanced Shipment Tracking is the #1 shipment tracking plugin for WooCommerce. AST provides powerful tools that help shops to manage WooCommerce orders in a more officiant ways and to automate the post-shipping operations.

https://www.youtube.com/watch?v=QOVbwfgXQdU

==Key Features==

* **Easily add Tracking information to orders**
Easily add tracking information to your orders, AST lets you add the tracking from the WooCommerce orders admin or from a single order admin, we also allow you to mark the order as Completed when adding the tracking information.  

* **Customize The Tracking Display on the Order Emails**
You can fully customize the tracking information display on the order emails and on their accounts. With our customizer with a live preview, you can choose the design layout, show/hide tracking information, edit the content, fonts, colors, and more..

* **List of 250+ Shipping Providers**
AST provides a list of more than 250 shipping providers (carriers) around the globe with a predefined tracking link, AST automatically generates the tracking link sent to your customers when you ship their orders.

* **Keep your Tracking Links Up-To-Date**
We maintain this list and you can sync the shipping providers list to keep it up-to-date with any changes in the shipping providers info.

* **Custom Shipping Providers**
If you can’t find your shipping provider on our list, you can suggest it for us to add or you can add your own custom providers, you can set a tracking link with tracking information variables to use in the tracking link.

* **WooCommerce REST API Support**
AST creates a Shipment Tracking WooCommerce REST API endpoint so you can easily update the tracking information in your WooCommerce orders from any external system or shipping labels service you use and automate your daily workflow..

* **Bulk Upload from CSV**
If your shipper provides the tracking information in files and you want to avoid manually adding them into orders, AST provides a quick and easy interface to import multiple tracking numbers to orders in bulk from a CSV.

* **Custom Order Statuses**
Improve your order management flow, with custom order statuses, enable the Partially Shipped for orders that you ship in separate packages at different times or enable the Delivered order status if you use [TrackShip](https://trackship.info/) for tracking & delivery automation.

* **Compatibility with many shipping providers plugins and services**
AST is compatible with many shipping labels plugins.

== PREMIUM ADD-ONS ==

**Tracking Per Item Add-on** - The Tracking per item add-on allows you to attach tracking numbers to specific order items and also to attach tracking numbers to different quantities of the same line item. [Get this Add-on](https://www.zorem.com/shop/tracking-per-item-ast-add-on/)

**TrackShip Integration** -  [TrackShip](https://trackship.info/) is a multi-carrier shipment tracking API that fully integrates into WooCommerce with the AST plugin. TrackShip automates the orders workflow, reduces customer inquiries and time spent on customer service, and keeps your customers informed on their shipment status at all times.

* Auto-track shipped orders with 200+ shipping providers
* Up-to-date shipment status and est. delivery date on your orders admin
* Automatically change the order status to Delivered once it’s delivered to your customers
* Send shipment status update emails to notify your customers when their shipments are Out For Delivery, Delivered or have an exception
* Direct customers to a Tracking Page on your store

You must have a [TrackShip](https://trackship.info/) account to activate these advanced features.

== Compatibility == 
The Advanced Shipment Tracking plugin is compatible with many other plugins such as shipping label plugins and services, email customizer plugins, Customer order number plugins, PDF invoices plugins,  multi vendor plugins, SMS plugins and more. Check out [AST's full list of plugins compatibility](https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/compatibility/). 

== Documentation ==
You can get more information, detailed tutorials and code snippets on the [ AST documentation](https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking).

== Translations == 
The AST plugin is localized/ translatable by default, we added translation to the following languages: 
English - default, German (Deutsch), Hebrew, Hindi, Italian, Norwegian (Bokmål), Russian, Swedish, Turkish, Bulgarian, Danish Spanish (Spain), French (France), Greek, Português Brasil, Dutch (Nederlands)

If your language is not in this list and you  want us to include it in the plugin, you can send us [on our docs](https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/translations/#upload-your-language-files)
 the translation files (po/mo) and we will add them to the plugin files.

== Shipping Providers == 

The AST plugin supports more then 250 shipping providers (carriers) with pre-defined tracking link:

USPS, ePacket, Delhivery, Yun Express Tracking, UPS, Australia Post, FedEx, Aramex, DHL eCommerce, ELTA Courier, Colissimo, DHL Express, La Poste, DHLParcel NL, Purolator, 4px, Brazil Correios, Deutsche Post DHL, Bpost, DHL US, EMS, DPD.de, GLS, China Post, Loomis Express, DHL Express, DHL Express UK, Poste Maroc, PostNL International 3S, Royal Mail and many others..

== FAQ == 

= Where will my customer see the tracking info?
The tracking info and a tracking link to track the order will be added to the Shipped (Completed) order status emails. We will also display the tracking info in my-account area for each order in the order history tab.

= Can I edit the Tracking info display on the WooCommerce emails?
Yes, you have full control over the design and display of the tracking info and you can customize the display and content in a customizer with a live preview.

= Can I add multiple tracking numbers to an order?
Yes, you can add as many tracking numbers to orders and they will all be displayed to your customers on the order email and their my-account area.

= Can AST automatically track my orders and send automated delivery emails?
Yes, AST fully integrates with [TrackShip](https://trackship.info/), a Multi-Carrier Shipment Tracking API that will auto-track your shipments and updates your orders with shipment status and delivery changes ,automates your order management process, lets you send shipment status notifications to your customers and direct them to tracking page on your store.

= How do I add a direct tracking link to my custom provider?
If your shipping providers has a tracking page and the URL contains the tracking number to allow direct tracking, you can add tracking number parameter that will automatically generate a tracking link with the tracking number, add the tracking URL in this format:

https://example-provider.com?tracking_number=%number% where the %number% variable in the URL will be replaced with the tracking number, you can even use the country code and postal code variables. Check out the [AST documentation](https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/setting-shipping-providers/#adding-custom-shipping-provider) for more details.

= Can I Import tracking information from CSV files?
Yes, you can use our [CSV import tool](https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/bulk-import-shipment-tracking/) to import multiple tracking numbers to orders, you need to add each tracking number in one row.

= Can I add tracking numbers to specific products in the Order?
Yes, you can use the [Tracking Per Item add-on](https://www.zorem.com/products/tracking-per-item-ast-add-on/) which adds the option to attach tracking numbers to specific line items and even to attach tracking numbers to specific line item quantities.

= How can we automatically add tracking info to orders?
If you use external shipping services that work with the WooCommerce REST API to update your orders, they can use the [AST shipment tracking API endpoint](https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/rest-api-support/) to update the tracking information in orders and if you're using a WooCommerce plugin to generate shipping labels, check out the [AST compatibility list](https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/compatibility/) and if it's not on the list, let us know on the support forum and we will try to add compatibility.

== Installation ==

1. Upload the folder `woo-advanced-shipment-tracking` to the `/wp-content/plugins/` folder
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Select default shipping provider from setting page and add tracking number in order page.

== Changelog ==

= 3.1.5 =
* Enhancement - Added "Add Provider" button in the Custom Providers Tab
* Enhancement - Hide the option to Save and "mark as shipped" for New admin orders (before "created")
* Enhancement - Updated settings page design
* Enhancement - TRACK button open the tracking url in a new window in My Account Orders history page
* Dev - Shipping providers - by Default non of the Shipping Providers active when first time install plugin
* Enhancement - Set all lightbox background opacity to 0.8
* Enhancement - Set delivered shipment status email notification customizer link disabled if delivered order status email is enable and added a message
* Fix - If WooCommerce is not installed showing multiple admin message of Advanced Shipment Tracking
* Fix - Fixed CSV Import issue for Tracking Per Item Addon
* Fix - Fixed warnings - Undefined variable: order_id in ...\wp-content\plugins\woo-advanced-shipment-tracking\includes\class-wc-advanced-shipment-tracking-settings.php on line 516
* Fix - Fixed warnings - PHP Notice: Undefined index: wcast_show_tracking_details in ...\wp-content\plugins\woo-advanced-shipment-tracking\includes\emails\class-shipment-delivered-email.php on line 142
* Fix - Fixed warnings - PHP Notice: Undefined index: wcast_show_order_details in ...\wp-content\plugins\woo-advanced-shipment-tracking\includes\emails\class-shipment-delivered-email.php on line 143
* Fix - Fixed warnings - PHP Notice: Undefined index: wcast_show_billing_address in ...\wp-content\plugins\woo-advanced-shipment-tracking\includes\emails\class-shipment-delivered-email.php on line 144
* Fix - Fixed warnings - PHP Notice: Undefined index: wcast_show_shipping_address in ...\wp-content\plugins\woo-advanced-shipment-tracking\includes\emails\class-shipment-delivered-email.php on line 145
* Fix - Fixed warnings - PHP Notice: Undefined index: wcast_enable_delivered_ga_tracking in ...\wp-content\plugins\woo-advanced-shipment-tracking\includes\emails\class-shipment-delivered-email.php on line 149
* Fix - Fixed warnings - PHP Notice: Undefined index: wcast_delivered_analytics_link in ...\wp-content\plugins\woo-advanced-shipment-tracking\includes\emails\class-shipment-delivered-email.php on line 150



= 3.1.4 =
* Enhancement - Added Tracking Button on Orders History (my-account) page
* Dev - Set default preview in Tracking Info Customizer
* Dev - Added new parameter - 'replace_tracking' in Add Tracking API Endpoint
* Dev - Used Rest API name in Bulk upload CSV and programmatically add tracking info
* Dev - Updated design of Late shipment Email Content
* Fix - Fix CSS issue in TrackShip Tracking page
* Dev - Create Order (Admin) option to add the tracking info on initial order.
* Fix - Custom order status not enabled/diabled
* Fix - jQuery Depreciated - jQuery.fn.load() is deprecated

= 3.1.3 =
* Enhancement - Updated Add Tracking, Add Custom Shipping Provider, Edit Shipping Provider, Sync Shipping Provider popup background color
* Dev - Removed Sync Providers notice and TrackShip notice from admin
* Dev - Removed the material design library
* Dev - Added filter in TrackShip Tracking page event message and location so user can add filter change event message and location - 'trackship_tracking_event_description' , 'trackship_tracking_event_location'
* Fix - Fixed TrackShip tools Get Shipment Status issue for 'TrackShip Balance 0' option

= 3.1.2 =
* Enhancement - Updated design of CSV Import process
* Enhancement - Updated AST settings page design
* Enhancement - Move Custom order status manager tab to general settings
* Enhancement - Updated design of TrackShip tracking page
* Enhancement - Updated design of TrackShip tracking page settings panel
* Dev - Seperate code of Tracking Per Item Addon option from AST to Tracking Per Item Addon files
* Dev - Remove the option to add tracking from order actions panel for Local Pickup Orders
* Dev - Add Support - sequential order number plugin(free)
* Dev - Updated Translation Files in Dutch (nl-nl)
* Dev - TrackShip Emails - added hook in order details template for shipment status emails ('ast_email_order_items_args') 
* Dev - Check if enable option - 'Rename the “Completed” Order status to “Shipped”' than change "Completed" tooltip in actions to "Mark as Shipped"
* Dev - Move all Tracking Per Item Addon translations from AST to TPI
* Dev - Remove ALP admin notice message
* Dev - Set default settings of "On which order status email to include the shipment tracking info?" and "On which Order status to display Add Tracking icon in the Order Actions menu?" on initial installation
* Fix - Sync Providers - duplicate view/hide details + updates issue
* Fix - Can’t find variable: api_provider_name issue on edit custom shipping provider
* Fix - Trying to access array offset on value of type bool on woo-advanced-shipment-tracking/includes/views/admin_options_osm.php:30
* Dev - Added custom order number plugin compatibility in shipment status email variables "{order_number}"

= 3.1.1 =
* Enhancement - Added a option in AST general settings for select On which Order status to display Add Tracking icon in the Order Actions menu.
* Enhancement - Uppdated Late Shipments Customizer
* Enhancement - Updated design of tools
* Enhancement - Updated toggle design
* Enhancement - Updated Shipping Providers List
* Enhancement - TrackShip - Added a option to set shipping provider tracking page link in TrackShip tracking page 
* Dev -Added compatibility with Advanced Order Status Manager plugin
* Fix - Fixed issue with TrackShip Dashboard widget

= 3.1 =
* Enhancement - Add API Name column to the shipping providers. User can use use API name for the providers lookup when adding tracking information through REST API
* Enhancement - TrackShip tracking page - added option for add custom URL as a tracking page
* Enhancement - Added a option in AST settings to select API Date Format
* Dev - Removed the shipment status Counts from the WC orders filter and added option in TrackShip for remove shipment status filter from orders page
* Enhancement - Tracking Per Item - Added Settings Option in the AST general settings - Display products SKU in add tracking form
* Enhancement - Change the Tracking # input to be first in the add tracking form
* Dev - Added Default colors for Custom Order Statuses
* Dev - Added and Updated API Endpoint
* Enhancement - Updated Shipment Tracking and TrackShip page settings
* Dev - Remove the Add Tracking from action menu for completed order status
* Enhancement - On deactivate plugin check if order in custom order status and give option to reassign that order to different order status
* Fixed - Tracking Page - Events CSS issue 
* Fixed - Fixed rtl issue of settings page
* Enhancement - Added admin message for review

= 3.0.9 =
* Dev - Added compatibility with WordPress 5.5
* Dev - Added compatibility with Custom Order Numbers for WooCommerce Pro plugin.
* Enhancement - Updated design TrackShip tab if TrackShip is not connected
* Enhancement - Added an option add display name in custom providers
* Enhancement - Added a seperate TrackShip menu if TrackShip is connected
* Fix - Fixed warning 'register_rest_route was called incorrectly. The REST API route definition for wc/v1/orders/(?P[\d]+)/shipment-trackings/providers is missing the required permission_callback argument' for WordPress version 5.5
* Enhancement - Updated the settings page design

= 3.0.8 =
* Fix - Fixed fatal error when changing status to delivered
* Fix - Fixed email content issue in TrackShip late shipments email

= 3.0.7 =
* Enhancement - Updated the design of TrackShip tracking page
* Enhancement - Updated design of Shipping Providers edit image lightbox and change label
* Enhancement - Updated design of shipping providers list
* Enhancement - Change CSV Import label
* Enhancement - Improve search shipping providers functionality
* Enhancement - Updated design of Trackship tools
* Enhancement - Custom Order Status auto save on change enable/disable, Color, Font color and Enable email
* Enhancement - Updated design of TrackShip tracking page settings
* Enhancement - Updated Order status and shipment status email customizer
* Fix - Fixed jQuery.live() in shipping_row.js

= 3.0.6 =
* Enhancement - Added Pending Trckship option in shipment status filter in orders page
* Enhancement - Added option for Edit shipping provider name and image
* Enhancement - Trackship tracking page added functionality for show origin tracking details and destination tracking details
* Enhancement - Add Re-Order button in my accounr single order page for custom order page - Delivered, Partially Shipped, Updated Tracking
* Dev - Added parent class in paging class in shipping providers list page
* Dev - Added Error message instead of error code in shipment status box in orders list page and single order page for TrackShip
* Fix - Fixed issue with bulk import with Partially Shipped

= 3.0.5 =
* Fix - Fixed issue with custom order number generated by Booster for WooCommerce plugin
* Fix - Fixed issue with custom order number in TrackShip tracking page
* Dev - Moved Tracking Per Item add-on license from this plugin to Tracking Per Item add-on
* Enhancement - Change default background color of tracking display table to #f5f5f5 

= 3.0.4 =
* Fix - Fixed license error for Tracking Per Item Add-on User

= 3.0.3 =
* Dev - Removed Tracking Per Item Add-on license activation code AST and moved to Tracking Per Item Add-on

= 3.0.2 =
* Fix - Fixed error on Add Tracking button on orders page
* Fix - fixed issue with On Hold customizer
* Enhancement - Remove TrackShip shipment stats change text before tracking info table from all shipment status emails

= 3.0.1 =
* Fix - fixed delivered order status email customizer issue
* Fix - fixed TrackShip tracking page jQuery Block UI issue for some of the themes

= 3.0 =
* Enhancement - Updated CSV Upload page design in settings page
* Enhancement - Updated TrackShip dashboard page design
* Enhancement - Added On Hold Shipment status emails for TrackShip
* Enhancement - Redesign Shipping Providers List in settings page
* Enhancement - Added option for hard sync shipping providers in Sync Providers option
* Dev - Updated plugin code for better security and optimize
* Dev - Removed compatibility code for WC – APG SMS Notifications from plugin
* Dev - Added all shipping provider image under wp-content/uploads/ast-shipping-providers folder. So load shipping provider image from there
* Dev - Optimized all shipping provider image
* Dev - Added new functions for add tracking information and get tracking information
* Dev - Removed all kind of special character validation from adding tracking number 
* Fix - Fixed issue of set order status shipped from order details page when "mark order as shipped" without page refresh
* Localization - Updated Swedish, Turkish and French Translations

[For the complete changelog](https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/changelog/)