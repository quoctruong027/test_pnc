<?php

class WFCO_Mautic_Sub_Points extends WFCO_Mautic_Call {

	private static $ins = null;

	private $contact_id = null;
	private $points = 0;

	public function __construct() {

		$this->required_fields = array( 'site_url', 'access_token', 'email', 'points' );
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

		if ( empty( intval( $this->data['points'] ) ) || intval( $this->data['points'] ) <= 0 ) {
			return array(
				'response' => 502,
				'body'     => array( 'Points data is not valid' ),
			);
		}

		$contact = WFCO_Mautic_Common::get_contact_id_by_email( $this->data['access_token'], $this->data['site_url'], $this->data['email'], true );

		// Error in getting contact
		if ( is_array( $contact ) && isset( $contact['response'] ) && 200 !== absint( $contact['response'] ) ) {
			return $contact;
		}

		$this->contact_id = $contact;
		$this->points     = $this->data['points'];

		$params = array(
			'access_token' => $this->data['access_token'],
		);

		if ( isset( $this->data['event_name'] ) && ! empty( $this->data['event_name'] ) ) {
			$params['eventName'] = $this->data['event_name'];
		}

		if ( isset( $this->data['action_name'] ) && ! empty( $this->data['action_name'] ) ) {
			$params['actionName'] = $this->data['action_name'];
		}

		$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Mautic::get_headers(), BWF_CO::$POST );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return $this->data['site_url'] . '/api/contacts/' . $this->contact_id . '/points/minus/' . $this->points;
	}

}

return 'WFCO_Mautic_Sub_Points';
