<?php

class WFACP_WooCommerce_Product_bundles {

	public function __construct() {
		add_filter( 'wfacp_enable_delete_item', [ $this, 'do_not_display' ], 10, 2 );
		add_filter( 'wfacp_show_item_quantity', [ $this, 'do_not_display' ], 10, 2 );
		add_filter( 'wfacp_show_you_save_text', [ $this, 'do_not_display' ], 10, 2 );
		add_filter( 'wfacp_show_item_price', [ $this, 'do_not_display_main_product_price' ], 10, 2 );
		add_filter( 'wfacp_show_undo_message_for_item', [ $this, 'do_not_undo' ], 10, 2 );
		add_filter( 'wfacp_exclude_product_cart_count', [ $this, 'do_not_undo' ], 10, 2 );
		add_filter( 'wfacp_show_item_price_placeholder', [ $this, 'display_cart_item_price' ], 10, 3 );
		add_filter( 'wfacp_show_item_quantity_placeholder', [ $this, 'display_item_quantity' ], 10, 3 );

	}

	public function do_not_display( $status, $cart_item ) {

		if ( isset( $cart_item['bundled_by'] ) ) {
			$status = false;
		}

		return $status;
	}

	public function do_not_undo( $status, $cart_item ) {
		if ( isset( $cart_item['bundled_by'] ) ) {
			$status = true;
		}

		return $status;
	}




	public function do_not_display_main_product_price( $status, $cart_item ) {
		if ( is_array( $cart_item ) && isset( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ) {
			if ( 'bundle' == $cart_item['data']->get_type() ) {

				$status = false;
			}
		}

		return $status;
	}

	public function display_cart_item_price( $_product, $cart_item, $cart_item_key ) {
		echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.

	}

	public function display_item_quantity( $cart_item ) {

		if ( isset( $cart_item['bundled_by'] ) ) {
			?>
            <span><?php echo $cart_item['quantity']; ?></span>
			<?php
		}
	}


}

new WFACP_WooCommerce_Product_bundles();
