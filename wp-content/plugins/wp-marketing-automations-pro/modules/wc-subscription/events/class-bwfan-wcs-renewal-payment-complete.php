<?php

final class BWFAN_WCS_Renewal_Payment_Complete extends BWFAN_Event {
	private static $instance = null;
	public $subscription = null;
	public $order = null;

	private function __construct() {
		$this->is_syncable            = true;
		$this->event_merge_tag_groups = array( 'wc_customer', 'wc_subscription' );
		$this->event_name             = esc_html__( 'Subscriptions Renewal Payment Complete', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs after a subscription renewal payment is completed.', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'wc_subscription', 'wc_customer' );
		$this->optgroup_label         = esc_html__( 'Subscription', 'autonami-automations-pro' );
		$this->support_lang           = true;
		$this->priority               = 25.5;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'woocommerce_subscription_renewal_payment_complete', [ $this, 'subscription_renewal_payment_complete' ], 11, 2 );
	}

	/**
	 * Show the html fields for the current event.
	 */
	public function get_view( $db_eventmeta_saved_value ) {
		?>
        <script type="text/html" id="tmpl-event-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            is_validated = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'validate_event')) ? 'checked' : '';
            #>
            <div class="bwfan_mt15"></div>
			<?php
			$this->get_validation_html( $this->get_slug(), 'Active subscriptions only', 'Validate' );
			?>
        </script>
		<?php
	}

	public function subscription_renewal_payment_complete( $subscription, $order ) {
		$subscription_id = $subscription->get_id();
		$order_id        = $order->get_id();
		$this->process( $subscription_id, $order_id );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $subscription_id
	 * @param $order_id
	 */
	public function process( $subscription_id, $order_id ) {
		$data                       = $this->get_default_data();
		$data['wc_subscription_id'] = $subscription_id;
		$data['order_id']           = $order_id;

		$this->send_async_call( $data );
	}

	/**
	 * Set up rules data
	 *
	 * @param $value
	 */
	public function pre_executable_actions( $value ) {
		BWFAN_Core()->rules->setRulesData( $this->order, 'wc_order' );
		BWFAN_Core()->rules->setRulesData( $this->subscription, 'wc_subscription' );
		BWFAN_Core()->rules->setRulesData( $this->event_automation_id, 'automation_id' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->order->get_billing_email(), $this->order->get_user_id() ), 'bwf_customer' );
	}

	public function validate_event_data_before_executing_task( $data ) {
		return $this->validate_subscription( $data );
	}

	/**
	 * Registers the tasks for current event.
	 *
	 * @param $automation_id
	 * @param $integration_data
	 * @param $event_data
	 */
	public function register_tasks( $automation_id, $integration_data, $event_data ) {
		if ( ! is_array( $integration_data ) ) {
			return;
		}

		$data_to_send = $this->get_event_data();

		$this->create_tasks( $automation_id, $integration_data, $event_data, $data_to_send );
	}

	public function get_event_data() {
		$data_to_send                                 = [];
		$data_to_send['global']['wc_subscription_id'] = is_object( $this->subscription ) ? $this->subscription->get_id() : '';
		$data_to_send['global']['wc_subscription']    = is_object( $this->subscription ) ? $this->subscription : '';
		$data_to_send['global']['email']              = is_object( $this->subscription ) ? $this->subscription->get_billing_email() : '';
		$data_to_send['global']['order_id']           = is_object( $this->order ) ? BWFAN_Woocommerce_Compatibility::get_order_id( $this->order ) : '';
		$data_to_send['global']['wc_order']           = is_object( $this->order ) ? $this->order : '';

		return $data_to_send;
	}

	/**
	 * Make the view data for the current event which will be shown in task listing screen.
	 *
	 * @param $global_data
	 *
	 * @return false|string
	 */
	public function get_task_view( $global_data ) {
		ob_start();
		?>
        <li>
            <strong><?php echo esc_html__( 'Subscription ID:', 'autonami-automations-pro' ); ?> </strong>
            <a target="_blank" href="<?php echo get_edit_post_link( $global_data['wc_subscription_id'] ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo esc_html__( '#' . $global_data['wc_subscription_id'] ); ?></a>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Subscription Email:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html__( $global_data['email'] ); ?>
        </li>
		<?php
		return ob_get_clean();
	}

