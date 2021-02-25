<?php

final class BWFAN_UpStroke_Funnel_Ended extends BWFAN_Event {
	private static $instance = null;
	/** @var WC_Order $order */
	public $order = null;
	public $funnel_id = null;

	private function __construct() {
		$this->event_merge_tag_groups = array( 'wc_customer', 'wc_order', 'wc_funnel' );
		$this->optgroup_label         = esc_html__( 'Funnel', 'autonami-automations-pro' );
		$this->event_name             = esc_html__( 'Funnel Ended', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs after an upsell funnel is ended.', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'wc_customer', 'upstroke_funnel' );
		$this->support_lang           = true;
		$this->priority               = 45.1;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'wfocu_funnel_ended_event', [ $this, 'funnel_ended' ], 999, 3 );
	}

	/**
	 * Set up rules data
	 *
	 * @param $value
	 */
	public function pre_executable_actions( $value ) {
		BWFAN_Core()->rules->setRulesData( $this->event_automation_id, 'automation_id' );
		BWFAN_Core()->rules->setRulesData( $this->order, 'wc_order' );
		BWFAN_Core()->rules->setRulesData( $this->funnel_id, 'upstroke_funnel_id' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->order->get_billing_email(), $this->order->get_user_id() ), 'bwf_customer' );
	}

	public function funnel_ended( $funnel_id, $order_id, $user_email ) {
		$this->process( $order_id, $funnel_id );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $order_id
	 * @param $funnel_id
	 */
	public function process( $order_id, $funnel_id ) {
		$data              = $this->get_default_data();
		$data['order_id']  = $order_id;
		$data['funnel_id'] = $funnel_id;

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
		$data_to_send['global']['order_id']  = is_object( $this->order ) ? BWFAN_Woocommerce_Compatibility::get_order_id( $this->order ) : '';
		$data_to_send['global']['wc_order']  = is_object( $this->order ) ? $this->order : '';
		$data_to_send['global']['email']     = is_object( $this->order ) ? BWFAN_Woocommerce_Compatibility::get_billing_email( $this->order ) : '';
		$data_to_send['global']['funnel_id'] = $this->funnel_id;

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

		$funnel_id = isset( $global_data['funnel_id'] ) ? $global_data['funnel_id'] : 0;
		?>
        <li>
            <strong><?php echo esc_html__( 'Funnel ID:', 'autonami-automations-pro' ); ?> </strong>
            <a target="_blank" href="<?php echo admin_url( 'admin.php' ) . '?page=upstroke&section=rules&edit=' . $funnel_id; //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo esc_html__( $funnel_id ); ?></a>
        </li>
        <li>
            <strong><?php echo esc_html__( 'User Email:', 'autonami-automations-pro' ); ?> </strong>
            <span><?php echo esc_html__( $global_data['email'] ); ?></span>
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
		$wc_order_id = BWFAN_Merge_Tag_Loader::get_data( 'wc_order_id' );
		if ( empty( $wc_order_id ) || intval( $wc_order_id ) !== intval( $task_meta['global']['order_id'] ) ) {
			$set_data = array(
				'wc_order_id' => intval( $task_meta['global']['order_id'] ),
				'email'       => $task_meta['global']['email'],
				'funnel_id'   => intval( $task_meta['global']['funnel_id'] ),
				'wc_order'    => $task_meta['global']['wc_order'],
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$order_id        = BWFAN_Common::$events_async_data['order_id'];
		$funnel_id       = BWFAN_Common::$events_async_data['funnel_id'];
		$this->funnel_id = $funnel_id;
		$this->order     = wc_get_order( $order_id );

		return $this->run_automations();
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woofunnels_upstroke_active() ) {
	return 'BWFAN_UpStroke_Funnel_Ended';
}
