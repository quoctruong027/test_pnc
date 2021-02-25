<?php

class WFCO_AC_Update_Deal extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'deal_id', 'email', 'deal_value', 'pipeline_id', 'stage_id', 'owner_id' );
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

		return $this->update_deal();
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
	public function update_deal() {

		// Get contact by email
		$api_action   = 'contacts';
		$params_data  = array();
		$endpoint_url = BWFCO_ActiveCampaign::get_endpoint_url( $this->data['api_url'], $api_action );
		$endpoint_url = $endpoint_url . '?filters[email]=' . $this->data['email'];
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), BWF_CO::$GET );

		// Contact is found, fetch its id
		if ( is_array( $result ) && isset( $result['response'] ) && 200 === $result['response'] && is_array( $result['body']['contacts'] ) && count( $result['body']['contacts'] ) > 0 ) {
			$customer_id = $result['body']['contacts'][0]['id'];
		} else { // Contact does not exists
			$result                        = [];
			$result['bwfan_custom_message'] = __( 'Contact does not exists', 'autonami-automations-connectors' );

			return $result;
		}

		// Now create actual deal
		$api_action  = 'deals';
		$params_data = array(
			'deal' => array(
				'group'   => $this->data['pipeline_id'],
				'stage'   => $this->data['stage_id'],
				'contact' => $customer_id,
				'value'   => $this->data['deal_value'],
				'owner'   => $this->data['owner_id'],
			),
		);

		$params_data  = wp_json_encode( $params_data );
		$endpoint_url = BWFCO_ActiveCampaign::get_endpoint_url( $this->data['api_url'], $api_action );
		$endpoint_url = $endpoint_url . '/' . $this->data['deal_id'];
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), BWF_CO::$PUT );

		return $result;
	}

}

return 'WFCO_AC_Update_Deal';
