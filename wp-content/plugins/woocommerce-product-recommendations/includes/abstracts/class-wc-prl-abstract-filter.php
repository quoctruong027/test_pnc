<?php
/**
 * WC_PRL_Filter class
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
 * Abstract class used for managing engine Filters.
 *
 * @class    WC_PRL_Filter
 * @version  1.2.5
 */
abstract class WC_PRL_Filter {

	/**
	 * Filter identifier.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Filter title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Filter type. (static|dynamic)
	 *
	 * @var string
	 */
	protected $type = 'static';

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
	protected $supported_modifiers;

	/**
	 * Filter needs value.
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
	 * @param  int      $filter_index
	 * @param  array    $filter_data
	 * @return void
	 */
	abstract public function get_admin_fields_html( $post_name, $filter_index, $filter_data );

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
	 * Get supported engine types.
	 *
	 * @return array
	 */
	public function get_supported_engine_types() {
		return apply_filters( 'woocommerce_prl_filter_get_supported_engine_types', $this->supported_engine_types, $this->id );
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
	 * Whether or not a filter is supported by an engine type.
	 *
	 * @param  string  $type
	 * @return bool
	 */
	public function has_engine_type( $type ) {

		if ( in_array( $type, $this->get_supported_engine_types() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Whether the filter is running at runtime or not.
	 *
	 * @return bool
	 */
	public function is_static() {
		return ( $this->type === 'static' );
	}

	/**
	 * Apply the filter to the query args array.
	 *
	 * @param  array  $query_args
	 * @param  WC_PRL_Deployment  $deployment
	 * @param  array  $data
	 * @return void
	 */
	public function apply( &$query_args, $deployment, $data = array() ) {

		if ( ! $this->has_engine_type( $deployment->get_engine_type() ) ) {
			return true;
		}

		if ( ! $this->validate( $data ) ) {
			return;
		}

		if ( $this->maybe_sync_context( $data, $deployment->get_source_data(), $deployment->get_engine_type() ) && is_null( $data[ 'value' ] ) ) {
			// This is the case, when it's a contextual filter with null value -- Early quit.
			$query_args[ 'force_empty_set' ] = true;
			return;
		}

		$query_args = $this->filter( $query_args, $deployment, $data );
	}

	/**
	 * Apply dynamic filter proccess.
	 *
	 * @param  array  $products
	 * @param  int    $max_products
	 * @param  array  $data
	 * @param  WC_PRL_Engine  $engine
	 * @return void
	 */
	public function run( &$products, $max_products, $data, $engine ) {
		// ...
	}

	/**
	 * Replace the filter value based on the context..
	 *
	 * @param  array  &$data
	 * @param  array  $source_data
	 * @param  string $engine_type
	 * @return mixed
	 */
	protected function maybe_sync_context( &$data, $source_data, $engine_type ) {

		if ( isset( $data[ 'context' ] ) && 'yes' === $data[ 'context' ] ) {

			if ( ! isset( $data[ 'value' ] ) ) {
				$data[ 'value' ] = null;
			}

			$data[ 'value' ] = $this->parse_contextual_value( $source_data, $engine_type, $data );

			return true;
		}

		return false;
	}

	/**
	 * Parse the contextual value.
	 * Hint: Return `null` is the filter is not applicable.
	 *
	 * @param  array  $source_data
	 * @param  string $engine_type
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function parse_contextual_value( $source_data, $engine_type, $value ) {
		return null;
	}

	/**
	 * Filter the query args array.
	 *
	 * @param  array  $query_args
	 * @param  WC_PRL_Deployment  $deployment
	 * @param  array  $data
	 * @return array
	 */
	public function filter( $query_args, $deployment, $data ) {
		return $query_args;
	}

	/**
	 * Validates the filter data.
	 *
	 * @param  array  $data
	 * @return bool
	 */
	public function validate( $data ) {

		if ( ! is_array( $data ) ) {
			return false;
		}

		// If the filter has a modifier_type, check for validity.
		if ( ! empty( $this->supported_modifiers ) ) {
			if ( ! in_array( $data[ 'modifier' ], array_keys( $this->supported_modifiers ) ) ) {
				return false;
			}
		}

		return true;
	}
}
