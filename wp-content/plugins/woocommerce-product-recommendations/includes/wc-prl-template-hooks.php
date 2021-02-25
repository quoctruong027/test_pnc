<?php
/**
 * Template Hooks
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Tracking based template hooks.
add_filter( 'woocommerce_loop_product_link', 'woocommerce_prl_add_link_track_param' );
add_filter( 'woocommerce_product_add_to_cart_url', 'woocommerce_prl_add_link_track_param' );
add_filter( 'woocommerce_loop_add_to_cart_args', 'woocommerce_prl_add_data_track_param', 10, 2 );
