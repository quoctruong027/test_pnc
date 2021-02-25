<?php

class WFCO_AC_Update_CustomFields extends WFCO_Call {

	private static $instance = null;
	public $default_fields = array(
		'first_name' => 'First Name',
		'last_name'  => 'Last Name',
		'phone'      => 'Phone',
		'orgname'    => 'Organization name',
	);

	public function __construct() {

		$this->required_fields = array( 'api_key', 'api_url', 'email' );
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

		return $this->update_contact_custom_fields();
	}

	/**
	 * Update contact custom fields.
	 *
	 * @return array|mixed
	 */
	public function update_contact_custom_fields() {

		/** get_contact_by_email*/
		$connector = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_ac_get_contact_by_email' );
		$connector->set_data( $this->data );
		$response = $connector->process();

		if ( isset( $response['response'] ) && 200 !== $response['response'] ) {
			return array(
				'status'  => 4,
				'message' => __( 'Unable to get contact. Response Code: ', 'autonami-automations-connectors' ) . $response['response']
			);
		}

		/** contact not exists */
		if ( 0 === count( $response['body']['contacts'] ) ) {
			/** create_new_contact*/
			$connector = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_ac_create_contact' );
			$connector->set_data( $this->data );
			$response1 = $connector->process();
			if ( isset( $response1['response'] ) && 200 === $response1['response'] && isset( $response1['body']['contact']['id'] ) ) {
				$contact_id = $response1['body']['contact']['id'];
			} else {
				return $response1;
			}
		} else {
			$contact_id = $response['body']['contacts'][0]['id'];
		}

		// Now update custom fields to contact
		if ( ! empty( $contact_id ) ) {

			$default_fields_updated = false;

			if ( ! empty( $this->data['have_default_field'] ) ) {
				// Run update contact call
				$update_contact = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_ac_update_contact' );
				$update_contact->set_data( $this->data );
				$result = $update_contact->process();

				if ( 200 !== $result['response'] && 201 !== $result['response'] ) {
					return array(
						'status'  => 4,
						'message' => __( 'Unable to update contact with default fields. Response Code: ', 'autonami-automations-connectors' ) . $response['response']
					);
				}
				$default_fields_updated = true;
			}

			$custom_fields = $this->data['custom_fields'];
			if ( ! is_array( $custom_fields ) || 0 === count( $custom_fields ) ) { // there were only firstname or lastname to update, so send true status which means custom fields are successfully update
				if ( true === $default_fields_updated ) {
					return array(
						'status'  => 3,
						'message' => __( 'Default Contact Fields updated successfully', 'wp-marketing-automations' ),
					);
				} else {
					return array(
						'status'  => 4,
						'message' => __( 'Invalid Custom Fields data provided', 'wp-marketing-automations' ),
					);
				}
			}

			$result     = '';
			$api_action = 'fieldValues';
			foreach ( $custom_fields as $field_id => $field_value ) {
				$params_data = array(
					'fieldValue' => array(
						'contact' => $contact_id,
						'field'   => $field_id,
						'value'   => $field_value,
					),
				);

				$params_data  = wp_json_encode( $params_data );
				$endpoint_url = BWFCO_ActiveCampaign::get_endpoint_url( $this->data['api_url'], $api_action );
				$result       = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), BWF_CO::$POST );
			}

			return $result;
		}

		return $response;
	}

}

return 'WFCO_AC_Update_CustomFields';
