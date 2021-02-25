<?php

class WFCO_Ck_Check_Oauth extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_secret' );
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

		return $this->check_oauth();
	}

	/**
	 * Check account.
	 *
	 * api_secret is required.
	 *
	 * @return array|bool
	 * @throws Exception
	 */
	public function check_oauth() {
		$params = array(
			'api_secret' => $this->data['api_secret'],
		);

		$url = $this->get_endpoint();
		$res = $this->make_wp_requests( $url, $params, array() );

		return $res;
	}

	/**
	 * Endpoint for adding or updating a subscriber in an account.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint() . 'account';
	}

}

return ( 'WFCO_Ck_Check_Oauth' );
