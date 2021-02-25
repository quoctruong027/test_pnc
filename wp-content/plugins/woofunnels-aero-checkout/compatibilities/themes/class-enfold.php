<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Theme_Enfold {

	public function __construct() {

		/* checkout page */
		add_action( 'wfacp_checkout_page_found', [ $this, 'dequeue_actions' ] );

	}

	public function dequeue_actions() {
		if ( class_exists( 'aviaAssetManager' ) ) {
			$instance = WFACP_Common::remove_actions( 'wp_enqueue_scripts', 'aviaAssetManager', 'try_minifying_scripts' );
			add_action( 'wp_enqueue_scripts', array( $instance, 'try_minifying_scripts' ), 11 );
		}
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Theme_Enfold(), 'enfold' );
