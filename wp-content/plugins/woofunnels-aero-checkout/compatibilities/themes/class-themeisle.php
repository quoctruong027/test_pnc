<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Themeisle {
	public function __construct() {

		/* checkout page */
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'actions' ] );
	}

	public function actions() {

		/* Zerif Theme Compatabilty */

		remove_action( 'woocommerce_before_checkout_form', 'zerif_coupon_after_order_table_js' );
		remove_action( 'woocommerce_checkout_order_review', 'zerif_coupon_after_order_table' );

		/* Shop Isle Theme Compatabilty */
		remove_action( 'woocommerce_before_checkout_form', 'shop_isle_coupon_after_order_table_js' );
		remove_action( 'woocommerce_checkout_order_review', 'shop_isle_coupon_after_order_table' );

	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Themeisle(), 'Themeisle' );
