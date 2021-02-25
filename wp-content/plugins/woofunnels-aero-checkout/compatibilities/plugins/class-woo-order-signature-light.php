<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_Woo_Order_Signature {
	public function __construct() {
		add_action( 'wfacp_checkout_page_found', [ $this, 'call_wosl_hook' ] );
	}

	public function call_wosl_hook() {
		if ( function_exists( 'swph_woo_sign_add_customer_signature' ) ) {
			if ( class_exists( 'WFACP_Core' ) ) {
				$is_global_checkout = WFACP_Core()->public->is_checkout_override();
				if ( $is_global_checkout === true ) {
					add_action( 'wfacp_template_before_payment', 'swph_woo_sign_add_customer_signature', 11 );
				}
			}
		}
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Woo_Order_Signature(), 'wosl' );
