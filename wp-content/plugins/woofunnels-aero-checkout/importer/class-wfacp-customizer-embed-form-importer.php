<?php

/**
 * Elementor template library local source.
 *
 * Elementor template library local source handler class is responsible for
 * handling local Elementor templates saved by the user locally on his site.
 *
 * @since 1.0.0
 */

class WFACP_Customizer_Embed_Form_Importer implements WFACP_Import_Export {
	public function __construct() {

	}

	public function import( $aero_id, $slug, $is_multi = 'no' ) {
		WFACP_Common::delete_page_layout( $aero_id );
		$data = WFACP_Core()->importer->get_remote_template( $slug, 'pre-built' );

		if ( isset( $data['error'] ) ) {
			return $data;
		}
		$data = json_decode( $data['data'], true );
		wp_update_post( [ 'ID' => $aero_id, 'post_content' => '[wfacp_forms]' ] );

		WFACP_Common::update_page_layout( $aero_id, $data['page_layout'], true );


		if ( isset( $data['page_settings'] ) ) {
			WFACP_Template_Importer::update_import_page_settings( $aero_id, $data['page_settings'] );
		}

		if ( isset( $data['wfacp_product_switcher_setting'] ) ) {
			update_post_meta( $aero_id, '_wfacp_product_switcher_setting', $data['wfacp_product_switcher_setting'] );
		}


		if ( isset( $data['default_customizer_value'] ) && is_array( $data['default_customizer_value'] ) ) {

			$customizer = $data['default_customizer_value'];

			$final_data = [];
			foreach ( $customizer as $key => $value ) {

				$final_data = array_merge( $final_data, $value );
			}
			if ( ! empty( $final_data ) ) {
				update_option( WFACP_SLUG . '_c_' . $aero_id, $final_data );
			}

		}

		update_post_meta( $aero_id, 'ct_other_template', '-1' );
		update_post_meta( $aero_id, '_wp_page_template', 'wfacp-full-width.php' );

		return [ 'status' => true ];
	}


	public function export( $aero_id, $slug ) {
		return [];
	}

}

if ( class_exists( 'WFACP_Template_Importer' ) ) {
	WFACP_Template_Importer::register( 'embed_forms', new WFACP_Customizer_Embed_Form_Importer() );
}