<?php
/**
 * WC_PRL_Engine_Data_Store_CPT class
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
 * Engine CPT Data Store.
 *
 * @class    WC_PRL_Engine_Data_Store_CPT
 * @version  1.3.0
 */
class WC_PRL_Engine_Data_Store_CPT extends WC_Data_Store_WP {

	/**
	 * Data stored in meta keys, but not considered "meta".
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array(
		'_filters_data',
		'_amplifiers_data'
	);

	/**
	 * Stores updated props.
	 *
	 * @var array
	 */
	protected $updated_props = array();

	/*
	|--------------------------------------------------------------------------
	| CRUD Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Method to create a new engine in the database.
	 *
	 * @param WC_PRL_Engine $engine
	 */
	public function create( &$engine ) {
		if ( ! $engine->get_date_created( 'edit' ) ) {
			$engine->set_date_created( current_time( 'timestamp', true ) );
		}

		$id = wp_insert_post(
			apply_filters(
				'woocommerce_new_engine_data', array(
					'post_type'      => 'prl_engine',
					'post_status'    => $engine->get_status() ? $engine->get_status() : 'publish',
					'post_author'    => get_current_user_id(),
					'post_title'     => $engine->get_name() ? $engine->get_name() : __( 'Engine', 'woocommerce-product-recommendations' ),
					'post_content'   => $engine->get_description(),
					'post_excerpt'   => $engine->get_short_description(),
					'post_parent'    => 0,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_date'      => gmdate( 'Y-m-d H:i:s', $engine->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt'  => gmdate( 'Y-m-d H:i:s', $engine->get_date_created( 'edit' )->getTimestamp() ),
					'post_name'      => $engine->get_slug( 'edit' ),
				)
			), true
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$product->set_id( $id );

			$this->update_post_meta( $engine, true );
			$this->update_terms( $engine, true );
			$this->handle_updated_props( $engine );

			$engine->save_meta_data();
			$engine->apply_changes();

			$this->clear_caches( $engine );

			do_action( 'woocommerce_prl_new_engine', $id );
		}
	}

	/**
	 * Method to read a engine from the database.
	 *
	 * @param  WC_PRL_Engine $engine Product object.
	 * @throws Exception If invalid engine.
	 */
	public function read( &$engine ) {
		$engine->set_defaults();
		$post_object = get_post( $engine->get_id() );

		if ( ! $engine->get_id() || ! $post_object || 'prl_engine' !== $post_object->post_type ) {
			throw new Exception( __( 'Invalid engine.', 'woocommerce-product-recommendations' ) );
		}

		$engine->set_props(
			array(
				'name'              => $post_object->post_title,
				'slug'              => $post_object->post_name,
				'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
				'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
				'status'            => $post_object->post_status,
				'description'       => $post_object->post_content,
				'short_description' => $post_object->post_excerpt
			)
		);

		$this->read_engine_data( $engine );
		$engine->set_object_read( true );
	}

