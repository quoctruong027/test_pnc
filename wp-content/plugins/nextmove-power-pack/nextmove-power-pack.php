<?php
/**
 * Plugin Name: WooCommerce Thank You Page - NextMove Power Pack
 * Plugin URI: https://xlplugins.com/woocommerce-thank-you-page-nextmove/
 * Description: NextMove Power Pack extends NextMove features. Allow Coupon component to embed on Order's emails. Auto embed Thank You receipt tracking URL to embed. Order Timeline component for more engagement.
 * Version: 1.2.0
 * Author: XLPlugins
 * Author URI: http://xlplugins.com/
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: nextmove-power-pack
 * Domain Path: languages
 * XL: true
 * Requires at least: 4.9.0
 * Tested up to: 5.0.2
 * WC requires at least: 2.6.0
 * WC tested up to: 3.5.3
 *
 * You should have received a copy of the GNU General Public License
 * along with NextMove Power Pack. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package NextMove
 * @Category Add-on
 * @author XLPlugins
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'nextmove_thankyou_page_dependency' ) ) {

	/**
	 * Function to check if nextmove thank you pages pro version is loaded and activated or not?
	 * @return bool True|False
	 */
	function nextmove_thankyou_page_dependency() {

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		if ( false === file_exists( plugin_dir_path( __DIR__ ) . 'thank-you-page-for-woocommerce-nextmove/woocommerce-thankyou-pages.php' ) ) {
			return false;
		}

		return in_array( 'thank-you-page-for-woocommerce-nextmove/woocommerce-thankyou-pages.php', $active_plugins ) || array_key_exists( 'thank-you-page-for-woocommerce-nextmove/woocommerce-thankyou-pages.php', $active_plugins );

	}
}

/**
 * Plugin class to handle setting constants and loading files and static helper methods.
 */
final class Nextmove_Power_Pack {

	/**
	 * Init.
	 */
	public function init() {
		/**
		 * Define Constants for the plugin
		 */
		$this->define_constants();
		/**
		 * Load Files
		 */
		$this->load_files();
	}

	/**
	 * Define all the constants we need
	 */
	public function define_constants() {
		define( 'XLWCTY_POWER_PACK_VERSION', '1.2.0' );
		define( 'XLWCTY_POWER_PACK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'XLWCTY_POWER_PACK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		define( 'XLWCTY_POWER_PACK_PLUGIN_FILE', __FILE__ );
		define( 'XLWCTY_POWER_PACK_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'XLWCTY_POWER_PACK_FULL_NAME', __( 'NextMove Power Pack', 'nextmove-power-pack' ) );
	}

	/**
	 * Loading files hook
	 */
	public function load_files() {
		add_action( 'xlwcty_loaded', array( $this, 'loaded' ) );
	}

	/**
	 * Including files
	 */
	public function loaded() {
		if ( defined( 'XLWCTY_VERSION' ) && version_compare( XLWCTY_VERSION, '1.8.0', '>=' ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'xlwcty_load_admin_scripts' ) );

			include_once XLWCTY_POWER_PACK_PLUGIN_DIR . 'class-xlwcty-power-pack-license-handler.php';
			include_once XLWCTY_POWER_PACK_PLUGIN_DIR . 'includes/class-xlwcty-power-pack-xl-support.php';
			include_once XLWCTY_POWER_PACK_PLUGIN_DIR . 'includes/class-xlwcty-power-pack-email.php';
			include_once XLWCTY_POWER_PACK_PLUGIN_DIR . 'includes/class-xlwcty-components.php';
			include_once XLWCTY_POWER_PACK_PLUGIN_DIR . 'includes/xlwcty-pp-common.php';

			if ( is_admin() ) {
				include_once XLWCTY_POWER_PACK_PLUGIN_DIR . 'includes/class-xlwcty-power-pack-admin.php';
			} else {
				include_once XLWCTY_POWER_PACK_PLUGIN_DIR . 'includes/class-xlwcty-power-pack-public.php';
			}
		} else {
			add_action( 'admin_notices', array( $this, 'xlwcty_nextmove_older_version_notice' ) );
		}
	}

	/**
	 * Show admin notice if older version of nextmove pro available
	 */
	public function xlwcty_nextmove_older_version_notice() {
		echo '<div class="notice error is-dismissible">';
		echo '<p>' . XLWCTY_POWER_PACK_FULL_NAME . __( ' is a NextMove - WooCommerce Thank You page extension and required 1.8.0 or higher.', 'nextmove-power-pack' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Function to include js file only on single campaign post in wp-admin
	 */
	public function xlwcty_load_admin_scripts() {
		if ( XLWCTY_Common::is_load_admin_assets( 'builder' ) || XLWCTY_Common::is_load_admin_assets( 'power_pack_settings' ) ) {
			wp_enqueue_script( 'xlwcty_admin_power_pack_js', XLWCTY_POWER_PACK_PLUGIN_URL . 'assets/js/xlwcty-admin.js', array( 'jquery' ), XLWCTY_POWER_PACK_VERSION, true );
			wp_enqueue_style( 'xlwcty_admin_power_pack_css', XLWCTY_POWER_PACK_PLUGIN_URL . 'assets/css/xlwcty-admin.css', array(), XLWCTY_POWER_PACK_VERSION );
		}

	}

}

if ( nextmove_thankyou_page_dependency() ) {
	$batch_processing = new Nextmove_Power_Pack();
	$batch_processing->init();
}
