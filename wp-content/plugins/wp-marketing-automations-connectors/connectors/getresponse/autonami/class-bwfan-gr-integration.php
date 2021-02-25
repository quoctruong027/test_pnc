<?php

final class BWFAN_GetResponse_Integration extends BWFAN_Integration {
	private static $ins = null;
	protected $connector_slug = 'bwfco_getresponse';
	protected $need_connector = true;

	private function __construct() {
		$this->action_dir = __DIR__;
		$this->nice_name  = __( 'Get Response', 'autonami-automations-connectors' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	protected function do_after_action_registration( BWFAN_Action $action_object ) {
		$action_object->connector = $this->connector_slug;
	}

}

/**
 * Register this class as an integration.
 */
BWFAN_Load_Integrations::register( 'BWFAN_GetResponse_Integration' );
