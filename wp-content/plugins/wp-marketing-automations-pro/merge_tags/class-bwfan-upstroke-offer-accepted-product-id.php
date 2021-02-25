<?php

class BWFAN_UpStroke_Offer_Accepted_Product_Id extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'upstroke_offer_accepted_product_id';
		$this->tag_description = __( 'Upstroke Offer Accepted Product Id', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_upstroke_offer_accepted_product_id', array( $this, 'parse_shortcode' ) );
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

		$accepted_product_id = BWFAN_Merge_Tag_Loader::get_data( 'accepted_product_id' );

		return $this->parse_shortcode_output( $accepted_product_id, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return integer
	 */
	public function get_dummy_preview() {
		return 123;
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woofunnels_upstroke_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_offer_product', 'BWFAN_UpStroke_Offer_Accepted_Product_Id' );
}
