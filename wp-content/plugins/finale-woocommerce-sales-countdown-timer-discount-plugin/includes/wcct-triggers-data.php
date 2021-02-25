<?php

class WCCT_Triggers_Data {

	public $wcct_triggers_data = null;

	/**
	 * Contains all triggers data
	 * @var array
	 */
	public $wcct_trigger_data = array();
	protected $wcct_product_metadata = array();

	/**
	 * WCCT_Triggers_Data constructor.
	 * Construct to call hooks and setting up properties
	 */
	public function __construct() {
		$this->cart_default_array       = array(
			'skin'         => 'square_ghost',
			'bg_color'     => '#c46e3c',
			'label_color'  => '#8224e3',
			'timer_font'   => '15',
			'label_font'   => '13',
			'display'      => 'Sale ends in {{countdown_timer}}',
			'label_days'   => 'D',
			'label_hrs'    => 'H',
			'label_mins'   => 'M',
			'label_secs'   => 'S',
			'border_width' => '1',
			'border_color' => '#444444',
			'border_style' => 'solid',
		);
		$this->grid_timer_default_array = array(
			'position'     => '',
			'skin'         => 'default',
			'bg_color'     => '#444444',
			'label_color'  => '#ffffff',
			'timer_font'   => '15',
			'label_font'   => '13',
			'display'      => 'Sale ends in {{countdown_timer}}',
			'label_days'   => 'days',
			'label_hrs'    => 'hrs',
			'label_mins'   => 'mins',
			'label_secs'   => 'secs',
			'border_width' => '1',
			'border_color' => '#444444',
			'border_style' => 'solid',
		);
		$this->grid_bar_default_array   = array(
			'skin'         => 'stripe_animate',
			'edge'         => 'rounded',
			'height'       => '16',
			'bg_color'     => '#dddddd',
			'active_color' => '#ee303c',
			'display'      => '{{counter_bar}} {{sold_units}} units sold out of {{total_units}}',
			'border_width' => '0',
			'border_color' => '#444444',
			'border_style' => 'none',
		);
	}

	/**
	 * Hooked over `wp`
	 * Checks if single product page
	 * Checks and prepare triggers data to be called in core file
	 */
	public function wcct_maybe_process_data( $ID = 0, $return_key = false, $skip_rules = false ) {
		global $wpdb;

		$this->wcct_trigger_data = array();
		$args                    = array(
			'post_type'        => WCCT_Common::get_campaign_post_type_slug(),
			'post_status'      => 'publish',
			'nopaging'         => true,
			'meta_key'         => '_wcct_campaign_menu_order',
			'orderby'          => 'meta_value_num',
			'order'            => 'ASC',
			'fields'           => 'ids',
			'suppress_filters' => false,   //WPML Compatibility
		);

		$xl_transient_obj = XL_Transient::get_instance();
		$xl_cache_obj     = XL_Cache::get_instance();

		$key = 'wcct_campaign_query';

		// handling for WPML
		if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE !== '' ) {
			$key .= '_' . ICL_LANGUAGE_CODE;
		}

		//Handling with PolyLang
		if ( function_exists( 'pll_current_language' ) ) {
			$current_lang = pll_current_language();
			$args['lang'] = $current_lang;
			$key          .= '_' . $current_lang;
		}

		$contents = array();
		do_action( 'wcct_before_query', $ID );

		/**
		 * Setting xl cache and transient for Finale campaign query
		 */
		$cache_data = $xl_cache_obj->get_cache( $key, 'finale' );
		if ( false !== $cache_data ) {
			$contents = $cache_data;
		} else {
			$transient_data = $xl_transient_obj->get_transient( $key, 'finale' );
			if ( false !== $transient_data ) {
				$contents = $transient_data;
			} else {
				$query_result = new WP_Query( $args );
				if ( $query_result instanceof WP_Query && $query_result->have_posts() ) {
					$contents = $query_result->posts;
					$xl_transient_obj->set_transient( $key, $query_result->posts, 7200, 'finale' );
				}
			}
			$xl_cache_obj->set_cache( $key, $contents, 'finale' );
		}

		do_action( 'wcct_after_query', $ID );

		if ( is_array( $contents ) && count( $contents ) > 0 ) {

			/** Check here is campaign cookie exist and valid */
			$campaign_check = apply_filters( 'wcct_cookie_based_campaign', false, $contents, $ID );

			if ( false === $campaign_check ) {
				foreach ( $contents as $content_single ) {
					/**
					 * post instance extra checking added as some plugins may modify wp_query args on pre_get_posts filter hook
					 */
					$content_id  = ( $content_single instanceof WP_Post && is_object( $content_single ) ) ? $content_single->ID : $content_single;
					$slug        = '';
					$rule_result = WCCT_Common::match_groups( $content_id, $ID );

					if ( $skip_rules || true === $rule_result ) {
						$cache_key = 'wcct_countdown_post_meta_' . $content_id;

						/**
						 * Setting xl cache and transient for Finale single campaign meta
						 */
						$cache_data = $xl_cache_obj->get_cache( $cache_key, 'finale' );
						if ( false !== $cache_data ) {
							$parseObj = $cache_data;
						} else {
							$transient_data = $xl_transient_obj->get_transient( $cache_key, 'finale' );

							if ( false !== $transient_data ) {
								$parseObj = $transient_data;
							} else {
								$get_product_wcct_meta = get_post_meta( $content_id );
								$product_meta          = WCCT_Common::get_parsed_query_results_meta( $get_product_wcct_meta );
								$parseObj              = wp_parse_args( $product_meta, $this->parse_default_args_by_trigger( $product_meta, $slug ) );
								$xl_transient_obj->set_transient( $cache_key, $parseObj, 7200, 'finale' );
							}
							$xl_cache_obj->set_cache( $cache_key, $parseObj, 'finale' );
						}

						if ( ! $parseObj ) {
							continue;
						}

						$get_parsed_data = $this->parse_key_value( $parseObj, $slug, 'product' );
						if ( true === WCCT_Core()->appearance->is_sticky_header_call ) {
							if ( '1' == $get_parsed_data['location_timer_show_sticky_header'] || '1' == $get_parsed_data['location_timer_show_sticky_footer'] ) {
								$this->wcct_trigger_data[ $content_id ] = $get_parsed_data;
							}
						} else {
							$this->wcct_trigger_data[ $content_id ] = $get_parsed_data;
						}
					}
				}
			}
		}

