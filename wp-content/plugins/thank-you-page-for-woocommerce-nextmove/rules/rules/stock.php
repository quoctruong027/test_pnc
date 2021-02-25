<?php
defined( 'ABSPATH' ) || exit;

class xlwcty_Rule_Stock_Status extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'stock_status' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'==' => __( 'is', 'thank-you-page-for-woocommerce-nextmove' ),
			'!=' => __( 'is not', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$options = array(

			'1' => __( 'In Stock', 'thank-you-page-for-woocommerce-nextmove' ),
			'0' => __( 'Out of Stock', 'thank-you-page-for-woocommerce-nextmove' ),
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
			$in = $product->is_in_stock();
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

class xlwcty_Rule_Stock_Level extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'stock_level' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'!=' => __( 'is not equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'>'  => __( 'is greater than', 'thank-you-page-for-woocommerce-nextmove' ),
			'<'  => __( 'is less than', 'thank-you-page-for-woocommerce-nextmove' ),
			'>=' => __( 'is greater or equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'=<' => __( 'is less or equal to', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data, $productID ) {
		global $post;
		$result  = false;
		$product = wc_get_product( $productID );
		if ( $product && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$stock = $product->get_stock_quantity();
			$value = (float) $rule_data['condition'];

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

		return $this->return_is_match( $result, $rule_data );
	}

}


class xlwcty_Rule_Manage_Stock extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'manage_stock' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'is' => __( 'is', 'thank-you-page-for-woocommerce-nextmove' ),

		);

		return $operators;
	}


	public function get_possibile_rule_values() {
		$options = array(
			'yes' => __( 'Yes', 'thank-you-page-for-woocommerce-nextmove' ),
			'no'  => __( 'No', 'thank-you-page-for-woocommerce-nextmove' ),
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

			if ( $rule_data['condition'] == 'yes' ) {
				$result = $product->manage_stock == 'yes';
			} else {
				$result = $product->manage_stock == 'no';
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}
