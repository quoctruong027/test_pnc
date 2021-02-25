<?php

class BWFCO_Mailchimp extends BWF_CO {
	public static $api_end_point = 'https://<dc>.api.mailchimp.com/';
	public static $headers = null;
	private static $ins = null;

	public function __construct() {
		/** Setup includes from add-on plugin */
		$this->define_plugin_properties();
		$this->init_mailchimp();

		/** Connector.php initialization */
		$this->keys_to_track = [
			'api_key',
			'default_list',
			'custom_fields',
			'tags',
			'lists',
			'stores',
			'products'
		];
		$this->form_req_keys = [
			'api_key',
			'default_list',
			'default_store'
		];

		$this->sync          = true;
		$this->connector_url = WFCO_MAILCHIMP_PLUGIN_URL;
		$this->dir           = __DIR__;
		$this->nice_name     = __( 'Mailchimp', 'autonami-automations-connectors' );

		$this->autonami_int_slug = 'BWFAN_Mailchimp_Integration';

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
		add_action( 'wp_ajax_bwf_get_mailchimp_lists', array( $this, 'ajax_get_mailchimp_lists' ) );
		add_action( 'wp_ajax_bwf_get_mailchimp_stores', array( $this, 'ajax_get_mailchimp_stores' ) );

		/** Mailchimp uses JSON formatted data as Body */
		add_filter( 'http_request_args', array( $this, 'parse_body_for_mailchimp' ), 999, 2 );

		/** In case a new product is created by Autonami */
		add_action( 'bwfan_mailchimp_product_created', array( $this, 'add_new_product_to_connector' ) );
	}

	public function add_new_product_to_connector( $product ) {
		$settings = WFCO_Mailchimp_Common::get_mailchimp_settings();
		if ( ! isset( $settings['products'] ) || ! is_array( $settings['products'] ) ) {
			return;
		}

		$settings['products'][ $product['id'] ] = $product['product_name'];
		WFCO_Mailchimp_Common::update_settings( $settings );
	}

