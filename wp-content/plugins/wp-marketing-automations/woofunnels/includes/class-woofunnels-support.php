<?php

/**
 * @author woofunnels
 * @package WooFunnels
 * @phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
 * @phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
 */
class WooFunnels_Support {

	protected static $instance;
	public $validation = true;
	public $is_submitted;

	/**
	 *
	 * WooFunnels_Support constructor.
	 */
	public function __construct() {
	}

	/**
	 * Creates and instance of the class
	 * @return WooFunnels_Support
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Prepares the system information report.
	 *
	 * @access public
	 * @return string $report
	 */
	public function prepare_system_information_report( $return = false ) {
		global $wpdb, $wp_version;
		$output = array();

		/* Queue up needed information. */
		$php_extensions         = get_loaded_extensions();
		$php_ini_get            = array( 'memory_limit', 'max_execution_time', 'upload_max_filesize', 'max_file_uploads', 'post_max_size', 'max_input_vars' );
		$mysql_version          = $wpdb->get_var( $wpdb->prepare( 'SELECT VERSION() AS %s', 'version' ) );
		$db_character_set       = $wpdb->get_var( 'SELECT @@character_set_database' );
		$db_collation           = $wpdb->get_var( 'SELECT @@collation_database' );
		$plugins                = get_plugins();
		$active_plugins         = get_option( 'active_plugins', array() );
		$active_network_plugins = is_multisite() ? $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->sitemeta} WHERE meta_key=%s", 'active_sitewide_plugins' ) ) : array();
		$has_log_files          = false;

		$theme               = array();
		$get_theme_info      = wp_get_theme();
		$theme['name']       = $get_theme_info->get( 'Name' );
		$theme['uri']        = $get_theme_info->get( 'ThemeURI' );
		$theme['version']    = $get_theme_info->get( 'Version' );
		$theme['author']     = $get_theme_info->get( 'Author' );
		$theme['author_uri'] = $get_theme_info->get( 'AuthorURI' );

		/* Begin report. */
		$report = '#### System Information Report ####' . "\n\n";

		/* PHP information. */
		$report                      .= '*** PHP Information ***' . "\n";
		$report                      .= 'Version: ' . phpversion() . "\n";
		$output['php']['phpversion'] = phpversion();
		foreach ( $php_ini_get as $ini_get ) {
			$report                    .= $ini_get . ': ' . ini_get( $ini_get ) . "\n";
			$output['php']["$ini_get"] = ini_get( $ini_get );
		}
		$report                .= 'cURL Enabled: ' . ( function_exists( 'curl_init' ) ? 'Yes' : 'No' ) . "\n";
		$output['php']['curl'] = ( function_exists( 'curl_init' ) ? 'Yes' : 'No' );
		$report                .= 'Mcrypt Enabled: ' . ( function_exists( 'mcrypt_encrypt' ) ? 'Yes' : 'No' ) . "\n";

		$output['php']['mcrypt']   = ( function_exists( 'mcrypt_encrypt' ) ? 'Yes' : 'No' );
		$report                    .= 'Mbstring Enabled: ' . ( function_exists( 'mb_strlen' ) ? 'Yes' : 'No' ) . "\n";
		$output['php']['mbstring'] = ( function_exists( 'mb_strlen' ) ? 'Yes' : 'No' );
		$report                    .= 'Loaded Extensions: ' . implode( ', ', $php_extensions ) . "\n\n";

		$output['php']['loaded_extenstions'] = $php_extensions;
		/* MySQL information. */
		$report                           .= '*** MySQL Information ***' . "\n";
		$report                           .= 'Version: ' . $mysql_version . "\n";
		$output['php']['mysql_version']   = $mysql_version;
		$report                           .= 'Database Character Set: ' . $db_character_set . "\n";
		$output['php']['mysql_charset']   = $db_character_set;
		$report                           .= 'Database Collation: ' . $db_collation . "\n\n";
		$output['php']['mysql_collation'] = $db_collation;

		/* Web server information. */
		$report                            .= '*** Web Server Information ***' . "\n";
		$report                            .= 'Software: ' . $_SERVER['SERVER_SOFTWARE'] . "\n"; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		$output['server']['software']      = $_SERVER['SERVER_SOFTWARE']; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		$report                            .= 'Port: ' . $_SERVER['SERVER_PORT'] . "\n"; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		$output['server']['post']          = $_SERVER['SERVER_PORT']; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		$report                            .= 'Document Root: ' . $_SERVER['DOCUMENT_ROOT'] . "\n\n"; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		$output['server']['document_root'] = $_SERVER['DOCUMENT_ROOT']; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification

