<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Wfob {
	private $bump_products = [];

	public function __construct() {

		/* checkout page */
		add_filter( 'wfacp_skip_add_to_cart', [ $this, 'read_order_bump_product' ], 9999 );

	}

	public function read_order_bump_product( $status ) {
		if ( class_exists( 'WFOB_Core' ) ) {

			$added_keys = [];
			$products   = WC()->session->get( 'wfob_added_bump_product', [] );

			if ( is_array( $products ) && count( $products ) > 0 ) {
				foreach ( $products as $bump_id => $added_products ) {
					$added_keys = array_merge( $added_keys, array_keys( $added_products ) );
				}
			}

			if ( ! is_null( WC()->cart ) && count( $added_keys ) > 0 ) {
				$cart_contents = WC()->cart->get_cart_contents();
				if ( count( $cart_contents ) > 0 ) {
					foreach ( $cart_contents as $item_key => $item ) {
						if ( isset( $item['_wfob_product'] ) && in_array( $item['_wfob_product_key'], $added_keys ) ) {
							$this->bump_products[ $item_key ] = $item;
						}
					}
				}
			}
			if ( count( $this->bump_products ) > 0 ) {
				return true;
			}

			return $status;
		}
	}

}

//WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Wfob(), 'wfob' );