	/**
	 * Defining constants
	 */
	public function define_plugin_properties() {
		define( 'WFCO_MAILCHIMP_VERSION', '1.0.0' );
		define( 'WFCO_MAILCHIMP_FULL_NAME', 'Autonami Marketing Automations Connectors: Mailchimp' );
		define( 'WFCO_MAILCHIMP_PLUGIN_FILE', __FILE__ );
		define( 'WFCO_MAILCHIMP_PLUGIN_DIR', __DIR__ );
		define( 'WFCO_MAILCHIMP_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_MAILCHIMP_PLUGIN_FILE ) ) );
		define( 'WFCO_MAILCHIMP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'WFCO_MAILCHIMP_MAIN', 'autonami-automations-connectors' );
		define( 'WFCO_MAILCHIMP_ENCODE', sha1( WFCO_MAILCHIMP_PLUGIN_BASENAME ) );
	}

	public function init_mailchimp() {
		require WFCO_MAILCHIMP_PLUGIN_DIR . '/includes/class-wfco-mailchimp-common.php';
		require WFCO_MAILCHIMP_PLUGIN_DIR . '/includes/class-bwfan-mailchimp-webook-setup.php';
	}

	public function parse_body_for_mailchimp( $args, $url ) {
		$settings    = WFCO_Mailchimp_Common::get_mailchimp_settings();
		$data_center = isset( $settings['api_key'] ) && ! empty( $settings['api_key'] ) ? self::get_data_center( $settings['api_key'] ) : false;
		if ( empty( $data_center ) && isset( $_POST['api_key'] ) && ! empty( $_POST['api_key'] ) ) {
			BWFAN_Common::check_nonce();
			$data_center = self::get_data_center( sanitize_text_field( $_POST['api_key'] ) );
		}

		if ( false === $data_center || false === strpos( $url, self::get_endpoint( $data_center ) ) ) {
			return $args;
		}

		$args['body'] = empty( $args['body'] ) ? '' : wp_json_encode( $args['body'] );

		return $args;
	}

	public function ajax_get_mailchimp_lists() {
		BWFAN_Common::check_nonce();

		$api_key = isset( $_POST['api_key'] ) ? $_POST['api_key'] : '';
		if ( empty( $api_key ) ) {
			wp_send_json( array(
				'response' => __( 'API Key is not provided', 'autonami-automations-connectors' )
			) );
		}

		$lists_result = $this->fetch_lists( array( 'api_key' => $api_key ) );
		if ( ! is_array( $lists_result ) || ! count( $lists_result ) > 0 ) {
			wp_send_json( array( 'status' => false ) );
		}

		wp_send_json( $lists_result );
	}

	public function ajax_get_mailchimp_stores() {
		BWFAN_Common::check_nonce();

		$api_key = isset( $_POST['api_key'] ) ? $_POST['api_key'] : '';
		if ( empty( $api_key ) ) {
			wp_send_json( array(
				'response' => __( 'API Key is not provided', 'autonami-automations-connectors' )
			) );
		}

		$list_id = isset( $_POST['list_id'] ) ? $_POST['list_id'] : '';
		if ( empty( $list_id ) ) {
			wp_send_json( array(
				'response' => __( 'List ID is not provided', 'autonami-automations-connectors' )
			) );
		}

		/** Fetch E-Commerce Stores */
		$stores_result = $this->fetch_stores( array( 'api_key' => $api_key ) );
		if ( ! is_array( $stores_result ) || ! count( $stores_result ) > 0 ) {
			$stores_result = $this->create_store( array( 'api_key' => $api_key, 'list_id' => $list_id ) );
		}

		wp_send_json( $stores_result );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public static function get_endpoint( $data_center, $version = '3.0' ) {
		return str_replace( '<dc>', $data_center, self::$api_end_point . $version . '/' );
	}

	public static function get_headers() {
		return self::$headers;
	}

	public static function set_headers( $api_key ) {
		$headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Basic ' . base64_encode( 'autonami:' . $api_key )
		);

		self::$headers = $headers;
	}

	public static function get_data_center( $api_key ) {
		if ( empty( $api_key ) || false === strpos( $api_key, '-' ) ) {
			return false;
		}

		return explode( '-', $api_key )[1];
	}

	/**
	 * This function connects to the automation and fetch the data required for the actions on automations screen to work properly.
	 *
	 * @param $posted_data
	 *
	 * @return array|int
	 */
	public function get_api_data( $posted_data ) {
		$resp_array                              = array();
		$resp_array['api_data']['api_key']       = isset( $posted_data['api_key'] ) ? $posted_data['api_key'] : '';
		$resp_array['api_data']['default_list']  = isset( $posted_data['default_list'] ) ? $posted_data['default_list'] : '';
		$resp_array['api_data']['default_store'] = isset( $posted_data['default_store'] ) ? $posted_data['default_store'] : '';
		$resp_array['status']                    = 'success';

		if ( ! isset( $posted_data['api_key'] ) || empty( $posted_data['api_key'] ) ) {
			return $resp_array;
		}

		$params = array(
			'api_key'  => $posted_data['api_key'],
			'list_id'  => $posted_data['default_list'],
			'store_id' => $posted_data['default_store']
		);

		/** Fetch Custom Fields */
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

		/** Fetch E-Commerce Stores */
		$stores_result = $this->fetch_stores( $params );
		if ( is_array( $stores_result ) && count( $stores_result ) > 0 ) {
			$resp_array['api_data']['stores'] = $stores_result;
		}

		/** Fetch Products */
		$products_result = $this->fetch_products( $params );
		if ( is_array( $products_result ) && count( $products_result ) > 0 ) {
			$resp_array['api_data']['products'] = $products_result;
		}

		/** Fetch Automations */
		$automations_result = $this->fetch_automations( $params );
		if ( is_array( $automations_result ) && count( $automations_result ) > 0 ) {
			$resp_array['api_data']['automations'] = $automations_result;
		}

		return $resp_array;
	}

	public function create_store( $params ) {
		$connectors = WFCO_Load_Connectors::get_instance();

		$params['store_id']      = 'autonami_store';
		$params['store_name']    = get_bloginfo( 'name' );
		$currency                = get_woocommerce_currency();
		$params['currency_code'] = ! empty( $currency ) ? $currency : 'USD';

		/** @var WFCO_Mailchimp_Create_Store $call */
		$call = $connectors->get_call( 'wfco_mailchimp_create_store' );
		$call->set_data( $params );
		$result = $call->process();

		if ( 200 !== $result['response'] || ! isset( $result['body']['id'] ) ) {
			$error = __( 'Error Response Code: ', 'autonami-automations-connectors' ) . $result['response'] . __( '. Mailchimp Error: ', 'autonami-automations-connectors' );
			$error .= is_array( $result['body'] ) && isset( $result['body']['detail'] ) ? $result['body']['detail'] : __( 'No Response from Mailchimp. ', 'autonami-automations-connectors' );
			$error .= ( 502 === absint( $result['response'] ) ) ? __( 'Autonami Error: ', 'autonami-automations-connectors' ) . $result['body'][0] : '';

			wp_send_json( array(
				'status'  => 'failed',
				'message' => $error,
			) );
		}

		return array( $result['body']['id'] => $result['body']['name'] );
	}

	/**
	 * Fetch Mailchimp Custom Contact Fields
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_custom_fields( $params, $captured_items = [] ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Mautic_Get_Contact_Fields $call */
		$call = $connectors->get_call( 'wfco_mailchimp_get_custom_fields' );
		$call->set_data( $params );
		$result = $call->process();

		if ( 200 !== absint( $result['response'] ) ) {
			$error = __( 'Error Response Code: ', 'autonami-automations-connectors' ) . $result['response'] . __( '. Mailchimp Error: ', 'autonami-automations-connectors' );
			$error .= is_array( $result['body'] ) && isset( $result['body']['detail'] ) ? $result['body']['detail'] : __( 'No Response from Mailchimp. ', 'autonami-automations-connectors' );
			$error .= ( 502 === absint( $result['response'] ) ) ? __( 'Autonami Error: ', 'autonami-automations-connectors' ) . $result['body'][0] : '';

			wp_send_json( array(
				'status'  => 'failed',
				'message' => $error,
			) );
		}

		$total_items_count = absint( $result['body']['total_items'] );
		$data              = $result['body']['merge_fields'];
		foreach ( $data as $row ) {
			$id                    = $row['tag'];
			$captured_items[ $id ] = $row['name'];
		}

		$offset = '';
		if ( $total_items_count > count( $captured_items ) ) {
			$offset = count( $captured_items );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;

			return $this->fetch_custom_fields( $params, $captured_items );
		}

		return $captured_items;
	}

	/**
	 * Fetch Mailchimp Tags
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_tags( $params, $captured_items = [] ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Mautic_Get_Contact_Fields $call */
		$call = $connectors->get_call( 'wfco_mailchimp_get_tags' );
		$call->set_data( $params );
		$result = $call->process();

		if ( 200 !== absint( $result['response'] ) ) {
			$error = __( 'Error Response Code: ', 'autonami-automations-connectors' ) . $result['response'] . __( '. Mailchimp Error: ', 'autonami-automations-connectors' );
			$error .= is_array( $result['body'] ) && isset( $result['body']['detail'] ) ? $result['body']['detail'] : __( 'No Response from Mailchimp. ', 'autonami-automations-connectors' );
			$error .= ( 502 === absint( $result['response'] ) ) ? __( 'Autonami Error: ', 'autonami-automations-connectors' ) . $result['body'][0] : '';

			wp_send_json( array(
				'status'  => 'failed',
				'message' => $error,
			) );
		}

		$total_items_count = absint( $result['body']['total_items'] );
		$data              = $result['body']['segments'];
		foreach ( $data as $row ) {
			$id                    = $row['id'];
			$captured_items[ $id ] = $row['name'];
		}

		$offset = '';
		if ( $total_items_count > count( $captured_items ) ) {
			$offset = count( $captured_items );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;

			return $this->fetch_tags( $params, $captured_items );
		}

		return $captured_items;
	}

