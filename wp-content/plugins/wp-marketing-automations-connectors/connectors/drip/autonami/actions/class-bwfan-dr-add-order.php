<?php

final class BWFAN_DR_Add_Order extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Add A New Order', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action creates a new WooCommerce order', 'autonami-automations-connectors' );
		$this->included_events = array(
			'wc_new_order',
			'wc_product_purchased',
		);
		$this->action_priority = 40;
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function make_data( $integration_object, $task_meta ) {
		$order_id                       = $task_meta['global']['order_id'];
		$order                          = wc_get_order( $order_id );
		$data_to_set                    = array();
		$data_to_set['email']           = $task_meta['global']['email'];
		$data_to_set['action']          = $this->get_order_status( $order );
		$data_to_set['provider']        = 'woocommerce';
		$data_to_set['occurred_at']     = $order->get_date_completed();
		$data_to_set['order_id']        = (string) $order->get_id();
		$data_to_set['total_discounts'] = (float) $order->get_discount_total();
		$data_to_set['grand_total']     = (float) $order->get_total();
		$data_to_set['total_fees']      = (float) $this->get_fee_total( $order );
		$data_to_set['total_shipping']  = (float) $order->get_shipping_total();
		$data_to_set['currency']        = $order->get_currency();
		$data_to_set['order_url']       = $order->get_checkout_order_received_url();
		$data_to_set['access_token']    = $integration_object->get_settings( 'access_token' );
		$data_to_set['account_id']      = $integration_object->get_settings( 'account_id' );
		$data_to_set['items']           = [];

		if ( $order->get_items( 'line_item' ) ) {

			/**
			 * @var $item WC_Order_Item_Product;
			 */
			foreach ( $order->get_items( 'line_item' ) as $item ) {
				$product       = $item->get_product();
				$item_discount = ( $item->get_subtotal() - $item->get_total() );

				$item_data              = [
					'product_id'         => (string) $item->get_product_id(),
					'product_variant_id' => (string) $item->get_variation_id(),
					'sku'                => $product->get_sku(),
					'name'               => $product->get_name(),
					'categories'         => $this->get_category_name( $product ),
					'price'              => floatval( $product->get_price() ),
					'sale_price'         => floatval( $product->get_sale_price() ),
					'quantity'           => (int) $item->get_quantity(),
					'discounts'          => $item_discount > 0 ? $item_discount : 0,
					'taxes'              => floatval( $item->get_total_tax() ),
					'total'              => floatval( $item->get_total() ),
					'product_url'        => $product->get_permalink(),
					'image_url'          => $product->get_image_id() > 0 ? wp_get_attachment_url( $product->get_image_id() ) : wc_placeholder_img_src(),
					'product_tag'        => $this->get_category_name( $product, 'tag' ),
				];
				$data_to_set['items'][] = apply_filters( 'bwfan_drip_add_order_item_api_data', $item_data, $item, $product );

			}
		}

		$billing_details                = [
			'first_name'  => $order->get_billing_first_name(),
			'last_name'   => $order->get_billing_last_name(),
			'company'     => $order->get_billing_company(),
			'address_1'   => $order->get_billing_address_1(),
			'address_2'   => $order->get_billing_address_2(),
			'city'        => $order->get_billing_city(),
			'state'       => $order->get_billing_state(),
			'postal_code' => $order->get_billing_postcode(),
			'country'     => $order->get_billing_country(),
			'phone'       => $order->get_billing_phone(),
		];
		$data_to_set['billing_address'] = apply_filters( 'bwfan_drip_add_order_billing_address_api_data', $billing_details, $order );

		$shipping_details = [
			'first_name'  => $order->get_shipping_first_name(),
			'last_name'   => $order->get_shipping_last_name(),
			'company'     => $order->get_shipping_company(),
			'address_1'   => $order->get_shipping_address_1(),
			'address_2'   => $order->get_shipping_address_2(),
			'city'        => $order->get_shipping_city(),
			'state'       => $order->get_shipping_state(),
			'postal_code' => $order->get_shipping_postcode(),
			'country'     => $order->get_shipping_country(),
		];

		$data_to_set['shipping_address'] = apply_filters( 'bwfan_drip_add_order_shipping_address_api_data', $shipping_details, $order );

		return $data_to_set;
	}

	/**
	 * @param $order WC_Order
	 *
	 * @return string
	 */
	private function get_order_status( $order ) {
		$status = $order->get_status();
		$return = 'placed';

		if ( ! in_array( $status, [ 'completed', 'refunded', 'cancelled' ], true ) ) {
			$paid_date = $order->get_date_paid();

			if ( empty( $paid_date ) ) {
				$paid_date = $order->get_date_created();
			}
			try {
				$current_date    = new DateTime();
				$order_paid_date = new DateTime( $paid_date );
				$diff            = $current_date->diff( $order_paid_date );
				if ( $diff->h < 1 ) {
					$return = 'placed';
				} else {
					$return = 'updated';
				}
			} catch ( Exception $e ) {

			}
		} else {

			if ( 'completed' === $status ) {
				$return = 'fulfilled';
			} elseif ( 'refunded' === $status ) {
				$return = 'refunded';
			} elseif ( 'cancelled' === $status ) {
				$return = 'canceled';
			} else {
				$return = 'placed';
			}
		}

		$already_placed = get_post_meta( $order->get_id(), '_bwfan_drip_add_order_status', true );
		if ( '' !== $already_placed && 'placed' === $return ) {
			$return = 'updated';
		}

		if ( 'placed' === $return ) {
			update_post_meta( $order->get_id(), '_bwfan_drip_add_order_status', $return );
		}

		return apply_filters( 'bwfan_drip_add_order_status', $return, $status );
	}

	/**
	 * @param $the_order
	 *
	 * @return int
	 */
	private function get_fee_total( $the_order ) {
		$fee_total = 0;
		if ( ! $the_order instanceof WC_Order ) {
			return 0;
		}

		// The fee total amount
		foreach ( $the_order->get_items( 'fee' ) as $item_fee ) {
			$fee_total += $item_fee->get_total();
		}

		return $fee_total;
	}

	private function get_category_name( $product, $type = 'category' ) {
		if ( ! $product instanceof WC_Product ) {
			return [];
		}

		$terms    = [];
		$taxonomy = 'product_cat';
		if ( 'category' === $type ) {
			$ids = $product->get_category_ids();
		} elseif ( 'tags' === $type ) {
			$ids      = $product->get_tag_ids();
			$taxonomy = 'product_tag';
		}

		if ( empty( $ids ) ) {
			return [];
		}

		foreach ( $ids as $id ) {
			$term    = get_term_by( 'id', $id, $taxonomy, ARRAY_A );
			$terms[] = $term['name'];
		}

		return $terms;
	}

	public function execute_action( $action_data ) {
		$result = parent::execute_action( $action_data );
		/** handling response in case required field missing **/
		if ( isset( $result['response'] ) && 502 === $result['response'] ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['body']['errors'] ) && isset( $result['body']['errors'][0] ) && isset( $result['body']['errors'][0]['message'] ) ) {
			return array(
				'status'  => 4,
				'message' => $result['body']['errors'][0]['message'],
			);
		}

		if ( isset( $result['response'] ) && 200 === $result['response'] ) {
			return array(
				'status'  => 3,
				'message' => __( 'Request ID: ', 'autonami-autmations-connectors' ) . $result['body']['request_id'],
			);
		}

		return array(
			'status'  => 4,
			'message' => __( 'Unknown API Error', 'autonami-automations-connectors' ),
		);
	}
}

return 'BWFAN_DR_Add_Order';
