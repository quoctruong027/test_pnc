<?php

final class BWFAN_LD_User_Completes_Course extends BWFAN_Event {
	private static $instance = null;
	/** @var WP_User $user */
	public $user = null;
	/** @var WP_Post $course */
	public $course = null;
	public $progress = null;
	public $email = '';

	private function __construct() {
		$this->event_merge_tag_groups = array( 'learndash_user', 'learndash_course' );
		$this->event_name             = __( 'User Completes a Course', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs when the user completes a course.', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'learndash_course' );
		$this->optgroup_label         = __( 'LearnDash', 'autonami-automations-pro' );
		$this->priority               = 30;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'learndash_course_completed', [ $this, 'process' ] );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $course : is an array with 4 keys
	 *                 1) user: user object
	 *                 2) course: course object
	 *                 3) progress: course progress array
	 *                 4) completed_time: The time at which the course is marked as complete
	 */
	public function process( $course ) {
		$data              = $this->get_default_data();
		$data['user_id']   = $course['user']->ID;
		$data['course_id'] = $course['course']->ID;
		$data['progress']  = $course['progress'];

		$user          = get_user_by( 'id', absint( $course['user']->ID ) );
		$data['email'] = $user instanceof WP_User && is_email( $user->user_email ) ? $user->user_email : '';

		$this->send_async_call( $data );
	}

	/**
	 * Set up rules data
	 *
	 * @param $automation_data
	 */
	public function pre_executable_actions( $automation_data ) {
		BWFAN_Core()->rules->setRulesData( $this->user->ID, 'user_id' );
		BWFAN_Core()->rules->setRulesData( $this->user, 'user' );
		BWFAN_Core()->rules->setRulesData( $this->course->ID, 'course_id' );
		BWFAN_Core()->rules->setRulesData( $this->course, 'course' );
		BWFAN_Core()->rules->setRulesData( $this->progress, 'progress' );
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
		$data_to_send                        = [];
		$data_to_send['global']['user_id']   = $this->user->ID;
		$data_to_send['global']['user']      = $this->user;
		$data_to_send['global']['course']    = $this->course;
		$data_to_send['global']['course_id'] = $this->course->ID;
		$data_to_send['global']['progress']  = $this->progress;
		$data_to_send['global']['email']     = $this->email;

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
		$user   = get_user_by( 'ID', $global_data['user_id'] );
		$course = get_post( $global_data['course_id'] );
		ob_start();
		?>
        <li>
            <strong><?php echo esc_html__( 'User:', 'autonami-automations-pro' ); ?> </strong>
            <a target="_blank" href="<?php echo admin_url() . '?user-edit.php?user_id=' . $user->ID; //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo esc_html__( $user->display_name ); ?></a>
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
		$get_data = BWFAN_Merge_Tag_Loader::get_data( 'course' );
		if ( ( empty( $get_data ) || intval( $get_data ) !== intval( $task_meta['global']['course'] ) ) ) {
			$set_data = array(
				'user_id'   => $task_meta['global']['user_id'],
				'user'      => $task_meta['global']['user'],
				'course'    => $task_meta['global']['course'],
				'course_id' => $task_meta['global']['course_id'],
				'progress'  => $task_meta['global']['progress'],
				'email'     => $task_meta['global']['email'],
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	public function get_email_event() {
		return $this->email;
	}

	public function get_user_id_event() {
		return $this->user->ID;
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$this->user     = get_user_by( 'ID', BWFAN_Common::$events_async_data['user_id'] );
		$this->course   = get_post( BWFAN_Common::$events_async_data['course_id'] );
		$this->progress = BWFAN_Common::$events_async_data['progress'];

		return $this->run_automations();
	}


}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_learndash_active() ) {
	return 'BWFAN_LD_User_Completes_Course';
}
