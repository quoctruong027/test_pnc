<?php

class BWFAN_Mailchimp_Contact_Last_Name extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'mailchimp_contact_last_name';
		$this->tag_description = __( 'Mailchimp Contact Last Name', 'autonami-automations' );
		add_shortcode( 'bwfan_mailchimp_contact_last_name', array( $this, 'parse_shortcode' ) );
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
		$mailchimp_data = BWFAN_Merge_Tag_Loader::get_data( 'mailchimp_data' );
		$value          = isset( $mailchimp_data['data']['merges']['LNAME'] ) && ! empty( $mailchimp_data['data']['merges']['LNAME'] ) ? $mailchimp_data['data']['merges']['LNAME'] : '';

		return $this->parse_shortcode_output( $value, $attr );
	}

}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'mailchimp_contact', 'BWFAN_Mailchimp_Contact_Last_Name' );
