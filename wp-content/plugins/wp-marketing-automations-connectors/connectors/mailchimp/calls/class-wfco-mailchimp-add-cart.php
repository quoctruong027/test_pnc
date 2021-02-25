<?php

class WFCO_Mailchimp_Add_Cart extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'email', 'bwfan_ab_id', 'store_id', 'abandoned_date', 'cart_url', 'cart_items', 'checkout_data', 'cart_total' );
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

		if ( ! is_email( $this->data['email'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Email is not valid' ),
			);
		}

		$customer_details = $this->get_customer_details( $this->data['checkout_data'], $this->data['email'] );
		$params           = array(
			'id'              => 'bwfan_cart_' . $this->data['bwfan_ab_id'],
			'customer'        => $customer_details,
			'lines'           => $this->get_product_order_line_items( $this->data['cart_items'] ),
			'currency_code'   => get_woocommerce_currency(),
			'order_total'     => $this->data['cart_total'],
			'checkout_url'    => $this->data['cart_url'],
			'billing_address' => $customer_details['address']
		);

		BWFCO_Mailchimp::set_headers( $this->data['api_key'] );

		return $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Mailchimp::get_headers(), BWF_CO::$POST );
	}

	/**
	 * @param array $cart_items
	 * @param string $email
	 *
	 * @return array
	 */
	public function get_customer_details( $cart_items, $email ) {
		if ( ! isset( $cart_items['fields'] ) || empty( $cart_items['fields'] ) ) {
			return array(
				'id'            => md5( $email ),
				'email_address' => $email,
				'first_name'    => '',
				'last_name'     => '',
				'opt_in_status' => true,
				'address'       => array()
			);
		}

		$country = isset( $cart_items['fields']['billing_country'] ) ? $cart_items['fields']['billing_country'] : '';

		return array(
			'id'            => md5( $email ),
			'email_address' => $email,
			'first_name'    => isset( $cart_items['fields']['billing_first_name'] ) ? $cart_items['fields']['billing_first_name'] : '',
			'last_name'     => isset( $cart_items['fields']['billing_last_name'] ) ? $cart_items['fields']['billing_last_name'] : '',
			'opt_in_status' => true,
			'address'       => array(
				'address1'     => isset( $cart_items['fields']['billing_address_1'] ) ? $cart_items['fields']['billing_address_1'] : '',
				'address2'     => isset( $cart_items['fields']['billing_address_2'] ) ? $cart_items['fields']['billing_address_2'] : '',
				'city'         => isset( $cart_items['fields']['billing_city'] ) ? $cart_items['fields']['billing_city'] : '',
				'province'     => isset( $cart_items['fields']['billing_state'] ) ? $cart_items['fields']['billing_state'] : '',
				'postal_code'  => isset( $cart_items['fields']['billing_postcode'] ) ? $cart_items['fields']['billing_postcode'] : '',
				'country'      => ( WC()->countries->get_countries() )[ $country ],
				'country_code' => $country
			)
		);
	}

	/**
	 * @param array $cart_items
	 *
	 * @return array
	 */
	public function get_product_order_line_items( $cart_items ) {
		$order_items = array();
		foreach ( $cart_items as $key => $item_data ) {
			$product_id = ( isset( $item_data['product_id'] ) ) ? $item_data['product_id'] : 0;
			$quantity   = ( isset( $item_data['quantity'] ) ) ? $item_data['quantity'] : 0;
			$product    = wc_get_product( $product_id );
			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$mailchimp_product_id = $this->get_product_id_by_wc_item( $product );
			/** if error: */
			if ( is_array( $mailchimp_product_id ) || empty( $mailchimp_product_id ) ) {
				continue;
			}

			$item_data = array(
				'id'                 => 'bwfan_order_line_' . $key,
				'product_id'         => $mailchimp_product_id,
				'product_variant_id' => $mailchimp_product_id,
				'quantity'           => $quantity,
				'price'              => $product->get_price(),
			);

			$order_items[] = $item_data;
		}

		return $order_items;
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return array|int
	 */
	public function get_product_id_by_wc_item( $product ) {
		$mailchimp_product_id = get_post_meta( $product->get_id(), 'bwfan_mailchimp_product_id', true );
		if ( ! empty( $mailchimp_product_id ) ) {
			return $mailchimp_product_id;
		}

		$name                 = str_replace( ' &ndash;', ': ', $product->get_name() );
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

		return BWFCO_Mailchimp::get_endpoint( $data_center ) . '/ecommerce/stores/' . $this->data['store_id'] . '/carts';
	}

}

return 'WFCO_Mailchimp_Add_Cart';
