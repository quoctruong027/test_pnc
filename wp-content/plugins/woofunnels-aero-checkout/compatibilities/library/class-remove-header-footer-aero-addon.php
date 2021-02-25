<?php

class WFACP_Compatibility_Aero_header_footer {


	public function __construct() {
		add_action( 'wfacp_before_loaded', [ $this, 'remove_old_header_footer_addon' ] );
	}

	public function remove_old_header_footer_addon() {
		WFACP_Common::remove_actions( 'wfacp_loaded', 'WFACPTHF_Core', 'register_templates' );
	}

}

new WFACP_Compatibility_Aero_header_footer();