<?php

abstract class WFCO_Mailerlite_Call extends WFCO_Call {
	/**
	 * In case of only api_key, no need to declare the construct in the child call class.
	 * If more than api_key, then use construct and required fields in child call class.
	 *
	 * @param array $required_fields
	 */
	public function __construct( $required_fields = array( 'api_key' ) ) {
		$this->required_fields = $required_fields;
	}

	/** Abstract functions that must be present in child's call class */
	abstract function process_mailerlite_call();

	abstract function get_endpoint( $endpoint_var = '' );

	/** Required fields handling is done here, Also process_mailerlite_call must be implemented in child call class */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->get_autonami_error( $this->show_fields_error()['body'][0] );
		}

		BWFCO_Mailerlite::set_headers( $this->data['api_key'] );

		return $this->process_mailerlite_call();
	}

	/**
	 * Use do_mailerlite_call instead of make_wp_requests,
	 * to make use of handling response and errors from remote API call.
	 *
	 * @param array $params
	 * @param int $method
	 * @param string $endpoint_var
	 *
	 * @return array
	 */
	public function do_mailerlite_call( $params, $method, $endpoint_var = '' ) {
		$response = $this->make_wp_requests( $this->get_endpoint( $endpoint_var ), $params, BWFCO_Mailerlite::get_headers(), $method );

		return $this->handle_api_response( $response );
	}

	public function get_autonami_error( $message = false ) {
		BWFAN_Core()->logger->log( $message, 'failed-' . $this->get_slug() . '-action' );
		return array(
			'status'  => 4,
			'message' => ( false !== $message ) ? $message : __( 'Unknown Autonami Error', 'autonami-automations-connectors' )
		);
	}

	public function get_autonami_success( $message = false ) {
		return array(
			'status'  => 3,
			'message' => ( false !== $message ) ? $message : __( 'Task executed successfully!', 'autonami-automations-connectors' )
		);
	}

	/**
	 * Handle API or Autonami Response or Error
	 *
	 * @param array $res
	 *
	 * @return array
	 */
	public function handle_api_response( $res ) {
		/** If success (within 200 status), then return payload (actual response) and status, message */
		if ( ( absint( $res['response'] ) - 200 ) < 100 ) {
			return array(
				'status'  => 3,
				'payload' => $res['body'],
				'message' => __( 'Mailerlite API call executed successfully', 'autonami-automations-connectors' )
			);
		}

		/** If failed, send appropriate error */
		/** Check Mailerlite error format here: https://developers.mailerlite.com/docs/response#response-with-error */
		$response_code    = __( '. Error Response Code: ', 'autonami-automations-connectors' ) . $res['response'];
		$mailerlite_error = __( 'Mailerlite Error: ', 'autonami-automations-connectors' ) . $res['body']['error']['message'] . ', Code: ' . $res['body']['error']['code'];
		$mailerlite_error = is_array( $res['body'] ) && isset( $res['body']['error'] ) ? $mailerlite_error : false;
		$unknown_error    = __( 'Unknown Mailerlite Error', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $mailerlite_error ? $mailerlite_error : $unknown_error ) . $response_code,
		);
	}
}