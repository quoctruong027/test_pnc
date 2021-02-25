<?php

class WFCO_Keap_Get_Access_Token extends WFCO_Keap_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'client_id', 'client_secret', 'redirect_uri' );
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

		$params = array(
			'client_id'     => $this->data['client_id'],
			'client_secret' => $this->data['client_secret'],
			'redirect_uri'  => $this->data['redirect_uri']
		);

		if ( ! isset( $this->data['refresh_token'] ) && ! isset( $this->data['code'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Either one of the fields required: Refresh Token or Code' ),
			);
		}

		if ( isset( $this->data['refresh_token'] ) ) {
			$params['grant_type']    = 'refresh_token';
			$params['refresh_token'] = $this->data['refresh_token'];
			BWFCO_Keap::set_headers( $this->data['client_id'] . ':' . $this->data['client_secret'], true );
		}

		if ( isset( $this->data['code'] ) ) {
			$params['grant_type'] = 'authorization_code';
			$params['code']       = $this->data['code'];
			BWFCO_Keap::set_headers();
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
		return BWFCO_Keap::get_endpoint( 'v1', true ) . '/oauth/v2/token';
	}

}

return 'WFCO_Keap_Get_Access_Token';
