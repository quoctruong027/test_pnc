<?php

class BWFAN_Mautic_Contact_First_Name extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'mautic_contact_first_name';
		$this->tag_description = __( 'Mautic Contact First Name', 'autonami-automations' );
		add_shortcode( 'bwfan_mautic_contact_first_name', array( $this, 'parse_shortcode' ) );
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
		$mautic_first_name = ! empty( BWFAN_Merge_Tag_Loader::get_data( 'first_name' ) ) ? BWFAN_Merge_Tag_Loader::get_data( 'first_name' ) : "";

		return $this->parse_shortcode_output( ucwords( $mautic_first_name ), $attr );
	}

}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'mautic_contact', 'BWFAN_Mautic_Contact_First_Name' );
