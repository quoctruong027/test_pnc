<?php

if ( class_exists( 'WFACP_Core' ) ) {
	class WCCT_Rule_WFACP_Page extends WCCT_Rule_Base {
		public $supports = array( 'cart' );

		public function __construct() {
			parent::__construct( 'wfacp_page' );
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
			$data   = WFACP_Common::get_saved_pages();

			if ( is_array( $data ) && count( $data ) > 0 ) {

				foreach ( $data as $v ) {
					$result[ $v['ID'] ] = $v['post_title'];
				}
			}

			return $result;
		}

		public function get_condition_input_type() {
			return 'Chosen_Select';
		}

		public function is_match( $rule_data, $env = 'cart' ) {
			global $post;
			$wfacp_id = 0;
			if ( ! is_null( $post ) && $post instanceof WP_Post ) {
				$wfacp_id = $post->ID;
			}
			$result = false;

			if ( empty( $wfacp_id ) || 'wfacp_checkout' !== $post->post_type ) {
				return $this->return_is_match( $result, $rule_data );
			}


			if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
				$in = false;
				if ( in_array( $wfacp_id, $rule_data['condition'] ) ) {
					$in = true;
				}
				$result = 'in' === $rule_data['operator'] ? $in : ! $in;
			}

			return $this->return_is_match( $result, $rule_data );
		}

	}
}


