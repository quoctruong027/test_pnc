<?php

/**
 * Elementor template library local source.
 *
 * Elementor template library local source handler class is responsible for
 * handling local Elementor templates saved by the user locally on his site.
 *
 * @since 1.0.0
 */

class WFACP_Divi_Importer implements WFACP_Import_Export {
	public function __construct() {

	}

	public function import( $aero_id, $slug, $is_multi = 'no' ) {
		WFACP_Common::delete_page_layout( $aero_id );


		return [ 'status' => true ];
	}


	public function export( $aero_id, $slug ) {
		return [];
	}


}

if ( class_exists( 'WFACP_Template_Importer' ) ) {
	WFACP_Template_Importer::register( 'divi', new WFACP_Divi_Importer() );
}