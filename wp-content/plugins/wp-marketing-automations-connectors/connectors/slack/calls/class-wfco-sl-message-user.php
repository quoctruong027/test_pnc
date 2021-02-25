<?php

class WFCO_SL_Message_User extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'access_token', 'body', 'user' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		BWFCO_Slack::set_headers();

		return $this->chat_post_message();
	}

	/**
	 * Return all the channels_list related to an account.
	 *
	 * Account id is required.
	 * @return array|bool
	 * @throws Exception
	 */
	public function chat_post_message() {

		$params = array(
			'access_token' => $this->data['access_token'],
		);
		$url    = add_query_arg( array(
			'token'   => $this->data['access_token'],
			'channel' => $this->data['user'],
			'as_user' => true,
			'text'    => rawurlencode( strip_tags( $this->data['body'] ) ),
		), $this->get_endpoint() );
		$res    = $this->make_wp_requests( $url, $params, BWFCO_Slack::get_headers(), BWF_CO::$POST );

		return $res;
	}

	/**
	 * The campaign endpoint to chat.postMessage.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Slack::get_endpoint() . 'chat.postMessage';
	}

}

/**
 * Register this call class.
 */
return ( 'WFCO_SL_Message_User' );
