<?php

class BWFCO_Keap extends BWF_CO {
	/** API Endpoint is the home URL of the Keap Installation */
	public static $api_end_point = 'https://api.infusionsoft.com/crm/rest/';
	public static $access_token_endpoint = 'https://api.infusionsoft.com/token/';
	public static $account_id = null;
	public static $headers = null;
	private static $ins = null;

	/** only require for oauth check  */
	public $oauth_endpoint = 'https://signin.infusionsoft.com/app/oauth/authorize';
	public $redirect_uri = null; // current application's redirect url

	public function __construct() {
		/** Init Keap Core as done in Add-On */
		$this->init_keap_core();

		/** Then do the rest of the things */
		$this->keys_to_track = [
			'client_id',
			'client_secret',
			'access_token',
			'refresh_token',
			'expires_in',
			'custom_fields',
			'optional_fields',
			'tags',
			'products'
		];
		$this->form_req_keys = [
			'client_id',
			'client_secret',
			'access_token',
			'refresh_token'
		];

		$this->sync          = true;
		$this->connector_url = WFCO_KEAP_PLUGIN_URL;
		$this->dir           = __DIR__;
		$this->nice_name     = __( 'Keap', 'autonami-automations-connectors' );
		$this->redirect_uri  = add_query_arg( array(
			'action' => 'bwfan_keap_redirect_oauth',
		), admin_url( 'admin-post.php' ) );

		$this->autonami_int_slug = 'BWFAN_Keap_Integration';

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
		add_action( 'wp_ajax_bwf_keap_connect_with_code', array( $this, 'keap_connect_with_code' ) );
		add_action( 'wp_ajax_bwf_temp_save_keap_credentials', array( $this, 'bwfco_temp_save_keap_credentials' ) );

		/** GetResponse uses JSON formatted data as Body */
		add_filter( 'http_request_args', array( $this, 'parse_body_for_keap' ), 999, 2 );

		/** Add tag to connector's global settings */
		add_action( 'wfco_keap_tag_created', array( $this, 'add_tag_to_settings' ), 10, 2 );
	}

