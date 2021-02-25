<?php

class XLWCTY_POWER_PACK_XL_Support {

	public static $_instance = null;
	public $full_name = XLWCTY_POWER_PACK_FULL_NAME;
	public $is_license_needed = true;
	public $license_instance;
	public $expected_url;

	protected $slug = 'xlwcty';
	protected $encoded_basename = '';

	public function __construct() {

		$this->expected_url     = admin_url( 'admin.php?page=xlplugins' );
		$this->encoded_basename = sha1( XLWCTY_POWER_PACK_PLUGIN_BASENAME );

		add_action( 'admin_init', array( $this, 'xlwcty_power_pack_xl_expected_slug' ), 9.1 );

		add_filter( 'extra_plugin_headers', array( $this, 'extra_woocommerce_headers' ) );

		add_action( 'admin_init', array( $this, 'init_edd_licensing' ), 1 );
		add_filter( 'xl_plugins_license_needed', array( $this, 'register_support' ) );

		add_action( 'xl_licenses_submitted', array( $this, 'process_licensing_form' ) );
		add_action( 'xl_deactivate_request', array( $this, 'maybe_process_deactivation' ) );

		add_filter( 'xl_after_license_table_notice', array( $this, 'xlwcty_power_pack_after_license_table_notice' ), 999, 1 );

		add_action( 'xl_tabs_modal_licenses', array( $this, 'schedule_license_check' ), 1 );

		add_filter( 'xl_uninstall_reasons', array( $this, 'modify_uninstall_reason' ) );

		add_filter( 'xl_uninstall_reason_threshold_' . XLWCTY_POWER_PACK_PLUGIN_BASENAME, function () {
			return 8;
		} );
	}

	/**
	 * @return null|XLWCTY_XL_Support
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function xlwcty_power_pack_xl_expected_slug() {
		if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'xlplugins' || $_GET['page'] == 'xlplugins-support' || $_GET['page'] == 'xlplugins-addons' ) ) {
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
				if ( $license['product_status'] == 'invalid' ) {
					$invalid_licenses[] = $license['plugin'];
				}
			}
		}

		if ( ! XL_admin_notifications::has_notification( 'license_needs_attention' ) && count( $invalid_licenses ) > 0 ) {
			$license_invalid_text = sprintf( __( '<p>You are <strong>not receiving</strong> Latest Updates, New Features, Security Updates &amp; Bug Fixes for <strong>%1$s</strong>. <a href="%2$s">Click Here To Fix This</a>.</p>', 'nextmove-power-pack' ), implode( ',', $invalid_licenses ), add_query_arg( array(
				'tab' => 'licenses',
			), $this->expected_url ) );

			XL_admin_notifications::add_notification( array(
				'license_needs_attention' => array(
					'type'           => 'error',
					'is_dismissable' => false,
					'content'        => $license_invalid_text,
				),
			) );
		}
	}

	public function xlwcty_power_pack_metabox_always_open( $classes ) {
		if ( ( $key = array_search( 'closed', $classes ) ) !== false ) {
			unset( $classes[ $key ] );
		}

		return $classes;
	}

	public function xlplugins_page() {
		if ( ! isset( $_GET['tab'] ) ) {
			XL_dashboard::$selected = 'licenses';
		}
		XL_dashboard::load_page();
	}

	public function xlplugins_support_page() {
		if ( ! isset( $_GET['tab'] ) ) {
			XL_dashboard::$selected = 'support';
		}
		XL_dashboard::load_page();
	}

	public function xlplugins_plugins_page() {
		XL_dashboard::$selected = 'plugins';
		XL_dashboard::load_page();
	}

	public function modify_api_args_for_gravityxl( $args ) {
		if ( isset( $args['edd_action'] ) && $args['edd_action'] == 'get_xl_plugins' ) {
			$args['attrs']['tax_query'] = array(
				array(
					'taxonomy' => 'xl_edd_tax_parent',
					'field'    => 'slug',
					'terms'    => 'woocommerce',
					'operator' => 'IN',
				),
			);
		}
		$args['purchase'] = XLWCTY_PURCHASE;

		return $args;
	}


	/**
	 * Adding XL Header to tell WordPress to read one extra params while reading plugin's header info. <br/>
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

	public function register_support( $plugins ) {
		$status = 'invalid';
		$renew  = 'Please Activate';
		if ( get_option( $this->edd_slugify_module_name( $this->full_name ) . '_license_active' ) == 'valid' ) {
			$status      = 'active';
			$licensedata = get_option( $this->edd_slugify_module_name( $this->full_name ) . 'license_data' );

			$renew = ( $licensedata && is_object( $licensedata ) ) ? $licensedata->expires : '';
		}

		$plugins[ $this->encoded_basename ] = array(
			'plugin'            => $this->full_name,
			'product_version'   => XLWCTY_POWER_PACK_VERSION,
			'product_status'    => $status,
			'license_expiry'    => $renew,
			'product_file_path' => $this->encoded_basename,
			'existing_key'      => get_option( 'xl_licenses_' . $this->edd_slugify_module_name( $this->full_name ) ),
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
			wp_safe_redirect( 'admin.php?page=' . $posted_data['page'] . '&tab=' . $posted_data['tab'] );
		}
	}

	public function xlwcty_power_pack_after_license_table_notice( $notice ) {
		return 'Note: You need to have a valid license key to receiving updates for these plugins. Click here to get your <a href="https://xlplugins.com/manage-licenses/" target="_blank">License Keys</a>.';
	}


	public function init_edd_licensing() {
		if ( is_admin() && class_exists( 'XLWCTY_Power_Pack_License_Handler' ) && $this->is_license_needed ) {
			$this->license_instance = new XLWCTY_Power_Pack_License_Handler( XLWCTY_POWER_PACK_PLUGIN_FILE, $this->full_name, XLWCTY_POWER_PACK_VERSION, 'xlplugins' );
		}
	}

	public function schedule_license_check() {
		wp_schedule_single_event( time() + 10, 'xlwcty_power_pack_maybe_schedule_check_license' );
	}


	public function modify_uninstall_reason( $reasons ) {
		$reasons_our = $reasons;

		$reason_other = array(
			'id'                => 7,
			'text'              => __( 'Other', 'nextmove-power-pack' ),
			'input_type'        => 'textfield',
			'input_placeholder' => __( 'Other', 'nextmove-power-pack' ),
		);

		$reasons_our[ XLWCTY_POWER_PACK_PLUGIN_BASENAME ] = array(
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
				'input_placeholder' => '',
			),
			array(
				'id'                => 5,
				'text'              => XL_deactivate::load_str( 'reason-suddenly-stopped-working' ),
				'input_type'        => '',
				'input_placeholder' => '',
			),
		);

		array_push( $reasons_our[ XLWCTY_POWER_PACK_PLUGIN_BASENAME ], $reason_other );

		return $reasons_our;
	}

	public function is_license_present() {
		$shortname   = $this->edd_slugify_module_name( $this->full_name );
		$license_key = get_option( 'xl_licenses_' . $shortname, '' );

		return ( $license_key == '' ) ? false : true;

	}

}

XLWCTY_POWER_PACK_XL_Support::get_instance();
