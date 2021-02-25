<?php
/**
 * Plugin Name: Save For Later For WooCommerce
 * Plugin URI: https://www.storeapps.org/product/save-for-later-for-woocommerce/
 * Description: Allow your customer to save products from cart for later use.
 * Version: 1.5.0
 * Author: StoreApps
 * Author URI: https://www.storeapps.org/
 * Requires at least: 4.0
 * Tested up to: 5.4.2
 * WC requires at least: 2.5.0
 * WC tested up to: 4.2.0
 * Text Domain: save-for-later-for-woocommerce
 * Domain Path: /languages/
 * Copyright (c) 2016-2020 StoreApps. All rights reserved.
 * License: GPLv2 or later
 *
 * @package save-for-later-for-woocommerce
 */

// Exit if accessed directly.
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
 * Initialize the plugin
 */
function initialize_save_for_later_for_woocommerce() {

	if ( ! defined( 'SA_SFL_PLUGIN_FILE' ) ) {
		define( 'SA_SFL_PLUGIN_FILE', __FILE__ );
	}
	if ( ! defined( 'SA_SFL_PLUGIN_DIRNAME' ) ) {
		define( 'SA_SFL_PLUGIN_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
	}
	if ( ! defined( 'SA_SFL_PRE_URL' ) ) {
		define( 'SA_SFL_PRE_URL', plugins_url() . '/' . strtolower( 'save-for-later-for-woocommerce' ) . '/' );
	}

	require_once 'includes/class-sa-save-for-later.php';
	require_once 'includes/class-sa-save-for-later-privacy.php';

	require_once 'includes/compat/class-sa-wc-compatibility-2-5.php';
	require_once 'includes/compat/class-sa-wc-compatibility-2-6.php';
	require_once 'includes/compat/class-sa-wc-compatibility-3-0.php';
	require_once 'includes/compat/class-sa-wc-compatibility-3-1.php';
	require_once 'includes/compat/class-sa-wc-compatibility-3-2.php';
	require_once 'includes/compat/class-sa-wc-compatibility-3-3.php';
	require_once 'includes/compat/class-sa-wc-compatibility-3-4.php';

	$GLOBALS['sa_save_for_later'] = SA_Save_For_Later::get_instance();

	$latest_upgrade_class = $GLOBALS['sa_save_for_later']->get_latest_upgrade_class();

	if ( ! class_exists( 'StoreApps_Upgrade_3_6' ) ) {
		require_once 'sa-includes/class-storeapps-upgrade-3-6.php';
	}

	$sku                = 'sfl';
	$prefix             = 'save-for-later-for-woocommerce';
	$plugin_name        = 'Save For Later For WooCommerce';
	$text_domain        = 'save-for-later-for-woocommerce';
	$documentation_link = 'https://www.storeapps.org/knowledgebase_category/save-for-later-for-woocommerce/';
	$sfl_upgrader       = new $latest_upgrade_class( __FILE__, $sku, $prefix, $plugin_name, $text_domain, $documentation_link );

}
add_action( 'plugins_loaded', 'initialize_save_for_later_for_woocommerce' );
