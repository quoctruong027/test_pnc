<?php
/**
 * Plugin Name: WooCommerce Update Variations In Cart
 * Plugin URI: https://www.storeapps.org/product/update-variations-in-cart/
 * Description: Allow your customers to directly update WooCommerce Product Variations in the cart.
 * Version: 1.8.5
 * Author: StoreApps
 * Author URI: https://www.storeapps.org/
 * Requires at least: 4.9.0
 * Tested up to: 5.5.3
 * WC requires at least: 2.5.0
 * WC tested up to: 4.7.0
 * Text Domain: woocommerce-update-variations-in-cart
 * Domain Path: /languages/
 * Copyright (c) 2013-2020 StoreApps. All rights reserved.
 * License: GPLv2 or later
 *
 * @package WooCommerce Update Variations In Cart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_plugins = (array) get_option( 'active_plugins', array() );

if ( is_multisite() ) {
	$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
}

if ( ! ( in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) ) {
	return;
}

/**
 * Initialize Update Variations In Cart
 */
function initialize_update_variation_cart() {
	if ( ! defined( 'UVC_PLUGIN_DIRNAME' ) ) {
		define( 'UVC_PLUGIN_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
	}

	if ( ! defined( 'UVC_URL' ) ) {
		define( 'UVC_URL', trailingslashit( plugins_url( UVC_PLUGIN_DIRNAME ) ) );
	}

	if ( ! defined( 'UVC_PLUGIN_FILE' ) ) {
		define( 'UVC_PLUGIN_FILE', __FILE__ );
	}

	if ( ! defined( 'UVC_PLUGIN_DIRPATH' ) ) {
		define( 'UVC_PLUGIN_DIRPATH', dirname( __FILE__ ) );
	}

	include_once dirname( __FILE__ ) . '/includes/compat/class-sa-wc-compatibility-2-5.php';
	include_once dirname( __FILE__ ) . '/includes/compat/class-sa-wc-compatibility-2-6.php';
	include_once dirname( __FILE__ ) . '/includes/compat/class-sa-wc-compatibility-3-0.php';

	if ( ! class_exists( 'WC_Update_Variations_In_Cart' ) ) {
		require_once dirname( __FILE__ ) . '/includes/class-wc-update-variations-in-cart.php';
	}

	$GLOBALS['wc_update_variations_in_cart'] = new WC_Update_Variations_In_Cart();

	if ( ! class_exists( 'StoreApps_Upgrade_3_7' ) ) {
		require_once 'sa-includes/class-storeapps-upgrade-3-7.php';
	}

	$latest_upgrade_class = $GLOBALS['wc_update_variations_in_cart']->get_latest_upgrade_class();

	$sku                             = 'uvc';
	$prefix                          = 'update_variations_in_cart';
	$plugin_name                     = 'WooCommerce Update Variations In Cart';
	$text_domain                     = 'woocommerce-update-variations-in-cart';
	$documentation_link              = 'https://www.storeapps.org/knowledgebase_category/woocommerce-update-variations-in-cart/';
	$GLOBALS[ $prefix . '_upgrade' ] = new $latest_upgrade_class( __FILE__, $sku, $prefix, $plugin_name, $text_domain, $documentation_link );
}

add_action( 'plugins_loaded', 'initialize_update_variation_cart' );
