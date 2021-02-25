<?php

class WFACP_Builder_Embed_Compatibility {
	public function __construct() {

		add_filter( 'wfacp_template_class', [ $this, 'assign_embed_form_conditionally' ], 10, 4 );
	}


	public function assign_embed_form_conditionally( $template_file, $locate_template, $templates, $loader ) {
		//return $template_file;

		$id = WFACP_Common::get_id();
		if ( ! isset( $locate_template['support_embed_form'] ) ) {
			return $template_file;
		}

		if ( isset( $_REQUEST['elementor-preview'] ) ) {
			return $template_file;
		}
		$posts = get_post( $id );
		if ( '' == $posts->post_content ) {
			return $template_file;
		}


		$embed_type          = $locate_template['support_embed_form'];
		$embed_template_file = $templates['embed_forms'][ $embed_type ]['path'];

		if ( WFACP_Common::is_customizer() ) {
			$template_file = $embed_template_file;
		} else if ( ! is_admin() && ! wp_doing_ajax() ) {
			$status = WFACP_Core()->embed_forms->check_shortcode_exist( $posts->post_content );
			if ( true == $status ) {
				$template_file = $embed_template_file;
				add_filter( 'wfacp_print_elementor_widget', '__return_false' );
			}

		}

		return $template_file;
	}
}

new WFACP_Builder_Embed_Compatibility();