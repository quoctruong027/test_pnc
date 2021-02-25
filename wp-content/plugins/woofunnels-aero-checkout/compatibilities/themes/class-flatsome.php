<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Theme_Flatsome {

	public function __construct() {

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_customizer_fields' ] );
		add_action( 'init', [ $this, 'add_term_conditions' ] );

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'add_terms_condition' ] );
		add_action( 'wfacp_before_process_checkout_template_loader', [ $this, 'add_terms_condition' ] );

	}

	public function add_terms_condition() {
		if ( function_exists( 'flatsome_fix_policy_text' ) ) {
			add_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 21 );
		}

	}

	public function remove_customizer_fields() {

		if ( function_exists( 'flatsome_checkout_scripts' ) ) {
			remove_action( 'wp_enqueue_scripts', 'flatsome_checkout_scripts', 100 );
		}

		if ( WFACP_Common::is_customizer() ) {

			return;
		}

		$page_design = WFACP_Common::get_page_design( WFACP_Common::get_id() );

		if ( 'embed_forms' == $page_design['selected_type'] ) {
			if ( ! WFACP_Common::is_customizer() ) {
				add_filter( 'wfacp_embed_form_allow_header', '__return_false' );

				return;
			}
		}


	}

	public function add_term_conditions() {
		if ( function_exists( 'flatsome_checkout_scripts' ) ) {
			remove_action( 'woocommerce_checkout_after_order_review', 'wc_checkout_privacy_policy_text', 1 );


		}

		if ( function_exists( 'add_ux_builder_post_type' ) ) {
			add_ux_builder_post_type( WFACP_Common::get_post_type_slug() );
		}

	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Theme_Flatsome(), 'flatsome' );
