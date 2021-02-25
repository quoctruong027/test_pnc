<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_Plugins_Search' ) ) {

	/**
	 * Class that takes care of adding XplodedThemes plugins within wordpress popular plugins sections.
	 *
	 * @package    XT_Framework
	 * @subpackage XT_Framework/includes
	 * @author     XplodedThemes
	 */
	class XT_Framework_Plugins_Search {

		public static $_instance;

		function __construct() {

			add_filter( 'plugins_api_result', array( $this, 'plugin_results' ), 1, 3 );
		}

		function plugin_results( $res, $action, $args ) {

			if ( $action !== 'query_plugins' ) {
				return $res;
			}

			$args = (array) $args;

			unset( $args['browse'] );

			if ( ! empty( $args['xt_plugin_query'] ) || ! empty( $args['search'] ) ) {
				return $res;
			}

			$args['author']          = 'XplodedThemes';
			$args['xt_plugin_query'] = true;

			$api = plugins_api( 'query_plugins', $args );

			if ( is_wp_error( $api ) ) {
				return $res;
			}

			$below_plugins = array_splice( $res->plugins, 8 );
			$top_plugins   = $res->plugins;

			$top_plugins = array_merge( $api->plugins, $top_plugins );
			shuffle( $top_plugins );

			$res->plugins = array_merge( $top_plugins, $below_plugins );

			return $res;
		}

		/**
		 * Main XT_Framework_Plugins_Search Instance
		 *
		 * Ensures only one instance of XT_Framework_Plugins_Search is loaded or can be loaded.
		 *
		 * @return XT_Framework_Plugins_Search instance
		 * @since 1.0.0
		 * @static
		 */
		public static function instance() {
			if ( empty( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		} // End instance()

	}
}