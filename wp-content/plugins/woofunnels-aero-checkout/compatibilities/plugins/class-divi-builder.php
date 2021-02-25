<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Divi_builder {


	public function __construct() {
		add_action( 'init', [ $this, 'remove_action' ] );
		add_filter( 'et_builder_enabled_builder_post_type_options', function ( $options ) {
			$options[ WFACP_Common::get_post_type_slug() ] = 'on';

			return $options;
		}, 999 );
		add_action( 'wfacp_template_removed', [ $this, 'remove_meta' ] );

	}

	public function remove_action() {
		if ( class_exists( 'ET_Builder_Plugin' ) ) {
			add_action( 'wfacp_checkout_page_found', [ $this, 'remove_wp_head' ] );
			if ( ( isset( $_GET['page'] ) ) && isset( $_GET['tab'] ) && ( $_GET['tab'] == 'wfacp-wizard' && $_GET['page'] == 'wfacp' ) ) {
				remove_action( 'admin_init', 'et_theme_builder_load_portability' );
			}


		}

	}

	public function remove_wp_head() {
		$page_design = WFACP_Common::get_page_design( WFACP_Common::get_id() );

		if ( 'embed_forms' == $page_design['selected_type'] ) {
			if ( ! WFACP_Common::is_customizer() ) {
				add_filter( 'wfacp_embed_form_allow_header', '__return_false' );

				return;
			}
		}
	}

	public function remove_meta( $wfacp_id ) {
		if ( class_exists( 'ET_Builder_Plugin' ) && $wfacp_id > 0 ) {
			global $wpdb;
			$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => '_et_pb_use_builder', 'post_id' => $wfacp_id ] );
		}
	}


}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Divi_builder(), 'Divi_builder' );
