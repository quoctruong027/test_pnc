<?php

class WFCO_GR_Search_Contacts_By_Email extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'api_key', 'email' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		if ( ! is_email( $this->data['email'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Email is not valid' ),
			);
		}

		BWFCO_GetResponse::set_headers( $this->data['api_key'] );

		$res = $this->make_wp_requests( $this->get_endpoint(), array(), BWFCO_GetResponse::get_headers(), BWF_CO::$GET );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_GetResponse::get_endpoint() . 'contacts?query[email]=' . $this->data['email'];
	}

}

return 'WFCO_GR_Search_Contacts_By_Email';
