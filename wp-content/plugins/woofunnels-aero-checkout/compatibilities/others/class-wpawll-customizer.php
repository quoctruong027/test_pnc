<?php

/**
 * this plugin create a js error when we open customizer page
 * Class WFACP_Wpawll
 */
class WFACP_Wpawll {

	public function __construct() {
		add_action( 'plugin_loaded', [ $this, 'actions' ], 11 );
	}

	public function actions() {
		if ( class_exists( 'WFACP_Common' ) && class_exists( 'WPAWLL_Customizer' ) && WFACP_Common::is_customizer() ) {
			WFACP_Common::remove_actions( 'customize_register', 'WPAWLL_Customizer', 'wpawll_customize_register' );
			WFACP_Common::remove_actions( 'customize_register', 'WPAWLL_Customizer', 'wpawll_customize_register' );
			WFACP_Common::remove_actions( 'customize_register', 'wpawll_tabs_customize_register' );
		}
	}
}

new WFACP_Wpawll();
