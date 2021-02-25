<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_Twint {

	public function __construct() {
		add_filter( 'wfacp_skip_checkout_page_detection', [ $this, 'skip_checkout_page' ] );
	}

	public function skip_checkout_page() {

		if ( function_exists( 'woocommerce_datatranscw_create_checkouts' ) ) {
			if ( isset( $_REQUEST['cwcontroller'] ) && 'redirection' == $_REQUEST['cwcontroller'] ) {
				return true;
			}

			// We need to be in checkout, to calculate the complete order total
			if ( isset( $GLOBALS['cwExternalCheckoutOrderTotal'] ) && $GLOBALS['cwExternalCheckoutOrderTotal'] ) {
				return true;
			}
			if ( function_exists( 'woocommerce_datatranscw_create_checkouts' ) && woocommerce_datatranscw_create_checkouts() ) {
				return true;
			}
		}
		return false;

	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Twint(), 'wc-twint' );
