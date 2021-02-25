<?php

class WFCO_Klaviyo_Get_Lists extends WFCO_Klaviyo_Call {

	private static $ins = null;

	public function __construct() {
		parent::__construct();
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process_klaviyo_call() {
		$params = [ 'api_key' => $this->data['api_key'], 'method' => 'get' ];

		return $this->do_klaviyo_call( $params, BWF_CO::$GET );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint( $endpoint_var = '' ) {
		return BWFCO_Klaviyo::$api_end_point . 'v2/lists';
	}

}

return 'WFCO_Klaviyo_Get_Lists';
