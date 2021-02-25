<?php

class WFCO_AC_Rmv_From_Automation extends WFCO_Call {

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

		return $this->remove_contact_automation();
	}

	/**
	 * Remove Contact contact to an automation.
	 *
	 * @return array|mixed|object|string
	 */
	public function remove_contact_automation() {
		/** get_contact_by_email*/
		$connector = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_ac_get_contact_by_email' );
		$connector->set_data( $this->data );
		$response = $connector->process();

		/** contact not exists */
		if ( isset( $response['response'] ) && 200 === $response['response'] && 0 === count( $response['body']['contacts'] ) ) {
			return $response;
		} else {
			$contact_id = $response['body']['contacts'][0]['id'];
		}

		if ( isset( $contact_id ) && '' !== $contact_id ) {
			$api_action   = 'automation_contact_remove';
			$params_data  = array(
				'api_action'    => $api_action,
				'contact_email' => $this->data['email'],
				'contact_id'    => $contact_id,
				'automation'    => $this->data['a_id'],
			);
			$endpoint_url = $this->get_endpoint( $this->data['api_key'], $this->data['api_url'], $api_action );
			$result       = $this->make_wp_requests( $endpoint_url, $params_data, array(), BWF_CO::$POST );

			return $result;
		}

		return array();
	}

	public function get_endpoint( $api_key, $api_url, $api_action ) {
		return BWFCO_ActiveCampaign::endpoint( $api_key, $api_url, $api_action );
	}

}

return 'WFCO_AC_Rmv_From_Automation';
