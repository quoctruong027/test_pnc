<?php

final class BWFAN_LD_User_Removed_From_Course extends BWFAN_Event {
	private static $instance = null;
	public $user_id = 0;
	public $course_id = 0;
	public $access_list = 0;
	public $email = '';

	private function __construct() {
		$this->event_merge_tag_groups = array( 'learndash_user', 'learndash_course' );
		$this->event_name             = __( 'User Removed from a Course', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs when the user is removed from a course.', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'learndash_course' );
		$this->optgroup_label         = __( 'LearnDash', 'autonami-automations-pro' );
		$this->priority               = 20;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'learndash_update_course_access', [ $this, 'process' ], 10, 4 );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $user_id
	 * @param $course_id
	 * @param $access_list
	 * @param $remove
	 */
	public function process( $user_id, $course_id, $access_list, $remove ) {
		if ( true !== $remove ) {
			return;
		}

		$data                = $this->get_default_data();
		$data['user_id']     = $user_id;
		$data['course_id']   = $course_id;
		$data['access_list'] = $access_list;

		$user          = get_user_by( 'id', absint( $user_id ) );
		$data['email'] = $user instanceof WP_User && is_email( $user->user_email ) ? $user->user_email : '';

		$this->send_async_call( $data );
	}

	/**
	 * Set up rules data
	 *
	 * @param $automation_data
	 */
	public function pre_executable_actions( $automation_data ) {
		BWFAN_Core()->rules->setRulesData( $this->user_id, 'user_id' );
		BWFAN_Core()->rules->setRulesData( $this->course_id, 'course_id' );
		BWFAN_Core()->rules->setRulesData( $this->access_list, 'access_list' );
		BWFAN_Core()->rules->setRulesData( $this->email, 'email' );
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
		$data_to_send['global']['course_id']   = $this->course_id;
		$data_to_send['global']['access_list'] = $this->access_list;
		$data_to_send['global']['email']       = $this->email;

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
		$user   = get_user_by( 'id', $global_data['user_id'] );
		$course = get_post( $global_data['course_id'] );
		?>
        <li>
            <strong><?php echo esc_html__( 'User:', 'autonami-automations-pro' ); ?> </strong>
            <a target="_blank" href="<?php echo admin_url() . '?user-edit.php?user_id=' . $user->ID; //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo esc_html__( $user->user_nicename ); ?></a>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Course:', 'autonami-automations-pro' ); ?> </strong>
            <a target="_blank" href="<?php echo admin_url() . 'post.php?post=' . $course->ID . '&action=edit'; //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo esc_html__( $course->post_title ); ?></a>
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
		$get_data = BWFAN_Merge_Tag_Loader::get_data( 'course_id' );
		if ( ( empty( $get_data ) || intval( $get_data ) !== intval( $task_meta['global']['course_id'] ) ) ) {
			$set_data = array(
				'user_id'     => intval( $task_meta['global']['user_id'] ),
				'course_id'   => intval( $task_meta['global']['course_id'] ),
				'access_list' => $task_meta['global']['access_list'],
				'email'       => $task_meta['global']['email'],
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	public function get_email_event() {
		return $this->email;
	}

	public function get_user_id_event() {
		return $this->user_id;
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$this->user_id     = BWFAN_Common::$events_async_data['user_id'];
		$this->course_id   = BWFAN_Common::$events_async_data['course_id'];
		$this->access_list = BWFAN_Common::$events_async_data['access_list'];

		return $this->run_automations();
	}


}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_learndash_active() ) {
	return 'BWFAN_LD_User_Removed_From_Course';
}
