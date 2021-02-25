<?php

namespace OM4\WooCommerceZapier\WooCommerceResource\Order;

use OM4\WooCommerceZapier\WooCommerceResource\CustomPostTypeResource;
use OM4\WooCommerceZapier\Trigger\Trigger;

defined( 'ABSPATH' ) || exit;


/**
 * Definition of the Order resource type.
 *
 * @since 2.0.0
 */
class ResourceDefinition extends CustomPostTypeResource {

	/**
	 * {@inheritDoc}
	 */
	public function __construct() {
		$this->key                   = 'order';
		$this->name                  = __( 'Order', 'woocommerce-zapier' );
		$this->custom_post_type_name = 'shop_order';
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_triggers() {

		$triggers[] = new Trigger(
			'order.status_changed',
			__( 'Order status changed', 'woocommerce-zapier' ),
			array( 'woocommerce_order_status_changed' )
		);

		// Order paid (previously New Order).
		$new_order_actions = array( 'woocommerce_payment_complete' );
		$triggers[]        = new Trigger(
			'order.paid',
			__( 'Order paid', 'woocommerce-zapier' ),
			$new_order_actions
		);
		return $triggers;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param int $resource_id Resource ID.
	 *
	 * @return string|null
	 */
	public function get_description( $resource_id ) {
		$object = \wc_get_order( $resource_id );
		if ( ! is_bool( $object ) && is_a( $object, 'WC_Order' ) && 'trash' !== $object->get_status() ) {
			return $object->get_formatted_billing_full_name();
		}
		return null;
	}
}
