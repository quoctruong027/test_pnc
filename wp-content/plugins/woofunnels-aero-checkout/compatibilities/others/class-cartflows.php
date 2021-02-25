<?php

class WFACP_CartFlows_Compatibility {
	public function __construct() {
		add_action( 'wfacp_checkout_page_found', [ $this, 'remove_template_redirect' ] );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_render_cart_flows_inline_js' ] );
		add_filter( 'wfacp_skip_checkout_page_detection', [ $this, 'disable_aero_checkout_on_cart_flows_template' ] );
	}

	public function remove_template_redirect() {
		if ( class_exists( 'Cartflows_Checkout_Markup' ) ) {
			WFACP_Common::remove_actions( 'template_redirect', 'Cartflows_Checkout_Markup', 'global_checkout_template_redirect' );
		}
	}

	public function remove_render_cart_flows_inline_js() {
		if ( class_exists( 'Cartflows_Tracking' ) ) {
			WFACP_Common::remove_actions( 'wp_head', 'Cartflows_Tracking', 'wcf_render_gtag' );
		}
	}

	public function disable_aero_checkout_on_cart_flows_template( $status ) {
		global $post;
		if ( ! is_null( $post ) && $post->post_type == 'cartflows_step' ) {
			$status = true;
		}

		return $status;
	}
}

return new WFACP_CartFlows_Compatibility();