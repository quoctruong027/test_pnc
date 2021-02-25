<?php
defined( 'ABSPATH' ) || exit;

class xlwcty_Rule_Day extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'day' );
	}

	public function get_possibile_rule_operators() {

		$operators = array(
			'==' => __( 'is', 'thank-you-page-for-woocommerce-nextmove' ),
			'!=' => __( 'is not', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$options = array(
			'0' => __( 'Sunday', 'thank-you-page-for-woocommerce-nextmove' ),
			'1' => __( 'Monday', 'thank-you-page-for-woocommerce-nextmove' ),
			'2' => __( 'Tuesday', 'thank-you-page-for-woocommerce-nextmove' ),
			'3' => __( 'Wednesday', 'thank-you-page-for-woocommerce-nextmove' ),
			'4' => __( 'Thursday', 'thank-you-page-for-woocommerce-nextmove' ),
			'5' => __( 'Friday', 'thank-you-page-for-woocommerce-nextmove' ),
			'6' => __( 'Saturday', 'thank-you-page-for-woocommerce-nextmove' ),

		);

		return $options;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $productID ) {
		global $post;
		$result    = false;
		$timestamp = current_time( 'timestamp' );

		$dateTime = new DateTime();
		$dateTime->setTimestamp( $timestamp );

		$day_today = $dateTime->format( 'w' );

		if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			if ( $rule_data['operator'] == '==' ) {
				$result = in_array( $day_today, $rule_data['condition'] ) ? true : false;
			}

			if ( $rule_data['operator'] == '!=' ) {
				$result = in_array( $day_today, $rule_data['condition'] ) ? false : true;
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class xlwcty_Rule_Date extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'date' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'!=' => __( 'is not equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'>'  => __( 'is greater than', 'thank-you-page-for-woocommerce-nextmove' ),
			'<'  => __( 'is less than', 'thank-you-page-for-woocommerce-nextmove' ),
			'>=' => __( 'is greater or equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'=<' => __( 'is less or equal to', 'thank-you-page-for-woocommerce-nextmove' ),
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
			$dateTime->setTimestamp( current_time( 'timestamp' ) );

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


class xlwcty_Rule_Time extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'time' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'!=' => __( 'is not equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'>'  => __( 'is greater than', 'thank-you-page-for-woocommerce-nextmove' ),
			'<'  => __( 'is less than', 'thank-you-page-for-woocommerce-nextmove' ),
			'>=' => __( 'is greater or equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'=<' => __( 'is less or equal to', 'thank-you-page-for-woocommerce-nextmove' ),
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
			if ( is_array( $parsetime ) && count( $parsetime ) !== 2 ) {
				return $this->return_is_match( $result, $rule_data );
			}

			$dateTime = new DateTime();
			$dateTime->setTimestamp( current_time( 'timestamp' ) );
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
