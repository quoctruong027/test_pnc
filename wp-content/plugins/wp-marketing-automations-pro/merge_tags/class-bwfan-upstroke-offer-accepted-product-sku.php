<?php

class BWFAN_UpStroke_Offer_Accepted_Product_SKU extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'upstroke_offer_accepted_product_sku';
		$this->tag_description = __( 'Upstroke Offer Accepted Product SKU', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_upstroke_offer_accepted_product_sku', array( $this, 'parse_shortcode' ) );
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

		$product_id  = BWFAN_Merge_Tag_Loader::get_data( 'accepted_product_id' );
		$wc_product  = wc_get_product( $product_id );
		$product_sku = $wc_product->get_sku();

		return $this->parse_shortcode_output( $product_sku, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'sku-123', 'autonami-automations-pro' );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woofunnels_upstroke_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_offer_product', 'BWFAN_UpStroke_Offer_Accepted_Product_SKU' );
}
