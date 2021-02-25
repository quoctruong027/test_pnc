<?php

/**
 * Class YIKES_Custom_Product_Tabs_Pro_Admin.
 */
class YIKES_Custom_Product_Tabs_Pro_Admin {

	/**
	 * Define hooks.
	 */
	public function __construct() {

		// Exclude some non-public WooCommerce taxonomies.
		add_filter( 'cptpro-available-product-taxonomy-slugs', array( $this, 'exclude_woocommerce_taxonomies' ), 99, 1 );
	}

	/**
	 * Wrapper function for `get_object_taxonomies` function w/ a filter.
	 *
	 * @see https://codex.wordpress.org/Function_Reference/get_object_taxonomies
	 *
	 * @param mixed  $object Name of the post type, or a post object.
	 * @param string $output The type of output to return, either taxonomy 'names' or 'objects'.
	 *
	 * @return array Array of taxonomy slugs assigned to the `$object`.
	 */
	public function cptpro_get_object_taxonomies( $object, $output = 'names' ) {

		// Get all of the taxonomies assigned to products.
		$product_taxonomy_slugs = get_object_taxonomies( $object, $output );

		/**
		 * Filter the taxonomy slugs array.
		 *
		 * These are the taxonomies [whose terms] a tab can be assigned to.
		 *
		 * @param array  | $product_taxonomy_slugs | An array of taxonomy slugs attached to the product CPT
		 *
		 * @return array | $product_taxonomy_slugs | A filtered array of taxonomy slugs
		 */
		$product_taxonomy_slugs = apply_filters( 'cptpro-available-product-taxonomy-slugs', $product_taxonomy_slugs );

		return $product_taxonomy_slugs;
	}

	/**
	 * Wrapper function for `get_terms` function w/ a filter.
	 *
	 * @param array $product_taxonomy_slugs Array of taxonomy slugs to fetch terms for.
	 *
	 * @return array $product_terms Array of WP_Term objects.
	 */
	public function cptpro_get_terms( $product_taxonomy_slugs ) {

		$get_terms_args = array(
			'taxonomy'   => $product_taxonomy_slugs,
			'hide_empty' => false,
		);

		/**
		* Filter the arguments sent to `get_terms` to determine which terms a tab can be assigned to.
		*
		* @param array  | $get_terms_args | An array of default arguments for retrieving taxonomy objects
		*
		* @return array | $get_terms_args | A filtered array of arguments for retrieving taxonomy objects
		*/
		$get_terms_args = apply_filters( 'cptpro-available-product-taxonomy-terms', $get_terms_args );

		$product_terms = get_terms( $get_terms_args );

		return $product_terms;
	}

	/**
	 * By default, exclude the 'product_visibility', 'product_type' and 'product_shipping_class' taxonomies from our available taxonomies list.
	 *
	 * @param array $taxonomy_slugs Array of taxonomy slugs.
	 *
	 * @return array Array of taxonomy slugs, minus 'product_visibility' & 'product_shipping_class' & 'product_type'.
	 */
	public function exclude_woocommerce_taxonomies( $taxonomy_slugs ) {
		return array_filter(
			$taxonomy_slugs,
			function( $slug ) {
				return $slug !== 'product_visibility' && $slug !== 'product_shipping_class' && $slug !== 'product_type';
			}
		);
	}

	/**
	 * Fetch all the Product IDs.
	 *
	 * We're using the DB directly as WP_Query takes too many resources.
	 *
	 * @return array $product_ids An array of product ids.
	 */
	public static function fetch_all_product_ids() {
		global $wpdb;
		$product_ids = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'product' AND post_status = 'publish';" );
		$product_ids = array_map(
			function( $product_id ) {
				return $product_id->ID;
			},
			$product_ids
		);
		return $product_ids;
	}

	/**
	 * Get the number of products this client has.
	 */
	public static function count_product_ids() {
		$count_product_ids = get_transient( 'cptpro_number_of_product_ids' );
		if ( false === $count_product_ids ) {
			$count_product_ids = count( self::fetch_all_product_ids() );
			set_transient( 'cptpro_number_of_product_ids', $count_product_ids, 10 * MINUTE_IN_SECONDS );
		}

		return $count_product_ids;
	}

	/**
	 * For clients that have over 7,000 products, display a warning.
	 */
	public static function display_too_many_products_warning() {

		if ( self::count_product_ids() < 7000 ) {
			return;
		}

		?>
			<div id="cptpro-too-many-products-warning" class="notice notice-error is-dismissible">
				<p> 
					<?php esc_html_e( 'Warning: this plugin can have unpredictable results when applying global tabs to over 7,000 products.', 'custom-product-tabs-pro' ); ?>
				</p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'custom-product-tabs-pro' ); ?></span>
				</button>
			</div>
		<?php
	}

	/**
	 * Add a class to the saved tabs content area.
	 */
	public static function add_class_to_saved_tabs_table() {
		echo 'cptpro-savedtabs-pro-active';
	}
}