	/**
	 * Method to update a engine in the database.
	 *
	 * @param WC_PRL_Engine $engine
	 */
	public function update( &$engine ) {
		$engine->save_meta_data();
		$changes = $engine->get_changes();

		// Only update the post when the post data changes.
		if ( array_intersect( array( 'description', 'short_description', 'name', 'status', 'date_created', 'date_modified', 'slug' ), array_keys( $changes ) ) ) {
			$post_data = array(
				'post_content'   => $engine->get_description( 'edit' ),
				'post_excerpt'   => $engine->get_short_description( 'edit' ),
				'post_title'     => $engine->get_name( 'edit' ),
				'post_name'      => $engine->get_slug( 'edit' ),
				'post_status'    => $engine->get_status() ? $engine->get_status() : 'publish',
				'post_type'      => 'prl_engine',
			);
			if ( $engine->get_date_created( 'edit' ) ) {
				$post_data[ 'post_date']     = gmdate( 'Y-m-d H:i:s', $engine->get_date_created( 'edit' )->getOffsetTimestamp() );
				$post_data[ 'post_date_gmt'] = gmdate( 'Y-m-d H:i:s', $engine->get_date_created( 'edit' )->getTimestamp() );
			}
			if ( isset( $changes[ 'date_modified' ] ) && $engine->get_date_modified( 'edit' ) ) {
				$post_data[ 'post_modified' ]     = gmdate( 'Y-m-d H:i:s', $engine->get_date_modified( 'edit' )->getOffsetTimestamp() );
				$post_data[ 'post_modified_gmt' ] = gmdate( 'Y-m-d H:i:s', $engine->get_date_modified( 'edit' )->getTimestamp() );
			} else {
				$post_data[ 'post_modified' ]     = current_time( 'mysql' );
				$post_data[ 'post_modified_gmt' ] = current_time( 'mysql', 1 );
			}

			/**
			 * When updating this object, to prevent infinite loops, use $wpdb
			 * to update data, since wp_update_post spawns more calls to the
			 * save_post action.
			 *
			 * This ensures hooks are fired by either WP itself (admin screen save),
			 * or an update purely from CRUD.
			 */
			if ( doing_action( 'save_post' ) ) {
				$GLOBALS[ 'wpdb' ]->update( $GLOBALS[ 'wpdb' ]->posts, $post_data, array( 'ID' => $engine->get_id() ) );
				clean_post_cache( $engine->get_id() );
			} else {
				wp_update_post( array_merge( array( 'ID' => $engine->get_id() ), $post_data ) );
			}
			$engine->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.

		} else { // Only update post modified time to record this save event.
			$GLOBALS[ 'wpdb' ]->update(
				$GLOBALS[ 'wpdb' ]->posts,
				array(
					'post_modified'     => current_time( 'mysql' ),
					'post_modified_gmt' => current_time( 'mysql', 1 ),
				),
				array(
					'ID' => $engine->get_id(),
				)
			);
			clean_post_cache( $engine->get_id() );
		}

		$this->update_post_meta( $engine );
		$this->update_terms( $engine );
		$this->handle_updated_props( $engine );

		$engine->apply_changes();

		$this->clear_caches( $engine );

		do_action( 'woocommerce_prl_update_engine', $engine->get_id() );
	}

