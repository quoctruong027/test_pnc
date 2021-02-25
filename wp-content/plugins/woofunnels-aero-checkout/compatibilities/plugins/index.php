<?php
// Silence is golden.
/**
 *
 */
//
add_action( 'wfacp_get_fragments', function () {
	if ( class_exists( 'RP_WCDPD_Controller_Methods_Product_Pricing' ) ) {
		$instance = RP_WCDPD_Controller_Methods_Product_Pricing::get_instance();
		$cart     = WC()->cart;
		$instance->cart_loaded_from_session( $cart );
	}
}, 10, 2 );
