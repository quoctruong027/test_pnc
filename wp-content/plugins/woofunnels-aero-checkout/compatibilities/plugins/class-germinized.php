<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_WC_Germinized {

	public function __construct() {
		/* checkout page */
		add_action( 'wfacp_form_widgets_elementor_editor', [ $this, 'remove_actions' ] );
		add_action( 'wfacp_mini_cart_widgets_elementor_editor', [ $this, 'remove_actions' ] );
		add_action( 'wfacp_before_process_checkout_template_loader', [ $this, 'remove_actions' ] );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_actions' ] );
		add_action( 'woocommerce_checkout_update_order_review', [ $this, 'update_order_review' ] );
		add_action( 'wfacp_get_fragments', [ $this, 'wfacp_get_fragments' ] );
		add_action( 'wfob_before_add_to_cart', [ $this, 'removed_Germanized_action' ] );
		add_action( 'wfob_before_remove_bump_from_cart', [ $this, 'removed_Germanized_action' ] );
		add_action( 'wfacp_woocommerce_review_order_before_submit', [ $this, 'remove_order_button_html_filter' ], 30 );
	}


	public function remove_actions() {
		if ( class_exists( 'WooCommerce_Germanized' ) && WFACP_Common::get_id() > 0 ) {
			add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );
			add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'refresh_order_submit' ), 11, 1 );
			$this->actions();
			add_action( 'wp_enqueue_scripts', [ $this, 'remove_css' ], 99 );
			WFACP_Common::remove_actions( 'woocommerce_review_order_before_shipping', 'WC_GZD_Checkout', 'remove_shipping_rates' );
			add_filter( 'wfacp_display_place_order_buttons', [ $this, 'do_not_display_native_submit_button' ] );
		}
	}

	private function actions() {
		if ( class_exists( 'WooCommerce_Germanized' ) && function_exists( 'wc_gzd_get_hook_priority' ) ) {

			remove_action( 'woocommerce_review_order_after_order_total', 'woocommerce_gzd_template_cart_total_tax', 1 );
			remove_action( 'woocommerce_review_order_before_cart_contents', 'woocommerce_gzd_template_checkout_remove_cart_name_filter' );
			remove_action( 'woocommerce_review_order_before_cart_contents', 'woocommerce_gzd_template_checkout_table_content_replacement' );
			remove_action( 'woocommerce_review_order_after_cart_contents', 'woocommerce_gzd_template_checkout_table_product_hide_filter_removal' );
			remove_filter( 'woocommerce_checkout_cart_item_quantity', 'wc_gzd_cart_product_units', wc_gzd_get_hook_priority( 'checkout_product_units' ) );
			remove_filter( 'woocommerce_checkout_cart_item_quantity', 'wc_gzd_cart_product_delivery_time', wc_gzd_get_hook_priority( 'checkout_product_delivery_time' ) );
			remove_filter( 'woocommerce_checkout_cart_item_quantity', 'wc_gzd_cart_product_item_desc', wc_gzd_get_hook_priority( 'checkout_product_item_desc' ) );
			remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', wc_gzd_get_hook_priority( 'checkout_order_review' ) );
			remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', wc_gzd_get_hook_priority( 'checkout_payment' ) );


			if ( ! is_null( WC()->session ) ) {

				$paypal_express_checkout_angle_eye = WC()->session->get( 'paypal_express_checkout', null );
				$paypal_express_checkout           = WC()->session->get( 'paypal', null );

				if ( ! is_null( $paypal_express_checkout_angle_eye ) || ! is_null( $paypal_express_checkout ) ) {
					add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
				}
			}
		}
	}

	public function refresh_order_submit( $fragments ) {
		if ( isset( $fragments['.wc-gzd-order-submit'] ) ) {
			unset( $fragments['.wc-gzd-order-submit'] );
		}

		return $fragments;
	}

	public function update_order_review( $postdata ) {
		$post_data = [];
		parse_str( $postdata, $post_data );
		if ( isset( $post_data['_wfacp_post_id'] ) ) {
			$this->actions();
		}
	}

	public function wfacp_get_fragments( $wfacp_id ) {
		if ( $wfacp_id > 0 ) {
			$this->actions();
		}
	}

	public function removed_Germanized_action() {
		if ( isset( $_REQUEST['wfacp_post_id'] ) ) {
			$this->actions();
		}
	}

	public function remove_css() {
		wp_dequeue_style( 'woocommerce-gzd-layout' );
	}

	public function internal_css( $selected_template_slug ) {

		if ( function_exists( 'wfacp_template' ) ) {
			$instance = wfacp_template();
		}
		if ( is_null( $instance ) ) {
			return;
		}
		$px = $instance->get_template_type_px();
		if ( ! isset( $px ) || $px == '' ) {
			return;
		}

		echo '<style>';
		echo 'body .wfacp_main_form .form-row.checkbox-legal .woocommerce-form__label-for-checkbox span.woocommerce-gzd-legal-checkbox-text{padding-left: 0;}';
		if ( $selected_template_slug == 'layout_9' || $selected_template_slug == 'layout_1' ) {
			echo 'body .wfacp_main_form .wc-gzd-checkbox-placeholder.wc-gzd-checkbox-placeholder-legal{padding: 0;}';
		}
		echo '</style>';


		if ( WFACP_Common::is_customizer() ) {
			echo '<style>';
			echo '#payment button#place_order {display: none;}';
			echo '</style>';

		}

	}

	public function do_not_display_native_submit_button( $status ) {

		if ( ! WFACP_Common::is_theme_builder() ) {
			$status = false;
		}

		return $status;
	}

	public function remove_order_button_html_filter() {
		remove_filter( 'woocommerce_order_button_html', 'woocommerce_gzd_template_button_temporary_hide', PHP_INT_MAX );
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_WC_Germinized(), 'wc_germinized' );
