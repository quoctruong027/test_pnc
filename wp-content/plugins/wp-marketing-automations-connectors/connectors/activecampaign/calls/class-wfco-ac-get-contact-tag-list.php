<?php

class WFCO_AC_Get_Contact_Tag_List extends WFCO_Call {

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

		return $this->get_contact_tag_list();
	}

	/**
	 * View tags associated with contact.
	 *
	 * @return array|mixed
	 */
	public function get_contact_tag_list() {
		$api_action   = 'contact_view_email';
		$params_data  = array(
			'api_action' => 'contact_view_email',
			'email'      => $this->data['email'],
		);
		$endpoint_url = $this->get_endpoint( $this->data['api_key'], $this->data['api_url'], $api_action );
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, array(), BWF_CO::$GET );

		if ( is_array( $result['body']['tags'] ) && count( $result['body']['tags'] ) > 0 ) {
			$result['body'] = $result['body']['tags'];
		}

		return $result;

	}

	public function get_endpoint( $api_key, $api_url, $api_action ) {
		return BWFCO_ActiveCampaign::endpoint( $api_key, $api_url, $api_action );
	}

}

return 'WFCO_AC_Get_Contact_Tag_List';
