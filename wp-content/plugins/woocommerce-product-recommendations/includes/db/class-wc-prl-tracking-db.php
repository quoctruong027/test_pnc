<?php
/**
 * WC_PRL_Tracking_DB class
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
 * Tracking DB API class.
 *
 * @version 1.4.7
 */
class WC_PRL_Tracking_DB {

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
	 * Query tracking event buckets from the DB.
	 *
	 * @param  array  $args  {
	 *     @type  string     $return           Return array format:
	 *
	 *         - 'all': entire row casted to array,
	 *         - 'ids': bucket ids only,
	 *
	 *     @type  int|array  $time_span        Bucket's time span(s) in WHERE clause.
	 *     @type  int|array  $location_hash    View location hash(s) in WHERE clause.
	 *     @type  int|array  $deployment_id    View deployment id(s) in WHERE clause.
	 *     @type  int|array  $engine_id        View engine id(s) in WHERE clause.
	 *     @type  array      $order_by         ORDER BY field => order pairs.
	 * }
	 *
	 * @return array
	 */
	public function query_views( $args ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'return'          => 'all', // 'ids'
			'deployment_id'   => 0,
			'engine_id'       => 0,
			'location_hash'   => '',
			'start_date'      => '',
			'end_date'        => '',
			'order_by'        => array( 'id' => 'ASC' )
		) );

		$table = $wpdb->prefix . 'woocommerce_prl_tracking_views';

		if ( in_array( $args[ 'return' ], array( 'ids' ) ) ) {
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

		if ( $args[ 'deployment_id' ] ) {
			$deployment_ids = array_map( 'absint', is_array( $args[ 'deployment_id' ] ) ? $args[ 'deployment_id' ] : array( $args[ 'deployment_id' ] ) );
			$deployment_ids = array_map( 'esc_sql', $deployment_ids );

			$where_clauses[] = "{$table}.deployment_id IN ('" . implode( "', '", $deployment_ids ) . "')";
		}

		if ( $args[ 'location_hash' ] ) {
			$location_hashes = is_array( $args[ 'location_hash' ] ) ? $args[ 'location_hash' ] : array( $args[ 'location_hash' ] );
			$location_hashes = array_map( 'esc_sql', $location_hashes );

			$where_clauses[] = "{$table}.location_hash IN ('" . implode( "', '", $location_hashes ) . "')";
		}

		// DATE BETWEEN.
		if ( ! empty( $args[ 'start_date' ] ) ) {
			$start_date      = absint( $args[ 'start_date' ] );
			$where_clauses[] = "{$table}.time_span >= {$start_date}";
		}
		if ( ! empty( $args[ 'end_date' ] ) ) {
			$end_date        = absint( $args[ 'end_date' ] );
			$where_clauses[] = "{$table}.time_span < {$end_date}";
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

		// Assemble and run the query.

		$sql .= $join . $where . $order_by;

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			return array();
		}

		$a = array();

		if ( 'ids' === $args[ 'return' ] ) {
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

	public function query_clicks( $args ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'return'          => 'all', // 'ids'
			'deployment_id'   => 0,
			'engine_id'       => 0,
			'product_id'      => 0,
			'location_hash'   => '',
			'start_date'      => '',
			'end_date'        => '',
			'order_by'        => array( 'id' => 'ASC' )
		) );

		$table = $wpdb->prefix . 'woocommerce_prl_tracking_clicks';

		if ( in_array( $args[ 'return' ], array( 'ids' ) ) ) {
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

		if ( $args[ 'deployment_id' ] ) {
			$deployment_ids = array_map( 'absint', is_array( $args[ 'deployment_id' ] ) ? $args[ 'deployment_id' ] : array( $args[ 'deployment_id' ] ) );
			$deployment_ids = array_map( 'esc_sql', $deployment_ids );

			$where_clauses[] = "{$table}.deployment_id IN ('" . implode( "', '", $deployment_ids ) . "')";
		}

		if ( $args[ 'product_id' ] ) {
			$product_ids = array_map( 'absint', is_array( $args[ 'product_id' ] ) ? $args[ 'product_id' ] : array( $args[ 'product_id' ] ) );
			$product_ids = array_map( 'esc_sql', $product_ids );

			$where_clauses[] = "{$table}.product_id IN ('" . implode( "', '", $product_ids ) . "')";
		}

		if ( $args[ 'location_hash' ] ) {
			$location_hashes = is_array( $args[ 'location_hash' ] ) ? $args[ 'location_hash' ] : array( $args[ 'location_hash' ] );
			$location_hashes = array_map( 'esc_sql', $location_hashes );

			$where_clauses[] = "{$table}.location_hash IN ('" . implode( "', '", $location_hashes ) . "')";
		}

		// DATE BETWEEN.
		if ( ! empty( $args[ 'start_date' ] ) ) {
			$start_date      = absint( $args[ 'start_date' ] );
			$where_clauses[] = "{$table}.time_span >= {$start_date}";
		}
		if ( ! empty( $args[ 'end_date' ] ) ) {
			$end_date        = absint( $args[ 'end_date' ] );
			$where_clauses[] = "{$table}.time_span < {$end_date}";
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

		// Assemble and run the query.

		$sql .= $join . $where . $order_by;

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			return array();
		}

		$a = array();

		if ( 'ids' === $args[ 'return' ] ) {
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

	public function get_top_convertion_rates( $args, $limit = 5 ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'group'           => 'products', // 'products, locations'
			'start_date'      => '',
			'end_date'        => ''
		) );

		if ( ! in_array( $args[ 'group' ], array( 'products', 'locations' ) ) ) {
			return array();
		}

		if ( $args[ 'group' ] === 'products' ) {
			$group = 'product_id';
		} elseif ( $args[ 'group' ] === 'locations' ) {
			$group = 'location_hash';
		}

		$table    = $wpdb->prefix . 'woocommerce_prl_tracking_conversions';
		$date_key = 'ordered_time';
		$select   = "{$table}.{$group}, COUNT(*) AS rate, sub_table.clicks, COUNT(*) / IFNULL( NULLIF( sub_table.clicks, 0 ), 1 ) * 100 as cr";

		// Build the query.
		$sql             = "SELECT " . $select . " FROM {$table}";
		$join_start_date = '';
		$join_end_date   = '';
		$group_by        = ' GROUP BY '. $group;
		$order_by        = ' ORDER BY cr DESC';
		$limit           = ' LIMIT ' . absint( $limit );

		// Where.
		$where           = '';
		$where_clauses   = array( '1=1' );

		// DATE BETWEEN.
		if ( ! empty( $args[ 'start_date' ] ) ) {
			$start_date      = absint( $args[ 'start_date' ] );
			$where_clauses[] = "{$table}.{$date_key} >= {$start_date}";
			$join_start_date = "AND {$wpdb->prefix}woocommerce_prl_tracking_clicks.time_span >= {$start_date}";
		}

		if ( ! empty( $args[ 'end_date' ] ) ) {
			$end_date        = absint( $args[ 'end_date' ] );
			$where_clauses[] = "{$table}.{$date_key} < {$end_date}";
			$join_end_date   = "AND {$wpdb->prefix}woocommerce_prl_tracking_clicks.time_span < {$end_date}";
		}

		$where           = ' WHERE ' . implode( ' AND ', $where_clauses );

		// Join.
		$join            = " LEFT JOIN (
								SELECT {$group}, SUM(count) as clicks
								FROM {$wpdb->prefix}woocommerce_prl_tracking_clicks
								WHERE 1=1
								{$join_start_date}
								{$join_end_date}
								GROUP BY {$wpdb->prefix}woocommerce_prl_tracking_clicks.{$group}
							) as sub_table on {$table}.{$group} = sub_table.{$group}";

		$sql  .= $join . $where . $group_by . $order_by . $limit;

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			return array();
		}

		$a = array();
		foreach ( $results as $result ) {
			$a[] = (array) $result;
		}

		return $a;
	}

	public function get_top( $args, $limit = 5 ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'type'            => 'clicks', // clicks, revenue
			'group'           => 'products', // 'products, locations'
			'start_date'      => '',
			'end_date'        => ''
		) );

		if ( ! in_array( $args[ 'group' ], array( 'products', 'locations' ) ) ) {
			return array();
		}

		if ( ! in_array( $args[ 'type' ], array( 'clicks', 'revenue' ) ) ) {
			return array();
		}

		if ( $args[ 'group' ] === 'products' ) {
			$select = $group = 'product_id';
		} elseif ( $args[ 'group' ] === 'locations' ) {
			$select = $group = 'location_hash';
		}

		if ( 'clicks' === $args[ 'type' ] ) {
			$table    = $wpdb->prefix . 'woocommerce_prl_tracking_clicks';
			$select  .= ', SUM(count) as rate';
			$date_key = 'time_span';
		} elseif ( 'revenue' === $args[ 'type' ] ) {
			$table    = $wpdb->prefix . 'woocommerce_prl_tracking_conversions';
			$select  .= ', SUM(total) as rate';
			$date_key = 'ordered_time';
		} elseif ( 'conversions' === $args[ 'type' ] ) {
			$table    = $wpdb->prefix . 'woocommerce_prl_tracking_conversions';
			$select  .= ', COUNT(*) as rate';
			$date_key = 'ordered_time';
		}

		// Build the query.
		$sql      = "SELECT " . $select . " FROM {$table}";
		$where    = '';
		$group_by = ' GROUP BY '. $group;
		$order_by = ' ORDER BY rate DESC';
		$limit    = ' LIMIT ' . absint( $limit );

		$where_clauses    = array( '1=1' );

		// DATE BETWEEN.
		if ( ! empty( $args[ 'start_date' ] ) ) {
			$start_date      = absint( $args[ 'start_date' ] );
			$where_clauses[] = "{$table}.{$date_key} >= {$start_date}";
		}
		if ( ! empty( $args[ 'end_date' ] ) ) {
			$end_date        = absint( $args[ 'end_date' ] );
			$where_clauses[] = "{$table}.{$date_key} < {$end_date}";
		}

		$where = ' WHERE ' . implode( ' AND ', $where_clauses );
		$sql  .= $where . $group_by . $order_by . $limit;

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			return array();
		}

		$a = array();
		foreach ( $results as $result ) {
			$a[] = (array) $result;
		}

		return $a;
	}

	public function query_conversions( $args ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'return'          => 'all', // 'ids'
			'deployment_id'   => 0,
			'engine_id'       => 0,
			'product_id'      => 0,
			'location_hash'   => '',
			'start_date'      => '',
			'end_date'        => '',
			'order_by'        => array( 'id' => 'ASC' )
		) );

		$table = $wpdb->prefix . 'woocommerce_prl_tracking_conversions';

		if ( in_array( $args[ 'return' ], array( 'ids' ) ) ) {
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

		if ( $args[ 'deployment_id' ] ) {
			$deployment_ids = array_map( 'absint', is_array( $args[ 'deployment_id' ] ) ? $args[ 'deployment_id' ] : array( $args[ 'deployment_id' ] ) );
			$deployment_ids = array_map( 'esc_sql', $deployment_ids );

			$where_clauses[] = "{$table}.deployment_id IN ('" . implode( "', '", $deployment_ids ) . "')";
		}

		if ( $args[ 'product_id' ] ) {
			$product_ids = array_map( 'absint', is_array( $args[ 'product_id' ] ) ? $args[ 'product_id' ] : array( $args[ 'product_id' ] ) );
			$product_ids = array_map( 'esc_sql', $product_ids );

			$where_clauses[] = "{$table}.product_id IN ('" . implode( "', '", $product_ids ) . "')";
		}

		if ( $args[ 'location_hash' ] ) {
			$location_hashes = is_array( $args[ 'location_hash' ] ) ? $args[ 'location_hash' ] : array( $args[ 'location_hash' ] );
			$location_hashes = array_map( 'esc_sql', $location_hashes );

			$where_clauses[] = "{$table}.location_hash IN ('" . implode( "', '", $location_hashes ) . "')";
		}

		// DATE BETWEEN.
		if ( ! empty( $args[ 'start_date' ] ) ) {
			$start_date      = absint( $args[ 'start_date' ] );
			$where_clauses[] = "{$table}.ordered_time >= {$start_date}";
		}
		if ( ! empty( $args[ 'end_date' ] ) ) {
			$end_date        = absint( $args[ 'end_date' ] );
			$where_clauses[] = "{$table}.ordered_time < {$end_date}";
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

		// Assemble and run the query.

		$sql .= $join . $where . $order_by;

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			return array();
		}

		$a = array();

		if ( 'ids' === $args[ 'return' ] ) {
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

	/**
	 * Create or Updates a tracking view bucket in the DB.
	 *
	 * @param  array  $args
	 * @return false|int
	 *
	 * @throws Exception
	 */
	public function add_view_event( $args ) {

		$args = wp_parse_args( $args, array(
			'time_span'       => 0,
			'deployment_id'   => 0,
			'engine_id'       => 0,
			'location_hash'   => '',
			'source_hash'     => ''
		) );

		// Empty attributes.
		if ( empty( $args[ 'deployment_id' ] ) || empty( $args[ 'engine_id' ] ) || empty( $args[ 'location_hash' ] ) ) {
			throw new Exception( __( 'Missing event attributes.', 'woocommerce-product-recommendations' ) );
		}

		// Calculate time span for the bucket.
		if ( empty( $args[ 'time_span' ] ) ) {
			$args[ 'time_span' ] = $this->get_current_time_span();
		}

		global $wpdb;

		// Increment views bucket counter into the DB or Create new Bucket.
		$hash_key             = md5( $args[ 'time_span' ] . $args[ 'deployment_id' ] . $args[ 'source_hash' ] );
		$create_or_update_sql = '
			INSERT INTO `' . $wpdb->prefix . 'woocommerce_prl_tracking_views`
				( hash_key, time_span, deployment_id, engine_id, location_hash, source_hash )
			VALUES
				( \'' . $hash_key . '\', ' . absint( $args[ 'time_span' ] ) . ', ' . absint( $args[ 'deployment_id' ] ) . ', ' . absint( $args[ 'engine_id' ] ) . ', \'' . wc_clean( $args[ 'location_hash' ] ) . '\', \'' . wc_clean( $args[ 'source_hash' ] ) . '\' )
			ON DUPLICATE KEY UPDATE count = count + 1';

		$wpdb->query( $create_or_update_sql );

		wc_prl_invalidate_reports();

		return ( $wpdb->insert_id ) ? $wpdb->insert_id : false;
	}

	/**
	 * Create or Updates a tracking view bucket in the DB.
	 *
	 * @param  array  $args
	 * @return false|int
	 *
	 * @throws Exception
	 */
	public function add_click_event( $args ) {

		$args = wp_parse_args( $args, array(
			'time_span'       => 0,
			'deployment_id'   => 0,
			'engine_id'       => 0,
			'location_hash'   => '',
			'source_hash'     => '',
			'product_id'      => 0
		) );

		// Empty attributes.
		if ( empty( $args[ 'deployment_id' ] ) || empty( $args[ 'engine_id' ] ) || empty( $args[ 'location_hash' ] ) || empty( $args[ 'product_id' ] ) ) {
			throw new Exception( __( 'Missing event attributes.', 'woocommerce-product-recommendations' ) );
		}

		// Calculate time span for the bucket.
		if ( empty( $args[ 'time_span' ] ) ) {
			$args[ 'time_span' ] = $this->get_current_time_span();
		}

		global $wpdb;

		// Increment views bucket counter into the DB or Create new Bucket.
		$hash_key             = md5( $args[ 'time_span' ] . $args[ 'deployment_id' ] . $args[ 'product_id' ] . $args[ 'source_hash' ] );
		$create_or_update_sql = '
			INSERT INTO `' . $wpdb->prefix . 'woocommerce_prl_tracking_clicks`
				( hash_key, time_span, deployment_id, engine_id, location_hash, source_hash, product_id )
			VALUES
				( \'' . $hash_key . '\', ' . absint( $args[ 'time_span' ] ) . ', ' . absint( $args[ 'deployment_id' ] ) . ', ' . absint( $args[ 'engine_id' ] ) . ', \'' . wc_clean( $args[ 'location_hash' ] ) . '\', \'' . wc_clean( $args[ 'source_hash' ] ) . '\', ' . absint( $args[ 'product_id' ] ) . ' )
			ON DUPLICATE KEY UPDATE count = count + 1';

		$wpdb->query( $create_or_update_sql );

		wc_prl_invalidate_reports();

		return ( $wpdb->insert_id ) ? $wpdb->insert_id : false;
	}

	/**
	 * Create a tracking conversion in the DB.
	 *
	 * @param  array  $args
	 * @return false|int
	 *
	 * @throws Exception
	 */
	public function add_conversion_event( $args ) {

		$args = wp_parse_args( $args, array(
			'added_to_cart_time' => 0,
			'ordered_time'       => 0,
			'deployment_id'      => 0,
			'engine_id'          => 0,
			'location_hash'      => '',
			'source_hash'        => '',
			'product_id'         => 0,
			'order_id'           => 0,
			'order_item_id'      => 0,
			'total'              => null,
			'total_tax'          => null
		) );

		// Empty attributes.
		if ( empty( $args[ 'deployment_id' ] ) || empty( $args[ 'engine_id' ] ) || empty( $args[ 'location_hash' ] ) || empty( $args[ 'product_id' ] ) || empty( $args[ 'order_id' ] ) ) {
			throw new Exception( __( 'Missing event attributes.', 'woocommerce-product-recommendations' ) );
		}

		// Add current time.
		if ( empty( $args[ 'ordered_time' ] ) ) {
			$args[ 'ordered_time' ] = time();
		}

		global $wpdb;

		// Increment views bucket counter into the DB or Create new Bucket.
		$create_sql = '
			INSERT INTO `' . $wpdb->prefix . 'woocommerce_prl_tracking_conversions`
				( added_to_cart_time, ordered_time, deployment_id, engine_id, location_hash, source_hash, product_id, order_id, order_item_id, total, total_tax )
			VALUES
				( ' . absint( $args[ 'added_to_cart_time' ] ) . ', ' . absint( $args[ 'ordered_time' ] ) . ', ' . absint( $args[ 'deployment_id' ] ) . ', ' . absint( $args[ 'engine_id' ] ) . ', \'' . wc_clean( $args[ 'location_hash' ] ) . '\', \'' . wc_clean( $args[ 'source_hash' ] ) . '\', ' . absint( $args[ 'product_id' ] ) . ', ' . absint( $args[ 'order_id' ] ) . ', ' . absint( $args[ 'order_item_id' ] ) . ', ' . (double) ( $args[ 'total' ] ) . ', ' . (double) ( $args[ 'total_tax' ] ) . ' )';

		$wpdb->query( $create_sql );

		wc_prl_invalidate_reports();

		return ( $wpdb->insert_id ) ? $wpdb->insert_id : false;
	}

	/**
	 * Get the current hourly time span.
	 *
	 * @return int
	 */
	private function get_current_time_span() {

		$min_bucket   = 3600; // An hour.
		$current_time = time();

		return $current_time - ( $current_time % $min_bucket );
	}
}
