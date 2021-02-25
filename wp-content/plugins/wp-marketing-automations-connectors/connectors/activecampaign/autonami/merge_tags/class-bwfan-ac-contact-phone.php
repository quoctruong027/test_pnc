<?php

class BWFAN_AC_Contact_Phone extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'ac_contact_phone';
		$this->tag_description = __( 'Active Campaign Contact Phone', 'autonami-automations' );
		add_shortcode( 'bwfan_ac_contact_phone', array( $this, 'parse_shortcode' ) );
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
		$ac_phone = BWFAN_Merge_Tag_Loader::get_data( 'phone' );
		$ac_phone = ! empty( $ac_phone ) ? $ac_phone : '';

		return $this->parse_shortcode_output( $ac_phone, $attr );
	}

}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'ac_contact', 'BWFAN_AC_Contact_Phone' );
