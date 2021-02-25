<?php
/**
 * Plugin Name: Smart Offers
 * Plugin URI: https://www.storeapps.org/product/smart-offers/
 * Description: <strong>WooCommerce Smart Offers</strong> lets you earn more by creating a powerful sales funnel of upsells, downsells, backend and order bump offers. Show special offers on any page of your choice.
 * Version: 3.13.3
 * Author: StoreApps
 * Author URI: https://www.storeapps.org/
 * Requires at least: 4.9.0
 * Tested up to: 5.5.1
 * WC requires at least: 3.0.0
 * WC tested up to: 4.5.2
 * Text Domain: smart-offers
 * Domain Path: /languages/
 * Copyright (c) 2013-2020 StoreApps. All rights reserved.
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

$active_plugins = (array) get_option('active_plugins', array());

if (is_multisite()) {
	$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
}

if (!( in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) )) {
	return;
} else {

	if ( !class_exists( 'SA_Smart_Offers' ) ) {

		class SA_Smart_Offers {

			function __construct() {

				global $wpdb;

				$this->define_constants();
				$this->includes();

				// WPML compatibility
				add_filter( 'sa_so_wpml_get_current_lang_offer_id',  array( $this, 'sa_so_wpml_get_offer_id' ) );

				if ( ! $this->is_wc_gte_30() ) {
					add_action( 'admin_notices', array( $this, 'admin_notice_sa_needs_wc_30_above' ) );
				}

				add_action( 'admin_init', array( $this, 'activated' ) );

				if ( is_admin() ) {
					$this->initialize_so_upgrade();
				}

			}

			/**
			 * To handle WC compatibility related function call from appropriate class
			 *
			 * @param $function_name string
			 * @param $arguments array of arguments passed while calling $function_name
			 * @return result of function call
			 *
			 */
			public function __call( $function_name, $arguments = array() ) {

				if ( ! is_callable( 'SA_WC_Compatibility_4_3', $function_name ) ) return;

				if ( ! empty( $arguments ) ) {
					return call_user_func_array( 'SA_WC_Compatibility_4_3::'.$function_name, $arguments );
				} else {
					return call_user_func( 'SA_WC_Compatibility_4_3::'.$function_name );
				}

			}

			/**
			 * Function to be executed on activation
			 */
			public static function so_activate() {
				include_once( 'includes/admin/class-so-admin-post-type.php' );
				include_once( 'includes/admin/class-so-admin-install.php' );
			}

			/**
			 * WPML compat
			 * @since 3.7.0
			 */
			function sa_so_wpml_get_offer_id( $offer_id ) {

				if ( is_array( $offer_id ) ) {
					foreach ( $offer_id as $key => $value ) {
						$offer_id[ $key ] = apply_filters( 'wpml_object_id', $value, 'smart_offers', true );
					}
				} else {
					$offer_id = apply_filters( 'wpml_object_id', $offer_id, 'smart_offers', true );
				}

				return $offer_id;

			}

			/**
			 * Function to show admin notice that Smart Offers works with WC 3.0+
			 */
			public function admin_notice_sa_needs_wc_30_above() {
				?>
				<div class="updated error">
					<p><?php
						echo sprintf(__( '%s Smart Offers is active but it will only work with WooCommerce 3.0+. %s.', 'smart-offers' ), '<strong>' . __( 'Important', 'smart-offers' ) . ':</strong>', '<a href="'.admin_url('plugins.php?plugin_status=upgrade').'" target="_blank" >' . __( 'Please update WooCommerce to the latest version', 'smart-offers' ) . '</a>' );
					?></p>
				</div>
				<?php
			}

			public function activated() {
				$prefix = 'smart_offers';
				$is_check = get_option( $prefix . '_check_update', 'no' );
				if ( $is_check === 'no' ) {
					$response = wp_remote_get( 'https://www.storeapps.org/wp-admin/admin-ajax.php?action=check_update&plugin=so' );
					update_option( $prefix . '_check_update', 'yes' );
				}
			}

			/*
			 * Include class files
			 */
			function includes() {
				global $sa_smart_offers;

				include_once( 'includes/compat/class-sa-wc-compatibility-2-5.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-2-6.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-3-0.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-3-1.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-3-2.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-3-3.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-3-4.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-3-5.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-3-6.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-3-7.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-3-8.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-3-9.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-4-0.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-4-1.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-4-2.php' );
				include_once( 'includes/compat/class-sa-wc-compatibility-4-3.php' );

				if ( ! $sa_smart_offers instanceof SA_Smart_Offers ) {
					$sa_smart_offers = $this;
				}

				// Post type should be registered from backend and frontend
				include( 'includes/admin/class-so-admin-post-type.php' );

				if ( is_admin() ) {
					include_once( 'includes/admin/class-so-admin-ready-offer-designs.php' );
					include_once 'includes/admin/class-so-admin-welcome.php' ;
					include( 'includes/admin/class-so-admin-pointers.php' );
					include_once( 'includes/admin/class-so-admin-offer.php' );
					include_once( 'includes/admin/class-so-admin-save-offer.php' );
					include_once( 'includes/admin/class-so-admin-offers.php' );
					include_once( 'includes/admin/class-so-admin-dashboard-widget.php' );
					include_once( 'includes/admin/class-so-admin-order-contribution.php' );
					include_once( 'includes/admin/class-so-admin-notifications.php' );
					include_once( 'includes/admin/class-so-privacy.php' );
					include_once( 'includes/admin/deactivation-survey/class-so-admin-deactivation.php' );
					// Settings file included when adding submenu pages

					if ( ! class_exists( 'StoreApps_Upgrade_3_6' ) ) {
						require_once 'sa-includes/class-storeapps-upgrade-3-6.php';
					}
				}

				if ( ! is_admin() || defined('DOING_AJAX') ) {
					include_once( 'includes/frontend/class-so-shortcodes.php' );
				}

				// In file class-so-init.php & class-so-offer.php, some stats are modified based on order statuses
				// and order statuses can be changed from admin side also, hence kept open for both admin & frontend
				include_once( 'includes/frontend/class-so-session-handler.php' );
				include_once( 'includes/frontend/class-so-offer.php' );
				include_once( 'includes/frontend/class-so-offers.php' );
				include_once( 'includes/frontend/class-so-init.php' );

				// For DB updates.
				include_once( 'includes/class-so-db-update.php' );

				if ( ! function_exists( 'is_plugin_active' ) ) {
					$abspath = trailingslashit( ABSPATH );
					require_once ABSPATH  . 'wp-admin/includes/plugin.php';
				}
				if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
					include_once( 'includes/compat/class-so-subscription.php' );
				}
				if ( is_plugin_active( 'woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php' ) ) {
					include_once( 'includes/compat/multi-currency-compat/class-so-aelia-cs-compatibility.php' );
				}
			}

			/*
			 * Defining SO Constants
			 */
			private function define_constants() {
				if( !defined( 'SO_PLUGIN_FILE' ) ) {
					define( 'SO_PLUGIN_FILE', __FILE__ );
				}
				if( !defined( 'SMART_OFFERS' ) ) {
					define( 'SMART_OFFERS', substr( plugin_basename( __FILE__ ), 0, strpos( plugin_basename( __FILE__ ), '/' ) ) );
				}
				if( !defined( 'SO_PRE_URL' ) ) {
					define( 'SO_PRE_URL',plugins_url().'/'.strtolower('smart-offers').'/' );
				}
				if ( !defined( 'SA_SO_PLUGIN_DIRPATH' ) ) {
					define( 'SA_SO_PLUGIN_DIRPATH', dirname( __FILE__ ) );
				}
				if ( !defined( 'SO_PLUGIN_BASE_NM' ) ) {
					define( 'SO_PLUGIN_BASE_NM', plugin_basename( __FILE__ ) );
				}
			}

			public static function get_smart_offers_plugin_data() {
				return get_plugin_data( __FILE__ );
			}

			/**
			 * Find latest StoreApps Upgrade file
			 * @return string classname
			 */
			function get_latest_upgrade_class() {
				$available_classes = get_declared_classes();
				$available_upgrade_classes = array_filter( $available_classes, function ( $class_name ) {
																					return strpos( $class_name, 'StoreApps_Upgrade_' ) === 0;
																				} );
				$latest_class = 'StoreApps_Upgrade_3_6';
				$latest_version = 0;
				foreach ( $available_upgrade_classes as $class ) {
					$exploded = explode( '_', $class );
					$get_numbers = array_filter( $exploded, function ( $value ) {
																return is_numeric( $value );
															} );
					$version = implode( '.', $get_numbers );
					if ( version_compare( $version, $latest_version, '>' ) ) {
						$latest_version = $version;
						$latest_class = $class;
					}
				}

				return $latest_class;
			}

			/*
			 * Initializing So Upgrade class
			 */
			function initialize_so_upgrade() {
				$latest_upgrade_class = $this->get_latest_upgrade_class();

				$sku = 'so';
				$prefix = 'smart_offers';
				$plugin_name = 'Smart Offers';
				$documentation_link = 'https://www.storeapps.org/knowledgebase_category/smart-offers/';
				$GLOBALS['so_upgrader'] = new $latest_upgrade_class(__FILE__, $sku, $prefix, $plugin_name, 'smart-offers', $documentation_link);
			}

		}// End of class SA_Smart_Offers

	} // End class exists check

	/*
	 * Initializing SO class
	 */
	function initialize_so() {
		$GLOBALS['sa_smart_offers'] = new SA_Smart_Offers();
	}

	add_action( 'woocommerce_loaded', 'initialize_so' );

}

register_activation_hook( __FILE__, array( 'SA_Smart_Offers', 'so_activate' ) );
