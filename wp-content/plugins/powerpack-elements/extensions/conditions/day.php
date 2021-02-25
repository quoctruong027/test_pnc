<?php
namespace PowerpackElements\Extensions\Conditions;

// Powerpack Elements Classes
use PowerpackElements\Base\Condition;

// Elementor Classes
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * \Extensions\Conditions\Day
 *
 * @since  1.4.13.1
 */
class Day extends Condition {

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
		return 'day';
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
		return __( 'Day of Week', 'powerpack' );
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
		return [
			'label'				=> __( 'Day(s)', 'powerpack' ),
			'type'				=> \Elementor\Controls_Manager::SELECT2,
			'options' => [
				'1' => __( 'Monday', 'powerpack' ),
				'2' => __( 'Tuesday', 'powerpack' ),
				'3' => __( 'Wednesday', 'powerpack' ),
				'4' => __( 'Thursday', 'powerpack' ),
				'5' => __( 'Friday', 'powerpack' ),
				'6' => __( 'Saturday', 'powerpack' ),
				'7' => __( 'Sunday', 'powerpack' ),
			],
			'multiple'			=> true,
			'label_block'		=> true,
			'default' 			=> '1',
		];
	}

	/**
	 * Check day of week
	 *
	 * Checks wether today falls inside a
	 * specified day of the week
	 *
	 * @since 1.4.13.1
	 *
	 * @access protected
	 *
	 * @param string  	$name  		The control name to check
	 * @param mixed  	$value  	The control value to check
	 * @param string  	$operator  	Comparison operator.
	 */
	public function check( $name, $operator, $value ) {

		$show = false;

		if ( is_array( $value ) && ! empty( $value ) ) {
			foreach ( $value as $_key => $_value ) {
				if ( $_value === date( 'w' ) ) {
					$show = true; break;
				}
			}
		} else { $show = $value === date( 'w' ); }

		return self::compare( $show, true, $operator );
	}
}
