<?php

/**
 * Class YIKES_Custom_Product_Tabs_Pro_Search.
 */
class YIKES_Custom_Product_Tabs_Pro_Search {

	/**
	 * Flag indicating whether adding custom product tabs to WordPress' search is enabled.
	 *
	 * @var bool $search_wordpress
	 */
	public $search_wordpress;

	/**
	 * Flag indicating whether adding custom product tabs to WooCommerce's search is enabled.
	 *
	 * @var bool $search_woo
	 */
	public $search_woo;

	/**
	 * Define hooks.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'maybe_search' ), 10 );
	}

	/**
	 * Check our settings to decide whether we need to add our search-customizing filters
	 */
	public function maybe_search() {

		$cptpro_settings = get_option( 'cptpro_settings' );

		$this->search_wordpress = isset( $cptpro_settings['search_wordpress'] ) ? $cptpro_settings['search_wordpress'] : false;
		$this->search_woo       = isset( $cptpro_settings['search_woo'] ) ? $cptpro_settings['search_woo'] : false;

		if ( $this->search_wordpress === false && $this->search_woo === false ) {
			return;
		}

		add_filter( 'posts_join', array( $this, 'search_join' ), 10, 1 );
		add_filter( 'posts_where', array( $this, 'search_where' ), 10, 1 );
		add_filter( 'posts_distinct', array( $this, 'search_distinct' ), 10, 1 );
	}

	/**
	 * Compare our settings to the current search to check if we should add the tabs to the search
	 */
	private function do_search() {

		// If we're only searching WordPress but our post_type is products, do not add our logic to the search.
		if ( $this->search_wordpress === true && $this->search_woo === false && get_query_var( 'post_type' ) === 'product' ) {
			return false;
		}

		// If we're only searching woo products but our query var isn't products, do not add our logic to the search.
		if ( $this->search_wordpress === false && $this->search_woo === true && get_query_var( 'post_type' ) !== 'product' ) {
			return false;
		}

		return true;
	}

	/**
	 * Add a join statement to join post meta table to the posts table.
	 *
	 * Code inspired by https://adambalee.com/search-wordpress-by-custom-fields-without-a-plugin/. Thank you!
	 *
	 * @param string $join The SQL `JOIN` statement.
	 *
	 * @return string $join
	 */
	public function search_join( $join ) {
		global $wpdb;

		if ( $this->do_search() === false ) {
			return $join;
		}

		if ( ! is_admin() && is_search() ) {

			$join .= ' LEFT OUTER JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id AND ' . $wpdb->postmeta . '.meta_key = "yikes_woo_products_tabs"';
		}

		return $join;
	}

	/**
	 * Customize the WHERE statement to include post meta fields
	 *
	 * Code inspired by https://adambalee.com/search-wordpress-by-custom-fields-without-a-plugin/. Thank you!
	 *
	 * @param string $where The SQL `WHERE` statement.
	 *
	 * @return string $where
	 */
	public function search_where( $where ) {
		global $wpdb;

		if ( $this->do_search() === false ) {
			return $where;
		}

		if ( ! is_admin() && is_search() ) {

			$where = preg_replace(
				'/\(\s*' . $wpdb->posts . '.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/',
				'(' . $wpdb->posts . '.post_title LIKE $1) OR (' . $wpdb->postmeta . '.meta_value LIKE $1)',
				$where
			);
		}
		return $where;
	}

	/**
	 * Add distinct call to search query.
	 *
	 * Code inspired by https://adambalee.com/search-wordpress-by-custom-fields-without-a-plugin/. Thank you!
	 *
	 * @param string $distinct The SQL `DISTINCT` statement.
	 *
	 * @return string $distinct
	 */
	public function search_distinct( $distinct ) {
		global $wpdb;

		if ( $this->do_search() === false ) {
			return $distinct;
		}

		if ( ! is_admin() && is_search() ) {
			return 'DISTINCT';
		}

		return $distinct;
	}
}

new YIKES_Custom_Product_Tabs_Pro_Search();
