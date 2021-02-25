<?php
/**
 * WC_PRL_Engine class
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
 * Engine.
 *
 * @class    WC_PRL_Engine
 * @version  1.4.0
 */
class WC_PRL_Engine extends WC_Data {

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'engine';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'prl_engine';

	/**
	 * Cache group.
	 *
	 * @var string
	 */
	protected $cache_group = 'engines';

	/**
	 * Stores engine data.
	 *
	 * @var array
	 */
	protected $data = array(
		'name'               => '',
		'slug'               => '',
		'description'        => '',
		'short_description'  => '',
		'date_created'       => null,
		'date_modified'      => null,
		'status'             => false,
		'type'               => '',
		'filters_data'       => array(),
		'amplifiers_data'    => array()
	);

	/**
	 * Cache dynamic filters array.
	 *
	 * @var array
	 */
	protected $dynamic_filters_data;

	/**
	 * Cache contextual flag on filters.
	 *
	 * @var bool|null
	 */
	protected $has_contextual_filters;

	/**
	 * Cache contextual flag on amplifiers.
	 *
	 * @since 1.4.0
	 *
	 * @var bool|null
	 */
	protected $has_contextual_amplifiers;

	/**
	 * Max sample array index per amplifier.
	 *
	 * @var int
	 */
	public $sampling_max_index;

	/**
	 * Max cached array index.
	 *
	 * @var int
	 */
	public $caching_max_index;

	/**
	 * Number of seconds before the cache is considered outdated.
	 *
	 * @var int
	 */
	public $refresh_interval_in_seconds;

