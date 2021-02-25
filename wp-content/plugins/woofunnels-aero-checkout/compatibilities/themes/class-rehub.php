<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Rehub {

	public function __construct() {

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_customer_details' ] );
		add_action( 'init', [ $this, 'register_elementor_widget' ] );
	}

	public function remove_customer_details() {

		remove_action( 'woocommerce_checkout_before_customer_details', 'rehub_woo_before_checkout' );
		remove_action( 'woocommerce_checkout_after_customer_details', 'rehub_woo_average_checkout' );

	}

	public function register_elementor_widget() {
		if ( defined( 'RH_MAIN_THEME_VERSION' ) && class_exists( 'Elementor\Plugin' ) && class_exists( 'WFACP_Core' ) ) {
			if ( is_admin() ) {
				return;
			}
			if ( WFACP_Common::get_id() > 0 ) {
				$instance = WFACP_Elementor::get_instance();
				add_action( 'elementor/widgets/widgets_registered', [ $instance, 'initialize_widgets' ] );
			}
		}
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Rehub(), 'rehub' );
