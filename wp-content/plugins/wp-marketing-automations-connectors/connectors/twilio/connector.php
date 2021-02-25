<?php

class  BWFCO_Twilio extends BWF_CO {

	private static $ins = null;
	public $is_setting = true;

	public function __construct() {
		$this->dir               = __DIR__;
		$this->autonami_int_slug = 'twilio_integration';
		$this->connector_url     = WFCO_AUTONAMI_CONNECTORS_PLUGIN_URL . '/connectors/twilio';
		$this->keys_to_track     = [
			'account_sid',
			'auth_token',
			'twilio_no',
		];
		$this->form_req_keys     = [
			'account_sid',
			'auth_token',
			'twilio_no',
		];

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
		$this->include_files();
	}

	public function include_files() {
		include_once __DIR__ . '/includes/class-bwfan-twilio-webook-setup.php';
		include_once __DIR__ . '/includes/class-bwfan-phone-number.php';
	}

	/**
	 * @return BWFCO_Twilio|null
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_twilio'] = array(
			'name'            => 'Twilio',
			'desc'            => __( 'Engage your customers via SMS, a marketing channel with a high engagement rate.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_Twilio',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}

	/**
	 * This function connects to the automation and fetch the data required for the actions on automations screen to work properly.
	 *
	 * @param $posted_data
	 *
	 * @return array|int
	 */
	public function get_api_data( $posted_data ) {
		$load_connector = WFCO_Load_Connectors::get_instance();
		$call_class     = $load_connector->get_call( 'wfco_twilio_oauth_check' );

		$resp_array = array(
			'api_data' => [],
			'status'   => 'failed',
			'message'  => __( 'There was problem authenticating your account. Confirm entered details.', 'autonami-automations-connectors' ),
		);

		if ( is_null( $call_class ) ) {
			return $resp_array;

		}

		$data = array(
			'account_sid' => isset( $posted_data['account_sid'] ) ? $posted_data['account_sid'] : '',
			'auth_token'  => isset( $posted_data['auth_token'] ) ? $posted_data['auth_token'] : '',
		);

		$call_class->set_data( $data );
		$ac_status = $call_class->process();

		if ( is_array( $ac_status ) && 200 === $ac_status['response'] ) {
			$response                            = [];
			$response['status']                  = 'success';
			$response['api_data']['account_sid'] = $posted_data['account_sid'];
			$response['api_data']['auth_token']  = $posted_data['auth_token'];
			$response['api_data']['twilio_no']   = $posted_data['twilio_no'];

			return $response;

		} else {
			$resp_array['status']  = 'failed';
			$resp_array['message'] = isset( $ac_status['body']['message'] ) ? $ac_status['body']['message'] : __( 'Undefined Api Error', 'autonami-automations-connectors' );

			return $resp_array;
		}
	}


}

WFCO_Load_Connectors::register( 'BWFCO_Twilio' );
