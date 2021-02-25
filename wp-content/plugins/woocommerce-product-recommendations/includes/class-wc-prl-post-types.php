<?php
/**
 * WC_PRL_Post_Types class
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
 * Product Recommendations Post Types Class.
 *
 * Registers custom post types and taxonomies.
 *
 * @class    WC_PRL_Post_Types
 * @version  1.0.0
 */
class WC_PRL_Post_Types {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 6 );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 6 );
		add_action( 'admin_bar_menu', array( __CLASS__, 'prl_admin_bar_menu' ), 9999 );
	}

	/**
	 * Remove "New Engine" item from Admin bar
	 */
	public static function prl_admin_bar_menu( $admin_bar ) {
		$admin_bar->remove_menu( 'new-prl_engine' );
	}


	/**
	 * Register taxonomies.
	 */
	public static function register_taxonomies() {

		if ( ! is_blog_installed() ) {
			return;
		}

		if ( taxonomy_exists( 'prl_engine_type' ) ) {
			return;
		}

		register_taxonomy(
			'prl_engine_type',
			array( 'prl_engine' ),
			array(
				'hierarchical'      => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'query_var'         => is_admin(),
				'rewrite'           => false,
				'public'            => false,
			)
		);
	}

	/**
	 * Register post types.
	 */
	public static function register_post_types() {

		if ( ! is_blog_installed() || post_type_exists( 'prl_engine' ) ) {
			return;
		}

		register_post_type(
			'prl_engine',
			array(
				'labels'              => array(
					'name'                  => __( 'Engines', 'woocommerce-product-recommendations' ),
					'singular_name'         => __( 'Engine', 'woocommerce-product-recommendations' ),
					'all_items'             => __( 'Engines', 'woocommerce-product-recommendations' ),
					'menu_name'             => _x( 'Engines', 'Admin menu name', 'woocommerce-product-recommendations' ),
					'add_new'               => __( 'Create new', 'woocommerce-product-recommendations' ),
					'add_new_item'          => __( 'Create new engine', 'woocommerce-product-recommendations' ),
					'edit'                  => __( 'Edit', 'woocommerce-product-recommendations' ),
					'edit_item'             => __( 'Edit engine', 'woocommerce-product-recommendations' ),
					'new_item'              => __( 'New engine', 'woocommerce-product-recommendations' ),
					'view_item'             => __( 'View engine', 'woocommerce-product-recommendations' ),
					'view_items'            => __( 'View engines', 'woocommerce-product-recommendations' ),
					'search_items'          => __( 'Search engines', 'woocommerce-product-recommendations' ),
					'not_found'             => self::no_engines_boarding(),
					'not_found_in_trash'    => __( 'No engines found in Trash', 'woocommerce-product-recommendations' ),
					'parent'                => __( 'Parent engine', 'woocommerce-product-recommendations' ),
					'featured_image'        => __( 'Engine image', 'woocommerce-product-recommendations' ),
					'set_featured_image'    => __( 'Set engine image', 'woocommerce-product-recommendations' ),
					'filter_items_list'     => __( 'Filter engines', 'woocommerce-product-recommendations' ),
					'items_list_navigation' => __( 'Engines navigation', 'woocommerce-product-recommendations' ),
					'items_list'            => __( 'Engines list', 'woocommerce-product-recommendations' ),
				),
				'description'         => __( 'Create and deploy Engines to display product recommendations on your store.', 'woocommerce-product-recommendations' ),
				'public'              => false,
				'show_ui'             => true,
				'capability_type'     => 'product',
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
				'show_in_menu'        => false,
				// 'show_in_menu'        => current_user_can( 'manage_woocommerce' ) ? 'woocommerce' : false,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => true,
				'show_in_rest'        => false
			)
		);
	}

	/**
	 * Boarding HTML when no engines.
	 */
	public static function no_engines_boarding() {
		ob_start();
		?><div class="prl-engines-empty-state">
			<p class="main">
				<?php esc_html_e( 'Create an Engine', 'woocommerce-product-recommendations' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Ready to offer some product recommendations?', 'woocommerce-product-recommendations' ); ?>
				<br/>
				<?php esc_html_e( 'Start by creating an Engine. Then, deploy it to one of the available Locations.', 'woocommerce-product-recommendations' ); ?>
			</p>
			<a class="button sw-button-primary sw-button-primary--woo" id="sw-button-primary" href="<?php echo admin_url( 'post-new.php?post_type=prl_engine' ); ?>"><?php esc_html_e( 'Create engine', 'woocommerce-product-recommendations' ); ?></a>
		</div><?php
		$message = ob_get_clean();
		return $message;
	}
}

WC_PRL_Post_Types::init();
