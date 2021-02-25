<?php

class BWFAN_UpStroke_Offer_Type extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'upstroke_offer_type';
		$this->tag_description = __( 'Upstroke Offer Type', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_upstroke_offer_type', array( $this, 'parse_shortcode' ) );
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

		$offer_id      = BWFAN_Merge_Tag_Loader::get_data( 'offer_id' );
		$offer_details = BWFAN_Common::get_offer_data( $offer_id );

		return $this->parse_shortcode_output( $offer_details['offer_type'], $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'upsell', 'autonami-automations-pro' );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woofunnels_upstroke_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_offer', 'BWFAN_UpStroke_Offer_Type' );
}
