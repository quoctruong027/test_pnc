<?php
/**
 * WC_PRL_Location class
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
 * Abstract class for setting the Location of each Recommendation Engine.
 *
 * @class    WC_PRL_Location
 * @version  1.3.3
 */
abstract class WC_PRL_Location {

	/**
	 * Location ID.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Location title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Defaults.
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * Supported hooks.
	 *
	 * @var array
	 */
	protected $hooks = array();

	/**
	 * Location cacheable flag.
	 *
	 * @var bool
	 */
	protected $is_cacheable = true;

	/*---------------------------------------------------*/
	/*  Runtime properties.                              */
	/*---------------------------------------------------*/

	/**
	 * Current hook.
	 *
	 * @var string
	 */
	protected $hook = array();

	/**
	 * Location load status.
	 *
	 * @var bool
	 */
	protected $is_loaded = false;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->setup_hooks();
		$this->parse_and_fill_hook_data();
	}

	/*---------------------------------------------------*/
	/*  Force methods.                                   */
	/*---------------------------------------------------*/

	/**
	 * Setup all supported hooks based on the location id.
	 *
	 * @return void
	 */
	abstract protected function setup_hooks();

	/*---------------------------------------------------*/
	/*  Setters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Set the current hook.
	 *
	 * @param  string $hook
	 * @return void
	 */
	public function set_current_hook( $hook ) {
		$this->hook = $hook;
	}

	/**
	 * Set the loaded status.
	 *
	 * @since 1.1.0
	 *
	 * @param  bool $value
	 * @return void
	 */
	public function set_load_status( $value ) {
		$this->is_loaded = (bool) $value;
	}

	/*---------------------------------------------------*/
	/*  Getters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Get the location ID.
	 *
	 * @return string
	 */
	public function get_location_id() {
		return $this->id;
	}

	/**
	 * Get the location label.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get the current hook if exists.
	 *
	 * @return mixed
	 */
	public function get_current_hook() {
		return ! empty( $this->hook ) ? $this->hook : false;
	}

	/**
	 * Get hook data.
	 *
	 * @return mixed
	 */
	public function get_hook_data( $hook = null ) {

		if ( ! $hook ) {
			$hook = $this->get_current_hook();
		}

		if ( $hook ) {
			return isset( $this->hooks[ $hook ] ) ? $this->hooks[ $hook ] : false;
		}
	}

	/**
	 * Get the supported engine types for the current hook.
	 *
	 * @return array
	 */
	public function get_current_supported_engine_types() {

		$data = $this->get_hook_data();

		if ( ! empty( $data ) ) {
			return $data[ 'engine_type' ];
		}
	}

	/**
	 * Get the supported actions.
	 *
	 * @param  string $context
	 * @return array
	 */
	public function get_hooks( $context = 'edit' ) {

		if ( ! $this->is_active() && 'view' === $context ) {
			return array();
		}

		return $this->hooks;
	}

	/**
	 * Get the supported hooks by engine type.
	 *
	 * @return array
	 */
	public function get_hooks_by_engine_type( $engine_type ) {

		$hooks = array();

		foreach ( $this->hooks as $hook => $data ) {
			if ( in_array( $engine_type, $data[ 'engine_type' ] ) ) {
				$hooks[ $hook ] = $data;
			}
		}

		return $hooks;
	}

	/*---------------------------------------------------*/
	/*  Utilities.                                       */
	/*---------------------------------------------------*/

	/**
	 * Indicates whether this location page could be served from a page cache.
	 *
	 * @return boolean
	 */
	public function is_cacheable() {
		return $this->is_cacheable;
	}

	/**
	 * Check if the current location page is active.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return false;
	}

	/**
	 * Whether or not location's hooks are registered.
	 *
	 * @since 1.1.0
	 *
	 * @return boolean
	 */
	public function is_loaded() {
		return $this->is_loaded;
	}

	/**
	 * Get the source data.
	 *
	 * @param  WC_PRL_Deployment $deployment
	 * @param  array  $args
	 * @return array
	 */
	public function get_source_data( $deployment, $args = array() ) {

		$custom_parse_fn = isset( $this->hooks[ $deployment->get_hook() ][ 'parse_source_fn' ] ) ? $this->hooks[ $deployment->get_hook() ][ 'parse_source_fn' ] : null;

		if ( ! empty( $custom_parse_fn ) ) {
			$source_data = $custom_parse_fn( $deployment->get_engine_type(), $args );
		} else {
			$source_data = WC_PRL()->locations->parse_source( $deployment->get_engine_type(), $args );
		}

		/**
		 * 'woocommerce_prl_location_source_data' filter.
		 *
		 * Alters the source data for every deployment request.
		 *
		 * @param  array  $deployment
		 * @param  array  $args  Current hook's arguments array.
		 */
		return (array) apply_filters( 'woocommerce_prl_location_source_data', $source_data, $deployment, $args );
	}

	/**
	 * Fill data into hooks array.
	 *
	 * @return void
	 */
	private function parse_and_fill_hook_data() {

		if ( empty( $this->hooks ) ) {
			return;
		}

		$max_deployments = get_option( 'wc_prl_max_location_deployments', 3 );

		foreach ( $this->hooks as $hook => $data ) {

			$this->hooks[ $hook ] = array(
				'location_id'     => $this->id,
				'id'              => $data[ 'id' ],
				'label'           => $data[ 'label' ],
				'class'           => isset( $data[ 'class' ] ) ? $data[ 'class' ] : array(),
				'title_class'     => isset( $data[ 'title_class' ] ) ? $data[ 'title_class' ] : array(),
				'title_level'     => isset( $data[ 'title_level' ] ) ? $data[ 'title_level' ] : false,
				'priority'        => isset( $data[ 'priority' ] ) ? $data[ 'priority' ] : $this->defaults[ 'priority' ],
				'engine_type'     => isset( $data[ 'engine_type' ] ) ? $data[ 'engine_type' ] : $this->defaults[ 'engine_type' ],
				'args_number'     => isset( $data[ 'args_number' ] ) ? $data[ 'args_number' ] : $this->defaults[ 'args_number' ],
				'max_deployments' => isset( $data[ 'max_deployments' ] ) ? $data[ 'max_deployments' ] : $max_deployments,
				'parse_source_fn' => isset( $data[ 'parse_source_fn' ] ) ? $data[ 'parse_source_fn' ] : false
			);
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public static function parse_source( $engine_type, $args = array() ) {
		_deprecated_function( __METHOD__ . '()', '1.1.0', 'WC_PRL()->locations->parse_source' );
		return WC_PRL()->locations->parse_source( $engine_type, $args = array() );
	}
}
