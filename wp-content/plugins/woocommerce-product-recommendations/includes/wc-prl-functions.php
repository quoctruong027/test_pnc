<?php
/**
 * Recommendation Lab Functions
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
 * Get engine types.
 *
 * @return array
 */
function wc_prl_get_engine_types() {
	return (array) apply_filters(
		'woocommerce_prl_engine_types', array(
			'cart'    => __( 'Generic', 'woocommerce-product-recommendations' ),
			'archive' => __( 'Product Archive', 'woocommerce-product-recommendations' ),
			'product' => __( 'Product', 'woocommerce-product-recommendations' ),
			'order'   => __( 'Order', 'woocommerce-product-recommendations' ),
		)
	);
}

/**
 * Get engine type label.
 *
 * @param  string $slug
 * @return string
 */
function wc_prl_get_engine_type_label( $slug ) {

	$types = wc_prl_get_engine_types();

	if ( ! in_array( $slug, array_keys( $types ) ) ) {
		return '-';
	}

	return $types[ $slug ];
}

/**
 * Get contextual engine types.
 * Hint: Used to limit the source data from the deployment -- @see WC_PRL_Deployment::get_source_data()
 *
 * @return array
 */
function wc_prl_get_contextual_engine_types() {
	return (array) apply_filters(
		'woocommerce_prl_contextual_engine_types', array(
			'product',
			'archive'
		)
	);
}

/**
 * Get engines by type.
 *
 * @param  string  $type
 * @param  int     $limit
 * @return array
 */
function wc_prl_get_engines_by_type( $type, $limit = 5 ) {

	$type           = wc_clean( $type );
	$limit          = $limit >= -1 ? $limit : 5;

	$data_store    = WC_Data_Store::load( 'prl_engine' );
	$engines       = $data_store->get_engines_by_type( $type, $limit );

	return $engines;
}

/**
 * Clear all transients cache for engine data.
 *
 * @param  int  $post_id
 * @return void
 */
function wc_prl_delete_engine_transients( $post_id = 0 ) {
	// Core transients.
	$transients_to_clear = array(
		// ...
	);

	// Transient names that include an ID.
	$post_transient_names = array(
		// ...
	);

	if ( $post_id > 0 ) {
		foreach ( $post_transient_names as $transient ) {
			$transients_to_clear[] = $transient . $post_id;
		}
	}

	// Delete transients.
	foreach ( $transients_to_clear as $transient ) {
		delete_transient( $transient );
	}

	// Increments the transient version to invalidate cache.
	WC_Cache_Helper::get_transient_version( 'engine', true );

	do_action( 'woocommerce_delete_engine_transients', $post_id );
}

/**
 * Clear all transients cache for prl reports.
 *
 * @since 1.0.5
 *
 * @return void
 */
function wc_prl_invalidate_reports() {
	set_transient( 'wc_prl_dirty_reports', false );
}

/**
 * Clear all transients cache for prl reports.
 *
 * @since 1.0.5
 *
 * @param  string  $type
 * @return bool
 */
function wc_prl_should_update_report( $type = '' ) {

	// Validation.
	if ( empty( $type ) ) {
		return false;
	}

	// Sanity.
	if ( ! in_array( $type, array( 'performance', 'sales', 'events', 'conversions' ) ) ) {
		return false;
	}

	$update = false;
	$dirty  = get_transient( 'wc_prl_dirty_reports' );
	if ( ! is_array( $dirty ) ) {
		$dirty  = array(); // Init.
		$update = true;
	} else if ( ! isset( $dirty[ $type ] ) || false !== $dirty[ $type ] ) {
		$update = true;
	}

	if ( $update ) {
		$dirty[ $type ] = false;
		set_transient( 'wc_prl_dirty_reports', $dirty );
	}

	return $update;
}

/**
 * Get shopping session interval (in seconds).
 *
 * @return int
 */
function wc_prl_get_shopping_session_interval() {

	$session = get_option( 'wc_prl_shopping_session_interval', 12 );

	// Validation for sanity.
	$session = max( $session, 1 );
	$session = min( $session, 48 );

	// Translate hours to seconds.
	$session = absint( $session ) * HOUR_IN_SECONDS;

	/**
	 * 'woocommerce_prl_shopping_session_interval' filter.
	 */
	$session = apply_filters( 'woocommerce_prl_shopping_session_interval', $session );
	$session = max( $session, HOUR_IN_SECONDS );

	return absint( $session );
}

