<?php

class WFCO_DR_Rmv_From_Campaign extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'email', 'campaign_id', 'account_id', 'access_token' );
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

		return $this->remove_subscriber_from_campaign();
	}

	/**
	 * Removes a subscriber from the campaign
	 *
	 * campaign_id is required.
	 * subscriber_email is required.
	 *
	 * @return array|mixed|object
	 */
	public function remove_subscriber_from_campaign() {
		$params = array(
			'campaign_id' => $this->data['campaign_id'],
		);
		$url    = $this->get_endpoint() . '/' . $this->data['email'] . '/remove';
		$res    = $this->make_wp_requests( $url, wp_json_encode( $params ), BWFCO_Drip::get_headers(), BWF_CO::$POST );

		return $res;
	}

	/**
	 * Endpoint for adding or updating a subscriber in an account.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Drip::get_endpoint( $this->data['account_id'] ) . 'subscribers';
	}

}

/**
 * Register this call class.
 */
return 'WFCO_DR_Rmv_From_Campaign';
