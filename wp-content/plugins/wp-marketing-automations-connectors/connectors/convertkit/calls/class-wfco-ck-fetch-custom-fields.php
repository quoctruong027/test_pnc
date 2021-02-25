<?php

class WFCO_Ck_Fetch_Custom_fields extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_secret' );
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

		return $this->get_custom_fields();
	}

	/**
	 * Returns all the custom fields for an account.
	 *
	 * Account id is required.
	 * @return array|bool
	 * @throws Exception
	 */
	public function get_custom_fields() {
		$params = array(
			'api_secret' => $this->data['api_secret'],
		);

		$url = $this->get_endpoint();
		$res = $this->make_wp_requests( $url, $params );

		return $res;
	}

	/**
	 * Endpoint for adding or updating a subscriber in an account.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint() . 'custom_fields';
	}

}

/**
 * Register this call class.
 */
return ( 'WFCO_Ck_Fetch_Custom_fields' );
