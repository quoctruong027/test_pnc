<?php

final class BWFAN_DR_Add_Cart extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Cart Activity', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action creates a WooCommerce abandoned cart', 'autonami-automations-connectors' );
		$this->action_priority = 35;
		$this->included_events = array( 'ab_cart_abandoned' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function make_data( $integration_object, $task_meta ) {
		if ( ! isset( $task_meta['global']['cart_abandoned_id'] ) || empty( $task_meta['global']['cart_abandoned_id'] ) ) {
			return array();
		}
		$cart_abandoned_id = $task_meta['global']['cart_abandoned_id'];
		$data              = BWFAN_Model_Abandonedcarts::get( $cart_abandoned_id );
		if ( empty( $data ) ) {
			return array();
		}

		$data_to_set                    = array();
		$data_to_set['email']           = $data['email'];
		$data_to_set['action']          = 'created';
		$data_to_set['provider']        = 'woocommerce';
		$data_to_set['occurred_at']     = $data['last_modified'];
		$data_to_set['cart_id']         = (string) $cart_abandoned_id;
		$data_to_set['grand_total']     = (float) $data['total'];
		$data_to_set['total_discounts'] = 0;
		$data_to_set['currency']        = $data['currency'];
		$data_to_set['access_token']    = $integration_object->get_settings( 'access_token' );
		$data_to_set['account_id']      = $integration_object->get_settings( 'account_id' );
		$data_to_set['cart_url']        = BWFAN_Common::wc_get_cart_recovery_url( $data['token'] );
		$data_to_set['items']           = [];
		$items                          = maybe_unserialize( $data['items'] );

		if ( is_array( $items ) && count( $items ) > 0 ) {

			/**
			 * @var $item WC_Order_Item_Product;
			 */
			foreach ( $items as $item ) {
				/**
				 * @var $product WC_Product;
				 */
				$product = $item['data'];
				if ( ! $product instanceof WC_Product ) {
					continue;
				}
				$item_discount = ( $item['line_subtotal'] - $item['line_total'] );

				$item_data              = [
					'product_id'         => (string) $item['product_id'],
					'product_variant_id' => (string) $item['variation_id'],
					'sku'                => $product->get_sku(),
					'name'               => $product->get_name(),
					'categories'         => $this->get_category_name( $product ),
					'price'              => floatval( $product->get_price() ),
					'sale_price'         => floatval( $product->get_sale_price() ),
					'quantity'           => (int) $item['quantity'],
					'discounts'          => $item_discount > 0 ? $item_discount : 0,
					'taxes'              => floatval( $item['line_tax'] ),
					'total'              => floatval( $item['line_total'] ),
					'product_url'        => $product->get_permalink(),
					'image_url'          => $product->get_image_id() > 0 ? wp_get_attachment_url( $product->get_image_id() ) : wc_placeholder_img_src(),
					'product_tag'        => $this->get_category_name( $product, 'tag' ),
				];
				$data_to_set['items'][] = apply_filters( 'bwfan_drip_add_cart_item_api_data', $item_data, $item, $product );
			}
		}

		return $data_to_set;
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

		if ( isset( $result['body']['request_id'] ) ) {
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

return 'BWFAN_DR_Add_Cart';
