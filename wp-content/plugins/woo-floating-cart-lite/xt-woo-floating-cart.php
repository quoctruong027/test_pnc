<?php
/**
 * XT WooCommerce Floating Cart
 *
 * @package     XT_Woo_Floating_Cart
 * @author      XplodedThemes
 * @copyright   2018 XplodedThemes
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: XT WooCommerce Floating Cart
 * Plugin URI:  https://xplodedthemes.com/products/woo-floating-cart/
 * Description: An Interactive Side Cart / Floating Cart for WooCommerce that slides in when the user decides to buy an item. Fully customizable right from WordPress Customizer with Live Preview. Products, quantities and prices are updated instantly via Ajax.
 * Tags: woocommerce cart, floating cart, site cart, mini cart, interactive cart, cart
 * Version:     2.1.6
 * WC requires at least: 3.0.0
 * WC tested up to: 4.8
 * Author:      XplodedThemes
 * Author URI:  https://xplodedthemes.com
 * Text Domain: woo-floating-cart
 * Domain Path: /languages/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
  */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $xt_woofc_plugin;

$market = '##XT_MARKET##';
$market = ( strpos( $market, 'XT_MARKET' ) !== false ) ? 'freemius' : $market;
$market = ( defined( 'XT_MARKET' ) ) ? XT_MARKET : $market;

$xt_woofc_plugin = array(
	'version'      => '2.1.6',
	'name'         => 'XT WooCommerce Floating Cart',
	'menu_name'    => 'Woo Floating Cart',
	'url'          => 'https://xplodedthemes.com/products/woo-floating-cart/',
	'icon'         => 'dashicons-cart',
	'slug'         => 'xt-woo-floating-cart',
	'prefix'       => 'xt_woo_floating_cart',
	'short_prefix' => 'xt_woofc',
	'market'       => $market,
	'markets'      => array(
		'freemius' => array(
			'id'            => 2907,
			'key'           => 'pk_befd5797ccb0ea582377dfd79ce5b',
			'url'           => 'https://xplodedthemes.com/products/woo-floating-cart/',
			'premium_slug'  => 'xt-woo-floating-cart',
			'freemium_slug' => 'woo-floating-cart-lite'
		),
		'envato'   => array(
			'id'  => 22115713,
			'url' => 'https://codecanyon.net/item/woocommerce-floating-cart/22115713',
			'premium_slug'  => 'xt-woo-floating-cart'
		)
	),
	'dependencies' => array(
		array(
			'name'  => 'WooCommerce',
			'class' => 'WooCommerce',
			'url'   => 'https://en-ca.wordpress.org/plugins/woocommerce/'
		)
	),
    'conflicts' => array(
        array(
            'name'  => 'Woocommerce Side Cart',
            'path'  => 'side-cart-woocommerce/xoo-wsc-main.php',
        ),
        array(
            'name'  => 'Woocommerce Side Cart Premium',
            'path'  => 'woocommerce-side-cart-premium/xoo-wsc-main.php',
        )
    ),
	'file'         => __FILE__
);


if ( function_exists( 'xt_woo_floating_cart' ) ) {

	xt_woo_floating_cart()->access_manager()->set_basename( false, __FILE__ );

} else {

	/**
	 * Require XT Framework
	 *
	 * @since    1.0.0
	 */
	require_once plugin_dir_path( __FILE__ ) . 'xt-framework/start.php';

	/**
	 * Require main plugin file
	 *
	 * @since    1.0.0
	 */
	require_once plugin_dir_path( __FILE__ ) . 'class-core.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function xt_woo_floating_cart() {

		global $xt_woofc_plugin;

		return XT_Woo_Floating_Cart::instance( $xt_woofc_plugin );
	}

	// Run Plugin.
	xt_woo_floating_cart();
}

