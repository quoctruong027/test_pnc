<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Marcadopago {


	public function __construct() {
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_actions' ], 999 );
	}

	public function remove_actions() {
		if ( class_exists( 'WC_WooMercadoPago_CustomGateway' ) ) {
			WFACP_Common::remove_actions( 'wp_enqueue_scripts', 'WC_WooMercadoPago_CustomGateway', 'add_checkout_scripts_custom' );

			add_action( 'wp_enqueue_scripts', array( $this, 'add_checkout_scripts_custom' ), 15 );
		}
	}

	public function add_checkout_scripts_custom() {
		if ( ! get_query_var( 'order-received' ) ) {

			$path = WFACP_Common::get_class_path( 'WC_WooMercadoPago_CustomGateway' );
			$path .= '/woocommerce-mercadopago.php';
			wp_enqueue_style( 'woocommerce-mercadopago-style', plugins_url( 'assets/css/custom_checkout_mercadopago.css', plugin_dir_path( $path ) ) );

			wp_enqueue_script( 'mercado-pago-module-custom-js', 'https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js', [ 'underscore', 'wp-util' ] );

		}
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Marcadopago(), 'marcadopago' );
