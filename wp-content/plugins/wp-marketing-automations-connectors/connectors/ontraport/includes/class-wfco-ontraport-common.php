<?php

class WFCO_Ontraport_Common {

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
		register_rest_route( 'autonami/v1', '/ontraport/webhook(?:/(?P<bwfan_ontraport_id>\d+))?', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'bwfan_capture_async_events' ),
			'permission_callback' => '__return_true',
			'args'                => [
				'bwfan_ontraport_id',
				'bwfan_ontraport_key',
				'bwfan_contact_id',
			],
		) );
	}

	public function bwfan_capture_async_events( WP_REST_Request $request ) {

		$request_params = $request->get_params();
		//check if url parmas is empty or not
		if ( empty( $request_params ) ) {
			return;
		}

		//check request params contain both the key and id
		if ( ( ! isset( $request_params['bwfan_ontraport_key'] ) && empty( $request_params['bwfan_ontraport_key'] ) ) && ( ! isset( $request_params['bwfan_ontraport_id'] ) && empty( $request_params['bwfan_ontraport_id'] ) ) && ( isset( $request_params['bwfan_contact_id'] ) && empty( $request_params['bwfan_contact_id'] ) ) ) {
			return;
		}
		//get automation key using automation id
		$automation_id  = $request_params['bwfan_ontraport_id'];
		$meta           = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );
		$automation_key = $meta['bwfan_unique_key'];
		$contact_id     = $request_params['bwfan_contact_id'];
		//check if the automation key exist in database
		if ( empty( $automation_key ) ) {
			return;
		}

		/** call to get contact details **/

		$saved_connectors = WFCO_Common::$connectors_saved_data;

		if ( empty( $saved_connectors ) ) {
			WFCO_Common::get_connectors_data();
			$saved_connectors = WFCO_Common::$connectors_saved_data;
		}
		$app_id  = '';
		$api_key = '';

		if ( array_key_exists( 'bwfco_ontraport', $saved_connectors ) ) {
			$bwfco_ontraport_connector = $saved_connectors['bwfco_ontraport'];
			foreach ( $bwfco_ontraport_connector as $key => $ontarport_data ) {
				$app_id  = $bwfco_ontraport_connector['app_id'];
				$api_key = $bwfco_ontraport_connector['api_key'];
			}
		}

		/** get connector call **/
		$connectors       = WFCO_Load_Connectors::get_instance();
		$get_contact_call = $connectors->get_call( 'wfco_ontraport_get_contact_by_id' );
		$params           = array(
			'app_id'     => $app_id,
			'api_key'    => $api_key,
			'contact_id' => $contact_id,
		);

		$get_contact_call->set_data( $params );
		$get_contact_response = $get_contact_call->process();
		/** validate correct response of contact **/
		if ( 200 !== $get_contact_response['response'] || ! isset( $get_contact_response['body']['data'] ) || empty( $get_contact_response['body']['data'] ) ) {
			return;
		}

		$contact_details['id']             = $get_contact_response['body']['data']['id'];
		$contact_details['first_name']     = $get_contact_response['body']['data']['firstname'];
		$contact_details['last_name']      = $get_contact_response['body']['data']['lastname'];
		$contact_details['email']          = $get_contact_response['body']['data']['email'];
		$contact_details['phone']          = $get_contact_response['body']['data']['home_phone'];
		$contact_details['automation_id']  = $get_contact_response['body']['data']['automation_id'];
		$contact_details['automation_key'] = $get_contact_response['body']['data']['automation_key'];
		//validate automation key
		if ( $automation_key !== $request_params['bwfan_ontraport_key'] ) {
			return;
		}

		if ( ! empty( $contact_details ) ) {
			do_action( 'bwfan_ontraport_connector_sync_call', $automation_id, $automation_key, $contact_details );
		}
	}

	/**
	 * Get Ontraport Saved Settings
	 *
	 * @return array
	 */
	public static function get_ontraport_settings() {
		if ( false === WFCO_Common::$saved_data ) {
			WFCO_Common::get_connectors_data();
		}
		$data = WFCO_Common::$connectors_saved_data;
		$slug = self::get_connector_slug();
		$data = ( isset( $data[ $slug ] ) && is_array( $data[ $slug ] ) ) ? $data[ $slug ] : array();

		return $data;
	}

	public static function get_connector_slug() {
		return sanitize_title( BWFCO_Ontraport::class );
	}

	public static function update_settings( $settings = array() ) {
		if ( empty( $settings ) ) {
			return false;
		}

		$old_settings = self::get_ontraport_settings();
		$settings     = array_merge( $old_settings, $settings );

		$active_connectors = WFCO_Load_Connectors::get_active_connectors();
		/** @var BWF_CO $connector_ins */
		$connector_ins = $active_connectors[ self::get_connector_slug() ];
		$response      = $connector_ins->handle_settings_form( $settings, 'update' );

		return is_array( $response ) && $response['status'] === 'success' ? true : false;
	}

	/**
	 * Get contact ID by Email (using Ontraport's WFCO_Ontraport_Get_Contact_ID_By_Email call)
	 *
	 * @param $app_id
	 * @param $api_key
	 * @param $email
	 * @param $create_if_not_exists
	 *
	 * @return array|int
	 */
	public static function get_contact_id_by_email( $app_id, $api_key, $email, $create_if_not_exists = false ) {
		//Get contact by Email
		$call = WFCO_Common::get_call_object( self::get_connector_slug(), 'wfco_ontraport_get_contact_id_by_email' );
		$call->set_data( array(
			'app_id'               => $app_id,
			'api_key'              => $api_key,
			'email'                => $email,
			'create_if_not_exists' => absint( $create_if_not_exists )
		) );

		/** @var int $contact (It is supposed to be contact ID, not object or array) */
		return $call->process();
	}
}

WFCO_Ontraport_Common::get_instance();
