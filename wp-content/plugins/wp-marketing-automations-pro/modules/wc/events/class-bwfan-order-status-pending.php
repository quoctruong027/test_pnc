<?php

final class BWFAN_WC_Order_Status_Pending extends BWFAN_Event {
	private static $instance = null;
	public $user_id = 0;
	public $email = '';
	public $order_id = 0;
	/** @var $order WC_Order|null */
	public $order = null;

	private function __construct() {
		$this->event_merge_tag_groups = array( 'wc_order' );
		$this->event_name             = esc_html__( 'Order Status Pending', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs after a Pending order is created.', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'wc_order' );
		$this->optgroup_label         = esc_html__( 'Orders', 'autonami-automations-pro' );
		$this->source_type            = 'wc';
		$this->priority               = 15.2;
		$this->is_time_independent    = true;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		/** Hooked over 5 mins action when active automations found */
		add_action( 'bwfan_5_min_action', array( $this, 'checking_eligible_orders' ) );

		add_action( 'bwfan_on_wc_order_status_pending', array( $this, 'process' ) );
	}

	public function checking_eligible_orders( $active_automations ) {
		/** Filter active automations for this event */
		$event_automations = array_filter( $active_automations, function ( $automation ) {
			return $this->get_slug() === $automation['event'];
		} );

		if ( ! count( $event_automations ) > 0 ) {
			return;
		}

		/** Fetching pending state orders with a gap of 10 mins default to current time and a 3 hr interval */
		$mins_gap  = apply_filters( 'bwfan_wc_pending_order_mins_gap', 10 );
		$mins_gap  = absint( $mins_gap ) > 0 ? absint( $mins_gap ) : 10;
		$to        = time() - ( MINUTE_IN_SECONDS * $mins_gap );
		$from      = apply_filters( 'bwfan_wc_pending_order_hrs_interval', 3 );
		$from      = absint( $from ) > 0 ? absint( $from ) : 3;
		$from      = $to - ( $from * HOUR_IN_SECONDS );
		$order_ids = $this->get_unpaid_orders( $from, $to );
		if ( ! is_array( $order_ids ) || ! count( $order_ids ) > 0 ) {
			return;
		}

		/** Trigger event on each order & mark the order as processed for this event */
		foreach ( $order_ids as $order_id ) {
			do_action( 'bwfan_on_wc_order_status_pending', $order_id );
		}
	}

	public function get_unpaid_orders( $from, $to ) {
		global $wpdb;

		return $wpdb->get_col( $wpdb->prepare( "SELECT distinct(posts.ID)
				FROM {$wpdb->posts} AS posts
				WHERE   posts.post_type = 'shop_order'
				AND     posts.post_status = 'wc-pending'
				AND     posts.post_date_gmt > %s
				AND     posts.post_date_gmt < %s
				AND     (SELECT count(*) FROM {$wpdb->postmeta} where post_id = posts.ID and meta_key = '_bwfan_pending_checked') = 0", gmdate( 'Y-m-d H:i:s', absint( $from ) ), gmdate( 'Y-m-d H:i:s', absint( $to ) ) ) );
	}

	public function process( $order_id ) {
		$order = $order_id > 0 ? wc_get_order( $order_id ) : false;
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$data             = $this->get_default_data();
		$data['order_id'] = $order_id;

		$this->send_async_call( $data );
	}

	/**
	 * Set up rules data
	 *
	 * @param $automation_data
	 */
	public function pre_executable_actions( $automation_data ) {
		BWFAN_Core()->rules->setRulesData( $this->user_id, 'user_id' );
		BWFAN_Core()->rules->setRulesData( $this->email, 'email' );
		BWFAN_Core()->rules->setRulesData( $this->order, 'wc_order' );
		BWFAN_Core()->rules->setRulesData( $this->order_id, 'wc_order_id' );
		BWFAN_Core()->rules->setRulesData( $this->order_id, 'order_id' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->email, $this->user_id ), 'bwf_customer' );
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
		$data_to_send                          = [];
		$data_to_send['global']['user_id']     = $this->user_id;
		$data_to_send['global']['email']       = $this->email;
		$data_to_send['global']['wc_order_id'] = $this->order_id;
		$data_to_send['global']['order_id']    = $this->order_id;
		$data_to_send['global']['wc_order']    = $this->order;

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
		if ( absint( $global_data['wc_order_id'] ) > 0 ) {
			$order = wc_get_order( absint( $global_data['wc_order_id'] ) );
			if ( $order instanceof WC_Order ) { ?>
                <li>
                    <strong><?php echo esc_html__( 'Order: ', 'autonami-automations-pro' ); ?></strong>
                    <a target="_blank" href="<?php echo get_edit_post_link( $global_data['order_id'] ); //phpcs:ignore WordPress.Security.EscapeOutput
					?>"><?php echo '#' . esc_attr( $global_data['order_id'] . ' ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?></a>
                </li>
				<?php
			}
		}
		?>
        <li>
            <strong><?php echo esc_html__( 'Email: ', 'autonami-automations-pro' ); ?></strong>
			<?php echo esc_html__( $global_data['email'] ); ?>
        </li>
		<?php
		return ob_get_clean();
	}

	/**
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$set_data = array(
			'user_id'     => $task_meta['global']['user_id'],
			'email'       => $task_meta['global']['email'],
			'wc_order_id' => $task_meta['global']['wc_order_id'],
			'order_id'    => $task_meta['global']['order_id'],
			'wc_order'    => $task_meta['global']['wc_order']
		);
		BWFAN_Merge_Tag_Loader::set_data( $set_data );
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$this->order_id = BWFAN_Common::$events_async_data['order_id'];
		$this->order    = wc_get_order( $this->order_id );
		if ( ! $this->order instanceof WC_Order ) {
			return false;
		}

		update_post_meta( $this->order_id, '_bwfan_pending_checked', 1 );

		$this->user_id = $this->order->get_user_id();
		$this->email   = $this->order->get_billing_email();

		return $this->run_automations();
	}

	public function get_email_event() {
		$email = is_email( $this->email ) ? $this->email : false;
		$email = ( false === $email && $this->user_id > 0 ) ? get_user_by( 'id', $this->user_id ) : false;
		$email = ! $email instanceof WP_User ? $email->user_email : false;
		$email = is_email( $email ) ? $email : false;

		return $email;
	}

	public function get_user_id_event() {
		$user_id = $this->user_id > 0 ? $this->user_id : false;
		$user_id = ( false === $user_id && $this->order instanceof WC_Order ) ? $this->order->get_user_id() : false;
		$user_id = ( false === $user_id && is_email( $this->email ) ) ? get_user_by( 'email', $this->email ) : false;
		$user_id = $user_id instanceof WP_User ? $user_id->ID : false;

		return $user_id;
	}

	public function validate_event_data_before_executing_task( $data ) {
		if ( ! isset( $data['order_id'] ) ) {
			return false;
		}

		$order = wc_get_order( absint( $data['order_id'] ) );
		if ( $order instanceof WC_Order ) {
			return ( 'pending' === $order->get_status() );
		}

		$order = isset( $data['wc_order_id'] ) ? wc_get_order( absint( $data['wc_order_id'] ) ) : false;
		if ( ! $order instanceof WC_Order ) {
			return false;
		}

		return ( 'pending' === $order->get_status() );
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_woocommerce_active() ) {
	return 'BWFAN_WC_Order_Status_Pending';
}
