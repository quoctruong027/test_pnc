<?php

class WCCT_Rule_Stock_Status extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'stock_status' );
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
			'0' => __( 'Out of stock', 'woocommerce' ),
			'1' => __( 'In stock', 'woocommerce' ),
		);

		$options = apply_filters( 'wcct_rule_stock_status', $options );

		return $options;
	}

	public function get_condition_input_type() {
		return 'Select';
	}

	public function is_match( $rule_data, $product_id ) {
		$result  = false;
		$product = wc_get_product( $product_id );

		if ( $product && $product instanceof WC_Product && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			if ( '0' == $rule_data['condition'] || '1' == $rule_data['condition'] ) {
				$in = $product->is_in_stock();
				if ( '==' == $rule_data['operator'] ) {
					$result = ( '1' == $rule_data['condition'] ) ? $in : ! $in;
				} elseif ( '!=' == $rule_data['operator'] ) {
					$result = ! ( ( '1' == $rule_data['condition'] ) ? $in : ! $in );
				}
			} elseif ( '2' == $rule_data['condition'] ) {
				$in = $product->is_on_backorder();
				if ( '==' == $rule_data['operator'] ) {
					$result = ( true === $in ) ? true : false;
				} elseif ( '!=' == $rule_data['operator'] ) {
					$result = ( true === $in ) ? false : true;
				}
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Stock_Level extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'stock_level' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'!=' => __( 'is not equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'>'  => __( 'is greater than', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'<'  => __( 'is less than', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'>=' => __( 'is greater or equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'=<' => __( 'is less or equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data, $product_id ) {

		$result  = false;
		$product = wc_get_product( $product_id );
		if ( $product && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			if ( in_array( $product->get_type(), WCCT_Common::get_variable_league_product_types() ) ) {
				$total_stock = WCCT_Common::get_total_stock( $product );
				$value       = (int) $rule_data['condition'];
				switch ( $rule_data['operator'] ) {
					case '==':
						$result = ( $total_stock == $value );
						break;
					case '!=':
						$result = ( ! ( $total_stock == $value ) );
						break;
					case '>':
						$result = ( $total_stock > $value );
						break;
					case '<':
						$result = ( $total_stock < $value );
						break;
					case '>=':
						$result = ( $total_stock >= $value );
						break;
					case '=<':
						$result = ( $total_stock <= $value );
						break;
					default:
						$result = false;
						break;
				}
			} else {
				$stock = $product->get_stock_quantity();
				$value = (float) $rule_data['condition'];

				if ( null === $stock ) {
					return $this->return_is_match( true, $rule_data );
				}

				switch ( $rule_data['operator'] ) {
					case '==':
						$result = $stock == $value;
						break;
					case '!=':
						$result = $stock != $value;
						break;
					case '>':
						$result = $stock > $value;
						break;
					case '<':
						$result = $stock < $value;
						break;
					case '>=':
						$result = $stock >= $value;
						break;
					case '<=':
						$result = $stock <= $value;
						break;
					default:
						$result = false;
						break;
				}
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}


class WCCT_Rule_Manage_Stock extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'manage_stock' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'is' => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}


	public function get_possible_rule_values() {
		$options = array(
			'yes' => __( 'Enabled', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'no'  => __( 'Disabled', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $options;
	}

	public function get_condition_input_type() {
		return 'Select';
	}

	public function is_match( $rule_data, $product_id ) {
		global $post;
		$result  = false;
		$product = wc_get_product( $product_id );

		if ( $product && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			if ( $rule_data['condition'] == 'yes' ) {
				$result = ( $product->managing_stock() === true );
			} else {
				$result = ( $product->managing_stock() === false );
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}
