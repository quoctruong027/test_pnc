<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.madebytribe.com
 * @since      1.0.0
 *
 * @package    Caddy
 * @subpackage Caddy/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Caddy
 * @subpackage Caddy/admin
 * @author     Tribe Interactive <success@madebytribe.co>
 */
class Caddy_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if ( isset( $_GET['page'] ) ) {
			$page_name = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
			if ( 'caddy' == $page_name || 'caddy-addons' === $page_name ) {
				wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/caddy-admin.css', array(), $this->version, 'all' );
				wp_enqueue_style( 'cc-admin-icons', plugin_dir_url( __FILE__ ) . 'css/caddy-admin-icons.css', array(), $this->version, 'all' );
			}
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if ( isset( $_GET['page'] ) ) {
			$page_name = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
			if ( 'caddy' == $page_name ) {
				wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/caddy-admin.js', array( 'jquery' ), $this->version, true );

				// make the ajaxurl var available to the above script
				$params = array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'caddy' ),
				);
				wp_localize_script( $this->plugin_name, 'caddyAjaxObject', $params );
			}
		}
	}

	/**
	 * Register a caddy menu page.
	 */
	public function cc_register_menu_page() {
		add_menu_page(
			__( 'Caddy', 'caddy' ),
			__( 'Caddy', 'caddy' ),
			'manage_options',
			'caddy',
			array( $this, 'caddy_menu_page_callback' ),
			'dashicons-cart',
			65
		);
		add_submenu_page(
			'caddy',
			__( 'Settings', 'caddy' ),
			__( 'Settings', 'caddy' ),
			'manage_options',
			'caddy'
		);
		add_submenu_page(
			'caddy',
			__( 'Add-ons', 'caddy' ),
			__( 'Add-ons', 'caddy' ),
			'manage_options',
			'caddy-addons',
			array( $this, 'caddy_addons_page_callback' )
		);
	}

	/**
	 * Display a caddy menu page.
	 */
	public function caddy_menu_page_callback() {
		require_once( plugin_dir_path( __FILE__ ) . 'partials/caddy-admin-display.php' );
	}

	/**
	 * Display a caddy add-ons submenu page.
	 */
	public function caddy_addons_page_callback() {
		require_once( plugin_dir_path( __FILE__ ) . 'partials/caddy-addons-page.php' );
	}

	/**
	 * Dismiss the welcome notice.
	 */
	public function cc_dismiss_welcome_notice() {

		//Check nonce
		if ( wp_verify_nonce( $_POST['nonce'], 'caddy' ) ) {

			update_option( 'cc_dismiss_welcome_notice', 'yes' );
		}

		wp_die();
	}

	/**
	 * Include tab screen files
	 */
	public function cc_include_tab_screen_files() {

		$caddy_tab = ( ! empty( $_GET['tab'] ) ) ? esc_attr( $_GET['tab'] ) : 'settings';

		if ( 'settings' === $caddy_tab ) {
			include( plugin_dir_path( __FILE__ ) . 'partials/caddy-admin-settings-screen.php' );
		} else if ( 'styles' === $caddy_tab ) {
			include( plugin_dir_path( __FILE__ ) . 'partials/caddy-admin-style-screen.php' );
		}
	}

	/**
	 * Upgrade to premium HTML
	 */
	public function cc_upgrade_to_premium_html() {

		// Display only if premium plugin is not active
		if ( ! class_exists( 'Caddy_Premium' ) ) {
			?>
			<div class="cc-box cc-upgrade">
				<h3><?php echo esc_html( __( 'Upgrade to Premium', 'caddy' ) ); ?></h3>
				<p><?php echo esc_html( __( 'Premium unlocks powerful customization features for Caddy including an in-cart "offers" tab, exclusion rules for recommendations and free shipping meter, color style management, positioning and more.', 'caddy' ) ); ?></p>
				<p><strong><?php echo esc_html( __( 'Use promo code "PREMIUM20" to get 20% off for a limited time.', 'caddy' ) ); ?></strong></p>
				<?php
				echo sprintf(
					'<a href="%1$s" target="_blank" class="button-primary">%2$s</a>',
					esc_url( 'https://usecaddy.com/?utm_source=upgrade-notice&amp;utm_medium=plugin&amp;utm_campaign=plugin-links' ),
					esc_html( __( 'Get Premium', 'caddy' ) )
				);
				?>
			</div>
			<?php
		}
	}

	/**
	 * Display addons tab html
	 */
	public function cc_addons_html_display() {

		$add_on_html_flag = false;
		if ( isset( $_GET['page'] ) && 'caddy-addons' === $_GET['page'] ) {
			$add_on_html_flag = true;
			if ( isset( $_GET['tab'] ) && 'addons' !== $_GET['tab'] ) {
				$add_on_html_flag = false;
			}
		}

		if ( $add_on_html_flag ) {
			$caddy_addons_array = array(
				'caddy-premium' => array(
					'title'       => __( 'Caddy Premium', 'caddy' ),
					'description' => __( 'Premium unlocks powerful customization features for Caddy including an in-cart "offers" tab, exclusion rules for recommendations and free shipping meter, color style management, positioning and more.', 'caddy' ),
					'btn_title'   => __( 'Get Premium', 'caddy' ),
					'btn_link'    => 'https://www.usecaddy.com/?utm_source=caddy-addons&utm_medium=plugin&utm_campaign=addon-links',
					'activated'   => in_array( 'caddy-premium-edd/caddy-premium.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ? 'true' : 'false',
				),
				'ga-event'      => array(
					'title'       => __( 'Google Analytics Tracking', 'caddy' ),
					'description' => __( 'Send Caddy enhanced e-commerce event tracking data to your Google Analytics account using our Google Analytics integration.', 'caddy' ),
					'btn_title'   => __( 'Get Analytics Add-on', 'caddy' ),
					'btn_link'    => 'https://www.usecaddy.com/?utm_source=caddy-addons&utm_medium=plugin&utm_campaign=addon-links',
					'activated'   => in_array( 'google-analytics-tracking/ee-ga-events.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ? 'true' : 'false',
				),
			);
			if ( ! empty( $caddy_addons_array ) ) {
				?>
				<div class="cc-addons-wrap">
					<?php foreach ( $caddy_addons_array as $key => $addon ) { ?>
						<div class="cc-addon">
							<h4 class="addon-title"><?php echo esc_html( $addon['title'] ); ?></h4>
							<p class="addon-description"><?php echo esc_html( $addon['description'] ); ?></p>
							<?php if ( 'false' == $addon['activated'] ) { ?>
								<a class="button addon-button" href="<?php echo $addon['btn_link']; ?>" target="_blank"><?php echo esc_html( $addon['btn_title'] ); ?></a>
							<?php } else { ?>
								<span class="active-addon-btn"><strong><?php esc_html_e( 'Activated', 'caddy' ); ?></strong></span>
							<?php } ?>
						</div>
					<?php } ?>
				</div>
				<?php
			}
		}
	}
}
