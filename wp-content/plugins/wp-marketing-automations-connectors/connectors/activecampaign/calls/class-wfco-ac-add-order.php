<?php

class WFCO_AC_Add_Order extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'email', 'tags' );
	}

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

		return $this->add_order();
	}

	/**
	 * Add new tags to a contact.
	 *
	 * @return array|mixed
	 */
	public function add_order() {
		/** contact_tag_add */
		$api_action   = 'ecomOrders';
		$params_data  = array(
			'api_action' => 'contact_tag_add',
			'email'      => $this->data['email'],
			'tags'       => implode( ', ', $this->data['tags'] ),
		);
		$endpoint_url = $this->get_endpoint( $this->data['api_key'], $this->data['api_url'], $api_action );

		$result = $this->make_wp_requests( $endpoint_url, $params_data, array(), BWF_CO::$POST );

		return $result;
	}

	public function get_endpoint( $api_key, $api_url, $api_action ) {
		$base = '';
		if ( ! preg_match( '/https:\/\/www.activecampaign.com/', $api_url ) ) {
			$base = '/api/3';
		}
		if ( preg_match( '/\/$/', $api_url ) ) {
			// remove trailing slash
			$api_url = substr( $api_url, 0, strlen( $api_url ) - 1 );
		}
		if ( $api_key ) {
			$api_url = "{$api_url}{$base}/{$api_action}/?api_key={$api_key}";
		}
		$endpoint_url = $api_url;

		return $endpoint_url;
	}


}

return 'WFCO_AC_Add_Order';
