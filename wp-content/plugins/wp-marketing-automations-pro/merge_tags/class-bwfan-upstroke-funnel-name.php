<?php

class BWFAN_UpStroke_Funnel_Name extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'upstroke_funnel_name';
		$this->tag_description = __( 'Upstroke Funnel Name', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_upstroke_funnel_name', array( $this, 'parse_shortcode' ) );
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

		$funnel_id      = BWFAN_Merge_Tag_Loader::get_data( 'funnel_id' );
		$funnel_details = BWFAN_Common::get_funnel_data( $funnel_id );

		return $this->parse_shortcode_output( $funnel_details['funnel_name'], $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'Demo Funnel', 'autonami-automations-pro' );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woofunnels_upstroke_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_funnel', 'BWFAN_UpStroke_Funnel_Name' );
}
