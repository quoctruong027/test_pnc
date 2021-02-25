<?php

class WFCO_AC_Update_Deal_CustomFields extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'custom_fields', 'deal_id' );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_endpoint( $api_key, $api_url, $api_action ) {
		return BWFCO_ActiveCampaign::endpoint( $api_key, $api_url, $api_action );
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

		return $this->update_deal_custom_fields();
	}

	/**
	 * Update contact custom fields.
	 *
	 * @return array|mixed
	 */
	public function update_deal_custom_fields() {
		$custom_fields = $this->data['custom_fields'];
		if ( ! is_array( $custom_fields ) || 0 === count( $custom_fields ) ) { // there were only firstname or lastname to update, so send true status which means custom fields are successfully update
			return array(
				'status'  => 4,
				'message' => __( 'Invalid Custom Fields', 'autonami-automations-connectors' )
			);
		}

		$result     = array();
		$failed     = array();
		$api_action = 'dealCustomFieldData';
		foreach ( $custom_fields as $field_id => $field_value ) {
			$params_data = array(
				'dealCustomFieldDatum' => array(
					'dealId'        => $this->data['deal_id'],
					'customFieldId' => $field_id,
					'fieldValue'    => $field_value,
				),
			);

			$params_data  = wp_json_encode( $params_data );
			$endpoint_url = BWFCO_ActiveCampaign::get_endpoint_url( $this->data['api_url'], $api_action );
			$response     = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), BWF_CO::$POST );
			if ( 200 === $response['response'] ) {
				$result[] = $field_id;
			} else {
				$failed[] = $field_id;
			}
		}

		return array(
			'updated' => $result,
			'failed'  => $failed
		);
	}

}

return 'WFCO_AC_Update_Deal_CustomFields';
