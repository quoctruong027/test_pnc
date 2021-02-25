<?php

class BWFCO_Mautic extends BWF_CO {
	/** API Endpoint is the home URL of the Mautic Installation */
	public static $api_end_point = null;
	public static $account_id = null;
	public static $headers = null;
	private static $ins = null;

	/** only require for oauth check  */
	public $oauth_url = null;
	public $redirect_uri = null; // current application's redirect url

	public function __construct() {
		/** Setup includes from add-on plugin */
		$this->define_plugin_properties();
		$this->init_mautic();

		/** Connector.php initialization */
		$this->keys_to_track = [
			'site_url',
			'client_id',
			'client_secret',
			'access_token',
			'refresh_token',
			'expires_in',
			'contact_fields',
			'tags',
			'campaigns',
			'segments'
		];
		$this->form_req_keys = [
			'site_url',
			'client_id',
			'client_secret',
			'access_token',
			'refresh_token'
		];

		$this->sync           = true;
		$this->connector_url  = WFCO_MAUTIC_PLUGIN_URL;
		$this->dir            = __DIR__;
		$this->nice_name      = __( 'Mautic', 'autonami-automations-connectors' );
		$this->oauth_endpoint = '/oauth/v2/authorize';
		$this->redirect_uri   = add_query_arg( array(
			'tab'  => 'connector',
			'page' => 'autonami'
		), site_url() . '/wp-admin/admin.php' );

		$this->autonami_int_slug = 'BWFAN_Mautic_Integration';

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
		add_action( 'wp_ajax_bwf_mautic_connect_with_code', array( $this, 'mautic_connect_with_code' ) );
		add_action( 'wp_ajax_bwf_temp_save_mautic_credentials', array( $this, 'bwfco_temp_save_mautic_credentials' ) );
	}

