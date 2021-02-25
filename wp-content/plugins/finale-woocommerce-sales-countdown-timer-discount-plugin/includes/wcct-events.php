<?php

class WCCT_Events {

	/**
	 * Process events to manipulate data arguments based on several events actions and conditions
	 *
	 * @param array $events array of events
	 * @param array $data array of data to modify
	 * @param array $helping_data array of data to have helping arguments.
	 * @param string $type Type of discount
	 *
	 * @return array|bool|mixed
	 */
	public static function process_events( $events, $data, $helping_data, $type = 'discount' ) {

		wcct_force_log( 'Initiating Processing Event of type ' . $type . ' For Product ID: ' . $helping_data['postID'] . ' AND Campaign ID: ' . $helping_data['campaignID'] );

		switch ( $type ) {
			case 'discount':
				return self::_process_discount( $events, $data, $helping_data );
				break;
			case 'regular_price':
				return self::_process_regular_price( $events, $data, $helping_data );
				break;
			case 'available_unit':
				return self::_process_available_unit( $events, $data, $helping_data );
				break;
			case 'sold_unit':
				return self::_process_sold_unit( $events, $data, $helping_data );
				break;
		}
	}

	/**
	 * Process Discounts Rule
	 *
	 * @param $events
	 * @param $data
	 * @param $helping_data
	 *
	 * @return array
	 */
	protected static function _process_discount( $events, $data, $helping_data ) {
		$deal_amount = array();
		foreach ( $events as $key => $event ) {

			if ( $event['entity'] !== 'discount' ) {
				continue;
			}

			/**
			 * setting up default values into the array of events
			 */
			$default_args = array(
				'event_value_min' => 0,
				'event_value_max' => '9999999999',
			);

			$event = wp_parse_args( $event, $default_args );

			if ( $event['event_value_min'] == '' ) {
				$event['event_value_min'] = $default_args['event_value_min'];
			}

			if ( $event['event_value_max'] == '' ) {
				$event['event_value_max'] = $default_args['event_value_max'];
			}

			//swapping if we cannot process the min-max
			if ( $event['event_value_min'] > $event['event_value_max'] ) {
				$temp                     = $event['event_value_min'];
				$event['event_value_min'] = $event['event_value_max'];
				$event['event_value_max'] = $temp;
			}

			/**
			 * Validating each rule by the logic that those rules also returns true if they need to be executed in the series
			 * For eg: if product sold qty is 2, the rule for sold qty 0-1 will be executed now
			 */

			if ( self::_validate_rule( $event, $helping_data, 'yes' ) ) {
				$type          = ( strpos( $event['number'], '%' ) === false ) ? 'fixed_price' : 'percentage';
				$value         = trim( str_replace( '%', '', $event['number'] ) );
				$deal_amount[] = $value;
			}
		}

		/**
		 * assigning max deal amount found by iterating all the rules
		 */
		if ( empty( $deal_amount ) ) {
			if ( is_array( $data ) && $data && count( $data ) > 0 ) {

				return ( isset( $data[ $helping_data['campaignID'] ] ) ? $data[ $helping_data['campaignID'] ] : false );
			}

			return false;
		}
		if ( is_array( $data ) && $data && count( $data ) > 0 ) {
			foreach ( $data as $id => $deal ) {
				$data[ $id ]['type']             = $type;
				$data[ $id ]['deal_amount']      = current( $deal_amount );
				$data[ $id ]['override']         = false;
				$data[ $id ]['event_overridden'] = true;
			}
		} else {
			wcct_force_log( 'Events: Setting up new rule data ' );
			$data = array(
				$helping_data['campaignID'] => array(
					'mode'             => '',
					'type'             => $type,
					'deal_amount'      => current( $deal_amount ),
					'start_time'       => $helping_data['campaign']['start_timestamp'],
					'end_time'         => $helping_data['campaign']['end_timestamp'],
					'campaign_type'    => $helping_data['campaign']['type'],
					'campaign_id'      => $helping_data['campaignID'],
					'override'         => false,
					'event_overridden' => true,
				),
			);
		}

		return current( $data );
	}

