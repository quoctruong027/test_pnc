<?php

class WFCO_Ontraport_Get_Contact_ID_By_Email extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'app_id', 'api_key','email' );
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
			'app_id' => $this->data['app_id'],
			'api_key' => $this->data['api_key'],
			'limit'   => 1,
			'minimal' => true
		);
		BWFCO_Ontraport::set_headers($this->data);

		$res = $this->make_wp_requests( $this->get_endpoint(), array(), BWFCO_Ontraport::get_headers(), BWF_CO::$GET );

		if ( isset( $res['body']['data'] ) && isset($res['body']['data']) && ! empty( $res['body']['data']['id'] ) ) {
			return absint(  $res['body']['data']['id']  );
		}

		/** If create_if_not_exists = false and contact does not exists, return 0 (empty Int) */
		if ( 0 === absint( $this->data['create_if_not_exists'] ) ) {
			return 0;
		}
		$user = get_user_by( 'email', $this->data['email'] );

		$call = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_ontraport_create_contact' );

		$params = array('email' =>$this->data['email']);

		// get user firstname and last name if it exists in wordpress database

		if($user instanceof WP_User){
			$params['firstname'] = isset($user->first_name)?$user->first_name:false;
			$params['lastname'] = isset($user->last_name)?$user->last_name:false;
		}
		$params['app_id'] = $this->data['app_id'];
		$params['api_key'] = $this->data['api_key'];
		$call->set_data($params);
		$create_result = $call->process();
		if ( empty( $create_result ) || ( isset( $create_result['response'] ) && 200 !== $create_result['response'] )  || !isset($create_result['body']['data']['id'])) {
			return array(
				'response' => 502,
				'body'     => array( 'Something wrong happened while creating contact.' ),
			);
		}
		return $create_result['body']['data']['id'];
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {

		return BWFCO_Ontraport::get_endpoint().'/object/getByEmail?objectID=0&email='.$this->data['email'];
	}

}

return 'WFCO_Ontraport_Get_Contact_ID_By_Email';
