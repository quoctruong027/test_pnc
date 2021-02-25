<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Functions used by plugins
 */
if ( ! class_exists( 'WCCT_Deals_WC_Dependencies' ) ) {
	require_once 'class-wcct-wc-deals-dependencies.php';
}

/**
 * WC Detection
 */
if ( ! function_exists( 'wcct_deal_is_woocommerce_active' ) ) {
	function wcct_deal_is_woocommerce_active() {
		return WCCT_Deals_WC_Dependencies::woocommerce_active_check();
	}

}
