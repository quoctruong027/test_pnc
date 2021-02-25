<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Stripe_GPAY_AND_APAY {
	public function __construct() {
		add_filter( 'wfacp_smart_buttons', [ $this, 'add_buttons' ] );
		add_action( 'wfacp_smart_button_container_stripe_gpay_apay', [ $this, 'add_stripe_gpay_apay_buttons' ] );
	}

	public function add_buttons( $buttons ) {

		if ( ! class_exists( 'WC_Stripe_Payment_Request' ) ) {
			return $buttons;
		}
		if ( true == apply_filters( 'wfacp_disabled_google_apple_pay_button_on_desktop', false, $buttons ) ) {


			if ( ! class_exists( 'WFACP_Mobile_Detect' ) ) {
				return $buttons;
			}

			$detect = new WFACP_Mobile_Detect();
			if ( ! $detect->isMobile() || empty( $detect ) ) {
				return $buttons;
			}
			add_filter( 'wfacp_template_localize_data', [ $this, 'set_local_data' ] );
		}
		$settings = get_option( 'woocommerce_stripe_settings', array() );
		// Checks if Payment Request is enabled.
		if ( ! isset( $settings['payment_request'] ) || 'yes' !== $settings['payment_request'] ) {
			return $buttons;
		}

		add_filter( 'wc_stripe_show_payment_request_on_checkout', '__return_true' );

		$instance = WC_Stripe_Payment_Request::instance();
		remove_action( 'woocommerce_checkout_before_customer_details', [ $instance, 'display_payment_request_button_html' ], 1 );
		remove_action( 'woocommerce_checkout_before_customer_details', [ $instance, 'display_payment_request_button_separator_html' ], 2 );

		$buttons['stripe_gpay_apay'] = [
			'iframe' => true,
			'name'   => __( 'Stripe Payment Reques', 'woocommerce-gateway-amazon-payments-advanced' ),
		];

		return $buttons;
	}

	public function add_stripe_gpay_apay_buttons() {
		$instance = WC_Stripe_Payment_Request::instance();
		$instance->display_payment_request_button_html();
	}

	public function set_local_data( $data ) {
		$data['stripe_smart_show_on_desktop'] = 'no';

		return $data;
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Stripe_GPAY_AND_APAY(), 'gpay_apay' );
