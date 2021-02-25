<?php

class WFCO_GR_Get_Contact_Id_By_Email extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'api_key', 'email', 'list_id', 'create_if_not_exists' );
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

		BWFCO_GetResponse::set_headers( $this->data['api_key'] );

		$res = $this->make_wp_requests( $this->get_endpoint(), array(), BWFCO_GetResponse::get_headers(), BWF_CO::$GET );
		if ( ! is_array( $res['body'] ) || isset( $res['body']['code'] ) || ( isset( $res['response'] ) && 200 !== absint( $res['response'] ) ) ) {
			return $res;
		}

		if ( ! empty( $res['body'] ) ) {
			return $res['body'][0]['contactId'];
		}

		if ( true !== $this->data['create_if_not_exists'] ) {
			return false;
		}

		$user = get_user_by_email( $this->data['email'] );

		/** @var WFCO_GR_Create_Contact $call */
		$call             = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_gr_create_contact' );
		$new_contact_data = array(
			'api_key' => $this->data['api_key'],
			'list_id' => $this->data['list_id'],
			'email'   => $this->data['email']
		);

		$contact_name = $user instanceof WP_User ? $user->first_name . ' ' . $user->last_name : '';
		if ( ! empty( str_replace( ' ', '', $contact_name ) ) ) {
			$new_contact_data ['name'] = $contact_name;
		}
		$call->set_data( $new_contact_data );

		$create_result = $call->process();
		if ( ( is_array( $create_result['body'] ) && isset( $create_result['body']['code'] ) ) || ( isset( $create_result['response'] ) && 200 !== absint( $create_result['response'] ) ) ) {
			return $create_result;
		}

		/**  Fetch contact again, because on creation, no GR Contact ID returned: http://imgwxl.com/ra/2020-12-42-39.png */
		$res = $this->make_wp_requests( $this->get_endpoint(), array(), BWFCO_GetResponse::get_headers(), BWF_CO::$GET );
		if ( ! is_array( $res['body'] ) || isset( $res['body']['code'] ) || ( isset( $res['response'] ) && 200 !== absint( $res['response'] ) ) ) {
			return $res;
		}

		if ( ! empty( $res['body'] ) ) {
			return $res['body'][0]['contactId'];
		}

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_GetResponse::get_endpoint() . 'contacts?query[email]=' . $this->data['email'] . '&query[campaignId]=' . $this->data['list_id'];
	}

}

return 'WFCO_GR_Get_Contact_Id_By_Email';
