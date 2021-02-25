<?php

final class BWFAN_Autonami_Integration extends BWFAN_Integration {
	public static $integration_type = null;
	public static $headers = null;
	private static $ins = null;

	private function __construct() {
		$this->action_dir = __DIR__;
		$this->nice_name  = __( 'Autonami', 'autonami-automations-pro' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}
}

/**
 * Register this class as an integration.
 */
BWFAN_Load_Integrations::register( 'BWFAN_Autonami_Integration' );
