<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class BWFAN_Admin
 */
class BWFAN_Admin {

	private static $ins = null;
	public $admin_path;
	public $admin_url;
	public $section_page = '';
	public $should_show_shortcodes = null;
	public $events_js_data = [];
	public $actions_js_data = [];
	public $select2ajax_js_data = [];

	public function __construct() {
		$this->admin_path = BWFAN_PLUGIN_DIR . '/admin';
		$this->admin_url  = BWFAN_PLUGIN_URL . '/admin';

		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 90 );

		/**
		 * Admin enqueue scripts
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_settings_page' ), 99 );

		/**
		 * Admin footer text
		 */
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 99999, 1 );
		add_filter( 'update_footer', array( $this, 'update_footer' ), 9999, 1 );

		add_action( 'admin_head', array( $this, 'js_variables' ) );
		add_action( 'admin_init', array( $this, 'maybe_set_automation_id' ) );

		/** Hooks to check if activation and deactivation request for post. */
		add_filter( 'plugin_action_links_' . BWFAN_PLUGIN_BASENAME, array( $this, 'plugin_actions' ) );

		add_action( 'in_admin_header', array( $this, 'maybe_remove_all_notices_on_page' ) );

		/** Set AS CT 1 min worker */
		add_action( 'admin_init', array( $this, 'maybe_set_as_ct_worker' ) );
		add_action( 'admin_init', array( $this, 'schedule_abandoned_cart_cron' ) );
		/** hook scheduler cron to wp  */
		add_action( 'wp', array( $this, 'maybe_set_as_ct_worker' ) );
		add_action( 'wp', array( $this, 'schedule_abandoned_cart_cron' ) );

		add_action( 'wp_ajax_bwfan_sync_customer_order', [ $this, 'bwfan_sync_customer_order' ] );
		add_action( 'wp_ajax_bwfan_optin_call', [ $this, 'handle_optin_ajax' ] );
		add_action( 'admin_init', array( $this, 'maybe_handle_optin_choice' ), 14 );

		add_action( 'admin_notices', array( $this, 'maybe_show_sandbox_mode_notice' ) );

		/** Create automation earlier */
		add_action( 'admin_init', array( $this, 'maybe_create_automation' ), 15 );

		/** Enable reset tracking setting in global woofunnels tools */
		add_filter( 'bwf_needs_order_indexing', '__return_true' );
	}

	public function maybe_show_sandbox_mode_notice() {
		$global_settings = BWFAN_Common::get_global_settings();
		if ( 0 === intval( $global_settings['bwfan_sandbox_mode'] ) && ( ! defined( 'BWFAN_SANDBOX_MODE' ) || false === BWFAN_SANDBOX_MODE ) ) {
			return;
		}

		?>
        <div class="notice notice-warning" style="display: block!important;">
            <p>
				<?php
				echo __( '<strong>Warning! Autonami is in Sandbox Mode</strong>. New Tasks will not be created & existing Tasks will not execute.', 'wp-marketing-automations' );
				?>
            </p>
        </div>
		<?php
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function get_admin_url() {
		return plugin_dir_url( BWFAN_PLUGIN_FILE ) . 'admin';
	}

	public function register_admin_menu() {
		$title = 'Automations';

		$global_settings = BWFAN_Common::get_global_settings();
		if ( 1 === intval( $global_settings['bwfan_sandbox_mode'] ) || ( defined( 'BWFAN_SANDBOX_MODE' ) && ( true === BWFAN_SANDBOX_MODE ) ) ) {
			$title .= ' <span style="background-color:#ca4a1f;border-radius:10px;margin-left:2px;font-size:11px;padding:3px 6px;">sandbox</span>';
		}

		add_submenu_page( 'woofunnels', 'Automations', $title, 'manage_options', 'autonami', array( $this, 'autonami_page' ) );

		if ( false === BWFAN_Plugin_Dependency::woocommerce_active_check() ) {
			return;
		}

		$global_settings = BWFAN_Common::get_global_settings();

		if ( empty( $global_settings['bwfan_ab_enable'] ) ) {
			return;
		}

		global $wpdb;

		$title = __( 'Carts', 'wp-marketing-automations' );

		$cart_count = get_transient( '_bwfan_cart_count', false );
		if ( false === $cart_count ) {
			$sql        = "SELECT COUNT(*) as `count` FROM {$wpdb->prefix}bwfan_abandonedcarts WHERE `status` IN (0,1,3,4)";
			$cart_count = $wpdb->get_var( $sql );
			set_transient( '_bwfan_cart_count', absint( $cart_count ), 15 * MINUTE_IN_SECONDS );
		}

		if ( absint( $cart_count ) > 0 ) {
			$title .= '<span class="update-plugins"><span class="processing-count">' . $cart_count . '</span></span>';
		}
		$position = apply_filters( 'bwfan_cart_submenu_position', 5 );
		if ( empty( absint( $position ) ) ) {
			$position = 5;
		}
		add_submenu_page( 'woocommerce', __( 'Carts', 'wp-marketing-automations' ), $title, 'manage_woocommerce', 'admin.php?page=autonami&tab=carts&ab_section=recoverable', false, $position );
	}

	public function admin_enqueue_assets() {
		global $post;

		$min = '.min';
		if ( defined( 'BWFAN_IS_DEV' ) && true === BWFAN_IS_DEV ) {
			$min = '';
		}
		$pro_active = false;

		if ( bwfan_is_autonami_pro_active() ) {
			$pro_active = true;
		}

		/**
		 * Adding Woofunnels' font CSS
		 */
		wp_enqueue_style( 'bwfan-woofunnel-fonts', $this->admin_url . '/assets/css/bwfan-admin-font.css', array(), BWFAN_VERSION_DEV );

		/**
		 * Load Funnel Builder page assets
		 */
		if ( BWFAN_Common::is_load_admin_assets( 'builder' ) ) {
			wp_enqueue_style( 'bwfan-funnel-bg', $this->admin_url . '/assets/css/bwfan-funnel-bg.css', array(), BWFAN_VERSION_DEV );
			wp_enqueue_style( 'woofunnels-opensans-font', '//fonts.googleapis.com/css?family=Open+Sans', array(), BWFAN_VERSION_DEV );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		}

		/**
		 * Including izimodal assets
		 */
		if ( BWFAN_Common::is_load_admin_assets( 'all' ) ) {
			wp_enqueue_style( 'bwfan-izimodal', $this->admin_url . '/includes/iziModal/iziModal.css', array(), BWFAN_VERSION_DEV );
			wp_enqueue_script( 'bwfan-izimodal', $this->admin_url . '/includes/iziModal/iziModal.js', array(), BWFAN_VERSION_DEV );
		}
		if ( BWFAN_Common::is_load_admin_assets( 'settings' ) ) {
			wp_enqueue_script( 'jquery-tiptip' );
		}

		$data = array(
			'ajax_nonce'            => wp_create_nonce( 'bwfan-action-admin' ),
			'plugin_url'            => plugin_dir_url( BWFAN_PLUGIN_FILE ),
			'ajax_url'              => admin_url( 'admin-ajax.php' ),
			'admin_url'             => admin_url(),
			'ajax_chosen'           => wp_create_nonce( 'json-search' ),
			'search_products_nonce' => wp_create_nonce( 'search-products' ),
			'loading_gif_path'      => admin_url() . 'images/wpspin_light.gif',
			'rules_texts'           => array(
				'text_or'         => __( 'OR', 'wp-marketing-automations' ),
				'text_apply_when' => '',
				'remove_text'     => __( 'Remove', 'wp-marketing-automations' ),
			),
			'current_page_id'       => ( isset( $post->ID ) ) ? $post->ID : 0,
		);

		/** WooCommerce ajax endpoint */
		if ( class_exists( 'WC_AJAX' ) ) {
			$data['wc_ajax_url'] = WC_AJAX::get_endpoint( '%%endpoint%%' );
		}

		/**
		 * Including Autonami assets on all Autonami pages.
		 */
		if ( BWFAN_Common::is_load_admin_assets( 'all' ) ) {

			wp_enqueue_script( 'wp-i18n' );
			wp_enqueue_script( 'wp-util' );

			if ( $this->is_autonami_page() ) {
				wp_dequeue_script( 'wpml-select-2' );
				wp_dequeue_script( 'select2' );
				wp_deregister_script( 'select2' );
				wp_enqueue_style( 'bwfan-select2-css', $this->admin_url . '/assets/css/select2.min.css', array(), BWFAN_VERSION_DEV );
				wp_enqueue_style( 'bwfan-sweetalert2-style', $this->admin_url . '/assets/css/sweetalert2.min.css', array(), BWFAN_VERSION_DEV );
				wp_enqueue_style( 'bwfan-toast-style', $this->admin_url . '/assets/css/toast.min.css', array(), BWFAN_VERSION_DEV );
				wp_register_script( 'select2', $this->admin_url . '/assets/js/select2.min.js', array( 'jquery' ), BWFAN_VERSION_DEV, true );
				wp_enqueue_script( 'select2' );
				wp_enqueue_script( 'bwfan-sweetalert2-script', $this->admin_url . '/assets/js/sweetalert2.js', array( 'jquery' ), BWFAN_VERSION_DEV, true );
				wp_enqueue_script( 'bwfan-toast-script', $this->admin_url . '/assets/js/toast.min.js', array( 'jquery' ), BWFAN_VERSION_DEV, true );
				wp_enqueue_editor();
				wp_enqueue_script( 'jquery-ui-datepicker' );

				//jQuery UI theme css file
				wp_register_style( 'jquery-ui', $this->admin_url . '/assets/css/jquery-ui.css', array(), BWFAN_VERSION_DEV );
				wp_enqueue_style( 'jquery-ui' );

				if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
					$all_events_merge_tags = BWFAN_Common::get_all_events_merge_tags();
					$all_events_rules      = BWFAN_Common::get_all_events_rules();
					$all_merge_tags        = BWFAN_Core()->merge_tags->get_localize_tags_with_source();

					/**
					 * @todo: Since we are including default merge tags at the bottom of every merge tags then we need to do sorting in JS.
					 */
					$all_events_merge_tags = BWFAN_Common::attach_default_merge_to_events( $all_events_merge_tags, $all_merge_tags );

					$data['events_merge_tags'] = $all_events_merge_tags;
					$data['events_rules']      = $all_events_rules;
				}
			}

			wp_enqueue_style( 'bwfan-admin-app', $this->admin_url . '/assets/css/bwfan-admin-app' . $min . '.css', array(), BWFAN_VERSION_DEV );
			wp_enqueue_style( 'bwfan-admin', $this->admin_url . '/assets/css/bwfan-admin' . $min . '.css', array(), BWFAN_VERSION_DEV );
			wp_enqueue_style( 'bwfan-admin-sub', $this->admin_url . '/assets/css/bwfan-admin-sub' . $min . '.css', array(), BWFAN_VERSION_DEV );

			/** Common open function */
			wp_enqueue_script( 'bwfan-admin-common', $this->admin_url . '/assets/js/bwfan-admin-common.js', array(), BWFAN_VERSION_DEV, true );

			wp_enqueue_script( 'wc-backbone-modal' );
			wp_enqueue_script( 'bwfan-admin-app', $this->admin_url . '/assets/js/bwfan-admin-ui-rules' . $min . '.js', array(
				'jquery',
				'jquery-ui-datepicker',
				'underscore',
				'backbone',
			), BWFAN_VERSION_DEV, true );

			/** @todo below admin sub css needs to clean */
			wp_enqueue_script( 'bwfan-admin', $this->admin_url . '/assets/js/bwfan-admin' . $min . '.js', array(), BWFAN_VERSION_DEV, true );
			wp_enqueue_script( 'bwfan-admin-ui-actions', $this->admin_url . '/assets/js/bwfan-admin-ui-actions' . $min . '.js', array(), BWFAN_VERSION_DEV, true );

			if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
				wp_enqueue_script( 'jquery-ui-draggable' );
				wp_enqueue_script( 'bwfan-admin-ui', $this->admin_url . '/assets/js/bwfan-admin-ui' . $min . '.js', array( 'bwfan-admin-ui-actions' ), BWFAN_VERSION_DEV, true );
			}
		}

		$data['bitly_success_authentication_message'] = __( 'Successfully Authenticated', 'wp-marketing-automations' );
		$data['setting_page_url']                     = admin_url( 'admin.php?page=autonami&tab=settings' );
		$data['connector_page_url']                   = admin_url( 'admin.php?page=autonami&tab=connector' );
		$data                                         = apply_filters( 'bwfan_admin_localize_data', $data, $this );
		$data['coupon_enabled']                       = ( 'yes' === get_option( 'woocommerce_enable_coupons' ) ) ? 'y' : 'n';
		$data['pro_active']                           = $pro_active;

		/** If recipe page */
		if ( BWFAN_Common::is_load_admin_assets( 'recipe' ) ) {
			global $recipe_data;
			$data_recipe = BWFAN_Recipe_Loader::get_registered_recipes();
			uasort( $data_recipe, function ( $item1, $item2 ) {
				return $item1->data['priority'] >= $item2->data['priority'];
			} );
			$data['recipes']           = $data_recipe;
			$data['recipes_connector'] = BWFAN_Recipe_Loader::get_recipes_filter_connectors();
			$data['recipes_plugins']   = BWFAN_Recipe_Loader::get_recipes_filter_plugins();

			$recipe_data['connectors'] = $data['recipes_connector'];
			$recipe_data['plugins']    = $data['recipes_plugins'];
		}

		wp_localize_script( 'bwfan-admin', 'bwfanParams', $data );

		$automation_id = BWFAN_Core()->automations->get_automation_id();

		if ( is_null( $automation_id ) || empty( $automation_id ) ) {
			return;
		}

		/** Single automation edit page. So continue respective params localization */
		$automation_global_js_object      = [];
		$automation_global_events_js_data = [];

		$automation_global_events_js_data['automation_sync_state'] = 'off';

		$batch_sync_status = BWFAN_Core()->automations->get_automations_sync_status( 1, [ $automation_id ] );
		if ( is_array( $batch_sync_status ) && count( $batch_sync_status ) > 0 ) {
			$batch_sync_status = array_column( $batch_sync_status, 'a_id' );
			if ( in_array( $automation_id, $batch_sync_status, true ) ) {
				BWFAN_Core()->automations->current_automation_sync_state   = 'data-sync-state="on"';
				$automation_global_events_js_data['automation_sync_state'] = 'on';
			}
		}

		$automation_meta = BWFAN_Core()->automations->get_automation_data_meta( $automation_id );

		$all_sources_events                                      = BWFAN_Load_Sources::get_sources_events_arr();
		$all_triggers                                            = BWFAN_Core()->sources->get_source_localize_data();
		$all_triggers_events                                     = BWFAN_Core()->sources->get_sources_events_localize_data();
		$all_integrations                                        = BWFAN_Core()->integration->get_integration_actions_localize_data();
		$all_automations                                         = BWFAN_Core()->integration->get_integration_localize_data();
		$automation_global_js_object['trigger']                  = [];
		$automation_global_js_object['actions']                  = [];
		$automation_global_js_object['condition']                = [];
		$automation_global_js_object['ui']                       = [];
		$automation_global_js_object['uiData']                   = [];
		$automation_global_events_js_data['all_integrations']    = [];
		$automation_global_events_js_data['all_automations']     = [];
		$automation_global_events_js_data['all_triggers_events'] = [];
		$automation_global_events_js_data['all_triggers']        = [];
		$automation_global_events_js_data['automation_id']       = $automation_id;

		/** Localize all the data which needs to be present on single automation screen. */
		if ( isset( $automation_meta['event'] ) && ! empty( $automation_meta['event'] ) ) {
			$automation_global_js_object['trigger']['source'] = $automation_meta['source'];
			$automation_global_js_object['trigger']['event']  = $automation_meta['event'];
			$automation_global_js_object['trigger']['name']   = __( 'Not Found', 'wp-marketing-automations' );

			$single_event = BWFAN_Core()->sources->get_event( $automation_meta['event'] );
			if ( ! is_null( $single_event ) && true === $single_event->is_time_independent() ) {
				$automation_global_events_js_data['is_time_independent'] = true;
				$automation_global_events_js_data['name']                = $single_event->get_name();
			} else {
				$automation_global_events_js_data['is_time_independent'] = false;
			}
		}
		if ( isset( $automation_meta['event_meta'] ) ) {
			$automation_global_js_object['trigger']['event_meta'] = $automation_meta['event_meta'];
		}
		$automation_global_js_object['actions'] = isset( $automation_meta['actions'] ) ? $automation_meta['actions'] : [];
		if ( isset( $automation_meta['condition'] ) ) {
			$automation_global_js_object['condition'] = $automation_meta['condition'];
		}
		if ( isset( $automation_meta['ui'] ) ) {
			$automation_global_js_object['ui'] = $automation_meta['ui'];
		}
		if ( isset( $automation_meta['uiData'] ) ) {
			$automation_global_js_object['uiData'] = $automation_meta['uiData'];
		}
		if ( isset( $all_integrations ) ) {
			$automation_global_events_js_data['all_integrations'] = $all_integrations;
		}
		if ( isset( $all_automations ) ) {
			$automation_global_events_js_data['all_automations'] = $all_automations;
		}
		if ( isset( $all_triggers_events ) ) {
			$automation_global_events_js_data['all_triggers_events'] = $all_triggers_events;
		}
		if ( isset( $all_triggers ) ) {
			$automation_global_events_js_data['all_triggers'] = $all_triggers;
		}
		if ( isset( $all_sources_events ) ) {
			$automation_global_events_js_data['all_sources_events'] = $all_sources_events;
		}
		if ( isset( $all_merge_tags ) ) {
			$automation_global_events_js_data['all_merge_tags'] = $all_merge_tags;
		}

		$automation_global_events_js_data['int_actions'] = BWFAN_Core()->integration->get_mapped_arr_action_with_integration();
		$automation_global_events_js_data['actions_int'] = BWFAN_Core()->integration->get_mapped_arr_integration_name_with_action_name();
		$automation_global_events_js_data['pro_actions'] = BWFAN_Common::merge_default_actions();
		$automation_global_events_js_data                = apply_filters( 'bwfan_admin_builder_localized_data', $automation_global_events_js_data );

		/** Exclude actions from events */
		$events                  = BWFAN_Core()->sources->get_events();
		$events_included_actions = array();
		$events_excluded_actions = array();

		if ( is_array( $events ) && count( $events ) > 0 ) {
			foreach ( $events as $event ) {
				/**
				 * @var $event_instance BWFAN_Event;
				 */
				$events_included_actions[ $event->get_slug() ] = $event->get_included_actions();
				$events_excluded_actions[ $event->get_slug() ] = $event->get_excluded_actions();
			}
		}

		// all event js data and then set localized unique key
		$all_event_js_data = BWFAN_Core()->admin->get_events_js_data();
		foreach ( $all_event_js_data as $key => $data ) {
			$all_event_js_data[ $key ]['localized_automation_key'] = md5( uniqid( time(), true ) );
		}
		$all_event_js_data = apply_filters( 'bwfan_all_event_js_data', $all_event_js_data, $automation_meta, $automation_id );

		$automation_global_events_js_data['enable_lang'] = 0;
		$language_options                                = [];
		if ( function_exists( 'icl_get_languages' ) ) {
			$languages = icl_get_languages();
			if ( ! empty( $languages ) ) {
				foreach ( $languages as $language ) {
					$language_options[ $language['language_code'] ] = ! empty( $language['translated_name'] ) ? $language['translated_name'] : $language['native_name'];
				}
			}
		}

		if ( count( $language_options ) > 1 ) {
			$automation_global_events_js_data['enable_lang']  = 1;
			$automation_global_events_js_data['lang_options'] = $language_options;
		}

		wp_enqueue_media();
		wp_localize_script( 'bwfan-admin', 'bwfan_automation_ui_data_detail', $automation_global_js_object );
		wp_localize_script( 'bwfan-admin', 'bwfan_automation_data', $automation_global_events_js_data );
		wp_localize_script( 'bwfan-admin', 'bwfan_events_js_data', $all_event_js_data );
		wp_localize_script( 'bwfan-admin', 'bwfan_events_included_actions', $events_included_actions );
		wp_localize_script( 'bwfan-admin', 'bwfan_events_excluded_actions', $events_excluded_actions );
		wp_localize_script( 'bwfan-admin', 'bwfan_set_select2ajax_js_data', BWFAN_Core()->admin->get_select2ajax_js_data() );
		wp_localize_script( 'bwfan-admin', 'bwfan_set_actions_js_data', BWFAN_Core()->admin->get_actions_js_data() );
	}

	public function admin_enqueue_settings_page() {
		$is_connector_page = $this->is_autonami_connector_page();
		if ( $is_connector_page ) {
			wp_enqueue_style( 'wfco-sweetalert2-style' );
			wp_enqueue_style( 'wfco-izimodal' );
			wp_enqueue_style( 'wfco-toast-style' );
			wp_enqueue_script( 'wfco-sweetalert2-script' );
			wp_enqueue_script( 'wfco-izimodal' );
			wp_enqueue_script( 'wfco-toast-script' );
			wp_enqueue_script( 'wc-backbone-modal' );
			wp_enqueue_style( 'wfco-admin' );
			wp_enqueue_script( 'wfco-admin' );
			WFCO_Admin::localize_data();
		}
	}

	public function autonami_page() {

		if ( 'blank' === get_option( 'bwfan_is_opted', 'blank' ) ) {
			include_once( $this->admin_path . '/view/optin-temp.php' );
		} else {

			$external_template = apply_filters( 'bwfan_load_external_autonami_page_template', '' );
			if ( ! empty( $external_template ) ) {
				if ( is_array( $external_template ) ) {
					foreach ( $external_template as $template ) {
						require_once( $template );
					}
				} else {
					require_once( $external_template );
				}

				return;
			}

			//phpcs:disable WordPress.Security.NonceVerification
			if ( ! isset( $_GET['page'] ) && 'autonami' !== sanitize_text_field( $_GET['page'] ) ) {
				return;
			}
			if ( isset( $_GET['section'] ) ) {
				if ( 'preview_email' === sanitize_text_field( $_GET['section'] ) ) {
					include_once( $this->admin_path . '/view/preview_email.php' );

					return;
				}
				include_once( $this->admin_path . '/view/automation-builder-view.php' );

				return;
			}

			if ( isset( $_GET['tab'] ) ) {
				$tab = sanitize_text_field( $_GET['tab'] );
				switch ( $tab ) {
					case 'settings' :
						include_once( $this->admin_path . '/view/global-settings.php' );
						break;
					case 'tasks' :
						require_once( BWFAN_PLUGIN_DIR . '/admin/includes/class-bwfan-tasks-table.php' );
						include_once( $this->admin_path . '/view/tasks.php' );
						break;
					case 'logs' :
						require_once( BWFAN_PLUGIN_DIR . '/admin/includes/class-bwfan-logs-table.php' );
						include_once( $this->admin_path . '/view/logs.php' );
						break;
					case 'contacts' :
						require_once( BWFAN_PLUGIN_DIR . '/admin/includes/class-bwfan-unsubscribers-table.php' );
						include_once( $this->admin_path . '/view/unsubscribers.php' );
						break;
					case 'carts' :
						$ab_section = isset( $_GET['ab_section'] ) ? trim( sanitize_text_field( $_GET['ab_section'] ) ) : '';
						do_action( 'bwfan_abandoned_cart_admin', $ab_section );
						break;
					case 'connector' :
						include_once( $this->admin_path . '/view/connector-admin.php' );
						break;
					case 'recipe' :
						include_once( $this->admin_path . '/view/recipe-admin.php' );
						break;
					default :
						if ( ! apply_filters( 'bwfan_include_autonami_page_' . $tab, false ) ) {
							require_once( BWFAN_PLUGIN_DIR . '/admin/includes/class-bwfan-automations-table.php' );
							include_once( $this->admin_path . '/view/automation-admin.php' );
						}
						break;
				}

				return;
			}

			if ( isset( $_GET['action'] ) ) {
				$action = sanitize_text_field( $_GET['action'] );
				switch ( $action ) {
					case 'import' :
						include_once( $this->admin_path . '/view/automation-admin-import.php' );
						break;
					case 'export' :
						include_once( $this->admin_path . '/view/automation-admin-export.php' );
						break;
				}

				return;
			}

			require_once( BWFAN_PLUGIN_DIR . '/admin/includes/class-bwfan-automations-table.php' );
			include_once( $this->admin_path . '/view/automation-admin.php' );
		}
		//phpcs:enable WordPress.Security.NonceVerification
	}

	public function automation_import() {
		//phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) && 'bwfan-autonami-import' === sanitize_text_field( $_GET['page'] ) ) {
			include_once( $this->admin_path . '/view/automation-admin-import.php' );
		}
		//phpcs:enable WordPress.Security.NonceVerification
	}

	public function automation_export() {
		//phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) && 'bwfan-autonami-export' === sanitize_text_field( $_GET['page'] ) ) {
			include_once( $this->admin_path . '/view/automation-admin-export.php' );
		}
		//phpcs:enable WordPress.Security.NonceVerification
	}

	public function js_variables() {
		$time_texts = array(
			'singular' => array(
				'minutes' => __( 'minute', 'wp-marketing-automations' ),
				'hours'   => __( 'hour', 'wp-marketing-automations' ),
				'day'     => __( 'day', 'wp-marketing-automations' ),
			),
			'plural'   => array(
				'minutes' => __( 'minutes', 'wp-marketing-automations' ),
				'hours'   => __( 'hours', 'wp-marketing-automations' ),
				'day'     => __( 'days', 'wp-marketing-automations' ),
			),
		);
		$data       = array(
			'site_url'   => home_url(),
			'texts'      => array(
				'sync_title'                         => __( 'Sync Integration', 'wp-marketing-automations' ),
				'sync_text'                          => __( 'All the data of this Integration will be Synced.', 'wp-marketing-automations' ),
				'sync_wait'                          => __( 'Please Wait...', 'wp-marketing-automations' ),
				'sync_progress'                      => __( 'Sync in progress...', 'wp-marketing-automations' ),
				'sync_success_title'                 => __( 'Integration Synced', 'wp-marketing-automations' ),
				'sync_success_text'                  => __( 'We have detected change in the integration during syncing. Please Re-save your Automations.', 'wp-marketing-automations' ),
				'sync_oops_title'                    => __( 'Oops', 'wp-marketing-automations' ),
				'sync_oops_text'                     => __( 'There was some error. Please try again later.', 'wp-marketing-automations' ),
				'delete_int_title'                   => __( 'There was some error. Please try again later.', 'wp-marketing-automations' ),
				'delete_int_text'                    => __( 'There was some error. Please try again later.', 'wp-marketing-automations' ),
				'delete_int_prompt_title'            => __( 'Delete Connector', 'wp-marketing-automations' ),
				'delete_int_prompt_text'             => __( 'All the Tasks of this Integration will be Deleted.', 'wp-marketing-automations' ),
				'delete_int_wait_title'              => __( 'Please Wait...', 'wp-marketing-automations' ),
				'delete_int_wait_text'               => __( 'Disconnecting the connector ...', 'wp-marketing-automations' ),
				'delete_int_success'                 => __( 'Connector Disconnected', 'wp-marketing-automations' ),
				'task_executed_success'              => __( 'Task Executed', 'wp-marketing-automations' ),
				'task_executed_just'                 => __( 'Just Executed', 'wp-marketing-automations' ),
				'log_deleted_title'                  => __( 'Log Deleted', 'wp-marketing-automations' ),
				'task_deleted_success'               => __( 'Task Deleted', 'wp-marketing-automations' ),
				'change_event_title'                 => __( 'Change in Event', 'wp-marketing-automations' ),
				'change_event_text'                  => __( 'You are about to change the event. You would need to Re-create your automation.', 'wp-marketing-automations' ),
				'delete_automation_title'            => __( 'Delete Automation', 'wp-marketing-automations' ),
				'delete_automation_text'             => __( 'All the Tasks of this automation will be deleted.', 'wp-marketing-automations' ),
				'delete_automation_wait_title'       => __( 'Please Wait...', 'wp-marketing-automations' ),
				'delete_automation_wait_text'        => __( 'Deleting the automation...', 'wp-marketing-automations' ),
				'delete_automation_success'          => __( 'Automation Deleted', 'wp-marketing-automations' ),
				'merge_tag_error_title'              => __( 'Merge Tag Error', 'wp-marketing-automations' ),
				'merge_tag_error_text'               => __( 'Please Check All Your Merge Tags.', 'wp-marketing-automations' ),
				'wrong_action_title'                 => __( 'Incompatible Action', 'wp-marketing-automations' ),
				'wrong_action_text'                  => __( 'Selected Action is not compatible with the selected Event.', 'wp-marketing-automations' ),
				'wrong_event_title'                  => __( 'Incompatible Event', 'wp-marketing-automations' ),
				'wrong_event_text'                   => __( 'Selected Event is not compatible with the Integrations. If you proceed, then you would need to re-create your integrations.', 'wp-marketing-automations' ),
				'no_event'                           => __( 'Please select an event', 'wp-marketing-automations' ),
				'no_trigger'                         => __( 'Please select a trigger', 'wp-marketing-automations' ),
				'no_action'                          => __( 'Please select an action', 'wp-marketing-automations' ),
				'source_change'                      => __( 'Change in Source ! You would need to re-create you automation.', 'wp-marketing-automations' ),
				'activated'                          => __( 'Activated', 'wp-marketing-automations' ),
				'deactivated'                        => __( 'Deactivated', 'wp-marketing-automations' ),
				'sync_process_oops_title'            => __( 'Automation is in sync process', 'wp-marketing-automations' ),
				'task_delete_title'                  => __( 'Delete Task', 'wp-marketing-automations' ),
				'task_delete_text'                   => __( 'Are you sure to delete the task ?', 'wp-marketing-automations' ),
				'delete_batch_process_title'         => __( 'Are you sure to delete the batch process', 'wp-marketing-automations' ),
				'delete_batch_process_text'          => __( 'This batch process will be deleted.', 'wp-marketing-automations' ),
				'delete_batch_process_wait_title'    => __( 'Please Wait...', 'wp-marketing-automations' ),
				'delete_batch_process_wait_text'     => __( 'Deleting the batch process...', 'wp-marketing-automations' ),
				'delete_batch_process_success'       => __( 'Batch Process Deleted', 'wp-marketing-automations' ),
				'terminate_batch_process_title'      => __( 'Are you sure to terminate the batch process', 'wp-marketing-automations' ),
				'terminate_batch_process_text'       => __( 'This batch process will be terminated.', 'wp-marketing-automations' ),
				'terminate_batch_process_wait_title' => __( 'Please Wait...', 'wp-marketing-automations' ),
				'terminate_batch_process_wait_text'  => __( 'Terminating the batch process...', 'wp-marketing-automations' ),
				'terminate_batch_process_success'    => __( 'Batch Process Terminated', 'wp-marketing-automations' ),
			),
			'time_delay' => $time_texts,
		);

		$wfo = 'window.bwfan=' . wp_json_encode( $data ) . ';';
		echo "<script>$wfo</script>"; //phpcs:ignore WordPress.Security.EscapeOutput
	}

	public function is_autonami_page() {
		if ( isset( $_GET['page'] ) && 'autonami' === sanitize_text_field( $_GET['page'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			return true;
		}

		return false;
	}

	public function is_autonami_connector_page() {
		if ( isset( $_GET['page'] ) && 'autonami' === sanitize_text_field( $_GET['page'] ) && isset( $_GET['tab'] ) && 'connector' === sanitize_text_field( $_GET['tab'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			return true;
		}

		return false;
	}

	public function admin_footer_text( $footer_text ) {
		if ( false === BWFAN_Common::is_load_admin_assets( 'all' ) ) {
			return $footer_text;
		}
		if ( BWFAN_Common::is_load_admin_assets( 'builder' ) ) {
			return '';
		}
		$link = add_query_arg( [
			'utm_source'   => 'website',
			'utm_medium'   => 'text',
			'utm_campaign' => 'footer',
			'utm_term'     => 'utm_term',
		], 'https://buildwoofunnels.com/support' );

		return sprintf( __( 'Thanks for creating with BuildWooFunnels. Need Help? <a href="%s" target="_blank">Contact Support</a>.', 'wp-marketing-automations' ), $link );
	}

	public function update_footer( $footer_text ) {
		if ( BWFAN_Common::is_load_admin_assets( 'builder' ) ) {
			return '';
		}

		return $footer_text;
	}

	public function get_automation_id() {
		if ( isset( $_GET['edit'] ) && ! empty( sanitize_text_field( $_GET['edit'] ) ) && isset( $_GET['page'] ) && 'autonami' === sanitize_text_field( $_GET['page'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			return sanitize_text_field( $_GET['edit'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		return false;
	}

	public function get_automation_section() {
		if ( isset( $_GET['section'] ) && ! empty( sanitize_text_field( $_GET['section'] ) ) && isset( $_GET['page'] ) && 'autonami' === sanitize_text_field( $_GET['page'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			return sanitize_text_field( $_GET['section'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		return '';
	}

	public function maybe_set_automation_id() {
		if ( $this->is_autonami_page() && isset( $_GET['edit'] ) && isset( $_GET['section'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			BWFAN_Core()->automations->set_automation_id( sanitize_text_field( $_GET['edit'] ) ); // WordPress.CSRF.NonceVerification.NoNonceVerification
			BWFAN_Core()->automations->set_automation_details();

			do_action( 'bwfan_automation_data_set_' . sanitize_text_field( $_GET['section'] ) ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		}
	}

	/**
	 * Hooked over 'plugin_action_links_{PLUGIN_BASENAME}' WordPress hook to add deactivate popup support
	 *
	 * @param array $links array of existing links
	 *
	 * @return array modified array
	 */
	public function plugin_actions( $links ) {
		if ( isset( $links['deactivate'] ) ) {
			$links['deactivate'] .= '<i class="woofunnels-slug" data-slug="' . BWFAN_PLUGIN_BASENAME . '"></i>';
		}

		return $links;
	}

	public function tooltip( $text ) {
		?>
        <span class="bwfan-help"><i class="icon"></i><div class="helpText"><?php esc_html_e( $text ); ?></div></span>
		<?php
	}

	/**
	 * Remove all the notices in our dashboard pages as they might break the design.
	 */
	public function maybe_remove_all_notices_on_page() {
		if ( isset( $_GET['page'] ) && 'autonami' === sanitize_text_field( $_GET['page'] ) && isset( $_GET['section'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			remove_all_actions( 'admin_notices' );
		}
	}

	/**
	 * Set the event field values for each html field present in that event.
	 *
	 * @param $event_slug
	 * @param $key
	 * @param $data
	 */
	public function set_events_js_data( $event_slug, $key, $data ) {
		if ( isset( $this->events_js_data[ $event_slug ] ) ) {
			if ( isset( $this->events_js_data[ $event_slug ][ $key ] ) ) {
				$saved_value = json_decode( $this->events_js_data[ $event_slug ][ $key ] );

				if ( ! empty( $data ) ) {
					$data = json_decode( $data );
					foreach ( $data as $key1 => $value1 ) {
						$saved_value[ $key1 ] = $value1;
					}
				}
				$this->events_js_data[ $event_slug ][ $key ] = wp_json_encode( $saved_value );
			} else {
				$this->events_js_data[ $event_slug ][ $key ] = $data;
			}
		} else {
			$this->events_js_data[ $event_slug ][ $key ] = $data;
		}
	}

	public function get_events_js_data() {
		return $this->events_js_data;
	}

	/**
	 * @param string $key a search type key to set data against to
	 * @param array $data
	 */
	public function set_select2ajax_js_data( $key, $data ) {
		if ( isset( $this->select2ajax_js_data[ $key ] ) ) {

			$this->select2ajax_js_data[ $key ] = array_replace( $this->select2ajax_js_data[ $key ], $data );
		} else {
			$this->select2ajax_js_data[ $key ] = $data;
		}
	}

	public function get_select2ajax_js_data() {
		return $this->select2ajax_js_data;
	}

	/**
	 * Set action's html fields data.
	 *
	 * @param $integration_slug
	 * @param $key
	 * @param $data
	 */
	public function set_actions_js_data( $integration_slug, $key, $data ) {
		if ( isset( $this->actions_js_data[ $integration_slug ] ) ) {
			if ( isset( $this->actions_js_data[ $integration_slug ][ $key ] ) ) {
				$saved_value = json_decode( $this->actions_js_data[ $integration_slug ][ $key ] );

				if ( ! empty( $data ) ) {
					$data = json_decode( $data );
					foreach ( $data as $key1 => $value1 ) {
						$saved_value[ $key1 ] = $value1;
					}
				}
				$this->actions_js_data[ $integration_slug ][ $key ] = wp_json_encode( $saved_value );
			} else {
				$this->actions_js_data[ $integration_slug ][ $key ] = $data;
			}
		} else {
			$this->actions_js_data[ $integration_slug ][ $key ] = $data;
		}
	}

	public function get_actions_js_data() {
		return $this->actions_js_data;
	}

	public function maybe_set_as_ct_worker() {
		if ( ! bwf_has_action_scheduled( 'bwfan_run_queue' ) ) {
			bwf_schedule_recurring_action( time(), MINUTE_IN_SECONDS, 'bwfan_run_queue' );
		}
	}

	public function schedule_abandoned_cart_cron() {
		if ( ! bwf_has_action_scheduled( 'bwfan_check_abandoned_carts' ) ) {
			bwf_schedule_recurring_action( time(), MINUTE_IN_SECONDS, 'bwfan_check_abandoned_carts' ); // check for abandoned carts for every minute
		}
		if ( ! bwf_has_action_scheduled( 'bwfan_delete_expired_autonami_coupons' ) || ! bwf_has_action_scheduled( 'bwfan_mark_abandoned_lost_cart' ) ) {
			$date = new DateTime();
			$date->modify( '+1 days' );
			BWFAN_Common::convert_from_gmt( $date ); // convert to site time
			$date->setTime( 0, 0, 0 );
			BWFAN_Common::convert_to_gmt( $date );

			if ( ! bwf_has_action_scheduled( 'bwfan_delete_expired_autonami_coupons' ) ) {
				bwf_schedule_recurring_action( $date->getTimestamp(), DAY_IN_SECONDS, 'bwfan_delete_expired_autonami_coupons' ); // Run once in a day
			}
			if ( ! bwf_has_action_scheduled( 'bwfan_mark_abandoned_lost_cart' ) ) {
				bwf_schedule_recurring_action( $date->getTimestamp(), DAY_IN_SECONDS, 'bwfan_mark_abandoned_lost_cart' ); // Run once in a day
			}
		}

		if ( true === apply_filters( 'bwfan_ab_delete_inactive_carts', false ) ) {
			if ( ! bwf_has_action_scheduled( 'bwfan_delete_old_abandoned_carts' ) ) {
				$date = new DateTime();
				$date->modify( '+1 days' );
				BWFAN_Common::convert_from_gmt( $date ); // convert to site time
				$date->setTime( 0, 0, 0 );
				BWFAN_Common::convert_to_gmt( $date );

				bwf_schedule_recurring_action( $date->getTimestamp(), DAY_IN_SECONDS, 'bwfan_delete_old_abandoned_carts' ); // Run once in a day
			}
		}
	}

	public function make_main_tabs_ui() {
		$tasks_count = intval( BWFAN_Core()->tasks->get_tasks_count() );
		if ( $tasks_count > 0 ) {
			echo '<style>';
			echo 'a.bwfan_tab_tasks.bwfan_btn_have_tasks:after {content:"' . esc_attr__( BWFAN_Common::modify_display_numbers( $tasks_count ) ) . '"}';
			echo '</style>';
		}
		$tab_arr = array(
			'automations'   => array(
				'name' => __( 'Automations', 'wp-marketing-automations' ),
				'href' => admin_url( 'admin.php?page=autonami' ),
			),
			'carts'         => array(
				'name' => __( 'Carts', 'wp-marketing-automations' ),
				'href' => admin_url( 'admin.php?page=autonami&tab=carts' ),
			),
			'connector'     => array(
				'name' => __( 'Connectors', 'wp-marketing-automations' ),
				'href' => admin_url( 'admin.php?page=autonami&tab=connector' ),
			),
			'recipe'        => array(
				'name' => __( 'Recipes', 'wp-marketing-automations' ),
				'href' => admin_url( 'admin.php?page=autonami&tab=recipe' ),
			),
			'tasks'         => array(
				'name'  => __( 'Task History', 'wp-marketing-automations' ),
				'href'  => admin_url( 'admin.php?page=autonami&tab=tasks' ),
				'class' => array(
					( $tasks_count > 0 ) ? ' bwfan_btn_have_tasks' : '',
				),
			),
			'batch_process' => array(
				'name' => __( 'Batch Process', 'wp-marketing-automations' ),
				'href' => admin_url( 'admin.php?page=autonami&tab=batch_process' ),
			),
			'contacts'      => array(
				'name' => __( 'Unsubscribers', 'wp-marketing-automations' ),
				'href' => admin_url( 'admin.php?page=autonami&tab=contacts' ),
			),
			'settings'      => array(
				'name' => __( 'Settings', 'wp-marketing-automations' ),
				'href' => admin_url( 'admin.php?page=autonami&tab=settings' ),
			),
		);

		if ( ! class_exists( 'WooCommerce' ) || ! bwfan_is_autonami_pro_active() ) {
			unset( $tab_arr['batch_process'] );
		}

		if ( 'autonami' === filter_input( INPUT_GET, 'page' ) ) {
			$tab = filter_input( INPUT_GET, 'tab' );
			switch ( $tab ) {
				case 'tasks':
				case 'settings':
				case 'contacts':
				case 'connector':
				case 'recipe':
				case 'batch_process':
				case 'carts':
					$tab_arr[ $tab ]['active'] = true;
					break;
				case 'logs':
					$tab_arr['tasks']['active'] = true;
					break;
				default:
					$tab_arr['automations']['active'] = true;
			}
		}

		$tab_arr = apply_filters( 'bwfan_main_tab_array', $tab_arr );
		$this->make_tab_ui( $tab_arr );
	}

	public function make_tab_ui( $arr, $prefix = 'bwfan' ) {
		if ( ! is_array( $arr ) || count( $arr ) === 0 ) {
			return;
		}

		ob_start();
		echo '<nav class="nav-tab-wrapper woo-nav-tab-wrapper">';
		foreach ( $arr as $key => $val ) {
			if ( ! isset( $val['name'] ) || empty( $val['name'] ) ) {
				continue;
			}
			$href  = ( isset( $val['href'] ) && ! empty( $val['href'] ) ) ? $val['href'] : 'javascript:void(0)';
			$class = array( 'nav-tab', $prefix . '_tab_' . $key );
			if ( isset( $val['active'] ) && true === $val['active'] ) {
				$class[] = 'nav-tab-active';
			}
			if ( isset( $val['class'] ) && is_array( $val['class'] ) && count( $val['class'] ) > 0 ) {
				$class = array_merge( $class, $val['class'] );
			}
			$attr = [];
			if ( isset( $val['attr'] ) && is_array( $val['attr'] ) && count( $val['attr'] ) > 0 ) {
				$attr = $val['attr'];
				array_walk( $attr, function ( &$val, $key ) {
					if ( ! empty( $key ) && ! empty( $val ) ) {
						$val = ' ' . $key . '=' . $val;
					}
				} );
			}

			?>
            <a href="<?php echo $href; //phpcs:ignore WordPress.Security.EscapeOutput ?>"
               class="<?php esc_attr_e( implode( ' ', $class ) ); ?>"<?php esc_attr_e( implode( ' ', $attr ) ); ?>><?php esc_html_e( $val['name'] ); ?></a>
			<?php
		}
		echo '</nav>';
		echo ob_get_clean(); //phpcs:ignore WordPress.Security.EscapeOutput
	}

	public function bwfan_sync_customer_order() {
		$updater_ins = WooFunnels_DB_Updater::get_instance();
		$updater_ins->bwf_start_indexing();

		wp_send_json( array( 'message' => __( 'Customer order sync in progress.', 'wp-marketing-automations' ) ) );
	}

	public function handle_optin_ajax() {
		check_ajax_referer( 'bwfan_optin_call' );
		if ( is_array( $_POST ) && count( $_POST ) > 0 ) {
			$_POST['domain'] = home_url();
			$_POST['ip']     = $_SERVER['REMOTE_ADDR'];
			WooFunnels_API::post_optin_data( $_POST );

			/** scheduling track call when success */
			if ( isset( $_POST['status'] ) && 'yes' === $_POST['status'] ) {
				wp_schedule_single_event( time() + 2, 'woofunnels_optin_success_track_scheduled' );
			}
		}
		wp_send_json( array(
			'status' => 'success',
		) );
		exit;
	}

	public function maybe_handle_optin_choice() {
		if ( isset( $_GET['bwfan-optin-choice'] ) && isset( $_GET['_bwfan_optin_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_bwfan_optin_nonce'], 'bwfan_optin_nonce' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'woofunnels' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheating huh?', 'woofunnels' ) );
			}

			$optin_choice = sanitize_text_field( $_GET['bwfan-optin-choice'] );
			if ( $optin_choice === 'yes' ) {
				$this->allow_optin();

			} else {
				$this->block_optin();
			}

			do_action( 'bwfan_after_optin_choice', $optin_choice );
			wp_redirect( admin_url( 'admin.php?page=autonami' ) );
			exit;
		}
	}

	public function maybe_create_automation() {
		if ( ! isset( $_GET['page'] ) || 'autonami' !== sanitize_text_field( $_GET['page'] ) ) {
			return;
		}

		/** Check if automation creation call */
		$automation_id = $this->get_automation_id();

		if ( false === $automation_id && isset( $_GET['create'] ) && 'y' === $_GET['create'] ) { //phpcs:ignore WordPress.Security.NonceVerification
			$automation_id = BWFAN_Core()->automations->create_automation();
			if ( false !== $automation_id ) {
				$url = add_query_arg( array(
					'page'    => 'autonami',
					'section' => 'automation',
					'edit'    => $automation_id,
				), admin_url( 'admin.php' ) );
				wp_redirect( $url );
				exit;
			}
			wp_die( esc_html__( 'Error occurred in Automation creation.', 'wp-marketing-automations' ) );
		}

	}

	public function allow_optin() {
		update_option( 'bwfan_is_opted', 'yes', true );

		//try to push data for once
		$data = WooFunnels_optIn_Manager::collect_data();

		//posting data to api
		WooFunnels_API::post_tracking_data( $data );
	}

	public function block_optin() {
		update_option( 'bwfan_is_opted', 'no', true );
	}

}

if ( class_exists( 'BWFAN_Core' ) ) {
	BWFAN_Core::register( 'admin', 'BWFAN_Admin' );
}
