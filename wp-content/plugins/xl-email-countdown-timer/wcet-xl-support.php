<?php

class WCET_XL_Support {

	public $full_name = WCET_FULL_NAME;
	public $is_license_needed = true;
	public $license_instance;
	public $expected_url;
	public static $_instance = null;

	protected $slug = 'wcet';

	public function __construct() {
		$this->expected_url = admin_url( 'admin.php?page=xlplugins' );

		/**
		 * XL CORE HOOKS
		 */
		add_filter( "xl_optin_notif_show", array( $this, 'wcet_xl_show_optin_pages' ), 10, 1 );

		add_action( 'admin_init', array( $this, 'wcet_xl_expected_slug' ), 9.1 );

		add_filter( 'extra_plugin_headers', array( $this, 'extra_woocommerce_headers' ) );

		add_action( 'admin_init', array( $this, 'init_edd_licensing' ), 1 );
		add_filter( 'xl_plugins_license_needed', array( $this, 'register_support' ) );

		add_action( 'xl_licenses_submitted', array( $this, 'process_licensing_form' ) );
		add_action( 'xl_deactivate_request', array( $this, 'maybe_process_deactivation' ) );

		add_action( 'admin_menu', array( $this, 'add_menus' ), 80.1 );

		add_action( 'xl_tabs_modal_licenses', array( $this, 'schedule_license_check' ), 1 );
		/**
		 * Pushing Deactivation For XL Core
		 */
		add_filter( 'plugin_action_links_' . WCET_PLUGIN_BASENAME, array( $this, 'wcet_plugin_actions' ) );

	}

	public function wcet_xl_show_optin_pages( $is_show ) {
		return true;
	}

	/**
	 * @return null|WCET_XL_Support
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function wcet_xl_expected_slug() {
		if ( isset( $_GET['page'] ) && ( $_GET['page'] == "xlplugins" || $_GET['page'] == "xlplugins-support" || $_GET['page'] == "xlplugins-addons" ) ) {
			XL_dashboard::set_expected_slug( $this->slug );
		}
		XL_dashboard::set_expected_url( $this->expected_url );

		/**
		 * Pushing notifications for invalid licenses found in ecosystem
		 */
		$licenses         = XL_licenses::get_instance()->get_data();
		$invalid_licenses = array();
		if ( $licenses && count( $licenses ) > 0 ) {
			foreach ( $licenses as $key => $license ) {
				if ( $license['product_status'] == "invalid" ) {
					$invalid_licenses[] = $license['plugin'];
				}
			}
		}

