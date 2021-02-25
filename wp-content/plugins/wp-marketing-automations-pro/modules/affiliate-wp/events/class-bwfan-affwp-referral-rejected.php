<?php

final class BWFAN_AFFWP_Referral_Rejected extends BWFAN_Event {
	private static $instance = null;
	public $referral_id = false;
	public $affiliate_id = false;
	public $old_status = '';
	public $user_id = 0;

	private function __construct() {
		$this->event_merge_tag_groups = array( 'aff_affiliate', 'aff_referral' );
		$this->event_name             = esc_html__( 'Referral Rejected', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs after a referral is rejected.', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'affiliatewp' );
		$this->optgroup_label         = esc_html__( 'AffiliateWP', 'autonami-automations-pro' );
		$this->priority               = 50;
		$this->customer_email_tag     = '{{affwp_affiliate_email}}';
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'affwp_set_referral_status', [ $this, 'process' ], 10, 3 );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $referral_id
	 * @param $new_status
	 * @param $old_status
	 */
	public function process( $referral_id, $new_status, $old_status ) {
		if ( 'rejected' !== $new_status || $old_status === $new_status ) {
			return;
		}
		$data                = $this->get_default_data();
		$data['referral_id'] = $referral_id;
		$data['old_status']  = $old_status;

		$this->send_async_call( $data );
	}

	/**
	 * Set up rules data
	 *
	 * @param $automation_data
	 */
	public function pre_executable_actions( $automation_data ) {
		BWFAN_Core()->rules->setRulesData( $this->affiliate_id, 'affiliate_id' );
		BWFAN_Core()->rules->setRulesData( $this->referral_id, 'referral_id' );
		BWFAN_Core()->rules->setRulesData( $this->user_id, 'user_id' );
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
		$data_to_send                           = [];
		$data_to_send['global']['referral_id']  = $this->referral_id;
		$data_to_send['global']['affiliate_id'] = $this->affiliate_id;
		$data_to_send['global']['old_status']   = $this->old_status;
		$data_to_send['global']['user_id']      = $this->user_id;

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
		$affiliate_id = isset( $global_data['affiliate_id'] ) ? $global_data['affiliate_id'] : 0;
		?>
        <li>
            <strong><?php echo esc_html__( 'Affiliate ID:', 'autonami-automations-pro' ); ?> </strong>
            <a target="_blank" href="<?php echo admin_url( 'admin.php' ) . '?page=affiliate-wp-affiliates&affiliate_id=' . $affiliate_id . '&action=edit_affiliate'; //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo esc_html__( $affiliate_id ); ?></a>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Referral ID:', 'autonami-automations-pro' ); ?> </strong>
            <span><?php echo esc_html__( $global_data['referral_id'] ); ?></span>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Old Status:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html__( $global_data['old_status'] ); ?>
        </li>
        <li>
            <strong><?php echo esc_html__( 'New Status:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html__( 'rejected' ); ?>
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
		$get_data = BWFAN_Merge_Tag_Loader::get_data( 'referral_id' );
		if ( ( empty( $get_data ) || intval( $get_data ) !== intval( $task_meta['global']['referral_id'] ) ) ) {
			$set_data = array(
				'affiliate_id' => intval( $task_meta['global']['affiliate_id'] ),
				'referral_id'  => intval( $task_meta['global']['referral_id'] ),
				'user_id'      => intval( $task_meta['global']['user_id'] ),
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$this->referral_id  = BWFAN_Common::$events_async_data['referral_id'];
		$this->old_status   = BWFAN_Common::$events_async_data['old_status'];
		$referral           = affwp_get_referral( $this->referral_id );
		$this->affiliate_id = $referral->affiliate_id;
		$this->user_id      = affwp_get_affiliate_user_id( $this->affiliate_id );

		return $this->run_automations();
	}

	public function get_email_event() {
		if ( ! empty( absint( $this->user_id ) ) ) {
			$user = get_user_by( 'id', absint( $this->user_id ) );

			return ( $user instanceof WP_User ) ? $user->user_email : false;
		}

		return false;
	}

	public function get_user_id_event() {
		return ! empty( absint( $this->user_id ) ) ? absint( $this->user_id ) : false;
	}


}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_affiliatewp_active() ) {
	return 'BWFAN_AFFWP_Referral_Rejected';
}
