<?php

class BWFCO_Slack extends BWF_CO {
	public static $api_end_point = null;
	public static $account_id = null;
	public static $headers = null;
	private static $ins = null;

	/** only require for oauth check  */
	public $oauth_url = null;
	public $redirect_uri = null; // current application's redirect url

	public function __construct() {
		$this->keys_to_track    = [
			'access_token',
			'channels',
			'users',
		];
		$this->form_req_keys    = [
			'access_token',
		];
		$this->sync             = true;
		$this->is_oauth         = true;
		$this->connector_url    = WFCO_AUTONAMI_CONNECTORS_PLUGIN_URL . '/connectors/slack';
		$this->is_setting       = true;
		self::$api_end_point    = 'https://slack.com/api/';
		$this->dir              = __DIR__;
		$this->nice_name        = __( 'Slack', 'autonami-automations-connectors' );
		$this->oauth_url        = 'https://secure-auth.buildwoofunnels.com/slack';
		$this->redirect_uri     = site_url() . '/wp-admin/admin.php';
		$this->autonami_int_slug = 'BWFAN_Slack_Integration';

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public static function get_endpoint() {
		$endpoint = self::$api_end_point;

		return $endpoint;
	}

	public static function get_headers() {
		return self::$headers;
	}

	public static function set_headers() {
		$headers       = array(
			'Content-Type' => 'application/x-www-form-urlencoded',
		);
		self::$headers = $headers;
	}

	/**
	 * Return the access rights url for the integration where user will be asked for giving rights.
	 *
	 * @return string
	 */
	public function get_access_right_url() {
		return add_query_arg( array(
			'connector'    => $this->get_slug(),
			'redirect_uri' => $this->redirect_uri,
		), $this->oauth_url );
	}

	/**
	 * Handles the settings form submission
	 */
	public function handle_settings_form( $posted_data, $type = 'save' ) {
		$old_data = [];
		$new_data = [];
		$status   = 'failed';
		$resp     = array(
			'status'  => $status,
			'id'      => 0,
			'message' => '',
		);

		/** Validating form settings */
		if ( 'sync' !== $type ) {
			$is_valid = $this->validate_settings_fields( $posted_data, $type );
			if ( false === $is_valid ) {
				$resp['message'] = $this->get_connector_messages( 'connector_settings_missing' );

				return $resp;
			}
		}

		switch ( $type ) {
			case 'save':
				$new_data = $this->get_api_data( $posted_data );
				if ( is_array( $new_data['api_data'] ) && count( $new_data['api_data'] ) > 0 ) {
					$id = WFCO_Common::save_connector_data( $new_data['api_data'], $this->get_slug(), 1 );

					$resp['id']      = $id;
					$resp['message'] = $this->get_connector_messages( 'connector_saved' );
				}
				break;
			case 'update':
				$saved_data = WFCO_Common::$connectors_saved_data;
				$old_data   = $saved_data[ $this->get_slug() ];
				$new_data   = $this->get_api_data( $posted_data );

				if ( isset( $new_data['status'] ) && 'success' === $new_data['status'] ) {
					$resp['message'] = $this->get_connector_messages( 'connector_updated' );
				}
				break;
			case 'sync':
				$saved_data = WFCO_Common::$connectors_saved_data;
				$old_data   = $saved_data[ $this->get_slug() ];
				$new_data   = $this->get_api_data( $old_data );

				if ( isset( $new_data['status'] ) && 'success' === $new_data['status'] ) {
					$resp['message'] = $this->get_connector_messages( 'connector_synced' );
				}
				break;
		}

		$resp['status']       = $this->get_response_status( $new_data, 'status' );
		$resp['data_changed'] = 0;

		/** Return for save type case */
		if ( 'save' === $type ) {
			return $resp;
		}

		/** Assigning ID */
		$resp['id'] = $posted_data['id'];

		/** Saving new data */
		WFCO_Common::update_connector_data( $new_data['api_data'], $resp['id'] );

		/** Tracking if data changed */
		$is_data_changed = $this->track_sync_changes( $new_data['api_data'], $old_data );
		if ( true === $is_data_changed ) {
			do_action( 'change_in_connector_data', $this->get_slug() );
			$resp['data_changed'] = 1;
		}

		return $resp;
	}

	/**
	 * This function connects to the automation and fetch the data required for the actions on automations screen to work properly.
	 *
	 * @param $posted_data
	 *
	 * @return array
	 */
	public function get_api_data( $posted_data ) {
		$access_token = isset( $posted_data['access_token'] ) ? $posted_data['access_token'] : '';

		if ( empty( $access_token ) ) {
			return array(
				'status'   => 'failed',
				'message'  => __( 'Access Token Expire' ),
				'api_data' => [],
			);
		}

		$resp_array = array(
			'status'   => 'success',
			'api_data' => [],
		);
		$params     = array(
			'access_token' => $access_token,
		);

		$connectors       = WFCO_Load_Connectors::get_instance();
		$wfco_sl_api_test = $connectors->get_call( 'wfco_sl_api_test' );

		$wfco_sl_api_test->set_data( $params );
		$response = $wfco_sl_api_test->process();

		if ( isset( $response['body']['error'] ) ) {
			return array(
				'status'  => 'failed',
				'message' => $response['body']['error'],
			);
		}

		/** Channels-list */
		$params['limit'] = 500;
		$params['types'] = 'public_channel,private_channel';
		$channels_result = $this->fetch_channels( [], $params );

		if ( is_array( $channels_result ) && count( $channels_result ) > 0 ) {
			$resp_array['api_data']['channels'] = $channels_result;
		}

		unset( $params['types'] );

		/** Users-list */
		$users_result = $this->fetch_users( [], $params );
		if ( is_array( $users_result ) && count( $users_result ) > 0 ) {
			$resp_array['api_data']['users'] = $users_result;
		}

		$resp_array['api_data']['access_token'] = $access_token;

		return $resp_array;
	}

	public function fetch_channels( $captured_channels, $params ) {
		$all_channels                    = [];
		$connectors                      = WFCO_Load_Connectors::get_instance();
		$wfco_sl_fetch_all_channels_list = $connectors->get_call( 'wfco_sl_fetch_all_channels_list' );

		$wfco_sl_fetch_all_channels_list->set_data( $params );
		$channels_list = $wfco_sl_fetch_all_channels_list->process();

		if ( is_array( $channels_list ) && 200 === $channels_list['response'] && 1 === intval( $channels_list['body']['ok'] ) ) {
			$channels = $channels_list['body']['channels'];
			foreach ( $channels as $channel_details ) {
				$captured_channels[ $channel_details['id'] ] = '#' . $channel_details['name'];
			}

			$all_channels = $captured_channels;
			$next_cursor  = $channels_list['body']['response_metadata']['next_cursor'];

			if ( ! empty( $next_cursor ) ) {
				$params['cursor'] = $next_cursor;
				$inner_channels   = $this->fetch_channels( $all_channels, $params );
				if ( is_array( $inner_channels ) && count( $inner_channels ) > 0 ) {
					foreach ( $inner_channels as $id => $name ) {
						$all_channels[ $id ] = $name;
					}
				}
			}
		}

		return $all_channels;
	}

	public function fetch_users( $captured_users, $params ) {
		$all_users = [];

		$connectors               = WFCO_Load_Connectors::get_instance();
		$wfco_sl_fetch_users_list = $connectors->get_call( 'wfco_sl_fetch_users_list' );
		$wfco_sl_fetch_users_list->set_data( $params );
		$users_list = $wfco_sl_fetch_users_list->process();

		if ( is_array( $users_list ) && 200 === $users_list['response'] && 1 === intval( $users_list['body']['ok'] ) ) {
			$users = $users_list['body']['members'];
			foreach ( $users as $user_details ) {
				if ( 1 !== intval( $user_details['deleted'] ) ) {
					$captured_users[ $user_details['id'] ] = $user_details['real_name'] . " (@{$user_details['name']})";
				}
			}
			$all_users   = $captured_users;
			$next_cursor = $users_list['body']['response_metadata']['next_cursor'];

			if ( ! empty( $next_cursor ) ) {
				$params['cursor'] = $next_cursor;
				$inner_users      = $this->fetch_users( $all_users, $params );
				if ( is_array( $inner_users ) && count( $inner_users ) > 0 ) {
					foreach ( $inner_users as $id => $name ) {
						$all_users[ $id ] = $name;
					}
				}
			}
		}

		return $all_users;
	}

	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_slack'] = array(
			'name'            => 'Slack',
			'desc'            => __( 'Receive Slack notifications for events in your WordPress site. Track key events and build business processes that save time.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_Slack',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}

}

WFCO_Load_Connectors::register( 'BWFCO_Slack' );
