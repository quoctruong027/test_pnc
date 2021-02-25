<?php

class WFCO_AC_Remove_Contact extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'contact_id' );
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

		return $this->remove_contact();
	}

	/**
	 * Delete existing contact.
	 *
	 * @param string $api_key
	 * @param string $api_url
	 * @param int $contact_id
	 *
	 * @return array|mixed
	 */
	public function remove_contact( $api_key = '', $api_url = '', $contact_id = 1 ) {
		if ( '' !== $api_key && '' !== $api_url && '' !== $contact_id ) {
			$params_data   = array(
				'api_action' => 'contact_delete',
				'id'         => $contact_id,
			);
			$endpoint_url  = $this->get_endpoint( $this->data['api_key'], $this->data['api_url'], $this->data['api_action'] );
			$result        = $this->make_wp_requests( $endpoint_url, $params_data, array(), BWF_CO::$GET );
			$custom_fields = maybe_unserialize( $result );
			if ( is_array( $custom_fields ) && count( $custom_fields ) > 0 && 1 === intval( $custom_fields['result_code'] ) ) {
				return $custom_fields;
			} else {
				return null;
			}
		}

		return null;
	}

	public function get_endpoint( $api_key, $api_url, $api_action ) {
		return BWFCO_ActiveCampaign::endpoint( $api_key, $api_url, $api_action );
	}

}

return 'WFCO_AC_Remove_Contact';
