<?php
/**
 * Handles webfonts.
 *
 * @package     WFACPKirki
 * @category    Modules
 * @author      Aristeides Stathopoulos
 * @copyright   Copyright (c) 2017, Aristeides Stathopoulos
 * @license     http://opensource.org/licenses/https://opensource.org/licenses/MIT
 * @since       3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds script for tooltips.
 */
class WFACPKirki_Modules_Webfonts {

	/**
	 * The object instance.
	 *
	 * @static
	 * @access private
	 * @since 3.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * The WFACPKirki_Fonts_Google object.
	 *
	 * @access protected
	 * @since 3.0.0
	 * @var object
	 */
	protected $fonts_google;

	/**
	 * The class constructor
	 *
	 * @access protected
	 * @since 3.0.0
	 */
	protected function __construct() {

		include_once wp_normalize_path( dirname( __FILE__ ) . '/class-kirki-fonts.php' );
		include_once wp_normalize_path( dirname( __FILE__ ) . '/class-kirki-fonts-google.php' );
		include_once wp_normalize_path( dirname( __FILE__ ) . '/class-kirki-fonts-google-local.php' );

		add_action( 'wp_loaded', array( $this, 'run' ) );

	}

	/**
	 * Run on after_setup_theme.
	 *
	 * @access public
	 * @since 3.0.0
	 */
	public function run() {
		$this->fonts_google = WFACPKirki_Fonts_Google::get_instance();
		$this->init();
	}

	/**
	 * Gets an instance of this object.
	 * Prevents duplicate instances which avoid artefacts and improves performance.
	 *
	 * @static
	 * @access public
	 * @since 3.0.0
	 * @return object
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init other objects depending on the method we'll be using.
	 *
	 * @access protected
	 * @since 3.0.0
	 */
	protected function init() {

		foreach ( array_keys( WFACPKirki::$config ) as $config_id ) {
			$method    = $this->get_method( $config_id );
			$classname = 'WFACPKirki_Modules_Webfonts_' . ucfirst( $method );
			new $classname( $config_id, $this, $this->fonts_google );
		}
		new WFACPKirki_Modules_Webfonts_Local( $this, $this->fonts_google );

	}

	/**
	 * Get the method we're going to use.
	 *
	 * @access public
	 * @since 3.0.0
	 * @return string
	 */
	public function get_method() {

		// Figure out which method to use.
		$method = apply_filters( 'wfacpkirki_googlefonts_load_method', 'async' );

		// Fallback to 'async' if value is invalid.
		if ( 'async' !== $method && 'embed' !== $method && 'link' !== $method ) {
			$method = 'async';
		}

		$classname = 'WFACPKirki_Modules_Webfonts_' . ucfirst( $method );
		if ( ! class_exists( $classname ) ) {
			$method = 'async';
		}

		// Force using the JS method while in the customizer.
		// This will help us work-out the live-previews for typography fields.
		// If we're not in the customizer use the defined method.
		return ( is_customize_preview() ) ? 'async' : $method;
	}

	/**
	 * Goes through all our fields and then populates the $this->fonts property.
	 *
	 * @access public
	 * @param string $config_id The config-ID.
	 */
	public function loop_fields( $config_id ) {
		foreach ( WFACPKirki::$fields as $field ) {
			if ( isset( $field['wfacpkirki_config'] ) && $config_id !== $field['wfacpkirki_config'] ) {
				continue;
			}
			if ( true === apply_filters( "wfacpkirki_{$config_id}_webfonts_skip_hidden", true ) ) {
				// Only continue if field dependencies are met.
				if ( ! empty( $field['required'] ) ) {
					$valid = true;

					foreach ( $field['required'] as $requirement ) {
						if ( isset( $requirement['setting'] ) && isset( $requirement['value'] ) && isset( $requirement['operator'] ) ) {
							$controller_value = WFACPKirki_Values::get_value( $config_id, $requirement['setting'] );
							if ( ! WFACPKirki_Helper::compare_values( $controller_value, $requirement['value'], $requirement['operator'] ) ) {
								$valid = false;
							}
						}
					}

					if ( ! $valid ) {
						continue;
					}
				}
			}
			$this->fonts_google->generate_google_font( $field );
		}
	}
}
