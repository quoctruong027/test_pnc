<?php
/**
 * Smart Offers
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.2.0
 * @package     Smart Offers
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('SO_Offer')) {

	Class SO_Offer {

		/**
		 * Preparing the valid offer to show
		 */
		function prepare_offer($display_as, $offers_data) {

			extract( $offers_data );

			// Show offer
			if ( !empty( $offer_data ) ) {
				$show_offer_as_popup = (!empty($display_as) ) ? $display_as : '';

				if (count($offer_data) > 1) {
					$show_offer_as_popup = "inline";
				}

				foreach ($offer_data as $data) {
					$this->show_offer($data, $page, $where_url, $show_offer_as_popup);
				}
			} else {
				if ($skipped_offer_id_variable && !empty($where_url)) {
					ob_clean();

					if (SO_Session_Handler::check_session_set_or_not($skip_offer_id_variable)) {
						SO_Session_Handler::so_delete_session($skip_offer_id_variable);
					}
					wp_safe_redirect($where_url);
					exit();
				}
			}
		}

		/**
		 * Show Offer
		 */
		function show_offer($offer_data, $page, $where_url, $display_as) {

			if ( !empty($offer_data) ) {

				$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_data ['post_id'] );

				$offer_rule_page_options = get_post_meta( $offer_id, 'offer_rule_page_options', true );
				$offer_rule_pages = explode( ',', $offer_rule_page_options );

				// When multiple offers are shown after checkout, avoid showing offer them before checkout + Compatibility with BN - Quick Checkout
				if( ( $page == 'checkout_page' ) && ( in_array( 'post_checkout_page', $offer_rule_pages ) ) && ! ( in_array( 'checkout_page', $offer_rule_pages ) ) ) {
					return;
				}

				$plugin_data = SA_Smart_Offers::get_smart_offers_plugin_data();
				$version = $plugin_data['Version'];
				if ( ! wp_script_is( 'jquery' ) ) {
					wp_enqueue_script( 'jquery' );
				}

				if( ! wp_style_is( 'so_frontend_css', $list = 'enqueued' ) ) {
					wp_enqueue_style( 'so_frontend_css', plugins_url(SMART_OFFERS) . '/assets/css/frontend.css', array(), $version );
				}

				$post_content = $this->return_post_content($offer_id, $page, $where_url);

				if ( !empty( $display_as ) ) {
					if ( $display_as == "popup" ) {
						$show_offer_as = "offer_as_popup";
					}
					if ( $display_as == "inline" ) {
						$show_offer_as = "offer_as_inline";
					}
				} else {
					$show_offer_as = get_post_meta( $offer_id, 'so_show_offer_as', true );
				}

				// Force offer to show as inline when quick checkout is enabled in Buy Now
				if ( class_exists('WC_Buy_Now') && get_option( 'wc_buy_now_is_quick_checkout' ) == 'yes' && get_option( 'wc_buy_now_set_for' ) == 'buy-now' && $show_offer_as == 'offer_as_popup' ) {
					$show_offer_as = "offer_as_inline";
				}

				echo apply_filters( 'the_content', $post_content );

				if ( $show_offer_as == "offer_as_inline" ) {
					$js = 'jQuery("#so-offer-content-' . $offer_id . '").css( "display" , "inline" );';
				} elseif ($show_offer_as == "offer_as_popup") {

					if ( ! wp_script_is( 'jquery' ) ) {
						wp_enqueue_script( 'jquery' );
						wp_enqueue_style( 'jquery' );
					}

					if ( ! wp_script_is( 'so_magnific_popup_js' ) ) {
						wp_enqueue_script ( 'so_magnific_popup_js', plugins_url('smart-offers/assets/js/jquery.magnific-popup.js'), array(), $version );
					}

					if (!wp_style_is('so_magnific_popup_css')) {
						wp_enqueue_style ( 'so_magnific_popup_css', plugins_url('smart-offers/assets/css/magnific-popup.css'), array(), $version );
					}

					$js = "jQuery(document).ready(function() {

								jQuery('#so-offer-content-". $offer_id . "').addClass('white-popup');

								//magnificPopup

								jQuery.magnificPopup.open({
										items: {
												  src: jQuery('#so-offer-content-" . $offer_id . "')
												},
											type: 'inline',
											modal: true,
											tError: '". __( 'The content could not be loaded.' , 'smart-offers' ) . "'
								 });
						});";
				}

				wc_enqueue_js($js);

				// After accepting an offer, 'offer_shown' was counted additionally, hence skipping it
				if ( ! ( !empty( $_REQUEST['so_action'] ) && $_REQUEST['so_action'] == 'accept' ) ) {
					$so_offer_type = get_post_meta( $offer_id, 'so_offer_type', true );
					// If it ajax request and offer type is order bump then don't update seen count as it is already updated on page load.
					if( ! ( wp_doing_ajax() && 'order_bump' === $so_offer_type ) ) {
						$this->update_accept_skip_count($offer_id, 'offer_shown');
					}
				}

			}
		}

		/**
		 * Calculate Offer price
		 */
		function get_offer_price($offer_data) {

			global $sa_smart_offers;

			if ( isset($offer_data['offer_id']) ) {
				$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_data['offer_id'] );
			}

			if ( isset( $offer_data['prod_id'] ) && $offer_data['prod_id'] != '' ) {
				$offered_prod_id = $offer_data['prod_id'];
			} else {
				$offered_prod_id = get_post_meta($offer_id, 'target_product_ids', true);
			}

			if ( !empty( $offered_prod_id ) ) {
				$offered_prod_instance = wc_get_product($offered_prod_id);
				if( !( $offered_prod_instance instanceof WC_Product ) ) {
					return;
				}
			}

			$priority = get_option( 'so_if_multiple', 'high_price' );

			$valid_product_types_for_known_ids = apply_filters( 'valid_product_types_for_known_ids', array( 'simple', 'variation', 'subscription' ) );
			$valid_product_types_for_unknown_ids = apply_filters( 'valid_product_types_for_unknown_ids', array( 'variable' ) );

			$offer_price = '';
			if ( !empty( $offered_prod_instance ) ) {
				$offered_product_type = $offered_prod_instance->get_type();

				// Fetch price of the offered product
				if ( in_array( $offered_product_type, $valid_product_types_for_known_ids ) ) {
					if ( $offered_product_type == 'subscription_variation' || $offered_product_type == 'subscription' ) {			// To calculate correct price if offered product is Subscription (Simple / Variable / Variation)
						$price = WC_Subscriptions_Product::get_price( $offered_prod_instance );
					} else {
						$price = $offered_prod_instance->get_price();
					}
				} elseif ( in_array( $offered_product_type, $valid_product_types_for_unknown_ids ) ) {
					if ( $priority == "high_price" || $priority == "random" ) {
						$price = $offered_prod_instance->get_variation_price( 'max', false );
					} elseif ($priority == "low_price") {
						$price = $offered_prod_instance->get_variation_price( 'min', false );
					}
				}

				$discount_type = get_post_meta($offer_id, 'discount_type', true);
				$discount_price = get_post_meta($offer_id, 'offer_price', true);

				// Calculating discount price
				switch ($discount_type) {
					case "fixed_price" :
						$offer_price = $discount_price;
						break;
					case "price_discount" :
						$offer_price = $price - $discount_price;
						break;
					case "percent_discount" :
						$percent_discount = ( $price != 0 ) ? ( $discount_price / 100 ) * $price : 0;
						$offer_price = $price - $percent_discount;
						break;
					default:
						$offer_price = $price;
						break;
				}

				$offer_price = ( $offer_price < 0 ) ? 0 : $offer_price;				
			}

			return $offer_price;
		}

		/**
		 * modify shortcode params and return Offer description/content
		 */
		function return_post_content($offer_id, $page, $where_url) {

			global $post, $product, $sa_smart_offers;

			$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_id );

			$post = get_post($offer_id);
			$post_content = $post->post_content;

			$offer_type = get_post_meta( $offer_id, 'so_offer_type', true );
			if( 'order_bump' === $offer_type ) {
				$so_offers = new SO_Offers();
				list( $accepted_session_variable, $accepted_ids_in_session ) = $so_offers->get_accepted_offer_ids_from_session();
				$is_offer_accepted = $accepted_session_variable && in_array( $offer_id, $accepted_ids_in_session ) ? true : false; // Check if current offer is already accepted.
				$order_bump_style = get_post_meta( $offer_id, 'so_order_bump_style', true );
				if( empty( $order_bump_style ) ) {
					$order_bump_style = 'default';
				}
				$order_bump_template = 'so-order-bump-' . $order_bump_style . '.php';
				ob_start();
				wc_get_template(
					$order_bump_template, 
					array(
						'offer_id'	=> $offer_id,
						'is_offer_accepted'	=> $is_offer_accepted,
						'post_content'	=> $post_content, // Pass existing post content data to template to place it in between order bump content.
					),
					'',
					SA_SO_PLUGIN_DIRPATH . '/templates/'
				);
				$order_bump_content = ob_get_clean();
				$post_content = $order_bump_content;
			}
			
			$post_content .= '<input type="hidden" id="so-offer-id" value="' . $offer_id . '">';

