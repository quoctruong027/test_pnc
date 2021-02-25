<?php

class WFCO_Mailchimp_Update_Custom_Fields extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		/** Optional: list_id (in case only one contact related to the list needs to be updated */
		$this->required_fields = array( 'api_key', 'email', 'custom_fields', 'list_id' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		if ( ! is_email( $this->data['email'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Email is not valid' ),
			);
		}

		if ( ! is_array( $this->data['custom_fields'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Custom Fields data is invalid' ),
			);
		}

		return WFCO_Mailchimp_Common::upsert_contact( $this->data['api_key'], $this->data['list_id'], $this->data['email'], $this->data['custom_fields'] );
	}

}

return 'WFCO_Mailchimp_Update_Custom_Fields';
