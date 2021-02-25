<?php
/**
 * Smart Offers Admin Install
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.1.5
 *
 * @package     smart-offers/includes/admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SO_Admin_Offer' ) ) {

	class SO_Admin_Offer {

		function __construct() {
			add_action( 'add_meta_boxes', array( $this, 'add_smart_offers_custom_box' ) );

			add_action( 'wp_ajax_woocommerce_json_search_offers', array( $this, 'woocommerce_json_search_offers' ), 1, 2 );
			add_action( 'wp_ajax_woocommerce_json_search_prod_category', array( $this, 'woocommerce_json_search_prod_category' ), 1, 2 );
			add_action( 'wp_ajax_woocommerce_json_search_coupons', array( $this, 'woocommerce_json_search_coupons' ), 1, 2 );
			add_action( 'wp_ajax_woocommerce_json_search_products_and_only_variations', array( $this, 'woocommerce_json_search_products_and_only_variations' ), 1, 2 );
			add_action( 'wp_ajax_woocommerce_json_search_product_attribute', array( $this, 'woocommerce_json_search_product_attribute' ), 1, 2 );

			add_action( 'admin_enqueue_scripts', array( $this, 'so_admin_script_and_style' ) );

			add_filter( 'enter_title_here', array( $this, 'woo_smart_offers_enter_title_here' ), 1, 2 );
			add_filter( 'default_content', array( $this, 'so_add_default_content' ) );
			add_filter( 'post_updated_messages', array( $this, 'so_add_custom_messages' ) );

			// To add product variation shortcode on save post
			add_filter( 'wp_insert_post_data', array( $this, 'add_shortcode_in_post_content' ) );

			// To add accept & skip link if not found in the offer content
			add_action( 'admin_footer', array( $this, 'so_add_accept_skip_links' ) );
		}

		/**
		 * Show metaboxes in SO
		 */
		function add_smart_offers_custom_box() {
			global $pagenow, $typenow;

			if ( $pagenow != 'edit.php' && $typenow != 'smart_offers' ) {
				return;
			}

			$so_offer_type = self::get_offer_type();

			$so_metabox_heading_text = 'order_bump' === $so_offer_type ? __( 'Setup your Order Bump offer', 'smart-offers' ) : __( 'Setup your offer', 'smart-offers' );

			add_meta_box( 'so-offer-data', $so_metabox_heading_text, array( $this, 'so_offer_setup_meta_box' ), 'smart_offers', 'normal', 'high' );
			add_meta_box( 'smart-offers-custom-css', __( 'Custom style (CSS)', 'smart-offers' ), array( $this, 'so_custom_code_block' ), 'smart_offers', 'normal', 'high' );
			add_meta_box( 'smart-offers-priority', __( 'Offer priority', 'smart-offers' ), array( $this, 'so_priority_meta_box' ), 'smart_offers', 'side', 'default' );
			add_meta_box( 'so-shortcodes', __( 'Available shortcodes', 'smart-offers' ), array( $this, 'so_available_shortcodes' ), 'smart_offers', 'side', 'default' );

			// Add blog links only if creating new offer
			if ( 'post-new.php' === $pagenow && 'order_bump' !== $so_offer_type ) {
				add_meta_box( 'so-resources', __( 'Must Read Links', 'smart-offers' ), array( $this, 'so_resource_links' ), 'smart_offers', 'side', 'default' );
			}

			remove_meta_box( 'woothemes-settings', 'smart_offers', 'normal' );
			remove_meta_box( 'commentstatusdiv', 'smart_offers', 'normal' );
			remove_meta_box( 'slugdiv', 'smart_offers', 'normal' );
		}

		/**
		 * Change the post title
		 */
		function woo_smart_offers_enter_title_here( $text, $post ) {
			if ( $post->post_type == 'smart_offers' ) {
				return __( 'Offer Title', 'smart-offers' );
			}
			return $text;
		}

		/**
		 * Add contents in Setup Your Offer metabox
		 */
		function so_offer_setup_meta_box() {
			$so_offer_type = self::get_offer_type();

			// Hide page editor for order bump offer
			if ( 'order_bump' === $so_offer_type ) {
				?>
				<style type="text/css" id="so-page-before-load">
					#post-body .postarea {
						display: none;
					}
				</style>
				<?php
			}
			?>
			<div class="panel-wrap offer_data">
				<input type="hidden" name="so_offer_type" value="<?php echo esc_attr( $so_offer_type ); ?>">
				<ul class="offer_data_tabs wc-tabs">
					<?php
						$product_data_tabs = array(
							'offered_product' => array(
								'label'  => __( 'What to offer', 'smart-offers' ),
								'target' => 'so-offered-product',
								'class'  => array( 'so-offered-product-tab' ),
							),
							'show_when'       => array(
								'label'  => __( 'Offer rules', 'smart-offers' ),
								'target' => 'so-offer-rules',
								'class'  => array( 'so-offer-rules-tab' ),
							),
						);

						// If current offer type is order bump then add following extra tabs.
						if ( 'order_bump' === $so_offer_type ) {
							$product_data_tabs = array_merge(
								$product_data_tabs,
								array(
									'offer_position' => array(
										'label'  => __( 'Offer position', 'smart-offers' ),
										'target' => 'so-offer-position',
										'class'  => array( 'so-offer-position' ),
									),
									'order_bump_style' => array(
										'label'  => __( 'Order bump style', 'smart-offers' ),
										'target' => 'so-offer-order-bump-style',
										'class'  => array( 'so-order-bump-style'),
									),
									'offer_content'  => array(
										'label'  => __( 'Offer content', 'smart-offers' ),
										'target' => 'so-offer-content',
										'class'  => array( 'so-offer-content' ),
									),
								)
							);
						} else {
							$product_data_tabs = array_merge(
								$product_data_tabs,
								array(
									'show_on_page' => array(
										'label'  => __( 'Show offer on page', 'smart-offers' ),
										'target' => 'so-show-on-page',
										'class'  => array( 'so-show-on-page-tab' ),
									),
									'show_as'      => array(
										'label'  => __( 'Show offer as', 'smart-offers' ),
										'target' => 'so-show-as',
										'class'  => array( 'so-show-as-tab' ),
									),
									'on_accept'    => array(
										'label'  => __( 'Accept actions', 'smart-offers' ),
										'target' => 'so-on-accept',
										'class'  => array( 'so-on-accept-tab' ),
									),
									'on_skip'      => array(
										'label'  => __( 'Skip actions', 'smart-offers' ),
										'target' => 'so-on-reject',
										'class'  => array( 'so-on-reject-tab' ),
									),
								)
							);
						}

						foreach ( $product_data_tabs as $key => $tab ) {
							?>
							<li id="<?php echo $key; ?>" class="<?php echo $key; ?>_options <?php echo $key; ?>_tab <?php echo implode( ' ', (array) $tab['class'] ); ?>">
								<a href="#<?php echo $tab['target']; ?>"><?php echo esc_html( $tab['label'] ); ?></a>
							</li>
							<?php
						}
						?>
				</ul>
				<?php
					$this->so_whats_the_offer_meta_box();
					$this->so_when_to_show_offer();
					$this->so_where_to_show_offer();
					$this->show_this_offer_as();
					$this->so_when_offer_is_accepted_skipped();
					if ( 'order_bump' === $so_offer_type ) {
						$this->so_order_bump_offer_position();
						$this->so_order_bump_offer_style();
						$this->so_order_bump_offer_content();
					}
				?>
			</div>
			<?php
		}

		/**
		 * What to offer tab content
		 */
		function so_whats_the_offer_meta_box() {
			global $post, $sa_smart_offers, $woocommerce;

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );

			$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$assets_path = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';

			$woocommerce_witepanel_params = array(
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
				'search_products_nonce' => wp_create_nonce( 'search-products' ),
				'calendar_image'        => WC()->plugin_url() . '/assets/images/calendar.png',
			);
			// Register scripts
			wp_enqueue_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC()->version );

			wp_enqueue_script( 'wc-admin-meta-boxes', WC()->plugin_url() . '/assets/js/admin/meta-boxes' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'accounting', 'round', 'ajax-chosen', 'chosen', 'plupload-all' ), WC_VERSION );
			wp_enqueue_script( 'wc-admin-product-meta-boxes', WC()->plugin_url() . '/assets/js/admin/meta-boxes-product' . $suffix . '.js', array( 'wc-admin-meta-boxes' ), WC_VERSION );
			wp_enqueue_script( 'wc-admin-variation-meta-boxes', WC()->plugin_url() . '/assets/js/admin/meta-boxes-product-variation' . $suffix . '.js', array( 'wc-admin-meta-boxes' ), WC_VERSION );

			$params = array(
				'post_id'                             => isset( $offer_id ) ? $offer_id : '',
				'plugin_url'                          => WC()->plugin_url(),
				'ajax_url'                            => admin_url( 'admin-ajax.php' ),
				'woocommerce_placeholder_img_src'     => wc_placeholder_img_src(),
				'add_variation_nonce'                 => wp_create_nonce( 'add-variation' ),
				'link_variation_nonce'                => wp_create_nonce( 'link-variations' ),
				'delete_variations_nonce'             => wp_create_nonce( 'delete-variations' ),
				'i18n_link_all_variations'            => esc_js( __( 'Are you sure you want to link all variations? This will create a new variation for each and every possible combination of variation attributes (max 50 per run).', 'smart-offers' ) ),
				'i18n_enter_a_value'                  => esc_js( __( 'Enter a value', 'smart-offers' ) ),
				'i18n_enter_a_value_fixed_or_percent' => esc_js( __( 'Enter a value (fixed or %)', 'smart-offers' ) ),
				'i18n_delete_all_variations'          => esc_js( __( 'Are you sure you want to delete all variations? This cannot be undone.', 'smart-offers' ) ),
				'i18n_last_warning'                   => esc_js( __( 'Last warning, are you sure?', 'smart-offers' ) ),
				'i18n_choose_image'                   => esc_js( __( 'Choose an image', 'smart-offers' ) ),
				'i18n_set_image'                      => esc_js( __( 'Set variation image', 'smart-offers' ) ),
				'i18n_variation_added'                => esc_js( __( 'variation added', 'smart-offers' ) ),
				'i18n_variations_added'               => esc_js( __( 'variations added', 'smart-offers' ) ),
				'i18n_no_variations_added'            => esc_js( __( 'No variations added', 'smart-offers' ) ),
				'i18n_remove_variation'               => esc_js( __( 'Are you sure you want to remove this variation?', 'smart-offers' ) ),
				'i18n_scheduled_sale_start'           => esc_js( __( 'Sale start date (YYYY-MM-DD format or leave blank)', 'smart-offers' ) ),
				'i18n_scheduled_sale_end'             => esc_js( __( 'Sale end date  (YYYY-MM-DD format or leave blank)', 'smart-offers' ) ),
			);

			wp_localize_script( 'wc-admin-meta-boxes', 'woocommerce_admin_meta_boxes', $woocommerce_witepanel_params );
			wp_localize_script( 'wc-admin-variation-meta-boxes', 'woocommerce_admin_meta_boxes_variations', $params );

			if ( ! wp_script_is( 'select2', 'registered' ) ) {
				wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/admin/select2' . $suffix . '.js', array( 'jquery' ), '3.5.2' );
			}

			if ( ! wp_script_is( 'wc-enhanced-select', 'registered' ) ) {
				wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), WC_VERSION );
			}

			$smart_offers_select_params = array(
				'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'smart-offers' ),
				'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'smart-offers' ),
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'smart-offers' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'smart-offers' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'smart-offers' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'smart-offers' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'smart-offers' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'smart-offers' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'smart-offers' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'smart-offers' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'smart-offers' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'smart-offers' ),
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'     => wp_create_nonce( 'search-products' ),
				'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
			);
			wp_localize_script( 'select2', 'wc_enhanced_select_params', $smart_offers_select_params );

			$locale  = localeconv();
			$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';

			$woocommerce_admin_params = array(
				'i18n_decimal_error'               => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'smart-offers' ), $decimal ),
				'i18n_mon_decimal_error'           => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'smart-offers' ), wc_get_price_decimal_separator() ),
				'i18n_country_iso_error'           => __( 'Please enter in country code with two capital letters.', 'smart-offers' ),
				'i18_sale_less_than_regular_error' => __( 'Please enter in a value less than the regular price.', 'smart-offers' ),
				'decimal_point'                    => $decimal,
				'mon_decimal_point'                => wc_get_price_decimal_separator(),
				// SO v3.3.0-Added to make it compatible with WC3.1 as import & export urls of product screen were giving jQuery error on offer edit screen resulting on help tip content not visible on hover
				'urls'                             => array(
					'export_products' => '',
					'import_products' => '',
				),
				'strings'                          => array(
					'export_products' => '',
					'import_products' => '',
				),
			);

			wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $woocommerce_admin_params );

			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css' );

			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_style( 'select2', $assets_path . 'css/select2.css' );

			?>
			<div id="so_whats_offer_panel" class="offered_product panel woocommerce_options_panel">
				<div class="options_group">
					<p class="form-field">
						<label id="offer_product_actions" class="so_label_text" for="offer_product_actions" style="margin-left: -150px !important;">
							<strong><?php echo __( 'Which product to offer?', 'smart-offers' ); ?></strong>
							<?php echo __( '(Add the product and set price to offer the product)', 'smart-offers' ); ?>
						</label>
					</p>
					<p class="form-field">
						<label for="offered_product"><?php echo __( 'Offered Product', 'smart-offers' ); ?></label>
						<select class="wc-product-search" style="width: 50%;" id="target_product_ids" name="target_product_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'smart-offers' ); ?>" data-allow_clear="true" data-action="woocommerce_json_search_products_and_variations">
							<?php
							$product_id = absint( get_post_meta( $offer_id, 'target_product_ids', true ) );
							if ( ! empty( $product_id ) ) {
								$product = wc_get_product( $product_id );
								if ( ( $product instanceof WC_Product ) ) {
									echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . esc_attr( wp_kses_post( $product->get_formatted_name() ) ) . '</option>';
								}
							} else {
								echo '';
							}
							?>
						</select> 
						<?php
							echo wc_help_tip( __( 'This product would be shown as an offer and on accepting this offer, this product would be added to the cart', 'smart-offers' ) );
						?>
					</p>
					<p class="form-field">
						<label for="offered_at"><?php echo __( 'Offer At', 'smart-offers' ); ?></label>
						<?php
							$discount_types = array(
								'fixed_price'      => __( 'Fixed Price', 'smart-offers' ),
								'price_discount'   => __( 'Fixed Price Discount', 'smart-offers' ),
								'percent_discount' => __( '% Discount', 'smart-offers' ),
							);

							$product_id = absint( get_post_meta( $offer_id, 'target_product_ids', true ) );
							if ( ! empty( $product_id ) ) {
								$offered_product = wc_get_product( $product_id );
								if ( ( $offered_product instanceof WC_Product ) ) {
									$product_type = $offered_product->get_type();
								}
							}
							if ( ! empty( $product_type ) && ( $product_type == 'subscription' || $product_type == 'variable-subscription' || $product_type == 'subscription_variation' ) ) {
								$discount_types = array(
									'fixed_price'      => __( 'Fixed Price', 'smart-offers' ),
									'price_discount'   => __( 'Recurring Product Discount', 'smart-offers' ),
									'percent_discount' => __( 'Recurring Product % Discount', 'smart-offers' ),
								);
							}
							?>
						<input type="number" step="any" min="0" class="short" name="offer_price" id="offer_price" placeholder="Enter price" value="<?php echo get_post_meta( $offer_id, 'offer_price', true ); ?>"> 
						<select id="discount_type" name="discount_type" class="select short">
							<?php
							foreach ( $discount_types as $key => $value ) {
								echo "<option value='$key' " . selected( $key, get_post_meta( $offer_id, 'discount_type', true ) ) . "> $value </option>";
							}
							?>
						</select>
						<?php
							echo wc_help_tip( __( 'Enter an amount/discount as a promotional price for the above offered product e.g. 2.99.', 'smart-offers' ) );
						?>
					</p>
				</div>
			</div>
			<?php
		}

		/**
		 * Display rules tab content
		 */
		function so_when_to_show_offer() {
			include_once 'class-so-admin-offer-rule.php';
		}

		/**
		 * Where to show tab content
		 */
		function so_where_to_show_offer() {
			global $post;

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );

			?>
			<div id="so_where_to_offer" class="show_on_page panel woocommerce_options_panel hidden">
				<div class="options_group">
					<p class="form-field">
						<label id="page_option_for_offer" class="so_label_text" for="page_option_for_offer">
							<strong><?php echo __( 'Where to show this offer?', 'smart-offers' ); ?></strong>
							<?php echo __( '(When multiple pages are checked, offer will be shown only on that page which the user visits first)', 'smart-offers' ); ?>
						</label>
					</p>
					<div class="so_page_options">
						<label class="page_checkbox">
							<input type="checkbox" id="offer_rule_home_page" name="offer_rule_home_page" class="checkbox" value="yes" 
								<?php if ( get_post_meta( $offer_id, 'offer_rule_home_page', true ) == 'yes' ) echo 'checked="checked"'; ?> />
							<span class="description"><?php echo __( 'Home page as a Popup', 'smart-offers' ); ?></span>
						</label>
						<label class="page_checkbox">
							<input type="checkbox" id="offer_rule_cart_page" name="offer_rule_cart_page" class="checkbox" value="yes" 
								<?php if ( get_post_meta( $offer_id, 'offer_rule_cart_page', true ) == 'yes' ) echo 'checked="checked"'; ?> />
							<span class="description"><?php echo __( 'Cart page', 'smart-offers' ); ?></span>
						</label>
						<label class="page_checkbox">
							<input type="checkbox" id="offer_rule_checkout_page" name="offer_rule_checkout_page" class="checkbox" value="yes"
								<?php if ( get_post_meta( $offer_id, 'offer_rule_checkout_page', true ) == 'yes' ) echo 'checked="checked"'; ?> />
							<span class="description"><?php echo __( 'Checkout page', 'smart-offers' ); ?></span>
						</label>
						<label class="page_checkbox">
							<input type="checkbox" id="offer_rule_post_checkout_page" name="offer_rule_post_checkout_page" class="checkbox" value="yes"
								<?php if ( get_post_meta( $offer_id, 'offer_rule_post_checkout_page', true ) == 'yes' ) echo 'checked="checked"'; ?> />
							<span class="description"><?php echo __( 'Before Order Complete', 'smart-offers' ); ?></span>
							<?php echo wc_help_tip( __( 'On click of Place Order button on the checkout page', 'smart-offers' ) ); ?>
						</label>
						<label class="page_checkbox">
							<input type="checkbox" id="offer_rule_thankyou_page" name="offer_rule_thankyou_page" class="checkbox" value="yes"
								<?php if ( get_post_meta( $offer_id, 'offer_rule_thankyou_page', true ) == 'yes' ) echo 'checked="checked"'; ?> />
							<span class="description"><?php echo __( 'WooCommerce Order Complete page', 'smart-offers' ); ?></span>
						</label>
						<label class="page_checkbox">
							<input type="checkbox" id="offer_rule_myaccount_page" name="offer_rule_myaccount_page" class="checkbox" value="yes"
								<?php if ( get_post_meta( $offer_id, 'offer_rule_myaccount_page', true ) == 'yes' ) echo 'checked="checked"'; ?> />
							<span class="description"><?php echo __( 'My Account page', 'smart-offers' ); ?></span>
						</label>
						<label class="page_checkbox" style="width: 100% !important;">
							<input type="checkbox" id="offer_rule_any_page" name="offer_rule_any_page" class="checkbox" value="yes"
									<?php if ( get_post_meta( $offer_id, 'offer_rule_any_page', true ) == 'yes' ) echo 'checked="checked"'; ?> />
							<span class="description">
								<?php echo __( 'Anywhere shortcode is added', 'smart-offers' ); ?>
								<?php echo sprintf( __( '(Offer added via shortcode will always show, %s)', 'smart-offers' ), '<a href="https://www.storeapps.org/docs/so-after-purchase-faqs/?utm_source=so&utm_medium=in_app&utm_campaign=view_faq_docs#so-shortcode" target="_blank">' . __( 'know more', 'smart-offers' ) . '</a>' ); ?>
							</span>
						</label>
					</div> 
					<div class="so_show_shortcode"> 
						<?php echo sprintf( __( 'Insert shortcode %1$1s when you select %2$2sAnywhere shortcode is added%3$3s page option.', 'smart-offers' ), ' <code>[so_show_offers offer_ids="' . $offer_id . '"]</code>', '<b>', '</b>' ); ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Show offer as tab content
		 */
		function show_this_offer_as() {
			global $post;

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );

			$show_offer_as = get_post_meta( $offer_id, 'so_show_offer_as', true );
			if ( empty( $show_offer_as ) ) {
				$show_offer_as = 'offer_as_inline';
			}
			?>
			<div id="so_offer_as" class="show_as panel woocommerce_options_panel hidden">
				<div class="options_group">
					<p class="form-field">
						<label id="show_this_offer_as" class="so_label_text">
							<strong><?php echo __( 'Show this offer...', 'smart-offers' ); ?></strong>
						</label>
					</p>
					<table class="so_options_table">
						<tbody>
							<tr valign="top" class="">
								<td class="forminp forminp-checkbox" >
									<div class='sprite show-offer-inline'></div>
								</td>
								<td class="forminp forminp-checkbox">
									<div class='sprite show-offer-as-lightbox'></div>
								</td>
							</tr>
							<tr valign="top" class="">
								<td>
									<input type="radio" id="offer_as_inline" name="so_show_offer_as" class="checkbox" value="offer_as_inline" 
										<?php if ( $show_offer_as == 'offer_as_inline' ) echo 'checked="checked"'; ?> />
									<span class="description"><?php echo __( 'As Inline with page content', 'smart-offers' ); ?></span>
								</td>
								<td>
									<input type="radio" id="offer_as_popup" name="so_show_offer_as" class="checkbox" value="offer_as_popup"
										<?php if ( $show_offer_as == 'offer_as_popup' ) echo 'checked="checked"'; ?> />
									<span class="description"><?php echo __( 'As a Popup', 'smart-offers' ); ?></span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>                                  
			<?php
		}

		/**
		 * Accept and Skip actions tab content
		 */
		function so_when_offer_is_accepted_skipped() {
			global $post, $sa_smart_offers, $post_id;

			$so_offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );

			$action_on_accept   = get_post_meta( $so_offer_id, 'so_actions_on_accept', true );
			$prod_ids_to_remove = $apply_coupon = $offer_ids_on_accept = null;

			if ( empty( $action_on_accept ) ) {
				$add_to_cart = true;
			} else {
				$add_to_cart               = ( isset( $action_on_accept['add_to_cart'] ) && $action_on_accept['add_to_cart'] == 'yes' ) ? true : false;
				$no_coupon_on_offered_prod = ( isset( $action_on_accept['sa_no_coupon'] ) && $action_on_accept['sa_no_coupon'] == 'yes' ) ? true : false;
				$buy_now                   = ( isset( $action_on_accept['buy_now'] ) && $action_on_accept['buy_now'] == true ) ? true : false;

				if ( isset( $action_on_accept['remove_prods_from_cart'] ) ) {
					$remove_prods_from_cart = true;
					$prod_ids_to_remove     = $action_on_accept['remove_prods_from_cart'];
				}

				if ( isset( $action_on_accept['sa_apply_coupon'] ) ) {
					$sa_apply_coupon = true;
					$apply_coupon    = $action_on_accept['sa_apply_coupon'];
				}

				if ( isset( $action_on_accept['accepted_offer_ids'] ) ) {
					$accepted_offer_ids  = true;
					$offer_ids_on_accept = $action_on_accept['accepted_offer_ids'];
				}

				if ( isset( $action_on_accept['sa_redirect_to_url'] ) ) {
					$sa_redirect_to_url = true;
				}
			}
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					if ( typeof getEnhancedSelectFormatString == "undefined" ) {
						function getEnhancedSelectFormatString() {
							var formatString = {
								noResults: function() {
									return wc_enhanced_select_params.i18n_no_matches;
								},
								errorLoading: function() {
									return wc_enhanced_select_params.i18n_ajax_error;
								},
								inputTooShort: function( args ) {
									var remainingChars = args.minimum - args.input.length;

									if ( 1 === remainingChars ) {
										return wc_enhanced_select_params.i18n_input_too_short_1;
									}

									return wc_enhanced_select_params.i18n_input_too_short_n.replace( '%qty%', remainingChars );
								},
								inputTooLong: function( args ) {
									var overChars = args.input.length - args.maximum;

									if ( 1 === overChars ) {
										return wc_enhanced_select_params.i18n_input_too_long_1;
									}

									return wc_enhanced_select_params.i18n_input_too_long_n.replace( '%qty%', overChars );
								},
								maximumSelected: function( args ) {
									if ( args.maximum === 1 ) {
										return wc_enhanced_select_params.i18n_selection_too_long_1;
									}

									return wc_enhanced_select_params.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
								},
								loadingMore: function() {
									return wc_enhanced_select_params.i18n_load_more;
								},
								searching: function() {
									return wc_enhanced_select_params.i18n_searching;
								}
							};

							var language = { 'language' : formatString };

							return language;
						}
					}

					var bindProductOnlyVariationsSelect2 = function() {
						jQuery( ':input.so-product-and-only-variations-search' ).filter( ':not(.enhanced)' ).each( function() {
							var select2_args = {
								allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
								placeholder: jQuery( this ).data( 'placeholder' ),
								minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '3',
								escapeMarkup: function( m ) {
									return m;
								},
								ajax: {
									url:         '<?php echo admin_url( 'admin-ajax.php' ); ?>',
									dataType:    'json',
									quietMillis: 250,
									data: function( params, page ) {
										return {
											term:     params.term,
											action:   jQuery( this ).data( 'action' ) || 'woocommerce_json_search_products_and_only_variations',
											security: '<?php echo wp_create_nonce( 'search-products-and-only-variations' ); ?>'
										};
									},
									processResults: function( data, page ) {
										var terms = [];
										if ( data ) {
											terms.push( { id: 'all', text: '<?php echo __( 'All Products', 'smart-offers' ); ?>' } );
											jQuery.each( data, function( id, text ) {
												terms.push( { id: id, text: text } );
											});
										}

										return { results: terms };
									},
									cache: true
								}
							};

							select2_args = jQuery.extend( select2_args, getEnhancedSelectFormatString() );

							jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
						});
					};

					bindProductOnlyVariationsSelect2();

					var bindOffersSelect2 = function() {
						jQuery( ':input.so-offer-search' ).filter( ':not(.enhanced)' ).each( function() {
							var select2_args = {
								allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
								placeholder: jQuery( this ).data( 'placeholder' ),
								minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '3',
								escapeMarkup: function( m ) {
									return m;
								},
								ajax: {
									url:         '<?php echo admin_url( 'admin-ajax.php' ); ?>',
									dataType:    'json',
									quietMillis: 250,
									data: function( params, page ) {
										return {
											term:     params.term,
											action:   jQuery( this ).data( 'action' ) || 'woocommerce_json_search_offers',
											security: '<?php echo wp_create_nonce( 'search-offers' ); ?>',
											exclude:  jQuery( this ).data( 'exclude' ),
										};
									},
									processResults: function( data, page ) {
										var terms = [];
										if ( data ) {
											jQuery.each( data, function( id, text ) {
												terms.push( { id: id, text: text } );
											});
										}
										return { results: terms };
									},
									cache: true
								}
							};

							select2_args = jQuery.extend( select2_args, getEnhancedSelectFormatString() );

							jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
						});
					};

					bindOffersSelect2();

					var bindCouponsSelect2 = function() {
						jQuery( ':input.wc-coupon-search' ).filter( ':not(.enhanced)' ).each( function() {
							var select2_args = {
								allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
								placeholder: jQuery( this ).data( 'placeholder' ),
								minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '3',
								escapeMarkup: function( m ) {
									return m;
								},
								ajax: {
									url:         '<?php echo admin_url( 'admin-ajax.php' ); ?>',
									dataType:    'json',
									quietMillis: 250,
									data: function( params, page ) {
										return {
											term:     params.term,
											action:   jQuery( this ).data( 'action' ) || 'woocommerce_json_search_coupons',
											security: '<?php echo wp_create_nonce( 'search-coupons' ); ?>'
										};
									},
									processResults: function( data, page ) {
										var terms = [];
										if ( data ) {
											jQuery.each( data, function( id, text ) {
												terms.push( { id: id, text: text } );
											});
										}
										return { results: terms };
									},
									cache: true
								}
							};

							select2_args = jQuery.extend( select2_args, getEnhancedSelectFormatString() );

							jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
						});
					};

					bindCouponsSelect2();

					jQuery(".accept_input_checkboxes").change(function() {
						var id = jQuery(this).attr('id');
						var sa_redirect_to_url = jQuery('input#sa_redirect_to_url');
						var buy_now = jQuery('input#buy_now');
						var accepted_offer_ids = jQuery('input#accepted_offer_ids');
						switch ( id ) {
							case 'accepted_offer_ids':
								sa_redirect_to_url.removeAttr('checked');
								buy_now.removeAttr('checked');
							break;

							case 'sa_redirect_to_url':
								accepted_offer_ids.removeAttr('checked');
								buy_now.removeAttr('checked');
							break;

							case 'buy_now':
								accepted_offer_ids.removeAttr('checked');
								sa_redirect_to_url.removeAttr('checked');
							break;
						}
					});
				});
			</script>

			<div id="so_when_offer_accepted" class="on_accept panel woocommerce_options_panel">
				<div class="options_group">
					<p class="form-field">
						<label id="offer_accept_actions" class="so_label_text" for="offer_accept_actions" >
							<strong><?php echo __( 'Actions to take when this offer is accepted:', 'smart-offers' ); ?></strong>
						</label>
					</p>
					<p class="form-field">
						<label class="accept_input_checkboxes" id="add_to_cart" for="add_to_cart">
							<input type="checkbox" name="sa_add_to_cart" id="add_to_cart" <?php if ( $add_to_cart == true ) echo 'checked="checked"'; ?> value="add_to_cart" >
							<?php echo __( 'Add the offered product to cart', 'smart-offers' ); ?>
						</label>
					</p>
					<p class="form-field">
						<label class="accept_input_checkboxes" id="remove_prods_from_cart" for="remove_prods_from_cart">
							<input type="checkbox" name="sa_remove_prods_from_cart" id="remove_prods_from_cart" <?php if ( isset( $remove_prods_from_cart ) && $remove_prods_from_cart == true ) echo 'checked="checked"'; ?> value="remove_prods_from_cart">
							<?php echo __( 'Remove these products from cart', 'smart-offers' ); ?>
						</label>
						<select class="so-product-and-only-variations-search" style="width: 44%;" id="remove_prods_from_cart" name="remove_prods_from_cart[]" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'smart-offers' ); ?>" data-action="woocommerce_json_search_products_and_only_variations">
							<?php
							if ( ! empty( $prod_ids_to_remove ) ) {
								if ( $prod_ids_to_remove == 'all' ) {
									echo '<option value="all"' . '"' . selected( true, true, false ) . '>' . __( 'All Products', 'smart-offers' ) . '</option>';
								} else {
									$prod_ids_to_remove = explode( ',', $prod_ids_to_remove );
									foreach ( $prod_ids_to_remove as $product_id ) {
										$product = wc_get_product( $product_id );
										if ( $product instanceof WC_Product ) {
											$title = $product->get_formatted_name();
											if ( ! $title ) {
												continue;
											}
											echo '<option value="' . $product_id . '"' . selected( true, true, false ) . '>' . $title . '</option>';
										}
									}
								}
							}
							?>
						</select>
					</p>
					<?php
					if ( wc_coupons_enabled() ) {
						?>
							<p class="form-field">
								<label class="accept_input_checkboxes" id="sa_redirect_to_url" for="sa_apply_coupon">
									<input type="checkbox" name="sa_apply_coupon" id="sa_apply_coupon" <?php if ( isset( $sa_apply_coupon ) && $sa_apply_coupon == true ) echo 'checked="checked"'; ?> value="sa_apply_coupon">
								<?php echo __( 'Apply coupons', 'smart-offers' ); ?>
								</label>
								<select class="wc-coupon-search" style="width: 44%;" id="sa_coupon_title" name="sa_coupon_title[]" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Search for a coupon&hellip;', 'smart-offers' ); ?>" data-action="woocommerce_json_search_coupons">
									<?php
									if ( ! class_exists( 'WC_Coupon' ) ) {
										require_once WP_PLUGIN_DIR . '/woocommerce/classes/class-wc-coupon.php';
									}
									$all_discount_types = wc_get_coupon_types();
									if ( ! empty( $apply_coupon ) ) {
										$coupon_titles = explode( ',', $apply_coupon );
										foreach ( $coupon_titles as $coupon_title ) {
											$coupon        = new WC_Coupon( $coupon_title );
											$discount_type = $coupon->get_discount_type();
											if ( isset( $discount_type ) && $discount_type ) {
												$discount_type = ' ( Type: ' . $all_discount_types[ $discount_type ] . ' )';
											}
											echo '<option value="' . $coupon_title . '"' . selected( true, true, false ) . '>' . $coupon_title . $discount_type . '</option>';
										}
									}
									?>
								</select>
							</p>
							<p class="form-field">
								<label class="accept_input_checkboxes" id="sa_no_coupon" for="sa_no_coupon">
									<input type="checkbox" name="sa_no_coupon" id="sa_no_coupon" 
									<?php
									if ( isset( $no_coupon_on_offered_prod ) && $no_coupon_on_offered_prod == true ) {
										echo 'checked="checked"';}
									?>
									 value="sa_no_coupon">
									<?php
									echo __( 'Do not apply any coupon on this offered product when added via this offer', 'smart-offers' );
									?>
								</label>
								<?php echo wc_help_tip( __( 'If a coupon is applied to the cart/checkout, that coupon\'s discount will not be applied on the offered product added via this offer.', 'smart-offers' ) ); ?>
							</p>
							<?php
					}
					?>
					<hr style="height: 0.1em;">
					<p class="form-field" id="so-another-offer">
						<label class="accept_input_checkboxes" id="accepted_offer_ids" for="accepted_offer_ids">
							<input type="checkbox" name="accepted_offer_ids" id="accepted_offer_ids" <?php if ( ! empty( $accepted_offer_ids ) && $accepted_offer_ids == true ) echo 'checked="checked"'; ?> value="accepted_offer_ids">
							<?php echo __( 'Show another (Upsell) offer', 'smart-offers' ); ?>
						</label>
						<select class="so-offer-search" style="width: 44%; padding: 2px; line-height: 28px; height: 28px; vertical-align: middle;" id="accept_offer_ids" name="accept_offer_ids[]" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Search for an offer&hellip;', 'smart-offers' ); ?>" data-action="woocommerce_json_search_offers" data-exclude="<?php echo intval( $so_offer_id ); ?>">
							<?php

							if ( ! empty( $offer_ids_on_accept ) ) {
								$offer_accept_id = explode( ',', $offer_ids_on_accept );

								foreach ( $offer_accept_id as $id ) {
									$title = get_the_title( $id );
									if ( ! empty( $title ) ) {
										echo '<option value="' . $id . '"' . selected( true, true, false ) . '>' . $title . '</option>';
									} else {
										echo '<option value="" ></option>';
									}
								}
							}
							?>
						</select>
						<?php
							echo wc_help_tip( __( 'Offer to be shown if this offer is accepted. If multiple offers are chosen, one will be shown based on your settings.', 'smart-offers' ) );
						?>
					</p>
					<p class="form-field" id="so-another-url">
						<label class="accept_input_checkboxes" id="sa_redirect_to_url" for="sa_redirect_to_url">
							<input type="checkbox" name="sa_redirect_to_url" id="sa_redirect_to_url" <?php if ( isset( $sa_redirect_to_url ) && $sa_redirect_to_url == true ) echo 'checked="checked"'; ?> value="sa_redirect_to_url">
							<?php echo __( 'Redirect to a URL', 'smart-offers' ); ?>
						</label>
						<input type='text' style="width: 44%;" placeholder="<?php echo __( 'https://www.storeapps.org/', 'smart-offers' ); ?>" name='accept_redirect_url' id='accept_redirect_url'
							value='<?php if ( isset( $action_on_accept['sa_redirect_to_url'] ) ) echo $action_on_accept['sa_redirect_to_url']; ?>' />
					</p>
					<p class="form-field" id="so-accept-buy-now">
						<?php
						$active_plugins = (array) get_option( 'active_plugins', array() );
						if ( is_multisite() ) {
							$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
						}

						if ( ( in_array( 'woocommerce-buy-now/woocommerce-buy-now.php', $active_plugins ) || array_key_exists( 'woocommerce-buy-now/woocommerce-buy-now.php', $active_plugins ) ) ) {
							?>
							<label class="accept_input_checkboxes" id="buy_now" for="buy_now">
								<input type="checkbox" class="accept_input_checkboxes" name="sa_buy_now" id="buy_now" <?php if ( isset( $buy_now ) && $buy_now == true ) echo 'checked="checked"'; ?> value="buy_now">&nbsp;
								<?php
									echo sprintf( __( 'Instantly checkout with %s plugin', 'smart-offers' ), '<a href="https://www.storeapps.org/product/woocommerce-buy-now/?utm_source=so&utm_medium=in_app&utm_campaign=view_bn_product_page" target="_blank">' . __( 'Buy Now', 'smart-offers' ) . '</a>' );
								?>
							</label>
							<?php
						} elseif ( ( in_array( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php', $active_plugins ) || array_key_exists( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php', $active_plugins ) )
									||
								   ( in_array( 'woocommerce-gateway-authorize-net-cim/woocommerce-gateway-authorize-net-cim.php', $active_plugins ) || array_key_exists( 'woocommerce-gateway-authorize-net-cim/woocommerce-gateway-authorize-net-cim.php', $active_plugins ) )
									||
								   ( in_array( 'woocommerce-gateway-paypal-powered-by-braintree/woocommerce-gateway-paypal-powered-by-braintree.php', $active_plugins ) || array_key_exists( 'woocommerce-gateway-paypal-powered-by-braintree/woocommerce-gateway-paypal-powered-by-braintree.php', $active_plugins ) )
								) {
							?>
							<label class="accept_input_checkboxes bn_disabled" id="buy_now" for="buy_now">
								<input type="checkbox" class="accept_input_checkboxes" name="sa_buy_now" id="buy_now" <?php if ( isset( $buy_now ) && $buy_now == true ) echo 'checked="checked"'; ?> value="buy_now" disabled>&nbsp;
								<?php
									echo sprintf( __( 'Enable one click checkout with %s plugin', 'smart-offers' ), '<a href="https://www.storeapps.org/product/woocommerce-buy-now/?utm_source=so&utm_medium=in_app&utm_campaign=view_bn_product_page" target="_blank">' . __( 'Buy Now', 'smart-offers' ) . '</a>' );
								?>
							</label>
							<?php
						} else {
							?>
							<label class="accept_input_checkboxes bn_disabled" id="buy_now" for="buy_now">
								<input type="checkbox" class="accept_input_checkboxes" name="sa_buy_now" id="buy_now" <?php if ( isset( $buy_now ) && $buy_now == true ) echo 'checked="checked"'; ?> value="buy_now" disabled>&nbsp;
								<?php
									echo sprintf( __( 'Instantly checkout with %s plugin', 'smart-offers' ), '<a href="https://www.storeapps.org/product/woocommerce-buy-now/?utm_source=so&utm_medium=in_app&utm_campaign=view_bn_product_page" target="_blank">' . __( 'Buy Now', 'smart-offers' ) . '</a>' );
								?>
							</label>
							<?php
						}
						?>
					</p>
				</div>
			</div>
			<?php
				$offer_denied_option = get_post_meta( $so_offer_id, 'sa_smart_offer_if_denied', true );
			if ( empty( $offer_denied_option ) ) {
				$offer_denied_option = 'order_page';
			}
				$url = get_post_meta( $so_offer_id, 'url', true );
			?>
			<div id="so_when_offer_skipped" class="on_skip panel woocommerce_options_panel hidden">
				<div class="options_group">
					<p class="form-field">
					<label id="offer_skip_action" class="so_label_text" for="offer_skip_action">
						<strong><?php echo __( 'Actions to take when this offer is skipped:', 'smart-offers' ); ?></strong>
					</label>
					</p>
					<p class="form-field">
						<label class="skip_options_radio" id="order_page" for="order_page">
							<input type="radio" name="sa_smart_offer_if_denied" id="order_page" value="order_page" <?php if ( $offer_denied_option == 'order_page' ) echo 'checked="checked"'; ?> />
							<?php echo __( 'Hide this offer', 'smart-offers' ); ?>
						</label>
					</p>
					<p class="form-field">
						<label class="skip_options_radio" for="offer_page">
							<input type="radio" name="sa_smart_offer_if_denied" id="offer_page" value="offer_page" <?php if ( $offer_denied_option == 'offer_page' ) echo 'checked="checked"'; ?> />
							<?php echo __( 'Show another (Downsell) offer', 'smart-offers' ); ?>
						</label>
						<select class="so-offer-search" style="width: 44%;" id="offer_ids" name="offer_ids[]" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Search for an offer&hellip;', 'smart-offers' ); ?>" data-action="woocommerce_json_search_offers" data-exclude="<?php echo intval( $so_offer_id ); ?>">
							<?php
							if ( $offer_denied_option == 'offer_page' ) {
								$offer_id = get_post_meta( $so_offer_id, 'url', true );
								if ( ! empty( $offer_id ) ) {
									$offer_id = explode( ',', $offer_id );
									foreach ( $offer_id as $id ) {
										$title = get_the_title( $id );
										if ( ! empty( $title ) ) {
											echo '<option value="' . $id . '"' . selected( true, true, false ) . '>' . $title . '</option>';
										} else {
											echo '<option value="" ></option>';
										}
									}
								}
							}
							?>
						</select>
						<?php
							echo wc_help_tip( __( 'Offer to be shown if this offer is skipped. If multiple offers are chosen, one will be shown based on your settings.', 'smart-offers' ) );
						?>
					</p>
					<p class="form-field">
						<label class="skip_options_radio" for="particular_page">
							<input type="radio" name="sa_smart_offer_if_denied" id="particular_page" value="particular_page" <?php if ( $offer_denied_option == 'particular_page' ) echo 'checked="checked"'; ?> />
							<?php echo __( 'Redirect to page', 'smart-offers' ); ?>
						</label>
						<?php
							$args = array(
								'selected' => $url,
								'class'    => 'so_skip_and_redirect_page_dropdown',
							);
							wp_dropdown_pages( $args );
							?>
					</p>
					<p class="form-field">
						<label class="skip_options_radio" for="url">
							<input type="radio" name="sa_smart_offer_if_denied" id="url" value="url" <?php if ( $offer_denied_option == 'url' ) echo 'checked="checked"'; ?> />
							<?php echo __( 'Redirect to a URL', 'smart-offers' ); ?>
						</label>
						<?php $value = ( $offer_denied_option == 'url' ) ? $url : ''; ?>
						<input type='text' style="width:44%" placeholder="<?php echo __( 'https://www.storeapps.org/', 'smart-offers' ); ?>" name='text_url' id='text_url' value='<?php echo $value; ?>' />
					</p>
					<p class="form-field">
						<?php
						if ( ( in_array( 'woocommerce-buy-now/woocommerce-buy-now.php', $active_plugins ) || array_key_exists( 'woocommerce-buy-now/woocommerce-buy-now.php', $active_plugins ) ) ) {
							?>
							<label class="skip_options_radio" id="buy_now_page" for="buy_now_page">
								<input type="radio" name="sa_smart_offer_if_denied" id="buy_now_page" value="buy_now_page" <?php if ( $offer_denied_option == 'buy_now_page' ) echo 'checked="checked"'; ?> />
								<?php
								echo sprintf( __( 'Instantly checkout with %s plugin', 'smart-offers' ), '<a href="https://www.storeapps.org/product/woocommerce-buy-now/?utm_source=so&utm_medium=in_app&utm_campaign=view_bn_product_page" target="_blank">' . __( 'Buy Now', 'smart-offers' ) . '</a>' );
								?>
							</label>
							<?php
						} elseif ( ( in_array( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php', $active_plugins ) || array_key_exists( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php', $active_plugins ) )
										||
								   ( in_array( 'woocommerce-gateway-authorize-net-cim/woocommerce-gateway-authorize-net-cim.php', $active_plugins ) || array_key_exists( 'woocommerce-gateway-authorize-net-cim/woocommerce-gateway-authorize-net-cim.php', $active_plugins ) )
										||
								   ( in_array( 'woocommerce-gateway-paypal-powered-by-braintree/woocommerce-gateway-paypal-powered-by-braintree.php', $active_plugins ) || array_key_exists( 'woocommerce-gateway-paypal-powered-by-braintree/woocommerce-gateway-paypal-powered-by-braintree.php', $active_plugins ) )
								 ) {
							?>
							<label class="skip_options_radio bn_disabled" id="buy_now_page" for="buy_now_page">
								<input type="radio" name="sa_smart_offer_if_denied" id="buy_now_page" value="buy_now_page" <?php if ( $offer_denied_option == 'buy_now_page' ) echo 'checked="checked"'; ?> disabled />
								<?php
									echo sprintf( __( 'Enable one click checkout with %s plugin', 'smart-offers' ), '<a href="https://www.storeapps.org/product/woocommerce-buy-now/?utm_source=so&utm_medium=in_app&utm_campaign=view_bn_product_page" target="_blank">' . __( 'Buy Now', 'smart-offers' ) . '</a>' );
								?>
							</label>
							<?php
						} else {
							?>
							<label class="skip_options_radio bn_disabled" id="buy_now_page" for="buy_now_page">
								<input type="radio" name="sa_smart_offer_if_denied" id="buy_now_page" value="buy_now_page" <?php if ( $offer_denied_option == 'buy_now_page' ) echo 'checked="checked"'; ?> disabled />
								<?php
									echo sprintf( __( 'Instantly checkout with %s plugin', 'smart-offers' ), '<a href="https://www.storeapps.org/product/woocommerce-buy-now/?utm_source=so&utm_medium=in_app&utm_campaign=view_bn_product_page" target="_blank">' . __( 'Buy Now', 'smart-offers' ) . '</a>' );
								?>
							</label>
							<?php
						}
						?>
					</p>
					<p class="form-field">
						<input type="checkbox" class="checkbox" id="sa_smart_offer_if_denied_skip_permanently" name="sa_smart_offer_if_denied_skip_permanently" class="checkbox" value="yes" <?php if ( get_post_meta( $so_offer_id, 'sa_smart_offer_if_denied_skip_permanently', true ) == 'yes' ) echo 'checked="checked"'; ?>
						<?php echo __( '<strong>&nbsp;&nbsp;Hide Forever From This User</strong> - Never show this offer to a customer again if skipped once', 'smart-offers' ); ?>
					</p>
				</div>
			</div>
			<?php
		}

		/**
		 * Show offer position tab's content
		 */
		function so_order_bump_offer_position() {
			global $post;

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );

			$offer_position = get_post_meta( $offer_id, 'so_offer_position', true );
			if ( empty( $offer_position ) ) {
				$offer_position = 'before_checkout_submit';
			}
			?>
			<div id="so_offer_position" class="offer_position panel woocommerce_options_panel hidden">
				<div class="options_group">
					<p class="form-field so_order_bump_position">
						<label id="so_offer_position_label" class="so_label_text">
							<strong><?php echo __( 'Show this offer on checkout page at', 'smart-offers' ); ?></strong>
						</label>
					</p>
					<table class="so_options_table">
						<tbody>
							<tr valign="top" class="">
								<td>
									<?php
										// Offer position.
										woocommerce_wp_select(
											array(
												'id'      => 'so_offer_position',
												'label'   => __( 'position', 'smart-offers' ),
												'class'   => 'select',
												'options' => array(
													'before_checkout_submit' => __( 'Before place order button', 'smart-offers' ),
													'after_checkout_submit' => __( 'After place order button', 'smart-offers' ),
												),
											)
										);
									?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<?php
		}

		/**
		 * Show offer content tab's content
		 */
		function so_order_bump_offer_content() {
			global $post;

			$offer_id                 = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );
			$order_bump_attachment_id = get_post_meta( $offer_id, 'so_order_bump_attachment_id', true );
			$order_bump_intro_text    = get_post_meta( $offer_id, 'so_order_bump_intro_text', true );
			$order_bump_style         = get_post_meta( $offer_id, 'so_order_bump_style', true );
			if( empty( $order_bump_style ) ) {
				$order_bump_style = 'default';
			}
			?>
			<div id="so_offer_content" class="offer_content panel woocommerce_options_panel hidden">
				<div class="options_group">
					<p class="form-field so_order_bump_content">
						<label id="order_bump_content" class="so_label_text">
							<strong><?php echo __( 'Add content for order bump offer...', 'smart-offers' ); ?></strong>
						</label>
					</p>
					<?php
						woocommerce_wp_text_input(
							array(
								'id'          => 'so_order_bump_lead_text',
								'label'       => __( 'CTA', 'smart-offers' ),
								'data_type'   => 'text',
								'placeholder' => __( 'Yes! I want it', 'smart-offers' ),
								'desc_tip'    => true,
								'description' => __( 'Enter text to show next to the checkbox. This will be shown as accept CTA. Leaving it blank will show placeholder as the default text.', 'smart-offers' ),
							)
						);

						// Disable intro text field for style-1 and style-2.
						$intro_text_wrapper_class = ( in_array( $order_bump_style, array( 'style-1', 'style-2' ), true ) ) ? ' disabled' : '';
						?>
						<p class="form-field so_order_bump_intro_text_field<?php echo esc_attr( $intro_text_wrapper_class ); ?>">
							<label for="so_order_bump_intro_text"><?php echo esc_html__( 'Headline', 'smart-offers' ); ?></label>
							<input type="text" class="short" name="so_order_bump_intro_text" id="so_order_bump_intro_text" value="<?php echo esc_attr( $order_bump_intro_text ); ?>" placeholder="<?php echo esc_attr__( 'ONE TIME OFFER!.', 'smart-offers' ); ?>"/>
							<span class="so_order_bump_no_intro_text">
								<?php
									echo esc_html__( 'This style does not support headline.', 'smart-offers' );
								?>
							</span>
							<?php
								$tooltip_text = esc_html__( 'Enter headline for the order bump offer. Leaving it blank will show placeholder as the default text.', 'smart-offers' );
								echo wc_help_tip( $tooltip_text ); // phpcs:ignore
							?>
						</p>
						<?php

						woocommerce_wp_textarea_input( array(
							'id'          => 'so_order_bump_body_text',
							'label'       => __( 'Content', 'smart-offers' ),
							'placeholder' => __( 'You can have access to this exclusive offer by ticking the box above. Click and add it to your order now. This offer is available only now.', 'smart-offers' ),
							'desc_tip'    => true,
							'description' => __( 'Enter content for the order bump offer. Leaving it blank will show placeholder as the default text.', 'smart-offers' ),
							'rows'        => '5',
						) );

						woocommerce_wp_hidden_input(
							array(
								'id' => 'so_order_bump_attachment_id',
							)
						);
						wp_enqueue_media();

						// Disable order bump image field for style-1.
						$image_wrapper_class = ( in_array( $order_bump_style, array( 'style-1' ), true ) ) ? ' disabled' : '';
						$image_preview_class = ( ! empty( $order_bump_attachment_id ) ) ? ' image_chosen' : '';
						?>
						<p class="form-field so_order_bump_image_field_wrapper<?php echo esc_attr( $image_wrapper_class ); ?>">
							<label for="so_order_bump_image_field_wrapper"><?php echo esc_html__( 'Offer Image', 'smart-offers' ); ?></label>
							<span class="so_order_bump_image_upload">
								<span class="so_order_bump_image_preview_wrapper<?php echo esc_attr( $image_preview_class ); ?>">
									<?php 
										if( ! empty( $order_bump_attachment_id ) ) {
											$order_bump_attachment_image = wp_get_attachment_image_src( $order_bump_attachment_id );
											if( ! empty( $order_bump_attachment_image ) && is_array( $order_bump_attachment_image ) ) {
												$order_bump_attachment_url = $order_bump_attachment_image[0];
												?>
												<img class="so_order_bump_image_preview" src="<?php echo esc_url( $order_bump_attachment_url );?>">
												<?php
											}
										}
									?>
									<span class="so_order_bump_remove_image dashicons dashicons-no-alt"></span>
								</span>
								<span class="so_order_bump_upload_button_wrapper">
									<input type="button" class="so_order_bump_upload_image button-primary" id="so_order_bump_upload_image" value="<?php echo esc_html__( 'Set Image', 'smart-offers' ); ?>"/>
								</span>
							</span>
							<span class="so_order_bump_no_image_text">
								<?php
									echo esc_html__( 'This style does not support image.', 'smart-offers' );
								?>
							</span>
						</p>
						<?php
					?>
				</div>
			</div>                                  
			<?php
		}

		/**
		 * Show order bump style tab's content
		 */
		function so_order_bump_offer_style() {
			global $post;

			$offer_id    = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );
			$offer_style = get_post_meta( $offer_id, 'so_order_bump_style', true );
			if ( empty( $offer_style ) ) {
				$offer_style = 'default';
			}
			?>
			<div id="so-offer-order-bump-style" class="offer-order-bump-style panel woocommerce_options_panel hidden">
				<div class="options_group">
					<p>
						<label id="order_bump_style_label" class="so_label_text">
							<strong><?php echo __( 'Choose style for order bump offer', 'smart-offers' ); ?></strong>
						</label>
					</p>
					<?php
						$styles_default_css = $this->get_order_bump_styles_default_css();
						// Order bump styles.
						$order_bump_styles = $this->get_order_bump_styles();
						if( ! empty( $order_bump_styles ) && is_array( $order_bump_styles ) ) {
							$so_url = plugins_url( SMART_OFFERS );
							?>
							<div class="so_order_bump_styles">
								<?php
									$style_number = 1;
									foreach( $order_bump_styles as $style_id => $style ) {
										$style_class           = $style_id;
										$style_label           = isset( $style['label'] ) ? $style['label'] : '';
										$style_description     = isset( $style['description'] ) ? $style['description'] : '';
										$style_class           .= ( $offer_style === $style_id ) ? ' selected' : '';
										$style_default_css     = isset( $styles_default_css[ $style_id ] ) ? $styles_default_css[ $style_id ] : array();
										$style_preview_img_url = $so_url . '/assets/images/so-order-bump-' . $style_id . '.jpg';

										// Add opening row wrapper if it an odd numbered style.
										if( $style_number % 2 === 1 ) {
										?>
										<div class="style_wrapper_row">
										<?php
										}
										?>
										<div class="style_wrapper <?php echo esc_attr( $style_class ); ?>">
											<span class="style-preview-img-wrapper">
												<img src="<?php echo esc_url( $style_preview_img_url ); ?>">
											</span>
											<div class="style-option">
												<label class="description"><input type="radio" id="so-order-bump-<?php echo esc_attr( $style_id ); ?>" name="so_order_bump_style" class="checkbox so_order_bump_style" value="<?php echo esc_attr( $style_id ); ?>" 
													<?php if ( $offer_style === $style_id ) echo 'checked="checked"'; ?> data-so-order-bump-style-default-css="<?php echo esc_attr( $style_default_css );?>"/>
													<span class="style-label"><?php echo $style_label; ?></span>
													<?php
													if( ! empty( $style_description ) ) {
														echo wc_help_tip( $style_description ); // phpcs:ignore
													}
													?>
												</label>
											</div>
										</div>
										<?php
										// Add closing row wrapper if it an even numbered style or the last style.
										if( $style_number % 2 === 0 || count( $order_bump_styles ) === $style_number ) {
										?>
										</div>
										<?php
										}
										$style_number++;
									}
								?>
							</div>
						<?php
						}
					?>
				</div>
			</div>                               
			<?php
		}

		function so_custom_code_block() {
			global $post;

			$offer_id      = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );
			$so_offer_type = self::get_offer_type();

			$custom_css = get_post_meta( $offer_id, 'so_custom_css', true );

			$dummy_css = '';

			// Add dummy css for normal offer type.
			if ( '' === $so_offer_type ) {
				$dummy_css = '.so-popup #so_this_offer {
	max-width: 60%;
	margin: 0 auto;
}
#so_this_offer .so-headline {
	text-align: center;
}
#so_this_offer .so-offer {
	display: flex;
	flex-direction: row;
	padding: 10px 0;
	justify-content: center;
}
@media only screen and (max-width: 768px) {
	.so-popup #so_this_offer {
		max-width: unset !important;
		margin: unset !important;
	}
	#so_this_offer .so-offer {
		flex-direction: column;
		text-align: center;
	}
}';
			} elseif ( 'order_bump' === $so_offer_type ) {
				$dummy_css = $this->get_order_bump_styles_default_css( 'default' );
			}

			$so_offer_custom_css = ( ! empty( $custom_css ) ) ? $custom_css : $dummy_css;

			?>

			<div id="so_custom_css-<?php echo $offer_id; ?>">
				<span><?php echo __( 'Write custom CSS for this offer ', 'smart-offers' ); ?></span>
				<span><?php echo __( '(Use <b>#so_this_offer</b> selector before any other selector when writing custom CSS)', 'smart-offers' ); ?></span><br><br>
				<textarea id="so_custom_css" name="so_custom_css" style="width: 100%;" rows="10" cols="95"><?php if ( isset( $so_offer_custom_css ) ) echo esc_attr( $so_offer_custom_css ); ?></textarea>
			</div>
			<?php
		}

		/**
		 * Function to get default CSS for order bump styles.
		 * 
		 * @since v3.10.8
		 * @return mixed $default_css Default css for order bump
		 */
		public function get_order_bump_styles_default_css( $style = '' ) {
			$default_css = array();
			$plugin_data = SA_Smart_Offers::get_smart_offers_plugin_data();
			$version = $plugin_data['Version'];
			if( ! empty( $style ) ) {
				$css_file = SA_SO_PLUGIN_DIRPATH . '/assets/css/so-order-bump-' . $style . '.css';
				if ( file_exists( apply_filters( 'so_order_bump_style_path', $css_file, $style ) ) ) {
					ob_start();
					include $css_file;
					$default_css = ob_get_clean();
				}
			} else {
				$order_bump_styles = $this->get_order_bump_styles();
				if( is_array( $order_bump_styles ) && ! empty( $order_bump_styles ) ) {
					foreach ( $order_bump_styles as $order_bump_style => $order_bump_style_data ) {
						$css_file = SA_SO_PLUGIN_DIRPATH . '/assets/css/so-order-bump-' . $order_bump_style . '.css';
						if ( file_exists( apply_filters( 'so_order_bump_style_path', $css_file, $order_bump_style ) ) ) {
							ob_start();
							include $css_file;
							$default_css[ $order_bump_style ] = ob_get_clean();
						}
					}
				}
			}
			return $default_css;
		}

		/**
		 * Function to get list of styles for order bump
		 * 
		 * @since v3.10.8
		 * @return array $order_bump_styles Order bump styles
		 */
		public function get_order_bump_styles() {

			$order_bump_styles = array(
				'default' => array(
					'label' => __( 'Default style', 'smart-offers' ),
				),
				'style-1' => array(
					'label' => __( 'Style 1', 'smart-offers' ),
				),
				'style-2' => array(
					'label' => __( 'Style 2', 'smart-offers' ),
				),
				'style-3' => array(
					'label' => __( 'Style 3', 'smart-offers' ),
				),
				'style-4' => array(
					'label'       => __( 'Style 4', 'smart-offers' ),
					'description' => __( 'This style is recommended for single column checkout page. Not recommended if you have two column checkout page.', 'smart-offers' ),
				),
			);

			return apply_filters( 'so_order_bump_styles', $order_bump_styles );
		}

		/**
		 * Show priority setting meta box in offers
		 *
		 * @since v3.10.4
		 */
		function so_priority_meta_box() {
			global $post;

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );

			$offer_priority = get_post_field( 'menu_order', $offer_id );
			if ( $offer_priority == 0 || empty( $offer_priority ) ) {
				$offer_priority = '0';
			}
			?>
			<div id="so_offer_priority" class="offer_priority panel woocommerce_options_panel">
				<div class="options_group">
					<table class="so_options_table">
						<tbody>
							<tr valign="top" class="">
								<td>
									<?php
										woocommerce_wp_select(
											array(
												'id'      => 'so_offer_priority',
												'label'   => __( 'Select priority for offer:', 'smart-offers' ),
												'class'   => 'select',
												'value'   => $offer_priority,
												'options' => array(
													'99' => __( 'Highest', 'smart-offers' ),
													'75' => __( 'High', 'smart-offers' ),
													'25' => __( 'Low', 'smart-offers' ),
													'5'  => __( 'Lowest', 'smart-offers' ),
													'0'  => __( 'Default', 'smart-offers' ),
												),
											)
										);
									?>
								</td>
							</tr>
						</tbody>
					</table>
					<span class="form-field so_priority_description">
						<?php echo __( 'Sometimes offer rules can be set up in a way that multiple offers get displayed on a page. In such cases, "Offer Priority" is used to determine offers will show in which order.', 'smart-offers' ); ?><br>
						<?php echo __( 'Offers with priority:', 'smart-offers' ); ?>
						<ul class="priority-desc">
							<li>
								<?php echo __( 'Highest => Show first', 'smart-offers' ); ?>
							</li>
							<li>
								<?php echo sprintf( __( 'Default => Show as per setting in %s.', 'smart-offers' ), '<a href="' . admin_url( 'edit.php?post_type=smart_offers&page=so-settings' ) . '" target="_blank">' . __( 'Multiple Offers on page? Show offer with...', 'smart-offers' ) . '</a>' ); ?>
							</li>
						</ul>
					</span>
				</div>
			</div>
			<?php
		}

		/**
		 * Add accept and skip shortcode if not found in the offer content
		 *
		 * @since v3.6.0
		 */
		function so_add_accept_skip_links() {
			global $post;

			if ( is_object( $post ) && $post->post_type == 'smart_offers' ) {

				$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );

				$so_offer_type = get_post_meta( $offer_id, 'so_offer_type', true );
				if ( '' === $so_offer_type ) {
					?>
					<script type="text/javascript" class="smart_offers">
						jQuery(function() {
							jQuery('a#missing_shortcode').click(function() {
								if ( ( jQuery('textarea#content').css('display') == 'none' ) ) {
									jQuery('textarea#content').css('display', "");
								}

								var postContent = jQuery('textarea#content').val();

								var position = postContent.indexOf('<div class="so_accept');
								if ( position == -1 ) {
									position = postContent.indexOf('<div class="so_skip');
								}

								var trimmedContent = '';
								if ( position > 0 ) {
									trimmedContent = postContent.substr(0, position);
									trimmedContent += '<div class="so_accept"><a href="[so_acceptlink offer_id=<?php echo $offer_id; ?>]">Yes, Add to Cart</a></div>';
									trimmedContent += '<div class="so_skip"><a href="[so_skiplink offer_id=<?php echo $offer_id; ?>]">No, Skip this offer</a></div>';
								} else {
									trimmedContent = postContent + '<div class="so_accept"><a href="[so_acceptlink offer_id=<?php echo $offer_id; ?>]">Yes, Add to Cart</a></div>';
									trimmedContent += '<div class="so_skip"><a href="[so_skiplink offer_id=<?php echo $offer_id; ?>]">No, Skip this offer</a></div>';
								}

								jQuery('textarea#content').val(trimmedContent);
								jQuery('input#publish').trigger('click');
								return false;
							});
						});
					</script>
					<?php
				}
			}
		}

		/*
		 * Show available shortcodes while creating or editing an offer
		 */
		function so_available_shortcodes() {
			$offer_type = self::get_offer_type();
			?>
			<h2 style="padding: unset;">
				<div>
					<?php
					if ( 'order_bump' === $offer_type ) {
						echo __( 'To use the following shortcodes, copy and paste them in the Offer content > Content.', 'smart-offers' );
					} else {
						echo __( 'To use the following shortcodes, copy and paste them in the offer content.', 'smart-offers' );
					}
					?>
				</div>
			</h2><br>
			<div class="so_shortcode_name_desc">
				<code class="so_shortcode_name"><?php echo __( '[so_product_name]', 'smart-offers' ); ?></code>
				<?php echo wc_help_tip( __( 'To show product name in the offer.', 'smart-offers' ) ); ?>
			</div>
			<div class="so_shortcode_name_desc">
				<code class="so_shortcode_name"><?php echo __( '[so_product_short_description]', 'smart-offers' ); ?></code>
				<?php echo wc_help_tip( __( 'To show short description of the product in the offer.', 'smart-offers' ) ); ?>
			</div>
			<div class="so_shortcode_name_desc">
				<code class="so_shortcode_name"><?php echo __( '[so_quantity allow_change=true]', 'smart-offers' ); ?></code>
				<?php echo wc_help_tip( __( 'To show quantity box in the offer, allowing your customer\'s to select multiple quantities of the offered product.', 'smart-offers' ) ); ?>
			</div>
			<?php
			if ( $offer_type !== 'order_bump' ) {
				?>
				<div class="so_shortcode_name_desc">
					<code class="so_shortcode_name"><?php echo __( '[so_price]', 'smart-offers' ); ?></code>
					<?php echo wc_help_tip( __( 'To show the original price & the new price of the offered product in the offer. Add only if offered product is a simple product. For the variable product, this gets automatically added.', 'smart-offers' ) ); ?>
				</div>
				<div class="so_shortcode_name_desc">
					<code class="so_shortcode_name"><?php echo __( '[so_product_image]', 'smart-offers' ); ?></code>
					<?php echo wc_help_tip( __( 'To show offered product image in the offer.', 'smart-offers' ) ); ?>
				</div><br>
				<h2 style="padding: unset;">
					<div>
						<?php
							echo sprintf( __( '%s to know in detail how to use these shortcodes in the offer content.', 'smart-offers' ), '<a href=' . esc_url( add_query_arg( array( 'page' => 'so-shortcode' ), 'admin.php' ) ) . ' target="_blank">' . __( 'Click here', 'smart-offers' ) . '</a>' );
						?>
					</div>
				</h2>
				<?php
			}
		}

		/*
		 * Show a few doc/blog links while creating or editing an offer
		 */
		function so_resource_links() {
			?>
			<h2 style="padding: unset;">
				<div>
					<?php
						echo __( 'Here are a few links that explains types of offers you can create:', 'smart-offers' );
					?>
				</div>
			</h2>
			<ol style="margin-left: 1em !important;">
				<li>
					<a href="https://www.storeapps.org/woocommerce-giveaway/?utm_source=so&utm_medium=in_app&utm_campaign=view_giveaway_blog" target="_blank">
						<?php echo __( 'Create Giveaway offer', 'smart-offers' ); ?>
					</a>
				</li>
				<li>
					<a href="https://www.storeapps.org/woocommerce-upsells/?utm_source=so&utm_medium=in_app&utm_campaign=view_upsells_blog" target="_blank">
						<?php echo __( 'Create Upsells, Downsell offers', 'smart-offers' ); ?>
					</a>
				</li>
				<li>
					<a href="https://www.storeapps.org/woocommerce-bogo/?utm_source=so&utm_medium=in_app&utm_campaign=view_bogo_blog" target="_blank">
						<?php echo __( 'Create BOGO offer', 'smart-offers' ); ?>
					</a>
				</li>
				<li>
					<a href="https://www.storeapps.org/how-to-create-1-click-upsells-in-woocommerce/?utm_source=so&utm_medium=in_app&utm_campaign=view_one_click_upsell_blog" target="_blank">
						<?php echo __( 'Setup One Click Upsell', 'smart-offers' ); ?>
					</a>
				</li>
				<li>
					<a href="https://www.storeapps.org/woocommerce-upsell-offer-bulk-purchase-user-roles/?utm_source=so&utm_medium=in_app&utm_campaign=view_bulk_user_blog" target="_blank">
						<?php echo __( 'Create offer to target customers based on bulk purchase / user roles', 'smart-offers' ); ?>
					</a>
				</li>
			</ol>
			<h2 style="padding: unset;">
				<div>
					<?php
						echo __( 'To know more, click on orange icon (Quick Help) at the bottom of the page.', 'smart-offers' );
					?>
				</div>
			</h2>
			<?php
		}

		/**
		 * Search for offers and return json
		 *
		 * @access public
		 * @return void
		 * @see WC_AJAX::woocommerce_json_search_offers()
		 */
		function woocommerce_json_search_offers( $x = '', $post_types = array( 'smart_offers' ) ) {

			check_ajax_referer( 'search-offers', 'security' );

			$term = (string) urldecode( stripslashes( strip_tags( $_GET ['term'] ) ) );

			if ( empty( $term ) ) {
				die();
			}

			$args = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => 'offer_title',
						'value'   => $term,
						'compare' => 'LIKE',
					),
				),
				'fields'         => 'ids',
			);

			$posts = get_posts( $args );

			$found_offers = array();

			if ( $posts ) {
				foreach ( $posts as $post ) {
					$found_offers [ $post ] = get_the_title( $post );
				}
			}

			echo json_encode( $found_offers );

			die();
		}

		/**
		 * Search for coupons and return json
		 *
		 * @access public
		 * @return void
		 * @see WC_AJAX::woocommerce_json_search_coupons()
		 */
		function woocommerce_json_search_coupons( $x = '', $post_types = array( 'shop_coupon' ) ) {
			global $wpdb, $sa_smart_offers;

			check_ajax_referer( 'search-coupons', 'security' );

			$term = (string) urldecode( stripslashes( strip_tags( $_GET['term'] ) ) );

			if ( empty( $term ) ) {
				die();
			}

			// Case: When Smart Coupons is de-activated, following line will always return core discount types and not type smart_coupon.
			// This will result in coupons with discount type set as smart_coupon not showing up in search results.
			$all_discount_types = wc_get_coupon_types();

			$search_coupons_args = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				's'              => $term,
				'fields'         => 'ids',
			);

			$found_coupon_ids = new WP_Query( $search_coupons_args );
			$found_coupons    = array();
			if ( $found_coupon_ids->post_count > 0 ) {
				foreach ( $found_coupon_ids->posts as $coupon_id ) {
					$discount_type = get_post_meta( $coupon_id, 'discount_type', true );
					if ( ! empty( $all_discount_types[ $discount_type ] ) ) {
						$discount_type                                = sprintf( __( ' ( Type: %s )', 'smart-offers' ), $all_discount_types[ $discount_type ] );
						$found_coupons[ get_the_title( $coupon_id ) ] = get_the_title( $coupon_id ) . $discount_type;
					}
				}
			}
			echo json_encode( $found_coupons );

			die();
		}

		/**
		 * Search for categories and return json
		 *
		 * @access public
		 * @return void
		 * @see WC_AJAX::woocommerce_json_search_prod_category()
		 */
		function woocommerce_json_search_prod_category( $x = '', $category = array( 'product_cat' ) ) {

			check_ajax_referer( 'so-search-product-category', 'security' );

			$term = (string) urldecode( stripslashes( strip_tags( $_GET ['term'] ) ) );

			if ( empty( $term ) ) {
				die();
			}

			$args = array(
				'search'     => $term,
				'hide_empty' => 0,
			);

			$get_category_by_name = get_terms( 'product_cat', $args );

			$found_category = array();

			if ( $get_category_by_name ) {
				foreach ( $get_category_by_name as $term ) {
					$found_category[ $term->term_id ] = $term->name;
				}
			}

			echo json_encode( $found_category );

			die();
		}

		/**
		 * Search for attribute values and return json
		 *
		 * @access public
		 * @return void
		 * @see WC_AJAX::woocommerce_json_search_product_attribute()
		 */
		function woocommerce_json_search_product_attribute( $x = '', $attribute = array( 'pa_' ) ) {

			check_ajax_referer( 'so-search-product-attribute', 'security' );

			$term               = (string) urldecode( stripslashes( strip_tags( $_GET ['term'] ) ) );
			$selected_attribute = (string) urldecode( stripslashes( strip_tags( $_GET ['key'] ) ) );

			if ( empty( $term ) || empty( $selected_attribute ) ) {
				die();
			}

			$args = array(
				'search'     => $term,
				'hide_empty' => 0,
			);

			$get_attribute_by_name = get_terms( $selected_attribute, $args );

			$found_attribute = array();

			if ( $get_attribute_by_name ) {
				foreach ( $get_attribute_by_name as $term ) {
					$found_attribute[ $term->term_id ] = $term->name;
				}
			}

			echo json_encode( $found_attribute );

			die();

		}

		/**
		 * Search for simple products, variations and return json
		 *
		 * @access public
		 * @return void
		 * @see WC_AJAX::woocommerce_json_search_prod_category()
		 */
		function woocommerce_json_search_products_and_only_variations( $x = '', $post_types = array( 'product', 'product_variation' ) ) {

			check_ajax_referer( 'search-products-and-only-variations', 'security' );

			global $sa_smart_offers;

			$term = (string) urldecode( stripslashes( strip_tags( $_GET['term'] ) ) );

			if ( empty( $term ) ) {
				die();
			}

			if ( is_numeric( $term ) ) {

				$args = array(
					'post_type'      => $post_types,
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => -1,
					'post__in'       => array( 0, $term ),
					'fields'         => 'ids',
				);

				$args2 = array(
					'post_type'      => $post_types,
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => -1,
					'post_parent'    => $term,
					'fields'         => 'ids',
				);

				$args3 = array(
					'post_type'      => $post_types,
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => '_sku',
							'value'   => $term,
							'compare' => 'LIKE',
						),
					),
					'fields'         => 'ids',
				);

				$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ), get_posts( $args3 ) ) );
			} else {

				$args = array(
					'post_type'      => $post_types,
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => -1,
					's'              => $term,
					'fields'         => 'ids',
				);

				$args2 = array(
					'post_type'      => $post_types,
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => '_sku',
							'value'   => $term,
							'compare' => 'LIKE',
						),
					),
					'fields'         => 'ids',
				);

				$posts = array_unique( array_merge( get_posts( $args, ARRAY_A ), get_posts( $args2, ARRAY_A ) ) );
			}

			$found_products = array();

			if ( $posts ) {

				foreach ( $posts as $post ) {

					$post_type    = get_post_type( $post );
					$product_type = wp_get_object_terms( $post, 'product_type', array( 'fields' => 'slugs' ) );

					if ( $post_type == 'product' && $product_type[0] == 'variable' ) {
						continue;
					} else {
						$product = wc_get_product( $post );
						if ( ( $product instanceof WC_Product ) ) {
							$found_products[ $post ] = $product->get_formatted_name();
						}
					}
				}
			}

			echo json_encode( $found_products );

			die();
		}

		/**
		 * Add default content in offer description
		 */
		function so_add_default_content( $content ) {
			global $post_type;

			if ( isset( $post_type ) ) {
				if ( $post_type == 'smart_offers' ) {
					$offer_type = self::get_offer_type();
					$content    = '';
					if ( 'order_bump' !== $offer_type ) {
						$content = '<h1 class="so-headline">Offer Heading</h1>
<div class="so-offer">
	[so_product_image image="yes"]
	<div class="so-offer-container">
		<h3 class="product_title entry-title">[so_product_name]</h3>
		<div class="woocommerce-product-details__short-description so-show-prod-desc">[so_product_short_description]</div>
		<div class="so-show-offer-price">[so_price]</div>
		<span name="so-prod-var"></span>
	</div>
</div>

<div class="so_accept"><a href="[so_acceptlink]">Yes, Add to Cart</a></div>
<div class="so_skip"><a href="[so_skiplink]">No, Skip this offer</a></div>';
					}

					return $content;
				}
			}
		}

		/**
		 * Add custom message for SO
		 */
		public function so_add_custom_messages( $messages ) {
			$post_ID                       = isset( $post_ID ) ? (int) $post_ID : 0;
			$so_offer_type                 = self::get_offer_type();
			$messages ['smart_offers'] [1] = sprintf( __( 'Offer updated successfully.' ) );

			// Add this message only if it is not an order bump offer type.
			if ( 'order_bump' !== $so_offer_type ) {
				$messages ['smart_offers'] [2] = sprintf( __( '<strong>Warning:</strong> Offer description does not include accept / skip links. <a id="missing_shortcode" href="">Click here to fix it automatically.</a>' ) );
			}
			return $messages;
		}

		/**
		 * Add [so_product_variant] shortcode in offer description if not present
		 */
		function add_shortcode_in_post_content( $data ) {

			// To execute this only if post type is smart_offers
			if ( $data['post_type'] != 'smart_offers' ) {
				return $data;
			}

			global $sa_smart_offers;

			if ( isset( $_POST['post_ID'] ) ) {
				$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $_POST['post_ID'] );

			}
			if ( isset( $_POST ['target_product_ids'] ) && isset( $_POST ['content'] ) ) {

				$offered_product_id = implode( ',', $_POST ['target_product_ids'] );

				$offered_prod_instance = wc_get_product( $offered_product_id );
				$sc_position           = strpos( $_POST ['content'], '[so_product_variants' );
				$add_sc                = false;

				if ( $offered_prod_instance->is_type( 'variable' ) && ( $sc_position === false ) ) {
					$add_sc = true;
				}

				if ( $add_sc == true ) {
					$position = strpos( $_POST['content'], '<div class=\"so-show-offer-price' );
					if ( ! empty( $position ) && ( strpos( $_POST['content'], '[so_price]' ) !== false ) ) {     // To insert [so_product_variants] at a particular position in the offer content
						$post_offer_content   = $_POST['content'];
						$shortcode_to_insert  = '[so_product_variants]';
						$updated_content      = substr_replace( $post_offer_content, $shortcode_to_insert, $position, 89 );
						$data['post_content'] = $updated_content;
					} else {
						$data['post_content'] = '[so_product_variants]' . $_POST['content'];
					}

					add_filter( 'redirect_post_location', array( $this, 'my_redirect_post_location_filter' ) );
				}
			}

			// @since SO v3.6.0
			if ( ! empty( $offer_id ) ) {
				$accept_position = strpos( $data['post_content'], '[so_acceptlink]' );
				if ( ! empty( $accept_position ) ) {
					$default_accept_shortcode = '[so_acceptlink]';
					$new_accept_shortcode     = '[so_acceptlink offer_id=' . $offer_id . ']';
					$updated_accept_shortcode = str_replace( $default_accept_shortcode, $new_accept_shortcode, $data['post_content'] );
					$data['post_content']     = $updated_accept_shortcode;
				}
				$skip_position = strpos( $data['post_content'], '[so_skiplink]' );
				if ( ! empty( $skip_position ) ) {
					$default_skip_shortcode = '[so_skiplink]';
					$new_skip_shortcode     = '[so_skiplink offer_id=' . $offer_id . ']';
					$updated_skip_shortcode = str_replace( $default_skip_shortcode, $new_skip_shortcode, $data['post_content'] );
					$data['post_content']   = $updated_skip_shortcode;
				}
			}

			return $data;
		}

		/**
		 * Add redirect parameter after adding shortcode
		 */
		function my_redirect_post_location_filter( $location ) {
			remove_filter( 'redirect_post_location', __FUNCTION__ );
			$location = add_query_arg( 'show_sc_msg', true, $location );
			return $location;
		}

		/**
		 * Add additional CSS
		 */
		function so_admin_script_and_style() {

			global $typenow, $post;

			if ( $typenow == 'smart_offers' ) {

				$plugin_data = SA_Smart_Offers::get_smart_offers_plugin_data();
				$version     = $plugin_data['Version'];

				wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), $version );
				wp_enqueue_style( 'jquery-ui-style', WC()->plugin_url() . '/assets/css/jquery-ui/jquery-ui.min.css', array(), $version );
				wp_enqueue_style( 'so_admin_styles', plugins_url( SMART_OFFERS ) . '/assets/css/admin.css', array(), $version );
				wp_enqueue_script( 'smart_offers_admin_js', plugins_url( SMART_OFFERS ) . '/assets/js/quick-bulk-edit.js', array( 'jquery', 'inline-edit-post' ), $version, true );

				// Enqueueing WP CodeMirror library for SO custom CSS textarea only on Add/Edit SO screen
				$current_screen = get_current_screen();
				if ( $current_screen instanceof WP_Screen && $current_screen->id == 'smart_offers' ) {
					wp_enqueue_script( 'so_cm_settings', plugins_url( SMART_OFFERS ) . '/assets/js/custom-css.js', array(), $version );
					$so_cm_settings['codeEditor'] = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
					wp_localize_script( 'so_cm_settings', 'so_cm_settings', $so_cm_settings );

					wp_enqueue_script( 'wp-theme-plugin-editor' );
					wp_enqueue_style( 'wp-codemirror' );
				}

				// Registering and enqueueing smart-offers-admin.js only on SO dashboard and Edit SO screen
				if ( $current_screen instanceof WP_Screen && ( $current_screen->id == 'edit-smart_offers' || $current_screen->id == 'smart_offers' ) ) {
					wp_register_script( 'smart-offers-admin', plugins_url( SMART_OFFERS ) . '/assets/js/smart-offers-admin.js', array( 'jquery' ), $version );
					wp_enqueue_script( 'smart-offers-admin' );

					$so_admin_data = array(
						'i18n_data' => array(
							'default_offer_text'           => __( 'Offer', 'smart-offers' ),
							'new_order_button_text'        => __( 'Order Bump', 'smart-offers' ),
							'new_order_bump_button_text'   => __( 'Order Bump', 'smart-offers' ),
							'order_bump_css_override_text' => __( 'Do you really want to change order bump layout? Changing layout will reset custom CSS back to default for that style.', 'smart-offers' ),
						),
					);
					wp_localize_script( 'smart-offers-admin', 'so_admin_data', $so_admin_data );
				}
			}

		}

		/**
		 * Get current offer's type
		 *
		 * @param int $offer_id
		 * @return string $so_offer_type
		 */
		static function get_offer_type( $offer_id = 0 ) {
			global $pagenow, $post;

			// SO Offer Type (Normal offer or order bump offer)
			$so_offer_type = '';
			if ( ! empty( $offer_id ) ) {
				$so_offer_type = get_post_meta( $offer_id, 'so_offer_type', true );
			} else {
				if ( 'post.php' === $pagenow && is_object( $post ) ) {
					$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post->ID );
					if ( isset( $offer_id ) ) {
						$so_offer_type = get_post_meta( $offer_id, 'so_offer_type', true );
					}
				} elseif ( 'post-new.php' === $pagenow ) {
					$so_offer_type = ! empty( $_GET['so_offer_type'] ) ? sanitize_text_field( $_GET['so_offer_type'] ) : '';
				}
			}

			return $so_offer_type;

		}

	}
	return new SO_Admin_Offer();
}
