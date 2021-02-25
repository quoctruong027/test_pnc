<?php

/**
 * Plugin: https://wordpress.org/plugins/woo-advanced-shipment-tracking
 * Class BWFAN_Handl_Utm_Grabber_Data
 */

class BWFAN_Handl_Utm_Grabber_Data extends Cart_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'handl_utm_grabber_data';
		$this->tag_description = __( 'HandL UTM Grabber Data', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_handl_utm_grabber_data', array( $this, 'parse_shortcode' ) );
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
		$handle_utm_fields = $this->get_view_data();
		$this->get_back_button();
		?>
        <div class="bwfan_mtag_wrap">
            <div class="bwfan_label">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select Handl UTM Grabber', 'autonami-automations-pro' ); ?></label>
            </div>
            <div class="bwfan_label_val">
                <select id="" class="bwfan-input-wrapper bwfan_tag_select" name="data">
					<?php
					foreach ( $handle_utm_fields as $slug => $name ) {
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


		$handler_merge_key = array(
			'utm_campaign'       => __( 'UTM Campaign', 'autonami-automations-pro' ),
			'utm_source'         => __( 'UTM Source', 'autonami-automations-pro' ),
			'utm_term'           => __( 'UTM Term', 'autonami-automations-pro' ),
			'utm_medium'         => __( 'UTM Medium', 'autonami-automations-pro' ),
			'utm_content'        => __( 'UTM Content', 'autonami-automations-pro' ),
			'gclid'              => __( 'Gclid', 'autonami-automations-pro' ),
			'handl_original_ref' => __( 'Handl Original Reference', 'autonami-automations-pro' ),
			'handl_landing_page' => __( 'Handl Landing Page', 'autonami-automations-pro' ),
			'handl_ip'           => __( 'Handl IP', 'autonami-automations-pro' ),
			'handl_ref'          => __( 'Handl Reference', 'autonami-automations-pro' ),
			'handl_url'          => __( 'Handl URL', 'autonami-automations-pro' ),
		);

		return apply_filters( 'bwfan_external_handl_utm_grabber_key', $handler_merge_key );
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

		$cart_details  = BWFAN_Merge_Tag_Loader::get_data( 'cart_details' );
		$checkout_data = isset( $cart_details['checkout_data'] ) ? $cart_details['checkout_data'] : '';

		if ( empty( $checkout_data ) ) {
			return $this->parse_shortcode_output( '', $attr );
		}

		$key = ! isset( $attr['data'] ) ? 'utm_campaign' : $attr['data'];

		$checkout_data = json_decode( $checkout_data, true );

		if ( ! isset( $checkout_data['handle_utm_grabber'][ $key ] ) ) {
			return $this->parse_shortcode_output( '', $attr );
		}

		$field_value = $checkout_data['handle_utm_grabber'][ $key ];

		return $this->parse_shortcode_output( $field_value, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return '';
	}


}

if ( false != function_exists( 'CaptureUTMs' ) ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_ab_cart', 'BWFAN_Handl_Utm_Grabber_Data' );
}
