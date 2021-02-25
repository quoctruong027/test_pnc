<?php

final class BWFAN_LD_Reset_Quiz_Attempts extends BWFAN_Action {

	private static $ins = null;

	protected function __construct() {
		$this->action_name     = __( 'Reset Quiz Attempts', 'autonami-automations-pro' );
		$this->action_desc     = __( 'This action resets a user quiz attempt count', 'wp-marketing-automations' );
		$this->required_fields = array( 'user_id', 'quiz_id' );
		$this->action_priority = 35;
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	/**
	 * Localize data for html fields for the current action.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$data = $this->get_view_data();
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'quizzes', $data );
		}
	}

	public function get_view_data() {
		$quizzes = get_posts( array(
			'post_type'        => 'sfwd-quiz',
			'posts_per_page'   => - 1,
			'status'           => 'publish',
			'suppress_filters' => false
		) );

		$quiz_array = array();
		foreach ( $quizzes as $quiz ) {
			$quiz_array[ $quiz->ID ] = $quiz->post_title;
		}

		return $quiz_array;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            selected_quiz = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'quiz')) ? data.actionSavedData.data.quiz : '';
            #>
            <div class="bwfan-<?php echo esc_html__( $this->get_slug() ); ?>">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Quiz', 'autonami-automations-pro' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][quiz]">
                    <option value=""><?php echo esc_html__( 'Choose any Quiz', 'autonami-automations-pro' ); ?></option>
                    <#
                    if(_.has(data.actionFieldsOptions, 'quizzes') && _.isObject(data.actionFieldsOptions.quizzes) ) {
                    _.each( data.actionFieldsOptions.quizzes, function( value, key ){
                    selected = (key == selected_quiz) ? 'selected' : '';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}}</option>
                    <# })
                    }
                    #>
                </select>
                <div class="clearfix bwfan_field_desc bwfan-mb20">Select the quiz for which you want to reset the attempts of</div>
            </div>
        </script>
		<?php
	}

	/**
	 * Make all the data which is required by the current action.
	 * This data will be used while executing the task of this action.
	 *
	 * @param $integration_object
	 * @param $task_meta
	 *
	 * @return array|void
	 */
	public function make_data( $integration_object, $task_meta ) {
		$data_to_set            = array();
		$data_to_set['user_id'] = $task_meta['global']['user_id'];
		$data_to_set['quiz_id'] = $task_meta['data']['quiz'];
		
		if ( empty( $data_to_set['user_id'] ) ) {
			$email                  = ( isset( $task_meta['global']['email'] ) && is_email( $task_meta['global']['email'] ) ) ? $task_meta['global']['email'] : '';
			$user                   = is_email( $email ) ? get_user_by( 'email', $email ) : '';
			$data_to_set['user_id'] = $user instanceof WP_User ? $user->ID : 0;
		}

		return $data_to_set;
	}

	/**
	 * Execute the current action.
	 * Return 3 for successful execution , 4 for permanent failure.
	 *
	 * @param $action_data
	 *
	 * @return array
	 */
	public function execute_action( $action_data ) {
		$this->set_data( $action_data['processed_data'] );
		$result = $this->process();

		if ( true === $result ) {
			return array(
				'status'  => 3,
				'message' => 'Quiz Attempts Reset Done'
			);
		}

		return $result;
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		$quiz_id = absint( $this->data['quiz_id'] );
		if ( empty( $quiz_id ) ) {
			return array(
				'status'  => 4,
				'message' => __( 'Quiz wasn\'t selected', 'autonami-automations-pro' ),
			);
		}

		$user_id = $this->data['user_id'];
		$user    = get_userdata( $user_id );
		if ( false === $user ) {
			return array(
				'status'  => 4,
				'message' => __( 'User doesn\'t exists', 'autonami-automations-pro' ),
			);
		}

		learndash_remove_user_quiz_attempt( $user_id, array( 'quiz' => $quiz_id ) );

		return true;
	}
}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_LD_Reset_Quiz_Attempts';