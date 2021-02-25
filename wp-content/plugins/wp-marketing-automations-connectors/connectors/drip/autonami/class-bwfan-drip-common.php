<?php

final class BWFAN_Drip_Common {
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
		register_rest_route( 'autonami/v1', '/drip/webhook(?:/(?P<drip_id>\d+))?', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'bwfan_capture_async_events' ),
			'permission_callback' => '__return_true',
			'args'                => [
				'drip_id',
				'drip_key',
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
		if ( ( ! isset( $request_params['drip_key'] ) && empty( $request_params['drip_key'] ) ) && ( ! isset( $request_params['drip_id'] ) && empty( $request_params['drip_id'] ) ) ) {
			return;
		}

		//get automation key using automation id
		$automation_id  = $request_params['drip_id'];
		$meta           = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );
		$automation_key = $meta['bwfan_unique_key'];

		//check if the automation key exist in database
		if ( empty( $automation_key ) ) {
			return;
		}

		//validate automation key
		if ( $automation_key !== $request_params['drip_key'] ) {
			return;
		}

		$request_data = ! empty( $request_params['data']['subscriber'] ) ? $request_params['data']['subscriber'] : array();
		do_action( 'bwfan_drip_connector_sync_call', $automation_id, $automation_key, $request_data );
	}

}

BWFAN_Drip_Common::get_instance();
