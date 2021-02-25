<?php

class WFCO_Twilio_Oauth_Check extends WFCO_Call {

	private static $instance = null;
	private $api_end_point = null;

	public function __construct() {

		$this->required_fields = array( 'account_sid', 'auth_token' );
		$this->api_end_point   = 'https://api.twilio.com/2010-04-01/Accounts/';
	}

	/**
	 * @return WFCO_Twilio_Oauth_Check|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		$url     = $this->api_end_point . $this->data['account_sid'] . '.json';
		$headers = array(
			'Content-Type'  => 'application/x-www-form-urlencoded',
			'Authorization' => 'Basic ' . base64_encode( $this->data['account_sid'] . ':' . $this->data['auth_token'] ),
		);
		$res     = $this->make_wp_requests( $url, array(), $headers, BWF_CO::$GET );

		return $res;
	}


}

return 'WFCO_Twilio_Oauth_Check';
