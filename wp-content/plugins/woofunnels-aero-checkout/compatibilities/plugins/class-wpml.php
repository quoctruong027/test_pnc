<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_WPML {

	public function __construct() {

		/* checkout page */
		add_filter( 'wfacp_wpml_checkout_page_id', [ $this, 'wfacp_wpml_checkout_page_id_function' ], 10, 1 );

	}

	public function wfacp_wpml_checkout_page_id_function( $overirde_checkout_page_id ) {

		if ( class_exists( 'WPML_TM_Records' ) ) {

			global $wpdb, $wpml_post_translations, $wpml_term_translations;
			$tm_records = new WPML_TM_Records( $wpdb, $wpml_post_translations, $wpml_term_translations );

			try {
				$translations = $tm_records->icl_translations_by_element_id_and_type_prefix( $overirde_checkout_page_id, 'post_wfacp_checkout' );
				if ( $translations->language_code() !== ICL_LANGUAGE_CODE ) {
					$element_id                = $tm_records->icl_translations_by_trid_and_lang( $translations->trid(), ICL_LANGUAGE_CODE )->element_id();
					$overirde_checkout_page_id = empty( $element_id ) ? $overirde_checkout_page_id : $element_id;
				}
			} catch ( Exception $e ) {
				//echo $e->getMessage();
			}
		}

		return $overirde_checkout_page_id;
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_WPML(), 'wfacp_wpml' );
