<?php

class WFCO_Mautic_Create_Segment extends WFCO_Mautic_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'site_url', 'access_token', 'name' );
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

		$params = array(
			'access_token' => $this->data['access_token'],
			'name'         => $this->data['name'],
			'alias'        => isset( $this->data['alias'] ) ? $this->data['alias'] : '',
			'description'  => isset( $this->data['description'] ) ? $this->data['description'] : '',
			'isPublished'  => isset( $this->data['isPublished'] ) && true === $this->data['isPublished'] ? 1 : 0,
		);

		$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Mautic::get_headers(), BWF_CO::$POST );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return $this->data['site_url'] . '/api/segments/new';
	}

}

return 'WFCO_Mautic_Create_Segment';
