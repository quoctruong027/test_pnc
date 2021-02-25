<?php

final class BWFAN_WCS_Created extends BWFAN_Event {
	private static $instance = null;
	public $subscription = null;
	public $order = null;

	private function __construct() {
		$this->event_merge_tag_groups = array( 'wc_customer', 'wc_subscription' );
		$this->event_name             = esc_html__( 'Subscriptions Created', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs after a new subscription is created.', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'wc_subscription', 'wc_customer' );
		$this->optgroup_label         = esc_html__( 'Subscription', 'autonami-automations-pro' );
		$this->support_lang           = true;
		$this->priority               = 25;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'woocommerce_checkout_subscription_created', [ $this, 'subscription_created' ], 20, 2 );
		add_action( 'bwfan_process_old_records_for_wcs_created', array( $this, 'sync_old_automation_records' ), 10, 4 );
		add_filter( 'bwfan_before_making_logs', array( $this, 'check_if_bulk_process_executing' ), 10, 1 );
	}

	public function subscription_created( $subscription, $order ) {
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
	 * @param $automation_data
	 */
	public function pre_executable_actions( $automation_data ) {
		BWFAN_Core()->rules->setRulesData( $this->order, 'wc_order' );
		BWFAN_Core()->rules->setRulesData( $this->subscription, 'wc_subscription' );
		BWFAN_Core()->rules->setRulesData( $this->event_automation_id, 'automation_id' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->order->get_billing_email(), $this->order->get_user_id() ), 'bwf_customer' );
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
		$data_to_send['global']['wc_order']           = is_object( $this->order ) ? $this->order : '';
		$data_to_send['global']['wc_subscription_id'] = is_object( $this->subscription ) ? $this->subscription->get_id() : '';
		$data_to_send['global']['wc_subscription']    = is_object( $this->subscription ) ? $this->subscription : '';
		$data_to_send['global']['email']              = is_object( $this->subscription ) ? $this->subscription->get_billing_email() : '';

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

	/**
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$get_data = BWFAN_Merge_Tag_Loader::get_data( 'wc_subscription_id' );
		if ( ( empty( $get_data ) || intval( $get_data ) !== intval( $task_meta['global']['wc_subscription_id'] ) ) && function_exists( 'wcs_get_subscription' ) ) {
			$set_data = array(
				'wc_subscription_id' => intval( $task_meta['global']['wc_subscription_id'] ),
				'email'              => $task_meta['global']['email'],
				'wc_subscription'    => $task_meta['global']['wc_subscription'],
				'wc_order'           => $task_meta['global']['wc_order'],
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	/**
	 * Batch Processing
	 * Return the old records for current event.
	 * This function is used during sync process.
	 *
	 * @param $automation_meta
	 *
	 * @return array|mixed
	 */
	public function get_event_records( $automation_meta ) {
		if ( ! function_exists( 'wcs_get_subscriptions' ) ) {
			return array();
		}

		$query_args = array(
			'orderby'                => 'date',
			'order'                  => 'asc',
			'subscriptions_per_page' => - 1,
			'fields'                 => 'ids',
			'subscription_status'    => array( 'active' ),
		);

		if ( ! is_null( $this->display_count ) ) {
			$query_args['subscriptions_per_page'] = $this->display_count;
		}
		if ( ! is_null( $this->page ) ) {
			$query_args['paged'] = $this->page;
		}
		if ( ! is_null( $this->offset ) ) {
			$query_args['offset'] = $this->offset;
		}

		$query_args['date_query'] = array(
			array(
				'after'     => array(
					'year'  => $this->from_year,
					'month' => $this->from_month,
					'day'   => $this->from_day,
				),
				'before'    => array(
					'year'  => $this->to_year,
					'month' => $this->to_month,
					'day'   => $this->to_day,
				),
				'inclusive' => true,
			),
		);

		$subscriptions = wcs_get_subscriptions( $query_args );

		return $subscriptions;
	}

	/**
	 * Batch processing
	 * Run automations on all the old records of the current event.
	 * This function is used in sync process.
	 *
	 * @param $subscriptions
	 */
	public function process_event_records( $subscriptions ) {
		if ( empty( $subscriptions ) ) {
			return;
		}

		foreach ( $subscriptions as $subscription ) {
			$this->sync_start_time ++;
			// make the tasks from here
			$this->subscription = $subscription;
			$this->order        = $subscription->get_parent();
			$this->run_automations();

			$this->offset ++;
			$this->processed ++;

			$data = array(
				'offset'    => $this->offset,
				'processed' => $this->processed,
			);
			$this->update_sync_record( $this->sync_id, $data );
		}
	}

	public function validate_event_data_before_executing_task( $data ) {
		return $this->validate_subscription( $data );
	}

	/**
	 * Recalculate action's execution time with respect to order date.
	 * eg.
	 * today is 22 jan.
	 * order was placed on 17 jan.
	 * user set an email to send after 10 days of order placing.
	 * user setup the sync process.
	 * email should be sent on 27 Jan as the order date was 17 jan.
	 *
	 * @param $actions
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function recalculate_actions_time( $actions ) {
		$subscription_date = $this->subscription->get_date( 'start' );
		$subscription_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $subscription_date );
		$actions           = $this->calculate_actions_time( $actions, $subscription_date );

		return $actions;
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

		$this->run_automations();
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woocommerce_subscriptions_active() ) {
	return 'BWFAN_WCS_Created';
}
