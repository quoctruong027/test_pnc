<?php

namespace PowerpackElements\Extensions;

// Powerpack Elements classes
use PowerpackElements\Base\Extension_Base;
use PowerpackElements\Classes\PP_Posts_Helper;

// Elementor classes
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Scheme_Typography;
use Elementor\Scheme_Color;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Conditions Extension
 *
 * Adds display conditions to elements
 *
 * @since 1.4.7
 */
class Extension_Display_Conditions extends Extension_Base {

	/**
	 * Is Common Extension
	 *
	 * Defines if the current extension is common for all element types or not
	 *
	 * @since 1.4.7
	 * @access protected
	 *
	 * @var bool
	 */
	protected $is_common = true;

	/**
	 * Display Conditions
	 *
	 * Holds all the conditions for display on the frontend
	 *
	 * @since 1.4.7
	 * @access protected
	 *
	 * @var bool
	 */
	protected $conditions = [];

	/**
	 * Display Conditions
	 *
	 * Holds all the conditions classes
	 *
	 * @since 2.0.0
	 * @access protected
	 *
	 * @var bool
	 */
	protected $_conditions = [];

	/**
	 * A list of scripts that the widgets is depended in
	 *
	 * @since 1.4.7
	 **/
	public function get_script_depends() {
		return [];
	}

	/**
	 * The description of the current extension
	 *
	 * @since 2.-.0
	 **/
	public static function get_description() {
		return __( 'Adds display conditions to widgets and sections allowing you to show them depending on authentication, roles, date and time of day.', 'powerpack' );
	}

	/**
	 * Is disabled by default
	 *
	 * Return wether or not the extension should be disabled by default,
	 * prior to user actually saving a value in the admin page
	 *
	 * @access public
	 * @since 1.4.7
	 * @return bool
	 */
	public static function is_default_disabled() {
		return true;
	}

