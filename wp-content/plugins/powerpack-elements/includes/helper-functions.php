<?php

/**
 * Wrapper for including files
 *
 * @since 2.0.3
 */
function pp_plugin_include( $file ) {

	$path = pp_plugin_get_path( $file );

	if ( file_exists( $path ) ) {
		include_once( $path );
	}
}

/**
 * Returns the path to a file relative to our plugin
 *
 * @since 2.0.3
 */
function pp_plugin_get_path( $path ) {

	return POWERPACK_ELEMENTS_PATH . $path;

}

if ( ! function_exists( 'pp_get_page_templates' ) ) {
	/**
	 * Get page templates
	 *
	 * @param  string $type type of page template.
	 * @return array list of page templates
	 */
	function pp_get_page_templates( $type = '' ) {
		$args = array(
			'post_type'      => 'elementor_library',
			'posts_per_page' => -1,
		);

		if ( $type ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'elementor_library_type',
					'field'    => 'slug',
					'terms'    => $type,
				),
			);
		}

		$page_templates = get_posts( $args );

		$options = array();

		if ( ! empty( $page_templates ) && ! is_wp_error( $page_templates ) ) {
			foreach ( $page_templates as $post ) {
				$options[ $post->ID ] = $post->post_title;
			}
		}
		return $options;
	}
}