/**
 * Get tracking status.
 *
 * @return bool
 */
function wc_prl_tracking_enabled() {

	/**
	 * 'woocommerce_prl_tracking_enabled' filter.
	 */
	return (bool) apply_filters( 'woocommerce_prl_tracking_enabled', true );
}

/**
 * Get view interest time (in seconds).
 *
 * @return int
 */
function wc_prl_get_view_tracking_threshold() {

	$session = get_option( 'wc_prl_view_tracking_threshold', 2 );

	// Validation for sanity.
	$session = max( $session, 1 );
	$session = min( $session, 12 );

	/**
	 * 'woocommerce_prl_view_tracking_threshold' filter.
	 */
	$session = apply_filters( 'woocommerce_prl_view_tracking_threshold', $session );
	$session = max( $session, 1 );

	return absint( $session );
}

/**
 * Get cache regeneration threshold.
 *
 * @param  WC_PRL_Engine  $engine
 * @return int
 */
function wc_prl_get_cache_regeneration_threshold( $engine ) {

	$threshold = get_option( 'wc_prl_cache_regeneration_threshold', 24 );

	// Validation for sanity.
	$threshold = max( $threshold, 1 );
	$threshold = min( $threshold, 168 );

	$threshold = absint( $threshold ) * HOUR_IN_SECONDS;

	/**
	 * 'woocommerce_prl_cache_regeneration_threshold' filter.
	 */
	$threshold = apply_filters( 'woocommerce_prl_cache_regeneration_threshold', $threshold, $engine );
	$threshold = max( $threshold, HOUR_IN_SECONDS );

	return absint( $threshold );
}

/**
 * Get max views event to store per cookie.
 *
 * @return int
 */
function wc_prl_get_clicks_max_cookie_num() {

	/**
	 * 'woocommerce_prl_clicks_max_cookie_num' filter.
	 */
	return (int) apply_filters( 'woocommerce_prl_clicks_max_cookie_num', 250 );
}

/**
 * Get max views event to store per cookie.
 *
 * @return int
 */
function wc_prl_get_views_max_cookie_num() {

	/**
	 * 'woocommerce_prl_views_max_cookie_num' filter.
	 */
	return (int) apply_filters( 'woocommerce_prl_views_max_cookie_num', 350 );
}


/**
 * Get max recently view products to store per cookie.
 *
 * @since 1.1.0
 *
 * @return int
 */
function wc_prl_get_recently_views_max_cookie_num() {

	/**
	 * 'woocommerce_prl_get_recently_views_max_cookie_num' filter.
	 */
	return (int) apply_filters( 'woocommerce_prl_get_recently_views_max_cookie_num', 100 );
}

/**
 * Whether or not to render deployments using AJAX.
 *
 * @since 1.1.0
 *
 * @param  string  $context
 * @return bool
 */
function wc_prl_render_using_ajax( $context = 'view' ) {

	$use_ajax = 'yes' === get_option( 'wc_prl_render_using_ajax' );

	if ( 'view' === $context ) {
		if ( is_user_logged_in() ) {
			$use_ajax = false;
		} else if ( is_cart() || is_checkout() || is_checkout_pay_page() || is_order_received_page() ) {
			$use_ajax = false;
		}
	}

	/**
	 * 'woocommerce_prl_render_using_ajax' filter.
	 *
	 * @param bool $use_ajax
	 */
	return 'view' === $context ? apply_filters( 'woocommerce_prl_render_using_ajax', $use_ajax ) : $use_ajax;
}

/**
 * Helper function to sort conditions execution order based on complexity.
 *
 * @return bool
 */
function wc_prl_complexity_cmp( $a, $b ) {

	$aa = $a[ 'object' ];
	$bb = $b[ 'object' ];

	return $aa->get_complexity() > $bb->get_complexity();
}

/**
 * Get engine instance.
 *
 * @param  int $id
 * @return WC_PRL_Engine
 */
function wc_prl_get_engine( $id ) {

	if ( ! $id || ! is_numeric( $id ) ) {
		return false;
	}

	return new WC_PRL_Engine( $id );
}

/**
 * Get debug status.
 *
 * @return bool
 */
