<?php

namespace OM4\WooCommerceZapier\TaskHistory\Listener;

use OM4\WooCommerceZapier\Logger;
use OM4\WooCommerceZapier\TaskHistory\TaskDataStore;
use OM4\WooCommerceZapier\Webhook\Topics as WebhookTopics;
use OM4\WooCommerceZapier\Webhook\ZapierWebhook;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Listener to detect when WooCommerce delivers data to Zapier via our Webhooks,
 * and record the event to our Task History.
 *
 * @since 2.0.0
 */
class TriggerListener {


	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * TaskDataStore instance.
	 *
	 * @var TaskDataStore
	 */
	protected $data_store;

	/**
	 * WebhookTopicsLists instance.
	 *
	 * @var WebhookTopics
	 */
	protected $webhook_topics;

	/**
	 * TriggerListener constructor.
	 *
	 * @param Logger        $logger         Logger.
	 * @param TaskDataStore $data_store     TaskDataStore instance.
	 * @param WebhookTopics $webhook_topics Webhook Topics.
	 *
	 * @return void
	 */
	public function __construct(
		Logger $logger,
		TaskDataStore $data_store,
		WebhookTopics $webhook_topics
	) {
		$this->logger         = $logger;
		$this->data_store     = $data_store;
		$this->webhook_topics = $webhook_topics;
	}

	/**
	 * Instructs the functionality to initialise itself.
	 *
	 * @return void
	 */
	public function initialise() {
		add_action( 'woocommerce_webhook_delivery', array( $this, 'woocommerce_webhook_delivery' ), 10, 5 );
	}

	/**
	 * Whenever WooCommerce delivers a payload to a WC Zapier webhook, add the event to our task history.
	 *
	 * Executed by the `woocommerce_webhook_delivery` hook (which occurs for all Webhooks not just Zapier Webhooks)
	 *
	 * @param array          $http_args HTTP request arguments.
	 * @param WP_Error|array $response HTTP response or WP_Error on webhook delivery failure.
	 * @param float          $duration Delivery duration (in microseconds).
	 * @param mixed          $arg Usually the resource ID.
	 * @param int            $webhook_id ID Webhook ID.
	 *
	 * @return void
	 */
	public function woocommerce_webhook_delivery( $http_args, $response, $duration, $arg, $webhook_id ) {
		$webhook = new ZapierWebhook( $webhook_id );
		if ( 0 === $webhook->get_id() ) {
			// Webhook doesn't exist.
			return;
		}

		if ( ! $webhook->is_zapier_webhook() ) {
			return;
		};

		$task = $this->data_store->new_task();
		$task->set_webhook_id( $webhook->get_id() );
		$task->set_type( 'trigger' );
		$task->set_resource_type( $webhook->get_resource() );
		$task->set_resource_id( $arg );

		if ( is_wp_error( $response ) ) {
			// Webhook delivery failed.
			$task->set_message(
				sprintf(
					// Translators: 1: Error Code. 2: Error Message.
					__( 'Error sending to Zapier. Error Code: %1$s. Error Message: %2$s', 'woocommerce-zapier' ),
					$response->get_error_code(),
					$response->get_error_message()
				)
			);
		} else {
			// Successful delivery.
			$task->set_message( __( 'Sent to Zapier successfully', 'woocommerce-zapier' ) );
		}

		if ( 0 === $task->save() ) {
			$this->logger->critical(
				'Error creating task history record for webhook ID %d, topic %s, args ID %s',
				array(
					$webhook->get_id(),
					$webhook->get_topic(),
					$arg,
				)
			);
		}
	}
}