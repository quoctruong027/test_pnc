<?php

class WCCT_Rule_Single_Post_Post_Type extends WCCT_Rule_Base {


	public function __construct() {
		parent::__construct( 'single_post_post_type' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result      = array();
		$postr_types = get_post_types( '', 'objects' );
		foreach ( $postr_types as $post ) {

			$result[ $post->name ] = $post->label;
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $productID ) {

		/**
		 * @deprecated but giving backward compatibility
		 */
		return true;
		$result = $rule_data['operator'] == 'in' ? false : true;

		if ( ! is_singular() ) {
			return $this->return_is_match( $result, $rule_data );
		}
		$post = WCCT_Common::$wcct_post;
		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$in     = (bool) ( is_singular() && in_array( $post->post_type, $rule_data['condition'] ) );
			$result = $rule_data['operator'] == 'in' ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );

	}

}


class WCCT_Rule_Single_Post_Taxonomy extends WCCT_Rule_Base {


	public function __construct() {
		parent::__construct( 'single_post_taxonomy' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Term_Select';
	}

	public function is_match( $rule_data, $productID ) {

		/**
		 * @deprecated but giving backward compatibility
		 */
		return true;
		$result = false;
		if ( ! is_singular() ) {
			return $this->return_is_match( $result, $rule_data );
		}
		$post = WCCT_Common::$wcct_post;

		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			$in = false;
			foreach ( $rule_data['condition'] as $condition ) {
				$split = explode( ':', $condition );

				$product_types = wp_get_post_terms( $post->ID, $split[0], array(
					'fields' => 'slugs',
				) );
				$in            = (bool) ( in_array( $split[1], $product_types ) );
				if ( $in === true ) {
					$in = true;
					break;
				}
			}

			$result = $rule_data['operator'] == 'in' ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );

	}

}
