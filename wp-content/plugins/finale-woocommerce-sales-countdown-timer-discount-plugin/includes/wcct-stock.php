<?php

class WCCT_stock {

	public static $_instance = null;

	public $hooks_attached = false;

	public function __construct() {

		/**
		 * Attaching all the necessary hooks and filter to modify the stock and stock states
		 */
		$this->attach_hooks();

		/**
		 * We need to update product meta for the first occurrence.
		 * In order to handle campaign inventory settings for running over out of stock products or not
		 * We are trying to do this over data setup completed , it prevents us from getting into infinite loop
		 */
		add_action( 'wcct_data_setup_completed', array( $this, 'wcct_maybe_save_product_stock_state' ), 10, 2 );

		/**
		 * Unhooking all our stock filters that we do not need to save our changes in the database
		 * WooCommerce reduce stock after order/payment successful and we are escaping our changes.
		 * When the action is completed , reattaching all the stock filters to the execution.
		 */
		add_filter( 'woocommerce_can_reduce_order_stock', array( $this, 'detach_hooks_after_payment' ), 1 );
		add_action( 'woocommerce_reduce_order_stock', array( $this, 'attach_hooks' ), 1 );

		/**
		 * #fallback , here we set variable meta by iterating its variations. It's a query that could break if a system have large number of variations.
		 * So we cannot run it over grids.
		 * now to handle stock further for the product we need to update meta before it gets added to the cart or the form come sin to chose variations.
		 * We use this hook to add the same meta for the variations that help us decide whether we want to apply campaign over out of stock products.
		 */
		add_action( 'woocommerce_before_variations_form', array( $this, 'wcct_maybe_save_product_stock_state_for_variations' ) );
	}

	/**
	 * Attach all the necessary hooks for the inventory operations.
	 */
	public function attach_hooks() {
		global $woocommerce;

		if ( true === $this->hooks_attached ) {
			return;
		}
		if ( version_compare( $woocommerce->version, 3.0, '>=' ) ) {

			add_filter( 'woocommerce_product_get_manage_stock', array( $this, 'wcct_modify_manage_stock' ), 10, 2 );
			add_filter( 'woocommerce_product_variation_get_manage_stock', array( $this, 'wcct_modify_manage_stock' ), 10, 2 );

			add_filter( 'woocommerce_product_get_stock_quantity', array( $this, 'wcct_manage_stock_qty' ), 10, 2 );
			add_filter( 'woocommerce_product_variation_get_stock_quantity', array( $this, 'wcct_manage_stock_qty' ), 10, 2 );

			add_filter( 'woocommerce_product_is_in_stock', array( $this, 'wcct_woocommerce_product_is_in_stock' ), 10, 2 );
			add_action( 'woocommerce_variation_is_in_stock', array( $this, 'wcct_woocommerce_product_is_in_stock' ), 10 );

		} else {
			add_filter( 'woocommerce_get_stock_quantity', array( $this, 'wcct_manage_stock_qty' ), 10, 2 );
			add_filter( 'woocommerce_variation_get_stock_quantity', array( $this, 'wcct_manage_stock_qty' ), 10, 2 );

			add_action( 'woocommerce_variation_is_in_stock', array( $this, 'wcct_woocommerce_product_is_in_stock' ), 10 );
			add_filter( 'woocommerce_product_is_in_stock', array( $this, 'wcct_woocommerce_product_is_in_stock' ), 10 );
		}

		add_filter( 'woocommerce_product_backorders_allowed', array( $this, 'wcct_backorders_allowed' ), 10, 3 );
	}

	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * @hooked over woocommerce_product_is_in_stock | woocommerce_variation_is_in_stock
	 *
	 * @param Boolean $status Current Status of Product
	 * @param string|WC_Product|Int $product_obj
	 *
	 * @return bool
	 */
	public function wcct_woocommerce_product_is_in_stock( $status, $product_obj = '' ) {
		/** checking on cart */ global $product;

		if ( ! is_object( $product_obj ) && $product_obj == '' ) {
			/** WC 2.6 or older */
			$my_product = $product;
			if ( WCCT_Core()->cart->cart_product_id != 0 ) {
				$my_product_id = WCCT_Core()->cart->cart_product_id;
				$my_product    = WCCT_Core()->public->product_obj[ $my_product_id ];
			} else {
				if ( isset( $_REQUEST['add-to-cart'] ) ) {
					$my_product_id                                     = $_REQUEST['add-to-cart'];
					$my_product                                        = wc_get_product( $my_product_id );
					WCCT_Core()->public->product_obj[ $my_product_id ] = $my_product;
				} elseif ( isset( $_REQUEST['product_id'] ) ) {
					$my_product_id                                     = $_REQUEST['product_id'];
					$my_product                                        = wc_get_product( $my_product_id );
					WCCT_Core()->public->product_obj[ $my_product_id ] = $my_product;
				} elseif ( is_object( $my_product ) ) {

					$my_product_id                                     = $my_product->get_id();
					WCCT_Core()->public->product_obj[ $my_product_id ] = $my_product;
				} else {
					$my_product_id = 0;
				}
			}
		} else {
			/** i.e. WC 3.0 */
			/** return if it is a bundle */
			$product_type = $product_obj->get_type();
			if ( 'yith_bundle' === $product_type ) {
				return $status;
			}
			$my_product_id = WCCT_Core()->public->wcct_get_product_parent_id( $product_obj ); // parent id in case of variable
			$my_product    = WCCT_Core()->public->wcct_get_product_obj( $my_product_id );
		}

		if ( ! $my_product instanceof WC_Product ) {
			return $status;
		}

		if ( $my_product_id > 0 ) {

			if ( WCCT_Core()->public->wcct_restrict_for_booking_oth( $my_product_id ) ) {
				return $status;
			}
		} else {
			return $status;
		}

		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $my_product_id );
		$actions     = WCCT_Core()->public->wcct_genrate_actions( $my_product_id );

