<?php

class WFCO_Keap_Get_Contact_Tags extends WFCO_Keap_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'site_url', 'access_token', 'email' );
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

		$params = array(
			'search'  => $this->data['email'],
			'orderBy' => 'email',
			'limit'   => 1
		);

		$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Keap::get_headers(), BWF_CO::$GET );

		if ( is_array( $res ) && isset( $res['response'] ) && 200 !== absint( $res['response'] ) ) {
			return $res;
		}

		if ( isset( $res['body']['contacts'] ) && empty( $res['body']['contacts'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Contact not found' ),
			);
		}

		return reset( $res['body']['contacts'] )['tags'];
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return $this->data['site_url'] . '/api/contacts?access_token=' . $this->data['access_token'];
	}

}

return 'WFCO_Keap_Get_Contact_Tags';
