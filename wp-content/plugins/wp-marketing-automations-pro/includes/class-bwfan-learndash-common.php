<?php

class BWFAN_Learndash_Common {

	public static function init() {
		add_filter( 'bwfan_select2_ajax_callable', array( __CLASS__, 'get_callable_object' ), 1, 2 );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_assets' ), 99 );
	}

	public static function admin_enqueue_assets() {
		if ( ! isset( $_GET['page'] ) || 'autonami' !== $_GET['page'] ) {
			return;
		}

		/** Prevent LearnDash styles to interfere with Autonami Styles */
		wp_dequeue_style( 'learndash-admin-settings-page' );
	}

	public static function get_callable_object( $is_empty, $data ) {
		if ( 'sfwd-courses' === $data['type'] ) {
			return [ __CLASS__, 'get_learndash_courses' ];
		}

		if ( 'sfwd-quizzes' === $data['type'] ) {
			return [ __CLASS__, 'get_learndash_quizzes' ];
		}

		return $is_empty;
	}

	/**
	 * Get quizzes by searched term
	 *
	 * @param $searched_term
	 */
	public static function get_learndash_quizzes( $searched_term = '' ) {
		$query_params = array(
			'post_type'      => learndash_get_post_type_slug( 'quiz' ),
			'posts_per_page' => - 1,
			'post_status'    => 'publish'
		);

		if ( '' !== $searched_term ) {
			$query_params['s'] = $searched_term;
		}

		$query = new WP_Query( $query_params );

		$results = array();
		if ( $query->found_posts > 0 ) {
			foreach ( $query->posts as $post ) {
				$results[] = array(
					'id'   => $post->ID,
					'text' => $post->post_title,
				);
			}
		}

		return array( 'results' => $results );
	}

	/**
	 * Get WpProQuiz_Model_Quiz models
	 *
	 * @return WpProQuiz_Model_Quiz[]
	 */
	public static function get_learndash_quizzes_models() {
		$quiz_mapper = new WpProQuiz_Model_QuizMapper();

		/** @var WpProQuiz_Model_Quiz[] $quizzes */
		$quizzes = $quiz_mapper->fetchAll();
		foreach ( $quizzes as $key => $quiz ) {
			if ( empty( $quiz->getPostId() ) ) {
				unset( $quizzes[ $key ] );
			}
		}

		return $quizzes;
	}

	public static function get_learndash_courses( $searched_term ) {
		$courses      = array();
		$results      = array();
		$query_params = array(
			'post_type'      => 'sfwd-courses',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);

		if ( '' !== $searched_term ) {
			$query_params['s'] = $searched_term;
		}

		$query = new WP_Query( $query_params );

		if ( $query->found_posts > 0 ) {
			foreach ( $query->posts as $post ) {
				$results[] = array(
					'id'   => $post->ID,
					'text' => $post->post_title,
				);
			}
		}

		$courses['results'] = $results;

		return $courses;
	}

	/**
	 * Get LearnDash User's Group's Leaders
	 *
	 * @param int $user_id
	 *
	 * @return array|null
	 */
	public static function get_group_leaders_by_user_id( $user_id ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT DISTINCT(user_id)  FROM {$wpdb->usermeta} WHERE `meta_key` LIKE '%learndash_group_leaders_%' AND meta_value IN (SELECT meta_value as 'group' FROM {$wpdb->usermeta} WHERE `meta_key` LIKE '%learndash_group_users_%' AND user_id = %d)", $user_id );

		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Get LearnDash Group's Leaders by Group ID
	 *
	 * @param int $user_id
	 *
	 * @return array|null
	 */
	public static function get_group_leaders_by_group_id( $group_id ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT DISTINCT(user_id)  FROM {$wpdb->usermeta} WHERE `meta_key` LIKE '%learndash_group_leaders_%' AND meta_value = %d)", $group_id );

		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Get LearnDash User's Groups
	 *
	 * @param int $user_id
	 *
	 * @return array|null
	 */
	public static function get_groups_by_user_id( $user_id ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT meta_value as 'group' FROM {$wpdb->usermeta} WHERE `meta_key` LIKE '%learndash_group_users_%' AND user_id = %d", $user_id );

		return $wpdb->get_results( $query, ARRAY_A );
	}

	public static function get_user_quiz_attempts( $user_id ) {
		$user_quizzes = get_user_meta( $user_id, '_sfwd-quizzes', true );

		return $user_quizzes;
	}

	/**
	 * Get LearnDash questions by quiz ID
	 *
	 * @param int $quiz_id
	 *
	 * @return array
	 */
	public static function get_learndash_quiz_questions( $quiz_id = 0 ) {
		if ( empty( $quiz_id ) ) {
			return array();
		}

		$questions = learndash_get_quiz_questions( absint( $quiz_id ) );
		$questions = array_keys( $questions );

		$questions_array = array();

		foreach ( $questions as $key ) {
			$questions_array[ $key ] = get_the_title( $key );
		}

		return $questions_array;
	}

	public static function get_learndash_quiz_questions_models( $quiz_id = 0 ) {
		if ( empty( $quiz_id ) ) {
			return array();
		}

		$question_mapper = new WpProQuiz_Model_QuestionMapper();
		$questions       = $question_mapper->fetchAll( $quiz_id );

		return $questions;
	}

	/**
	 * Get Answers from Quiz Results data (LearnDash)
	 *
	 * @param $results
	 * @param WpProQuiz_Model_Question[] $question_models
	 *
	 * @return array
	 */
	public static function get_learndash_quiz_answers_from_result_data( $results, $question_models ) {
		$questions_and_answers = array();

		foreach ( $results as $key => $result ) {

			$questions_and_answers[ $key ] = $result['e']['r'];

		}

		// Map the question IDs into post IDs
		foreach ( $question_models as $model ) {

			foreach ( $questions_and_answers as $key => $result ) {

				if ( $key === $model->getId() ) {

					// Convert multiple choice from true / false into the selected option
					if ( is_array( $result ) ) {

						foreach ( $result as $n => $multiple_choice_answer ) {

							if ( true === $multiple_choice_answer ) {

								$answers = $model->getAnswerData();

								foreach ( $answers as $x => $answer ) {

									if ( $x === $n ) {

										$result = $answer->getAnswer();
										break 2;

									}
								}
							}
						}
					}

					$answers[ $model->getId() ] = $result;
				}
			}
		}

		return $questions_and_answers;
	}

}