		/** checking if any action for the stock is on and needed to be apply */
		if ( isset( $actions['stock'] ) ) {

			if ( 'in-stock' == $actions['stock'] ) {
				$status = true;

			}
			if ( 'out-of-stock' == $actions['stock'] ) {
				$status = false;

			}
		} else {

			/**  if status is out of stock then checking for the inventory settings to make it true
			 *   This makes the products to be purchasable even if they are out of stock if user opted in
			 */

			if ( false === $status ) {
				$get_cache_instance = XL_Cache::get_instance();
				if ( ( $my_product && $my_product->get_type() == 'simple' ) || ( $my_product && $my_product->get_type() == 'subscription' ) ) {

					if ( isset( $single_data['goals'] ) && is_array( $single_data['goals'] ) && count( $single_data['goals'] ) > 0 ) {
						$goals = $single_data['goals'];

						if ( 'custom' === $goals['type'] ) {

							if ( 'yes' === $goals['allow_backorder'] ) {
								$status = true;
							} else {

								$wcct_campaign_event_product_stock_state = "_wcct_goaldeal_stock_{$goals['campaign_id']}_{$goals['start_timestamp']}_{$goals['end_timestamp']}";

								$get_product_state_meta = $get_cache_instance->get_cache( $wcct_campaign_event_product_stock_state, 'finale' );
								if ( false === $get_product_state_meta ) {
									$get_product_state_meta = get_post_meta( $my_product_id, $wcct_campaign_event_product_stock_state, true );
									$get_cache_instance->set_cache( $wcct_campaign_event_product_stock_state, $get_product_state_meta, 'finale' );
								}

								if ( '' !== $get_product_state_meta ) {
									$status = true;
								}
							}
						}
					}
				} elseif ( ( $my_product && 'variable' === $my_product->get_type() ) || ( 'variable-subscription' === $my_product->get_type() && $my_product ) ) {
					if ( isset( $single_data['goals'] ) && is_array( $single_data['goals'] ) && count( $single_data['goals'] ) > 0 ) {
						$goals = $single_data['goals'];
						if ( isset( $goals['start_timestamp'] ) ) {
							if ( 'custom' === $goals['type'] ) {

								if ( 'yes' === $goals['allow_backorder'] ) {
									$status = true;
								} else {
									if ( $product_obj instanceof WC_Product && $product_obj->is_type( 'variation' ) ) {
										$wcct_campaign_event_product_stock_state = "_wcct_goaldeal_stock_{$goals['campaign_id']}_{$goals['start_timestamp']}_{$goals['end_timestamp']}";

										$get_product_state_meta = $get_cache_instance->get_cache( $wcct_campaign_event_product_stock_state, 'finale' );
										if ( false === $get_product_state_meta ) {
											$get_product_state_meta = get_post_meta( $my_product->get_id(), $wcct_campaign_event_product_stock_state, true );
											$get_cache_instance->set_cache( $wcct_campaign_event_product_stock_state, $get_product_state_meta, 'finale' );
										}

										if ( ! empty( $get_product_state_meta ) ) {
											$get_json_status = json_decode( $get_product_state_meta, true );
											if ( $get_json_status && is_array( $get_json_status ) && in_array( $product_obj->get_id(), $get_json_status ) ) {
												$status = true;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$echo = 'no';

		if ( $status ) {
			$echo = 'yes';
		}
		wcct_force_log( "Product id {$my_product_id} \n\n wcct_woocommerce_product_is_in_stock \n\r" . $echo );
		WCCT_Core()->cart->cart_product_id = 0;

		return $status;
	}

	/**
	 * @hooked over `woocommerce_product_backorders_allowed`
	 * Tells WooCommerce to disallow backorder if Finale Inventory Campaign is on.
	 * It prevents users to purchase additional (infinite) quantity within the campaign.
	 *
	 * @param Boolean $status
	 * @param Integer $pid
	 * @param WC_Product $product
	 *
	 * @return bool
	 */
	public function wcct_backorders_allowed( $status, $pid, $product = null ) {

		if ( null === $product ) {

			$product = WCCT_Core()->public->wcct_get_product_obj( $pid );
		}
		if ( WCCT_Core()->public->wcct_restrict_for_booking_oth( $pid ) ) {
			return $status;
		}

		$pid         = WCCT_Core()->public->wcct_get_product_parent_id( $product );
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $pid );

		if ( $product->get_type() == 'simple' || $product->get_type() == 'subscription' ) {
			if ( isset( $single_data['goals'] ) && is_array( $single_data['goals'] ) && count( $single_data['goals'] ) > 0 ) {
				$goals = $single_data['goals'];

				if ( true === $status && 'custom' === $goals['type'] ) {

					$status = false;
				}
			}
		}

		if ( $product->get_type() == 'variation' || $product->get_type() == 'subscription_variation' ) {
			if ( isset( $single_data['goals'] ) && is_array( $single_data['goals'] ) && count( $single_data['goals'] ) > 0 ) {

				$goals = $single_data['goals'];
				if ( isset( $goals['start_timestamp'] ) && true === $status && 'custom' === $goals['type'] ) {

					$status = false;
				}
			}
		}

		$echo = 'no';
		if ( $status ) {
			$echo = 'yes';
		}

		wcct_force_log( "Product id {$pid} \n\n " . __FUNCTION__ . " \n\r" . $echo );

		return $status;
	}

	/**
	 * Hooked over `woocommerce_get_stock_quantity` | `woocommerce_product_variation_get_stock_quantity` | `woocommerce_product_get_stock_quantity`
	 * Modify product inventory on the fly for the product based on the running inventory campaigns.
	 *
	 * @param Int $qty Quantity of the Product
	 * @param WC_Product $product
	 *
	 * @return bool
	 */
	public function wcct_manage_stock_qty( $qty, $product ) {
		$product_id  = WCCT_Core()->public->wcct_get_product_parent_id( $product );
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $product_id );

		if ( empty( $single_data ) ) {
			return $qty;
		}

		$available_qty   = false;
		$get_goal_object = WCCT_Core()->public->wcct_get_goal_object( $single_data['goals'], $product_id );

		if ( ! empty( $get_goal_object ) ) {
			$available_qty = $get_goal_object['quantity'] - $get_goal_object['sold_out'];
		}
		if ( false !== $available_qty && 'custom' === $get_goal_object['type'] ) {
			$qty = $available_qty;
		}
		if ( false !== $available_qty && 'same' === $get_goal_object['type'] && ( isset( $get_goal_object['is_event_modified'] ) && 'yes' === $get_goal_object['is_event_modified'] ) ) {

			$qty = $available_qty;
		}

		return $qty;
	}

	/**
	 * @hooked over `woocommerce_product_get_manage_stock` | `woocommerce_product_get_manage_stock`
	 *
	 * @param boolean $bool is_managing product stock
	 * @param WC_Product $product product Object
	 *
	 * @return bool modified stock management status
	 * Specifically for WC 3.0 or greater
	 */
	public function wcct_modify_manage_stock( $bool, $product ) {
		$product_id  = WCCT_Core()->public->wcct_get_product_parent_id( $product );
		$single_data = WCCT_Core()->public->get_single_campaign_pro_data( $product_id );

		if ( empty( $single_data ) ) {
			return $bool;
		}

		$available_qty   = false;
		$get_goal_object = WCCT_Core()->public->wcct_get_goal_object( $single_data['goals'], $product_id );

		if ( ! empty( $get_goal_object ) ) {
			$available_qty = $get_goal_object['quantity'] - $get_goal_object['sold_out'];
		}

		if ( false !== $available_qty && 'custom' === $get_goal_object['type'] ) {
			$bool = true;
		}

		return $bool;
	}

	/**
	 * Inventory Goal tiered discounting check.
	 * validate each given rule and provide a new deal stock if any rule matches.
	 *
	 * @param $product_obj
	 * @param $deal_custom_units
	 * @param $deal_custom_advanced
	 *
	 * @return int|mixed
	 */
	public function wcct_get_custom_inventory_goal_by_conditions( $product_obj, $deal_custom_units, $deal_custom_advanced ) {
		$unit = WCCT_Common::get_total_stock( $product_obj );

		if ( is_array( $deal_custom_advanced ) && count( $deal_custom_advanced ) > 0 ) {
			foreach ( $deal_custom_advanced as $rule ) {

				$default = array(
					'range_from'  => 0,
					'range_to'    => '0',
					'range_value' => 0,
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
				if ( $config['range_from'] <= $unit && $config['range_to'] >= $unit ) {
					return $config['range_value'];
				}
			}
		}

		return 0;
	}

	/**
	 * This Function sets the custom inventory by the dynamic range of total quantity set in the campaign settings
	 * Sets meta in the product so that it behave like a basic inventory further.
	 *
	 * @param WC_Product $product_obj
	 * @param Integer $campaign_id
	 * @param $goal
	 * @param $campaign_obj
	 *
	 * @return int|mixed
	 */
	public function wcct_get_custom_inventory_goal_by_range( $product_obj, $campaign_id, $goal, $campaign_obj ) {
		$from_unit  = (int) $campaign_obj['deal_range_from_custom_units'];
		$to_unit    = (int) $campaign_obj['deal_range_to_custom_units'];
		$start_time = (int) $goal['start_timestamp'];
		$end_time   = (int) $goal['end_timestamp'];

		if ( $from_unit >= 0 && $to_unit > 0 ) {
			$meta_key = "_wcct_inventory_range_{$campaign_id}_from_{$start_time}_to_{$end_time}";

			$get_cache_instance = XL_Cache::get_instance();
			$key                = 'wcct_custom_inventory_goal_meta' . $product_obj->get_id() . '_' . $campaign_id;
			$quantity           = $get_cache_instance->get_cache( $key, 'finale' );
			if ( false === $quantity ) {
				$quantity = get_post_meta( $product_obj->get_id(), $meta_key, true );
				$get_cache_instance->set_cache( $key, $quantity, 'finale' );
			}

			/** swapping if we cannot process the units */
			if ( $from_unit > $to_unit ) {
				$temp      = $from_unit;
				$from_unit = $to_unit;
				$to_unit   = $temp;
			}

			if ( $quantity > 0 && ( $from_unit <= (int) $quantity && (int) $quantity <= $to_unit ) ) {
				return (int) $quantity;
			}

			$quantity = (int) rand( $from_unit, $to_unit );
			update_post_meta( $product_obj->get_id(), $meta_key, $quantity );

			return $quantity;
		}

		return 0;
	}

	/**
	 * @hooked over `wcct_data_setup_completed`
	 * Just after data get setup, We ae good to check product current stock state and record that state for further use in managing inventory.
	 *
	 * @param $data
	 * @param $id
	 */
	public function wcct_maybe_save_product_stock_state( $data, $id ) {

		if ( $id != '0' && isset( $data['goals'] ) && is_array( $data['goals'] ) && 'custom' === $data['goals']['type'] ) {
			$wcct_campaign_event_product_stock_state = "_wcct_goaldeal_stock_{$data['goals']['campaign_id']}_{$data['goals']['start_timestamp']}_{$data['goals']['end_timestamp']}";

			$product_main_id = WCCT_Core()->public->wcct_get_product_parent_id( $id );
			$product         = WCCT_Core()->public->wcct_get_product_obj( $product_main_id );

			/** Return if not a valid product instance */
			if ( ! $product instanceof WC_Product ) {
				return;
			}

			$get_cache_instance = XL_Cache::get_instance();
			$get_meta           = $get_cache_instance->get_cache( $wcct_campaign_event_product_stock_state, 'finale' );

			if ( false === $get_meta ) {
				$get_meta = get_post_meta( $product_main_id, $wcct_campaign_event_product_stock_state, true );
				$get_cache_instance->set_cache( $wcct_campaign_event_product_stock_state, $get_meta, 'finale' );
			}

			$this->detach_hooks();
			/** if meta doesn't exist */
			if ( empty( $get_meta ) ) {
				if ( $product->is_in_stock() && $product->is_type( 'simple' ) ) {
					update_post_meta( $product_main_id, $wcct_campaign_event_product_stock_state, 'yes' );
				} elseif ( $product->is_type( 'variable' ) ) {
					if ( is_singular( 'product' ) ) {
						$get_all_variations = $product->get_available_variations();
						if ( $get_all_variations && is_array( $get_all_variations ) && count( $get_all_variations ) > 0 ) {
							$all_variation_state = array();
							foreach ( $get_all_variations as $variation ) {
								if ( true === $variation['is_in_stock'] ) {
									array_push( $all_variation_state, $variation['variation_id'] );
								}
							}
							if ( $all_variation_state && is_array( $all_variation_state ) && count( $all_variation_state ) > 0 ) {
								update_post_meta( $product_main_id, $wcct_campaign_event_product_stock_state, wp_json_encode( $all_variation_state ) );
							}
						}
					}
				}
			}

			$this->attach_hooks();
		}

	}

	/**
	 * Detach All the hooks for the inventory
	 */
	public function detach_hooks() {
		global $woocommerce;
		if ( version_compare( $woocommerce->version, 3.0, '>=' ) ) {
			remove_filter( 'woocommerce_product_get_manage_stock', array( $this, 'wcct_modify_manage_stock' ), 10, 2 );
			remove_filter( 'woocommerce_product_variation_get_manage_stock', array( $this, 'wcct_modify_manage_stock' ), 10, 2 );

			remove_filter( 'woocommerce_product_get_stock_quantity', array( $this, 'wcct_manage_stock_qty' ), 10, 2 );
			remove_filter( 'woocommerce_product_variation_get_stock_quantity', array( $this, 'wcct_manage_stock_qty' ), 10, 2 );

			remove_filter( 'woocommerce_product_is_in_stock', array( $this, 'wcct_woocommerce_product_is_in_stock' ), 10, 2 );
			remove_action( 'woocommerce_variation_is_in_stock', array( $this, 'wcct_woocommerce_product_is_in_stock' ), 10 );

		} else {
			remove_filter( 'woocommerce_get_stock_quantity', array( $this, 'wcct_manage_stock_qty' ), 10, 2 );
			remove_filter( 'woocommerce_variation_get_stock_quantity', array( $this, 'wcct_manage_stock_qty' ), 10, 2 );

			remove_action( 'woocommerce_variation_is_in_stock', array( $this, 'wcct_woocommerce_product_is_in_stock' ), 10 );
			remove_filter( 'woocommerce_product_is_in_stock', array( $this, 'wcct_woocommerce_product_is_in_stock' ), 10 );
		}

		remove_filter( 'woocommerce_product_backorders_allowed', array( $this, 'wcct_backorders_allowed' ), 10, 3 );
		$this->hooks_attached = false;
	}

	/**
	 *
	 * @hooked over `woocommerce_before_variations_form`
	 * Just after data get setup, We ae good to check product current stock state and record that state for further use in managing inventory.
	 */

	public function wcct_maybe_save_product_stock_state_for_variations() {
		global $product;

		if ( is_singular( 'product' ) ) {
			return;
		}
		if ( $product instanceof WC_Product && $product->is_type( 'variable' ) ) {
			$data = WCCT_Core()->public->get_single_campaign_pro_data( $product->get_id() );

			if ( isset( $data['goals'] ) && is_array( $data['goals'] ) && 'custom' === $data['goals']['type'] ) {
				$wcct_campaign_event_product_stock_state = "_wcct_goaldeal_stock_{$data['goals']['campaign_id']}_{$data['goals']['start_timestamp']}_{$data['goals']['end_timestamp']}";

				$product_main_id = $product->get_id();

				$get_cache_instance = XL_Cache::get_instance();
				$get_meta           = $get_cache_instance->get_cache( $wcct_campaign_event_product_stock_state, 'finale' );
				if ( false === $get_meta ) {
					$get_meta = get_post_meta( $product_main_id, $wcct_campaign_event_product_stock_state, true );
					$get_cache_instance->set_cache( $wcct_campaign_event_product_stock_state, $get_meta, 'finale' );
				}

				$this->detach_hooks();
				/** if meta doesn't exist */
				if ( empty( $get_meta ) ) {

					if ( is_singular( 'product' ) ) {
						$get_all_variations = $product->get_available_variations();

						if ( $get_all_variations && is_array( $get_all_variations ) && count( $get_all_variations ) > 0 ) {

							$all_variation_state = array();

							foreach ( $get_all_variations as $variation ) {
								if ( true === $variation['is_in_stock'] ) {
									array_push( $all_variation_state, $variation['variation_id'] );
								}
							}

							if ( $all_variation_state && is_array( $all_variation_state ) && count( $all_variation_state ) > 0 ) {
								update_post_meta( $product_main_id, $wcct_campaign_event_product_stock_state, wp_json_encode( $all_variation_state ) );

							}
						}
					}
				}

				$this->attach_hooks();
			}
		}
	}

	public function detach_hooks_after_payment( $bool ) {
		$this->detach_hooks();

		return $bool;
	}
}

if ( class_exists( 'WCCT_stock' ) ) {
	WCCT_Core::register( 'stock', 'WCCT_stock' );
}
