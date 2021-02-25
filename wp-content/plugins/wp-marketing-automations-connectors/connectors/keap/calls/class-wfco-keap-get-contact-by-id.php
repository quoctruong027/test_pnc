<?php

class WFCO_Keap_Get_Contact_By_ID extends WFCO_Keap_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'access_token', 'contact_id' );
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

		BWFCO_Keap::set_headers( $this->data['access_token'] );

		$res = $this->make_wp_requests( $this->get_endpoint(), array(), BWFCO_Keap::get_headers(), BWF_CO::$GET );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Keap::get_endpoint() . 'contacts/' . absint( $this->data['contact_id'] );
	}

}

return 'WFCO_Keap_Get_Contact_By_ID';
