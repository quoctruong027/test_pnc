<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Active_Avada {

	public function __construct() {

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_actions' ] );
		add_action( 'wfacp_checkout_page_found', [ $this, 'remove_actions' ] );
	}

	public function remove_actions() {

		global $avada_woocommerce;
		if ( class_exists( 'Avada_Woocommerce' ) && $avada_woocommerce instanceof Avada_Woocommerce ) {
			remove_action( 'woocommerce_before_checkout_form', array( $avada_woocommerce, 'avada_top_user_container' ), 1 );
			remove_action( 'woocommerce_before_checkout_form', array( $avada_woocommerce, 'checkout_coupon_form' ), 10 );
			remove_action( 'woocommerce_before_checkout_form', array( $avada_woocommerce, 'before_checkout_form' ) );
			remove_action( 'woocommerce_after_checkout_form', array( $avada_woocommerce, 'after_checkout_form' ) );
			remove_action( 'woocommerce_checkout_before_customer_details', array( $avada_woocommerce, 'checkout_before_customer_details' ) );
			remove_action( 'woocommerce_checkout_after_customer_details', array( $avada_woocommerce, 'checkout_after_customer_details' ) );
			remove_action( 'woocommerce_checkout_billing', array( $avada_woocommerce, 'checkout_billing' ), 20 );
			remove_action( 'woocommerce_checkout_shipping', array( $avada_woocommerce, 'checkout_shipping' ), 20 );

			remove_filter( 'woocommerce_order_button_html', array( $avada_woocommerce, 'order_button_html' ) );

			add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );
		}
		if ( class_exists( 'Fusion_Dynamic_CSS' ) ) {
			$dynamic_css = Fusion_Dynamic_CSS::get_instance();
			if ( $dynamic_css->inline instanceof Fusion_Dynamic_CSS_Inline ) {
				remove_action( 'wp_head', array( $dynamic_css->inline, 'add_inline_css' ), 999 );
			}
		}

	}

	public function internal_css() {
		?>

        <style>
            html:not(.avada-html-layout-boxed):not(.avada-html-layout-framed) body,
            html {
                background-color: transparent !important;

            }

            body.wfacp_checkout-template-wfacp-canvas-php {
                overflow-x: initial;
            }

        </style>
		<?php
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Active_Avada(), 'avada' );
