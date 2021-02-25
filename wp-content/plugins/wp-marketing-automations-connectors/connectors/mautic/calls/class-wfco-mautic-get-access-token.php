<?php

class WFCO_Mautic_Get_Access_Token extends WFCO_Mautic_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'site_url', 'client_id', 'client_secret', 'redirect_uri' );
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

		BWFCO_Mautic::set_headers();

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
		}

		if ( isset( $this->data['code'] ) ) {
			$params['grant_type'] = 'authorization_code';
			$params['code']       = $this->data['code'];
		}

		$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Mautic::get_headers(), BWF_CO::$POST );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return $this->data['site_url'] . '/oauth/v2/token';
	}

}

return 'WFCO_Mautic_Get_Access_Token';
