<?php

class WFCO_Mailerlite_Create_Subscriber extends WFCO_Mailerlite_Call {

	private static $ins = null;

	public function __construct() {
		parent::__construct( array( 'api_key', 'email', 'name', 'last_name' ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process_mailerlite_call() {
		if ( ! is_email( $this->data['email'] ) ) {
			return $this->get_autonami_error( __( 'Email is not valid', 'autonami-automations-connectors' ) );
		}

		$params = [
			'email'  => $this->data['email'],
			'name'   => !empty($this->data['name'])?$this->data['name']:'',
			'fields' => [
				'last_name' => !empty($this->data['last_name'])?$this->data['last_name']:''
			]
		];

		return $this->do_mailerlite_call($params, BWF_CO::$POST);
	}

	public function get_endpoint($endpoint_var = '') {
		return BWFCO_Mailerlite::$api_end_point . 'subscribers';
	}

}

return 'WFCO_Mailerlite_Create_Subscriber';
