<?php

final class BWFAN_WCS_Trial_End extends BWFAN_Event {
	private static $instance = null;
	public $subscription_id = null;
	public $subscription = null;

	private function __construct() {
		$this->event_merge_tag_groups = array( 'wc_customer', 'wc_subscription' );
		$this->event_name             = esc_html__( 'Subscriptions Trial End', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs after a subscription trial is ended.', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'wc_subscription', 'wc_customer' );
		$this->optgroup_label         = esc_html__( 'Subscription', 'autonami-automations-pro' );
		$this->support_lang           = true;
		$this->priority               = 25.2;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'woocommerce_scheduled_subscription_trial_end', [ $this, 'subscription_trial_end' ], 11, 1 );
	}

	public function subscription_trial_end( $subscription_id ) {
		$this->process( $subscription_id );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $subscription_id
	 */
	public function process( $subscription_id ) {
		$data                       = $this->get_default_data();
		$data['wc_subscription_id'] = $subscription_id;

		$this->send_async_call( $data );
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
		$data_to_send['global']['user_id']            = is_object( $this->subscription ) ? $this->get_user_id_event() : '';

		return $data_to_send;
	}

	public function get_user_id_event() {
		return $this->subscription->get_user_id();
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
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$subscription_id = BWFAN_Merge_Tag_Loader::get_data( 'wc_subscription_id' );
		if ( ( empty( $subscription_id ) || intval( $subscription_id ) !== intval( $task_meta['global']['wc_subscription_id'] ) ) && function_exists( 'wcs_get_subscription' ) ) {
			$set_data = array(
				'wc_subscription_id' => intval( $task_meta['global']['wc_subscription_id'] ),
				'email'              => $task_meta['global']['email'],
				'wc_order'           => $task_meta['global']['wc_order'],
				'user_id'            => $task_meta['global']['user_id'],
				'wc_subscription'    => $task_meta['global']['wc_subscription'],
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	public function validate_event_data_before_executing_task( $data ) {
		return $this->validate_subscription( $data );
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		if ( ! function_exists( 'wcs_get_subscription' ) ) {
			return false;
		}

		$subscription_id       = BWFAN_Common::$events_async_data['wc_subscription_id'];
		$subscription          = wcs_get_subscription( $subscription_id );
		$this->subscription    = $subscription;
		$this->subscription_id = $subscription_id;

		return $this->run_automations();
	}

	/**
	 * Set up rules data
	 *
	 * @param $value
	 */
	public function pre_executable_actions( $value ) {
		BWFAN_Core()->rules->setRulesData( $this->subscription, 'wc_subscription' );
		BWFAN_Core()->rules->setRulesData( $this->event_automation_id, 'automation_id' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->get_email_event(), $this->get_user_id_event() ), 'bwf_customer' );
	}

	public function get_email_event() {
		return $this->subscription->get_billing_email();
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woocommerce_subscriptions_active() ) {
	return 'BWFAN_WCS_Trial_End';
}
