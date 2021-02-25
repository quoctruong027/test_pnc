<?php

class BWFAN_NinjaForm_Source extends BWFAN_Source {
	private static $instance = null;

	public function __construct() {
		$this->event_dir = __DIR__;
		$this->nice_name = __( 'Ninja Forms', 'autonami-automations-pro' );
		$this->priority  = 100;
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_NinjaForm_Source|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

/**
 * Register this as a source.
 */
if ( bwfan_is_ninja_forms_active() ) {
	BWFAN_Load_Sources::register( 'BWFAN_NinjaForm_Source' );
}
