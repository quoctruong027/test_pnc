=== Finale WooCommerce Deal Pages ===
Contributors: xlplugins
Tags: WooCommerce, WooCommerce Deals page, WooCommerce OnSale Products
Requires at least: 4.2.1
Tested up to: 5.2.4
Stable tag: 1.4.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Now list all your deals, special offers, enticing sales on a single page.
Visitors can access it via a single click on the navigation bar/sticky header. They can browse Deals of the day/Exclusives/Stock Clearance sale/Christmas specials, all curated on a single page. Browsing and shopping during a sale has never been so hassle-free.

== Change log ==

= 1.4.1 =
* Fixed: woocommerce_loop_add_to_cart_link filter hook arguments error fixed.


= 1.4.0 =
* Added: Out of stock text displayed in all deal pages template.
* Improved: Product link on deal pages made dynamic.
* Fixed: Pagination not displaying on homepage, resolved now.
* Fixed: Deal pages not displaying when string is translated in Finale plugin, resolved now.
* Fixed: Indexing not working properly in some cases, resolved now.
* Fixed: Hide timer and bar on native template not working, resolved now.
* Fixed: Sales badge not changing on native template, resolved now.
* Fixed: De-indexing not working properly, resolved now.


= 1.3.1 =
* Improved: Indexing of campaigns.
* Improved: Inventory bar displayed from right to left by default.
* Improved: Finale deal shortcode can now accept orderby attribute with modified, comment_count, etc values also.


= 1.3.0 =
* Added: Compatibility is added for Finale Evergreen campaigns.
* Fixed: Countdown Timer or Counter Bar on Finale Deal Pages detached from Campaign's respective element.
* Fixed: Did handling for a case when product's regular price or sale price is 0.
* Improved: Optimized calls on Finale Deal pages.
* Improved: Don't show timer in the grid if product doesn't have any Finale campaigns Deal ON.
* Improved: Validate deal campaign's rules before showing product inside a deal.

= 1.2.5 =
* Fixed: No counter bar and timer is showing for a product which is already added to the cart. Data needed to be reset by the Finale Core, Now forced data setup during shortcode execution.

= 1.2.4 =
* Added: New field added to exclude product types to alter 'add to cart'.
* Fixed: Sometimes default values were not getting to work when fields left empty.
* Fixed: Removed additional meta boxes from edit area in the deal page shortcode.
* Fixed: duplicate deal pages were showing for a campaign in campaign index page when deal page mark to run for "all campaigns".
* Added: New order by param as orderby "campaign_priority" that will show products by grouping products for each campaign.

= 1.2.3 =
* Fixed: Add to cart text to modify for more product type on grids.
* Fixed: PHP notice was throwing on deletion of multiple posts from admin area.
* Fixed: An error was printing for the non logged in users which should be printed for the admin only.

= 1.2.2 =
* Added: CMB2 Tabs compatibility with the recent Finale Changes.
* Fixed: List view safari CSS was breaking, now corrected.
* Added: Compatibility with PHP 7.2
* Fixed: When finale deleted manually through FTP, deal pages throwing a fatal error. Handled the scenario by checking if a file exists.
* Fixed: Hide Review rating and Hide sales Badge was not working as expected for grid templates.
* Added: Duplicate the deal page link provided as row link.

= 1.2.1 =
* Fixed: Error handling for the case where we have no campaigns that pass rules.
* Fixed: Add to cart text for the variable product is getting filtered by the hook placed openly, now placed inside grid.native view.
* Fixed: PHP notice throwing on deal page listing for the undefined hook callback.
* Fixed: Style related fixes for the themes overiding UL list style.
* Fixed: Dynamic styles getting overridden by the first grid/list in case of multiple grids in one place.
* Added: Added a new option to hide products of a non running campaigns without any message.

= 1.2.0 =
* Added: New settings for sales badge text color and text.
* Added: New one column layout is available.
* Fixed: Shortcodes handling in short description for list layouts.
* Added: Multi-selection of campaigns are available while configuring deal page settings. Allows users to add multiple campaigns to a single grid.
* Added: Setting added to select all indexed campaigns, allows users to set all indexed campaigns in a deal page.
* Improved: Query caching and optimization with the new file structure of campaign index. Reducing DB queries improved page load speed, especially with the selection of all campaign Or multiple campaigns.
* Improved: Increased default per posts count to 100 reducing vulnerability when not all products get processed.

= 1.1.3 =
* Fixed: Shortcode preview field getting saved in meta.

= 1.1.2 =
* Fixed: Rules with single_page was not working properly.
* Fixed: Pagination links color settings was not working.
* Improved: Notice and handling when WC not available.
* Improved: Default Shortcode shown to user contains more attribute to remove confusions.

= 1.1.1 =
* Improved: UI & UX of Index Campaigns page. Helping user to perform actions after changes to campaign etc.
* Added: New metabox and button added to de-index all the campaigns to restore the initial state.

= 1.1.0 =
* Added: New Settings added to allow store owners to decide what to do when campaign ends.
* Fixed: Add to cart text was not working correctly for variable and external products.
* Added: Shortcode new arguments added to control order of the products in the shortcode.
* Added: New setting to control thumbnail size of image in grids/lists.
* Improved: Better UX (notices,errors & status colors ) for the case when campaign needs re-indexing.
* Added: Tax query in the main shortcode query to consider WooCommerce native product visibility settings.

= 1.0.0 =
* Public Release




















