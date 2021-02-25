<?php
/**
 * Plugin Name: WooCommerce Orders Tracking Premium
 * Plugin URI: https://villatheme.com/extensions/woocommerce-orders-tracking
 * Description: Easily import/manage your order tracking, add tracking to PayPal, send email/sms notification to customers. Support AfterShip, EasyPost, TrackingMore and Cainiao API.
 * Version: 1.0.6
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * Text Domain: woocommerce-orders-tracking
 * Domain Path: /languages
 * Copyright 2019-2020 VillaTheme.com. All rights reserved.
 * Tested up to: 5.6
 * WC tested up to: 4.8
 **/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'VI_WOOCOMMERCE_ORDERS_TRACKING_VERSION', '1.0.6' );
define( 'VI_WOOCOMMERCE_ORDERS_TRACKING_PATH_FILE', __FILE__ );
define( 'VI_WOOCOMMERCE_ORDERS_TRACKING_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'woocommerce-orders-tracking' . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_ORDERS_TRACKING_INCLUDES', VI_WOOCOMMERCE_ORDERS_TRACKING_DIR . 'includes' . DIRECTORY_SEPARATOR );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
/*Required for register_activation_hook*/
if ( is_file( VI_WOOCOMMERCE_ORDERS_TRACKING_INCLUDES . 'data.php' ) ) {
	require_once VI_WOOCOMMERCE_ORDERS_TRACKING_INCLUDES . 'data.php';
}
if ( is_file( VI_WOOCOMMERCE_ORDERS_TRACKING_INCLUDES . 'class-vi-woo-orders-tracking-track-info-table.php' ) ) {
	require_once VI_WOOCOMMERCE_ORDERS_TRACKING_INCLUDES . 'class-vi-woo-orders-tracking-track-info-table.php';
}
if ( is_file( VI_WOOCOMMERCE_ORDERS_TRACKING_INCLUDES . 'class-vi-woo-orders-tracking-trackingmore-table.php' ) ) {
	require_once VI_WOOCOMMERCE_ORDERS_TRACKING_INCLUDES . 'class-vi-woo-orders-tracking-trackingmore-table.php';
}
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	$init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-orders-tracking" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "define.php";
	require_once $init_file;
} else {
	add_action( 'admin_notices', array( 'WOOCOMMERCE_ORDERS_TRACKING', 'global_note' ) );
}

if ( ! class_exists( 'WOOCOMMERCE_ORDERS_TRACKING' ) ) {
	class WOOCOMMERCE_ORDERS_TRACKING {
		protected $settings;

		public function __construct() {
			register_activation_hook( VI_WOOCOMMERCE_ORDERS_TRACKING_PATH_FILE, array( __CLASS__, 'install' ) );
		}

		/**
		 * Notify if WooCommerce is not activated
		 */
		public static function global_note() {
			?>
            <div id="message" class="error">
                <p><?php esc_html_e( 'Please install and activate WooCommerce to use WooCommerce Orders Tracking plugin.', 'woocommerce-orders-tracking' ); ?></p>
            </div>
			<?php
		}

		public static function install() {
			wp_unschedule_hook( 'vi_wot_refresh_track_info' );
			/*Create custom table to store tracking data*/
			VI_WOOCOMMERCE_ORDERS_TRACKING_TRACKINGMORE_TABLE::create_table();
			VI_WOOCOMMERCE_ORDERS_TRACKING_TRACK_INFO_TABLE::create_table();
			/*create tracking page*/
			if ( ! get_option( 'woo_orders_tracking_settings' ) ) {
				$current_user = wp_get_current_user();
				// create post object
				$page = array(
					'post_title'  => esc_html__( 'Order Tracking', 'woocommerce-orders-tracking' ),
					'post_status' => 'publish',
					'post_author' => $current_user->ID,
					'post_type'   => 'page',
					'post_name'   => 'order-tracking',
				);
				// insert the post into the database
				$page_id = wp_insert_post( $page, false );
				if ( ! is_wp_error( $page_id ) ) {
					$settings                      = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance();
					$args                          = $settings->get_params();
					$args['service_tracking_page'] = $page_id;
					update_option( 'woo_orders_tracking_settings', $args );
				}
			}
		}
	}
}
new WOOCOMMERCE_ORDERS_TRACKING();