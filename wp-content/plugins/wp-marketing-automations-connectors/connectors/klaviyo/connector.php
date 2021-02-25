<?php

class BWFCO_Klaviyo extends BWF_CO {
	public static $api_end_point = 'https://a.klaviyo.com/api/';
	public static $headers = null;
	private static $ins = null;

	public function __construct() {
		/**
		 * Load important variables and constants
		 */
		$this->define_plugin_properties();

		/**
		 * Loads common file
		 */
		$this->load_commons();

		/** Connector.php initialization */
		$this->keys_to_track = [
			'api_key',
			'lists',
		];
		$this->form_req_keys = [
			'api_key',
		];

		$this->sync          = true;
		$this->connector_url = WFCO_KLAVIYO_PLUGIN_URL;
		$this->dir           = __DIR__;
		$this->nice_name     = __( 'Klaviyo', 'autonami-automations-connectors' );

		$this->autonami_int_slug = 'BWFAN_Klaviyo_Integration';
		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );

		/** Facebook Audience uses JSON formatted data as Body */
		add_filter( 'http_request_args', array( $this, 'parse_body_for_klaviyo' ), 999, 2 );
	}

	public function define_plugin_properties() {
		define( 'WFCO_KLAVIYO_VERSION', '1.0.0' );
		define( 'WFCO_KLAVIYO_FULL_NAME', 'Autonami Marketing Automations Connectors: Klaviyo Addon' );
		define( 'WFCO_KLAVIYO_PLUGIN_FILE', __FILE__ );
		define( 'WFCO_KLAVIYO_PLUGIN_DIR', __DIR__ );
		define( 'WFCO_KLAVIYO_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_KLAVIYO_PLUGIN_FILE ) ) );
		define( 'WFCO_KLAVIYO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'WFCO_KLAVIYO_MAIN', 'autonami-automations-connectors' );
		define( 'WFCO_KLAVIYO_ENCODE', sha1( WFCO_KLAVIYO_PLUGIN_BASENAME ) );
	}

	/**
	 * Load common hooks
	 */
	public function load_commons() {
		require WFCO_KLAVIYO_PLUGIN_DIR . '/includes/class-wfco-klaviyo-call.php';
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public static function get_endpoint( $data_center, $version = '3.0' ) {
		return self::$api_end_point;
	}

	public static function get_headers() {
		return self::$headers;
	}

	public static function set_headers( $api_key ) {
		$headers = array(
			'Content-Type' => 'application/json',
		);

		self::$headers = $headers;
	}

	public function parse_body_for_klaviyo( $args, $url ) {
		if ( empty( $args['body'] ) || false === strpos( $url, 'a.klaviyo' ) ) {
			return $args;
		}
		if ( ! empty( $args['body']['method'] ) ) {
			unset( $args['body']['method'] );

			return $args;
		}
		$args['body'] = wp_json_encode( $args['body'] );

		return $args;
	}

	/**
	 * This function connects to the automation and fetch the data required for the actions on automations screen to work properly.
	 *
	 * @param $posted_data
	 *
	 * @return array|int
	 */
	public function get_api_data( $posted_data ) {
		$resp_array                        = array();
		$resp_array['api_data']['api_key'] = isset( $posted_data['api_key'] ) ? $posted_data['api_key'] : '';
		$resp_array['status']              = 'success';

		if ( ! isset( $posted_data['api_key'] ) || empty( $posted_data['api_key'] ) ) {
			return $resp_array;
		}

		$params = array(
			'api_key' => $posted_data['api_key']
		);

		/** Fetch Lists */
		$lists_result = $this->fetch_lists( $params );
		if ( is_array( $lists_result ) && count( $lists_result ) > 0 ) {
			$resp_array['api_data']['lists'] = $lists_result;
		}

		return $resp_array;
	}

	/**
	 * Fetch Klaviyo Lists
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_lists( $params, $captured_items = [] ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Klaviyo_Get_Lists $call */
		$call = $connectors->get_call( 'wfco_klaviyo_get_lists' );
		$call->set_data( $params );
		$result = $call->process();

		if ( 4 === $result['status'] ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => $result['message'],
			) );

			return;
		}

		$response = $result['payload'];
		$lists    = [];
		foreach ( $response as $data ) {
			$lists[ $data['list_id'] ] = $data['list_name'];
		}

		return $lists;
	}


	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_klaviyo'] = array(
			'name'            => 'Klaviyo',
			'desc'            => __( 'Add to list, Remove From list and update persons fields.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_Klaviyo',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}
}

WFCO_Load_Connectors::register( 'BWFCO_Klaviyo' );
