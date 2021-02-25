<?php

class WFCO_AC_Add_To_Automation extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'email', 'automation_id' );
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

		return $this->add_contact_automation();
	}

	/**
	 * Add new contact to an automation.
	 *
	 * @return array|mixed
	 */
	public function add_contact_automation() {
		/** get_contact_by_email*/
		$connector = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_ac_get_contact_by_email' );
		$connector->set_data( $this->data );
		$response = $connector->process();

		/** contact not exists */
		if ( isset( $response['response'] ) && 200 === $response['response'] && 0 === count( $response['body']['contacts'] ) ) {
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

		// Now add contact to automation
		if ( ! empty( $contact_id ) ) {
			$params_data               = [];
			$params_data['contact_id'] = $contact_id;
			$params_data['automation'] = $this->data['a_id'];
			$api_action                = 'automation_contact_add';
			$endpoint_url              = $this->get_endpoint( $this->data['api_key'], $this->data['api_url'], $api_action );
			$result                    = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), BWF_CO::$POST );

			return $result;
		}

		return $response;
	}

	public function get_endpoint( $api_key, $api_url, $api_action ) {
		return BWFCO_ActiveCampaign::endpoint( $api_key, $api_url, $api_action );
	}

}

return 'WFCO_AC_Add_To_Automation';
