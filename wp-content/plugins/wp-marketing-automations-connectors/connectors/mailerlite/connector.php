<?php

class BWFCO_Mailerlite extends BWF_CO {
	public static $api_end_point = 'https://api.mailerlite.com/api/v2/';
	public static $headers = null;
	private static $ins = null;

	public function __construct() {
		/** Setup includes from add-on plugin */
		$this->define_plugin_properties();
		$this->init_mailerlite();

		/** Connector.php initialization */
		$this->keys_to_track = [
			'api_key',
			'subscriber_fields',
			'groups',
			'subscribers'
		];
		$this->form_req_keys = [
			'api_key'
		];

		$this->sync          = true;
		$this->connector_url = WFCO_MAILERLITE_PLUGIN_URL;
		$this->dir           = __DIR__;
		$this->nice_name     = __( 'Mailerlite', 'autonami-automations-connectors' );

		$this->autonami_int_slug = 'BWFAN_Mailerlite_Integration';

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );

		/** Mailerlite uses JSON formatted data as Body */
		add_filter( 'http_request_args', array( $this, 'parse_body_for_mailerlite' ), 999, 2 );
	}

	/**
	 * Defining constants
	 */
	public function define_plugin_properties() {
		define( 'WFCO_MAILERLITE_VERSION', '1.0.0' );
		define( 'WFCO_MAILERLITE_FULL_NAME', 'Autonami Marketing Automations Connectors: Mailerlite Addon' );
		define( 'WFCO_MAILERLITE_PLUGIN_FILE', __FILE__ );
		define( 'WFCO_MAILERLITE_PLUGIN_DIR', __DIR__ );
		define( 'WFCO_MAILERLITE_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_MAILERLITE_PLUGIN_FILE ) ) );
		define( 'WFCO_MAILERLITE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'WFCO_MAILERLITE_MAIN', 'autonami-automations-connectors' );
		define( 'WFCO_MAILERLITE_ENCODE', sha1( WFCO_MAILERLITE_PLUGIN_BASENAME ) );
	}

	public function init_mailerlite() {
		require WFCO_MAILERLITE_PLUGIN_DIR . '/includes/class-wfco-mailerlite-call.php';
	}

	public function parse_body_for_mailerlite( $args, $url ) {
		if ( empty( $args['body'] ) || false === strpos( $url, 'mailerlite' ) ) {
			return $args;
		}

		$args['body'] = wp_json_encode( $args['body'] );

		return $args;
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
			'X-MailerLite-ApiKey' => $api_key,
			'Content-Type'        => 'application/json',
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
		$resp_array                        = array();
		$resp_array['api_data']['api_key'] = isset( $posted_data['api_key'] ) ? $posted_data['api_key'] : '';
		$resp_array['status']              = 'success';

		if ( ! isset( $posted_data['api_key'] ) || empty( $posted_data['api_key'] ) ) {
			return $resp_array;
		}

		$params = array(
			'api_key' => $posted_data['api_key']
		);

		/** Fetch Subscriber Fields */
		$custom_fields_result = $this->fetch_subscriber_fields( $params );
		if ( is_array( $custom_fields_result ) && count( $custom_fields_result ) > 0 ) {
			$resp_array['api_data']['subscriber_fields'] = $custom_fields_result;
		}

		/** Fetch Groups */
		$tags_result = $this->fetch_groups( $params );
		if ( is_array( $tags_result ) && count( $tags_result ) > 0 ) {
			$resp_array['api_data']['groups'] = $tags_result;
		}

		return $resp_array;
	}

	/**
	 * Fetch Mailerlite Custom Contact Fields
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_subscriber_fields( $params, $captured_items = [] ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Mailerlite_Get_Subscriber_Fields $call */
		$call = $connectors->get_call( 'wfco_mailerlite_get_subscriber_fields' );
		$call->set_data( $params );
		$result = $call->process();

		if ( 4 === $result['status'] ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => $result['message'],
			) );
		}

		$fields = $result['payload'];

		$subscriber_fields = [];
		foreach ( $fields as $data ) {
			$subscriber_fields[ $data['key'] ] = $data['title'];
		}

		return $subscriber_fields;
	}

	/**
	 * Fetch Mailerlite Tags
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_groups( $params, $captured_items = [] ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Mautic_Get_Contact_Fields $call */
		$call = $connectors->get_call( 'wfco_mailerlite_get_group_list' );
		$call->set_data( $params );
		$result = $call->process();

		if ( 4 === $result['status'] ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => $result['message'],
			) );
		}

		$groups         = $result['payload'];
		$captured_items = [];
		foreach ( $groups as $group ) {
			$captured_items[ $group['id'] ] = $group['name'];
		}

		return $captured_items;
	}


	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_mailerlite'] = array(
			'name'            => 'Mailerlite',
			'desc'            => __( 'Add or Remove tags, Change contact\'s list, Update merge custom fields and much more.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_Mailerlite',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}
}

WFCO_Load_Connectors::register( 'BWFCO_Mailerlite' );
