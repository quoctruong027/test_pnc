<?php

class WCCT_Deal_XL_Support {

	public static $_instance = null;
	public $full_name = WCCT_DEAL_FULL_NAME;
	public $is_license_needed = true;
	public $license_instance;
	public $expected_url;

	protected $slug = 'wcct';
	protected $encoded_basename = '';

	public function __construct() {

		$this->expected_url     = admin_url( 'admin.php?page=xlplugins' );
		$this->encoded_basename = sha1( WCCT_DEAL_PAGE_PLUGIN_BASENAME );

		/**
		 * Loading XL core
		 */
		add_action( 'init', array( $this, 'xl_init' ), 8 );
		add_action( 'admin_init', array( $this, 'wcct_deal_xl_expected_slug' ), 9.1 );

		add_action( 'admin_init', array( $this, 'modify_api_args_if_wcct_deal_dashboard' ), 20 );
		add_filter( 'extra_plugin_headers', array( $this, 'extra_woocommerce_headers' ) );

		add_filter( 'add_menu_classes', array( $this, 'modify_menu_classes' ) );
		add_action( 'admin_init', array( $this, 'wcct_deal_xl_parse_request_and_process' ), 15 );

		add_action( 'admin_init', array( $this, 'init_edd_licensing' ), 1 );
		add_filter( 'xl_plugins_license_needed', array( $this, 'register_support' ) );
		add_action( 'xl_licenses_submitted', array( $this, 'process_licensing_form' ) );
		add_action( 'xl_deactivate_request', array( $this, 'maybe_process_deactivation' ) );

		add_filter( 'xl_dashboard_tabs', array( $this, 'wcct_deal_modify_tabs' ), 999, 1 );
		add_filter( 'xl_after_license_table_notice', array( $this, 'wcct_deal_after_license_table_notice' ), 999, 1 );


		add_action( 'admin_menu', array( $this, 'add_menus' ), 80.1 );
		add_action( 'admin_menu', array( $this, 'add_wcct_deal_menu' ), 86 );


		add_action( 'xl_tabs_modal_licenses', array( $this, 'schedule_license_check' ), 1 );

		add_filter( 'xl_uninstall_reasons', array( $this, 'modify_uninstall_reason' ) );

		add_filter( 'xl_uninstall_reason_threshold_' . WCCT_DEAL_PAGE_PLUGIN_BASENAME, function () {
			return 8;
		} );

		add_filter( 'xl_global_tracking_data', array( $this, 'xl_add_administration_emails' ) );
		add_filter( 'xl_api_call_agrs', array( $this, 'xl_add_license_data_on_deactivation' ) );
		add_action( 'admin_init', array( $this, 'maybe_handle_license_activation_wizard' ), 1 );


	}

	/**
	 * @return null|WCCT_XL_Support
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function xl_init() {
		XL_Common::include_xl_core();
	}

	public function wcct_deal_xl_expected_slug() {
		if ( isset( $_GET['page'] ) && ( $_GET['page'] == "xlplugins" || $_GET['page'] == "xlplugins-support" || $_GET['page'] == "xlplugins-addons" ) ) {
			XL_dashboard::set_expected_slug( $this->slug );
		}
		XL_dashboard::set_expected_url( $this->expected_url );

		/**
		 * Pushing notifications for invalid licenses found in ecosystem
		 */
		$licenses         = XL_licenses::get_instance()->get_data();
		$invalid_licenses = array();
		if ( $licenses && is_array( $licenses ) && count( $licenses ) > 0 ) {
			foreach ( $licenses as $key => $license ) {
				if ( $license['product_status'] == "invalid" ) {
					$invalid_licenses[] = $license['plugin'];
				}
			}
		}