	/**
	 * Method to delete a engine from the database.
	 *
	 * @param WC_PRL_Engine $engine
	 * @param array         $args
	 */
	public function delete( &$engine, $args = array() ) {
		$id = $engine->get_id();

		$args = wp_parse_args(
			$args, array(
				'force_delete' => false,
			)
		);

		if ( ! $id ) {
			return;
		}

		if ( $args[ 'force_delete' ] ) {
			do_action( 'woocommerce_prl_before_delete_engine', $id );
			wp_delete_post( $id );
			$engine->set_id( 0 );
			do_action( 'woocommerce_prl_delete_engine', $id );
		} else {
			wp_trash_post( $id );
			$engine->set_status( 'trash' );
			do_action( 'woocommerce_prl_trash_engine', $id );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Additional Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Read engine data.
	 *
	 * @param WC_PRL_Engine $engine
	 */
	protected function read_engine_data( &$engine ) {
		$id = $engine->get_id();

		// Read terms.
		$type = wc_get_object_terms( $engine->get_id(), 'prl_engine_type' );
		$type = is_array( $type ) ? array_pop( $type ) : false;

		if ( $type instanceof WP_Term ) {
			$engine->set_type( $type->slug );
		}

		// Read meta.
		$engine->set_props(
			array(
				'filters_data'    => get_post_meta( $id, '_filters_data', true ),
				'amplifiers_data' => get_post_meta( $id, '_amplifiers_data', true )
			)
		);
	}

	/**
	 * Helper method that updates all the post meta for a engine based on it's settings in the WC_PRL_Engine class.
	 *
	 * @param WC_PRL_Engine $engine
	 * @param bool          $force Force update. Used during create.
	 */
	protected function update_post_meta( &$engine, $force = false ) {
		$meta_key_to_props = array(
			'_filters_data'    => 'filters_data',
			'_amplifiers_data' => 'amplifiers_data'
		);

		$props_to_update = $force ? $meta_key_to_props : $this->get_props_to_update( $engine, $meta_key_to_props );

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $engine->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			switch ( $prop ) {
				default:
					$updated = update_post_meta( $engine->get_id(), $meta_key, $value );
					break;
			}
			if ( $updated ) {
				$this->updated_props[] = $prop;
			}
		}
	}

	/**
	 * For all stored terms in all taxonomies, save them to the DB.
	 *
	 * @param WC_PRL_Engine $engine
	 * @param bool          $force  Force update. Used during create.
	 */
	protected function update_terms( &$engine, $force = false ) {
		$changes = $engine->get_changes();

		if ( $force || array_key_exists( 'type', $changes ) ) {
			$current_deployments = WC_PRL()->db->deployment->query( array( 'return' => 'ids', 'engine_id' => $engine->get_id() ) );
			if ( ! empty( $current_deployments ) && ! $force ) {
				WC_PRL_Admin_Notices::add_notice( __( 'There are active deployments using this engine. Engine type change is not allowed.', 'woocommerce-product-recommendations' ), 'warning', true );
				return;
			}

			wp_set_object_terms( $engine->get_id(), $engine->get_type(), 'prl_engine_type', false );
		}
	}

	/**
	 * Handle updated meta props after updating meta data.
	 *
	 * @param WC_PRL_Engine $engine
	 */
	protected function handle_updated_props( &$engine ) {

		// Trigger action so 3rd parties can deal with updated props.
		do_action( 'woocommerce_prl_engine_object_updated_props', $engine, $this->updated_props );

		// After handling, we can reset the props array.
		$this->updated_props = array();
	}

	/**
	 * Clear any caches.
	 *
	 * @param WC_PRL_Engine $engine
	 */
	protected function clear_caches( &$engine ) {
		wc_prl_delete_engine_transients( $engine->get_id() );
		WC_PRL_Core_Compatibility::invalidate_cache_group( 'engine_' . $engine->get_id() );
	}

	/**
	 * Get engines based on type.
	 *
	 * @param string $type
	 * @param int    $limit
	 */
	public function get_engines_by_type( $type, $limit = -1 ) {

		return get_posts(
			array(
				'post_type'      => 'prl_engine',
				'posts_per_page' => $limit,
				'post_status'    => 'publish',
				'tax_query'      => array(
					array(
						'taxonomy' => 'prl_engine_type',
						'field'    => 'slug',
						'terms'    => array( $type ),
					)
				)
			)
		);
	}

	/**
	 * Search engine data for a term and return ids.
	 *
	 * @param  string   $term Search term.
	 * @param  array    $type Type of engine.
	 * @param  null|int $limit Limit returned results.
	 * @return array of ids
	 */
	public function search_engines( $term, $type = array(), $limit = null ) {
		global $wpdb;

		$post_types    = array( 'prl_engine' );
		$post_statuses = array( 'publish' );
		$type_join     = '';
		$type_where    = '';
		$status_where  = '';
		$limit_query   = '';
		$term          = wc_strtolower( $term );

		// See if search term contains OR keywords.
		if ( strstr( $term, ' or ' ) ) {
			$term_groups = explode( ' or ', $term );
		} else {
			$term_groups = array( $term );
		}

		if ( ! empty( $type ) ) {
			$type_join  = "LEFT JOIN {$wpdb->term_relationships} ON (engines.ID = {$wpdb->term_relationships}.object_id) ";
			$type_join .= "LEFT JOIN {$wpdb->terms} ON ( {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->terms}.term_id )";
		}

		$search_where   = '';
		$search_queries = array();

		foreach ( $term_groups as $term_group ) {
			// Parse search terms.
			if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $term_group, $matches ) ) {
				$search_terms = $this->get_valid_search_terms( $matches[0] );
				$count        = count( $search_terms );

				// if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence.
				if ( 9 < $count || 0 === $count ) {
					$search_terms = array( $term_group );
				}
			} else {
				$search_terms = array( $term_group );
			}

			$term_group_query = '';
			$searchand        = '';

			foreach ( $search_terms as $search_term ) {
				$like              = '%' . $wpdb->esc_like( $search_term ) . '%';
				$term_group_query .= $wpdb->prepare( " {$searchand} ( engines.post_title LIKE %s )", $like );
				$searchand         = ' AND ';
			}

			if ( $term_group_query ) {
				$search_queries[] = $term_group_query;
			}
		}

