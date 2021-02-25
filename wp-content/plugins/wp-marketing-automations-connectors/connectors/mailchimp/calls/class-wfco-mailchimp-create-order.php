<?php

class WFCO_Mailchimp_Create_Order extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'email', 'order_id', 'store_id' );
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

		$order = wc_get_order( $this->data['order_id'] );
		if ( ! $order instanceof WC_Order ) {
			return array(
				'response' => 502,
				'body'     => array( 'Invalid Order' ),
			);
		}

		if ( ! is_email( $this->data['email'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Email is not valid' ),
			);
		}

		$customer_details = $this->get_customer_details( $order );
		$params           = array(
			'id'              => 'bwfan_order_' . $order->get_id(),
			'customer'        => $customer_details,
			'lines'           => $this->get_product_order_line_items( $order ),
			'currency_code'   => $order->get_currency(),
			'order_total'     => $order->get_total(),
			'order_url'       => $order->get_edit_order_url(),
			'discount_total'  => $order->get_discount_total(),
			'tax_total'       => $order->get_total_tax(),
			'shipping_total'  => $order->get_shipping_total(),
			'billing_address' => $customer_details['address']
		);

		BWFCO_Mailchimp::set_headers( $this->data['api_key'] );

		return $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Mailchimp::get_headers(), BWF_CO::$POST );
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function get_customer_details( $order ) {
		return array(
			'id'            => md5( $order->get_billing_email() ),
			'email_address' => $order->get_billing_email(),
			'first_name'    => $order->get_billing_first_name(),
			'last_name'     => $order->get_billing_last_name(),
			'opt_in_status' => true,
			'address'       => array(
				'address1'     => $order->get_billing_address_1(),
				'address2'     => $order->get_billing_address_2(),
				'city'         => $order->get_billing_city(),
				'province'     => $order->get_billing_state(),
				'postal_code'  => $order->get_billing_postcode(),
				'country'      => ( WC()->countries->get_countries() )[ $order->get_billing_country() ],
				'country_code' => $order->get_billing_country()
			)
		);
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function get_product_order_line_items( $order ) {
		$order_items = array();
		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}

			$mailchimp_product_id = $this->get_product_id_by_wc_item( $item );
			/** if error: */
			if ( is_array( $mailchimp_product_id ) || empty( $mailchimp_product_id ) ) {
				continue;
			}

			$item_data = array(
				'id'                 => 'bwfan_order_line_' . $item->get_id(),
				'product_id'         => $mailchimp_product_id,
				'product_variant_id' => $mailchimp_product_id,
				'quantity'           => $item->get_quantity(),
				'price'              => round( $item->get_subtotal() / $item->get_quantity(), 2 ),
			);

			$order_items[] = $item_data;
		}

		if ( empty( $order_items ) ) {
			return array();
		}

		/** Add per item discount */
		$order_discount = $order->get_total_discount();
		if ( $order_discount > 0.0 ) {
			$per_item_discount = $order_discount / count( $order_items );
			$order_items       = array_map( function ( $item ) use ( $per_item_discount ) {
				$item['discount'] = $per_item_discount;

				return $item;
			}, $order_items );
		}

		return $order_items;
	}

	/**
	 * @param WC_Order_Item_Product $item
	 *
	 * @return array|int
	 */
	public function get_product_id_by_wc_item( $item ) {
		$product = $item->get_product();
		if ( ! $product instanceof WC_Product ) {
			return array( 'error' => 'WooCommerce Product doesn\'t exists.' );
		}

		$mailchimp_product_id = get_post_meta( $product->get_id(), 'bwfan_mailchimp_product_id', true );
		if ( ! empty( $mailchimp_product_id ) ) {
			return $mailchimp_product_id;
		}

		$name                 = str_replace( ' &ndash;', ': ', $item->get_name() );
		$mailchimp_product_id = $this->get_mailchimp_product_id( $name, $product->get_id() );
		if ( is_array( $mailchimp_product_id ) ) {
			return $mailchimp_product_id;
		}

		update_post_meta( $product->get_id(), 'bwfan_mailchimp_product_id', $mailchimp_product_id );

		return $mailchimp_product_id;
	}

	public function get_mailchimp_product_id( $name, $wc_product_id ) {
		$available_products = WFCO_Mailchimp_Common::get_mailchimp_settings()['products'];
		$crm_product_id     = array_search( $name, $available_products, true );

		if ( ! empty( $crm_product_id ) ) {
			return $crm_product_id;
		}

		$connector = WFCO_Load_Connectors::get_instance();

		$call = $connector->get_call( 'wfco_mailchimp_create_product' );
		$call->set_data( array(
			'product_id'   => $wc_product_id,
			'product_name' => $name,
			'api_key'      => $this->data['api_key'],
			'store_id'     => $this->data['store_id']
		) );
		$result = $call->process();

		if ( 200 !== $result['response'] || ! isset( $result['body']['id'] ) ) {
			$error = __( 'Error Response Code: ', 'autonami-automations-connectors' ) . $result['response'] . __( '. Mailchimp Error: ', 'autonami-automations-connectors' );
			$error .= is_array( $result['body'] ) && isset( $result['body']['detail'] ) ? $result['body']['detail'] : __( 'No Response from Mailchimp. ', 'autonami-automations-connectors' );
			$error .= ( 502 === absint( $result['response'] ) ) ? __( 'Autonami Error: ', 'autonami-automations-connectors' ) . $result['body'][0] : '';

			return array(
				'status'  => 'failed',
				'message' => $error,
			);
		}

		return $result['body']['id'];
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		$data_center = BWFCO_Mailchimp::get_data_center( $this->data['api_key'] );

		return BWFCO_Mailchimp::get_endpoint( $data_center ) . '/ecommerce/stores/' . $this->data['store_id'] . '/orders';
	}

}

return 'WFCO_Mailchimp_Create_Order';
