<?php
namespace PowerpackElements\Extensions\Conditions;

// Powerpack Elements Classes
use PowerpackElements\Base\Condition;

// Elementor Classes
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * \Extensions\Conditions\Date
 *
 * @since  1.4.13.1
 */
class Date extends Condition {

	/**
	 * Get Group
	 * 
	 * Get the group of the condition
	 *
	 * @since  1.4.13.1
	 * @return string
	 */
	public function get_group() {
		return 'date_time';
	}

	/**
	 * Get Name
	 * 
	 * Get the name of the module
	 *
	 * @since  1.4.13.1
	 * @return string
	 */
	public function get_name() {
		return 'date';
	}

	/**
	 * Get Title
	 * 
	 * Get the title of the module
	 *
	 * @since  1.4.13.1
	 * @return string
	 */
	public function get_title() {
		return __( 'Current Date', 'powerpack' );
	}

	/**
	 * Get Value Control
	 * 
	 * Get the settings for the value control
	 *
	 * @since  1.4.13.1
	 * @return string
	 */
	public function get_value_control() {
		$default_date_start = date( 'Y-m-d', strtotime( '-3 day' ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
		$default_date_end 	= date( 'Y-m-d', strtotime( '+3 day' ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
		$default_interval 	= $default_date_start . ' to ' . $default_date_end;

		return [
			'label'				=> __( 'In interval', 'powerpack' ),
			'type'				=> \Elementor\Controls_Manager::DATE_TIME,
			'picker_options'	=> [
				'enableTime'	=> false,
				'mode' 			=> 'range',
			],
			'label_block'		=> true,
			'default'			=> $default_interval,
		];
	}

	/**
	 * Check condition
	 *
	 * @since 1.4.13.1
	 *
	 * @access public
	 *
	 * @param string  	$name  		The control name to check
	 * @param string 	$operator  	Comparison operator
	 * @param mixed  	$value  	The control value to check
	 */
	public function check( $name = null, $operator, $value ) {
		// Split control valur into two dates
		$intervals = explode( 'to' , preg_replace('/\s+/', '', $value ) );

		// Make sure the explode return an array with exactly 2 indexes
		if ( ! is_array( $intervals ) || 2 !== count( $intervals ) ) 
			return;

		// Set start and end dates
		$start 	= $intervals[0];
		$end 	= $intervals[1];
		$today 	= date('Y-m-d');

		// Default returned bool to false
		$show 	= false;

		// Check vars
		if ( \DateTime::createFromFormat( 'Y-m-d', $start ) === false || // Make sure it's a date
			 \DateTime::createFromFormat( 'Y-m-d', $end ) === false ) // Make sure it's a date
			return;

		// Convert to timestamp
		$start_ts 	= strtotime( $start ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$end_ts 	= strtotime( $end ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$today_ts 	= strtotime( $today ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

		// Check that user date is between start & end
		$show = ( ($today_ts >= $start_ts ) && ( $today_ts <= $end_ts ) );

		return $this->compare( $show, true, $operator );
	}
}
