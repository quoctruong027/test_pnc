<?php

class WFCO_Mailerlite_Add_Group extends WFCO_Mailerlite_Call {

	private static $ins = null;

	public function __construct() {
		parent::__construct( array( 'api_key', 'name' ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process_mailerlite_call() {
		$params = [ 'name' => $this->data['name'] ];

		return $this->do_mailerlite_call( $params, BWF_CO::$POST );
	}

	public function get_endpoint( $endpoint_var = '' ) {
		return BWFCO_Mailerlite::$api_end_point . 'groups';
	}

}

return 'WFCO_Mailerlite_Add_Group';