		if ( ! XL_admin_notifications::has_notification( 'license_needs_attention' ) && count( $invalid_licenses ) > 0 ) {
			$license_invalid_text = sprintf( __( '<p>You are <strong>not receiving</strong> Latest Updates, New Features, Security Updates &amp; Bug Fixes for <strong>%s</strong>. <a href="%s">Click Here To Fix This</a>.</p>', WCET_SLUG ), implode( ",", $invalid_licenses ), add_query_arg( array( 'tab' => 'licenses' ), $this->expected_url ) );

			XL_admin_notifications::add_notification( array(
				'license_needs_attention' => array(
					'type'           => 'error',
					'is_dismissable' => false,
					'content'        => $license_invalid_text,
				)
			) );
		}
	}

	public function wcet_metabox_always_open( $classes ) {
		if ( ( $key = array_search( 'closed', $classes ) ) !== false ) {
			unset( $classes[ $key ] );
		}

		return $classes;
	}

	public function xlplugins_page() {
		if ( ! isset( $_GET['tab'] ) ) {
			XL_dashboard::$selected = "licenses";
		}
		XL_dashboard::load_page();
	}

	public function xlplugins_support_page() {
		if ( ! isset( $_GET['tab'] ) ) {
			XL_dashboard::$selected = "support";
		}
		XL_dashboard::load_page();
	}

	public function xlplugins_plugins_page() {
		XL_dashboard::$selected = "plugins";
		XL_dashboard::load_page();
	}

	/**
	 * Adding XL Header to tell wordpress to read one extra params while reading plugin's header info. <br/>
	 * Hooked over `extra_plugin_headers`
	 * @since 1.0.0
	 *
	 * @param array $headers already registered arrays
	 *
	 * @return type
	 */
	public function extra_woocommerce_headers( $headers ) {
		array_push( $headers, 'XL' );

		return $headers;
	}

	public function modify_menu_classes( $menu ) {
		return $menu;
	}

	public function register_support( $plugins ) {
		$status = "invalid";
		$renew  = "Please Activate";
		if ( get_option( $this->edd_slugify_module_name( $this->full_name ) . '_license_active' ) == "valid" ) {
			$status      = "active";
			$licensedata = get_option( $this->edd_slugify_module_name( $this->full_name ) . "license_data" );
			$renew       = $licensedata->expires;
		}

		$plugins[ WCET_PLUGIN_BASENAME ] = array(
			'plugin'            => $this->full_name,
			'product_version'   => WCET_VERSION,
			'product_status'    => $status,
			'license_expiry'    => $renew,
			'product_file_path' => WCET_PLUGIN_BASENAME,
			'existing_key'      => get_option( 'xl_licenses_' . $this->edd_slugify_module_name( $this->full_name ) )
		);

		return $plugins;
	}

	/**
	 * License management helper function to create a slug that is friendly with edd
	 *
	 * @param type $name
	 *
	 * @return type
	 */
	public function edd_slugify_module_name( $name ) {
		return preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $name ) ) );
	}

	public function process_licensing_form( $posted_data ) {

		if ( isset( $posted_data['license_keys'][ WCET_PLUGIN_BASENAME ] ) ) {
			$shortname = $this->edd_slugify_module_name( $this->full_name );
			update_option( 'xl_licenses_' . $shortname, $posted_data['license_keys'][ WCET_PLUGIN_BASENAME ], false );
			$this->license_instance->activate_license( $posted_data['license_keys'][ WCET_PLUGIN_BASENAME ] );
		}
	}

	/**
	 * Validate is it is for email product deactivation
	 *
	 * @param type $posted_data
	 */
	public function maybe_process_deactivation( $posted_data ) {
		if ( isset( $posted_data['filepath'] ) && $posted_data['filepath'] == WCET_PLUGIN_BASENAME ) {
			$this->license_instance->deactivate_license();
			wp_safe_redirect( 'admin.php?page=' . $posted_data['page'] . "&tab=" . $posted_data['tab'] );
		}
	}

	public function init_edd_licensing() {
		if ( is_admin() && class_exists( 'WCET_EDD_License' ) && $this->is_license_needed ) {
			$this->license_instance = new WCET_EDD_License( WCET_PLUGIN_FILE, $this->full_name, WCET_VERSION, 'xlplugins' );
		}
	}

	/**
	 * Adding WooCommerce sub-menu for global options
	 */
	public function add_menus() {
		if ( ! XL_dashboard::$is_core_menu ) {
			add_menu_page( __( 'XLPlugins', WCET_SLUG ), __( 'XLPlugins', WCET_SLUG ), 'manage_woocommerce', 'xlplugins', array( $this, 'xlplugins_page' ), '', '59.5' );
			add_submenu_page( 'xlplugins', __( 'Licenses', WCET_SLUG ), __( 'License', WCET_SLUG ), 'manage_woocommerce', 'xlplugins' );
			XL_dashboard::$is_core_menu = true;
		}
	}

	public function schedule_license_check() {
		wp_schedule_single_event( time() + 10, 'wcet_maybe_schedule_check_license' );
	}


	public function wcet_plugin_actions( $links ) {
		$links['deactivate'] .= '<i class="xl-slug" data-slug="' . WCET_PLUGIN_BASENAME . '"></i>';

		return $links;
	}

}

new WCET_XL_Support();
