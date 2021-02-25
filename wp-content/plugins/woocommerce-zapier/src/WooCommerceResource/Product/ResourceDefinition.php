<?php

namespace OM4\WooCommerceZapier\WooCommerceResource\Product;

use OM4\WooCommerceZapier\WooCommerceResource\CustomPostTypeResource;

defined( 'ABSPATH' ) || exit;


/**
 * Definition of the Product resource type.
 *
 * @since 2.0.0
 */
class ResourceDefinition extends CustomPostTypeResource {

	/**
	 * {@inheritDoc}
	 */
	public function __construct() {
		$this->key                   = 'product';
		$this->name                  = __( 'Product', 'woocommerce-zapier' );
		$this->custom_post_type_name = 'product';
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_triggers() {
		// No custom Triggers for products.
		return array();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param int $resource_id Resource key.
	 *
	 * @return string|null
	 */
	public function get_description( $resource_id ) {
		$object = \wc_get_product( $resource_id );
		if ( ! is_null( $object ) && ! is_bool( $object ) && is_a( $object, 'WC_Product' ) && 'trash' !== $object->get_status() ) {
			return $object->get_name();
		}
		return null;
	}

}
