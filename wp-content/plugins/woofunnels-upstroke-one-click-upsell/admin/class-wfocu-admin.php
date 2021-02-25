<?php

class WFOCU_Admin {

	private static $ins = null;
	public $admin_path;
	public $admin_url;
	public $section_page = '';
	public $should_show_shortcodes = null;
	public $updater = null;
	public $thank_you_page_posts = null;

	public function __construct() {

		$this->admin_path = WFOCU_PLUGIN_DIR . '/admin';
		$this->admin_url  = WFOCU_PLUGIN_URL . '/admin';

		$this->section_page = ( $this->is_upstroke_page() ) ? strtolower( filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING ) ) : '';


		/**
		 * Admin enqueue scripts
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 99 );

		/**
		 * Admin customizer enqueue scripts
		 */
		add_action( 'customize_controls_print_styles', array( $this, 'admin_customizer_enqueue_assets' ), 10 );

		/**
		 * Admin footer text
		 */
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 9999, 1 );

		add_action( 'save_post', array( $this, 'maybe_reset_transients' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'js_variables' ), 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_register_breadcrumbs' ), 10 );
		add_action( 'admin_init', array( $this, 'maybe_set_funnel_id' ) );

		add_action( 'delete_post', array( $this, 'clear_transients_on_delete' ), 10 );
		add_action( 'delete_post', array( $this, 'clear_session_record_on_shop_order_delete' ), 10 );

		/**
		 * Hooks to check if activation and deactivation request for post.
		 */
		add_action( 'admin_init', array( $this, 'maybe_activate_post' ) );
		add_action( 'admin_init', array( $this, 'maybe_deactivate_post' ) );

		add_action( 'customize_controls_print_footer_scripts', array( $this, 'maybe_print_mergetag_helpbox' ) );
		add_filter( 'plugin_action_links_' . WFOCU_PLUGIN_BASENAME, array( $this, 'plugin_actions' ) );
		add_action( 'admin_init', array( $this, 'maybe_handle_http_referer' ) );

		add_action( 'woocommerce_admin_field_payment_gateways', array( $this, 'hide_test_gateway_from_admin_list' ) );
		add_action( 'admin_init', array( $this, 'maybe_show_wizard' ) );

		add_action( 'in_admin_header', array( $this, 'maybe_remove_all_notices_on_page' ) );

		add_action( 'admin_init', array( $this, 'check_db_version' ), 990 );


		add_filter( 'woocommerce_get_formatted_order_total', array( $this, 'show_upsell_total_in_order_listings' ), 999, 2 );

		add_action( 'admin_init', array( $this, 'maybe_update_upsell_gross_total_to_order_meta' ), 999 );

		add_action( 'admin_bar_menu', array( $this, 'toolbar_link_to_xlplugins' ), 999 );

		add_filter( 'woocommerce_payment_gateways_setting_columns', array( $this, 'set_wc_payment_gateway_column' ) );

		add_action( 'woocommerce_payment_gateways_setting_column_wfocu', array( $this, 'wc_payment_gateway_column_content' ) );

		/**
		 * Initiate Background Database updaters
		 */
		add_action( 'init', array( $this, 'init_background_updater' ) );
		add_action( 'admin_head', array( $this, 'maybe_update_database_update' ) );

		add_action( 'admin_init', array( $this, 'maybe_update_upstroke_version_in_option' ) );


		/**
		 * Handling to prevent scripts and styles in our pages.
		 */
		add_action( 'wp_print_scripts', array( $this, 'no_conflict_mode_script' ), 1000 );
		add_action( 'admin_print_footer_scripts', array( $this, 'no_conflict_mode_script' ), 9 );

		add_action( 'wp_print_styles', array( $this, 'no_conflict_mode_style' ), 1000 );
		add_action( 'admin_print_styles', array( $this, 'no_conflict_mode_style' ), 1 );
		add_action( 'admin_print_footer_scripts', array( $this, 'no_conflict_mode_style' ), 1 );
		add_action( 'admin_footer', array( $this, 'no_conflict_mode_style' ), 1 );

		add_action( 'admin_head', function () {
			if ( WFOCU_Common::is_load_admin_assets( 'builder' ) ) {
				echo "<div class='wfocu_builder_admin_head_wrap'>";
			}
		}, - 1 );

		add_action( 'admin_head', function () {
			if ( WFOCU_Common::is_load_admin_assets( 'builder' ) ) {
				echo "</div>";
			}
		}, 999 );

		add_action( 'admin_footer', function () {
			if ( WFOCU_Common::is_load_admin_assets( 'builder' ) ) {
				echo "<div class='wfocu_builder_admin_foot_wrap'>";
			}
		}, - 1 );

		add_action( 'admin_footer', function () {
			if ( WFOCU_Common::is_load_admin_assets( 'builder' ) ) {
				echo "</div>";
			}
		}, 999 );

		add_filter( 'woofunnels_global_settings', function ( $menu ) {
			array_push( $menu, array(
				'title'    => __( 'One Click Upsells', 'woofunnels-upstroke-one-click-upsell' ),
				'slug'     => 'upstroke',
				'link'     => admin_url( 'admin.php?page=upstroke&tab=settings' ),
				'priority' => 50,
			) );

			return $menu;
		} );
		add_action( 'edit_form_after_title', [ $this, 'add_back_button' ] );

		/*** bwf general setting ***/
		add_filter( 'bwf_general_settings_link', function () {
			return admin_url( 'admin.php?page=upstroke&tab=bwf_settings' );
		} );

		add_action( 'admin_footer', function () {
			?>
			<script>
				if (typeof window.bwfBuilderCommons !== "undefined") {
					window.bwfBuilderCommons.addFilter('bwf_common_permalinks_fields', function (e) {
						e.push(
							{
								type: "input",
								inputType: "text",
								label: "",
								model: "wfocu_page_base",
								inputName: 'wfocu_page_base',
							});
						return e;
					});
				}
			</script>
			<?php
		}, 90 );

		add_filter( 'bwf_general_settings_fields', function ( $fields ) {
			$fields['wfocu_page_base'] = array(
				'label' => __( 'Upsell page', 'woofunnels-upstroke-one-click-upsell' ),
				'hint'  => __( '', 'woofunnels-upstroke-one-click-upsell' ),
			);

			return $fields;
		}, 90 );
		add_filter( 'bwf_general_settings_default_config', function ( $fields ) {
			$fields['wfocu_page_base'] = 'offer';

			return $fields;
		} );

		add_filter( 'bwf_enable_ecommerce_integration_pinterest', function ( $res ) {
			return true;
		} );

		add_filter( 'bwf_enable_ecommerce_integration_fb_purchase', '__return_true' );
		add_filter( 'bwf_enable_ecommerce_integration_ga_purchase', '__return_true' );
		add_filter( 'bwf_enable_ecommerce_integration_gad', '__return_true' );

		add_action( 'wfocu_loaded', array( $this, 'maybe_add_timeline_files' ), 999 );


		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 90 );
		add_filter( 'wfocu_add_control_meta_query', array( $this, 'exclude_from_query' ) );

	}


	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function get_admin_url() {
		return WFOCU_PLUGIN_URL . '/admin';
	}


	public function admin_enqueue_assets() {
		$is_min = 'min';
		$suffix = '.min';
		if ( defined( 'WFOCU_IS_DEV' ) && true === WFOCU_IS_DEV ) {
			$is_min = '';
			$suffix = '';
		}

		wp_enqueue_style( 'woofunnels-admin-font', $this->get_admin_url() . '/assets/css/wfocu-admin-font.css', array(), WFOCU_VERSION_DEV );
		$gateways_list = [];
		if ( $this->is_upstroke_page() ) {
			WFOCU_Core()->funnels->setup_funnel_options( ( isset( $_GET['edit'] ) ? wc_clean( $_GET['edit'] ) : 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if ( 'rules' === $this->section_page ) {
			wp_register_script( 'wfocu-chosen', $this->get_admin_url() . '/assets/js/chosen/chosen.jquery.min.js', array( 'jquery' ), WFOCU_VERSION_DEV );
			wp_register_script( 'wfocu-ajax-chosen', $this->get_admin_url() . '/assets/js/chosen/ajax-chosen.jquery.min.js', array(
				'jquery',
				'wfocu-chosen',
			), WFOCU_VERSION_DEV );
			wp_enqueue_script( 'wfocu-ajax-chosen' );

			wp_enqueue_style( 'wfocu-chosen-app', $this->get_admin_url() . '/assets/css/chosen.css', array(), WFOCU_VERSION_DEV );
			wp_enqueue_style( 'wfocu-admin-app', $this->get_admin_url() . '/assets/css/wfocu-admin-app.css', array(), WFOCU_VERSION_DEV );
			wp_register_script( 'jquery-masked-input', $this->get_admin_url() . '/assets/js/jquery.maskedinput.min.js', array( 'jquery' ), WFOCU_VERSION_DEV );
			wp_enqueue_script( 'jquery-masked-input' );
			wp_enqueue_script( 'wfocu-admin-app', $this->get_admin_url() . '/assets/js/wfocu-admin-app.js', array(
				'jquery',
				'jquery-ui-datepicker',
				'underscore',
				'backbone',
			), WFOCU_VERSION_DEV );

		}
		if ( WFOCU_Common::is_load_admin_assets( 'all' ) ) {
			wp_enqueue_script( 'wfocu-admin-ajax', $this->get_admin_url() . '/assets/js/wfocu-ajax.js', [], WFOCU_VERSION_DEV );
		}
		/**
		 * Load Color Picker
		 */
		if ( WFOCU_Common::is_load_admin_assets( 'settings' ) ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}

		/**
		 * Load Funnel Builder page assets
		 */
		if ( WFOCU_Common::is_load_admin_assets( 'builder' ) ) {
			//wp_enqueue_style( 'wfocu-funnel-bg', $this->admin_url . '/assets/css/wfocu-funnel-bg.css', array(), WFOCU_VERSION_DEV );
			wp_enqueue_style( 'wfocu-opensans-font', '//fonts.googleapis.com/css?family=Open+Sans', array(), WFOCU_VERSION_DEV );

		}
		if ( 'shop_order' === get_current_screen()->post_type ) {
			wp_enqueue_style( 'wfocu-timeline-style', $this->get_admin_url() . '/assets/css/wfocu-timeline' . $suffix . '.css', array(), WFOCU_VERSION_DEV );
		}
		/**
		 * Including izimodal assets
		 */
		if ( WFOCU_Common::is_load_admin_assets( 'all' ) ) {
			wp_enqueue_style( 'wfocu-izimodal', $this->admin_url . '/includes/iziModal/iziModal.css', array(), WFOCU_VERSION_DEV );
			wp_enqueue_script( 'wfocu-izimodal', $this->admin_url . '/includes/iziModal/iziModal.js', array(), WFOCU_VERSION_DEV );
		}
		if ( WFOCU_Common::is_load_admin_assets( 'settings' ) ) {
			$gateways_list = WFOCU_Core()->gateways->get_gateways_list();
			wp_enqueue_script( 'jquery-tiptip' );

		}
		/**
		 * Including vuejs assets
		 */
		if ( WFOCU_Common::is_load_admin_assets( 'settings' ) || ( WFOCU_Common::is_load_admin_assets( 'all' ) && false === $this->is_upstroke_page( 'rules' ) ) ) {
			wp_enqueue_style( 'wfocu-vue-multiselect', $this->admin_url . '/includes/vuejs/vue-multiselect.min.css', array(), WFOCU_VERSION_DEV );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'wfocu-vuejs', $this->admin_url . '/includes/vuejs/vue.min.js', array(), '2.6.10' );
			wp_enqueue_script( 'wfocu-vue-vfg', $this->admin_url . '/includes/vuejs/vfg.min.js', array(), '2.3.4' );
			wp_enqueue_script( 'wfocu-vue-multiselect', $this->admin_url . '/includes/vuejs/vue-multiselect.min.js', array(), WFOCU_VERSION_DEV );
		}
		if ( WFOCU_Common::is_load_admin_assets( 'builder' ) ) {
			wp_enqueue_script( 'accounting' );

			wp_localize_script( 'accounting', 'wfocu_wc_params', array(
				'currency_format_num_decimals' => wc_get_price_decimals(),
				'currency_format_symbol'       => get_woocommerce_currency_symbol(),
				'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
				'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
				'currency_format'              => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),
			) );

		}

		if ( $this->is_upstroke_page( 'bwf_settings' ) ) {

			BWF_Admin_General_Settings::get_instance()->maybe_add_js();
		}
		/**
		 * Including One Click Upsell assets on all OCU pages.
		 */
		if ( WFOCU_Common::is_load_admin_assets( 'all' ) ) {
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_enqueue_script( 'wc-backbone-modal' );

			if ( ! empty( $is_min ) ) {
				wp_enqueue_style( 'wfocu-admin', $this->admin_url . '/assets/css/' . $is_min . '/wfocu-admin' . $suffix . '.css', array(), ( defined( 'WFOCU_VERSION_ADMIN_DEV' ) ) ? WFOCU_VERSION_ADMIN_DEV : WFOCU_VERSION_DEV );

			} else {
				wp_enqueue_style( 'wfocu-admin', $this->admin_url . '/assets/css/wfocu-admin.css', array(), WFOCU_VERSION_DEV );

			}
			wp_enqueue_script( 'wfocu-admin', $this->admin_url . '/assets/js/wfocu-admin.js', array(), WFOCU_VERSION_DEV );
			wp_enqueue_script( 'wfocu-swal', $this->admin_url . '/assets/js/wfocu-sweetalert.min.js', array(), WFOCU_VERSION_DEV );

			wp_enqueue_script( 'wfocu-admin-builder', $this->admin_url . '/assets/js/wfocu-admin-builder.js', array(
				'jquery',
				'wfocu-swal',
				'wfocu-vuejs',
				'wfocu-vue-vfg',
				'wfocu-admin'
			), WFOCU_VERSION_DEV );
			wp_enqueue_script( 'updates' );
		}

		/**
		 * deregister this script as its in the conflict with the vue JS
		 */
		if ( WFOCU_Common::is_load_admin_assets( 'all' ) ) {

			wp_dequeue_script( 'backbone-marionette' );
			wp_deregister_script( 'backbone-marionette' );
		}

		if ( WFOCU_Common::is_load_admin_assets( 'customizer' ) ) {

			wp_enqueue_script( 'wfocu-modal', WFOCU_PLUGIN_URL . '/admin/assets/js/wfocu-modal.js', array( 'jquery' ), WFOCU_VERSION );
			wp_enqueue_style( 'wfocu-modal', WFOCU_PLUGIN_URL . '/admin/assets/css/wfocu-modal.css', null, WFOCU_VERSION );

		}


		$data                = array(
			'ajax_nonce'                            => wp_create_nonce( 'wfocuaction-admin' ),
			'ajax_nonce_toggle_funnel_state'        => wp_create_nonce( 'wfocu_toggle_funnel_state' ),
			'ajax_nonce_preview_details'            => wp_create_nonce( 'wfocu_preview_details' ),
			'ajax_nonce_duplicate_funnel'           => wp_create_nonce( 'wfocu_duplicate_funnel' ),
			'ajax_nonce_save_rules_settings'        => wp_create_nonce( 'wfocu_save_rules_settings' ),
			'ajax_nonce_remove_offer_from_funnel'   => wp_create_nonce( 'wfocu_remove_offer_from_funnel' ),
			'ajax_nonce_save_funnel_steps'          => wp_create_nonce( 'wfocu_save_funnel_steps' ),
			'ajax_nonce_product_search'             => wp_create_nonce( 'wfocu_product_search' ),
			'ajax_nonce_wfocu_add_product'          => wp_create_nonce( 'wfocu_add_product' ),
			'ajax_nonce_remove_product'             => wp_create_nonce( 'wfocu_remove_product' ),
			'ajax_nonce_save_funnel_settings'       => wp_create_nonce( 'wfocu_save_funnel_settings' ),
			'ajax_nonce_save_funnel_offer_settings' => wp_create_nonce( 'wfocu_save_funnel_offer_settings' ),
			'ajax_nonce_save_funnel_offer_product'  => wp_create_nonce( 'wfocu_save_funnel_offer_product' ),
			'ajax_nonce_save_global_settings'       => wp_create_nonce( 'wfocu_save_global_settings' ),
			'ajax_nonce_apply_template'             => wp_create_nonce( 'wfocu_apply_template' ),
			'ajax_nonce_update_template'            => wp_create_nonce( 'wfocu_update_template' ),
			'ajax_nonce_activate_plugins'           => wp_create_nonce( 'wfocu_activate_plugins' ),
			'ajax_nonce_clear_template'             => wp_create_nonce( 'wfocu_clear_template' ),
			'ajax_nonce_get_custom_page'            => wp_create_nonce( 'wfocu_get_custom_page' ),
			'ajax_nonce_activate_next_move'         => wp_create_nonce( 'wfocu_activate_next_move' ),
			'ajax_nonce_make_wpml_duplicate'        => wp_create_nonce( 'wfocu_make_wpml_duplicate' ),
			'ajax_nonce_get_wpml_edit_url'          => wp_create_nonce( 'wfocu_get_wpml_edit_url' ),
			'plugin_url'                            => WFOCU_PLUGIN_URL,
			'ajax_url'                              => admin_url( 'admin-ajax.php' ),
			'admin_url'                             => admin_url(),
			'ajax_chosen'                           => wp_create_nonce( 'json-search' ),
			'search_products_nonce'                 => wp_create_nonce( 'search-products' ),
			'search_customers_nonce'                => wp_create_nonce( 'search-customers' ),
			'search_coupons_nonce'                  => wp_create_nonce( 'search-coupons' ),
			'text_or'                               => __( 'or', 'woofunnels-upstroke-one-click-upsell' ),
			'text_apply_when'                       => __( 'Open this page when these conditions are matched', 'woofunnels-upstroke-one-click-upsell' ),
			'remove_text'                           => __( 'Remove', 'woofunnels-upstroke-one-click-upsell' ),
			'modal_add_offer_step_text'             => __( 'Add Offer', 'woofunnels-upstroke-one-click-upsell' ),
			'modal_add_add_product'                 => __( 'Add Products', 'woofunnels-upstroke-one-click-upsell' ),
			'modal_update_offer'                    => __( 'Offers', 'woofunnels-upstroke-one-click-upsell' ),
			'modal_funnel_div'                      => __( 'Upsell Funnel', 'woofunnels-upstroke-one-click-upsell' ),
			'section_page'                          => $this->section_page,
			'legends_texts'                         => array(

				'order_statuses' => __( 'Order Statuses', 'woofunnels-upstroke-one-click-upsell' ),
				'offer_conf'     => __( 'Offer Confirmation Settings', 'woofunnels-upstroke-one-click-upsell' ),
				'gateways'       => __( 'Gateways', 'woofunnels-upstroke-one-click-upsell' ),
				'tan'            => __( 'Tracking & Analytics', 'woofunnels-upstroke-one-click-upsell' ),
				'emails'         => __( 'Confirmation Email', 'woofunnels-upstroke-one-click-upsell' ),
				'misc'           => __( 'Miscellaneous', 'woofunnels-upstroke-one-click-upsell' ),
				'fb_tracking'    => __( 'Facebook Pixel', 'woofunnels-upstroke-one-click-upsell' ),
				'scripts'        => __( 'External Scripts', 'woofunnels-upstroke-one-click-upsell' ),
				'scripts_head'   => __( 'External Scripts H', 'woofunnels-upstroke-one-click-upsell' ),

			),
			'alerts'                                => array(
				'delete_offer'         => array(
					'title'             => __( 'Want to Remove this offer from your funnel?', 'woofunnels-upstroke-one-click-upsell' ),
					'text'              => __( 'You won\'t be able to revert this!', 'woofunnels-upstroke-one-click-upsell' ),
					'confirmButtonText' => __( 'Yes, Remove it!', 'woofunnels-upstroke-one-click-upsell' ),
				),
				'offer_edit'           => array(
					'title'             => __( 'Hey! A gentle reminder that this offer is inactive.', 'woofunnels-upstroke-one-click-upsell' ),
					'text'              => __( 'Do activate the offer when you have completed the setup.', 'woofunnels-upstroke-one-click-upsell' ),
					'confirmButtonText' => __( 'Continue and Save!', 'woofunnels-upstroke-one-click-upsell' ),
					'img_url'           => WFOCU_PLUGIN_URL . '/admin/assets/img/set_active.gif'
				),
				'jump_error'           => array(
					'title'             => __( 'Sorry! we are unable to save this offer.', 'woofunnels-upstroke-one-click-upsell' ),
					'text'              => __( 'You have enabled dynamic offer path but no offer is selected. Please select an offer.', 'woofunnels-upstroke-one-click-upsell' ),
					'confirmButtonText' => __( 'Close and Select!', 'woofunnels-upstroke-one-click-upsell' ),
				),
				'no_variations_chosen' => array(
					'title'             => __( 'Oops! Unable to save this offer', 'woofunnels-upstroke-one-click-upsell' ),
					'text'              => __( 'This offer contains product(s) with no variation selected. Please select atleast one variation', 'woofunnels-upstroke-one-click-upsell' ),
					'confirmButtonText' => __( 'Okay! Got it', 'woofunnels-upstroke-one-click-upsell' ),
					'type'              => 'error',
				),
				'max_variation_error'  => array(
					'title'             => __( 'Oops! Unable to save this offer', 'woofunnels-upstroke-one-click-upsell' ),
					'text'              => __( 'This offer contains extremely large variants. Please increase server\'s max_input_vars limit. Not sure? Contact support.', 'woofunnels-upstroke-one-click-upsell' ),
					'confirmButtonText' => __( 'Okay! Got it', 'woofunnels-upstroke-one-click-upsell' ),
					'type'              => 'error',
				),
				'remove_product'       => array(
					'title'             => __( 'Want to remove this product from the offer?', 'woofunnels-upstroke-one-click-upsell' ),
					'text'              => __( 'You won\'t be able to revert this!', 'woofunnels-upstroke-one-click-upsell' ),
					'confirmButtonText' => __( 'Yes, Remove it!', 'woofunnels-upstroke-one-click-upsell' ),
				),
				'remove_template'      => array(
					'title'             => __( 'Are you sure you want to remove this template?', 'woofunnels-upstroke-one-click-upsell' ),
					'text'              => __( 'Attention: By removing this template all changes to current template will be lost.', 'woofunnels-upstroke-one-click-upsell' ),
					'confirmButtonText' => __( 'Yes, remove this template!', 'woofunnels-upstroke-one-click-upsell' ),
				),
				'import_template'      => array(
					'title'             => __( 'Are you sure you want to import this template?', 'woofunnels-upstroke-one-click-upsell' ),
					'text'              => '',
					'confirmButtonText' => __( 'Yes, import this template!', 'woofunnels-upstroke-one-click-upsell' ),
				),
				'failed_import_beaver' => array(
					'title'             => __( 'Unable to import this template', 'woofunnels-upstroke-one-click-upsell' ),
					'text'              => __( 'Beaver Builder PRO version is required to import this template', 'woofunnels-upstroke-one-click-upsell' ),
					'confirmButtonText' => __( 'Yes, import this template!', 'woofunnels-upstroke-one-click-upsell' ),
				),
			),
			'forms_labels'                          => array(

				'funnel_setting'        => array(
					array(
						'funnel_name' => array(
							'label' => __( 'Name Of Funnel', 'woofunnels-upstroke-one-click-upsell' ),
						),
					),
				),
				'add_new_offer_setting' => array(
					'funnel_step_name' => array(
						'label'       => __( 'Offer Name', 'woofunnels-upstroke-one-click-upsell' ),
						'placeholder' => __( 'Enter Offer Name', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'step_type' => array(
						'label'  => __( 'Type', 'woofunnels-upstroke-one-click-upsell' ),
						'help'   => __( '<strong>Upsell</strong> <br/>The upsell is when you present a new offer.<hr/><strong>Downsell</strong><br/>The downsell is when your Upsell offer was declined and you present a new offer usually at a lower price.', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(

							array(
								'name'  => __( 'Upsell', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'upsell',
							),
							array(
								'name'  => __( 'Downsell', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'downsell',
							),

						),
					),
				),
				'update_step'           => array(
					'funnel_step_name' => array(
						'label'       => __( 'Offer Name', 'woofunnels-upstroke-one-click-upsell' ),
						'placeholder' => __( 'Enter Offer Name', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'step_type'        => array(
						'label'  => __( 'Type', 'woofunnels-upstroke-one-click-upsell' ),
						'help'   => __( '<strong>Upsell</strong> <br/>The upsell is when you present a new offer.<hr/><strong>Downsell</strong><br/>The downsell is when your Upsell offer was declined and you present a new offer usually at a lower price.', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(

							array(
								'name'  => __( 'Upsell', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'upsell',
							),
							array(
								'name'  => __( 'Downsell', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'downsell',
							),

						),
					),
					'funnel_step_slug' => array(
						'label'       => __( 'Offer URL', 'woofunnels-upstroke-one-click-upsell' ),
						'placeholder' => __( 'Enter Offer Slug', 'woofunnels-upstroke-one-click-upsell' ),
					),
				),
				'settings'              => array(
					'funnel_order_label'        => array(

						'label' => __( 'Behavioural Settings', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'order_behavior'            => array(

						'label'  => __( 'Accepted Upsell Order', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(

							array(
								'name'  => __( 'Add to Main Order', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'batching',
							),
							array(
								'name'  => __( 'Create a New Order', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'create_order',
							),

						),
					),
					'is_cancel_order'           => array(

						'label'  => __( 'Do Cancel Primary Order?', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(

							array(
								'name'  => __( 'Yes', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
							array(
								'name'  => __( 'No', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'no',
							),

						),
					),
					'funnel_priority_label'     => array(

						'label' => __( 'Priority', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'funnel_priority'           => array(

						'label' => __( 'Priority Number', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'  => __( "There maybe chance more than one Upsell Funnels can trigger.\n In such cases, Upsell Funnel Priority is used to determine which Upsell Funnel will trigger. Priority Number 1 is considered highest.", 'woofunnels-upstroke-one-click-upsell' ),

					),
					'funnel_display_label'      => array(

						'label' => __( 'Prices', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'is_tax_included'           => array(

						'label'  => __( 'Show Prices with taxes', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(

							array(
								'name'  => __( 'Yes (Recommended)', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
							array(
								'name'  => __( 'No', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'no',
							),

						),
					),
					'offer_messages_label_help' => array(
						'label' => __( 'These messages show when buyer\'s upsell order is charged & confirmed. If unable to charge user, a failure message will show.<a href="javascript:void(0);" onclick="window.wfocuBuilder.show_funnel_design_messages()">Click here to learn about these settings.</a> ', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'offer_messages_label'      => array(

						'label' => __( 'Upsell Confirmation Messages', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'offer_success_message_pop' => array(

						'label' => __( 'Upsell Success Message', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'offer_failure_message_pop' => array(

						'label' => __( 'Upsell Failure Message', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'offer_wait_message_pop'    => array(

						'label' => __( 'Upsell Processing Message', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'offer_scripts_label'       => array(

						'label' => __( 'External Tracking Code', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'funnel_success_script'     => array(

						'label'       => __( 'Add tracking code to run, this upsells', 'woofunnels-upstroke-one-click-upsell' ),
						'placeholder' => __( 'Paste your code here', 'woofunnels-upstroke-one-click-upsell' ),

					),
				),

				'funnel_advanced_settings' => array(
					'next_move_install' => array(
						'label' => __( 'Install Next Move Plugin to configure thank you page', 'woofunnels-upstroke-one-click-upsell' ),
					),
				),

				'global_settings' => array(

					'label_section_head_tan' => array(
						'label' => __( 'Facebook Pixel Tracking', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'label_section_head_tan_ga' => array(
						'label' => __( 'Google Analytics Tracking', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'label_section_head_tan_gad' => array(
						'label' => __( 'Google Ads Tracking', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'label_section_head_tan_pint' => array(
						'label' => __( 'Pinterest Tracking', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'label_section_head_orders' => array(
						'label' => __( 'Order Status When Upsell Are To Be Merged With original Order', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'label_section_head_orders_no_batch_cancel' => array(
						'label' => __( 'Order Status When Primary Order Is Cancelled & Upsell Is Accepted', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'label_section_head_orders_upsell_fails'    => array(
						'label' => __( 'Order Status When Charge For Upsell Fails.', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'label_section_head_emails'                 => array(
						'label' => __( 'Applicable When Upsell Are To Be Merged With Original Order', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'label_section_head_emails_no_batch'        => array(
						'label' => __( 'Applicable When Upsell Are To Be Created Separate Orders', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'label_section_head_emails_no_batch_cancel' => array(
						'label' => __( 'Applicable When Primary Order Is Cancelled & Upsell Is Accepted', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'label_section_head_emails_upsell_fails'    => array(
						'label' => __( 'Applicable When Charge For Upsell Fails.', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'label_section_head_orders_no_batch_note'        => array(
						'label' => __( 'No change happens in order emails in this scenario.', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'label_section_head_orders_no_batch_cancel_note' => array(
						'label' => __( 'No change happens in order emails in this scenario.', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'label_section_head_orders_upsell_fails_note'    => array(
						'label' => __( 'No change happens in order emails in this scenario.', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'custom_html_tracking_general_'                  => array(
						'label' => '',

					),
					'is_fb_view_event'                               => array(
						'label'  => __( '', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'name'  => __( 'Enable PageView Event', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),
					),
					'is_fb_purchase_event'                           => array(
						'label'  => '',
						'hint'   => __( 'Note: UpStroke will send total order value and store currency based on order. <a target="_blank" href="https://developers.facebook.com/docs/facebook-pixel/pixel-with-ads/conversion-tracking#add-value">Click here to know more.</a>', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'name'  => __( 'Enable Purchase Event', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),
					),
					'is_fb_synced_event'                             => array(
						'label'  => '',
						'hint'   => __( 'Note: Your Product catalog must be synced with Facebook. <a target="_blank" href="https://developers.facebook.com/docs/facebook-pixel/implementation/dynamic-ads">Click here to know more.</a>', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'name'  => __( 'Enable Content Settings for Dynamic Ads', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),
					),
					'content_id_value'                               => array(

						'label'  => '',
						'hint'   => __( 'Select either Product ID or SKU to pass value in content_id parameter', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'id'   => '',
								'name' => __( 'Select content_id parameter', 'woofunnels-upstroke-one-click-upsell' ),
							),
							array(
								'id'   => 'product_id',
								'name' => __( 'Product ID', 'woofunnels-upstroke-one-click-upsell' ),
							),
							array(
								'id'   => 'product_sku',
								'name' => __( 'Product SKU', 'woofunnels-upstroke-one-click-upsell' ),
							),

						),
					),
					'content_id_variable'                            => array(
						'label'  => '',
						'hint'   => __( 'Turn this option ON when your Product Catalog doesn\'t include the variants for variable products.', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'name'  => __( 'Treat variable products like simple products', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),
					),
					'content_id_prefix'                              => array(

						'label'       => '',
						'placeholder' => __( 'content_id prefix', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'        => __( 'Add prefix to the content_id parameter (optional)', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'content_id_suffix'                              => array(

						'label'       => '',
						'placeholder' => __( 'content_id suffix', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'        => __( 'Add suffix to the content_id parameter (optional)', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'id_prefix_gad' => array(

						'label'       => '',
						'placeholder' => __( 'Product ID prefix', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'        => __( 'Add prefix to the product_id parameter (optional)', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'id_suffix_gad' => array(

						'label'       => '',
						'placeholder' => __( 'Product ID suffix', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'        => __( 'Add suffix to the product_id parameter (optional)', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'is_fb_advanced_event' => array(
						'label'  => '',
						'hint'   => __( 'Note: UpStroke will send customer\'s email, name, phone, address fields whichever available in the order. <a target="_blank" href="https://developers.facebook.com/docs/facebook-pixel/pixel-with-ads/conversion-tracking#advanced_match">Click here to know more.', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'name'  => __( 'Enable Advanced Matching With the Pixel', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),
					),
					'pint_key'             => array(
						'label' => __( 'Tag ID', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'  => __( '<a target="_blank" href="https://buildwoofunnels.com/docs/upstroke/global-settings/tracking-analytics/#google-ads-tracking">Learn more about how to get the Conversion ID</a>', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'is_ga_view_event'     => array(
						'label'  => __( '', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'name'  => __( 'Enable PageView Event', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),
					),

					'is_ga_purchase_event'          => array(
						'label'  => '',
						'values' => array(
							array(
								'name'  => __( 'Enable Purchase Event', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),
					),
					'is_pint_purchase_event'        => array(
						'label'  => __( 'Tracking Events', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'name'  => __( 'Enable Purchase Event', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),
					),
					'is_gad_purchase_event'         => array(
						'label'  => __( '', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'name'  => __( 'Enable Conversion Event', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),
					),
					'scripts'                       => array(
						'label' => __( 'External Scripts On Offer Pages', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'  => __( 'These scripts will be globally embedded in all the upsell funnels\' offer pages*. ', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'scripts_head'                  => array(
						'label' => __( 'External Scripts On Offer Pages in head tag', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'  => __( 'These scripts will be globally embedded in all the upsell funnels\' offer pages*. ', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'label_section_head_paypal_ref' => array(
						'label' => '',
						'hint'  => __( 'UpStroke works with or without reference transactions. If you have reference transactions enabled in your PayPal account select Yes otherwise No.', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'gateways' => array(
						'label'  => __( 'Enable Gateways', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => $gateways_list,
						'hint'   => __( 'Could not find your gateway in the list? <a target="_blank" href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">Click here to check enabled Payment Methods</a>', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'no_gateways' => array(
						'label' => __( 'Enable Gateways', 'woofunnels-upstroke-one-click-upsell' ),

						'hint' => __( 'No Gateways Found. Could not find your gateway in the list?<br> <a target="_blank" href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">Click here to check enabled Payment Methods</a>', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'paypal_ref_trans' => array(

						'label' => __( 'PayPal Reference Transactions', 'woofunnels-upstroke-one-click-upsell' ),

						'values' => array(

							array(
								'name'  => __( 'Yes, Reference transactions are enabled on my PayPal account', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
							array(
								'name'  => __( 'No, Reference transaction are not enabled on my PayPal account.', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'no',
							),

						),

					),

					'gateway_test' => array(
						'label'  => __( 'Enable Test Gateway', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'   => __( 'To quickly test upsell funnels , create a Test Gateway. This is only visible to Admin.', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'name'  => __( 'Test Gateway By WooFunnels', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),
					),

					'enable_log' => array(

						'label' => __( 'Enable Logging', 'woofunnels-upstroke-one-click-upsell' ),

					),

					'order_copy_meta_keys' => array(

						'label'       => __( 'Meta Keys To Copy From Primary Order', 'woofunnels-upstroke-one-click-upsell' ),
						'placeholder' => __( 'Enter keys like utm_campaign|utm_source|...', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'        => __( 'Applicable in case of new upsell order is created.', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'treat_variable_as_simple' => array(
						'label' => __( 'Treat variable products like simple products', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'  => __( 'Skip offer when any variant of a variable product is sold.', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'enable_noconflict_mode' => array(
						'label' => __( 'Enable no conflict mode', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'  => __( 'Remove third party plugin scripts on plugin\'s admin pages. Usually used, when some external plugin conflicts on Upstroke\'s pages.', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'send_processing_mail_on'                 => array(

						'label' => __( 'Send Order Confirmation Email When', 'woofunnels-upstroke-one-click-upsell' ),

						'values' => array(

							array(
								'name'  => __( 'Upsell Funnel Start', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'start',
							),
							array(
								'name'  => __( 'Upsell Funnel Ends (Recommended)', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'end',
							),

						),

					),
					'send_processing_mail_on_no_batch'        => array(

						'label' => __( 'Send Order Confirmation Email When', 'woofunnels-upstroke-one-click-upsell' ),

						'values' => array(

							array(
								'name'  => __( 'Upsell Funnel Start (Recommended)', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'start',
							),
							array(
								'name'  => __( 'Upsell Funnel Ends', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'end',
							),

						),

					),
					'send_processing_mail_on_no_batch_cancel' => array(

						'label' => __( 'Send Order Confirmation Email When', 'woofunnels-upstroke-one-click-upsell' ),

						'values' => array(

							array(
								'name'  => __( 'Upsell Funnel Start', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'start',
							),
							array(
								'name'  => __( 'Upsell Funnel Ends (Recommended)', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'end',
							),

						),

					),
					'send_emails_label'                       => array(
						'label' => __( 'When user enters the upsell funnel, you can decide whether to send an email right away or when upsell funnel ends.', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'primary_order_status_title'   => array(

						'hint' => __( '<br/><strong>What is custom order state?</strong><br/>
						    It is an intermediary state when upsell funnel is running. Once user has accepted or rejected or time of offer expired, order status is automatically switched to successful order status.

 <br/><br/> <strong>Why it is needed?</strong> <br/>There can be additional processes such as sending of data to external CRMs which can trigger when order is successful. By having an intermediate order state, we wait for upsell funnel to get complete. Once it is done order status automatically moves to successful order state. This method enables additional processes to process correct order data.', 'woofunnels-upstroke-one-click-upsell' ),

						'label' => __( 'Custom Order State Label', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'flat_shipping_label'          => array(

						'label' => __( 'Shipping Label For Custom Fixed Rates', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'  => __( 'When Fixed rate shipping is applied, what would be the label of the shipping method?', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'create_new_order_status_fail' => array(

						'label'  => __( 'Order Status Of Failed Order When Upsell Is Accepted', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'   => __( '<br/><strong>Why it is needed?</strong> <br/>
Sometimes it may happen that due to failure of payment gateways, the user could not be charged for upsell rightaway.
In such scenarios a separate order is created for your record and is created and marked as failed. ', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => WFOCU_Common::get_order_status_settings(),
					),
					'ttl_funnel'                   => array(

						'label' => __( 'Forcefully End Upsell Funnel (in minutes)', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'  => __( '<br/><strong> Why it is needed? </strong><br/>Sometimes users may keep Offer Page open and not take a decision. Set up a realistic time in minutes after which upsell funnel forcefully ends.
This setting will determine time of Order Confirmation emails if it set to "When Upsell Funnel Ends". <br/> If you are not sure keep it by default to 15 mins', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'track_traffic_source'         => array(

						'label'  => '',
						'hint'   => __( 'Add traffic source as traffic_source and URL parameters (UTM) as parameters to all your events.', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'name'  => __( 'Track Traffic Source & UTMs', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),
					),
					'ga_track_traffic_source'      => array(

						'label'  => '',
						'hint'   => __( 'Add traffic source as traffic_source and URL parameters (UTM) as parameters to all your events.', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'name'  => __( 'Track Traffic Source & UTMs', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),
					),
					'enable_general_event'         => array(

						'label'  => '',
						'hint'   => __( 'Use the GeneralEvent for your Custom Audiences and Custom Conversions.', 'woofunnels-upstroke-one-click-upsell' ),
						'values' => array(
							array(
								'name'  => __( 'Enable General Event', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'yes',
							),
						),

					),
					'general_event_name'           => array(

						'label'       => '',
						'placeholder' => __( 'General Event Name', 'woofunnels-upstroke-one-click-upsell' ),
						'hint'        => __( 'Customize the name of general event.', 'woofunnels-upstroke-one-click-upsell' ),
					),

					'custom_aud_opt_conf' => array(
						'label' => '',
						'hint'  => __( 'Choose the parameters you want to send with purchase event', 'woofunnels-upstroke-one-click-upsell' ),

						'values' => array(
							array(
								'name'  => __( 'Add Town,State & Country Parameters', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'add_town_s_c',
							),
							array(
								'name'  => __( 'Add Payment Method Parameters', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'add_payment_method',
							),
							array(
								'name'  => __( 'Add Shipping Method Parameters', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'add_shipping_method',
							),
							array(
								'name'  => __( 'Add Coupon parameters', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'add_coupon',
							),
						),
					),

					'exclude_from_total' => array(
						'label' => '',
						'hint'  => __( 'Check above boxes to exclude shipping/taxes from the total.', 'woofunnels-upstroke-one-click-upsell' ),

						'values' => array(
							array(
								'name'  => __( 'Exclude Shipping from Total', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'is_disable_shipping',
							),
							array(
								'name'  => __( 'Exclude Taxes from Total', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'is_disable_taxes',
							),

						),
					),

					'gad_exclude_from_total' => array(
						'label' => '',
						'hint'  => __( 'Check above boxes to exclude shipping/taxes from the total.', 'woofunnels-upstroke-one-click-upsell' ),

						'values' => array(
							array(
								'name'  => __( 'Exclude Shipping from Total', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'is_disable_shipping',
							),
							array(
								'name'  => __( 'Exclude Taxes from Total', 'woofunnels-upstroke-one-click-upsell' ),
								'value' => 'is_disable_taxes',
							),

						),
					),

				),

				'global_settings_offer_confirmation' => array(
					'offer_header_label'   => array(
						'label' => __( 'These settings are applicable when you use custom upsell offer pages and have enabled confirmation.Need help with these settings? <a href="https://buildwoofunnels.com/docs/upstroke/global-settings/offer-confirmation/" target="_blank">Click here to learn about it.</a> ', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'offer_header_text'    => array(

						'label' => __( 'Header Text', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'offer_yes_btn_text'   => array(

						'label' => __( 'Acceptance Button Text', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'offer_skip_link_text' => array(

						'label' => __( 'Skip Link Text', 'woofunnels-upstroke-one-click-upsell' ),

					),

					'offer_yes_btn_bg_cl'  => array(

						'label' => __( 'Acceptance Button Background Color', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'offer_yes_btn_sh_cl'  => array(

						'label' => __( 'Acceptance Button Shadow Color', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'offer_yes_btn_txt_cl' => array(

						'label' => __( 'Acceptance Button Text Color', 'woofunnels-upstroke-one-click-upsell' ),

					),

					'offer_yes_btn_bg_cl_h'  => array(

						'label' => __( 'Acceptance Button Background Color (Hover)', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'offer_yes_btn_sh_cl_h'  => array(

						'label' => __( 'Acceptance Button Shadow Color (Hover)', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'offer_yes_btn_txt_cl_h' => array(

						'label' => __( 'Acceptance Button Text Color (Hover)', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'offer_no_btn_txt_cl'    => array(

						'label' => __( 'Skip Link Text Color', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'offer_no_btn_txt_cl_h'  => array(

						'label' => __( 'Skip Link Hover Text Color', 'woofunnels-upstroke-one-click-upsell' ),

					),

					'cart_opener_text'             => array(

						'label' => __( 'Re-open Badge Text', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'cart_opener_text_color'       => array(

						'label' => __( 'Re-open Badge Text Color', 'woofunnels-upstroke-one-click-upsell' ),

					),
					'cart_opener_background_color' => array(

						'label' => __( 'Re-open Badge Background Color', 'woofunnels-upstroke-one-click-upsell' ),

					),
				),
				'offer_settings'                     => array(
					'label_confirmation'           => array(
						'label' => __( 'Ask Confirmation', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'ask_confirmation'             => array(
						'label' => __( 'Ask for confirmation every time user accepts this offer. A new side cart will trigger and ask for confirmation if this option is enabled.', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'label_order'                  => array(
						'label' => __( 'Skip Offer', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'skip_exist'                   => array(
						'label' => __( 'Skip this offer if product(s) exist in parent order', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'skip_purchased'               => array(
						'label' => __( 'Skip this offer if buyer had ever purchased this product(s)', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'label_terminate'              => array(
						'label' => __( 'Terminate Funnel', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'terminate_if_accepted'        => array(
						'label' => __( 'Terminate this funnel, if this offer is accepted. Buyer will be redirected to thank you page.', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'terminate_if_declined'        => array(
						'label' => __( 'Terminate this funnel, if this offer is rejected. Buyer will be redirected to thank you page.', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'upsell_page_track_code_label' => array(
						'label' => __( 'Tracking Code', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'check_add_offer_script'       => array(
						'label' => __( 'Add tracking code if the buyer views this offer', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'upsell_page_track_code'       => array(
						'placeholder' => __( 'Paste your code here', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'check_add_offer_purchase'     => array(
						'label' => __( 'Add tracking code if the buyer accepts this offer', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'upsell_page_purchase_code'    => array(
						'placeholder' => __( 'Paste your code here', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'qty_selector_label'           => array(
						'label' => __( 'Quantity Selector', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'qty_selector'                 => array(
						'label' => __( 'Allow buyer to choose the quantity while purchasing this upsell product(s)', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'qty_max_label'                => array(
						'label' => __( 'Maximum Quantity', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'qty_max'                      => array(
						'placeholder' => __( 'Input Max Quantity', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'jump_to_offer'                => array(
						'label' => __( 'Dynamic Offer Path', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'jump_on_accepted'             => array(
						'label' => __( 'On acceptance, redirect buyers to', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'jump_on_rejected'             => array(
						'label' => __( 'On rejection, redirect buyers to', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'jump_to_offer_default_option' => array(
						'id'   => 'automatic',
						'name' => __( 'Select an Offer', 'woofunnels-upstroke-one-click-upsell' )
					),
					'jump_to_thankyou'             => array(
						'id'   => 'terminate',
						'name' => __( 'Thank You Page', 'woofunnels-upstroke-one-click-upsell' ),
					),
					'jump_optgroups'               => array(
						'upsells'   => __( 'Upsells', 'woofunnels-upstroke-one-click-upsell' ),
						'downsells' => __( 'Downsells', 'woofunnels-upstroke-one-click-upsell' ),
						'terminate' => __( 'Terminate Funnel', 'woofunnels-upstroke-one-click-upsell' ),
					),
				),
			),
			'funnel_settings'                       => WFOCU_Core()->funnels->get_funnel_option(),
			'global_settings'                       => WFOCU_Core()->data->get_option(),
			'funnel_advanced_settings'              => array( 'next_move_install' => false ),
			'shortcodes'                            => $this->get_shortcodes_list(),
			'templates'                             => WFOCU_Core()->template_loader->get_templates(),
			'permalinkStruct'                       => get_option( 'permalink_structure' ),
			'gensettingshelptextfb'                 => sprintf( __( 'Enter Facebook Pixel ID in <a href="%s">General > Tracking IDs </a>' ), BWF_Admin_General_Settings::get_instance()->get_settings_link() ),
			'gensettingshelptextga'                 => sprintf( __( 'Enter Google Analytics ID in <a href="%s">General > Tracking IDs </a>' ), BWF_Admin_General_Settings::get_instance()->get_settings_link() ),
			'gensettingshelptextgad'                => sprintf( __( 'These settings are now moved to <a href="%s">WooFunnels > Settings > General  </a>' ), BWF_Admin_General_Settings::get_instance()->get_settings_link() ),
			'funnel_setting_tabs'                   => array(
				'basic'    => __( 'Basic', 'woofunnels-upstroke-one-click-upsell' ),
				'advanced' => __( 'Advanced', 'woofunnels-upstroke-one-click-upsell' ),
			),
		);
		$data['isNOGateway'] = true;
		if ( $gateways_list && is_array( $gateways_list ) && count( $gateways_list ) > 0 ) {
			$data['isNOGateway'] = false;
		}
		$funnel_id = filter_input( INPUT_GET, 'edit', FILTER_SANITIZE_NUMBER_INT );
		if ( $funnel_id > 0 ) {
			$data['is_funnel_upsell'] = ( get_post_meta( $funnel_id, '_bwf_in_funnel', true ) > 0 );
		}
		$data['isBeaverProActive'] = WFOCU_Plugin_Compatibilities::get_compatibility_class( 'beaver' )->is_pro_active();

		$data['pageBuildersOptions'] = WFOCU_Core()->template_loader->get_plugins_groupby_page_builders();
		$data['pageBuildersTexts']   = WFOCU_Core()->template_loader->localize_page_builder_texts();
		wp_localize_script( 'wfocu-admin', 'wfocuParams', $data );

	}

	public function admin_customizer_enqueue_assets() {
		if ( WFOCU_Common::is_load_admin_assets( 'customizer' ) ) {

			wp_enqueue_style( 'wfocu-customizer', $this->admin_url . '/assets/css/wfocu-customizer.css', array(), WFOCU_VERSION_DEV );
		}
	}

	public function upstroke_page() {
		if ( isset( $_GET['page'] ) && 'upstroke' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_GET['section'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				include_once( $this->admin_path . '/view/funnel-builder-view.php' );  // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			} elseif ( isset( $_GET['tab'] ) && $_GET['tab'] === 'settings' ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				include_once( $this->admin_path . '/view/global-settings.php' );  // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			} elseif ( isset( $_GET['tab'] ) && $_GET['tab'] === 'import' ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				include_once( $this->admin_path . '/view/flex-import.php' );  // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			} elseif ( isset( $_GET['tab'] ) && $_GET['tab'] === 'export' ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				include_once( $this->admin_path . '/view/flex-export.php' );  // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			} elseif ( ( isset( $_GET['tab'] ) && $_GET['tab'] === 'bwf_settings' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				BWF_Admin_General_Settings::get_instance()->__callback();
			} else {
				require_once( WFOCU_PLUGIN_DIR . '/admin/includes/class-wfocu-post-table.php' );  // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
				include_once( $this->admin_path . '/view/funnel-admin.php' );  // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			}
		}
		if ( 'yes' === filter_input( INPUT_GET, 'activated', FILTER_SANITIZE_STRING ) ) {
			flush_rewrite_rules(); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules
		}
	}

	public function js_variables() {
		$bwb_admin_setting = BWF_Admin_General_Settings::get_instance();

		$data        = array(
			'site_url'    => site_url(),
			'editor_path' => admin_url( 'post.php?post={{current_offer}}&action=edit' ),
			'offer_slug'  => $bwb_admin_setting->get_option( 'wfocu_page_base' ),
			'texts'       => array(
				'closeSwal'              => __( 'Close', 'woofunnels-upstroke-one-click-upsell' ),
				'changesSaved'           => __( 'Changes Saved', 'woofunnels-upstroke-one-click-upsell' ),
				'update_template'        => __( 'Template Updated Successfully', 'woofunnels-upstroke-one-click-upsell' ),
				'clear_template'         => __( 'Template Removed Successfully', 'woofunnels-upstroke-one-click-upsell' ),
				'product_success'        => __( 'Offer Saved Successfully', 'woofunnels-upstroke-one-click-upsell' ),
				'shortcode_copy_message' => __( 'Shortcode Copied!', 'woofunnels-upstroke-one-click-upsell' ),
				'importing'              => __( 'Importing...', 'woofunnels-upstroke-one-click-upsell' ),
			),
		);
		$funnel_post = get_post( WFOCU_Core()->funnels->get_funnel_id() );

		if ( false === is_null( $funnel_post ) ) {
			$data['id']          = WFOCU_Core()->funnels->get_funnel_id();
			$data['funnel_name'] = html_entity_decode( trim( get_the_title( $funnel_post ) ) );
			$data['funnel_desc'] = $funnel_post->post_content;
			$data['offers_link'] = admin_url( 'admin.php?page=upstroke&section=offers&edit=' . $data['id'] );
		}


		if ( $this->is_upstroke_page( 'offers' ) || $this->is_upstroke_page( 'design' ) ) {
			$get_all_template_groups = WFOCU_Core()->template_loader->get_all_groups();
			$data['edit_links']      = [];
			$data['preview_links']   = [];
			$allTemplates            = WFOCU_Core()->template_loader->get_templates();
			$data['alltemplates']    = $allTemplates;
			foreach ( $get_all_template_groups as $key => $template_group ) {
				$data['edit_links'][ $key ]      = $template_group->get_edit_link();
				$data['preview_links'][ $key ]   = $template_group->get_preview_link();
				$data['template_groups'][ $key ] = $template_group->get_nice_name();
			}
			$data['preview_links']['custom_page']   = site_url() . '?p={{custom_page_id}}';
			$data['template_groups']['custom_page'] = __( 'Custom Page', 'woofunnels-upstroke-one-click-upsell' );
			$data['custom_page_image']              = WFOCU_PLUGIN_URL . '/admin/assets/img/thumbnail-custom-page.jpg';
			$data_funnels                           = WFOCU_Core()->funnels->get_funnel_offers_admin();

			$data = array_merge( $data, $data_funnels );

		}
		$data['button_texts'] = array(
			'importingtext' => __( 'Importing...', 'woofunnels-upstroke-one-click-upsell' ),
			're_apply'      => __( 'Re-Apply', 'woofunnels-upstroke-one-click-upsell' ),
			'apply'         => __( 'Apply', 'woofunnels-upstroke-one-click-upsell' ),
			'import'        => __( 'Import', 'woofunnels-upstroke-one-click-upsell' ),
		);


		$state                       = absint( WooFunnels_Dashboard::$classes['WooFunnels_DB_Updater']->get_upgrade_state() );
		$data['bwf_needs_indexning'] = in_array( $state, array( 0, 1, 2, 3, 6 ), true );
		$help_text                   = __( 'This setting needs indexing of past orders. Go to', 'woofunnels-upstroke-one-click-upsell' );
		$link_text                   = __( 'Tools > Index Orders', 'woofunnels-upstroke-one-click-upsell' );
		$after_text                  = __( ' and click \'Start\' to index orders.', 'woofunnels-upstroke-one-click-upsell' );

		if ( 3 === $state ) {
			$help_text  = __( 'Indexing of orders is underway. This setting will work once the process completes.', 'woofunnels-upstroke-one-click-upsell' );
			$link_text  = '';
			$after_text = '';
		}

		$data['indexing_texts']      = array(
			'link'       => $link_text,
			'help_text'  => $help_text,
			'after_text' => $after_text,
		);
		$data['preset_texts']        = array(
			'success' => __( 'Preset applied successfully.', 'woofunnels-upstroke-one-click-upsell' ),
		);
		$data['add_funnel']          = array(
			'creating'    => __( 'Creating...', 'woofunnels-upstroke-one-click-upsell' ),
			'label_texts' => array(
				'funnel_name' => array(
					'label'       => __( 'Name', 'woofunnels-upstroke-one-click-upsell' ),
					'placeholder' => __( 'Enter Name', 'woofunnels-upstroke-one-click-upsell' ),
				),
				'funnel_desc' => array(

					'label'       => __( 'Description', 'woofunnels-upstroke-one-click-upsell' ),
					'placeholder' => __( 'Enter Description (Optional)', 'woofunnels-upstroke-one-click-upsell' )
				),
			)
		);
		$data['price_tooltip_texts'] = array(
			'of'           => __( 'of', 'woofunnels-upstroke-one-click-upsell' ),
			'fixed_amount' => __( '(fixed discount)', 'woofunnels-upstroke-one-click-upsell' ),
			'shipping'     => __( '(shipping)', 'woofunnels-upstroke-one-click-upsell' ),
			'dynamic_ship' => __( '(Dynamic Shipping Cost)', 'woofunnels-upstroke-one-click-upsell' ),
		);
		$data['funnel_duplicate']    = array(
			'success' => __( 'Funnel duplicated.', 'woofunnels-upstroke-one-click-upsell' ),
		);

		if ( $this->is_upstroke_page( 'settings' ) ) {
			$data['nextmoveLocals'] = array(
				'loading'            => __( 'Please wait....', 'woofunnels-upstroke-one-click-upsell' ),
				'installing'         => __( 'Installing', 'woofunnels-upstroke-one-click-upsell' ),
				'activate_incomlete' => __( 'Unable to Activate', 'woofunnels-upstroke-one-click-upsell' ),
				'cta_text'           => '',
			);
			if ( class_exists( 'XLWCTY_Core' ) ) {

				/**
				 * Plugin is installed & activated
				 */
				$funnel_id      = filter_input( INPUT_GET, 'edit', FILTER_SANITIZE_NUMBER_INT );
				$thank_page_ids = ( $funnel_id > 0 ) ? get_post_meta( $funnel_id, 'xlwcty_ids', true ) : array();
				$thank_page_ids = is_array( $thank_page_ids ) && count( $thank_page_ids ) > 0 ? $thank_page_ids : 0;

				if ( $thank_page_ids > 0 ) {
					$data['nextMoveState']      = 'configured';
					$this->thank_you_page_posts = array_map( 'get_post', $thank_page_ids );
				} else {
					$data['nextMoveState']   = 'ready_to_configure';
					$get_compatibility_class = WFOCU_Plugin_Compatibilities::get_compatibility_class( 'xlwcty' );
					if ( class_exists( 'XLWCTY_EDD_License' ) && version_compare( XLWCTY_VERSION, $get_compatibility_class::PRO_MIN_VAR, '<' ) ) {

						$data['nextMoveState'] = 'unable_to_configure';

					} elseif ( ! class_exists( 'XLWCTY_EDD_License' ) && version_compare( XLWCTY_VERSION, $get_compatibility_class::LITE_MIN_VAR, '<' ) ) {
						$data['nextMoveState'] = 'unable_to_configure';
					}

					$data['nextmoveLocals']['cta_text'] = __( 'Configure', 'woofunnels-upstroke-one-click-upsell' );
				}


			} else {
				$data['nextmoveLocals']['cta_text'] = __( 'Yes! Install & Activate NextMove', 'woofunnels-upstroke-one-click-upsell' );
				$data['nextMoveState']              = 'ready_to_install';
				if ( true === file_exists( WP_PLUGIN_DIR . '/woo-thank-you-page-nextmove-lite/thank-you-page-for-woocommerce-nextmove-lite.php' ) || true === file_exists( WP_PLUGIN_DIR . '/thank-you-page-for-woocommerce-nextmove/woocommerce-thankyou-pages.php' ) ) {
					$data['nextMoveState']              = 'ready_to_activate';
					$data['nextmoveLocals']['cta_text'] = __( 'Activate NextMove', 'woofunnels-upstroke-one-click-upsell' );

				}
			}


		}

		?>
		<script>window.wfocu = <?php echo wp_json_encode( $data )?>;</script>
		<?php

	}

	public function is_upstroke_page( $section = '' ) {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'upstroke' && isset( $_GET['tab'] ) && $_GET['tab'] === 'bwf_settings' && 'bwf_settings' === $section ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return true;
		}


		if ( isset( $_GET['page'] ) && $_GET['page'] === 'upstroke' && '' === $section ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return true;
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] === 'upstroke' && isset( $_GET['section'] ) && $_GET['section'] === $section ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return true;
		}

		return false;
	}

	public function admin_footer_text( $footer_text ) {
		if ( WFOCU_Common::is_load_admin_assets( 'all' ) ) {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return $footer_text;
			}
			$footer_text = __( 'Thanks for creating with WooFunnels. Need Help? <a href="https://buildwoofunnels.com/support" target="_blank">Contact Support</a>', 'woofunnels-upstroke-one-click-upsell' );

		}


		return $footer_text;
	}

	public function update_footer( $footer_text ) {
		if ( WFOCU_Common::is_load_admin_assets( 'builder' ) ) {
			return '';
		}

		return $footer_text;
	}

	public function maybe_reset_transients( $post_id, $post = null ) {
		//Check it's not an auto save routine
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		//Perform permission checks! For example:
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( class_exists( 'WooFunnels_Transient' ) && ( is_object( $post ) && $post->post_type === WFOCU_Common::get_funnel_post_type_slug() ) ) {
			$woofunnels_transient_obj = WooFunnels_Transient::get_instance();
			$woofunnels_transient_obj->delete_all_transients( 'upstroke' );
		}

	}


	public function maybe_set_funnel_id() {

		if ( $this->is_upstroke_page() && isset( $_GET['edit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			WFOCU_Core()->funnels->set_funnel_id( wc_clean( $_GET['edit'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
	}

	/**
	 * @hooked over `delete_post`
	 *
	 * @param $post_id
	 */
	public function clear_transients_on_delete( $post_id ) {

		$get_post_type = get_post_type( $post_id );

		if ( WFOCU_Common::get_funnel_post_type_slug() === $get_post_type ) {
			if ( class_exists( 'WooFunnels_Transient' ) ) {
				$woofunnels_transient_obj = WooFunnels_Transient::get_instance();
				$woofunnels_transient_obj->delete_all_transients( 'upstroke' );
			}
			do_action( 'wfocu_funnel_admin_deleted', $post_id );
		}

	}


	/**
	 * @hooked over `delete_post`
	 * Delete the funnel record if any from the database on permanent deletion of order.
	 *
	 * @param mixed $post_id
	 */
	public function clear_session_record_on_shop_order_delete( $post_id ) {
		$get_post_type = get_post_type( $post_id );

		/**
		 * @todo keep eye on WC upgrades so that we can handle this when Orders are no longer be posts.
		 */
		if ( 'shop_order' === $get_post_type ) {

			$sess_id = WFOCU_Core()->session_db->get_session_id_by_order_id( $post_id );

			if ( ! empty( $sess_id ) ) {
				WFOCU_Core()->session_db->delete( $sess_id );
			}
		}
	}

	public function maybe_activate_post() {

		if ( isset( $_GET['action'] ) && $_GET['action'] === 'wfocu-post-activate' ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( wc_clean( $_GET['_wpnonce'] ), 'wfocu-post-activate' ) ) {

				$postID = filter_input( INPUT_GET, 'postid', FILTER_SANITIZE_STRING );
				if ( $postID ) {
					wp_update_post( array(
						'ID'          => $postID,
						'post_status' => 'publish',
					) );
					wp_safe_redirect( admin_url( 'admin.php?page=upstroke' ) );
					exit;
				}
			} else {
				die( esc_attr__( 'Unable to Activate', 'woofunnels-upstroke-one-click-upsell' ) );
			}
		}
	}

	public function maybe_deactivate_post() {
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'wfocu-post-deactivate' ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( wc_clean( $_GET['_wpnonce'] ), 'wfocu-post-deactivate' ) ) {

				$postID = filter_input( INPUT_GET, 'postid', FILTER_SANITIZE_STRING );
				if ( $postID ) {
					wp_update_post( array(
						'ID'          => $postID,
						'post_status' => WFOCU_SLUG . '-disabled',
					) );

					wp_safe_redirect( admin_url( 'admin.php?page=upstroke' ) );
					exit;
				}
			} else {
				die( esc_attr__( 'Unable to Deactivate', 'woofunnels-upstroke-one-click-upsell' ) );
			}
		}
	}


	public function maybe_print_mergetag_helpbox() {


		if ( false === WFOCU_Common::is_load_admin_assets( 'customizer' ) ) {
			return;
		}
		$offer_data = WFOCU_Core()->offers->get_offer_meta( WFOCU_Core()->customizer->offer_id );
		?>

		<div class='' id="wfocu_shortcode_help_box" style="display: none;">

			<h3><?php esc_attr_e( 'Merge Tags', 'woofunnels-upstroke-one-click-upsell' ); ?></h3>
			<div style="font-size: 1.1em; margin: 5px;"><?php esc_attr_e( 'Here are are set of Merge Tags that can be used on this page.', 'woofunnels-upstroke-one-click-upsell' ); ?> </i> </div>
			<?php foreach ( $offer_data->products as $hash => $product_id ) { ?>
				<h4><?php esc_attr_e( sprintf( 'Product: %1$s', wc_get_product( $product_id )->get_title() ), 'woofunnels-upstroke-one-click-upsell' ); ?></h4>

				<table class="table widefat">
					<thead>
					<tr>
						<td><?php esc_attr_e( 'Title', 'woofunnels-upstroke-one-click-upsell' ); ?></td>
						<td style="width: 70%;"><?php esc_attr_e( 'Merge Tags', 'woofunnels-upstroke-one-click-upsell' ); ?></td>

					</tr>
					</thead>
					<tbody>

					<tr>
						<td>
							<?php esc_attr_e( 'Product Offer Price', 'woofunnels-upstroke-one-click-upsell' ); ?>


						</td>
						<td>
							<input type="text" style="width: 75%;" readonly onClick="this.select()" value='<?php ( printf( '{{product_offer_price key="%s"}}', esc_attr( $hash ) ) ); ?>'/>
						</td>

					</tr>
					<tr>
						<td>
							<?php esc_attr_e( 'Product Regular Price', 'woofunnels-upstroke-one-click-upsell' ); ?>
						</td>
						<td>
							<input type="text" style="width: 75%;" readonly onClick="this.select()"
								   value='<?php printf( '{{product_regular_price key="%s"}}', esc_attr( $hash ) ); ?>'/>
						</td>

					</tr>
					<tr>
						<td>

							<?php esc_attr_e( ' Product Price HTML', 'woofunnels-upstroke-one-click-upsell' ); ?>
						</td>
						<td>
							<input type="text" style="width: 75%;" readonly onClick="this.select()"
								   value='<?php printf( '{{product_price_full key="%s"}}', esc_attr( $hash ) ); ?>'/>
						</td>

					</tr>

					<tr>
						<td>
							<?php esc_attr_e( 'Product Offer Save Value', 'woofunnels-upstroke-one-click-upsell' ); ?>
						</td>
						<td>
							<input type="text" style="width: 75%;" readonly onClick="this.select()"
								   value='<?php printf( '{{product_save_value key="%s"}}', esc_attr( $hash ) ); ?>'/>
						</td>

					</tr>
					<tr>
						<td>
							<?php esc_attr_e( ' Product Offer Save Percentage', 'woofunnels-upstroke-one-click-upsell' ); ?>
						</td>

						<td>
							<input type="text" style="width: 75%;" readonly onClick="this.select()"
								   value='<?php printf( '{{product_save_percentage key="%s"}}', esc_attr( $hash ) ); ?>'/>
						</td>

					</tr>

					<tr>
						<td>
							<?php esc_attr_e( ' Product Single Unit Price', 'woofunnels-upstroke-one-click-upsell' ); ?>
						</td>

						<td>
							<input type="text" style="width: 75%;" readonly onClick="this.select()"
								   value='<?php printf( '{{product_single_unit_price key="%s"}}', esc_attr( $hash ) ); ?>'/>
						</td>

					</tr>

					<tr>
						<td>
							<?php esc_attr_e( 'Product Offer Save Value & Percentage', 'woofunnels-upstroke-one-click-upsell' ); ?>

						</td>
						<td>
							<input type="text" style="width: 75%;" readonly onClick="this.select()"
								   value='<?php printf( '{{product_savings key="%s"}}', esc_attr( $hash ) ); ?>'/>
						</td>

					</tr>


					</tbody>


				</table>
			<?php } ?>
			<br/>

			<h3>Order Merge Tags</h3>
			<table class="table widefat">
				<thead>
				<tr>
					<td width="300">Name</td>
					<td>Syntax</td>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( WFOCU_Dynamic_Merge_Tags::get_all_tags() as $tag ) : ?>
					<tr>
						<td>
							<?php echo esc_html( $tag['name'] ); ?>
						</td>
						<td>
							<input type="text" style="width: 75%;" onClick="this.select()" readonly
								   value='<?php echo '{{' . esc_html( $tag['tag'] ) . '}}'; ?>'/>
							<?php
							if ( isset( $tag['desc'] ) && $tag['desc'] !== '' ) {
								echo '<p>' . wp_kses_post( $tag['desc'] ) . '</p>';
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<br/>

			<h3>Other Merge Tags</h3>
			<table class="table widefat">
				<thead>
				<tr>
					<td width="300">Name</td>
					<td>Syntax</td>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( WFOCU_Dynamic_Merge_Tags::get_all_other_tags() as $tag ) : ?>
					<tr>
						<td>
							<?php echo esc_attr( $tag['name'] ); ?>
						</td>
						<td>
							<input type="text" style="width: 75%;" onClick="this.select()" readonly
								   value='<?php echo '{{' . esc_html( $tag['tag'] ) . '}}'; ?>'/>
							<?php
							if ( isset( $tag['desc'] ) && $tag['desc'] !== '' ) {
								echo '<p>' . wp_kses_post( $tag['desc'] ) . '</p>';
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<?php
	}

	/**
	 * Hooked over 'plugin_action_links_{PLUGIN_BASENAME}' WordPress hook to add deactivate popup support
	 *
	 * @param array $links array of existing links
	 *
	 * @return array modified array
	 */
	public function plugin_actions( $links ) {
		$links['deactivate'] .= '<i class="woofunnels-slug" data-slug="' . WFOCU_PLUGIN_BASENAME . '"></i>';

		return $links;
	}

	public function maybe_handle_http_referer() {
		if ( $this->is_upstroke_page() && ! empty( $_REQUEST['_wp_http_referer'] ) && ! empty( $_REQUEST['REQUEST_URI'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Missing
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'offer_state' ), wp_unslash( wc_clean( $_SERVER['REQUEST_URI'] ) ) ) );
			exit;
		}

	}

	public function tooltip( $text ) {
		?>
		<span class="wfocu-help"><i class="icon"></i><div class="helpText"><?php echo( $text ); ?></div></span> <?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
	}

	public function hide_test_gateway_from_admin_list() {
		?>
		<style>
            table.wc_gateways tr[data-gateway_id="wfocu_test"] {
                display: none !important;
            }
		</style>
		<?php
	}

	public function maybe_show_wizard() {
		if ( empty( $_GET['page'] ) || 'upstroke' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		if ( isset( $_GET['tab'] ) && strpos( wc_clean( $_GET['tab'] ), 'wizard' ) !== false ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		if ( true === apply_filters( 'wfocu_override_wizard', false ) ) {
			return;
		}

		if ( WFOCU_Core()->support->is_license_present() === false ) {
			wp_redirect( admin_url( 'admin.php?page=woofunnels&tab=' . WFOCU_SLUG . '-wizard' ) );
			exit;
		}

	}

	/**
	 * Remove all the notices in our dashboard pages as they might break the design.
	 */
	public function maybe_remove_all_notices_on_page() {
		if ( isset( $_GET['page'] ) && 'upstroke' === $_GET['page'] && isset( $_GET['section'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			remove_all_actions( 'admin_notices' );
		}
	}


	public function check_db_version() {

		$get_db_version = get_option( '_wfocu_db_version', '0.0.0' );

		if ( version_compare( WFOCU_DB_VERSION, $get_db_version, '>' ) ) {

			//needs checking
			global $wpdb;
			include_once plugin_dir_path( WFOCU_PLUGIN_FILE ) . 'db/tables.php';
			$tables = new WFOCU_DB_Tables( $wpdb );

			$tables->add_if_needed();

		}

	}


	function show_upsell_total_in_order_listings( $total, $order ) {
		global $current_screen;
		if ( ! $current_screen instanceof WP_Screen || ! $order instanceof WC_Order || $current_screen->base !== 'edit' || $current_screen->id !== 'edit-shop_order' || $current_screen->post_type !== 'shop_order' ) {
			return $total;
		}

		$result = $order->get_meta( '_wfocu_upsell_amount', true );

		if ( empty( $result ) ) {
			$order_id = WFOCU_WC_Compatibility::get_order_id( $order );

			$upstroke_data = WFOCU_Core()->track->query_results( array(
				'data'         => array(
					'value' => array(
						'type'     => 'col',
						'function' => 'SUM',
						'name'     => 'upsells',
					),
				),
				'where'        => array(
					array(
						'key'      => 'events.action_type_id',
						'value'    => 4,
						'operator' => '=',
					),
					array(
						'key'      => 'session.order_id',
						'value'    => $order_id,
						'operator' => '=',
					),
				),
				'query_type'   => 'get_results',
				'session_join' => true,
			) );

			$result = ( count( $upstroke_data ) > 0 && $upstroke_data[0]->upsells !== null ) ? $upstroke_data[0]->upsells : 0;

			if ( $result === 0 ) {
				return $total;
			}

			if ( 0 < $result ) {
				$order->update_meta_data( '_wfocu_upsell_amount', $result );
				$order->save_meta_data();
			}
		}
		$html  = '<br/>
<p style="font-size: 12px;"><em> ' . sprintf( esc_html__( 'UpStroke: %s' ), wc_price( $result, array( 'currency' => get_option( 'woocommerce_currency' ) ) ) ) . '</em></p>';
		$total = $total . $html;

		return $total;
	}


	/**
	 * @hooked `shutdown`
	 * As we moved to DB version 1.0 with the new table structure.
	 * We need to ensure the data for the upsell gross amount get stored in the order meta.
	 *
	 */
	public function maybe_update_upsell_gross_total_to_order_meta() {

		$check_if_already_updated_meta = get_option( '_wfocu_db_total_meta', 'no' );

		if ( 'no' === $check_if_already_updated_meta ) {
			global $wpdb;

			$query = $wpdb->prepare( 'SELECT `order_id`, SUM(`value`) as `amount` FROM `' . $wpdb->prefix . 'wfocu_events` WHERE `action_type_id` = %s GROUP BY `order_id` LIMIT 5000', '4' ); //db call ok; no-cache ok; WPCS: unprepared SQL ok.

			$a = $wpdb->get_results( $query, ARRAY_A ); //db call ok; no-cache ok; WPCS: unprepared SQL ok.

			if ( is_array( $a ) && count( $a ) > 0 ) {
				foreach ( $a as $v ) {
					$order_id   = $v['order_id'];
					$upsell_val = $v['amount'];
					update_post_meta( $order_id, '_wfocu_upsell_amount', number_format( $upsell_val, 2, '.', '' ) );
				}
			}
			update_option( '_wfocu_db_total_meta', 'yes', true );
		}
	}


	public function toolbar_link_to_xlplugins( $wp_admin_bar ) {
		if ( defined( 'WFOCU_IS_DEV' ) && true === WFOCU_IS_DEV ) {
			if ( is_order_received_page() ) {
				global $wp;
				$args = array(
					'id'    => 'wfocu_admin_vorder',
					'title' => __( 'View Order', 'woofunnels-upstroke-one-click-upsell' ),
					'href'  => admin_url( 'post.php?post=' . $wp->query_vars['order-received'] . '&action=edit' ),

				);
				$wp_admin_bar->add_node( $args );
			}

		}
		if ( defined( 'WFOCU_IS_DEV' ) && true === WFOCU_IS_DEV ) {
			$args = array(
				'id'    => 'wfocu_admin_logs',
				'title' => __( 'Logs', 'woofunnels-upstroke-one-click-upsell' ),
				'href'  => admin_url( 'admin.php?page=wc-status&tab=logs' ),
				'meta'  => array( 'class' => 'wfocu_admin_logs' ),
			);
			$wp_admin_bar->add_node( $args );

			$wp_admin_bar->add_node( array(
				'parent' => 'wfocu_admin_logs',
				'id'     => 'wfocu_wc_admin_logs',
				'title'  => __( 'WC Logs', 'woofunnels-upstroke-one-click-upsell' ),
				'href'   => admin_url( 'admin.php?page=wc-status&tab=logs' ),
				'meta'   => array( 'class' => 'wfocu_admin_logs' ),
			) );
			$wp_admin_bar->add_node( array(
				'parent' => 'wfocu_admin_logs',
				'id'     => 'wfocu_bwf_admin_logs',
				'title'  => __( 'BWF Logs', 'woofunnels-upstroke-one-click-upsell' ),
				'href'   => admin_url( 'admin.php?page=woofunnels&tab=logs' ),
				'meta'   => array( 'class' => 'wfocu_admin_logs' ),
			) );


			$arr = WC_Log_Handler_File::get_log_files();

			if ( count( $arr ) > 0 ) {
				$data = end( $arr );
				$wp_admin_bar->add_node( array(
					'parent' => 'wfocu_admin_logs',
					'id'     => 'wfocu_wfocu_admin_logs',
					'title'  => __( 'UpStroke Log', 'woofunnels-upstroke-one-click-upsell' ),
					'href'   => admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . $data ),
					'meta'   => array( 'class' => 'wfocu_admin_logs' ),
				) );
			}
		}

		return $wp_admin_bar;

	}


	public function set_wc_payment_gateway_column( $header ) {

		$header_new = array_slice( $header, 0, count( $header ) - 1, true ) + array( 'wfocu' => __( 'Upsell Allowed', 'woocommerce-subscriptions' ) ) + // Ideally, we could add a link to the docs here, but the title is passed through esc_html()
		              array_slice( $header, count( $header ) - 1, count( $header ) - ( count( $header ) - 1 ), true );

		return $header_new;
	}

	public function wc_payment_gateway_column_content( $gateway ) {
		$supported_gateways = WFOCU_Core()->gateways->get_supported_gateways();
		echo '<td class="renewals">';
		$status_html = '-';
		if ( ( is_array( $supported_gateways ) && array_key_exists( $gateway->id, $supported_gateways ) ) ) {
			$status_html = '<span class="status-enabled tips" data-tip="' . esc_attr__( 'Supports UpSell payments with the UpStroke One Click Upsell.', 'woocommerce-subscriptions' ) . '">' . esc_html__( 'Yes', 'woocommerce-subscriptions' ) . '</span>';
		}

		$allowed_html                     = wp_kses_allowed_html( 'post' );
		$allowed_html['span']['data-tip'] = true;

		/**
		 * Automatic Renewal Payments Support Status HTML Filter.
		 *
		 * @param string $status_html
		 * @param \WC_Payment_Gateway $gateway
		 *
		 * @since 2.0
		 *
		 */
		echo wp_kses( apply_filters( 'woocommerce_payment_gateways_upstroke_support_status_html', $status_html, $gateway ), $allowed_html );

		echo '</td>';
	}

	/**
	 * Initiate WFOCU_Background_Updater class
	 * @see maybe_update_database_update()
	 */
	public function init_background_updater() {

		if ( class_exists( 'WFOCU_Background_Updater' ) ) {
			$this->updater = new WFOCU_Background_Updater();
		}

	}


	/**
	 * @hooked over `admin_head`
	 * This method takes care of database updating process.
	 * Checks whether there is a need to update the database
	 * Iterates over define callbacks and passes it to background updater class
	 */
	public function maybe_update_database_update() {

		if ( is_null( $this->updater ) ) {

			/**
			 * Update the option as tables are updated.
			 */
			update_option( '_wfocu_db_version', WFOCU_DB_VERSION, true );

			return;
		}
		$task_list          = array(
			'2.0' => array( 'wfocu_maybe_update_sessions_on_2_0', 'wfocu_rest_update_missing_gateways' ),
			'3.0' => array( 'wfocu_update_fullwidth_page_template' ),
			'3.3' => array( 'wfocu_update_general_setting_fields' ),
			'3.4' => array( 'wfocu_migrate_public_images' ),
		);
		$current_db_version = get_option( '_wfocu_db_version', '0.0.0' );
		$update_queued      = false;

		foreach ( $task_list as $version => $tasks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $tasks as $update_callback ) {

					$this->updater->push_to_queue( $update_callback );
					$update_queued = true;
				}
			}
		}

		if ( $update_queued ) {

			$this->updater->save()->dispatch();
		}

		update_option( '_wfocu_db_version', WFOCU_DB_VERSION, true );

	}

	public function maybe_update_upstroke_version_in_option() {

		$get_db_version = get_option( '_wfocu_plugin_version', '' );

		if ( version_compare( $get_db_version, WFOCU_VERSION, '<' ) ) {
			update_option( '_wfocu_plugin_version', WFOCU_VERSION, true );
			update_option( '_wfocu_plugin_last_updated', time(), true );
		}

	}


	/**
	 * Defines scripts needed for "no conflict mode".
	 *
	 * @since  Unknown
	 * @access public
	 * @global $wp_scripts
	 *
	 * @uses WFOCU_Admin::no_conflict_mode()
	 */
	public function no_conflict_mode_script() {
		if ( ! apply_filters( 'wfocu_no_conflict_mode', true ) ) {
			return;
		}

		global $wp_scripts;

		$wp_required_scripts    = array( 'admin-bar', 'common', 'jquery-color', 'utils', 'svg-painter' );
		$wfocu_required_scripts = apply_filters( 'wfocu_no_conflict_scripts', array(
			'common'       => array(
				'jquery-ui-sortable',
				'jquery-ui-sortable',
				'wfocu-admin-ajax',
				'wfocu-admin',
				'wc-backbone-modal',
				'accounting',
				'wfocu-izimodal',
				'wfocu-admin-builder',
				'sack'
			),
			'settings'     => array(
				'wfocu-vue-multiselect',
				'wfocu-vuejs',
				'wfocu-vue-vfg',
				'updates',
			),
			'rules'        => array( 'wfocu-chosen', 'wfocu-ajax-chosen', 'jquery-masked-input', 'wfocu-admin-app' ),
			'offers'       => array(
				'wfocu-vue-multiselect',
				'wfocu-vuejs',
				'wfocu-vue-vfg',
				'wfocu_autoship_admin_script',
				'wfocu_dynamic_shipping_script',
				'wfocu_subscription_admin_script'
			),
			'design'       => array(
				'wfocu-vue-multiselect',
				'wfocu-vuejs',
				'wfocu-vue-vfg',
			),
			'bwf_settings' => array( 'bwf-admin-settings' ),
		) );
		$this->no_conflict_mode( $wp_scripts, $wp_required_scripts, $wfocu_required_scripts, 'scripts' );
	}

	/**
	 * Defines styles needed for "no conflict mode"
	 *
	 * @since  Unknown
	 * @access public
	 * @global $wp_styles
	 *
	 * @uses   WFOCU_Admin::no_conflict_mode()
	 */
	public function no_conflict_mode_style() {
		if ( ! apply_filters( 'wfocu_no_conflict_mode', true ) ) {
			return;
		}
		global $wp_styles;
		$wp_required_styles    = array( 'common', 'admin-bar', 'colors', 'ie', 'wp-admin', 'editor-style' );
		$wfocu_required_styles = apply_filters( 'wfocu_no_conflict_styles', array(
			'common'   => array( 'wfocu-funnel-bg', 'woofunnels-admin-font', 'wfocu-izimodal', 'wfocu-modal', 'wfocu-admin', 'woocommerce_admin_styles' ),
			'settings' => array(
				'wfocu-modal',
				'wfocu-vue-multiselect',
			),
			'rules'    => array(
				'wfocu-chosen-app',
				'wfocu-admin-app',
				'wfocu-modal',
				'wfocu-vue-multiselect',
			),
			'offers'   => array(
				'wfocu-vue-multiselect',
			),
			'design'   => array(
				'wfocu-vue-multiselect',
			),
		) );

		$this->no_conflict_mode( $wp_styles, $wp_required_styles, $wfocu_required_styles, 'styles' );
	}

	/**
	 * Runs "no conflict mode".
	 *
	 * @param WP_Scripts $wp_objects WP_Scripts object.
	 * @param array $wp_required_objects Scripts required by WordPress Core.
	 * @param array $wfocu_required_objects Scripts required by WooFunnels Forms.
	 * @param string $type Determines if scripts or styles are being run through the function.
	 *
	 * @since   Unknown
	 * @access  private
	 *
	 * @used-by WFOCU_Admin::no_conflict_mode_style()
	 * @used-by WFOCU_Admin::no_conflict_mode_style()
	 *
	 */
	public function no_conflict_mode( &$wp_objects, $wp_required_objects, $wfocu_required_objects, $type = 'scripts' ) {

		$current_page = trim( strtolower( filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ) ) );

		if ( 'upstroke' !== $current_page ) {
			return;
		}

		$section    = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING );
		$tab        = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
		$is_section = isset( $wfocu_required_objects[ $section ] ) ? $wfocu_required_objects[ $section ] : false;
		$is_listing = ( ! $is_section && is_null( $tab ) ) ? true : false;
		//disable no-conflict if $page_objects is false
		if ( $is_section === false && $is_listing === false ) {
			return;
		}


		$enable_no_conflict_mode = WFOCU_Core()->data->get_option( 'enable_noconflict_mode' );
		if ( false === $enable_no_conflict_mode || empty( $enable_no_conflict_mode ) ) {
			return;
		}

		if ( ! is_array( $is_section ) ) {
			$is_section = array();
		}

		//merging wp scripts with gravity forms scripts
		$required_objects = array_merge( $wp_required_objects, $wfocu_required_objects['common'], $is_section );

		//allowing addons or other products to change the list of no conflict scripts
		$required_objects = apply_filters( "wfocu_noconflict_{$type}", $required_objects );


		$queue = array();
		foreach ( $wp_objects->queue as $object ) {
			if ( in_array( $object, $required_objects, true ) ) {
				$queue[] = $object;
			}
		}
		$wp_objects->queue = $queue;

		$required_objects = $this->add_script_dependencies( $wp_objects->registered, $required_objects );

		//unregistering scripts
		$registered = array();
		foreach ( $wp_objects->registered as $script_name => $script_registration ) {
			if ( in_array( $script_name, $required_objects, true ) ) {
				$registered[ $script_name ] = $script_registration;
			}
		}

		$wp_objects->registered = $registered;
	}

	/**
	 * Adds script dependencies needed.
	 *
	 * @param array $registered Registered scripts.
	 * @param array $scripts Required scripts.
	 *
	 * @return array $scripts Scripts including dependencies.
	 * @since   Unknown
	 *
	 * @used-by WFOCU_Admin::no_conflict_mode()
	 *
	 */
	public function add_script_dependencies( $registered, $scripts ) {

		//gets all dependent scripts linked to the $scripts array passed
		do {
			$dependents = array();
			foreach ( $scripts as $script ) {
				$deps = isset( $registered[ $script ] ) && is_array( $registered[ $script ]->deps ) ? $registered[ $script ]->deps : array();
				foreach ( $deps as $dep ) {
					if ( ! in_array( $dep, $scripts, true ) && ! in_array( $dep, $dependents, true ) ) {
						$dependents[] = $dep;
					}
				}
			}
			$scripts = array_merge( $scripts, $dependents );
		} while ( ! empty( $dependents ) );

		return $scripts;
	}

	public function get_selected_nav_class( $nav ) {
		if ( ! isset( $_GET['tab'] ) && 'upstroke' === $_GET['page'] ) {
			return 'nav-tab-active';
		}

		return '';
	}

	public function get_selected_nav_class_global( $nav ) {
		if ( isset( $_GET['tab'] ) && 'upstroke' === $_GET['page'] && 'settings' === $_GET['tab'] ) {
			return 'nav-tab-active';
		}

		return '';
	}

	public function get_selected_nav_class_tools( $nav ) {

		return '';
	}


	/**
	 * Check if its our builder page and registered required nodes to prepare a breadcrumb
	 */
	public function maybe_register_breadcrumbs() {

		if ( WFOCU_Common::is_load_admin_assets( 'builder' ) ) {

			/**
			 * Only register primary node if not added yet
			 */
			if ( empty( BWF_Admin_Breadcrumbs::$nodes ) ) {
				BWF_Admin_Breadcrumbs::register_node( array( 'text' => __( 'One Click Upsells' ), 'link' => admin_url( 'admin.php?page=upstroke' ) ) );
			}
			$funnel_id = WFOCU_Core()->funnels->get_funnel_id();
			BWF_Admin_Breadcrumbs::register_node( array( 'text' => get_the_title( $funnel_id ), 'link' => '' ) );
		}
	}

	public function get_shortcodes_list() {
		return array(
			array(
				'label' => __( 'Product Offer Accept Link', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => site_url() . '?wfocu-accept-link=yes',
					'multi'  => site_url() . '?wfocu-accept-link=yes&key=%s',
				),
			),
			array(
				'label' => __( 'Product Offer Skip Link', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => site_url() . '?wfocu-reject-link=yes',
					'multi'  => site_url() . '?wfocu-reject-link=yes',
				),
			),

			array(
				'label' => __( 'Product Offer Variation Selector', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_variation_selector_form]',
					'multi'  => '[wfocu_variation_selector_form key="%s"]'
				),
			),
			array(
				'label' => __( 'Product Quantity Selector', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_qty_selector]',
					'multi'  => '[wfocu_qty_selector key="%s"]'
				),
			),
			array(
				'label' => __( 'Product Image Slider', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_product_image_slider]',
					'multi'  => '[wfocu_product_image_slider key="%s"]'
				),
			),
			array(
				'label' => __( 'Product Offer Price', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_product_offer_price]',
					'multi'  => '[wfocu_product_offer_price key="%s"]'
				),
			),
			array(
				'label' => __( 'Product Title', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_product_title]',
					'multi'  => '[wfocu_product_title key="%s"]'
				),
			),
			array(
				'label' => __( 'Product Short Description', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_product_short_description]',
					'multi'  => '[wfocu_product_short_description key="%s"]'
				),
			),
			array(
				'label' => __( 'Product Regular Price', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_product_regular_price]',
					'multi'  => '[wfocu_product_regular_price key="%s"]'
				),
			),
			array(
				'label' => __( 'Product Price HTML', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_product_price_full]',
					'multi'  => '[wfocu_product_price_full key="%s"]'
				),
			),
			array(
				'label' => __( 'Product Offer Save Value', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_product_save_value]',
					'multi'  => '[wfocu_product_save_value key="%s"]'
				),
			),
			array(
				'label' => __( 'Product Offer Save Percentage', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_product_save_percentage]',
					'multi'  => '[wfocu_product_save_percentage key="%s"]'
				),
			),
			array(
				'label' => __( 'Product Offer Save value & Percentage', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_product_savings]',
					'multi'  => '[wfocu_product_savings key="%s"]'
				),
			),
			array(
				'label' => __( 'Product Single Unit Price', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_product_single_unit_price]',
					'multi'  => '[wfocu_product_single_unit_price key="%s"]'
				),
			),
			array(
				'label' => __( 'Product Offer Accept Link HTML', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_yes_link]' . __( 'Add to my Order', 'woofunnels-upstroke-one-click-upsell' ) . '[/wfocu_yes_link]',
					'multi'  => '[wfocu_yes_link key="%s"]' . __( 'Add to my Order', 'woofunnels-upstroke-one-click-upsell' ) . '[/wfocu_yes_link]'
				),
			),
			array(
				'label' => __( 'Product Offer Skip Link HTML', 'woofunnels-upstroke-one-click-upsell' ),
				'code'  => array(
					'single' => '[wfocu_no_link]' . __( 'No, thanks', 'woofunnels-upstroke-one-click-upsell' ) . '[/wfocu_no_link]',
					'multi'  => '[wfocu_no_link]' . __( 'No, thanks', 'woofunnels-upstroke-one-click-upsell' ) . '[/wfocu_no_link]'
				),
			),
		);
	}

	/**
	 * Adding metabox on editor page for 'Back to funnel' link.
	 */
	public function add_meta_boxes_for_back_button() {
		$post_type = WFOCU_Common::get_offer_post_type_slug();
		add_meta_box( 'wfocu-edit-offer', __( 'Offer Page', 'woofunnels-upstroke-one-click-upsell' ), [ $this, 'render_funnel_link_meta_box' ], $post_type, 'side', 'default' );
	}

	public function render_funnel_link_meta_box() {
		return;


	}

	public function add_back_button() {
		global $post;
		$offer_id = ( WFOCU_Common::get_offer_post_type_slug() === $post->post_type ) ? $post->ID : 0;
		if ( $offer_id > 0 ) {
			$funnel_id = get_post_meta( $offer_id, '_funnel_id', true );

			$upsell_id = get_post_meta( $offer_id, '_funnel_id', true );
			$funnel_id = get_post_meta( $upsell_id, '_bwf_in_funnel', true );

			if ( ! empty( $funnel_id ) && abs( $funnel_id ) > 0 ) {
				BWF_Admin_Breadcrumbs::register_ref( 'wffn_funnel_ref', $funnel_id );
			}
			$edit_link = BWF_Admin_Breadcrumbs::maybe_add_refs( add_query_arg( [
				'page'    => 'upstroke',
				'edit'    => $funnel_id,
				'section' => 'design',
			], admin_url( 'admin.php' ) ) );

			if ( use_block_editor_for_post_type( WFOCU_Common::get_offer_post_type_slug() ) ) {
				add_action( 'admin_footer', array( $this, 'render_back_to_funnel_script_for_block_editor' ) );
			} else {
				?>
				<div id="wf_funnel-switch-mode">
					<a id="wf_funnel-back-button" class="button button-default button-large" href="<?php echo esc_url( $edit_link ); ?>">
						<?php esc_html_e( '&#8592; Back to Funnel Edit Page', 'woofunnels-upstroke-one-click-upsell' ); ?>
					</a>
				</div>
				<?php

			}
		}

	}

	public function render_back_to_funnel_script_for_block_editor() {
		global $post;
		$offer_id = ( WFOCU_Common::get_offer_post_type_slug() === $post->post_type ) ? $post->ID : 0;
		if ( $offer_id > 0 ) {
			$upsell_id = get_post_meta( $offer_id, '_funnel_id', true );
			$funnel_id = get_post_meta( $upsell_id, '_bwf_in_funnel', true );

			if ( ! empty( $funnel_id ) && abs( $funnel_id ) > 0 ) {
				BWF_Admin_Breadcrumbs::register_ref( 'wffn_funnel_ref', $funnel_id );
			}
			$edit_link = BWF_Admin_Breadcrumbs::maybe_add_refs( add_query_arg( [
				'page'    => 'upstroke',
				'edit'    => $upsell_id,
				'section' => 'design',
			], admin_url( 'admin.php' ) ) );
			?>
			<script id="wf_funnel-back-button-template" type="text/html">
				<div id="wf_funnel-switch-mode">
					<a id="wf_funnel-back-button" class="button button-default button-large" href="<?php echo esc_url( $edit_link ); ?>">
						<?php echo esc_html_e( '&#8592; Back to Funnel Edit Page', 'woofunnels-upstroke-one-click-upsell' ); ?>
					</a>
				</div>
			</script>
			<script>
				window.addEventListener('load', function () {
					(function ($) {
						let back_button = $($('#wf_funnel-back-button-template').html());
						if ($('#editor').find('.edit-post-header-toolbar').length > 0) {
							$('#editor').find('.edit-post-header-toolbar').prepend(back_button);
						}
					})(jQuery);
				});
			</script>
			<?php
		}
	}

	public function maybe_add_timeline_files() {


		/**
		 * Apply a condition to handle activation of old reporting plugin that could break activation
		 */
		if ( 'activate' === filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING ) && 'woofunnels-upstroke-reports/woofunnels-upstroke-reports.php' === filter_input( INPUT_GET, 'plugin', FILTER_SANITIZE_STRING ) ) {
			return;
		}

		/**
		 * Add timeline file and hooks
		 */
		require __DIR__ . '/includes/class-wfocu-upstroke-timeline.php';

		/**
		 * IF reporting plugin
		 */
		if ( is_callable( [ 'WFOCU_Admin_Reports', 'wfocu_add_licence_support_file' ] ) ) {
			$wfocu_upstroke_timeline = WFOCU_Upstroke_Timeline::instance( 'woofunnels-upstroke-one-click-upsell' );
		} else {
			$wfocu_upstroke_timeline = WFOCU_Upstroke_Timeline::instance();
		}


		add_action( 'add_meta_boxes', array( $wfocu_upstroke_timeline, 'wfocu_register_upstroke_reports_meta_boxes' ) );

	}

	public function register_admin_menu() {
		add_submenu_page( 'woofunnels', __( 'One Click Upsells', 'woofunnels-upstroke-one-click-upsell' ), __( 'One Click Upsells', 'woofunnels-upstroke-one-click-upsell' ), 'manage_woocommerce', 'upstroke', array(
			WFOCU_Core()->admin,
			'upstroke_page',
		) );
	}

	/**
	 * @param $existing_args
	 * Exclude upsells create by funnel builder or AB testing
	 * @return mixed
	 */
	public function exclude_from_query( $existing_args ) {
		if ( isset( $existing_args['get_existing'] ) && true === $existing_args['get_existing'] ) {
			unset( $existing_args['get_existing'] );

			return $existing_args;
		}
		if ( isset( $existing_args['meta_query'] ) && is_array( $existing_args['meta_query'] ) && count( $existing_args['meta_query'] ) > 0 ) {
			array_push( $existing_args['meta_query'], array(
				'key'     => '_bwf_in_funnel',
				'compare' => 'NOT EXISTS',
				'value'   => '',
			) );
			array_push( $existing_args['meta_query'], array(
				'key'     => '_bwf_ab_variation_of',
				'compare' => 'NOT EXISTS',
				'value'   => '',
			) );
		} else {
			$existing_args['meta_query'] = array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_bwf_in_funnel',
					'compare' => 'NOT EXISTS',
					'value'   => '',
				),
				array(
					'key'     => '_bwf_ab_variation_of',
					'compare' => 'NOT EXISTS',
					'value'   => '',
				)
			);
		}

		return $existing_args;
	}


}

if ( class_exists( 'WFOCU_Core' ) ) {
	WFOCU_Core::register( 'admin', 'WFOCU_Admin' );
}

