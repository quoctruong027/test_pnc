<?php

class BWFAN_Drip_Contact_Phone extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'drip_contact_phone';
		$this->tag_description = __( 'Drip Contact Phone', 'autonami-automations' );
		add_shortcode( 'bwfan_drip_contact_phone', array( $this, 'parse_shortcode' ) );
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
		$drip_phone = ! empty( BWFAN_Merge_Tag_Loader::get_data( 'phone' ) ) ? BWFAN_Merge_Tag_Loader::get_data( 'phone' ) : "";

		return $this->parse_shortcode_output( $drip_phone, $attr );
	}

}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'drip_contact', 'BWFAN_Drip_Contact_Phone' );
