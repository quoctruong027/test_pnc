<?php

/**
 * WooCommerce - MailerLite by MailerLite by MailerLite
 * Class WFACP_Compatibility_WC_Coderockz_Delivery
 */
class WFACP_Compatibility_WC_MailerLite {


	public function __construct() {

		/* Register Add field */
		add_filter( 'wfacp_advanced_fields', [ $this, 'add_field' ], 20 );
		add_filter( 'wfacp_html_fields_woo_ml_subscribe_html', '__return_false' );

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'actions' ] );

		add_action( 'process_wfacp_html', [ $this, 'call_fields_hook' ], 50, 3 );

		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );
		add_action( 'wfacp_before_process_checkout_template_loader', [ $this, 'actions' ] );

	}

	public function actions() {

		if ( function_exists( 'woo_ml_get_option' ) ) {
			$checkout_position      = woo_ml_get_option( 'checkout_position', 'checkout_billing' );
			$checkout_position_hook = 'woocommerce_' . $checkout_position;
			remove_action( $checkout_position_hook, 'woo_ml_checkout_label', 20 );
		}

	}

	public function add_field( $fields ) {
		if ( $this->is_enable() ) {
			$fields['woo_ml_subscribe_html'] = [
				'type'       => 'wfacp_html',
				'class'      => [ 'woo_ml_subscribe' ],
				'id'         => 'woo_ml_subscribe_html',
				'field_type' => 'advanced',
				'label'      => __( 'Woo MailerLite', 'woofunnels-aero-checkout' ),
			];
		}

		return $fields;
	}

	public function call_fields_hook( $field, $key, $args ) {

		if ( $this->is_enable() && ( ! empty( $key ) && ( 'woo_ml_subscribe_html' === $key ) ) ) {
			if ( function_exists( 'woo_ml_checkout_label' ) ) {
				echo "<div class='wfacp_woo_mailerlite_wrap'>";
				woo_ml_checkout_label();
				echo "</div>";
			}

		}
	}

	public function is_enable() {

		if ( class_exists( 'Woo_Mailerlite' ) ) {
			return true;
		}

		return false;

	}

	public function internal_css() {
		if ( ! $this->is_enable() ) {
			return;
		}

		if ( ! function_exists( 'wfacp_template' ) ) {
			return;

		}


		$instance = wfacp_template();
		if ( ! $instance instanceof WFACP_Template_Common ) {
			return;
		}

		$px = $instance->get_template_type_px();


		echo "<style>";
		if ( $px != '' ) {
			echo ".wfacp_woo_mailerlite_wrap{clear: both;padding:0 $px" . 'px' . "}";
			echo ".wfacp_woo_mailerlite_wrap p{position: relative;}";
			echo "body .wfacp_main_form.woocommerce .wfacp_woo_mailerlite_wrap input[type=checkbox] + label{    padding-left: 5px !important;}";

		}
		echo "</style>";

	}


}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_WC_MailerLite(), 'wc-mailerlite' );
