<?php

final class BWFAN_TVE_Integration extends BWFAN_Integration {

	private static $instance = null;

	/**
	 * BWFAN_TVE_Integration constructor.
	 */
	private function __construct() {
		$this->action_dir = __DIR__;
		$this->nice_name  = __( 'Thrive Leads', 'autonami-automations-pro' );
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_TVE_Integration|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

if ( bwfan_is_tve_active() ) {
	BWFAN_Load_Integrations::register( 'BWFAN_TVE_Integration' );
}
