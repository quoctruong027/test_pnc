<?php

class WFCO_Mailerlite_Get_Subscriber_Fields extends WFCO_Mailerlite_Call {

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

	public function process_mailerlite_call() {
		return $this->do_mailerlite_call( array(), BWF_CO::$GET );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint( $endpoint_var = '' ) {
		return BWFCO_Mailerlite::$api_end_point . 'fields';
	}

}

return 'WFCO_Mailerlite_Get_Subscriber_Fields';
