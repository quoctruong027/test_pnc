<?php

class WFCO_AC_Create_Connection extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'service', 'externalid', 'name', 'logoUrl', 'linkUrl' );
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

		return $this->create_connection();
	}

	/**
	 * Create a single new tag.
	 *
	 * @param string $api_key
	 * @param string $api_url
	 * @param string $email
	 *
	 * @return array|mixed
	 */
	public function create_connection() {
		$api_action  = 'connections';
		$params_data = array(
			'connection' => array(
				'service'    => $this->data['service'],
				'externalid' => $this->data['externalid'],
				'name'       => $this->data['name'],
				'logoUrl'    => $this->data['logoUrl'],
				'linkUrl'    => $this->data['linkUrl'],
			),
		);

		$params_data  = wp_json_encode( $params_data );
		$endpoint_url = BWFCO_ActiveCampaign::get_endpoint_url( $this->data['api_url'], $api_action );
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), BWF_CO::$POST );

		return $result;
	}

}

return 'WFCO_AC_Create_Connection';
