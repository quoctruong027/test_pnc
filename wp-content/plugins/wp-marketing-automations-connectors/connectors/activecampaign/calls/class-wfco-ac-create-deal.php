<?php

class WFCO_AC_Create_Deal extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'title', 'email', 'deal_value', 'currency', 'pipeline_id', 'stage_id', 'owner_id' );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
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

		BWFCO_ActiveCampaign::set_headers( $this->data['api_key'] );

		return $this->create_deal();
	}

	/**
	 * Create a single new tag.
	 *
	 * @param string $api_key
	 * @param string $api_url
	 * @param string $email
	 *
	 * @return array|mixed
	 */
	public function create_deal() {
		// Get contact by email
		$api_action   = 'contacts';
		$params_data  = array();
		$endpoint_url = BWFCO_ActiveCampaign::get_endpoint_url( $this->data['api_url'], $api_action );
		$endpoint_url = $endpoint_url . '?filters[email]=' . $this->data['email'];
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), BWF_CO::$GET );

		// Contact is found, fetch its id
		if ( is_array( $result ) && isset( $result['response'] ) && 200 === $result['response'] && is_array( $result['body']['contacts'] ) && count( $result['body']['contacts'] ) > 0 ) {
			$customer_id = $result['body']['contacts'][0]['id'];
		} else { // Create a new customer
			$connector = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_ac_create_contact' );
			$connector->set_data( $this->data );
			$response = $connector->process();
			if ( is_array( $response ) && isset( $response['response'] ) && 200 === $response['response'] && isset( $response['body']['contact']['id'] ) ) {
				$customer_id = $response['body']['contact']['id'];
			} else {
				return $response;
			}
		}

		// Now create actual deal
		$api_action     = 'deals';
		$order_id       = $this->data['order_id'];
		$request_method = BWF_CO::$POST;

		if ( $order_id > 0 ) {
			$deal_id = get_post_meta( $order_id, '_bwfan_ac_deal_id', true );
			if ( $deal_id > 0 ) {
				$api_action     .= '/' . $deal_id;
				$request_method = BWF_CO::$PUT;
			}
		}
		$params_data = array(
			'deal' => array(
				'title'       => $this->data['title'],
				'group'       => $this->data['pipeline_id'],
				'stage'       => $this->data['stage_id'],
				'contact'     => $customer_id,
				'value'       => $this->data['deal_value'],
				'currency'    => $this->data['currency'],
				'owner'       => $this->data['owner_id'],
				'description' => $this->data['description'],
			),
		);

		$params_data  = wp_json_encode( $params_data );
		$endpoint_url = BWFCO_ActiveCampaign::get_endpoint_url( $this->data['api_url'], $api_action );
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), $request_method );
		if ( isset( $result['body']['deal'] ) ) {
			if ( $order_id > 0 ) {
				update_post_meta( $order_id, '_bwfan_ac_deal_id', $result['body']['deal']['id'] );
			}
		}

		return $result;
	}

}

return 'WFCO_AC_Create_Deal';
