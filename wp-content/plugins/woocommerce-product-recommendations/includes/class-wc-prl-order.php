<?php
/**
 * WC_PRL_Order class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks and actions for Orders.
 *
 * @class    WC_PRL_Order
 * @version  1.0.0
 */
class WC_PRL_Order {

	/**
	 * Init.
	 */
	public static function init() {

		// Modify order items to include conversion meta.
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'add_order_item_meta' ), 10, 3 );
		// Hide conversion metadata in order line items.
		add_filter( 'woocommerce_hidden_order_itemmeta', array( __CLASS__, 'hide_order_item_meta' ) );
		// Add conversion event.
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'maybe_add_order_conversion_event' ), 10, 4 );
	}

	/**
	 * Hide conversion metadata in edit-order screen.
	 *
	 * @param  array  $hidden_meta
	 * @return void
	 */
	public static function hide_order_item_meta( $hidden_meta ) {
		return array_merge( $hidden_meta, array( '_prl_conversion', '_prl_conversion_time', '_prl_conversion_source_hash' ) );
	}

	/**
	 * Add conversion meta to order items.
	 *
	 * @param  WC_Order_Item  $order_item
	 * @param  string         $cart_item_key
	 * @param  array          $cart_item
	 * @return void
	 */
	public static function add_order_item_meta( $order_item, $cart_item_key, $cart_item ) {

		// Search for cart conversion and add it to the order meta.
		if ( isset( $cart_item[ '_prl_conversion' ], $cart_item[ '_prl_conversion_time' ] ) && ! empty( $cart_item[ '_prl_conversion' ] ) ) {
			$order_item->add_meta_data( '_prl_conversion', absint( $cart_item[ '_prl_conversion' ] ), true );
			$order_item->add_meta_data( '_prl_conversion_time', absint( $cart_item[ '_prl_conversion_time' ] ), true );
		}

		if ( isset( $cart_item[ '_prl_conversion_source_hash' ] ) ) {
			$order_item->add_meta_data( '_prl_conversion_source_hash', $cart_item[ '_prl_conversion_source_hash' ], true );
		}
	}

	/**
	 * Adds a conversion event on manual status edit if the current status is applicable.
	 *
	 * @param  WC_Order  $order
	 * @param  string    $status
	 * @return void
	 */
	public static function maybe_add_order_conversion_event( $order_id, $status_from, $status_to, $order ) {

		if ( ! ( $order instanceof WC_Order ) ) {
			return;
		}

		$is_saved = $order->get_meta( '_prl_conversion_saved', true, 'edit' );

		if ( 'yes' !== $is_saved && in_array( $status_to, wc_get_is_paid_statuses() ) ) {
			self::add_order_conversion_event( $order );
		}
	}

	/**
	 * Writes a conversion event in the DB for every converted item that exists in order line items.
	 *
	 * @param  WC_Order  $order
	 * @return void
	 */
	private static function add_order_conversion_event( $order ) {

		$order = is_numeric( $order ) ? wc_get_order( $order ) : $order;
		if ( ! ( $order instanceof WC_Order ) ) {
			return;
		}

		$items            = $order->get_items();
		$found_conversion = false;

		foreach ( $items as $key => $item ) {

			// Search if an order item with conversion key exists.
			if ( $item->get_meta( '_prl_conversion' ) ) {

				// Fetch the deployment.
				$deployment = new WC_PRL_Deployment_Data( absint( $item->get_meta( '_prl_conversion' ) ) );

				// Add event to DB.
				$args = apply_filters( 'woocommerce_prl_conversion_event_data', array(
					'added_to_cart_time' => absint( $item->get_meta( '_prl_conversion_time' ) ),
					'deployment_id'      => $deployment->get_id(),
					'engine_id'          => $deployment->get_engine_id(),
					'location_hash'      => substr( md5( $deployment->get_hook() ), 0, 7),
					'source_hash'        => wc_clean( $item->get_meta( '_prl_conversion_source_hash' ) ),
					'product_id'         => $item->get_product_id(),
					'order_id'           => $order->get_id(),
					'order_item_id'      => $item->get_id(),
					'total'              => wc_format_decimal( $item->get_total() ),
					'total_tax'          => wc_format_decimal( $item->get_total_tax() ),
				), $item, $order );

				try {
					WC_PRL()->db->tracking->add_conversion_event( $args );
					$found_conversion = true;
				} catch ( Exception $e ) {
					if ( $e->getMessage() ) {
						WC_PRL()->log( 'Conversion Event: ' . $e->getMessage(), 'notice', 'wc_prl' );
					}
				}
			}
		}

		// If conversion is found, mark the order as saved.
		if ( $found_conversion ) {
			$order->add_meta_data( '_prl_conversion_saved', 'yes', true );
			$order->save();
		}
	}
}

WC_PRL_Order::init();