	/**
	 * Fetch Mailchimp Lists
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_lists( $params, $captured_items = [] ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Mautic_Get_Contact_Fields $call */
		$call = $connectors->get_call( 'wfco_mailchimp_get_lists' );
		$call->set_data( $params );
		$result = $call->process();

		if ( 200 !== absint( $result['response'] ) ) {
			$error = __( 'Error Response Code: ', 'autonami-automations-connectors' ) . $result['response'] . __( '. Mailchimp Error: ', 'autonami-automations-connectors' );
			$error .= is_array( $result['body'] ) && isset( $result['body']['detail'] ) ? $result['body']['detail'] : __( 'No Response from Mailchimp. ', 'autonami-automations-connectors' );
			$error .= ( 502 === absint( $result['response'] ) ) ? __( 'Autonami Error: ', 'autonami-automations-connectors' ) . $result['body'][0] : '';

			wp_send_json( array(
				'status'  => 'failed',
				'message' => $error,
			) );
		}

		$total_items_count = absint( $result['body']['total_items'] );
		$data              = $result['body']['lists'];
		foreach ( $data as $row ) {
			$id                    = $row['id'];
			$captured_items[ $id ] = $row['name'];
		}

		$offset = '';
		if ( $total_items_count > count( $captured_items ) ) {
			$offset = count( $captured_items );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;

			return $this->fetch_lists( $params, $captured_items );
		}

