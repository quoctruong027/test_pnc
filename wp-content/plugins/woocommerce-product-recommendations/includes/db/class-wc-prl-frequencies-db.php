<?php
/**
 * WC_PRL_Frequencies_DB class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frequencies DB API class.
 *
 * @version 1.4.0
 */
class WC_PRL_Frequencies_DB {

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
	 * Query frequencies from the DB.
	 *
	 * @param  array  $args  {
	 *     @type  string        $return           Return array format:
	 *
	 *         - 'all': entire row casted to array,
	 *         - 'ids': bucket ids only,
	 *
	 *     @type  string|array  $context       Product ID(s) in WHERE clause.
	 *     @type  int|array     $product_id       Product ID(s) in WHERE clause.
	 *     @type  int|array     $expire_date      Expire Date in WHERE clause.
	 *     @type  array         $order_by         ORDER BY field => order pairs.
	 * }
	 *
	 * @return array
	 */
	public function query( $args ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'return'          => 'all', // 'ids'
			'product_id'      => array(),
			'context'         => 'order',
			'has_expired'     => null,
			'order_by'        => array( 'id' => 'ASC' )
		) );

		$table = $wpdb->prefix . 'woocommerce_prl_frequencies';

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
		if ( $args[ 'product_id' ] ) {
			$product_ids = array_map( 'absint', is_array( $args[ 'product_id' ] ) ? $args[ 'product_id' ] : array( $args[ 'product_id' ] ) );
			$product_ids = array_map( 'esc_sql', $product_ids );

			$where_clauses[] = "{$table}.product_id IN ('" . implode( "', '", $product_ids ) . "')";
		}

		if ( $args[ 'context' ] ) {
			$contextes = array_map( 'wc_clean', is_array( $args[ 'context' ] ) ? $args[ 'context' ] : array( $args[ 'context' ] ) );
			$contextes = array_map( 'esc_sql', $contextes );

			$where_clauses[] = "{$table}.context IN ('" . implode( "', '", $contextes ) . "')";
		}

		if ( ! is_null( $args[ 'has_expired' ] ) ) {
			$has_expired      = (bool) $args[ 'has_expired' ];
			$where_clauses[] = $has_expired ? "{$table}.expire_date <= " . time() : "{$table}.expire_date > " . time();
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
	 * Create or update a frequency record.
	 *
	 * @param  array  $args
	 * @return false|int
	 *
	 * @throws Exception
	 */
	public function cache( $args ) {

		$args = wp_parse_args( $args, array(
			'product_id'  => 0,
			'context'     => 'order',
			'count'       => 0,
			'base_total'  => 0,
			'expire_date' => ''
		) );

		// Empty attributes.
		if ( empty( $args[ 'product_id' ] ) || empty( $args[ 'context' ] ) || empty( $args[ 'count' ] ) || empty( $args[ 'base_total' ] ) || empty( $args[ 'expire_date' ] ) ) {
			throw new Exception( __( 'Missing record attributes.', 'woocommerce-product-recommendations' ) );
		}

		$hash = md5( absint( $args[ 'product_id' ] ) . '_' . wc_clean( $args[ 'context' ] ) );

		global $wpdb;
		$create_or_update_sql = '
			INSERT INTO `' . $wpdb->prefix . 'woocommerce_prl_frequencies`
				( `hash`, `product_id`, `context`, `count`, `base_total`, `expire_date` )
			VALUES
				( \'' . $hash . '\', ' . absint( $args[ 'product_id' ] ) . ', \'' . wc_clean( $args[ 'context' ] ) . '\', ' . absint( $args[ 'count' ] ) . ', ' . absint( $args[ 'base_total' ] ) . ', ' . absint( $args[ 'expire_date' ] ) . ' )
				ON DUPLICATE KEY UPDATE `count` = ' . absint( $args[ 'count' ] ) . ', `base_total` = ' . absint( $args[ 'base_total' ] ) . ', expire_date = ' . absint( $args[ 'expire_date' ] );

		$wpdb->query( $create_or_update_sql );

		return ( $wpdb->insert_id ) ? $wpdb->insert_id : false;
	}
}
