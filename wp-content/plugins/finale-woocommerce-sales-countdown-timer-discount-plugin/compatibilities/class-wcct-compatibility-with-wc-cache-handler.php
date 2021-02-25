<?php

class WCCT_Compatibility_With_WC_Cache_Handler {

	public $is_cart_content = false;
	public $ajax_test_product_id = false;

	public function __construct() {
		if ( class_exists( 'Cache_Handler_RequirementsChecks' ) ) {
			add_action( 'init', array( $this, 'maybe_check_ajax_call' ) );

		}
	}


	public function maybe_check_ajax_call() {

		if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) {
			if ( 'woocommerce_cache_handler_ajax' === filter_input( INPUT_POST, 'action' ) && 'get_product_prices_html' === filter_input( INPUT_POST, 'exec' ) ) {
				$post_ids = $_POST['product_ids'];
				error_reporting( E_ALL );
				ini_set( 'display_errors', '1' );
				if ( $post_ids && is_array( $post_ids ) && count( $post_ids ) > 0 ) {
					foreach ( $post_ids as $id ) {

						$product_id = WCCT_Core()->public->wcct_get_product_parent_id( $id );

						WCCT_Core()->public->get_single_campaign_pro_data( $product_id, true );

					}
				}
			}
		}
	}
}

new WCCT_Compatibility_With_WC_Cache_Handler();
