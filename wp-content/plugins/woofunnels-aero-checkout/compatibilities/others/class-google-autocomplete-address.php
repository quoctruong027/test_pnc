<?php

class WFACP_Google_Address_Complete {

	private $billing_Address = [];
	private $shipping_Address = [];

	public function __construct() {
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'actions' ] );
	}


	public function is_enable() {
		if ( class_exists( 'WooCommerce_Google_Address_Plugin_Front' ) ) {

			global $WooCommerce_Google_Address_Plugin_Front;
			if ( ! is_null( $WooCommerce_Google_Address_Plugin_Front ) ) {

				return true;
			}
		}

		return false;
	}

	/**
	 *
	 */
	public function actions() {
		if ( $this->is_enable() ) {
			/**
			 * @var $WooCommerce_Google_Address_Plugin_Front WooCommerce_Google_Address_Plugin_Front
			 */ global $WooCommerce_Google_Address_Plugin_Front;

			$temp_data = $WooCommerce_Google_Address_Plugin_Front->woocommerce_billing_fields_filter( [], '' );

			if ( ! empty( $temp_data['billing_address_google'] ) ) {
				$this->billing_Address                  = $temp_data['billing_address_google'];
				$this->billing_Address['class'][]       = 'wfacp_billing_fields wfacp-col-full wfacp-form-control-wrapper';
				$this->billing_Address['label_class'][] = 'wfacp-form-control-label';
				$this->billing_Address['input_class'][] = 'wfacp-form-control';

				add_action( 'wfacp_divider_billing', [ $this, 'print_billing_address' ] );
			}

			$temp_s_data = $WooCommerce_Google_Address_Plugin_Front->woocommerce_shipping_fields_filter( [], '' );
			if ( ! empty( $temp_s_data['shipping_address_google'] ) ) {
				$this->shipping_Address                  = $temp_s_data['shipping_address_google'];
				$this->shipping_Address['class'][]       = 'wfacp_shipping_fields wfacp-col-full wfacp-form-control-wrapper';
				$this->shipping_Address['label_class'][] = 'wfacp-form-control-label';
				$this->shipping_Address['input_class'][] = 'wfacp-form-control';
				add_action( 'wfacp_divider_shipping', [ $this, 'print_shipping_address' ] );
			}
		}
	}


	public function print_billing_address() {
		woocommerce_form_field( 'billing_address_google', $this->billing_Address, '' );
	}

	public function print_shipping_address() {
		woocommerce_form_field( 'shipping_address_google', $this->shipping_Address, '' );
	}

}

//new WFACP_Google_Address_Complete();
