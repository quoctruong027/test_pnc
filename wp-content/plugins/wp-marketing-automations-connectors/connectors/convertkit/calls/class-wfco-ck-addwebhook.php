<?php

class WFCO_Ck_Addwebhook extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_secret', 'webhook_url', 'event_details' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		BWFCO_ConvertKit::set_headers();

		return $this->add_webhook();
	}

	/**
	 * Add webhook to account
	 *
	 * api_secret, target_url, event are required.
	 *
	 * @return array|mixed|object|string
	 */
	public function add_webhook() {
		$params = '{ "api_secret": "' . $this->data['api_secret'] . '","target_url": "' . $this->data['webhook_url'] . '","event": ' . $this->data['event_details'] . ' }';
		$url    = $this->get_endpoint();
		$res    = $this->make_wp_requests( $url, $params, BWFCO_ConvertKit::get_headers(), 2 );

		return $res;
	}

	/**
	 * The Tags endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint() . 'automations/hooks';
	}

}

return ( 'WFCO_Ck_Addwebhook' );
