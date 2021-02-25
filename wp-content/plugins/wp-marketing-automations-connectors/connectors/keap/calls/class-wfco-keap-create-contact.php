<?php

class WFCO_Keap_Create_Contact extends WFCO_Keap_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'access_token', 'email' );
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

		BWFCO_Keap::set_headers( $this->data['access_token'] );

		$params = array(
			'email_addresses' => array(
				array( 'email' => $this->data['email'], 'field' => 'EMAIL1' )
			)
		);

		if ( isset( $this->data['first_name'] ) && ! empty( $this->data['first_name'] ) ) {
			$params['given_name'] = $this->data['first_name'];
		}

		if ( isset( $this->data['last_name'] ) && ! empty( $this->data['last_name'] ) ) {
			$params['family_name'] = $this->data['last_name'];
		}

		$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Keap::get_headers(), BWF_CO::$POST );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Keap::get_endpoint() . 'contacts';
	}

}

return 'WFCO_Keap_Create_Contact';
