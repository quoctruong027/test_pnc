<?php
/**
 * WC_PRL_Notices class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notice handling.
 *
 * @class    WC_PRL_Notices
 * @version  1.4.4
 */
class WC_PRL_Notices {

	/**
	 * Notice options.
	 * @var array
	 */
	public static $notice_options = array();

	/**
	 * Constructor.
	 */
	public static function init() {

		self::$notice_options = get_option( 'wc_prl_notice_options', array() );

		// Save notice options.
		add_action( 'shutdown', array( __CLASS__, 'save_notice_options' ), 100 );

		// Page cache testing is only available through the WC queing system.
		if ( function_exists( 'WC' ) && method_exists( WC(), 'queue' ) ) {

			// Schedules the 'page_cache' notice test.
			add_action( 'admin_head', array( __CLASS__, 'schedule_tests' ) );

			// Handler for html cache tests.
			add_action( 'woocommerce_prl_page_cache_test', array( __CLASS__, 'run_page_cache_test' ) );

			// Add html comment with timestamp for page cache tests.
			add_action( 'wp_footer', array( __CLASS__, 'add_page_cache_test_data' ), 0 );
		}
	}

	/**
	 * Get a setting for a notice type.
	 *
	 * @param  string  $notice_name
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return array
	 */
	public static function get_notice_option( $notice_name, $key, $default = null ) {
		return isset( self::$notice_options[ $notice_name ] ) && is_array( self::$notice_options[ $notice_name ] ) && isset( self::$notice_options[ $notice_name ][ $key ] ) ? self::$notice_options[ $notice_name ][ $key ] : $default;
	}

	/**
	 * Set a setting for a notice type.
	 *
	 * @param  string  $notice_name
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	public static function set_notice_option( $notice_name, $key, $value ) {

		if ( ! isset( self::$notice_options ) ) {
			self::$notice_options = array();
		}

		if ( ! isset( self::$notice_options[ $notice_name ] ) ) {
			self::$notice_options[ $notice_name ] = array();
		}

		self::$notice_options[ $notice_name ][ $key ] = $value;
	}

	/**
	 * Save notice options to the DB.
	 */
	public static function save_notice_options() {
		update_option( 'wc_prl_notice_options', self::$notice_options );
	}

	/**
	 * Schedules recurring tests.
	 */
	public static function schedule_tests() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		self::schedule_page_cache_test();
	}

	/**
	 * Schedules the 'page_cache' notice test.
	 */
	public static function schedule_page_cache_test( $force = false ) {

		if ( ! method_exists( WC(), 'queue' ) ) {
			return new WP_Error( 'error', __( 'WooCommerce does not support task queueing.', 'woocommerce-product-recommendations' ) );
		}

		$schedule_test = 'pass' === self::get_notice_option( 'loopback', 'last_result', '' );

		if ( ! $schedule_test ) {
			return new WP_Error( 'error', __( 'Your system may not be able to perform loopback requests at this time.', 'woocommerce-product-recommendations' ) );
		}

		$now = gmdate( 'U' );

		if ( ! $force ) {

			$last_scheduled = self::get_notice_option( 'page_cache', 'last_scheduled', 0 );
			$schedule_test = $now - $last_scheduled > DAY_IN_SECONDS;

			if ( ! $schedule_test ) {
				return false;
			}
		}

		// Run page cache test only once per day, and only if the loopback test was successful.
		if ( $schedule_test ) {

			self::set_notice_option( 'page_cache', 'last_scheduled', $now );

			if ( $force ) {
				WC_PRL_Notices::set_notice_option( 'page_cache', 'last_result', '' );
			}

			WC()->queue()->add( 'woocommerce_prl_page_cache_test', array() );
		}

		return true;
	}

	/**
	 * Runs the 'page_cache' notice test.
	 *
	 * @return void
	 */
	public static function run_page_cache_test() {

		self::set_notice_option( 'page_cache', 'last_tested', gmdate( 'U' ) );
		self::set_notice_option( 'page_cache', 'last_result', self::generate_page_cache_test_result() );

		// Call this manually just in case we never get to 'shutdown'.
		self::save_notice_options();
	}

	/**
	 * Generates a new 'page_cache' test result.
	 *
	 * @return void
	 */
	private static function generate_page_cache_test_result() {

		$page_cache_test_url = apply_filters( 'wc_prl_page_cache_test_url', home_url() );

		if ( ! $time_ref = self::get_page_generated_time( $page_cache_test_url ) ) {
			return 'test-failed';
		}

		sleep( 2 );

		if ( ! $time_test = self::get_page_generated_time( $page_cache_test_url ) ) {
			return 'test-failed';
		}

		// If they are the same, we know for sure they were served from a cache.
		if ( $time_ref === $time_test ) {

			return 'cached';

		// If not, there's a tiny chance we may have done our check right at the end of the expiration interval.
		// So make a another check to be sure.
		} else {

			$time_ref = $time_test;

			sleep( 2 );

			if ( ! $time_test = self::get_page_generated_time( $page_cache_test_url ) ) {
				return 'test-failed';
			}

			if ( $time_ref === $time_test ) {
				return 'cached';
			}
		}

		return 'not-cached';
	}

	/**
	 * Get the result of the page cache test.
	 *
	 * @return string
	 */
	public static function get_page_cache_test_result() {
		return self::get_notice_option( 'page_cache', 'last_result' );
	}

	/**
	 * Searches for our 'prl_page_cache_test' meta tag to figure out when a page was generated.
	 *
	 * @param  string  $url
	 * @return mixed
	 */
	private static function get_page_generated_time( $url ) {

		$response = wp_remote_get( $url, array( 'sslverify' => false ) );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		preg_match( '/var prl_page_cache_test=(\d+)\;/', $response[ 'body' ], $matches );

		if ( ! isset( $matches[ 1 ] ) ) {
			return '';
		}

		return intval( $matches[ 1 ] );
	}

	/**
	 * Creates an html comment that contains a timestamp to help us identify whether a page is served from a cache.
	 *
	 * @return void
	 */
	public static function add_page_cache_test_data() {
		echo '<script>var prl_page_cache_test=' . gmdate( 'U' ) . ';</script>';
	}

	/**
	 * Used to determine if a feature plugin is installed.
	 *
	 * @since  1.4.0
	 *
	 * @param  string  $name
	 * @return boolean|null
	 */
	public static function is_feature_plugin_installed( $name ) {

		if ( ! isset( self::$plugin_data[ $name ] ) ) {
			return null;
		}

		if ( class_exists( self::$plugin_data[ $name ][ 'install_check' ] ) ) {
			return true;
		}

		include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		return 0 === validate_plugin( self::$plugin_data[ $name ][ 'install_path' ] );
	}
}

WC_PRL_Notices::init();
