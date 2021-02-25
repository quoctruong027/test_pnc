<?php

class WFCO_AC_Delete_Hook extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'webhook_id' );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		return $this->delete_hook();
	}

	/**
	 *  Contact tag added hook.
	 *
	 * @return array|mixed
	 */
	public function delete_hook() {
		$api_action   = 'webhook_delete';
		$params_data  = array(
			'api_action' => 'webhook_delete',
			'api_key'    => $this->data['api_key'],
			'id'         => $this->data['webhook_id'],
		);
		$endpoint_url = $this->get_endpoint( $this->data['api_key'], $this->data['api_url'], $api_action );
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, array(), BWF_CO::$POST );

		return $result;
	}

	public function get_endpoint( $api_key, $api_url, $api_action ) {
		return BWFCO_ActiveCampaign::endpoint( $api_key, $api_url, $api_action );
	}

}

return 'WFCO_AC_Delete_Hook';
