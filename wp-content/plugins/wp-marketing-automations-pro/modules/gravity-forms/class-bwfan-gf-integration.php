<?php

final class BWFAN_GF_Integration extends BWFAN_Integration {

	private static $instance = null;

	/**
	 * BWFAN_GF_Integration constructor.
	 */
	private function __construct() {
		$this->action_dir = __DIR__;
		$this->nice_name  = __( 'Gravity Forms', 'autonami-automations-pro' );
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_GF_Integration|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

if ( bwfan_is_gforms_active() ) {
	BWFAN_Load_Integrations::register( 'BWFAN_GF_Integration' );
}
