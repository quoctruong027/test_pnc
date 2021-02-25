<?php

/**
 * License handler for Easy Digital Downloads
 *
 * This class should simplify the process of adding license information
 * to new EDD extensions.
 *
 * @version 1.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WCCT_EDD_License' ) ) :

	/**
	 * EDD_License Class
	 */
	class WCCT_EDD_License {

		protected static $instance = null;
		private $file;
		private $license;
		private $item_name;
		private $item_shortname;
		private $version;
		private $author;
		private $api_url = 'https://xlplugins.com/';

		/**
		 * Class constructor
		 *
		 * @param string $_file
		 * @param string $_item_name
		 * @param string $_version
		 * @param string $_author
		 * @param string $_optname
		 * @param string $_api_url
		 *
		 * @global  array $edd_options
		 *
		 */
		function __construct( $_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null ) {

			$this->file           = $_file;
			$this->item_name      = $_item_name;
			$this->item_shortname = '' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
			$this->version        = $_version;

			$this->license = get_option( 'xl_licenses_' . $this->item_shortname ) == false ? '' : get_option( 'xl_licenses_' . $this->item_shortname );
			$this->author  = $_author;
			$this->api_url = is_null( $_api_url ) ? $this->api_url : $_api_url;

			// Setup hooks
			$this->includes();

			$this->auto_updater();
		}

		/**
		 * Include the updater class
		 *
		 * @access  private
		 * @return  void
		 */
		private function includes() {
			if ( ! class_exists( 'WCCT_EDD_SL_Plugin_Updater' ) ) {
				require_once 'WCCT_EDD_SL_Plugin_Updater.php';
			}
		}


		/**
		 * Auto updater
		 *
		 * @access  private
		 * @return  void
		 * @global  array $edd_options
		 */
		private function auto_updater() {

			// Setup the updater
			$edd_updater = new WCCT_EDD_SL_Plugin_Updater( $this->api_url, $this->file, array(
				'version'   => $this->version,
				'license'   => $this->license,
				'item_name' => $this->item_name,
				'author'    => $this->author,
			) );
		}


		/**
		 * Activate the license key
		 *
		 * @access  public
		 * @return  void
		 */
		public function activate_license( $license ) {

			if ( ! $license ) {
				return;
			}

			if ( 'valid' == get_option( $this->item_shortname . '_license_active' ) ) {
				return;
			}

			// Data to send to the API
			$api_params = array(
				'url'        => home_url(),
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->item_name ),
				'purchase'   => WCCT_PURCHASE,
			);

			// Call the API
			$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, $this->api_url ) ), array(
				'timeout'   => 15,
				'body'      => $api_params,
				'sslverify' => false,
			) );
			// Make sure there are no errors
			if ( is_wp_error( $response ) ) {
				return;
			}

			if ( $response['response']['code'] !== 200 ) {
				return;
			}
			// Decode license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			update_option( $this->item_shortname . 'license_data', $license_data, true );
			update_option( $this->item_shortname . '_license_active', $license_data->license, true );

			if ( class_exists( 'XL_admin_notifications' ) && $license_data->license == 'valid' ) {
				do_action( 'xl_license_activated', WCCT_PLUGIN_BASENAME );
				XL_admin_notifications::add_notification( array(
					'plugin_license_notif' . $this->item_shortname => array(
						'type'           => 'success',
						'is_dismissable' => true,
						'content'        => sprintf( __( '<p> Plugin <strong>%s</strong> successfully activated. </p>', 'my-text-domain' ), $this->item_name ),
					),
				) );
			}
		}

		/**
		 * Deactivate the license key
		 *
		 * @access  public
		 * @return  void
		 */
		public function deactivate_license() {

			// Data to send to the API
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'purchase'   => WCCT_PURCHASE,
			);

			// Call the API
			$response = wp_remote_get( esc_url_raw( add_query_arg( $api_params, $this->api_url ) ), array(
				'timeout'   => 15,
				'sslverify' => false,
			) );

			// Make sure there are no errors
			if ( is_wp_error( $response ) ) {
				return;
			}

			// Decode the license data

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( $license_data->license == 'deactivated' ) {
				delete_option( $this->item_shortname . '_license_active' );
				delete_option( $this->item_shortname . 'license_data' );
				delete_option( 'xl_licenses_' . $this->item_shortname );
			}

			return ( $license_data->license == 'deactivated' ) ? true : false;
		}

		/**
		 * Check if license key is valid once per week
		 *
		 * @access  public
		 * @return  void
		 * @since   2.5
		 */
		public function weekly_license_check() {

			if ( ! empty( $_POST['edd_settings'] ) ) {
				return; // Don't fire when saving settings
			}

			$is_transient = get_transient( 'xl_last_license_check_for_' . $this->item_shortname );

			if ( ! empty( $is_transient ) ) {
				return;
			}

			if ( empty( $this->license ) ) {
				return;
			}

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'check_license',
				'license'    => $this->license,
				'item_name'  => urlencode( $this->item_name ),
				'url'        => home_url(),
				'purchase'   => WCCT_PURCHASE,
			);

			// Call the API
			$response = wp_remote_post( $this->api_url, array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			update_option( $this->item_shortname . 'license_data', $license_data, true );
			update_option( $this->item_shortname . '_license_active', $license_data->license, true );

			set_transient( 'xl_last_license_check_for_' . $this->item_shortname, 'yes', 60 * 60 );
		}

	}


endif; // end class_exists check


