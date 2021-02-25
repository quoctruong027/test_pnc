<?php

class WFOCU_Compatibility_With_WPML_MultiCurrency {

	public function __construct() {

		add_filter( 'wfocu_front_offer_url', array( $this, 'modify_link' ) );
	}

	public function is_enable() {
		global $woocommerce_wpml;

		if ( class_exists( 'woocommerce_wpml' ) && $woocommerce_wpml instanceof woocommerce_wpml ) {
			return true;
		}

		return false;

	}


	/**
	 *
	 * Modifies the amount for the fixed discount given by the admin in the currency selected.
	 *
	 * @param integer|float $price
	 *
	 * @return float
	 */
	public function alter_fixed_amount( $price, $currency = null ) {
		global $woocommerce_wpml;

		return $woocommerce_wpml->get_multi_currency()->prices->convert_price_amount( $price );
	}

	function get_fixed_currency_price_reverse( $price, $from = null, $base = null ) {
		global $woocommerce_wpml;

		$price = $woocommerce_wpml->get_multi_currency()->prices->unconvert_price_amount( $price, $from );

		return $price;
	}

	public function modify_link( $link ) {

		if ( ! $this->is_enable() ) {
			return $link;
		}
		global $wpml_url_converter;

		return $wpml_url_converter->convert_url( $link );
	}


}

WFOCU_Plugin_Compatibilities::register( new WFOCU_Compatibility_With_WPML_MultiCurrency(), 'woowpmlmulticurrency' );



