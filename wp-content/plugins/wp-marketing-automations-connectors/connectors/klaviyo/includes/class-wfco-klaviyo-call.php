<?php

abstract class WFCO_Klaviyo_Call extends WFCO_Call {
	/**
	 *
	 * @param array $required_fields
	 */
	public function __construct( $required_fields = array( 'api_key' ) ) {
		$this->required_fields = $required_fields;
	}

	/** Required fields handling is done here, Also process_klaviyo_call must be implemented in child call class */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->get_autonami_error( $this->show_fields_error()['body'][0] );
		}

		BWFCO_Klaviyo::set_headers( $this->data['api_key'] );

		return $this->process_klaviyo_call();
	}

	public function get_autonami_error( $message = false ) {
		BWFAN_Core()->logger->log( $message, 'failed-' . $this->get_slug() . '-action' );

		return array(
			'status'  => 4,
			'message' => ( false !== $message ) ? $message : __( 'Unknown Autonami Error', 'autonami-automations-connectors' )
		);
	}

	/** Abstract functions that must be present in child's call class */
	abstract function process_klaviyo_call();

	/**
	 * Use do_klaviyo_call instead of make_wp_requests,
	 * to make use of handling response and errors from remote API call.
	 *
	 * @param array $params
	 * @param int $method
	 * @param string $endpoint_var
	 *
	 * @return array
	 */
	public function do_klaviyo_call( $params, $method, $endpoint_var = '' ) {
		$response = $this->make_wp_requests( $this->get_endpoint( $endpoint_var ), $params, BWFCO_Klaviyo::get_headers(), $method );

		return $this->handle_api_response( $response );
	}

	abstract function get_endpoint( $endpoint_var = '' );

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
				'message' => __( 'Klaviyo API call executed successfully', 'autonami-automations-connectors' )
			);
		}

		/** If failed, send appropriate error */
		$response_code = __( '. Error Response Code: ', 'autonami-automations-connectors' ) . $res['response'];
		if ( isset( $res['body']['message'] ) ) {
			$klaviyo_error = __( 'Klaviyo Error: ', 'autonami-automations-connectors' ) . $res['body']['message'] . ', Code: ' . $res['response'];
		}
		$klaviyo_error = is_array( $res['body'] ) && isset( $res['body']['message'] ) ? $klaviyo_error : false;
		$unknown_error = __( 'Unknown Klaviyo Error', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $klaviyo_error ? $klaviyo_error : $unknown_error ) . $response_code,
		);
	}

	public function get_autonami_success( $message = false ) {
		return array(
			'status'  => 3,
			'message' => ( false !== $message ) ? $message : __( 'Task executed successfully!', 'autonami-automations-connectors' )
		);
	}
}