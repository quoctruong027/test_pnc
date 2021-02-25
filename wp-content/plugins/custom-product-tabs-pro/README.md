# Custom-Product-Tabs-Pro - v1.2.2
The pro version of Custom Product Tabs

# Features

1. Global Tabs
- Create global saved tabs. This will apply your saved tab to all of your products.

2. Taxonomy Tabs
- Assign taxonomy terms to tabs. This will apply your saved tab to all of your products using the specified term(s).
- If you apply a taxonomy to a product, updating that product will add the saved tab.

3. WooCommerce Bulk Edit
- Assign saved tabs to products via WordPress'/WooCommerce's core bulk edit screen.

4. Search
- Make WordPress' standard site search also search Custom Product Tabs data
- Make WooCommerce's standard search widget also search Custom Product Tabs data

5. Premium Support
- Premium YIKES support with a support form accessible directly from your dashboard

6. And more!
- Remove the tab title from tab content site-wide without needing a filter
- From a saved tab, view which products are currently using that tab

# Changelog

##### v1.2.2 - May 26th, 2020
- Bug fix: Disable ssl verify warning showing in query monitor.

##### v1.2.1 - March 26th, 2020
- Fixes edge case where global tabs won't save under certain conditions.

##### v1.2.0 - March 10th, 2020
- Support For WooCommerce 4.0
- Adding ability to turn on a custom content filter for tabs that are built with a page builder.

##### v1.1.8 - November 26st, 2019
- Fixed bug preventing new products within a tab category from getting their tabs.

##### v1.1.5 - November 21st, 2019
- WooCommerce v3.8 Supported.

##### v1.1.4 - August 31st, 2019
- Global tabs that are modified or removed are not re-added to the product.

##### v1.1.3 - July 24th, 2019
- Advanced settings for toggling SSL verification on update.

##### v1.1.2 - April 19th, 2019
- Updating WC Compatibility.

##### v1.1.1 - December 20th, 2018
- Fixed an issue with lifetime licenses.
- Re-added a CSS file that was deleted during the last update.
- Remove the current license feedback message before showing a new one.

##### v1.1.0 - December 20th, 2018
- Added tab ordering. You can do this by enabling tab ordering on the settings page. For saved tabs, you can order them by drag-and-dropping tabs from the saved tabs list. For default tabs, use the dropdowns provided on the settings page.
- Added ability to disable the default WooCommerce tabs (description, additional information, and reviews tabs).
- Added i18n, escape functions, and WPCS fixes throughout the plugin.
- Added filters for the following: ability to exclude a product from being ordered (`cptpro_tab_reorder_excluded_product_ids`), ability to exclude a product's default tabs from being disabled (`cptpro_disable_description_excluded_product_ids`, `cptpro_disable_additional_information_excluded_product_ids`, `cptpro_reviews_excluded_product_ids`), the ability to exclude products from using global tabs `cptpro_global_tab_excluded_product_ids`, the ability to change the order of all tabs for a product (`cptpro_tabs_after_reorder`), ability to reorder a specific saved tab for a product (`cptpro_saved_tab_priority`), and the ability to reorder default tabs for a product (`cptpro_description_tab_priority`, `cptpro_additional_information_tab_priority`, `cptpro_reviews_tab_priority`) 

##### v1.0.8 - October 26th, 2018
- Bumping WooCo Compatibility.
- Changed `wp_send_json_failure()` to `wp_send_json_error()`.

##### v1.0.7 - October 3rd, 2018
- Fixed an issue where saved tabs were not being initialized.
- Bumped WooCommerce compatibility.

##### v1.0.6 - March 21st, 2018
- Fixed an issue with foreign characters breaking the "Assign to Taxonomy" feature
- Updated some logic to be compatible with older versions of PHP
- Added a filter so you can exclude products from global tabs  

##### v1.0.5 - February 7th, 2018
- Added the tab name to the bulk edit. Users should now be able to better distinguish tab's that use the same title.
- Added support for removing taxonomy tabs from products that do not have that taxonomy. Users should now see tabs being removed when taxonomies are removed from a product. If this functionality is not desired, it can be turned off by using the filter `cptpro-remove-tabs-from-product-by-taxonomy` and returning `false`.

##### v1.0.4 - January 30th, 2018
- Refactored the way "Products using this Tab" box worked on the single saved tabs page. It should now fetch products in batches which will prevent memory overflow/timeout issues.
- Added the version number to all JS and CSS files. They should now be suffixed with the plugin's version number. This should prevent caching on updates.

##### v1.0.3 - November 17th, 2017
- Fixed an issue where global tabs were not being applied after they were created as non-global
- Fixed an issue where tabs were being duplicated if their name was changed, they were added to a taxonomy, and they were removed from being global.
- Fixed an issue where deleting a taxonomy wasn't deleting associated tabs from products with more than one saved tab
- Changing the default permission for managing settings to `publish_products`. This can be changed with the 

##### v1.0.2 - November 7th, 2017

- Fixing an issue where changing a global tab's name would create a new tab instead of updating the original one

##### v1.0.1 - November 1st, 2017

- Resolving a conflict issue with the EDD updater class.
Declaring support for WooCommerce.

##### v1.0.0 - October 13th, 2017

- Call me Custom Product Tabs Pro.
