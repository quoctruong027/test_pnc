<?php

class WFOCU_Compatibility_With_GeneratePress {

	public function __construct() {
		add_action( 'customize_register', [ $this, 'wfocu_temp_remove_controls' ], 1500 );
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 */
	public function wfocu_temp_remove_controls( $wp_customize ) {

		if ( function_exists( 'WFOCU_Core' ) && ( class_exists( 'Generate_Typography_Customize_Control' ) || class_exists( 'GeneratePress_Pro_Typography_Customize_Control' ) ) && is_object( WFOCU_Core()->template_loader ) && WFOCU_Core()->template_loader->is_customizer_preview() ) {
			$all_controls = $wp_customize->controls();
			foreach ( $all_controls as $id => $control ) {
				if ( $control instanceof Generate_Typography_Customize_Control || $control instanceof GeneratePress_Pro_Typography_Customize_Control ) {
					$wp_customize->remove_control( $id );

				}
			}
		}
	}

	public function is_enable() {
		if ( false === class_exists( 'Generate_Typography_Customize_Control' ) && false === class_exists( 'GeneratePress_Pro_Typography_Customize_Control' ) ) {
			return false;
		}

		return true;
	}

}

WFOCU_Plugin_Compatibilities::register( new WFOCU_Compatibility_With_GeneratePress(), 'generatepress' );