		$this->wcct_trigger_data = apply_filters( 'wcct_trigger_data', $this->wcct_trigger_data );
		if ( is_array( $this->wcct_trigger_data ) && count( $this->wcct_trigger_data ) > 0 ) {
			return $this->wcct_triggers_public_data( $this->wcct_trigger_data, $return_key, $ID );
		} else {
			return array();
		}
	}

	public function parse_default_args_by_trigger( $data, $trigger ) {
		$field_option_data = WCCT_Common::get_default_settings();

		foreach ( $field_option_data as $slug => $value ) {
			if ( strpos( $slug, '_wcct_' ) !== false ) {
				$data[ $slug ] = $value;
			}
		}

		return $data;
	}

	/**
	 * Parse and prepare data for single trigger
	 *
	 * @param $data : Array Options data
	 * @param $trigger String Trigger slug
	 * @param string $mode options|product
	 *
	 * @return array
	 */
	public function parse_key_value( $data, $trigger, $mode = 'options' ) {
		$trigger_data = array();
		$prepare_key  = '_wcct_';
		foreach ( $data as $key => $field_val ) {
			if ( strpos( $key, $prepare_key ) === false ) {
				continue;
			}
			$key                  = str_replace( $prepare_key, '', $key );
			$trigger_data[ $key ] = apply_filters( 'wcct_filter_values', maybe_unserialize( $field_val ), $key, $trigger );
		}

		return apply_filters( 'wcct_filter_trigger_data', $trigger_data, $trigger );
	}

	public function wcct_triggers_public_data( $meta_data, $return_key = false, $product_id = 0 ) {
		$campaign_meta          = $uniqueArr = $show_on_cart = $single_timer = $custom_text = $grid_timer = $goals = $coupons = $sticky_header = $sticky_footer = $grid_bar = $single_bar = $deals = $during_campaign = $after_campaign = $during_deal_campaign_final = $during_goal_campaign_final = $after_campaign_final = $add_to_cart_text = $custom_css = array();
		$expiry_text            = array();
		$deal_end_time          = $deal_start_time = $goal_start_time = $goal_end_time = 0;
		$menu_order_campaign_id = $menu_order_start_time = $menu_order_end_time = 0;
		$regular_prices         = array();
		$expired_camp           = array();
		$scheduled_camps        = array();
		$events_camp            = array();
		$running_camp           = array();
		$timer_labels           = array();
		$finale_goal            = array();

		if ( is_array( $meta_data ) && count( $meta_data ) ) {
			foreach ( $meta_data as $campaign_id => $val ) {

				$val = apply_filters( 'wcct_campaign_meta_data_before_trigger', $val, $campaign_id );

				if ( 'fixed_date' === $val['campaign_type'] || 'recurring' === $val['campaign_type'] ) {
					$flag = true;
				} else {
					$flag = apply_filters( 'wcct_continue_external_campaign', false, $campaign_id, $val );
				}
				if ( false === $flag ) {
					continue;
				}

				$threshold_reach_out   = false;
				$j                     = $campaign_id;
				$start_end             = WCCT_Common::start_end_timestamp( $val );
				$start_end['campaign'] = $j;

				/** Hook to modify campaign start end datetime */
				$start_end = apply_filters( 'wcct_cookie_campaign_timestamp', $start_end, $campaign_id, $val );

				$start_date_timestamp = $start_end['start_date_timestamp'];
				$end_date_timestamp   = $start_end['end_date_timestamp'];
				$today_date           = $start_end['todayDate'];
				$timer_labels[ $j ]   = array();

				if ( isset( $val['misc_timer_label_days'] ) && '' !== $val['misc_timer_label_days'] ) {
					$timer_labels[ $j ]['label_days'] = $val['misc_timer_label_days'];
				}
				if ( isset( $val['misc_timer_label_hrs'] ) && '' !== $val['misc_timer_label_hrs'] ) {
					$timer_labels[ $j ]['label_hrs'] = $val['misc_timer_label_hrs'];
				}
				if ( isset( $val['misc_timer_label_mins'] ) && '' !== $val['misc_timer_label_mins'] ) {
					$timer_labels[ $j ]['label_mins'] = $val['misc_timer_label_mins'];
				}
				if ( isset( $val['misc_timer_label_secs'] ) && '' !== $val['misc_timer_label_secs'] ) {
					$timer_labels[ $j ]['label_secs'] = $val['misc_timer_label_secs'];
				}

				if ( $start_date_timestamp > 0 && $end_date_timestamp > 0 && $today_date > 0 ) {
					$campaignType        = array(
						'type'            => isset( $val['campaign_type'] ) ? $val['campaign_type'] : '',
						'start_timestamp' => $start_date_timestamp,
						'end_timestamp'   => $end_date_timestamp,
					);
					$campaign_meta[ $j ] = array(
						'campaign_id' => $campaign_id,
						'type'        => isset( $val['campaign_type'] ) ? $val['campaign_type'] : '',
						'start_time'  => $start_date_timestamp,
						'end_time'    => $end_date_timestamp,
					);

					if ( $today_date >= $start_date_timestamp ) {

						/**  Entering to the current condition means a campaign is not scheduled and crossed the starting time */

						if ( $today_date < $end_date_timestamp ) {
							/** Entering here means campaign end time is still yet to come, hence it is a running campaign.*/

							$uniqueArr[ $j ]['campaign'] = $campaignType;

							/** Deal */
							if ( $product_id != '0' && isset( $val['deal_enable_price_discount'] ) && $val['deal_enable_price_discount'] == '1' ) {
								if ( $deal_start_time == 0 ) {
									$deal_start_time        = $start_date_timestamp;
									$deal_end_time          = $end_date_timestamp;
									$menu_order_campaign_id = $j;
									$menu_order_start_time  = $deal_start_time;
									$menu_order_end_time    = $deal_end_time;
								}
								$uniqueArr[ $j ]['deals'] = apply_filters( 'wcct_campaign_deals_data', array(
									'mode'                 => isset( $val['deal_mode'] ) ? $val['deal_mode'] : '',
									'type'                 => $val['deal_type'],
									'deal_amount'          => $val['deal_amount'],
									'deal_amount_advanced' => isset( $val['discount_custom_advanced'] ) ? $val['discount_custom_advanced'] : '',

									'start_time'       => $deal_start_time,
									'end_time'         => $end_date_timestamp,
									'campaign_type'    => $campaignType['type'],
									'override'         => ( isset( $val['deal_override_price_discount'] ) && 'on' === $val['deal_override_price_discount'] ) ? true : false,
									'campaign_id'      => $j,
									'event_overridden' => false,
								), $val );
								$deals[ $j ]              = $uniqueArr[ $j ]['deals'];
							}

							/** Goal */
							if ( $product_id != '0' && isset( $val['deal_enable_goal'] ) && $val['deal_enable_goal'] == '1' ) {
								if ( empty( $finale_goal ) ) {
									$allow_backorder = 'no';
									if ( isset( $val['deal_units'] ) ) {
										if ( 'custom' === $val['deal_units'] ) {
											if ( isset( $val['deal_custom_units_allow_backorder'] ) && 'yes' === $val['deal_custom_units_allow_backorder'] ) {
												$allow_backorder = 'yes';
											}
										}

										$uniqueArr[ $j ]['goals'] = array(
											'threshold'         => $val['deal_threshold_units'],
											'type'              => $val['deal_units'],
											'default_sold_out'  => 0,
											'deal_custom_units' => $val['deal_custom_units'],
											'is_custom'         => 'custom' === $val['deal_units'] ? 1 : 0,
											'start_timestamp'   => $start_date_timestamp,
											'end_timestamp'     => $end_date_timestamp,
											'campaign_type'     => $campaignType['type'],
											'allow_backorder'   => $allow_backorder,
											'campaign_id'       => $j,
										);
										if ( isset( $val['deal_inventory_goal_for'] ) && '' !== $val['deal_inventory_goal_for'] ) {
											$uniqueArr[ $j ]['goals']['inventry_goal_for'] = $val['deal_inventory_goal_for'];
										}

										if ( $deal_start_time > 0 ) {
											$uniqueArr[ $j ]['goals']['start_timestamp'] = $deal_start_time;
											$uniqueArr[ $j ]['goals']['end_timestamp']   = $deal_end_time;
										} else {
											if ( $goal_start_time == 0 ) {
												$goal_start_time                             = $start_date_timestamp;
												$goal_end_time                               = $end_date_timestamp;
												$uniqueArr[ $j ]['goals']['start_timestamp'] = $goal_start_time;
												$uniqueArr[ $j ]['goals']['end_timestamp']   = $goal_end_time;
												$menu_order_campaign_id                      = $j;
												$menu_order_start_time                       = $goal_start_time;
												$menu_order_end_time                         = $goal_end_time;
											}
										}

										$finale_goal = $goals[ $j ] = $uniqueArr[ $j ]['goals'];
									}
								}
							} else {

								if ( WCCT_Core()->shortcode->is_shortcode_process === true ) {

									WCCT_Core()->public->register_error( __( 'Unable to Show. Inventory should be enabled to show counter bar.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), $j, 'inventory' );
								}
							}
							if ( isset( $goals[ $j ] ) && is_array( $goals[ $j ] ) && count( $goals[ $j ] ) > 0 && $product_id != '0' ) {
								$goals_before_event = $goals;
								$product_obj        = WCCT_Core()->public->wcct_get_product_obj( $product_id );
								if ( ! is_wp_error( $product_obj ) && method_exists( $product_obj, 'get_type' ) ) {

									if ( isset( $val['deal_custom_advanced'] ) && is_array( $val['deal_custom_advanced'] ) && 'tiered' === $val['deal_custom_mode'] ) {
										$goals[ $j ]['deal_custom_units'] = WCCT_Core()->stock->wcct_get_custom_inventory_goal_by_conditions( $product_obj, $val['deal_custom_units'], $val['deal_custom_advanced'] );

									}

									if ( isset( $val['deal_range_from_custom_units'] ) && $val['deal_range_from_custom_units'] > 0 && isset( $val['deal_range_to_custom_units'] ) && $val['deal_range_to_custom_units'] > 0 && 'range' === $val['deal_custom_mode'] ) {
										$goals[ $j ]['deal_custom_units'] = WCCT_Core()->stock->wcct_get_custom_inventory_goal_by_range( $product_obj, $j, $goals[ $j ], $val );
									}

									//Added to run events login to modify goals data
									$goal_out       = WCCT_Core()->public->wcct_set_goal_meta( $product_obj, $product_obj->get_id(), $goals[ $j ], $j );
									$goals_by_event = $goals;

									if ( isset( $val['events_enable'] ) && $val['events_enable'] && isset( $val['events'] ) && is_array( $val['events'] ) && count( $val['events'] ) > 0 ) {
										$goals_by_event = $goals = WCCT_Events::process_events( $val['events'], $goals, array(
											'campaign'        => $campaignType,
											'goals'           => $goals,
											'postID'          => $product_id,
											'campaignID'      => $j,
											'sold_units'      => 0,
											'available_units' => 0,
										), 'available_unit' );
										$goals_by_event = $goals = WCCT_Events::process_events( $val['events'], $goals, array(
											'campaign'        => $campaignType,
											'goals'           => $goals,
											'postID'          => $product_id,
											'campaignID'      => $j,
											'sold_units'      => 0,
											'available_units' => 0,
										), 'sold_unit' );
									}

									$is_goal_modified = WCCT_Common::array_recursive( $goals_by_event, $goals_before_event );

									if ( $is_goal_modified ) {

										$goal_out = WCCT_Core()->public->wcct_set_goal_meta( $product_obj, $product_obj->get_id(), $goals[ $j ], $j );
									}

									if ( '' === $goal_out || ! is_array( $goal_out ) ) {
										$threshold_reach_out = false;
									} else {

										if ( is_array( $goal_out ) && count( $goal_out ) > 0 ) {
											//threshold
											$sold_qty_final = 0;

											if ( (int) $goal_out['quantity'] > 0 ) {
												$sold_qty_final = (int) $goal_out['sold_out'];
												if ( 'campaign' === $goals[ $j ]['inventry_goal_for'] ) {
													$sold_qty_final = (int) $goal_out['sold_out_campaign'];
												}
												$is_manage_stock = $product_obj->managing_stock();
												$threshold_qty   = (int) $goals[ $j ]['threshold'];

												if ( true === $is_manage_stock && ( (int) $goal_out['quantity'] - $sold_qty_final ) <= $threshold_qty ) {

													unset( $goals[ $j ] );
													if ( isset( $val['deal_end_campaign'] ) && 'yes' === $val['deal_end_campaign'] ) {
														$threshold_reach_out = true;
													}
												}
											}

											//checking if sold out is greater than total custom quantity if yes please end campaign
											if ( 'custom' === $goal_out['type'] && isset( $goals[ $j ] ) ) {
												if ( $sold_qty_final >= (int) $goal_out['quantity'] ) {
													unset( $goals[ $j ] );
													if ( isset( $val['deal_end_campaign'] ) && 'yes' === $val['deal_end_campaign'] ) {
														$threshold_reach_out = true;
													}
												}
											}
										}
									}
								}
							}

							if ( $threshold_reach_out == true ) {
								unset( $deals[ $j ] );
								unset( $goals[ $j ] );
								$uniqueArr[ $j ]      = array();
								$after_campaign[ $j ] = array();

								if ( isset( $val['location_timer_show_single'] ) && $val['location_timer_show_single'] == '1' ) {
									$val_exp_text = isset( $val['misc_timer_expiry_text'] ) ? $val['misc_timer_expiry_text'] : '';
									if ( '' !== $val_exp_text ) {
										$expiry_text[ $j ] = array(
											'text'        => $val_exp_text,
											'position'    => isset( $val['location_timer_single_location'] ) ? $val['location_timer_single_location'] : '',
											'campaign_id' => $j,
										);
									}
								}
								if ( isset( $val['actions_after_end_stock'] ) && 'none' !== $val['actions_after_end_stock'] ) {
									$after_campaign[ $j ]['stock'] = $val['actions_after_end_stock'];
								}

								if ( isset( $val['actions_after_end_add_to_cart'] ) && 'none' !== $val['actions_after_end_add_to_cart'] ) {
									$after_campaign[ $j ]['add_to_cart'] = $val['actions_after_end_add_to_cart'];
								}

								array_push( $expired_camp, $j );
								do_action( 'wcct_after_campaign_finished', $j );
							} else {
								array_push( $running_camp, $j );
								if ( $menu_order_campaign_id == 0 ) {
									$menu_order_campaign_id = $j;
									$menu_order_start_time  = $start_date_timestamp;
									$menu_order_end_time    = $end_date_timestamp;
								}

								// Timer Single Product
								if ( isset( $val['location_timer_show_single'] ) && $val['location_timer_show_single'] == '1' ) {

									$uniqueArr[ $j ]['single_timer'] = array(
										'position'        => isset( $val['location_timer_single_location'] ) ? $val['location_timer_single_location'] : '',
										'skin'            => isset( $val['appearance_timer_single_skin'] ) ? $val['appearance_timer_single_skin'] : '',
										'bg_color'        => isset( $val['appearance_timer_single_bg_color'] ) ? $val['appearance_timer_single_bg_color'] : '',
										'label_color'     => isset( $val['appearance_timer_single_text_color'] ) ? $val['appearance_timer_single_text_color'] : '',
										'timer_font'      => isset( $val['appearance_timer_single_font_size_timer'] ) ? $val['appearance_timer_single_font_size_timer'] : '',
										'label_font'      => isset( $val['appearance_timer_single_font_size'] ) ? $val['appearance_timer_single_font_size'] : '',
										'display'         => isset( $val['appearance_timer_single_display'] ) ? $val['appearance_timer_single_display'] : '',
										'label_days'      => isset( $val['appearance_timer_single_label_days'] ) ? $val['appearance_timer_single_label_days'] : '',
										'label_hrs'       => isset( $val['appearance_timer_single_label_hrs'] ) ? $val['appearance_timer_single_label_hrs'] : '',
										'label_mins'      => isset( $val['appearance_timer_single_label_mins'] ) ? $val['appearance_timer_single_label_mins'] : '',
										'label_secs'      => isset( $val['appearance_timer_single_label_secs'] ) ? $val['appearance_timer_single_label_secs'] : '',
										'border_width'    => isset( $val['appearance_timer_single_border_width'] ) ? $val['appearance_timer_single_border_width'] : '',
										'border_color'    => isset( $val['appearance_timer_single_border_color'] ) ? $val['appearance_timer_single_border_color'] : '',
										'border_style'    => isset( $val['appearance_timer_single_border_style'] ) ? $val['appearance_timer_single_border_style'] : '',
										'timer_mobile'    => isset( $val['appearance_timer_mobile_reduction'] ) ? $val['appearance_timer_mobile_reduction'] : '',
										'delay'           => isset( $val['appearance_timer_single_delay'] ) ? $val['appearance_timer_single_delay'] : '',
										'delay_hrs'       => isset( $val['appearance_timer_single_delay_hrs'] ) ? $val['appearance_timer_single_delay_hrs'] : '',
										'start_timestamp' => $start_date_timestamp,
										'end_timestamp'   => $end_date_timestamp,
										'campaign_type'   => $campaignType['type'],
										'timer_labels'    => $timer_labels[ $j ],
									);
									$single_timer[ $j ]              = $uniqueArr[ $j ]['single_timer'];
								} else {
									if ( true === WCCT_Core()->shortcode->is_shortcode_process ) {
										WCCT_Core()->public->register_error( __( 'Unable to Show. Go to Elements > Single Product Countdown Timer and check visibility settings.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), $j, 'timer' );
									}
								}

								// Timer Grid
								if ( count( $grid_timer ) == '0' ) {
									$add_timer_to_grid_filter = apply_filters( 'wcct_add_timer_to_grid', array() );
									$add_timer_to_grid_filter = apply_filters( "wcct_add_timer_to_grid_{$j}", $add_timer_to_grid_filter );
									if ( is_array( $add_timer_to_grid_filter ) && count( $add_timer_to_grid_filter ) > 0 ) {
										$add_timer_to_grid_filter            = wp_parse_args( $add_timer_to_grid_filter, $this->grid_timer_default_array );
										$grid_timer[ $j ]                    = $add_timer_to_grid_filter;
										$grid_timer[ $j ]['start_timestamp'] = $start_date_timestamp;
										$grid_timer[ $j ]['end_timestamp']   = $end_date_timestamp;
										$grid_timer[ $j ]['campaign_type']   = $campaignType['type'];
									}
								}

								// Sticky Header
								if ( isset( $val['location_timer_show_sticky_header'] ) && $val['location_timer_show_sticky_header'] == '1' ) {
									$sticky_header_app = array(
										'wrap_bg'            => isset( $val['appearance_sticky_header_wrap_bg'] ) ? $val['appearance_sticky_header_wrap_bg'] : '',
										'hide_mobile'        => isset( $val['appearance_sticky_header_hide_mobile'] ) ? $val['appearance_sticky_header_hide_mobile'] : '',
										'hide_tablet'        => isset( $val['appearance_sticky_header_hide_tablet'] ) ? $val['appearance_sticky_header_hide_tablet'] : '',
										'hide_desktop'       => isset( $val['appearance_sticky_header_hide_desktop'] ) ? $val['appearance_sticky_header_hide_desktop'] : '',
										'headline'           => isset( $val['appearance_sticky_header_headline'] ) ? $val['appearance_sticky_header_headline'] : '',
										'headline_font_size' => isset( $val['appearance_sticky_header_headline_font_size'] ) ? $val['appearance_sticky_header_headline_font_size'] : '',
										'headline_color'     => isset( $val['appearance_sticky_header_headline_color'] ) ? $val['appearance_sticky_header_headline_color'] : '',
										'headline_align'     => isset( $val['appearance_sticky_header_headline_alignment'] ) ? $val['appearance_sticky_header_headline_alignment'] : 'left',
										'desc'               => isset( $val['appearance_sticky_header_description'] ) ? $val['appearance_sticky_header_description'] : '',
										'desc_font_size'     => isset( $val['appearance_sticky_header_description_font_size'] ) ? $val['appearance_sticky_header_description_font_size'] : '',
										'desc_color'         => isset( $val['appearance_sticky_header_description_color'] ) ? $val['appearance_sticky_header_description_color'] : '',
										'desc_align'         => isset( $val['appearance_sticky_header_description_alignment'] ) ? $val['appearance_sticky_header_description_alignment'] : 'left',
										'desc_hide_mobile'   => isset( $val['appearance_sticky_header_sub_headline_hide_mobile'] ) ? $val['appearance_sticky_header_sub_headline_hide_mobile'] : '',
										'start_timestamp'    => $start_date_timestamp,
										'end_timestamp'      => $end_date_timestamp,
										'campaign_type'      => $campaignType['type'],
										'timer_hide'         => isset( $val['appearance_sticky_header_disable_timer'] ) ? $val['appearance_sticky_header_disable_timer'] : '',
										'skin'               => isset( $val['appearance_sticky_header_skin'] ) ? $val['appearance_sticky_header_skin'] : '',
										'bg_color'           => isset( $val['appearance_sticky_header_bg_color'] ) ? $val['appearance_sticky_header_bg_color'] : '',
										'label_color'        => isset( $val['appearance_sticky_header_text_color'] ) ? $val['appearance_sticky_header_text_color'] : '',
										'timer_font'         => isset( $val['appearance_sticky_header_font_size_timer'] ) ? $val['appearance_sticky_header_font_size_timer'] : '',
										'label_font'         => isset( $val['appearance_sticky_header_font_size'] ) ? $val['appearance_sticky_header_font_size'] : '',
										'label_days'         => isset( $val['appearance_sticky_header_label_days'] ) ? $val['appearance_sticky_header_label_days'] : '',
										'label_hrs'          => isset( $val['appearance_sticky_header_label_hrs'] ) ? $val['appearance_sticky_header_label_hrs'] : '',
										'label_mins'         => isset( $val['appearance_sticky_header_label_mins'] ) ? $val['appearance_sticky_header_label_mins'] : '',
										'label_secs'         => isset( $val['appearance_sticky_header_label_secs'] ) ? $val['appearance_sticky_header_label_secs'] : '',
										'border_width'       => isset( $val['appearance_sticky_header_timer_border_width'] ) ? $val['appearance_sticky_header_timer_border_width'] : '',
										'border_color'       => isset( $val['appearance_sticky_header_timer_border_color'] ) ? $val['appearance_sticky_header_timer_border_color'] : '',
										'border_style'       => isset( $val['appearance_sticky_header_timer_border_style'] ) ? $val['appearance_sticky_header_timer_border_style'] : '',
										'timer_mobile'       => isset( $val['appearance_sticky_header_timer_mobile_reduction'] ) ? $val['appearance_sticky_header_timer_mobile_reduction'] : '',
										'timer_position'     => isset( $val['appearance_sticky_header_timer_position'] ) ? $val['appearance_sticky_header_timer_position'] : '',
										'button_enable'      => isset( $val['appearance_sticky_header_enable_button'] ) ? $val['appearance_sticky_header_enable_button'] : '',
										'button_skins'       => isset( $val['appearance_sticky_header_button_skin'] ) ? $val['appearance_sticky_header_button_skin'] : '',
										'button_text'        => isset( $val['appearance_sticky_header_button_text'] ) ? $val['appearance_sticky_header_button_text'] : '',
										'button_bg_color'    => isset( $val['appearance_sticky_header_button_bg_color'] ) ? $val['appearance_sticky_header_button_bg_color'] : '',
										'button_text_color'  => isset( $val['appearance_sticky_header_button_text_color'] ) ? $val['appearance_sticky_header_button_text_color'] : '',
										'button_url'         => isset( $val['appearance_sticky_header_button_action'] ) ? $val['appearance_sticky_header_button_action'] : '',
										'delay'              => $val['appearance_sticky_header_delay'],
										'expire_time'        => isset( $val['misc_cookie_expire_time'] ) ? $val['misc_cookie_expire_time'] : '1',
										'timer_labels'       => $timer_labels[ $j ],
									);

									$uniqueArr[ $j ]['sticky_header'] = $sticky_header_app;
									$sticky_header[ $j ]              = $uniqueArr[ $j ]['sticky_header'];
								}

								// Sticky Footer
								if ( isset( $val['location_timer_show_sticky_footer'] ) && $val['location_timer_show_sticky_footer'] == '1' ) {
									$sticky_footer_app = array(
										'wrap_bg'            => isset( $val['appearance_sticky_footer_wrap_bg'] ) ? $val['appearance_sticky_footer_wrap_bg'] : '',
										'hide_mobile'        => isset( $val['appearance_sticky_footer_hide_mobile'] ) ? $val['appearance_sticky_footer_hide_mobile'] : '',
										'hide_tablet'        => isset( $val['appearance_sticky_footer_hide_tablet'] ) ? $val['appearance_sticky_footer_hide_tablet'] : '',
										'hide_desktop'       => isset( $val['appearance_sticky_footer_hide_desktop'] ) ? $val['appearance_sticky_footer_hide_desktop'] : '',
										'headline'           => isset( $val['appearance_sticky_footer_headline'] ) ? $val['appearance_sticky_footer_headline'] : '',
										'headline_font_size' => isset( $val['appearance_sticky_footer_headline_font_size'] ) ? $val['appearance_sticky_footer_headline_font_size'] : '',
										'headline_color'     => isset( $val['appearance_sticky_footer_headline_color'] ) ? $val['appearance_sticky_footer_headline_color'] : '',
										'headline_align'     => isset( $val['appearance_sticky_footer_headline_alignment'] ) ? $val['appearance_sticky_footer_headline_alignment'] : 'left',
										'desc'               => isset( $val['appearance_sticky_footer_description'] ) ? $val['appearance_sticky_footer_description'] : '',
										'desc_font_size'     => isset( $val['appearance_sticky_footer_description_font_size'] ) ? $val['appearance_sticky_footer_description_font_size'] : '',
										'desc_color'         => isset( $val['appearance_sticky_footer_description_color'] ) ? $val['appearance_sticky_footer_description_color'] : '',
										'desc_align'         => isset( $val['appearance_sticky_footer_description_alignment'] ) ? $val['appearance_sticky_footer_description_alignment'] : 'left',
										'desc_hide_mobile'   => isset( $val['appearance_sticky_footer_sub_headline_hide_mobile'] ) ? $val['appearance_sticky_footer_sub_headline_hide_mobile'] : '',
										'start_timestamp'    => $start_date_timestamp,
										'end_timestamp'      => $end_date_timestamp,
										'campaign_type'      => $campaignType['type'],
										'timer_hide'         => isset( $val['appearance_sticky_footer_disable_timer'] ) ? $val['appearance_sticky_footer_disable_timer'] : '',
										'skin'               => isset( $val['appearance_sticky_footer_skin'] ) ? $val['appearance_sticky_footer_skin'] : '',
										'bg_color'           => isset( $val['appearance_sticky_footer_bg_color'] ) ? $val['appearance_sticky_footer_bg_color'] : '',
										'label_color'        => isset( $val['appearance_sticky_footer_text_color'] ) ? $val['appearance_sticky_footer_text_color'] : '',
										'timer_font'         => isset( $val['appearance_sticky_footer_font_size_timer'] ) ? $val['appearance_sticky_footer_font_size_timer'] : '',
										'label_font'         => isset( $val['appearance_sticky_footer_font_size'] ) ? $val['appearance_sticky_footer_font_size'] : '',
										'label_days'         => isset( $val['appearance_sticky_footer_label_days'] ) ? $val['appearance_sticky_footer_label_days'] : '',
										'label_hrs'          => isset( $val['appearance_sticky_footer_label_hrs'] ) ? $val['appearance_sticky_footer_label_hrs'] : '',
										'label_mins'         => isset( $val['appearance_sticky_footer_label_mins'] ) ? $val['appearance_sticky_footer_label_mins'] : '',
										'label_secs'         => isset( $val['appearance_sticky_footer_label_secs'] ) ? $val['appearance_sticky_footer_label_secs'] : '',
										'border_width'       => isset( $val['appearance_sticky_footer_timer_border_width'] ) ? $val['appearance_sticky_footer_timer_border_width'] : '',
										'border_color'       => isset( $val['appearance_sticky_footer_timer_border_color'] ) ? $val['appearance_sticky_footer_timer_border_color'] : '',
										'border_style'       => isset( $val['appearance_sticky_footer_timer_border_style'] ) ? $val['appearance_sticky_footer_timer_border_style'] : '',
										'timer_mobile'       => isset( $val['appearance_sticky_header_timer_mobile_reduction'] ) ? $val['appearance_sticky_header_timer_mobile_reduction'] : '',
										'timer_position'     => isset( $val['appearance_sticky_footer_timer_mobile_reduction'] ) ? $val['appearance_sticky_footer_timer_mobile_reduction'] : '',
										'button_enable'      => isset( $val['appearance_sticky_footer_enable_button'] ) ? $val['appearance_sticky_footer_enable_button'] : '',
										'button_skins'       => isset( $val['appearance_sticky_footer_button_skin'] ) ? $val['appearance_sticky_footer_button_skin'] : '',
										'button_text'        => isset( $val['appearance_sticky_footer_button_text'] ) ? $val['appearance_sticky_footer_button_text'] : '',
										'button_bg_color'    => isset( $val['appearance_sticky_footer_button_bg_color'] ) ? $val['appearance_sticky_footer_button_bg_color'] : '',
										'button_text_color'  => isset( $val['appearance_sticky_footer_button_text_color'] ) ? $val['appearance_sticky_footer_button_text_color'] : '',
										'button_url'         => isset( $val['appearance_sticky_footer_button_action'] ) ? $val['appearance_sticky_footer_button_action'] : '',
										'delay'              => $val['appearance_sticky_footer_delay'],
										'expire_time'        => isset( $val['misc_cookie_expire_time'] ) ? $val['misc_cookie_expire_time'] : '1',
										'timer_labels'       => $timer_labels[ $j ],
									);

									$uniqueArr[ $j ]['sticky_footer'] = $sticky_footer_app;
									$sticky_footer[ $j ]              = $uniqueArr[ $j ]['sticky_footer'];
								}

								// Custom Text
								if ( isset( $val['location_show_custom_text'] ) && $val['location_show_custom_text'] == '1' ) {
									$uniqueArr[ $j ]['custom_text'] = array(
										'position'        => isset( $val['location_custom_text_location'] ) ? $val['location_custom_text_location'] : '',
										'description'     => isset( $val['appearance_custom_text_description'] ) ? $val['appearance_custom_text_description'] : '',
										'bg_color'        => isset( $val['appearance_custom_text_bg_color'] ) ? $val['appearance_custom_text_bg_color'] : '',
										'text_color'      => isset( $val['appearance_custom_text_text_color'] ) ? $val['appearance_custom_text_text_color'] : '',
										'font_size'       => isset( $val['appearance_custom_text_font_size'] ) ? $val['appearance_custom_text_font_size'] : '',
										'border_width'    => isset( $val['appearance_custom_text_border_width'] ) ? $val['appearance_custom_text_border_width'] : '',
										'border_color'    => isset( $val['appearance_custom_text_border_color'] ) ? $val['appearance_custom_text_border_color'] : '',
										'border_style'    => isset( $val['appearance_custom_text_border_style'] ) ? $val['appearance_custom_text_border_style'] : '',
										'start_timestamp' => $start_date_timestamp,
										'end_timestamp'   => $end_date_timestamp,
										'campaign_type'   => $campaignType['type'],
										'timer_labels'    => $timer_labels[ $j ],
									);
									$custom_text[ $j ]              = $uniqueArr[ $j ]['custom_text'];
								} else {
									if ( WCCT_Core()->shortcode->is_shortcode_process === true ) {
										WCCT_Core()->public->register_error( __( 'Unable to Show. Go to Elements > Single Product Countdown Timer and check visibility settings.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), $j, 'timer' );
									}
								}
								$custom_css[ $j ] = isset( $val['appearance_custom_css'] ) ? $val['appearance_custom_css'] : '';

								// Counter Bar preparations
								if ( isset( $uniqueArr[ $j ]['goals'] ) && is_array( $uniqueArr[ $j ]['goals'] ) && count( $uniqueArr[ $j ]['goals'] ) > 0 ) {
									// Bar Single
									if ( isset( $val['location_bar_show_single'] ) && $val['location_bar_show_single'] == '1' ) {
										$countBarr_style = array(
											'position'              => isset( $val['location_bar_single_location'] ) ? $val['location_bar_single_location'] : '',
											'skin'                  => isset( $val['appearance_bar_single_skin'] ) ? $val['appearance_bar_single_skin'] : '',
											'edge'                  => isset( $val['appearance_bar_single_edges'] ) ? $val['appearance_bar_single_edges'] : '',
											'orientation'           => isset( $val['appearance_bar_single_orientation'] ) ? $val['appearance_bar_single_orientation'] : 'ltr',
											'height'                => isset( $val['appearance_bar_single_height'] ) ? $val['appearance_bar_single_height'] : '',
											'bg_color'              => isset( $val['appearance_bar_single_bg_color'] ) ? $val['appearance_bar_single_bg_color'] : '',
											'active_color'          => isset( $val['appearance_bar_single_active_color'] ) ? $val['appearance_bar_single_active_color'] : '',
											'display'               => isset( $val['appearance_bar_single_display'] ) ? $val['appearance_bar_single_display'] : '',
											'border_width'          => isset( $val['appearance_bar_single_border_width'] ) ? $val['appearance_bar_single_border_width'] : '',
											'border_color'          => isset( $val['appearance_bar_single_border_color'] ) ? $val['appearance_bar_single_border_color'] : '',
											'border_style'          => isset( $val['appearance_bar_single_border_style'] ) ? $val['appearance_bar_single_border_style'] : '',
											'delay'                 => isset( $val['appearance_bar_single_delay'] ) ? $val['appearance_bar_single_delay'] : '',
											'delay_items'           => isset( $val['appearance_bar_single_delay_item'] ) ? $val['appearance_bar_single_delay_item'] : '',
											'delay_items_remaining' => isset( $val['appearance_bar_single_delay_item_remaining'] ) ? $val['appearance_bar_single_delay_item_remaining'] : '',
											'start_timestamp'       => $start_date_timestamp,
											'end_timestamp'         => $end_date_timestamp,
											'campaign_type'         => $campaignType['type'],
											'timer_labels'          => $timer_labels[ $j ],
										);

										$uniqueArr[ $j ]['bar_single'] = $countBarr_style;
										$single_bar[ $j ]              = $uniqueArr[ $j ]['bar_single'];
									} else {
										if ( WCCT_Core()->shortcode->is_shortcode_process === true ) {
											WCCT_Core()->public->register_error( __( 'Unable to Show. Go to Elements > Single Product Counter Bar and check visibility settings.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), $j, 'inventory' );
										}
									}

									// Bar Grid
									if ( count( $grid_bar ) == '0' ) {
										$wcct_add_bar_to_grid = apply_filters( 'wcct_add_bar_to_grid', array() );
										$wcct_add_bar_to_grid = apply_filters( "wcct_add_bar_to_grid_{$j}", $wcct_add_bar_to_grid );

										if ( is_array( $wcct_add_bar_to_grid ) && count( $wcct_add_bar_to_grid ) > 0 ) {
											$wcct_add_bar_to_grid              = wp_parse_args( $wcct_add_bar_to_grid, $this->grid_bar_default_array );
											$grid_bar[ $j ]                    = $wcct_add_bar_to_grid;
											$grid_bar[ $j ]['start_timestamp'] = $start_date_timestamp;
											$grid_bar[ $j ]['end_timestamp']   = $end_date_timestamp;
											$grid_bar[ $j ]['campaign_type']   = $campaignType['type'];
										}
									}
								}

								//get values from during campaign tags
								$during_campaign[ $j ] = array();
								if ( isset( $val['actions_during_stock'] ) && 'none' !== $val['actions_during_stock'] ) {
									$during_campaign[ $j ]['stock'] = $val['actions_during_stock'];
								}
								if ( isset( $val['actions_during_add_to_cart'] ) && 'none' !== $val['actions_during_add_to_cart'] ) {
									$during_campaign[ $j ]['add_to_cart'] = $val['actions_during_add_to_cart'];
								}
								if ( isset( $val['misc_add_to_cart_btn_text_enable'] ) && '' !== $val['misc_add_to_cart_btn_text_enable'] ) {
									if ( isset( $val['misc_add_to_cart_btn_text'] ) && '' !== $val['misc_add_to_cart_btn_text'] ) {
										$add_to_cart_text[ $j ]['button_text'] = $val['misc_add_to_cart_btn_text'];
									}
									if ( isset( $val['misc_add_to_cart_btn_exclude'] ) && '' !== $val['misc_add_to_cart_btn_exclude'] ) {
										$add_to_cart_text[ $j ]['add_to_cart_btn_exclude'] = $val['misc_add_to_cart_btn_exclude'];
									}
								}
								if ( isset( $deals[ $j ] ) && is_array( $deals[ $j ] ) && count( $deals[ $j ] ) > 0 ) {
									if ( is_array( $during_deal_campaign_final ) && count( $during_deal_campaign_final ) === 0 ) {
										$during_deal_campaign_final                 = $during_campaign[ $j ];
										$during_deal_campaign_final['deal_enables'] = true;
										$during_deal_campaign_final['campaign_id']  = $j;
									}
								}
								if ( isset( $uniqueArr[ $j ]['goals'] ) && is_array( $uniqueArr[ $j ]['goals'] ) && count( $uniqueArr[ $j ]['goals'] ) > 0 && ! isset( $deals[ $j ] ) ) {
									if ( is_array( $during_goal_campaign_final ) && count( $during_goal_campaign_final ) === 0 ) {
										$during_goal_campaign_final                 = $during_campaign[ $j ];
										$during_goal_campaign_final['goal_enables'] = true;
										$during_goal_campaign_final['campaign_id']  = $j;
									}
								}
							}
						} else {

							/**campaign is finished. */
							array_push( $expired_camp, $j );
							do_action( 'wcct_after_campaign_finished', $j );
							WCCT_Core()->public->register_error( __( 'Unable to Show. Campaign may not be running, check your settings.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), $j );

							$menu_order_campaign_id = $j;
							$after_campaign[ $j ]   = array();
							if ( isset( $val['location_timer_show_single'] ) && $val['location_timer_show_single'] == '1' ) {
								$val_exp_text = isset( $val['misc_timer_expiry_text'] ) ? $val['misc_timer_expiry_text'] : '';
								if ( $val_exp_text != '' ) {
									$expiry_text[ $j ]          = array(
										'text'        => $val_exp_text,
										'position'    => isset( $val['location_timer_single_location'] ) ? $val['location_timer_single_location'] : '',
										'campaign_id' => $j,
									);
									WCCT_Core()->public->errors = array();
								}
							}
							//get values from during campaign tags

							if ( isset( $val['actions_after_end_stock'] ) && 'none' !== $val['actions_after_end_stock'] ) {
								$after_campaign[ $j ]['stock'] = $val['actions_after_end_stock'];
							}

							if ( isset( $val['actions_after_end_add_to_cart'] ) && 'none' !== $val['actions_after_end_add_to_cart'] ) {
								$after_campaign[ $j ]['add_to_cart'] = $val['actions_after_end_add_to_cart'];
							}
						}
					} else {
						//campaign is scheduled
						array_push( $scheduled_camps, $j );
					}

					if ( ( is_array( $expired_camp ) && false == in_array( $j, $expired_camp ) ) && $threshold_reach_out === false && isset( $val['events_enable'] ) && $val['events_enable'] == '1' && isset( $val['events'] ) && is_array( $val['events'] ) && count( $val['events'] ) > 0 ) {
						array_push( $events_camp, $j );

						$deals_e = WCCT_Events::process_events( $val['events'], $deals, array(
							'campaign'        => $campaignType,
							'goals'           => $goals,
							'postID'          => $product_id,
							'campaignID'      => $j,
							'sold_units'      => 0,
							'available_units' => 0,
						), 'discount' );
						if ( $deals_e && is_array( $deals_e ) && count( $deals_e ) > 0 ) {
							$deals_event[ $j ] = $deals_e;
						}

						$get_prices = WCCT_Events::process_events( $val['events'], array(), array(
							'campaign'        => $campaignType,
							'goals'           => $goals,
							'postID'          => $product_id,
							'campaignID'      => $j,
							'sold_units'      => 0,
							'available_units' => 0,
						), 'regular_price' );

						if ( $get_prices ) {
							$regular_prices[ $j ] = $get_prices;
						}
					}
				}

				//coupons array setup
				if ( isset( $val['coupons_enable'] ) && $val['coupons_enable'] == '1' && $val['coupons'] !== '' ) {

					$coupons[ $j ] = array(
						'coupons'                  => $val['coupons'],
						'apply_mode'               => $val['coupons_apply_mode'],
						'is_expire'                => $val['coupons_is_expire'],
						'success_message'          => $val['coupons_success_message'],
						'failure_message'          => $val['coupons_failure_message'],
						'cart_message'             => $val['coupons_cart_message'],
						'hide_errors'              => $val['coupons_is_hide_errors'],
						'empty_cart_message'       => $val['coupons_empty_cart_message'],
						'is_checkout_button'       => isset( $val['coupons_is_checkout_link'] ) ? $val['coupons_is_checkout_link'] : '',
						'timer_labels'             => $timer_labels[ $j ],
						'notice_after_add_to_cart' => isset( $val['coupons_notice_after_add_to_cart'] ) ? $val['coupons_notice_after_add_to_cart'] : 'no',
						'notice_pages'             => isset( $val['coupons_notice_pages'] ) ? $val['coupons_notice_pages'] : array(),
						'notice_products'          => isset( $val['coupons_notice_products'] ) ? $val['coupons_notice_products'] : array(),
						'notice'                   => isset( $val['coupons_notice_show'] ) ? $val['coupons_notice_show'] : 'all',
					);
				}

				unset( $val );
			}
		}

		/**
		 * Here we are handling deals event with the original deals, so to  make sure every time there exists deal, we try and get applied to the same campaign
		 * Checking if any deals event set
		 */
		if ( isset( $deals_event ) && is_array( $deals_event ) && count( $deals_event ) > 0 ) {
			//checking if deals exists
			if ( $deals && is_array( $deals ) && count( $deals ) > 0 ) {
				//iterating over all the deals
				foreach ( $deals as $key => $deal ) {

					$event_to_assign = current( $deals_event );

					//discard if we do not have event set
					if ( false === $event_to_assign ) {
						continue;
					}

					//Checking if there exists a deal for a campaign for which we do not have deal event
					//if we found any deals for this criteria, we assign deal event to the same camp ID.
					if ( ! array_key_exists( $key, $deals_event ) ) {

						$deals[ $key ] = current( $deals_event );
					} else {
						//if we do found a matching deal event for the current deal then assign respective deal event.
						$deals[ $key ] = $deals_event[ $key ];
					}
				}
			} else {
				$deals = $deals_event;
			}
		}

		/**
		 * handling for regular prices
		 * Checking if regular prices events are registered or not, if they are registered and doesn't belong to the same camp ID to which we have deals, we assign regular prices to deal related campaigns only
		 */
		if ( isset( $regular_prices ) && is_array( $regular_prices ) && count( $regular_prices ) > 0 ) {
			if ( $deals && is_array( $deals ) && count( $deals ) > 0 ) {
				foreach ( $deals as $key => $deal ) {
					//checking if there exists any deal for which we have regular prices set
					//if we do not found any matching key against that deal, we simply assign the current regular price to the current iteration
					if ( ! array_key_exists( $key, $regular_prices ) ) {
						$get_current            = current( $regular_prices );
						$regular_prices         = array();
						$regular_prices[ $key ] = $get_current;
					}
				}
			}
		}

		reset( $deals );
		reset( $goals );

		$final_during_campaign = array();
		$flag                  = false;

		if ( $during_campaign && is_array( $during_campaign ) ) {
			foreach ( $during_campaign as $campID => $campaign ) {
				if ( ! empty( $campaign ) ) {
					$final_during_campaign           = $campaign;
					$final_during_campaign['campID'] = $campID;
					break;
				}
			}
		}

		$final_after_campaign = array();
		if ( $after_campaign && is_array( $after_campaign ) && ! empty( $after_campaign ) ) {
			foreach ( $after_campaign as $campID => $campaign ) {
				if ( ! empty( $campaign ) ) {
					$final_after_campaign               = $campaign;
					$final_after_campaign['campaignID'] = $campID;
					break;
				}
			}
		}

		$global_settings = WCCT_Common::get_global_default_settings();
		if ( 'yes' == $global_settings['wcct_timer_hide_multiple'] ) {
			if ( is_array( $single_timer ) && count( $single_timer ) > 0 ) {
				$first_key      = key( $single_timer );
				$first_key_data = reset( $single_timer );
				$single_timer   = array(
					$first_key => $first_key_data,
				);
			}
		}

		$return = array(
			'campaign_meta'    => $campaign_meta,
			'grid_bar'         => $grid_bar,
			'single_bar'       => $single_bar,
			'sticky_header'    => $sticky_header,
			'sticky_footer'    => $sticky_footer,
			'show_on_cart'     => $show_on_cart,
			'grid_timer'       => $grid_timer,
			'single_timer'     => $single_timer,
			'custom_text'      => $custom_text,
			'goals'            => current( $goals ),
			'deals'            => current( $deals ),
			'add_to_cart_text' => $add_to_cart_text,
			'custom_css'       => $custom_css,
			'expiry_text'      => $expiry_text,
			'during_campaign'  => ( $final_during_campaign ) ? $final_during_campaign : array(),
			'after_campaign'   => ( $final_after_campaign ) ? $final_after_campaign : array(),
			'regular_prices'   => $regular_prices,
			'coupons'          => $coupons,
			'events'           => $events_camp,
			'expired'          => $expired_camp,
			'running'          => $running_camp,
			'scheduled'        => $scheduled_camps,
			'timer_labels'     => $timer_labels,
		);

		if ( $return_key && isset( $return[ $return_key ] ) ) {
			return $return[ $return_key ];
		}

		return $return;
	}

	public function get_multiple_instance_for_loop_and_cart( $contents, $ID = 0, $slug = '', $return_key = false, $skip_rules = false ) {
		global $wpdb;
		$wcct_trigger_data = array();
		$xl_cache_obj      = XL_Cache::get_instance();
		$xl_transient_obj  = XL_Transient::get_instance();

		if ( $contents && is_array( $contents ) && count( $contents ) ) {
			foreach ( $contents as $content ) {

				if ( ! is_object( $content ) ) {
					continue;
				}
				$content_id = $content->ID;
				if ( $skip_rules || WCCT_Common::match_groups( $content_id, $ID ) ) {
					$cache_key = 'wcct_countdown_post_meta_' . $content_id;

					/**
					 * Setting xl cache and transient for Finale single campaign meta
					 */
					$cache_data = $xl_cache_obj->get_cache( $cache_key, 'finale' );
					if ( false !== $cache_data ) {
						$parseObj = $cache_data;
					} else {
						$transient_data = $xl_transient_obj->get_transient( $cache_key, 'finale' );

						if ( false !== $transient_data ) {
							$parseObj = $transient_data;
						} else {
							$get_product_wcct_meta = get_post_meta( $content_id );
							$product_meta          = WCCT_Common::get_parsed_query_results_meta( $get_product_wcct_meta );
							$parseObj              = wp_parse_args( $product_meta, $this->parse_default_args_by_trigger( $product_meta, $slug ) );
							$xl_transient_obj->set_transient( $cache_key, $parseObj, 7200, 'finale' );
						}
						$xl_cache_obj->set_cache( $cache_key, $parseObj, 'finale' );
					}

					if ( ! $parseObj ) {
						continue;
					}
					$get_parsed_data                  = $this->parse_key_value( $parseObj, $slug, 'product' );
					$wcct_trigger_data[ $content_id ] = $get_parsed_data;
				}
			}
		}

		return $this->wcct_triggers_public_data( $wcct_trigger_data, $return_key, $ID );
	}

	/**
	 * Calling non public property will return data from property `wcct_trigger_data`
	 *
	 * @param $name : name if property to be called
	 *
	 * @return bool|mixed Data on success, false otherwise
	 */
	public function __get( $name ) {

		return ( 'data' === $name ) ? $this->wcct_trigger_data : false;
	}
}
