<?php

final class BWFAN_AC_Common {
	private static $instance = null;

	private function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'bwfan_add_webhook_endpoint' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function bwfan_add_webhook_endpoint() {
		register_rest_route( 'autonami/v1', '/ac/webhook(?:/(?P<ac_id>\d+))?', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'bwfan_capture_async_events' ),
			'permission_callback' => '__return_true',
			'args'                => [
				'ac_id',
				'ac_key',
			],
		) );
	}

	public static function bwfan_capture_async_events( WP_REST_Request $request ) {
		$request_params = $request->get_params();
		//check if url parmas is empty or not
		if ( empty( $request_params ) ) {
			return;
		}

		//check request params contain both the key and id
		if ( ( ! isset( $request_params['ac_key'] ) && empty( $request_params['ac_key'] ) ) && ( ! isset( $request_params['ac_id'] ) && empty( $request_params['ac_id'] ) ) ) {
			return;
		}

		//get automation key using automation id
		$automation_id  = $request_params['ac_id'];
		$meta           = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );
		$automation_key = $meta['bwfan_unique_key'];

		//check if the automation key exist in database
		if ( empty( $automation_key ) ) {
			return;
		}

		//validate automation key
		if ( $automation_key !== $request_params['ac_key'] ) {
			return;
		}

		$request_data = $request->get_body_params();

		if ( ! is_array( $request_data ) || ! isset( $request_data['contact'] ) || empty( $request_data['contact'] ) ) {
			return;
		}

		do_action( 'bwfan_connector_sync_call', $automation_id, $automation_key, $request_data['contact'] );
	}

}

BWFAN_AC_Common::get_instance();