	/**
	 * Add common sections
	 *
	 * @since 1.4.7
	 *
	 * @access protected
	 */
	protected function add_common_sections_actions() {

		// Activate sections for widgets
		add_action( 'elementor/element/common/section_custom_css/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );

		// Activate sections for sections
		add_action( 'elementor/element/section/section_custom_css/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );

		// Activate sections for widgets if elementor pro
		add_action( 'elementor/element/common/section_custom_css_pro/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );

		

		// Activate sections for sections if elementor pro
		add_action( 'elementor/element/section/section_custom_css_pro/after_section_end', function( $element, $args ) {
			$this->add_common_sections( $element, $args );
		}, 10, 2 );

	}

	/**
	 * Conditions Repeater
	 *
	 * The repeater control
	 *
	 * @since 1.4.13
	 * @access protected
	 *
	 * @var bool
	 */
	protected $_conditions_repeater;

	const USER_GROUP        = 'user';
	const SINGLE_GROUP      = 'single';
	const ARCHIVE_GROUP     = 'archive';
	const DATE_TIME_GROUP   = 'date_time';
	const ACF_GROUP         = 'acf';
	const EDD_GROUP         = 'edd';
	const MISC_GROUP        = 'misc';

	public function get_groups() {
		return [
			self::USER_GROUP => [
				'label' => __( 'User', 'powerpack' ),
			],
			self::SINGLE_GROUP => [
				'label' => __( 'Singular', 'powerpack' ),
			],
			self::ARCHIVE_GROUP => [
				'label' => __( 'Archive', 'powerpack' ),
			],
			self::DATE_TIME_GROUP => [
				'label' => __( 'Date & Time', 'powerpack' ),
			],
			self::ACF_GROUP => [
				'label' => __( 'Advanced Custom Fields', 'powerpack' ),
			],
			self::MISC_GROUP => [
				'label' => __( 'Misc', 'powerpack' ),
			],
		];
	}

	/**
	 * @since 0.1.0
	 */
	public function register_conditions() {

		$available_conditions = [
			// User
			'authentication',
			'user',
			'role',

			// Singular
			'page',
			'post',
			'static_page',
			'post_type',

			// Archive
			'taxonomy_archive',
			'term_archive',
			'post_type_archive',
			'date_archive',
			'author_archive',
			'search_results',

			// Date & Time
			'date',
			'date_time_before',
			'time',
			'day',

			// ACF
			'acf_text',
			'acf_choice',
			'acf_true_false',
			'acf_post',
			'acf_taxonomy',
			'acf_date_time',

			// Misc
			'os',
			'browser',
			'search_bot',
		];

		foreach ( $available_conditions as $condition_name ) {

			$class_name = str_replace( '-', ' ', $condition_name );
			$class_name = str_replace( ' ', '', ucwords( $class_name ) );
			$class_name = __NAMESPACE__ . '\\Conditions\\' . $class_name;

			if ( class_exists( $class_name ) ) {
				if ( $class_name::is_supported() ) {
					$this->_conditions[ $condition_name ] = $class_name::instance();
				}
			}
		}
	}

	/**
	 * Add Controls
	 *
	 * @since 1.4.7
	 *
	 * @access private
	 */
	private function add_controls( $element, $args ) {

		$element_type = $element->get_type();

		$element->add_control(
			'pp_display_conditions_enable',
			[
				'label'                     => __( 'Display Conditions', 'powerpack' ),
				'type'                      => Controls_Manager::SWITCHER,
				'default'                   => '',
				'label_on'                  => __( 'Yes', 'powerpack' ),
				'label_off'                 => __( 'No', 'powerpack' ),
				'return_value'              => 'yes',
				'frontend_available'        => true,
			]
		);

		if ( 'widget' === $element_type ) {
			$element->add_control(
				'pp_display_conditions_output',
				[
					'label'                 => __( 'Output HTML', 'powerpack' ),
					'description'           => sprintf( __( 'If enabled, the HTML code will exist on the page but the %s will be hidden using CSS.', 'powerpack' ), $element_type ),
					'default'               => '',
					'type'                  => Controls_Manager::SWITCHER,
					'label_on'              => __( 'Yes', 'powerpack' ),
					'label_off'             => __( 'No', 'powerpack' ),
					'return_value'          => 'yes',
					'frontend_available'    => true,
					'condition'             => [
						'pp_display_conditions_enable' => 'yes',
					],
				]
			);
		}

		$element->add_control(
			'pp_display_conditions_relation',
			[
				'label'                     => __( 'Display on', 'powerpack' ),
				'type'                      => Controls_Manager::SELECT,
				'default'                   => 'all',
				'options'                   => [
					'all'       => __( 'All conditions met', 'powerpack' ),
					'any'       => __( 'Any condition met', 'powerpack' ),
				],
				'condition'                 => [
					'pp_display_conditions_enable' => 'yes',
				],
			]
		);

		$this->_conditions_repeater = new Repeater();

		$this->_conditions_repeater->add_control(
			'pp_condition_key',
			[
				'type'          => Controls_Manager::SELECT,
				'default'       => 'authentication',
				'label_block'   => true,
				'groups'        => $this->get_conditions_options(),
			]
		);

		$this->add_name_controls();

		$this->_conditions_repeater->add_control(
			'pp_condition_operator',
			[
				'type'              => Controls_Manager::SELECT,
				'default'           => 'is',
				'label_block'       => true,
				'options'           => [
					'is'        => __( 'Is', 'powerpack' ),
					'not'       => __( 'Is not', 'powerpack' ),
				],
			]
		);

		$this->add_value_controls();

		$element->add_control(
			'pp_display_conditions',
			[
				'label'     => __( 'Conditions', 'powerpack' ),
				'type'      => Controls_Manager::REPEATER,
				'default'   => [
					[
						'pp_condition_key'                  => 'authentication',
						'pp_condition_operator'             => 'is',
						'pp_condition_authentication_value' => 'authenticated',
					],
				],
				'condition'     => [
					'pp_display_conditions_enable' => 'yes',
				],
				'fields'        => $this->_conditions_repeater->get_controls(),
				'title_field'   => 'Condition',
			]
		);

	}

	/**
	 * Add Actions
	 *
	 * @since 1.4.7
	 *
	 * @access protected
	 */
	protected function add_actions() {

		$this->register_conditions();

		// Activate controls for widgets
		add_action( 'elementor/element/common/section_powerpack_elements_advanced/before_section_end', function( $element, $args ) {

			$this->add_controls( $element, $args );

		}, 10, 2 );

		add_action( 'elementor/element/section/section_powerpack_elements_advanced/before_section_end', function( $element, $args ) {

			$this->add_controls( $element, $args );

		}, 10, 2 );

		// Conditions for widgets
		add_action( 'elementor/widget/render_content', function( $widget_content, $element ) {

			$settings = $element->get_settings();

			if ( 'yes' === $settings['pp_display_conditions_enable'] ) {

				// Set the conditions
				$this->set_conditions( $element->get_id(), $settings['pp_display_conditions'] );

				// if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				// 	ob_start();
				// 	$this->render_editor_notice( $settings );
				// 	$widget_content .= ob_get_clean();
				// }

				if ( ! $this->is_visible( $element->get_id(), $settings['pp_display_conditions_relation'] ) ) { // Check the conditions
					if ( 'yes' !== $settings['pp_display_conditions_output'] ) {
						return; // And on frontend we stop the rendering of the widget
					}
				}
			}

			return $widget_content;

		}, 10, 2 );

		// Conditions for widgets
		add_action( 'elementor/frontend/widget/before_render', function( $element ) {

			$settings = $element->get_settings();

			if ( 'yes' === $settings['pp_display_conditions_enable'] ) {

				// Set the conditions
				$this->set_conditions( $element->get_id(), $settings['pp_display_conditions'] );

				if ( ! $this->is_visible( $element->get_id(), $settings['pp_display_conditions_relation'] ) ) { // Check the conditions
					$element->add_render_attribute( '_wrapper', 'class', 'pp-visibility-hidden' );
				}
			}

		}, 10, 1 );

		// Conditions for sections
		add_action( 'elementor/frontend/section/before_render', function( $element ) {

			$settings = $element->get_settings();

			if ( 'yes' === $settings['pp_display_conditions_enable'] ) {

				// Set the conditions
				$this->set_conditions( $element->get_id(), $settings['pp_display_conditions'] );

				if ( ! $this->is_visible( $element->get_id(), $settings['pp_display_conditions_relation'] ) ) { // Check the conditions
					$element->add_render_attribute( '_wrapper', 'class', 'pp-visibility-hidden' );
				}
			}

		}, 10, 1 );

	}

	protected function render_editor_notice( $settings ) {
		?><span><?php _e( 'This widget is displayed conditionally.', 'powerpack' ); ?></span>
		<?php
	}

	/**
	 * @param string $condition_name
	 *
	 * @return Module_Base|Module_Base[]
	 */
	public function get_conditions( $condition_name = null ) {
		if ( $condition_name ) {
			if ( isset( $this->_conditions[ $condition_name ] ) ) {
				return $this->_conditions[ $condition_name ];
			}
			return null;
		}

		return $this->_conditions;
	}

	/**
	 * Set conditions.
	 *
	 * Sets the conditions property to all conditions comparison values
	 *
	 * @since 1.4.7
	 * @access protected
	 * @static
	 *
	 * @param mixed  $conditions  The conditions from the repeater field control
	 *
	 * @return void
	 */
	protected function set_conditions( $id, $conditions = [] ) {
		if ( ! $conditions ) {
			return;
		}

		foreach ( $conditions as $index => $condition ) {
			$key        = $condition['pp_condition_key'];
			$operator   = $condition['pp_condition_operator'];
			$value      = $condition[ 'pp_condition_' . $key . '_value' ];
			$name       = null;

			if ( array_key_exists( 'pp_condition_' . $key . '_name', $condition ) ) {
				$name = $condition[ 'pp_condition_' . $key . '_name' ];
			}

			$_condition = $this->get_conditions( $key );

			if ( ! $_condition ) {
				continue;
			}

			$check = $_condition->check( $name, $operator, $value );

			$this->conditions[ $id ][ $key . '_' . $condition['_id'] ] = $check;
		}
	}

	/**
	 * Set the Conditions options array
	 *
	 * @since 1.4.13
	 *
	 * @access private
	 */
	private function get_conditions_options() {

		$groups = $this->get_groups();

		foreach ( $this->_conditions as $_condition ) {
			$groups[ $_condition->get_group() ]['options'][ $_condition->get_name() ] = $_condition->get_title();
		}

		return $groups;
	}

	/**
	 * Add Value Controls
	 *
	 * Loops through conditions and adds the controls
	 * which select the value to check
	 *
	 * @since 1.4.13.4
	 *
	 * @access private
	 * @return void
	 */
	private function add_name_controls() {
		if ( ! $this->_conditions ) {
			return;
		}

		foreach ( $this->_conditions as $_condition ) {

			if ( false === $_condition->get_name_control() ) {
				continue;
			}

			$condition_name     = $_condition->get_name();
			$control_key        = 'pp_condition_' . $condition_name . '_name';
			$control_settings   = $_condition->get_name_control();

			// Show this only if the user select this specific condition
			$control_settings['condition'] = [
				'pp_condition_key' => $condition_name,
			];

			//
			$this->_conditions_repeater->add_control( $control_key, $control_settings );
		}
	}

	/**
	 * Add Value Controls
	 *
	 * Loops through conditions and adds the controls
	 * which select the value to check
	 *
	 * @since 1.4.13
	 *
	 * @access private
	 * @return void
	 */
	private function add_value_controls() {
		if ( ! $this->_conditions ) {
			return;
		}

		foreach ( $this->_conditions as $_condition ) {

			$condition_name     = $_condition->get_name();
			$control_key        = 'pp_condition_' . $condition_name . '_value';
			$control_settings   = $_condition->get_value_control();

			// Show this only if the user select this specific condition
			$control_settings['condition'] = [
				'pp_condition_key' => $condition_name,
			];

			//
			$this->_conditions_repeater->add_control( $control_key, $control_settings );
		}
	}

	/**
	 * Check conditions.
	 *
	 * Checks for all or any conditions and returns true or false
	 * depending on wether the content can be shown or not
	 *
	 * @since 1.4.7
	 * @access protected
	 * @static
	 *
	 * @param mixed  $relation  Required conditions relation
	 *
	 * @return bool
	 */
	protected function is_visible( $id, $relation ) {

		if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			if ( 'any' === $relation ) {
				if ( ! in_array( true, $this->conditions[ $id ] ) ) {
					return false;
				}
			} else {
				if ( in_array( false, $this->conditions[ $id ] ) ) {
					return false;
				}
			}
		}

		return true;
	}
}
