<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_Sg_optimizer {

	public function __construct() {
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_sg_optiomizer_hook' ] );

	}

	public function remove_sg_optiomizer_hook() {

		if ( class_exists( 'SiteGround_Optimizer\Combinator\Combinator' ) ) {
			WFACP_Common::remove_actions( 'wp_print_styles', 'SiteGround_Optimizer\Combinator\Combinator', 'pre_combine_header_styles' );
		}

	}


}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Sg_optimizer(), 'sg_optimizer' );