if ( ! function_exists( 'is_pp_magic_wand' ) ) {
	/**
	 * Is Magic Wand active
	 *
	 * @return bool
	 */
	function is_pp_magic_wand() {
		if ( file_exists( POWERPACK_ELEMENTS_PATH . 'classes/class-pp-magic-wand.php' ) ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'is_pp_woocommerce' ) ) {
	/**
	 * Is WooCommerce active
	 *
	 * @return bool
	 */
	function is_pp_woocommerce() {
		if ( class_exists( 'WooCommerce' ) && file_exists( POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/module.php' ) ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! function_exists( 'is_pp_woo_builder' ) ) {
	/**
	 * Is WooCommerce active
	 *
	 * @return bool
	 */
	function is_pp_woo_builder() {
		if ( ( class_exists( 'WooCommerce' ) || is_plugin_active( 'woocommerce/woocommerce.php' ) ) && file_exists( POWERPACK_ELEMENTS_PATH . 'classes/class-pp-woo-builder.php' ) ) {
			return true;
		}

		return false;
	}
}

/**
 * Get PowerPack Modules
 *
 * @return $modules
 */
function pp_get_modules() {
	$modules = array(
		'pp-link-effects'         => __( 'Link Effects', 'powerpack' ),
		'pp-divider'              => __( 'Divider', 'powerpack' ),
		'pp-recipe'               => __( 'Recipe', 'powerpack' ),
		'pp-info-box'             => __( 'Info Box', 'powerpack' ),
		'pp-info-box-carousel'    => __( 'Info Box Carousel', 'powerpack' ),
		'pp-info-list'            => __( 'Info List', 'powerpack' ),
		'pp-info-table'           => __( 'Info Table', 'powerpack' ),
		'pp-tiled-posts'          => __( 'Tiled Posts', 'powerpack' ),
		'pp-posts'                => __( 'Advanced Posts', 'powerpack' ),
		'pp-pricing-table'        => __( 'Pricing Table', 'powerpack' ),
		'pp-price-menu'           => __( 'Price Menu', 'powerpack' ),
		'pp-business-hours'       => __( 'Businsess Hours', 'powerpack' ),
		'pp-team-member'          => __( 'Team Member', 'powerpack' ),
		'pp-team-member-carousel' => __( 'Team Member Carousel', 'powerpack' ),
		'pp-counter'              => __( 'Counter', 'powerpack' ),
		'pp-hotspots'             => __( 'Image Hotspots', 'powerpack' ),
		'pp-icon-list'            => __( 'Icon List', 'powerpack' ),
		'pp-dual-heading'         => __( 'Dual Heading', 'powerpack' ),
		'pp-promo-box'            => __( 'Promo Box', 'powerpack' ),
		'pp-logo-carousel'        => __( 'Logo Carousel', 'powerpack' ),
		'pp-logo-grid'            => __( 'Logo Grid', 'powerpack' ),
		'pp-modal-popup'          => __( 'Modal Popup', 'powerpack' ),
		'pp-onepage-nav'          => __( 'One Page Navigation', 'powerpack' ),
		'pp-table'                => __( 'Table', 'powerpack' ),
		'pp-toggle'               => __( 'Toggle', 'powerpack' ),
		'pp-image-comparison'     => __( 'Image Comparison', 'powerpack' ),
		'pp-instafeed'            => __( 'Instagram Feed', 'powerpack' ),
		'pp-google-maps'          => __( 'Google Maps', 'powerpack' ),
		'pp-review-box'           => __( 'Review Box', 'powerpack' ),
		'pp-countdown'            => __( 'Countdown', 'powerpack' ),
		'pp-buttons'              => __( 'Buttons', 'powerpack' ),
		'pp-advanced-tabs'        => __( 'Advanced Tabs', 'powerpack' ),
		'pp-image-gallery'        => __( 'Image Gallery', 'powerpack' ),
		'pp-image-slider'         => __( 'Image Slider', 'powerpack' ),
		'pp-advanced-menu'        => __( 'Advanced Menu', 'powerpack' ),
		'pp-offcanvas-content'    => __( 'Offcanvas Content', 'powerpack' ),
		'pp-timeline'             => __( 'Timeline', 'powerpack' ),
		'pp-showcase'             => __( 'Showcase', 'powerpack' ),
		'pp-card-slider'          => __( 'Card Slider', 'powerpack' ),
		'pp-flipbox'              => __( 'Flip Box', 'powerpack' ),
		'pp-image-accordion'      => __( 'Image Accordion', 'powerpack' ),
		'pp-advanced-accordion'   => __( 'Advanced Accordion', 'powerpack' ),
		'pp-breadcrumbs'          => __( 'Breadcrumbs', 'powerpack' ),
		'pp-content-ticker'       => __( 'Content Ticker', 'powerpack' ),
		'pp-magazine-slider'      => __( 'Magazine Slider', 'powerpack' ),
		'pp-video'                => __( 'Video', 'powerpack' ),
		'pp-video-gallery'        => __( 'Video Gallery', 'powerpack' ),
		'pp-testimonials'         => __( 'Testimonials', 'powerpack' ),
		'pp-scroll-image'         => __( 'Scroll Image', 'powerpack' ),
		'pp-album'                => __( 'Album', 'powerpack' ),
		'pp-twitter-buttons'      => __( 'Twitter Buttons', 'powerpack' ),
		'pp-twitter-grid'         => __( 'Twitter Grid', 'powerpack' ),
		'pp-twitter-timeline'     => __( 'Twitter Timeline', 'powerpack' ),
		'pp-twitter-tweet'        => __( 'Twitter Tweet', 'powerpack' ),
		'pp-tabbed-gallery'       => __( 'Tabbed Gallery', 'powerpack' ),
		'pp-devices'              => __( 'Devices', 'powerpack' ),
		'pp-fancy-heading'        => __( 'Fancy Heading', 'powerpack' ),
		'pp-faq'                  => __( 'FAQ', 'powerpack' ),
		'pp-how-to'               => __( 'How To', 'powerpack' ),
		'pp-coupons'              => __( 'Coupons', 'powerpack' ),
		'pp-categories'           => __( 'Categories', 'powerpack' ),
		'pp-sitemap'              => __( 'Sitemap', 'powerpack' ),
		'pp-table-of-contents'    => __( 'Table of Contents', 'powerpack' ),
		'pp-login-form'           => __( 'Login Form', 'powerpack' ),
		'pp-registration-form'    => __( 'Registration Form', 'powerpack' ),
	);

	// Contact Form 7.
	if ( function_exists( 'wpcf7' ) ) {
		$modules['pp-contact-form-7'] = __( 'Contact Form 7', 'powerpack' );
	}

	// Gravity Forms.
	if ( class_exists( 'GFCommon' ) ) {
		$modules['pp-gravity-forms'] = __( 'Gravity Forms', 'powerpack' );
	}

	// Ninja Forms.
	if ( class_exists( 'Ninja_Forms' ) ) {
		$modules['pp-ninja-forms'] = __( 'Ninja Forms', 'powerpack' );
	}

	// Caldera Forms.
	if ( class_exists( 'Caldera_Forms' ) ) {
		$modules['pp-caldera-forms'] = __( 'Caldera Forms', 'powerpack' );
	}

	// WPForms.
	if ( class_exists( 'WPForms_Pro' ) || class_exists( 'WPForms_Lite' ) ) {
		$modules['pp-wpforms'] = __( 'WPForms', 'powerpack' );
	}

	// Fluent Forms.
	if ( function_exists( 'wpFluentForm' ) ) {
		$modules['pp-fluent-forms'] = __( 'Fluent Forms', 'powerpack' );
	}

	// Formidable Forms.
	if ( class_exists( 'FrmForm' ) ) {
		$modules['pp-formidable-forms'] = __( 'Formidable Forms', 'powerpack' );
	}

	// Check whether WooCommerce plugin is installed and activated.
	if ( is_pp_woocommerce() ) {
		$modules['pp-woo-add-to-cart']    = __( 'Woo - Add To Cart', 'powerpack' );
		$modules['pp-woo-categories']     = __( 'Woo - Categories', 'powerpack' );
		$modules['pp-woo-cart']           = __( 'Woo - Cart', 'powerpack' );
		$modules['pp-woo-offcanvas-cart'] = __( 'Woo - Offcanvas Cart', 'powerpack' );
		$modules['pp-woo-checkout']       = __( 'Woo - Checkout', 'powerpack' );
		$modules['pp-woo-mini-cart']      = __( 'Woo - Mini Cart', 'powerpack' );
		$modules['pp-woo-products']       = __( 'Woo - Products', 'powerpack' );
		$modules['pp-woo-my-account']     = __( 'Woo - My Account', 'powerpack' );
		if ( get_option( 'pp_woo_builder_enable' ) ) {
			$modules['pp-woo-product-tabs']   = __( 'Woo - Product Tabs', 'powerpack' );
			$modules['pp-woo-product-title']  = __( 'Woo - Product Title', 'powerpack' );
			$modules['pp-woo-product-meta']   = __( 'Woo - Product Meta', 'powerpack' );
			$modules['pp-woo-product-price']  = __( 'Woo - Product Price', 'powerpack' );
			$modules['pp-woo-product-rating'] = __( 'Woo - Product Rating', 'powerpack' );
			$modules['pp-woo-product-stock']  = __( 'Woo - Product Stock', 'powerpack' );
			$modules['pp-woo-product-short-description'] = __( 'Woo - Product Short Description', 'powerpack' );
			$modules['pp-woo-product-content']           = __( 'Woo - Product Content', 'powerpack' );
			$modules['pp-woo-product-images']            = __( 'Woo - Product Images', 'powerpack' );
			$modules['pp-woo-product-reviews']           = __( 'Woo - Product Reviews', 'powerpack' );
			$modules['pp-woo-product-upsell']            = __( 'Woo - Product Upsell', 'powerpack' );
			$modules['pp-woo-add-to-cart-notification']  = __( 'Woo - Add to Cart Notification', 'powerpack' );
			$modules['pp-woo-archive-description']       = __( 'Woo - Archive Description', 'powerpack' );
		}
	}

	ksort( $modules );

	return $modules;
}

if ( ! function_exists( 'is_pp_white_label' ) ) {
	function is_pp_white_label() {
		if ( file_exists(POWERPACK_ELEMENTS_PATH . 'includes/admin/admin-settings-wl.php') ) {
			return true;
		}
		
		return false;
	}
}

if ( ! function_exists( 'is_pp_woocommerce' ) ) {
	function is_pp_woocommerce() {
		if ( ( class_exists( 'WooCommerce' ) || is_plugin_active( 'woocommerce/woocommerce.php' ) ) && file_exists(POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/module.php') ) {
			return true;
		}
		
		return false;
	}

	ksort( $modules );

	return $modules;
}

function pp_get_thumbnail_taxonomies() {
	$taxonomies    = array();
	$taxonomy_list = array();

	$post_types = \PowerpackElements\Classes\PP_Posts_Helper::get_post_types();

	foreach ( $post_types as $slug => $type ) {
		$taxonomies = \PowerpackElements\Classes\PP_Posts_Helper::get_post_taxonomies( $slug );
		foreach ( (array) $taxonomies as $taxonomy ) {
			$taxonomy_list[ $taxonomy->name ] = $taxonomy->label;
		}
	}

	return $taxonomy_list;
}

function pp_get_extensions() {
	$extensions = array(
		'pp-display-conditions' => __( 'Display Conditions', 'powerpack' ),
		'pp-background-effects' => __( 'Background Effects', 'powerpack' ),
	);

	$extensions = apply_filters( 'pp_elements_extensions', $extensions );

	return $extensions;
}

function pp_get_enabled_modules() {
	$enabled_modules = get_option( 'pp_elementor_modules' );

	if ( is_array( $enabled_modules ) ) {
		return $enabled_modules;
	}

	if ( 'disabled' == $enabled_modules ) {
		return $enabled_modules;
	}

	return pp_get_modules();
}

function pp_get_enabled_extensions() {
	$enabled_extensions = get_option( 'pp_elementor_extensions' );

	if ( is_array( $enabled_extensions ) ) {
		return $enabled_extensions;
	}

	if ( 'disabled' == $enabled_extensions ) {
		return $enabled_extensions;
	}

	return pp_get_extensions();
}

function pp_get_enabled_taxonomies() {
	$enabled_taxonomies = get_option( 'pp_elementor_taxonomy_thumbnail_taxonomies' );

	if ( is_array( $enabled_taxonomies ) ) {
		return $enabled_taxonomies;
	}

	if ( 'disabled' == $enabled_taxonomies ) {
		return $enabled_taxonomies;
	}

	return pp_get_thumbnail_taxonomies();
}

// Get templates

function pp_get_saved_templates( $templates = array() ) {

	if ( empty( $templates ) ) {
		return array();
	}

	$options = array();

	foreach ( $templates as $template ) {
		$options[ $template['template_id'] ] = $template['title'];
	}

	return $options;
}

// Query functions.

/**
 * Fetches available post types
 *
 * @since 2.0.0
 */
function pp_get_public_post_types_options( $singular = false, $any = false, $args = array() ) {
	$defaults = array(
		'show_in_nav_menus' => true,
	);

	$post_types     = array();
	$post_type_args = wp_parse_args( $args, $defaults );

	if ( $any ) {
		$post_types['any'] = __( 'Any', 'powerpack' );
	}

	if ( ! function_exists( 'get_post_types' ) ) {
		return $post_types;
	}

	$_post_types = get_post_types( $post_type_args, 'objects' );

	foreach ( $_post_types as $post_type => $object ) {
		$post_types[ $post_type ] = $singular ? $object->labels->singular_name : $object->label;
	}

	return $post_types;
}

/**
 * Get Taxonomies Options
 *
 * Fetches available taxonomies
 *
 * @since 2.0.0
 */
function pp_get_taxonomies_options( $post_type = false ) {

	$options = array();

	if ( ! $post_type ) {
		// Get all available taxonomies
		$taxonomies = get_taxonomies(
			array(
				'show_in_nav_menus' => true,
			),
			'objects'
		);
	} else {
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
	}

	foreach ( $taxonomies as $taxonomy ) {
		if ( ! $taxonomy->publicly_queryable ) {
			continue;
		}

		$options[ $taxonomy->name ] = $taxonomy->label;
	}

	if ( empty( $options ) ) {
		$options[0] = __( 'No taxonomies found', 'powerpack' );
		return $options;
	}

	return $options;
}

/**
 * Get Taxonomies Labels
 *
 * Fetches labels for given taxonomy
 *
 * @since 2.1.0
 */
function pp_get_taxonomy_labels( $taxonomy = '' ) {

	if ( ! $taxonomy || '' === $taxonomy ) {
		return false;
	}

	$labels          = false;
	$taxonomy_object = get_taxonomy( $taxonomy );

	if ( $taxonomy_object && is_object( $taxonomy_object ) ) {
		$labels = $taxonomy_object->labels;
	}

	return $labels;
}

/**
 * Get Terms Options
 *
 * Retrieve the terms options array for a control
 *
 * @since  1.6.0
 * @param  taxonomy     The taxonomy for the terms
 * @param  key|string   The key to use when building the options. Can be 'slug' or 'id'
 * @param  all|bool     The string to use for the first option. Can be false to disable. Default: true
 * @return array
 */
function pp_get_terms_options( $taxonomy, $key = 'slug', $all = true ) {

	if ( false !== $all ) {
		$all     = ( true === $all ) ? __( 'All', 'powerpack' ) : $all;
		$options = array( '' => $all );
	}

	$terms = get_terms(
		array(
			'taxonomy' => $taxonomy,
		)
	);

	if ( empty( $terms ) ) {
		$options[''] = sprintf( __( 'No terms found', 'powerpack' ), $taxonomy );
		return $options;
	}

	foreach ( $terms as $term ) {
		$term_key             = ( 'id' === $key ) ? $term->term_id : $term->slug;
		$options[ $term_key ] = $term->name;
	}

	return $options;
}

/**
 * Get Terms
 *
 * Retrieve a list of terms for specific taxonomies
 *
 * @since  1.6.0
 * @return array
 */
function pp_get_terms( $taxonomies = array() ) {
	$_terms = array();

	if ( empty( $taxonomies ) ) {
		return false;
	}

	if ( is_array( $taxonomies ) ) {
		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_the_terms( get_the_ID(), $taxonomy );

			if ( empty( $terms ) ) {
				continue;
			}

			foreach ( $terms as $term ) {
				$_terms[] = $term; }
		}
	} else {
		$_terms = get_the_terms( get_the_ID(), $taxonomies );
	}

	if ( empty( $_terms ) || 0 === count( $_terms ) ) {
		return false;
	}

	return $_terms;

}

/**
 * Fetches available pages
 *
 * @since 2.0.0
 */
function pp_get_pages_options() {

	$options = array();

	$pages = get_pages(
		array(
			'hierarchical' => false,
		)
	);

	if ( empty( $pages ) ) {
		$options[''] = __( 'No pages found', 'powerpack' );
		return $options;
	}

	foreach ( $pages as $page ) {
		$options[ $page->ID ] = $page->post_title;
	}

	return $options;
}

/**
 * Fetches available users
 *
 * @since 2.0.0
 */
function pp_get_users_options() {

	$options = array();

	$users = get_users(
		array(
			'fields' => array( 'ID', 'display_name' ),
		)
	);

	if ( empty( $users ) ) {
		$options[''] = __( 'No users found', 'powerpack' );
		return $options;
	}

	foreach ( $users as $user ) {
		$options[ $user->ID ] = $user->display_name;
	}

	return $options;
}

/**
 * Get category with highest number of parents
 * from a given list
 *
 * @since 2.0.0
 */
function pp_get_most_parents_category( $categories = array() ) {

	$counted_cats = array();

	if ( ! is_array( $categories ) ) {
		return $categories;
	}

	foreach ( $categories as $category ) {
		$category_parents                   = get_category_parents( $category->term_id, false, ',' );
		$category_parents                   = explode( ',', $category_parents );
		$counted_cats[ $category->term_id ] = count( $category_parents );
	}

	arsort( $counted_cats );
	reset( $counted_cats );

	return key( $counted_cats );
}

/**
 * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
 * placed under a 'children' member of their parent term.
 *
 * @since 2.2.6
 *
 * @param Array   $cats     taxonomy term objects to sort
 * @param Array   $into     result array to put them in
 * @param integer $parentId the current parent ID to put them in
 */
function pp_sort_terms_hierarchicaly( array &$cats, array &$into, $parentId = 0 ) {
	foreach ( $cats as $i => $cat ) {
		if ( $cat->parent == $parentId ) {
			$into[ $cat->term_id ] = $cat;
			unset( $cats[ $i ] );
		}
	}

	foreach ( $into as $topCat ) {
		$topCat->children = array();
		pp_sort_terms_hierarchicaly( $cats, $topCat->children, $topCat->term_id );
	}
}

/**
 * Constrain search query for posts by searching only in post titles
 *
 * @since 2.2.0
 */
function pp_posts_where_by_title_name( $where, &$wp_query ) {
	global $wpdb;
	if ( $s = $wp_query->get( 'search_title_name' ) ) {
		$where .= ' AND (' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $s ) ) . '%\' OR ' . $wpdb->posts . '.post_name LIKE \'%' . esc_sql( $wpdb->esc_like( $s ) ) . '%\')';
	}
	return $where;
}

/**
 * Compare conditions.
 *
 * Checks two values against an operator
 *
 * @since 2.0.0
 *
 * @param mixed  $left_value  First value to compare.
 * @param mixed  $right_value Second value to compare.
 * @param string $operator    Comparison operator.
 *
 * @return bool
 */
function pp_value_compare( $left_value, $right_value, $operator ) {
	switch ( $operator ) {
		case 'is':
			return $left_value == $right_value;
		case 'not':
			return $left_value != $right_value;
		default:
			return $left_value === $right_value;
	}
}

/**
 * Checks if Header is enabled from PowerPack settings.
 *
 * @since  1.4.15
 * @return bool True if header is enabled. False if header is not enabled
 */
function pp_header_enabled() {
	$header_id = get_option( 'pp_header_footer_template_header' );
	$status    = false;

	if ( '' !== $header_id ) {
		$status = true;
	}

	return apply_filters( 'pp_header_enabled', $status );
}

/**
 * Checks if Footer is enabled from PowerPack settings.
 *
 * @since  1.4.15
 * @return bool True if header is enabled. False if header is not enabled.
 */
function pp_footer_enabled() {
	$footer_id = get_option( 'pp_header_footer_template_footer' );
	$status    = false;

	if ( '' !== $footer_id ) {
		$status = true;
	}

	return apply_filters( 'pp_footer_enabled', $status );
}

/**
 * Elementor
 *
 * Retrieves the elementor plugin instance
 *
 * @since  2.1.0
 * @return \Elementor\Plugin|$instace
 */
function pp_get_elementor() {
	return \Elementor\Plugin::$instance;
}
