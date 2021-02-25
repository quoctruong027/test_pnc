<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Theme_Shoptimizer {
	public function __construct() {
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'unhook_func' ] );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_product_thumbnail_in_checkout' ] );
		add_action( 'wfacp_before_process_checkout_template_loader', [ $this, 'remove_product_thumbnail_in_checkout' ] );


	}

	public function unhook_func() {
		if ( defined( 'SHOPTIMIZER_CORE' ) ) {
			remove_action( 'woocommerce_after_checkout_form', 'woocommerce_checkout_coupon_form' );
			remove_action( 'woocommerce_after_checkout_form', 'shoptimizer_coupon_wrapper_start', 5 );
			remove_action( 'woocommerce_after_checkout_form', 'shoptimizer_coupon_wrapper_end', 60 );
			add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );
		}
	}

	public function remove_product_thumbnail_in_checkout() {
		if ( function_exists( 'shoptimizer_product_thumbnail_in_checkout' ) ) {
			remove_filter( 'woocommerce_cart_item_name', 'shoptimizer_product_thumbnail_in_checkout', 20, 3 );
		}
	}

	public function internal_css() {
		echo "<style>";
		echo "body #ship-to-different-address {border: none;margin: 0;padding: 0;}";
		echo "</style>";

	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Theme_Shoptimizer(), 'shoptimizer' );
