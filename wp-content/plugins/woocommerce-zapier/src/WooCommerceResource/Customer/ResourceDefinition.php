<?php

namespace OM4\WooCommerceZapier\WooCommerceResource\Customer;

use OM4\WooCommerceZapier\WooCommerceResource\Base;
use WC_Customer;

defined( 'ABSPATH' ) || exit;

/**
 * Definition of the Customer resource type.
 *
 * @since 2.0.0
 */
class ResourceDefinition extends Base {

	/**
	 * {@inheritDoc}
	 */
	public function __construct() {
		$this->key  = 'customer';
		$this->name = __( 'Customer', 'woocommerce-zapier' );
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_enabled() {
		return true;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param int $resource_id Resource ID.
	 */
	public function get_url( $resource_id ) {
		return admin_url( "user-edit.php?user_id={$resource_id}" );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_triggers() {
		return array();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param int $resource_id Resource ID.
	 */
	public function get_description( $resource_id ) {
		$object = new WC_Customer( $resource_id );
		if ( $object->get_id() > 0 ) {
			// Translators: WooCommerce customer name. 1: Customer First Name. 2: Customer Last Name.
			return sprintf( __( '%1$s %2$s', 'woocommerce-zapier' ), $object->get_first_name(), $object->get_last_name() );
		}
		return null;
	}
}
