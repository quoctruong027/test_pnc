<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class WCCT_discount {

	public static $_instance = null;
	public $regular_prices = array();
	public $excluded = array();
	public $is_wc_calculating = false;
	private $percentage = false;
	public $variation_prices = array();

	public function __construct() {

		global $woocommerce;

		if ( version_compare( $woocommerce->version, 3.0, '>=' ) ) {

			add_filter( 'woocommerce_product_get_price', array( $this, 'wcct_trigger_get_price' ), 999, 2 );
			add_filter( 'woocommerce_product_get_sale_price', array( $this, 'wcct_trigger_get_sale_price' ), 999, 2 );
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'wcct_trigger_get_price' ), 999, 2 );
			add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'wcct_trigger_get_sale_price' ), 999, 2 );

			/**
			 * Handling for the regular price event action
			 */
			add_filter( 'woocommerce_product_get_price', array( $this, 'wcct_handle_price_by_event' ), 900, 2 );
			add_filter( 'woocommerce_product_get_regular_price', array( $this, 'wcct_handle_regular_price_by_event' ), 900, 2 );
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'wcct_handle_price_by_event' ), 999, 2 );
			add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'wcct_handle_regular_price_by_event' ), 999, 2 );

			/**
			 * Additional filter applied to check if we need to display price or not.
			 */
			add_filter( 'woocommerce_product_get_date_on_sale_from', array( $this, 'wcct_get_date_on_sale_from' ), 999, 2 );
			add_filter( 'woocommerce_product_variation_get_date_on_sale_from', array( $this, 'wcct_get_date_on_sale_from' ), 999, 2 );
			add_filter( 'woocommerce_product_get_date_on_sale_to', array( $this, 'wcct_get_date_on_sale_to' ), 999, 2 );
			add_filter( 'woocommerce_product_variation_get_date_on_sale_to', array( $this, 'wcct_get_date_on_sale_to' ), 999, 2 );
		} else {

			add_filter( 'woocommerce_get_price', array( $this, 'wcct_trigger_get_price' ), 10, 2 );
			add_filter( 'woocommerce_get_sale_price', array( $this, 'wcct_trigger_get_sale_price' ), 999, 2 );
			add_filter( 'woocommerce_get_price', array( $this, 'wcct_handle_price_by_event' ), 900, 2 );
			add_filter( 'woocommerce_get_regular_price', array( $this, 'wcct_handle_regular_price_by_event' ), 900, 2 );
		}

		/**
		 * For variation products we need to handle case where we mark variable product as it is on sale
		 */
		add_filter( 'woocommerce_product_is_on_sale', array( $this, 'wcct_maybe_mark_product_having_sale' ), 999, 2 );

		/**
		 * modify price ranges for variable products
		 */
		add_filter( 'woocommerce_variation_prices', array( $this, 'wcct_change_price_ranges' ), 900, 3 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'maybe_flag_running_calculations' ) );
		add_action( 'woocommerce_after_calculate_totals', array( $this, 'maybe_unflag_running_calculations' ) );

		/**
		 * Managing the discounts hooks and filters to skip infinite loop/iterations
		 */

		add_action( 'wcct_before_get_sale_price', array( $this, 'maybe_remove_regular_price_hooks' ), 10, 3 );
		add_action( 'wcct_after_get_sale_price', array( $this, 'maybe_restore_regular_price_hooks' ), 10, 3 );

		/**
		 * Need to modify the variation hash in order to let the woocommerce display the correct and modified variation
		 * Commented in 2.12.1 as causing transients creation every time on page load, so DB flood.
		 */
		add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'maybe_modify_variation_price_hash' ), 999, 3 );
	}

	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * @param Float $get_price
	 * @param WC_Product $product_global
	 *
	 * @return mixed
	 */
	public function wcct_trigger_get_price( $get_price, $product_global ) {
		if ( ! $product_global instanceof WC_Product ) {
			return $get_price;
		}

		$is_skip = apply_filters( 'wcct_skip_discounts', false, $get_price, $product_global );

		wcct_force_log( "Product id {$product_global->get_id()} : " . __FUNCTION__ . ' Before Price: ' . $get_price );
		if ( '' === $get_price ) {
			return $get_price;
		}

		if ( true === $is_skip ) {
			return $get_price;
		}

		if ( in_array( $product_global->get_type(), WCCT_Common::get_sale_compatible_league_product_types(), true ) ) {
			$get_price = $this->wcct_trigger_create_price( $get_price, $product_global );
		}

		wcct_force_log( "Product id {$product_global->get_id()} : " . __FUNCTION__ . ' After Price: ' . $get_price );

		return $get_price;
	}

	/**
	 * @param $sale_price
	 * @param WC_Product $product_global
	 * @param string $mode
	 * @param bool $regular_price
	 * @param int $parent_product
	 *
	 * @return mixed
	 */
	public function wcct_trigger_create_price( $sale_price, $product_global, $mode = 'basic', $regular_price = false, $parent_product = 0 ) {
		/**
		 * Here we are handling the case of all the hooks works while variable price range is getting generated
		 * We always pass $regular_price in that case
		 * so for the regular price we have, we do not need to generate any product variation object
		 * In that way we can easily by pass creating an object & the queries associated with that.
		 */

		if ( false === $regular_price ) {
			$type              = $product_global->get_type();
			$parent_id         = WCCT_Core()->public->wcct_get_product_parent_id( $product_global );
			$product_global_id = $product_global->get_id();
		} else {
			$type              = 'variation';
			$product_global_id = $product_global;
			$parent_id         = $parent_product;
		}

		if ( WCCT_Core()->public->wcct_restrict_for_booking_oth( $parent_id, $type ) ) {
			return $sale_price;
		}

		$temp_id = $parent_id;
		$data    = WCCT_Core()->public->get_single_campaign_pro_data( $temp_id );

		if ( empty( $data ) ) {
			wcct_force_log( ' terminating ' . __FUNCTION__ . ' For Product' . $temp_id );

			return $sale_price;
		}
		if ( ! isset( $data['deals'] ) || ! is_array( $data['deals'] ) ) {
			return $sale_price;
		}

		$deals          = $data['deals'];
		$deals_override = $deals;

		if ( false === $regular_price ) {
			do_action( 'wcct_before_get_regular_price', $product_global );

			$regular_price = $product_global->get_regular_price();

			do_action( 'wcct_after_get_regular_price', $product_global );
		}

		if ( empty( $regular_price ) ) {
			return $sale_price;
		}
		if ( ! is_array( $deals_override ) ) {

			return $this->wcct_validate_sale_price( $sale_price, $regular_price, $product_global_id );
		}
		$deal_ultimate_amount = false;

		if ( 'tiered' === $deals_override['mode'] ) {
			$deal_custom_advanced = $deals_override['deal_amount_advanced'];

			if ( is_array( $deal_custom_advanced ) && count( $deal_custom_advanced ) > 0 ) {

				foreach ( $deal_custom_advanced as $rule ) {

					$default = array(
						'range_from'  => 0,
						'range_to'    => '99999999',
						'range_value' => 5,
					);

					$config = wp_parse_args( $rule, $default );

					if ( '' === $config['range_from'] ) {
						$config['range_from'] = $default['range_from'];
					}
					if ( '' === $config['range_to'] ) {
						$config['range_to'] = $default['range_to'];
					}
					if ( '' === $config['range_value'] ) {
						$config['range_value'] = $default['range_value'];
					}

					$config = apply_filters( 'wcct_deals_custom_advanced_range', $config, $deals_override );
					if ( $config['range_from'] <= $regular_price && $config['range_to'] >= $regular_price ) {
						$deal_ultimate_amount = ( true === $deals_override['event_overridden'] ) ? $deals_override['deal_amount'] : $config['range_value'];
					}
				}
			}
		} else {
			$deal_ultimate_amount = apply_filters( 'wcct_discount_ultimate_amount', $deals_override['deal_amount'], $product_global_id, $type, $data );
		}

		if ( false === $deal_ultimate_amount ) {
			return $sale_price;
		}

		if ( 1 == $deals_override['override'] ) {

			$check_sale = get_post_meta( $product_global_id, '_sale_price', true ); // we are fetching sale price from db using get_post_meta otherwise will stick in loop

			$check_sale = apply_filters( 'wcct_discount_check_sale_price', $check_sale, $product_global );

			if ( $check_sale >= '0' ) {
				$sale_price = (float) $sale_price;

				return $this->wcct_validate_sale_price( $sale_price, $regular_price, $product_global_id );
			}
		}

		$deal        = $deals_override;
		$deal_amount = $deal_ultimate_amount;

		if ( $deal_amount >= 0 ) {
			switch ( $deal['type'] ) {
				case 'percentage':
					$deal_amount = apply_filters( "wcct_deal_amount_percentage_{$type}", $deal_amount, $product_global, $data );
					if ( 'sale' === $mode && '0' == $deal_amount ) {
						return '';
					}
					$set_sale_price = $regular_price - ( $regular_price * ( $deal_amount / 100 ) );
					if ( $set_sale_price >= 0 ) {
						$sale_price = $set_sale_price;
					} else {
						$sale_price = 0;
					}
					break;
				case 'percentage_sale':
					$deal_amount = apply_filters( "wcct_deal_amount_percentage_{$type}", $deal_amount, $product_global, $data );
					if ( 'sale' === $mode && '0' == $deal_amount ) {
						return '';
					}
					if ( empty( $sale_price ) ) {
						$sale_price = $regular_price;
					}
					$set_sale_price = $sale_price - ( $sale_price * ( $deal_amount / 100 ) );
					if ( $set_sale_price >= 0 ) {
						$sale_price = $set_sale_price;
					} else {
						$sale_price = 0;
					}
					break;
				case 'fixed_sale':
					$deal_amount = apply_filters( "wcct_deal_amount_fixed_amount_{$type}", $deal_amount, $product_global, $data );
					if ( 'sale' === $mode && '0' == $deal_amount ) {
						return '';
					}
					if ( empty( $sale_price ) ) {
						$sale_price = $regular_price;
					}

					if ( false !== $this->percentage ) {
						$deal_amount = ( $deal_amount + ( $deal_amount * ( $this->percentage / 100 ) ) );
					}

					$set_sale_price = $sale_price - $deal_amount;
					if ( $set_sale_price >= 0 ) {
						$sale_price = $set_sale_price;
					} else {
						$sale_price = 0;
					}
					break;
				case 'fixed_price':
					$deal_amount = apply_filters( "wcct_deal_amount_fixed_amount_{$type}", $deal_amount, $product_global, $data );
					if ( 'sale' === $mode && '0' == $deal_amount ) {
						return '';
					}

					if ( false !== $this->percentage ) {
						$deal_amount = ( $deal_amount + ( $deal_amount * ( $this->percentage / 100 ) ) );
					}

					$set_sale_price = $regular_price - $deal_amount;
					if ( $set_sale_price >= 0 ) {
						$sale_price = $set_sale_price;
					} else {
						$sale_price = 0;
					}
					break;
				case 'flat_sale':
					$deal_amount = apply_filters( "wcct_deal_amount_fixed_amount_{$type}", $deal_amount, $product_global, $data );
					if ( 'sale' === $mode && '0' == $deal_amount ) {
						return '';
					}

					if ( false !== $this->percentage ) {
						$deal_amount = ( $deal_amount + ( $deal_amount * ( $this->percentage / 100 ) ) );
					}

					if ( $regular_price > $deal_amount ) {
						$sale_price = $deal_amount;
					}
					break;
			}
			do_action( 'wcct_finale_price_discount_applied', $sale_price, $regular_price, $product_global_id );

		} else {
			return $this->wcct_validate_sale_price( $sale_price, $regular_price, $product_global_id );
		}

		$sale_price = apply_filters( 'wcct_finale_discounted_price', $sale_price, $regular_price, $product_global_id );

		return $this->wcct_validate_sale_price( $sale_price, $regular_price, $product_global_id );
	}

	/**
	 * Validate sale price with regular price (modified with out plugin using event actions )
	 *
	 * @param $sale_price : Sale price to check with
	 * @param Int|boolean $regular_price
	 * @param $product_id : Product ID
	 *
	 * @return mixed Original $sale_price when everything fine, regular price when regular price is less or equal to the calculated sale price
	 */
	private function wcct_validate_sale_price( $sale_price, $regular_price, $product_id ) {
		if ( $sale_price == '0' ) {
			return '0';
		}
		$return = $sale_price;

		if ( ! array_key_exists( $product_id, $this->regular_prices ) ) {
			$return = $sale_price;
		}

		if ( $regular_price <= $sale_price ) {
			$return = $regular_price;
		}

		$return = wc_format_decimal( $return, wc_get_price_decimals() );

		return $return;
	}

	public function wcct_trigger_get_sale_price( $sale_price, $product_global ) {

		if ( ! $product_global instanceof WC_Product ) {
			return $sale_price;
		}

		$is_skip = apply_filters( 'wcct_skip_discounts', false, $sale_price, $product_global );

		if ( true === $is_skip ) {
			return $sale_price;
		}
		wcct_force_log( "Product id {$product_global->get_id()} : " . __FUNCTION__ . ' Before Price: ' . $sale_price );

		if ( in_array( $product_global->get_type(), WCCT_Common::get_sale_compatible_league_product_types(), true ) ) {
			$sale_price = $this->wcct_trigger_create_price( $sale_price, $product_global, 'sale' );
		}
		wcct_force_log( "Product id {$product_global->get_id()} : " . __FUNCTION__ . ' After Price: ' . $sale_price );

		return $sale_price;
	}

	/**
	 * Common wrapper function for regular price events
	 * handle all callbacks, checks for data existence
	 *
	 * @param $price float to modify
	 * @param $product WC_Product|Int
	 *
	 * @return mixed Modified or same price
	 */
	private function _wcct_common_handle_regular_price_by_event( $price, $product, $display = false, $parent_id = 0 ) {
		$parent_id = ( 0 == $parent_id ) ? WCCT_Core()->public->wcct_get_product_parent_id( $product ) : $parent_id;

		$data = WCCT_Core()->public->get_single_campaign_pro_data( $parent_id );

		if ( isset( $data['regular_prices'] ) && $data['regular_prices'] && is_array( $data['regular_prices'] ) && count( $data['regular_prices'] ) > 0 ) {

			return $this->_wcct_get_modified_regular_price( $price, $data, $product, $display );
		}

		return $price;
	}

	/**
	 * Modal function to calculate regular price based on given data
	 *
	 * @param $price : Price to modify
	 * @param $data : Event Data to modify price
	 * @param WC_Product|Int $product_global Current Product
	 *
	 * @return int Modified Price
	 */
	private function _wcct_get_modified_regular_price( $price, $data, $product_global, $display = false ) {
		if ( ! is_array( $data ) ) {
			return $price;
		}
		if ( is_array( $data ) && count( $data ) === 0 ) {
			return $price;
		}

		$price                 = (float) $price;
		$product_global_object = false;
		$final_price           = 0;
		$regular_prices_array  = current( $data['regular_prices'] );

		if ( is_array( $regular_prices_array ) && count( $regular_prices_array ) > 0 ) {

			if ( ! $product_global instanceof WC_Product ) {
				$product_global_object = WCCT_Core()->public->wcct_get_product_obj( $product_global );
			} else {
				$product_global_object = $product_global;
			}

			foreach ( $regular_prices_array as $events ) {
				$regular_prices          = $events;
				$regular_prices['value'] = (float) $regular_prices['value'];

				if ( $regular_prices['is_percent'] ) {
					$value = $price * ( $regular_prices['value'] / 100 );
				} else {
					$value = apply_filters( 'wcct_regular_price_event_value_fixed', $regular_prices['value'], $product_global_object );
				}

				if ( '-' === $regular_prices['operator'] ) {

					$final_price = $final_price - $value;
				} else {
					$final_price = $final_price + $value;
				}
			}
		}

		$generated_regular_price = ( $price + $final_price ) <= 0 ? 0 : ( $price + $final_price );

		if ( $product_global_object instanceof WC_Product ) {
			$this->regular_prices[ $product_global_object->get_id() ] = $generated_regular_price;
		} else {
			$this->regular_prices[ $product_global ] = $generated_regular_price;
		}

		return $generated_regular_price;
	}

	public function wcct_set_variation_price( $input, $type = 'basic', $regular_price = false, $parent_product = 0 ) {
		if ( is_array( $input ) ) {

			foreach ( $input as $k => $price ) {

				$is_skip = apply_filters( 'wcct_skip_discounts', false, $price, $k );

				if ( true === $is_skip ) {
					$input[ $k ] = $price;
					continue;
				}
				//formatting the prices as per WC is doing so that further comparison can take place between reg price and sale price to detect is on sale
				$input[ $k ] = $this->wcct_trigger_create_price( $price, $k, $type, ( false !== $regular_price ) ? $regular_price[ $k ] : false, $parent_product );
			}
		}

		return $input;
	}

	public function wcct_trigger_create_sale_variation( $sale_price, $variation, $product_global ) {
		return $this->wcct_trigger_create_price( $sale_price, $variation, 'sale' );
	}

	/**
	 * @param $sale_from
	 * @param $product_global WC_Product
	 *
	 * @return WC_DateTime
	 * @throws Exception
	 */
	public function wcct_get_date_on_sale_from( $sale_from, $product_global ) {
		if ( $product_global instanceof WC_Product ) {
			$parent_id = WCCT_Core()->public->wcct_get_product_parent_id( $product_global );
			if ( WCCT_Core()->public->wcct_restrict_for_booking_oth( $parent_id ) ) {
				return $sale_from;
			}
			$data = WCCT_Core()->public->get_single_campaign_pro_data( $parent_id );

			if ( isset( $data['deals'] ) && is_array( $data['deals'] ) && count( $data['deals'] ) > 0 ) {
				$deals = $data['deals'];
				if ( isset( $deals['override'] ) && ( true === $deals['override'] ) ) {
					return $sale_from;
				}
				if ( isset( $deals['start_time'] ) && ( (int) $deals['start_time'] > 0 ) ) {
					$sale_start_date = (int) $deals['start_time'];
					$timezone        = new DateTimeZone( WCCT_Common::wc_timezone_string() );
					if ( $sale_from instanceof WC_DateTime ) {
						$sale_from->setTimezone( $timezone );
						$sale_from->setTimestamp( $sale_start_date );
					} else {
						$sale_from = new WC_DateTime();
						$sale_from->setTimezone( $timezone );
						$sale_from->setTimestamp( $sale_start_date );
					}
				}
			}
		}

		return $sale_from;
	}

	/**
	 * @param $sale_to
	 * @param $product_global WC_Product
	 *
	 * @return WC_DateTime
	 * @throws Exception
	 */
	public function wcct_get_date_on_sale_to( $sale_to, $product_global ) {
		if ( $product_global instanceof WC_Product ) {
			$parent_id = WCCT_Core()->public->wcct_get_product_parent_id( $product_global );

			if ( WCCT_Core()->public->wcct_restrict_for_booking_oth( $parent_id ) ) {
				return $sale_to;
			}
			$data = WCCT_Core()->public->get_single_campaign_pro_data( $parent_id );

			if ( isset( $data['deals'] ) && is_array( $data['deals'] ) && count( $data['deals'] ) > 0 ) {
				$deals = $data['deals'];
				if ( isset( $deals['override'] ) && ( true === $deals['override'] ) ) {
					return $sale_to;
				}
				if ( isset( $deals['end_time'] ) && ( (int) $deals['end_time'] > 0 ) ) {
					$sale_end_date = (int) $deals['end_time'];
					$timezone      = new DateTimeZone( WCCT_Common::wc_timezone_string() );
					if ( $sale_to instanceof WC_DateTime ) {
						$sale_to->setTimezone( $timezone );
						$sale_to->setTimestamp( $sale_end_date );
					} else {
						$sale_to = new WC_DateTime();
						$sale_to->setTimezone( $timezone );
						$sale_to->setTimestamp( $sale_end_date );
					}
				}
			}
		}

		return $sale_to;
	}

	/**
	 * Hooked over `woocommerce_get_price`
	 * Callback to modify regular price of a product when event is set
	 *
	 * @param $price : Price to modify
	 * @param $product_global WC_Product
	 *
	 * @return mixed price
	 */
	public function wcct_handle_price_by_event( $price, $product_global ) {

		if ( ! $product_global instanceof WC_Product ) {
			return $price;
		}

		$is_skip = apply_filters( 'wcct_skip_discounts', false, $price, $product_global );

		if ( true === $is_skip ) {
			return $price;
		}

		//check if sale is applied on the product locally or by any campaigns
		//skip changing regular price
		do_action( 'wcct_before_get_sale_price', $price, $product_global, __FUNCTION__ );

		$sale_price = $product_global->get_sale_price();

		do_action( 'wcct_after_get_sale_price', $price, $product_global, __FUNCTION__ );

		if ( '' !== $sale_price ) {
			return $price;
		}

		if ( in_array( $product_global->get_type(), WCCT_Common::get_sale_compatible_league_product_types(), true ) ) {

			return $this->_wcct_common_handle_regular_price_by_event( $price, $product_global );
		} else {
			return $price;
		}
	}

	/**
	 * Hooked over `woocommerce_get_regular_price`
	 * Callback to modify regular price of a product when event is set
	 *
	 * @param $price : Price to modify
	 * @param $product_global WC_Product
	 *
	 * @return mixed price
	 */
	public function wcct_handle_regular_price_by_event( $price, $product_global ) {

		if ( ! $product_global instanceof WC_Product ) {
			return $price;
		}

		wcct_force_log( "Product id {$product_global->get_id()} : " . __FUNCTION__ . ' Before Price: ' . $price );

		if ( in_array( $product_global->get_type(), WCCT_Common::get_sale_compatible_league_product_types(), true ) ) {
			return $this->_wcct_common_handle_regular_price_by_event( $price, $product_global );
		} else {
			return $price;
		}
	}

	/**
	 * @hooked over `woocommerce_get_variation_prices_hash`
	 * Added current time as unique key so that the variation prices comes to display with the discounts added by finale but not by the object caching (by WordPress)
	 *
	 * @param array $price_hash
	 * @param WC_Product $product
	 * @param boolean $display
	 *
	 * @return array
	 */
	public function maybe_modify_variation_price_hash( $price_hash, $product, $display ) {
		if ( ! $product instanceof WC_Product ) {
			return $price_hash;
		}

		$campaign = WCCT_Core()->public->get_single_campaign_pro_data( $product->get_id() );
		if ( empty( $campaign ) || ! isset( $campaign['deals'] ) || empty( $campaign['deals'] ) ) {
			return $price_hash;
		}

		unset( $campaign['deals']['start_time'] );
		unset( $campaign['deals']['end_time'] );
		$hash = md5( maybe_serialize( $campaign['deals'] ) );

		if ( is_array( $price_hash ) ) {
			$price_hash[] = $hash;
		} elseif ( empty( $price_hash ) ) {
			$price_hash = array( $hash );
		} else {
			$price_hash = array( $price_hash, $hash );
		}

		return $price_hash;
	}

	public function wcct_change_price_ranges( $price_ranges, $product, $display ) {
		if ( ! $product instanceof WC_Product ) {
			return $price_ranges;
		}

		$prices         = array();
		$regular_prices = array();

		/**
		 * Using the product object to get the tax.
		 * If different variations will have different tax then it will display the incorrect product range
		 */
		if ( $product->is_taxable() && ! wc_prices_include_tax() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
			$check_tax = wc_get_price_including_tax( $product, array(
				'qty'   => 1,
				'price' => 10, // Passing price as 10 to get the tax
			) );

			$this->percentage = ( $check_tax - 10 ) * 10; // Now minus 10 to get the actual tax percentage
		}

		if ( is_array( $price_ranges ) && count( $price_ranges ) > 0 ) {
			foreach ( $price_ranges['regular_price'] as $key => $val ) {
				$temp = false;
				if ( $val == $price_ranges['price'][ $key ] ) {
					$temp = true;
				}
				$regular_prices[ $key ] = $this->_wcct_common_handle_regular_price_by_event( $val, $key, $display, $product->get_id() );

				if ( true === $temp ) {
					$price_ranges['price'][ $key ] = $regular_prices[ $key ];
				}
			}

			$prices = $this->wcct_set_variation_price( $price_ranges['price'], 'basic', $regular_prices, $product->get_id() );
		}

		$this->percentage = false;

		asort( $prices );
		asort( $regular_prices );

		$price_ranges = array(
			'price'         => $prices,
			'regular_price' => $regular_prices,
			'sale_price'    => $prices,
		);

		return $price_ranges;
	}

	public function wcct_maybe_mark_product_having_sale( $bool, $product ) {
		if ( ! $product instanceof WC_Product ) {
			return $bool;
		}

		if ( in_array( $product->get_type(), WCCT_Common::get_variable_league_product_types(), true ) ) {

			$price_ranges   = $product->get_variation_prices();
			$prices         = array();
			$regular_prices = array();

			if ( is_array( $price_ranges ) && count( $price_ranges ) > 0 ) {
				foreach ( $price_ranges['regular_price'] as $key => $val ) {
					$temp = false;
					if ( $val == $price_ranges['price'][ $key ] ) {
						$temp = true;
					}
					$regular_prices[ $key ] = $this->_wcct_common_handle_regular_price_by_event( $val, $key, true, $product->get_id() );

					if ( true === $temp ) {
						$price_ranges['price'][ $key ] = $regular_prices[ $key ];
					}
				}

				$prices = $this->wcct_set_variation_price( $price_ranges['price'], 'basic', $regular_prices, $product->get_id() );
			}
			asort( $prices );
			asort( $regular_prices );

			$price_ranges = array(
				'price'         => $prices,
				'regular_price' => $regular_prices,
				'sale_price'    => $prices,
			);

			if ( is_array( $price_ranges['regular_price'] ) && ! empty( $price_ranges['regular_price'] ) ) {
				$bool = false;
				foreach ( $price_ranges['regular_price'] as $id => $price ) {
					if ( $price_ranges['sale_price'][ $id ] != $price && $price_ranges['sale_price'][ $id ] == $price_ranges['price'][ $id ] ) {
						$bool = true;
					}
				}
			}

			return $bool;
		} else {
			$price     = $product->get_price();
			$reg_price = $product->get_regular_price();

			if ( '' !== (string) $price && $reg_price > $price ) {
				$bool = true;
			}
		}

		return $bool;
	}


	public function maybe_flag_running_calculations() {
		$this->is_wc_calculating = true;
	}

	public function maybe_unflag_running_calculations() {
		$this->is_wc_calculating = false;
	}

	public function maybe_remove_regular_price_hooks( $price, $product_global, $function ) {
		remove_filter( 'woocommerce_product_get_price', array( $this, $function ), 900, 2 );
		remove_filter( 'woocommerce_product_variation_get_price', array( $this, $function ), 999, 2 );
	}

	public function maybe_restore_regular_price_hooks( $price, $product_global, $function ) {
		add_filter( 'woocommerce_product_get_price', array( $this, $function ), 900, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( $this, $function ), 999, 2 );
	}

}

if ( class_exists( 'WCCT_Core' ) ) {
	WCCT_Core::register( 'discount', 'WCCT_discount' );
}
