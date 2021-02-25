<?php

class WFCO_Ontraport_Get_Contact_By_ID extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'app_id', 'api_key', 'contact_id' );
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
		BWFCO_Ontraport::set_headers($this->data);
		$res = $this->make_wp_requests( $this->get_endpoint(), array(), BWFCO_Ontraport::get_headers(), BWF_CO::$GET );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Ontraport::get_endpoint().'/Contact?id='.$this->data['contact_id'];

	}

}

return 'WFCO_Ontraport_Get_Contact_By_ID';
