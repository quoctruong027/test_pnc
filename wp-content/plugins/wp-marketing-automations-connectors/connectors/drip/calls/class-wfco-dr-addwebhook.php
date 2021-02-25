<?php

class WFCO_DR_Addwebhook extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'url', 'account_id', 'access_token', 'events' );
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

		BWFCO_Drip::set_headers( $this->data['access_token'] );

		return $this->add_webhook();
	}

	/**
	 * Add a webook to drip account.
	 *
	 * @param $to_post_url
	 * @param $events (subscriber.created, subscriber.subscribed_to_campaign, subscriber.completed_campaign, subscriber.applied_tag)
	 *
	 * @return array|mixed|object|string
	 */
	public function add_webhook() {
		$params     = array(
			'post_url' => $this->data['url'],
			'events'   => $this->data['events'],
		);
		$url        = $this->get_endpoint();
		$req_params = array(
			'webhooks' => array( $params ),
		);
		$res        = $this->make_wp_requests( $url, wp_json_encode( $req_params ), BWFCO_Drip::get_headers(), BWF_CO::$POST );

		return $res;
	}

	/**
	 * Return the endpoint for creating / updating an order.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Drip::get_endpoint( $this->data['account_id'] ) . 'webhooks';
	}

}

return 'WFCO_DR_Addwebhook';
