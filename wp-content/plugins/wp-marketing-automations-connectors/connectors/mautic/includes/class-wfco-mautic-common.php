<?php

class WFCO_Mautic_Common {

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
		register_rest_route( 'autonami/v1', '/mautic/webhook(?:/(?P<bwfan_mautic_id>\d+))?', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'bwfan_capture_async_events' ),
			'permission_callback' => '__return_true',
			'args'                => [
				'bwfan_mautic_id',
				'bwfan_mautic_key',
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
		if ( ( ! isset( $request_params['bwfan_mautic_key'] ) && empty( $request_params['bwfan_mautic_key'] ) ) && ( ! isset( $request_params['bwfan_mautic_id'] ) && empty( $request_params['bwfan_mautic_id'] ) ) ) {
			return;
		}

		//get automation key using automation id
		$automation_id  = $request_params['bwfan_mautic_id'];
		$meta           = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );
		$automation_key = $meta['bwfan_unique_key'];

		//check if the automation key exist in database
		if ( empty( $automation_key ) ) {
			return;
		}

		//validate automation key
		if ( $automation_key !== $request_params['bwfan_mautic_key'] ) {
			return;
		}

		$request_arr_keys     = array_keys( $request_params );
		$mautic_webhook_calls = array_filter( $request_arr_keys, function ( $key ) {
			return strpos( $key, 'mautic.' ) !== false;
		} );

		$webhook_call_data    = count( $mautic_webhook_calls ) > 0 ? reset( $request_params[ reset( $mautic_webhook_calls ) ] ) : array();
		$webhook_call_contact = isset( $webhook_call_data['contact'] ) ? $webhook_call_data['contact'] : ( isset( $webhook_call_data['lead'] ) ? $webhook_call_data['lead'] : array() );

		$args = array();
		if ( ! empty( $webhook_call_contact ) ) {
			$args = array(
				'first_name'     => isset( $webhook_call_contact['fields']['core']['firstname'] ) && isset( $webhook_call_contact['fields']['core']['firstname']['value'] ) ? $webhook_call_contact['fields']['core']['firstname']['value'] : '',
				'last_name'      => isset( $webhook_call_contact['fields']['core']['lastname'] ) && isset( $webhook_call_contact['fields']['core']['lastname']['value'] ) ? $webhook_call_contact['fields']['core']['lastname']['value'] : '',
				'phone'          => isset( $webhook_call_contact['fields']['core']['phone'] ) && isset( $webhook_call_contact['fields']['core']['phone']['value'] ) ? $webhook_call_contact['fields']['core']['phone']['value'] : '',
				'email'          => isset( $webhook_call_contact['fields']['core']['email'] ) && isset( $webhook_call_contact['fields']['core']['email']['value'] ) ? $webhook_call_contact['fields']['core']['email']['value'] : '',
				'contact_id'     => isset( $webhook_call_contact['id'] ) ? $webhook_call_contact['id'] : '',
				'automation_key' => $automation_key,
				'automation_id'  => $automation_id,
			);
		} else {
			isset( $request_params['firstname'] ) ? $args['first_name'] = $request_params['firstname'] : false;
			isset( $request_params['lastname'] ) ? $args['last_name'] = $request_params['lastname'] : false;
			isset( $request_params['phone'] ) ? $args['phone'] = $request_params['phone'] : false;
			isset( $request_params['email'] ) ? $args['email'] = $request_params['email'] : false;
			isset( $request_params['id'] ) ? $args['id'] = $request_params['id'] : false;

			if ( ! empty( $args ) ) {
				$args['automation_key'] = $automation_key;
				$args['automation_id']  = $automation_id;
			}
		}

