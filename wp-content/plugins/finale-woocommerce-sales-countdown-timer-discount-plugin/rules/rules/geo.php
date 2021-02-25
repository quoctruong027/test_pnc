<?php

class WCCT_Rule_Geo_Country_Code extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'geo_country_code' );
	}

	public function get_possible_rule_operators() {

		$operators = array(
			'==' => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'!=' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		return WC()->countries->get_allowed_countries();
	}

	public function get_condition_input_type() {
		return 'Select';
	}

	public function is_match( $rule_data, $productID ) {
		$result = false;
		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			$location = WC_Geolocation::geolocate_ip();

			// Base fallback
			if ( empty( $location['country'] ) ) {
				$location = wc_format_country_state_string( apply_filters( 'woocommerce_customer_default_location', get_option( 'woocommerce_default_country' ) ) );
			}

			if ( isset( $location['country'] ) && ! empty( $location['country'] ) ) {
				$is_match = $location['country'] == $rule_data['condition'];
				$result   = $rule_data['operator'] == '==' ? $is_match : ! $is_match;
			}
		}

		return $result;
	}

}
