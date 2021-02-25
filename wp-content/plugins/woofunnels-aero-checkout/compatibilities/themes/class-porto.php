<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Theme_Porto {

	public function __construct() {
		/* checkout page */
		add_action( 'wfacp_checkout_page_found', [ $this, 'dequeue_actions' ] );

		add_action( 'init', [ $this, 'remove_customizer_fields' ] );

	}

	public function dequeue_actions() {
		add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_style' ], 99999 );
		// for version 4.9.5 and above

		add_filter( 'wfacp_css_js_removal_paths', function ( $paths, $template ) {


			if ( 'pre_built' == $template->get_template_type() ) {
				$paths[] = 'porto_styles';
			}

			return $paths;
		}, 10, 2 );
	}

	public function dequeue_style() {

		$instance = wfacp_template();

		if ( ! function_exists( 'porto_setup' ) || $instance === null ) {
			return;
		}

		$instancType = $instance->get_template_type();

		if ( $instancType === 'pre_built' ) {
			wp_dequeue_style( 'porto-bootstrap' );
			wp_dequeue_style( 'porto-dynamic-style' );
			wp_dequeue_style( 'porto-shortcodes' );
		}

	}

	public function remove_customizer_fields() {
		global $reduxPortoSettings;

		if ( ! function_exists( 'porto_setup' ) ) {
			return;
		}

		if ( ! WFACP_Common::is_customizer() ) {
			return;
		}
		// for version 4.9.5 and above
		remove_action( 'customize_controls_print_styles', 'porto_customizer_enqueue_stylesheets' );
		remove_action( 'customize_preview_init', 'porto_customizer_live_scripts' );
		if ( class_exists( 'Redux_Framework_porto_settings' ) && ( $reduxPortoSettings instanceof Redux_Framework_porto_settings ) ) {

			if ( ! $reduxPortoSettings->ReduxFramework instanceof ReduxFramework ) {

				return;
			}

			if ( ! isset( $reduxPortoSettings->ReduxFramework->extensions['customizer'] ) || ! $reduxPortoSettings->ReduxFramework->extensions['customizer'] instanceof ReduxFramework_extension_customizer ) {
				return;
			}

			$instance = $reduxPortoSettings->ReduxFramework->extensions['customizer'];
			remove_action( 'customize_register', [ $instance, '_register_customizer_controls' ] );

		}
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Theme_Porto(), 'porto' );
