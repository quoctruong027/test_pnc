<?php

class WFCO_Klaviyo_Check_List_Membership extends WFCO_Klaviyo_Call {

	private static $ins = null;

	public function __construct() {
		parent::__construct( [ 'api_key', 'list_id', 'email' ] );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process_klaviyo_call() {
		if ( ! is_email( $this->data['email'] ) ) {
			return $this->get_autonami_error( __( 'Email is not valid', 'autonami-automations-connectors' ) );
		}
		$params = [
			'api_key' => $this->data['api_key'],
			'emails'  => $this->data['email'],
			'method'  => 'get'
		];

		return $this->do_klaviyo_call( $params, BWF_CO::$GET );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint( $endpoint_var = '' ) {
		return BWFCO_Klaviyo::$api_end_point . 'v2/list/' . $this->data['list_id'] . '/members';
	}

}

return 'WFCO_Klaviyo_Check_List_Membership';
