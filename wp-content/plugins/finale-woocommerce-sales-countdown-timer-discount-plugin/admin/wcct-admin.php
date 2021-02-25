<?php

class XLWCCT_Admin {

	protected static $instance = null;
	protected static $default;

	public function __construct() {
		$this->setup_default();
		$this->includes();
		$this->hooks();
	}

	public static function setup_default() {
		self::$default = WCCT_Common::get_default_settings();
	}

	/**
	 * Include files
	 */
	public function includes() {
		/**
		 * Loading dependencies
		 */
		include_once $this->get_admin_uri() . 'includes/cmb2/init.php';
		include_once $this->get_admin_uri() . 'includes/cmb2-addons/tabs/CMB2-WCCT-Tabs.php';
		include_once $this->get_admin_uri() . 'includes/cmb2-addons/switch/switch.php';
		include_once $this->get_admin_uri() . 'includes/cmb2-addons/conditional/cmb2-conditionals.php';

		/**
		 * Loading custom classes for product and option page.
		 */
		include_once $this->get_admin_uri() . 'includes/xl-wcct-reports.php';

		include_once $this->get_admin_uri() . 'includes/wcct-admin-cmb2-support.php';
		include_once $this->get_admin_uri() . 'includes/wcct-admin-countdown-options.php';
	}

	/**
	 * Get Plugin admin path
	 * @return string
	 */
	public function get_admin_uri() {
		return plugin_dir_path( WCCT_PLUGIN_FILE ) . '/admin/';
	}

