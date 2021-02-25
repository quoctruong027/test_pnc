<?php
/**
 * WC_PRL_Admin_Notices class
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
 * Admin notices handling.
 *
 * @class    WC_PRL_Admin_Notices
 * @version  1.4.1
 */
class WC_PRL_Admin_Notices {

	/**
	 * Notices presisting on the next request.
	 * @var array
	 */
	public static $meta_box_notices = array();

	/**
	 * Notices displayed on the current request.
	 * @var array
	 */
	public static $admin_notices = array();

	/**
	 * Maintenance notices displayed on every request until cleared.
	 * @var array
	 */
	public static $maintenance_notices = array();

	/**
	 * Dismissible notices displayed on the current request.
	 * @var array
	 */
	public static $dismissed_notices = array();

	/**
	 * Array of maintenance notice types - name => callback.
	 * @var array
	 */
	private static $maintenance_notice_types = array(
		'update'     => 'update_notice',
		'welcome'    => 'welcome_notice',
		'loopback'   => 'loopback_notice',
		'queue'      => 'queue_notice',
		'page_cache' => 'page_cache_notice'
	);

	/**
	 * Constructor.
	 */
	public static function init() {

		if ( ! class_exists( 'WC_PRL_Notices' ) ) {
			require_once  WC_PRL_ABSPATH . 'includes/class-wc-prl-notices.php' ;
		}

		// Avoid duplicates for some notice types that are meant to be unique.
		if ( ! isset( $GLOBALS[ 'sw_store' ][ 'notices_unique' ] ) ) {
			$GLOBALS[ 'sw_store' ][ 'notices_unique' ] = array();
		}

		self::$maintenance_notices = get_option( 'wc_prl_maintenance_notices', array() );
		self::$dismissed_notices   = get_user_meta( get_current_user_id(), 'wc_prl_dismissed_notices', true );
		self::$dismissed_notices   = empty( self::$dismissed_notices ) ? array() : self::$dismissed_notices;

		// Show meta box notices.
		add_action( 'admin_notices', array( __CLASS__, 'output_notices' ) );
		// Save meta box notices.
		add_action( 'shutdown', array( __CLASS__, 'save_notices' ), 100 );

		if ( function_exists( 'WC' ) ) {
			// Show maintenance notices.
			add_action( 'admin_print_styles', array( __CLASS__, 'hook_maintenance_notices' ) );
		}
	}

	/**
	 * Add a notice/error.
	 *
	 * @param  string   $text
	 * @param  mixed    $args
	 * @param  boolean  $save_notice
	 */
	public static function add_notice( $text, $args, $save_notice = false ) {

		if ( is_array( $args ) ) {
			$type           = $args[ 'type' ];
			$dismiss_class  = isset( $args[ 'dismiss_class' ] ) ? $args[ 'dismiss_class' ] : false;
			$unique_context = isset( $args[ 'unique_context' ] ) ? $args[ 'unique_context' ] : false;
		} else {
			$type           = $args;
			$dismiss_class  = false;
			$unique_context = false;
		}

		if ( $unique_context ) {
			if ( self::unique_notice_exists( $unique_context ) ) {
				return;
			} else {
				$GLOBALS[ 'sw_store' ][ 'notices_unique' ][] = $unique_context;
			}
		}

		$notice = array(
			'type'            => $type,
			'content'         => $text,
			'dismiss_class'   => $dismiss_class
		);

		if ( $save_notice ) {
			self::$meta_box_notices[] = $notice;
		} else {
			self::$admin_notices[] = $notice;
		}
	}

	/**
	 * Checks if a notice that belongs to a the specified uniqueness context already exists.
	 *
	 * @since  1.3.0
	 *
	 * @param  string  $context
	 * @return bool
	 */
	private static function unique_notice_exists( $context ) {
		return $context && in_array( $context, $GLOBALS[ 'sw_store' ][ 'notices_unique' ] );
	}

	/**
	 * Get a setting for a notice type.
	 *
	 * @since  1.3.0
	 *
	 * @param  string  $notice_name
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return array
	 */
	public static function get_notice_option( $notice_name, $key, $default = null ) {
		return WC_PRL_Notices::get_notice_option( $notice_name, $key, $default );
	}

