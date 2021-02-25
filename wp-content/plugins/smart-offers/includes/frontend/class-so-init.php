<?php
/**
 * Smart Offers
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.3.1
 * @package     Smart Offers
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SO_Init' ) ) {

	class SO_Init {

		function __construct() {
			ob_start();
			global $sa_smart_offers;

			add_action( 'wp_head', array( $this, 'so_process_offer_action' ) );
			add_action( 'wp_ajax_so_process_offer_action', array( $this, 'so_process_offer_action' ) );
			add_action( 'wp_ajax_nopriv_so_process_offer_action', array( $this, 'so_process_offer_action' ) );

			add_action( 'wp_head', array( $this, 'so_wp_head' ) );

			/**
			 * Offered product can be removed from cart using many ways
			 * 1. by making quantity zero from cart (Refer to first 6 actions)
			 * 2. by removing product from cart via cross icon (Refer to next 4 after first 6)
			 */
			if ( $sa_smart_offers->is_wc_gte_37() ) {
				add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'sa_so_after_cart_item_quantity_update' ), 1, 4 );
				add_action( 'woocommerce_remove_cart_item', array( $this, 'sa_so_before_cart_item_quantity_zero' ), 1, 2 );
			} elseif ( $sa_smart_offers->is_wc_gte_32() ) {
				add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'sa_so_after_cart_item_quantity_update' ), 1, 4 );
				add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'sa_so_before_cart_item_quantity_zero' ), 1, 2 );
			} else {
				add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'remove_offered_product' ), 10, 2 );
				add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'remove_offered_product' ), 10, 2 );
			}
			add_action( 'woocommerce_cart_updated', array( $this, 'remove_offered_product' ), 10, 2 );
			add_action( 'woocommerce_before_checkout_process', array( $this, 'remove_offered_product' ), 10, 2 );
			add_action( 'woocommerce_cart_updated', array( $this, 'remove_offered_product_having_parent' ), 10 );
			add_action( 'woocommerce_before_checkout_process', array( $this, 'remove_offered_product_having_parent' ), 10 );

			add_action( 'woocommerce_before_calculate_totals', array( $this, 'add_offered_price' ) );
			add_action( 'woocommerce_checkout_process', array( $this, 'add_offered_price_during_checkout' ) );
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'so_update_order_meta' ), 10, 2 );
			add_action( 'woocommerce_order_status_changed', array( $this, 'change_paid_through_count' ), 10, 3 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles_scripts' ) );
			add_action( 'wp_logout', array( $this, 'so_clear_session' ) );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_offered_cart_item_from_session' ), 10, 3 );

			// To restrict offered product's quantity in cart.
			$allow_update_quantity = get_option( 'so_update_quantity', 'no' );
			if ( 'no' == $allow_update_quantity ) {
				add_filter( 'woocommerce_cart_item_quantity', array( $this, 'offered_prod_cart_item_quantity' ), 10, 2 );
			}

			// Post-checkout
			add_action( 'woocommerce_after_checkout_form', array( $this, 'smart_offers_post_checkout' ) );
			add_action( 'wp_ajax_parse_checkout_form_data', array( $this, 'parse_checkout_form_data' ) );
			add_action( 'wp_ajax_nopriv_parse_checkout_form_data', array( $this, 'parse_checkout_form_data' ) );

			// Filter to prevent redirect loop for BN - guest user checkout
			add_filter( 'redirect_url_after_buy_now', array( $this, 'so_remove_extra_params' ) );

			// Filter added to make after checkout work while skipping an offer
			add_filter( 'woocommerce_payment_successful_result', array( $this, 'so_do_redirect_on_after_checkout_page' ), 1, 2 );
			add_filter( 'woocommerce_checkout_no_payment_needed_redirect', array( $this, 'so_do_redirect_on_after_checkout_page' ), 1, 2 );

			// Action to get offer content for inline preview of offer
			add_action( 'wp_ajax_so_get_preview_content_inline', array( $this, 'so_get_preview_content_inline' ) );

			// Filters to prevent applying coupon on offered product
			add_filter( 'woocommerce_coupon_is_valid_for_product', array( $this, 'smart_offers_is_valid_for_product' ), 10, 4 );
			add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'smart_offers_coupon_amount' ), 10, 5 );
		}

		// From v3.4.0
		function offer_product_ids() {

			$offer_product_ids = array();
			$cart_contents     = WC()->cart->cart_contents;

			foreach ( $cart_contents as $key => $values ) {
				if ( isset( $values['smart_offers'] ) && isset( $values['smart_offers']['no_coupon_on_offered_prod'] ) ) {
					if ( $values['smart_offers']['no_coupon_on_offered_prod'] == 'yes' ) {              // Meta (no_coupon_on_offered_prod) that determines setting in the offer
						$offer_product_ids[] = $values['product_id'];
					}
				}
			}

			return $offer_product_ids;

		}

		// From v3.4.0
		function smart_offers_is_valid_for_product( $valid, $product = null, $coupon = null, $values = null ) {

			global $sa_smart_offers;

			$coupon_excluded_offer_product_ids = $this->offer_product_ids();

			if ( isset( WC()->cart ) && isset( $coupon_excluded_offer_product_ids ) ) {
				$cart_items = WC()->cart->get_cart();
				foreach ( $cart_items as $key => $item ) {
					if ( in_array( $item['product_id'], $coupon_excluded_offer_product_ids ) ) {
						$valid = true;
					}
				}
			}

			return $valid;

		}

		function smart_offers_coupon_amount( $discount, $discounting_amount, $cart_item, $single, $coupon ) {

			$coupon_excluded_offer_product_ids = $this->offer_product_ids();
			if ( in_array( $cart_item['product_id'], $coupon_excluded_offer_product_ids ) ) {
				$discount = 0;
			}

			return $discount;

		}

		/*
		 * Ajax call to get offer content for Inline preview
		 */
		function so_get_preview_content_inline() {

			check_ajax_referer( 'so-offer-preview-inline', 'security' );

			$offer_content = '';

			if ( isset( $_POST['offer_id'] ) && ! empty( $_POST['offer_id'] ) ) {

				$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $_POST['offer_id'] );

				$so_offer      = new SO_Offer();
				$offer_content = $so_offer->return_post_content( $offer_id, $page = '', $where_url = '' );

				if ( ! empty( $offer_content ) ) {
					$offer_content = apply_filters( 'the_content', $offer_content );
				}
			}

			echo $offer_content;

			die();

		}

		/**
		 * Enqueqe Accept/Skip CSS on preview offer
		 */
		function so_wp_head() {

			// offer_id can be either in $_GET['preview_id'] OR $_GET['p']. Hence handled both cases
			if ( isset( $_GET['preview'] ) && $_GET['preview'] == 'true' && ( ! empty( $_GET['preview_id'] ) || ! empty( $_GET['p'] ) ) ) {
				$plugin_data = SA_Smart_Offers::get_smart_offers_plugin_data();
				$version     = $plugin_data['Version'];
				wp_enqueue_style( 'so_frontend_css' );

				if ( ! empty( $_GET['preview_id'] ) ) {
					$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $_GET['preview_id'] );
				} else {
					$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $_GET['p'] );
				}

				$show_offer_as = get_post_meta( $offer_id, 'so_show_offer_as', true );

				$js = '';
				if ( $show_offer_as == 'offer_as_inline' ) {
					$js = "jQuery(document).ready(function() {
								jQuery.ajax({
									url: '" . admin_url( 'admin-ajax.php' ) . "',
									type: 'POST',
									dataType: 'html',
									data: {
										action: 'so_get_preview_content_inline',
										preview: 'true',
										offer_id: '" . $offer_id . "',
										security: '" . wp_create_nonce( 'so-offer-preview-inline' ) . "'
									},
									success: function( offer_content ) {
										jQuery('div.site-content').find('div.entry-content').html('<div id=\"so_preview_inline\"></div>');
										jQuery('#so_preview_inline').html( offer_content );
										jQuery('#so-offer-content-" . $offer_id . "').css( 'display' , 'inline' );
										jQuery('form.variations_form div.images').addClass('so_product_image');
										jQuery('div.images').addClass('so_product_image');
									}
								});
							});";

					$so_offer_type = get_post_meta( $offer_id, 'so_offer_type', true );
					if ( 'order_bump' === $so_offer_type ) {
						$order_bump_style = get_post_meta( $offer_id, 'so_order_bump_style', true );

						if ( in_array( $order_bump_style, array( 'style-1' ), true ) ) {
							if ( ! wp_style_is( 'font-awesome' ) ) {
								wp_enqueue_style( 'font-awesome' );
							}
						}
					}
				} elseif ( $show_offer_as == 'offer_as_popup' ) {

					if ( ! wp_script_is( 'jquery' ) ) {
						wp_enqueue_script( 'jquery' );
						wp_enqueue_style( 'jquery' );
					}

					if ( ! wp_script_is( 'so_magnific_popup_js' ) ) {
						wp_enqueue_script( 'so_magnific_popup_js', plugins_url( 'smart-offers/assets/js/jquery.magnific-popup.js' ), array(), $version );
					}

					if ( ! wp_style_is( 'so_magnific_popup_css' ) ) {
						wp_enqueue_style( 'so_magnific_popup_css', plugins_url( 'smart-offers/assets/css/magnific-popup.css' ), array(), $version );
					}

					$so_offer      = new SO_Offer();
					$offer_content = $so_offer->return_post_content( $offer_id, $page = '', $where_url = '' );

					echo apply_filters( 'the_content', $offer_content );

					$js = "jQuery(document).ready(function() {
								jQuery('div.site-content').find('div.entry-content').html('');
								jQuery('#so-offer-content-" . $offer_id . "').addClass('white-popup');
								jQuery('form.variations_form div.images').addClass('so_product_image');
								jQuery('div.images').addClass('so_product_image');					
								//magnificPopup
								
								jQuery.magnificPopup.open({
										items: {
												  src: jQuery('#so-offer-content-" . $offer_id . "')
												},
											type: 'inline',
											modal: true,
											tError: '" . __( 'The content could not be loaded.', 'smart-offers' ) . "'
								 });
						});";
				}

				wc_enqueue_js( $js );

			}
		}

		/**
		 * Enqueue frontend scripts
		 */
		function enqueue_frontend_styles_scripts() {
			$plugin_data = SA_Smart_Offers::get_smart_offers_plugin_data();
			$version     = $plugin_data['Version'];
			$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_style( 'so_frontend_css', plugins_url( SMART_OFFERS ) . '/assets/css/frontend.css', 'so_frontend_css', $version );

			if ( ! wp_style_is( 'font-awesome', 'registered' ) ) {
				wp_register_style( 'font-awesome', plugins_url( SMART_OFFERS ) . '/assets/css/font-awesome' . $suffix . '.css', 'font-awesome', $version );
			}

			$button_style = get_option( 'so_accept_button_styles' );

			if ( $button_style == 'smart_offers_custom_style_button' ) {
				$accept = get_option( 'so_css_for_accept' );
			} else {
				$accept = get_option( $button_style );
			}

			$skip             = get_option( 'so_css_for_skip' );
			$style_for_accept = "div.so_accept { $accept }";
			$style_for_skip   = "div.so_skip { $skip }";
			wp_add_inline_style( 'so_frontend_css', $style_for_accept );
			wp_add_inline_style( 'so_frontend_css', $style_for_skip );

			if ( $button_style != 'smart_offers_custom_style_button' ) {
				$style_for_accept_text = 'div.so_accept a { text-decoration: none !important; color: white; }';
				wp_add_inline_style( 'so_frontend_css', $style_for_accept_text );
			}

			if ( wp_script_is( 'wc-add-to-cart-variation', 'registered' ) && ! wp_script_is( 'wc-add-to-cart-variation', 'enqueued' ) ) {
				wp_enqueue_script( 'wc-add-to-cart-variation' );
			}

			wp_register_script( 'smart-offers-frontend', plugins_url( SMART_OFFERS ) . '/assets/js/smart-offers-frontend.js', array( 'jquery' ), $version, true );
			wp_enqueue_script( 'smart-offers-frontend' );

			$where = '';

			if ( is_home() || is_front_page() ) {
				$where = 'home';
			} elseif ( is_cart() ) {
				$where = 'cart';
			} elseif ( is_account_page() ) {
				$where = 'myaccount';
			} elseif ( is_checkout() ) {
				global $wp;
				if ( isset( $wp->query_vars['order-received'] ) ) {
					$where = 'thankyou';
				} else {
					$where = 'checkout';
				}
			} else {
				$where = 'any';
			}

			$so_frontend_data = array(
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'ajax_error'          => __( 'An error has occured. Please try again later.', 'smart-offers' ),
				'so_actions_security' => wp_create_nonce( 'so_actions_security' ),
				'so_plugin_url'       => plugins_url( SMART_OFFERS ),
				'where'               => $where,
			);
			wp_localize_script( 'smart-offers-frontend', 'so_frontend_data', $so_frontend_data );
		}

		/*
		 * @since SO v3.3.7 onwards as action and params are changed from WC3.2
		 */
		public function sa_so_after_cart_item_quantity_update( $cart_item_key, $quantity, $old_quantity, $cart ) {
			$this->remove_offered_product( $cart_item_key, $quantity );
		}

		/*
		 * @since SO v3.3.7 onwards as action and params are changed from WC3.2
		 */
		public function sa_so_before_cart_item_quantity_zero( $cart_item_key, $cart ) {
			$this->remove_offered_product( $cart_item_key );
		}

		/**
		 * Remove upsell product from cart if cart contains rule does not satisfy
		 */
		function remove_offered_product( $cart_item_key, $quantity = 0 ) {
			if ( $quantity == 0 ) {

				$cart = WC()->cart->cart_contents;
				unset( $cart[ $cart_item_key ] );

				$count_of_offered_prod_in_cart          = 0;
				$count_of_non_offered_prod_in_cart      = 0;
				$count_offered_product_having_parent_id = 0;
				$key_of_offered_prod_having_parent_id   = array();

				foreach ( $cart as $key => $values ) {
					if ( isset( $values['smart_offers']['cart_contains_keys'] ) ) {
						$count_of_offered_prod_in_cart++;
					} else {
						$count_of_non_offered_prod_in_cart++;
					}
				}

				$offer_ids_to_unset = array();

				// To perform further execution only of there are offered prod in cart
				if ( $count_of_offered_prod_in_cart > 0 ) {
					foreach ( $cart as $key => $values ) {

						if ( isset( $values['smart_offers'] ) && isset( $values['smart_offers']['cart_contains_keys'] ) ) {
							$cart_contains_keys = $values['smart_offers']['cart_contains_keys'];

							foreach ( $cart_contains_keys as $k => $cart_key ) {

								if ( $cart_item_key == $cart_key ) {

									if ( isset( $values['smart_offers']['parent_offer_id'] ) ) {
										unset( WC()->cart->cart_contents[ $key ]['smart_offers']['cart_contains_keys'][ $k ] );
									}

									unset( $cart[ $key ]['smart_offers']['cart_contains_keys'][ $k ] );
								}
							}
						}
					}

					$cart_items_keys_to_be_removed = array();

					foreach ( $cart as $k => $v ) {
						if ( isset( $v['smart_offers'] ) && isset( $v['smart_offers']['cart_contains_keys'] ) ) {

							$cart_contains_keys = $v['smart_offers']['cart_contains_keys'];
							$cart_contains_ids  = $v['smart_offers']['cart_contains_ids'];
							$ids                = array();

							if ( ! empty( $cart_contains_keys ) ) {

								foreach ( $cart_contains_keys as $cart_contains_key ) {

									if ( $cart[ $cart_contains_key ]['variation_id'] != '' ) {
										$ids[] = $cart[ $cart_contains_key ]['variation_id'];
										$ids[] = $cart[ $cart_contains_key ]['product_id'];
									} else {
										$ids[] = $cart[ $cart_contains_key ]['product_id'];
									}
								}
							} else {

								foreach ( $cart as $item_key => $item_val ) {

									if ( $k != $item_key ) {
										if ( $cart[ $item_key ]['variation_id'] != '' ) {
											$ids[] = $cart[ $item_key ]['variation_id'];
											$ids[] = $cart[ $item_key ]['product_id'];
										} else {
											$ids[] = $cart[ $item_key ]['product_id'];
										}
									}
								}
							}

							$cart_contains_value = ( count( array_intersect( $cart_contains_ids, $ids ) ) == count( $cart_contains_ids ) ) ? 1 : 0;

							if ( $cart_contains_value == 0 ) {

								if ( isset( $v['smart_offers'] ) && isset( $v['smart_offers']['parent_offer_id'] ) ) {
									continue;
								} else {
									unset( $cart[ $k ] );
									$cart_items_keys_to_be_removed[] = $k;
								}
							} else {
								continue;
							}
						} else {
							continue;
						}
					}
					if ( ! empty( $cart_items_keys_to_be_removed ) ) {
						foreach ( $cart_items_keys_to_be_removed as $item_key ) {

							$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', WC()->cart->cart_contents[ $item_key ]['smart_offers']['offer_id'] );

							$offer_ids_to_unset[] = $offer_id;
							if ( isset( WC()->cart->cart_contents[ $key ]['smart_offers']['parent_offer_id'] ) ) {
								$offer_ids_to_unset[] = WC()->cart->cart_contents[ $key ]['smart_offers']['parent_offer_id'];
							}
							unset( WC()->cart->cart_contents[ $item_key ] );
							// WC()->cart->remove_cart_item( $item_key );

							if ( ! empty( WC()->cart->cart_contents[ $item_key ]['variation_id'] ) ) {
								$product = wc_get_product( WC()->cart->cart_contents[ $item_key ]['variation_id'] );
								if ( $product instanceof WC_Product ) {
									$removed_product_name = $product->get_formatted_name();
								}
							} else {
								$removed_product_name = get_the_title( $values['product_id'] );
							}
							wc_clear_notices();     // Workaround to prevent following notice from displaying multiple times.
							wc_add_notice( sprintf( __( 'Product %s is removed, because it is not valid for your cart contents.', 'smart-offers' ), $removed_product_name ), 'error' );
						}
					} else {
						return;
					}

					if ( count( $offer_ids_to_unset ) > 0 ) {
						SO_Session_Handler::unset_offer_ids_from_session( $offer_ids_to_unset );
					}
				} else {
					return;
				}
			} else {
				return;
			}
		}

		/**
		 * Remove upsell product from cart if rules of upsell offer or it's parent offer don't satisfy
		 */
		function remove_offered_product_having_parent() {
			$global_wc = ( function_exists( 'WC' ) ) ? WC() : null;

			if ( ! is_object( $global_wc ) || ! is_object( $global_wc->cart ) || $global_wc->cart->is_empty() ) {
				return;
			}

			$cart_contents = is_callable( array( WC()->cart, 'get_cart' ) ) ? WC()->cart->get_cart() : array();

			if ( empty( $cart_contents ) ) {
				return;
			}

			$hook_name = current_filter();

			$so_offers = new SO_Offers();

			if ( ( isset( $_GET['remove_item'] ) && $_GET['remove_item'] ) || ( ! empty( $_POST['update_cart'] ) ) ) {
				$offer_ids_to_unset = array();

				foreach ( WC()->cart->cart_contents as $key => $values ) {
					$offer_ids = array();

					if ( isset( $values['smart_offers'] ) ) {
						$so_pages = array(
							'cart_page',
							'checkout_page',
							'myaccount_page',
							'home_page',
							'any_page',
							'before_checkout_submit_page',
							'after_checkout_submit_page',
						);
						// To validate the offers on any updation of cart
						if ( in_array( $values['smart_offers']['accepted_from'], $so_pages ) ) {

							$offer_ids[] = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $values['smart_offers']['offer_id'] );

							if ( ( isset( $values['smart_offers']['cart_contains_keys'] ) && empty( $values['smart_offers']['cart_contains_keys'] ) ) ) {
								if ( is_array( $values['smart_offers']['parent_offer_id'] ) ) {
									$offer_ids = array_unique( array_merge( $offer_ids, $values['smart_offers']['parent_offer_id'] ) );
								} else {
									$offer_ids[] = $values['smart_offers']['parent_offer_id'];
								}
							}
						}

						if ( ! empty( $offer_ids ) ) {
							// Get all user details
							$user_details = $so_offers->get_user_details( 'cart', '' );

							// Get Cart/Order details
							$cart_order_details = $so_offers->get_cart_contents();
							$dp                 = (int) get_option( 'woocommerce_price_num_decimals' );
							WC()->cart->calculate_shipping();

							$cart_total = apply_filters( 'woocommerce_calculated_total', round( WC()->cart->cart_contents_total + WC()->cart->tax_total + WC()->cart->shipping_tax_total + WC()->cart->shipping_total + WC()->cart->fee_total, WC()->cart->dp ), WC()->cart );

							$cart_order_details['offer_rule_grand_total'] = $cart_total;

							$details      = array_merge( $user_details, $cart_order_details );
							$offer_rules  = $so_offers->get_all_offer_rules_meta( $offer_ids );
							$valid_offers = $so_offers->validate_offers( 'cart_page', $offer_rules, $details );
							if ( empty( $valid_offers ) ) {
								if ( isset( WC()->cart->cart_contents[ $key ]['smart_offers']['offer_id'] ) ) {
									$offer_id = WC()->cart->cart_contents[ $key ]['smart_offers']['offer_id'];
								}
								$offer_ids_to_unset[] = $offer_id;
								if ( isset( WC()->cart->cart_contents[ $key ]['smart_offers']['parent_offer_id'] ) ) {
									$offer_ids_to_unset[] = WC()->cart->cart_contents[ $key ]['smart_offers']['parent_offer_id'];
								}
								WC()->cart->set_quantity( $key, 0 );

								if ( $hook_name == 'woocommerce_before_checkout_process' ) {
									WC()->session->set( 'refresh_totals', true );
								}

								if ( ! array_key_exists( 'woocommerce_checkout_update_totals', $_POST ) ) {
									$_POST['woocommerce_checkout_update_totals'] = '';
								}

								if ( ! empty( $values['variation_id'] ) ) {
									$product = wc_get_product( $values['variation_id'] );
									if ( $product instanceof WC_Product ) {
										$removed_product_name = $product->get_formatted_name();
									}
								} else {
									$removed_product_name = get_the_title( $values['product_id'] );
								}
								wc_clear_notices();     // Workaround to prevent following notice from displaying multiple times.
								wc_add_notice( sprintf( __( 'Product %s is removed, because it is not valid for your cart contents.', 'smart-offers' ), $removed_product_name ), 'error' );
							} else {
								continue;
							}
						}
					} else {
						continue;
					}
				}

				if ( count( $offer_ids_to_unset ) > 0 ) {
					SO_Session_Handler::unset_offer_ids_from_session( $offer_ids_to_unset );
				}
			} else {
				return;
			}
		}

		/**
		 * Add meta information in the order and increase the count of offer
		 */
		function so_update_order_meta( $order_id, $posted ) {
			$so_order_meta = get_post_meta( $order_id, 'smart_offers_meta_data', true );
			foreach ( WC()->cart->get_cart() as $cart_key => $values ) {
				if ( isset( $values ['smart_offers'] ) ) {

					$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $values ['smart_offers'] ['offer_id'] );

					$so_order_count = get_post_meta( $offer_id, 'so_order_count', true );
					if ( empty( $so_order_count ) ) {
						$so_order_count                = array();
						$so_order_count['order_count'] = 0;
					}

					if ( is_array( $so_order_count ) && array_key_exists( 'order_count', $so_order_count ) ) {
						$count = ++$so_order_count['order_count'];
					} else {
						$temp                          = $so_order_count;
						$so_order_count                = array();
						$so_order_count['order_count'] = $temp;
						$count                         = ++$so_order_count['order_count'];
					}

					$so_order_count['order_count'] = $count;
					update_post_meta( $offer_id, 'so_order_count', $so_order_count );
					$product_id = ( ! empty( $values ['variation_id'] ) ) ? $values ['variation_id'] : $values ['product_id'];

					if ( empty( $so_order_meta ) && ! is_array( $so_order_meta ) ) {
						$so_order_meta = array();
					}

					if ( ! empty( $so_order_meta ) && ! is_array( $so_order_meta ) ) {
						$so_order_meta = array( $offer_id => $so_order_meta );
					}

					$so_order_meta [ $offer_id ] ['product_id'] = $product_id;
					// Multipled by quantity to consider multiple quantites of the offered product.
					$so_order_meta [ $offer_id ] ['offered_price'] = $values ['data']->get_price() * $values ['quantity'];

				}
			}

			if ( ! empty( $so_order_meta ) ) {
				update_post_meta( $order_id, 'smart_offers_meta_data', $so_order_meta );
			}

			$so_order_bumps_data = isset( $_POST['so-order-bumps-data'] ) ? $_POST['so-order-bumps-data'] : array();
			if ( ! empty( $so_order_bumps_data ) ) {
				$so_offer = new SO_Offer();
				foreach ( $so_order_bumps_data as $offer_id => $offer_checked ) {
					$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_id );
					if ( 'yes' === $offer_checked ) {
						$so_offer->update_accept_skip_count( $offer_id, 'accepted' );
					} else {
						$so_offer->update_accept_skip_count( $offer_id, 'skipped' );
						// Update so skipped offer ids in session since skipped is triggered only after order is placed.
						SO_Session_Handler::so_set_session_variables( 'sa_smart_offers_skipped_offer_ids', $offer_id );
					}
				}
			}
		}

		/**
		 * Fetch all skipped offers of cart and account page by user
		 */
		function get_skipped_offers( $current_offer_id ) {

			global $current_user;

			$user_skipped_offers = get_user_meta( $current_user->ID, 'customer_skipped_offers', true );

			if ( ! empty( $user_skipped_offers ) ) {
				$customer_skipped_offers = maybe_unserialize( $user_skipped_offers );
			}
			$customer_skipped_offers [] = $current_offer_id;
			$customer_skipped_offers    = array_unique( $customer_skipped_offers );

			return $customer_skipped_offers;
		}

		/**
		 * Add offered price in cart.
		 */
		function add_offered_price( $cart_object ) {

			global $sa_smart_offers;

			if ( sizeof( $cart_object->cart_contents ) > 0 ) {

				foreach ( $cart_object->cart_contents as $key => $value ) {
					if ( isset( $value ['smart_offers'] ['accept_offer'] ) ) {

						$product_id = ( isset( $value['variation_id'] ) && $value['variation_id'] != '' ) ? $value['variation_id'] : $value['product_id'];

						$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $value ['smart_offers'] ['offer_id'] );

						$so_offer = new SO_Offer();
						$price    = $so_offer->get_offer_price(
							array(
								'offer_id' => $offer_id,
								'prod_id'  => $product_id,
							)
						);

						$value ['data']->set_price( $price );
						$value ['data']->set_sale_price( $price );
						$value ['data']->set_regular_price( $price );

					}
				}
			}

		}

		/**
		 * Add offered price in checkout.
		 */
		function add_offered_price_during_checkout() {

			global $sa_smart_offers;

			$cart = WC()->cart->cart_contents;

			if ( sizeof( $cart ) > 0 ) {
				foreach ( $cart as $key => $value ) {

					if ( isset( $value ['smart_offers']['accept_offer'] ) ) {

						$product_id = ( isset( $value['variation_id'] ) && $value['variation_id'] != '' ) ? $value['variation_id'] : $value['product_id'];

						$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $value ['smart_offers'] ['offer_id'] );

						$so_offer = new SO_Offer();
						$price    = $so_offer->get_offer_price(
							array(
								'offer_id' => $offer_id,
								'prod_id'  => $product_id,
							)
						);

						$value ['data']->set_price( $price );
						$value ['data']->set_sale_price( $price );
						$value ['data']->set_regular_price( $price );

					}
				}
			}
		}

		/**
		 * Set quantity for the offered product in cart.
		 */
		function offered_prod_cart_item_quantity( $quantity, $cart_item_key ) {
			if ( isset( WC()->cart->cart_contents [ $cart_item_key ] ['smart_offers'] ) ) {
				return WC()->cart->cart_contents [ $cart_item_key ] ['quantity'];
			}

			return $quantity;
		}

		/**
		 * Function to show offer just after Place Order button is clicked on checkout page, if a valid offer is available
		 */
		function smart_offers_post_checkout() {
			$so_offer  = new SO_Offer();
			$so_offers = new SO_Offers();

			$page                    = 'post_checkout_page';
			list($where, $where_url) = $so_offers->get_page_details();

			$so_get_offers = $so_offers->get_offers( $offer_ids = null );
			$so_offers_id  = $so_offers->get_valid_offer_ids( $so_get_offers );            // $offer_id = $so_get_offers['offer_data'][0]['post_id'];

			if ( ! empty( $so_offers_id ) ) {
				foreach ( $so_offers_id as $key => $value ) {
					$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $key );

					$offer_rule_page_options = get_post_meta( $offer_id, 'offer_rule_page_options', true );
					$offer_rule_pages        = explode( ',', $offer_rule_page_options );

					if ( ! in_array( $page, $offer_rule_pages ) ) {
						continue;
					} else {
						$plugin_data   = SA_Smart_Offers::get_smart_offers_plugin_data();
						$version       = $plugin_data['Version'];
						$show_offer_as = get_post_meta( $offer_id, 'so_show_offer_as', true );
						wp_enqueue_style( 'so_frontend_css' );
						if ( $show_offer_as == 'offer_as_popup' ) {

							if ( ! wp_script_is( 'jquery' ) ) {
								wp_enqueue_script( 'jquery' );
								wp_enqueue_style( 'jquery' );
							}

							if ( ! wp_script_is( 'so_magnific_popup_js' ) ) {
								wp_enqueue_script( 'so_magnific_popup_js', trailingslashit( plugins_url() ) . dirname( plugin_basename( SO_PLUGIN_FILE ) ) . '/assets/js/jquery.magnific-popup.js', array(), $version );
							}

							if ( ! wp_style_is( 'so_magnific_popup_css' ) ) {
								wp_enqueue_style( 'so_magnific_popup_css', trailingslashit( plugins_url() ) . dirname( plugin_basename( SO_PLUGIN_FILE ) ) . '/assets/css/magnific-popup.css', array(), $version );
							}
						}
						?>
						<div class="smart-offers-post-action" id="smart-offers-post-action">
							<?php
								$sa_so_offer_content     = $so_offer->return_post_content( $offer_id, $page, $where_url );
								$processed_offer_content = apply_filters( 'the_content', $sa_so_offer_content );
								echo $processed_offer_content;
							?>
						</div>
						<?php

							$js = "jQuery(document).ready(function() {
										var post_element = jQuery( 'div#smart-offers-post-action' );
										if( ( post_element.length ) > 0 ) {
											if( ( post_element.find( 'div.so-offer-content' ).length ) > 0 ){
												post_element.find( 'div.so-offer-content' ).hide();
											}
										}
									});

								jQuery('body').on( 'click', '#place_order.button.alt', function( e ) {
									var post_element = jQuery( 'div#smart-offers-post-action' );
									if( ( post_element.length ) > 0 ) {
										e.preventDefault();
										var checkout_form_data = jQuery('form.checkout').serialize();
										jQuery.ajax({
											url: '" . admin_url( 'admin-ajax.php' ) . "',
											type: 'POST',
											dataType: 'json',
											data: {
												action: 'parse_checkout_form_data',
												security: '" . wp_create_nonce( 'post_checkout_offers' ) . "',
												form_data: checkout_form_data
											},
											success: function( response ) {
												post_element.show();
												if ( response.success != '' && response.success != undefined && response.success == 'no' ) {
													console.log( '" . __( 'Unable to save checkout form data.', 'smart-offers' ) . "' );
												}
											}
										});
										if( ( post_element.find( 'div.so-offer-content.so-inline' ).length ) > 0 ) {
											jQuery( 'form.checkout' ).hide();
											post_element.find( 'div.so-offer-content' ).show();
										} else if( ( post_element.find( 'div.so-offer-content.so-popup' ).length ) > 0 ) {
											post_element.find( 'div.so-offer-content' ).addClass( 'white-popup' );
											jQuery.magnificPopup.open({
												items: {
														  src: jQuery( 'div#smart-offers-post-action div.so-offer-content' )
														},
												type: 'inline',
												modal: true,
												tError: '" . __( 'The content could not be loaded.', 'smart-offers' ) . "'
											});
											post_element.find( 'div.so-offer-content.so-popup' ).show();
										}
									}
								});
							";

						wc_enqueue_js( $js );

						// After accepting an offer, 'offer_shown' was counted additionally, hence skipping it
						if ( ! ( ! empty( $_REQUEST['so_action'] ) && $_REQUEST['so_action'] == 'accept' ) ) {
							$so_offer->update_accept_skip_count( $offer_id, 'offer_shown' );
						}
					}
				}
			}
		}

		/**
		 * Function to save checkout form data used to process after checkout
		 */
		function parse_checkout_form_data() {
			check_ajax_referer( 'post_checkout_offers', 'security' );

			$checkout_form_data = ( ! empty( $_POST['form_data'] ) ) ? $_POST['form_data'] : '';

			$response = array();

			if ( empty( $checkout_form_data ) ) {
				$response['success'] = 'no';
			} else {
				parse_str( $checkout_form_data, $simplified_form_data );
				SO_Session_Handler::so_set_session_variables( 'so_checkout_form_data', $simplified_form_data );
				$response['success'] = 'yes';
			}

			echo json_encode( $response );
			die();
		}

		/**
		 * Add offered price in cart.
		 */
		function get_offered_cart_item_from_session( $cart_item, $values, $key = null ) {
			global $sa_smart_offers;

			if ( isset( $values ['smart_offers'] ) ) {
				$so_offer                   = new SO_Offer();
				$cart_item ['smart_offers'] = $values ['smart_offers'];

				$product_id = ( isset( $cart_item['variation_id'] ) && $cart_item['variation_id'] != '' ) ? $cart_item['variation_id'] : $cart_item['product_id'];

				$offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $values ['smart_offers'] ['offer_id'] );

				$price = $so_offer->get_offer_price(
					array(
						'offer_id' => $offer_id,
						'prod_id'  => $product_id,
					)
				);

				$cart_item ['data']->set_price( $price );
				$cart_item ['data']->set_sale_price( $price );
				$cart_item ['data']->set_regular_price( $price );
			}

			return $cart_item;
		}

		/**
		 * Action to perform on accept/skip offer
		 */
		function so_process_offer_action() {
			if ( wp_doing_ajax() ) {
				check_ajax_referer( 'so_actions_security', 'so_actions_security' );
			}

			global $sa_smart_offers, $current_user;

			$so_offer  = new SO_Offer();
			$so_offers = new SO_Offers();

			if ( isset( $_GET['so_action'] ) && ( $_GET['so_action'] == 'accept' || $_GET ['so_action'] == 'skip' ) ) {

				$current_offer_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $_GET['so_offer_id'] );

				$source = ( ! empty( $_GET['source'] ) ) ? $_GET['source'] : null;

				list($where, $where_url) = $so_offers->get_page_details();
				$page                    = $where . '_page';

				list($accepted_session_variable, $accepted_ids_in_session) = $so_offers->get_accepted_offer_ids_from_session();
				list($skipped_session_variable, $skipped_ids_in_session)   = $so_offers->get_skipped_offer_ids_from_session();

				$skip_offer_id_variable                                 = ( $where == 'any' ) ? str_replace( array( '/', '-', '&', '=', ':' ), '', $where_url ) . '_skip_offer_id' : $where . '_skip_offer_id';
				list($offer_id_on_skipping, $skipped_offer_id_variable) = $so_offers->get_offer_id_on_skipping( $skip_offer_id_variable );

				$parent_offer_id_variable         = ( $where == 'any' ) ? str_replace( array( '/', '-', '&', '=', ':' ), '', $where_url ) . '_parent_offer_id' : $where . '_parent_offer_id';
				$check_parent_offer_id_set_or_not = SO_Session_Handler::check_session_set_or_not( $parent_offer_id_variable );

				if ( ! $check_parent_offer_id_set_or_not ) {
					SO_Session_Handler::so_set_session_variables( $parent_offer_id_variable, $current_offer_id );
				}
				if ( $_GET['so_action'] == 'accept' ) {

					$variation_data  = ( isset( $_POST['variation_id'] ) || isset( $_POST['quantity'] ) ) ? $_POST : array();
					$parent_offer_id = '';

					if ( $offer_id_on_skipping != '' ) {
						$check_parent_offer_id = SO_Session_Handler::check_session_set_or_not( $parent_offer_id_variable );
						$parent_offer_id       = ( $check_parent_offer_id ) ? SO_Session_Handler::so_get_session_value( $parent_offer_id_variable ) : '';
					}

					SO_Session_Handler::so_delete_session( $parent_offer_id_variable );
					SO_Session_Handler::so_delete_session( $skip_offer_id_variable );

					SO_Session_Handler::so_set_session_variables( 'sa_smart_offers_accepted_offer_ids', $current_offer_id );

					// Update stats
					$so_offer->update_accept_skip_count( $current_offer_id, 'accepted' );

					// Validate offer before add to cart.
					$offer_ids = array( $current_offer_id );
					$is_valid  = $this->is_offer_valid( $page, $offer_ids );

					if ( ! empty( $is_valid ) && is_array( $is_valid ) ) {
						// Adds to cart
						$so_offer->action_on_accept_offer( $current_offer_id, $page, $parent_offer_id, $variation_data );
					} else {
						// Add notice data
						$data = array(
							'messages' => __( 'This offer is not valid for you.', 'smart-offers' ),
						);
						$so_offer->process_response( '', 'failure', $data );
					}
				} elseif ( $_GET['so_action'] == 'skip' ) {

					$so_offer->update_accept_skip_count( $current_offer_id, 'skipped' );

					// Update if this offer needs to be skipped permanently for this user
					$skip_permanently = get_post_meta( $current_offer_id, 'sa_smart_offer_if_denied_skip_permanently', true );

					if ( ! empty( $skip_permanently ) && $skip_permanently == true && $current_user->ID != 0 ) {
						$customer_skipped_offers = $this->get_skipped_offers( $current_offer_id );
						$customer_skipped_offers = array_unique( $customer_skipped_offers );
						update_user_meta( $current_user->ID, 'customer_skipped_offers', $customer_skipped_offers );
					}

					// To store skipped offers in session even if they are updated in DB
					SO_Session_Handler::so_set_session_variables( 'sa_smart_offers_skipped_offer_ids', $current_offer_id );
					SO_Session_Handler::so_delete_session( $skip_offer_id_variable );

					$redirecting_option = get_post_meta( $current_offer_id, 'sa_smart_offer_if_denied', true );
					$redirect_to        = get_post_meta( $current_offer_id, 'url', true );

					if ( strpos( $where_url, 'so_action=skip' ) ) {
						$where_url = esc_url_raw( remove_query_arg( array( 'so_action', 'so_offer_id', 'source' ), $where_url ) );
					}

					ob_clean();

					if ( ! empty( $redirecting_option ) ) {
						if ( $page == 'checkout_page' && $source == 'so_post_checkout' ) {
							if ( $redirecting_option == 'order_page' || $redirecting_option == 'url' || $redirecting_option == 'particular_page' ) {
								if ( $redirecting_option == 'url' ) {
									if ( ! preg_match( '~^(?:ht)tps?://~i', $redirect_to ) ) {
										$return_url = ( @$_SERVER ['HTTPS'] == 'on' ) ? 'https://' : 'http://';
										$return_url = 'http://' . $redirect_to;
									} else {
										$return_url = $redirect_to;
									}
									SO_Session_Handler::so_set_session_variables( 'sa_offer_on_after_checkout', $return_url );
								} elseif ( $redirecting_option == 'particular_page' ) {
									$redirection_url = get_permalink( $redirect_to );
									SO_Session_Handler::so_set_session_variables( 'sa_offer_on_after_checkout', $redirection_url );
								}

								$form_values = SO_Session_Handler::check_session_set_or_not( 'so_checkout_form_data' );
								if ( $form_values ) {
									$sa_so_form_checkout = SO_Session_Handler::so_get_session_value( 'so_checkout_form_data' );
								} else {
									$sa_so_form_checkout = null;
								}

								if ( ! empty( $sa_so_form_checkout ) ) {
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

							} else {
								if ( $redirecting_option == 'offer_page' ) {
									$so_offer->force_show_smart_offers( $redirect_to );
								} elseif ( $redirecting_option == 'buy_now_page' ) {
									$this->redirecting_actions_on_skip( $redirecting_option, $redirect_to, $where_url );
								}
							}
						} else {
							$this->redirecting_actions_on_skip( $redirecting_option, $redirect_to, $where_url );
						}
					}
				}
			}
		}

		function so_do_redirect_on_after_checkout_page( $result, $order_id ) {
			$check_session_set = SO_Session_Handler::check_session_set_or_not( 'sa_offer_on_after_checkout' );

			if ( $check_session_set ) {
				$current_filter = current_filter();
				if ( $current_filter == 'woocommerce_payment_successful_result' ) {
					$sa_offer_after_checkout = SO_Session_Handler::so_get_session_value( 'sa_offer_on_after_checkout' );
					$result['redirect']      = $sa_offer_after_checkout;
				} elseif ( $current_filter == 'woocommerce_checkout_no_payment_needed_redirect' ) {
					$sa_offer_after_checkout = SO_Session_Handler::so_get_session_value( 'sa_offer_on_after_checkout' );
					$result                  = $sa_offer_after_checkout;
				}
			}

			return $result;
		}

		/*
		 * Function to make skip actions work on All pages (except After Checkout page)
		 */
		function redirecting_actions_on_skip( $redirecting_option, $redirect_to, $where_url ) {
			$response = array();
			$so_offer = new SO_Offer();

			if ( ! empty( $redirecting_option ) ) {
				if ( $redirecting_option == 'order_page' ) {
					$is_cart_empty = isset( WC()->cart ) && ! WC()->cart->is_empty() ? false : true;
					// Check if cart is not empty.
					if ( false === $is_cart_empty ) {
						$cart_url     = wc_get_cart_url();
						$checkout_url = wc_get_checkout_url();
						if ( ! in_array( $where_url, array( $cart_url, $checkout_url ), true ) ) {
							// Change $where_url(redirect url) to checkout url if cart is not empty and user is not on cart/checkout page.
							$where_url = $checkout_url;
						}
					}
					$so_offer->process_response( $where_url, 'success' );
				} elseif ( $redirecting_option == 'buy_now_page' ) {
					if ( class_exists( 'WC_Buy_Now' ) ) {
						$buy_now             = new WC_Buy_Now();
						$bn_plugin_meta_data = get_plugin_data( WP_PLUGIN_DIR . '/woocommerce-buy-now/woocommerce-buy-now.php' );
						$bn_current_version  = $bn_plugin_meta_data['Version'];
						if ( $bn_current_version >= '1.9' ) {
							$cart_contents = WC()->cart->get_cart();
							if ( ! empty( $cart_contents ) ) {
								$buy_now->checkout_redirect();
							} else {
								$so_offer->process_response( $where_url, 'success' );
							}
						} else {
							$so_offer->process_response( $where_url, 'success' );
						}
					} else {
						$so_offer->process_response( $where_url, 'success' );
					}
				} elseif ( $redirect_to != '' ) {
					if ( $redirecting_option == 'offer_page' ) {
						$so_offer->force_show_smart_offers( $redirect_to );
					} elseif ( $redirecting_option == 'url' ) {

						if ( ! preg_match( '~^(?:ht)tps?://~i', $redirect_to ) ) {
							$return_url = ( @$_SERVER ['HTTPS'] == 'on' ) ? 'https://' : 'http://';
							$return_url = 'http://' . $redirect_to;
						} else {
							$return_url = $redirect_to;
						}

						$so_offer->process_response( $return_url, 'success' );
					} elseif ( $redirecting_option == 'particular_page' ) {
						$return_url = get_permalink( $redirect_to );
						$so_offer->process_response( $return_url, 'success' );
					}
				}
				exit;
			}
		}

		/*
		 * New function: to prevent redirect loop for BN - guest user checkout
		 */
		function so_remove_extra_params( $url ) {
			if ( ( strpos( $url, 'so_action=skip' ) ) || ( strpos( $url, 'so_action=accept' ) ) ) {
				$url = esc_url_raw( remove_query_arg( array( 'so_action', 'so_offer_id', 'source' ), $url ) );
			}
			return $url;
		}

		/*
		 * Validate offer before and after add to cart.
		 */
		function is_offer_valid( $page, $offer_ids ) {
			global $sa_smart_offers, $wp;

			$so_offers = new SO_Offers();

			if ( ! empty( $wp->query_vars['order-received'] ) ) {
				$order_id = absint( $wp->query_vars['order-received'] );
			} elseif ( 'thankyou_page' === $page && true === wp_doing_ajax() ) {
				$order_key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';
				if ( ! empty( $order_key ) && function_exists( 'wc_get_order_id_by_order_key' ) ) {
					$order_id = wc_get_order_id_by_order_key( $order_key );
				}
			}
			$order = ( ! empty( $order_id ) ) ? wc_get_order( $order_id ) : null;

			$order_containing_ids = $current_order_details = array();
			if ( ! empty( $order ) ) {
				list($order_containing_ids, $current_order_details, $orders) = $so_offers->get_current_order_details('thankyou');
			}

			// Get user's details
			$user_details = $so_offers->get_user_details( $page, $order );

			$dp = (int) get_option( 'woocommerce_price_num_decimals' );
			WC()->cart->calculate_shipping();

			$cart_total = apply_filters( 'woocommerce_calculated_total', round( WC()->cart->cart_contents_total + WC()->cart->tax_total + WC()->cart->shipping_tax_total + WC()->cart->shipping_total + WC()->cart->fee_total, WC()->cart->dp ), WC()->cart );

			// Get Cart/Order details
			$cart_details                           = $so_offers->get_cart_contents();
			$cart_details['offer_rule_grand_total'] = $cart_total;
			// If on order complete, merge with just placed order.
			if ( ! empty( $current_order_details ) ) {
				$cart_order_details = array_merge( $cart_details, $current_order_details );
			} else {
				$cart_order_details = $cart_details;
			}

			$details = array_merge( $user_details, $cart_order_details );

			$offer_to_validate = $so_offers->get_all_offer_rules_meta( $offer_ids );
			$valid_offer_ids   = $so_offers->validate_offers( $page, $offer_to_validate, $details );
			$key               = ( ! empty( $offer_ids[0] ) ) ? $offer_ids[0] : 0;

			if ( ! empty( $valid_offer_ids ) && array_key_exists( $key, $valid_offer_ids ) ) {
				return $valid_offer_ids;
			} else {
				return false;
			}
		}

		/**
		 * Empty SO related session data on logout
		 */
		function so_clear_session() {
			SO_Session_Handler::so_delete_session( 'sa_smart_offers_skipped_offer_ids' );
			SO_Session_Handler::so_delete_session( 'sa_smart_offers_accepted_offer_ids' );

			$pages = array( 'cart', 'checkout', 'thankyou', 'myaccount', 'home', 'any' );

			foreach ( $pages as $page ) {
				SO_Session_Handler::so_delete_session( $page . '_skip_offer_id' );
				SO_Session_Handler::so_delete_session( $page . '_parent_offer_id' );
			}

			$global_wc = ( function_exists( 'WC' ) ) ? WC() : null;
			if ( is_object( $global_wc ) ) {
				$wc_session = WC()->session;
			}

			$data = ( ! empty( $wc_session ) ) ? get_option( '_wc_session_' . WC()->session->get_customer_id(), array() ) : null;
			if ( ! empty( $data ) ) {
				foreach ( $data as $key_name => $value ) {
					if ( strpos( $key_name, '_skip_offer_id' ) !== false || strpos( $key_name, '_parent_offer_id' ) !== false ) {
						SO_Session_Handler::so_delete_session( $key_name );
					}
				}
			}
		}

		/**
		 * Change the order count in case of order status change
		 */
		function change_paid_through_count( $order_id, $old_status, $new_status ) {
			// In WC 2.2 also, woocommerce_order_status_changed pass previous statuses, not new one, therefore no need of change
			$order_statuses = array( 'cancelled', 'refunded', 'failed' );

			if ( in_array( $new_status, $order_statuses, true ) && in_array( $old_status, $order_statuses, true ) ) {
				return;
			}

			$is_change_paid_through_count = false;

			if ( in_array( $new_status, $order_statuses, true ) ) {
				$is_change_paid_through_count = true;
			}

			if ( $is_change_paid_through_count ) {
				$so_order_meta = get_post_meta( $order_id, 'smart_offers_meta_data', true );
				if ( ! empty( $so_order_meta ) && is_array( $so_order_meta ) ) {
					foreach ( $so_order_meta as $offer_id => $offer_data ) {
						$offer_id    = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $offer_id );
						$order_count = get_post_meta( $offer_id, 'so_order_count', true );
						if ( $order_count ) {
							$count                      = --$order_count['order_count'];
							$order_count['order_count'] = $count;
							update_post_meta( $offer_id, 'so_order_count', $order_count );
						}
					}
				}
			}

		}

	}

}

return new SO_Init();