	public function init_keap_core() {
		require_once __DIR__ . '/class-wfco-keap-core.php';
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public static function get_headers() {
		return self::$headers;
	}

	public static function set_headers( $access_token = '', $is_refreshing_token = false ) {
		$headers = array(
			'Content-Type' => 'application/x-www-form-urlencoded',
		);

		if ( false === $is_refreshing_token && ! empty( $access_token ) ) {
			$headers = array(
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json, */*',
				'Authorization' => 'Bearer ' . $access_token
			);
		} else if ( true === $is_refreshing_token && ! empty( $access_token ) ) {
			/** In this case, Access Token must be: $access_token = $client_id . ':' . $client_secret */
			$headers['Authorization'] = 'Basic ' . base64_encode( $access_token );
		}
		self::$headers = $headers;
	}

	public function add_tag_to_settings( $tag_id, $tag_name ) {
		$settings = WFCO_Keap_Common::get_keap_settings();
		if ( ! isset( $settings['tags'] ) || ! is_array( $settings['tags'] ) ) {
			return;
		}

		$settings['tags'][ $tag_id ] = $tag_name;
		WFCO_Keap_Common::update_settings( $settings );
	}

	public function parse_body_for_keap( $args, $url ) {
		if ( false === strpos( $url, self::get_endpoint() ) ) {
			return $args;
		}

		$args['body'] = wp_json_encode( $args['body'] );

		return $args;
	}

	public static function get_endpoint( $version = 'v1', $access_token_endpoint = false ) {
		if ( true === $access_token_endpoint ) {
			return self::$access_token_endpoint;
		}

		return self::$api_end_point . $version . '/';
	}

	/**
	 * Loads all calls of current connector
	 */
	public function load_calls() {
		$resource_dir = $this->dir . '/calls';
		if ( @file_exists( $resource_dir ) ) {
			foreach ( glob( $resource_dir . '/class-*.php' ) as $filename ) {
				$call_class = require_once( $filename );
				if ( method_exists( $call_class, 'get_instance' ) ) {
					/** @var WFCO_Keap_Call $call_obj */
					$call_obj = $call_class::get_instance();
					if ( true === $call_obj->need_wc_active && ! class_exists( 'WooCommerce' ) ) {
						continue;
					}

					$call_obj->set_connector_slug( $this->get_slug() );
					WFCO_Load_Connectors::register_calls( $call_obj );
				}
			}
		}

		do_action( 'bwfan_' . $this->get_slug() . '_actions_loaded' );
	}

	public function bwfco_temp_save_keap_credentials() {
		if ( ! isset( $_POST['_wpnonce'] ) || false === wp_verify_nonce( sanitize_text_field( $_POST['_wpnonce'] ), 'bwfco_temp_save_keap_cred_nonce' ) ) {
			wp_send_json( array( 'status' => false, 'message' => __( 'Security Token Expired / Invalid. Reloading the page...', 'autonami-automations-connectors' ) ) );
		}

		if ( isset( $_POST['client_id'] ) && ! empty( $_POST['client_id'] ) && isset( $_POST['client_secret'] ) && ! empty( $_POST['client_secret'] ) ) {
			update_option( 'bwfco_temp_keap_client_id', sanitize_text_field( $_POST['client_id'] ) );
			update_option( 'bwfco_temp_keap_client_secret', sanitize_text_field( $_POST['client_secret'] ) );

			wp_send_json( array( 'status' => true ) );
		}

		wp_send_json( array( 'status' => false, 'message' => __( 'One of the settings is not provided', 'autonami-automations-connectors' ) ) );
	}

	public function keap_connect_with_code() {
		$resp = array(
			'status'       => false,
			'message'      => __( 'Something is wrong, no data exists.', 'autonami-automations-connectors' ),
			'data_changed' => false,
		);

		if ( ! isset( $_POST['state'] ) || false === wp_verify_nonce( sanitize_text_field( $_POST['state'] ), 'wfco_keap_state' ) ) {
			wp_send_json( array( 'status' => false, 'message' => 'Keap state is invalid' ) );
		}

		if ( ! isset( $_POST['code'] ) ) {
			wp_send_json( array( 'status' => false, 'message' => 'Keap code is not provided' ) );
		}

		$client_id     = get_option( 'bwfco_temp_keap_client_id', true );
		$client_secret = get_option( 'bwfco_temp_keap_client_secret', true );

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			wp_send_json( array( 'status' => false, 'message' => 'Keap Details not found' ) );
		}

		$connector = WFCO_Load_Connectors::get_instance();
		$call      = $connector->get_call( 'wfco_keap_get_access_token' );
		$call->set_data( array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'code'          => sanitize_text_field( $_POST['code'] ),
			'redirect_uri'  => $this->redirect_uri
		) );
		$result = $call->process();

		if ( ! is_array( $result ) || ( isset( $result['body'] ) && is_array( $result['body'] ) && isset( $result['body']['error'] ) ) || 200 !== $result['response'] ) {
			$error_description = isset( $result['body']['error_description'] ) ? $result['body']['error_description'] : __( 'Unknown error while exchanging code for access token', 'autonami-automations-connectors' );
			wp_send_json( array( 'status' => false, 'message' => $error_description ) );
		}

		if ( is_array( $result ) && $result['response'] === 200 ) {

			$params = array(
				'access_token'  => $result['body']['access_token'],
				'expires_in'    => time() + absint( $result['body']['expires_in'] ),
				'refresh_token' => $result['body']['refresh_token'],
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'redirect_uri'  => $this->redirect_uri
			);

			$saved_data    = WFCO_Common::$connectors_saved_data;
			$saved_data_id = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && isset( $saved_data[ $this->get_slug() ]['id'] ) ) ? absint( $saved_data[ $this->get_slug() ]['id'] ) : 0;

			if ( $saved_data_id > 0 ) {
				$params['id'] = $saved_data_id;
			}

			$active_connectors = WFCO_Load_Connectors::get_active_connectors();
			/** @var BWF_CO $connector_ins */
			$connector_ins = $active_connectors[ $this->get_slug() ]; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			$response      = $connector_ins->handle_settings_form( $params, $saved_data_id > 0 ? 'update' : 'save' ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification

			$resp['status']  = ( 'success' === $response['status'] ) ? true : false;
			$resp['message'] = $response['message'];

			/** Error occurred */
			if ( false === $resp['status'] ) {
				wp_send_json( $resp );
			}

			/** Call succeeded */
			$resp['status']       = true;
			$resp['id']           = $response['id'];
			$resp['redirect_url'] = $this->redirect_uri;
		}

		if ( is_array( $result ) && $result['response'] === 502 && is_array( $result['body'] ) ) {
			wp_send_json( array( 'status' => false, 'message' => $result['body'][0] ) );
		}

		wp_send_json( $resp );
	}