//                          ============ Modifying shortcode [so_accept_link] & [so_skip_link] =====

			$shortcode_accept_start = strpos($post_content, '[so_acceptlink');

			if( $page == 'post_checkout_page' ) {
				$source = 'so_post_checkout';
			} else if( $page == 'checkout_page' ) {
				$source = 'so_pre_checkout';
			} else {
				$source = '';
			}

			if ($shortcode_accept_start !== false) {
				$shortcode_accept_end = strpos($post_content, "]", $shortcode_accept_start);
				if ($shortcode_accept_end !== false) {
					$shortcode_accept_length = $shortcode_accept_end - $shortcode_accept_start + 1;
					$shortcode_accept_string = substr($post_content, $shortcode_accept_start, $shortcode_accept_length);
					if(empty($source)) {
						$new_accept_shortcode = "[so_acceptlink offer_id=" . $offer_id . " page_url=" . urlencode($where_url) . " ]";
					} else {
						$new_accept_shortcode = "[so_acceptlink offer_id=" . $offer_id . " page_url=" . urlencode($where_url) . " source=" . $source . " ]";
					}
					$post_content = str_replace($shortcode_accept_string, $new_accept_shortcode, $post_content);
				}
			}

			$shortcode_skip_start = strpos($post_content, '[so_skiplink');
			if ($shortcode_skip_start !== false) {
				$shortcode_skip_end = strpos($post_content, "]", $shortcode_skip_start);
				if ($shortcode_skip_end !== false) {
					$shortcode_skip_length = $shortcode_skip_end - $shortcode_skip_start + 1;
					$shortcode_skip_string = substr($post_content, $shortcode_skip_start, $shortcode_skip_length);
					if(empty($source)) {
						$new_skip_shortcode = "[so_skiplink offer_id=" . $offer_id . " page_url=" . urlencode($where_url) . " ]";
					} else {
						$new_skip_shortcode = "[so_skiplink offer_id=" . $offer_id . " page_url=" . urlencode($where_url) . " source=" . $source . " ]";
					}
					$post_content = str_replace($shortcode_skip_string, $new_skip_shortcode, $post_content);
				}
			}

