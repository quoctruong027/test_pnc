<?php

// Exit if accessed directly.
if ( !defined('ABSPATH') ) {
	exit;
}

if ( !class_exists( 'SO_Admin_Order_Contribution' ) ) {

	Class SO_Admin_Order_Contribution {

		function __construct() {
			add_filter( 'woocommerce_get_formatted_order_total', array( $this, 'show_offer_contribution_in_order_dashboard' ), 999, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'sa_css_on_orders_dashboard' ) );
		}

		function sa_css_on_orders_dashboard() {

			$current_screen = get_current_screen();
			if ( $current_screen instanceof WP_Screen && $current_screen->base == 'edit' && $current_screen->id == 'edit-shop_order' && $current_screen->post_type == 'shop_order' ) {

				$plugin_data = SA_Smart_Offers::get_smart_offers_plugin_data();
				$version = $plugin_data['Version'];
				wp_register_style( 'so_admin_orders_style', plugins_url(SMART_OFFERS) . '/assets/css/so-admin-orders-dashboard.css', array(), $version );
				wp_enqueue_style( 'so_admin_orders_style' );

			}

		}

		function show_offer_contribution_in_order_dashboard( $total, $order ) {

			$current_screen = get_current_screen();
			if ( ! $current_screen instanceof WP_Screen || ! $order instanceof WC_Order || $current_screen->base !== 'edit' || $current_screen->id !== 'edit-shop_order' || $current_screen->post_type !== 'shop_order' ) {
				return $total;
			}

			$order_status = $order->get_status();
			$valid_order_status = array( 'on-hold', 'processing', 'completed' );
			if ( ! in_array( $order_status, $valid_order_status )  ) {
				return $total;
			}

			$so_meta_data = $order->get_meta( 'smart_offers_meta_data', true );

			$so_contribution = array();
			if ( !empty( $so_meta_data ) ) {
				foreach ( $so_meta_data as $so_meta ) {
					array_push( $so_contribution, $so_meta['offered_price'] );
				}
			}

			$so_total = '';
			if ( is_array( $so_contribution ) && !empty( $so_contribution ) ) {
				$so_total = array_sum( $so_contribution );				
			}

			if ( empty( $so_total ) ) {
				return $total;
			}

			$order_currency = $order->get_currency();
			$html  = '</span><br/>
			<div class="so_contribution"><span class="tips" data-tip="' . esc_attr__( 'Smart Offers contribution in this order.', 'smart-offers' ) . '"><img class="so_trendline" src='.SO_PRE_URL.'assets/images/trendline_green_up.png><em> ' . sprintf( esc_html__( '%s' ), wc_price( $so_total, array( 'currency' => $order_currency ) ) ) . '</em></span></div>';
				$total = $total . $html;

			return $total;

		}

	}

	return new SO_Admin_Order_Contribution();
}
