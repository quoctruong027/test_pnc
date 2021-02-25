<?php

class WFCO_DR_Rmv_From_Workflow extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'email', 'workflow_id', 'account_id', 'access_token' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
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

		BWFCO_Drip::set_headers( $this->data['access_token'] );

		return $this->remove_subscriber_to_campaign();
	}

	/**
	 * remove a subscriber to the campaign.
	 *
	 * subscriber_email is required.
	 * campaign_id is required.
	 * $custom_fields array optional.
	 * $tags array optional.
	 *
	 * @return array|bool
	 */
	public function remove_subscriber_to_campaign() {
		$params = array();
		$url    = $this->get_endpoint() . '/' . $this->data['workflow_id'] . '/subscribers/' . $this->data['email'];
		// The API wants the params to be JSON encoded
		$res = $this->make_wp_requests( $url, $params, BWFCO_Drip::get_headers(), BWF_CO::$DELETE );

		return $res;
	}

	/**
	 * Endpoint for adding or updating a subscriber in an account.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Drip::get_endpoint( $this->data['account_id'] ) . 'workflows';
	}

}

/**
 * Register this call class.
 */
return 'WFCO_DR_Rmv_From_Workflow';
