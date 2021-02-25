<?php
/**
 * Plugin Name: Autonami Marketing Automations Connectors
 * Plugin URI: https://buildwoofunnels.com
 * Description: Take your marketing automation game notches up by unlocking third-party integrations- like with Active Campaign, Drip, ConvertKit, to add a tag/add to a list/start automation etc. And even with Slack, Twilio & more. Many more apps are joining the party.
 * Version: 1.2.0
 * Author: WooFunnels
 * Author URI: https://buildwoofunnels.com
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: autonami-automations-connectors
 *
 * Requires at least: 4.9
 * Tested up to: 5.5.1
 * WC requires at least: 3.0.0
 * WC tested up to: 4.6
 * WooFunnels: true
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WFCO_Autonami_Connectors_Core {

	/**
	 * @var WFCO_Autonami_Connectors_Core
	 */
	public static $_instance = null;

	private function __construct() {
		$this->sync = true;

		/**
		 * Load important variables and constants
		 */
		$this->define_plugin_properties();

		/**
		 * Loads common file
		 */
		$this->load_commons();
	}

	/**
	 * Defining constants
	 */
	public function define_plugin_properties() {
		define( 'WFCO_AUTONAMI_CONNECTORS_VERSION', '1.2.0' );
		define( 'WFCO_AUTONAMI_CONNECTORS_SLUG', 'autonami-automations-connectors' );
		define( 'WFCO_AUTONAMI_CONNECTORS_FULL_NAME', 'Autonami Marketing Automations Connectors' );
		define( 'WFCO_AUTONAMI_CONNECTORS_PLUGIN_FILE', __FILE__ );
		define( 'WFCO_AUTONAMI_CONNECTORS_PLUGIN_DIR', __DIR__ );
		define( 'WFCO_AUTONAMI_CONNECTORS_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_AUTONAMI_CONNECTORS_PLUGIN_FILE ) ) );
		define( 'WFCO_AUTONAMI_CONNECTORS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'WFCO_AUTONAMI_CONNECTORS_MAIN', 'autonami-automations-connectors' );
		define( 'WFCO_AUTONAMI_CONNECTORS_ENCODE', sha1( WFCO_AUTONAMI_CONNECTORS_PLUGIN_BASENAME ) );

		add_action( 'bwfan_loaded', [ $this, 'add_support' ], 15 );
		add_action( 'bwfan_loaded', [ $this, 'load_includes' ] );
	}

	public function load_includes() {
		/** Connector Common */
		require_once WFCO_AUTONAMI_CONNECTORS_PLUGIN_DIR . '/includes/class-bwfan-connectors-common.php';
		BWFAN_Connectors_Common::init();
	}

	/**
	 * Load common hooks
	 */
	public function load_commons() {
		$this->load_hooks();
	}

	public function load_hooks() {
		add_action( 'wfco_load_connectors', [ $this, 'load_connector_classes' ] );
		add_action( 'bwfan_automations_loaded', [ $this, 'load_autonami_classes' ] );
		add_action( 'bwfan_merge_tags_loaded', [ $this, 'load_tag_classes' ], 12 );
		/** Initialize Localization */
		add_action( 'init', array( $this, 'localization' ) );
	}

	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Add License Support to basic connector
	 */
	public function add_support() {
		include_once( WFCO_AUTONAMI_CONNECTORS_PLUGIN_DIR . '/class-bwfan-support.php' );
	}

	/**
	 * Load Connector Classes
	 */
	public function load_connector_classes() {
		$resource_dir = WFCO_AUTONAMI_CONNECTORS_PLUGIN_DIR . '/connectors';
		foreach ( glob( $resource_dir . '/*' ) as $connector ) {
			if ( strpos( $connector, 'index.php' ) !== false ) {
				continue;
			}
			$_field_filename = $connector . '/connector.php';
			if ( file_exists( $_field_filename ) ) {
				require_once( $_field_filename );
			}
		}

		do_action( 'wfco_basic_autonami_connectors_all_connectors_loaded', $this );
	}

	/**
	 * Load Autonami Integration classes
	 */
	public function load_autonami_classes() {
		$resource_dir = WFCO_AUTONAMI_CONNECTORS_PLUGIN_DIR . '/connectors';

		foreach ( glob( $resource_dir . '/*' ) as $connector ) {
			if ( strpos( $connector, 'index.php' ) !== false ) {
				continue;
			}
			foreach ( glob( $connector . '/autonami/class-*.php' ) as $_field_filename ) {
				require_once( $_field_filename );
			}
		}

		do_action( 'wfco_basic_autonami_connectors_all_integrations_loaded', $this );
	}

	/**
	 * Load Active Campaign Merge Tag classes
	 */
	public function load_tag_classes() {
		$connector = array( 'activecampaign', 'drip', 'twilio' );
		foreach ( $connector as $conn ) {
			$resource_dir = WFCO_AUTONAMI_CONNECTORS_PLUGIN_DIR . '/connectors/' . $conn . '/autonami/merge_tags';
			foreach ( glob( $resource_dir . '/class-*.php' ) as $_field_filename ) {
				require_once( $_field_filename );
			}
		}

	}

	public function localization() {
		load_plugin_textdomain( 'autonami-automations-connectors', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

}

if ( ! function_exists( 'WFCO_Autonami_Connectors_Core' ) ) {

	/**
	 * Global Common function to load all the classes
	 * @return WFCO_Autonami_Connectors_Core
	 */
	function WFCO_Autonami_Connectors_Core() {  //@codingStandardsIgnoreLine
		return WFCO_Autonami_Connectors_Core::get_instance();
	}
}

WFCO_Autonami_Connectors_Core();
