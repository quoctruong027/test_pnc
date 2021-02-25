<?php

/**
 * Elementor template library local source.
 *
 * Elementor template library local source handler class is responsible for
 * handling local Elementor templates saved by the user locally on his site.
 *
 * @since 1.0.0
 */

class WFACP_Elementor_Importer extends Elementor\TemplateLibrary\Source_Local implements WFACP_Import_Export {

	private $is_multi = 'no';
	private $slug = '';
	public $delete_page_meta = true;

	public function __construct() {
		//DO NOT DELETE
	}

	public function import( $aero_id, $slug, $is_multi = 'no' ) {
		$this->slug     = $slug;
		$this->is_multi = $is_multi;

		if ( false !== strpos( $slug, 'elementor_' ) ) {
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

		if ( 'elementor_1' === $slug ) {
			wp_update_post( [ 'ID' => $aero_id, 'post_content' => '' ] );
			delete_post_meta( $aero_id, '_elementor_data' );
			$this->delete_template_data( $aero_id );
			update_post_meta( $aero_id, '_wp_page_template', 'wfacp-canvas.php' );

			return [ 'status' => true ];
		}

		$templates = WFACP_Core()->template_loader->get_templates();
		if ( $templates['elementor'][ $slug ] && isset( $templates['elementor'][ $slug ]['build_from_scratch'] ) ) {
			$this->save_data( $aero_id );

			return [ 'status' => true ];
		}

		$data = WFACP_Core()->importer->get_remote_template( $slug, 'elementor' );

		if ( isset( $data['error'] ) ) {
			return $data;
		}

		$content = $data['data'];

		if ( ! empty( $content ) ) {
			$content = is_string( $content ) ? $content : json_encode( $content );
			$status  = $this->import_aero_template( $aero_id, $content );

			return [ 'status' => $status ];
		}

		return [ 'error' => __( 'Something Went wrong', 'woofunnels-aero-checkout' ) ];
	}

	public function export( $aero_id, $slug ) {
		$data = get_post_meta( $aero_id, '_elementor_data', true );

		return $data;
	}

	/**
	 *  Import single template
	 *
	 * @param int $post_id post ID.
	 */
	public function import_aero_template( $post_id, $content ) {
		wp_update_post( [ 'ID' => $post_id, 'post_content' => '' ] );
		delete_post_meta( $post_id, '_elementor_data' );

		if ( empty( $content ) ) {
			$this->clear_cache();

			return true;
		}

		$content = json_decode( $content, true );
		if ( ! is_array( $content ) ) {
			return false;
		}

		if ( isset( $content['content'] ) && ! empty( $content['content'] ) ) {
			$content = $content['content'];
		}
		//go ahead and import the content
		$content = $this->process_export_import_content( $content, 'on_import' );

		if ( empty( $content ) ) {
			return false;
		}
		$this->save_data( $post_id, $content );

		return true;
	}


	private function save_data( $post_id, $content = '' ) {
		if ( true == $this->delete_page_meta ) {
			$this->delete_template_data( $post_id );
		}
		if ( '' !== $content ) {
			update_post_meta( $post_id, '_elementor_data', $content );
		}
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_wp_page_template', 'wfacp-canvas.php' );

		$file = __DIR__ . '/elementor/' . $this->slug . '.php';

		if ( file_exists( $file ) ) {
			$page_layout = include $file;

			if ( isset( $page_layout['page_layout'] ) ) {
				WFACP_Common::update_page_layout( $post_id, $page_layout['page_layout'], true );
			}

			if ( isset( $page_layout['page_settings'] ) ) {
				WFACP_Template_Importer::update_import_page_settings( $post_id, $page_layout['page_settings'] );
			}


			if ( isset( $page_layout['wfacp_product_switcher_setting'] ) ) {

				update_post_meta( $post_id, '_wfacp_product_switcher_setting', $page_layout['wfacp_product_switcher_setting'] );
			}

		}

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
		}

		$this->clear_cache();
	}

	private function delete_template_data( $post_id ) {
		WFACP_Common::delete_page_layout( $post_id );


	}

	public function clear_cache() {
		Elementor\Plugin::$instance->files_manager->clear_cache();
	}


}

if ( class_exists( 'WFACP_Template_Importer' ) ) {
	WFACP_Template_Importer::register( 'elementor', new WFACP_Elementor_Importer() );
}