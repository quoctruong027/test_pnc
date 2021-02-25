<?php
/**
 * WC_PRL_Deployment class
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
 * Deployment class.
 *
 * @class    WC_PRL_Deployment
 * @version  1.0.0
 */
class WC_PRL_Deployment {

	/**
	 * Deployment settings meta are copied from the low-level data object to this array - @see WC_PRL_Deployment::load_data().
	 * @var array
	 */
	public $item_data = array();

	/**
	 * A reference to the deployment data object - @see WC_PRL_Deployment_Data.
	 * @var WC_PRL_Deployment_Data
	 */
	public $data = null;

	/**
	 * Current source data.
	 * @var array
	 */
	protected $source_data;

	/**
	 * Current source hash.
	 * @var string
	 */
	protected $source_hash;

	/**
	 * Deployment results are stored at runtime in this array - @see WC_PRL_Deployment::get_products().
	 * @var array
	 */
	private $products;

	/**
	 * Runtime property that checks if the deployment has a contexual engine.
	 * @var bool
	 */
	private $has_contextual_engine;

	/**
	 * Runtime property that indicates whether this deployment's cache is out of date.
	 * @var bool
	 */
	private $expired;

	/**
	 * Constructor.
	 *
	 * @param  int|object  $deployment  ID to load from the DB (optional).
	 */
	public function __construct( $deployment ) {

		if ( is_numeric( $deployment ) ) {
			$this->data = WC_PRL()->db->deployment->get( absint( $deployment ) );
		} elseif ( $deployment instanceof WC_PRL_Deployment_Data ) {
			$this->data = $deployment;
		}

		if ( is_object( $this->data ) ) {
			$this->load_data();
		}

		$this->init_runtime_properties();
	}

	/**
	 * Initialize bundled item class props from bundled item data object.
	 *
	 * @return void
	 */
	private function load_data() {

		// Defaults.
		$defaults        = array();
		$this->item_data = wp_parse_args( $this->data->get_meta_data(), $defaults );

		foreach ( $defaults as $key => $value ) {
			$this->$key = $this->item_data[ $key ];
		}
	}

	/**
	 * Initialize runtime properties.
	 *
	 * @return void
	 */
	private function init_runtime_properties() {
		$this->source_data           = array();
		$this->has_contextual_engine = false;
		$this->expired               = false;
	}

	/*---------------------------------------------------*/
	/*  Setters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Sets the contextual engine state.
	 *
	 * @param  bool $value
	 * @return void
	 */
	public function set_contextual_engine_state( $value ) {
		$this->has_contextual_engine = boolval( $value );
	}

	/**
	 * Sets the source data.
	 *
	 * @param  array $value
	 * @return void
	 */
	public function set_source_data( $value ) {
		$this->source_data = $value;
	}

	/**
	 * Sets the expired state.
	 *
	 * @param  bool $value
	 * @return void
	 */
	public function set_expired( $value ) {
		$this->expired = boolval( $value );
	}

	/*---------------------------------------------------*/
	/*  Getters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Get Deployment ID.
	 * Returns the ID of the associated WC_PRL_Deployment_Data object - @see WC_PRL_Deployment_Data class.
	 *
	 * @return int|null
	 */
	public function get_id() {
		return is_object( $this->data ) ? $this->data->get_id() : null;
	}

	/**
	 * Get Engine ID.
	 *
	 * @return int|null
	 */
	public function get_engine_id() {
		return is_object( $this->data ) ? $this->data->get_engine_id() : null;
	}

	/**
	 * Get Engine type.
	 *
	 * @return string|null
	 */
	public function get_engine_type() {
		return is_object( $this->data ) ? $this->data->get_engine_type() : null;
	}

	/**
	 * Get Title.
	 *
	 * @return int|null
	 */
	public function get_title() {
		return is_object( $this->data ) ? $this->data->get_title() : null;
	}

	/**
	 * Get Description.
	 *
	 * @param  bool  $formatted
	 * @return int|null
	 */
	public function get_description( $formatted = false ) {
		$description = is_object( $this->data ) ? $this->data->get_description() : null;
		if ( ! empty( $description ) ) {
			$description = $formatted ? wpautop( do_shortcode( wp_kses_post( $description ) ) ) : $description;
		}

		return $description;
	}

	/**
	 * Get Display order.
	 *
	 * @return int|null
	 */
	public function get_display_order() {
		return is_object( $this->data ) ? $this->data->get_display_order() : null;
	}

	/**
	 * Get number of columns.
	 *
	 * @return int|null
	 */
	public function get_columns() {
		return is_object( $this->data ) ? $this->data->get_columns() : null;
	}