		if ( ! XL_admin_notifications::has_notification( 'license_needs_attention' ) && count( $invalid_licenses ) > 0 ) {
			$license_invalid_text = sprintf( __( '<p>You are <strong>not receiving</strong> Latest Updates, New Features, Security Updates &amp; Bug Fixes for <strong>%s</strong>. <a href="%s">Click Here To Fix This</a>.</p>', 'finale-woocommerce-deal-pages' ), implode( ",", $invalid_licenses ), add_query_arg( array( 'tab' => 'licenses' ), $this->expected_url ) );

			XL_admin_notifications::add_notification( array(
				'license_needs_attention' => array(
					'type'           => 'error',
					'is_dismissable' => false,
					'content'        => $license_invalid_text,
				)
			) );
		}
	}

	public function wcct_deal_metabox_always_open( $classes ) {
		if ( ( $key = array_search( 'closed', $classes ) ) !== false ) {
			unset( $classes[ $key ] );
		}

		return $classes;
	}

	public function modify_api_args_if_wcct_deal_dashboard() {
		if ( XL_dashboard::get_expected_slug() == $this->slug ) {
			add_filter( 'xl_api_call_agrs', array( $this, 'modify_api_args_for_gravityxl' ) );
			XL_dashboard::register_dashboard( array(
					'parent' => array(
						'woocommerce' => 'WooCommerce Add-ons'
					),
					'name'   => $this->slug,
				) );
		}
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

	public function modify_api_args_for_gravityxl( $args ) {
		if ( isset( $args['edd_action'] ) && $args['edd_action'] == "get_xl_plugins" ) {
			$args['attrs']['tax_query'] = array(
				array(
					'taxonomy' => 'xl_edd_tax_parent',
					'field'    => 'slug',
					'terms'    => 'woocommerce',
					'operator' => 'IN'
				)
			);
		}
		$args['purchase'] = WCCT_PURCHASE;

		return $args;
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

	public function wcct_deal_xl_parse_request_and_process() {
		$instance_support = XL_Support::get_instance();

		if ( $this->slug == XL_dashboard::get_expected_slug() && isset( $_POST['xl_submit_support'] ) ) {

			if ( filter_input( INPUT_POST, 'choose_addon' ) == "" || filter_input( INPUT_POST, 'comments' ) == "" ) {
				$instance_support->validation = false;
				XL_admin_notifications::add_notification( array(
					'support_request_failure' => array(
						'type'           => 'error',
						'is_dismissable' => true,
						'content'        => __( '<p> Unable to submit your request.All fields are required. Please ensure that all the fields are filled out.</p>', 'finale-woocommerce-deal-pages' ),
					)
				) );
			} else {
				$instance_support->xl_maybe_push_support_request( $_POST );
			}
		}
	}

	public function register_support( $plugins ) {
		$status = "invalid";
		$renew  = "Please Activate";
		if ( get_option( $this->edd_slugify_module_name( $this->full_name ) . '_license_active' ) == "valid" ) {
			$status      = "active";
			$licensedata = get_option( $this->edd_slugify_module_name( $this->full_name ) . "license_data" );

			$renew = ( $licensedata && is_object( $licensedata ) ) ? $licensedata->expires : '';
		}

		$plugins[ $this->encoded_basename ] = array(
			'plugin'            => $this->full_name,
			'product_version'   => WCCT_DEAL_PAGE_VERSION,
			'product_status'    => $status,
			'license_expiry'    => $renew,
			'product_file_path' => $this->encoded_basename,
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

		if ( isset( $posted_data['license_keys'][ $this->encoded_basename ] ) ) {
			$shortname = $this->edd_slugify_module_name( $this->full_name );
			update_option( 'xl_licenses_' . $shortname, $posted_data['license_keys'][ $this->encoded_basename ], false );

			$this->license_instance->activate_license( $posted_data['license_keys'][ $this->encoded_basename ] );
		}
	}

	/**
	 * Validate is it is for email product deactivation
	 *
	 * @param type $posted_data
	 */
	public function maybe_process_deactivation( $posted_data ) {
		if ( isset( $posted_data['filepath'] ) && $posted_data['filepath'] == $this->encoded_basename ) {
			$this->license_instance->deactivate_license();
			wp_safe_redirect( 'admin.php?page=' . $posted_data['page'] . "&tab=" . $posted_data['tab'] );
		}
	}

	public function wcct_deal_modify_tabs( $tabs ) {
		if ( $this->slug == XL_dashboard::get_expected_slug() ) {
			return array();
		}

		return $tabs;
	}

	public function wcct_deal_after_license_table_notice( $notice ) {
		return 'Note: You need to have a valid license key to receiving updates for these plugins. Click here to get your <a href="https://xlplugins.com/manage-licenses/" target="_blank">License Keys</a>.';
	}


	public function init_edd_licensing() {
		if ( is_admin() && class_exists( 'WCCT_Deal_License_Handler' ) && $this->is_license_needed ) {
			$this->license_instance = new WCCT_Deal_License_Handler( WCCT_DEAL_PAGE_PLUGIN_FILE, $this->full_name, WCCT_DEAL_PAGE_VERSION, 'xlplugins' );
		}
	}

	/**
	 * Adding WooCommerce sub-menu for global options
	 */
	public function add_menus() {
		if ( ! XL_dashboard::$is_core_menu ) {
			add_menu_page( __( 'XLPlugins', 'finale-woocommerce-deal-pages' ), __( 'XLPlugins', 'finale-woocommerce-deal-pages' ), 'manage_woocommerce', 'xlplugins', array(
				$this,
				'xlplugins_page'
			), '', '59.5' );
			add_submenu_page( 'xlplugins', __( 'Licenses', 'finale-woocommerce-deal-pages' ), __( 'License', 'finale-woocommerce-deal-pages' ), 'manage_woocommerce', 'xlplugins' );
			XL_dashboard::$is_core_menu = true;
		}
	}

	public function add_wcct_deal_menu() {

		add_submenu_page( 'xlplugins', WCCT_DEAL_FULL_NAME, 'Finale Deal Pages', 'manage_woocommerce', 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=deal_pages', false );


	}


	public function schedule_license_check() {
		wp_schedule_single_event( time() + 10, 'wcct_deal_maybe_schedule_check_license' );
	}


	public function modify_uninstall_reason( $reasons ) {
		$reasons_our = $reasons;


		$reason_other = array(
			'id'                => 7,
			'text'              => __( "Other", 'finale-woocommerce-deal-pages' ),
			'input_type'        => 'textfield',
			'input_placeholder' => __( "Other", 'finale-woocommerce-deal-pages' ),
		);


		$reasons_our[ WCCT_DEAL_PAGE_PLUGIN_BASENAME ] = array(

			array(
				'id'                => 3,
				'text'              => XL_deactivate::load_str( 'reason-needed-for-a-short-period' ),
				'input_type'        => '',
				'input_placeholder' => XL_deactivate::load_str( 'reason-needed-for-a-short-period' ),
			),
			array(
				'id'                => 4,
				'text'              => XL_deactivate::load_str( 'reason-broke-my-site' ),
				'input_type'        => '',
				'input_placeholder' => ''
			),
			array(
				'id'                => 5,
				'text'              => XL_deactivate::load_str( 'reason-suddenly-stopped-working' ),
				'input_type'        => '',
				'input_placeholder' => ''
			),
			array(
				'id'                => 19,
				'text'              => __( 'I am unable to locate Deal Shortcode.', 'finale-woocommerce-deal-pages' ),
				'input_type'        => '',
				'input_placeholder' => ''
			),
			array(
				'id'                => 20,
				'text'              => __( 'Campaign Indexing is taking too much time and slowing the site.', 'finale-woocommerce-deal-pages' ),
				'input_type'        => '',
				'input_placeholder' => ''
			),
			array(
				'id'                => 21,
				'text'              => __( 'Deal Shortcode showing blank output.', 'finale-woocommerce-deal-pages' ),
				'input_type'        => '',
				'input_placeholder' => ''
			),
			array(
				'id'                => 22,
				'text'              => __( 'Countdown Timer or Inventory Bar Position is incorrect on Deal Page.', 'finale-woocommerce-deal-pages' ),
				'input_type'        => '',
				'input_placeholder' => ''
			),


		);


		array_push( $reasons_our[ WCCT_DEAL_PAGE_PLUGIN_BASENAME ], $reason_other );

		return $reasons_our;

	}

	public function xl_add_administration_emails( $data ) {

		if ( isset( $data['admins'] ) ) {
			return $data;
		}
		$users = get_users( array( 'role' => 'administrator', 'fields' => array( 'user_email', 'user_nicename' ) ) );

		$data['admins'] = $users;

		return $data;


	}

	public function xl_add_license_data_on_deactivation( $args ) {

		if ( isset( $args['edd_action'] ) && $args['edd_action'] !== "get_deactivation_data" ) {
			return $args;
		}

		$licenses = XL_licenses::get_instance()->get_data();


		if ( $licenses && is_array( $licenses ) && count( $licenses ) > 0 ) {
			foreach ( $licenses as $key => $license ) {

				if ( $key !== WCCT_DEAL_PAGE_PLUGIN_BASENAME ) {
					continue;
				}
				$args['licenses'] = $license;
			}
		}


		return $args;


	}

	public function is_license_present() {
		$shortname   = $this->edd_slugify_module_name( $this->full_name );
		$license_key = get_option( 'xl_licenses_' . $shortname, '' );

		return ( $license_key == '' ) ? false : true;

	}

	public function maybe_handle_license_activation_wizard() {

		if ( filter_input( INPUT_POST, 'wcct_deal_verify_license' ) !== null ) {
			$shortname = $this->edd_slugify_module_name( $this->full_name );
			update_option( 'xl_licenses_' . $shortname, filter_input( INPUT_POST, 'license_key' ), false );

			$this->license_instance->activate_license( filter_input( INPUT_POST, 'license_key' ) );
			$get_option = get_option( $shortname . '_license_active' );

			if ( $get_option === 'valid' ) {
				WCCT_Wizard::set_license_state( true );
				do_action( 'xl_license_activated', WCCT_DEAL_PAGE_PLUGIN_BASENAME );

				if ( filter_input( INPUT_POST, '_redirect_link' ) !== null ) {
					wp_redirect( filter_input( INPUT_POST, '_redirect_link' ) );
				}
			} else {
				WCCT_Wizard::set_license_state( false );
				WCCT_Wizard::set_license_key( filter_input( INPUT_POST, 'license_key' ) );

			}


		}
	}

}

new WCCT_Deal_XL_Support();
