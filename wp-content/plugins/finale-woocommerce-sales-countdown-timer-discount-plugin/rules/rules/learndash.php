<?php
defined( 'ABSPATH' ) || exit;

class WCCT_Rule_Learndash_Single_Course extends WCCT_Rule_Base {

	public function __construct() {

		parent::__construct( 'learndash_single_course' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => 'is',
			'notin' => 'is not',
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result      = array();
		$args        = array(
			'numberposts' => - 1,
			'post_type'   => 'sfwd-courses',
		);
		$postr_types = get_posts( $args );

		foreach ( $postr_types as $post ) {
			$result[ $post->ID ] = $post->post_title;
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $post_id ) {
		global $post;
		if ( ! $post instanceof WP_Post ) {
			return false;
		}
		$result = false;

		if ( $post->ID && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			$in     = in_array( $post->ID, $rule_data['condition'] );
			$result = 'in' === $rule_data['operator'] ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Learndash_Single_Lesson extends WCCT_Rule_Base {

	public function __construct() {

		parent::__construct( 'learndash_single_lesson' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result      = array();
		$args        = array(
			'numberposts' => - 1,
			'post_type'   => 'sfwd-lessons',
		);
		$postr_types = get_posts( $args );

		foreach ( $postr_types as $post ) {
			$result[ $post->ID ] = $post->post_title;
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $product_id ) {
		global $post;
		if ( ! $post instanceof WP_Post ) {
			return false;
		}
		$result = false;

		if ( $post->ID && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			$in     = in_array( $post->ID, $rule_data['condition'] );
			$result = 'in' === $rule_data['operator'] ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Learndash_Single_Topic extends WCCT_Rule_Base {

	public function __construct() {

		parent::__construct( 'learndash_single_topic' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result      = array();
		$args        = array(
			'numberposts' => - 1,
			'post_type'   => 'sfwd-topic',
		);
		$postr_types = get_posts( $args );

		foreach ( $postr_types as $post ) {
			$result[ $post->ID ] = $post->post_title;
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $post_id ) {
		global $post;
		if ( ! $post instanceof WP_Post ) {
			return false;
		}
		$result = false;

		if ( $post->ID && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			$in     = in_array( $post->ID, $rule_data['condition'] );
			$result = 'in' === $rule_data['operator'] ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}
