<?php
/**
 * WC_PRL_Install class
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
 * Handles installation and updating tasks.
 *
 * @class    WC_PRL_Install
 * @version  1.4.0
 */
class WC_PRL_Install {

	/**
	 * DB updates and callbacks that need to be run per version.
	 * @var array
	 */
	private static $db_updates = array();

	/**
	 * Background update class.
	 * @var WC_PRL_Background_Updater
	 */
	private static $background_updater;

	/**
	 * Current plugin version.
	 * @var string
	 */
	private static $current_version;

	/**
	 * Current DB version.
	 * @var string
	 */
	private static $current_db_version;

	/**
	 * Whether install() ran in this request.
	 * @var boolean
	 */
	private static $is_install_request;

	/**
	 * Hook in.
	 */
	public static function init() {

		// Installation and DB updates handling.
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 5 );
		add_action( 'init', array( __CLASS__, 'define_updating_constant' ) );
		add_action( 'init', array( __CLASS__, 'maybe_install' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_update' ) );

		// Show row meta on the plugin screen.
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );

		// Get PRL plugin and DB versions.
		self::$current_version    = get_option( 'wc_prl_version', null );
		self::$current_db_version = get_option( 'wc_prl_db_version', null );

		include_once( WC_PRL_ABSPATH . 'includes/class-wc-prl-background-updater.php' );
	}

	/**
	 * Init background updates.
	 */
	public static function init_background_updater() {
		self::$background_updater = new WC_PRL_Background_Updater();
	}

	/**
	 * Installation needed?
	 *
	 * @return boolean
	 */
	private static function must_install() {
		return version_compare( self::$current_version, WC_PRL()->get_plugin_version(), '<' );
	}

	/**
	 * Installation possible?
	 *
	 * @param  boolean  $check_installing
	 * @return boolean
	 */
	private static function can_install( $check_installing = true ) {
		return ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! defined( 'IFRAME_REQUEST' ) && ! self::is_installing();
	}

	/**
	 * Check version and run the installer if necessary.
	 */
	public static function maybe_install() {
		if ( self::can_install() && self::must_install() ) {
			self::install();
		}
	}

	/**
	 * Is currently installing?
	 *
	 * @since  1.3.0
	 *
	 * @return boolean
	 */
	private static function is_installing() {
		return 'yes' === get_transient( 'wc_prl_installing' );
	}

	/**
	 * DB update needed?
	 *
	 * @return boolean
	 */
	private static function must_update() {

		if ( is_null( self::$current_db_version ) ) {
			return false;
		}

		$db_update_versions = array_keys( self::$db_updates );

		if ( empty( $db_update_versions ) ) {
			return false;
		}

		$db_version_target = end( $db_update_versions );

		return version_compare( self::$current_db_version, $db_version_target, '<' );
	}

	/**
	 * DB update possible?
	 *
	 * @param  boolean  $check_installing
	 * @return boolean
	 */
	private static function can_update( $check_installing = true ) {
		return ( self::$is_install_request || ( self::can_install() && current_user_can( 'manage_woocommerce' ) ) ) && version_compare( self::$current_db_version, WC_PRL()->get_plugin_version( true ), '<' );
	}

	/**
	 * Run the updater if triggered.
	 */
	public static function maybe_update() {

		if ( ! empty( $_GET[ 'force_wc_prl_db_update' ] ) && wp_verify_nonce( $_GET[ '_wc_prl_admin_nonce' ], 'wc_prl_force_db_update_nonce' ) ) {

			if ( self::can_update() && self::must_update() ) {
				self::force_update();
			}

		} elseif ( ! empty( $_GET[ 'trigger_wc_prl_db_update' ] ) && wp_verify_nonce( $_GET[ '_wc_prl_admin_nonce' ], 'wc_prl_trigger_db_update_nonce' ) ) {

			if ( self::can_update() && self::must_update() ) {
				self::trigger_update();
			}

		} else {

			// Queue upgrade tasks.
			if ( self::can_update() ) {

				if ( ! is_blog_installed() ) {
					return;
				}

				// Plugin data exists - queue upgrade tasks.
				if ( self::must_update() ) {

					if ( ! class_exists( 'WC_PRL_Admin_Notices' ) ) {
						require_once WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-notices.php';
					}

					// Add 'update' notice and save early -- saving on the 'shutdown' action will fail if a chained request arrives before the 'shutdown' hook fires.
					WC_PRL_Admin_Notices::add_maintenance_notice( 'update' );
					WC_PRL_Admin_Notices::save_notices();

					if ( self::auto_update_enabled() ) {
						self::update();
					} else {
						delete_transient( 'wc_prl_installing' );
						delete_option( 'wc_prl_update_init' );
					}

					// Nothing found - this is a new install :)
				} else {
					self::update_db_version();
				}
			}
		}
	}