	/**
	 * Set a setting for a notice type.
	 *
	 * @since  1.3.0
	 *
	 * @param  string  $notice_name
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	public static function set_notice_option( $notice_name, $key, $value ) {
		return WC_PRL_Notices::set_notice_option( $notice_name, $key, $value );
	}

	/**
	 * Checks if a maintenance notice is visible.
	 *
	 * @param  string  $notice_name
	 * @return boolean
	 */
	public static function is_maintenance_notice_visible( $notice_name ) {
		return in_array( $notice_name, self::$maintenance_notices );
	}

	/**
	 * Checks if a dismissible notice has been dismissed in the past.
	 *
	 * @param  string  $notice_name
	 * @return boolean
	 */
	public static function is_dismissible_notice_dismissed( $notice_name ) {
		return in_array( $notice_name, self::$dismissed_notices );
	}

	/**
	 * Save notices to the DB.
	 */
	public static function save_notices() {
		update_option( 'wc_prl_meta_box_notices', self::$meta_box_notices );
		update_option( 'wc_prl_maintenance_notices', self::$maintenance_notices );
	}

	/**
	 * Show any stored error messages.
	 */
	public static function output_notices() {

		$saved_notices = get_option( 'wc_prl_meta_box_notices', array() );
		$notices       = $saved_notices + self::$admin_notices;

		if ( ! empty( $notices ) ) {

			foreach ( $notices as $notice ) {

				$notice_classes = array( 'wc_prl_notice', 'notice', 'notice-' . $notice[ 'type' ] );
				$dismiss_attr   = $notice[ 'dismiss_class' ] ? 'data-dismiss_class="' . $notice[ 'dismiss_class' ] . '"' : '';

				if ( $notice[ 'dismiss_class' ] ) {
					$notice_classes[] = $notice[ 'dismiss_class' ];
					$notice_classes[] = 'is-dismissible';
				}

				echo '<div class="' . implode( ' ', $notice_classes ) . '"' . $dismiss_attr . '>';
				echo wpautop( wp_kses_post( $notice[ 'content' ] ) );
				echo '</div>';
			}

			if ( function_exists( 'wc_enqueue_js' ) ) {
				wc_enqueue_js( "
					jQuery( function( $ ) {
						jQuery( '.wc_prl_notice .notice-dismiss' ).on( 'click', function() {

							var data = {
								action: 'wc_prl_dismiss_notice',
								notice: jQuery( this ).parent().data( 'dismiss_class' ),
								security: '" . wp_create_nonce( 'wc_prl_dismiss_notice_nonce' ) . "'
							};

							jQuery.post( '" . WC()->ajax_url() . "', data );
						} );
					} );
				" );
			}

			// Clear.
			delete_option( 'wc_prl_meta_box_notices' );
		}
	}

	/**
	 * Show maintenance notices.
	 */
	public static function hook_maintenance_notices() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		foreach ( self::$maintenance_notice_types as $notice_name => $callback ) {
			if ( self::is_maintenance_notice_visible( $notice_name ) ) {
				call_user_func( array( __CLASS__, $callback ) );
			}
		}
	}

	/**
	 * Add a dimissible notice/error.
	 *
	 * @param  string   $text
	 * @param  mixed    $args
	 */
	public static function add_dismissible_notice( $text, $args ) {
		if ( ! isset( $args[ 'dismiss_class' ] ) || ! self::is_dismissible_notice_dismissed( $args[ 'dismiss_class' ] ) ) {
			self::add_notice( $text, $args );
		}
	}

	/**
	 * Remove a dismissible notice.
	 *
	 * @param  string  $notice_name
	 */
	public static function remove_dismissible_notice( $notice_name ) {

		// Remove if not already removed.
		if ( ! self::is_dismissible_notice_dismissed( $notice_name ) ) {
			self::$dismissed_notices = array_merge( self::$dismissed_notices, array( $notice_name ) );
			update_user_meta( get_current_user_id(), 'wc_prl_dismissed_notices', self::$dismissed_notices );
			return true;
		}

		return false;
	}

	/**
	 * Add a maintenance notice to be displayed.
	 *
	 * @param  string  $notice_name
	 */
	public static function add_maintenance_notice( $notice_name ) {

		// Add if not already there.
		if ( ! self::is_maintenance_notice_visible( $notice_name ) ) {
			self::$maintenance_notices = array_merge( self::$maintenance_notices, array( $notice_name ) );
			return true;
		}

		return false;
	}

