<?php
/**
 * WC_PRL_DB class
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
 * Product Recommendations DB API class.
 */
class WC_PRL_DB {

	/**
	 * A reference to the deployment DB Model - @see WC_PRL_Deployment_DB.
	 * @var WC_PRL_Deployment_DB
	 */
	public $deployment;

	/**
	 * A reference to the tracking DB Model - @see WC_PRL_Tracking_DB.
	 * @var WC_PRL_Tracking_DB
	 */
	public $tracking;

	/**
	 * A reference to the frequencies DB Model - @see WC_PRL_Frequencies_DB.
	 * @var WC_PRL_Frequencies_DB
	 */
	public $frequencies;

	/**
	 * Runtime shared memory for `posts_clauses` filters.
	 * @var array
	 */
	private $posts_clauses;

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

		// Deployments meta table fix.
		add_action( 'init', array( $this, 'wpdb_deployments_table_fix' ), 0 );
		add_action( 'switch_blog', array( $this, 'wpdb_deployments_table_fix' ), 0 );

		// Register Engines data store.
		add_filter( 'woocommerce_data_stores', array( $this, 'register_engine_data_store' ), 10 );

		// Attach DB Models to public properties.
		$this->deployment  = new WC_PRL_Deployment_DB();
		$this->tracking    = new WC_PRL_Tracking_DB();
		$this->frequencies = new WC_PRL_Frequencies_DB();

	}

	/**
	 * Get the shared posts clauses.
	 */
	public function get_shared_posts_clauses() {
		return $this->posts_clauses;
	}

	/**
	 * Set the shared posts clauses.
	 *
	 * @param array
	 */
	public function set_shared_posts_clauses( $args ) {
		$this->posts_clauses = $args;
	}

	/**
	 * Get terms of mulptiple objects at once.
	 *
	 * @param  array  $ids
	 * @param  string  $term_taxonomy
	 * @param  array  $args
	 * @return false|int
	 *
	 * @throws Exception
	 */
	public function get_object_terms( $ids = array(), $term_taxonomy, $args = array() ) {

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		if ( empty( $ids ) || empty( $term_taxonomy ) ) {
			return false;
		}

		$args = wp_parse_args( $args, array(
			'fields' => 'slugs'
		) );

		if ( 'slugs' === $args[ 'fields' ] ) {
			$column = 'slug';
		} else {
			$column = 'term_id';
		}

		global $wpdb;

		$sql = "
			SELECT t." . $column . " FROM wp_terms AS t
			INNER JOIN wp_term_taxonomy AS tt ON ( tt.term_id = t.term_id )
			INNER JOIN wp_term_relationships AS tr ON ( tr.term_taxonomy_id = tt.term_taxonomy_id )
			WHERE tt.taxonomy IN ( '%s' )
			AND tr.object_id IN ( " . implode( ',', array_map( 'absint', $ids ) ) . " )
			GROUP BY t." . $column . "
			ORDER BY t." . $column . " ASC;
			";

		$results = $wpdb->get_results( $wpdb->prepare( $sql, $term_taxonomy ) );
		$terms  = array();

		if ( ! empty( $results ) ) {
			foreach ( $results as $term ) {
				$terms[] = $term->$column;
			}
		}

		return $terms;
	}

	/*
	|--------------------------------------------------------------------------
	| Engines.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Registers the Engine Custom Post Type data store.
	 *
	 * @param  array  $stores
	 * @return array
	 */
	public static function register_engine_data_store( $stores ) {

		$stores[ 'prl_engine' ] = 'WC_PRL_Engine_Data_Store_CPT';

		return $stores;
	}

	/*
	|--------------------------------------------------------------------------
	| Deployments.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Make WP see 'prl_deployment' as a meta type.
	 */
	public function wpdb_deployments_table_fix() {
		global $wpdb;
		$wpdb->prl_deploymentmeta  = $wpdb->prefix . 'woocommerce_prl_deploymentmeta';
		$wpdb->tables[]            = 'woocommerce_prl_deploymentmeta';
	}
}
