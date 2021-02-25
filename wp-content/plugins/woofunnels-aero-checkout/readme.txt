=== Aero: Custom WooCommerce Checkout Pages ===
Contributors: WooFunnels
Tested up to: 5.5.1
Stable tag: 2.0.10
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html


== Change log ==

= 2.0.10 (2020-09-12) =
* Added: Compatibility added for 'local pickup plus' plugin by Skyverge.
* Added: Compatibility added for 'Woo Advanced search' plugin by Illid.
* Added: Compatibility added for 'Price based on country' plugin.
* Added: Compatibility added for 'Bridge core' plugin. Issue detected with Elementor, fixed.
* Added: Compatibility added for 'Sendinblue subscription' plugin.
* Added: Compatibility added for 'PostNL' plugin by Door PostNL.
* Improved: Compatibility improved of 'EU Vat Number' by WooCommerce. Issue detected with their latest version.
* Improved: Compatibility improved of 'Metorik Helper' plugin.
* Fixed: Permalink issue in case permalink wasn't saved earlier ever.
* Fixed: Change payment method from the my-account area causing issues, showing Aero checkout template, fixed.
* Fixed: Template import site URL mismatch issue fixed for WPML case.
* Fixed: Handling smart button in case cart is updated, some cases improved.


= 2.0.9 (2020-08-27) =
* Compatible with WooCommerce 4.5
* Improved: Hiding collapsible summary and breadcrumb (in case of multi-step) on order pay page.
* Fixed: Checkout pages editing broke with latest Elementor update i.e. 3.0.2, fixed.
* Fixed: Thrive builder overriding the Aero checkout page template in case of custom form, fixed.


= 2.0.8 (2020-08-24) =
* Added: Compatibility added for WooCredit plugin by WooCredits.
* Added: Compatibility added for WooSocial Login plugin by WooCommerce.
* Added: Compatibility added for F4 Shipping Phone and E-Mail for WooCommerce plugin by Faktor View.
* Improved: MyParcel Compatibility improved.
* Fixed: Order Pay URL showing 404 page not found error for some payment methods, fixed.
* Fixed: Issue with Oxygen page builder.


= 2.0.7 (2020-08-19) =
* Compatible with WordPress 5.5
* Compatible with WooCommerce 4.3 & 4.4
* Added: ShopCheckout: New shopify style checkout template added for Elementor builder.
* Added: Compatibility added for WooCommerce gift card plugin by 'SomewhereWarm'.
* Added: Compatibility added for Japanized for WooCommerce plugin by 'Artisan Workshop'. Now supports Japanized 'Delivery date' and 'Time slot' fields.
* Added: Collapsible order summary on Customizer ShopCheckout template for tablet view.
* Added: 'GetRepsonse' New field option added in the 'Email Service' dropdown field under the optimization tab.
* Added: Compatibility with 'Blocksy' theme. CSS conflict.
* Added: Enable or disable product image setting created for collapsible order summary in Elementor.
* Added: Compatibility added with WooCommerce delivery date & time pro plugin by 'CodeRockz'
* Added: Preview field: Created a new setting to change the 'change' text under the optimization tab.
* Added: Compatibility added with Tefacturo Comprobantes Electronicos plugin by 'PublicaOnline'.
* Improved: Optimized the admin pages as some duplicate queries are executing.
* Improved: In case of Bump products in the mini cart, quantity incrementor is hidden.
* Improved: Google autocomplete: address format corrected for New Zealand & Vietnam country.
* Improved: Auto selecting country based on geolocation under WC settings.
* Improved: Compatibility improved for URL coupons plugin by 'SkyVerge'.
* Improved: Checkout page network calls optimized so some speed improvement.
* Improved: Woocommerce multilingual plugin compatibility improved.
* Improved: WooCommerce currency switcher plugin (Woocs) compatibility improved for the newer version.
* Improved: Order delivery date pro plugin by 'Tyche' compatibility improved.
* Improved: Smart button UI improved in case of IE browser.
* Fixed: Removed the noindex meta from the custom pages where checkout form shortcode is used.
* Fixed: NL Postcode checker compatibility improved. Street number suffix (TOEV) field value wasn't saving in the order.
* Fixed: Support for Oxygen page builder when Elementor & Oxygen both are activated.
* Fixed: Duplicate checkout template has an error when the current page is built before Aero 2.0 version.
* Fixed: Elementor conflict for older WP versions < 5.0 and Elementor version > 2.8 produces a PHP error, fixed.
* Fixed: AeroCheckout page template was overridden with a different checkout plugin, fixed.
* Fixed: Sometime in mini cart on a checkout page, product double image issue occurred, fixed. (Recently found with Shoptimizer theme)
* Fixed: Login link on Aero checkout pages going to cart page, fixed.


= 2.0.6 (2020-05-31) =
* Added: New settings for Elementor checkout widget: Quantity increment and delete item options added for collapsible order summary.
* Added: New settings for Elementor checkout widget:  Subscription product text color under order summary.
* Added: Compatibility added with 'WooCommerce PostNL' plugin by PostNL.
* Improved: 'Shipmondo for WooCommerce' plugin compatibility improved with better handling.
* Improved: Compatibility updated of 'Finale' plugin for Elementor pages.
* Improved: Divi customizer CSS now working with Aero checkout pages.
* Improved: Flatsome theme improvement on Elementor templates.
* Improved: Subscription product text color under order summary, setting added in Elementor.
* Improved: Closer template: Amazon pay plugin related CSS improvement.
* Fixed: PayPal smart buttons updated their HTML structure recently. Fixed with the new changes.
* Fixed: Geocode JS error occurred with 'Google translate' plugin, fixed.
* Fixed: IE11, product field: Images display issue, fixed.
* Fixed: Ebanx payment gateway, optional field issue resolved with the Aero checkout pages.


