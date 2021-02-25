<?php

class BWFAN_Mailchimp_Contact_Email extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'mailchimp_contact_email';
		$this->tag_description = __( 'Mailchimp Contact Email', 'autonami-automations' );
		add_shortcode( 'bwfan_mailchimp_contact_email', array( $this, 'parse_shortcode' ) );
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
		$email = BWFAN_Merge_Tag_Loader::get_data( 'email' );
		$value = is_email( $email ) ? $email : '';

		return $this->parse_shortcode_output( $value, $attr );
	}

}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'mailchimp_contact', 'BWFAN_Mailchimp_Contact_Email' );
