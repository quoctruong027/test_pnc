<?php
/**
 * WC_PRL_Elementor_Compatibility class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks for Elementor Widgets compatibility.
 *
 * @version 1.1.0
 */
class WC_PRL_Elementor_Compatibility {

	/**
	 * Initialize.
	 */
	public static function init() {
		add_filter( 'woocommerce_before_single_product', array( __CLASS__, 'reload_product_location' ), 100 );
	}

	/**
	 * Load Product location's recommendations if not already loaded.
	 */
	public static function reload_product_location() {

		$location = WC_PRL()->locations->get_location( 'product_details' );

		if ( $location && ! $location->is_loaded() ) {

			$hooks = $location->get_hooks();

			foreach ( $hooks as $hook => $data ) {
				add_action( $hook, array( WC_PRL()->templates , 'display_recommendations' ), $data[ 'priority' ], $data[ 'args_number' ] );
			}

			if ( ! empty( $hooks ) ) {
				$location->set_load_status( true );
			}
		}
	}
}

WC_PRL_Elementor_Compatibility::init();