//                          ============================================================================================
//                          ============ Modifying shortcode [so_product_variants] =====================================

			$offered_product = get_post_meta($post->ID, 'target_product_ids', true);
			if( !empty($offered_product) ){

				$offered_prod_instance = wc_get_product($offered_product);
				if( ( $offered_prod_instance instanceof WC_Product ) ) {
					$offered_product_type = $offered_prod_instance->get_type();
				}

				if ( $offered_product_type == "variable" || $offered_product_type == "variable-subscription" ) {

					$shortcode_start = strpos($post_content, '[so_product_variants');
					$shortcode_end = strpos($post_content, "]", $shortcode_start);

					if ($shortcode_start !== false && $shortcode_end !== false) {
						$shortcode_length = $shortcode_end - $shortcode_start + 1;
						$shortcode_string = substr($post_content, $shortcode_start, $shortcode_length);

						if ( strpos( $post_content, '[so_product_image' ) !== false ) {
							$start = strpos($post_content, '[so_product_image');
							$end = strpos($post_content, "]", $start);
							if ( $start !== false && $end !== false ) {
								$length = $end - $start + 1;
								$string = substr($post_content, $start, $length);
								$post_content = str_replace( $string, '', $post_content );
								$image_content = 'image="yes"';

						} else {
							$image_content = 'image="no"';
						}
					} else {
						$image_content = 'image="no"';
					}

					$new_shortcode = "[so_product_variants prod_id=" . $offered_product . " offer_id=" . $offer_id . " " . $image_content . " page=" . $page . " where_url=" . $where_url . "]";
					$post_content = str_replace($shortcode_string, $new_shortcode, $post_content);
				}
			}
		}
