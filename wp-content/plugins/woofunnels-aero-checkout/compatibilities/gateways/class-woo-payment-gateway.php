<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Name: Braintree For WooCommerce
 * Class WFACP_Compatibility_With_Woo_Payment_Gateway
 */
class WFACP_Compatibility_With_Woo_Payment_Gateway {


	public function __construct() {
		add_filter( 'wfacp_body_class', [ $this, 'add_body_class' ], 999 );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_action' ], 999 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment' ], 150, 2 );
		add_action( 'wfacp_intialize_template_by_ajax', function () {
			//for when our fragments calls running
			add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment' ], 99, 2 );
		}, 10 );
	}

	public function add_body_class( $class ) {
		if ( function_exists( 'requireBraintreeProDependencies' ) ) {
			$class[] = 'bfwc-body';
		}

		return $class;
	}

	public function remove_action() {
		if ( class_exists( 'WC_Braintree_Field_Manager' ) ) {
			remove_action( 'woocommerce_review_order_after_order_total', [ 'WC_Braintree_Field_Manager', 'output_checkout_fields' ] );
			add_action( 'woocommerce_checkout_before_customer_details', [ $this, 'print_order_total_fields' ] );
		}

	}

	public function print_order_total_fields() {
		if ( class_exists( 'WC_Braintree_Field_Manager' ) ) {
			echo '<div id="woo-payment-gatewway-wfacp-payment-fields">';
			WC_Braintree_Field_Manager::output_checkout_fields();
			echo '</div>';
		}
	}

	public function add_fragment( $fragments ) {
		if ( class_exists( 'WC_Braintree_Field_Manager' ) && isset( WFACP_Common::$post_data['_wfacp_post_id'] ) ) {
			ob_start();
			$this->print_order_total_fields();
			$fragments['#woo-payment-gatewway-wfacp-payment-fields'] = ob_get_clean();
		}

		return $fragments;
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Woo_Payment_Gateway(), 'woo-payment-gateway' );
