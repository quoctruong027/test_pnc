<?php
/**
 * Automatic preset scripts calculation for Xirki controls.
 *
 * @package     Xirki
 * @category    Modules
 * @author      XplodedThemes (@XplodedThemes)
 * @copyright   Copyright (c) 2019, XplodedThemes (@XplodedThemes)
 * @license     https://opensource.org/licenses/MIT
 * @since       3.0.26
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds styles to the customizer.
 */
class Xirki_Modules_Preset {

	/**
	 * The object instance.
	 *
	 * @static
	 * @access private
	 * @since 3.0.26
	 * @var object
	 */
	private static $instance;

	/**
	 * Constructor.
	 *
	 * @access protected
	 * @since 3.0.26
	 */
	protected function __construct() {
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'customize_controls_print_footer_scripts' ) );
	}

	/**
	 * Gets an instance of this object.
	 * Prevents duplicate instances which avoid artefacts and improves performance.
	 *
	 * @static
	 * @access public
	 * @since 3.0.26
	 * @return object
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @access public
	 * @since 3.0.26
	 */
	public function customize_controls_print_footer_scripts() {
		wp_enqueue_script( 'xirki-preset', trailingslashit( Xirki::$url ) . 'modules/preset/preset.js', array( 'jquery' ), XIRKI_VERSION, false );
	}
}