	public function hooks() {

		add_action( 'admin_enqueue_scripts', array( $this, 'wcct_post_wcct_load_assets' ), 100 );
		/**
		 * Running product meta info setup
		 */
		add_filter( 'cmb2_init', array( $this, 'wcct_add_options_countdown_metabox' ) );

		add_filter( 'cmb2_init', array( $this, 'wcct_add_order_report_metabox' ) );

		/**
		 * Running product meta info setup
		 */
		add_filter( 'cmb2_init', array( $this, 'wcct_add_options_quick_view_metabox' ) );
		add_filter( 'cmb2_init', array( $this, 'wcct_add_options_wcct_metabox' ) );
		add_filter( 'cmb2_init', array( $this, 'wcct_add_options_menu_order_metabox' ) );
		add_filter( 'cmb2_init', array( $this, 'wcct_add_cmb2_multiselect' ) );
		add_filter( 'cmb2_init', array( $this, 'wcct_add_cmb2_post_select' ) );

		add_action( 'cmb2_render_wcct_multiselect', array( $this, 'wcct_multiselect' ), 10, 5 );
		add_action( 'cmb2_render_wcct_post_select', array( $this, 'wcct_post_select' ), 10, 5 );
		/**
		 * Loading js and css
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'wcct_enqueue_admin_assets' ), 20 );
		/**
		 * Remove plugin update transient
		 */
		add_action( 'admin_init', array( $this, 'wcct_remove_plugin_update_transient' ), 10 );
		/**
		 * Loading cmb2 assets
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'cmb2_load_toggle_button_assets' ), 20 );
		/**
		 * Allowing conditionals to work on custom page
		 */
		add_filter( 'xl_cmb2_add_conditional_script_page', array( 'WCCT_Admin_CMB2_Support', 'wcct_push_support_form_cmb_conditionals' ) );
		/**
		 * Handle tabs ordering
		 */
		add_filter( 'wcct_cmb2_modify_field_tabs', array( $this, 'wcct_admin_reorder_tabs' ), 99 );
		/**
		 * Adds HTML field to cmb2 config
		 */
		add_action( 'cmb2_render_wcct_html_content_field', array( $this, 'wcct_html_content_fields' ), 10, 5 );
		/**
		 * Keeping meta box open
		 */
		add_filter( 'postbox_classes_product_wcct_product_option_tabs', array( $this, 'wcct_metabox_always_open' ) );
		/**
		 * Pushing Deactivation For XL Core
		 */
		add_filter( 'plugin_action_links_' . WCCT_PLUGIN_BASENAME, array( $this, 'wcct_plugin_actions' ) );
		add_filter( 'plugin_row_meta', array( $this, 'wcct_plugin_row_actions' ), 10, 2 );
		/**
		 * Adding New Tab in WooCommerce Settings API
		 */
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'modify_woocommerce_settings' ), 99 );
		/**
		 * Adding Customer HTML On setting page for WooCommerce
		 */
		add_action( 'woocommerce_settings_' . WCCT_Common::get_wc_settings_tab_slug(), array( $this, 'wcct_woocommerce_options_page' ) );
		/**
		 * Modifying Publish meta box for our posts
		 */
		add_action( 'post_submitbox_misc_actions', array( $this, 'wcct_post_publish_box' ) );
		/**
		 * Adding `Return To` Notice Out Post Pages
		 */
		add_action( 'edit_form_top', array( $this, 'wcct_edit_form_top' ) );
		/**
		 * Adding Optgroup to trigger selects
		 */
		add_filter( 'cmb2_select_attributes', array( 'WCCT_Admin_CMB2_Support', 'cmb_opt_groups' ), 10, 4 );
		/**
		 * Modifying Post update messages
		 */
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		/**
		 * Hooks to check if activation and deactivation request for post.
		 */
		add_action( 'admin_init', array( $this, 'maybe_activate_post' ) );
		add_action( 'admin_init', array( $this, 'maybe_deactivate_post' ) );
		add_action( 'admin_init', array( $this, 'maybe_duplicate_post' ) );
		add_action( 'admin_init', array( $this, 'maybe_show_wizard' ) );
		add_action( 'save_post_' . WCCT_Common::get_campaign_post_type_slug(), array( $this, 'save_menu_order' ), 99, 2 );
		add_action( 'save_post_product', array( $this, 'delete_product_taxonomy_ids_meta' ), 99 );
		add_filter( 'quick_edit_show_taxonomy', array( $this, 'delete_product_taxonomy_ids_meta_quick_edit' ), 99, 3 );

		/**
		 * CMB2 AFTER SAVE METADATA HOOK
		 */
		add_action( 'cmb2_save_post_fields_wcct_campaign_settings', array( $this, 'sanitize_group_cmb2' ), 999, 3 );
		add_action( 'cmb2_save_post_fields_wcct_campaign_settings', array( $this, 'clear_transients' ), 1000 );

		/**
		 * Add text for  help popup
		 */
		add_action( 'admin_footer', array( $this, 'wcct_add_mergetag_text' ) );
		add_action( 'admin_footer', array( $this, 'wcct_footer_css' ), 20 );

		add_action( 'do_meta_boxes', array( $this, 'wcct_do_meta_boxes' ), 999, 3 );

		/**
		 * Checking deprecated rule types settings and removing their data
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'wcct_maybe_remove_rule_type_data' ), 10 );

		add_action( 'xl_license_activated', array( $this, 'maybe_run_install' ) );

		add_action( 'admin_notices', array( $this, 'maybe_notice_for_deal_page_old_version' ) );

		add_action( 'delete_post', array( $this, 'clear_transients_on_delete' ), 10 );

		add_action( 'post_updated', array( $this, 'restrict_to_publish_when_campaign_is_disabled' ), 10, 3 );

		add_filter( 'admin_notices', array( $this, 'maybe_show_advanced_update_notification' ), 999 );

		/** Metabox when counter bar enabled but inventory bar not */
		add_action( 'edit_form_after_title', array( $this, 'show_counter_bar_error' ) );

		/** Delete post data transient */
		add_action( 'pre_post_update', array( $this, 'delete_post_data_transient' ), 99, 2 );

		/** Validating & removing scripts on page load */
		add_action( 'admin_print_styles', array( $this, 'removing_scripts_finale_campaign_load' ), - 1 );
		add_action( 'admin_print_scripts', array( $this, 'removing_scripts_finale_campaign_load' ), - 1 );
		add_action( 'admin_print_footer_scripts', array( $this, 'removing_scripts_finale_campaign_load' ), - 1 );
	}

	/**
	 * Return an instance of this class.
	 * @return    object    A single instance of this class.
	 * @since     1.0.0
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Hooked over Activation
	 * Checks and insert plugin options(data) in wp_options
	 */
	public function handle_activation() {
		/**
		 * Handle optIn option
		 */
		$sample_campaign = array(
			'wcct_recurring_never' => array(
				'title' => 'Recurring Timer + Bar',
				'meta'  => array(
					'_wcct_campaign_type'                           => 'recurring',
					'_wcct_campaign_fixed_recurring_start_date'     => date( 'Y-m-d', strtotime( '-1 days' ) ),
					'_wcct_campaign_fixed_recurring_start_time'     => '04:00 PM',
					'_wcct_campaign_recurring_duration_days'        => '0',
					'_wcct_campaign_recurring_duration_hrs'         => '5',
					'_wcct_campaign_recurring_gap_days'             => '0',
					'_wcct_campaign_recurring_gap_hrs'              => '0',
					'_wcct_campaign_recurring_gap_mins'             => '0',
					'_wcct_campaign_recurring_ends'                 => 'never',
					'_wcct_deal_enable_goal'                        => '1',
					'_wcct_deal_units'                              => 'custom',
					'_wcct_deal_custom_mode'                        => 'basic',
					'_wcct_deal_custom_units'                       => '8',
					'_wcct_deal_range_from_custom_units'            => '8',
					'_wcct_deal_range_to_custom_units'              => '16',
					'_wcct_deal_inventory_goal_for'                 => 'recurrence',
					'_wcct_location_timer_show_single'              => '1',
					'_wcct_location_timer_single_location'          => '4',
					'_wcct_appearance_timer_single_skin'            => 'highlight_1',
					'_wcct_appearance_timer_single_bg_color'        => '#ffffff',
					'_wcct_appearance_timer_single_text_color'      => '#dd3333',
					'_wcct_appearance_timer_single_font_size_timer' => '26',
					'_wcct_appearance_timer_single_font_size'       => '14',
					'_wcct_appearance_timer_single_label_days'      => 'days',
					'_wcct_appearance_timer_single_label_hrs'       => 'hrs',
					'_wcct_appearance_timer_single_label_mins'      => 'mins',
					'_wcct_appearance_timer_single_label_secs'      => 'secs',
					'_wcct_appearance_timer_single_border_style'    => 'none',
					'_wcct_appearance_timer_single_display'         => "{{countdown_timer}} \nPrices go up when the timer hits zero.",
					'_wcct_location_bar_show_single'                => '1',
					'_wcct_location_bar_single_location'            => '4',
					'_wcct_appearance_bar_single_skin'              => 'stripe_animate',
					'_wcct_appearance_bar_single_edges'             => 'rounded',
					'_wcct_appearance_bar_single_orientation'       => 'rtl',
					'_wcct_appearance_bar_single_bg_color'          => '#dddddd',
					'_wcct_appearance_bar_single_active_color'      => '#ee303c',
					'_wcct_appearance_bar_single_height'            => '12',
					'_wcct_appearance_bar_single_display'           => "Hurry up! Just <span>{{remaining_units}}</span> items left in stock\n{{counter_bar}}",
					'_wcct_appearance_bar_single_border_style'      => 'none',
					'_wcct_appearance_bar_single_border_width'      => '0',
					'_wcct_appearance_bar_single_border_color'      => '#444444',
					'_wcct_campaign_menu_order'                     => 1,
				),
			),
			'wcct_sticky_header'   => array(
				'title' => 'Sticky Header 1 day',
				'meta'  => array(
					'_wcct_campaign_type'                                  => 'fixed_date',
					'_wcct_campaign_fixed_recurring_start_date'            => date( 'Y-m-d' ),
					'_wcct_campaign_fixed_recurring_start_time'            => '12:00 AM',
					'_wcct_campaign_fixed_end_date'                        => date( 'Y-m-d', strtotime( '+1 days' ) ),
					'_wcct_campaign_fixed_end_time'                        => '12:00 AM',
					'_wcct_campaign_recurring_duration_days'               => '1',
					'_wcct_campaign_recurring_duration_hrs'                => '0',
					'_wcct_campaign_recurring_gap_days'                    => '0',
					'_wcct_campaign_recurring_gap_hrs'                     => '0',
					'_wcct_campaign_recurring_gap_mins'                    => '0',
					'_wcct_campaign_recurring_ends'                        => 'never',
					'_wcct_location_timer_show_sticky_header'              => '1',
					'_wcct_appearance_sticky_header_wrap_bg'               => '#ec6952',
					'_wcct_appearance_sticky_header_headline'              => "The Men's wear mega sale (30% off) is ON.",
					'_wcct_appearance_sticky_header_headline_font_size'    => '25',
					'_wcct_appearance_sticky_header_headline_color'        => '#ffffff',
					'_wcct_appearance_sticky_header_description'           => "Act fast to grab season's best sale.",
					'_wcct_appearance_sticky_header_description_font_size' => '16',
					'_wcct_appearance_sticky_header_description_color'     => '#ffffff',
					'_wcct_appearance_sticky_header_skin'                  => 'highlight_1',
					'_wcct_appearance_sticky_header_bg_color'              => '#ec6952',
					'_wcct_appearance_sticky_header_text_color'            => '#ffffff',
					'_wcct_appearance_sticky_header_font_size_timer'       => '22',
					'_wcct_appearance_sticky_header_font_size'             => '14',
					'_wcct_appearance_sticky_header_label_days'            => 'days',
					'_wcct_appearance_sticky_header_label_hrs'             => 'hrs',
					'_wcct_appearance_sticky_header_label_mins'            => 'mins',
					'_wcct_appearance_sticky_header_label_secs'            => 'secs',
					'_wcct_appearance_sticky_header_timer_border_width'    => '1',
					'_wcct_appearance_sticky_header_timer_border_color'    => '#ffffff',
					'_wcct_appearance_sticky_header_timer_border_style'    => 'dashed',
					'_wcct_appearance_sticky_header_enable_button'         => 'on',
					'_wcct_appearance_sticky_header_button_skin'           => 'button_2',
					'_wcct_appearance_sticky_header_button_text'           => 'Checkout Latest Deals',
					'_wcct_appearance_sticky_header_button_bg_color'       => '#000000',
					'_wcct_appearance_sticky_header_button_text_color'     => '#ffffff',
					'_wcct_appearance_sticky_header_button_action'         => '#',
					'_wcct_campaign_menu_order'                            => 2,
				),
			),
		);

		$ids_array = get_option( 'wcct_posts_sample_ids', array() );

		foreach ( $sample_campaign as $key => $val ) {
			if ( ! isset( $ids_array[ $key ] ) || $ids_array[ $key ] == 0 || $ids_array[ $key ] == null ) {
				$id = wp_insert_post( array(
					'post_type'   => WCCT_Common::get_campaign_post_type_slug(),
					'post_title'  => __( $val['title'], 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'post_status' => WCCT_SHORT_SLUG . 'disabled',
				) );
				if ( ! is_wp_error( $id ) ) {
					$ids_array[ $key ] = $id;
					update_option( 'wcct_posts_sample_ids', $ids_array, false );
					$metafields = $val['meta'];
					if ( is_array( $metafields ) && count( $metafields ) > 0 ) {
						foreach ( $metafields as $mkey => $mval ) {
							update_post_meta( $id, $mkey, $mval );
						}
					}
				}
			}
		}
		if ( is_array( $ids_array ) && count( $ids_array ) > 0 ) {
			delete_transient( 'WCCT_INSTANCES' );
		}

		//by default getting opted done on license activation

	}

	/**
	 * Sorter function to sort array by internal key called priority
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	public static function _sort_by_priority( $a, $b ) {
		if ( $a['position'] == $b['position'] ) {
			return 0;
		}

		return ( $a['position'] < $b['position'] ) ? - 1 : 1;
	}

	public static function add_metaboxes() {
		if ( WCCT_Common::wcct_valid_admin_pages( 'single' ) ) {
			add_meta_box( 'wcct_rules', 'Rules', array(
				__CLASS__,
				'rules_metabox',
			), WCCT_Common::get_campaign_post_type_slug(), 'normal', 'high' );
		}
	}

	public static function rules_metabox() {
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'admin/views/metabox-rules.php';
	}

	public function wcct_add_options_countdown_metabox() {
		WCCT_Admin_CountDown_Post_Options::prepere_default_config();
		WCCT_Admin_CountDown_Post_Options::setup_fields();
	}

	public function wcct_add_options_wcct_metabox() {
		WCCT_Admin_CountDown_Post_Options::shortcode_metabox_fields();
	}

	public function wcct_add_options_menu_order_metabox() {
		WCCT_Admin_CountDown_Post_Options::menu_order_metabox_fields();
	}

	public function wcct_add_options_quick_view_metabox() {
		WCCT_Admin_CountDown_Post_Options::quick_view_metabox_fields();
	}

	public function wcct_add_order_report_metabox() {
		WCCT_Admin_CountDown_Post_Options::wcct_report_order_metabox_fields();
	}

	/**
	 * Render options for woocommerce custom option page
	 */
	public function wcct_woocommerce_options_page() {

		if ( filter_input( INPUT_GET, 'section' ) === 'settings' ) {
			?>
            <div class="notice">
                <p><?php _e( 'Back to <a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) . '">' . WCCT_FULL_NAME . '</a> listing.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></p>
            </div>
            <div class="wrap wcct_global_option">
                <h1 class="wp-heading-inline"><?php echo __( 'Settings', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></h1>
                <div id="poststuff">
                    <div class="inside">
                        <div class="wcct_options_page_col2_wrap">
                            <div class="wcct_options_page_left_wrap">
                                <div class="postbox">
                                    <div class="inside">
                                        <div class="wcct_options_common wcct_options_settings">
                                            <div class="wcct_h20"></div>
											<?php cmb2_metabox_form( 'wcct_global_settings', 'wcct_global_options' ); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="wcct_options_page_right_wrap">
								<?php do_action( 'wcct_options_page_right_content' ); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
			<?php
		} else {
			require_once( $this->get_admin_uri() . 'includes/wcct-post-table.php' );

			$listing_sections = array(
				'null'        => null,
				'all'         => 'all',
				'running'     => 'running',
				'paused'      => 'paused',
				'schedule'    => 'schedule',
				'finished'    => 'finished',
				'deactivated' => 'deactivated',
			);
			$sections         = apply_filters( 'xlwcct_section_pages', array() );
			?>

            <style>body {
                    position: relative;
                    height: auto;
                }</style>
            <div class="wrap cmb2-options-page wcct_global_option">
				<?php
				$addon_found = false;
				if ( is_array( $sections ) && count( $sections ) > 0 ) {
					foreach ( $sections as $key => $pages ) {
						if ( filter_input( INPUT_GET, 'section' ) === $key && filter_input( INPUT_GET, 'tab' ) === 'xl-countdown-timer' ) {
							$addon_found = true;
							do_action( 'xlwcct_add_on_setting-' . $pages );
							break;
						}
					}
				}

				if ( ! $addon_found && in_array( filter_input( INPUT_GET, 'section' ), $listing_sections, true ) ) {
					$this->admin_page();
				}
				?>
            </div>
			<?php
		}
	}

	public function admin_page() {
		?>
        <h1 class="wp-heading-inline">Finale Campaigns</h1>
		<?php
		$tabs = array(
			array(
				'title' => __( 'Add New Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'link'  => admin_url( 'post-new.php?post_type=' . WCCT_Common::get_campaign_post_type_slug() ),
			),
			array(
				'title' => __( 'Settings', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'link'  => admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=settings' ),
			),
		);
		$tabs = array_merge( $tabs, apply_filters( 'xlwcct_setting_option_tabs', array() ) );
		if ( is_array( $tabs ) && count( $tabs ) > 0 ) {
			foreach ( $tabs as $key => $val ) {
				?>
                <a href="<?php echo $val['link']; ?>" class="page-title-action"><?php echo $val['title']; ?></a>
				<?php
			}
		}
		?>
        <br/>
        <br/>
		<?php WCCT_Admin_CMB2_Support::render_trigger_nav(); ?>
        <div id="poststuff">
            <div class="inside">
                <div class="inside">
                    <div class="wcct_options_page_col2_wrap">
                        <div class="wcct_options_page_left_wrap">
							<?php

							add_filter( 'wcct_default_filter_args_campaigns_admin', array( $this, 'default_orderby_date' ) );
							$table       = new WCCT_Post_Table();
							$table->data = WCCT_Common::get_post_table_data( WCCT_Admin_CMB2_Support::get_current_trigger(), WCCT_Common::get_filter_args() );

							$table->prepare_items();
							$table->display();
							?>
                        </div>
                        <div class="wcct_options_page_right_wrap">
							<?php do_action( 'wcct_options_page_right_content' ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Loading additional assets for toggle/switch button
	 */
	public function cmb2_load_toggle_button_assets() {
		wp_enqueue_style( 'cmb2_switch-css', $this->get_admin_url() . 'includes/cmb2-addons/switch/switch_metafield.css', false, WCCT_VERSION );
		//CMB2 Switch Styling
		wp_enqueue_script( 'cmb2_switch-js', $this->get_admin_url() . 'includes/cmb2-addons/switch/switch_metafield.js', '', WCCT_VERSION, true );
	}

	/**
	 * Get Plugin admin path
	 * @return string
	 */
	public function get_admin_url() {
		return plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/';
	}

	/**
	 * Hooked over `admin_enqueue_scripts`
	 * Enqueue scripts and css to wp-admin
	 */
	public function wcct_enqueue_admin_assets() {
		if ( true === WCCT_Common::wcct_valid_admin_pages() ) {
			wp_enqueue_style( 'wcct_admin-css', $this->get_admin_url() . 'assets/css/wcct-admin-style.css', false, WCCT_VERSION );
			wp_enqueue_style( 'cmb2-styles' );
		}

		if ( true === WCCT_Common::wcct_valid_admin_pages( 'single' ) ) {
			wp_enqueue_script( 'wcct_admin-js', $this->get_admin_url() . 'assets/js/wcct-admin.min.js', array(
				'jquery',
				'cmb2-scripts',
				'wcct-cmb2-conditionals',
				'wcct-cmb-tabs-js',
			), WCCT_VERSION, true );
		}

		if ( true === WCCT_Common::wcct_valid_admin_pages() ) {
			wp_register_script( 'jquery-masked-input', $this->get_admin_url() . 'assets/js/jquery.maskedinput.min.js', array( 'jquery' ), WCCT_VERSION );
			wp_enqueue_script( 'jquery-masked-input' );

			wp_register_script( 'wcct-modal', $this->get_admin_url() . 'assets/js/wcct-modal.min.js', array( 'jquery' ), WCCT_VERSION );
			wp_register_style( 'wcct-modal', $this->get_admin_url() . 'assets/css/wcct-modal.css', null, WCCT_VERSION );
			wp_enqueue_script( 'wcct-modal' );
			wp_enqueue_style( 'wcct-modal' );

			wp_enqueue_script( 'jquery' );
		}
	}

	/**
	 * Hooked over `admin_enqueue_scripts`
	 * Force remove Plugin update transient
	 */
	public function wcct_remove_plugin_update_transient() {
		if ( isset( $_GET['remove_update_transient'] ) && $_GET['remove_update_transient'] == '1' ) {
			delete_site_transient( 'update_plugins' );
		}
	}

	/**
	 * Hooked over `wcct_cmb2_modify_field_tabs`
	 * Sorts Tabs for settings
	 *
	 * @param $tabs Array of tabs
	 *
	 * @return mixed Sorted array
	 */
	public function wcct_admin_reorder_tabs( $tabs ) {
		usort( $tabs, array( $this, '_sort_by_priority' ) );

		return $tabs;
	}

	/**
	 * Hooked over `cmb2_render_wcct_html_content_field`
	 * Render Html for `wcct_html_content` Field
	 *
	 * @param $field : CMB@ Field object
	 * @param $escaped_value : Value
	 * @param $object_id object ID
	 * @param $object_type Object Type
	 * @param $field_type_object : Field Tpe Object
	 */
	public function wcct_html_content_fields( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		$conditional_value      = ( isset( $field->args['attributes']['data-conditional-value'] ) ? 'data-conditional-value="' . esc_attr( $field->args['attributes']['data-conditional-value'] ) . '"' : '' );
		$conditional_id         = ( isset( $field->args['attributes']['data-conditional-id'] ) ? ' data-conditional-id="' . esc_attr( $field->args['attributes']['data-conditional-id'] ) . '"' : '' );
		$wcct_conditional_value = ( isset( $field->args['attributes']['data-wcct-conditional-value'] ) ? 'data-wcct-conditional-value="' . esc_attr( $field->args['attributes']['data-wcct-conditional-value'] ) . '"' : '' );
		$wcct_conditional_id    = ( isset( $field->args['attributes']['data-wcct-conditional-id'] ) ? ' data-wcct-conditional-id="' . esc_attr( $field->args['attributes']['data-wcct-conditional-id'] ) . '"' : '' );
		$switch                 = '<div ' . $conditional_value . $conditional_id . $wcct_conditional_value . $wcct_conditional_id . ' class="cmb2-wcct_html" id="' . $field->args['id'] . '">';

		if ( isset( $field->args['content_cb'] ) ) {
			$switch .= call_user_func( $field->args['content_cb'] );
		} elseif ( isset( $field->args['content'] ) ) {
			$switch .= ( $field->args['content'] );
		}

		$switch .= '</div>';

		echo $switch;
	}

	/**
	 * Hooked over `postbox_classes_product_wcct_product_option_tabs`
	 * Always open for meta boxes
	 * removing closed class
	 *
	 * @param $classes
	 *
	 * @return mixed array of classes
	 */
	public function wcct_metabox_always_open( $classes ) {
		if ( ( $key = array_search( 'closed', $classes ) ) !== false ) {
			unset( $classes[ $key ] );
		}

		return $classes;
	}

	/**
	 * Hooked over 'plugin_action_links_{PLUGIN_BASENAME}' WordPress hook to add deactivate popup support
	 *
	 * @param array $links array of existing links
	 *
	 * @return array modified array
	 */
	public function wcct_plugin_actions( $links ) {
		$links['settings']   = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() ) . '" class="edit">Settings</a>';
		$links['deactivate'] .= '<i class="xl-slug" data-slug="' . WCCT_PLUGIN_BASENAME . '"></i>';

		return $links;
	}

	public function wcct_plugin_row_actions( $links, $file ) {
		if ( $file == WCCT_PLUGIN_BASENAME ) {
			$links[] = '<a href="' . add_query_arg( array(
					'utm_source'   => 'plugin-admin',
					'utm_campaign' => 'finale',
					'utm_medium'   => 'plugin_action_link',
					'utm_term'     => 'Docs',
				), 'https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/' ) . '">' . esc_html__( 'Docs', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';
			$links[] = '<a href="' . admin_url( 'admin.php?page=xlplugins&tab=support' ) . '">' . esc_html__( 'Support', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Hooked to `woocommerce_settings_tabs_array`
	 * Adding new tab in woocommerce settings
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function modify_woocommerce_settings( $settings ) {
		$settings[ WCCT_Common::get_wc_settings_tab_slug() ] = __( 'Finale: XLPlugins', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );

		return $settings;
	}

	/**
	 * Loading assets for Rules functionality
	 *
	 * @param $handle : handle current page
	 */
	public function wcct_post_wcct_load_assets( $handle ) {
		global $post_type, $woocommerce;
		$this->wcct_url = untrailingslashit( plugin_dir_url( WCCT_PLUGIN_FILE ) );
		wp_enqueue_style( 'wcct-admin-all', $this->get_admin_url() . '/assets/css/wcct-admin-all.css' );
		if ( ( $handle == 'post-new.php' || $handle == 'post.php' || $handle == 'edit.php' ) && ( $post_type == WCCT_Common::get_campaign_post_type_slug() || $post_type == 'countdown' || apply_filters( 'wcct_addons', false ) ) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'wcct_flicons', $this->get_admin_url() . 'assets/fonts/flicon.css' );
			wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );
			wp_enqueue_style( 'wcct-admin-app', $this->get_admin_url() . 'assets/css/wcct-admin-app.css' );
			wp_enqueue_style( 'xl-chosen-css', $this->get_admin_url() . 'assets/css/chosen' . $suffix . '.css' );
			wp_register_script( 'xl-chosen', $this->get_admin_url() . 'assets/js/chosen/chosen.jquery.min.js', array( 'jquery' ), WCCT_VERSION );
			wp_register_script( 'xl-ajax-chosen', $this->get_admin_url() . 'assets/js/chosen/ajax-chosen.jquery.min.js', array(
				'jquery',
				'xl-chosen',
			), WCCT_VERSION );
			wp_enqueue_script( 'xl-ajax-chosen' );
			wp_enqueue_script( 'wcct-admin-app', $this->get_admin_url() . 'assets/js/wcct-admin-app.min.js', array(
				'jquery',
				'jquery-ui-datepicker',
				'underscore',
				'backbone',
				'xl-ajax-chosen',
			) );
			wp_enqueue_script( 'wcct-countdown', $this->wcct_url . '/assets/js/jquery.countdown.min.js', array( 'jquery' ), '2.2.0', true );

			$data = array(
				'ajax_nonce'            => wp_create_nonce( 'wcctaction-admin' ),
				'plugin_url'            => plugin_dir_url( WCCT_PLUGIN_FILE ),
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
				'ajax_chosen'           => wp_create_nonce( 'json-search' ),
				'search_products_nonce' => wp_create_nonce( 'search-products' ),
				'text_or'               => __( 'or', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'text_apply_when'       => __( 'Apply this Campaign when these conditions are matched', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'remove_text'           => __( 'Remove', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'wcct_coupon_nonce'     => wp_create_nonce( 'wcct-apply_coupon' ),
				'admin_url'             => admin_url( 'post.php' ),
			);
			wp_localize_script( 'wcct-admin-app', 'WCCTParams', $data );
		}
	}

	public function wcct_post_publish_box() {
		global $post;
		if ( WCCT_Common::get_campaign_post_type_slug() != $post->post_type ) {
			return;
		}

		$deactivation_url = wp_nonce_url( add_query_arg( array(
			'wcct_action' => 'wcct-post-deactivate',
			'postid'      => get_the_ID(),
			'post'        => get_the_ID(),
			'action'      => 'edit',
		), network_admin_url( 'post.php' ) ), 'wcct-post-deactivate' );

		$trigger_status = 'Activated (<a href="' . $deactivation_url . '">deactivate</a>)';
		if ( $post->post_status == 'trash' || $post->post_status == 'wcctdisabled' ) {
			$deactivation_url = wp_nonce_url( add_query_arg( array(
				'wcct_action' => 'wcct-post-activate',
				'postid'      => get_the_ID(),
				'post'        => get_the_ID(),
				'action'      => 'edit',
			), network_admin_url( 'post.php' ) ), 'wcct-post-activate' );
			$trigger_status   = 'Deactivated (<a href="' . $deactivation_url . '">activate</a>)';
		}
		if ( $post->post_date ) {
			$date_format  = get_option( 'date_format' );
			$date_format  = $date_format ? $date_format : 'M d, Y';
			$publish_date = date( $date_format, strtotime( $post->post_date ) );
		}
		if ( $post->post_status != 'auto-draft' ) {
			?>
            <div class="misc-pub-section misc-pub-post-status wcct_always_show">
                Status: <span id="post-status-display"><?php echo $trigger_status; ?></span>
            </div>
			<?php
		}
		if ( $post->post_date ) {
			?>
            <div class="misc-pub-section curtime misc-pub-curtime wcct_always_show">
                <span id="timestamp">Added on: <b><?php echo $publish_date; ?></b></span>
            </div>
			<?php
		}

		$timezone_format = _x( 'Y-m-d H:i:s', 'timezone date format' );
		?>
        <div class="misc-pub-section curtime misc-pub-curtime wcct_always_show">
<span class=""><i style="color:#82878c"
                  class="dashicons dashicons-clock"></i> Current Time: <b> <?php echo date_i18n( $timezone_format ) . '(' . WCCT_Common::wc_timezone_string() . ')'; ?></b></span>
        </div>

		<?php
	}

	/*     * ******** Functions For Rules Functionality Starts ************************************* */

	public function wcct_edit_form_top() {
		global $post;
		if ( WCCT_Common::get_campaign_post_type_slug() != $post->post_type ) {
			return;
		}
		?>
        <div class="notice">
            <p><?php _e( 'Back to <a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) . '">' . WCCT_FULL_NAME . '</a> settings.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></p>
        </div>
		<?php
	}

	public function post_updated_messages( $messages ) {
		global $post;

		$messages[ WCCT_Common::get_campaign_post_type_slug() ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => sprintf( __( 'Countdown timer updated.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) ),
			2  => __( 'Custom field updated.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			3  => __( 'Custom field deleted.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			4  => sprintf( __( 'Countdown timer updated. ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Trigger restored to revision from %s', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( 'Countdown timer updated. ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) ),
			7  => sprintf( __( 'Trigger saved. ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) ),
			8  => sprintf( __( 'Countdown timer updated. ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) ),
			9  => sprintf( __( 'Trigger scheduled for: <strong>%1$s</strong>.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) ) ),
			10 => __( 'Trigger draft updated.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			11 => sprintf( __( 'Countdown timer updated. ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) ),
		);

		return $messages;
	}

	public function maybe_activate_post() {
		if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'wcct-post-activate' || ( isset( $_GET['wcct_action'] ) && $_GET['wcct_action'] == 'wcct-post-activate' ) ) ) {
			if ( wp_verify_nonce( $_GET['_wpnonce'], 'wcct-post-activate' ) ) {

				$postID  = filter_input( INPUT_GET, 'postid' );
				$section = filter_input( INPUT_GET, 'trigger' );

				if ( $postID ) {
					wp_update_post( array(
						'ID'          => $postID,
						'post_status' => 'publish',
					) );
					WCCT_Common::wcct_maybe_clear_cache();

					if ( isset( $_GET['wcct_action'] ) ) {
						wp_redirect( admin_url( 'post.php?post=' . $_GET['postid'] . '&action=edit' ) );
					} else {
						$redirect_url = admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=' . $section );
						if ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) {
							$redirect_url = add_query_arg( array(
								'paged' => $_GET['paged'],
							), $redirect_url );
						}

						wp_safe_redirect( $redirect_url );
					}
				}
			} else {
				die( __( 'Unable to Activate', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}
		}
	}

	public function maybe_deactivate_post() {
		if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'wcct-post-deactivate' || ( isset( $_GET['wcct_action'] ) && $_GET['wcct_action'] == 'wcct-post-deactivate' ) ) ) {

			if ( wp_verify_nonce( $_GET['_wpnonce'], 'wcct-post-deactivate' ) ) {

				$postID  = filter_input( INPUT_GET, 'postid' );
				$section = filter_input( INPUT_GET, 'trigger' );
				if ( $postID ) {

					wp_update_post( array(
						'ID'          => $postID,
						'post_status' => WCCT_SHORT_SLUG . 'disabled',
					) );
					WCCT_Common::wcct_maybe_clear_cache();

					if ( isset( $_GET['wcct_action'] ) ) {
						wp_redirect( admin_url( 'post.php?post=' . $_GET['postid'] . '&action=edit' ) );
					} else {
						$redirect_url = admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=' . $section );
						if ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) {
							$redirect_url = add_query_arg( array(
								'paged' => $_GET['paged'],
							), $redirect_url );
						}

						wp_safe_redirect( $redirect_url );
					}
				}
			} else {
				die( __( 'Unable to Deactivate', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}
		}
	}

	public function save_menu_order( $post_id, $post = null ) {

		//Check it's not an auto save routine
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		//Perform permission checks! For example:
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( class_exists( 'XL_Transient' ) ) {
			$xl_transient_obj = XL_Transient::get_instance();
		}

		//Check your nonce!
		//If calling wp_update_post, unhook this function so it doesn't loop infinitely
		remove_action( 'save_post_' . WCCT_Common::get_campaign_post_type_slug(), array( $this, 'save_menu_order' ), 99, 2 );
		if ( $post != null ) {
			if ( $post && $post->post_type == WCCT_Common::get_campaign_post_type_slug() ) {
				if ( isset( $_POST['_wcct_data_menu_order'] ) ) {
					wp_update_post( array(
						'ID'         => $post_id,
						'post_name'  => sanitize_title( $_POST['post_title'] ) . '_' . $post_id,
						'menu_order' => $_POST['_wcct_data_menu_order'],
					) );
				}

				if ( is_array( $_POST ) && isset( $_POST['action'] ) && 'editpost' == $_POST['action'] ) {
					// don't delete transients as this is a single post save call
				} elseif ( class_exists( 'XL_Transient' ) ) {
					$xl_transient_obj->delete_all_transients( 'finale' );
				}

				if ( ! wp_next_scheduled( 'wcct_schedule_reset_state', array( $post_id ) ) ) {
					wp_schedule_single_event( time() + 5, 'wcct_schedule_reset_state', array( $post_id ) );
				}
			}
		}

		// re-hook this function - commented because firing twice when order save
		//      add_action( 'save_post_' . WCCT_Common::get_campaign_post_type_slug(), array( $this, 'save_menu_order' ), 99, 2 );
	}

	public function delete_product_taxonomy_ids_meta( $post_id ) {
		delete_post_meta( $post_id, '_wcct_product_taxonomy_term_ids' );
	}

	public function delete_product_taxonomy_ids_meta_quick_edit( $flag, $tax, $post_type ) {
		if ( isset( $_POST['post_ID'] ) && ! empty( $_POST['post_ID'] ) && 'product' === $post_type ) {
			delete_post_meta( $_POST['post_ID'], '_wcct_product_taxonomy_term_ids' );
		}

		return $flag;
	}

	/**
	 * removing extra meta boxes on page, added by 3rd party plugin etc
	 *
	 * @param $post_type
	 * @param $cur_context
	 * @param $post
	 *
	 * @global $wp_meta_boxes
	 *
	 */
	public function wcct_do_meta_boxes( $post_type, $cur_context, $post ) {
		global $wp_meta_boxes;
		if ( 'wcct_countdown' === $post_type ) {
			$allowed_side_metaboxes   = array(
				'wcct_campaign_quick_view_settings',
				'wcct_campaign_shortcode_settings',
				'wcct_campaign_menu_order_settings',
			);
			$allowed_normal_metaboxes = array( 'wcct_campaign_settings', 'wcct_rules' );
			if ( isset( $wp_meta_boxes['wcct_countdown']['side']['high'] ) ) {
				unset( $wp_meta_boxes['wcct_countdown']['side']['high'] );
			}
			if ( isset( $wp_meta_boxes['wcct_countdown']['advanced'] ) ) {
				unset( $wp_meta_boxes['wcct_countdown']['advanced'] );
			}
			if ( isset( $wp_meta_boxes['wcct_countdown']['normal']['low'] ) ) {
				unset( $wp_meta_boxes['wcct_countdown']['normal']['low'] );
			}
			if ( is_array( $wp_meta_boxes['wcct_countdown']['side']['low'] ) && count( $wp_meta_boxes['wcct_countdown']['side']['low'] ) > 0 ) {
				$meta_box_keys = array_keys( $wp_meta_boxes['wcct_countdown']['side']['low'] );
				if ( is_array( $meta_box_keys ) && count( $meta_box_keys ) > 0 ) {
					foreach ( $meta_box_keys as $metabox_id ) {
						if ( ! in_array( $metabox_id, $allowed_side_metaboxes ) ) {
							unset( $wp_meta_boxes['wcct_countdown']['side']['low'][ $metabox_id ] );
						}
					}
				}
			}
			$meta_box_keys = array();
			if ( is_array( $wp_meta_boxes['wcct_countdown']['normal']['high'] ) && count( $wp_meta_boxes['wcct_countdown']['normal']['high'] ) > 0 ) {
				$meta_box_keys = array_keys( $wp_meta_boxes['wcct_countdown']['normal']['high'] );
				if ( is_array( $meta_box_keys ) && count( $meta_box_keys ) > 0 ) {
					foreach ( $meta_box_keys as $metabox_id ) {
						if ( ! in_array( $metabox_id, $allowed_normal_metaboxes ) ) {
							unset( $wp_meta_boxes['wcct_countdown']['normal']['high'][ $metabox_id ] );
						}
					}
				}
			}
		}
	}

	public function wcct_footer_css() {
		if ( WCCT_Common::wcct_valid_admin_pages() ) {
			?>
            <style>
                .wrap.woocommerce p.submit {
                    display: none;
                }

                #WCCT_MB_ajaxContent ol {
                    font-weight: bold;
                }
            </style>
			<?php
		}
	}

	public function wcct_add_mergetag_text() {
		if ( true !== WCCT_Common::wcct_valid_admin_pages( 'single' ) ) {
			return;
		}

		$date_obj = new DateTime();
		$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
		$date_cutoff = $date_obj->modify( '- 5 minutes' )->format( 'h:i a' );
		$campaign_id = get_the_ID();
		if ( false === $campaign_id ) {
			$campaign_id = '{$campaign_id}';
		}

		$any_shortcodes = apply_filters( 'wcct_any_shortcodes_for_all_campaigns', array(
			'timer' => array(
				'label'     => 'Timer',
				'shortcode' => '[finale_countdown_timer skip_rules="no"]',
			),
			'bar'   => array(
				'label'     => 'Inventory Bar',
				'shortcode' => '[finale_counter_bar skip_rules="no"]',
			),
			'text'  => array(
				'label'     => 'Custom Text',
				'shortcode' => '[finale_custom_text skip_rules="no"]',
			),
		) );

		$campaign_shortcodes = apply_filters( 'wcct_campaign_shortcodes_for_this_campaign', array(
			'timer' => array(
				'label'     => 'Timer',
				'shortcode' => '[finale_countdown_timer campaign_id="' . $campaign_id . '"]',
			),
			'bar'   => array(
				'label'     => 'Inventory Bar',
				'shortcode' => '[finale_counter_bar campaign_id="' . $campaign_id . '"]',
			),
			'text'  => array(
				'label'     => 'Custom Text',
				'shortcode' => '[finale_custom_text campaign_id="' . $campaign_id . '"]',
			),
		) );

		$price_shortcodes = apply_filters( 'wcct_price_shortcodes_for_campaign', array(
			'price_html'    => array(
				'label'     => 'Price HTML',
				'shortcode' => '[finale_product_price_html product_id="..."]',
			),
			'sale_price'    => array(
				'label'     => 'Sale Price',
				'shortcode' => '[finale_product_sale_price product_id="..." format="yes"]',
			),
			'regular_price' => array(
				'label'     => 'Regular Price',
				'shortcode' => '[finale_product_regular_price product_id="..." format="yes"]',
			),
		) );

		?>

        <div class='' id="wcct_shortcode_help_box" style="display: none;">

            <h3>Shortcode</h3>
            <div style="font-size: 1.1em; margin: 5px;">To show elements for <strong>this campaign</strong> use
                these shortcodes</i> </div>
            <table class="table widefat">
                <tbody>

				<?php
				foreach ( $campaign_shortcodes as $shortcode ) {
					?>
                    <tr>
                        <td>
							<?php echo $shortcode['label']; ?>
                        </td>
                        <td>
                            <input type="text" style="width: 75%;" readonly onClick="this.select()"
                                   value='<?php echo $shortcode['shortcode']; ?>'/>
                        </td>

                    </tr>
					<?php
				}
				?>

                </tbody>
            </table>
            <br/>


            <div style="font-size: 1.1em; margin: 5px; margin-bottom: 10px;"> To show elements for <strong>any
                    campaign</strong> use these shortcodes. Usually used when you want to display Elements in Grids
                Or Page Builders. <strong>product_id</strong> must be available on the page.
            </div>
            <table class="table widefat">
                <tbody>

				<?php
				foreach ( $any_shortcodes as $shortcode ) {
					?>
                    <tr>
                        <td>
							<?php echo $shortcode['label']; ?>
                        </td>
                        <td>
                            <input type="text" style="width: 75%;" readonly onClick="this.select()"
                                   value='<?php echo $shortcode['shortcode']; ?>'/>
                        </td>

                    </tr>
					<?php
				}
				?>

                </tbody>
            </table>
            <br/>


            <div style="font-size: 1.1em; margin: 5px; margin-bottom: 10px;">To show dynamic price of a particular product (set by Finale campaign) use these shortcodes. Usually used when you want
                to show product price on custom landing pages. <strong>product_id</strong> is a mandatory parameter.
            </div>
            <table class="table widefat">
                <tbody>

				<?php
				foreach ( $price_shortcodes as $shortcode ) {
					?>
                    <tr>
                        <td>
							<?php echo $shortcode['label']; ?>
                        </td>
                        <td>
                            <input type="text" style="width: 75%;" readonly onClick="this.select()"
                                   value='<?php echo $shortcode['shortcode']; ?>'/>
                        </td>

                    </tr>
					<?php
				}
				?>

                </tbody>
            </table>
            <div class="table_bottom_note"> Note: In case of variable products, if you want to show dynamic price of a <strong>specific variation</strong> pass <strong>variation ID</strong> in
                product_id parameter
            </div>
            <br/>

            <h3>Other Attributes:</h3>

            <p>
                <strong>product_id</strong>: to get respective Element for the specific product, use product_id
                attribute. Usually used when you are building landing page of your specific product.
                <br/>
            </p>


            <p>
                <strong>skip_rules</strong>: to skip the rule check while displaying Element.
                <br/>Example: skip_rules="no" or skip_rules="yes" (preferred)

            </p>
            <p>
                <strong>debug</strong>: Only used for the purpose of troubleshooting . <br/>
                In case you are unable to see output of your shortcode then set debug ='yes' and system will try to
                find out why shortcode is not rendering the output.
            <p/>


        </div>

        <div style="display:none;" class="wcct_tb_content" id="wcct_merge_tags_help">
            <br/>
            <div class="regex_help_text">
                Copy & Paste One or more merge tags to show advance messages under .
            </div>
            <br/>

            <div style="font-size: 1.3em; margin: 5px;">Campaign Timings Merge Tags:</div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <i>Shows Campaign Start Date/Time</i>
                    </td>
                    <td>
                        <i>{{campaign_start_date}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        <i>Shows Campaign End Date/Time</i>
                    </td>
                    <td>
                        <i>{{campaign_end_date}}</i>
                    </td>

                </tr>


                </tbody>
            </table>
            <div class="wcct_help_table_note">Note: Above two merge tags outputs date in 'Month Date' format,
                Example: <?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="M j"}}' ) ); ?> </div>
            <br/>


            <div style="font-size: 1.3em; margin: 5px;">Product Pricing Merge Tags:</div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <i>Shows Product Price HTML</i>
                    </td>
                    <td>
                        <i>{{price_html format="yes"}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        <i>Shows Product Regular Price</i>
                    </td>
                    <td>
                        <i>{{regular_price format="yes"}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        <i>Shows Product Sale Price</i>
                    </td>
                    <td>
                        <i>{{sale_price format="yes"}}</i>
                    </td>

                </tr>


                </tbody>
            </table>
            <br/>
            <div style="font-size: 1.3em; margin: 5px;"><strong>{{current_date}}</strong> accepts parameters <i>adjustment,
                    cutoff & format.</i></div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="F j"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="F j"}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="F j" adjustment="+1 days" cutoff="' . $date_cutoff . '"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="F j" adjustment="+1 days" cutoff="<?php echo $date_cutoff; ?>
                            "}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date adjustment="+4 days" format="F j"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date adjustment="+4 days" format="F j"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date adjustment="+5 days" cutoff="' . $date_cutoff . '" format="F j"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date adjustment="+5 days" cutoff="<?php echo $date_cutoff; ?>" format="F
                            j"}}</i>
                    </td>
                </tr>


                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date adjustment="+1 day" exclude_days="sunday,saturday" exclude_dates="2018-03-30" format="F j"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date adjustment="+1 day" exclude_days="sunday,saturday"
                            exclude_dates="2018-03-30" format="F j"}}</i>
                    </td>
                </tr>


                </tbody>
            </table>
            <div style="font-style: italic; margin-top: 5px; margin-bottom: 20px;">
                Note: Scroll down to bottom to know about how you can change date formats.
            </div>

            <br/>
            <div style="font-size: 1.3em; margin: 5px;"><strong>{{current_day}}</strong> accepts parameters <i>adjustment
                    & cutoff.</i></div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        Want it by
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_day adjustment="+4 days"}}' ) ); ?></i>?
                        Order Now
                    </td>
                    <td>
                        Want it by <i>{{current_day adjustment="+4 days"}}</i>? Order Now
                    </td>
                </tr>
                </tbody>
            </table>
            <br/> <br/>
            <div style="font-size: 1.3em; margin: 5px;"><strong>{{today}}</strong> accepts parameter <i>cutoff</i>.
            </div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        Want it
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{today}}' ) ); ?></i>?
                        Order Now
                    </td>
                    <td>
                        Want it <i>{{today}}</i>? Order Now
                    </td>
                </tr>
                <tr>
                    <td>
                        Want it
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{today cutoff="' . $date_cutoff . '"}}' ) ); ?></i>?
                        Order Now
                    </td>
                    <td>
                        Want it <i>{{today cutoff="<?php echo $date_cutoff; ?>"}}</i>? Order Now
                    </td>
                </tr>
                </tbody>
            </table>
            <br/><br/>
            <div style="font-size: 1.3em; margin: 5px;"><strong>{{custom_countdown_timer}}</strong> accepts <i>adjustment, cutoff, exclude_days & exclude_dates</i></div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{custom_countdown_timer cutoff="4:00 pm" exclude_days="sunday,saturday"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{custom_countdown_timer cutoff="4:00 pm" exclude_days="sunday,saturday"}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{custom_countdown_timer cutoff="12:00 pm" adjustment="+1 days" exclude_days="sunday,saturday"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{custom_countdown_timer cutoff="12:00 pm" adjustment="+1 days" exclude_days="sunday,saturday"}}</i>

                    </td>

                </tr>
                </tbody>
            </table>
            <br/><br/>
            <div style="font-size: 1.3em; margin: 5px;"><strong>{{countdown_timer}}</strong> outputs the countdown timer with basic skin of current campaign end date time</div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        Want it <?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{today cutoff="5:20 pm" exclude_days="sunday,saturday" exclude_dates="2018-03-30"}}' ) ); ?>,
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date exclude_days="sunday,saturday" exclude_dates="2018-03-30" format="F j" cutoff="5:20 pm"}}' ) ); ?></i>?
                        Order within
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?></i>
                    </td>
                    <td>
                        Want it <i>{{today cutoff="5:20 pm" exclude_days="sunday,saturday"
                            exclude_dates="2018-03-30"}}</i>, <i>{{current_date cutoff="5:20 pm"
                            exclude_days="sunday,saturday" exclude_dates="2018-03-30" format="F j"}}</i>?
                        Order within<i>{{countdown_timer}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        Next business day shipping if you order within
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?></i>
                    </td>
                    <td>
                        Next business day shipping if you order within {{countdown_timer}}<i>

                    </td>

                </tr>
                <tr>
                    <td>
                        Independence Day Special Free Shipping for Next
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?></i>
                    </td>
                    <td>
                        Independence Day Special Free Shipping for Next <i>{{countdown_timer}}</i>
                    </td>


                </tr>
                <tr>

                    <td>
                        Ships today if you order in next
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?></i>
                    </td>
                    <td>

                        Ships today if you order in next <i>{{countdown_timer}}</i>
                    </td>
                </tr>

                <tr>
                    <td>
                        Order in the next
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?></i>
                        and get it by
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date exclude_days="sunday,saturday" exclude_dates="2018-03-30" adjustment="+4 days"}}' ) ); ?></i>
                    </td>
                    <td>
                        Order in the next <i>{{countdown_timer}}</i> and get it by <i>{{current_date adjustment="+4
                            days" exclude_days="sunday,saturday" exclude_dates="2018-03-30" }}</i>
                    </td>

                </tr>

                <tr>
                    <td>
                        Order in the next
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?> </i>and
                        get it by
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_time adjustment="+24 hours"}}' ) ); ?></i>
                        Tomorrow
                    </td>
                    <td>
                        Order in the next <i>{{countdown_timer}}</i> and get it by <i>{{current_time adjustment="+24 hours"}}</i> Tomorrow

                    </td>

                </tr>

                <tr>
                    <td>
                        Want it tomorrow,
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date adjustment="+1 days" format="F j" exclude_days="sunday,saturday" exclude_dates="2018-03-30"}}' ) ); ?> </i>?
                        Order within
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?></i>
                        and choose One-Day Shipping
                        at checkout.
                    </td>
                    <td>
                        Want it tomorrow, <i>{{current_date adjustment="+1 days" format="F j"
                            exclude_days="sunday,saturday" exclude_dates="2018-03-30"}}</i>? Order in the
                        next
                        <i>{{countdown_timer}}</i> and choose One-Day
                        Shipping at checkout.
                    </td>

                </tr>


                </tbody>
            </table>
            <br/><br/>
            <div style="font-size: 1.3em; margin: 5px;"><strong>Date Formats</strong></div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="F j"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="F j"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="F jS"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="F jS"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="j M"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="j M"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="jS M"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="jS M"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="M j"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="M j"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="M jS"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="M jS"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="M j Y"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="M j Y"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="M jS Y"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="M jS Y"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="d/m/Y"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="d/m/Y"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="d-m-Y"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="d-m-Y"}}</i>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div style="display:none;" class="wcct_tb_content" id="wcct_merge_tags_invenotry_bar_help">
            <p>Here are the merge tags which you can use.</p>
            <p>
                <em><strong>{{total_units}}</strong></em>: Outputs total quantity to be sold during the campaign.
                Example, Total Units: 10.<br/>
                <em><strong>{{sold_units}}</strong></em>: Outputs total quantity sold during the campaign. Example,
                Currently Sold: 5.<br/>
                <em><strong>{{remaining_units}}</strong></em>: Outputs total quantity left during the campaign.
                Example, Currently Left: 5.<br/><br/>
                <em><strong>{{total_units_price}}</strong></em>: Outputs total price value of total quantity to be
                sold during the campaign. Example, Total Funds To Be Raised: $100.<br/>
                <em><strong>{{sold_units_price}}</strong></em>: Outputs price value of quantity sold during the
                campaign. Example, Funds To Raised Till Now: $50.<br/><br/>
                <em><strong>{{sold_percentage}}</strong></em>: Outputs percentage of quantity sold during the
                campaign. Example, Campaign Goal: 51% achieved.<br/>
                <em><strong>{{remaining_percentage}}</strong></em>: Outputs percentage of remaining quantity left
                during the campaign. Example, Campaign Goal: 49% left.
            </p>
            <div style="font-size: 1.3em; margin: 5px;">Campaign Timings Merge Tags:</div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <i>Shows Campaign Start Date/Time</i>
                    </td>
                    <td>
                        <i>{{campaign_start_date}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        <i>Shows Campaign End Date/Time</i>
                    </td>
                    <td>
                        <i>{{campaign_end_date}}</i>
                    </td>

                </tr>


                </tbody>
            </table>
            <div class="wcct_help_table_note">Note: Above two merge tags outputs date in 'Month Date' format,
                Example: <?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="M j"}}' ) ); ?> </div>
            <br/>


            <div style="font-size: 1.3em; margin: 5px;">Product Pricing Merge Tags:</div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <i>Shows Product Price HTML</i>
                    </td>
                    <td>
                        <i>{{price_html format="yes"}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        <i>Shows Product Regular Price</i>
                    </td>
                    <td>
                        <i>{{regular_price format="yes"}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        <i>Shows Product Sale Price</i>
                    </td>
                    <td>
                        <i>{{sale_price format="yes"}}</i>
                    </td>

                </tr>


                </tbody>
            </table>
            <br/>
            <p>More Date/ Time related merge tags which you can use.</p>
            <div style="font-size: 1.3em; margin: 5px;"><strong>{{current_date}}</strong> accepts parameters <i>adjustment,
                    cutoff & format.</i></div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="F j"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="F j"}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="F j" adjustment="+1 days" cutoff="' . $date_cutoff . '"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="F j" adjustment="+1 days" cutoff="<?php echo $date_cutoff; ?>
                            "}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date adjustment="+4 days" format="F j"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date adjustment="+4 days" format="F j"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date adjustment="+5 days" cutoff="' . $date_cutoff . '" format="F j"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date adjustment="+5 days" cutoff="<?php echo $date_cutoff; ?>" format="F
                            j"}}</i>
                    </td>
                </tr>


                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date adjustment="+1 day" exclude_days="sunday,saturday" exclude_dates="2018-03-30" format="F j"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date adjustment="+1 day" exclude_days="sunday,saturday"
                            exclude_dates="2018-03-30" format="F j"}}</i>
                    </td>
                </tr>


                </tbody>
            </table>
            <div style="font-style: italic; margin-top: 5px; margin-bottom: 20px;">
                Note: Scroll down to bottom to know about how you can change date formats.
            </div>

            <br/>
            <div style="font-size: 1.3em; margin: 5px;"><strong>{{current_day}}</strong> accepts parameters <i>adjustment
                    & cutoff.</i></div>
            <br/>


            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        Want it by
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_day adjustment="+4 days"}}' ) ); ?></i>?
                        Order Now
                    </td>
                    <td>
                        Want it by <i>{{current_day adjustment="+4 days"}}</i>? Order Now
                    </td>
                </tr>
                </tbody>
            </table>
            <br/> <br/>
            <div style="font-size: 1.3em; margin: 5px;"><strong>{{today}}</strong> accepts parameter <i>cutoff</i>.
            </div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        Want it
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{today}}' ) ); ?></i>?
                        Order Now
                    </td>
                    <td>
                        Want it <i>{{today}}</i>? Order Now
                    </td>
                </tr>
                <tr>
                    <td>
                        Want it
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{today cutoff="' . $date_cutoff . '"}}' ) ); ?></i>?
                        Order Now
                    </td>
                    <td>
                        Want it <i>{{today cutoff="<?php echo $date_cutoff; ?>"}}</i>? Order Now
                    </td>
                </tr>
                </tbody>
            </table>
            <br/><br/>
            <div style="font-size: 1.3em; margin: 5px;"><strong>{{countdown_timer}}</strong> accepts parameters <i>adjustment,
                    cutoff & format.</i></div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        Want
                        it <?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{today cutoff="5:20 pm" exclude_days="sunday,saturday" exclude_dates="2018-03-30"}}' ) ); ?>
                        ,
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date exclude_days="sunday,saturday" exclude_dates="2018-03-30" format="F j" cutoff="5:20 pm"}}' ) ); ?> </i>?
                        Order within
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?></i>
                    </td>
                    <td>
                        Want it <i>{{today cutoff="5:20 pm" exclude_days="sunday,saturday"
                            exclude_dates="2018-03-30"}}</i>, <i>{{current_date cutoff="5:20 pm"
                            exclude_days="sunday,saturday" exclude_dates="2018-03-30" format="F j"}}</i>?
                        Order within<i>{{countdown_timer}}</i>
                    </td>

                </tr>
                <tr>
                    <td>
                        Next business day shipping if you order within
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?></i>
                    </td>
                    <td>
                        Next business day shipping if you order within {{countdown_timer}}<i>

                    </td>

                </tr>
                <tr>
                    <td>
                        Independence Day Special Free Shipping for Next
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?></i>
                    </td>
                    <td>
                        Independence Day Special Free Shipping for Next <i>{{countdown_timer}}</i>
                    </td>


                </tr>
                <tr>

                    <td>
                        Ships today if you order in next
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?></i>
                    </td>
                    <td>

                        Ships today if you order in next <i>{{countdown_timer}}</i>
                    </td>
                </tr>

                <tr>
                    <td>
                        Order in the next
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?></i>
                        and get it by
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date exclude_days="sunday,saturday" exclude_dates="2018-03-30" adjustment="+4 days"}}' ) ); ?></i>
                    </td>
                    <td>
                        Order in the next <i>{{countdown_timer}}</i> and get it by <i>{{current_date adjustment="+4
                            days" exclude_days="sunday,saturday" exclude_dates="2018-03-30" }}</i>
                    </td>

                </tr>

                <tr>
                    <td>
                        Order in the next
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?> </i>and
                        get it by
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_time adjustment="+24 hours"}}' ) ); ?></i>
                        Tomorrow
                    </td>
                    <td>
                        Order in the next <i>{{countdown_timer}}</i> and get it by <i>{{current_time adjustment="+24 hours"}}</i> Tomorrow

                    </td>

                </tr>

                <tr>
                    <td>
                        Want it tomorrow,
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date adjustment="+1 days" format="F j" exclude_days="sunday,saturday" exclude_dates="2018-03-30"}}' ) ); ?> </i>?
                        Order within
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{wcct_countdown_timer_admin}}' ) ); ?></i>
                        and choose One-Day Shipping
                        at checkout.
                    </td>
                    <td>
                        Want it tomorrow, <i>{{current_date adjustment="+1 days" format="F j"
                            exclude_days="sunday,saturday" exclude_dates="2018-03-30"}}</i>? Order in the
                        next
                        <i>{{countdown_timer}}</i> and choose One-Day
                        Shipping at checkout.
                    </td>

                </tr>


                </tbody>
            </table>

            <br/><br/>
            <div style="font-size: 1.3em; margin: 5px;"><strong>Date Formats</strong></div>
            <br/>
            <table class="table widefat">
                <thead>
                <tr>
                    <td>Output text</td>
                    <td>Input text</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="F j"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="F j"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="F jS"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="F jS"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="j M"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="j M"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="jS M"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="jS M"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="M j"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="M j"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="M jS"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="M jS"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="M j Y"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="M j Y"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="M jS Y"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="M jS Y"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="d/m/Y"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="d/m/Y"}}</i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i><?php echo do_shortcode( WCCT_Merge_Tags::maybe_parse_merge_tags( '{{current_date format="d-m-Y"}}' ) ); ?></i>
                    </td>
                    <td>
                        <i>{{current_date format="d-m-Y"}}</i>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div style="display:none;" class="wcct_tb_content" id="wcct_events_help">
            <br/>
            <div class="regex_help_text">You can use Events to automate the following tasks:</div>
            <ol>1. Bump Sale: Increase Regular Price Of Product on Every X sales</ol>
            <p align="center"><img src="//storage.googleapis.com/xl-finale/1.bump-sale.png"/></p>
            <ol>2. Early Bird Discount: Decrease Discounts By Y% on Every X Sales. For Example: Decrease Discount by 10% on every 10 sales.</ol>
            <p align="center"><img src="//storage.googleapis.com/xl-finale/2.early-bird-discount.png"/></p>
            <ol>3. Time Sensitive Prices: Regular Prices Increase After Every Few Hours.</ol>
            <p align="center"><img src="//storage.googleapis.com/xl-finale/3.time-sensitive-prices.png"/></p>
            <ol>4. Time Sensitive Discounts: Increase Discounts by X% After Every Few Hours.</ol>
            <p align="center"><img src="//storage.googleapis.com/xl-finale/4.time-sensitive-discounts.png"/></p>
            <ol>5. Inventory Bump Based on Units: Increase Inventory by X units when Y Unit is left for sale.</ol>
            <p align="center"><img src="//storage.googleapis.com/xl-finale/5.inventory-bump-by-units.png"/></p>
            <ol>6. Inventory Bump Based on Time:Increase Inventory by X units when Y hrs is left for sale.</ol>
            <p align="center"><img src="//storage.googleapis.com/xl-finale/6.inventory-bump-by-time.png"/></p>
        </div>
        <div style="display:none;" class="wcct_tb_content" id="wcct_inventory_sold_unit_help">
            <br/>
            <p>We understand that this may be tricky option to grasp but carefully read the instructions below to
                understand how each of these options play up for Recurring & One Time Campaigns.</p>
            <h3>Overall Campaign</h3>
            <p><strong>Recurring Campaign</strong><br/>Say you have 'X' units to sell and set up recurring
                campaigns. It may be the case that your units don't entirely sell in the first recurrence. You would
                want the campaign to re-start but still carry forward total sold units during all the previous
                recurrences. If that's the case, set 'Calculate Sold Units' to 'Overall Campaign'.</p>
            <p><strong>One Time Campaign</strong><br/>Say you have 'X' units to sell and set up one time campaigns.
                It may be the case that your units don't sell and you want to extend the date of the campaign. And
                include previously sold units in calculation. If that's the case, set 'Calculate Sold Units' to
                'Overall Campaign'.</p>
            <br/>
            <h3>Current Occurrence</h3>
            <p><strong>Recurring Campaign</strong><br/>Say you are a pizza shop which has the capacity to serve 'X'
                pizzas daily. And you have set up a recurring schedule. You would want the campaign to re-start
                daily but also reset sold units for latest recurrence. In this case, you would set 'Calculate Sold
                Units' to 'Current Occurrence'.</p>
            <p><strong>One Time Campaign</strong><br/>Say you have 'X' units to sell and set up one time
                campaigns.It may be the case that you want to extend the time of the campaign. But want to reset the
                previously sold units. If that's the case, set 'Calculate Sold Units' to 'Current Occurrence'.</p>

        </div>
        <div style="display:none;" class="wcct_tb_content" id="wcct_inventory_out_of_stock_help">
            <br/>
            <p>Finale dynamically changes the amount of units that can be sold during the Campaign based on the Inventory settings.</p>
            <p>Some of your products can be Out-of-Stock before the start of a Campaign or can go Out-of-Stock during the Campaign.</p>
            <p>Keep this setting to <strong><u>NO</u></strong> if you don't have the ability to fulfill the Out-of-Stock products.</p>
            <p>Keep this setting to <strong><u>YES</u></strong> if you can fulfill the Out-of-Stock products.</p>
        </div>
		<?php
	}

	public function wcct_maybe_remove_rule_type_data() {
		global $post;
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		$deprecated_rule_types = array(
			'single_post_post_type',
			'single_term_page',
			'single_post_taxonomy',
		);
		if ( $screen_id == WCCT_Common::get_campaign_post_type_slug() ) {
			$data = get_post_meta( $post->ID, 'wcct_rule', true );

			if ( $data && is_array( $data ) && count( $data ) > 0 ) {
				$cloned_Data = $data;

				foreach ( $data as $group => $rule ) {

					foreach ( $rule as $key => $inner_rule ) {

						if ( in_array( $inner_rule['rule_type'], $deprecated_rule_types ) ) {
							unset( $cloned_Data[ $group ][ $key ] );
						}
					}

					if ( empty( $cloned_Data[ $group ] ) ) {
						unset( $cloned_Data[ $group ] );
					}
				}

				if ( empty( $cloned_Data ) ) {
					delete_post_meta( $post->ID, 'wcct_rule' );
				} else {
					update_post_meta( $post->ID, 'wcct_rule', $cloned_Data );
				}
			}
		}
	}

	public function sanitize_group_cmb2( $post_id, $updated, $CMB2 ) {
		//Check it's not an auto save routine
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		//Perform permission checks! For example:
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( $post_id != null ) {

			$discounted_price = array();
			if ( isset( $_POST['_wcct_discount_custom_advanced'] ) ) {
				foreach ( $_POST['_wcct_discount_custom_advanced'] as $row ) {
					if ( $row['range_value'] === '' ) {
						continue;
					}
					array_push( $discounted_price, $row );
				}

				update_post_meta( $post_id, '_wcct_discount_custom_advanced', $discounted_price );
			}

			$advanced_inventory = array();
			if ( isset( $_POST['_wcct_deal_custom_advanced'] ) ) {
				foreach ( $_POST['_wcct_deal_custom_advanced'] as $row ) {
					if ( $row['range_value'] === '' ) {
						continue;
					}
					array_push( $advanced_inventory, $row );
				}

				update_post_meta( $post_id, '_wcct_deal_custom_advanced', $advanced_inventory );
			}
		}
	}

	public function wcct_add_cmb2_multiselect() {
		include_once $this->get_admin_uri() . 'includes/cmb2-addons/multiselect/CMB2_Type_MultiSelect.php';
	}

	public function wcct_add_cmb2_post_select() {
		include_once $this->get_admin_uri() . 'includes/cmb2-addons/post-select/CMB2_Type_PostSelect.php';
	}

	/**
	 * Hooked over `cmb2_render_wcct_multiselect`
	 * Render Html for `wcct_multiselect` Field
	 *
	 * @param $field CMB@ Field object
	 * @param $escaped_value Value
	 * @param $object_id object ID
	 * @param $object_type Object Type
	 * @param $field_type_object Field Type Object
	 */
	public function wcct_multiselect( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		$field_obj = new CMB2_Type_WCCT_MultiSelect( $field_type_object );
		echo $field_obj->render();
	}

	/**
	 * Hooked over `cmb2_render_wcct_post_select`
	 * Render Html for `wcct_wcct_post_select` Field
	 *
	 * @param $field CMB@ Field object
	 * @param $escaped_value Value
	 * @param $object_id object ID
	 * @param $object_type Object Type
	 * @param $field_type_object Field Type Object
	 */
	public function wcct_post_select( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		$field_obj = new CMB2_Type_WCCT_PostSelect( $field_type_object );
		echo $field_obj->render();
	}

	public function maybe_duplicate_post() {
		global $wpdb;
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'wcct-duplicate' ) {

			if ( wp_verify_nonce( $_GET['_wpnonce'], 'wcct-duplicate' ) ) {

				$original_id = filter_input( INPUT_GET, 'postid' );
				$section     = filter_input( INPUT_GET, 'trigger' );
				if ( $original_id ) {

					// Get the post as an array
					$duplicate = get_post( $original_id, 'ARRAY_A' );

					$settings = $defaults = array(
						'status'                => 'same',
						'type'                  => 'same',
						'timestamp'             => 'current',
						'title'                 => __( 'Copy', 'post-duplicator' ),
						'slug'                  => 'copy',
						'time_offset'           => false,
						'time_offset_days'      => 0,
						'time_offset_hours'     => 0,
						'time_offset_minutes'   => 0,
						'time_offset_seconds'   => 0,
						'time_offset_direction' => 'newer',
					);

					// Modify some of the elements
					$appended                = ( $settings['title'] != '' ) ? ' ' . $settings['title'] : '';
					$duplicate['post_title'] = $duplicate['post_title'] . ' ' . $appended;
					$duplicate['post_name']  = sanitize_title( $duplicate['post_name'] . '-' . $settings['slug'] );

					// Set the status
					if ( $settings['status'] != 'same' ) {
						$duplicate['post_status'] = $settings['status'];
					}

					// Set the type
					if ( $settings['type'] != 'same' ) {
						$duplicate['post_type'] = $settings['type'];
					}

					// Set the post date
					$timestamp     = ( $settings['timestamp'] == 'duplicate' ) ? strtotime( $duplicate['post_date'] ) : current_time( 'timestamp', 0 );
					$timestamp_gmt = ( $settings['timestamp'] == 'duplicate' ) ? strtotime( $duplicate['post_date_gmt'] ) : current_time( 'timestamp', 1 );

					if ( $settings['time_offset'] ) {
						$offset = intval( $settings['time_offset_seconds'] + $settings['time_offset_minutes'] * 60 + $settings['time_offset_hours'] * 3600 + $settings['time_offset_days'] * 86400 );
						if ( $settings['time_offset_direction'] == 'newer' ) {
							$timestamp     = intval( $timestamp + $offset );
							$timestamp_gmt = intval( $timestamp_gmt + $offset );
						} else {
							$timestamp     = intval( $timestamp - $offset );
							$timestamp_gmt = intval( $timestamp_gmt - $offset );
						}
					}
					$duplicate['post_date']         = date( 'Y-m-d H:i:s', $timestamp );
					$duplicate['post_date_gmt']     = date( 'Y-m-d H:i:s', $timestamp_gmt );
					$duplicate['post_modified']     = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
					$duplicate['post_modified_gmt'] = date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) );

					// Remove some of the keys
					unset( $duplicate['ID'] );
					unset( $duplicate['guid'] );
					unset( $duplicate['comment_count'] );

					// Insert the post into the database
					$duplicate_id = wp_insert_post( $duplicate );

					// Duplicate all the taxonomies/terms
					$taxonomies = get_object_taxonomies( $duplicate['post_type'] );
					foreach ( $taxonomies as $taxonomy ) {
						$terms = wp_get_post_terms( $original_id, $taxonomy, array(
							'fields' => 'names',
						) );
						wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
					}

					// Duplicate all the custom fields
					$custom_fields = get_post_custom( $original_id );
					if ( isset( $custom_fields['campaign_hash_id'] ) ) {
						unset( $custom_fields['campaign_hash_id'] );
					}
					foreach ( $custom_fields as $key => $value ) {
						if ( is_array( $value ) && count( $value ) > 0 ) {
							foreach ( $value as $i => $v ) {
								$result = $wpdb->insert( $wpdb->prefix . 'postmeta', array(
									'post_id'    => $duplicate_id,
									'meta_key'   => $key,
									'meta_value' => $v,
								) );
							}
						}
					}

					do_action( 'wcct_post_duplicated', $original_id, $duplicate_id, $settings );

					wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=' . $section ) );
				}
			} else {
				die( __( 'Unable to Duplicate', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}
		}
	}

	public function maybe_run_install( $plugin_base ) {
		if ( WCCT_PLUGIN_BASENAME === $plugin_base ) {
			$this->handle_activation();
		}
	}

	public function maybe_show_wizard() {
		if ( empty( $_GET['page'] ) || 'wc-settings' !== $_GET['page'] ) {
			return;
		}
		if ( empty( $_GET['tab'] ) || 'xl-countdown-timer' !== $_GET['tab'] ) {
			return;
		}

		if ( WCCT_Core()->xl_support->is_license_present() === false ) {
			wp_redirect( admin_url( 'admin.php?page=xlplugins&tab=' . 'finale-woocommerce-sales-countdown-timer-discount-plugin' . '-wizard' ) );
		}
	}

	/**
	 * @hooked over `admin_notices`
	 * Check and throw a notice when deal page version is lagging behind with the current version of Finale
	 */
	public function maybe_notice_for_deal_page_old_version() {

		if ( defined( 'WCCT_DEAL_PAGE_VERSION' ) && version_compare( WCCT_DEAL_PAGE_VERSION, '1.2.1', '<=' ) ) {

			?>
            <div class="notice error is-dismissible">
                <p><?php _e( 'Finale Deal Pages needs to be updated to work with latest version of Finale. <a class="button" href="' . admin_url( 'plugins.php' ) . '">Update </a>  ', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ); ?></p>
            </div>
			<?php
		}

	}

	/**
	 * @hooked over `cmb2 after field save`
	 *
	 * @param $post_id
	 */
	public function clear_transients( $post_id ) {
		if ( class_exists( 'XL_Transient' ) ) {
			$xl_transient_obj = XL_Transient::get_instance();
			$xl_transient_obj->delete_all_transients( 'finale' );
		}

		WCCT_Common::wcct_maybe_clear_cache();
	}

	/**
	 * @hooked over `delete_post`
	 *
	 * @param $post_id
	 */
	public function clear_transients_on_delete( $post_id ) {

		$get_post_type = get_post_type( $post_id );

		if ( WCCT_Common::get_campaign_post_type_slug() === $get_post_type ) {
			if ( class_exists( 'XL_Transient' ) ) {
				$xl_transient_obj = XL_Transient::get_instance();
				$xl_transient_obj->delete_all_transients( 'finale' );
			}

			WCCT_Common::wcct_maybe_clear_cache();
		}
	}

	public function restrict_to_publish_when_campaign_is_disabled( $post_ID, $post_after, $post_before ) {
		remove_action( 'post_updated', array( $this, 'restrict_to_publish_when_campaign_is_disabled' ), 10 );
		WCCT_Common::wcct_maybe_clear_cache();

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == 'xl-countdown-timer' ) {
		} else {
			if ( $post_before->post_status == 'wcctdisabled' && ! isset( $_GET['wcct_action'] ) ) {
				$post_after->post_status = 'wcctdisabled';
				$temp                    = json_encode( $post_after );
				$post_after              = json_decode( $temp, true );
				wp_update_post( $post_after );
			}
		}
	}

	public function default_orderby_date( $args ) {
		$args['order']   = 'DESC';
		$args['orderby'] = 'date';

		return $args;
	}

	/**
	 * Check the screen and check if plugins update available to show notification to the admin to update the plugin
	 */
	public function maybe_show_advanced_update_notification() {

		$screen = get_current_screen();

		if ( is_object( $screen ) && ( 'plugins.php' == $screen->parent_file || 'index.php' == $screen->parent_file || WCCT_Common::get_wc_settings_tab_slug() == filter_input( INPUT_GET, 'tab' ) ) ) {
			$plugins = get_site_transient( 'update_plugins' );
			if ( isset( $plugins->response ) && is_array( $plugins->response ) ) {
				$plugins = array_keys( $plugins->response );
				if ( is_array( $plugins ) && count( $plugins ) > 0 && in_array( WCCT_PLUGIN_BASENAME, $plugins ) ) {
					?>
                    <div class="notice notice-warning is-dismissible">
                        <p>
							<?php
							_e( sprintf( 'Attention: There is an update available of <strong>%s</strong> plugin. &nbsp;<a href="%s" class="">Go to updates</a>', WCCT_FULL_NAME, admin_url( 'plugins.php?s=finale&plugin_status=all' ) ), 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
							?>
                        </p>
                    </div>
					<?php

				}
			}
		}

	}

	/**
	 * Display Counter bar error when inventory bar is disabled.
	 * @since 2.6.0
	 */
	public function show_counter_bar_error() {
		global $post;
		if ( $post instanceof WP_Post && $post->post_type == WCCT_Common::get_campaign_post_type_slug() ) {
			$meta = WCCT_Common::get_item_data( $post->ID );
			if ( $meta['location_bar_show_single'] == 1 && $meta['deal_enable_goal'] != '1' ) {
				?>
                <div class="notice notice-error">
                    <p><strong>Finale</strong>: You have enabled the Counter Bar on this campaign, but Inventory is OFF. It should be ON in order Counter Bar to be visible and work.</p>
                </div>
				<?php
			}
		}
	}

	public function delete_post_data_transient( $post_id, $post = null ) {

		/** Check it's not an auto save routine */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		/** Perform permission checks */
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		/** Return of xl_transient class not exist */
		if ( ! class_exists( 'XL_Transient' ) ) {
			return;
		}

		/** If calling wp_update_post, unhook this function so it doesn't loop infinitely */
		remove_action( 'save_post', array( $this, 'delete_post_data_transient' ), 99, 2 );

		WCCT_Common::delete_post_data( $post_id );
	}

	public static function wcct_discount_mode_options() {
		return apply_filters( 'wcct_discount_mode_options', array(
			'simple' => __( 'Basic', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'tiered' => __( 'Advanced', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		) );
	}

	/**
	 * Remove CMB2 any style or script that have cmb2 name in the src
	 */
	public function removing_scripts_finale_campaign_load() {
		global $wp_scripts, $wp_styles;

		if ( false === WCCT_Common::wcct_valid_admin_pages( 'single' ) ) {
			return;
		}

		$mod_wp_scripts = $wp_scripts;
		$assets         = $wp_scripts;

		if ( 'admin_print_styles' === current_action() ) {
			$mod_wp_scripts = $wp_styles;
			$assets         = $wp_styles;
		}

		if ( is_object( $assets ) && isset( $assets->registered ) && count( $assets->registered ) > 0 ) {
			foreach ( $assets->registered as $handle => $script_obj ) {
				if ( ! isset( $script_obj->src ) || empty( $script_obj->src ) ) {
					continue;
				}
				$src = $script_obj->src;

				/** Remove scripts of clever mega menu plugin */
				if ( strpos( $src, 'clever-mega-menu/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove scripts of visual-products-configurator-for-woocommerce plugin */
				if ( strpos( $src, 'visual-products-configurator-for-woocommerce/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove scripts of massive VC addons plugin */
				if ( strpos( $src, 'mpc-massive/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove scripts of VC addons from ronneby theme */
				if ( strpos( $src, 'ronneby/inc/vc_custom/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove css from ronneby theme */
				if ( strpos( $src, 'ronneby/assets/css/admin-panel.css' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove scripts of Swift Framework plugin */
				if ( strpos( $src, 'swift-framework/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove scripts of datetimepicker from third party plugin */
				if ( strpos( $src, 'datetimepicker' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** If script doesn't belong to a plugin continue */
				if ( strpos( $src, '/tt-proven/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** If script doesn't belong to a plugin continue */
				if ( strpos( $src, 'plus-options/cmb2-conditionals.js' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** If no cmb2 in src continue */
				if ( strpos( $src, 'cmb2' ) === false ) {
					continue;
				}

				/** If script doesn't belong to a theme continue */
				if ( strpos( $src, 'themes/' ) === false ) {
					continue;
				}

				/** Allow assets of ascend_premium theme */
				if ( strpos( $src, 'themes/ascend_premium' ) !== false ) {
					continue;
				}

				/** Unset cmb2 script */
				unset( $mod_wp_scripts->registered[ $handle ] );

			}
		}

		if ( 'admin_print_styles' === current_action() ) {
			$wp_styles = $mod_wp_scripts;
		} else {
			$wp_scripts = $mod_wp_scripts;
		}

	}

}

new XLWCCT_Admin();
