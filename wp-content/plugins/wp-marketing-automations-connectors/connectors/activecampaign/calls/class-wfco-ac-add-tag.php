<?php

class WFCO_AC_Add_Tag extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'email', 'tags' );
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

		return $this->add_contact_tags();
	}

	/**
	 * Add new tags to a contact.
	 *
	 * @return array|mixed
	 */
	public function add_contact_tags() {
		$contact_id = '';

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

		// Now add the tags to contact
		if ( ! empty( $contact_id ) ) {
			$result     = '';
			$api_action = 'contactTags';
			foreach ( $this->data['tags'] as $tag_id ) {
				$params_data  = array(
					'contactTag' => array(
						'contact' => $contact_id,
						'tag'     => $tag_id,
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

return 'WFCO_AC_Add_Tag';
