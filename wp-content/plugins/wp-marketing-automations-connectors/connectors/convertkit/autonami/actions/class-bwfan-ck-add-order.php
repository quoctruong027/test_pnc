<?php

final class BWFAN_CK_Add_Order extends BWFAN_Action {

	private static $ins = null;
	public $show = false;

	private function __construct() {
		$this->action_name     = __( 'Create A New Purchase', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action creates a new WooCommerce purchase', 'autonami-automations-connectors' );
		$this->included_events = array(
			'wc_new_order',
			'wc_product_purchased',
		);
		$this->action_priority = 25;
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function get_view_data() {
		return array();
	}

	public function make_data( $integration_object, $task_meta ) {
		$data_to_set               = array();
		$data_to_set['api_secret'] = $integration_object->get_settings( 'api_secret' );
		$data_to_set['order_id']   = $task_meta['global']['order_id'];
		$order_id                  = $task_meta['global']['order_id'];
		$order                     = wc_get_order( $order_id );
		$purchase                  = [];

		if ( $order->get_items( 'line_item' ) ) {
			/**
			 * @var $item WC_Order_Item_Product;
			 */
			foreach ( $order->get_items( 'line_item' ) as $item ) {
				$product                = $item->get_product();
				$item_data              = [
					'pid'        => (string) $item->get_product_id(),
					'name'       => $product->get_name(),
					'sku'        => $product->get_sku(),
					'quantity'   => $item->get_quantity(),
					'unit_price' => floatval( $product->get_price() ),
					'lid'        => $item->get_id(),
				];
				$purchase['products'][] = apply_filters( 'bwfan_' . $this->get_slug() . '_add_order_item_api_data', $item_data, $item, $product );
			}
		}

		$purchase['discount'] = $order->get_total();
		$purchase['subtotal'] = $order->get_subtotal();

		$date                         = $order->get_date_paid();
		$purchase['transaction_time'] = '';
		if ( $date instanceof WC_DateTime ) {
			$purchase['transaction_time'] = gmdate( DATE_W3C, $date->getTimestamp() );
		} else {
			$create_date = $order->get_date_created();
			if ( $create_date instanceof WC_DateTime ) {
				$purchase['transaction_time'] = gmdate( DATE_W3C, $create_date->getTimestamp() );
			}
		}

		$purchase['currency']       = $order->get_currency();
		$purchase['first_name']     = $order->get_billing_first_name();
		$purchase['email_address']  = $order->get_billing_email();
		$purchase['transaction_id'] = ! empty( $order->get_transaction_id() ) ? $order->get_transaction_id() : $order_id;
		$purchase['status']         = ( true === $order->is_paid() ? 'paid' : '' );
		$purchase['total']          = $order->get_total();
		$purchase['shipping']       = $order->get_shipping_total();
		$data_to_set['purchase']    = $purchase;

		return $data_to_set;
	}

	protected function handle_response( $response, $call_object = null ) {
		if ( isset( $response['status'] ) ) {
			return $response;
		}
		if ( 200 === $response['response'] && isset( $response['body']['id'] ) && '' !== $response['body']['id'] ) {
			$result = array(
				'status'  => 3,
				'message' => __( 'Purchase event added', 'autonami-automations-connectors' ),
			);

			return $result;
		}

		if ( 502 === absint( $response['response'] ) && is_array( $response['body'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $response['body'][0] ) ? $response['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $response['response'];
		$result_message  = ( is_array( $response['body'] ) && isset( $response['body']['error'] ) ) ? $response['body']['error'] : false;
		$message         = ( is_array( $response['body'] ) && isset( $response['body']['message'] ) ) ? $response['body']['message'] : false;
		$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $result_message ? $result_message : ( false !== $message ? $message : $unknown_message ) ) . $response_code,
		);
	}

}

return 'BWFAN_CK_Add_Order';
