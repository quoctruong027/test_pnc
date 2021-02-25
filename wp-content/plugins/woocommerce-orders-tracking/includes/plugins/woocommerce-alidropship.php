<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Aliexpress Dropshipping and Fulfillment for WooCommerce
 */
if ( ! class_exists( 'VI_WOOCOMMERCE_ORDERS_TRACKING_PLUGINS_WooCommerce_Alidropship' ) ) {
	class VI_WOOCOMMERCE_ORDERS_TRACKING_PLUGINS_WooCommerce_Alidropship {
		protected static $settings;

		/**
		 * VI_WOOCOMMERCE_ORDERS_TRACKING_PLUGINS_WooCommerce_Alidropship constructor.
		 */
		public function __construct() {
			self::$settings = VI_WOOCOMMERCE_ORDERS_TRACKING_DATA::get_instance();
			add_action( 'vi_wad_sync_aliexpress_order_tracking_info', array(
				$this,
				'send_email_ali_order'
			), 10, 5 );
		}

		/**
		 * @param $current_tracking_data
		 * @param $old_tracking_data
		 * @param $status_switch_to_shipped
		 * @param $order_item_id
		 * @param $order_id
		 *
		 * @throws Exception
		 */
		public function send_email_ali_order( $current_tracking_data, $old_tracking_data, $status_switch_to_shipped, $order_item_id, $order_id ) {
			if ( self::$settings->get_params( 'email_send_after_aliexpress_order_synced' ) && $current_tracking_data['tracking_number'] && ( $current_tracking_data['tracking_number'] != $old_tracking_data['tracking_number'] || $status_switch_to_shipped ) ) {
				VI_WOOCOMMERCE_ORDERS_TRACKING_ADMIN_IMPORT_CSV::send_mail( $order_id );
			}
		}
	}
}
