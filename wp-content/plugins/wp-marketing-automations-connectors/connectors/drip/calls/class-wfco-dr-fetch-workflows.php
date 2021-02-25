<?php

class WFCO_DR_Fetch_Workflows extends WFCO_Call {

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

		return $this->get_forms();
	}

	/**
	 * Return all the forms related to an account.
	 *
	 * Account id is required.
	 * @return array|bool
	 */
	public function get_forms() {
		$params = array();
		$url    = $this->get_endpoint();
		$res    = $this->make_wp_requests( $url, $params, BWFCO_Drip::get_headers() );

		return $res;
	}

	/**
	 * The forms endpoint to fetch all forms.
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
return 'WFCO_DR_Fetch_Workflows';
