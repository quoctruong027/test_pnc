<?php

class WFCO_AC_Get_Campaign_List extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'campaigns' );
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

		return $this->get_campaign_list();
	}

	/**
	 * View one or many campaigns.
	 *
	 * @return array|mixed
	 */
	public function get_campaign_list() {
		$api_action  = 'campaign_list';
		$params_data = array(
			'api_action' => 'campaign_list',
			'ids'        => implode( ', ', $this->data['campaigns'] ),
			'full'       => 1, /* (set to 1 for ALL data, and 0 for abbreviated) */
		);

		$endpoint_url = $this->get_endpoint( $this->data['api_key'], $this->data['api_url'], $api_action );
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, array(), BWF_CO::$GET );

		return $result;

	}

	public function get_endpoint( $api_key, $api_url, $api_action ) {
		return BWFCO_ActiveCampaign::endpoint( $api_key, $api_url, $api_action );
	}

}

return 'WFCO_AC_Get_Campaign_List';
