<?php

class BWFAN_Twilio_SMS_Zip extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'twilio_sms_zip';
		$this->tag_description = __( 'Twilio SMS Sender Zip', 'autonami-automations' );
		add_shortcode( 'bwfan_twilio_sms_zip', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		$twilio_data = BWFAN_Merge_Tag_Loader::get_data( 'twilio_data' );
		$value       = isset( $twilio_data['FromZip'] ) && ! empty( $twilio_data['FromZip'] ) ? $twilio_data['FromZip'] : '';

		return $this->parse_shortcode_output( $value, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( '32250', 'autonami-automations' );
	}


}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'twilio_sms', 'BWFAN_Twilio_SMS_Zip' );
