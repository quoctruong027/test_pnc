<?php

class WFCO_DR_Add_Tags extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'email', 'access_token', 'account_id', 'tags' );
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

		return $this->add_update_subscriber_to_account();
	}

	/**
	 * Add tags to a subscriber in the account. If subscriber with email is not made, then a new subscriber is made
	 * else the subscriber is updated.
	 *
	 * subscriber_email is required.
	 * array tags is required.
	 *
	 * @return array|bool
	 */
	public function add_update_subscriber_to_account() {
		$params = array(
			'email' => $this->data['email'],
			'tags'  => $this->data['tags'], // API wants this as an array
		);

		$url = $this->get_endpoint();
		// The API wants the params to be JSON encoded
		$req_params = array(
			'subscribers' => array( $params ),
		);
		$res        = $this->make_wp_requests( $url, wp_json_encode( $req_params ), BWFCO_Drip::get_headers(), BWF_CO::$POST );

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

return 'WFCO_DR_Add_Tags';