//                          ============================================================================================
//                          ============ Modifying shortcode [so_quantity] =====================================

			$shortcode_quantity_start = strpos($post_content, '[so_quantity');
			if ($shortcode_quantity_start !== false) {
				$shortcode_quantity_end = strpos($post_content, "]", $shortcode_quantity_start);
				if ($shortcode_quantity_end !== false) {

					$shortcode_quantity_length = $shortcode_quantity_end - $shortcode_quantity_start + 1;
					$shortcode_quantity_string = substr($post_content, $shortcode_quantity_start, $shortcode_quantity_length);
					$shortcode_qty_substr_length = ( $shortcode_quantity_end - 1 ) - $shortcode_quantity_start + 1;
					$new_qty_shortcode = substr($post_content, $shortcode_quantity_start, $shortcode_qty_substr_length);
					$new_qty_shortcode .= " prod_id=" . $offered_product . " offer_id=" . $offer_id . " page=" . $page . " where_url=" . $where_url . "]";
					$post_content = str_replace($shortcode_quantity_string, $new_qty_shortcode, $post_content);
				}
			}

			$show_offer_as = get_post_meta($offer_id, 'so_show_offer_as', true);
			$custom_css = get_post_meta( $offer_id, 'so_custom_css', true );

			if( $show_offer_as == "offer_as_popup" ) {
				$class = 'so-popup';
			} elseif( $show_offer_as == "offer_as_inline" ) {
				$class = 'so-inline';
			}

			if ( ! empty ( $custom_css ) ) {
				$offer_class = '#so-entry-content-'. $offer_id;
				$custom_css = str_replace( "#so_this_offer", $offer_class, $custom_css );

				return '<div class="so-offer-content ' . $class . ' ' . $offer_type . '" id="so-offer-content-' . $offer_id . '" style="display:none;"><style type="text/css" id="so-custom-css-' . $offer_id . '">'. $custom_css .'</style><div id="so-entry-content-' . $offer_id . '" class="entry-content woocommerce" >' . $post_content . '</div></div>';
			}

			return '<div class="so-offer-content ' . $class . ' ' . $offer_type . '" id="so-offer-content-' . $offer_id . '" style="display:none;"><div id="so-entry-content-' . $offer_id . '" class="entry-content woocommerce" >' . $post_content . '</div></div>';
		}

		/**
		 * Change accept/skip count of an offer
		 */
		function update_accept_skip_count($current_offer_id, $meta_key) {
			$accept_skip_counter = get_post_meta($current_offer_id, 'so_accept_skip_counter', true);

			if ( empty( $accept_skip_counter ) ) {
				$accept_skip_counter = array();
			}

			$count = ( ! array_key_exists( $meta_key, $accept_skip_counter ) ) ? 1 : ++$accept_skip_counter [$meta_key];

			$accept_skip_counter [$meta_key] = $count;

			update_post_meta($current_offer_id, 'so_accept_skip_counter', $accept_skip_counter);

		}

		/**
		 * Function to show offer forcefully
		 */
		function force_show_smart_offers($redirect_to) {

			$response = array();
			$so_offers = new SO_Offers();

			list($where, $where_url) = $so_offers->get_page_details();
			if ( ( strpos( $where_url, 'so_action=skip' ) ) || ( strpos( $where_url, 'so_action=accept') ) ) {
				$where_url = esc_url_raw( remove_query_arg( array('so_action', 'so_offer_id', 'source'), $where_url ) );
			}

			list($accepted_session_variable, $accepted_ids_in_session) = $so_offers->get_accepted_offer_ids_from_session();
			list($skipped_session_variable, $skipped_ids_in_session) = $so_offers->get_skipped_offer_ids_from_session();

			$skip_offer_id_variable = ( $where == "any" ) ? str_replace(array('/', '-', '&', '=', ':'), '', $where_url) . '_skip_offer_id' : $where . '_skip_offer_id';
			list($offer_id_on_skipping, $skipped_offer_id_variable) = $so_offers->get_offer_id_on_skipping($skip_offer_id_variable);

			$offers_processed_by_user = array();

			$redirect_to = explode(',', $redirect_to);
			$valid_another_offer_ids = array();

			foreach ($redirect_to as $value) {
				$unset_offer_id = false;

				if (in_array($value, $offers_processed_by_user)) {
					$unset_offer_id = true;
				}

				if ($skipped_session_variable) {
					if (in_array($value, $skipped_ids_in_session)) {
						$unset_offer_id = true;
					}
				}

				if ($accepted_session_variable) {
					if (in_array($value, $accepted_ids_in_session)) {
						$unset_offer_id = true;
					}
				}

				if ($unset_offer_id && $unset_offer_id == true) {
					$key = array_search($value, $redirect_to);
					unset($redirect_to [$key]);
				} else {
					$get_offer_price = $this->get_offer_price(array('offer_id' => $value));
					$valid_another_offer_ids[$value] = $get_offer_price;
				}
			}

			// TODO: Define settings class and fetch value from it.
			$get_option_for_hidden = get_option( 'so_show_hidden_items' );
			$get_option_for_price  = get_option( 'so_if_multiple' );
			$show_another_offer_data = $so_offers->process_offers($get_option_for_hidden, $get_option_for_price, $valid_another_offer_ids, $where, array());

			if (!empty($show_another_offer_data)) {
				$valid_offer_id = $show_another_offer_data[0]['post_id'];
				SO_Session_Handler::so_set_session_variables($skip_offer_id_variable, $valid_offer_id);
			}

			// Create nonce for this offer actions
			$data = array(
						'so_actions_security' => wp_create_nonce( 'so_actions_security' ),
					);

			if( count( $redirect_to ) > 0 ) {
				$linked_offers_html = do_shortcode( '[so_show_offers offer_ids="' . implode( ',', $redirect_to ) . '"]' );
				if( ! empty( $linked_offers_html ) ) {
					$data['linked_offers_html'] = $linked_offers_html; // Linked Upsell/Downsell offer ids.
				}
			}

			$this->process_response( $where_url, 'success', $data );

		}

		/**
		 * Action to perform when offer is accepted
		 */
		function action_on_accept_offer($post_id, $page, $parent_offer_id, $variation_data) {

			global $current_user, $sa_smart_offers;

			$response = array();

			$source = ( !empty( $_GET['source']) ) ? $_GET['source'] : null;

			$so_offers = new SO_Offers();

			$current_offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $_GET['so_offer_id'] );

			list($where, $where_url) = $so_offers->get_page_details();
			if ( ( strpos( $where_url, 'so_action=skip' ) ) || ( strpos( $where_url, 'so_action=accept') ) ) {
				$where_url = esc_url_raw( remove_query_arg( array('so_action', 'so_offer_id', 'source'), $where_url ) );
			}

			list($accepted_session_variable, $accepted_ids_in_session) = $so_offers->get_accepted_offer_ids_from_session();
			list($skipped_session_variable, $skipped_ids_in_session) = $so_offers->get_skipped_offer_ids_from_session();

			$skip_offer_id_variable = ( $where == "any" ) ? str_replace(array('/', '-', '&', '=', ':'), '', $where_url) . '_skip_offer_id' : $where . '_skip_offer_id';
			list($offer_id_on_skipping, $skipped_offer_id_variable) = $so_offers->get_offer_id_on_skipping($skip_offer_id_variable);

			$parent_offer_id_variable = ( $where == "any" ) ? str_replace(array('/', '-', '&', '=', ':'), '', $where_url) . '_parent_offer_id' : $where . '_parent_offer_id';
			$check_parent_offer_id_set_or_not = SO_Session_Handler::check_session_set_or_not($parent_offer_id_variable);

			if (!$check_parent_offer_id_set_or_not) {
				SO_Session_Handler::so_set_session_variables($parent_offer_id_variable, $current_offer_id);
			}

			if (isset($variation_data['variation_id']) && $variation_data['variation_id'] != '') {
				$target_product_id = $variation_data['variation_id'];
			} else {
				$target_product_id = get_post_meta($post_id, 'target_product_ids', true);
			}

			$quantity = ( isset($variation_data['quantity']) && !empty($variation_data['quantity']) ) ? $variation_data['quantity'] : 1;

			if ( !empty($target_product_id) ) {

				if ( isset($variation_data['variation_id']) && $variation_data['variation_id'] != '' ) {		// when a variable product is offered
					$all_variations_set = true;
					$parent_id = $variation_data['parent_prod_id'];
					$adding_to_cart = wc_get_product($parent_id);
					$attributes = $adding_to_cart->get_attributes();
					$variation_id = $variation_data['variation_id'];
					$variation_instance = wc_get_product($variation_id);

					foreach ( $attributes as $attribute ) {

						if ( !$attribute['is_variation'] ) {
							continue;
						}

						$taxonomy = 'attribute_' . sanitize_title($attribute['name']);

						if ( !empty($_POST[$taxonomy]) ) {

							// Get value from post data
							// Don't use woocommerce_clean as it destroys sanitized characters
							$value = sanitize_title(trim(stripslashes($_POST[$taxonomy])));

							// Get valid value from variation
							$valid_value = isset( $variation_data[ $taxonomy ] ) ? $variation_data[ $taxonomy ] : '';

							// Allow if valid
							if ( $valid_value == '' || $valid_value == $value ) {
								if ( $attribute['is_taxonomy'] )
									$variation[esc_html($attribute['name'])] = $value;
								else {
									// For custom attributes, get the name from the slug
									$options = array_map('trim', explode('|', $attribute['value']));
									foreach ($options as $option) {
										if (sanitize_title($option) == $value) {
											$value = $option;
											break;
										}
									}
									$variation[esc_html($attribute['name'])] = $value;
								}
								continue;
							}
						}

						$all_variations_set = false;
					}
				} else {
					$target_product_instance = wc_get_product($target_product_id);
					if ( $target_product_instance->get_parent_id() != 0 ) {						// when a variation child is offered
						$parent_id = $target_product_instance->get_parent_id();
						$variation_id = $target_product_instance->get_id();
						$variation = $target_product_instance->get_variation_attributes();
					} else {																	// when a simple product is offered
						$parent_id = $target_product_instance->get_id();
						$variation_id = '';
						$variation = '';
					}
				}

			}

			$so_pages = array(
				'cart_page',
				'checkout_page',
				'myaccount_page',
				'home_page',
				'post_checkout_page',
				'any_page',
				'before_checkout_submit_page',
				'after_checkout_submit_page',
			);
			// Storing offer rules of parent in case of skipped offers
			if ( ( !empty( $parent_offer_id ) ) || in_array( $page, $so_pages ) ) {
				$offer_rules = get_post_meta($post_id, '_offer_rules', true);
			}

			$action_on_accept = get_post_meta($post_id, 'so_actions_on_accept', true);

			$products_to_be_removed = array();

			if ( !empty( $action_on_accept ) ) {
				if ( isset( $action_on_accept['remove_prods_from_cart'] ) ) {
					$products_to_be_removed = ( ! empty( $action_on_accept['remove_prods_from_cart'] ) ) ? explode( ',', $action_on_accept['remove_prods_from_cart'] ) : array();
				}
			}

			if ( in_array( 'all', $products_to_be_removed ) ) {
				WC()->cart->empty_cart();
				$products_to_be_removed = array();
			}

			if ( !empty( $offer_rules ) ) {

				foreach ( $offer_rules as $offer_rule ) {
					foreach ( $offer_rule as $key => $val ) {
						if ( isset( $val['offer_action'] ) && $val['offer_action'] == 'cart_contains' ) {
							$cart_contains = $val['offer_rule_value'];
						}
					}
				}

				$cart_contains = ( isset($cart_contains) ) ? explode(",", $cart_contains) : array();

				if ( !empty( $products_to_be_removed ) ) {
					foreach ($products_to_be_removed as $prod_ids) {
						$prod_parent_id = wp_get_post_parent_id($prod_ids);
						if (!empty($prod_parent_id)) {
							$products_to_be_removed[] = $prod_parent_id;
						}
					}
				}

				$cart_contains_item_key = array();
				$keys_of_products_removed = array();

				if ( !empty( $cart_contains ) ) {
					if (count($products_to_be_removed) > 0) {
						$cart_contains = array_diff($cart_contains, $products_to_be_removed);
					}

					foreach ($cart_contains as $id) {
						foreach (WC()->cart->cart_contents as $key => $values) {
							if ($id == $values['product_id'] || $id == $values['variation_id']) {
								$cart_contains_item_key[] = $key;
							}
						}
					}
				}

				if (count($products_to_be_removed) > 0) {
					foreach ($products_to_be_removed as $p_id) {
						foreach (WC()->cart->cart_contents as $key => $values) {
							if ($p_id == $values['product_id'] || $p_id == $values['variation_id']) {
								$keys_of_products_removed[] = $key;
							}
						}
					}
				}

				if (!empty($keys_of_products_removed)) {
					if (count($cart_contains_item_key) > 0) {
						$cart_contains_item_key = array_diff($cart_contains_item_key, $keys_of_products_removed);
					}
					if (count($cart_contains) < 0) {
						$cart_contains = array();
					}
					if (count($cart_contains_item_key) < 0) {
						$cart_contains_item_key = array();
					}

					if (isset($parent_offer_id) && !empty($parent_offer_id)) {
						$parent_offer_ids = array();
						array_push($parent_offer_ids, $parent_offer_id);
					} else {
						$parent_offer_ids = array();
					}

					$cart = WC()->cart->cart_contents;

					foreach ($keys_of_products_removed as $cart_key) {
						if (isset($cart[$cart_key]['smart_offers'])) {
							if (is_array($cart[$cart_key]['smart_offers']['cart_contains_keys']) && count($cart[$cart_key]['smart_offers']['cart_contains_keys']) > 0) {
								$cart_contains_item_key = array_unique(array_merge($cart_contains_item_key, $cart[$cart_key]['smart_offers']['cart_contains_keys']));
							}

							if (is_array($cart[$cart_key]['smart_offers']['cart_contains_ids']) && count($cart[$cart_key]['smart_offers']['cart_contains_ids']) > 0) {
								$cart_contains = array_unique(array_merge($cart_contains, $cart[$cart_key]['smart_offers']['cart_contains_ids']));
							}

							if (isset($cart[$cart_key]['smart_offers']['parent_offer_id']) && !empty($cart[$cart_key]['smart_offers']['parent_offer_id'])) {
								if (is_array($cart[$cart_key]['smart_offers']['parent_offer_id'])) {
									$parent_offer_id = array_unique(array_merge($parent_offer_ids, $cart[$cart_key]['smart_offers']['parent_offer_id']));
								} else {
									array_push($parent_offer_ids, $cart[$cart_key]['smart_offers']['parent_offer_id']);
								}
							}
						}
						unset(WC()->cart->cart_contents[$cart_key]);
					}

					if (is_array($parent_offer_ids) && count($parent_offer_ids) > 0) {
						$parent_offer_id = $parent_offer_ids;
					}
				}

				if ( !empty($cart_contains) && is_array($cart_contains) && !empty($cart_contains_item_key) ) {
					$args ['smart_offers'] = array('accept_offer' => true, 'offer_id' => $post_id, 'accepted_from' => $page, 'cart_contains_keys' => $cart_contains_item_key, 'cart_contains_ids' => $cart_contains);
				} else {
					$args ['smart_offers'] = array('accept_offer' => true, 'offer_id' => $post_id, 'accepted_from' => $page);
				}

			} else {
				$args ['smart_offers'] = array('accept_offer' => true, 'offer_id' => $post_id, 'accepted_from' => $page);
			}

			if ( is_array( $parent_offer_id ) && count( $parent_offer_id ) > 0 ) {
				$args ['smart_offers']['parent_offer_id'] = $parent_offer_id;
			} elseif ( !is_array( $parent_offer_id ) && $parent_offer_id != '' ) {
				$args ['smart_offers']['parent_offer_id'] = $parent_offer_id;
			}

			// Set 'sa_no_coupon' as arg in smart_offers
			if ( !empty( $action_on_accept['sa_no_coupon'] ) ) {
				$apply_coupon_on_accept = $action_on_accept['sa_no_coupon'];
				if ( $apply_coupon_on_accept == 'yes' ) {
					$args ['smart_offers']['no_coupon_on_offered_prod'] = 'yes';
				} elseif ( $apply_coupon_on_accept == 'no' ) {
					$args ['smart_offers']['no_coupon_on_offered_prod'] = 'no';
				}
			}

			// Add product to cart when offer is accepted
			if ( !empty( $action_on_accept['add_to_cart'] ) && $action_on_accept['add_to_cart'] == 'yes' ) {
				if ( !empty( $parent_id ) || !empty( $variation_id ) || !empty( $variation ) ) {
					WC()->cart->add_to_cart($parent_id, $quantity, $variation_id, $variation, $args);
				}
			}

			if ( isset( $action_on_accept['sa_apply_coupon'] ) && !empty( $action_on_accept['sa_apply_coupon'] ) ) {
				$coupons = explode(",", $action_on_accept['sa_apply_coupon']);
				if (is_array($coupons) && count($coupons) > 0) {
					foreach ($coupons as $coupon_title) {
						WC()->cart->add_discount($coupon_title);
					}
				}
			}

			if ( isset( $action_on_accept['sa_redirect_to_url'] ) && !empty( $action_on_accept['sa_redirect_to_url'] ) ) {
				$url = $action_on_accept['sa_redirect_to_url'];
			} elseif ( isset( $action_on_accept['accepted_offer_ids'] ) && !empty( $action_on_accept['accepted_offer_ids'] ) ) {
				$redirecting_option = explode(",", $action_on_accept['accepted_offer_ids']);
				if( is_array( $redirecting_option ) && count( $redirecting_option ) > 0 ) {
					foreach ( $redirecting_option as $id ) {
						$redirect_to = $id;
					}
				}

				ob_start();

				if ( $redirect_to != "" ) {
					$this->force_show_smart_offers($redirect_to);							
				}
			} elseif (isset($action_on_accept['buy_now']) && $action_on_accept['buy_now'] == true && class_exists('WC_Buy_Now')) {
				$buy_now = new WC_Buy_Now();
				$buy_now->checkout_redirect();
			} else {
				if ( $page == "cart_page" ) {
					$url = wc_get_cart_url();
				} elseif ( !empty( $source ) ) {
					if( $page == "checkout_page" && $source == 'so_post_checkout' ) {

						$form_values = SO_Session_Handler::check_session_set_or_not('so_checkout_form_data');
						if ( $form_values ) {
							$sa_so_form_checkout = SO_Session_Handler::so_get_session_value('so_checkout_form_data');
						} else {
							$sa_so_form_checkout = null;
						}

						if ( !empty( $sa_so_form_checkout ) ) {
							$_POST = $sa_so_form_checkout;
						}

						if ( wc_get_page_id( 'terms' ) > 0 ) {
							$_POST['terms'] = 'yes';
						}

						if ( $sa_smart_offers->is_wc_gte_34() ) {
							$_REQUEST['woocommerce-process-checkout-nonce'] = wp_create_nonce( 'woocommerce-process_checkout' );
						}

						wc_clear_notices();

						$woocommerce_checkout = WC()->checkout();
						$woocommerce_checkout->process_checkout();

					} else if( $page == "checkout_page" && $source == 'so_pre_checkout' ) {
						$url = wc_get_checkout_url();
					}
				} else {
					$url = wc_get_checkout_url();
				}
			}

			if ( empty( $url ) ) {
				$url = ( wp_get_referer() ) ? wp_get_referer() : wc_get_cart_url();
			}
			if ( filter_var( $url, FILTER_VALIDATE_URL ) !== FALSE ) {
				ob_clean();
				$this->process_response( $url, 'success' );
			}

		}

		/*
		 * Process response
		 * @param string $url      The path or URL to redirect to.
 		 * @param string    $status        Response status.
	 	 * @param array $data additional response data.
		 * @return bool False if the processing fails, true otherwise.
		 */
		function process_response( $url = '', $status = '', $data = array() ) {
			
			$is_cart_empty = isset( WC()->cart ) && ! WC()->cart->is_empty() ? false : true;

			$response = array(
				'redirect' => $url,
				'result' => $status,
				'data' => $data,
				'is_cart_empty' => $is_cart_empty, // Flag for js ajax function to remove WooCommerce' empty cart HTML already present on current page but cart is not empty.
			);

			if( wp_doing_ajax() ) {
				wp_send_json( $response );
			} else if( ! empty( $response['redirect'] ) ) {
				wp_redirect( $response['redirect'] );
			}

			return false;
		}
	}
}
