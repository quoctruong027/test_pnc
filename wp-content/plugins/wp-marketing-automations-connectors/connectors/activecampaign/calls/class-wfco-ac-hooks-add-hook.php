<?php

class WFCO_AC_Add_Hook extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'hook_url', 'name', 'event' );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		return $this->add_hook();
	}

	/**
	 *  contact added hook.
	 *
	 * @return array|mixed
	 */
	public function add_hook() {
		$api_action  = 'webhook_add';
		$params_data = array(
			'api_action' => 'webhook_add',
			'api_key'    => $this->data['api_key'],
			'name'       => $this->data['name'],
			'url'        => $this->data['hook_url'],
			'action[]'   => $this->data['event'],
			'init[0]'    => 'public',
			'init[1]'    => 'admin',
			'init[2]'    => 'api',
		);
		if ( 'subscribe' !== $this->data['event'] ) {
			$params_data['init[3]'] = 'system';
		}
		$endpoint_url = $this->get_endpoint( $this->data['api_key'], $this->data['api_url'], $api_action );
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, array(), BWF_CO::$POST );

		return $result;
	}

	public function get_endpoint( $api_key, $api_url, $api_action ) {
		return BWFCO_ActiveCampaign::endpoint( $api_key, $api_url, $api_action );
	}

}

return 'WFCO_AC_Add_Hook';
