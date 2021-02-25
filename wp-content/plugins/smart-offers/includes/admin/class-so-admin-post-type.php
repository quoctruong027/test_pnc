<?php
/**
 * For registering Smart Offers as new post type in WordPress.
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.0.0
 *
 * @package     smart-offers/includes/admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SO_Admin_Post_Type' ) ) {

	/**
	 * Class for registering Smart Offers post type
	 */
	class SO_Admin_Post_Type {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'init', array( __CLASS__, 'register_post_type' ), 20 );
		}

		/**
		 * Register core post types
		 */
		public static function register_post_type() {

			if ( post_type_exists( 'smart_offers' ) ) {
				return;
			}

			$labels = array(
				'name'               => __( 'Smart Offers', 'smart-offers' ),
				'singular_name'      => __( 'Smart Offer', 'smart-offers' ),
				'add_new'            => __( 'Add New', 'smart-offers' ),
				'add_new_item'       => __( 'Add New', 'smart-offers' ),
				'edit_item'          => __( 'Edit Offer', 'smart-offers' ),
				'new_item'           => __( 'New Offer', 'smart-offers' ),
				'search_items'       => __( 'Search Offers', 'smart-offers' ),
				'not_found'          => __( 'No offers found', 'smart-offers' ),
				'not_found_in_trash' => __( 'No offers found in Trash', 'smart-offers' ),
				'edit'               => __( 'Edit', 'smart-offers' ),
				'parent'             => __( 'Parent offer', 'smart-offers' ),
				'all_items'          => __( 'All Offers', 'smart-offers' ),
				'menu_name'          => __( 'Smart Offers ', 'smart-offers' ),
			);

			$args = array(
				'labels'              => $labels,
				'description'         => '',
				'public'              => true,
				'exclude_from_search' => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_nav_menus'   => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 58,
				'menu_icon'           => 'dashicons-cart',
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => array( 'title', 'editor' ),
				'has_archive'         => true,
				'rewrite'             => array(
					'slug'       => 'smart_offers',
					'with_front' => true,
					'feeds'      => true,
					'pages'      => true,
				),
				'query_var'           => true,
			);

			register_post_type( 'smart_offers', $args );

		}

	}

	return new SO_Admin_Post_Type();
}
