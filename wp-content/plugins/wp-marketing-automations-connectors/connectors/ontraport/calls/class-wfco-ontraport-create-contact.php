<?php

class WFCO_Ontraport_Create_Contact extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'app_id','api_key', 'email' );
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

		BWFCO_Ontraport::set_headers( $this->data );

		$params['email'] = $this->data['email'];

		if ( isset( $this->data['first_name'] ) && ! empty( $this->data['first_name'] ) ) {
			$params['firstname'] = $this->data['first_name'];
		}

		if ( isset( $this->data['last_name'] ) && ! empty( $this->data['last_name'] ) ) {
			$params['lastname'] = $this->data['last_name'];
		}

		$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Ontraport::get_headers(), BWF_CO::$POST );
		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Ontraport::get_endpoint() . '/Contacts/saveorupdate';
	}

}

return 'WFCO_Ontraport_Create_Contact';