	/**
	 * This function decides if the task has to be executed or not.
	 * The event has validate checkbox in its meta fields.
	 *
	 * @param $task_details
	 *
	 * @return array|mixed
	 */
	public function validate_event( $task_details ) {
		if ( ! function_exists( 'wcs_get_subscription' ) ) {
			return array();
		}

		$result                                     = [];
		$task_id                                    = $task_details['task_id'];
		$task_global_data                           = BWFAN_Model_Taskmeta::get_meta( $task_id, 'integration_data' );
		$subscription_id                            = $task_global_data['global']['wc_subscription_id'];
		$subscription                               = wcs_get_subscription( $subscription_id );
		$subscription_status                        = $subscription->get_status();
		$task_event                                 = $task_details['event_data']['event_slug'];
		$automation_id                              = $task_details['processed_data']['automation_id'];
		$automation_details                         = BWFAN_Model_Automations::get( $automation_id );
		$current_automation_event                   = $automation_details['event'];
		$current_automation_event_meta              = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );
		$current_automation_event_validation_status = ( isset( $current_automation_event_meta['validate_event'] ) ) ? $current_automation_event_meta['validate_event'] : 0;

		// Current automation has no checking
		if ( 0 === $current_automation_event_validation_status ) {
			$result = $this->get_automation_event_validation();

			return $result;
		}

		// Current automation event does not match with the event of task when the task was made
		if ( $task_event !== $current_automation_event ) {
			$result = $this->get_automation_event_status();

			return $result;
		}
		if ( 'active' === $subscription_status ) {
			$result = $this->get_automation_event_success();

			return $result;
		}

		$result['status']  = 4;
		$result['message'] = __( 'Subscription status has been changed', 'autonami-automations-pro' );

		return $result;
	}

	/**
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$subscription_id = BWFAN_Merge_Tag_Loader::get_data( 'wc_subscription_id' );
		$order_id        = BWFAN_Merge_Tag_Loader::get_data( 'wc_order_id' );

		if ( empty( $order_id ) || intval( $order_id ) !== intval( $task_meta['global']['order_id'] ) ) {
			$set_data = array(
				'wc_order_id' => intval( $task_meta['global']['order_id'] ),
				'email'       => $task_meta['global']['email'],
				'wc_order'    => $task_meta['global']['wc_order'],
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}

		if ( ( empty( $subscription_id ) || intval( $subscription_id ) !== intval( $task_meta['global']['wc_subscription_id'] ) ) && function_exists( 'wcs_get_subscription' ) ) {
			$set_data = array(
				'wc_subscription_id' => intval( $task_meta['global']['wc_subscription_id'] ),
				'email'              => $task_meta['global']['email'],
				'wc_subscription'    => $task_meta['global']['wc_subscription'],
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		if ( ! function_exists( 'wcs_get_subscription' ) ) {
			return false;
		}

		$subscription_id    = BWFAN_Common::$events_async_data['wc_subscription_id'];
		$order_id           = BWFAN_Common::$events_async_data['order_id'];
		$subscription       = wcs_get_subscription( $subscription_id );
		$order              = wc_get_order( $order_id );
		$this->subscription = $subscription;
		$this->order        = $order;

		return $this->run_automations();
	}

	public function get_email_event() {
		if ( $this->order instanceof WC_Order ) {
			return $this->order->get_billing_email();
		}

		if ( $this->subscription instanceof WC_Subscription ) {
			return $this->subscription->get_billing_email();
		}

		return false;
	}

	public function get_user_id_event() {
		if ( $this->order instanceof WC_Order ) {
			return $this->order->get_user_id();
		}

		if ( $this->subscription instanceof WC_Subscription ) {
			return $this->subscription->get_user_id();
		}

		return false;
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woocommerce_subscriptions_active() ) {
	return 'BWFAN_WCS_Renewal_Payment_Complete';
}
