<?php

class BWFAN_Ontraport_Contact_Phone extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'ontraport_contact_phone';
		$this->tag_description = __( 'Ontraport Contact Phone', 'autonami-automations' );
		add_shortcode( 'bwfan_ontraport_contact_phone', array( $this, 'parse_shortcode' ) );
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
		$ontraport_phone = ! empty( BWFAN_Merge_Tag_Loader::get_data( 'phone' ) ) ? BWFAN_Merge_Tag_Loader::get_data( 'phone' ) : "";

		return $this->parse_shortcode_output( $ontraport_phone, $attr );
	}

}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'ontraport_contact', 'BWFAN_Ontraport_Contact_Phone' );
