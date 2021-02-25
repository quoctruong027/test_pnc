<?php

class BWFCO_Ontraport extends BWF_CO {
	/** API Endpoint is the home URL of the Ontraport Installation */
	public static $api_end_point = "https://api.ontraport.com/1";
	public static $headers = array();
	private static $ins = null;
	public $app_id = null;
	public $app_key = null;

	public function __construct() {

		/**
		 * Load important variables and constants
		 */
		$this->define_plugin_properties();
		$this->init_ontraport();

		$this->sync          = true;
		$this->connector_url = WFCO_ONTRAPORT_PLUGIN_URL;
		$this->dir           = __DIR__;
		$this->nice_name     = __( 'Ontraport', 'autonami-automations-connectors' );
		$this->redirect_uri  = add_query_arg( array(
			'tab'  => 'connector',
			'page' => 'autonami'
		), site_url() . '/wp-admin/admin.php' );

		$this->autonami_int_slug = 'BWFAN_Ontraport_Integration';

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
		add_action( 'wp_ajax_bwf_temp_save_ontraport_credentials', array( $this, 'bwfco_temp_save_ontraport_credentials' ) );

		/** Add tag to connector's global settings */
		add_action( 'wfco_ontraport_tag_created', array( $this, 'add_tag_to_settings' ), 10, 2 );
	}

