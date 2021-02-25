<?php

class BWFCO_GetResponse extends BWF_CO {
	public static $api_end_point = 'https://api.getresponse.com/';
	public static $headers = null;
	private static $ins = null;

	public function __construct() {
		/** Setup includes from add-on plugin */
		$this->define_plugin_properties();
		$this->init_getresponse();

		/** Connector.php initialization */
		$this->keys_to_track = [
			'api_key',
			'default_list',
			'custom_fields',
			'tags',
			'lists'
		];
		$this->form_req_keys = [
			'api_key',
			'default_list'
		];

		$this->sync          = true;
		$this->connector_url = WFCO_GETRESPONSE_PLUGIN_URL;
		$this->dir           = __DIR__;
		$this->nice_name     = __( 'GetResponse', 'autonami-automations-connectors' );

		$this->autonami_int_slug = 'BWFAN_GetResponse_Integration';

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
		add_action( 'wp_ajax_bwf_get_gr_lists', array( $this, 'ajax_get_gr_lists' ) );

		/** GetResponse uses JSON formatted data as Body */
		add_filter( 'http_request_args', array( $this, 'parse_body_for_gr' ), 999, 2 );

		/** Add tag to connector's global settings */
		add_action( 'wfco_getresponse_tag_created', array( $this, 'add_tag_to_settings' ), 10, 2 );
	}

	/**
	 * Defining constants
	 */
	public function define_plugin_properties() {
		define( 'WFCO_GETRESPONSE_VERSION', '1.0.0' );
		define( 'WFCO_GETRESPONSE_FULL_NAME', 'Autonami Marketing Automations Connectors: GetResponse' );
		define( 'WFCO_GETRESPONSE_PLUGIN_FILE', __FILE__ );
		define( 'WFCO_GETRESPONSE_PLUGIN_DIR', __DIR__ );
		define( 'WFCO_GETRESPONSE_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_GETRESPONSE_PLUGIN_FILE ) ) );
		define( 'WFCO_GETRESPONSE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'WFCO_GETRESPONSE_MAIN', 'autonami-automations-connectors' );
		define( 'WFCO_GETRESPONSE_ENCODE', sha1( WFCO_GETRESPONSE_PLUGIN_BASENAME ) );
	}

	public function init_getresponse() {
		require WFCO_GETRESPONSE_PLUGIN_DIR . '/includes/class-wfco-getresponse-common.php';
	}

	public function parse_body_for_gr( $args, $url ) {
		if ( false === strpos( $url, self::get_endpoint() ) ) {
			return $args;
		}

		$args['body'] = wp_json_encode( $args['body'] );

		return $args;
	}

	public function add_tag_to_settings( $tag_id, $tag_name ) {
		$settings = WFCO_GetResponse_Common::get_gr_settings();
		if ( ! isset( $settings['tags'] ) || ! is_array( $settings['tags'] ) ) {
			$settings['tags'] = array();
		}

		$settings['tags'][ $tag_id ] = $tag_name;
		WFCO_GetResponse_Common::update_settings( $settings );
	}

	public function ajax_get_gr_lists() {
		BWFAN_Common::check_nonce();

		$api_key = isset( $_POST['api_key'] ) ? $_POST['api_key'] : '';
		if ( empty( $api_key ) ) {
			wp_send_json( array(
				'response' => __( 'API Key is not provided', 'autonami-automations-connectors' )
			) );
		}

		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_GR_Get_Lists $lists_call */
		$lists_call = $connectors->get_call( 'wfco_gr_get_lists' );
		$lists_call->set_data( array( 'api_key' => $api_key ) );
		$result = $lists_call->process();

		if ( empty( $result['body'] ) ) {
			wp_send_json( array(
				'response' => __( 'No Response from GetResponse', 'autonami-automations-connectors' )
			) );
		}

		if ( isset( $result['body']['code'] ) ) {
			wp_send_json( array(
				'response' => $result['body']['code'] . ': ' . $result['body']['codeDescription']
			) );
		}

		wp_send_json( is_array( $result['body'] ) ? $result['body'] : array( 'response' => $result['body'] ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public static function get_endpoint( $version = 'v3' ) {
		return self::$api_end_point . $version . '/';
	}

	public static function get_headers() {
		return self::$headers;
	}

	public static function set_headers( $api_key ) {
		$headers = array(
			'Content-Type' => 'application/json',
			'X-Auth-Token' => 'api-key ' . $api_key
		);

		self::$headers = $headers;
	}

	/**
	 * This function connects to the automation and fetch the data required for the actions on automations screen to work properly.
	 *
	 * @param $posted_data
	 *
	 * @return array|int
	 */
	public function get_api_data( $posted_data ) {
		$resp_array                             = array();
		$resp_array['api_data']['api_key']      = isset( $posted_data['api_key'] ) ? $posted_data['api_key'] : '';
		$resp_array['api_data']['default_list'] = isset( $posted_data['default_list'] ) ? $posted_data['default_list'] : '';
		$resp_array['status']                   = 'success';

		if ( ! isset( $posted_data['api_key'] ) || empty( $posted_data['api_key'] ) ) {
			return $resp_array;
		}

		$params = array( 'api_key' => $posted_data['api_key'] );

		/** Fetch Contact Fields */
		$custom_fields_result = $this->fetch_custom_fields( $params );
		if ( is_array( $custom_fields_result ) && count( $custom_fields_result ) > 0 ) {
			$resp_array['api_data']['custom_fields'] = $custom_fields_result;
		}

		/** Fetch Tags */
		$tags_result = $this->fetch_tags( $params );
		if ( is_array( $tags_result ) && count( $tags_result ) > 0 ) {
			$resp_array['api_data']['tags'] = $tags_result;
		}

		/** Fetch Lists */
		$lists_result = $this->fetch_lists( $params );
		if ( is_array( $lists_result ) && count( $lists_result ) > 0 ) {
			$resp_array['api_data']['lists'] = $lists_result;
		}

		return $resp_array;
	}

	/**
	 * Fetch GR Custom Contact Fields
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_custom_fields( $params ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Mautic_Get_Contact_Fields $call */
		$call = $connectors->get_call( 'wfco_gr_get_custom_fields' );
		$call->set_data( $params );
		$result = $call->process();

		if ( isset( $result['response'] ) && 200 !== absint( $result['response'] ) ) {
			$message = ( 502 === absint( $result['response'] ) ) ? $result['body'] : 'Unknown Error Occurred';
			wp_send_json( array(
				'status'  => 'failed',
				'message' => __( $message, 'autonami-automations-connectors' ),
			) );
		}

		if ( isset( $result['body']['code'] ) ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => __( 'Error: ' . $result['body']['codeDescription'], 'autonami-automations-connectors' ),
			) );
		}

		if ( ! is_array( $result ) || empty( $result['body'] ) ) {
			return [];
		}

		$return_output = array();
		$data          = $result['body'];
		foreach ( $data as $row ) {
			$id                   = $row['customFieldId'];
			$return_output[ $id ] = $row['name'];
		}

		return $return_output;
	}

	/**
	 * Fetch GR Tags
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_tags( $params ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Mautic_Get_Contact_Fields $call */
		$call = $connectors->get_call( 'wfco_gr_get_tags' );
		$call->set_data( $params );
		$result = $call->process();

		if ( isset( $result['response'] ) && 200 !== absint( $result['response'] ) ) {
			$message = ( 502 === absint( $result['response'] ) ) ? $result['body'] : 'Unknown Error Occurred';
			wp_send_json( array(
				'status'  => 'failed',
				'message' => __( $message, 'autonami-automations-connectors' ),
			) );
		}

		if ( isset( $result['body']['code'] ) ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => __( 'Error: ' . $result['body']['codeDescription'], 'autonami-automations-connectors' ),
			) );
		}

