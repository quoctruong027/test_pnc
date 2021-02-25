<?php

class WFCO_Mautic_Update_Contact_Fields extends WFCO_Mautic_Call {

	private static $ins = null;

	private $contact_id = null;
	private $site_url = null;

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

		$contact = WFCO_Mautic_Common::get_contact_id_by_email( $this->data['access_token'], $this->data['site_url'], $this->data['email'], true );

		// Error in getting contact
		if ( is_array( $contact ) && isset( $contact['response'] ) && 200 !== absint( $contact['response'] ) ) {
			return $contact;
		}

		$this->contact_id = $contact;
		$this->site_url   = $this->data['site_url'];
		unset( $this->data['site_url'] );

		$res = $this->make_wp_requests( $this->get_endpoint(), $this->data, BWFCO_Mautic::get_headers(), BWF_CO::$PATCH );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return $this->site_url . '/api/contacts/' . absint( $this->contact_id ) . '/edit';
	}

}

return 'WFCO_Mautic_Update_Contact_Fields';