= 2.0.5 (2020-05-15) =
* Improved: RTL compatibility improved.
* Added: Compatibility added with 'Order Signature for WooCommerce Pro' plugin by Super WP Heros.
* Fixed: IE11, image width issue resolved.
* Fixed: An issue discovered with smart buttons, occurring in certain conditions resolved.


= 2.0.4 (2020-05-11) =
* Fixed: Issue detected when only have one country on the checkout page, fixed.


= 2.0.3 (2020-05-09) =
* Added: Compatibility added with 'Order Delivery Date Pro' plugin by Tyche software. Now you can drag and drop delivery date fields inside Aero.
* Added: Compatibility added with 'Order Signature for WooCommerce' by Super WP Heros
* Improved: Google fonts updated to the latest.
* Improved: OceanWP theme, double overlay issue and a notice removal when editing through customizer.
* Improved: Allowing editing via Oxygen builder when no product is in the cart/ dedicated checkout page.
* Improved: Product field CSS improved for Divi builder.
* Improved: Allowing the display of smart buttons outside the mobile.
* Improved: Allow all page template related to theme to Aero checkout pages as options under page attributes.
* Improved: Allowing 'aero-default' argument to work with product field.
* Improved: Running order review ajax call in case of smart buttons when a coupon is updated i.e. added or removed.
* Improved: Google address autocomplete: Australia & UK based addresses pattern corrected.
* Improved: Gtag JS now including only on Aero checkout pages.
* Improved: Compatibility with klarna payment method improved, allowing pay later and pay overtime options too.
* Fixed: Related to 'currency switcher' plugin by villa theme, displaying currency switcher UI on the Aero checkout pages.
* Fixed: XL NMI Gateway plugin: before place order text was not changing through customizer, fixed.
* Fixed: Pagantis payment gateway: image skewed, issue resolved.
* Fixed: The "Return to Cart" text appears below the button on elementor on mobile or desktop, fixed.
* Fixed: Checkout fields outline issue & fragments stuck issue found in IE 11, fixed.
* Fixed: Mailchimp position before order button setting was not working, fixed.
* Fixed: 'Amazon Pay' logout link wasn't showing on Aero checkout pages, fixed.
* Fixed: Shipping method section overlaps on the above fields in a case when the first name and last name is not filled.
* Fixed: 'Amazon Pay' payment method by WooCommerce, compatibility fix with the latest version 1.12.0
* Fixed: Tracking and analytics: Gtag - AddPaymentInfo event overridden setting wasn't working, fixed.
* Fixed: Aero checkout widget wasn't showing inside elementor widgets list when 'Premium Addons for Elementor' by Leap13 is activated, fixed.
* Fixed: An issue when WeGlot was rendering its widget on Aero checkout pages.
* Fixed: An error found with Payment express payment method when express buttons are not active, Fixed.
* Fixed: Shipping address overriding Billing address values, a rare scenario found, resolved.


= 2.0.2 (2020-04-17) =
* Improved: Product field: UI optimized in case of quantity column is not present on mobile.
* Fixed: Issue with Generatepress lite plugin. Unable to open the customizer preview for editing the Aero checkout page.
* Fixed: Payment gateways section loading issue on certain servers in private (incognito) mode on mobile, fixed.
* Fixed: Wrong order total after applying or removing the discount on checkout with payment method Google Pay smart button, fixed.
* Fixed: Redirection to global checkout page while doing order with klarna payment method in a specific case of embedding a custom checkout form shortcode, fixed.
* Fixed: Compatibility updated of 'NL postcode checker' plugin (latest version) with WC MyParcel plugin.
* Fixed: Compatibility updated of 'Brazilian Market on WooCommerce' plugin latest version, calling address fields twice on the checkout.
* Fixed: Postcode NL checker plugin: Billing suffix field is optional but was mark required through Aero (JS validation), fixed.
* Fixed: Woocommerce yapay payment gateway: Icon images were distorted on the payment gateways section, fixed.
* Fixed: Brazilian Market on WooCommerce: Adds Billing Sex field. Its value wasn't coming pre-filled in case of logged-in users.
* Fixed: Loading spinner keeps on showing on Mozilla browsers on the input number field, issue fixed.


= 2.0.1 (2020-04-10) =
* Improved: Design > Custom Tab now displays shortcode to embed form on other pages.
* Improved: Performance improvement after setting auto-loading options to no.
* Improved: Made Aero checkout custom forms RTL compatible.
* Fixed: Order made from payment gateway 'PayPal express' (by Angelleye) smart button is not saving the buyer email in the order, fixed.
* Fixed: Compatibility issues with 'NL postcode checker' plugin, when activated with 'MyParcel' plugin, fixed.


