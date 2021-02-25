<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_Active_OGF {

	public function __construct() {

		/* checkout page */
		add_action( 'wfacp_checkout_page_found', [ $this, 'remove_actions' ] );
	}

	public function remove_actions() {
		if ( function_exists( 'ogf_customize_register' ) ) {
			remove_action( 'customize_register', 'ogf_customize_register' );
		}
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Active_OGF(), 'ogf' );
