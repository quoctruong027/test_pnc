<?php

class WCCT_Rule_Day extends WCCT_Rule_Base {


	public function __construct() {
		parent::__construct( 'day' );
	}

	public function get_possible_rule_operators() {

		$operators = array(
			'==' => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'!=' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$options = array(
			'0' => __( 'Sunday', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'1' => __( 'Monday', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'2' => __( 'Tuesday', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'3' => __( 'Wednesday', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'4' => __( 'Thursday', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'5' => __( 'Friday', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'6' => __( 'Saturday', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),

		);

		return $options;
	}

	public function get_condition_input_type() {
		return 'Select';
	}

	public function is_match( $rule_data, $productID ) {
		global $post;
		$result    = false;
		$timestamp = current_time( 'timestamp' );

		$dateTime = new DateTime();
		$dateTime->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

		$day_today = $dateTime->format( 'w' );

		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			if ( $rule_data['operator'] == '==' ) {
				$result = $rule_data['condition'] == $day_today ? true : false;
			}

			if ( $rule_data['operator'] == '!=' ) {
				$result = $rule_data['condition'] == $day_today ? false : true;
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Date extends WCCT_Rule_Base {


	public function __construct() {
		parent::__construct( 'date' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'!=' => __( 'is not equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'>'  => __( 'is greater than', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'<'  => __( 'is less than', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'>=' => __( 'is greater or equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'=<' => __( 'is less or equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Date';
	}

	public function is_match( $rule_data, $productID ) {
		global $post;

		$result = false;

		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			$dateTime = new DateTime();
			$dateTime->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

			switch ( $rule_data['operator'] ) {
				case '==':
					$result = ( $rule_data['condition'] ) == $dateTime->format( 'Y-m-d' );

					break;
				case '!=':
					$result = ( $rule_data['condition'] ) != $dateTime->format( 'Y-m-d' );

					break;

				case '>':
					$result = $dateTime->getTimestamp() > strtotime( $rule_data['condition'] );

					break;

				case '<':
					$result = $dateTime->getTimestamp() < strtotime( $rule_data['condition'] );

					break;

				case '=<':
					$result = $dateTime->getTimestamp() <= strtotime( $rule_data['condition'] );
					break;
				case '>=':
					$result = $dateTime->getTimestamp() >= strtotime( $rule_data['condition'] );

					break;

				default:
					$result = false;
					break;
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}


class WCCT_Rule_Time extends WCCT_Rule_Base {


	public function __construct() {
		parent::__construct( 'time' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'!=' => __( 'is not equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'>'  => __( 'is greater than', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'<'  => __( 'is less than', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'>=' => __( 'is greater or equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'=<' => __( 'is less or equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Time';
	}

	public function is_match( $rule_data, $productID ) {
		global $post;

		$result = false;

		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) && $rule_data['condition'] ) {

			$parsetime = explode( ' : ', $rule_data['condition'] );

			$dateTime = new DateTime();
			$dateTime->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

			$timestamp_current = $dateTime->getTimestamp();

			$dateTime->setTime( $parsetime[0], $parsetime[1] );
			$timestamp = $dateTime->getTimestamp();

			switch ( $rule_data['operator'] ) {
				case '==':
					$result = $timestamp_current == $timestamp;

					break;
				case '!=':
					$result = $timestamp_current != $timestamp;

					break;

				case '>':
					$result = $timestamp_current > $timestamp;

					break;

				case '<':
					$result = $timestamp_current < $timestamp;

					break;

				case '=<':
					$result = $timestamp_current <= $timestamp;

					break;
				case '>=':
					$result = $timestamp_current >= $timestamp;

					break;

				default:
					$result = false;
					break;
			}
		}

		return $this->return_is_match( $result, $rule_data );

	}

}
