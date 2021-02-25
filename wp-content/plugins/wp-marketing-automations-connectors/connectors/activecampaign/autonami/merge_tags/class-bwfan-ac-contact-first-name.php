<?php

class BWFAN_AC_Contact_First_Name extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'ac_contact_first_name';
		$this->tag_description = __( 'Active Campaign Contact First Name', 'autonami-automations' );
		add_shortcode( 'bwfan_ac_contact_first_name', array( $this, 'parse_shortcode' ) );
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
		$ac_first_name = BWFAN_Merge_Tag_Loader::get_data( 'first_name' );
		$ac_first_name = ! empty( $ac_first_name ) ? $ac_first_name : '';

		return $this->parse_shortcode_output( ucwords( $ac_first_name ), $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'John', 'autonami-automations' );
	}


}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'ac_contact', 'BWFAN_AC_Contact_First_Name' );
