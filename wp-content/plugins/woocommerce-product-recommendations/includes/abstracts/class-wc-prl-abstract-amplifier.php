<?php
/**
 * WC_PRL_Amplifier class
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
 * Abstract class used for managing engine Amplifiers.
 *
 * @class    WC_PRL_Amplifier
 * @version  1.0.0
 */
abstract class WC_PRL_Amplifier {

	/**
	 * Amplifier identifier.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Amplifier title.
	 *
	 * @var string
	 */
	protected $title;

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
	protected $supported_modifiers = array(
		'order' => array( 'DESC', 'ASC' ),
	);

	/**
	 * Constructor.
	 */
	protected function __construct() {
		// ...
	}

	/*---------------------------------------------------*/
	/*  Force methods.                                   */
	/*---------------------------------------------------*/

	/**
	 * Get admin html for filter inputs.
	 *
	 * @param  string|null $post_name
	 * @param  int      $amplifier_index
	 * @param  array    $amplifier_data
	 * @return void
	 */
	abstract public function get_admin_fields_html( $post_name, $amplifier_index, $amplifier_data );

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
		return apply_filters( 'woocommerce_prl_amplifier_get_supported_engine_types', $this->supported_engine_types, $this->id );
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
	 * Whether or not a amplifier is supported by an engine type.
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
	 * How many steps this amp needs to be calculated in the background generator.
	 *
	 * @since 1.4.0
	 *
	 * @return int
	 */
	public function get_steps_count() {
		return 1;
	}

	/**
	 * Run a step based on the step index. (non-zero index)
	 *
	 * @since 1.4.0
	 *
	 * @return mixed
	 */
	public function run_step( $step_index, $deployment, $args = array() ) {
		return null;
	}

	/**
	 * Apply the amplifier to the query args array.
	 *
	 * @param  array             $query_args
	 * @param  WC_PRL_Deployment $deployment
	 * @param  array             $data
	 * @return array
	 */
	public function amplify( $query_args, $deployment, $data = array() ) {

		if ( ! $this->has_engine_type( $deployment->get_engine_type() ) ) {
			return $query_args;
		}

		if ( ! $this->validate( $data ) ) {
			return $query_args;
		}

		$query_args = $this->amp( $query_args, $deployment, $data );

		return $query_args;
	}

	/**
	 * Amplify the query.
	 *
	 * @param  array $query_args
	 * @param  WC_PRL_Deployment $deployment
	 * @param  array $data
	 * @return array
	 */
	public function amp( $query_args, $deployment, $data ) {
		return $query_args;
	}

	/**
	 * Fetches all products based on the filters.
	 *
	 * @param  array $query_args
	 * @return array
	 */
	public function query( $query_args ) {

		// Query.
		$products = wc_get_products( $query_args );

		// Cleanup.
		$this->remove_amp();

		return $products;
	}

	/**
	 * Removes global filters that might get registered relative to the previous query.
	 *
	 * @return void
	 */
	public function remove_amp() {}

	/**
	 * Validates the amplifier data.
	 *
	 * @param  string $modifier
	 * @param  string $type
	 * @return bool
	 */
	public function validate( $data ) {

		if ( ! is_array( $data ) ) {
			return false;
		}

		// If the filter has a modifier_type, check for validity.
		if ( ! empty( $this->supported_modifiers ) ) {
			if ( isset( $data[ 'modifier' ] ) && ! in_array( $data[ 'modifier' ], array_keys( $this->supported_modifiers ) ) ) {
				return false;
			}
		}

		return true;
	}
}
