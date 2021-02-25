<?php

class WFCO_Twilio_Send_SMS extends WFCO_Call {

	private static $instance = null;
	private $api_end_point = null;

	public function __construct() {

		$this->required_fields = array( 'account_sid', 'auth_token', 'twilio_no', 'sms_body', 'phone' );
		$this->api_end_point   = 'https://api.twilio.com/2010-04-01/Accounts/';
	}

	/**
	 * @return WFCO_Twilio_Send_SMS|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}
		$media_urls = ! empty( $this->data['mediaUrl'] ) ? $this->data['mediaUrl'] : '';
		$url        = $this->api_end_point . $this->data['account_sid'] . '/Messages.json';
		$headers    = array(
			'Content-Type'  => 'application/x-www-form-urlencoded',
			'Authorization' => 'Basic ' . base64_encode( $this->data['account_sid'] . ':' . $this->data['auth_token'] ),
		);

		$phone_numbers = trim( stripslashes( $this->data['phone'] ) );
		$phone_numbers = explode( ',', $phone_numbers );

		$this->data['sms_body'] = BWFAN_Common::decode_merge_tags( $this->data['sms_body'] );
		$this->data['sms_body'] = apply_filters( 'bwfan_modify_send_sms_body', $this->data['sms_body'], $this->data );

		$req_params = array(
			'Body' => $this->data['sms_body'],
			'From' => $this->data['twilio_no'],
		);

		foreach ( $phone_numbers as $phone ) {
			$req_params['To'] = $phone;

			/** User 2 digit country code passed */
			if ( isset( $this->data['country_code'] ) && ! empty( $this->data['country_code'] ) ) {
				$req_params['To'] = Phone_Numbers::add_country_code( $phone, $this->data['country_code'] );
			}

			/** Filter hook to modify to mobile number per event */
			$req_params['To'] = apply_filters( 'bwfan_modify_send_sms_to', $req_params['To'], $this->data );

			if ( ! empty( $media_urls ) ) {
				$req_params['MediaUrl'] = $media_urls;

				$res = $this->make_wp_requests( $url, $req_params, $headers, BWF_CO::$POST );
				continue;
			}
			$res = $this->make_wp_requests( $url, $req_params, $headers, BWF_CO::$POST );
		}

		return $res;
	}


}

return 'WFCO_Twilio_Send_SMS';