	public function add_tag_to_settings( $tag_id, $tag_name ) {
		$settings = WFCO_Ontraport_Common::get_ontraport_settings();
		if ( ! isset( $settings['tags'] ) || ! is_array( $settings['tags'] ) ) {
			return;
		}

		$settings['tags'][ $tag_id ] = $tag_name;
		WFCO_Ontraport_Common::update_settings( $settings );
	}


	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}


	/**
	 * Defining constants
	 */
	public function define_plugin_properties() {
		define( 'WFCO_ONTRAPORT_VERSION', '1.0.1' );
		define( 'WFCO_ONTRAPORT_FULL_NAME', 'Autonami Marketing Automations Connectors: Ontraport Addon' );
		define( 'WFCO_ONTRAPORT_PLUGIN_FILE', __FILE__ );
		define( 'WFCO_ONTRAPORT_PLUGIN_DIR', __DIR__ );
		define( 'WFCO_ONTRAPORT_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_ONTRAPORT_PLUGIN_FILE ) ) );
		define( 'WFCO_ONTRAPORT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'WFCO_ONTRAPORT_MAIN', 'autonami-automations-connectors' );
		define( 'WFCO_ONTRAPORT_ENCODE', sha1( WFCO_ONTRAPORT_PLUGIN_BASENAME ) );
	}

	public function init_ontraport() {
		require WFCO_ONTRAPORT_PLUGIN_DIR . '/includes/class-wfco-ontraport-common.php';
		require WFCO_ONTRAPORT_PLUGIN_DIR . '/includes/class-wfco-ontraport-call.php';
	}

	public static function get_endpoint( $site_url = '' ) {
		return self::$api_end_point;
	}

	public static function get_headers() {
		return self::$headers;
	}

	public static function set_headers( $data = array() ) {

		$headers = array(
			"Api-Appid"    => isset( $data['app_id'] ) ? $data['app_id'] : '',
			"Api-Key"      => isset( $data['api_key'] ) ? $data['api_key'] : '',
			"Content-Type" => 'application/json'
		);

		self::$headers = $headers;
	}

	public function bwfco_temp_save_ontraport_credentials() {
		if ( ! isset( $_POST['_wpnonce'] ) || false === wp_verify_nonce( sanitize_text_field( $_POST['_wpnonce'] ), 'bwfco_temp_save_ontraport_cred_nonce' ) ) {
			wp_send_json( array( 'status' => false, 'message' => 'Security Token Expired / Invalid. Reloading the page...' ) );
		}

		if ( isset( $_POST['app_id'] ) && ! empty( $_POST['app_id'] ) && isset( $_POST['api_key'] ) && ! empty( $_POST['api_key'] ) ) {

			update_option( 'bwfco_temp_ontraport_app_id', sanitize_text_field( $_POST['app_id'] ) );
			update_option( 'bwfco_temp_ontraport_api_key', sanitize_text_field( $_POST['api_key'] ) );


			$params = array(
				'app_id'   => $_POST['app_id'],
				'api_key'  => $_POST['api_key'],
				'site_url' => site_url(),
			);

			$saved_data    = WFCO_Common::$connectors_saved_data;
			$saved_data_id = ( isset( $saved_data['bwfco_ontraport'] ) && is_array( $saved_data['bwfco_ontraport'] ) && isset( $saved_data['bwfco_ontraport']['id'] ) ) ? absint( $saved_data['bwfco_ontraport']['id'] ) : 0;

			if ( $saved_data_id > 0 ) {
				$params['id'] = $saved_data_id;
			}

			$active_connectors = WFCO_Load_Connectors::get_active_connectors();
			/** @var BWF_CO $connector_ins */
			$connector_ins = $active_connectors['bwfco_ontraport']; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification


			$response = $connector_ins->handle_settings_form( $params, $saved_data_id > 0 ? 'update' : 'save' ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification


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

			wp_send_json( array( 'status' => true ) );
		}

		wp_send_json( array( 'status' => false, 'message' => 'One of the settings is not provided' ) );


	}

	/**
	 * This function connects to the automation and fetch the data required for the actions on automations screen to work properly.
	 *
	 * @param $posted_data
	 *
	 * @return array|int
	 */
	public function get_api_data( $posted_data ) {
		$resp_array             = array();
		$resp_array['api_data'] = $posted_data;
		$resp_array['status']   = 'success';
		$params                 = array(
			'site_url'               => $posted_data['site_url'],
			'app_id'                 => isset( $posted_data['app_id'] ) ? $posted_data['app_id'] : '',
			'api_key'                => isset( $posted_data['api_key'] ) ? $posted_data['api_key'] : '',
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

		return $resp_array;
	}

	/**
	 * Fetch Ontraport Custom Contact Fields
	 *
	 * @param $captured_custom_fields
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_custom_fields( $captured_custom_fields, $params ) {
		$connectors        = WFCO_Load_Connectors::get_instance();
		$all_custom_fields = [];
		/** @var WFCO_Ontraport_Get_Contact_Fields $custom_fields_call */
		$custom_fields_call = $connectors->get_call( 'wfco_ontraport_get_contact_fields' );

		$custom_fields_call->set_data( $params );

		$custom_fields_result = $custom_fields_call->process();

		if ( isset( $custom_fields_result['response'] ) && 502 === absint( $custom_fields_result['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( $custom_fields_result['body'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $custom_fields_result['body']['errors'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( 'Error: ' . $custom_fields_result['body']['errors'][0]['message'], 'autonami-automations-connectors' ),
			);
		}

		if ( ! is_array( $custom_fields_result ) || 200 !== $custom_fields_result['response'] || ! isset( $custom_fields_result['body']['data'][0]['fields'] ) || ! is_array( $custom_fields_result['body']['data'] ) || 0 === count( $custom_fields_result['body']['data'][0]['fields'] ) ) {
			return $all_custom_fields;
		}

		$custom_fields     = $custom_fields_result['body']['data'][0]['fields'];
		$all_custom_fields = array();
		foreach ( $custom_fields as $field_key => $fields ) {
			$custom_field_id                                = $field_key;
			$all_custom_fields[ $custom_field_id ]['label'] = $fields['alias'];
			$all_custom_fields[ $custom_field_id ]['alias'] = $custom_field_id;
		}

		return $all_custom_fields;
	}

	/**
	 * Fetch Ontraport Tags
	 *
	 * @param $captured_tags
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_tags( $captured_tags, $params ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		$all_tags   = [];
		/** @var WFCO_Ontraport_Get_Tags $tags_call */
		$tags_call = $connectors->get_call( 'wfco_ontraport_get_tags' );
		$tags_call->set_data( $params );
		$tags_result = $tags_call->process();
		if ( isset( $tags_result['response'] ) && 502 === absint( $tags_result['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( $tags_result['body'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $tags_result['body']['errors'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( 'Error: ' . $tags_result['body']['errors'][0]['message'], 'autonami-automations-connectors' ),
			);
		}

		if ( ! is_array( $tags_result ) || 200 !== $tags_result['response'] || ! isset( $tags_result['body']['data'] ) || ! is_array( $tags_result['body']['data'] ) || 0 === count( $tags_result['body']['data'] ) ) {
			return $all_tags;
		}

		$tags = $tags_result['body']['data'];
		foreach ( $tags as $tag_details ) {
			$tag_id                   = $tag_details['tag_id'];
			$captured_tags[ $tag_id ] = $tag_details['tag_name'];
		}

		$all_tags = $captured_tags;

		return $all_tags;
	}

	/**
	 * Fetch Ontraport Campaigns
	 *
	 * @param $captured_campaigns
	 * @param $params
	 *
	 * @return array
	 */
	public function fetch_campaigns( $captured_campaigns, $params ) {
		$connectors    = WFCO_Load_Connectors::get_instance();
		$all_campaigns = [];
		/** @var WFCO_Ontraport_Get_Campaigns $tags_call */
		$campaigns_call = $connectors->get_call( 'wfco_ontraport_get_campaign' );

		$campaigns_call->set_data( $params );
		$campaigns_result = $campaigns_call->process();

		if ( isset( $campaigns_result['response'] ) && 502 === absint( $campaigns_result['response'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( $campaigns_result['body'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $campaigns_result['body']['errors'] ) ) {
			return array(
				'status'   => 'failed',
				'api_data' => array(),
				'message'  => __( 'Error: ' . $campaigns_result['body']['errors'][0]['message'], 'autonami-automations-connectors' ),
			);
		}

		if ( ! is_array( $campaigns_result ) || 200 !== $campaigns_result['response'] || ! isset( $campaigns_result['body']['data'] ) || ! is_array( $campaigns_result['body']['data'] ) || 0 === count( $campaigns_result['body']['data'] ) ) {
			return $all_campaigns;
		}
		$campaigns = $campaigns_result['body']['data'];

		foreach ( $campaigns as $campaign_details ) {
			$campaign_id                        = $campaign_details['id'];
			$captured_campaigns[ $campaign_id ] = $campaign_details['name'];
		}

		$all_campaigns = $captured_campaigns;

		return $all_campaigns;
	}

	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_ontraport'] = array(
			'name'            => 'Ontraport',
			'desc'            => __( 'Add or Remove tags, Add or Remove contact(s) to a campaign, Update contact custom fields, Assign / Remove Points to / from Contact and much more.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_Ontraport',
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

                $('body').on('click', '.wfco-ontraport-wrap .wfco_connect_to_api', function () {
                    var self = $(this);
                    var app_id = $('.wfco-ontraport-wrap input[name="app_id"]').val();
                    var api_key = $('.wfco-ontraport-wrap input[name="api_key"]').val();

                    if (!_.isUndefined(app_id) && !_.isUndefined(api_key)) {
                        let wp_ajax = new bwf_ajax();
                        let add_query = {
                            "_wpnonce": '<?php esc_html_e( wp_create_nonce( 'bwfco_temp_save_ontraport_cred_nonce' ) ) ?>',
                            "app_id": app_id,
                            "api_key": api_key
                        };

                        self.prop('disabled', 'disabled');
                        self.text('<?php esc_html_e( 'Connecting...', 'autonami-automations-connectors' ) ?>');
                        wp_ajax.ajax('temp_save_ontraport_credentials', add_query);

                        wp_ajax.success = function (rsp) {
                            jQuery('#wfco-modal-connect').iziModal('close');
                            jQuery('#modal-edit-connector').iziModal('close');
                            if (rsp.status === true) {
                                swal({
                                    title: wfcoParams.texts.connect_success_title,
                                    text: "",
                                    type: "success",
                                });
                                setTimeout(
                                    function () {
                                        window.location.href = '<?php echo $this->redirect_uri; ?>';
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

                                        setTimeout(function () {
                                            window.location.href = window.location.href;
                                        }, 3000);
                                    }, 1000);
                            }
                        };
                    }

                    return false;
                });
            })(jQuery);
        </script>
		<?php
	}


}

WFCO_Load_Connectors::register( 'BWFCO_Ontraport' );