	/**
	 * This function connects to the automation and fetch the data required for the actions on automations screen to work properly.
	 *
	 * @param $posted_data
	 *
	 * @return array|int
	 */
	public function get_api_data( $posted_data ) {
		$posted_data = $this->maybe_refresh_tokens( $posted_data );

		$resp_array             = array();
		$resp_array['api_data'] = $posted_data;
		$resp_array['status']   = 'success';

		if ( ! isset( $posted_data['access_token'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( 'Access Token Missing', 'autonami-automations-connectors' ),
			);
		}

		$params = array(
			'access_token'           => $posted_data['access_token'],
			'connector_initialising' => true
		);

		/** Fetch Contact Fields */
		$custom_fields_result = $this->fetch_custom_fields( $params );
		if ( is_array( $custom_fields_result ) && count( $custom_fields_result ) > 0 && isset( $custom_fields_result['custom_fields'] ) ) {
			$resp_array['api_data']['custom_fields']   = $custom_fields_result['custom_fields'];
			$resp_array['api_data']['optional_fields'] = $custom_fields_result['optional_fields'];
		}

		/** Fetch Tags */
		$tags_result = $this->fetch_tags( [], $params );
		if ( is_array( $tags_result ) && count( $tags_result ) > 0 ) {
			$resp_array['api_data']['tags'] = $tags_result;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			return $resp_array;
		}

		return $resp_array;
	}

	public function maybe_refresh_tokens( $posted_data ) {
		$expires_in   = isset( $posted_data['expires_in'] ) && ! empty( $posted_data['expires_in'] ) ? absint( $posted_data['expires_in'] ) : 0;
		$current_time = time();

		/** Tokens aren't expired */
		if ( $current_time < $expires_in ) {
			return $posted_data;
		}

		/** Tokens are expired */
		$params = array(
			'refresh_token' => isset( $posted_data['refresh_token'] ) ? $posted_data['refresh_token'] : '',
			'client_id'     => isset( $posted_data['client_id'] ) ? $posted_data['client_id'] : '',
			'client_secret' => isset( $posted_data['client_secret'] ) ? $posted_data['client_secret'] : '',
			'redirect_uri'  => $this->redirect_uri,
		);

		$connector = WFCO_Load_Connectors::get_instance();
		$call      = $connector->get_call( 'wfco_keap_get_access_token' );
		$call->set_data( $params );
		$result = $call->process();

		/** Handle error, if unable to refresh tokens */
		if ( 200 !== $result['response'] ) {

			if ( 502 === absint( $result['response'] ) && is_array( $result['body'] ) ) {
				return array(
					'status'   => 'failed',
					'api_data' => array(),
					'message'  => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
				);
			}

			$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $result['response'];
			$fault_message   = ( is_array( $result['body'] ) && isset( $result['body']['fault'] ) ) ? $result['body']['fault']['faultstring'] : false;
			$message         = isset( $result['body']['message'] ) ? $result['body']['message'] : false;
			$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => false !== $fault_message ? $fault_message : ( false !== $message ? $message : $unknown_message ) . $response_code,
			);
		}

		/** All went good, let's send new tokens back with posted data */
		$new_tokens = $settings = array(
			'access_token'  => $result['body']['access_token'],
			'expires_in'    => time() + absint( $result['body']['expires_in'] ),
			'refresh_token' => $result['body']['refresh_token']
		);

		return array_replace( $posted_data, $new_tokens );
	}

