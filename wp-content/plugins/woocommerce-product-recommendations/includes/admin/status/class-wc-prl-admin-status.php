<?php
/**
 * WC_PRL_Admin_Status class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Status Report Class.
 *
 * @class    WC_PRL_Admin_Status
 * @version  1.4.0
 */
class WC_PRL_Admin_Status {

	/**
	 * Setup Admin class.
	 */
	public static function init() {

		// Add debug data in the system status report.
		add_action( 'woocommerce_system_status_report', array( __CLASS__, 'render_system_status_items' ) );
	}

	/**
	 * Add PRL debug data in the system status.
	 */
	public static function render_system_status_items() {

		$debug_data = array(
			'db_version'             => get_option( 'wc_prl_db_version', null ),
			'loopback_test_result'   => WC_PRL_Notices::get_notice_option( 'loopback', 'last_result', '' ),
			'page_cache_test_result' => WC_PRL_Notices::get_notice_option( 'page_cache', 'last_result', '' ),
			'queue_test_result'      => WC_PRL_Notices::get_notice_option( 'page_cache', 'last_scheduled', 0 ) && ( absint( WC_PRL_Notices::get_notice_option( 'page_cache', 'last_scheduled', 0 ) - WC_PRL_Notices::get_notice_option( 'page_cache', 'last_tested', 0 ) ) > WEEK_IN_SECONDS ) && ( absint( gmdate( 'U' ) - WC_PRL_Notices::get_notice_option( 'page_cache', 'last_scheduled', 0 ) ) > HOUR_IN_SECONDS ) ? 'fail' : 'pass'
		);

		include( WC_PRL_ABSPATH . 'includes/admin/status/views/html-admin-page-status-report.php' );
	}
}

WC_PRL_Admin_Status::init();
