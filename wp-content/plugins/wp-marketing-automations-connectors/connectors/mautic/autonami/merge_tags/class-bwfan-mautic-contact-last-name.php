<?php

class BWFAN_Mautic_Contact_Last_Name extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'mautic_contact_last_name';
		$this->tag_description = __( 'Mautic Contact Last Name', 'autonami-automations' );
		add_shortcode( 'bwfan_mautic_contact_last_name', array( $this, 'parse_shortcode' ) );
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
		$mautic_last_name = ! empty( BWFAN_Merge_Tag_Loader::get_data( 'last_name' ) ) ? BWFAN_Merge_Tag_Loader::get_data( 'last_name' ) : "";

		return $this->parse_shortcode_output( ucwords( $mautic_last_name ), $attr );
	}

}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'mautic_contact', 'BWFAN_Mautic_Contact_Last_Name' );
