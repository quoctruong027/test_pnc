<?php

class WFCO_Mailerlite_Update_Subscriber extends WFCO_Mailerlite_Call {

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

		$connector         = WFCO_Load_Connectors::get_instance();
		$subscriber_params = [
			'api_key' => $this->data['api_key'],
			'email'   => $this->data['email'],
		];

		/** get Subscriber */
		/** @var WFCO_Mailerlite_Get_Subscriber $call */
		$call = $connector->get_call( 'wfco_mailerlite_get_subscriber' );
		$call->set_data( $subscriber_params );
		$response = $call->process();

		/** If Subscriber not found */
		if ( 4 === $response['status'] ) {

			/** create Subscriber */
			/** @var WFCO_Mailerlite_Create_Subscriber $call */
			$call = $connector->get_call( 'wfco_mailerlite_create_subscriber' );
			$call->set_data( $subscriber_params );
			$create_response = $call->process();
			if ( 4 === $create_response['status'] ) {
				return $this->get_autonami_error( __( 'Subscriber not found.', 'autonami-automations-connectors' ) );
			}
		}

		/** update Subscriber **/
		$params['fields'] = $this->data['custom_fields'];

		return $this->do_mailerlite_call( $params, BWF_CO::$PUT );
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

return 'WFCO_Mailerlite_Update_Subscriber';