	/**
	 * Remove a maintenance notice.
	 *
	 * @param  string  $notice_name
	 */
	public static function remove_maintenance_notice( $notice_name ) {

		// Remove if there.
		if ( self::is_maintenance_notice_visible( $notice_name ) ) {
			self::$maintenance_notices = array_diff( self::$maintenance_notices, array( $notice_name ) );
			return true;
		}

		return false;
	}

	/**
	 * Add 'update' maintenance notice.
	 */
	public static function update_notice() {

		if ( ! class_exists( 'WC_PRL_Install' ) ) {
			return;
		}

		if ( WC_PRL_Install::is_update_pending() ) {

			$status = '';

			// Show notice to indicate that an update is in progress.
			if ( WC_PRL_Install::is_update_process_running() || WC_PRL_Install::is_update_queued() ) {

				$prompt = '';

				// Check if the update process is running.
				if ( false === WC_PRL_Install::is_update_process_running() ) {
					$prompt = self::get_force_update_prompt();
				}

				$status = sprintf( __( '<strong>WooCommerce Product Recommendations</strong> is updating your database.%s', 'woocommerce-product-recommendations' ), $prompt );

			// Show a prompt to update.
			} elseif ( false === WC_PRL_Install::auto_update_enabled() && false === WC_PRL_Install::is_update_incomplete() ) {

				$status  = __( '<strong>WooCommerce Product Recommendations</strong> has been updated! To keep things running smoothly, your database needs to be updated, as well.', 'woocommerce-product-recommendations' );
				$status .= '<br/>' . sprintf( __( 'Before you proceed, please take a few minutes to <a href="%s" target="_blank">learn more</a> about best practices when updating.', 'woocommerce-product-recommendations' ), WC_PRL()->get_resource_url( 'updating' ) );
				$status .= self::get_trigger_update_prompt();

			} elseif ( WC_PRL_Install::is_update_incomplete() ) {

				$status = sprintf( __( '<strong>WooCommerce Product Recommendations</strong> has not finished updating your database.%s', 'woocommerce-product-recommendations' ), self::get_failed_update_prompt() );
			}

			if ( $status ) {
				self::add_notice( $status, array( 'type' => 'info' ) );
			}

		// Show persistent notice to indicate that the update process is complete.
		} else {
			$notice = __( '<strong>WooCommerce Product Recommendations</strong> database update complete. Thank you for updating to the latest version!', 'woocommerce-product-recommendations' );
			self::add_notice( $notice, array( 'type' => 'info', 'dismiss_class' => 'update' ) );
		}
	}

	/**
	 * Add 'welcome' notice.
	 */
	public static function welcome_notice() {

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$show_on_screens = array(
			'dashboard',
			'plugins',
			'woocommerce_page_prl_performance'
		);

		// Onboarding notices should only show on the main dashboard, and on the plugins screen.
		if ( ! in_array( $screen_id, $show_on_screens, true ) ) {
			return;
		}

		ob_start();

		?>
		<div class="sw-welcome-icon"></div>
		<h2 class="sw-welcome-title"><?php esc_attr_e( 'Ready to offer some product recommendations?', 'woocommerce-product-recommendations' ); ?></h2>
		<p class="sw-welcome-text"><?php esc_attr_e( 'Thank you for installing WooCommerce Product Recommendations.', 'woocommerce-product-recommendations' ); ?><br/><?php esc_attr_e( 'Let\'s get started by creating your first recommendation Engine.', 'woocommerce-product-recommendations' ); ?></p>
		<a href="<?php echo admin_url( 'post-new.php?post_type=prl_engine' ); ?>" class="sw-welcome-button button-primary"><?php esc_attr_e( 'Let\'s go!', 'woocommerce-product-recommendations' ); ?></a>
		<?php

		$notice = ob_get_clean();

		self::add_dismissible_notice( $notice, array( 'type' => 'native', 'dismiss_class' => 'welcome' ) );
	}