	/**
	 * Get max number of products.
	 *
	 * @return int|null
	 */
	public function get_limit() {
		return is_object( $this->data ) ? $this->data->get_limit() : null;
	}

	/**
	 * Get Location ID.
	 *
	 * @return int|null
	 */
	public function get_location_id() {
		return is_object( $this->data ) ? $this->data->get_location_id() : null;
	}

	/**
	 * Get Hook.
	 *
	 * @return int|null
	 */
	public function get_hook() {
		return is_object( $this->data ) ? $this->data->get_hook() : null;
	}

	/**
	 * Get conditions data.
	 *
	 * @return array|null
	 */
	public function get_conditions_data() {
		return is_object( $this->data ) ? $this->data->get_conditions_data() : null;
	}

	/**
	 * Get item data.
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->item_data;
	}

	/**
	 * Get source data.
	 *
	 * @param  string  $context
	 * @return array
	 */
	public function get_source_data( $context = 'view' ) {

		if ( 'edit' === $context ) {
			return $this->source_data;
		} elseif ( 'view' === $context && in_array( $this->get_engine_type(), wc_prl_get_contextual_engine_types() ) ) {
			return $this->source_data;
		}

		return array();
	}

	/**
	 * Get tracking source hash.
	 *
	 * @param  string  $context
	 * @return array
	 */
	public function get_tracking_source_hash( $context = 'view' ) {

		if ( $this->source_hash ) {
			return $this->source_hash;
		}

		$source_hash = '';

		if ( $this->has_contextual_engine && 'product' === $this->get_engine_type() ) {
			$source_hash = array_pop( $this->source_data );
		}

		$this->source_hash = $source_hash;

		return $source_hash;
	}

	/**
	 * Get cache key.
	 *
	 * @return string
	 */
	public function get_cache_key() {

		$cache_key   = 'products';
		$context_key = '';

		if ( $this->has_contextual_engine ) {

			if ( in_array( $this->get_engine_type(), array( 'product', 'archive' ) ) ) {
				$context_key = md5( $this->get_engine_type() . json_encode( $this->source_data ) );
			}
		}

		return $cache_key . $context_key;
	}

	/**
	 * Get engine's product ids.
	 *
	 * @param  bool  $force
	 * @return array
	 */
	public function get_products( $force = false ) {

		// Runtime cache ?
		if ( ! $force && ! is_null( $this->products ) ) {
			return $this->products;
		}

		// Init engine -- Don't show if the engine is draft.
		$engine = new WC_PRL_Engine( $this->get_engine_id() );
		if ( ! $engine || ! $engine->is_active() ) {
			return array();
		}

		// Init.
		$products = array();

		// Parse and check if there are any contextual filters or amps in the engine...
		$this->set_contextual_engine_state( $engine->has_contextual_filters() || $engine->has_contextual_amplifiers() );

		// If is a contextual engine and has no right to do that, quit.
		if ( $this->has_contextual_engine && empty( $this->get_source_data() ) ) {
			return array();
		}

		// Fetch data or schedule.
		$cached_products = $this->data->get_meta( $this->get_cache_key() );

		if ( ! empty( $cached_products ) ) {

			$products = $engine->apply_dynamic_filters( $cached_products[ 'products' ], $this );

			if ( $force || time() > absint( $cached_products[ 'created_at' ] ) + $engine->refresh_interval_in_seconds ) {
				$this->set_expired( true );
				WC_PRL()->deployments->schedule_deployment_generation( $this );
				if ( wc_prl_debug_enabled() ) {
					echo '<!-- Scheduling regeneration for deployment #' . $this->get_id() . ' -->';
				}
			} else {
				if ( wc_prl_debug_enabled() ) {
					echo '<!-- Fetching cached results from meta key: `' . $this->get_cache_key() . '` -->';
				}
			}

			// Limit products to desired number.
			$products = array_slice( $products, 0, $this->get_limit() );

		} else {
			// Initial case -- no results.
			WC_PRL()->deployments->schedule_deployment_generation( $this );
			$products = null; // Show placeholder when null.
		}

		// Runtime Store.
		$this->products = $products;

		return $products;
	}

	/**
	 * Get Engine active status.
	 *
	 * @return bool|null
	 */
	public function is_active() {
		return is_object( $this->data ) ? $this->data->is_active() : null;
	}

	/**
	 * Whether has contexual engine or not.
	 *
	 * @return bool
	 */
	public function has_contextual_engine() {
		return $this->has_contextual_engine;
	}

	/**
	 * Has this deployment expired?.
	 *
	 * @return bool
	 */
	public function has_expired() {
		return $this->expired;
	}
}