	/**
	 * Validating Rules for all the conditions, so that respective action can be triggered
	 *
	 * @param $rule_data Array All rules
	 * @param $campaign_data Array Current campaign data to be used to validate rules
	 * @param $discount string whether its a call for the discount event or not
	 *
	 * @return bool True on success False otherwise
	 */
	protected static function _validate_rule( $rule_data, $campaign_data, $discount = 'no' ) {

		wcct_force_log( 'Validating Rule Condition ' . $rule_data['event'] );

		switch ( $rule_data['event'] ) {
			case 'units_left':
				//check if goal exists
				if ( $campaign_data['goals'] ) {
					wcct_force_log( 'Termination: Unable to find any set goals for units_left condition' );

					//Getting product object
					$product_obj = wc_get_product( $campaign_data['postID'] );

					if ( ! is_wp_error( $product_obj ) && is_object( $product_obj ) ) {

						$current_goal          = current( $campaign_data['goals'] );
						$get_campaign_instance = WCCT_Core()->public;

						$get_goal_data = $get_campaign_instance->wcct_get_goal_object( $current_goal, $campaign_data['postID'], true );

						if ( self::is_product_compatible_for_goal_rule_event( $get_goal_data, $product_obj, $campaign_data ) ) {

							$key_for_sold = 'sold_out';

							$sold_out        = (int) $get_goal_data[ $key_for_sold ] + ( ( isset( $campaign_data['sold_units'] ) ) ? $campaign_data['sold_units'] : 0 );
							$available_units = $get_goal_data['quantity'] + ( ( isset( $campaign_data['available_units'] ) ) ? $campaign_data['available_units'] : 0 );
							$leftamt         = $available_units - $sold_out;

							/**
							 * Checking units left by max event only to make the current rule work in the scenario. Value should be greater or equal to left amt , means it lies in the scope of execution
							 * Eg: if current unit left is 5, then rule for 10 - 20 units left will run because 20 >= 5
							 */

							/**
							 * Also if units left is '10' then rule for units left is 10-20 will not hold true as we do not need to work with min limit, because it was serving the current row to the 11th purchase if 30 is total stock.
							 */
							if ( $discount == 'yes' ) {
								if ( $get_goal_data && ( $rule_data['event_value_max'] >= $leftamt ) && ( $rule_data['event_value_min'] < $leftamt ) ) {

									wcct_force_log( 'Event Rule units_left returns True case 1.' );

									return true;
								} else {
									wcct_force_log( 'Event Rule units_left returns False.' );

									return false;
								}
							} else {
								if ( $get_goal_data && ( $rule_data['event_value_max'] >= $leftamt ) ) {

									wcct_force_log( 'Event Rule units_left returns True case 1.' );

									return true;
								} else {
									wcct_force_log( 'Event Rule units_left returns False.' );

									return false;
								}
							}
						}
					}
				}
				break;
			case 'units_sold':
				if ( $campaign_data['goals'] ) {

					$current_goal          = current( $campaign_data['goals'] );
					$product_obj           = wc_get_product( $campaign_data['postID'] );
					$get_campaign_instance = WCCT_Core()->public;

					$get_goal_data = $get_campaign_instance->wcct_get_goal_object( $current_goal, $campaign_data['postID'], true );
					if ( ! is_wp_error( $product_obj ) && is_object( $product_obj ) ) {
						if ( self::is_product_compatible_for_goal_rule_event( $get_goal_data, $product_obj, $campaign_data ) ) {

							$key_for_sold = 'sold_out';
							$sold_out     = (int) $get_goal_data[ $key_for_sold ] + ( ( isset( $campaign_data['sold_units'] ) ) ? $campaign_data['sold_units'] : 0 );

							/**
							 * Checking units sold by min event only to make the current rule work in the scenario. Value should be lesser or equal to sold amt , means it lies in the scope of execution
							 * Eg: if current unit sold is 5, then rule for 0 - 2 units sold will run because 0 <= 2
							 */

							/**
							 * Also if units sold is '5' then units sold is 0-5 will not hold true as we do not need to work max limit, because it was serving the current row to the 6th purchase.
							 */
							if ( $discount == 'yes' ) {
								if ( $get_goal_data && ( (int) $sold_out >= $rule_data['event_value_min'] ) && ( (int) $sold_out < $rule_data['event_value_max'] ) ) {
									wcct_force_log( 'Event Rule units_sold returns TRUE.' );

									return true;
								} else {
									wcct_force_log( 'Event Rule units_sold returns False.' );

									return false;
								}
							} else {

								if ( $get_goal_data && ( $rule_data['event_value_min'] <= (int) $sold_out ) ) {

									wcct_force_log( 'Event Rule units_sold returns TRUE.' );

									return true;
								} else {
									wcct_force_log( 'Event Rule units_sold returns False.' );

									return false;
								}
							}
						}
					}
				} else {
					wcct_force_log( 'Termination: Unable to find any set goals for units_sold condition' );

				}

				break;
			case 'days_left':
				$datetimeob     = new DateTime( 'now', new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
				$current_time   = $datetimeob->getTimestamp();
				$get_difference = $campaign_data['campaign']['end_timestamp'] - $current_time;

				$days_inseconds_min = $rule_data['event_value_min'] * DAY_IN_SECONDS;
				$days_inseconds_max = $rule_data['event_value_max'] * DAY_IN_SECONDS;

				if ( $discount == 'yes' ) {
					if ( $days_inseconds_min < $get_difference && $days_inseconds_max > $get_difference ) {
						wcct_force_log( 'Event Rule days_left returns True.' );

						return true;
					} else {
						wcct_force_log( 'Event Rule days_left returns False.' );

						return false;
					}
				} else {
					if ( $days_inseconds_max > $get_difference ) {
						wcct_force_log( 'Event Rule days_left returns True.' );

						return true;
					} else {
						wcct_force_log( 'Event Rule days_left returns False.' );

						return false;
					}
				}

				break;
			case 'hrs_left':
				$datetimeob     = new DateTime( 'now', new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
				$current_time   = $datetimeob->getTimestamp();
				$get_difference = $campaign_data['campaign']['end_timestamp'] - $current_time;

				$hour_inseconds_min = $rule_data['event_value_min'] * HOUR_IN_SECONDS;
				$hour_inseconds_max = $rule_data['event_value_max'] * HOUR_IN_SECONDS;

				if ( $discount == 'yes' ) {
					if ( $hour_inseconds_max > $get_difference && $hour_inseconds_min < $get_difference ) {
						return true;
					} else {
						return false;
					}
				} else {
					if ( $hour_inseconds_max > $get_difference ) {
						return true;
					} else {
						return false;
					}
				}

				break;
		}

		return false;
	}

	protected static function is_product_compatible_for_goal_rule_event( $goal, $product, $campaign_data ) {

		$manage_stock_check = true;

		if ( ! $goal ) {
			return false;
		}

		if ( ! is_array( $goal ) ) {
			return false;
		}

		if ( is_array( $goal ) && count( $goal ) < 1 ) {
			return false;
		}
		if ( in_array( $product->get_type(), WCCT_Common::get_simple_league_product_types() ) ) {
			$manage_stock_check = $product->managing_stock();
		}

		// in some cases manage stock returns blank, that's why below handling
		$manage_stock_check = ( $manage_stock_check ) ? true : false;

		if ( isset( $goal['type'] ) && $goal['type'] == 'same' && ! $manage_stock_check ) {
			return false;
		}

		if ( isset( $goal['type'] ) && $goal['type'] == 'same' && in_array( $product->get_type(), WCCT_Common::get_variable_league_product_types() ) && WCCT_Common::get_total_stock( $product ) <= 0 ) {
			// use <= for sometimes stock quantity goes to in negative
			return false;
		}

		return true;
	}

	protected static function _process_regular_price( $events, $data, $helping_data ) {

		$final_array = false;
		foreach ( $events as $key => $event ) {
			if ( $event['entity'] !== 'regular_price' ) {
				continue;
			}
			$default_args = array(
				'event_value_min' => 0,
				'event_value_max' => '9999999999',
			);
			$event        = wp_parse_args( $event, $default_args );

			if ( $event['event_value_min'] == '' ) {
				$event['event_value_min'] = $default_args['event_value_min'];
			}

			if ( $event['event_value_max'] == '' ) {
				$event['event_value_max'] = $default_args['event_value_max'];
			}
			//swapping if we cannot process the min-max
			if ( $event['event_value_min'] > $event['event_value_max'] ) {
				$temp                     = $event['event_value_min'];
				$event['event_value_min'] = $event['event_value_max'];
				$event['event_value_max'] = $temp;
			}

			if ( self::_validate_rule( $event, $helping_data ) ) {

				if ( ! isset( $event['number'] ) ) {
					return false;
				}
				$operator   = $event['operator_rest'];
				$is_percent = ( strpos( $event['number'], '%' ) === false ) ? false : true;
				$value      = trim( str_replace( '%', '', $event['number'] ) );

				$final_array[] = array(
					'operator'   => $operator,
					'is_percent' => $is_percent,
					'value'      => $value,
				);
			}
		}

		return $final_array;
	}

	/**
	 * Process available unit
	 *
	 * @param $events
	 * @param $data
	 * @param $helping_data
	 *
	 * @return mixed
	 */
	protected static function _process_available_unit( $events, $data, $helping_data ) {

		$available_units            = 0;
		$total_available_units      = 0;
		$total_available_percentage = 0;
		foreach ( $events as $key => $event ) {

			if ( $event['entity'] !== 'available_unit' ) {
				continue;
			}
			$default_args = array(
				'event_value_min' => 0,
				'event_value_max' => '9999999999',
			);
			$event        = wp_parse_args( $event, $default_args );

			if ( $event['event_value_min'] == '' ) {
				$event['event_value_min'] = $default_args['event_value_min'];
			}

			if ( $event['event_value_max'] == '' ) {
				$event['event_value_max'] = $default_args['event_value_max'];
			}
			//swapping if we cannot process the min-max
			if ( $event['event_value_min'] > $event['event_value_max'] ) {
				$temp                     = $event['event_value_min'];
				$event['event_value_min'] = $event['event_value_max'];
				$event['event_value_max'] = $temp;
			}
			$helping_data['available_units'] = $available_units;
			if ( self::_validate_rule( $event, $helping_data ) ) {

				$type     = ( strpos( $event['number'], '%' ) === false ) ? 'fixed_price' : 'percentage';
				$value    = trim( str_replace( '%', '', $event['number'] ) );
				$operator = $event['operator_rest'];

				if ( strpos( $event['number'], '%' ) !== false ) {
					$value_percentage           = (int) trim( str_replace( '%', '', $event['number'] ) );
					$total_available_percentage += $operator . '' . $value_percentage;
				} else {
					$value                 = trim( $event['number'] );
					$total_available_units += $operator . '' . $value;
				}
			}
		}
		if ( is_array( $data ) && $data && count( $data ) > 0 ) {

			foreach ( $data as $id => $deal ) {
				if ( $total_available_percentage != '0' ) {

					$data[ $id ]['events_units_percentage'] = $total_available_percentage;
					$data[ $id ]['is_event_modified']       = 'yes';
				}

				if ( $total_available_units != '0' ) {
					$data[ $id ]['event_units']       = $total_available_units;
					$data[ $id ]['is_event_modified'] = 'yes';
				}
			}
		}

		return $data;
	}

	protected static function _process_sold_unit( $events, $data, $helping_data ) {

		$total_sold_units      = 0;
		$total_sold_percentage = 0;
		foreach ( $events as $key => $event ) {
			$value            = 0;
			$value_percentage = 0;
			if ( $event['entity'] !== 'sold_unit' ) {
				continue;
			}
			$default_args = array(
				'event_value_min' => 0,
				'event_value_max' => '9999999999',
			);
			$event        = wp_parse_args( $event, $default_args );

			if ( $event['event_value_min'] == '' ) {
				$event['event_value_min'] = $default_args['event_value_min'];
			}

			if ( $event['event_value_max'] == '' ) {
				$event['event_value_max'] = $default_args['event_value_max'];
			}

			//swapping if we cannot process the min-max
			if ( $event['event_value_min'] > $event['event_value_max'] ) {
				$temp                     = $event['event_value_min'];
				$event['event_value_min'] = $event['event_value_max'];
				$event['event_value_max'] = $temp;
			}
			$helping_data['sold_units'] = $total_sold_units;
			if ( self::_validate_rule( $event, $helping_data ) ) {
				$operator = $event['operator_rest'];
				if ( strpos( $event['number'], '%' ) !== false ) {
					$value_percentage      = (int) trim( str_replace( '%', '', $event['number'] ) );
					$total_sold_percentage += $operator . '' . $value_percentage;
				} else {
					$value            = trim( $event['number'] );
					$total_sold_units += $operator . '' . $value;
				}
			}
		}

		if ( is_array( $data ) && $data && count( $data ) > 0 ) {

			foreach ( $data as $id => $deal ) {

				if ( $total_sold_percentage != '0' ) {

					$data[ $id ]['default_sold_out_percentage'] = $total_sold_percentage;
					$data[ $id ]['is_event_modified']           = 'yes';
				}

				if ( $total_sold_units != '0' ) {
					$data[ $id ]['default_sold_out']  = $total_sold_units;
					$data[ $id ]['is_event_modified'] = 'yes';
				}
			}
		}

		return $data;
	}

}