	/**
	 * Add 'loopback' notice.
	 *
	 * @since  1.3.0
	 */
	public static function loopback_notice() {

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$show_on_screens = array(
			'dashboard',
			'plugins',
			'woocommerce_page_prl_performance'
		);

		// Onboarding notices should only show on the main dashboard, and on the plugins screen.
		if ( ! in_array( $screen_id, $show_on_screens, true ) ) {
			return;
		}

		// Health check class exists?
		if ( ! file_exists( ABSPATH . 'wp-admin/includes/class-wp-site-health.php' ) ) {
			return;
		}

		$last_tested   = self::get_notice_option( 'loopback', 'last_tested', 0 );
		$last_result   = self::get_notice_option( 'loopback', 'last_result', '' );
		$auto_run_test = gmdate( 'U' ) - $last_tested > DAY_IN_SECONDS;
		$show_notice   = 'fail' === $last_result;

		if ( ! function_exists( 'wc_enqueue_js' ) ) {
			return;
		}

		wc_enqueue_js( "
			jQuery( function( $ ) {

				var auto_run_test  = " . ( $auto_run_test ? 'true' : 'false' ) . ",
					notice         = jQuery( '.wc_prl_notice.loopback' ),
					notice_exists  = notice.length > 0;

				var do_loopback_test = function() {

					if ( notice_exists && ! auto_run_test ) {
						notice.find( 'a.wc-prl-run-again' ).addClass( 'disabled' );
						notice.find( 'span.spinner' ).addClass( 'is-active' );
					}

					var data = {
						action: 'wc_prl_loopback_test',
						security: '" . wp_create_nonce( 'wc_prl_loopback_notice_nonce' ) . "'
					};

					jQuery.post( '" . WC()->ajax_url() . "', data, function( response ) {

						if ( ! notice_exists || auto_run_test ) {
							return;
						}

						if ( 'success' === response.result ) {
							notice.html( '" . '<p>' . __( 'Loopback test passed!', 'woocommerce-product-recommendations' ) . '</p>' . "' ).removeClass( 'notice-warning' ).addClass( 'notice-success' );
						} else {
							notice.html( '" . '<p>' . __( 'Loopback test failed!', 'woocommerce-product-recommendations' ) . '</p>' . "' ).removeClass( 'notice-warning' ).addClass( 'notice-error' );
						}
					} );
				};

				if ( auto_run_test ) {
					do_loopback_test();
				}

				if ( notice_exists ) {

					notice.find( 'a.wc-prl-run-again' ).on( 'click', function() {

						auto_run_test = false;

						do_loopback_test();

						return false;
					} );
				}
			} );
		" );

		if ( $show_notice ) {

			$notice       = __( 'Product Recommendations ran a quick check-up on your site, and found that loopback requests might be failing to complete. Loopback requests are used by WooCommerce to run scheduled events, such as database upgrades. To keep your site in top shape, please ask the host or administrator of your server to look into this for you.', 'woocommerce-product-recommendations' );
			$rerun_prompt = '<p><a href="#trigger_loopback_test" class="button wc-prl-run-again">' . __( 'Repeat test', 'woocommerce-product-recommendations' ) . '</a><span class="spinner" style="float:none;vertical-align:top"></span></p>';

			$notice .= $rerun_prompt;

			self::add_dismissible_notice( $notice, array( 'type' => 'warning', 'unique_context' => 'loopback', 'dismiss_class' => 'loopback' ) );
		}
	}

	/**
	 * Add 'page_cache' notice.
	 */
	public static function page_cache_notice() {

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$show_on_screens = array(
			'dashboard',
			'plugins'
		);

		// Onboarding notices should only show on the main dashboard, and on the plugins screen.
		if ( ! in_array( $screen_id, $show_on_screens, true ) ) {
			return;
		}

		// Wait until we have confirmed there's a page cache to show this notice.
		if ( 'cached' !== WC_PRL_Notices::get_page_cache_test_result() ) {
			return;
		}

		// If AJAX rendering is enabled, back off.
		if ( wc_prl_render_using_ajax( 'edit' ) ) {
			return;
		}

		$notice = sprintf( __( 'Product Recommendations ran a quick check-up on your site, and found that your catalog pages are probably served from a full page cache. While this ensures a snappier experience for your visitors, it also prevents <a href="%1$s" target="_blank">some advanced features</a> in Product Recommendations from working correctly. As a workaround, you may enable <strong>Deployments rendering > Use AJAX</strong> under <strong>WooCommerce > Settings > Recommendations</strong> &ndash; but please be aware that doing so will have an impact on server utilization and user experience.', 'woocommerce-product-recommendations' ), WC_PRL()->get_resource_url( 'page-caching' ) );

		self::add_dismissible_notice( $notice, array( 'type' => 'warning', 'dismiss_class' => 'page_cache' ) );
	}

	/**
	 * Display notice if the WC queueing system fails.
	 *
	 * @since  1.3.3
	 */
	public static function queue_notice() {

		if ( ! method_exists( WC(), 'queue' ) ) {
			return;
		}

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$show_on_screens = array(
			'dashboard',
			'plugins'
		);

		// Notices should only show and get scheduled on the main dashboard, and on the plugins screen.
		if ( ! in_array( $screen_id, $show_on_screens, true ) ) {
			return;
		}

		$last_scheduled = WC_PRL_Notices::get_notice_option( 'page_cache', 'last_scheduled', 0 );
		$last_tested    = WC_PRL_Notices::get_notice_option( 'page_cache', 'last_tested', 0 );

		// More than 1 week apart?
		$show_notice = absint( $last_scheduled - $last_tested ) > WEEK_IN_SECONDS;

		if ( $show_notice ) {

			$notice = __( 'Product Recommendations ran a quick check-up on your site, and found that the task scheduler built into WooCommerce might be failing to process certain tasks in time. To keep your site in top shape, please ask the host or administrator of your server to look into this for you.', 'woocommerce-product-recommendations' );

			self::add_dismissible_notice( $notice, array( 'type' => 'warning', 'unique_context' => 'queue', 'dismiss_class' => 'queue' ) );
		}
	}

	/**
	 * Returns a "trigger update" notice component.
	 *
	 * @return string
	 */
	private static function get_trigger_update_prompt() {
		$update_url    = esc_url( wp_nonce_url( add_query_arg( 'trigger_wc_prl_db_update', true, admin_url() ), 'wc_prl_trigger_db_update_nonce', '_wc_prl_admin_nonce' ) );
		$update_prompt = '<p><a href="' . $update_url . '" class="wc-prl-update-now button">' . __( 'Update database', 'woocommerce-product-recommendations' ) . '</a></p>';
		return $update_prompt;
	}

	/**
	 * Returns a "force update" notice component.
	 *
	 * @return string
	 */
	private static function get_force_update_prompt() {

		$fallback_prompt = '';
		$update_runtime  = get_option( 'wc_prl_update_init', 0 );

		// Wait for at least 30 seconds.
		if ( gmdate( 'U' ) - $update_runtime > 30 ) {
			// Perhaps the upgrade process failed to start?
			$fallback_url    = esc_url( wp_nonce_url( add_query_arg( 'force_wc_prl_db_update', true, admin_url() ), 'wc_prl_force_db_update_nonce', '_wc_prl_admin_nonce' ) );
			$fallback_link   = '<a href="' . $fallback_url . '">' . __( 'run it manually', 'woocommerce-product-recommendations' ) . '</a>';
			$fallback_prompt = sprintf( __( ' The process seems to be taking a little longer than usual, so let\'s try to %s.', 'woocommerce-product-recommendations' ), $fallback_link );
		}

		return $fallback_prompt;
	}

	/**
	 * Returns a "failed update" notice component.
	 *
	 * @return string
	 */
	private static function get_failed_update_prompt() {

		$support_link   = '<a href="' . WC_PRL()->get_resource_url( 'ticket-form' ) . '">' . __( 'get in touch with us', 'woocommerce-product-recommendations' ) . '</a>';
		$support_prompt = '<br/><em>' . sprintf( __( 'If this message persists, please restore your database from a backup, or %s.', 'woocommerce-product-recommendations' ), $support_link ) . '</em>';

		return $support_prompt;
	}

	/**
	 * Dismisses a notice. Dismissible maintenance notices cannot be dismissed forever.
	 *
	 * @param  string  $notice
	 */
	public static function dismiss_notice( $notice ) {
		if ( isset( self::$maintenance_notice_types[ $notice ] ) ) {
			return self::remove_maintenance_notice( $notice );
		} else {
			return self::remove_dismissible_notice( $notice );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Notes for the WC Admin Inbox.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Add note.
	 *
	 * @since  1.4.0
	 *
	 * @param  array|string  $args
	 */
	public static function add_note( $args ) {

		if ( ! class_exists( 'WC_PRL_Core_Compatibility' ) ) {
			require_once WC_PRL_ABSPATH . 'includes/compatibility/core/class-wc-prl-core-compatibility.php' ;
		}

		if ( ! WC_PRL_Core_Compatibility::is_wc_admin_enabled() ) {
			return;
		}

		if ( ! class_exists( 'Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes' ) ) {
			return;
		}

		if ( ! is_array( $args ) ) {
			$args = self::get_note_args( $args );
		}

		if ( ! is_array( $args ) ) {
			return;
		}

		$default_args = array(
			'name'         => '',
			'title'        => '',
			'content'      => '',
			'type'         => Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_INFORMATIONAL,
			'source'       => '',
			'icon'         => '',
			'check_plugin' => '',
			'actions'      => array()
		);

		$args = wp_parse_args( $args, $default_args );

		if ( empty( $args[ 'name' ] ) || empty( $args[ 'title' ] ) || empty( $args[ 'content' ] ) || empty( $args[ 'type' ] ) || empty( $args[ 'icon' ] ) ) {
			return false;
		}

		// First, see if we've already created this note so we don't do it again.
		$data_store = WC_Data_Store::load( 'admin-note' );
		$note_ids   = $data_store->get_notes_with_name( $args[ 'name' ] );
		if ( ! empty( $note_ids ) ) {
			return;
		}

		// Otherwise, add the note.
		$note = new Automattic\WooCommerce\Admin\Notes\WC_Admin_Note();

		$note->set_name( $args[ 'name' ] );
		$note->set_title( $args[ 'title' ] );
		$note->set_content( $args[ 'content' ] );
		$note->set_type( $args[ 'type' ] );

		if ( ! method_exists( $note, 'set_image' ) ) {
			$note->set_icon( $args[ 'icon' ] );
		}

		if ( $args[ 'source' ] ) {
			$note->set_source( $args[ 'source' ] );
		}

		if ( is_array( $args[ 'actions' ] ) ) {
			foreach ( $args[ 'actions' ] as $action ) {
				if ( empty( $action[ 'name' ] ) || empty( $action[ 'label' ] ) ) {
					continue;
				}
				$note->add_action( $action[ 'name' ], $action[ 'label' ], empty( $action[ 'url' ] ) ? false : $action[ 'url' ], empty( $action[ 'status' ] ) ? Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_UNACTIONED : $action[ 'status' ], empty( $action[ 'primary' ] ) ? false : $action[ 'primary' ] );
			}
		}

		// Check if plugin installed or activated.
		if ( ! empty( $args[ 'check_plugin' ] ) ) {
			if ( WC_PRL_Notices::is_feature_plugin_installed( $args[ 'name' ] ) ) {
				$note->set_status( Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_ACTIONED );
			}
		}

		$note->save();
	}

	/**
	 * Get note data.
	 *
	 * @since 1.4.0
	 *
	 * @param  string  $name
	 * @return array
	 */
	public static function get_note_args( $name ) {

		if ( 'whats-new-1-4' === $name ) {

			ob_start();

			?>
			<p><?php _e( '<strong>Product Recommendations</strong> just got smarter!', 'woocommerce-product-recommendations' ); ?></p>
			<p><?php _e( 'Add a pinch of intelligence to your catalog, with automated "Bought Together" and "Others Also Bought" recommendations.', 'woocommerce-product-recommendations' ); ?></p>
			<?php

			$content = ob_get_clean();

			$args = array(
				'name'         => 'wc-prl-whats-new-1-4',
				'title'        => __( 'A pinch of intelligence to grow your sales', 'woocommerce-product-recommendations' ),
				'content'      => $content,
				'type'         => Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_INFORMATIONAL,
				'source'       => 'woocommerce-product-recommendations',
				'icon'         => 'share',
				'check_plugin' => true,
				'actions'      => array(
					array(
						'name'  => 'learn-more-1-4',
						'label' => __( 'Learn more', 'woocommerce-product-recommendations' ),
						'url'   => WC_PRL()->get_resource_url( 'whats-new-1-4' )
					)
				)
			);

			return $args;
		}
	}
}

WC_PRL_Admin_Notices::init();
