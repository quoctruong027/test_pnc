<?php

final class BWFAN_WCM_Membership_Created extends BWFAN_Event {

	private static $instance = null;

	/** @var WC_Memberships_Membership_Plan $membership_plan */
	public $membership_plan = null;
	/** @var WC_Memberships_User_Membership $user_membership */
	public $user_membership = null;

	public function __construct() {
		$this->event_name             = esc_html__( 'Membership Created', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs after a WooCommerce membership is created.', 'autonami-automations-pro' );
		$this->optgroup_label         = esc_html__( 'Membership', 'autonami-automations-pro' );
		$this->support_lang           = true;
		$this->priority               = 25;
		$this->event_merge_tag_groups = [ 'wc_membership' ];
		$this->is_syncable            = true;
		$this->event_rule_groups      = array( 'wc_member' );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'wc_memberships_user_membership_created', [ $this, 'membership_created_triggered' ], 20, 2 );
		add_action( 'bwfan_process_old_records_for_wcm_membership_created', array( $this, 'sync_old_automation_records' ), 10, 4 );
		add_filter( 'bwfan_before_making_logs', array( $this, 'check_if_bulk_process_executing' ), 10, 1 );
	}

	public function membership_created_triggered( $membership_plan, $user_membership_data ) {
		//Proceed only if membership is created
		if ( false === $user_membership_data['is_update'] ) {
			$membership_plan_id = $membership_plan->get_id();
			$user_membership_id = $user_membership_data['user_membership_id'];
			$this->process( $membership_plan_id, $user_membership_id );
		}
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $membership_plan_id
	 * @param $user_membership_id
	 */
	public function process( $membership_plan_id, $user_membership_id ) {
		$data                       = $this->get_default_data();
		$data['membership_plan_id'] = $membership_plan_id;
		$data['user_membership_id'] = $user_membership_id;

		$this->send_async_call( $data );
	}

	public function pre_executable_actions( $automation_data ) {
		if ( $this->user_membership instanceof WC_Memberships_User_Membership ) {
			BWFAN_Core()->rules->setRulesData( $this->user_membership->get_id(), 'wc_user_membership_id' );
			BWFAN_Core()->rules->setRulesData( $this->user_membership->get_user()->get( 'user_email' ), 'email' );
			BWFAN_Core()->rules->setRulesData( $this->user_membership->get_user_id(), 'user_id' );
		}

		if ( $this->membership_plan instanceof WC_Memberships_Membership_Plan ) {
			BWFAN_Core()->rules->setRulesData( $this->membership_plan->get_id(), 'wc_membership_plan_id' );
		}
	}

	/**
	 * Capture the async data for thes if the task has to be executed or not.
	 *
	 * @return array|bool
	 */
	public function capture_async_data() {
		if ( ! function_exists( 'wc_memberships_get_membership_plan' ) || ! function_exists( 'wc_memberships_get_user_membership' ) ) {
			return false;
		}

		$membership_plan_id    = BWFAN_Common::$events_async_data['membership_plan_id'];
		$user_membership_id    = BWFAN_Common::$events_async_data['user_membership_id'];
		$membership_plan       = wc_memberships_get_membership_plan( $membership_plan_id );
		$user_membership       = wc_memberships_get_user_membership( $user_membership_id );
		$this->membership_plan = $membership_plan;
		$this->user_membership = $user_membership;

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
	 * Return the old records for current event.
	 * This function is used during sync process.
	 *
	 * @param $automation_meta
	 *
	 * @return array|mixed
	 */
	public function get_event_records( $automation_meta ) {
		if ( ! function_exists( 'wc_memberships_get_user_membership' ) ) {
			return array();
		}

		$query_args = array(
			'orderby'        => 'date',
			'order'          => 'asc',
			'posts_per_page' => - 1,
			'post_type'      => 'wc_user_membership',
			'post_status'    => BWFAN_PRO_Common::get_wc_membership_active_statuses(),
		);

		if ( ! is_null( $this->display_count ) ) {
			$query_args['posts_per_page'] = $this->display_count;
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

		$memberships = new WP_Query( $query_args );
		$memberships = array_map( 'wc_memberships_get_user_membership', $memberships->posts );

		return $memberships;
	}

	/**
	 * Run automations on all the old records of the current event.
	 * This function is used in sync process.
	 *
	 * @param $memberships
	 */
	public function process_event_records( $memberships ) {
		if ( empty( $memberships ) ) {
			return;
		}

		foreach ( $memberships as $membership ) {
			$this->sync_start_time ++;
			// make the tasks from here
			$this->membership_plan = $membership->get_plan();
			$this->user_membership = $membership;
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
		$membership_date = $this->user_membership->get_start_date();
		$membership_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $membership_date );
		$actions         = $this->calculate_actions_time( $actions, $membership_date );

		return $actions;
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
		$data_to_send                                    = [];
		$data_to_send['global']['wc_user_membership_id'] = is_object( $this->user_membership ) ? $this->user_membership->get_id() : '';
		$data_to_send['global']['wc_membership_plan_id'] = is_object( $this->membership_plan ) ? $this->membership_plan->get_id() : '';
		$data_to_send['global']['email']                 = is_object( $this->user_membership ) ? $this->user_membership->get_user()->get( 'user_email' ) : '';
		$data_to_send['global']['user_id']               = is_object( $this->user_membership ) ? $this->user_membership->get_user_id() : '';

		return $data_to_send;
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
				'wc_user_membership_id' => $task_meta['global']['wc_user_membership_id'],
				'wc_membership_plan_id' => $task_meta['global']['wc_membership_plan_id'],
				'email'                 => $task_meta['global']['email'],
				'user_id'               => $task_meta['global']['user_id'],
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
            <strong><?php echo esc_html__( 'Selected Membership Plan ID:', 'autonami-automations-pro' ); ?> </strong>
            <a target="_blank" href="<?php echo get_edit_post_link( $global_data['wc_membership_plan_id'] ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo esc_html__( '#' . $global_data['wc_membership_plan_id'] ); ?></a>
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
	return 'BWFAN_WCM_Membership_Created';
}
