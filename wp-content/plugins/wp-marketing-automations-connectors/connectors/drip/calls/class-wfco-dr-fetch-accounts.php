<?php

class WFCO_DR_Fetch_Accounts extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'access_token' );
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

			return $this->get_accounts();
		} else {
			return $this->show_fields_error();
		}
	}

	public function get_accounts() {
		$params = array();
		$url    = $this->get_endpoint();
		$res    = $this->make_wp_requests( $url, $params, BWFCO_Drip::get_headers() );

		if ( is_array( $res ) && isset( $res['accounts'] ) ) {
			$campaigns = $res['accounts'];
		} else {
			$campaigns = $res;
		}

		return $campaigns;
	}

	/**
	 * Endpoint for adding or updating a subscriber in an account.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Drip::get_endpoint() . 'accounts';
	}

}

return 'WFCO_DR_Fetch_Accounts';
