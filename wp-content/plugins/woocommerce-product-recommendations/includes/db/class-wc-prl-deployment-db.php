<?php
/**
 * WC_PRL_Deployment_DB class
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
 * Deployments DB API class.
 */
class WC_PRL_Deployment_DB {

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-recommendations' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-recommendations' ), '1.0.0' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		//...
	}

	/**
	 * Query deployments data from the DB.
	 *
	 * @param  array  $args  {
	 *     @type  string     $return           Return array format:
	 *
	 *         - 'all': entire row casted to array,
	 *         - 'ids': delpoyments ids only,
	 *         - 'objects': WC_PRL_Deployment_Data objects.
	 *
	 *     @type  int|array  $location_id      Deployment location id(s) in WHERE clause.
	 *     @type  int|array  $hook             Deployment hook(s) in WHERE clause.
	 *     @type  int|array  $engine_id        Deployment engine id(s) in WHERE clause.
	 *     @type  array      $order_by         ORDER BY field => order pairs.
	 *     @type  array      $meta_query       Deployment meta query parameters, uses 'WP_Meta_Query' - see https://codex.wordpress.org/Class_Reference/WP_Meta_Query .
	 * }
	 *
	 * @return array
	 */
	public function query( $args ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'return'          => 'all', // 'ids' | 'objects'
			'active'          => '',
			'engine_id'       => 0,
			'location_id'     => '',
			'hook'            => '',
			'order_by'        => array( 'id' => 'ASC' ),
			'limit'           => -1,
			'offset'          => -1,
			'meta_query'      => array()
		) );


		$table = $wpdb->prefix . 'woocommerce_prl_deployments';

		if ( in_array( $args[ 'return' ], array( 'ids', 'objects' ) ) ) {
			$select = $table . '.id';
		} else {
			$select = '*';
		}

		// Build the query.
		$sql      = "SELECT " . $select . " FROM {$table}";
		$join     = '';
		$where    = '';
		$order_by = '';

		$where_clauses    = array( '1=1' );
		$order_by_clauses = array();

		// WHERE clauses.
		if ( $args[ 'engine_id' ] ) {
			$engine_ids = array_map( 'absint', is_array( $args[ 'engine_id' ] ) ? $args[ 'engine_id' ] : array( $args[ 'engine_id' ] ) );
			$engine_ids = array_map( 'esc_sql', $engine_ids );

			$where_clauses[] = "{$table}.engine_id IN ('" . implode( "', '", $engine_ids ) . "')";
		}

		if ( $args[ 'hook' ] ) {

			$hooks = is_array( $args[ 'hook' ] ) ? $args[ 'hook' ] : array( $args[ 'hook' ] );
			$hooks = array_map( 'esc_sql', $hooks );

			$where_clauses[] = "{$table}.hook IN ('" . implode( "', '", $hooks ) . "')";
		}

		if ( $args[ 'location_id' ] ) {

			$location_ids = is_array( $args[ 'location_id' ] ) ? $args[ 'location_id' ] : array( $args[ 'location_id' ] );
			$location_ids = array_map( 'esc_sql', $location_ids );

			$where_clauses[] = "{$table}.location_id IN ('" . implode( "', '", $location_ids ) . "')";
		}

		if ( ! empty( $args[ 'active' ] ) ) {

			$active          = ( 'on' === $args[ 'active' ] ) ? 'on' : 'off';
			$where_clauses[] = "{$table}.active = '" . $active . "'";
		}

		// ORDER BY clauses.
		if ( $args[ 'order_by' ] && is_array( $args[ 'order_by' ] ) ) {
			foreach ( $args[ 'order_by' ] as $what => $how ) {
				$order_by_clauses[] = $table . '.' . esc_sql( strval( $what ) ) . " " . esc_sql( strval( $how ) );
			}
		}

		$order_by_clauses = empty( $order_by_clauses ) ? array( $table . '.id, ASC' ) : $order_by_clauses;

		// Build SQL query components.

		$where    = ' WHERE ' . implode( ' AND ', $where_clauses );
		$order_by = ' ORDER BY ' . implode( ', ', $order_by_clauses );
		$limit    = $args[ 'limit' ] > 0 ? ' LIMIT ' . absint( $args[ 'limit' ] ) : '';
		$offset   = $args[ 'offset' ] > 0 ? ' OFFSET ' . absint( $args[ 'offset' ] ) : '';
		// Assemble and run the query.

		$sql .= $join . $where . $order_by . $limit . $offset;

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			return array();
		}

		$a = array();

		if ( 'objects' === $args[ 'return' ] ) {
			foreach ( $results as $result ) {
				$a[] = self::get( $result->id );
			}
		} elseif ( 'ids' === $args[ 'return' ] ) {
			foreach ( $results as $result ) {
				$a[] = $result->id;
			}
		} else {
			foreach ( $results as $result ) {
				$a[] = (array) $result;
			}
		}

		return $a;
	}

	public function count() {
		global $wpdb;

		$table = $wpdb->prefix . 'woocommerce_prl_deployments';
		return $wpdb->get_var( "SELECT COUNT(id) FROM $table" );
	}

	/**
	 * Get a deployment from the DB.
	 *
	 * @param  mixed  $deployment
	 * @return false|WC_PRL_Deployment_Data
	 */
	public function get( $deployment ) {

		if ( is_numeric( $deployment ) ) {
			$deployment = absint( $deployment );
			$deployment = new WC_PRL_Deployment_Data( $deployment );
		} elseif ( $deployment instanceof WC_PRL_Deployment_Data ) {
			$deployment = new WC_PRL_Deployment_Data( $deployment );
		} else {
			$deployment = false;
		}

		if ( ! $deployment || ! is_object( $deployment ) || ! $deployment->get_id() ) {
			return false;
		}

		return $deployment;
	}

	/**
	 * Create a deployment in the DB.
	 *
	 * @param  array  $args
	 * @return false|int
	 *
	 * @throws Exception
	 */
	public function add( $args ) {

		$args = wp_parse_args( $args, array(
			'active'          => 'on',
			'engine_id'       => 0,
			'engine_type'     => '',
			'title'           => '',
			'description'     => '',
			'display_order'   => 0,
			'columns'         => 4,
			'rows'            => 1,
			'location_id'     => '',
			'hook'            => '',
			'meta_data'       => array(),
			'conditions_data' => array()
		) );

		// Empty attributes.
		if ( empty( $args[ 'engine_id' ] ) || empty( $args[ 'location_id' ] ) || empty( $args[ 'hook' ] ) ) {
			throw new Exception( __( 'Missing engine attributes.', 'woocommerce-product-recommendations' ) );
		}

		// No engine.
		$engine = new WC_PRL_Engine( absint( $args[ 'engine_id' ] ) );
		if ( 0 === $engine->get_id() ) {
			throw new Exception( __( 'Invalid engine.', 'woocommerce-product-recommendations' ) );
		}

		// Invalid engine type.
		$location = WC_PRL()->locations->get_location_by_hook( $args[ 'hook' ] );
		if ( ! $location ) {
			throw new Exception( __( 'Invalid location.', 'woocommerce-product-recommendations' ) );
		}

		if ( ! in_array( $engine->get_type(), $location->get_current_supported_engine_types() ) ) {
			throw new Exception( __( 'Invalid engine type.', 'woocommerce-product-recommendations' ) );
		}

		$deployment = new WC_PRL_Deployment_Data( array(
			'active'          => $args[ 'active' ],
			'engine_id'       => $engine->get_id(),
			'engine_type'     => $engine->get_type(),
			'title'           => $args[ 'title' ],
			'description'     => $args[ 'description' ],
			'display_order'   => $args[ 'display_order' ],
			'columns'         => $args[ 'columns' ],
			'limit'           => absint( $args[ 'rows' ] * $args[ 'columns' ] ),
			'location_id'     => $location->get_location_id(),
			'hook'            => $location->get_current_hook(),
			'meta_data'       => $args[ 'meta_data' ],
			'conditions_data' => $args[ 'conditions_data' ]
		) );

		return $deployment->save();
	}

	/**
	 * Update a deployment in the DB.
	 *
	 * @param  mixed  $deployment
	 * @param  array  $args
	 * @return boolean
	 */
	public function update( $deployment, $args ) {
		if ( is_numeric( $deployment ) ) {
			$deployment = absint( $deployment );
			$deployment = new WC_PRL_Deployment_Data( $deployment );
		}

		if ( is_object( $deployment ) && $deployment->get_id() && ! empty( $args ) && is_array( $args ) ) {

			// Empty attributes.
			if ( empty( $args[ 'engine_id' ] ) || empty( $args[ 'location_id' ] ) || empty( $args[ 'hook' ] ) ) {
				throw new Exception( __( 'Missing engine attributes.', 'woocommerce-product-recommendations' ) );
			}

			// No engine.
			$engine = new WC_PRL_Engine( absint( $args[ 'engine_id' ] ) );
			if ( 0 === $engine->get_id() ) {
				throw new Exception( __( 'Invalid engine.', 'woocommerce-product-recommendations' ) );
			}

			// Invalid engine type.
			$location = WC_PRL()->locations->get_location_by_hook( $args[ 'hook' ] );
			if ( ! $location ) {
				throw new Exception( __( 'Invalid location.', 'woocommerce-product-recommendations' ) );
			}

			if ( ! in_array( $engine->get_type(), $location->get_current_supported_engine_types() ) ) {
				throw new Exception( __( 'Invalid engine type.', 'woocommerce-product-recommendations' ) );
			}

			// Fill data.
			$args[ 'engine_type' ] = $engine->get_type();
			// Transform rows to limit and unset rows.
			$args[ 'limit' ]       = absint( $args[ 'rows' ] * $args[ 'columns' ] );
			unset( $args[ 'rows' ] );

			// Clear caches if the engine is changed.
			if ( $args[ 'engine_id' ] !== $deployment->get_engine_id() ) {
				$this->clear_caches( array( $deployment->get_id() ) );
			}

			$deployment->set_all( $args );
			return $deployment->save();
		}

		return false;
	}

	/**
	 * Delete a deployment from the DB.
	 *
	 * @param  mixed  $deployment
	 * @return void
	 */
	public function delete( $deployment ) {
		$deployment = self::get( $deployment );
		if ( $deployment ) {
			$deployment->delete();
		}
	}

	/**
	 * Delete caches for deployments.
	 *
	 * @param  array  $ids
	 * @return void
	 */
	public function clear_caches( $ids ) {

		if ( empty( $ids ) ) {
			return;
		}

		global $wpdb;
		$ids = implode( ',', $ids );
		return $wpdb->query( "DELETE FROM {$wpdb->prefix}woocommerce_prl_deploymentmeta WHERE `prl_deployment_id` IN ($ids) AND `meta_key` LIKE 'products%'");
	}
}
