<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Astra {

	public function __construct() {
		add_action( 'wfacp_before_process_checkout_template_loader', [ $this, 'remove_actions' ] );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_actions' ] );
		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );

	}

	public function remove_actions() {

		remove_action( 'woocommerce_checkout_before_customer_details', 'astra_two_step_checkout_form_wrapper_div', 1 );
		remove_action( 'woocommerce_checkout_before_customer_details', 'astra_two_step_checkout_form_ul_wrapper', 2 );
		remove_action( 'woocommerce_checkout_order_review', 'astra_woocommerce_div_wrapper_close', 30 );
		remove_action( 'woocommerce_checkout_order_review', 'astra_woocommerce_ul_close', 30 );
		remove_action( 'woocommerce_checkout_before_customer_details', 'astra_two_step_checkout_address_li_wrapper', 5 );
		remove_action( 'woocommerce_checkout_after_customer_details', 'astra_woocommerce_li_close' );
		if ( class_exists( 'Astra_Ext_Nav_Menu_Loader' ) ) {
			WFACP_Common::remove_actions( 'wp_nav_menu_args', 'Astra_Ext_Nav_Menu_Loader', 'modify_nav_menu_args' );
			WFACP_Common::remove_actions( 'astra_theme_defaults', 'Astra_Ext_Nav_Menu_Loader', 'theme_defaults' );
			WFACP_Common::remove_actions( 'wp_enqueue_scripts', 'Astra_Ext_Nav_Menu_Loader', 'load_scripts' );
			WFACP_Common::remove_actions( 'customize_register', 'Astra_Ext_Nav_Menu_Loader', 'customize_register' );
			WFACP_Common::remove_actions( 'wp_footer', 'Astra_Ext_Nav_Menu_Loader', 'megamenu_style' );
			WFACP_Common::remove_actions( 'customize_preview_init', 'Astra_Ext_Nav_Menu_Loader', 'customize_preview_init' );
		}

		add_action( 'wp_print_styles', [ $this, 'remove_theme_css_and_scripts' ], 100 );
	}


	public function remove_theme_css_and_scripts() {
		global $wp_scripts, $wp_styles;

		/* Unwanted folder for dequeue css and js */
		$us = [ '/astra-addon/', 'astra-' ];

		$registered_script = $wp_scripts->registered;
		if ( ! empty( $registered_script ) ) {

			foreach ( $registered_script as $handle => $data ) {

				if ( false !== strpos( $data->src, $us[0] ) || ( false !== strpos( $data->src, $us[1] ) ) ) {

					unset( $wp_scripts->registered[ $handle ] );
					wp_dequeue_script( $handle );
				}
			}
		}

		$registered_style = $wp_styles->registered;

		if ( ! empty( $registered_style ) ) {
			foreach ( $registered_style as $handle => $data ) {

				if ( false !== strpos( $data->src, $us[0] ) || ( false !== strpos( $data->src, $us[1] ) ) ) {

					unset( $wp_styles->registered[ $handle ] );
					wp_dequeue_style( $handle );
				}
			}
		}
	}

	public function internal_css() {
		if ( defined( 'ASTRA_THEME_VERSION' ) ) {
			echo '<style>';
			echo 'body{overflow-x: visible;}';
			echo '</style>';
		}

	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Astra(), 'Astra' );