		if ( ! empty( $args ) ) {
			do_action( 'bwfan_mautic_connector_sync_call', $args );
		}
	}


	/**
	 * Get Access Token if Valid, otherwise return false
	 *
	 * @return bool|string
	 */
	public static function get_access_token() {
		$data = self::get_mautic_settings();

		return isset( $data['access_token'] ) && ! empty( $data['access_token'] ) ? $data['access_token'] : '';
	}

	/**
	 * Get Mautic Saved Settings
	 *
	 * @return array
	 */
	public static function get_mautic_settings() {
		if ( false === WFCO_Common::$saved_data ) {
			WFCO_Common::get_connectors_data();
		}
		$data = WFCO_Common::$connectors_saved_data;
		$slug = self::get_connector_slug();
		$data = ( isset( $data[ $slug ] ) && is_array( $data[ $slug ] ) ) ? $data[ $slug ] : array();

		return $data;
	}

	public static function get_connector_slug() {
		return sanitize_title( BWFCO_Mautic::class );
	}

	/**
	 * Check if stored Access Token is valid
	 *
	 * @return bool
	 */
	public static function is_access_token_valid() {
		$data = self::get_mautic_settings();

		$expires_in   = isset( $data['expires_in'] ) && ! empty( $data['expires_in'] ) ? absint( $data['expires_in'] ) : 0;
		$current_time = time();

		return $current_time < $expires_in;
	}

	/**
	 * Get Site URL setting
	 *
	 * @return string
	 */
	public static function get_mautic_site_url() {
		$data = self::get_mautic_settings();

		return ( isset( $data['site_url'] ) && ! empty( $data['site_url'] ) ) ? $data['site_url'] : '';
	}

	public static function refresh_access_token() {
		$data = self::get_mautic_settings();

		$params = array(
			'refresh_token' => isset( $data['refresh_token'] ) ? $data['refresh_token'] : false,
			'site_url'      => isset( $data['site_url'] ) ? $data['site_url'] : false,
			'client_id'     => isset( $data['client_id'] ) ? $data['client_id'] : false,
			'client_secret' => isset( $data['client_secret'] ) ? $data['client_secret'] : false,
			'redirect_uri'  => add_query_arg( array(
				'tab'  => 'connector',
				'page' => 'autonami'
			), site_url() . '/wp-admin/admin.php' )
		);

		if ( empty( $params['refresh_token'] ) && empty( $params['site_url'] ) && empty( $params['client_id'] ) && empty( $params['client_secret'] ) ) {
			return false;
		}

		$connector = WFCO_Load_Connectors::get_instance();

		$call = $connector->get_call( 'wfco_mautic_get_access_token' );
		$call->set_data( $params );
		$result = $call->process();

		if ( is_array( $result ) && isset( $result['body'] ) && is_array( $result['body'] ) && isset( $result['body']['errors'] ) ) {
			return false;
		}

		$settings        = array(
			'access_token'  => $result['body']['access_token'],
			'expires_in'    => time() + absint( $result['body']['expires_in'] ),
			'refresh_token' => $result['body']['refresh_token']
		);
		$setting_updated = self::update_settings( $settings );

		/** Update the cache flag to fetch the cache again, when successfully updated token */
		if ( false !== $setting_updated ) {
			WFCO_Common::$saved_data = false;
		}

		return $setting_updated ? $result['body']['access_token'] : false;
	}

	public static function update_settings( $settings = array() ) {
		if ( empty( $settings ) ) {
			return false;
		}

		$old_settings = self::get_mautic_settings();
		$settings     = array_merge( $old_settings, $settings );

		$active_connectors = WFCO_Load_Connectors::get_active_connectors();
		/** @var BWF_CO $connector_ins */
		$connector_ins = $active_connectors[ self::get_connector_slug() ];
		$response      = $connector_ins->handle_settings_form( $settings, 'update' );

		return is_array( $response ) && $response['status'] === 'success' ? true : false;
	}

	/**
	 * Get contact ID by Email (using Mautic's WFCO_Mautic_Get_Contact_ID_By_Email call)
	 *
	 * @param $access_token
	 * @param $site_url
	 * @param $email
	 * @param $create_if_not_exists
	 *
	 * @return array|int
	 */
	public static function get_contact_id_by_email( $access_token, $site_url, $email, $create_if_not_exists = false ) {
		//Get contact by Email
		$call = WFCO_Common::get_call_object( self::get_connector_slug(), 'wfco_mautic_get_contact_id_by_email' );
		$call->set_data( array(
			'access_token'         => $access_token,
			'site_url'             => $site_url,
			'email'                => $email,
			'create_if_not_exists' => absint( $create_if_not_exists )
		) );

		/** @var int $contact (It is supposed to be contact ID, not object or array) */
		return $call->process();
	}
}

WFCO_Mautic_Common::get_instance();