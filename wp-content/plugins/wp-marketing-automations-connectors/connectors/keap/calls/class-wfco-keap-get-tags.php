<?php

class WFCO_Keap_Get_Tags extends WFCO_Keap_Call {

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
		if ( isset( $this->data['next_url'] ) && wc_is_valid_url( $this->data['next_url'] ) ) {
			return $this->data['next_url'];
		} else if ( isset( $this->data['prev_url'] ) && wc_is_valid_url( $this->data['prev_url'] ) ) {
			return $this->data['prev_url'];
		}

		$search_tag_query = '';
		if ( isset( $this->data['search'] ) && ! empty( $this->data['search'] ) && is_string( $this->data['search'] ) ) {
			$search_tag_query = '?name=' . $this->data['search'];
		}

		return BWFCO_Keap::get_endpoint() . 'tags' . $search_tag_query;
	}

}

return 'WFCO_Keap_Get_Tags';
