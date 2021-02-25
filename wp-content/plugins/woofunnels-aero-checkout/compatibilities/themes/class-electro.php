<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Electro {

	public function __construct() {
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_electro_hooks' ] );
		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );
	}

	public function remove_electro_hooks() {

		remove_action( 'customize_controls_print_styles', 'x_customizer_preloader' );

	}

	public function internal_css() {
		if ( class_exists( 'TGM_Plugin_Activation' ) ) {
			echo '<style>';
			echo 'body{overflow-x: visible;}';
			echo '</style>';

		}

	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Electro(), 'electro' );
