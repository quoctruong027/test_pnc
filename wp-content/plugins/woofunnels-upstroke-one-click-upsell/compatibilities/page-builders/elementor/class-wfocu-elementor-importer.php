<?php

/**
 * Elementor template library local source.
 *
 * Elementor template library local source handler class is responsible for
 * handling local Elementor templates saved by the user locally on his site.
 *
 * @since 1.0.0
 */
class WFOCU_Importer_Elementor extends Elementor\TemplateLibrary\Source_Local {

	/**
	 *  Import single template
	 *
	 * @param int $post_id post ID.
	 */
	public function single_template_import( $post_id, $content = '', $offer_settings = array() ) {

		if ( empty( $content ) ) {
			return;
		}
		$rest_content = add_magic_quotes( $content );
		$content      = json_decode( $rest_content, true );


		if ( ! is_array( $content ) ) {
			//skip if not an array
		} else {
			//go ahead and import the content
			if ( isset( $content['content'] ) && ! empty( $content['content'] ) ) {
				$content = $content['content'];
			}
			$content = $this->process_export_import_content( $content, 'on_import' );
			if ( ! empty( $post_id ) ) {
				$products     = isset( $offer_settings->products ) ? $offer_settings->products : array();
				$product_keys = array_keys( (array) $products );

				$content = $this->wfocu_replace_position_to_selected_products( $product_keys, $content );
			}
			// Update content.
			update_metadata( 'post', $post_id, '_elementor_data', $content );
			if ( defined( 'ELEMENTOR_VERSION' ) ) {
				update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
			}
		}
	}

	/**
	 * @param $offer_id
	 * @param $el_data
	 *
	 * @return mixed
	 */
	public function wfocu_replace_position_to_selected_products( $product_keys, $el_data ) {
		$output = array();
		foreach ( $el_data as $el_key => $el_value ) {

			if ( is_array( $el_value ) ) {
				$output[ $el_key ] = $this->wfocu_replace_position_to_selected_products( $product_keys, $el_value );
			} else {
				if ( 'product_position' === $el_key && 1 < $el_value ) {
					if ( isset( $product_keys[ $el_value - 1 ] ) ) {
						$output['selected_product'] = $product_keys[ $el_value - 1 ];
					}
				} elseif ( 'product_position' === $el_key ) {
					unset( $el_data[ $el_key ] );
				} else {
					$output[ $el_key ] = $el_value;
				}
			}
		}

		return $output;
	}
}
