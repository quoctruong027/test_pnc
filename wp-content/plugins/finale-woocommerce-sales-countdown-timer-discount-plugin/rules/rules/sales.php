<?php

class WCCT_Rule_Sale_Status extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'sale_status' );
	}

	public function get_possible_rule_operators() {

		$operators = array(
			'==' => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'!=' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$options = array(
			'1' => __( 'On Sale', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $options;
	}

	public function get_condition_input_type() {
		return 'Select';
	}

	public function is_match( $rule_data, $productID ) {
		global $post;
		$result  = false;
		$product = wc_get_product( $productID );

		if ( $product && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$in = $product->is_on_sale();
			if ( $rule_data['operator'] == '==' ) {
				$result = $rule_data['condition'] == 1 ? $in : ! $in;
			}

			if ( $rule_data['operator'] == '!=' ) {
				$result = ! ( $rule_data['condition'] == 1 ? $in : ! $in );
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Sale_Schedule extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'sale_schedule' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'>=' => __( 'starts', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'=<' => __( 'ends', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Date';
	}

	public function is_match( $rule_data, $product_id ) {
		global $post;
		$result = false;
		if ( $product_id && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$start_date = get_post_meta( $product_id, '_sale_price_dates_from', true );
			$end_date   = get_post_meta( $product_id, '_sale_price_dates_to', true );

			switch ( $rule_data['operator'] ) {
				case '>=':
					if ( $start_date ) {
						$result = strtotime( date( 'Y-m-d' ) ) >= strtotime( $start_date );
					}
					break;
				case '<=':
					if ( $end_date ) {
						$result = strtotime( date( 'Y-m-d' ) ) <= strtotime( $end_date );
					}
					break;
				default:
					$result = false;
					break;
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}
