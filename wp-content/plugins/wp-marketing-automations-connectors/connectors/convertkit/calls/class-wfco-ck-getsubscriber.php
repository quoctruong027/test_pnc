<?php

class WFCO_Ck_Get_Subscriber extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_secret', 'email' );
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

		return $this->get_subscriber();
	}

	/**
	 * Get subscriber details bu email.
	 *
	 * email is required.
	 *
	 * @return array|mixed|null|object|string
	 */
	public function get_subscriber() {
		$params = array(
			'api_secret'    => $this->data['api_secret'],
			'email_address' => $this->data['email'],
		);

		$url = $this->get_endpoint();
		$res = $this->make_wp_requests( $url, $params, array() );

		return $this->handle_subscriber_response( $res );
	}

	public function handle_subscriber_response( $result ) {
		if ( 200 === absint( $result['response'] ) ) {
			if ( isset( $result['body']['subscribers'] ) && ! empty( $result['body']['subscribers'] ) ) {
				return $result['body']['subscribers'][0]['id'];
			}

			return array(
				'status'  => 4,
				'message' => __( 'Subscriber doesn\'t exists', 'autonami-automations-connectors' ),
			);
		}

		if ( 502 === absint( $result['response'] ) && is_array( $result['body'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $result['response'];
		$error           = ( is_array( $result['body'] ) && isset( $result['body']['error'] ) ) ? $result['body']['error'] : false;
		$message         = ( is_array( $result['body'] ) && isset( $result['body']['message'] ) ) ? $result['body']['message'] : false;
		$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $error ? $error : ( false !== $message ? $message : $unknown_message ) ) . $response_code,
		);
	}

	/**
	 * The endpoint for getting the subscriber details.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint() . 'subscribers';
	}

}

/**
 * Register this call class.
 */
return ( 'WFCO_Ck_Get_Subscriber' );
