<?php

class WFCO_AC_Get_Contact_List extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url' );
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

		return $this->get_contact_list();
	}

	/**
	 * View a list of contacts.
	 *
	 * @return array
	 */
	public function get_contact_list() {
		$api_action  = 'contact_list';
		$params_data = array(
			'api_action' => 'contact_list',
			'ids'        => 'ALL',
			/**"full"       => 0*/
			/** (set to 1 for ALL data, and 0 for abbreviated) */
		);

		$page                = ( isset( $this->data['page'] ) ) ? $this->data['page'] : 1;
		$full                = ( isset( $this->data['full'] ) ) ? $this->data['full'] : 0;
		$params_data['page'] = $page;
		$params_data['full'] = $full;
		$endpoint_url        = $this->get_endpoint( $this->data['api_key'], $this->data['api_url'], $api_action );
		$result              = $this->make_wp_requests( $endpoint_url, $params_data, array(), BWF_CO::$GET );

		return $result;

	}

	public function get_endpoint( $api_key, $api_url, $api_action ) {
		return BWFCO_ActiveCampaign::endpoint( $api_key, $api_url, $api_action );
	}

}

return 'WFCO_AC_Get_Contact_List';
