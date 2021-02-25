<?php

class WFCO_Klaviyo_Update_Profile_fields extends WFCO_Klaviyo_Call {

	private static $ins = null;
	private $person_id = null;

	public function __construct() {
		parent::__construct( [ 'api_key', 'list_id', 'email' ] );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process_klaviyo_call() {
		if ( isset( $this->data['email'] ) && ! is_email( $this->data['email'] ) ) {
			return $this->get_autonami_error( __( 'Email is not valid', 'autonami-automations-connectors' ) );
		}

		$params     = [
			'api_key' => $this->data['api_key'],
			'list_id' => $this->data['list_id'],
			'email'   => $this->data['email']
		];
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Klaviyo_Check_List_Membership $call */
		$call = $connectors->get_call( 'wfco_klaviyo_check_list_membership' );
		$call->set_data( $params );
		$result = $call->process();

		if ( 3 == $result['status'] && ! empty( $result['payload'] ) ) {
			$this->person_id = $result['payload'][0]['id'];
		} else {
			/** @var WFCO_Klaviyo_Add_To_List $call */
			$call = $connectors->get_call( 'wfco_klaviyo_add_to_list' );
			$call->set_data( $params );
			$result = $call->process();
			if ( ! empty( $result['payload'] ) ) {
				$this->person_id = $result['payload'][0]['id'];
			}
		}
		if ( empty( $this->person_id ) ) {
			return $this->get_autonami_error( __( 'Member not found.', 'autonami-automations-connectors' ) );
		}
		$params = [
			'api_key' => $this->data['api_key'],
			'method'  => 'put'
		];
		$params = array_merge( $params, $this->data['profile_fields'] );

		return $this->do_klaviyo_call( $params, BWF_CO::$PUT );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint( $endpoint_var = '' ) {
		return BWFCO_Klaviyo::$api_end_point . 'v1/person/' . $this->person_id;
	}

}

return 'WFCO_Klaviyo_Update_Profile_fields';