		/* WordPress information. */
		$report                              .= '*** WordPress Information ***' . "\n";
		$report                              .= 'WP_VERSION: ' . ( $wp_version ) . "\n";
		$output['wordpress']['wp_version']   = $wp_version;
		$report                              .= 'WP_DEBUG: ' . ( WP_DEBUG ? 'Enabled' : 'Disabled' ) . "\n";
		$output['wordpress']['wp_debug']     = ( WP_DEBUG ? 'Enabled' : 'Disabled' );
		$report                              .= 'WP_DEBUG_LOG: ' . ( WP_DEBUG_LOG ? 'Enabled' : 'Disabled' ) . "\n";
		$output['wordpress']['wp_debug_log'] = ( WP_DEBUG_LOG ? 'Enabled' : 'Disabled' );

		$report .= 'WP_MEMORY_LIMIT: ' . WP_MEMORY_LIMIT . "\n";

		$output['wordpress']['wp_memory_limit'] = WP_MEMORY_LIMIT;
		$report                                 .= 'Multisite: ' . ( is_multisite() ? 'Enabled' : 'Disabled' ) . "\n";
		$output['wordpress']['mutltisite']      = ( is_multisite() ? 'Enabled' : 'Disabled' );
		$report                                 .= 'Site URL: ' . get_site_url() . "\n";
		$output['wordpress']['site_url']        = get_site_url();
		$report                                 .= 'Home URL: ' . get_home_url() . "\n\n";
		$output['wordpress']['home_url']        = get_home_url();
		$output['wordpress']['timezone_string'] = get_option( 'timezone_string' );
		$output['wordpress']['gmt_offset']      = get_option( 'gmt_offset' );

		if ( class_exists( 'WooCommerce' ) ) {
			$payment_gateways = WC()->payment_gateways->payment_gateways();
			$sections         = array();
			foreach ( $payment_gateways as $gateway ) {
				if ( 'yes' === $gateway->enabled ) {
					$sections[] = esc_html( $gateway->get_title() );
				}
			}

			$output['woocommerce']['payement_gateways'] = $sections;
			/* WordPress information. */
			$report .= '*** WooCommerce Information ***' . "\n";
			$report .= 'WC_GATEWAYS: ' . implode( ' || ', $sections ) . "\n\n";
		}

		/* Current Theme Info. */
		$report                        .= '*** Theme Information ***' . "\n";
		$report                        .= 'Name: ' . $theme['name'] . "\n";
		$output['theme']['name']       = $theme['name'];
		$report                        .= 'URI: ' . $theme['uri'] . "\n";
		$output['theme']['uri']        = $theme['uri'];
		$report                        .= 'Version: ' . $theme['version'] . "\n";
		$output['theme']['version']    = $theme['version'];
		$report                        .= 'Author: ' . $theme['author'] . "\n";
		$output['theme']['author']     = $theme['author'];
		$report                        .= 'Author URI: ' . $theme['author_uri'] . "\n";
		$output['theme']['author_uri'] = $theme['author_uri'];

		/* Plugin information. */
		$report .= '*** Active Plugins ***' . "\n";
		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( in_array( $plugin_path, $active_plugins, true ) ) {
				$report                                        .= $plugin['Name'] . ' (' . $plugin['Version'] . ')' . "\n";
				$output['active_plugins']["{$plugin['Name']}"] = $plugin['Version'];
			} else {
				$output['inactive_plugins']["{$plugin['Name']}"] = $plugin['Version'];
			}
		}
		$report .= "\n";

		/* Multi site plugin information. */
		if ( is_multisite() && ! empty( $active_network_plugins ) ) {
			$report                 .= '*** Network Active Plugins ***' . "\n";
			$active_network_plugins = maybe_unserialize( $active_network_plugins );
			$get_slugs              = array_keys( $active_network_plugins );
			foreach ( $get_slugs as $plugin_slug ) {
				$plugin                                         = get_plugin_data( WP_CONTENT_DIR . '/plugins/' . $plugin_slug );
				$report                                         .= $plugin['Name'] . ' (' . $plugin['Version'] . ')' . "\n";
				$output['network_plugins']["{$plugin['Name']}"] = $plugin['Version'];
			}
			$report .= "\n";
		}

		$report .= "\n" . '#### System Information Report ####';
		if ( $return ) {
			return $output;
		}

		return $report;

	}

	/**
	 * Processing support request
	 *
	 * @param $posted_data
	 *
	 * @uses WooFunnels_API used to fire api request to generate request
	 * @uses WooFunnels_admin_notifications pushing success and failure notifications
	 * @since 1.0.4
	 */
	public function woofunnels_maybe_push_support_request( $posted_data ) {
	}


	public function fetch_tools_data() {
	}

	public function js_script() {
	}


}
