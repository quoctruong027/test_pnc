<?php

class WFCO_Mailerlite_Get_Subscriber extends WFCO_Mailerlite_Call {

	private static $ins = null;

	public function __construct() {
		parent::__construct( array( 'api_key', 'email' ) );
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

		return $this->do_mailerlite_call( array(), BWF_CO::$GET );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint( $endpoint_var = '' ) {
		return BWFCO_Mailerlite::$api_end_point . 'subscribers/' . $this->data['email'];
	}

}

return 'WFCO_Mailerlite_Get_Subscriber';