= 2.0.0 (2020-04-08) =
* Added: Compatible with WordPress 5.4
* Added: New Design with One/ Two/ Three step templates allowing the user to import design with a single click.
* Added: Elementor: Introduced pre-built Elementor templates. Six design concepts for each step, totalling 18 templates.
* Added: Elementor: Two new widgets for Checkout form and Mini Cart. Now enjoy complete visual editing experience right inside elementor.
* Added: Page Builders: Use Custom tab to create Aero checkout pages using page builders such as Divi, Beaver, Thrive and  Oxygen.
* Added: Utility: Import/ Export feature to quickly port over design from one site to another.
* Added: Optimization: Added new Optimization menu.
* Added: Optimization: Google Address Autocomplete optimization added.
* Added: Optimization: Smart payment buttons for Express checkout introduced. It supports Amazon & Stripe's Apple Pay & Google pay.
* Added: Optimization: Preferred countries which prioritise countries to show in Country dropdown.
* Added: Optimization: Generate URL to populate checkout which helps you generate a link and pre-populate checkout using from URL. Enhanced support for CRMSs such as  ActiveCampaign, Drip, ConvertKit, Infusionsoft and MailChimp.
* Added: Support for Google Analytics for AddtoCart, BeginCheckout and AddPaymentInfo Events in Global Settings.
* Added: Tracking and Analytics Settings per checkout. Useful in case you are running one-page checkouts and want to adjust how events fire per checkout.
* Added: Two new field types Select2 and Multiselect type fields added.
* Added: Ability to drag and drop billing and shipping fields.
* Added: Ability cart editing for ShopCheckout template.
* Added: Compatibility added with official 'EU Vat Field' plugin by WooCommerce.
* Added: Compatibility added with 'WooCommerce MyParcel' plugin.
* Added: Compatibility added with 'Simple sales tax' plugin.
* Added: Compatibility added with 'Aweber' plugin.
* Added: Compatibility added with 'Afterpay' payment gateway.
* Added: Compatibility added with 'WooCommerce PDF Invoices & Packing Slips' plugin by Ewout Fernhout.
* Added: Compatibility added with 'Advanced dynamic pricing' plugin by Algol plus (Only for global checkout).
* Added: Compatibility added with 'ShipMondo' plugin.
* Improved: Admin UI improved for better user experience.
* Improved: Aero would now have a simple drop-down (full width, one-half, one-third) to control field width instead of classes. You can still use classes in the advanced section
* Improved: Customizer options re-arranged to make it beginner-friendly.
* Improved: Showing Aero checkout pages in preview mode without adding products.
* Improved: Allowing the last item to be deleted from Product Switcher element.
* Improved: Displaying tax status in the order total.
* Improved: Compatibility issues with 'Authorized' payment gateway by Skyverge, with the latest version, resolved.
* Improved: Compatibility issues with 'Klarna' payment gateway, with the latest version, resolved.
* Improved: Compatibility issues with 'Angelleye' payment gateway, with the latest version, resolved.
* Improved: Compatibility issues with 'ActiveWoo', with latest version resolved (now Woocommerce ActiveCampaign).
* Improved: Compatibility issues with 'Oxygen' builder, with the latest version, resolved.
* Improved: Compatibility issues with 'Divi' builder, with the latest version, resolved.
* Improved: Compatibility issues with 'NL postcode checker' plugin, with the latest version, resolved.
* Improved: Compatibility issues with 'Germanized' plugin, with the latest version, resolved.
* Fixed: Allowing Aero checkout form sections and fields label removal.
* Fixed: Cart data is not going to klaviyo when buyer changes the email address.
* Fixed: Disabled the auto-populating fields via URL parameter for logged in users.
* Fixed: Displaying variant name in the product switcher field, fixed.
* Fixed: Aero checkout pages, 'aero_add_to_checkout' query argument to add products to the cart fixed for quantities.
* Fixed: Coupon remove UI glitch fixed.
* Fixed: Compatibility added with 'North' theme, JS fixes done.


= 1.9.3.5 (2020-03-11) =
* Fixed: Issue of Coupon object data printing on checkout page.
* Fixed: Compatible with WooCommerce 4.0


= 1.9.3.4 (2020-03-05) =
* Fixed: Street address 2 field made mandatory in WooCommerce Square payment method v2.1 caused a conflict with AeroCheckout, fixed now.


= 1.9.3.3 (2020-02-20) =
* Fixed: Scenario where no products were added to the dedicated checkout pages.


= 1.9.3.2 (2020-01-24) =
* No change


= 1.9.3.1 (2020-01-24) =
* Fixed: Compatible with WooCommerce 3.9


= 1.9.3 (2019-09-09) =
* Added: Compatibility with 'WooCommerce - Store Exporter Deluxe' plugin (author: Visser Labs), allowing Aero custom checkout fields in order export.
* Added: Compatibility with 'Loco Translate' plugin (author: Tim Whitlock), allowing translations of default checkout form data.
* Added: Compatibility with 'AutomateWoo' plugin (author: Prospress), a new custom aero checkout field to provide the ability to subscribe newsletters.
* Added: Compatibility with 'WooCommerce Gift Certificates Pro' plugin (author: Ignitewoo), allowing custom checkout fields on Aero checkout page.
* Improved: Validation working on an individual step in a unique scenario where product field is used and variation product exists.
* Improved: Payment methods loading UX experience on checkout improved.
* Fixed: Validation on radio buttons UI fixed in case of multi-step form.
* Fixed: Shipping methods additional text issue resolved.
* Fixed: Next step button position UI issue in case of multi-step forms.


= 1.9.2 (2019-08-16) =
* Added: Compatibility with 'TranslatePress' plugin (author: Cozmoslabs), allowing translation of strings in the admin area.
* Improved: Shopcheckout: steps bar UX improvement.
* Improved: Hard string 'change' localization corrected.
* Improved: Product switcher UI improvement on mobile devices
* Improved: Allow dynamic changing of shipping labels using a filter hook.
* Fixed: Shipping first name and last name values wasn't copied from the billing field in a specific scenario, fixed now
* Fixed: Auto apply coupon setting in Aero checkout page, overridden with default settings in a specific case, fixed now.
* Fixed: Bundle product child items deletion icon was displaying in product switcher, fixed.
* Fixed: JS (nicescroll) conflict with Legenda theme, fixed on Aero checkout pages.
* Fixed: Plugin listing screen caused PHP error on load in a specific scenario, fixed.


