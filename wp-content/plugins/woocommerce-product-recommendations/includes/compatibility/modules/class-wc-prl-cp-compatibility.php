<?php
/**
 * WC_PRL_CP_Compatibility class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks for Composite Products compatibility.
 *
 * @version 1.0.6
 */
class WC_PRL_CP_Compatibility {

	/**
	 * Initialize.
	 */
	public static function init() {

		// Aggregate parent + child item totals.
		add_filter( 'woocommerce_prl_conversion_event_data', array( __CLASS__, 'conversion_event_data' ), 10, 3 );
	}

	/**
	 * Aggregate component order item totals into parent order item container.
	 *
	 * @param  array                  $data
	 * @param  WC_Order_Item_Product  $item
	 * @param  WC_Order               $order
	 * @return array
	 */
	public static function conversion_event_data( $data, $item, $order ) {

		if ( $child_items = wc_cp_get_composited_order_items( $item, $order ) ) {

			// Aggregate totals.
			$composite_totals = array(
				'total'     => $item->get_total(),
				'total_tax' => $item->get_total_tax(),
			);

			foreach ( $child_items as $child_item ) {
				$composite_totals[ 'total' ]     += $child_item->get_total();
				$composite_totals[ 'total_tax' ] += $child_item->get_total_tax();
			}

			$data[ 'total' ]     = wc_format_decimal( $composite_totals[ 'total' ] );
			$data[ 'total_tax' ] = wc_format_decimal( $composite_totals[ 'total_tax' ] );
		}

		return $data;
	}
}

WC_PRL_CP_Compatibility::init();
