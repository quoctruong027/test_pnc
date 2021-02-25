<?php

/**
 * Create,show,delete,edit and manages the process related to offers in the plugin.
 * Class WFOCU_Offers
 */
class WFOCU_Offers {

	const INVALIDATION_PRODUCT_IN_ORDER = 1;
	const INVALIDATION_NOT_PURCHASABLE = 2;
	const INVALIDATION_PAST_PURCHASED = 3;
	private static $ins = null;
	public $is_custom_page = false;

	public function __construct() {
		add_filter( 'wfocu_offer_product_data', array( $this, 'offer_product_setup_stock_data' ), 9, 4 );
		add_filter( 'wfocu_view_body_classes', array( $this, 'append_offer_unique_class' ), 10, 1 );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function validate( $post ) {

		$funnel_check = WFOCU_Core()->funnels->validate();

		if ( false === $funnel_check ) {
			WFOCU_Core()->log->log( 'Validation Failed: Funnel Check Failed' );

			return false;
		}

		$get_current_offer = WFOCU_Core()->data->get_current_offer();

		if ( false === $get_current_offer ) {
			WFOCU_Core()->log->log( 'Validation Failed: Unable to find the current offer' );

			return false;
		}

		/**
		 * if we do not have any offer to show then fail the validation.
		 */
		if ( false === $post ) {
			WFOCU_Core()->log->log( 'Validation Failed: Unable to find the current offer' );

			return false;
		}

		/**
		 * IF current offer is not the one we are expecting in the funnel
		 */
		if ( $get_current_offer !== $post ) {
			WFOCU_Core()->log->log( 'Validation Failed: Current offer set in the session doesn\'t match with the one opening now.' );

			return false;
		}

		return true;
	}

	public function get_offers( $funnel_id ) {
		if ( $funnel_id ) {
			return get_post_meta( $funnel_id, '_funnel_upsell_downsell', true );
		}

		return false;
	}

	/**
	 * Return the first offer in the list
	 * @return int|null|string
	 */
	public function get_the_first_offer() {
		$get_offers = WFOCU_Core()->data->get( 'funnel' );

		return $this->get_the_offer( 'yes', null, $get_offers );

	}

	public function get_the_offer( $type = 'yes', $offer = null, $get_offers ) {

		if ( null === $offer ) {
			reset( $get_offers );
			$offer = key( $get_offers );

			return absint( $offer );
		}

		$get_offer_type_key = $this->get_meta_key_for_offer_type( $type );
		if ( $get_offers && is_array( $get_offers ) && count( $get_offers ) > 0 && isset( $get_offers[ $offer ] ) && isset( $get_offers[ $offer ][ $get_offer_type_key ] ) ) {
			return absint( $get_offers[ $offer ][ $get_offer_type_key ] );
		}

		return 0;
	}

	public function get_meta_key_for_offer_type( $type = 'yes' ) {
		$offer_type = array(
			'y' => 'yes',
			'n' => 'no',
		);

		return array_search( $type, $offer_type, true );

	}

	public function get_the_next_offer( $type = 'yes' ) {
		$get_offers                      = WFOCU_Core()->data->get( 'funnel' );
		$get_current_offer               = WFOCU_Core()->data->get( 'current_offer' );
		$get_the_previous_offer_response = WFOCU_Core()->data->get( '_offer_result', null );

		if ( false === is_null( $get_the_previous_offer_response ) ) {
			$get_offer_data = WFOCU_Core()->data->get( '_current_offer', '' );
			if ( true === $get_the_previous_offer_response ) {

				if ( '' !== $get_offer_data && true === $get_offer_data->settings->terminate_if_accepted ) {
					return 0;
				}
			} else {
				if ( '' !== $get_offer_data && true === $get_offer_data->settings->terminate_if_declined ) {
					return 0;
				}
			}
		}

		return $this->get_the_offer( $type, $get_current_offer, $get_offers );
	}

	public function get_offer_index( $offer_id, $funnel_id = 0 ) {
		/** return in case no funnel id: customizer preview case */
		if ( 0 === $funnel_id ) {
			return - 1;
		}
		$get_funnel_steps = WFOCU_Core()->funnels->get_funnel_steps( $funnel_id );
		$index            = 0;
		if ( is_array( $get_funnel_steps ) && count( $get_funnel_steps ) > 0 ) {
			$key = 0;
			foreach ( $get_funnel_steps as $step ) {

				if ( $step['id'] === $offer_id || absint( $step['id'] ) === $offer_id ) {
					$index = $key;
					break;
				}

				$key ++;
			}
		}

		return $index;

	}


	public function get_offer_id_by_index( $index, $funnel_id = 0 ) {
		/** return in case no funnel id: customizer preview case */
		if ( 0 === $funnel_id ) {
			return - 1;
		}
		$get_funnel_steps = WFOCU_Core()->funnels->get_funnel_steps( $funnel_id );
		$id               = 0;
		if ( is_array( $get_funnel_steps ) && count( $get_funnel_steps ) > 0 ) {
			$key = 0;
			foreach ( $get_funnel_steps as $step ) {

				if ( $key === $index ) {
					$id = $step['id'];
					break;
				}

				$key ++;
			}
		}

		return $id;

	}

	public function get_offer_attributes( $offer_id, $get = 'type' ) {
		/** return in case no funnel id: customizer preview case */
		if ( false === WFOCU_Core()->data->get_funnel_id() ) {
			return;
		}
		$get_funnel_steps = WFOCU_Core()->funnels->get_funnel_steps( WFOCU_Core()->data->get_funnel_id() );

		$upsells   = 1;
		$downsells = 1;
		if ( is_array( $get_funnel_steps ) && count( $get_funnel_steps ) > 0 ) {
			foreach ( $get_funnel_steps as $step ) {

				if ( $step['id'] === $offer_id || absint( $step['id'] ) === $offer_id ) {
					$type = $step['type'];
					switch ( $get ) {
						case 'type':
							return $type;
							break;
						case 'index':
							return ( 'upsell' === $type ) ? $upsells : $downsells;
						case 'state':
							return $step['state'];
					}

					break;
				}

				if ( 'upsell' === $step['type'] ) {
					$upsells ++;
				} else {
					$downsells ++;
				}
			}
		}

		return null;

	}

	public function get_offer_meta( $offer_id ) {
		return get_post_meta( $offer_id, '_wfocu_setting', true );
	}

	public function prepare_shipping_package( $offer_meta, $posted_data = array() ) {

		$complete_package = array();

		$offer_products          = $offer_meta->products;
		$offer_products_settings = $offer_meta->fields;
		$chosen_hashes           = array();

		if ( is_array( $posted_data ) && count( $posted_data ) > 0 ) {
			$chosen_hashes = wp_list_pluck( $posted_data, 'hash' );
		}
		$i = 0;
		foreach ( $chosen_hashes as $key => $hash ) {

			if ( isset( $posted_data[ $key ]['data'] ) ) {
				$complete_package[ $i ]                   = array();
				$complete_package[ $i ]['product']        = ( false !== $posted_data[ $key ]['data']['variation'] ) ? $posted_data[ $key ]['data']['variation'] : $offer_products->{$hash};
				$complete_package[ $i ]['qty']            = ( isset( $offer_products_settings->{$hash} ) ) ? $offer_products_settings->{$hash}->quantity : 0;
				$complete_package[ $i ]['price']          = $this->get_product_price( $complete_package[ $i ]['product'], $offer_products_settings->{$hash}, false, $offer_products_settings );
				$complete_package[ $i ]['price_with_tax'] = $this->get_product_price( $complete_package[ $i ]['product'], $offer_products_settings->{$hash}, true, $offer_products_settings );
				$complete_package[ $i ]['_product']       = wc_get_product( $complete_package[ $i ]['product'] );
				$complete_package[ $i ]['meta']           = $posted_data[ $key ]['data']['attributes'];

			} else {
				$complete_package[ $i ]                   = array();
				$complete_package[ $i ]['product']        = ( isset( $offer_products->{$hash} ) ) ? (int) $offer_products->{$hash} : '37';
				$complete_package[ $i ]['qty']            = ( isset( $offer_products_settings->{$hash} ) ) ? $offer_products_settings->{$hash}->quantity : 0;
				$complete_package[ $i ]['price']          = $this->get_product_price( $complete_package[ $i ]['product'], $offer_products_settings->{$hash}, false, $offer_products_settings );
				$complete_package[ $i ]['price_with_tax'] = $this->get_product_price( $complete_package[ $i ]['product'], $offer_products_settings->{$hash}, true, $offer_products_settings );
				$complete_package[ $i ]['_product']       = wc_get_product( $complete_package[ $i ]['product'] );
				$complete_package[ $i ]['meta']           = array();
			}
			$i ++;
		}

		return $complete_package;
	}

	/**
	 * @param $product
	 * @param $options
	 * @param bool $incl_tax
	 * @param $offer_settings
	 *
	 * @return float
	 */
	public function get_product_price( $product, $options, $incl_tax = false, $offer_settings = array() ) {

		if ( ! $product instanceof WC_Product ) {
			$product = wc_get_product( $product );
		}

		$regular_price          = $product->get_regular_price() * $options->quantity;
		$get_product_raw_price  = $price = apply_filters( 'wfocu_product_raw_price', $regular_price, $product, $options );
		$do_not_apply_discounts = apply_filters( 'wfocu_do_not_apply_discounts', false, $product, $options, $offer_settings );
		if ( is_object( $options ) && isset( $options->discount_type ) && false === $do_not_apply_discounts ) {
			if ( in_array( $options->discount_type, [ 'percentage_on_sale', 'fixed_on_sale' ], true ) ) {
				$sale_price            = $product->get_price() * $options->quantity;
				$get_product_raw_price = apply_filters( 'wfocu_product_raw_sale_price', $sale_price, $product, $options, $get_product_raw_price );
			}

			$price = WFOCU_Common::apply_discount( $get_product_raw_price, $options, $product );
		}

		$price = ( true === $incl_tax ) ? wc_get_price_including_tax( $product, array( 'price' => $price ) ) : wc_get_price_excluding_tax( $product, array( 'price' => $price ) );

		return round( $price, wc_get_price_decimals() );

	}

	public function parse_posted_data() {
		$posted_data = array();
		$data        = $_POST;   // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( false === in_array( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ), apply_filters( 'wfocu_allow_ajax_actions_for_charge_setup', array(
				WFOCU_AJAX_Controller::CHARGE_ACTION,
				WFOCU_AJAX_Controller::SHIPPING_CALCULATION_ACTION,
			) ), true ) ) {

			return $posted_data;
		}

		if ( isset( $data['items'] ) && is_array( $data['items'] ) && count( $data['items'] ) > 0 ) {

			foreach ( $data['items'] as $key => $hash ) {
				$posted_data[ $key ] = array(
					'hash' => $hash,
				);

				if ( isset( $data['itemsData'] ) && isset( $data['itemsData'][ $key ] ) ) {

					$get_attribute_values = WFOCU_Core()->data->get( 'attribute_variation_stock_' . $hash, array(), 'variations' );
					$variation_attributes = array();
					wp_parse_str( implode( '&', $data['itemsData'][ $key ] ), $variation_attributes );
					$exclude = array( '_wfocu_variation' );

					$filtered = array_filter( $variation_attributes, function ( $key ) use ( $exclude ) {
						return ! in_array( $key, $exclude, true );
					}, ARRAY_FILTER_USE_KEY );

					$result = array();

					if ( ! empty( $get_attribute_values ) ) {
						array_walk( $filtered, function ( &$value, $key ) use ( &$result, $get_attribute_values ) {

							if ( isset( $get_attribute_values[ $key ] ) ) {
								$result[ $get_attribute_values[ $key ] ] = $value;
							} else {
								$result[ $key ] = $value;
							}

						} );
					} else {
						$result = $filtered;
					}

					$posted_data[ $key ]['data'] = array(
						'variation'  => ( isset( $variation_attributes['_wfocu_variation'] ) ? $variation_attributes['_wfocu_variation'] : false ),
						'attributes' => $result,
					);
				}
			}
		}

		return $posted_data;

	}

	public function get_offer_from_post( $post ) {

		if ( ! $post instanceof WP_Post ) {

			$post = get_post( $post );
		}

		if ( ! $post instanceof WP_Post ) {

			return false;
		}

		//if single offer page
		if ( WFOCU_Common::get_offer_post_type_slug() === $post->post_type ) {
			return $post->ID;
		}

		$get_offer  = WFOCU_Core()->data->get_current_offer();
		$offer_data = WFOCU_Core()->data->get( '_current_offer_data' );

		if ( $get_offer && is_object( $offer_data ) && 'custom-page' === $offer_data->template ) {
			$get_custom_page = get_post_meta( $get_offer, '_wfocu_custom_page', true );

			if ( absint( $get_custom_page ) === absint( $post->ID ) ) {
				$this->is_custom_page = true;

				return $get_offer;
			}
		}

		return false;
	}

	/**
	 * Here we find out whether to show tax info during side cart totals.
	 * The decision for it came from the settings for the woocommerce.
	 * So if woocommerce says "show cart items including prices" that means no separate row needs to be make on cart table
	 * @return bool
	 */
	public function show_tax_info_in_confirmation() {

		return wc_tax_enabled() && ( ! WFOCU_WC_Compatibility::display_prices_including_tax() );
	}

	public function offer_product_setup_stock_data( $product_details, $output, $offer_data, $is_front ) {
		if ( true === $is_front ) {

			if ( in_array( $product_details->data->get_type(), WFOCU_Common::get_variable_league_product_types(), true ) && true === $product_details->data->is_purchasable() ) {
				$product_details->is_purchasable = true;
			} else {
				$product_details->is_purchasable = $product_details->data->is_purchasable();
			}
			if ( in_array( $product_details->data->get_type(), $this->product_compatible_for_stock_check(), true ) ) {

				$product_details->is_in_stock        = $product_details->data->is_in_stock();
				$product_details->max_qty            = $product_details->data->get_max_purchase_quantity();
				$product_details->backorders_allowed = $product_details->data->backorders_allowed();

			}
		}

		return $product_details;
	}

	public function product_compatible_for_stock_check() {
		return apply_filters( 'wfocu_products_compatible_for_stock_check', array( 'simple', 'variation', 'subscription', 'subscription_variation' ) );
	}

	/**
	 * This method is to validate the product in the current offer against purchasable and stock standards
	 * Based on these results we hide/show Or redirect the user
	 *
	 * @param $offer_build
	 *
	 * @return bool
	 */
	public function validate_product_offers( $offer_build ) {

		if ( new stdClass() === $offer_build->products ) {
			//no products
			return false;
		}
		$get_order                = WFOCU_Core()->data->get_parent_order();
		$treat_variable_as_simple = WFOCU_Core()->data->get_option( 'treat_variable_as_simple' );
		if ( true === $offer_build->settings->skip_exist ) {

			$items            = $get_order->get_items();
			$offer_items_sold = array();
			$offer_items      = array();

			foreach ( $offer_build->products as $product_data ) {
				$offer_product_id = $product_data->data->get_id();
				$offer_product    = wc_get_product( $offer_product_id );

				$offer_items[ $offer_product_id ] = 1;

				foreach ( $items as $item ) {
					$product = WFOCU_WC_Compatibility::get_product_from_item( $get_order, $item );

					/**
					 * By Default, If global settings are checked to treat variable product as simple then We treat order variaion as variable and matches with the variable product in the offer.
					 * Rest all the cases handled in the else where we check direct product ID match.
					 */
					if ( true === $treat_variable_as_simple && $offer_product->is_type( 'variable' ) && $product->is_type( 'variation' ) ) {
						$order_product_id = $product->get_parent_id();
						if ( $offer_product_id === $order_product_id ) {
							$offer_items_sold[ $offer_product_id ] = 1;
						}
					} elseif ( $offer_product_id === $product->get_id() ) {
						$offer_items_sold[ $offer_product_id ] = 1;
					}
				}
			}
			/**
			 * Items are already purchased. as count of sold items in the cart matches to the offer items sold in the prev order
			 */
			if ( count( $offer_items_sold ) > 0 ) {
				WFOCU_Core()->template_loader->invalidation_reason = self::INVALIDATION_PRODUCT_IN_ORDER;
				WFOCU_Core()->log->log( 'Order #' . WFOCU_WC_Compatibility::get_order_id( $get_order ) . ': Items are already purchased' );

				return false;
			}
		}

		if ( true === $offer_build->settings->skip_purchased ) {

			if ( ! function_exists( 'bwf_get_contact' ) ) {
				return true;
			}
			$bwf_contact = bwf_get_contact( $get_order->get_customer_id(), $get_order->get_billing_email() );

			if ( ! $bwf_contact instanceof WooFunnels_Contact ) {
				return true;
			}
			$bwf_contact->set_customer_child();
			$purchased_products = $bwf_contact->get_customer_purchased_products();
			$purchased          = false;

			foreach ( $offer_build->products as $product_data ) {
				$offer_product_id = $product_data->data->get_id();
				$offer_product    = $product_data->data;

				if ( $offer_product->is_type( 'variation' ) && true === $treat_variable_as_simple ) {
					$offer_product_id = $offer_product->get_parent_id();
				}


				if ( in_array( $offer_product_id, $purchased_products, true ) ) {
					/**
					 * If any of the offer Product IDs matches with the purchased product then
					 */
					$purchased = true;
					break;
				}
			}

			/**
			 * Items are already purchased. as products in offer are available in purchased products
			 */
			if ( $purchased ) {
				WFOCU_Core()->template_loader->invalidation_reason = self::INVALIDATION_PAST_PURCHASED;
				WFOCU_Core()->log->log( 'Order #' . WFOCU_WC_Compatibility::get_order_id( $get_order ) . ': Items are already purchased in past' );

				return false;
			}
		}

		foreach ( $offer_build->products as $product_data ) {

			$iteration = true;

			if ( $product_data->data->is_type( 'variable' ) ) {
				if ( ! isset( $product_data->variations_data ) ) {
					$iteration = false;
					continue;
				}

				if ( ! isset( $product_data->variations_data['available_variations'] ) ) {
					$iteration = false;
					continue;
				}

				if ( empty( $product_data->variations_data['available_variations'] ) ) {
					$iteration = false;
					continue;
				}
			}
			if ( false === $product_data->is_purchasable ) {
				$iteration = false;
				continue;
			}
			if ( isset( $product_data->is_in_stock ) ) {

				// Enable or disable the add to cart button
				if ( ! $product_data->is_purchasable || ! ( isset( $product_data->is_in_stock ) && $product_data->is_in_stock ) ) {

					$iteration = false;
				}

				if ( ( isset( $product_data->is_in_stock ) && false === $product_data->backorders_allowed ) && ( isset( $product_data->max_qty ) && - 1 !== $product_data->max_qty ) ) {

					$current_stock = $product_data->max_qty;
					$offer_qty     = (int) $product_data->quantity;

					if ( $current_stock < $offer_qty ) {

						$iteration = false;
					}
				}
			}

			/**
			 * If all product passes the check, then show the upsell
			 */
			if ( true === $iteration ) {
				return true;
			}
		}
		WFOCU_Core()->log->log( 'Order #' . WFOCU_WC_Compatibility::get_order_id( $get_order ) . ': Offer product(s) are not purchasable/instock' );

		WFOCU_Core()->template_loader->invalidation_reason = self::INVALIDATION_NOT_PURCHASABLE;

		return false;

	}

	public function append_offer_unique_class( $classes ) {
		array_push( $classes, 'wfocu_offer' . WFOCU_Core()->data->get_current_offer() );

		return $classes;
	}

	public function get_the_link( $offer ) {

		$offer_data = WFOCU_Core()->offers->get_offer( $offer );

		if ( 'custom-page' === $offer_data->template ) {
			$custom_page_id = get_post_meta( $offer, '_wfocu_custom_page', true );
			if ( ! empty( $custom_page_id ) ) {
				return apply_filters( 'wfocu_front_offer_url', get_permalink( $custom_page_id ) );
			}
		}

		return apply_filters( 'wfocu_front_offer_url', get_permalink( $offer ) );

	}

	public function get_offer( $offer_id, $build = false ) {
		$data = get_post_meta( $offer_id, '_wfocu_setting', true );

		$offer_data = apply_filters( 'wfocu_offer_setting', $data, $offer_id );
		if ( false !== $build ) {
			return $this->build_offer_product( $offer_data );
		}

		return $offer_data;
	}

	public function build_offer_product( $offer_data, $offer_id = 0, $is_front = false ) {
		$variations = new stdClass();
		$products   = new stdClass();
		if ( empty( $offer_data ) || ! isset( $offer_data->products ) ) {
			$default                        = new stdClass();
			$default->products              = new stdClass();
			$default->fields                = new stdClass();
			$default->variations            = new stdClass();
			$default->settings              = $this->get_default_offer_setting();
			$default->template              = '';
			$default->template_group        = '';
			$default->have_multiple_product = 1;

			return $default;
		}

		$offer_settings                = isset( $offer_data->settings ) ? $offer_data->settings : [];
		$offer_data->settings          = (object) array_merge( (array) $this->get_default_offer_setting(), (array) $offer_settings );
		$products_list                 = $offer_data->products;
		$output                        = new stdClass();
		$output->fields                = $offer_data->fields;
		$output->settings              = ! empty( $offer_data->settings ) ? $offer_data->settings : $this->get_default_offer_setting();
		$output->template_group        = isset( $offer_data->template_group ) ? $offer_data->template_group : '';
		$output->have_multiple_product = isset( $offer_data->have_multiple_product ) ? $offer_data->have_multiple_product : 1;
		$output->is_show_confirmation  = $offer_data->settings->ask_confirmation;
		$output->shipping_preferece    = ( true === $offer_data->settings->ship_dynamic ) ? 'dynamic' : 'flat';

		if ( $is_front === true ) {
			$output->template = isset( $offer_data->template ) ? $offer_data->template : WFOCU_Core()->template_loader->get_default_template( $offer_data );

		} else {
			$output->template = isset( $offer_data->template ) ? $offer_data->template : '';
		}
		if ( false === class_exists( 'WooFunnels_UpStroke_Dynamic_Shipping' ) ) {
			$output->shipping_preferece     = 'flat';
			$output->settings->ship_dynamic = false;
		}
		$output->allow_free_shipping = false;

		$custom_page = get_post_meta( $offer_id, '_wfocu_custom_page', true );

		if ( $custom_page !== '' ) {
			$output->template_custom_path = get_edit_post_link( $custom_page );
			$output->template_custom_name = get_the_title( $custom_page );
			$output->template_custom_id   = $custom_page;
		}
		foreach ( $products_list as $hash_key => $pid ) {
			$offer_data->fields->{$hash_key}->discount_type = WFOCU_Common::get_discount_setting( $offer_data->fields->{$hash_key}->discount_type );
			$pro                                            = wc_get_product( $pid );
			if ( $pro instanceof WC_Product ) {
				if ( $pro->is_type( 'variable' ) ) {

					foreach ( $pro->get_children() as $child_id ) {
						$variation = wc_get_product( $child_id );

						$variation_id = $child_id;
						$vpro         = $variation;

						if ( $vpro ) {
							$variation_options                    = new stdClass();
							$variation_options->vid               = $variation_id;
							$variation_options->is_enable         = false;
							$variation_options->attributes        = new stdClass();
							$variation_options->attributes        = WFOCU_Common::get_variation_attribute( $vpro );
							$variation_options->regular_price     = wc_price( $vpro->get_regular_price() );
							$variation_options->regular_price_raw = wc_get_price_including_tax( $vpro, array( 'price' => $vpro->get_regular_price() ) );
							$variation_options->price             = wc_price( $vpro->get_price() );
							$variation_options->price_raw         = $vpro->get_price();

							if ( false === $is_front && isset( $variation_options->price ) && $variation_options->regular_price === $variation_options->price ) {
								unset( $variation_options->price );
							}

							$variation_options->display_price   = wc_price( $vpro->get_price() );
							$variation_options->discount_amount = 0;
							$variation_options->name            = WFOCU_Common::get_formatted_product_name( $vpro );
							$variation_options->is_in_stock     = $vpro->is_in_stock();

							if ( isset( $offer_data->variations->{$hash_key} ) ) {
								if ( isset( $offer_data->variations->{$hash_key}[ $variation_id ] ) ) {
									$vars = $offer_data->variations->{$hash_key}[ $variation_id ];
									foreach ( $vars as $vkey => $vval ) {
										$variation_options->is_enable = true;
										$variation_options->{$vkey}   = $vval;
									}
								}
							}

							$variations->{$hash_key}[ $variation_id ] = $variation_options;
							unset( $variation_options );
						}
					}
				}

				$image_url = wp_get_attachment_url( $pro->get_image_id() );

				if ( false === $image_url || '' === $image_url ) {
					$image_url = wc_placeholder_img_src();
				}
				$product_details       = new stdClass();
				$product_details->id   = $pid;
				$product_details->name = ( false === $is_front ) ? WFOCU_Common::get_formatted_product_name( $pro ) : $pro->get_title();

				$product_details->image  = $image_url;
				$product_options         = $product_details;
				$product_details->type   = $pro->get_type();
				$product_details->status = $pro->get_status();
				if ( false === $pro->is_type( 'variable' ) ) {

					if ( false === $is_front ) {
						if ( $pro->is_type( 'subscription' ) ) {
							$product_details->regular_price = WC_Subscriptions_Product::get_price_string( $pro, array( 'price' => wc_price( $pro->get_regular_price() ) ) );
						} else {
							$product_details->regular_price = wc_price( $pro->get_regular_price() );
						}
						$product_details->regular_price_raw = $pro->get_regular_price();
						if ( $pro->is_type( 'subscription' ) ) {
							$product_details->price     = WC_Subscriptions_Product::get_price_string( $pro, array( 'price' => wc_price( $pro->get_price() ) ) );
							$product_details->price_raw = $pro->get_price();
						} else {
							$product_details->price     = wc_price( $pro->get_price() );
							$product_details->price_raw = $pro->get_price();
						}

						if ( $product_details->regular_price === $product_details->price ) {
							unset( $product_details->price );
						}
					} else {
						$product_details->regular_price_incl_tax = wc_get_price_including_tax( $pro, array( 'price' => $pro->get_regular_price() ) ) * $offer_data->fields->{$hash_key}->quantity;
						$product_details->regular_price_excl_tax = wc_get_price_excluding_tax( $pro, array( 'price' => $pro->get_regular_price() ) ) * $offer_data->fields->{$hash_key}->quantity;

						$product_details->sale_price_incl_tax      = WFOCU_Core()->offers->get_product_price( $pro, $offer_data->fields->{$hash_key}, true, $offer_data );
						$product_details->sale_price_raw_incl_tax  = WFOCU_Core()->offers->get_product_price( $pro, $offer_data->fields->{$hash_key}, true, $offer_data );
						$product_details->sale_price_excl_tax      = WFOCU_Core()->offers->get_product_price( $pro, $offer_data->fields->{$hash_key}, false, $offer_data );
						$product_details->sale_price_incl_tax_html = WFOCU_Core()->offers->get_product_price_display( $pro, $offer_data->fields->{$hash_key}, true, $offer_data, $offer_data );
						$product_details->sale_price_excl_tax_html = WFOCU_Core()->offers->get_product_price_display( $pro, $offer_data->fields->{$hash_key}, false, $offer_data, $offer_data );

						if ( $this->show_price_including_tax() ) {
							$product_details->price         = $product_details->sale_price_incl_tax;
							$product_details->price_raw     = $product_details->sale_price_incl_tax;
							$product_details->display_price = $product_details->sale_price_incl_tax_html;
							$product_details->regular_price = $product_details->regular_price_incl_tax;
						} else {
							$product_details->price_raw     = $product_details->sale_price_excl_tax;
							$product_details->display_price = $product_details->sale_price_excl_tax_html;
							$product_details->regular_price = $product_details->regular_price_excl_tax;
						}
						$product_details->tax = $product_details->sale_price_incl_tax - $product_details->sale_price_excl_tax;

					}
				}
				$product_details->data = $pro;
				$temp_fields           = $offer_data->fields->{$hash_key};
				if ( ! empty( $temp_fields ) ) {
					foreach ( $temp_fields as $fkey => $t_fields ) {
						$product_details->{$fkey} = $t_fields;
					}
				}

				if ( ! property_exists( $product_details, 'shipping_cost_flat' ) ) {
					$product_details->shipping_cost_flat = 10;
				}
				$product_details->shipping_cost_flat = WFOCU_Plugin_Compatibilities::get_fixed_currency_price( $product_details->shipping_cost_flat );
				if ( ! property_exists( $product_details, 'shipping_cost_flat_tax' ) ) {
					$product_details->shipping_cost_flat_tax = $is_front ? WFOCU_Core()->shipping->get_flat_shipping_rates( $product_details->shipping_cost_flat ) : 0;
				}
				if ( ! property_exists( $product_details, 'needs_shipping' ) ) {
					$product_details->needs_shipping = wc_shipping_enabled() && $pro->needs_shipping();
				}


				$products->{$hash_key} = apply_filters( 'wfocu_offer_product_data', $product_details, $output, $offer_data, $is_front, $hash_key );
				unset( $product_details );
				unset( $product_options );
			}
			if ( false === WFOCU_Common::is_add_on_exist( 'MultiProduct' ) ) {
				break;
			}
		}
		$output->last_edit  = $this->get_offer_last_edit( $offer_id );
		$output->products   = $products;
		$output->variations = $variations;
		$output             = apply_filters( 'wfocu_offer_data', $output, $offer_data, $is_front );

		return $output;
	}

	public function get_default_offer_setting() {
		$obj                            = new stdClass();
		$obj->ship_dynamic              = false;
		$obj->ask_confirmation          = false;
		$obj->allow_free_ship_select    = false;
		$obj->skip_exist                = false;
		$obj->skip_purchased            = false;
		$obj->terminate_if_declined     = false;
		$obj->terminate_if_accepted     = false;
		$obj->check_add_offer_script    = false;
		$obj->check_add_offer_purchase  = false;
		$obj->upsell_page_track_code    = '';
		$obj->upsell_page_purchase_code = '';
		$obj->qty_selector              = false;
		$obj->qty_max                   = '10';
		$obj->jump_on_accepted          = false;
		$obj->jump_on_rejected          = false;
		$obj->jump_to_offer_on_accepted = 'automatic';
		$obj->jump_to_offer_on_rejected = 'automatic';

		return apply_filters( 'wfocu_offer_settings_default', $obj );
	}

	/**
	 * @param $product
	 * @param $options
	 * @param bool $incl_tax
	 * @param $offer_data
	 *
	 * @return string
	 */
	public function get_product_price_display( $product, $options, $incl_tax = false, $offer_data ) {

		if ( ! $product instanceof WC_Product ) {
			$product = wc_get_product( $product );
		}

		$get_price = $this->get_product_price( $product, $options, $incl_tax, $offer_data );

		return wc_price( $get_price );

	}

	public function show_price_including_tax() {
		return true;
	}

	public function get_offer_last_edit( $offer_id ) {
		$get_last_edit = get_post_meta( $offer_id, '_wfocu_edit_last', true );

		return ( '' !== $get_last_edit ) ? $get_last_edit : 0;
	}

	public function filter_product_object_for_db( $product ) {
		$keys_to_filter = array(
			'settings' => array( 'needs_shipping', 'shipping_cost_flat_tax' ),
			'is_in_stock',
			'max_qty',
			'is_purchasable',
			'backorders_allowed',
			'name',
			'image',
		);

		if ( isset( $product->options ) ) {
			unset( $product->options );
		}
		foreach ( $keys_to_filter as $key => $value ) {

			if ( is_array( $value ) ) {
				foreach ( $value as $internal_keys ) {
					if ( isset( $product->{$key}->{$internal_keys} ) ) {
						unset( $product->{$key}->{$internal_keys} );
					}
				}
			} else {
				if ( isset( $product->{$value} ) ) {
					unset( $product->{$value} );
				}
			}
		}

		return $product;
	}

	public function filter_step_object_for_db( $step ) {
		$keys_to_filter = array(
			'url',
		);
		if ( $step['state'] === '1' || $step['state'] === 'true' || $step['state'] === true || $step['state'] === 1 ) {
			$step['state'] = '1';
		} else {
			$step['state'] = '0';
		}

		foreach ( $keys_to_filter as $value ) {

			if ( isset( $step[ $value ] ) ) {
				unset( $step[ $value ] );
			}
		}

		return $step;
	}

	public function filter_fields_object_for_db( $fields ) {
		$keys_to_filter = array( 'needs_shipping', 'shipping_cost_flat_tax' );

		foreach ( $keys_to_filter as $value ) {

			foreach ( $fields as $k => $config ) {  //phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UnusedVariable
				if ( isset( $fields->{$k}->{$value} ) ) {
					unset( $fields->{$k}->{$value} );
				}
			}
		}

		return $fields;
	}


	public function get_parent_funnel( $offer_id ) {

		return get_post_meta( $offer_id, '_funnel_id', true );
	}

	public function get_invalidation_reason_string( $identifier ) {
		$reasons = $this->invalidation_reasons();

		if ( array_key_exists( $identifier, $reasons ) ) {
			return '<span class="skip-reason">' . $reasons[ $identifier ] . '</span>';
		}

		return 'NA';

	}

	public function invalidation_reasons() {
		return array(
			self::INVALIDATION_PRODUCT_IN_ORDER => __( 'Offer product(s) exist in parent order.', '' ),
			self::INVALIDATION_NOT_PURCHASABLE  => __( 'Offer Product is not purchasable/in stock.', '' ),
			self::INVALIDATION_PAST_PURCHASED   => __( 'Offer product(s) previously purchased by customer.', '' ),
		);
	}

	public function get_default_offer_schema() {
		return array(
			'id'    => '{{offer_id}}',
			'name'  => __( 'Sample Offer- Do not miss', 'woofunnels-upstroke-one-click-upsell' ),
			'type'  => 'upsell',
			'state' => '1',
			'slug'  => 'sample-offer-do-not-miss',
			'meta'  => array(

				'_offer_type'    => 'upsell',
				'_wfocu_setting' => (object) array(
					'products'              => (object) array(
						$this->get_default_product_key( $this->get_default_product() ) => $this->get_default_product(),
					),
					'variations'            => (object) array(),
					'fields'                => (object) array(
						$this->get_default_product_key( $this->get_default_product() ) => (object) array(
							'discount_amount'    => '10',
							'discount_type'      => 'percentage_on_reg',
							'quantity'           => '1',
							'shipping_cost_flat' => 0.0,
						),
					),
					'have_multiple_product' => 1,
					'template'              => '',
					'template_group'        => '',
					'settings'              => array(
						'ship_dynamic'           => false,
						'ask_confirmation'       => false,
						'allow_free_ship_select' => false,
						'skip_exist'             => false,
						'skip_purchased'         => false,
					),
				),
			),
		);
	}

	public function get_default_product_key( $post ) {
		return md5( $post );

	}

	public function get_default_product() {
		$bwf_cache      = WooFunnels_Cache::get_instance();
		$latest_product = $bwf_cache->get_cache( 'get_latest_product', 'upstroke' );
		if ( empty( $latest_product ) ) {
			$query    = new WC_Product_Query( array(
				'limit'  => 1,
				'type'   => 'simple',
				'return' => 'ids',
			) );
			$products = $query->get_products();

			if ( is_array( $products ) ) {
				$bwf_cache->set_cache( 'get_latest_product', $products[0], 'upstroke' );

				return $products[0];
			} else {
				return false;
			}
		}

		return $latest_product;

	}

	public function get_offer_state( $steps, $offer_id ) {
		foreach ( is_array( $steps ) ? $steps : array() as $step ) {
			if ( intval( $step['id'] ) === intval( $offer_id ) || absint( $step['id'] ) === $offer_id ) {
				return $step['state'];
			}
		}

		return null;
	}

	public function get_product_key_by_index( $index, $products ) {

		$get_keys = get_object_vars( $products );

		if ( empty( $get_keys ) ) {
			return false;
		}
		$get_keys = array_keys( $get_keys );
		if ( empty( $get_keys ) ) {
			return false;
		}
		if ( ! is_numeric( $index ) || ! isset( $get_keys[ $index - 1 ] ) ) {
			return false;
		}

		return $get_keys[ $index - 1 ];
	}
}

if ( class_exists( 'WFOCU_Core' ) ) {
	WFOCU_Core::register( 'offers', 'WFOCU_Offers' );
}