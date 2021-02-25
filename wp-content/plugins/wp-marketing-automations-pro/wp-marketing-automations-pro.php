<?php

/**
 * Plugin Name: Autonami Marketing Automations Pro
 * Plugin URI: https://buildwoofunnels.com
 * Description: Unlock deep integration with feature-rich WP & Woo plugins like WooCommerce Subscriptions, Gravity Forms, Affiliate WP, UpStroke, Zapier and many more that we'll keep on adding to the list.
 * Version: 1.2.2
 * Author: WooFunnels
 * Author URI: https://buildwoofunnels.com
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: autonami-automations-pro
 *
 * Requires at least: 4.9
 * Tested up to: 5.5.1
 * WC requires at least: 3.0.0
 * WC tested up to: 4.6.0
 * WooFunnels: true
 */

final class BWFAN_Pro {

	private static $_instance = null;

	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'load_pro_dependencies_support' ], 5 );
		add_action( 'bwfan_loaded', [ $this, 'init_pro' ] );
		add_action( 'bwfan_before_automations_loaded', [ $this, 'add_modules' ] );
		add_action( 'bwfan_merge_tags_loaded', [ $this, 'load_merge_tags' ] );
		add_filter( 'bwfan_event_wc_comment_post_merge_tag_group', [ $this, 'add_wc_affiliate_merge' ], 999 );
		add_filter( 'bwfan_event_wc_new_order_merge_tag_group', [ $this, 'add_wc_affiliate_merge' ], 999 );
		add_filter( 'bwfan_event_wc_order_note_added_merge_tag_group', [ $this, 'add_wc_affiliate_merge' ], 999 );
		add_filter( 'bwfan_event_wc_order_status_change_merge_tag_group', [ $this, 'add_wc_affiliate_merge' ], 999 );
		add_filter( 'bwfan_event_wc_product_purchased_merge_tag_group', [ $this, 'add_wc_affiliate_merge' ], 999 );
		add_filter( 'bwfan_event_wc_product_refunded_merge_tag_group', [ $this, 'add_wc_affiliate_merge' ], 999 );
		add_filter( 'bwfan_event_wc_product_stock_reduced_merge_tag_group', [ $this, 'add_wc_affiliate_merge' ], 999 );
	}

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
	}

	public function init_pro() {
		$this->define_plugin_properties();
		$this->core();

		$this->load_rules();
		$this->load_compatibilities();
	}

	public function load_compatibilities() {
		require BWFAN_PRO_PLUGIN_DIR . '/compatibilities/class-bwfan-pro-compatibilities.php';
	}

	public function add_wc_affiliate_merge( $event_merge_group ) {
		if ( ! bwfan_is_affiliatewp_active() ) {
			return $event_merge_group;
		}
		if ( empty( $event_merge_group ) ) {
			$event_merge_group = array( 'wc_aff_affiliate' );

			return $event_merge_group;
		}

		array_push( $event_merge_group, 'wc_aff_affiliate' );

		return $event_merge_group;
	}

	public function define_plugin_properties() {
		define( 'BWFAN_PRO_VERSION', '1.2.2' );
		define( 'BWFAN_PRO_FULL_NAME', 'Autonami Marketing Automations Pro' );
		define( 'BWFAN_PRO_PLUGIN_FILE', __FILE__ );
		define( 'BWFAN_PRO_PLUGIN_DIR', __DIR__ );
		define( 'BWFAN_PRO_PLUGIN_URL', untrailingslashit( plugin_dir_url( BWFAN_PRO_PLUGIN_FILE ) ) );
		define( 'BWFAN_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'BWFAN_PRO_IS_DEV', true );
		define( 'BWFAN_PRO_DB_VERSION', '1.0' );
		define( 'BWFAN_PRO_ENCODE', sha1( BWFAN_PRO_PLUGIN_BASENAME ) );
	}

	/*
	 * load plugin dependency
	 */
	public function load_pro_dependencies_support() {
		if ( ! did_action( 'bwfan_loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'show_activate_autonami_notice' ) );

			return;
		}

		require BWFAN_PRO_PLUGIN_DIR . '/includes/bwfan-pro-functions.php';
		require BWFAN_PRO_PLUGIN_DIR . '/includes/class-bwfan-pro-plugin-dependency.php';
	}

	public function show_activate_autonami_notice() {
		$screen = get_current_screen();

		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}

		$plugin = 'wp-marketing-automations/wp-marketing-automations.php';
		if ( $this->autonami_install_check() ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );;
			?>
            <div class="notice notice-error" style="display: block!important;">
                <p>
					<?php
					echo '<p>' . __( 'The <b>Autonami Pro</b> plugin requires <b>Autonami</b> plugin to be activated.', 'autonami-automations-pro' ) . '</p>';
					echo '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate Autonami Now', 'autonami-automations-pro' ) ) . '</p>';
					?>
                </p>
            </div>

			<?php
		} else {
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}
			$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=wp-marketing-automations' ), 'install-plugin_wp-marketing-automations' );
			?>
            <div class="notice notice-error" style="display: block!important;">
                <p>
					<?php
					echo '<p>' . __( 'The <b>Autonami Pro</b> plugin requires <b>Autonami</b> plugin to be installed.', 'autonami-automations-pro' ) . '</p>';
					echo '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install Autonami Now', 'autonami-automations-pro' ) ) . '</p>';
					?>
                </p>
            </div>
			<?php
		}
	}

	public function autonami_install_check() {

		$path    = 'wp-marketing-automations/wp-marketing-automations.php';
		$plugins = get_plugins();

		return isset( $plugins[ $path ] );
	}

	private function core() {
		require BWFAN_PRO_PLUGIN_DIR . '/includes/class-bwfan-pro-common.php';
		include BWFAN_PRO_PLUGIN_DIR . '/includes/class-bwfan-pro-woofunnel-support.php';

		BWFAN_PRO_Common::init();

		if ( bwfan_is_learndash_active() ) {
			$this->load_learndash();
		}

		if ( is_admin() ) {
			include BWFAN_PRO_PLUGIN_DIR . '/admin/class-bwfan-pro-admin.php';
		}
	}

	private function load_learndash() {
		require BWFAN_PRO_PLUGIN_DIR . '/includes/class-bwfan-learndash-common.php';
		BWFAN_Learndash_Common::init();
	}

	public function add_modules() {
		$integration_dir = BWFAN_PRO_PLUGIN_DIR . '/modules';
		foreach ( glob( $integration_dir . '/*/class-*.php' ) as $_field_filename ) {
			if ( strpos( $_field_filename, 'index.php' ) !== false ) {
				continue;
			}
			require_once( $_field_filename );
		}
	}

	public function load_merge_tags() {
		$integration_dir = BWFAN_PRO_PLUGIN_DIR . '/merge_tags';
		foreach ( glob( $integration_dir . '/class-*.php' ) as $_field_filename ) {
			if ( strpos( $_field_filename, 'index.php' ) !== false ) {
				continue;
			}
			require_once( $_field_filename );
		}
	}

	private function load_rules() {

		include_once BWFAN_PRO_PLUGIN_DIR . '/rules/class-bwfan-rules.php';
	}

	/**
	 * to avoid unserialize of the current class
	 */
	public function __wakeup() {
		throw new ErrorException( 'BWFAN_Core Pro can`t converted to string' );
	}

	/**
	 * to avoid serialize of the current class
	 */
	public function __sleep() {
		throw new ErrorException( 'BWFAN_Core Pro can`t converted to string' );
	}

	/**
	 * To avoid cloning of current class
	 */
	protected function __clone() {
	}
}

BWFAN_Pro::get_instance();
