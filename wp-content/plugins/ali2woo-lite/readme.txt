=== Ali2Woo Lite for AliExpress Dropshipping ===
Contributors: ali2woo
Tags: woocommerce, woo, aliexpress, dropship, dropshipping, ali2woo, dropshipper, affiliate, alidropship, alidropship, alidropshipping, import, extension, fulfil, fulfilment
Requires at least: 4.7
Tested up to: 5.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Stable tag: trunk
Requires PHP: 5.2.4
WC tested up to: 4.9
WC requires at least: 2.6

Start your Dropshipping business with AliExpress: easily find and import profitable products into store, set up your pricing markups, keep products prices and inventory synced.

== Description ==

Want to launch your own dropshipping store? With Ali2Woo Lite you can accomplish this task easily! Import unlimited products with variants from AliExpress! Place unlimited orders on AliExpress automatically! Automate other dropshipping routine tasks! Moreover, Ali2Woo Lite is integrated with AliExpress Affiliate Program! So you can earn more if you sell affiliate products.

[Knowledge Base](https://ali2woo.com/codex/) | [Chrome extension](https://ali2woo.com/codex/aliexpress-dropshipping-chrome-extension/) | [Facebook page](https://www.facebook.com/Ali2Woo/) | [Full Version](https://1.envato.market/KEgOy)

### Important Notice:

- This plugin requires WooCommerce plugin installed and activated before use.

- It is released on WordPress.org and you can use plugin as free to build themes for sale.

### FEATURES
  
&#9658; **Import Products**:

This plugin can import products from AliExpress with several methods: via a built-in search module or through the free chrome extension. Additionally, it can pull products from selected categories or store pages on AliExpress. Also, if you want to import a specific product only, you can use AliExpress product ID or AliExpress product URL to do that.

- **Import from single product page**

- **Import from category page**

- **Import from store page**

&#9658; **Split product variants into separate products**:

The plugin allows splitting product variants. For example: a lot of products on AliExpress come with the "ShipFrom" attribute. Often dropshippers don't want to show this variant for customers. With this feature it's possible to split such a product by the "ShipFrom" attribute. As result you will get separate products without it.

Please look through the [this article](https://ali2woo.com/codex/how-to-split-product-variants-video/) to understand clearly how splitting feature works.

&#9658; **Override product supplier**:

The product override feature is very helpful if you get a new order for the out-of-stock product and want to fulfill the order from another supplier or vendor on AliExpress.
Also it helps if you have a product that was loaded through other dropshipping tool or it was added manually in Woocommerce and you want to connect the product to some AliExpress item using Ali2Woo.

Check out an article from the plugin Knowledge Base to know [how to use this feature.](https://ali2woo.com/codex/how-to-change-the-product-supplier/)

&#9658; Change product images through the built-in image editor**:

Have you ever noticed the most product images having seller’s logo on AliExpress? And when you import the products into your store, those watermarks are visible for your customers. We know about such a problem and added a built-in images editor to the plugin`s features. The image tool allows to adjust photos right in your WordPress Dashboard.

It's recommended to check a detailed article [about the image editor tool.](https://ali2woo.com/codex/how-to-hide-watermark-on-images-from-aliexpress/)

&#9658; **Configure settings for all imported products**:

This set of settings apply to all products imported from AliExpress. Go to Ali2Woo Settings > Common Settings > Import Settings.

- **Language**: Set language of product data such as title, description, variations, attributes, etc. Currently, the plugin supports 17 AliExpress languages. We add new languages periodically.

- **Currency**: Change the currency of products. Currently, the plugin includes 145 currencies. We add new currencies periodically.

- **Default product type**: By default the plugin imports product as "Simple/Variable product". In this case, shoppers will stay on your website when they make a purchase else choose the "External/Affiliate Product" option and your visitors will be redirected to AliExpress to finish the purchase.

- **Default product status**: Choose the "Draft" option and imported products will not be visible on your website frontend.

- **Not import specifications**: Turn this feature on if you'd NOT like to import product attributes from AliExpress. You can see these attributes in the "specifications" tab on the AliExpress website.

- **Not import description**: Enable this feature if you don't want to import product description from AliExpress.

- **Don't import images from the description**: If you want to skip images from the product description and don't import them to the wordpress media library, use this option.

- **Use external image urls**: By default, the plugin keeps product images on your server. If you want to save free space on your server, 
activate this option and the plugin will load an image using an external AliExpress URL. Please note: This feature works if the plugin is active only!

- **Use image proxy**: If you enabled "Use external image urls" and then have a problem with loading of AliExpress images, try to activate this option. It allows the plugin to use the built-in proxy for image loading. 

- **Use random stock value**: By default the plugin imports the original stock level value. Some sellers on AliExpress set very high value and it doesn't look natural. To solve the issue just enable the feature. It forces the plugin to generate stock level value automatically and choose it from a predefined range.

- **Import in the background**: Enable this feature and allow the plugin to import products in a background mode. In this case, each product is loaded in several stages. First, the plugin imports main product data such as: title, description, and attributes, and in the second stage, it imports product images and variants. This feature speeds up the import process extremely. Please note: In the first stage a product is published with the "draft" status and then when all product data is loaded the product status is changed to "published".

&#9658; **Set options related to the order fulfillment process**:

These settings allow changing an order status after the order is placed on AliExpress. Go to Ali2Woo Settings > Common Settings > Order Fulfillment Settings.

- **Tracking Code Order Status**: Set a status for an order that receives the tracking code. The plugin will assign that status to the order automatically if you activate the "tracking sync" process.

- **Placed Order Status**: Set a status for an order that's placed on AliExpress using the chrome extension. 

&#9658; **Configure Chrome extension settings**:

In this section you can manage the behavior of the Chrome extension. Most settings are related to the order fulfillment process. Go to Ali2Woo Settings > Common Settings > Chrome Extension settings.

- **Default shipping method**: If possible, the extension auto-select the shipping method on AliExpress during an order fulfillment process.

- **Override phone number**: The extension will use these phone code and number instead of the real phone provided by your customer.

- **Custom note**: Set a note for a supplier on the AliExpress.

- **Transliteration**: Enable the auto-transliteration of AliExpress order details such as first name, last name, address, etc.

- **Middle name field**: Adds the Middle name field to WooCommerce checkout page in your store. The extension uses the field data during an order-fulfillment process on AliExpress.

- **Automatic payments**: Allow the extension to pay for orders automatically during an order fulfillment process on AliExpress. This feature requires that you select a credit card as the default payment method on AliExpress.

- **Place order to Awaiting payment list**: Allow the extension to place each order to the Awaiting payment list on AliExpress. As a result, it gives an ability to pay for all orders at a time.

&#9658; **Earn more with AliExpress Affiliate program**:

On this setting page, you can connect your store to AliExpress Affiliate program. You can connect to the program using your AliExpress, Admitad, or EPN account.
Go to Ali2Woo Settings > Account Settings.

We have a detailed guide on how to connect to the AliExpress Affiliate program [HERE](https://ali2woo.com/codex/account-settings/) 

&#9658; **Set up global pricing rules for all products**:

These options allow setting markup over AliExpress prices. You can add separate markup formula for each pricing range. The formula is a rule of a price calculation that includes different math operators such as +, *, =. Pricing rules support three different modes that manage the calculation in your formulas. Additionally, you can add cents to your prices automatically. And even more, it's easy to apply your pricing rules to already imported products. 

Go to Ali2Woo Settings > Pricing Rules.

Also, read a detailed post about [global pricing rules.](https://ali2woo.com/codex/pricing-markup-formula/)

&#9658; **Filter or delete unnecessary text from AliExpress product**: 

Here you can filter all unwanted phrases and text from AliExpress product. It allows adding unlimited rules to filter the texts. These rules apply to the product title, description, attributes, and reviews. Please note the plugin checks your text in case-sensitive mode.

Go to Ali2Woo Settings > Phrase Filtering.

See a detailed guide on this topic [HERE.](https://ali2woo.com/codex/phrase-filtering/)


###PRO VERSION

- **All features from the free version**

- **6 months of Premium support**

- **Lifetime update**

&#9658; **Set options related to the product synchronization**:

**[pro version feature]** This set of features allows synchronizing an imported product automatically with AliExpress. Also, you can set a specific action that applies to the product depending on change occurring on AliExpress.  Go to Ali2Woo Settings > Common Settings > Schedule Settings.

- **Aliexpress Sync**: Enable product sync with AliExpress in your store. It can sync product price, quantity and variants.

- **When product is no longer available**: Choose an action when some imported product is no longer available on AliExpress.

- **When variant is no longer available**: Choose an action when some product variant becomes not available on AliExpress.

- **When a new variant has appeared**: Choose an action when a new product variant appears on AliExpress.

- **When the price changes**: Choose an action when the price of some imported product changes on AliExpress.

- **When inventory changes**: Choose an action when the inventory level of some imported product changes on AliExress.


&#9658; **Get email alerts on product changes**:

**[pro version feature]** Get email notification if product price, stock or availability change, also be alerted if new product variations appear on AliExpress.

You can set email address for notifications and override the email template if needed. The plugin sends notification once per half-hour.

&#9658; **Import reviews from AliExpress**: 

**[pro version feature]** Import product reviews quickly from AliExpress, check for an appearance of new reviews as well.

Go to Ali2Woo Settings > Reviews settings.

Check out a detailed guide about [reviews settings.](https://ali2woo.com/codex/importing-reviews/)

&#9658; **Import shipping methods from AliExpress**: 

**[pro version feature]** Easily import delivery methods from AliExpress. Also, set pricing rules to add an own margin over the original shipping cost.

Go to Ali2Woo Settings > Shipping settings.

See a detailed guide on this topic [HERE.](https://ali2woo.com/codex/shipping-settings/)

[GET PRO VERSION](https://1.envato.market/KEgOy) or [https://codecanyon.net/item/aliexpress-dropship-for-woocommerce/19821022](https://1.envato.market/KEgOy)

### MAY BE YOU NEED

[Variation swatches images for WooCommerce](https://codecanyon.net/item/woocommerce-variation-swatches-images/20327701): Convert your normal variable attribute dropdown select to nicely looking color or image select. You can display images or color in all common size.

[AliExpress Shipment Tracking](https://codecanyon.net/item/woocommerce-aliexpress-shipment-tracking/22040640): Add tracking numbers to WooCommerce orders, track them using special tracking service, etc.

[eBay Dropshipping and Fulfillment for WooCommerce](https://codecanyon.net/item/ebay-dropship-for-woocommerce/21805662): Allows you to easily import dropshipped or affiliated eBay products directly into your WooCommerce store and ship them directly to your customers – in only a few clicks. Also you can place your orders on eBay.com using our FREE chrome extension.

### Documentation

- [Getting Started](https://ali2woo.com/codex/)

### Plugin Links

- [Project Page](https://ali2woo.com)
- [Documentation](https://ali2woo.com/codex/)
- [Report Bugs/Issues](https://support.ali2woo.com/)

= Helpful resources: =

Check out the following resources to be successful in dropshipping.

* [How To Find Best Dropshipping Niches + Niches List (in 2021)](https://ali2woo.com/blog/best-dropshipping-niches/)
* [Best Dropshipping Ideas For Every Season](https://ali2woo.com/blog/dropshipping-ideas/)
* [26 Best Free Traffic Sources (in 2019) - The Complete List](https://ali2woo.com/blog/4-ways-get-traffic-dropshipping-store/)
* [The Complete Guide: Dropshipping Tips for 2020](https://ali2woo.com/aliexpress-dropshipping-guide/)

= Minimum Requirements =

* PHP 5.6 or greater is recommended
* MySQL version 5.0 or greater
* WooCommerce 3.0.0+

= Support = 

In case you have any questions or need technical assistance, get in touch with us through our [support center](https://support.ali2woo.com) or send us email at lite@ali2woo.com


= Follow Us =

* The [Ali2Woo Plugin](https://ali2woo.com/) official homepage.
* Follow Ali2Woo on [Facebook](https://facebook.com/ali2woo) & [Twitter](https://twitter.com/ali2woo).
* Watch Ali2Woo training videos on [YouTube channel](https://www.youtube.com/channel/UCmcs_NMPkHi0IE_x9UENsoA)
* Other Ali2Woo social pages: [Pinterest](https://www.pinterest.ru/ali2woo/), [Instagram](https://www.instagram.com/ali2woo/), [LinkedIn](https://www.linkedin.com/company/18910479)

== Installation ==

=== From within WordPress ===

1. Visit 'Plugins > Add New'
1. Search for 'Ali2Woo Lite'
1. Activate Ali2Woo Lite from your Plugins page.
1. Go to "after activation" below.

=== Manually ===

1. Upload the `ali2woo-lite` folder to the `/wp-content/plugins/` directory
1. Activate the Yoast SEO plugin through the 'Plugins' menu in WordPress
1. Go to "after activation" below.

== Screenshots ==

1. The Ali2Woo Lite plugin build-in product search tool. 
2. The Import List page, here you can adjust the products before pushing them into WooCommerce store.
3. Built-in image editor tool, easy way to remove supplier logos for the images.
4. The Ali2Woo Lite Setting page.
5. Set up your pricing markups.
6. Remove or replace unwanted text from the content imported from AliExpress

== Changelog ==
/**2.0.1- 2021.02.14*/ 
- Fix minor bugs

/**2.0.0- 2021.01.30*/ 
- Added a feature to import product variants
- Added a feature to import unlimited products
- Added a feature to split product variants
- Added a feature to override product supplier
- Support the latest Ali2Woo chrome extension
- Support for WordPress 5.6
- Support for WooCommerce 4.9
- Fixed a lot of bugs

/**1.1.0- 2019.08.19*/ 
- Update plugin API
- Fixed minor bugs

/**1.0.3 - 2019.07.19*/ 
- Fixed the issue with an empty products description and attributes (item specifics data)

/**1.0.2 - 2019.05.23*/ 
- Fixed issues with the chrome extension
- Simpliy way to connect your store to the chrome extension

/**1.0.0 - 2019.03.16*/ 
~ The first released
== Upgrade Notice ==


 