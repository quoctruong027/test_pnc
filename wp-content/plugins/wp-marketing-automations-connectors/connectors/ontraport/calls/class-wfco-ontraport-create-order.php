<?php

class WFCO_Ontraport_Create_Order extends WFCO_Call {

	private static $ins = null;
	private $site_url = null;

	public function __construct() {

		$this->required_fields = array( 'app_id', 'api_key', 'email' );
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
		BWFCO_Ontraport::set_headers( $this->data );

		/** get contact id using email **/
		$contact = WFCO_Ontraport_Common::get_contact_id_by_email( $this->data['app_id'], $this->data['api_key'], $this->data['email'], true );
		// Error in getting contact
		if ( is_array( $contact ) && isset( $contact['response'] ) && 200 !== absint( $contact['response'] ) ) {
			return $contact;
		}

		if ( empty( $contact ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Unable to Create or Get Contact' ),
			);
		}

		$this->contact_id = $contact;

		/** get product details of order **/
		$order = wc_get_order( $this->data['order_id'] );
		if ( ! $order instanceof WC_Order ) {
			return array(
				'response' => 502,
				'body'     => array( 'Invalid Order' ),
			);
		}

		$ontraport_order_id = get_post_meta( $this->data['order_id'], 'bwfan_ontraport_order_id', true );
		if ( absint( $ontraport_order_id ) > 0 ) {
			return array(
				'response' => 502,
				'body'     => array( 'Order already created in Ontraport' ),
			);
		}

		$productdata = $this->get_product_items( $order );
		if ( 0 === count( $productdata ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Order contains no valid product items' ),
			);
		}

		/** discount details **/
		if ( $order->get_total_discount() > 0.0 ) {
			$total_products       = count( $productdata );
			$discount_per_product = $order->get_total_discount() / $total_products;
			foreach ( $productdata as $i => $product ) {
				$productdata[ $i ]['price'][0]['price'] = round( $product['total'] - $discount_per_product, 2 );
			}
		}

		$params = array(
			'objectID'         => 0,
			'contact_id'       => $this->contact_id,
			'chargeNow'        => 'chargeLog',
			'trans_date'       => time(),
			'invoice_template' => 0,
			'offer'            => [
				'products'          => $productdata,
				"delay"             => 0,
				"external_order_id" => $this->data['order_id'],
				'subTotal'          => $order->get_subtotal(),
				'grandTotal'        => $order->get_total(),
			],
			'billing_address'  => $this->get_billing_info( $order ),
		);

		$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Ontraport::get_headers(), BWF_CO::$POST );

		if ( isset( $res['response'] ) && ( 200 === $res['response'] || 201 === $res['response'] ) && isset( $res['body']['data'] ) && isset( $res['body']['data']['invoice_id'] ) ) {
			update_post_meta( $this->data['order_id'], 'bwfan_ontraport_order_id', $res['body']['data']['invoice_id'] );
		}

		return $res;
	}


	/**
	 * param $order
	 * retun $billing_address
	 **/

	public function get_billing_info( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return array();
		}

		$billing_address = array(
			'address'  => $order->get_billing_address_1(),
			'address2' => $order->get_billing_address_2(),
			'city'     => $order->get_billing_city(),
			'state'    => $order->get_billing_state(),
			'zip'      => $order->get_billing_postcode(),
			'country'  => $order->get_billing_country(),
		);

		return $billing_address;
	}

	/**
	 *    create product in ontraport
	 *
	 **/
	public function create_product( $product, $params ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Ontraport_Create_Product */
		$create_product_call = $connectors->get_call( 'wfco_ontraport_create_product' );
		$data                = array(
			'product_name'  => $product['name'],
			'product_price' => $product['price'],
			'app_id'        => $params['app_id'],
			'api_key'       => $params['api_key'],
		);
		$create_product_call->set_data( $data );
		$response = $create_product_call->process();

		if ( 200 !== $response['response'] || empty( $response['body']['data'] ) || ! isset( $response['body']['data']['id'] ) ) {
			return $response;
		}

		$ontraport_product_id = $response['body']['data']['id'];
		update_post_meta( $product['id'], 'bwfan_ontraport_product_id', $ontraport_product_id );

		return $ontraport_product_id;
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array $product_items
	 */
	public function get_product_items( $order ) {
		$product_items = array();

		foreach ( $order->get_items() as $item_id => $item ) {
			$product_id = $item->get_product_id();
			/** @var WC_Product $product */
			$product = wc_get_product( $product_id );
			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$name     = $item->get_name();
			$quantity = $item->get_quantity();
			$tax      = $item->get_total_tax();
			$sku      = $product->get_sku();
			$price    = round( $item->get_subtotal(), 2 ) / absint( $quantity );
			$price    += (float) $tax / absint( $quantity );
			/** Tax Addition */

			$ontraport_product_id = get_post_meta( $product_id, 'bwfan_ontraport_product_id', true );
			if ( empty( $ontraport_product_id ) ) {
				$ontraport_product_id = $this->fetch_product_by_name( $product->get_name() );
			} else {
				$ontraport_product_id = $this->fetch_product_by_id( $ontraport_product_id );
			}

			/** If product found in ontraport */
			if ( ! is_array( $ontraport_product_id ) ) {
				$product_items[ $item_id ] = array(
					'name'     => $name,
					'id'       => $ontraport_product_id,
					'quantity' => $quantity,
					'sku'      => $sku,
					'price'    => array(
						array(
							'price'         => $price,
							'payment_count' => 1,
							'unit'          => 'day',
						),
					)
				);
				continue;
			}

			/** If not found, then create product in ontraport **/
			$create_product_data = array(
				'name'  => $name,
				'id'    => $product->get_id(),
				'price' => $product->get_price(),
				'type'  => $product->get_type(),
			);

			$ontraport_product_id = $this->create_product( $create_product_data, $this->data );

			if ( is_array( $ontraport_product_id ) ) {
				continue;
			}

			$product_items[ $item_id ] = array(
				'name'     => $name,
				'id'       => $ontraport_product_id,
				'quantity' => $quantity,
				'sku'      => $sku,
				'price'    => array(
					array(
						'price'         => $price,
						'payment_count' => 1,
						'unit'          => 'day',
					),
				)
			);
		}

		return $product_items;
	}

	public function fetch_product_by_name( $product_name ) {
		$connectors = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Ontraport_Create_Product */
		$call       = $connectors->get_call( 'wfco_ontraport_search_product_by_name' );
		$product_id = null;
		$data       = array(
			'name'    => $product_name,
			'app_id'  => $this->data['app_id'],
			'api_key' => $this->data['api_key'],
		);
		$call->set_data( $data );
		$response = $call->process();

		if ( isset( $response['response'] ) && 200 === $response['response'] && ! empty( $response['body']['data'] ) && isset( $res['body']['data'][0]['id'] ) ) {
			return $response['body']['data'][0]['id'];
		}

		return $response;
	}

	/** validate ontraport product **/
	public function fetch_product_by_id( $ontraport_product_id ) {
		$connector        = WFCO_Load_Connectors::get_instance();
		$get_product_call = $connector->get_call( 'wfco_ontraport_get_product_by_id' );

		$params = array(
			'product_id' => $ontraport_product_id,
			'app_id'     => $this->data['app_id'],
			'api_key'    => $this->data['api_key'],
		);
		$get_product_call->set_data( $params );

		return $get_product_call->process();
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Ontraport::get_endpoint() . '/transaction/processManual';
	}

}

return 'WFCO_Ontraport_Create_Order';
