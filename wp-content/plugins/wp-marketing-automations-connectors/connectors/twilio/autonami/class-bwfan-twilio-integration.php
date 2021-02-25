<?php

class BWFAN_Twilio_Integration extends BWFAN_Integration {

	private static $ins = null;
	protected $connector_slug = 'bwfco_twilio';
	protected $need_connector = true;

	public function __construct() {
		$this->action_dir = __DIR__;
		$this->nice_name  = __( 'Twilio', 'autonami-automations-connectors' );
	}

	/**
	 * @return BWFAN_Twilio_Integration|null
	 */
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

BWFAN_Load_Integrations::register( 'BWFAN_Twilio_Integration' );
