<?php

final class BWFAN_Autonami_Source extends BWFAN_Source {
	private static $instance = null;

	public function __construct() {
		$this->event_dir = __DIR__;
		$this->nice_name = __( 'Autonami', 'autonami-automations-pro' );
		$this->priority  = 100;
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_Autonami_Source|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

BWFAN_Load_Sources::register( 'BWFAN_Autonami_Source' );
