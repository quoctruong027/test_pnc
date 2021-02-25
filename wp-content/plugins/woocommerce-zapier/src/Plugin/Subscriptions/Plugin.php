<?php

namespace OM4\WooCommerceZapier\Plugin\Subscriptions;

use OM4\WooCommerceZapier\Helper\FeatureChecker;
use OM4\WooCommerceZapier\Logger;
use OM4\WooCommerceZapier\Plugin\Subscriptions\ResourceDefinition;
use WC_Subscription;
use WC_Subscriptions;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality that is enabled when the WooCommerce Subscriptions plugin is active.
 *
 * @since 2.0.0
 */
class Plugin {

	/**
	 * FeatureChecker instance.
	 *
	 * @var FeatureChecker
	 */
	protected $checker;

	/**
	 * FeatureChecker instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * ResourceDefinition instance.
	 *
	 * @var ResourceDefinition
	 */
	protected $resource_definition;

	/**
	 * The minimum WooCommerce Subscriptions version that this plugin supports.
	 */
	const MINIMUM_SUPPORTED_SUBSCRIPTIONS_VERSION = '2.4.3';

	/**
	 * Constructor.
	 *
	 * @param FeatureChecker     $checker FeatureChecker instance.
	 * @param Logger             $logger Logger instance.
	 * @param ResourceDefinition $resource_definition Resource Definition.
	 */
	public function __construct( FeatureChecker $checker, Logger $logger, ResourceDefinition $resource_definition ) {
		$this->checker             = $checker;
		$this->logger              = $logger;
		$this->resource_definition = $resource_definition;
	}

	/**
	 * Instructs the Subscriptions functionality to initialise itself.
	 *
	 * @return void
	 */
	public function initialise() {

		if ( ! $this->is_active() ) {
			return;
		}

		if ( ! $this->is_supported_version() ) {
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
			$this->logger->alert(
				'WooCommerce Subscriptions plugin version (%s) is less than %s',
				array( WC_Subscriptions::$version, self::MINIMUM_SUPPORTED_SUBSCRIPTIONS_VERSION )
			);
			return;
		}

		add_filter( 'wc_zapier_additional_resource_classes', array( $this, 'wc_zapier_additional_resource_classes' ) );

		foreach ( $this->resource_definition->get_triggers() as $trigger ) {
			foreach ( $trigger->get_actions() as $action ) {
				if ( 0 === strpos( $action, 'wc_zapier_' ) ) {
					$action = str_replace( 'wc_zapier_', '', $action );
					add_action( $action, array( $this, 'convert_arg_to_subscription_id_then_execute' ) );
				}
			}
		}
	}

	/**
	 * Whenever a relevant WooCommerce Subscriptions built-in action/event occurs,
	 * convert the args WC_Subscription object into a numerical subscription ID,
	 * and then trigger our own built-in action which then queues the webhook for delivery.
	 *
	 * @param WC_Subscription $arg Subscription object.
	 *
	 * @return void
	 */
	public function convert_arg_to_subscription_id_then_execute( $arg ) {
		if ( ! is_a( $arg, WC_Subscription::class ) ) {
			return;
		}
		$arg = $arg->get_id();
		/**
		 * Execute the WooCommerce Zapier handler for this hook/action.
		 *
		 * @internal
		 * @since 2.0.4
		 *
		 * @param int $arg Subscription ID.
		 */
		do_action( 'wc_zapier_' . current_action(), $arg );
	}

	/**
	 * Add our Subscriptions Resource class to the WC Zapier Plugins' Resource Manager.
	 *
	 * Executed by the `wc_zapier_additional_resource_classes` filter.
	 *
	 * @param array $resources Resource Class Name(s).
	 *
	 * @return array
	 */
	public function wc_zapier_additional_resource_classes( $resources ) {
		$resources[] = ResourceDefinition::class;
		return $resources;
	}

	/**
	 * Whether or not the running a version of WooCommerce Subscriptoins is newer than our minimum supported version.
	 *
	 * @return bool
	 */
	protected function is_supported_version() {
		return $this->is_active() ? version_compare( WC_Subscriptions::$version, self::MINIMUM_SUPPORTED_SUBSCRIPTIONS_VERSION, '>=' ) : false;
	}

	/**
	 * Whether not not the user has the WooCommerce Subscriptions plugin active.
	 *
	 * @return bool
	 */
	protected function is_active() {
		return class_exists( '\WC_Subscriptions' );
	}

	/**
	 * Displays a message if the user isn't using a supported version of WooCommerce Subscriptions.
	 *
	 * @return void
	 */
	public function admin_notice() {
		?>
		<div id="message" class="error">
			<p>
				<?php
				// Translators: %s: MINIMUM_SUPPORTED_SUBSCRIPTIONS_VERSION Supported Woocommerce Subscription Version.
				echo esc_html( sprintf( __( 'WooCommerce Zapier is only compatible with WooCommerce Subscriptions version %s or later. Please update WooCommerce Subscriptions.', 'woocommerce-zapier' ), self::MINIMUM_SUPPORTED_SUBSCRIPTIONS_VERSION ) );
				?>
			</p>
		</div>
		<?php
	}
}
