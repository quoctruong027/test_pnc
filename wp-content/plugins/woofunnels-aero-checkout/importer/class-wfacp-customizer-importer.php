<?php

/**
 * Elementor template library local source.
 *
 * Elementor template library local source handler class is responsible for
 * handling local Elementor templates saved by the user locally on his site.
 *
 * @since 1.0.0
 */

class WFACP_Customizer_Importer implements WFACP_Import_Export {
	public function __construct() {

	}

	public function import( $aero_id, $slug, $is_multi = 'no' ) {
		WFACP_Common::delete_page_layout( $aero_id );

		if ( $slug === 'shopcheckout' ) {

			$pageProductSetting = [
				'coupons'                             => '',
				'enable_coupon'                       => 'false',
				'disable_coupon'                      => 'false',
				'hide_quantity_switcher'              => 'false',
				'enable_delete_item'                  => 'false',
				'hide_product_image'                  => 'false',
				'is_hide_additional_information'      => 'true',
				'additional_information_title'        => WFACP_Common::get_default_additional_information_title(),
				'hide_quick_view'                     => 'false',
				'hide_you_save'                       => 'true',
				'hide_best_value'                     => 'false',
				'best_value_product'                  => '',
				'best_value_text'                     => 'Best Value',
				'best_value_position'                 => 'above',
				'enable_custom_name_in_order_summary' => 'false',
				'autocomplete_enable'                 => 'false',
				'autocomplete_google_key'             => '',
				'preferred_countries_enable'          => 'false',
				'preferred_countries'                 => '',
				'product_switcher_template'           => 'default',
			];

			$product_settings                     = [];
			$product_settings['settings']         = $pageProductSetting;
			$product_settings['products']         = [];
			$product_settings['default_products'] = [];

			if ( is_array( $product_settings ) && count( $product_settings ) > 0 ) {
				update_post_meta( $aero_id, '_wfacp_product_switcher_setting', $product_settings );
			}
		}

		$data = WFACP_Core()->importer->get_remote_template( $slug, 'pre-built' );

		if ( isset( $data['error'] ) ) {
			return $data;
		}

		$data = json_decode( $data['data'], true );

		if ( isset( $data['page_layout'] ) ) {
			WFACP_Common::update_page_layout( $aero_id, $data['page_layout'], true );
		}


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


		return [ 'status' => true ];
	}


	public function export( $aero_id, $slug ) {
		return [];
	}


}

if ( class_exists( 'WFACP_Template_Importer' ) ) {
	WFACP_Template_Importer::register( 'pre_built', new WFACP_Customizer_Importer() );
}