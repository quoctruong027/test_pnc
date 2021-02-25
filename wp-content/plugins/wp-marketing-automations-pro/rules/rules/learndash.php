<?php

if ( bwfan_is_learndash_active() ) {
	class BWFAN_Rule_Learndash_Quiz_Percentage extends BWFAN_Rule_Base {
		public function __construct() {
			parent::__construct( 'learndash_quiz_percentage' );
		}

		public function get_possible_rule_operators() {
			$operators = array(
				'==' => __( 'is equal to', 'autonami-automations-pro' ),
				'!=' => __( 'is not equal to', 'autonami-automations-pro' ),
				'>'  => __( 'is greater than', 'autonami-automations-pro' ),
				'<'  => __( 'is less than', 'autonami-automations-pro' ),
				'>=' => __( 'is greater or equal to', 'autonami-automations-pro' ),
				'<=' => __( 'is less or equal to', 'autonami-automations-pro' ),
			);

			return $operators;
		}

		public function get_condition_input_type() {
			return 'Text';
		}

		/**
		 * Get percentage from quiz data
		 *
		 * @return int
		 */
		public function get_percentage() {
			$quiz_data = BWFAN_Core()->rules->getRulesData( 'quiz_data' );

			return $quiz_data['percentage'];
		}

		public function is_match( $rule_data ) {
			$percentage = (float) $this->get_percentage();
			$value      = (float) $rule_data['condition'];

			switch ( $rule_data['operator'] ) {
				case '==':
					$result = $percentage === $value;
					break;
				case '!=':
					$result = $percentage !== $value;
					break;
				case '>':
					$result = $percentage > $value;
					break;
				case '<':
					$result = $percentage < $value;
					break;
				case '>=':
					$result = $percentage >= $value;
					break;
				case '<=':
					$result = $percentage <= $value;
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'If user\'s percentage ', 'autonami-automations-pro' )
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= condition + '%' %>
			<?php
		}
	}

	class BWFAN_Rule_Learndash_Quiz_Points extends BWFAN_Rule_Base {
		public function __construct() {
			parent::__construct( 'learndash_quiz_points' );
		}

		public function get_possible_rule_operators() {
			$operators = array(
				'==' => __( 'are equal to', 'autonami-automations-pro' ),
				'!=' => __( 'are not equal to', 'autonami-automations-pro' ),
				'>'  => __( 'are greater than', 'autonami-automations-pro' ),
				'<'  => __( 'are less than', 'autonami-automations-pro' ),
				'>=' => __( 'are greater or equal to', 'autonami-automations-pro' ),
				'<=' => __( 'are less or equal to', 'autonami-automations-pro' ),
			);

			return $operators;
		}

		public function get_condition_input_type() {
			return 'Text';
		}

		/**
		 * Get points from quiz data
		 *
		 * @return float
		 */
		public function get_points() {
			$quiz_data = BWFAN_Core()->rules->getRulesData( 'quiz_data' );

			return $quiz_data['points'];
		}

		public function is_match( $rule_data ) {
			$points = (float) $this->get_points();
			$value  = (float) $rule_data['condition'];

			switch ( $rule_data['operator'] ) {
				case '==':
					$result = $points === $value;
					break;
				case '!=':
					$result = $points !== $value;
					break;
				case '>':
					$result = $points > $value;
					break;
				case '<':
					$result = $points < $value;
					break;
				case '>=':
					$result = $points >= $value;
					break;
				case '<=':
					$result = $points <= $value;
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'If user\'s points ', 'autonami-automations-pro' )
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= condition %>
			<?php
		}
	}

	class BWFAN_Rule_Learndash_Quiz_Score extends BWFAN_Rule_Base {
		public function __construct() {
			parent::__construct( 'learndash_quiz_score' );
		}

		public function get_possible_rule_operators() {
			$operators = array(
				'==' => __( 'is equal to', 'autonami-automations-pro' ),
				'!=' => __( 'is not equal to', 'autonami-automations-pro' ),
				'>'  => __( 'is greater than', 'autonami-automations-pro' ),
				'<'  => __( 'is less than', 'autonami-automations-pro' ),
				'>=' => __( 'is greater or equal to', 'autonami-automations-pro' ),
				'<=' => __( 'is less or equal to', 'autonami-automations-pro' ),
			);

			return $operators;
		}

		public function get_condition_input_type() {
			return 'Text';
		}

		/**
		 * Get score from quiz data
		 *
		 * @return float
		 */
		public function get_score() {
			$quiz_data = BWFAN_Core()->rules->getRulesData( 'quiz_data' );

			return $quiz_data['score'];
		}

		public function is_match( $rule_data ) {
			$score = $this->get_score();
			$value = $rule_data['condition'];

			switch ( $rule_data['operator'] ) {
				case '==':
					$result = $score === $value;
					break;
				case '!=':
					$result = $score !== $value;
					break;
				case '>':
					$result = $score > $value;
					break;
				case '<':
					$result = $score < $value;
					break;
				case '>=':
					$result = $score >= $value;
					break;
				case '<=':
					$result = $score <= $value;
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'If user\'s score ', 'autonami-automations-pro' )
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= condition %>
			<?php
		}
	}

	class BWFAN_Rule_Learndash_Quiz_Timespent extends BWFAN_Rule_Base {
		public function __construct() {
			parent::__construct( 'learndash_quiz_timespent' );
		}

		public function get_possible_rule_operators() {
			$operators = array(
				'==' => __( 'is equal to', 'autonami-automations-pro' ),
				'!=' => __( 'is not equal to', 'autonami-automations-pro' ),
				'>'  => __( 'is greater than', 'autonami-automations-pro' ),
				'<'  => __( 'is less than', 'autonami-automations-pro' ),
				'>=' => __( 'is greater or equal to', 'autonami-automations-pro' ),
				'<=' => __( 'is less or equal to', 'autonami-automations-pro' ),
			);

			return $operators;
		}

		public function get_condition_input_type() {
			return 'Text';
		}

		/**
		 * Get timespent from quiz data
		 *
		 * @return float
		 */
		public function get_timespent() {
			$quiz_data = BWFAN_Core()->rules->getRulesData( 'quiz_data' );

			return $quiz_data['timespent'];
		}

		public function is_match( $rule_data ) {
			$score = (float) $this->get_timespent();
			$value = (float) $rule_data['condition'];

			switch ( $rule_data['operator'] ) {
				case '==':
					$result = $score === $value;
					break;
				case '!=':
					$result = $score !== $value;
					break;
				case '>':
					$result = $score > $value;
					break;
				case '<':
					$result = $score < $value;
					break;
				case '>=':
					$result = $score >= $value;
					break;
				case '<=':
					$result = $score <= $value;
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function get_result_operator_values() {
			$operators = array(
				'==' => __( 'spent', 'autonami-automations-pro' ),
				'!=' => __( 'not spent', 'autonami-automations-pro' ),
				'>'  => __( 'spent more than', 'autonami-automations-pro' ),
				'<'  => __( 'spent less than', 'autonami-automations-pro' ),
				'>=' => __( 'spent greater or equal to', 'autonami-automations-pro' ),
				'<=' => __( 'spent less or equal to', 'autonami-automations-pro' ),
			);

			return $operators;
		}

		public function ui_view() {
			esc_html_e( 'If user ', 'autonami-automations-pro' )
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_result_operator_values() ); ?>'); %>

            <%= ops[operator] %>
            <%= condition + '<?php esc_html_e( ' seconds', 'autonami-automations-pro' ); ?>' %>
			<?php
		}
	}

	class BWFAN_Rule_Learndash_Course extends BWFAN_Rule_Base {
		public function __construct() {
			parent::__construct( 'learndash_course' );
		}

		public function get_possible_rule_operators() {
			$operators = array(
				'is'     => __( 'is', 'wp-marketing-automations' ),
				'is_not' => __( 'is not', 'wp-marketing-automations' ),
			);

			return $operators;
		}

		public function get_condition_input_type() {
			return 'select';
		}

		public function get_possible_rule_values() {
			$result = array();

			$args = array(
				'post_type'      => 'sfwd-courses',
				'fields'         => 'ids',
				'posts_per_page' => - 1,
				'post_status'    => 'publish'
			);

			$courses_query = new WP_Query( $args );
			$courses       = $courses_query->posts;

			foreach ( $courses as $course ) {
				$result[ $course ] = get_the_title( $course );
			}

			return $result;
		}

		/**
		 * Get Course ID from Event's Rule Data
		 *
		 * @return int
		 */
		public function get_event_courseId_from_rulesData() {
			$course_id = BWFAN_Core()->rules->getRulesData( 'course_id' );

			if ( empty( $course_id ) ) {
				$lesson_id = BWFAN_Core()->rules->getRulesData( 'lesson_id' );
				$course_id = ! empty( $lesson_id ) ? get_post_meta( $lesson_id, 'course_id', true ) : 0;
			}

			if ( empty( $course_id ) ) {
				$topic_id  = BWFAN_Core()->rules->getRulesData( 'topic_id' );
				$course_id = ! empty( $topic_id ) ? get_post_meta( $topic_id, 'course_id', true ) : 0;
			}

			if ( empty( $course_id ) ) {
				$quiz_id   = BWFAN_Core()->rules->getRulesData( 'quiz_id' );
				$course_id = ! empty( $quiz_id ) ? get_post_meta( $quiz_id, 'course_id', true ) : 0;
			}

			return $course_id;
		}

		public function is_match( $rule_data ) {
			$course_id = absint( $this->get_event_courseId_from_rulesData() );
			$value     = absint( $rule_data['condition'] );

			switch ( $rule_data['operator'] ) {
				case 'is':
					$result = $course_id === $value;
					break;
				case 'is_not':
					$result = $course_id !== $value;
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'If Course ', 'autonami-automations-pro' )
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= '"'+uiData[condition]+'"' %>
			<?php
		}
	}

	class BWFAN_Rule_Learndash_Lesson extends BWFAN_Rule_Base {
		public function __construct() {
			parent::__construct( 'learndash_lesson' );
		}

		public function get_possible_rule_operators() {
			$operators = array(
				'is'     => __( 'is', 'wp-marketing-automations' ),
				'is_not' => __( 'is not', 'wp-marketing-automations' ),
			);

			return $operators;
		}

		public function get_condition_input_type() {
			return 'select';
		}

		public function get_possible_rule_values() {
			$result = array();

			$args = array(
				'post_type'      => 'sfwd-lessons',
				'fields'         => 'ids',
				'posts_per_page' => - 1,
				'post_status'    => 'publish'
			);

			$lessons_query = new WP_Query( $args );
			$lessons       = $lessons_query->posts;

			foreach ( $lessons as $lesson ) {
				$course_id = '';
				if ( function_exists( 'learndash_get_course_id' ) ) {
					$course_id = learndash_get_course_id( $lesson );
				}

				$result[ $lesson ] = empty( $course_id ) ? get_the_title( $lesson ) : get_the_title( $lesson ) . ' (' . get_the_title( $course_id ) . ')';
			}

			return $result;
		}

		/**
		 * Get Course ID from Event's Rule Data
		 *
		 * @return int
		 */
		public function get_event_lessonId_from_rulesData() {
			$lesson_id = BWFAN_Core()->rules->getRulesData( 'lesson_id' );

			if ( empty( $lesson_id ) ) {
				$topic_id  = BWFAN_Core()->rules->getRulesData( 'topic_id' );
				$lesson_id = ! empty( $topic_id ) ? get_post_meta( $topic_id, 'course_id', true ) : 0;
			}

			if ( empty( $lesson_id ) ) {
				$quiz_id   = BWFAN_Core()->rules->getRulesData( 'quiz_id' );
				$lesson_id = ! empty( $quiz_id ) ? get_post_meta( $quiz_id, 'lesson_id', true ) : 0;
			}

			return $lesson_id;
		}

		public function is_match( $rule_data ) {
			$lesson_id = absint( $this->get_event_lessonId_from_rulesData() );
			$value     = absint( $rule_data['condition'] );

			switch ( $rule_data['operator'] ) {
				case 'is':
					$result = $lesson_id === $value;
					break;
				case 'is_not':
					$result = $lesson_id !== $value;
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'If Lesson ', 'autonami-automations-pro' )
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= '"'+uiData[condition]+'"' %>
			<?php
		}
	}

	class BWFAN_Rule_Learndash_Topic extends BWFAN_Rule_Base {
		public function __construct() {
			parent::__construct( 'learndash_topic' );
		}

		public function get_possible_rule_operators() {
			$operators = array(
				'is'     => __( 'is', 'wp-marketing-automations' ),
				'is_not' => __( 'is not', 'wp-marketing-automations' ),
			);

			return $operators;
		}

		public function get_condition_input_type() {
			return 'select';
		}

		public function get_possible_rule_values() {
			$result = array();

			$args = array(
				'post_type'      => 'sfwd-topic',
				'fields'         => 'ids',
				'posts_per_page' => - 1,
				'post_status'    => 'publish'
			);

			$topics_query = new WP_Query( $args );
			$topics       = $topics_query->posts;

			foreach ( $topics as $topic ) {
				$result[ $topic ] = get_the_title( $topic );
			}

			return $result;
		}

		/**
		 * Get Course ID from Event's Rule Data
		 *
		 * @return int
		 */
		public function get_event_topicId_from_rulesData() {
			$topic_id = BWFAN_Core()->rules->getRulesData( 'topic_id' );

			if ( empty( $topic_id ) ) {
				$quiz_id  = BWFAN_Core()->rules->getRulesData( 'quiz_id' );
				$topic_id = ! empty( $quiz_id ) ? get_post_meta( $quiz_id, 'topic_id', true ) : 0;
			}

			return $topic_id;
		}

		public function is_match( $rule_data ) {
			$topic_id = absint( $this->get_event_topicId_from_rulesData() );
			$value    = absint( $rule_data['condition'] );

			switch ( $rule_data['operator'] ) {
				case 'is':
					$result = $topic_id === $value;
					break;
				case 'is_not':
					$result = $topic_id !== $value;
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'If Topic ', 'autonami-automations-pro' )
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= '"'+uiData[condition]+'"' %>
			<?php
		}
	}

	class BWFAN_Rule_Learndash_Quiz extends BWFAN_Rule_Base {
		public function __construct() {
			parent::__construct( 'learndash_quiz' );
		}

		public function get_possible_rule_operators() {
			$operators = array(
				'is'     => __( 'is', 'wp-marketing-automations' ),
				'is_not' => __( 'is not', 'wp-marketing-automations' ),
			);

			return $operators;
		}

		public function get_condition_input_type() {
			return 'select';
		}

		public function get_possible_rule_values() {
			$result = array();

			$args = array(
				'post_type'      => 'sfwd-quiz',
				'fields'         => 'ids',
				'posts_per_page' => - 1,
				'post_status'    => 'publish'
			);

			$quiz_query = new WP_Query( $args );
			$quizzes    = $quiz_query->posts;

			foreach ( $quizzes as $quiz ) {
				$result[ $quiz ] = get_the_title( $quiz );
			}

			return $result;
		}

		public function is_match( $rule_data ) {
			$quiz_id = absint( BWFAN_Core()->rules->getRulesData( 'quiz_id' ) );
			$value   = absint( $rule_data['condition'] );

			switch ( $rule_data['operator'] ) {
				case 'is':
					$result = $quiz_id === $value;
					break;
				case 'is_not':
					$result = $quiz_id !== $value;
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'If Quiz ', 'autonami-automations-pro' )
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= '"'+uiData[condition]+'"' %>
			<?php
		}
	}

	class BWFAN_Rule_Learndash_Quiz_Result extends BWFAN_Rule_Base {

		public function __construct() {
			parent::__construct( 'learndash_quiz_result' );
		}

		public function get_possible_rule_operators() {
			return null;
		}

		public function get_possible_rule_values() {
			$operators = array(
				'yes' => __( 'True', 'autonami-automations-pro' ),
				'no'  => __( 'False', 'autonami-automations-pro' ),
			);

			return $operators;
		}

		public function is_match( $rule_data ) {


			$quiz_data = BWFAN_Core()->rules->getRulesData( 'quiz_data' );


			$result = isset( $quiz_data['pass'] ) && 1 === absint( $quiz_data['pass'] );

			return $rule_data['condition'] === 'yes' ? $result : ! $result;
		}

		public function ui_view() {
			esc_html_e( 'If ', 'autonami-automations-pro' );
			?>
            <% if (condition == "yes") { %> user passes <% } %>
            <% if (condition == "no") { %> user fails <% } %>

			<?php
			esc_html_e( 'the Quiz', 'autonami-automations-pro' );
		}


	}

	class BWFAN_Rule_Learndash_Group extends BWFAN_Rule_Base {
		public function __construct() {
			parent::__construct( 'learndash_group' );
		}

		public function get_possible_rule_operators() {
			$operators = array(
				'is'     => __( 'is', 'wp-marketing-automations' ),
				'is_not' => __( 'is not', 'wp-marketing-automations' ),
			);

			return $operators;
		}

		public function get_condition_input_type() {
			return 'select';
		}

		public function get_possible_rule_values() {
			$result = array();

			$args = array(
				'post_type'      => 'groups',
				'fields'         => 'ids',
				'posts_per_page' => - 1,
				'post_status'    => 'publish'
			);

			$groups_query = new WP_Query( $args );
			$groups       = $groups_query->posts;

			foreach ( $groups as $group ) {
				$result[ $group ] = get_the_title( $group );
			}

			return $result;
		}

		public function is_match( $rule_data ) {
			$group_id = absint( BWFAN_Core()->rules->getRulesData( 'group_id' ) );
			$value    = absint( $rule_data['condition'] );

			switch ( $rule_data['operator'] ) {
				case 'is':
					$result = $group_id === $value;
					break;
				case 'is_not':
					$result = $group_id !== $value;
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'If Group ', 'autonami-automations-pro' )
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= '"'+uiData[condition]+'"' %>
			<?php
		}
	}


}