= 1.9.1 (2019-08-01) =
* Added: Shake effect on 'Best Value' item after selecting different product.
* Fixed: Product switcher field: Custom name wasn't showing, resolved.
* Fixed: Blank JS parse issue occurred, fixed.


= 1.9.0 (2019-07-31) =
* Added: Onboarding experience added and made pre-built templates.
* Added: Checkout form fields preview feature added, this adds the ability for buyers to preview the last step filled data on a current step.
* Added: New field 'Order total' introduced. You can now opt for this field instead of Order Summary & converse space.
* Added: Allowing dynamic step name merge tags in multistep form. Use [step_name] to generate a link to previous step. Instead of using text "Back" you can now use text "Return to {{step_name}}""
* Added: Deep field level integration with below plugins. Now Aero would automatically detect these plugins and register a field inside Form editor. Drag and drop fields from these plugins and use them anywhere in the form.
   - WooCommerce Checkout Add-Ons (by SkyVerge) fields plugin.
   - WooCommerce Constant Contact (By SkyVerge).
   - WooCommerce Subscribe to Newsletter (By WooCommerce).
   - ActiveCampaign for WooCommerce (by ActiveCampaign) plugin.
   - WooCommerce NL Postcode Checker (Ewout Fernhout) plugin.
   - AutomateWoo - Birthdays Add-on (Prospress) plugin.
* Added: HTML widgets section added for checkout pages built via customizer.
* UX Improvements: Tons on UX improvements, here are few:
   - Smooth transition for multi-step forms.
   - On applying Coupon, showing a coupon SVG graphic before the coupon name.
   - Shipping methods would sort from low to high cost, and lowest will be default selected (avoid shipping cost stick shock).
   - Subtle Hover and Focus colour added on fields.
   - Improved behaviour with variation products when product-specific order forms are used.
   - Visited steps are now filled. Earlier it used to fill only selected step.
   - Some icon images replaced from png to SVG format.
   - Let users navigate back to previous steps without triggering validation for current steps.
* Improved: Compatibility with Aelia Currency Switcher for prices set at each variation level.
* Improved: Compatibility with Klaviyo for WooCommerce V2 (Klaviyo, Inc.), sending events with when a user switches products.
* Improved: Compatibility with Bundled Products. Deleting items would delete the complete bundle.
* Improved: IE 11 various improvements.
* Improved: CSS ready classed on WooCommerce Extra Checkout Fields for Brazil (Author name: Claudio Sanches).
* Improved: FB Initiate Checkout and AddtoCart events now contain custom parameters.
* Fixed: AffiliateWP (AffiliateWP) plugin tracking .js issue with AeroCheckout.
* Fixed: Bug with PayPal AngellEYE and Germanized plugin using smart buttons.
* Fixed: Various fixes for the latest version of Germanized for WooCommerce (v2.3.2) plugin.
* Fixed: Fixed an issue with MercadoPago.
* Fixed: PayPal Express Checkout confirmation page, displayed an error message on top and added handling with custom fields.
* Fixed: Discounting logic fixed for multiple quantities and fixed price discount.
* Fixed: Few keys were not duplicating when form duplicated (hide quantity, product deletion, hide custom description checkboxes etc.), fixed.
* Fixed: SG optimizer combined CSS was causing issues, fixed.
* Fixed: Saved Card styling issues for some themes.
* Fixed: Dropdown custom field value was not saving issue resolved.
* Fixed: Various CSS fixes with PUCA theme, Shoptimizer, DavinciWoo, Flatsome (Google Fonts feature), Boss theme


= 1.8.4 (2019-05-16) =
* Added: Compatibility with 'WP Admin white label login' plugin (Author: Ozan), conflicting with customizer, now allowing to edit checkout page, fixed.
* Added: Compatibility with 'Constant contact' plugin (Author: SkyVerge), added email optin field in the admin form fields area.
* Improved: Modifying Order cancel URL in case of Aero dedicated checkout page and Embedded form checkout page only.
* Improved: An edge scenario with PayPal express checkout where active session doesn't exist, which results in PHP error.
* Fixed: AffiliateWP View Tracking in case fallback mode is not enabled.
* Fixed: Coupon auto removed during product switch in radio mode only, fixed.
* Fixed: WPML edit link issue resolved.
* Fixed: On AeroCheckout pages, in some scenarios mini cart checkout link changed to AeroCheckout dedicated checkout page, fixed.
* Fixed: Storefront latest version adds some JS which was breaking the customizer while customizing the checkout form, fixed.
* Fixed: Gateway conflict with latest version of WC Germanized, compatibility updated
* Fixed: Multistep form: Custom Field 'Checkbox' field validation fixed.
* Fixed: Additional code handling with 'uncode' theme, as it was causing styling conflicts.


= 1.8.3 (2019-04-23) =
* Added: Compatible with NextMove plugin, auto displaying custom form fields in advanced option of order summary component.
* Added: CSS Compatibility added with Pagseguro, Gerencianet, Paytral gateway, radio input boxes & alignments corrected.
* Improved: Order summary component was displaying shipping method name, now it is hidden and can be visible using PHP filter hook.
* Improved: During multi-step form, select2 js re-init when step is changed through breadcrumb links.
* Fixed: PayPal payment method input selection small CSS fix.


