<?php

namespace OM4\WooCommerceZapier\WooCommerceResource\Coupon;

use OM4\WooCommerceZapier\Helper\FeatureChecker;
use OM4\WooCommerceZapier\WooCommerceResource\CustomPostTypeResource;

defined( 'ABSPATH' ) || exit;


/**
 * Definition of the Coupon resource type.
 *
 * This resource is only enabled to users if WooCommerce core's coupons functionality is enabled.
 *
 * @since 2.0.0
 */
class ResourceDefinition extends CustomPostTypeResource {

	/**
	 * Feature Checker instance.
	 *
	 * @var FeatureChecker
	 */
	protected $checker;

	/**
	 * {@inheritDoc}
	 *
	 * @param FeatureChecker $checker FeatureChecker instance.
	 */
	public function __construct( FeatureChecker $checker ) {
		$this->checker               = $checker;
		$this->key                   = 'coupon';
		$this->name                  = __( 'Coupon', 'woocommerce-zapier' );
		$this->custom_post_type_name = 'shop_coupon';
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_enabled() {
		return $this->checker->is_coupon_enabled();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_triggers() {
		// No custom Triggers for coupons.
		return array();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param int $resource_id Resource ID.
	 *
	 * @return string|null
	 */
	public function get_description( $resource_id ) {
		$coupon_code = \wc_get_coupon_code_by_id( $resource_id );
		if ( '' !== $coupon_code ) {
			return $coupon_code;
		}
		return null;
	}

}
