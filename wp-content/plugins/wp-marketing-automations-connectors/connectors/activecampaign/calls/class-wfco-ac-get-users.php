<?php

class WFCO_AC_Get_Users extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url' );
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

		BWFCO_ActiveCampaign::set_headers( $this->data['api_key'] );

		return $this->get_users();
	}

	/**
	 * View multiple mailing lists.
	 *
	 * @return array
	 */
	public function get_users() {
		$api_action  = 'users';
		$params_data = array(
			'limit'  => $this->data['limit'],
			'offset' => $this->data['offset'],
		);

		$endpoint_url = BWFCO_ActiveCampaign::get_endpoint_url( $this->data['api_url'], $api_action );
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), BWF_CO::$GET );

		return $result;

	}

}

return 'WFCO_AC_Get_Users';
