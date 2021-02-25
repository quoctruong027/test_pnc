<?php

class BWFAN_AFFWP_Affiliate_Payment_Email extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'affwp_affiliate_payment_email';
		$this->tag_description = __( 'Affiliate Payment Email', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_affwp_affiliate_payment_email', array( $this, 'parse_shortcode' ) );
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

		$affiliate_id = BWFAN_Merge_Tag_Loader::get_data( 'affiliate_id' );

		if ( ! $affiliate_id ) {
			return;
		}

		$affiliate_payment_email = affwp_get_affiliate_payment_email( $affiliate_id );

		return $this->parse_shortcode_output( $affiliate_payment_email, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 *
	 * @todo:Hard values shouldn't be passed
	 */
	public function get_dummy_preview() {
		return 'test@gmail.com';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_affiliatewp_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'aff_affiliate', 'BWFAN_AFFWP_Affiliate_Payment_Email' );
}
