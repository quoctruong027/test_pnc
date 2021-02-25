<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_Active_OxygenBuilder {
	private $execute = '';

	public function __construct() {
		/* checkout page */
		add_filter( 'wfacp_skip_add_to_cart', [ $this, 'wfacp_skip_add_to_cart' ] );
		add_action( 'wfacp_checkout_page_found', [ $this, 'remove_wp_head' ] );
		add_action( 'wfacp_run_shortcode_before', [ $this, 'oxygen_builder_shortcode' ] );
		add_filter( 'wfacp_skip_form_printing', [ $this, 'show_embed_form' ], 11 );
		add_filter( 'show_admin_bar', [ $this, 'remove_admin_bar' ], 100 );

	}

	public function wfacp_skip_add_to_cart( $status ) {
		if ( defined( 'CT_VERSION' ) && class_exists( 'CT_API' ) ) {
			if ( $this->is_xlink_open() ) {
				return true;
			}
		}

		return $status;
	}

	public function remove_wp_head() {

		if ( defined( 'CT_VERSION' ) ) {

			if ( wfacp_is_elementor() ) {
				remove_action( 'wp_head', 'ct_footer_styles_hook' );
				remove_action( 'wp_head', 'oxy_print_cached_css', 999999 );
				remove_action( 'wp_head', 'oxygen_vsb_iframe_styles' );
				remove_action( 'wp_head', 'add_web_font', 0 );

			} else {
				$page_design = WFACP_Common::get_page_design( WFACP_Common::get_id() );

				if ( 'embed_forms' == $page_design['selected_type'] ) {
					if ( ! WFACP_Common::is_customizer() ) {
						add_filter( 'wfacp_embed_form_allow_header', '__return_false' );
						add_filter( 'wfacp_allow_printing_shortcode_direct', '__return_true' );

						return;
					}
				}

				remove_action( 'ct_builder_start', 'ct_templates_buffer_start' );
				remove_action( 'ct_builder_end', 'ct_templates_buffer_end' );
				if ( $this->is_xlink_open() ) {
					$template_loader = WFACP_Core()->template_loader;
					remove_action( 'template_redirect', [ $template_loader, 'setup_preview' ], 99 );
				}
			}
		}

	}


	function oxygen_builder_shortcode( $shortcode_exist ) {

		if ( isset( $_REQUEST['ct_builder'] ) ) {
			return $shortcode_exist;
		}
		//return $shortcode_exist;
		if ( true === $shortcode_exist ) {
			return $shortcode_exist;
		}
		if ( function_exists( 'ct_template_shortcodes' ) ) {
			global $post;
			$shortcodes     = get_post_meta( $post->ID, "ct_builder_shortcodes", true );
			$start_position = strpos( $shortcodes, '[wfacp_forms' );
			if ( false !== $start_position ) {
				$shortcode_string = substr( $shortcodes, $start_position );
				$closing_position = strpos( $shortcode_string, ']', 1 );
				if ( false !== $closing_position ) {
					$shortcode_string = substr( $shortcodes, $start_position, $closing_position + 1 );
					if ( strlen( $shortcode_string ) > 0 ) {
						do_shortcode( $shortcode_string );
					}
				}
			}
		}

		return $shortcode_exist;
	}

	public function remove_admin_bar( $status ) {

		if ( isset( $_REQUEST['ct_builder'] ) ) {
			return false;
		}

		return $status;
	}

	public function is_xlink_open() {
		if ( isset( $_REQUEST['xlink'] ) || isset( $_REQUEST['nouniversal'] ) ) {
			return true;
		}

		return false;
	}

	public function show_embed_form( $status ) {
		if ( defined( 'CT_VERSION' ) ) {
			$template = wfacp_template();

			if ( $template instanceof WFACP_Template_Common ) {
				$data = $template->get_selected_register_template();
				global $post;
				if ( 'embed_forms' == $data['template_type'] && ! is_null( $post ) && $post->ID == WFACP_Common::get_id() ) {
					$status = false;
				}
			}
		}

		return $status;
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Active_OxygenBuilder(), 'oxygen_builder' );