function wc_prl_debug_enabled() {

	$debug = defined( 'WP_DEBUG' ) ? WP_DEBUG : false;

	/**
	 * 'woocommerce_prl_debug_enabled' filter.
	 */
	return apply_filters( 'woocommerce_prl_debug_enabled', $debug );
}

/**
 * Get engine instance.
 *
 * @param  string  $table_group
 * @return bool
 */
function wc_prl_lookup_tables_enabled( $table_group = 'product' ) {

	$enabled = false;

	if ( 'product' === $table_group ) {
		$enabled = WC_PRL_Core_Compatibility::is_wc_version_gte( '3.6' );
	} elseif ( 'order' === $table_group ) {
		$enabled = WC_PRL_Core_Compatibility::is_wc_version_gte( '4.0' ) && defined( 'WC_ADMIN_APP' );
	}

	return apply_filters( 'woocommerce_prl_lookup_tables_enabled', $enabled );
}

/**
 * Builds terms tree of a flatten terms array.
 *
 * @since  1.2.5
 *
 * @param  array  $terms Array of WP_Term objects.
 * @param  int    $parent_id
 * @return array
 */
function wc_prl_build_taxonomy_tree( $terms, $parent_id = 0 ) {

	if ( empty( $terms ) ) {
		return array();
	}

	// Build.
	$tree = array();
	foreach ( $terms as $index => $term ) {
		if ( $term->parent === $parent_id && ! isset( $tree[ $term->term_id ] ) ) {
			$tree[ $term->term_id ]           = $term;
			$tree[ $term->term_id ]->children = wc_prl_build_taxonomy_tree( $terms, $term->term_id );
		}
	}

	return $tree;
}

/**
 * Prints <option/> elements for a given terms tree.
 *
 * @since  1.2.5
 *
 * @param  array  $terms Array of WP_Term objects.
 * @param  array  $selected_ids
 * @param  string $prefix_html
 * @param  array  $args
 * @return void
 */
function wc_prl_print_taxonomy_tree_options( $terms, $selected_ids = array(), $args = array() ) {

	$args = wp_parse_args( $args, array(
		'prefix_html'   => '',
		'key'           => 'id',
		'seperator'     => _x( '%1$s&nbsp;&gt;&nbsp;%2$s', 'term separator', 'woocommerce-product-recommendations' ),
		'shorten_text'  => true,
		'shorten_level' => 3,
		'term_path'     => array()
	) );

	$key       = 'slug' === $args[ 'key' ] ? 'slug' : 'term_id';
	$term_path = $args[ 'term_path' ];

	foreach ( $terms as $term ) {

		$term_path[] = $term->name;
		$option_text = $term->name;

		if ( ! empty( $args[ 'prefix_html' ] ) ) {
			$option_text = sprintf( $args[ 'seperator' ], $args[ 'prefix_html' ], $option_text );
		}

		// Print option element.
		echo '<option value="' . $term->$key . '" ' . selected( in_array( $term->$key, $selected_ids ), true, false ) . '>';

		if ( $args[ 'shorten_text' ] && count( $term_path ) > $args[ 'shorten_level' ] ) {
			echo sprintf( _x( '%1$s&nbsp;&gt;&nbsp;&hellip;&nbsp;&gt;&nbsp;%2$s', 'many terms separator', 'woocommerce-product-recommendations' ), $term_path[ 0 ], $term_path[ count( $term_path ) - 1 ] );
		} else {
			echo $option_text;
		}

		echo '</option>';

		// Recursive call to print children.
		if ( ! empty( $term->children ) ) {

			// Reset `prefix_html` argument to recursive mode.
			$reset_args                  = $args;
			$reset_args[ 'prefix_html' ] = $option_text;
			$reset_args[ 'term_path' ]   = $term_path;

			wc_prl_print_taxonomy_tree_options( $term->children, $selected_ids, $reset_args );
		}

		$term_path = $args[ 'term_path' ];
	}
}

/**
 * Get a key-value list of all global attribute taxonomies.
 *
 * @since 1.3.0
 *
 * @return array
 */
function wc_prl_get_attribute_taxonomies() {
	$attributes = wc_get_attribute_taxonomies();
	$formatted  = array();
	foreach ( $attributes as $attribute ) {
		$formatted[ $attribute->attribute_name ] = $attribute->attribute_label;
	}

	return $formatted;
}
