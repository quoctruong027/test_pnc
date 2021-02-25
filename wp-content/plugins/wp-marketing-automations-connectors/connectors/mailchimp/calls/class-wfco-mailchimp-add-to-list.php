<?php

class WFCO_Mailchimp_Add_To_List extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'email', 'list_id' );
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

		return WFCO_Mailchimp_Common::get_contact( $this->data['api_key'], $this->data['list_id'], $this->data['email'] );
	}

}

return 'WFCO_Mailchimp_Add_To_List';
