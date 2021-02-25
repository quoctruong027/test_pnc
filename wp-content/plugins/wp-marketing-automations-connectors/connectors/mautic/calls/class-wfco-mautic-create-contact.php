<?php

class WFCO_Mautic_Create_Contact extends WFCO_Mautic_Call {

	private static $ins = null;
	private $site_url = null;

	public function __construct() {

		$this->required_fields = array( 'site_url', 'access_token', 'email' );
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

		$this->site_url = $this->data['site_url'];

		$params = array(
			'email'        => $this->data['email'],
			'firstname'    => $this->data['first_name'],
			'lastname'     => $this->data['last_name'],
			'access_token' => $this->data['access_token']
		);
		$res    = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Mautic::get_headers(), BWF_CO::$POST );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return $this->site_url . '/api/contacts/new';
	}

}

return 'WFCO_Mautic_Create_Contact';