= 1.8.2 (2019-04-19) =
* Fixed: Hiding non-purchasable i.e. private products from product field.
* Fixed: Displaying error message from PayPal in case buyer enters the invalid address, fixed.


= 1.8.1 (2019-04-18) =
* Fixed: Shipping method field sometimes showing a spinner even after getting the shipping options, fixed.
* Fixed: Shop checkout template: Order Summary in the right sidebar spacing adjusted.


= 1.8.0 (2019-04-17) =
* Product Switcher UI/ UX improved.
* Product Switcher, admin settings UI, improved and new fields introduced.
	- Product title option added and carry forward to order emails or Thank you page.
	- Ability to delete items or recover deleted item from checkout (applicable for global checkout and specific checkout pages (with force all products option))
	- Option added to choose the 'best value' product.
	- 4 new positions are introduced to change the location of 'best value tag'.
	- Option added to Show/ Hide product images.
	- Ability to customize 'You save' text per item.
* Added: New custom field - HTML field type added. Easily add HTML blocks in between the checkout form fields.
* Added: Allow saving of the last step without a field so that only payment method field can come on the last step.
* Added: Single order admin view, showing AeroCheckout page details to track the order checkout source.
* Added: Global settings -> External script setting added. That will be added to each checkout page.
* Added: Showing error message on the checkout form if in case all the desired products are out of stock.
* Added: Compatible with Aero Embed form latest version 1.5.
* Added: Compatible with OrderBump latest version 1.6.
* Added: Compatible with the Support tracking feature of Metorik official plugin.
* Added: Compatible with Woocommerce Currency switcher by realmag777 and Aelia currency switcher. Allowing changing of currency and prices on the checkout page in product switcher.
* Improved: Admin messages/ notices on the checkout form builder page in case form is mis-configured.
* Improved: Various changes done to improve the end-user experience on checkout form.
* Improved: Quick view UX improved.
* Improved: Triggering Facebook initiate checkout event on page load of dedicated checkout pages.
* Improved: Hiding Product switcher item image on mobile screens below 375px.
* Improved: Cancel URL for PayPal payment method modified in case checkout is performed from a dedicated checkout page.
* Improved: Shipping method field is now compatible with WC subscription products. Displaying subscription products shipping inline as well.
* Improved: Hiding payment information heading in case cart is not eligible for payment.
* Improved: Handling with subscription orders in case buyer opted to pay from my account area using pay now link.
* Improved: Shop checkout template, coupon form in the sidebar now following a collapsible approach like native WC.
* Improved: All checkout templates, images, and icons are optimized to increase the page load speed.
* Improved: Finale plugin compatibility improved, now allowing the display of sticky header or footer on checkout pages.
* Improved: WooCommerce Extra Checkout Fields for Brazil Plugin compatibility improved, in case checkout form is multistep and compatible fields are used on the 2nd or 3rd step.
* Fixed: Wrong shipping sometimes appeared in case of a subscription product and with a specific form configuration, fixed.
* Fixed: Hidden type custom field had required checkbox field, removed now.
* Fixed: Not displaying shipping as 'free' when there are no shipping methods available.
* Fixed: Authorized CIM gateway overriding checkout place order button text, fixed.
* Fixed: Handled a scenario when shipping method field is used, and cart contains only virtual products.
* Fixed: Classic template layout fixed for tablet portrait mode or lower viewports.
* Fixed: Place order button label was changing their value to default on fragment refresh call, fixed.
* Fixed: Showing testimonials dynamically based on the added cart products if automatic option is selected.


= 1.7.2 (2019-01-31) =
* Added: Compatible with 'Featured Image From URL' plugin (Author: Marcel Jacques Machado), calling default images issue resolved on the product tab.
* Added: Compatible with 'Flux checkout lang' plugin (Author: Fluxcheckout.com), as their JS breaking the customizer editing.
* Added: Did additional code handling with 'Shop isle' theme, as it was adding customer login section auto.
* Fixed: Validation error display on new Coupon field.
* Fixed: Scroll fixed issue resolved in case of themes override a CSS property.


= 1.7.1 (2019-01-29) =
* Added: Compatible with 'Send cloud shipping' plugin, added dynamic shipping calculation button aside shipping method box in Aero checkout pages
* Improved: 'name' attribute added in the quantity input field, as some plugins js causing JS error.
* Improved: Some textual improvements for better UX.
* Fixed: Some themes removed payment block, attaching on the correct hook again for Aero pages.
* Fixed: iOS Mobile Safari scrolling issue.


