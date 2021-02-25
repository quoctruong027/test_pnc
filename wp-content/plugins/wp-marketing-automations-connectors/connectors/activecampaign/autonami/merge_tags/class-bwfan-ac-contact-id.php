<?php

class BWFAN_AC_Contact_ID extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'ac_contact_id';
		$this->tag_description = __( 'Active Campaign Contact ID', 'autonami-automations' );
		add_shortcode( 'bwfan_ac_contact_id', array( $this, 'parse_shortcode' ) );
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
		$ac_contact_id = BWFAN_Merge_Tag_Loader::get_data( 'contact_id' );
		$ac_contact_id = ! empty( $ac_contact_id ) ? $ac_contact_id : '';

		return $this->parse_shortcode_output( $ac_contact_id, $attr );
	}

}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'ac_contact', 'BWFAN_AC_Contact_ID' );
