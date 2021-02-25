<?php

if ( ! class_exists( 'BWFCO_Google_Sheets' ) ) {
	class BWFCO_Google_Sheets extends BWF_CO {

		private static $ins = null;
		public $is_setting = true;

		public function __construct() {
			$this->define_plugin_properties();
			$this->load_hooks();

			$this->dir               = __DIR__;
			$this->connector_url     = WFCO_GOOGLE_SHEETS_PLUGIN_URL;
			$this->autonami_int_slug = 'BWFAN_Google_Sheets_Integration';

			register_deactivation_hook( WFCO_GOOGLE_SHEETS_PLUGIN_FILE, array( $this, 'deactivation' ) );
			add_filter( 'plugin_action_links_' . WFCO_GOOGLE_SHEETS_PLUGIN_BASENAME, array( $this, 'plugin_actions' ) );
			add_filter( 'wfco_connectors_loaded', array( $this, 'load_google_sheets' ) );
		}

		public function load_hooks() {
			/** Load Google SpreadSheet client */
			require WFCO_GOOGLE_SHEETS_PLUGIN_DIR . '/vendor/autoload.php';
		}

		/**
		 * Defining constants
		 */
		public function define_plugin_properties() {
			define( 'WFCO_GOOGLE_SHEETS_VERSION', '1.0.0' );
			define( 'WFCO_GOOGLE_SHEETS_SLUG', 'autonami-automations-connectors' );
			define( 'WFCO_GOOGLE_SHEETS_FULL_NAME', 'Autonami Google Sheets Connector' );
			define( 'WFCO_GOOGLE_SHEETS_PLUGIN_FILE', __FILE__ );
			define( 'WFCO_GOOGLE_SHEETS_PLUGIN_DIR', __DIR__ );
			define( 'WFCO_GOOGLE_SHEETS_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_GOOGLE_SHEETS_PLUGIN_FILE ) ) );
			define( 'WFCO_GOOGLE_SHEETS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

			/** Auth Constants */
			define( 'WFCO_GS_CLIENT_ID', '650563178524-92liqp259gfqgbsdv8e278sail2885la.apps.googleusercontent.com' );
			define( 'WFCO_GS_CLIENT_SECRET', 'svBSCSI--j-kH0w39aaci27F' );
			define( 'WFCO_GS_PROJECT_ID', 'autonami' );
			define( 'WFCO_GS_REDIRECT_URI', 'urn:ietf:wg:oauth:2.0:oob' );
			define( 'WFCO_GS_AUTH_URI', 'https://accounts.google.com/o/oauth2/auth' );
			define( 'WFCO_GS_TOKEN_URI', 'https://oauth2.googleapis.com/token' );
			define( 'WFCO_GS_AUTH_PROVIDER_CERT_URL', 'https://www.googleapis.com/oauth2/v1/certs' );

			define( 'WFCO_GOOGLE_SHEETS_ENCODE', sha1( WFCO_GOOGLE_SHEETS_PLUGIN_BASENAME ) );
		}

		/**
		 * @return BWFCO_Google_Sheets|null
		 */
		public static function get_instance() {
			if ( null === self::$ins ) {
				self::$ins = new self();
			}

			return self::$ins;
		}

		public function load_google_sheets( $available_connectors ) {
			$available_connectors['autonami']['connectors']['bwfco_google_sheets'] = array(
				'name'            => 'Google Spread Sheets',
				'desc'            => 'Connect with Google Spreadsheets to insert new row or Update any row in a sheet.',
				'connector_class' => 'WFCO_Google_Sheets_Core',
				'image'           => $this->get_image(),
				'source'          => '',
				'file'            => '',
			);

			return $available_connectors;
		}

		/**
		 * Handles the settings form submission
		 */

		public function handle_settings_form( $posted_data, $type = 'save' ) {
			$status = 'failed';
			$resp   = array(
				'status'  => $status,
				'id'      => 0,
				'message' => '',
			);

			if ( ! isset( $posted_data['gs_token'] ) || empty( $posted_data['gs_token'] ) ) {
				$resp['message'] = __( 'Please enter token', 'autonami-automations-connectors' );

				return $resp;
			}

			$connector_data = $this->get_api_data( $posted_data );

			if ( 'failed' === $connector_data['status'] ) {
				$resp['message'] = $connector_data['message'];

				return $resp;
			}
			$resp['status'] = 'success';
			$resp['id']     = WFCO_Common::save_connector_data( $connector_data, $this->get_slug(), 1 );

			return $resp;
		}

		public function get_api_data( $posted_data ) {
			$resp_array = array(
				'status'   => 'success',
				'api_data' => [],
			);

			$resp_array['api_data']['gs_token'] = $posted_data['gs_token'];

			try {
				$client = new Google_Client();
				$client->setApplicationName( 'Google Sheets API PHP Quickstart' );
				$client->setScopes( array( Google_Service_Sheets::SPREADSHEETS ) );
				$client->setAuthConfig( WFCO_GOOGLE_SHEETS_PLUGIN_DIR . '/credentials.json' );
				$client->setAccessType( 'offline' );
				$client->setPrompt( 'select_account consent' );

				$authCode = trim( $posted_data['gs_token'] );

				// Exchange authorization code for an access token.
				$accessToken = $client->fetchAccessTokenWithAuthCode( $authCode );
				$client->setAccessToken( $accessToken );
			} catch ( InvalidArgumentException $error ) {
				$resp_array['status']  = 'failed';
				$resp_array['message'] = $error->getMessage();

				return $resp_array;
			}

			// Check to see if there was an error.
			if ( array_key_exists( 'error', $accessToken ) ) {
				$resp_array['status']  = 'failed';
				$resp_array['message'] = __( 'There was problem authenticating your Token. Confirm entered details.', 'autonami-automations-connectors' );

				return $resp_array;
			}

			// Save the token.
			$resp_array['api_data']['auth_token'] = wp_json_encode( $client->getAccessToken() );

			return $resp_array;
		}

		/**
		 * returns google client by setting auth and access token
		 *
		 * @return mixed
		 */
		public static function get_google_client() {
			$load_connector = WFCO_Load_Connectors::get_instance();
			$client         = $load_connector->get_call( 'wfco_gs_get_google_client' );
			if ( is_null( $client ) ) {
				return false;
			}

			return $client->process();
		}

		/**
		 * Get all worksheets of a particular spreadsheet
		 *
		 * @param $spreadsheet_id
		 *
		 * @return mixed
		 */
		public static function get_google_worksheets( $spreadsheet_id ) {
			$load_connector = WFCO_Load_Connectors::get_instance();
			$worksheet      = $load_connector->get_call( 'wfco_gs_get_worksheets' );
			if ( is_null( $worksheet ) ) {
				return false;
			}

			/**
			 * Set data for worksheet
			 */
			$worksheet->set_data( array(
				'spreadsheet_id' => $spreadsheet_id,
			) );

			return $worksheet->process();
		}

		/**
		 * Runs deactivation hook
		 */
		public function deactivation() {
			do_action( 'connector_disconnected', $this->get_slug() );
		}

		/**
		 * Hooked over 'plugin_action_links_{PLUGIN_BASENAME}' WordPress hook to add deactivate popup support
		 *
		 * @param array $links array of existing links
		 *
		 * @return array modified array
		 */
		public function plugin_actions( $links ) {
			$links['deactivate'] .= '<i class="woofunnels-connector-slug" data-slug="' . WFCO_GOOGLE_SHEETS_PLUGIN_BASENAME . '" data-connector-slug="' . $this->get_slug() . '"></i>';

			return $links;
		}
	}

	WFCO_Load_Connectors::register( 'BWFCO_Google_Sheets' );
}
