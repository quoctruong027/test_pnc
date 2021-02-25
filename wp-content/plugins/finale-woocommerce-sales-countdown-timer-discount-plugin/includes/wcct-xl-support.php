<?php

class WCCT_XL_Support {

	public static $_instance = null;
	public $full_name = WCCT_FULL_NAME;
	public $is_license_needed = true;
	public $license_instance;
	public $expected_url;
	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = 'Sales Triggers';
	protected $slug = 'wcct';
	protected $encoded_basename = '';

	public function __construct() {
		$this->expected_url     = admin_url( 'admin.php?page=xlplugins' );
		$this->encoded_basename = sha1( WCCT_PLUGIN_BASENAME );

		/**
		 * XL CORE HOOKS
		 */
		add_filter( "xl_optin_notif_show", array( $this, 'wcct_xl_show_optin_pages' ), 10, 1 );

		add_action( 'admin_init', array( $this, 'wcct_version_update' ), 10 );

		add_action( 'admin_init', array( $this, 'wcct_xl_expected_slug' ), 9.1 );
		add_action( 'maybe_push_optin_notice_state_action', array( $this, 'wcct_try_push_notification_for_optin' ), 10 );

		add_action( 'admin_init', array( $this, 'modify_api_args_if_wcct_dashboard' ), 20 );
		add_filter( 'extra_plugin_headers', array( $this, 'extra_woocommerce_headers' ) );

		add_filter( 'add_menu_classes', array( $this, 'modify_menu_classes' ) );

		add_action( 'admin_init', array( $this, 'init_edd_licensing' ), 1 );
		add_filter( 'xl_plugins_license_needed', array( $this, 'register_support' ) );
		add_action( 'xl_licenses_submitted', array( $this, 'process_licensing_form' ) );
		add_action( 'xl_deactivate_request', array( $this, 'maybe_process_deactivation' ) );

		add_filter( 'xl_dashboard_tabs', array( $this, 'wcct_modify_tabs' ), 999, 1 );
		add_filter( 'xl_after_license_table_notice', array( $this, 'wcct_after_license_table_notice' ), 999, 1 );

		add_action( 'wcct_options_page_right_content', array( $this, 'wcct_options_page_right_content' ), 10 );

		add_action( 'admin_menu', array( $this, 'add_menus' ), 80.1 );
		add_action( 'admin_menu', array( $this, 'add_wcct_menu' ), 85.2 );


		add_action( 'xl_tabs_modal_licenses', array( $this, 'schedule_license_check' ), 1 );

		add_filter( 'xl_uninstall_reasons', array( $this, 'modify_uninstall_reason' ) );

		add_filter( 'xl_uninstall_reason_threshold_' . WCCT_PLUGIN_BASENAME, function () {
			return 10;
		} );

		add_filter( 'xl_global_tracking_data', array( $this, 'xl_add_administration_emails' ) );
		add_filter( 'xl_api_call_agrs', array( $this, 'xl_add_license_data_on_deactivation' ) );
		add_action( 'admin_init', array( $this, 'maybe_handle_license_activation_wizard' ), 1 );

		// tools
		add_action( 'admin_init', array( $this, 'download_tools_settings' ), 2 );
		add_action( 'xl_tools_after_content', array( $this, "export_tools_after_content" ) );
		add_action( 'xl_tools_after_content', array( $this, "export_xl_tools_right_area" ) );
		add_action( "xl_fetch_tools_data", array( $this, "xl_fetch_tools_data" ), 10, 2 );


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

	public function wcct_version_update() {
		$version_update = get_option( 'wcct_version_update', array() );

		if ( ! isset( $version_update[ WCCT_VERSION ] ) ) {
			$version_update[ WCCT_VERSION ] = time();

			update_option( 'wcct_version_update', $version_update );
		}
	}

	public function wcct_xl_show_optin_pages( $is_show ) {
		$version_update = get_option( 'wcct_version_update', array() );

		if ( ! isset( $version_update[ WCCT_VERSION ] ) || ( time() - $version_update[ WCCT_VERSION ] ) < ( 7 * DAY_IN_SECONDS ) ) {
			return $is_show;
		}

		return true;
	}

	public function wcct_xl_expected_slug() {
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

		if ( ! XL_admin_notifications::has_notification( 'license_needs_attention' ) && is_array( $invalid_licenses ) && count( $invalid_licenses ) > 0 ) {
			$license_invalid_text = sprintf( __( '<p>You are <strong>not receiving</strong> Latest Updates, New Features, Security Updates &amp; Bug Fixes for <strong>%s</strong>. <a href="%s">Click Here To Fix This</a>.</p>', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), implode( ",", $invalid_licenses ), add_query_arg( array( 'tab' => 'licenses' ), $this->expected_url ) );

			XL_admin_notifications::add_notification( array(
				'license_needs_attention' => array(
					'type'           => 'error',
					'is_dismissable' => false,
					'content'        => $license_invalid_text,
				)
			) );
		}
	}

	public function wcct_metabox_always_open( $classes ) {
		if ( ( $key = array_search( 'closed', $classes ) ) !== false ) {
			unset( $classes[ $key ] );
		}

		return $classes;
	}

	public function modify_api_args_if_wcct_dashboard() {
		if ( XL_dashboard::get_expected_slug() == $this->slug ) {
			add_filter( 'xl_api_call_agrs', array( $this, 'modify_api_args_for_gravityxl' ) );
			XL_dashboard::register_dashboard( array( 'parent' => array( 'woocommerce' => "WooCommerce Add-ons" ), 'name' => $this->slug ) );
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

	public function wcct_try_push_notification_for_optin() {
		if ( ! XL_admin_notifications::has_notification( 'xl_optin_notice' ) ) {
			XL_admin_notifications::add_notification( array(
				'xl_optin_notice' => array(
					'type'    => 'info',
					'content' => sprintf( '
                        <p>We\'re always building new features into <a target="_blank" href=\'%s\'>Finale</a>, Play a part in shaping the future of Finale and in turn benefit from new conversion-boosting updates.</p>
                        <p>Simply by allowing us to learn about your plugin usage. No sensitive information will be passed on to us. It\'s all safe & secure to say YES.</p>
                        <p><a href=\'%s\' class=\'button button-primary\'>Yes, I want to help</a> <a href=\'%s\' class=\'button button-secondary\'>No, I don\'t want to help</a> <a style="float: right;" target="_blank" href=\'%s\'>Know More</a></p> ', esc_url( "https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/?utm_source=wpplugin&utm_campaign=finale&utm_medium=text&utm_term=optin" ), esc_url( wp_nonce_url( add_query_arg( array(
						'xl-optin-choice' => 'yes',
						'ref'             => filter_input( INPUT_GET, 'page' )
					) ), 'xl_optin_nonce', '_xl_optin_nonce' ) ), esc_url( wp_nonce_url( add_query_arg( 'xl-optin-choice', 'no' ), 'xl_optin_nonce', '_xl_optin_nonce' ) ), esc_url( "https://xlplugins.com/data-collection-policy/?utm_source=wpplugin&utm_campaign=finale&utm_medium=text&utm_term=optin" ) )
				)
			) );
		}
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
		$status      = "invalid";
		$renew       = "Please Activate";
		$license_key = ( isset( $_POST['license_keys'][ $this->encoded_basename ] ) && ( '' != $_POST['license_keys'][ $this->encoded_basename ] ) ) ? $_POST['license_keys'][ $this->encoded_basename ] : '';
		if ( get_option( $this->edd_slugify_module_name( $this->full_name ) . '_license_active' ) == "valid" ) {
			$status      = "active";
			$licensedata = get_option( $this->edd_slugify_module_name( $this->full_name ) . "license_data" );

			$renew       = ( $licensedata && is_object( $licensedata ) ) ? $licensedata->expires : '';
			$license_key = get_option( 'xl_licenses_' . $this->edd_slugify_module_name( $this->full_name ) );
		}

		$plugins[ $this->encoded_basename ] = array(
			'plugin'            => $this->full_name,
			'product_version'   => WCCT_VERSION,
			'product_status'    => $status,
			'license_expiry'    => $renew,
			'product_file_path' => $this->encoded_basename,
			'existing_key'      => $license_key,
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
			$this->license_instance->activate_license( $posted_data['license_keys'][ $this->encoded_basename ] );
			$get_option = get_option( $shortname . '_license_active' );

			if ( $get_option === 'valid' ) {
				update_option( 'xl_licenses_' . $shortname, $posted_data['license_keys'][ $this->encoded_basename ], true );
			}
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

	public function wcct_modify_tabs( $tabs ) {
		if ( $this->slug == XL_dashboard::get_expected_slug() ) {
			return array();
		}

		return $tabs;
	}

	public function wcct_after_license_table_notice( $notice ) {
		return 'Note: You need to have a valid license key to receiving updates for these plugins. Click here to get your <a href="https://xlplugins.com/manage-licenses/" target="_blank">License Keys</a>.';
	}

	public function wcct_add_right_section_on_xlpages() {
		?>
        <div class="postbox ">
            <div class="inside">
                <h3>Activate License</h3>
                <p>1. Visit <a href="https://xlplugins.com/manage-licenses/" target="_blank">License Activation</a>
                    page.</p>
                <p>2. Login to Authenticate with Envato.</p>
                <p>3. Select Item Purchased & Generate License Key.</p>
                <p>4. Enter License Key & Press Activate License.</p>
                <p></p>
            </div>
        </div>
		<?php
	}

	public function init_edd_licensing() {
		if ( is_admin() && class_exists( 'WCCT_EDD_License' ) && $this->is_license_needed ) {
			$this->license_instance = new WCCT_EDD_License( WCCT_PLUGIN_FILE, $this->full_name, WCCT_VERSION, 'xlplugins' );
		}
	}

	/**
	 * Adding WooCommerce sub-menu for global options
	 */
	public function add_menus() {
		if ( ! XL_dashboard::$is_core_menu ) {
			add_menu_page( __( 'XLPlugins', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), __( 'XLPlugins', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), 'manage_woocommerce', 'xlplugins', array(
				$this,
				'xlplugins_page'
			), '', '59.5' );
			add_submenu_page( 'xlplugins', __( 'Licenses', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), __( 'License', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), 'manage_woocommerce', 'xlplugins' );
			XL_dashboard::$is_core_menu = true;
		}
	}

	public function add_wcct_menu() {
		add_submenu_page( 'xlplugins', WCCT_FULL_NAME, 'Finale', 'manage_woocommerce', 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug(), false );
	}

	public function wcct_options_page_right_content() {
		$support_banner_link = add_query_arg( array(
			'pro'          => 'finale',
			'utm_source'   => 'finale-pro',
			'utm_medium'   => 'banner',
			'utm_campaign' => 'plugin-resource',
			'utm_term'     => 'support',
		), 'https://xlplugins.com/support/' );
		$demo_link           = add_query_arg( array(
			'utm_source'   => 'finale-pro',
			'utm_medium'   => 'sidebar',
			'utm_campaign' => 'plugin-resource',
			'utm_term'     => 'demo',
		), 'http://demo.xlplugins.com/finale/' );
		$support_link        = add_query_arg( array(
			'pro'          => 'finale',
			'utm_source'   => 'finale-pro',
			'utm_medium'   => 'sidebar',
			'utm_campaign' => 'plugin-resource',
			'utm_term'     => 'support',
		), 'https://xlplugins.com/support/' );
		$documentation_link  = add_query_arg( array(
			'utm_source'   => 'finale-pro',
			'utm_medium'   => 'sidebar',
			'utm_campaign' => 'plugin-resource',
			'utm_term'     => 'documentation',
		), 'https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/' );

		$other_products = array();
		if ( ! class_exists( 'WCCT_Core' ) ) {
			$finale_link              = add_query_arg( array(
				'utm_source'   => 'finale-pro',
				'utm_medium'   => 'sidebar',
				'utm_campaign' => 'other-products',
				'utm_term'     => 'finale',
			), 'https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/' );
			$other_products['finale'] = array(
				'image' => 'finale.png',
				'link'  => $finale_link,
				'head'  => 'Finale WooCommerce Sales Countdown Timer',
				'desc'  => 'Run Urgency Marketing Campaigns On Your Store And Move Buyers to Make A Purchase',
			);
		}
		if ( ! defined( 'WCST_SLUG' ) ) {
			$sales_trigger_link              = add_query_arg( array(
				'utm_source'   => 'finale-pro',
				'utm_medium'   => 'sidebar',
				'utm_campaign' => 'other-products',
				'utm_term'     => 'sales-trigger',
			), 'https://xlplugins.com/woocommerce-sales-triggers/' );
			$other_products['sales_trigger'] = array(
				'image' => 'sales-trigger.png',
				'link'  => $sales_trigger_link,
				'head'  => 'XL WooCommerce Sales Triggers',
				'desc'  => 'Use 7 Built-in Sales Triggers to Optimise Single Product Pages For More Conversions',
			);
		}
		if ( ! class_exists( 'XLWCTY_Core' ) ) {
			$nextmove_link              = add_query_arg( array(
				'utm_source'   => 'finale-pro',
				'utm_medium'   => 'sidebar',
				'utm_campaign' => 'other-products',
				'utm_term'     => 'nextmove',
			), 'https://xlplugins.com/woocommerce-thank-you-page-nextmove/' );
			$other_products['nextmove'] = array(
				'image' => 'nextmove.png',
				'link'  => $nextmove_link,
				'head'  => 'NextMove WooCommerce Thank You Pages',
				'desc'  => 'Get More Repeat Orders With 17 Plug n Play Components',
			);
		}

		/** Aero offer */
		if ( ! function_exists( 'WFACP_Core' ) ) {
			include_once( WCCT_PLUGIN_DIR . '/admin/views/aero_offers.php' );
		}

		/** UpStroke offer */
		if ( ! function_exists( 'WFOCU_Core' ) ) {
			include_once( WCCT_PLUGIN_DIR . '/admin/views/upstroke_offers.php' );
		}

		/** Other xlplugin products offer */
		if ( is_array( $other_products ) && count( $other_products ) > 0 ) {
			?>
            <h3>Checkout Our Other Plugins:</h3>
			<?php
			foreach ( $other_products as $product_short_name => $product_data ) {
				?>
                <div class="postbox wcct_side_content wcct_xlplugins wcct_xlplugins_<?php echo $product_short_name ?>">
                    <a href="<?php echo $product_data['link'] ?>" target="_blank"></a>
                    <img src="<?php echo plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/' . $product_data['image']; ?>"/>
                    <div class="wcct_plugin_head"><?php echo $product_data['head'] ?></div>
                    <div class="wcct_plugin_desc"><?php echo $product_data['desc'] ?></div>
                </div>
				<?php
			}
		}
		$site_url = site_url();
		$img_url  = add_query_arg( array(
			'v' => WCCT_VERSION,
			'u' => WCCT_Common::String2Hex( $site_url ),
		), 'https://xlplugins.com/assets/wcct/support.jpg' );

		?>
        <div class="postbox wcct_side_content">
            <div class="inside">
                <h3>Resources</h3>
                <p><a href="<?php echo $support_banner_link ?>" target="_blank"><img src="<?php echo $img_url ?>" width="100%"/></a></p>
                <ul>
                    <li><a href="<?php echo $demo_link ?>" target="_blank">Demo</a></li>
                    <li><a href="<?php echo $support_link ?>" target="_blank">Support</a></li>
                    <li><a href="<?php echo $documentation_link ?>" target="_blank">Documentation</a></li>
                </ul>
            </div>
        </div>
		<?php
	}

	public function schedule_license_check() {
		wp_schedule_single_event( time() + 10, 'wcct_maybe_schedule_check_license' );
	}


	public function modify_uninstall_reason( $reasons ) {
		$reasons_our = $reasons;

		$reason_other = array(
			'id'                => 7,
			'text'              => __( "Other", 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'input_type'        => 'textfield',
			'input_placeholder' => __( "Other", 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		$countdown_timer_debug_doc_link = add_query_arg( array(
			'utm_source' => 'finale-lite',
			'utm_medium' => 'deactivation-modal',
			'utm_term'   => 'timer-bar-not-showing',
		), 'https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/troubleshooting-guides/unable-to-see-countdown-timer-or-counter-bar/' );

		$explore_feature_link = add_query_arg( array(
			'utm_source' => 'finale-lite',
			'utm_medium' => 'deactivation-modal',
			'utm_term'   => 'explore-finale',
		), 'https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/' );

		$supoort_ticket_link = admin_url( 'admin.php?page=xlplugins' );

		$product_position_mismatch_link = add_query_arg( array(
			'utm_source' => 'finale-lite',
			'utm_medium' => 'deactivation-modal',
			'utm_term'   => 'timer-bar-position-mismatch',
		), 'https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/troubleshooting-guides/countdown-timer-or-counter-bar-render-at-wrong-positions/' );

		$xl_contact_link = add_query_arg( array(
			'utm_source' => 'finale-lite',
			'utm_medium' => 'deactivation-modal',
			'utm_term'   => 'contact',
		), 'https://xlplugins.com/contact/' );

		$reasons_our[ WCCT_PLUGIN_BASENAME ] = array(

			array(
				'id'                => 29,
				'text'              => __( 'Countdown Timer or Counter Bar didn\'t show even while campaign was running', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'There could be multiple reasons for this. Take 2 mins and read <a href="' . $countdown_timer_debug_doc_link . '" target="_blank">step by step guide</a> to get resolution.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			array(
				'id'                => 30,
				'text'              => __( 'Expected discount amount didn\'t appear', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'There could be a caching plugin, try clearing Cache.<br/>OR you could be using other plugins that modify pricing such as currency switcher, discounting plugin, etc. Then Raise a <a href="' . $supoort_ticket_link . '">Support ticket</a> and will try & help resolve this.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			array(
				'id'                => 31,
				'text'              => __( 'Campaigns were not restricted as per rules', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'Raise a <a href="' . $supoort_ticket_link . '">Support ticket</a> with the screenshot of rules settings and will help you resolve this.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			array(
				'id'                => 32,
				'text'              => __( 'Countdown Timer or Counter Bar didn\'t appear at right positions', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'It seems your theme modified the native WooCommerce positions. Take 2 mins and read <a href="' . $product_position_mismatch_link . '" target="_blank">step by step guide</a> to get resolution.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			array(
				'id'                => 33,
				'text'              => __( 'Finale Activation caused PHP Errors or blank white screen', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'Ensure you have the latest version of WooCommerce & Finale. There could be a possibility of conflict with other plugins. Raise a <a href="' . $supoort_ticket_link . '">Support ticket</a> and will help you resolve this.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			array(
				'id'                => 34,
				'text'              => __( 'Add to Cart wasn\'t working', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'Check Finale\'s Inventory settings or see if you have order with \'Pending Payment\' state. As they may block product inventory.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			array(
				'id'                => 41,
				'text'              => __( 'Troubleshooting conflicts with other plugins', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'Hope you could resolve conflicts soon.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			array(
				'id'                => 35,
				'text'              => __( 'Doing Testing', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'Hope to see you using it again.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
			array(
				'id'                => 1,
				'text'              => __( 'I no longer need the plugin', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'Sorry to know that! How can we better your experience? We may be able to fix what we are aware of. Please <a href="' . $xl_contact_link . '" target="_blank">let us know</a>.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			),
		);

		array_push( $reasons_our[ WCCT_PLUGIN_BASENAME ], $reason_other );

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

				if ( $key !== WCCT_PLUGIN_BASENAME ) {
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

		if ( isset( $_POST['xlp_is_opted'] ) ) {
			update_option( 'xlp_is_opted', 'yes' );
		}

		if ( filter_input( INPUT_POST, 'wcct_verify_license' ) !== null ) {
			$shortname = $this->edd_slugify_module_name( $this->full_name );

			$this->license_instance->activate_license( filter_input( INPUT_POST, 'license_key' ) );
			$get_option = get_option( $shortname . '_license_active' );

			if ( $get_option === 'valid' ) {
				update_option( 'xl_licenses_' . $shortname, filter_input( INPUT_POST, 'license_key' ), false );
				WCCT_Wizard::set_license_state( true );
				do_action( 'xl_license_activated', WCCT_PLUGIN_BASENAME );

				if ( filter_input( INPUT_POST, '_redirect_link' ) !== null ) {
					wp_redirect( filter_input( INPUT_POST, '_redirect_link' ) );
				}
			} else {
				WCCT_Wizard::set_license_state( false );
				WCCT_Wizard::set_license_key( filter_input( INPUT_POST, 'license_key' ) );

			}


		}
	}

	public function export_tools_after_content( $model ) {
		$system_info = XL_Support::get_instance()->prepare_system_information_report() . "\r" . $this->xl_support_system_info();
		?>
        <div class="xl_core_tools" style="width:80%;background: #fff;">
            <h2><?php echo __( 'Finale' ); ?></h2>
            <form method="post">
                <div class="xl_core_tools_inner" style="min-height: 300px;">
                    <textarea name="xl_tools_system_info" readonly style="width:100%;height: 280px;"><?php echo $system_info ?></textarea>
                </div>
                <div style="clear: both;"></div>
                <div class="xl_core_tools_button" style="margin-bottom: 10px;">
                    <a class="button button-primary button-large xl_core_tools_btn" data-plugin="nextmove-lite" href="<?php echo add_query_arg( array(
						"content"  => "wcct_countdown",
						"download" => "true"
					), admin_url( "export.php" ) ) ?>"><?php echo __( "Export Finale Campaign", 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) ?></a>
                    <button type="submit" class="button button-primary button-large xl_core_tools_btn" name="xl_tools_export_setting" value="finale"><?php echo __( "Export Settings", 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) ?></button>
                </div>
                <br>
            </form>
        </div>
		<?php
	}

	public function xl_support_system_info( $return = false ) {
		$setting_report   = array();
		$nm_options       = array();
		$setting_report[] = "#### Finale Settings start here ####";

		$free_shipping = $this->get_shipping_method();
		if ( is_array( $free_shipping ) && count( $free_shipping ) > 0 ) {
			$nm_options['free_coupon_method'] = $free_shipping;
			$setting_report[]                 = "\r***Avaiable Free Shipping Method***\r";
			foreach ( $free_shipping as $sk => $shipping ) {
				$sk ++;
				$setting_report[] = "\t{$sk} title - {$shipping["title"]} ";
				$setting_report[] = "\trequires - {$shipping["requires"]} ";
				$setting_report[] = "\tmin_amount - {$shipping["min_amount"]} \r";
			}
		}

		$free_shipping_coupon = $this->get_free_shipping_coupon();
		if ( is_array( $free_shipping_coupon ) && count( $free_shipping_coupon ) > 0 ) {
			$nm_options['free_coupon_method_coupons'] = $free_shipping_coupon;
			$setting_report[]                         = "\r***Avaiable Free Shipping Method Coupons (recent 10 only)*** \r";
			foreach ( $free_shipping_coupon as $sk => $shipping_coupon ) {
				$setting_report[] = "\tcoupon id-{$shipping_coupon["id"]} ";
				if ( isset( $shipping_coupon["date_expires"] ) && $shipping_coupon["date_expires"] != "" ) {
					$date_expires     = gmdate( "Y-m-d", $shipping_coupon["date_expires"] );
					$setting_report[] = "\tdate_expires - {$date_expires} (yy-mm-dd)";
				}
				if ( isset( $shipping_coupon["expiry_date"] ) && $shipping_coupon["expiry_date"] != "" ) {
					$setting_report[] = "\texpiry_date - {$shipping_coupon["expiry_date"]} ";
				}
				if ( isset( $shipping_coupon["coupon_code"] ) && $shipping_coupon["coupon_code"] != "" ) {
					$setting_report[] = "\tcoupon_code - {$shipping_coupon["coupon_code"]} \r";
				}
			}
		}

		if ( $return ) {
			return array( "finale_settings" => $nm_options );

		}

		$setting_report[] = "#### Finale Settings  end here ####";

		return implode( "\r", $setting_report );
	}

	public function get_shipping_method() {
		global $wpdb;
		$output     = array();
		$freeMethod = $wpdb->get_results( "select * from {$wpdb->prefix}woocommerce_shipping_zone_methods where method_id='free_shipping'", ARRAY_A );
		if ( is_array( $freeMethod ) && count( $freeMethod ) > 0 ) {
			foreach ( $freeMethod as $method ) {
				$free_shipping = get_option( "woocommerce_free_shipping_{$method["method_order"]}_settings", array() );
				if ( is_array( $free_shipping ) && count( $free_shipping ) > 0 ) {
					$output[] = $free_shipping;
				}
			}
		}

		return $output;

	}

	public function get_free_shipping_coupon() {
		global $wpdb;
		$free_coupon = $wpdb->get_results( "select p.id,p.post_title from {$wpdb->prefix}postmeta as m join {$wpdb->prefix}posts as p on m.post_id=p.id where m.meta_key='free_shipping' and m.meta_value='yes' and p.post_type='shop_coupon' and p.post_status='publish' order by p.post_date desc limit 10 ", ARRAY_A );
		if ( is_array( $free_coupon ) && count( $free_coupon ) > 0 ) {
			foreach ( $free_coupon as $key => $value ) {
				$date_expires                        = get_post_meta( $value['id'], "date_expires", true );
				$expiry_date                         = get_post_meta( $value['id'], "expiry_date", true );
				$free_coupon[ $key ]["date_expires"] = $date_expires;
				$free_coupon[ $key ]["expiry_date"]  = $expiry_date;
				$post_title                          = $free_coupon[ $key ]["post_title"];
				unset( $free_coupon[ $key ]["post_title"] );
				$free_coupon[ $key ]["coupon_code"] = $post_title;
			}
		}

		return $free_coupon;
	}

	public function export_xl_tools_right_area() {
//		echo "Hello right content";
	}

	public function download_tools_settings() {
		if ( isset( $_POST["xl_tools_export_setting"] ) && $_POST["xl_tools_export_setting"] == "finale" && isset( $_POST["xl_tools_system_info"] ) && $_POST["xl_tools_system_info"] != '' ) {
			$system_info = XL_Support::get_instance()->prepare_system_information_report( true ) + $this->xl_support_system_info( true );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename=finale.json' );
			echo( json_encode( $system_info ) );
			exit;
		}
	}

	public function xl_fetch_tools_data( $file, $post ) {

		if ( $file == "finale-woocommerce-sales-countdown-timer-discount-plugin.php" ) {
			$xl_support_url = "";
			$system_info    = XL_Support::get_instance()->prepare_system_information_report( true ) + $this->xl_support_system_info( true );
			$upload_dir     = wp_upload_dir();
			$basedir        = $upload_dir["basedir"];
			$baseurl        = $upload_dir["baseurl"];
			if ( is_writable( $basedir ) ) {
				$xl_support     = $basedir . "/xl_support";
				$xl_support_url = $baseurl . "/xl_support";
				if ( ! file_exists( $xl_support ) ) {
					mkdir( $xl_support, 0755, true );
				}
				if ( is_array( $system_info ) && count( $system_info ) > 0 ) {
					$xl_support_file_path = $xl_support . "/finale-support.json";
					$success              = file_put_contents( $xl_support_file_path, json_encode( $system_info ) );
					if ( $success ) {
						$xl_support_url .= "/finale-support.json";
					}
				}
			}
			echo $xl_support_url;
		}
	}

}

if ( class_exists( "WCCT_XL_Support" ) ) {
	WCCT_Core::register( "xl_support", "WCCT_XL_Support" );
}
