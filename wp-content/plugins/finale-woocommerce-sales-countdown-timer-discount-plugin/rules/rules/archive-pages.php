<?php


class WCCT_Rule_Single_Product_Cat_Tax extends WCCT_Rule_Base {


	public function __construct() {

		parent::__construct( 'single_product_cat_tax' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();

		$terms = get_terms( 'product_cat', array(
			'hide_empty' => false,
		) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $productID ) {

		$result = $rule_data['operator'] == 'in' ? false : true;

		$wp_query = WCCT_Common::$wcct_query;
		if ( ! is_object( $wp_query ) ) {
			return $this->return_is_match( $result, $rule_data );
		}

		$get_tax = $wp_query->get_queried_object();

		if ( ! is_object( $get_tax ) || ! $get_tax instanceof WP_Term ) {
			return $this->return_is_match( $result, $rule_data );
		}
		$term_id = $get_tax->term_id;

		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$in     = (bool) ( in_array( $term_id, $rule_data['condition'] ) );
			$result = $rule_data['operator'] == 'in' ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}


class WCCT_Rule_Single_Product_Tags_Tax extends WCCT_Rule_Base {


	public function __construct() {

		parent::__construct( 'single_product_tags_tax' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();

		$terms = get_terms( 'product_tag', array(
			'hide_empty' => false,
		) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $productID ) {

		$result = false;

		$wp_query = WCCT_Common::$wcct_query;

		if ( ! is_object( $wp_query ) ) {
			return $this->return_is_match( $result, $rule_data );
		}
		$get_tax = $wp_query->get_queried_object();

		$term_id = $get_tax->term_id;

		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$in     = (bool) ( in_array( $term_id, $rule_data['condition'] ) );
			$result = $rule_data['operator'] == 'in' ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

