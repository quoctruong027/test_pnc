<?php

final class BWFAN_Twilio_Webhook_Setup {
	private static $instance = null;

	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'bwfan_add_webhook_endpoint' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function bwfan_add_webhook_endpoint() {
		register_rest_route( 'autonami/v1', '/twilio/webhook(?:/(?P<twilio_id>\d+))?', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'bwfan_capture_async_events' ),
			'permission_callback' => '__return_true',
			'args'                => [
				'twilio_id',
				'twilio_key',
			],
		) );
	}

	public function bwfan_capture_async_events( WP_REST_Request $request ) {
		$request_params = $request->get_params();
		//check if url parmas is empty or not
		if ( empty( $request_params ) ) {
			$this->responseToTwilio();
		}

		//check request params contain both the key and id
		if ( ( ! isset( $request_params['twilio_key'] ) && empty( $request_params['twilio_key'] ) ) && ( ! isset( $request_params['twilio_id'] ) && empty( $request_params['twilio_id'] ) ) ) {
			$this->responseToTwilio();
		}

		//get automation key using automation id
		$automation_id  = $request_params['twilio_id'];
		$meta           = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );
		$automation_key = $meta['bwfan_unique_key'];

		//check if the automation key exist in database
		if ( empty( $automation_key ) ) {
			$this->responseToTwilio();
		}

		//validate automation key
		if ( $automation_key !== $request_params['twilio_key'] ) {
			$this->responseToTwilio();
		}

		if ( isset( $request_params['SmsSid'] ) && ! empty( $request_params['SmsSid'] ) ) {
			do_action( 'bwfan_twilio_connector_sync_call', $automation_id, $automation_key, $request_params );
		}
		$this->responseToTwilio();
	}

	public function responseToTwilio() {
		header( 'content-type: text/xml' );
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		echo '<Response></Response>';
		die();
	}

}

BWFAN_Twilio_Webhook_Setup::get_instance();
