<?php

class WFCO_Ontraport_Add_To_Campaign extends WFCO_Call {

	private static $ins = null;

	private $contact_id = null;
	private $campaign_id = null;

	public function __construct() {

		$this->required_fields = array( 'app_id', 'api_key', 'email', 'campaign_id' );
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

		$contact = WFCO_Ontraport_Common::get_contact_id_by_email( $this->data['app_id'], $this->data['api_key'], $this->data['email'], true );

		// Error in getting contact
		if ( is_array( $contact ) && isset( $contact['response'] ) && 200 !== absint( $contact['response'] ) ) {
			return $contact;
		}

		$this->contact_id  = $contact;
		$this->campaign_id = $this->data['campaign_id'];

		BWFCO_Ontraport::set_headers( $this->data );

		$params = array(
			'objectID' => 0,
			'add_list' => $this->campaign_id,
			'ids'      => $this->contact_id,
			'sub_type' => 'Campaign',
		);

		return $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Ontraport::get_headers(), BWF_CO::$PUT );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Ontraport::get_endpoint() . '/objects/subscribe';
	}

}

return 'WFCO_Ontraport_Add_To_Campaign';
