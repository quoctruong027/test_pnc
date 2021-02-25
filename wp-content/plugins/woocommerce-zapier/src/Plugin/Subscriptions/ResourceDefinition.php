<?php

namespace OM4\WooCommerceZapier\Plugin\Subscriptions;

use OM4\WooCommerceZapier\Helper\FeatureChecker;
use OM4\WooCommerceZapier\WooCommerceResource\CustomPostTypeResource;
use OM4\WooCommerceZapier\Trigger\Trigger;

defined( 'ABSPATH' ) || exit;


/**
 * Definition of the Subscription resource type.
 *
 * This resource is only enabled if WooCommerce Subscriptions is available.
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
		$this->key                   = 'subscription';
		$this->name                  = __( 'Subscription', 'woocommerce-zapier' );
		$this->custom_post_type_name = 'shop_subscription';
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_enabled() {
		return $this->checker->class_exists( '\WC_REST_Subscriptions_Controller' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_triggers() {
		return array(
			new Trigger(
				'subscription.status_changed',
				__( 'Subscription status changed', 'woocommerce-zapier' ),
				// `woocommerce_subscription_status_updated` hook with our own prefix/handler to convert the arg from a WC_Subscription object to a subscription ID.
				array( 'wc_zapier_woocommerce_subscription_status_updated' )
			),
			new Trigger(
				'subscription.renewed',
				__( 'Subscription renewed', 'woocommerce-zapier' ),
				// `woocommerce_subscription_renewal_payment_complete` hook with our own prefix/handler to convert the arg from a WC_Subscription object to a subscription ID.
				array( 'wc_zapier_woocommerce_subscription_renewal_payment_complete' )
			),
			new Trigger(
				'subscription.renewal_failed',
				__( 'Subscription renewal failed', 'woocommerce-zapier' ),
				// `woocommerce_subscription_renewal_payment_failed` hook with our own prefix/handler to convert the arg from a WC_Subscription object to a subscription ID.
				array( 'wc_zapier_woocommerce_subscription_renewal_payment_failed' )
			),
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param int $resource_id Resource ID.
	 *
	 * @return string|null
	 */
	public function get_description( $resource_id ) {
		$object = \wcs_get_subscription( $resource_id );
		if ( false !== $object && is_a( $object, 'WC_Subscription' ) && 'trash' !== $object->get_status() ) {
			return $object->get_formatted_billing_full_name();
		}
		return null;
	}
}
