<?php

class BWFAN_WCM_Membership_Status extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'user_membership_status';
		$this->tag_description = __( 'Membership Status', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_user_membership_status', array( $this, 'parse_shortcode' ) );
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

		$status = BWFAN_Merge_Tag_Loader::get_data( 'wc_user_membership_status' );
		if ( empty( $status ) ) {
			$membership = wc_memberships_get_user_membership( BWFAN_Merge_Tag_Loader::get_data( 'wc_user_membership_id' ) );
			$status     = $membership->get_status();
		}

		return $this->parse_shortcode_output( esc_html( $status ), $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @param $parameters
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return 'active';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woocommerce_membership_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_membership', 'BWFAN_WCM_Membership_Status' );
}
