<?php

class WFCO_DR_Fetch_Tags extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'access_token', 'account_id' );
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

		return $this->get_tags();
	}

	/**
	 * Returns all the tags of an account.
	 *
	 * Account id is required.
	 * subscriber_email is optional. it will return tags of a subscriber.
	 *
	 * @return array|bool
	 */
	public function get_tags() {
		$params = array();
		if ( isset( $this->data['email'] ) && '' !== $this->data['email'] ) {
			$params['email'] = $this->data['email'];
		}
		$url = $this->get_endpoint();
		$res = $this->make_wp_requests( $url, $params, BWFCO_Drip::get_headers() );

		return $res;
	}

	/**
	 * Endpoint for adding or updating a subscriber in an account.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Drip::get_endpoint( $this->data['account_id'] ) . 'tags';
	}

}

/**
 * Register this call class.
 */
return 'WFCO_DR_Fetch_Tags';
