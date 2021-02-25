<?php

/**
 * @link              https://www.madebytribe.com
 * @since             1.0.0
 * @package           Caddy
 *
 * @wordpress-plugin
 * Plugin Name:       Caddy
 * Plugin URI:        https://usecaddy.com
 * Description:       Enhanced mini-cart drawer for WooCommerce with up-selling, free shipping meter and save for later list.
 * Version:           1.5
 * Author:            Tribe Interactive
 * Author URI:        https://www.madebytribe.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       caddy
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0
 * WC tested up to: 4.0.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
 * Define all constants for the plugin
 */
if ( ! defined( 'CADDY_VERSION' ) ) {
	define( 'CADDY_VERSION', '1.5' );
}
if ( ! defined( 'CADDY_PLUGIN_FILE' ) ) {
	define( 'CADDY_PLUGIN_FILE', __FILE__ );
}

/**
 * Checks if the WC requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function caddy_wc_requirements_met() {

	/**
	 * Check if WooCommerce is active
	 **/
	if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		return false;
	}

	return true;
}

if ( caddy_wc_requirements_met() ) {

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-caddy-activator.php
	 */
	function activate_caddy() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-caddy-activator.php';
		Caddy_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-caddy-deactivator.php
	 */
	function deactivate_caddy() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-caddy-deactivator.php';
		Caddy_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'activate_caddy' );
	register_deactivation_hook( __FILE__, 'deactivate_caddy' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-caddy.php';

	/**
	 * The plugin class that is used to register and load the cart widget.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-caddy-cart-widget.php';

	/**
	 * The plugin class that is used to register and load the saved items widget.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-caddy-saved-items-widget.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_caddy() {

		$plugin = new Caddy();
		$plugin->run();

	}

	run_caddy();

	/**
	 * Add plugin settings link.
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	function cc_add_settings_link( $links ) {

		$links = array_merge( array( '<a href="' . esc_url( admin_url( '/admin.php?page=caddy&amp;tab=settings' ) ) . '">' . __( 'Settings', 'caddy' ) . '</a>' ), $links );

		return $links;
	}

	$plugin = plugin_basename( __FILE__ );
	add_filter( "plugin_action_links_$plugin", 'cc_add_settings_link' );

} else {

	add_action( 'admin_notices', 'caddy_wc_requirements_error' );

}

/**
 * If WC requirements are not match
 */
function caddy_wc_requirements_error() {
	?>
	<div class="error notice"><p>
			<strong><?php _e( 'The WooCommerce plugin needs to be installed and activated in order for Caddy to work properly.', 'caddy' ); ?></strong> <?php _e( 'Please activate WooCommerce to enable Caddy.', 'caddy' ); ?>
		</p></div>
	<?php
}
