<?php
/**
 * Plugin Name: WooCommerce Thank You Page - NextMove
 * Plugin URI: https://xlplugins.com/woocommerce-thank-you-page-nextmove/
 * Description: The only plugin in WooCommerce that empowers you to build profit-pulling Thank You Pages with plug & play components. It's for store owners who want to get repeat orders on autopilot
 * Version: 1.14.0
 * Author: XLPlugins
 * Author URI: https://www.xlplugins.com
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: thank-you-page-for-woocommerce-nextmove
 * XL: True
 * XLTOOLS: True
 * Requires at least: 4.2.1
 * Tested up to: 5.4.1
 * WC requires at least: 3.0
 * WC tested up to: 4.1
 *
 * WooCommerce Thank You Page - NextMove is free software.
 * You can redistribute it and/or modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * WooCommerce Thank You Page - NextMove is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WooCommerce Thank You Page - NextMove. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package NextMove
 * @Category Core
 * @author XLPlugins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XLWCTY_Core' ) ) :

	class XLWCTY_Core {

		/**
		 * @var XLWCTY_Core
		 */
		public static $_instance = null;
		private static $_registered_entity = array(
			'active'   => array(),
			'inactive' => array(),
		);

		/**
		 * @var xlwcty
		 */
		public $public;

		/**
		 * @var XLWCTY_XL_Support
		 */
		public $xl_support;

		/**
		 * @var XLWCTY_Data
		 */
		public $data;

		/**
		 * @var bool Dependency check property
		 */
		private $is_dependency_exists = true;

		public function __construct() {

			/**
			 * Load important variables and constants
			 */
			$this->define_plugin_properties();

			/**
			 * Load dependency classes like woo-functions.php
			 */
			$this->load_dependencies_support();

			/**
			 * Run dependency check to check if dependency available
			 */
			$this->do_dependency_check();
			if ( $this->is_dependency_exists ) {

				/**
				 * Loads all the hooks
				 */
				$this->load_hooks();

				/**
				 * Initiates and loads XL start file
				 */
				$this->load_xl_core_classes();

				/**
				 * Include common classes
				 */
				$this->include_commons();

				/**
				 * Initialize common hooks and functions
				 */
				$this->initialize_common();

				/**
				 * Maybe load admin if admin screen
				 */
				$this->maybe_load_admin();
			}
		}

		public function define_plugin_properties() {
			/** DEFINING CONSTANTS */
			define( 'XLWCTY_VERSION', '1.14.0' );
			define( 'XLWCTY_MIN_WC_VERSION', '3.0' );
			define( 'XLWCTY_TEXTDOMAIN', 'thank-you-page-for-woocommerce-nextmove' );
			define( 'XLWCTY_NAME', 'NextMove' );
			define( 'XLWCTY_FULL_NAME', 'WooCommerce Thank You Page - ' . XLWCTY_NAME );
			define( 'XLWCTY_PLUGIN_FILE', __FILE__ );
			define( 'XLWCTY_PLUGIN_DIR', __DIR__ );
			define( 'XLWCTY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			define( 'XLWCTY_PURCHASE', 'xlplugin' );
			define( 'XLWCTY_SHORT_SLUG', 'xlwcty' );
		}

		public function load_dependencies_support() {
			/** Setting up WooCommerce Dependency Classes */
			require_once( 'woo-includes/woo-functions.php' );
		}

		public function do_dependency_check() {
			if ( ! xlwcty_is_woocommerce_active() ) {
				add_action( 'admin_notices', array( $this, 'xlwcty_wc_not_installed_notice' ) );
				$this->is_dependency_exists = false;
			}
		}

		public function load_hooks() {
			/** Initializing Functionality */

			add_action( 'plugins_loaded', array( $this, 'xlwcty_init' ), 0 );

			add_action( 'plugins_loaded', array( $this, 'xlwcty_register_classes' ), 1 );
			/** Initialize Localization */
			add_action( 'init', array( $this, 'xlwcty_init_localization' ) );

			/** Redirecting Plugin to the settings page after activation */
			add_action( 'activated_plugin', array( $this, 'xlwcty_settings_redirect' ) );

			do_action( 'xlwcty_loaded' );

			add_action( 'xl_loaded', array( $this, 'xlwcty_load_xl_core_require_files' ), 10, 1 );
		}

		public function load_xl_core_classes() {

			/** Setting Up XL Core */
			require_once( 'start.php' );
		}

		public function include_commons() {
			/** Loading Common Class */
			include_once plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'XLWCTY_EDD_License_Handler.php';

			require plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'includes/xlwcty-common.php';
			require_once plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'compatibilities/class-xlwcty-compatibilities.php';
			require plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'includes/xlwcty-xl-support.php';
			require plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'merge-tags/xlwcty-shortcode-merge-tags.php';
			require plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'merge-tags/xlwcty-dynamic-merge-tags.php';
			require plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'merge-tags/xlwcty-static-merge-tags.php';
			require plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'includes/xlwcty-interface-coupon.php';
			require plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'includes/xlwcty-component.php';
			require plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'includes/xlwcty-components.php';
			require plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'includes/xlwcty-dynamic-component.php';
		}

		public function initialize_common() {
			/** Firing Init to init basic Functions */
			XLWCTY_Common::init();
		}

		public function maybe_load_admin() {
			/* ----------------------------------------------------------------------------*
			 * Dashboard and Administrative Functionality
			 * ---------------------------------------------------------------------------- */
			if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
				require_once( plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'admin/xlwcty-admin.php' );
				require_once( plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'admin/class-xlwcty-wizard.php' );
			}
		}

		public function xlwcty_register_classes() {
			$load_classes = self::get_registered_class();
			if ( is_array( $load_classes ) && count( $load_classes ) > 0 ) {
				foreach ( $load_classes as $access_key => $class ) {
					$this->$access_key = $class::get_instance();
				}
			}
			do_action( 'xlwcty_loaded' );
		}

		public static function get_registered_class() {
			return self::$_registered_entity['active'];
		}

		public static function register( $short_name, $class, $overrides = null ) {

			//Ignore classes that have been marked as inactive
			if ( in_array( $class, self::$_registered_entity['inactive'], true ) ) {
				return;
			}

			//Mark classes as active. Override existing active classes if they are supposed to be overridden
			$index = array_search( $overrides, self::$_registered_entity['active'], true );
			if ( false !== $index ) {
				self::$_registered_entity['active'][ $index ] = $class;
			} else {
				self::$_registered_entity['active'][ $short_name ] = $class;
			}

			//Mark overridden classes as inactive.
			if ( ! empty( $overrides ) ) {
				self::$_registered_entity['inactive'][] = $overrides;
			}
		}

		/**
		 * @return null|XLWCTY_Core
		 */
		public static function get_instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new self;
			}

			return self::$_instance;
		}

		public function xlwcty_init_localization() {
			load_plugin_textdomain( 'thank-you-page-for-woocommerce-nextmove', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Added redirection on plugin activation
		 *
		 * @param $plugin
		 */
		public function xlwcty_settings_redirect( $plugin ) {
			if ( ! defined( 'WP_CLI' ) && xlwcty_is_woocommerce_active() && class_exists( 'WooCommerce' ) ) {
				if ( plugin_basename( __FILE__ ) === $plugin ) {

					wp_safe_redirect( add_query_arg( array(
						'page'      => 'wc-settings',
						'tab'       => XLWCTY_Common::get_wc_settings_tab_slug(),
						'activated' => 'yes',
					), admin_url( 'admin.php' ) ) );
					exit;
				}
			}
		}

		/**
		 * Checking WooCommerce dependency and then loads further
		 */
		public function xlwcty_init() {
			if ( xlwcty_is_woocommerce_active() && class_exists( 'WooCommerce' ) ) {

				if ( ! version_compare( WC()->version, XLWCTY_MIN_WC_VERSION, '>=' ) ) {
					add_action( 'admin_notices', 'xlwcty_wc_version_check_notice' );
				}

				if ( isset( $_GET['xlwcty_disable'] ) && 'yes' === $_GET['xlwcty_disable'] && is_user_logged_in() && current_user_can( 'administrator' ) ) {
					return;
				}
				require plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'includes/xlwcty-data.php';
				require plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'includes/xlwcty-themes-helper.php';

				if ( ! ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) || ! is_admin() ) {
					require plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'includes/xlwcty-public.php';
				}
			}
		}

		/*         * ******** REGISTERING NOTICES ******************* */

		public function xlwcty_wc_version_check_notice() {
			?>
            <div class="error">
                <p>
					<?php
					/* translators: %1$s: Min required woocommerce version */
					printf( __( 'NextMove requires WooCommerce version %1$s or greater. Kindly update the WooCommerce plugin.', XLWCTY_FULL_NAME ), XLWCTY_MIN_WC_VERSION );
					?>
                </p>
            </div>
			<?php
		}

		public function xlwcty_wc_not_installed_notice() {
			?>
            <div class="error">
                <p>
					<?php
					echo __( 'WooCommerce is not installed or activated. NextMove is a WooCommerce Extension and would only work if WooCommerce is activated. Please install the WooCommerce Plugin first.', XLWCTY_FULL_NAME );
					?>
                </p>
            </div>
			<?php
		}

		public function xlwcty_load_xl_core_require_files( $get_global_path ) {
			if ( file_exists( $get_global_path . 'includes/class-xl-cache.php' ) ) {
				require_once $get_global_path . 'includes/class-xl-cache.php';
			}
			if ( file_exists( $get_global_path . 'includes/class-xl-transients.php' ) ) {
				require_once $get_global_path . 'includes/class-xl-transients.php';
			}
			if ( file_exists( $get_global_path . 'includes/class-xl-file-api.php' ) ) {
				require_once $get_global_path . 'includes/class-xl-file-api.php';
			}
		}
	}

endif;

if ( ! function_exists( 'XLWCTY_Core' ) ) {

	/**
	 * Global Common function to load all the classes
	 * @return XLWCTY_Core
	 */
	function XLWCTY_Core() {
		return XLWCTY_Core::get_instance();
	}
}

require plugin_dir_path( __FILE__ ) . 'includes/xlwcty-logging.php';

/**
 * Collect PHP fatal errors and save it in the log file so that it can be later viewed
 * @see register_shutdown_function
 */
if ( ! function_exists( 'xlplugins_collect_errors' ) ) {
	function xlplugins_collect_errors() {
		$error = error_get_last();
        
		if ( ! isset( $error['type'] ) || empty( $error['type'] ) ) {
			return;
		}

		if ( E_ERROR === $error['type'] ) {
			xlplugins_force_log( $error['message'] . PHP_EOL, 'fatal-errors.txt' );
		}
	}

	register_shutdown_function( 'xlplugins_collect_errors' );
}

$GLOBALS['XLWCTY_Core'] = XLWCTY_Core();
