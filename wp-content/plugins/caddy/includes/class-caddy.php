<?php

/**
 * The file that defines the core plugin class
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 * @package    Caddy
 * @subpackage Caddy/includes
 * @author     Tribe Interactive <success@madebytribe.co>
 */
class Caddy {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Caddy_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'CADDY_VERSION' ) ) {
			$this->version = CADDY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'caddy';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Caddy_Loader. Orchestrates the hooks of the plugin.
	 * - Caddy_i18n. Defines internationalization functionality.
	 * - Caddy_Admin. Defines all hooks for the admin area.
	 * - Caddy_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-caddy-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-caddy-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-caddy-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-caddy-public.php';

		$this->loader = new Caddy_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Caddy_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Caddy_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Caddy_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add action to register menu page
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'cc_register_menu_page' );

		// Add action to include tab screen files
		$this->loader->add_action( 'caddy_admin_tab_screen', $plugin_admin, 'cc_include_tab_screen_files' );

		// Add action to load html for upgrade to premium
		$this->loader->add_action( 'cc_upgrade_to_premium', $plugin_admin, 'cc_upgrade_to_premium_html' );

		// Add action to dismiss the welcome notice
		$this->loader->add_action( 'wp_ajax_dismiss_welcome_notice', $plugin_admin, 'cc_dismiss_welcome_notice' );

		// Add action to display addons html
		$this->loader->add_action( 'cc_addons_html', $plugin_admin, 'cc_addons_html_display' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Caddy_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Add action to load cc widget
		$this->loader->add_action( 'wp_footer', $plugin_public, 'cc_load_widget', 1 );

		// Add action for ajaxify cart count
		$this->loader->add_filter( 'woocommerce_add_to_cart_fragments', $plugin_public, 'cc_compass_cart_count_fragments' );

		// Add action for ajaxify cart window total amount
		$this->loader->add_filter( 'woocommerce_add_to_cart_fragments', $plugin_public, 'cc_compass_cart_window_totals_fragments' );

		// Add action for ajaxify update cart count in shortcode
		$this->loader->add_filter( 'woocommerce_add_to_cart_fragments', $plugin_public, 'cc_shortcode_cart_count_fragments' );

		// Add filter to hide shipping rates when free shipping amount matched
		$this->loader->add_filter( 'woocommerce_package_rates', $plugin_public, 'cc_shipping_when_free_is_available' );

		// Add a short-code for saved list
		$this->loader->add_shortcode( 'cc_saved_items', $plugin_public, 'cc_saved_items_shortcode' );

		// Add a short-code for cart items list
		$this->loader->add_shortcode( 'cc_cart_items', $plugin_public, 'cc_cart_items_shortcode' );

		// Add action for adding a product to save for later
		$this->loader->add_action( 'woocommerce_after_add_to_cart_button', $plugin_public, 'cc_add_product_to_sfl', 20 );

		// Add action to hide "added to your cart" message
		$this->loader->add_filter( 'wc_add_to_cart_message_html', $plugin_public, 'cc_empty_wc_add_to_cart_message', 10, 2 );

		// Add action to load the custom css into footer
		$this->loader->add_action( 'wp_footer', $plugin_public, 'cc_load_custom_css', 99 );

		// Add action to load nav tabs items
		$this->loader->add_action( 'caddy_nav_tabs', $plugin_public, 'cc_load_nav_tabs' );

		// Add action to display up-sell message in product added screen
		$this->loader->add_action( 'caddy_up_sell_message', $plugin_public, 'cc_display_up_sell_message' );

		// Add action to display up-sell message in product added screen
		$this->loader->add_action( 'caddy_free_shipping_title_text', $plugin_public, 'cc_free_shipping_bar_html' );

		// Add action to display compass icon
		$this->loader->add_action( 'caddy_compass_icon', $plugin_public, 'cc_display_compass_icon' );

		// Add action to display up-sells slider in product added screen
		$this->loader->add_action( 'caddy_product_upsells_slider', $plugin_public, 'cc_display_product_upsells_slider', 10 );

		// Add action for update window data
		$this->loader->add_action( 'wp_ajax_cc_update_window_data', $plugin_public, 'update_window_data' );
		$this->loader->add_action( 'wp_ajax_nopriv_cc_update_window_data', $plugin_public, 'update_window_data' );

		// Add action for add item to the cart
		$this->loader->add_action( 'wp_ajax_cc_add_to_cart', $plugin_public, 'cc_add_to_cart' );
		$this->loader->add_action( 'wp_ajax_nopriv_cc_add_to_cart', $plugin_public, 'cc_add_to_cart' );

		// Add action for display product added information
		$this->loader->add_action( 'wp_ajax_cc_product_added_info', $plugin_public, 'cc_product_added_info_html' );
		$this->loader->add_action( 'wp_ajax_nopriv_cc_product_added_info', $plugin_public, 'cc_product_added_info_html' );

		// Add action for remove product from the cart
		$this->loader->add_action( 'wp_ajax_cc_remove_item_from_cart', $plugin_public, 'cc_remove_item_from_cart' );
		$this->loader->add_action( 'wp_ajax_nopriv_cc_remove_item_from_cart', $plugin_public, 'cc_remove_item_from_cart' );

		// Add action for update quantity for cart item
		$this->loader->add_action( 'wp_ajax_cc_quantity_update', $plugin_public, 'cc_cart_item_quantity_update' );
		$this->loader->add_action( 'wp_ajax_nopriv_cc_quantity_update', $plugin_public, 'cc_cart_item_quantity_update' );

		// Add action to add cart item into wishlist
		$this->loader->add_action( 'wp_ajax_cc_save_for_later', $plugin_public, 'cc_save_for_later_item' );
		$this->loader->add_action( 'wp_ajax_nopriv_cc_save_for_later', $plugin_public, 'cc_save_for_later_item' );

		// Add action to add item to cart from wishlist
		$this->loader->add_action( 'wp_ajax_cc_move_to_cart', $plugin_public, 'cc_move_to_cart_item' );
		$this->loader->add_action( 'wp_ajax_nopriv_cc_move_to_cart', $plugin_public, 'cc_move_to_cart_item' );

		// Add action to remove item from wishlist
		$this->loader->add_action( 'wp_ajax_cc_remove_item_from_sfl', $plugin_public, 'cc_remove_item_from_sfl' );
		$this->loader->add_action( 'wp_ajax_nopriv_cc_remove_item_from_sfl', $plugin_public, 'cc_remove_item_from_sfl' );

		// Add action to apply coupon code to the cart
		$this->loader->add_action( 'wp_ajax_cc_apply_coupon_to_cart', $plugin_public, 'cc_apply_coupon_to_cart' );
		$this->loader->add_action( 'wp_ajax_nopriv_cc_apply_coupon_to_cart', $plugin_public, 'cc_apply_coupon_to_cart' );

		// Add action to remove coupon code from the cart
		$this->loader->add_action( 'wp_ajax_cc_remove_coupon_code', $plugin_public, 'cc_remove_coupon_code' );
		$this->loader->add_action( 'wp_ajax_nopriv_cc_remove_coupon_code', $plugin_public, 'cc_remove_coupon_code' );

		// Add action to add product to save for later
		$this->loader->add_action( 'wp_ajax_add_product_to_sfl_action', $plugin_public, 'cc_add_product_to_sfl_action' );
		$this->loader->add_action( 'wp_ajax_nopriv_add_product_to_sfl_action', $plugin_public, 'cc_add_product_to_sfl_action' );

		// Add action to load window content when page loads
		$this->loader->add_action( 'wp_ajax_cc_load_window_content', $plugin_public, 'cc_load_window_content' );
		$this->loader->add_action( 'wp_ajax_nopriv_cc_load_window_content', $plugin_public, 'cc_load_window_content' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Caddy_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}
