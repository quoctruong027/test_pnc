<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Functions used by plugins
 */
if ( ! class_exists( 'WCCT_Dependencies' ) ) {
	require_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'woo-includes/class-wc-dependencies.php';
}

/**
 * WC Detection
 */
if ( ! function_exists( 'wcct_is_woocommerce_active' ) ) {

	function wcct_is_woocommerce_active() {
		return WCCT_Dependencies::wcct_woocommerce_active_check();
	}
}
