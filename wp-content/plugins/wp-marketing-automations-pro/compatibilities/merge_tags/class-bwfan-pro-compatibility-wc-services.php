<?php

class BWFAN_WC_Jetpack_Shipment extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'wc_jetpack_shipment';
		$this->tag_description = __( 'WooCommerce Services - Jetpack', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_wc_jetpack_shipment', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Show the html in popup for the merge tag.
	 */
	public function get_view() {
		$tracking_fields = $this->get_view_data();
		$this->get_back_button();

		?>
        <div class="bwfan_mtag_wrap">
            <div class="bwfan_label">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select Tracking Field', 'autonami-automations-pro' ); ?></label>
            </div>
            <div class="bwfan_label_val">
                <select id="" class="bwfan-input-wrapper bwfan_tag_select" name="data">
					<?php
					foreach ( $tracking_fields as $slug => $name ) {
						echo '<option value="' . esc_attr__( $slug ) . '">' . esc_attr__( $name ) . '</option>';
					}
					?>
                </select>
            </div>
        </div>
		<?php

		if ( $this->support_fallback ) {
			$this->get_fallback();
		}

		$this->get_preview();
		$this->get_copy_button();
	}

	public function get_view_data() {
		return array(
			'carrier_name_short' => __( 'Carrier Name - Short', 'autonami-automations-pro' ),
			'carrier_name_full'  => __( 'Carrier Name - Full', 'autonami-automations-pro' ),
			'package_name'       => __( 'Package Name', 'autonami-automations-pro' ),
			'tracking_number'    => __( 'Tracking Number', 'autonami-automations-pro' ),
			'tracking_link'      => __( 'Tracking Link', 'autonami-automations-pro' ),
		);
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			return $this->get_dummy_preview();
		}

		$order_id = absint( BWFAN_Merge_Tag_Loader::get_data( 'wc_order_id' ) );
		if ( 0 === $order_id ) {
			return '';
		}

		$tracking_items = get_post_meta( $order_id, 'wc_connect_labels', true );
		if ( ! is_array( $tracking_items ) || empty( $tracking_items ) ) {
			return '';
		}

		if ( count( $tracking_items ) > 1 ) {
			$tracking_items = array_filter( $tracking_items, function ( $label ) {
				if ( array_key_exists( 'error', $label ) || array_key_exists( 'refund', $label ) ) {
					return false;
				}

				return true;
			} );

			if ( count( $tracking_items ) > 1 ) {
				usort( $tracking_items, function ( $item1, $item2 ) {
					return absint( $item1['created'] ) <= absint( $item2['created'] );
				} );
			}
		}

		$return_value = '';
		$item_key     = ( isset( $attr['data'] ) && ! empty( $attr['data'] ) ) ? $attr['data'] : 'carrier_name_short';
		$item         = $tracking_items[0];
		switch ( $item_key ) {
			case 'carrier_name_short':
				$return_value = ( isset( $item['carrier_id'] ) && ! empty( $item['carrier_id'] ) ) ? strtoupper( $item['carrier_id'] ) : '';
				break;
			case 'carrier_name_full':
				$return_value = ( isset( $item['service_name'] ) && ! empty( $item['service_name'] ) ) ? $item['service_name'] : '';
				break;
			case 'package_name':
				$return_value = ( isset( $item['package_name'] ) && ! empty( $item['package_name'] ) ) ? $item['package_name'] : '';
				break;
			case 'tracking_number':
				$return_value = ( isset( $item['tracking'] ) && ! empty( $item['tracking'] ) ) ? $item['tracking'] : '';
				break;
			case 'tracking_link':
				$url          = $this->get_tracking_url( $item['carrier_id'], $item['tracking'] );
				$return_value = ( ! empty( $url ) ) ? $url : '';
				break;
		}

		return $this->parse_shortcode_output( $return_value, $attr );
	}

	public function get_tracking_url( $carrier, $tracking_number ) {
		$tracking_url = '';
		switch ( $carrier ) {
			case 'fedex':
				$tracking_url = 'https://www.fedex.com/apps/fedextrack/?action=track&tracknumbers=' . $tracking_number;
				break;
			case 'usps':
				$tracking_url = 'https://tools.usps.com/go/TrackConfirmAction.action?tLabels=' . $tracking_number;
				break;
		}

		return $tracking_url;
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return '123456789';
	}


}


if ( false !== defined( 'WOOCOMMERCE_CONNECT_MINIMUM_WOOCOMMERCE_VERSION' ) ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Jetpack_Shipment' );
}
