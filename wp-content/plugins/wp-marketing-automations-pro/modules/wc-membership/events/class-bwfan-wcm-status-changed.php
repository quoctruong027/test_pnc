<?php

final class BWFAN_WCM_Status_Changed extends BWFAN_Event {

	private static $instance = null;
	/** @var WC_Memberships_User_Membership $user_membership */
	public $user_membership = null;
	/** @var string $user_membership */
	public $status = null;

	public function __construct() {
		$this->event_name             = esc_html__( 'Membership Status Changed', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs after a WooCommerce membership status is changed.', 'autonami-automations-pro' );
		$this->optgroup_label         = esc_html__( 'Membership', 'autonami-automations-pro' );
		$this->priority               = 25;
		$this->event_merge_tag_groups = [ 'wc_membership' ];
		$this->event_rule_groups      = array( 'wc_member' );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'wc_memberships_user_membership_status_changed', [ $this, 'membership_status_changed_triggered' ], 20, 3 );
	}

	/**
	 * Membership status changed triggered
	 *
	 * @param \WC_Memberships_User_Membership $user_membership
	 * @param string $old_status
	 * @param string $new_status
	 */
	public function membership_status_changed_triggered( $user_membership, $old_status, $new_status ) {
		$user_membership_id = $user_membership->get_id();
		$status             = $new_status;
		$this->process( $user_membership_id, $status );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param int $user_membership_id
	 * @param string status
	 */
	public function process( $user_membership_id, $status ) {
		$data                       = $this->get_default_data();
		$data['user_membership_id'] = $user_membership_id;
		$data['status']             = $status;

		$this->send_async_call( $data );
	}

	/**
	 * Capture the async data for thes if the task has to be executed or not.
	 *
	 * @return array|bool
	 */
	public function capture_async_data() {
		if ( ! function_exists( 'wc_memberships_get_user_membership' ) ) {
			return false;
		}
		$user_membership_id    = BWFAN_Common::$events_async_data['user_membership_id'];
		$status                = BWFAN_Common::$events_async_data['status'];
		$user_membership       = wc_memberships_get_user_membership( $user_membership_id );
		$this->user_membership = $user_membership;
		$this->status          = $status;

		$this->run_automations();
	}

	public function get_email_event() {
		if ( $this->user_membership instanceof WC_Memberships_User_Membership ) {
			$user = $this->user_membership->get_user();

			return ! empty( $user ) ? $user->user_email : false;
		}

		return false;
	}

	public function get_user_id_event() {
		return $this->user_membership instanceof WC_Memberships_User_Membership ? $this->user_membership->get_user_id() : false;
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
		$data_to_send                                        = [];
		$data_to_send['global']['wc_user_membership_id']     = is_object( $this->user_membership ) ? $this->user_membership->get_id() : '';
		$data_to_send['global']['email']                     = is_object( $this->user_membership ) ? $this->user_membership->get_user()->get( 'user_email' ) : '';
		$data_to_send['global']['user_id']                   = is_object( $this->user_membership ) ? $this->user_membership->get_user_id() : '';
		$data_to_send['global']['wc_user_membership_status'] = ! empty( $this->status ) ? $this->status : '';

		return $data_to_send;
	}

	public function pre_executable_actions( $automation_data ) {
		if ( function_exists( 'wc_memberships_get_user_membership' ) && $this->user_membership instanceof WC_Memberships_User_Membership ) {
			BWFAN_Core()->rules->setRulesData( $this->user_membership->get_id(), 'wc_user_membership_id' );
			BWFAN_Core()->rules->setRulesData( $this->user_membership->get_user()->get( 'user_email' ), 'email' );
			BWFAN_Core()->rules->setRulesData( $this->user_membership->get_user_id(), 'user_id' );
		}

		if ( ! empty( $this->status ) ) {
			BWFAN_Core()->rules->setRulesData( $this->status, 'wc_user_membership_status' );
		}
	}

	/**
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$get_data = BWFAN_Merge_Tag_Loader::get_data( 'wc_user_membership_id' );
		if ( ( empty( $get_data ) || intval( $get_data ) !== intval( $task_meta['global']['wc_user_membership_id'] ) ) && function_exists( 'wc_memberships_get_user_membership' ) ) {
			$set_data = array(
				'wc_user_membership_id'     => $task_meta['global']['wc_user_membership_id'],
				'email'                     => $task_meta['global']['email'],
				'user_id'                   => $task_meta['global']['user_id'],
				'wc_user_membership_status' => $task_meta['global']['wc_user_membership_status'],
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
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
            <strong><?php echo esc_html__( 'Membership ID:', 'autonami-automations-pro' ); ?> </strong>
            <a target="_blank" href="<?php echo get_edit_post_link( $global_data['wc_user_membership_id'] ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo esc_html__( '#' . $global_data['wc_user_membership_id'] ); ?></a>
        </li>
        <li>
            <strong><?php echo esc_html__( 'New Status:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html__( $global_data['wc_user_membership_status'] ); ?>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Membership User Email:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html__( $global_data['email'] ); ?>
        </li>
		<?php
		return ob_get_clean();
	}


}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */

if ( bwfan_is_woocommerce_active() && bwfan_is_woocommerce_membership_active() ) {
	return 'BWFAN_WCM_Status_Changed';
}
