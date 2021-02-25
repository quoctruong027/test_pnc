<?php

final class BWFAN_Mailchimp_Integration extends BWFAN_Integration {
	private static $ins = null;
	protected $connector_slug = 'bwfco_mailchimp';
	protected $need_connector = true;

	private function __construct() {
		$this->action_dir = __DIR__;
		$this->nice_name  = __( 'Mailchimp', 'autonami-automations-connectors' );
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
BWFAN_Load_Integrations::register( 'BWFAN_Mailchimp_Integration' );
