<?php

class WFCO_AC_Contact_Sync extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'email' );
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

		return $this->contact_sync();
	}

	/**
	 * Sync a contact.
	 *
	 * @return array|mixed
	 */
	public function contact_sync() {
		$params_data = $this->data;
		if ( isset( $params_data['tags'] ) && is_array( $params_data['tags'] ) && count( $params_data['tags'] ) > 0 ) {
			$params_data['tags'] = implode( ', ', $params_data['tags'] );
		}
		if ( isset( $params_data['custom_fields'] ) && is_array( $params_data['custom_fields'] ) && count( $params_data['custom_fields'] ) > 0 ) {
			$custom_fields = $params_data['custom_fields'];
			foreach ( $custom_fields as $field_key => $field_value ) {
				$field_key                 = 'field[' . $field_key . ',0]';
				$params_data[ $field_key ] = $field_value;
			}
			unset( $params_data['custom_fields'] );
		}

		$api_action   = 'contact_sync';
		$endpoint_url = $this->get_endpoint( $this->data['api_key'], $this->data['api_url'], $api_action );
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, array(), BWF_CO::$POST );

		return $result;
	}

	public function get_endpoint( $api_key, $api_url, $api_action ) {
		return BWFCO_ActiveCampaign::endpoint( $api_key, $api_url, $api_action );
	}

}

return 'WFCO_AC_Contact_Sync';
