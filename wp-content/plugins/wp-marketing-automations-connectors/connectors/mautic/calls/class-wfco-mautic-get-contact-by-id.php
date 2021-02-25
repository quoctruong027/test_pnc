<?php

class WFCO_Mautic_Get_Contact_By_ID extends WFCO_Mautic_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'site_url', 'access_token', 'contact_id' );
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

		$res = $this->make_wp_requests( $this->get_endpoint(), array(), BWFCO_Mautic::get_headers(), BWF_CO::$GET );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return $this->data['site_url'] . '/api/contacts/' . absint( $this->data['contact_id'] ) . '?access_token=' . $this->data['access_token'];
	}

}

return 'WFCO_Mautic_Get_Contact_By_ID';
