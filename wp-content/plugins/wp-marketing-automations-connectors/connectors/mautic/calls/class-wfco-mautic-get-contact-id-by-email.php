<?php

class WFCO_Mautic_Get_Contact_ID_By_Email extends WFCO_Mautic_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'site_url', 'access_token', 'email', 'create_if_not_exists' );
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
			'limit'   => 1,
			'minimal' => true
		);

		$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Mautic::get_headers(), BWF_CO::$GET );

		if ( is_array( $res ) && isset( $res['response'] ) && 200 !== absint( $res['response'] ) ) {
			return $res;
		}

		if ( isset( $res['body']['contacts'] ) && ! empty( $res['body']['contacts'] ) ) {
			return absint( reset( $res['body']['contacts'] )['id'] );
		}

		/** If create_if_not_exists = false and contact does not exists, return 0 (empty Int) */
		if ( 0 === absint( $this->data['create_if_not_exists'] ) ) {
			return 0;
		}

		$user = get_user_by( 'email', $this->data['email'] );

		$call = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_mautic_create_contact' );
		$call->set_data( array(
			'access_token' => $this->data['access_token'],
			'site_url'     => $this->data['site_url'],
			'email'        => $this->data['email'],
			'first_name'   => $user instanceof WP_User ? $user->first_name : '',
			'last_name'    => $user instanceof WP_User ? $user->last_name : '',
		) );

		$create_result = $call->process();
		if ( empty( $create_result ) || ( isset( $create_result['response'] ) && 200 !== $create_result['response'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Something wrong happened while creating contact.' ),
			);
		}

		return $create_result['body']['contact']['id'];
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

return 'WFCO_Mautic_Get_Contact_ID_By_Email';
