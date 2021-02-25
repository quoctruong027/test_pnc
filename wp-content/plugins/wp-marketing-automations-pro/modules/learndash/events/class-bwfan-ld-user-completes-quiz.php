<?php

final class BWFAN_LD_User_Completes_Quiz extends BWFAN_Event {
	private static $instance = null;
	/** @var WP_User $user */
	public $user = null;
	public $quiz_data = [];
	/** @var WP_Post $quiz */
	public $quiz = null;
	public $quiz_answers = array();
	public $email = '';

	private function __construct() {
		$this->event_merge_tag_groups = array( 'learndash_user', 'learndash_quiz' );
		$this->event_name             = __( 'User Completes a Quiz', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs when the user completes a quiz.', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'learndash_quiz', 'learndash_quiz_result', 'learndash_lesson', 'learndash_course', 'learndash_topic' );
		$this->optgroup_label         = __( 'LearnDash', 'autonami-automations-pro' );
		$this->priority               = 70;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
		add_action( 'learndash_quiz_completed', [ $this, 'process' ], 10, 2 );
		add_filter( 'bwfan_all_event_js_data', array( $this, 'add_form_data' ), 10, 2 );
		add_action( 'wp_ajax_bwfan_get_quiz_questions', array( $this, 'bwfan_get_quiz_questions' ) );
	}

	/**
	 * Localize data for html fields for the current event.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$data = $this->get_view_data();

			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'quizzes', $data );
		}
	}

	public function get_view_data() {
		$quizzes = BWFAN_Learndash_Common::get_learndash_quizzes_models();
		$options = [];

		foreach ( $quizzes as $quiz ) {
			if ( $quiz instanceof WpProQuiz_Model_Quiz ) {
				$options[ $quiz->getId() ] = get_the_title( $quiz->getPostId() );
			}
		}

		return $options;
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $quizdata : array( 'quiz' => $quiz, 'course' => $course, 'questions' => $questions, 'score' => $score, 'count' => $count, 'pass' => $pass, 'rank' => '-', 'time' => time(), 'pro_quizid' => $quiz_id, 'points' => $points, 'total_points' => $total_points, 'percentage' => $result, 'timespent' => $timespent);
	 * @param $current_user
	 */
	public function process( $quizdata, $current_user ) {
		$data = $this->get_default_data();

		$data['quiz_data'] = $quizdata;
		$data['quiz_id']   = ( isset( $quizdata['quiz'] ) && $quizdata['quiz'] instanceof WP_Post ) ? $quizdata['quiz']->ID : 0;
		$data['user_id']   = $current_user->ID;

		$user          = get_user_by( 'id', absint( $current_user->ID ) );
		$data['email'] = $user instanceof WP_User && is_email( $user->user_email ) ? $user->user_email : '';

		$this->send_async_call( $data );
	}

	public function add_form_data( $event_js_data, $automation_meta ) {
		if ( ! isset( $automation_meta['event_meta'] ) || ! isset( $event_js_data['ld_user_completes_quiz'] ) || ! isset( $automation_meta['event_meta']['quiz_id'] ) ) {
			return $event_js_data;
		}

		if ( isset( $automation_meta['event'] ) && ! empty( $automation_meta['event'] ) && 'ld_user_completes_quiz' !== $automation_meta['event'] ) {
			return $event_js_data;
		}

		$event_js_data['ld_user_completes_quiz']['selected_quiz'] = $automation_meta['event_meta']['quiz_id'];

		//$questions                                                     = BWFAN_Learndash_Common::get_learndash_quiz_questions( $automation_meta['event_meta']['quiz_id'] );
		$questions                                                     = $this->get_quiz_questions( $automation_meta['event_meta']['quiz_id'] );
		$event_js_data['ld_user_completes_quiz']['selected_questions'] = $questions;

		return $event_js_data;
	}

	public function get_quiz_questions( $quiz_id ) {
		$questions = BWFAN_Learndash_Common::get_learndash_quiz_questions_models( $quiz_id );
		$options   = array();
		if ( is_array( $questions ) ) {
			/** @var WpProQuiz_Model_Question $question */
			foreach ( $questions as $question ) {
				$options[ $question->getId() ] = esc_html( $question->getQuestion() );
			}
		}

		return $options;
	}

	public function bwfan_get_quiz_questions() {
		if ( ! isset( $_POST['bwfan_ld_quiz_nonce'] ) || false === wp_verify_nonce( sanitize_text_field( $_POST['bwfan_ld_quiz_nonce'] ), 'bwfan_ld_quiz_nonce' ) ) {
			wp_send_json( array(
				'error' => __( 'Nonce authentication failed!' ),
			) );
		}

		$quiz_id = isset( $_POST['id'] ) ? absint( sanitize_text_field( $_POST['id'] ) ) : 0;

		if ( empty( $quiz_id ) ) {
			wp_send_json( array(
				'questions' => array(),
			) );
		}

		$questions = $this->get_quiz_questions( $quiz_id );

		wp_send_json( array(
			'questions' => $questions,
		) );
	}

	/**
	 * Set up rules data
	 *
	 * @param $automation_data
	 */
	public function pre_executable_actions( $automation_data ) {
		BWFAN_Core()->rules->setRulesData( $this->user->ID, 'user_id' );
		BWFAN_Core()->rules->setRulesData( $this->user, 'user' );
		BWFAN_Core()->rules->setRulesData( $this->quiz_data, 'quiz_data' );
		BWFAN_Core()->rules->setRulesData( $this->quiz->ID, 'quiz_id' );
		BWFAN_Core()->rules->setRulesData( $this->quiz_answers, 'quiz_answers' );
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
		$data_to_send                           = [];
		$data_to_send['global']['user_id']      = $this->user->ID;
		$data_to_send['global']['user']         = $this->user;
		$data_to_send['global']['quiz_data']    = $this->quiz_data;
		$data_to_send['global']['email']        = $this->email;
		$data_to_send['global']['quiz_id']      = $this->quiz->ID;
		$data_to_send['global']['quiz_answers'] = $this->quiz_answers;

		return $data_to_send;
	}

	/**
	 * Show the html fields for the current event.
	 */
	public function get_view( $db_eventmeta_saved_value ) {

		?>
        <script type="text/html" id="tmpl-event-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            selected_quiz_id = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'quiz_id')) ? data.eventSavedData.quiz_id : '';
            #>
            <div class="bwfan_mt15"></div>
            <div class="bwfan-col-sm-12 bwfan-p-0">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select Quiz', 'autonami-automations-pro' ); ?></label>
                <select id="bwfan-ld_quiz_id" class="bwfan-input-wrapper" name="event_meta[quiz_id]">
                    <option value="bwfan-ld-any-quiz"><?php esc_html_e( 'Any Quiz', 'autonami-automations-pro' ); ?></option>
                    <#
                    if(_.has(data.eventFieldsOptions, 'quizzes') && _.isObject(data.eventFieldsOptions.quizzes) ) {
                    _.each( data.eventFieldsOptions.quizzes, function( value, key ){
                    selected =(key == selected_quiz_id)?'selected':'';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}}</option>
                    <# })
                    } #>
                </select>
            </div>
        </script>

        <script>
            jQuery(document).on('change', '#bwfan-ld_quiz_id', function () {
                var selected_quiz = jQuery(this).val();

                bwfan_events_js_data['ld_user_completes_quiz']['selected_quiz'] = selected_quiz;
                jQuery.ajax({
                    method: 'post',
                    url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                    datatype: "JSON",
                    data: {
                        action: 'bwfan_get_quiz_questions',
                        id: selected_quiz,
                        bwfan_ld_quiz_nonce: '<?php echo wp_create_nonce( 'bwfan_ld_quiz_nonce' ); ?>'
                    },
                    success: function (response) {
                        bwfan_events_js_data['ld_user_completes_quiz']['selected_questions'] = response.questions;
                    }
                });
            });

            jQuery('body').on('bwfan-selected-merge-tag', function (e, v) {
                if ('ld_quiz_selected_answer' !== v.tag) {
                    return;
                }

                var options = '';
                var i = 1;
                var selected = '';

                _.each(bwfan_events_js_data['ld_user_completes_quiz']['selected_questions'], function (value, key) {
                    selected = (i == 1) ? 'selected' : '';
                    options += '<option value="' + key + '" ' + selected + '>' + value + '</option>';
                    i++;
                });

                jQuery('.bwfan_ld_quiz_questions').html(options);
                jQuery('.bwfan_tag_select').trigger('change');
            });
        </script>
		<?php
	}

	/**
	 * Make the view data for the current event which will be shown in task listing screen.
	 *
	 * @param $global_data
	 *
	 * @return false|string
	 */
	/**
	 * Make the view data for the current event which will be shown in task listing screen.
	 *
	 * @param $global_data
	 *
	 * @return false|string
	 */
	public function get_task_view( $global_data ) {
		$user = get_user_by( 'ID', absint( $global_data['user_id'] ) );
		ob_start();
		?>
        <li>
            <strong><?php echo esc_html__( 'User:', 'autonami-automations-pro' ); ?> </strong>
            <a target="_blank" href="<?php echo admin_url() . '?user-edit.php?user_id=' . $user->ID; //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo esc_html__( $user->first_name . ' ' . $user->last_name ); ?></a>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Result:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo ( ! empty( $global_data['quiz_data']['pass'] ) ) ? 'Pass' : 'Fail'; ?>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Score:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html( $global_data['quiz_data']['score'] ); ?>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Percentage:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html( $global_data['quiz_data']['percentage'] ); ?>
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
		$get_data = BWFAN_Merge_Tag_Loader::get_data( 'quiz_data' );
		if ( ( empty( $get_data ) || intval( $get_data ) !== intval( $task_meta['global']['quiz_data'] ) ) ) {
			$set_data = array(
				'user_id'      => $task_meta['global']['user_id'],
				'email'        => $task_meta['global']['email'],
				'user'         => $task_meta['global']['user'],
				'quiz_data'    => $task_meta['global']['quiz_data'],
				'quiz_answers' => $task_meta['global']['quiz_answers'],
				'quiz_id'      => $task_meta['global']['quiz_id']
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
		$this->user         = get_user_by( 'ID', BWFAN_Common::$events_async_data['user_id'] );
		$this->quiz_data    = BWFAN_Common::$events_async_data['quiz_data'];
		$this->quiz         = get_post( BWFAN_Common::$events_async_data['quiz_id'] );
		$this->quiz_answers = $this->get_answer_data();

		return $this->run_automations();
	}

	public function get_answer_data() {
		$answers = array();

		if ( isset( $this->quiz_data['statistic_ref_id'] ) && $this->quiz_data['pro_quizid'] ) {
			/** @var WpProQuiz_Model_StatisticUser[] $stats */
			$stats = ( new WpProQuiz_Model_StatisticUserMapper() )->fetchUserStatistic( $this->quiz_data['statistic_ref_id'], $this->quiz_data['pro_quizid'] );

			foreach ( $stats as $stat ) {
				$user_answer      = $stat->getStatisticAnswerData();
				$user_question_id = $stat->getQuestionId();

				/** @var WpProQuiz_Model_AnswerTypes[] $questions_answer_data */
				$questions_answer_data = $stat->getQuestionAnswerData();
				foreach ( $user_answer as $index => $option ) {
					if ( 1 === $option ) {
						$answers[ $user_question_id ] = $questions_answer_data[ $index ]->getAnswer();
						break;
					}
				}
			}
		}

		return $answers;

	}

	/**
	 * Validating form id after submission with the selected form id in the event
	 *
	 * @param $automations_arr
	 *
	 * @return mixed
	 */
	public function validate_event_data_before_creating_task( $automations_arr ) {

		$automations_arr_temp = $automations_arr;
		foreach ( $automations_arr as $automation_id => $automation_data ) {
			if ( isset( $automation_data['event_meta']['quiz_id'] ) && isset( $this->quiz_data['pro_quizid'] ) && $automation_data['event_meta']['quiz_id'] !== 'bwfan-ld-any-quiz' && absint( $this->quiz_data['pro_quizid'] ) !== absint( $automation_data['event_meta']['quiz_id'] ) ) {
				unset( $automations_arr_temp[ $automation_id ] );
			}
		}

		return $automations_arr_temp;
	}


}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_learndash_active() ) {
	return 'BWFAN_LD_User_Completes_Quiz';
}
