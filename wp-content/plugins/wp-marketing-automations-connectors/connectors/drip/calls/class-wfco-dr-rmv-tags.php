<?php

class WFCO_DR_Rmv_Tags extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'access_token', 'account_id', 'tags', 'email' );
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
		$connector = WFCO_Load_Connectors::get_instance();

		// First get subscriber details. If subscriber not present then do nothing.
		$call_instance = $connector->get_call( 'wfco_dr_getsubscriber' );
		if ( ! is_null( $call_instance ) ) {
			$call_instance->set_data( $this->data );
			$result = $call_instance->process();
			if ( is_array( $result ) && isset( $result['body']['errors'] ) ) {
				return $result;
			}
		}

		return $this->add_update_subscriber_to_account();
	}

	/**
	 * Add tags to a subscriber in the account. If subscriber with email is not made, then a new subscriber is made
	 * else the subscriber is updated.
	 *
	 * @param $subscriber_email
	 * @param array $tags
	 *
	 * @return array|bool
	 */
	public function add_update_subscriber_to_account() {
		$params = array(
			'email'       => $this->data['email'],
			'remove_tags' => $this->data['tags'], // API wants this as an array
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

/**
 * Register this call class.
 */
return 'WFCO_DR_Rmv_Tags';