= 1.7.0 (2019-01-24) =
* Added: Did additional code handling with 'unero' theme, as it was causing styling conflicts.
* Added: Did additional code handling with 'TCS - Auto Add To Cart Freebie' plugin, to auto allow adding of Freebies to cart.
* Added: Did additional code handling with 'Zerif Lite' theme, js conflict on checkout pages.
* Added: Did additional code handling with 'MercadoPago payment gateway' plugin, modify bank EMI options on change of products on a checkout page.
* Added: New 'Coupon' field introduced.
* Added: Did additional code handling with 'Aelia EU Vat assistant' plugin, allowing conditional VAT field based on a country selection.
* Added: Did additional code handling with 'Klaviyo' plugin. Added 'Subscribe' field in checkout form.
* Added: Admin notice if Aero checkout slug is similar to WooCommerce checkout endpoint.
* Added: Displaying selected product stock status while adding products in the checkout form in Admin.
* Added: Allow using CSS ready classes to checkout form fields.
* Added: Did additional code handling with 'EU Vat Premium Field' plugin (Author: David Anderson), added vat field in the admin form fields area.
* Added: Did additional code handling with 'WooCommerce Drip Field' plugin (Author: WooCommerce). Added 'Subscribe' field in checkout form.
* Added: Did additional code handling with 'FooEvent' Plugin (Author: FooEvents), allow adding events related fields in the checkout.
* Added: Did additional code handling with 'eStore' theme, JS was breaking on the customizer page.
* Added: Disable cache on checkout pages notification added for multiple cache plugins.
* Improved: wp-ajax endpoint replaced with wc-ajax for speed improvement.
* Improved: restrict update_checkout trigger call when there is no need to run.
* Improved: Handled dom exception error in the customizer during multiple iframes.
* Improved: Handled cart total 0 scenario.
* Improved: Disallow saving of 'username', 'password', 'user email' fields values in user storage.
* Improved: Handled product 'sold individually' option in admin.
* Improved: Handled a scenario when shipping field is used and shipping disabled on WC end.
* Improved: MailChimp styling issue on the pre-built checkout templates.
* Fixed: Hiding error messages of a step on validation success to a next step.
* Fixed: Customizer changeset error message resolved.
* Fixed: Billing and Shipping fields: first name and last name fields values carry forward when the respective field is same and hidden.
* Fixed: Slashes issue resolved in global setting on CSS field save.


= 1.6.1 (2018-12-21) =
* Added: Did additional code handling with 'Leka' theme, as it was causing styling conflicts.
* Added: Did additional code handling with 'One store' theme, as it was breaking the customizer.
* Fixed: Handled a scenario when cart only contains a virtual product hence hide the shipping methods.


= 1.6.0 (2018-12-20) =
* Added: Did additional code handling with 'Avada theme', as converting normal checkout pages into multi-pages checkout. (version 5.7.1)
* Added: Did additional code handling with 'OceanWP theme', as it was causing styling conflicts.
* Added: Did additional code handling with 'Buzstore theme', as it was causing styling conflicts.
* Added: Did additional code handling with 'Easy google font customizer' plugin by Danny Cooper, issues with the customizer.
* Added: Did additional code handling with 'Ebanx gateway' plugin, modifying certain fields on Aero checkout pages.
* Added: Global custom CSS field added in the global setting for Aero checkout pages.
* Added: Did additional code handling with 'Thrive Leads' plugin, Thrive pop-ups now displaying Aero checkout pages.
* Added: Billing and Shipping Company & Address 2 fields support added.
* Added: Did additional code handling with 'InfusedWoo' plugin, now supports soft subscription products.
* Added: Supporting WooCommerce Bundle products, YITH Bundle products & Smart Bundle products product types.
* Improved: Coupon related calls optimized.
* Improved: Correct price display in case of subscription products in a product list component. Displayed subscription full textual line and correct price to be charged.
* Improved: Checkout page, header logo is not clickable when a link is not set.
* Improved: Handling a scenario when the product is set to sold individually.
* Fixed: First name and last name field values copied to a respective hidden field (Billing or Shipping).
* Fixed: ActiveWoo plugin compatibility fixed for a certain case.


= 1.5.6 (2018-12-10) =
* Fixed: ShopCheckout template: Coupon field display on mobile corrected.


= 1.5.5 (2018-12-09) =
* Added: Did additional code handling for 'Ocean' theme, as it was modifying the Aero checkout pages.
* Added: Did additional code handling with 'Google font' plugin, as it was causing JS conflicts on customizer.
* Fixed: ES6 JS Compatibility Fix for IE.


= 1.5.4 (2018-12-08) =
* Added: Did additional code handling with 'Square Payment gateway', as it was causing styling conflicts.
* Added: Did additional code handling with 'Checkout address autocomplete for WooCommerce' plugin by eCreations
* Added: Did additional code handling with 'WooCommerce Measurement Price Calculator' plugin By SkyVerge, as it was causing wrong discount display with multiple quantities.
* Added: Did additional code handling with 'Checkout Field Editor' plugin by WooCommerce, to avoid any conflicts.
* Added: Did additional code handling with 'Magic order' plugin by Ridwan Pujakesuma, causing JS conflicts at Aero checkout pages.
* Added: Did additional code handling with 'Astra Pro Addon' plugin by Brainstorm force, as some of its checkout settings were causing conflicts at Aero Checkout page.
* Improved: Code optimized for User email checking.
* Improved: Subscription Product Order Summery Styling compatibility Added.
* Improved: ShopCheckout/ Marketer template: Order Summary distorted when subscription product in the cart.
* Improved: Handling scenarios when ship to specific countries option selected
* Fixed: Mixed product type issue resolved on Aero checkout pages.


= 1.5.3 (2018-12-04) =
* Fixed: WooChimp plugin compatibility one function causing PHP error, fixed now.


