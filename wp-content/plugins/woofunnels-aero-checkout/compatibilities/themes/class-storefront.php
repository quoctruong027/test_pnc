<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_storefront {

	public function __construct() {

		add_action( 'wfacp_checkout_page_found', [ $this, 'remove_actions' ] );
	}

	public function remove_actions() {

		if ( WFACP_Common::is_customizer() ) {
			WFACP_Common::remove_actions( 'customize_preview_init', 'Storefront_NUX_Starter_Content', 'update_homepage_content' );

		}

	}


}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_storefront(), 'storefront' );