		if ( ! is_array( $result ) || empty( $result['body'] ) ) {
			return [];
		}

		$return_output = array();
		$data          = $result['body'];
		foreach ( $data as $row ) {
			$id                   = $row['tagId'];
			$return_output[ $id ] = $row['name'];
		}

		return $return_output;
	}

	/**
	 * Fetch GR Lists
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_lists( $params ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Mautic_Get_Contact_Fields $call */
		$call = $connectors->get_call( 'wfco_gr_get_lists' );
		$call->set_data( $params );
		$result = $call->process();

		if ( isset( $result['response'] ) && 200 !== absint( $result['response'] ) ) {
			$message = ( 502 === absint( $result['response'] ) ) ? $result['body'] : 'Unknown Error Occurred';
			wp_send_json( array(
				'status'  => 'failed',
				'message' => __( $message, 'autonami-automations-connectors' ),
			) );
		}

		if ( isset( $result['body']['code'] ) ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => __( 'Error: ' . $result['body']['codeDescription'], 'autonami-automations-connectors' ),
			) );
		}

		if ( ! is_array( $result ) || empty( $result['body'] ) ) {
			return [];
		}

		$return_output = array();
		$data          = $result['body'];
		foreach ( $data as $row ) {
			$id                   = $row['campaignId'];
			$return_output[ $id ] = $row['name'];
		}

		return $return_output;
	}

	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_getresponse'] = array(
			'name'            => 'GetResponse',
			'desc'            => __( 'Add or Remove tags, Change contact\'s list, Update contact custom fields and much more.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_GetResponse',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}

}

WFCO_Load_Connectors::register( 'BWFCO_GetResponse' );