= 1.5.2 (2018-12-04) =
* Added: WPML Compatibility: Giving an option to create checkout pages per languages by clicking country flag. Some more functional handlings done.
* Added: Did additional code handling with 'Twilio SMS Notification' plugin, adding user optin checkbox field below the billing email field.
* Added: Did additional code handling with PaynowCw gateway, place order leads to 'pay now' page.
* Added: Did additional code handling with Jupiter, Nitro and Puca theme, assets fixes.
* Added: Did additional code handling with MailChimp for WooCommerce by MailChimp.
* Added: Did additional code handling with MailChimp for WordPress by Libercode.
* Added: Did additional code handling with MailChimp for WooCommerce by Saint System.
* Added: Did additional code handling with Checkout field editor plugin, causing conflicts at Aero Checkout page.
* Added: Did additional code handling with WordPress 5.0
* Improved: Loading checkout page inside assets caused cart data mismanagement), fixed now
* Improved: Design Customizer return URL now going back to the design page of the respective checkout page.
* Improved: Active state added in breadcrumb for all pre-built layouts. Same with the progress bar on the shop checkout template.
* Improved: Styling Compatibility added for 'Woocommerce Easy Checkout Fields Editor' plugin for all pre-built layouts
* Improved: Styling issue with Safari MAC.
* Improved: CSS fixes for countries drop down on the checkout page when shipping to a single country.
* Fixed: Current user saved WC cart session, sometimes overrides the current cart session, fixed now.
* Fixed: Sustain query arguments on a checkout page, during WordPress user login call.
* Fixed: Country/ state dropdown sometimes appearing distorted on the selected browser, fixed now.
* Fixed: Redeclare Template issue with PHP 7.2.12
* Fixed: Multistep checkout: place order button wasn't showing with Amazon Pay gateway, fixed.
* Fixed: Flatsome one customizer field JS conflict with checkout page, fixed.


= 1.5.1 (2018-11-17) =
* Improved: Used Swal js library which is causing conflicts with other plugins Swal library, fixed now.
* Improved: Remove Dependency of order summary field in cart widget for ShopCheckout layout.
* Improved: Passed WP default date format in testimonials date.


= 1.5.0 (2018-11-16) =
* Added: Compatible with WooCommerce 3.5
* Added: Global checkout: displaying savings text in product list field.
* Added: Did additional code handling with Amazon Pay payment gateway.
* Added: Did additional code handling with Woochimp plugin.
* Added: Did additional code handling with 'Improved Variable Product Attributes for WooCommerce' plugin, causing design issues with product list field.
* Added: Did additional code handling with 'WooCommerce Multilevel Referral' plugin, causing issues with 'create an account' field.
* Added: Did additional code handling with the Paytrail gateway, design issues were there.
* Added: Did additional code handling with Porto theme, some design issue was there.
* Added: Did additional code handling with 'post smart-ship' plugin by Webbisivut.
* Improved: Aero fragments calls code optimized for faster loading.
* Improved: hover over the checkout form fields now gives an impression of click to see field-specific data.
* Improved: Some hooks modified to avoid conflicts with 3rd party plugins.
* Improved: Multistep checkout: Terms & conditions field validation, showing field label to recognize the field.
* Improved: Multistep checkout: back button sometimes overlapping, CSS fixes.
* Improved: Multistep checkout with shop checkout template: progress bar issue on the last step when checkout via PayPal Express Checkout.
* Improved: UX maintained when removing coupon from order summary.
* Improved: Sometimes store owners don't choose the country field and geolocate if disabled as well, so handled the scenario as payment gateways required user country.
* Improved: Smooth scroll to form top when switching the steps.
* Fixed: US-themes has some styling issues, corrected.
* Fixed: Product list field: Prices now respecting WC tax input settings.
* Fixed: Braintree payment gateway: sometime credit card input fields gets hidden during multistep checkout, fixed now.
* Fixed: Theme customizer 'custom style' issue resolved.
* Fixed: Fetching reviews of a product stopped in woocommerce 3.5, as they modified their code 'comment type', fixed now.
* Fixed: Sometimes deactivation and activation back needs permalink reset on Checkout pages, auto done.


= 1.4.1 (2018-11-03) =
* Added: Compatibility with Oxygen page builder.
* Added: Compatibility with PayPal Express plugin v1.6.5. They modified their JS.
* Improved: Compatibility with Kirki, issue occurred with Flatsome theme.
* Fixed: WooCommerce modified their payment.php template after v3.3. Causing conflicts with 3.3 version.
* Fixed: Some PHP notices fixed.
* Fixed: Display savings 100% when complete savings in product switcher.


= 1.4.0 (2018-10-31) =
* Added: Compatibility with WC Custom Thank You
* Added: Provide Partial compatibility of woocommerce germanized plugin for our pages
* Added: Display custom field Field ID  for shipping & billing address when user edit the field in backend
* Added: new field added when use choose their button position is fixed or not using field
* Added: tel number new field added and called on the all 4 templates and  header 3 layout for shop checkout page
* Added:  new field added for mobile mini cart on shopcheckout template which are related to text translation
* Improved: Add validation for email field. `Billing Email field must be on step 1 for the form`
* Improved: Restrict Maximum Possible discount to 100 At Product table
* Improved: Reload Checkout page when we add subscription product and removed. After removing subscription product cart get empty then we reload the checkout page for tackle the session expired error message
* Improved: Rename Field ID to Field ID (Order Meta Key) add field form
* Improved: Sustain Best value parameter in session. To display proper best value product when woocommerce fragment ajax call running
* Improved: Stop Execution(loading) of aero checkout data on woocommerce my account page
* Improved: Replace all fragments calls with our ajax to make faster experience. Restrict our fragment call to our ajax only no fragment generate for woocommerce calls
* Improved: Replace ShopCheckout additional Fragment with our fragment calls.
* Improved: Set proper position these hooks woocommerce_checkout_before_order_review woocommerce_checkout_after_order_review
* Improved: Now serve payment.php terms.php payment-methods.php from our plugins folder. To avoid Template override by themes
* Improved: when resize the screen then no padding was adding then change the structure for default spacing on mobile
* Improved: header menu html changed when not menu added than space will be not showing on showing in page for desktop and mobile view.
* Improved: distorted order summary custom field issue resolved on the mobile
* Improved: default font setting changed, font increased for edit title and sub title text in admin panel
* Improved: woocommerce fields translation added on checkout form.
* Improved: changed text of form express checkout using woocommerce text domain for translation
* Improved: theme compatibility with electro and theme x
* Improved: when no breadcrumb added in the field then hide the field no icon will be display on all templates
* Improved: default hide product element from the visibility on shopchekout page
* Improved: removed enable product element setting under the product section.
* Improved: breadcrumb will be off for only ShopCheckout template not rest of.
* Improved: fragment issue resolved for rehub theme compatibility
* Improved: worked on the admin notices
* Fixed: Display Loader GIF over the shipping-method when we change the state drop down value
* Fixed: Custom field data not pull from our shortcode for woocommerce native fields
* Fixed: Sometimes multiple product highlight in product switcher(Radio Case). Now this issue resolved by sustaining the current added product item key  in ajax
* Fixed: Stock Status issue. Previous when manage stock is off product then we not check stock status of product at time of add to cart. Now i add stock status checking of product when manage product is uncheck in product setting
* Fixed: spacing issue for order summary on all layouts
* Fixed: cart section text field dynamic on shopcheckout
* Fixed: back button position overlapping issue resolved on mobile
* Fixed: when single country selected from woocommerce back end option field then their styling will be not distorted and border setting is working fine for this fields
* Fixed: worked on the notices for templates


