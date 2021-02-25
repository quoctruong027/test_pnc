<?php
/**
 * WC_PRL_Vendors_Compatibility class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.4.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks for Product Vendors compatibility.
 *
 * @version 1.4.1
 */
class WC_PRL_Vendors_Compatibility {

	/**
	 * Initialize.
	 */
	public static function init() {

		// Add Vendors related filters.
		add_filter( 'woocommerce_prl_filters', array( __CLASS__, 'add_filters' ) );
	}

	/**
	 * Add 'Relative Bundle' filter.
	 *
	 * @param  array  $classes
	 * @return array
	 */
	public static function add_filters( $filters ) {

		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-vendor.php' );
		require_once( WC_PRL_ABSPATH . 'includes/filters/class-wc-prl-filter-vendor-context.php' );

		$filters[] = 'WC_PRL_Filter_Vendor';
		$filters[] = 'WC_PRL_Filter_Vendor_Context';

		return $filters;
	}
}

WC_PRL_Vendors_Compatibility::init();