	/**
	 * Defining constants
	 */
	public function define_plugin_properties() {
		define( 'WFCO_MAUTIC_VERSION', '1.0.0' );
		define( 'WFCO_MAUTIC_FULL_NAME', 'Autonami Marketing Automations Connectors: Mautic Addon' );
		define( 'WFCO_MAUTIC_PLUGIN_FILE', __FILE__ );
		define( 'WFCO_MAUTIC_PLUGIN_DIR', __DIR__ );
		define( 'WFCO_MAUTIC_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_MAUTIC_PLUGIN_FILE ) ) );
		define( 'WFCO_MAUTIC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'WFCO_MAUTIC_MAIN', 'autonami-automations-connectors' );
		define( 'WFCO_MAUTIC_ENCODE', sha1( WFCO_MAUTIC_PLUGIN_BASENAME ) );
	}

	public function init_mautic() {
		require_once( WFCO_MAUTIC_PLUGIN_DIR . '/includes/class-wfco-mautic-common.php' );
		require_once( WFCO_MAUTIC_PLUGIN_DIR . '/includes/class-wfco-mautic-countries.php' );
		require_once( WFCO_MAUTIC_PLUGIN_DIR . '/includes/class-wfco-mautic-call.php' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public static function get_endpoint( $site_url = '' ) {
		if ( empty( $site_url ) ) {
			return '';
		}

		return $site_url;
	}

	public static function get_headers() {
		return self::$headers;
	}

	public static function set_headers( $access_token = '' ) {
		$headers = array(
			'Content-Type' => 'application/x-www-form-urlencoded',
		);

		if ( ! empty( $access_token ) ) {
			$headers['Authorization'] = 'Basic ' . base64_encode( $access_token );
		}
		self::$headers = $headers;
	}

	public function bwfco_temp_save_mautic_credentials() {
		if ( ! isset( $_POST['_wpnonce'] ) || false === wp_verify_nonce( sanitize_text_field( $_POST['_wpnonce'] ), 'bwfco_temp_save_mautic_cred_nonce' ) ) {
			wp_send_json( array( 'status' => false, 'message' => __( 'Security Token Expired / Invalid. Reloading the page...', 'autonami-automations-connectors' ) ) );
		}

		if ( isset( $_POST['site_url'] ) && ! empty( $_POST['site_url'] ) && isset( $_POST['client_id'] ) && ! empty( $_POST['client_id'] ) && isset( $_POST['client_secret'] ) && ! empty( $_POST['client_secret'] ) ) {
			update_option( 'bwfco_temp_mautic_site_url', esc_url_raw( $_POST['site_url'] ) );
			update_option( 'bwfco_temp_mautic_client_id', sanitize_text_field( $_POST['client_id'] ) );
			update_option( 'bwfco_temp_mautic_client_secret', sanitize_text_field( $_POST['client_secret'] ) );

			wp_send_json( array( 'status' => true ) );
		}

		wp_send_json( array( 'status' => false, 'message' => __( 'One of the settings is not provided', 'autonami-automations-connectors' ) ) );
	}

	public function mautic_connect_with_code() {
		$resp = array(
			'status'       => false,
			'message'      => __( 'Something is wrong, no data exists.', 'autonami-automations-connectors' ),
			'data_changed' => false,
		);

		if ( ! isset( $_POST['state'] ) || false === wp_verify_nonce( sanitize_text_field( $_POST['state'] ), 'wfco_mautic_state' ) ) {
			wp_send_json( array( 'status' => false, 'message' => 'Mautic state is invalid' ) );
		}

		if ( ! isset( $_POST['code'] ) ) {
			wp_send_json( array( 'status' => false, 'message' => 'Mautic code is not provided' ) );
		}

		$site_url      = get_option( 'bwfco_temp_mautic_site_url', true );
		$client_id     = get_option( 'bwfco_temp_mautic_client_id', true );
		$client_secret = get_option( 'bwfco_temp_mautic_client_secret', true );

		if ( empty( $site_url ) || empty( $client_id ) || empty( $client_secret ) ) {
			wp_send_json( array( 'status' => false, 'message' => 'Mautic Details not found' ) );
		}

		$connector = WFCO_Load_Connectors::get_instance();
		$call      = $connector->get_call( 'wfco_mautic_get_access_token' );
		$call->set_data( array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'site_url'      => $site_url,
			'code'          => sanitize_text_field( $_POST['code'] ),
			'redirect_uri'  => $this->redirect_uri
		) );
		$result = $call->process();

		if ( is_array( $result ) && isset( $result['body'] ) && is_array( $result['body'] ) && isset( $result['body']['errors'] ) && ! empty( $result['body']['errors'] ) ) {
			wp_send_json( array( 'status' => false, 'message' => $result['body']['errors'][0]['message'] ) );
		}

		if ( is_array( $result ) && $result['response'] === 200 ) {

			$params = array(
				'access_token'  => $result['body']['access_token'],
				'expires_in'    => time() + absint( $result['body']['expires_in'] ),
				'refresh_token' => $result['body']['refresh_token'],
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'site_url'      => $site_url,
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
			$resp['redirect_url'] = add_query_arg( array(
				'page' => 'autonami',
				'tab'  => 'connector',
			), admin_url( 'admin.php' ) );

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

		$params = array(
			'site_url'               => $posted_data['site_url'],
			'access_token'           => $posted_data['access_token'],
			'connector_initialising' => true
		);

		/** Fetch Contact Fields */
		$custom_fields_result = $this->fetch_custom_fields( [], $params );
		if ( is_array( $custom_fields_result ) && count( $custom_fields_result ) > 0 ) {
			$resp_array['api_data']['custom_fields'] = $custom_fields_result;
		}

		/** Fetch Tags */
		$tags_result = $this->fetch_tags( [], $params );
		if ( is_array( $tags_result ) && count( $tags_result ) > 0 ) {
			$resp_array['api_data']['tags'] = $tags_result;
		}

		/** Fetch Campaigns */
		$campaigns_result = $this->fetch_campaigns( [], $params );
		if ( is_array( $campaigns_result ) && count( $campaigns_result ) > 0 ) {
			$resp_array['api_data']['campaigns'] = $campaigns_result;
		}

		/** Fetch Segments */
		$segments_result = $this->fetch_segments( [], $params );
		if ( is_array( $segments_result ) && count( $segments_result ) > 0 ) {
			$resp_array['api_data']['segments'] = $segments_result;
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
			'refresh_token' => $posted_data['refresh_token'],
			'site_url'      => $posted_data['site_url'],
			'client_id'     => $posted_data['client_id'],
			'client_secret' => $posted_data['client_secret'],
			'redirect_uri'  => add_query_arg( array(
				'tab'  => 'connector',
				'page' => 'autonami'
			), site_url() . '/wp-admin/admin.php' )
		);

		$connector = WFCO_Load_Connectors::get_instance();
		$call      = $connector->get_call( 'wfco_mautic_get_access_token' );
		$call->set_data( $params );
		$result = $call->process();

		/** Handle error, if unable to refresh tokens */
		if ( 200 !== $result['response'] ) {

			if ( 502 === absint( $result['response'] ) && is_array( $result['body'] ) ) {
				wp_send_json( array(
					'status'  => 'failed',
					'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
				) );
			}

			$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $result['response'];
			$result_message  = ( is_array( $result['body'] ) && isset( $result['body']['errors'] ) ) ? $result['body']['errors'][0]['message'] : false;
			$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

			wp_send_json( array(
				'status'  => 'failed',
				'message' => false !== $result_message ? $result_message : $unknown_message . $response_code,
			) );
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
	 * Fetch Mautic Custom Contact Fields
	 *
	 * @param $captured_custom_fields
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_custom_fields( $captured_custom_fields, $params ) {
		$connectors        = WFCO_Load_Connectors::get_instance();
		$all_custom_fields = [];
		/** @var WFCO_Mautic_Get_Contact_Fields $custom_fields_call */
		$custom_fields_call = $connectors->get_call( 'wfco_mautic_get_contact_fields' );
		$custom_fields_call->set_data( $params );
		$custom_fields_result = $custom_fields_call->process();

		if ( isset( $custom_fields_result['response'] ) && 502 === absint( $custom_fields_result['response'] ) ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => $custom_fields_result['body'][0],
			) );
		}

		if ( isset( $custom_fields_result['body']['errors'] ) ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => __( 'Error: ', 'autonami-automations-connectors' ) . $custom_fields_result['body']['errors'][0]['message'],
			) );
		}

		if ( ! is_array( $custom_fields_result ) || 200 !== $custom_fields_result['response'] || ! isset( $custom_fields_result['body']['fields'] ) || ! is_array( $custom_fields_result['body']['fields'] ) || 0 === count( $custom_fields_result['body']['fields'] ) ) {
			return $all_custom_fields;
		}

		$total_cf_count = intval( $custom_fields_result['body']['total'] );
		$custom_fields  = $custom_fields_result['body']['fields'];
		foreach ( $custom_fields as $field_details ) {
			$custom_field_id                                     = $field_details['id'];
			$captured_custom_fields[ $custom_field_id ]['label'] = $field_details['label'];
			$captured_custom_fields[ $custom_field_id ]['alias'] = $field_details['alias'];
		}

		$all_custom_fields = $captured_custom_fields;
		$offset            = '';
		if ( $total_cf_count > count( $all_custom_fields ) ) {
			$offset = count( $all_custom_fields );
		}
		if ( ! empty( $offset ) ) {
			$params['start'] = $offset;

			return $this->fetch_custom_fields( $all_custom_fields, $params );
		}

		return $all_custom_fields;
	}

	/**
	 * Fetch Mautic Tags
	 *
	 * @param $captured_tags
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_tags( $captured_tags, $params ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		$all_tags   = [];
		/** @var WFCO_Mautic_Get_Tags $tags_call */
		$tags_call = $connectors->get_call( 'wfco_mautic_get_tags' );
		$tags_call->set_data( $params );
		$tags_result = $tags_call->process();

		if ( isset( $tags_result['response'] ) && 502 === absint( $tags_result['response'] ) ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => $tags_result['body'][0],
			) );
		}

		if ( isset( $tags_result['body']['errors'] ) ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => __( 'Error: ', 'autonami-automations-connectors' ) . $tags_result['body']['errors'][0]['message'],
			) );
		}

		if ( ! is_array( $tags_result ) || 200 !== $tags_result['response'] || ! isset( $tags_result['body']['tags'] ) || ! is_array( $tags_result['body']['tags'] ) || 0 === count( $tags_result['body']['tags'] ) ) {
			return $all_tags;
		}

		$total_tag_count = intval( $tags_result['body']['total'] );
		$tags            = $tags_result['body']['tags'];
		foreach ( $tags as $tag_details ) {
			$tag_id                   = $tag_details['id'];
			$captured_tags[ $tag_id ] = $tag_details['tag'];
		}

		$all_tags = $captured_tags;
		$offset   = '';
		if ( $total_tag_count > count( $all_tags ) ) {
			$offset = count( $all_tags );
		}
		if ( ! empty( $offset ) ) {
			$params['start'] = $offset;

			return $this->fetch_tags( $all_tags, $params );
		}

		return $all_tags;
	}

	/**
	 * Fetch Mautic Campaigns
	 *
	 * @param $captured_campaigns
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_campaigns( $captured_campaigns, $params ) {
		$connectors    = WFCO_Load_Connectors::get_instance();
		$all_campaigns = [];
		/** @var WFCO_Mautic_Get_Campaigns $tags_call */
		$campaigns_call = $connectors->get_call( 'wfco_mautic_get_campaigns' );
		$campaigns_call->set_data( $params );
		$campaigns_result = $campaigns_call->process();

		if ( isset( $campaigns_result['response'] ) && 502 === absint( $campaigns_result['response'] ) ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => $campaigns_result['body'][0],
			) );
		}

		if ( isset( $campaigns_result['body']['errors'] ) ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => __( 'Error: ', 'autonami-automations-connectors' ) . $campaigns_result['body']['errors'][0]['message'],
			) );
		}

		if ( ! is_array( $campaigns_result ) || 200 !== $campaigns_result['response'] || ! isset( $campaigns_result['body']['campaigns'] ) || ! is_array( $campaigns_result['body']['campaigns'] ) || 0 === count( $campaigns_result['body']['campaigns'] ) ) {
			return $all_campaigns;
		}

		$total_campaign_count = intval( $campaigns_result['body']['total'] );
		$campaigns            = $campaigns_result['body']['campaigns'];
		foreach ( $campaigns as $campaign_details ) {
			$campaign_id                        = $campaign_details['id'];
			$captured_campaigns[ $campaign_id ] = $campaign_details['name'];
		}

		$all_campaigns = $captured_campaigns;
		$offset        = '';
		if ( $total_campaign_count > count( $all_campaigns ) ) {
			$offset = count( $all_campaigns );
		}
		if ( ! empty( $offset ) ) {
			$params['start'] = $offset;

			return $this->fetch_campaigns( $all_campaigns, $params );
		}

		return $all_campaigns;
	}

	/**
	 * Fetch Mautic Segments
	 *
	 * @param $captured_segments
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_segments( $captured_segments, $params ) {
		$connectors   = WFCO_Load_Connectors::get_instance();
		$all_segments = [];
		/** @var WFCO_Mautic_Get_Segments $tags_call */
		$segments_call = $connectors->get_call( 'wfco_mautic_get_segments' );
		$segments_call->set_data( $params );
		$segments_result = $segments_call->process();

		if ( isset( $segments_result['response'] ) && 502 === absint( $segments_result['response'] ) ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => $segments_result['body'][0],
			) );
		}

		if ( isset( $segments_result['body']['errors'] ) ) {
			wp_send_json( array(
				'status'  => 'failed',
				'message' => __( 'Error: ', 'autonami-automations-connectors' ) . $segments_result['body']['errors'][0]['message'],
			) );
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
		$available_connectors['autonami']['connectors']['bwfco_mautic'] = array(
			'name'            => 'Mautic',
			'desc'            => __( 'Add or Remove tags, Add or Remove contact(s) to a campaign, Update contact custom fields, Assign / Remove Points to / from Contact and much more.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_Mautic',
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

                $('body').on('click', '.wfco-mautic-wrap .wfco_connect_to_api', function () {
                    var self = $(this);
                    var url = $('.wfco-mautic-wrap input[name="site_url"]').val().replace(/\/$/, "");
                    url = url.indexOf('http') !== -1 ? url : 'http://' + url;
                    var client_id = $('.wfco-mautic-wrap input[name="client_id"]').val();
                    var client_secret = $('.wfco-mautic-wrap input[name="client_secret"]').val();

                    if (!_.isUndefined(url) && !_.isUndefined(client_id) && !_.isUndefined(client_secret)) {
                        let wp_ajax = new bwf_ajax();
                        let add_query = {
                            "_wpnonce": '<?php esc_html_e( wp_create_nonce( 'bwfco_temp_save_mautic_cred_nonce' ) ) ?>',
                            "site_url": url,
                            "client_id": client_id,
                            "client_secret": client_secret
                        };

                        self.prop('disabled', 'disabled');
                        self.text('<?php esc_html_e( 'Connecting...', 'autonami-automations-connectors' ) ?>');
                        wp_ajax.ajax('temp_save_mautic_credentials', add_query);

                        wp_ajax.success = function (rsp) {
                            if (rsp.status === true) {
                                url = new URL(url + '<?php echo esc_url_raw( $this->oauth_endpoint ); ?>');
                                url.searchParams.append('client_id', client_id);
                                url.searchParams.append('grant_type', 'authorization_code');
                                url.searchParams.append('redirect_uri', '<?php echo esc_url_raw( $this->redirect_uri ); ?>');
                                url.searchParams.append('response_type', 'code');
                                url.searchParams.append('state', '<?php esc_html_e( wp_create_nonce( 'wfco_mautic_state' ) ); ?>');

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
                    var state = wfco_getUrlParameter('state');

                    if (_.isEmpty(code) || _.isEmpty(state)) {
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

                    wp_ajax.ajax('mautic_connect_with_code', add_query);

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

WFCO_Load_Connectors::register( 'BWFCO_Mautic' );
