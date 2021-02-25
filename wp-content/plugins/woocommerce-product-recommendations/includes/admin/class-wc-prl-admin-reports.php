<?php
/**
 * WC_PRL_Admin_Reports class
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
 * Admin Reports Class.
 *
 * @class    WC_PRL_Admin_Reports
 * @version  1.0.0
 */
class WC_PRL_Admin_Reports {

	/**
	 * Reports.
	 *
	 * @var array
	 */
	public static $reports = array();

	/**
	 * Setup Admin class.
	 */
	public static function init() {
		// Add "Recommendations" report tab.
		add_filter( 'woocommerce_admin_reports', array( __CLASS__, 'add_recommendations_reports' ) );
		add_filter( 'woocommerce_reports_screen_ids', array( __CLASS__, 'add_recommendations_reports_screens' ) );
	}

	/**
	 * Adds the "Recommendations" reports screen IDs.
	 *
	 * @param  array  $screens
	 * @return array
	 */
	public static function add_recommendations_reports_screens( $screens ) {

		if ( ! wc_prl_tracking_enabled() ) {
			return $screens;
		}

		$screens[] = 'woocommerce_page_prl_performance';
		return $screens;
	}

	/**
	 * Adds the "Recommendations" reports tab.
	 *
	 * @param  array  $reports
	 * @return array
	 */
	public static function add_recommendations_reports( $reports ) {

		if ( ! wc_prl_tracking_enabled() ) {
			return $reports;
		}

		$reports[ 'prl_recommendations' ] = array(
			'title' => __( 'Recommendations', 'woocommerce-product-recommendations' ),
			'reports' => array(
				'sales'      => array(
					'title'       => __( 'Revenue', 'woocommerce-product-recommendations' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( __CLASS__, 'get_report' ),
				),
				'events'      => array(
					'title'       => __( 'Events', 'woocommerce-product-recommendations' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( __CLASS__, 'get_report' ),
				),
				'conversions' => array(
					'title'       => __( 'Conversion', 'woocommerce-product-recommendations' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( __CLASS__, 'get_report' ),
				)
			)
		);

		// Cache for future reference.
		self::$reports = $reports[ 'prl_recommendations' ];

		return $reports;
	}

	/**
	 * Get a report from our reports subfolder.
	 *
	 * @param string $name
	 */
	public static function get_report( $name ) {
		$name  = sanitize_title( str_replace( '_', '-', $name ) );
		$class = 'WC_PRL_Report_' . str_replace( '-', '_', $name );

		include_once 'reports/class-wc-prl-report-' . $name . '.php';

		if ( ! class_exists( $class ) ) {
			return;
		}

		$report = new $class();
		$report->output_report();
	}
}

WC_PRL_Admin_Reports::init();
