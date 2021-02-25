<?php

class BWFAN_WCS_Shipping_Address extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'subscription_shipping_address';
		$this->tag_description = __( 'Subscription Shipping Address', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_subscription_shipping_address', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
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

		$address = BWFAN_Merge_Tag_Loader::get_data( 'wc_subscription' )->get_formatted_shipping_address();

		return $this->parse_shortcode_output( $address, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'John Marsh Ashok Nagar, Delhi 110022, India', 'autonami-automations-pro' );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woocommerce_subscriptions_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_subscription', 'BWFAN_WCS_Shipping_Address' );
}
