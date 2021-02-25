<?php
/**
 * WC_PRL_Condition class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class used for managing deployment visibility conditions.
 *
 * @class    WC_PRL_Condition
 * @version  1.2.5
 */
abstract class WC_PRL_Condition {

	/**
	 * Condition identifier.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Condition title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Condition runtime complexity factors.
	 *
	 * Info: Seperating conditions based on complexity, we can achieve perfomance boost by executing them in order.
	 *
	 * @var int
	 */
	const ZERO_COMPLEXITY        = 0;
	const LOW_COMPLEXITY         = 10;
	const MEDIUM_COMPLEXITY      = 20;
	const QUERY_COMPLEXITY       = 50;
	const QUERY_JOIN_COMPLEXITY  = 80;

	protected $complexity;

	/**
	 * Supported engine types.
	 *
	 * @var array
	 */
	protected $supported_engine_types = array();

	/**
	 * Supported modifiers.
	 *
	 * @var array
	 */
	protected $supported_modifiers = array();

	/**
	 * Condition needs value.
	 *
	 * @var bool
	 */
	public $needs_value = false;

	/**
	 * Runtime caching of the category hierarchical tree.
	 *
	 * @since 1.2.5
	 *
	 * @var array
	 */
	protected static $product_categories_tree;

	/**
	 * Constructor.
	 */
	protected function __construct() {}

	/*---------------------------------------------------*/
	/*  Force methods.                                   */
	/*---------------------------------------------------*/

	/**
	 * Get admin html for filter inputs.
	 *
	 * @param  string|null $post_name
	 * @param  int      $condition_index
	 * @param  array    $condition_data
	 * @return void
	 */
	abstract public function get_admin_fields_html( $post_name, $condition_index, $condition_data );

	/*---------------------------------------------------*/
	/*  Setters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Set the current id.
	 *
	 * @param  string $value
	 * @return void
	 */
	public function set_id( $value ) {
		$this->id = $value;
	}

	/*---------------------------------------------------*/
	/*  Getters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Get the ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get the complexity factor.
	 *
	 * @return string
	 */
	public function get_complexity() {
		return absint( $this->complexity );
	}

	/**
	 * Get supported engine types.
	 *
	 * @return array
	 */
	public function get_supported_engine_types() {
		return apply_filters( 'woocommerce_prl_condition_get_supported_engine_types', $this->supported_engine_types, $this->id );
	}

	/**
	 * Get modifiers select html.
	 *
	 * @param  string  $selected
	 * @return string
	 */
	public function get_modifiers_select_options( $selected ) {

		foreach ( $this->supported_modifiers as $value => $label ) {
			echo '<option value="' . $value . '" ';
			selected( $selected, $value, true );
			echo '>' . $label . '</<option>';
		}
	}

	/*---------------------------------------------------*/
	/*  Conditionals.                                    */
	/*---------------------------------------------------*/

	/**
	 * Whether or not a condition is supported by an engine type.
	 *
	 * @param  string $type
	 * @return bool
	 */
	public function has_engine_type( $type ) {

		if ( in_array( $type, $this->get_supported_engine_types() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Apply the condition to the current request.
	 *
	 * @param  array  $data
	 * @param  WC_PRL_deployment  $deployment
	 * @return bool
	 */
	public function apply( $data, $deployment ) {

		if ( ! $this->has_engine_type( $deployment->get_engine_type() ) ) {
			return false;
		}

		if ( ! $this->validate( $data ) ) {
			return false;
		}

		return $this->check( $data, $deployment );
	}

	/**
	 * Actual checking for the condition.
	 *
	 * @param  array  $data
	 * @param  WC_PRL_deployment  $deployment
	 * @return bool
	 */
	public function check( $data, $deployment ) {
		return true;
	}

	/**
	 * Get the default term relatioship.
	 *
	 * @return string
	 */
	public function get_default_term_relationship() {
		/**
		 * 'woocommerce_prl_conditions_term_relatioship' filter.
		 *
		 * Whether multiple condition values should be evaluated through `AND` or `OR` logical operators.
		 *
		 * @param  WC_PRL_Condition  $this
		 */
		return apply_filters( 'woocommerce_prl_conditions_term_relatioship', 'or', $this );
	}

	/**
	* Checks if the provided modifier is inside the modifiers haystack.
	*
	* @param  string  $modifier
	* @param  array   $haystack
	* @return bool
	*/
	protected function modifier_is( $modifier, $haystack = array() ) {

		if ( ! is_array( $haystack ) ) {
			$haystack = array( $haystack );
		}

		return in_array( $modifier, $haystack );
	}

	/**
	 * Validates the condition data.
	 *
	 * @param  string $modifier
	 * @param  string $type
	 * @return bool
	 */
	public function validate( $data ) {

		if ( ! is_array( $data ) ) {
			return false;
		}

		// If the condition has a modifier_type, check for validity.
		if ( ! empty( $this->supported_modifiers ) ) {
			if ( ! in_array( $data[ 'modifier' ], array_keys( $this->supported_modifiers ) ) ) {
				return false;
			}
		}

		return true;
	}
}