	/**
	 * If the DB version is out-of-date, a DB update must be in progress: define a 'WC_PRL_UPDATING' constant.
	 */
	public static function define_updating_constant() {
		if ( self::is_update_pending() && ! defined( 'WC_PRL_TESTING' ) ) {
			wc_maybe_define_constant( 'WC_PRL_UPDATING', true );
		}
	}

	/**
	 * Install PRL.
	 */
	public static function install() {

		if ( ! is_blog_installed() ) {
			return;
		}

		// Running for the first time? Set a transient now. Used in 'can_install' to prevent race conditions.
		set_transient( 'wc_prl_installing', 'yes', 10 );

		// Set a flag to indicate we're installing in the current request.
		self::$is_install_request = true;

		// Create tables.
		self::create_tables();

		// Set up environment.
		self::setup_environment();

		// Create terms.
		self::create_terms();

		if ( ! class_exists( 'WC_PRL_Admin_Notices' ) ) {
			require_once WC_PRL_ABSPATH . 'includes/admin/class-wc-prl-admin-notices.php';
		}

		// Update plugin version - once set, 'maybe_install' will not call 'install' again.
		self::update_version();

		if ( is_null( self::$current_version ) ) {

			// Add dismissible welcome notice.
			WC_PRL_Admin_Notices::add_maintenance_notice( 'welcome' );

		} else {

			// Add new feature nudges.
			WC_PRL_Admin_Notices::add_note( 'whats-new-1-4' );
		}

		// Add page cache notice if the tests have never run before.
		if ( is_null( WC_PRL_Notices::get_page_cache_test_result() ) ) {
			WC_PRL_Admin_Notices::add_maintenance_notice( 'page_cache' );
		}

		// Restore queueing notice after every update.
		WC_PRL_Admin_Notices::add_maintenance_notice( 'queue' );

		// Run a loopback test after every update. Will only run once if successful.
		WC_PRL_Admin_Notices::add_maintenance_notice( 'loopback' );
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 */
	private static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( self::get_schema() );
	}

	/**
	 * Setup post types and taxonomies.
	 */
	private static function setup_environment() {
		WC_PRL_Post_Types::register_post_types();
		WC_PRL_Post_Types::register_taxonomies();
	}

	/**
	 * Add terms.
	 */
	public static function create_terms() {

		// ...
	}

	/**
	 * Get table schema.
	 *
	 * @return string
	 */
	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$max_index_length = 191;