		if ( ! empty( $search_queries ) ) {
			$search_where = 'AND (' . implode( ') OR (', $search_queries ) . ')';
		}

		$status_where = " AND engines.post_status IN ('" . implode( "','", $post_statuses ) . "') ";

		if ( $limit ) {
			$limit_query = $wpdb->prepare( ' LIMIT %d ', $limit );
		}

		if ( ! empty( $type ) ) {
			$type_where = "AND {$wpdb->terms}.slug IN ('" . implode( "','", $type ) . "') ";
		}

		$search_results = $wpdb->get_results(
			"SELECT DISTINCT engines.ID as engine_id FROM {$wpdb->posts} engines
			$type_join
			WHERE engines.post_type IN ('" . implode( "','", $post_types ) . "')
			$search_where
			$status_where
			$type_where
			ORDER BY engine_id ASC
			$limit_query
			"
		);

		return wp_parse_id_list( wp_list_pluck( $search_results, 'engine_id' ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Compatibility
	|--------------------------------------------------------------------------
	*/

	/**
	 * Check if the terms are suitable for searching.
	 *
	 * Uses an array of stopwords (terms) that are excluded from the separate
	 * term matching when searching for posts. The list of English stopwords is
	 * the approximate search engines list, and is translatable.
	 *
	 * @param array $terms Terms to check.
	 * @return array Terms that are not stopwords.
	 */
	protected function get_valid_search_terms( $terms ) {

		if ( WC_PRL_Core_Compatibility::is_wc_version_gte( '3.4' ) ) {
			return parent::get_valid_search_terms( $terms );
		}

		$valid_terms = array();
		$stopwords   = $this->get_search_stopwords();

		foreach ( $terms as $term ) {
			// keep before/after spaces when term is for exact match, otherwise trim quotes and spaces.
			if ( preg_match( '/^".+"$/', $term ) ) {
				$term = trim( $term, "\"'" );
			} else {
				$term = trim( $term, "\"' " );
			}

			// Avoid single A-Z and single dashes.
			if ( empty( $term ) || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) ) {
				continue;
			}

			if ( in_array( wc_strtolower( $term ), $stopwords, true ) ) {
				continue;
			}

			$valid_terms[] = $term;
		}

		return $valid_terms;
	}

	/**
	 * Retrieve stopwords used when parsing search terms.
	 *
	 * @return array Stopwords.
	 */
	protected function get_search_stopwords() {

		if ( WC_PRL_Core_Compatibility::is_wc_version_gte( '3.4' ) ) {
			return parent::get_search_stopwords();
		}

		// Translators: This is a comma-separated list of very common words that should be excluded from a search, like a, an, and the. These are usually called "stopwords". You should not simply translate these individual words into your language. Instead, look for and provide commonly accepted stopwords in your language.
		$stopwords = array_map(
			'wc_strtolower', array_map(
				'trim', explode(
					',', _x(
						'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
						'Comma-separated list of search stopwords in your language',
						'woocommerce'
					)
				)
			)
		);

		return apply_filters( 'wp_search_stopwords', $stopwords );
	}
}
