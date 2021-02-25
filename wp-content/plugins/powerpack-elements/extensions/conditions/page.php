<?php
namespace PowerpackElements\Extensions\Conditions;

// Powerpack Elements Classes
use PowerpackElements\Base\Condition;
use PowerpackElements\Classes\PP_Posts_Helper;

// Elementor Classes
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * \Extensions\Conditions\Page
 *
 * @since  1.4.13.1
 */
class Page extends Condition {

	/**
	 * Get Group
	 * 
	 * Get the group of the condition
	 *
	 * @since  1.4.13.1
	 * @return string
	 */
	public function get_group() {
		return 'single';
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
		return 'page';
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
		return __( 'Page', 'powerpack' );
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
		$pages_all = PP_Posts_Helper::get_all_posts_by_type( 'page' );

		/*return [
			'type'				=> Controls_Manager::SELECT2,
			'default'			=> '',
			'placeholder'		=> __( 'Any', 'powerpack' ),
			'description'		=> __( 'Leave blank for any page.', 'powerpack' ),
			'label_block' 		=> true,
			'multiple'			=> true,
			'options'			=> $pages_all,
		];*/

		return [
			'type'				=> 'pp-query',
			'default'			=> '',
			'multiple'			=> true,
			'label_block'		=> true,
			'placeholder'		=> __( 'Any', 'powerpack' ),
			'description'		=> __( 'Leave blank for any page.', 'powerpack' ),
			'query_type'		=> 'posts',
			'object_type'		=> 'page',
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
		$show = false;

		if ( is_array( $value ) && ! empty( $value ) ) {
			foreach ( $value as $_key => $_value ) {
				if ( is_page( $_value ) ) {
					$show = true; break;
				}
			}
		} else { $show = is_page( $value ); }

		return $this->compare( $show, true, $operator );
	}
}