		$tables = "
CREATE TABLE {$wpdb->prefix}woocommerce_prl_deployments (
  `id` BIGINT UNSIGNED NOT NULL auto_increment,
  `active` CHAR(3) NOT NULL,
  `engine_id` BIGINT UNSIGNED NOT NULL,
  `engine_type` VARCHAR(25) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` longtext default NULL,
  `display_order` INT UNSIGNED NOT NULL,
  `columns` INT UNSIGNED NOT NULL,
  `limit` INT UNSIGNED NOT NULL,
  `location_id` VARCHAR(80) NOT NULL,
  `hook` VARCHAR($max_index_length) NOT NULL,
  `conditions_data` longtext NULL,
  PRIMARY KEY  (`id`),
  KEY `active` (`active`),
  KEY `engine_id` (`engine_id`),
  KEY `hook` (`hook`),
  KEY `location_id` (`location_id`)

) $collate;
CREATE TABLE {$wpdb->prefix}woocommerce_prl_deploymentmeta (
  `meta_id` BIGINT UNSIGNED NOT NULL auto_increment,
  `prl_deployment_id` BIGINT UNSIGNED NOT NULL,
  `meta_key` VARCHAR($max_index_length) default NULL,
  `meta_value` longtext NULL,
  PRIMARY KEY  (`meta_id`),
  KEY `prl_deployment_id` (`prl_deployment_id`),
  KEY `meta_key` (meta_key($max_index_length))

) $collate;
CREATE TABLE {$wpdb->prefix}woocommerce_prl_tracking_views (
  `id` BIGINT UNSIGNED NOT NULL auto_increment,
  `hash_key` CHAR(32) NOT NULL,
  `time_span` INT UNSIGNED NOT NULL,
  `deployment_id` BIGINT UNSIGNED NOT NULL,
  `engine_id` BIGINT UNSIGNED NOT NULL,
  `location_hash` CHAR(7) NOT NULL,
  `source_hash` CHAR(32) NULL default '',
  `count` INT UNSIGNED NOT NULL default 1,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hash_key` (`hash_key`),
  KEY `engine_id` (`engine_id`)

) $collate;
CREATE TABLE {$wpdb->prefix}woocommerce_prl_tracking_clicks (
  `id` BIGINT UNSIGNED NOT NULL auto_increment,
  `hash_key` CHAR(32) NOT NULL,
  `time_span` INT UNSIGNED NOT NULL,
  `deployment_id` BIGINT UNSIGNED NOT NULL,
  `engine_id` BIGINT UNSIGNED NOT NULL,
  `location_hash` CHAR(7) NOT NULL,
  `source_hash` CHAR(32) NULL default '',
  `product_id` BIGINT UNSIGNED NOT NULL,
  `count` INT UNSIGNED NOT NULL default 1,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hash_key` (`hash_key`),
  KEY `engine_id` (`engine_id`),
  KEY `product_id` (`product_id`)

) $collate;
CREATE TABLE {$wpdb->prefix}woocommerce_prl_tracking_conversions (
  `id` BIGINT UNSIGNED NOT NULL auto_increment,
  `added_to_cart_time` INT UNSIGNED NOT NULL,
  `ordered_time` INT UNSIGNED NOT NULL,
  `deployment_id` BIGINT UNSIGNED NOT NULL,
  `engine_id` BIGINT UNSIGNED NOT NULL,
  `location_hash` CHAR(7) NOT NULL,
  `source_hash` CHAR(32) NULL default '',
  `product_id` BIGINT UNSIGNED NOT NULL,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `order_item_id` BIGINT UNSIGNED NOT NULL,
  `total` double NULL default NULL,
  `total_tax` double NULL default NULL,
  PRIMARY KEY  (`id`),
  KEY `deployment_id` (`deployment_id`),
  KEY `engine_id` (`engine_id`),
  KEY `product_id` (`product_id`)

) $collate;
CREATE TABLE {$wpdb->prefix}woocommerce_prl_frequencies (
  `id` BIGINT UNSIGNED NOT NULL auto_increment,
  `hash` CHAR(32) NOT NULL,
  `context` VARCHAR(32) NOT NULL default 'order',
  `product_id` BIGINT UNSIGNED NOT NULL,
  `count` INT UNSIGNED NOT NULL,
  `base_total` INT UNSIGNED NOT NULL,
  `expire_date` INT UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hash` (`hash`)

) $collate;
		";

