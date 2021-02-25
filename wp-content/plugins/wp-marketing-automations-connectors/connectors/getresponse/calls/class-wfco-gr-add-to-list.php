<?php

class WFCO_GR_Add_To_List extends WFCO_Call {

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

		$contact_id = WFCO_GetResponse_Common::get_contact_id_by_email( $this->data['api_key'], $this->data['list_id'], $this->data['email'], true );
		if ( is_array( $contact_id ) ) {
			return $contact_id;
		}

		if ( empty( $contact_id ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Unable to Create or Get Contact' ),
			);
		}

		return array(
			'response' => 200,
			'body'     => array( 'Added to list successfully' ),
		);
	}

}

return 'WFCO_GR_Add_To_List';