		return $captured_items;
	}

	/**
	 * Fetch Mailchimp Stores
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_stores( $params, $captured_items = [] ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Mautic_Get_Contact_Fields $call */
		$call = $connectors->get_call( 'wfco_mailchimp_get_stores' );
		$call->set_data( $params );
		$result = $call->process();

		if ( 200 !== absint( $result['response'] ) ) {
			$error = __( 'Error Response Code: ', 'autonami-automations-connectors' ) . $result['response'] . __( '. Mailchimp Error: ', 'autonami-automations-connectors' );
			$error .= is_array( $result['body'] ) && isset( $result['body']['detail'] ) ? $result['body']['detail'] : __( 'No Response from Mailchimp. ', 'autonami-automations-connectors' );
			$error .= ( 502 === absint( $result['response'] ) ) ? __( 'Autonami Error: ', 'autonami-automations-connectors' ) . $result['body'][0] : '';

			wp_send_json( array(
				'status'  => 'failed',
				'message' => $error,
			) );
		}

		$total_items_count = absint( $result['body']['total_items'] );
		$data              = $result['body']['stores'];
		foreach ( $data as $row ) {
			$id                    = $row['id'];
			$captured_items[ $id ] = $row['name'];
		}

		$offset = '';
		if ( $total_items_count > count( $captured_items ) ) {
			$offset = count( $captured_items );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;

			return $this->fetch_stores( $params, $captured_items );
		}

		return $captured_items;
	}

	/**
	 * Fetch Mailchimp Stores
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_products( $params, $captured_items = [] ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		$call       = $connectors->get_call( 'wfco_mailchimp_get_products' );
		$call->set_data( $params );
		$result = $call->process();

		if ( 200 !== absint( $result['response'] ) ) {
			$error = __( 'Error Response Code: ', 'autonami-automations-connectors' ) . $result['response'] . __( '. Mailchimp Error: ', 'autonami-automations-connectors' );
			$error .= is_array( $result['body'] ) && isset( $result['body']['detail'] ) ? $result['body']['detail'] : __( 'No Response from Mailchimp. ', 'autonami-automations-connectors' );
			$error .= ( 502 === absint( $result['response'] ) ) ? __( 'Autonami Error: ', 'autonami-automations-connectors' ) . $result['body'][0] : '';

			wp_send_json( array(
				'status'  => 'failed',
				'message' => $error,
			) );
		}

		$total_items_count = absint( $result['body']['total_items'] );
		$data              = $result['body']['products'];
		foreach ( $data as $row ) {
			$id                    = $row['id'];
			$captured_items[ $id ] = $row['title'];
		}

		$offset = '';
		if ( $total_items_count > count( $captured_items ) ) {
			$offset = count( $captured_items );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;

			return $this->fetch_products( $params, $captured_items );
		}

		return $captured_items;
	}

	/**
	 * Fetch Mailchimp Automations
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_automations( $params, $captured_items = [] ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		$call       = $connectors->get_call( 'wfco_mailchimp_get_automations' );
		$call->set_data( $params );
		$result = $call->process();

		if ( 200 !== absint( $result['response'] ) ) {
			$error = __( 'Error Response Code: ', 'autonami-automations-connectors' ) . $result['response'] . __( '. Mailchimp Error: ', 'autonami-automations-connectors' );
			$error .= is_array( $result['body'] ) && isset( $result['body']['detail'] ) ? $result['body']['detail'] : __( 'No Response from Mailchimp. ', 'autonami-automations-connectors' );
			$error .= ( 502 === absint( $result['response'] ) ) ? __( 'Autonami Error: ', 'autonami-automations-connectors' ) . $result['body'][0] : '';

			wp_send_json( array(
				'status'  => 'failed',
				'message' => $error,
			) );
		}

		$total_items_count = absint( $result['body']['total_items'] );
		$data              = $result['body']['automations'];
		foreach ( $data as $row ) {
			$captured_items[ $row['id'] ] = $row['settings']['title'];
		}

		$offset = '';
		if ( $total_items_count > count( $captured_items ) ) {
			$offset = count( $captured_items );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;

			return $this->fetch_automations( $params, $captured_items );
		}

		return $captured_items;
	}

	/**
	 * Fetch Mailchimp Automations Emails
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_automations_email( $params, $captured_items = [] ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		$call       = $connectors->get_call( 'wfco_mailchimp_get_automations_emails' );
		$call->set_data( $params );
		$result = $call->process();

		if ( 200 !== absint( $result['response'] ) ) {
			$error = __( 'Error Response Code: ', 'autonami-automations-connectors' ) . $result['response'] . __( '. Mailchimp Error: ', 'autonami-automations-connectors' );
			$error .= is_array( $result['body'] ) && isset( $result['body']['detail'] ) ? $result['body']['detail'] : __( 'No Response from Mailchimp. ', 'autonami-automations-connectors' );
			$error .= ( 502 === absint( $result['response'] ) ) ? __( 'Autonami Error: ', 'autonami-automations-connectors' ) . $result['body'][0] : '';

			wp_send_json( array(
				'status'  => 'failed',
				'message' => $error,
			) );
		}

		$total_items_count = absint( $result['body']['total_items'] );
		$data              = $result['body']['emails'];
		foreach ( $data as $row ) {
			$captured_items[] = $row['id'];
		}

		$offset = '';
		if ( $total_items_count > count( $captured_items ) ) {
			$offset = count( $captured_items );
		}
		if ( ! empty( $offset ) ) {
			$params['offset'] = $offset;

			return $this->fetch_automations_email( $params, $captured_items );
		}

		return $captured_items;
	}

	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_mailchimp'] = array(
			'name'            => 'Mailchimp',
			'desc'            => __( 'Add or Remove tags, Change contact\'s list, Update merge custom fields and much more.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_Mailchimp',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}

}

WFCO_Load_Connectors::register( 'BWFCO_Mailchimp' );
