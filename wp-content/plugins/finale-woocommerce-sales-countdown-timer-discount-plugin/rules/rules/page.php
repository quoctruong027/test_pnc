<?php

class WCCT_Rule_Single_Page extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'single_page' );
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
		return 'Page_Select';
	}

	public function is_match( $rule_data, $product_id ) {
		$wp_query = WCCT_Common::$wcct_query;
		$result   = ( 'in' === $rule_data['operator'] ) ? false : true;

		if ( ! is_object( $wp_query ) ) {
			return $this->return_is_match( $result, $rule_data );
		}

		$post_query = $wp_query->get_queried_object();

		if ( is_shop() ) {
			$in     = (bool) ( in_array( wc_get_page_id( 'shop' ), $rule_data['condition'] ) );
			$result = ( 'in' === $rule_data['operator'] ) ? $in : ! $in;
		} else {
			/**
			 * if current page is not WordPress post type single page, then always return true
			 */
			if ( isset( $rule_data['operator'] ) && ( 'notin' === $rule_data['operator'] ) && ! is_page() ) {
				if ( ! is_front_page() && is_home() ) {
					// static blog page
					$in = (bool) ( ( $post_query->ID == get_option( 'page_for_posts', '0' ) ) && in_array( $post_query->ID, $rule_data['condition'] ) );
					if ( true === $in ) {
						return $this->return_is_match( false, $rule_data );
					}
				}

				return $this->return_is_match( true, $rule_data );
			}

			if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) && $post_query instanceof WP_Post ) {
				if ( is_front_page() && is_home() ) {
					// Default homepage, nothing to do
				} elseif ( is_front_page() ) {
					// static homepage and available in rules
					$in     = (bool) ( ( $post_query->ID == get_option( 'page_on_front', '0' ) ) && in_array( $post_query->ID, $rule_data['condition'] ) );
					$result = $rule_data['operator'] == 'in' ? $in : ! $in;
				} elseif ( is_home() ) {
					// blog page and available in rules
					$in     = (bool) ( ( $post_query->ID == get_option( 'page_for_posts', '0' ) ) && in_array( $post_query->ID, $rule_data['condition'] ) );
					$result = $rule_data['operator'] == 'in' ? $in : ! $in;
				} else {
					$in     = (bool) ( in_array( $post_query->ID, $rule_data['condition'] ) );
					$result = $rule_data['operator'] == 'in' ? $in : ! $in;
				}
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Post_Type extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'post_type' );
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

		$post_types = get_post_types( array(
			'public' => true,
		), 'objects' );
		if ( $post_types && ! is_wp_error( $post_types ) ) {
			foreach ( $post_types as $post_key => $post_type ) {
				$result[ $post_key ] = $post_type->label;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $product_id ) {
		$result = false;

		if ( ! isset( $rule_data['condition'] ) || ! is_array( $rule_data['condition'] ) || '0' == count( $rule_data['condition'] ) || ! isset( $rule_data['operator'] ) ) {
			return $this->return_is_match( $result, $rule_data );
		}

		if ( is_shop() ) {
			$in     = (bool) ( in_array( 'page', $rule_data['condition'] ) );
			$result = ( 'in' == $rule_data['operator'] ) ? $in : ! $in;
		} else {
			if ( is_front_page() && is_home() ) {
				// Default homepage, nothing to do
			} elseif ( is_front_page() || is_home() ) {
				// static homepage or blog page
				$in     = (bool) ( in_array( 'page', $rule_data['condition'] ) );
				$result = ( 'in' == $rule_data['operator'] ) ? $in : ! $in;

				return $this->return_is_match( $result, $rule_data );
			} elseif ( false === is_singular() ) {
				$result = ( 'in' == $rule_data['operator'] ) ? false : true;

				return $this->return_is_match( $result, $rule_data );
			}

			$in = false;

			foreach ( $rule_data['condition'] as $slug ) {
				if ( is_singular( $slug ) ) {
					$in = true;
					break;
				}
			}
			$result = $rule_data['operator'] == 'in' ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}
