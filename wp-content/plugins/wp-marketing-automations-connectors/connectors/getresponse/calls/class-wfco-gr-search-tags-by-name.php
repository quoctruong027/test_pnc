<?php

class WFCO_GR_Search_Tags_By_Name extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'api_key','tags_name' );
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
		return BWFCO_GetResponse::get_endpoint() . 'tags/?query[name]='.$this->data['tags_name'];
	}

}

return 'WFCO_GR_Search_Tags_By_Name';