	/**
	 * Get the engine if ID is passed, otherwise the engine is new and empty.
	 *
	 * @param int|WC_PRL_Engine|object $engine Engine to init.
	 */
	public function __construct( $engine = 0 ) {

		parent::__construct( $engine );

		if ( is_numeric( $engine ) && $engine > 0 ) {
			$this->set_id( $engine );
		} elseif ( $engine instanceof self ) {
			$this->set_id( absint( $engine->get_id() ) );
		} elseif ( ! empty( $engine->ID ) ) {
			$this->set_id( absint( $engine->ID ) );
		} else {
			$this->set_object_read( true );
		}

		$this->data_store = WC_Data_Store::load( 'prl_engine' );

		if ( $this->get_id() > 0 ) {
			try {
				$this->data_store->read( $this );
			} catch ( Exception $e ) {
				$this->set_id( 0 );
				$this->set_object_read( true );
			}
		}

		/**
		 * Filter `woocommerce_prl_engine_sampling_max_index` the sampling number.
		 *
		 * @param  WC_PRL_Engine  $engine
		 */
		$this->sampling_max_index          = apply_filters( 'woocommerce_prl_engine_sampling_max_index', 200, $this );

		/**
		 * Filter `woocommerce_prl_engine_caching_max_index` the sampling number.
		 *
		 * @param  WC_PRL_Engine  $engine
		 */
		$this->caching_max_index           = apply_filters( 'woocommerce_prl_engine_caching_max_index', 100, $this );

		/**
		 * Filter `woocommerce_prl_cache_refresh_interval` the interval for the cache regeneration.
		 *
		 * @param  WC_PRL_Engine  $engine
		 */
		$this->refresh_interval_in_seconds = wc_prl_get_cache_regeneration_threshold( $this );
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Methods for getting data from the engine object.
	*/

	/**
	 * Get engine name.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_name( $context = 'view' ) {
		return $this->get_prop( 'name', $context );
	}

	/**
	 * Get engine slug.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_slug( $context = 'view' ) {
		return $this->get_prop( 'slug', $context );
	}

	/**
	 * Get the engine type.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_type( $context = 'view' ) {
		return $this->get_prop( 'type', $context );
	}

	/**
	 * Get engine filters.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return array
	 */
	public function get_filters_data( $context = 'view' ) {
		$filters = $this->get_prop( 'filters_data', $context );
		return is_array( $filters ) ? $filters : array();
	}

	/**
	 * Get engine amplifiers.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return array
	 */
	public function get_amplifiers_data( $context = 'view' ) {
		$amplifiers = $this->get_prop( 'amplifiers_data', $context );
		return is_array( $amplifiers ) ? $amplifiers : array();
	}

	/**
	 * Get engine description.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_description( $context = 'view' ) {
		return $this->get_prop( 'description', $context );
	}

	/**
	 * Get engine short description.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_short_description( $context = 'view' ) {
		return $this->get_prop( 'short_description', $context );
	}

	/**
	 * Get engine status.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string
	 */
	public function get_status( $context = 'view' ) {
		return $this->get_prop( 'status', $context );
	}

	/**
	 * Get engine created date.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_created( $context = 'view' ) {
		return $this->get_prop( 'date_created', $context );
	}

	/**
	 * Get engine modified date.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_modified( $context = 'view' ) {
		return $this->get_prop( 'date_modified', $context );
	}

	/**
	 * Get Dynamic filters on runtime.
	 * Hint: Need to have a efficient order of execution.
	 *
	 * @param  bool  $force
	 * @return array
	 */
	public function get_dynamic_filters_data( $force = false ) {

		if ( ! $force && ! empty( $this->dynamic_filters_data ) ) {
			return $this->dynamic_filters_data;
		}

		// Store all filter instances.
		$dynamic_filters = array();

		foreach ( $this->get_filters_data() as $filter_data ) {

			$filter = WC_PRL()->filters->get_filter( $filter_data[ 'id' ] );

			if ( $filter && ! $filter->is_static() ) {
				$dynamic_filters[] = $filter_data;
			}
		}

		unset( $filter );

		// Modify order of dynamic filters execution.
		foreach ( wp_list_pluck( $dynamic_filters, 'id' ) as $index => $filter_id ) {

			// Μοve recently viewed filter up-front.
			if ( 'recently_viewed' === $filter_id ) {
				$temp_filter = $dynamic_filters[ $index ];
				unset( $dynamic_filters[ $index ] );
				$dynamic_filters = array_merge( array( $temp_filter ), $dynamic_filters );
			}
		}

		return $dynamic_filters;
	}

	/**
	 * Get query exclude set of IDs.
	 *
	 * @param  array $source_data
	 * @return array
	 */
	public function get_exclude_ids( $source_data ) {

		$exclude = array();

		if ( empty( $source_data ) ) {
			return $exclude;
		}

		// Exclude current product on Product type contextual engines.
		if ( 'product' === $this->get_type() ) {
			$exclude[] = absint( array_pop( $source_data ) );
		}

		return $exclude;
	}

	/**
	 * Returns the filtererd query args.
	 *
	 * @param  WC_PRL_Deployment $deployment
	 * @return array
	 */
	public function get_filtered_args( $deployment ) {

		$query_args = (array) apply_filters( 'woocommerce_prl_initial_engine_query_args', array(
			'exclude'    => $deployment->has_contextual_engine() ? $this->get_exclude_ids( $deployment->get_source_data() ) : array(),
			'limit'      => $this->sampling_max_index,
			'status'     => 'publish'
		), $deployment, $this );

		// Force return param.
		$query_args[ 'return' ] = 'ids';

		$filters = $this->get_filters_data();

		// Run the `on_sale` filter last.
		$temp_sale_filter = array();
		foreach ( $filters as $index => $filter_data ) {
			if ( 'on_sale' === $filter_data[ 'id' ] ) {
				$temp_sale_filter = $filter_data;
				unset( $filters[ $index ] );
			}
		}

		if ( ! empty( $temp_sale_filter ) ) {
			array_push( $filters, $temp_sale_filter );
		}

		foreach ( $filters as $filter_data ) {

			$filter = WC_PRL()->filters->get_filter( $filter_data[ 'id' ] );

			if ( $filter ) {
				$filter->apply( $query_args, $deployment, $filter_data );
			}
		}

		return $query_args;
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting engine data. These should not update anything in the
	| database itself and should only change what is stored in the class
	| object.
	*/

	/**
	 * Set engine name.
	 *
	 * @param string $name
	 */
	public function set_name( $name ) {
		$this->set_prop( 'name', $name );
	}

	/**
	 * Set engine slug.
	 *
	 * @param string $slug
	 */
	public function set_slug( $slug ) {
		$this->set_prop( 'slug', $slug );
	}

	/**
	 * Set engine type.
	 *
	 * @param string $type
	 */
	public function set_type( $type ) {

		$valid_types = wc_prl_get_engine_types();

		if ( isset( $valid_types[ $type ] ) ) {
			$this->set_prop( 'type', $type );
		} else {
			// No type or invalid.
			$type = $this->get_type() ? $this->get_type() : 'cart';
			$this->set_prop( 'type', $type );
		}
	}

	/**
	 * Set engine description.
	 *
	 * @param string $description
	 */
	public function set_description( $description ) {
		$this->set_prop( 'description', $description );
	}

	/**
	 * Set engine short description.
	 *
	 * @param string $short_description
	 */
	public function set_short_description( $short_description ) {
		$this->set_prop( 'short_description', $short_description );
	}

	/**
	 * Set engine amplifiers data.
	 *
	 * @param array $amplifiers_data
	 */
	public function set_amplifiers_data( $amplifiers_data ) {
		$this->set_prop( 'amplifiers_data', $amplifiers_data );
		$this->has_contextual_amplifiers = null;
	}

	/**
	 * Set engine filters data.
	 *
	 * @param array $filters_data
	 */
	public function set_filters_data( $filters_data ) {
		$this->set_prop( 'filters_data', $filters_data );
		$this->dynamic_filters_data = null;
		$this->has_contextual_filters = null;
	}

	/**
	 * Set engine status.
	 *
	 * @param string $status
	 */
	public function set_status( $status ) {
		$this->set_prop( 'status', $status );
	}

	/**
	 * Set engine created date.
	 *
	 * @param string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if their is no date.
	 */
	public function set_date_created( $date = null ) {
		$this->set_date_prop( 'date_created', $date );
	}

	/**
	 * Set engine modified date.
	 *
	 * @param string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if their is no date.
	 */
	public function set_date_modified( $date = null ) {
		$this->set_date_prop( 'date_modified', $date );
	}

	/*
	|--------------------------------------------------------------------------
	| Conditionals
	|--------------------------------------------------------------------------
	*/

	/**
	 * Checks the engine type.
	 *
	 * @param string|array $type
	 * @return bool
	 */
	public function is_type( $type ) {
		return ( $this->get_type() === $type || ( is_array( $type ) && in_array( $this->get_type(), $type ) ) );
	}

	/**
	 * Checks the engine is inactive.
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->get_status() === 'publish';
	}

	/**
	 * Checks if the engine has contextual filters.
	 *
	 * @param  bool  $force
	 * @return bool
	 */
	public function has_contextual_filters( $force = false ) {

		if ( ! $force && ! is_null( $this->has_contextual_filters ) ) {
			return $this->has_contextual_filters;
		}

		$this->has_contextual_filters = false;

		foreach( $this->get_filters_data() as $filter_data ) {

			if ( isset( $filter_data[ 'context' ] ) && 'yes' === $filter_data[ 'context' ] ) {

				$this->has_contextual_filters = true;
				break;
			}
		}

		return $this->has_contextual_filters;
	}

	/**
	 * Checks if the engine has contextual amplifiers.
	 *
	 * @since 1.4.0
	 *
	 * @param  bool  $force
	 * @return bool
	 */
	public function has_contextual_amplifiers( $force = false ) {

		if ( ! $force && ! is_null( $this->has_contextual_amplifiers ) ) {
			return $this->has_contextual_amplifiers;
		}

		$this->has_contextual_amplifiers = false;

		foreach( $this->get_amplifiers_data() as $amp_data ) {

			if ( isset( $amp_data[ 'context' ] ) && 'yes' === $amp_data[ 'context' ] ) {

				$this->has_contextual_amplifiers = true;
				break;
			}
		}

		return $this->has_contextual_amplifiers;
	}

	/*
	|--------------------------------------------------------------------------
	| Other Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Fetches product results.
	 *
	 * @param  array $query_args
	 * @return array
	 */
	public function query( $query_args ) {
		return wc_get_products( $query_args );
	}

	/**
	 * Returns the filtererd query args.
	 *
	 * @param  array $products
	 * @param  WC_PRL_Deployment $deployment
	 * @return array
	 */
	public function apply_dynamic_filters( $products, $deployment ) {

		$dynamic_filters        = $this->get_dynamic_filters_data();
		$show_only_out_of_stock = false;

		foreach ( $this->get_dynamic_filters_data() as $filter_data ) {

			// Determine if showing only out-of-stock.
			if ( 'stock_status' === $filter_data[ 'id' ] ) {
				if ( 'is' === $filter_data[ 'modifier' ] && 'outofstock' === $filter_data[ 'value' ] ) {
					$show_only_out_of_stock = true;
				} elseif ( 'is-not' === $filter_data[ 'modifier' ] && 'instock' === $filter_data[ 'value' ] ) {
					$show_only_out_of_stock = true;
				}
			}

			$filter = WC_PRL()->filters->get_filter( $filter_data[ 'id' ] );
			$filter->run( $products, $deployment->get_limit(), $filter_data, $this );
		}

		// Add an additional stock_status filter if applicable.
		if ( ! $show_only_out_of_stock && 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {

			$additional_filter_data = array(
				'id'       => 'stock_status',
				'modifier' => 'is-not',
				'value'    => 'outofstock'
			);

			$filter = WC_PRL()->filters->get_filter( $additional_filter_data[ 'id' ] );
			$filter->run( $products, $deployment->get_limit(), $additional_filter_data, $this );
		}

		/**
		 * 'woocommerce_prl_exclude_current_source' filter.
		 *
		 * Auto-exclude current source
		 *
		 * @param  WC_PRL_Deployment  $deployment
		 */
		if ( apply_filters( 'woocommerce_prl_exclude_current_source', true, $deployment ) ) {

			if ( 'product' === $this->get_type() || 'cart' === $this->get_type() ) {

				$products = array_diff( $products, $deployment->get_source_data( 'edit' ) );

			} elseif ( 'order' === $this->get_type() ) {

				$order_id = $deployment->get_source_data( 'edit' );
				$order    = wc_get_order( absint( array_pop( $order_id ) ) );

				if ( $order ) {

					foreach ( $order->get_items() as $order_item ) {
						$product_ids[] = $order_item->get_product_id();
					}

					$products = array_diff( $products, $product_ids );
				}
			}

			// Exclude nothing from `archive` engines...
		}

		return $products;
	}

	/**
	 * Do a weighted merge of each amplifier results.
	 *
	 * @param  array $results
	 * @param  int $max_index
	 * @return array
	 */
	public function weight_merge( $results, $max_index ) {

		$weighted_list = array();

		// Loop each amplifier and accumulate the weights.
		foreach ( $results as $id => $amplifier ) {

			foreach ( $amplifier[ 'products' ] as $index => $product_id ) {

				$factor = ( $max_index - $index ) * $amplifier[ 'weight' ];

				if ( isset( $weighted_list[ $product_id ] ) ) {
					$weighted_list[ $product_id ] += $factor;
				} else {
					$weighted_list[ $product_id ] = $factor;
				}
			}
		}

		// Sort by weight.
		arsort( $weighted_list );

		return array_keys( $weighted_list );
	}

	/**
	 * Ensure properties are set correctly before save.
	 */
	public function validate_props() {

		// Filters sanity.
		$filters_data = $this->get_filters_data();
		foreach ( $filters_data as $index => $data ) {

			$filter = WC_PRL()->filters->get_filter( $data[ 'id' ] );

			if ( ! $filter ) {
				unset( $filters_data[ $index ] );
			}

			$has_value = isset( $data[ 'value' ] ) && ( ! empty( $data[ 'value' ] ) || $data[ 'value' ] === '0' );
			if ( $filter->needs_value && ! $has_value ) {
				unset( $filters_data[ $index ] );
			}
		}

		$this->set_filters_data( $filters_data );

		// Amps sanity.
		$amps_data = $this->get_amplifiers_data();
		$amps      = array();

		foreach ( $amps_data as $index => $data ) {

			$amp = WC_PRL()->amplifiers->get_amplifier( $data[ 'id' ] );

			if ( ! $amp || ! is_a( $amp, 'WC_PRL_Amplifier' ) ) {
				unset( $amps_data[ $index ] );
				continue;
			}

			// Unique amps.
			$key = array_search( $amp->get_id(), array_column( $amps_data, 'id' ) );
			if ( $key === $index ) {
				$amps[] = $data;
			}
		}
		$this->set_amplifiers_data( $amps );
	}

	/**
	 * Save data (either create or update depending on if we are working on an existing product).
	 *
	 * @return int
	 */
	public function save() {

		$this->validate_props();

		if ( $this->data_store ) {
			// Trigger action before saving to the DB. Use a pointer to adjust object props before save.
			do_action( 'woocommerce_before_' . $this->object_type . '_object_save', $this, $this->data_store );

			if ( $this->get_id() ) {
				$this->data_store->update( $this );
			} else {
				$this->data_store->create( $this );
			}
		}

		// Clear dismissible welcome notice.
		WC_PRL_Admin_Notices::remove_dismissible_notice( 'welcome' );

		return $this->get_id();
	}

}
