<?php

class WFCO_Ck_Deletewebhook extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_secret', 'webhook_id' );
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
		$params = array(
			'api_secret' => $this->data['api_secret'],
		);

		$url = $this->get_endpoint() . '/' . $this->data['webhook_id'];
		$res = $this->make_wp_requests( $url, $params, array(), 3 );

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

return ( 'WFCO_Ck_Deletewebhook' );
