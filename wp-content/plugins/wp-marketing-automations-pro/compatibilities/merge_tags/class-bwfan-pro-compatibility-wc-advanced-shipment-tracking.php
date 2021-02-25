<?php

/**
 * Plugin: https://wordpress.org/plugins/woo-advanced-shipment-tracking
 * Class BWFAN_WC_Advanced_Shipment_Tracking
 */
class BWFAN_WC_Advanced_Shipment_Tracking extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'wc_advanced_shipment_tracking';
		$this->tag_description = __( 'WooCommerce Advanced Shipment Tracking details', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_wc_advanced_shipment_tracking', array( $this, 'parse_shortcode' ) );
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
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select Shipment Tracking Field', 'autonami-automations-pro' ); ?></label>
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
			'tracking_number'             => __( 'Tracking Number', 'autonami-automations-pro' ),
			'formatted_tracking_provider' => __( 'Tracking Provider', 'autonami-automations-pro' ),
			'formatted_tracking_link'     => __( 'Tracking Link', 'autonami-automations-pro' ),
			'date_shipped'                => __( 'Date Shipped', 'autonami-automations-pro' ),
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
		if ( empty( $order_id ) ) {
			return '';
		}

		$tracking_items = ast_get_tracking_items( $order_id );
		if ( ! is_array( $tracking_items ) || 1 > count( $tracking_items ) ) {
			return '';
		}

		/** If more than one shipments, then sort by date_shipped */
		if ( count( $tracking_items ) > 1 ) {
			usort( $tracking_items, function ( $item1, $item2 ) {
				return absint( $item1['date_shipped'] ) <= absint( $item2['date_shipped'] );
			} );
		}

		$item_key     = ( isset( $attr['data'] ) && ! empty( $attr['data'] ) ) ? $attr['data'] : 'tracking_number';
		$return_value = 'date_shipped' === $item_key ? date( 'm-d-Y', $tracking_items[0]['date_shipped'] ) : $tracking_items[0][ $item_key ];

		return $this->parse_shortcode_output( $return_value, $attr );
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


if ( false !== function_exists( 'wc_advanced_shipment_tracking' ) ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Advanced_Shipment_Tracking' );
}