= 1.3.1 (2018-10-27) =
* Fixed: Hidden Shipping fields required validation, prompting during checkout, fixed now.


= 1.3.0 (2018-10-21) =
* Added: Make an attempt to auto-populate State in address field when Zip code and Country are filled.
* Added: Compatibility with ActiveWoo & AutomateWoo Abandonment Cart plugin.
* Added: New Header & Footer style introduced in Shop Checkout template. That makes it closer to Shopify checkout experience.
* Added: Shortcode Introduced for printing checkout field for label/values [wfacp_order_custom_field field_id="my-checkout-field" type='label']/[wfacp_order_custom_field field_id="my-checkout-field" type='value']
* Added: Compatibility of Datatrans payment gateway with our plugins
* Improved: Payment Information Field added under the Form section in Customizer. Heading and Sub heading can be changed for Payment Information field.
* Improved: Breadcrumb Styling improved for the desktop. And removed from all layouts on mobile devices.
* Improved: 'Select an option' text replaced to 'Choose an option'. This is in sync WooCommerce native behaviour. Will auto-translate for different languages.
* Improved: "Hide Additional Information" field added in Customizer. A user can hide additional information from the product switcher section.
* Improved: Compatibility check for current WooCommerce version.
* Improved: Classes position swapped for between shipping and billing address inside Customizer.
* Improved: 'Back' button text is dynamic now, the option is under the form setting section.
* Improved: Subscription Recurring Total text colour style changed.
* Improved: Default Image added inside admin settings when no product image available from the product list. And some minor enhancements are done for coupon button for mobile.
* Improved: Show warning in admin while saving Form when billing email is missing.
* Fixed: Auto-fill fields when returning user logs in.
* Fixed: Handling of multiple checkout pages when a user opens two checkout pages at same browser. After reloading user now place order successfully.
* Fixed: When billing field have a first_name,last_name and shipping does not have first_name,last_name then auto-fill shipping first_name,last_name from billing fields
* Fixed: Loader display infinite time when product stock is low
* Fixed: .00 percentage in you save text issue in product list field
* Fixed: Customizer compatibility issue resolved for theme Shoptimizer Version 1.2.1
* Fixed: modal pop up Distorted design CSS issue resolved when user choose the different skin from the order bump template list for Divi theme 3.17.1
* Fixed: Amazon Pay section CSS fixes for all page builders.
* Fixed: WooCommerce social login CSS fixes for all page builders except Divi Builder.
* Fixed: Removed notices from sidebar order summary for ShopCheckout template.
* Fixed: Sustain cart has values. The issue arises when multi checkout pages are opened, and different products are added. It's fixed.


= 1.2.0 (2018-10-12) =
* Added: Some components didn't have the visibility control, added now.
* Added: Did additional code handling with PayPal Express gateway plugin.
* Added: Did additional code handling with WooCommerce Social Login plugin.
* Added: WP Embed shortcode support added.
* Added: Compatible from WordPress min version 4.9
* Improved: State/ County validation should be based on the selected Country.
* Improved: Checkout pages admin UI improved.
* Improved: No widget gets displayed if no inner content available.
* Improved: Assurance widget can now have images as well.
* Improved: Tested with popular themes and resolved their CSS bugs.
* Fixed: Some notices were coming in product switcher.
* Fixed: Conflict with kirki autoload library with some themes where kirki inbuilt used.


= 1.1.0 (2018-10-05) =
* Added: Allowed Course product type from LearnDash plugin to include as a product in a Checkout page.
* Added: Sustain user-filled checkout form data in a user session, so that if page reloads or during next offer, data should come pre-filled.
* Added: A feature to pre-populate checkout form data using URL arguments/ parameters like billing_first_name=john&billing_email=john@example.com
* Added: Allowed capability to assign Best Value from the URL in a Product switcher like http://example.com/checkout/aero/?aero-best-value=1
* Improved: Assigning 'Aero Checkout page' as a default checkout page. Page options called in alphabetical order.
* Improved: Shop Checkout design tablet view improved.
* Fixed: Variable product image wasn't showing in Shop Checkout design, fixed.
* Fixed: Some PHP notices were coming, resolved now.


= 1.0.0 (2018-10-03) =
* Public Release