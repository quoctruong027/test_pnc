<?php

class WFCO_AC_Create_Order extends WFCO_Call {

	private static $instance = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'api_url', 'email', 'order' );
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

		return $this->create_order();
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
	public function create_order() {
		// Get customer by email
		$api_action   = 'ecomCustomers';
		$params_data  = array();
		$endpoint_url = BWFCO_ActiveCampaign::get_endpoint_url( $this->data['api_url'], $api_action );
		$endpoint_url = $endpoint_url . '?filters[email]=' . $this->data['email'];
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), BWF_CO::$GET );

		// Customer is found, fetch its id
		if ( is_array( $result ) && isset( $result['response'] ) && 200 === $result['response'] && is_array( $result['body']['ecomCustomers'] ) && count( $result['body']['ecomCustomers'] ) > 0 ) {
			$customers = $result['body']['ecomCustomers'];
			foreach ( $customers as $customer_details ) {
				if ( $customer_details['connectionid'] === $this->data['order']['connectionid'] ) {
					$customer_id = $customer_details['id'];
					break;
				}
			}
		} else { // Create a new customer
			$connector = WFCO_Common::get_call_object( $this->connector_slug, 'wfco_ac_create_customer' );
			$connector->set_data( $this->data );
			$response = $connector->process();
			if ( is_array( $response ) && isset( $response['response'] ) && 200 === $response['response'] && isset( $response['body']['ecomCustomer']['id'] ) ) {
				$customer_id = $response['body']['ecomCustomer']['id'];
			} else {
				return $response;
			}
		}

		$order               = $this->data['order'];
		$order['customerid'] = $customer_id;
		$api_action          = 'ecomOrders';
		$params_data         = array(
			'ecomOrder' => $order,
		);
		$already_exist       = get_post_meta( $order['orderNumber'], '_bwfan_ac_create_order_id', true );

		if ( '' !== $already_exist ) {
			$api_action .= '/' . $already_exist;
			$req_method = 4;
		} else {
			$req_method = 2;
		}

		$params_data  = wp_json_encode( $params_data );
		$endpoint_url = BWFCO_ActiveCampaign::get_endpoint_url( $this->data['api_url'], $api_action );
		$result       = $this->make_wp_requests( $endpoint_url, $params_data, BWFCO_ActiveCampaign::get_headers(), $req_method );

		return $result;
	}

}

return 'WFCO_AC_Create_Order';
