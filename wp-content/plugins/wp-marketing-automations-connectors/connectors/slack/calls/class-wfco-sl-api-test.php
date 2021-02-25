<?php

class WFCO_Sl_Api_Test extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'access_token' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		BWFCO_Slack::set_headers();

		return $this->check_api();
	}

	/**
	 * Check_api.
	 *
	 * Account id is required.
	 * @return array|bool
	 * @throws Exception
	 */
	public function check_api() {
		$params = array(
			'access_token' => $this->data['access_token'],
		);
		$url    = add_query_arg( array(
			'token' => $this->data['access_token'],
		), $this->get_endpoint() );

		$res = $this->make_wp_requests( $url, $params, BWFCO_Slack::get_headers(), BWF_CO::$POST );

		return $res;
	}

	/**
	 * The campaign endpoint to api.test.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Slack::get_endpoint() . 'api.test';
	}
}

/**
 * Register this call class.
 */
return ( 'WFCO_Sl_Api_Test' );
