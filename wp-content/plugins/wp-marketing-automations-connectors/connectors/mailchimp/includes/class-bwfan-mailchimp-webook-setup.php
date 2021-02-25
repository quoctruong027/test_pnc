<?php

final class BWFAN_Mialchimp_Webhook_Setup {
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
		register_rest_route( 'autonami/v1', '/mailchimp/webhook(?:/(?P<mailchimp_id>\d+))?', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'bwfan_capture_async_events' ),
			'permission_callback' => '__return_true',
			'args'                => [
				'mailchimp_id',
				'mailchimp_key',
			],
		) );

		register_rest_route( 'autonami/v1', '/mailchimp/webhook(?:/(?P<mailchimp_id>\d+))?', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'bwfan_capture_async_events' ),
			'permission_callback' => '__return_true',
			'args'                => [
				'mailchimp_id',
				'mailchimp_key',
			],
		) );
	}

	public function bwfan_capture_async_events( WP_REST_Request $request ) {
		$request_params = $request->get_params();
		//check if url parmas is empty or not
		if ( empty( $request_params ) ) {
			$this->responseToMailchimp();
		}

		//check request params contain both the key and id
		if ( ( ! isset( $request_params['mailchimp_key'] ) && empty( $request_params['mailchimp_key'] ) ) && ( ! isset( $request_params['mailchimp_id'] ) && empty( $request_params['mailchimp_id'] ) ) ) {
			$this->responseToMailchimp();
		}

		//get automation key using automation id
		$automation_id  = $request_params['mailchimp_id'];
		$meta           = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );
		$automation_key = $meta['bwfan_unique_key'];

		//check if the automation key exist in database
		if ( empty( $automation_key ) ) {
			$this->responseToMailchimp();
		}

		//validate automation key
		if ( $automation_key !== $request_params['mailchimp_key'] ) {
			$this->responseToMailchimp();
		}

		$supported_webhook_types = array( 'subscribe', 'unsubscribe', 'profile' );
		if ( isset( $request_params['type'] ) && in_array( $request_params['type'], $supported_webhook_types, true ) ) {
			do_action( 'bwfan_mailchimp_connector_sync_call', $automation_id, $automation_key, $request_params );
		}
		$this->responseToMailchimp();
	}

	public function responseToMailchimp() {
		wp_send_json( array( 'status' => 'Invalid Mailchimp webhook request received' ) );
	}

}

BWFAN_Mialchimp_Webhook_Setup::get_instance();