	/**
	 * Fetch Keap Custom Contact Fields
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_custom_fields( $params ) {
		$connectors        = WFCO_Load_Connectors::get_instance();
		$all_custom_fields = [];
		/** @var WFCO_Keap_Get_Contact_Fields $custom_fields_call */
		$custom_fields_call = $connectors->get_call( 'wfco_keap_get_contact_fields' );
		$custom_fields_call->set_data( $params );
		$custom_fields_result = $custom_fields_call->process();

		if ( isset( $custom_fields_result['response'] ) && 502 === absint( $custom_fields_result['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( $custom_fields_result['body'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $custom_fields_result['body']['fault'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( 'Error: ' . $custom_fields_result['body']['fault']['faultstring'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $custom_fields_result['response'] ) && 200 !== absint( $custom_fields_result['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => isset( $custom_fields_result['body']['message'] ) ? __( 'Error: ' . $custom_fields_result['body']['message'], 'autonami-automations-connectors' ) : __( 'Unable to get Custom Fields', 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $custom_fields_result['body']['custom_fields'] ) ) {
			$fetched_custom_fields = $custom_fields_result['body']['custom_fields'];
			$optional_fields       = $custom_fields_result['body']['optional_properties'];

			$custom_fields = array();
			foreach ( $fetched_custom_fields as $field ) {
				$custom_fields[ $field['id'] ] = $field['label'];
			}

			$all_custom_fields['custom_fields']   = $custom_fields;
			$all_custom_fields['optional_fields'] = $optional_fields;
		}

		return $all_custom_fields;
	}

	/**
	 * Fetch Keap Tags
	 *
	 * @param $captured_tags
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_tags( $captured_tags, $params ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		$all_tags   = [];
		/** @var WFCO_Keap_Get_Tags $tags_call */
		$tags_call = $connectors->get_call( 'wfco_keap_get_tags' );
		$tags_call->set_data( $params );
		$tags_result = $tags_call->process();

		if ( isset( $tags_result['response'] ) && 502 === absint( $tags_result['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( $tags_result['body'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $tags_result['body']['fault'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( 'Error: ' . $tags_result['body']['fault']['faultstring'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $tags_result['response'] ) && 200 !== absint( $tags_result['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => isset( $tags_result['body']['message'] ) ? __( 'Error: ' . $tags_result['body']['message'], 'autonami-automations-connectors' ) : __( 'Unable to get Tags', 'autonami-automations-connectors' ),
			);
		}

		if ( ! is_array( $tags_result ) || 200 !== $tags_result['response'] || ! isset( $tags_result['body']['tags'] ) || ! is_array( $tags_result['body']['tags'] ) ) {
			return $all_tags;
		}

		$tags = $tags_result['body']['tags'];
		foreach ( $tags as $tag_details ) {
			$tag_id                   = $tag_details['id'];
			$captured_tags[ $tag_id ] = $tag_details['name'];
		}

		$all_tags = $captured_tags;
		if ( count( $tags ) > 0 && isset( $tags_result['body']['next'] ) ) {
			$params['next_url'] = $tags_result['body']['next'];

			return $this->fetch_tags( $all_tags, $params );
		}

		return $all_tags;
	}

	/**
	 * Fetch Keap Products
	 *
	 * @param $captured_products
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_products( $captured_products, $params ) {
		$connectors   = WFCO_Load_Connectors::get_instance();
		$all_products = [];
		/** @var WFCO_Keap_Get_Products $products_call */
		$products_call = $connectors->get_call( 'wfco_keap_get_products' );
		$products_call->set_data( $params );
		$products = $products_call->process();

		if ( isset( $products['response'] ) && 502 === absint( $products['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( $products['body'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $products['body']['fault'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( 'Error: ' . $products['body']['fault']['faultstring'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $products['response'] ) && 200 !== absint( $products['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => isset( $products['body']['message'] ) ? __( 'Error: ' . $products['body']['message'], 'autonami-automations-connectors' ) : __( 'Unable to get Products', 'autonami-automations-connectors' ),
			);
		}

		if ( ! is_array( $products ) || 200 !== $products['response'] || ! isset( $products['body']['products'] ) || ! is_array( $products['body']['products'] ) ) {
			return $all_products;
		}

		$prods = $products['body']['products'];
		foreach ( $prods as $product ) {
			$id                       = $product['id'];
			$captured_products[ $id ] = $product['product_name'];
		}

		$all_products = $captured_products;
		if ( count( $prods ) > 0 && isset( $products['body']['next'] ) ) {
			$params['next_url'] = $products['body']['next'];

			return $this->fetch_products( $all_products, $params );
		}

		return $all_products;
	}

	/**
	 * Fetch Keap Campaigns
	 *
	 * @param $captured_campaigns
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_campaigns( $captured_campaigns, $params ) {
		$connectors    = WFCO_Load_Connectors::get_instance();
		$all_campaigns = [];
		/** @var WFCO_Keap_Get_Campaigns $tags_call */
		$campaigns_call = $connectors->get_call( 'wfco_keap_get_campaigns' );
		$campaigns_call->set_data( $params );
		$campaigns_result = $campaigns_call->process();

		if ( isset( $campaigns_result['response'] ) && 502 === absint( $campaigns_result['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( $campaigns_result['body'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $campaigns_result['body']['fault'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( 'Error: ' . $campaigns_result['body']['fault']['faultstring'] ),
			);
		}

		if ( isset( $campaigns_result['response'] ) && 200 !== absint( $campaigns_result['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => isset( $campaigns_result['body']['message'] ) ? __( 'Error: ' . $campaigns_result['body']['message'], 'autonami-automations-connectors' ) : __( 'Unable to get Campaigns', 'autonami-automations-connectors' ),
			);
		}

		if ( ! is_array( $campaigns_result ) || 200 !== $campaigns_result['response'] || ! isset( $campaigns_result['body']['campaigns'] ) || ! is_array( $campaigns_result['body']['campaigns'] ) ) {
			return $all_campaigns;
		}

		$campaigns = $campaigns_result['body']['campaigns'];
		foreach ( $campaigns as $campaign_details ) {
			$campaign_id = $campaign_details['id'];

			$sequences = array();
			if ( isset( $campaign_details['sequences'] ) && ! empty( $campaign_details['sequences'] ) ) {
				foreach ( $campaign_details['sequences'] as $sequence ) {
					$sequences[ $sequence['id'] ] = $sequence['name'];
				}
			}

			$captured_campaigns[ $campaign_id ] = array(
				'name'      => $campaign_details['name'],
				'sequences' => $sequences
			);
		}

		$all_campaigns = $captured_campaigns;
		if ( count( $campaigns ) > 0 && isset( $campaigns_result['body']['next'] ) ) {
			$params['next_url'] = $campaigns_result['body']['next'];

			return $this->fetch_campaigns( $all_campaigns, $params );
		}

		return $all_campaigns;
	}

	/**
	 * Fetch Keap Segments
	 *
	 * @param $captured_segments
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_segments( $captured_segments, $params ) {
		$connectors   = WFCO_Load_Connectors::get_instance();
		$all_segments = [];
		/** @var WFCO_Keap_Get_Segments $tags_call */
		$segments_call = $connectors->get_call( 'wfco_keap_get_segments' );
		$segments_call->set_data( $params );
		$segments_result = $segments_call->process();

		if ( isset( $segments_result['response'] ) && 502 === absint( $segments_result['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( $segments_result['body'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $segments_result['body']['errors'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( 'Error: ' . $segments_result['body']['errors'][0]['message'], 'autonami-automations-connectors' ),
			);
		}

		if ( ! is_array( $segments_result ) || 200 !== $segments_result['response'] || ! isset( $segments_result['body']['lists'] ) || ! is_array( $segments_result['body']['lists'] ) || 0 === count( $segments_result['body']['lists'] ) ) {
			return $all_segments;
		}

		$total_segment_count = intval( $segments_result['body']['total'] );
		$segments            = $segments_result['body']['lists'];
		foreach ( $segments as $segment_details ) {
			$segment_id                       = $segment_details['id'];
			$captured_segments[ $segment_id ] = $segment_details['name'];
		}

		$all_segments = $captured_segments;
		$offset       = '';
		if ( $total_segment_count > count( $all_segments ) ) {
			$offset = count( $all_segments );
		}
		if ( ! empty( $offset ) ) {
			$params['start'] = $offset;

			return $this->fetch_segments( $all_segments, $params );
		}

		return $all_segments;
	}

	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_keap'] = array(
			'name'            => 'Keap',
			'desc'            => __( 'Add or Remove tags, Add or Remove contact(s) to a campaign, Update contact custom fields, Assign / Remove Points to / from Contact and much more.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_Keap',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}

	public function setting_view() {
		?>
        <script type="text/html" id="tmpl-connector-<?php echo esc_html( $this->get_slug() ); ?>">
			<?php $this->get_settings_view(); ?>
        </script>
		<?php
		$this->get_settings_script();
	}

	public function get_settings_script() {
		?>
        <script type="text/javascript">
            (function ($) {

                function wfco_getUrlParameter(name) {
                    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                    var results = regex.exec(location.search);
                    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
                }

                $('body').on('click', '.wfco-keap-wrap .wfco_connect_to_api', function () {
                    var self = $(this);
                    var client_id = $('.wfco-keap-wrap input[name="client_id"]').val();
                    var client_secret = $('.wfco-keap-wrap input[name="client_secret"]').val();

                    if (!_.isUndefined(client_id) && !_.isUndefined(client_secret)) {
                        let wp_ajax = new bwf_ajax();
                        let add_query = {
                            "_wpnonce": '<?php esc_html_e( wp_create_nonce( 'bwfco_temp_save_keap_cred_nonce' ) ) ?>',
                            "client_id": client_id,
                            "client_secret": client_secret
                        };

                        self.prop('disabled', 'disabled');
                        self.text('<?php esc_html_e( 'Connecting...', 'autonami-automations-connectors' ) ?>');
                        wp_ajax.ajax('temp_save_keap_credentials', add_query);

                        wp_ajax.success = function (rsp) {
                            if (rsp.status === true) {

                                var url = new URL('<?php echo esc_url_raw( $this->oauth_endpoint ); ?>');
                                url.searchParams.append('client_id', client_id);
                                url.searchParams.append('response_type', 'code');
                                url.searchParams.append('redirect_uri', '<?php echo esc_url_raw( $this->redirect_uri ); ?>');
                                url.searchParams.append('scope', 'full');
                                window.location.href = url.toString();

                            } else {
                                $("#wfco-modal-connect").iziModal('close');
                                setTimeout(
                                    function () {
                                        swal({
                                            title: "Oops",
                                            text: rsp.message,
                                            type: "error",
                                        });

                                        setTimeout(function () {
                                            window.location.href = window.location.href;
                                        }, 3000);
                                    }, 1000);
                            }
                        };
                    }

                    return false;
                });

                function checkForCode() {
                    var code = wfco_getUrlParameter('code');
                    var state = '<?php echo esc_html( wp_create_nonce( 'wfco_keap_state' ) ); ?>';
                    var connector = wfco_getUrlParameter('connector');

                    if (_.isEmpty(code) || _.isEmpty(state) || _.isEmpty(connector) || 'keap' !== connector) {
                        return;
                    }
                    swal({
                        title: wfcoParams.texts.sync_wait,
                        text: wfcoParams.texts.save_progress,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        onOpen: () => {
                            swal.showLoading();
                        }
                    });
                    let wp_ajax = new bwf_ajax();
                    let add_query = {"state": state, "code": code, "redirect_uri": '<?php echo esc_url_raw( $this->redirect_uri ); ?>'};

                    wp_ajax.ajax('keap_connect_with_code', add_query);

                    wp_ajax.success = function (rsp) {
                        if (rsp.status === true) {
                            swal({
                                title: wfcoParams.texts.connect_success_title,
                                text: "",
                                type: "success",
                            });
                            setTimeout(
                                function () {
                                    window.location.href = rsp.redirect_url;
                                }, 3000);
                        } else {
                            $("#wfco-modal-connect").iziModal('close');
                            setTimeout(
                                function () {
                                    swal({
                                        title: "Oops",
                                        text: rsp.message,
                                        type: "error",
                                    });
                                }, 1000);
                        }
                    };
                    return false;
                }

                setTimeout(checkForCode, 1000);
            })(jQuery);
        </script>
		<?php
	}


}

WFCO_Load_Connectors::register( 'BWFCO_Keap' );
