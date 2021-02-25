<?php
/**
 * Smart Offers Admin Notifications
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.0.0
 *
 * @package     smart-offers/includes/admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SO_Admin_Dashboard_Widget' ) ) {

	/**
	 * Class for handling dashboard widget of Smart Offers
	 */
	class SO_Admin_Dashboard_Widget {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'wp_dashboard_setup', array( $this, 'init_dashboard' ), 10 );
			add_action( 'admin_enqueue_scripts', array( $this, 'sa_css_on_dashboard_widget' ) );
		}

		/**
		 * Init dashboard widgets
		 */
		public function init_dashboard() {
			wp_add_dashboard_widget( 'smart_offers_dashboard_widget', __( 'Smart Offers Statistics', 'smart-offers' ), array( $this, 'smart_offers_stats' ) );
		}

		/**
		 * CSS for Smart Offers Dashboard widget
		 */
		public function sa_css_on_dashboard_widget() {
			$current_screen = get_current_screen();
			if ( $current_screen instanceof WP_Screen && 'dashboard' === $current_screen->id ) {
				$plugin_data = SA_Smart_Offers::get_smart_offers_plugin_data();
				$version     = $plugin_data['Version'];
				wp_register_style( 'so_admin_dashboard_style', plugins_url( SMART_OFFERS ) . '/assets/css/so-admin-dashboard-widget.css', array(), $version );
				wp_enqueue_style( 'so_admin_dashboard_style' );
			}
		}

		/**
		 * Show SO statistics
		 */
		public function smart_offers_stats() {
			global $wpdb;

			$wpdb->query( 'SET SESSION group_concat_max_len=999999' ); // WPCS: cache ok, db call ok.

			$offers_count_args = array(
				'post_type'   => 'smart_offers',
				'fields'      => 'ids',
				'nopaging'    => true,
				'post_status' => 'any',
				'meta_query'  => array( // phpcs:ignore
					array(
						'key' => 'so_accept_skip_counter',
					),
				),
			);

			$query_results_for_offers_count = new WP_Query( $offers_count_args );

			$accept_count = 0;
			$skip_count   = 0;
			$total_count  = 0;

			if ( $query_results_for_offers_count->post_count > 0 ) {

				foreach ( $query_results_for_offers_count->posts as $post_id ) {
					$post_id = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post_id );
					$result  = get_post_meta( $post_id, 'so_accept_skip_counter', true );
					foreach ( $result as $key => $value ) {
						if ( 'accepted' === $key ) {
							$accept_count += $value;
						}
						if ( 'skipped' === $key ) {
							$skip_count += $value;
						}
						if ( 'offer_shown' === $key ) {
							$total_count += $value;
						}
					}
				}
			}

			$valid_order_status       = array( 'wc-completed', 'wc-processing', 'wc-on-hold' );
			$get_valid_order_statuses = get_option( 'so_valid_order_statuses_for_earning', $valid_order_status );

			$offers_sale_args      = array(
				'post_type'   => 'shop_order',
				'fields'      => 'ids',
				'nopaging'    => true,
				'post_status' => $get_valid_order_statuses,
				'meta_query'  => array( // phpcs:ignore
					array(
						'key' => 'smart_offers_meta_data',
					),
				),
			);
			$offers_sale_order_ids = new WP_Query( $offers_sale_args );

			$offers_paid_through = 0;
			$total_sale          = 0;

			$store_currency = get_option( 'woocommerce_currency' );

			if ( $offers_sale_order_ids->post_count > 0 ) {
				foreach ( $offers_sale_order_ids->posts as $post_id ) {
					$post_id             = apply_filters( 'sa_so_wpml_get_current_lang_offer_id', $post_id );
					$result              = get_post_meta( $post_id, 'smart_offers_meta_data', true );
					$offers_paid_through = $offers_paid_through + count( $result );
					foreach ( $result as $key => $value ) {
						// TODO: move currency dependency class checking in one class and then route through individual plugin files.
						$order_currency = get_post_meta( $post_id, '_order_currency', true );
						if ( $store_currency !== $order_currency && class_exists( 'SO_Aelia_CS_Compatibility' ) ) {
							$so_aelia_cs        = new SO_Aelia_CS_Compatibility();
							$so_converted_total = $so_aelia_cs->modify_so_contri_amount( $value['offered_price'], $order_currency );
							$total_sale        += $so_converted_total;
						} else {
							$total_sale += $value ['offered_price'];
						}
					}
				}
			}

			$conversion_rate = '';
			if ( is_numeric( $total_count ) && is_numeric( $offers_paid_through ) ) {
				$conversion_rate = ( 0 !== $total_count ) ? ( $offers_paid_through / $total_count ) * 100 : 0;
			}

			$total_count         = is_numeric( $total_count ) ? $total_count : 0;
			$skip_count          = is_numeric( $skip_count ) ? $skip_count : 0;
			$accept_count        = is_numeric( $accept_count ) ? $accept_count : 0;
			$offers_paid_through = is_numeric( $offers_paid_through ) ? $offers_paid_through : 0;

			$stats  = '<ul class="woocommerce_stats">';
			$stats .= '<li style="width: 55%; overflow: hidden"><strong>' . wc_price( $total_sale ) . '</strong><p class="so-revenue"> ' . __( 'Revenue from Offers', 'smart-offers' ) . '</p></li>';
			$stats .= '<li style="width: 45%; overflow: hidden"><strong>' . wc_format_decimal( $conversion_rate, get_option( 'woocommerce_price_num_decimals' ), $trim_zeros = false ) . '%</strong><p class="so-conversion-rate"> ' . __( 'Conversion Rate', 'smart-offers' ) . '</p></li>';
			$stats .= '</ul>';
			$stats .= '<ul class="woocommerce_stats">';
			$stats .= '<li id="so-seen" style="width: 25%;"><strong>' . $total_count . '</strong><span class="so dashicons dashicons-visibility"><p class="offers-seen"> ' . __( 'Seen', 'smart-offers' ) . '</p></span></li>';
			$stats .= '<li id="so-skipped" style="width: 25%"><strong>' . $skip_count . '</strong><span class="so dashicons dashicons-thumbs-down"><p class="offers-skipped"> ' . __( 'Skipped', 'smart-offers' ) . '</p></span></li>';
			$stats .= '<li id="so-accepted"  style="width: 25%"><strong>' . $accept_count . '</strong><span class="so dashicons dashicons-thumbs-up"><p class="offers-accepted"> ' . __( 'Accepted', 'smart-offers' ) . '</p></span></li>';
			$stats .= '<li id="so-paid" style="width: 25%"><strong>' . $offers_paid_through . '</strong><span class="so dashicons dashicons-awards"><p class="offers-paid"> ' . __( 'Paid Through', 'smart-offers' ) . '</p></span></li>';
			$stats .= '</ul>';

			echo wp_kses_post( $stats );
		}

	}

	return new SO_Admin_Dashboard_Widget();
}
