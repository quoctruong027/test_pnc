<?php

class BWFAN_WCS_Expire_Card_Remaining_Days extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'expire_card_remaining_days';
		$this->tag_description = __( 'Expire credit card Year', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_expire_card_remaining_days', array( $this, 'parse_shortcode' ) );
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
	 * @return int|mixed|void
	 */
	public function parse_shortcode( $attr ) {
		if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			return $this->get_dummy_preview();
		}

		$month        = BWFAN_Merge_Tag_Loader::get_data( 'credit_card_expiry_month' );
		$year         = BWFAN_Merge_Tag_Loader::get_data( 'credit_card_expiry_year' );
		$expiry_date  = "$year-$month-01";
		$current_data = new DateTime();
		$temp_data    = new DateTime();
		$temp_data->setTimestamp( strtotime( $expiry_date ) );
		$interval      = $temp_data->diff( $current_data );
		$interval_date = $interval->format( '%a' );

		return $this->parse_shortcode_output( $interval_date, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return integer
	 */
	public function get_dummy_preview() {
		return 8;
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woocommerce_subscriptions_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wcs_card_expiry', 'BWFAN_WCS_Expire_Card_Remaining_Days' );
}
