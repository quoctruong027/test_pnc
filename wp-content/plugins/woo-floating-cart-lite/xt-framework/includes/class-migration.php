<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_Migration' ) ) {

	/**
	 * Class that takes care of migrations.
	 *
	 * @package    XT_Framework
	 * @subpackage XT_Framework/includes
	 * @author     XplodedThemes
	 */
	class XT_Framework_Migration {

		/**
		 * Core class reference.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      XT_Framework    core    Core Class
		 */
		private $core;

		public $version_key;
		public $new_version;
		public $old_version;
		public $migrations = array();

		public function __construct( &$core ) {

			$this->core = $core;

			$this->version_key = $this->get_version_key();
			$this->new_version = $this->core->plugin_version();
			$this->old_version = get_option( $this->version_key );

			add_action( 'init', array( $this, 'upgrade' ), 10 );
		}

		function get_version_key() {

			return $this->core->plugin_slug( 'version' );
		}

		function get_migrations() {

			$files      = glob( $this->core->plugin_path( 'admin/migrations', 'migration-*.php' ) );
			$migrations = array();

			foreach ( $files as $file ) {

				preg_match( '/migration\-(.+?)\.php/', $file, $matches );
				$migrations[] = $matches[1];
			}

			return $migrations;
		}

		function upgrade() {

			if ( $this->new_version !== $this->old_version ) {

				$migrations = $this->get_migrations();

				foreach ( $migrations as $migration ) {

					if ( $this->old_version < $migration ) {

						$this->migrate( $migration );
					}
				}
				// End Migrations

				update_option( $this->version_key, $this->new_version );

				$this->after_upgrade();
			}
		}

		function migrate( $version ) {

			$path = $this->core->plugin_path( 'admin/migrations', 'migration-' . $version . '.php' );

			if ( file_exists( $path ) ) {

				require_once $path;
			}

		}

		function after_upgrade() {

			do_action( $this->core->plugin_prefix( 'migration_complete' ) );
		}

	}
}