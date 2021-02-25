<?php

class WFCO_CK_Add_Order extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_secret', 'purchase' );
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
		BWFCO_ConvertKit::set_headers();

		return $this->add_order();
	}

	/**
	 * order id, email, productid and lineitem id are required.
	 *
	 * @return array|mixed|null|object|string
	 */
	public function add_order() {
		$purchase_data = $this->data['purchase'];
		if ( ! is_array( $purchase_data ) ) {
			return array(
				'status'  => 4,
				'message' => __( 'Invalid Purchase Data', 'autonami-automations-connectors' )
			);
		}

		$all_products = array();
		foreach ( $purchase_data['products'] as $single_product ) {
			$all_products[] = $single_product;
		}

		if ( 0 === count( $all_products ) ) {
			return array(
				'status'  => 4,
				'message' => __( 'No Products Selected', 'autonami-automations-connectors' )
			);
		}

		$all_products = wp_json_encode( $all_products );
		$params       = '{ "api_secret": "' . $this->data['api_secret'] . '", "purchase": { "total": "' . $purchase_data['total'] . '", "transaction_id": "' . $purchase_data['transaction_id'] . '", "email_address": "' . $purchase_data['email_address'] . '", "products": ' . $all_products . ', "shipping": ' . $purchase_data['shipping'] . ' } }';
		$url = $this->get_endpoint();

		return $this->make_wp_requests( $url, $params, BWFCO_ConvertKit::get_headers(), BWF_CO::$POST );
	}

	/**
	 * The endpoint to make a new order.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint() . 'purchases';
	}

}

return ( 'WFCO_CK_Add_Order' );