		return $tables;
	}

	/**
	 * Update WC PRL version to current.
	 */
	private static function update_version() {
		delete_option( 'wc_prl_version' );
		add_option( 'wc_prl_version', WC_PRL()->get_plugin_version() );
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function update() {

		if ( ! is_object( self::$background_updater ) ) {
			self::init_background_updater();
		}

		$update_queued = false;

		foreach ( self::$db_updates as $version => $update_callbacks ) {

			if ( version_compare( self::$current_db_version, $version, '<' ) ) {

				$update_queued = true;
				WC_PRL()->log( sprintf( 'Updating to version %s.', $version ), 'info', 'wc_prl_db_updates' );

				foreach ( $update_callbacks as $update_callback ) {
					WC_PRL()->log( sprintf( '- Queuing %s callback.', $update_callback ), 'info', 'wc_prl_db_updates' );
					self::$background_updater->push_to_queue( $update_callback );
				}
			}
		}

		if ( $update_queued ) {

			// Define 'WC_PRL_UPDATING' constant.
			wc_maybe_define_constant( 'WC_PRL_UPDATING', true );

			// Keep track of time.
			delete_option( 'wc_prl_update_init' );
			add_option( 'wc_prl_update_init', gmdate( 'U' ) );

			// Dispatch.
			self::$background_updater->save()->dispatch();
		}
	}

	/**
	 * Is auto-updating enabled?
	 *
	 * @return boolean
	 */
	public static function auto_update_enabled() {
		return apply_filters( 'wc_prl_auto_update_db', true );
	}

	/**
	 * Trigger DB update.
	 */
	public static function trigger_update() {
		self::update();
		wp_safe_redirect( admin_url() );
		exit;
	}

	/**
	 * Force re-start the update cron if everything else fails.
	 */
	public static function force_update() {

		if ( ! is_object( self::$background_updater ) ) {
			self::init_background_updater();
		}

		/**
		 * Updater cron action.
		 */
		do_action( self::$background_updater->get_cron_hook_identifier() );
		wp_safe_redirect( admin_url() );
		exit;
	}

	/**
	 * Updates plugin DB version when all updates have been processed.
	 */
	public static function update_complete() {

		WC_PRL()->log( 'Data update complete.', 'info', 'wc_prl_db_updates' );
		self::update_db_version();
		delete_option( 'wc_prl_update_init' );
		wp_cache_flush();
	}

	/**
	 * True if a DB update is pending.
	 *
	 * @return boolean
	 */
	public static function is_update_pending() {
		return self::must_update();
	}

	/**
	 * True if a DB update was started but not completed.
	 *
	 * @return boolean
	 */
	public static function is_update_incomplete() {
		return false !== get_option( 'wc_prl_update_init', false );
	}


	/**
	 * True if a DB update is in progress.
	 *
	 * @return boolean
	 */
	public static function is_update_queued() {
		return self::$background_updater->is_update_queued();
	}

	/**
	 * True if an update process is running.
	 *
	 * @return boolean
	 */
	public static function is_update_process_running() {
		return self::is_update_cli_process_running() || self::is_update_background_process_running();
	}

	/**
	 * True if an update background process is running.
	 *
	 * @return boolean
	 */
	public static function is_update_background_process_running() {
		return self::$background_updater->is_process_running();
	}

	/**
	 * True if a CLI update is running.
	 *
	 * @return boolean
	 */
	public static function is_update_cli_process_running() {
		return false !== get_transient( 'wc_prl_update_cli_init', false );
	}

	/**
	 * Update DB version to current.
	 *
	 * @param  string  $version
	 */
	public static function update_db_version( $version = null ) {

		$version = is_null( $version ) ? WC_PRL()->get_plugin_version() : $version;

		// Remove suffixes.
		$version = WC_PRL()->get_plugin_version( true, $version );

		delete_option( 'wc_prl_db_version' );
		add_option( 'wc_prl_db_version', $version );

		WC_PRL()->log( sprintf( 'Database version is %s.', get_option( 'wc_prl_db_version', 'unknown' ) ), 'info', 'wc_prl_db_updates' );
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param	mixed  $links
	 * @param	mixed  $file
	 * @return	array
	 */
	public static function plugin_row_meta( $links, $file ) {

		if ( $file == WC_PRL()->get_plugin_basename() ) {
			$row_meta = array(
				'docs'    => '<a href="' . WC_PRL()->get_resource_url( 'docs-contents' ) . '">' . __( 'Documentation', 'woocommerce-product-recommendations' ) . '</a>',
				'support' => '<a href="' . WC_PRL()->get_resource_url( 'ticket-form' ) . '">' . __( 'Support', 'woocommerce-product-recommendations' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return $links;
	}
}

WC_PRL_Install::init();
