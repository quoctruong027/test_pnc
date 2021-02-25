<?php

class BWFAN_Ontraport_Contact_First_Name extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'ontraport_contact_first_name';
		$this->tag_description = __( 'Ontraport Contact First Name', 'autonami-automations' );
		add_shortcode( 'bwfan_ontraport_contact_first_name', array( $this, 'parse_shortcode' ) );
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
		$ontraport_first_name = ! empty( BWFAN_Merge_Tag_Loader::get_data( 'first_name' ) ) ? BWFAN_Merge_Tag_Loader::get_data( 'first_name' ) : "";

		return $this->parse_shortcode_output( ucwords( $ontraport_first_name ), $attr );
	}

}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'ontraport_contact', 'BWFAN_Ontraport_Contact_First_Name' );
