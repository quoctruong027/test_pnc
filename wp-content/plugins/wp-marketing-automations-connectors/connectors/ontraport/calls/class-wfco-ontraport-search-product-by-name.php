<?php

class WFCO_Ontraport_Search_Product_By_Name extends WFCO_Call {

	private static $ins = null;
	private $site_url = null;

	public function __construct() {

		$this->required_fields = array( 'app_id', 'api_key', 'name' );
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

		BWFCO_Ontraport::set_headers( $this->data );
		$query  = '[{ "field":{"field":"name"}, "op":"=", "value":{"value":"' . $this->data['name'] . '"} }]';

		return $this->make_wp_requests( $this->get_endpoint( $query ), array(), BWFCO_Ontraport::get_headers(), BWF_CO::$GET );
	}

	/**
	 * Return the endpoint.
	 *
	 * @param string $query
	 *
	 * @return string
	 */
	public function get_endpoint( $query ) {
		return BWFCO_Ontraport::get_endpoint() . '/Products?condition=' . urlencode( $query );
	}

}

return 'WFCO_Ontraport_Search_Product_By_Name';
