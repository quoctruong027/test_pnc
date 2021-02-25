<?php

class WFCO_DR_Removesubscribertoaccount extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'email', 'account_id', 'access_token' );
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
		if ( $is_required_fields_present ) {
			BWFCO_Drip::set_headers( $this->data['access_token'] );

			return $this->remove_subscriber_from_account( $this->data['email'] );
		} else {
			return $this->show_fields_error();
		}
	}

	/**
	 * Removes a subscriber from a drip account
	 *
	 * @param $subscriber_email
	 *
	 * @return array|mixed|object|string
	 */
	public function remove_subscriber_from_account( $subscriber_email ) {
		$params = array();
		$url    = $this->get_endpoint() . '/' . $subscriber_email;
		$res    = $this->make_wp_requests( $url, $params, BWFCO_Drip::get_headers(), BWF_CO::$DELETE );

		if ( is_array( $res ) && isset( $res['code'] ) && 204 === $res['code'] ) {
			$data = array(
				'code'    => 1,
				'message' => 'Subscriber Successfully Deleted',
			);
		} else {
			$data = $res;
		}

		return $data;
	}

	/**
	 * Endpoint for adding or updating a subscriber in an account.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Drip::get_endpoint( $this->data['account_id'] ) . '/subscribers';
	}

}

/**
 * Register this call class.
 */
return 'WFCO_DR_Removesubscribertoaccount';
