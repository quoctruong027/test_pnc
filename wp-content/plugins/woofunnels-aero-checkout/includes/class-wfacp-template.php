<?php
defined( 'ABSPATH' ) || exit;

/**
 * Abstract Class for all the Template Loading
 * Class WFACP_Template_Common
 */
abstract class WFACP_Template_Common {
	protected $selected_register_template = [];
	public $default_badges = [];
	public $web_google_fonts = [
		'Open Sans' => 'Open Sans',
	];
	public $wfacp_templates_slug = [
		'pre_built'  => [
			'layout_1' => 15,
			'layout_2' => 15,
			'layout_4' => 15,
			'layout_9' => 7,
		],
		'elementor'  => [
			'elementor_1' => 7,
			'elementor_2' => 7,
			'elementor_3' => 7,
			'elementor_4' => 7,
		],
		'embed_form' => [
			'embed_forms_2' => 7,
		],
	];

	public $device_type = 'not-mobile';
	public $device_mb_tab = 'only-desktop';
	public $enabled_product_switching = 'no';
	public $have_billing_address = false;
	public $have_shipping_address = false;
	public $have_billing_address_index = 2;
	public $have_shipping_address_index = 1;
	public $setting_new_version = false;

	protected $available_fields = [
		'layout',
		'header',
		'product',
		'guarantee',
		'listing',
		'testimonial',
		'widget',
		'customer-care',
		'promises',
		'footer',
		'style',
		'gbadge',
		'product_switcher',
		'html_widget_1',
		'html_widget_2',
		'html_widget_3',
	];
	protected $data = null;
	protected $fields = [];
	protected $template_dir = __DIR__;
	protected $template_type = 'pre_built';
	protected $template_slug = 'layout_4';
	protected $template_name = 'layout_4';
	protected $steps = [];
	protected $fieldsets = [];
	protected $checkout_fields = [];
	protected $css_classes = [];
	protected $current_step = 'single_step';
	protected $current_open_step = 'single_step';
	protected $wfacp_id = 0;
	protected $url = '';
	protected $have_coupon_field = false;
	protected $have_shipping_method = true;
	protected $form_data = [];
	protected $smart_buttons = [];
	protected $base_country = [ 'billing_country' => '', 'shipping_country' => '' ];
	private $footer_js_printed = false;
	private $address_keys = [];


	protected function __construct() {
		$this->img_path        = WFACP_PLUGIN_URL . '/admin/assets/img/';
		$this->img_public_path = WFACP_PLUGIN_URL . '/assets/img/';
		$this->url             = WFACP_PLUGIN_URL . '/public/templates/' . $this->get_template_slug() . '/views/';

		$this->setup_data_hooks();
		$this->css_js_hooks();
		$this->woocommerce_field_hooks();
		$this->remove_actions();
		$this->setup_smart_buttons();
		$this->include_front_files();

		$this->address_keys = [
			'billing_first_name'  => 'shipping_first_name',
			'billing_last_name'   => 'shipping_last_name',
			'billing_address_1'   => 'shipping_address_1',
			'billing_address_2'   => 'shipping_address_2',
			'billing_city'        => 'shipping_city',
			'billing_postcode'    => 'shipping_postcode',
			'billing_country'     => 'shipping_country',
			'billing_state'       => 'shipping_state',
			'shipping_first_name' => 'billing_first_name',
			'shipping_last_name'  => 'billing_last_name',
			'shipping_address_1'  => 'billing_address_1',
			'shipping_address_2'  => 'billing_address_2',
			'shipping_city'       => 'billing_city',
			'shipping_postcode'   => 'billing_postcode',
			'shipping_country'    => 'billing_country',
			'shipping_state'      => 'billing_state',
		];

	}

	public function get_template_slug() {
		return $this->template_slug;
	}

	private function setup_data_hooks() {

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_action_at_page_found' ], 100 );
		add_filter( 'woocommerce_checkout_before_customer_details', [ $this, 'assign_first_last_name' ], 10, 3 );

		add_filter( 'wfacp_default_values', [ $this, 'pre_populate_from_get_parameter' ], 10, 3 );


		add_filter( 'wfacp_layout_default_setting', [ $this, 'change_setting_for_default_checkout' ], 99, 2 );
		add_filter( 'wfacp_native_checkout_cart', '__return_false' );

		/** Adding the_content default filters on 'wfacp_the_content' handle */
		add_filter( 'wfacp_the_content', 'wptexturize' );
		add_filter( 'wfacp_the_content', 'convert_smilies', 20 );
		add_filter( 'wfacp_the_content', 'wpautop' );
		add_filter( 'wfacp_the_content', 'shortcode_unautop' );
		add_filter( 'wfacp_the_content', 'prepend_attachment' );
		add_filter( 'wfacp_the_content', 'do_shortcode', 11 );

		add_filter( 'wfacp_the_content', [ $GLOBALS['wp_embed'], 'run_shortcode' ], 8 );
		add_filter( 'wfacp_the_content', [ $GLOBALS['wp_embed'], 'autoembed' ], 8 );
		add_filter( 'wc_get_template', [ $this, 'remove_form_billing_and_shipping_html' ] );
		add_filter( 'wc_get_template', [ $this, 'replace_recurring_total_shipping' ], 999, 2 );
		add_action( 'wfacp_after_billing_email_field', [ $this, 'show_account_fields' ], 10, 3 );
		add_filter( 'show_admin_bar', [ $this, 'remove_admin_bar' ], 99 );
		add_action( 'wfacp_footer_before_print_scripts', [ $this, 'remove_admin_bar_print_hook' ] );
		add_filter( 'woocommerce_country_locale_field_selectors', [ $this, 'remove_add1_add2_local_field_selector' ] );
		add_action( 'wfacp_before_product_switcher_html', [ $this, 'display_undo_message' ] );

		add_action( 'wfacp_before_mini_cart_html', [ $this, 'display_mini_cart_undo_message' ] );

		add_action( 'woocommerce_checkout_process', [ $this, 'woocommerce_checkout_process' ], 0 );
		add_filter( 'woocommerce_available_payment_gateways', [ $this, 'remove_extra_payment_gateways_in_customizer' ], 99 );

		add_filter( 'wfacp_forms_field', [ $this, 'merge_builder_data' ], 10, 2 );
		add_filter( 'wfacp_forms_field', [ $this, 'add_styling_class_to_country_field' ], 12, 2 );

		add_action( 'wfacp_form_single_step_start', [ $this, 'preview_field_generate' ], 10, 2 );
		add_action( 'wfacp_form_two_step_start', [ $this, 'preview_field_generate' ], 10, 2 );
		add_action( 'wfacp_form_third_step_start', [ $this, 'preview_field_generate' ], 10, 2 );

		add_action( 'wfacp_after_form', [ $this, 'remove_unused_js' ] );
	}


	private function css_js_hooks() {
		add_filter( 'body_class', [ $this, 'add_body_class' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ], 100 );
		add_action( 'wfacp_header_print_in_head', [ $this, 'global_css' ] );
		add_action( 'wp_head', [ $this, 'add_viewport_meta' ], - 1 );
		add_action( 'wp_head', [ $this, 'no_follow_no_index' ], - 1 );
		add_action( 'wp_head', [ $this, 'add_header_script' ], 99 );
		add_action( 'wp_print_styles', [ $this, 'remove_woocommerce_js_css' ], 99 );
		add_action( 'wp_print_styles', [ $this, 'remove_theme_css_and_scripts' ], 100 );
		add_action( 'wp_footer', [ $this, 'add_footer_script' ] );
		add_action( 'wp_footer', [ $this, 'localize_locals' ] );

	}

	private function woocommerce_field_hooks() {
		add_action( 'woocommerce_before_checkout_form', [ $this, 'checkout_form_login' ] );
		add_action( 'woocommerce_before_checkout_form', [ $this, 'checkout_form_coupon' ] );

		add_filter( 'wfacp_default_field', [ $this, 'wfacp_default_field' ], 10, 2 );
		/* change text of next step*/

		add_filter( 'woocommerce_locate_template', [ $this, 'change_template_location_for_cart_shipping' ], 99998, 3 );
		add_filter( 'woocommerce_locate_template', [ $this, 'change_template_location_for_payment' ], 99999, 3 );
		add_filter( 'woocommerce_checkout_fields', [ $this, 'woocommerce_checkout_fields' ], 0 );

		//for normal update_checkout hook
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'check_cart_coupons' ], - 1 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'remove_default_order_summary_table' ] );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment_order_summary' ], 99, 2 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment_shipping_calculator' ], 99, 2 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment_order_total' ], 99, 2 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment_coupon' ], 99, 2 );

		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment_product_switching' ], 99, 2 );
		add_action( 'wfacp_intialize_template_by_ajax', function () {
			//for when our fragments calls running
			add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_fragment_product_switching' ], 99, 2 );
		}, 10 );

		add_filter( 'wfacp_checkout_fields', [ $this, 'set_priority_of_form_fields' ], 0, 2 );
		add_filter( 'wfacp_checkout_fields', [ $this, 'handling_checkout_post_data' ], 1 );
		add_filter( 'wfacp_checkout_fields', [ $this, 'correct_country_state_locals' ], 2 );

		add_filter( 'woocommerce_countries_shipping_countries', [ $this, 'woocommerce_countries_shipping_countries' ] );
		add_filter( 'woocommerce_countries_allowed_countries', [ $this, 'woocommerce_countries_allowed_countries' ] );
		// updating shipping and billing address vice-versa

		add_action( 'woocommerce_before_checkout_form', [ $this, 'reattach_necessary_hooks' ] );
		add_action( 'woocommerce_review_order_before_submit', [ $this, 'display_hide_payment_box_heading' ] );
		add_filter( 'woocommerce_available_payment_gateways', [ $this, 'change_payment_gateway_text' ] );
		add_filter( 'woocommerce_get_cart_page_permalink', [ $this, 'change_cancel_url' ], 999 );
		add_action( 'wfacp_before_breadcrumb', [ $this, 'call_before_cart_link' ] );

		add_filter( 'wfacp_change_next_btn_single_step', [ $this, 'change_single_step_label' ], 10, 2 );
		add_filter( 'wfacp_change_next_btn_two_step', [ $this, 'change_two_step_label' ], 10, 2 );
		add_filter( 'woocommerce_order_button_text', [ $this, 'change_place_order_button_text' ], 11 );
		add_filter( 'wfacp_change_back_btn', [ $this, 'change_back_step_label' ], 10, 3 );
		add_action( 'wfacp_template_after_step', [ $this, 'display_back_button' ], 10, 2 );
		add_action( 'wfacp_template_after_step', [ $this, 'display_next_button' ], 11, 2 );
		add_action( 'wfacp_template_after_step', [ $this, 'close_back_button_div' ], 12, 2 );
		add_filter( 'woocommerce_order_button_html', [ $this, 'add_class_change_place_order' ], 11 );
		add_action( 'wfacp_outside_header', [ $this, 'get_base_country' ] );

		add_filter( 'woocommerce_checkout_posted_data', [ $this, 'set_checkout_posted_data' ], - 1 );
		add_action( 'woocommerce_checkout_create_order', [ $this, 'set_address_data' ], 10, 2 );
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'update_custom_fields' ], 10, 2 );

	}

	public function get_base_country() {
		$default_customer_address = get_option( 'woocommerce_default_customer_address' );
		if ( in_array( $default_customer_address, [ 'geolocation', 'geolocation_ajax' ] ) ) {
			$this->base_country['billing_country']  = WFACP_Common::get_base_country();
			$this->base_country['shipping_country'] = WFACP_Common::get_base_country( 'shipping_country' );
		}
	}


	private function remove_actions() {
		remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	}


	public function remove_action_at_page_found() {
		remove_all_actions( 'woocommerce_review_order_after_submit' );
		remove_all_actions( 'woocommerce_review_order_before_submit' );
	}

	private function setup_smart_buttons() {
		$page_settings = WFACP_Common::get_page_settings( WFACP_Common::get_id() );
		if ( ! wc_string_to_bool( $page_settings['enable_smart_buttons'] ) ) {
			return;
		}
		$this->smart_buttons = apply_filters( 'wfacp_smart_buttons', [] );
		$position            = $page_settings['smart_button_position'];
		if ( isset( $position['id'] ) ) {
			add_action( $position['id'], [ $this, 'display_smart_buttons' ] );
		}
	}

	public function get_template_type() {
		return $this->template_type;
	}


	public function get_step_count() {
		$form_current_step = $this->get_current_step();
		$no_of_fields      = 1;
		if ( isset( $form_current_step ) && $form_current_step == 'two_step' ) {
			$no_of_fields = 2;
		} elseif ( isset( $form_current_step ) && $form_current_step == 'third_step' ) {
			$no_of_fields = 3;
		}

		return $no_of_fields;
	}

	public function get_current_step() {
		return $this->current_step;
	}

	public function get_template_fields_class() {
		return $this->css_classes;
	}

	public function default_css_class() {
		return [
			'input_class' => 'wfacp-form-control',
			'class'       => 'wfacp-col-full',
		];
	}


	public function remove_default_order_summary_table( $fragments ) {
		unset( $fragments['.woocommerce-checkout-review-order-table'] );

		$total                    = WC()->cart->get_total( 'edit' );
		$fragments['.cart_total'] = $total;

		return $fragments;
	}


	public function no_follow_no_index() {
		echo "\n <meta name='robots' content='noindex,nofollow' /> \n";
	}


	public function enqueue_script() {

		$tempType        = $this->get_template_type();
		$global_settings = WFACP_Common::global_settings( true );


		if ( 'pre_built' === $tempType ) {
			wp_enqueue_style( 'wfacp-' . $tempType . '-style', plugin_dir_url( WFACP_PLUGIN_FILE ) . 'assets/css/wfacp_prebuilt_combined.min.css', false, WFACP_VERSION_DEV );

		} else {
			wp_enqueue_style( 'wfacp-' . $tempType . '-style', plugin_dir_url( WFACP_PLUGIN_FILE ) . 'assets/css/wfacp_combined.min.css', false, WFACP_VERSION_DEV );
		}

		wp_enqueue_script( 'jquery' );


		wp_enqueue_script( 'wc-add-to-cart-variation' );
		if ( defined( 'BWF_DEV' ) ) {
			wp_enqueue_script( 'wfacp_checkout_js', plugin_dir_url( WFACP_PLUGIN_FILE ) . 'assets/js/public.js', [ 'jquery' ], WFACP_VERSION_DEV, true );
		} else {
			wp_enqueue_script( 'wfacp_checkout_js', plugin_dir_url( WFACP_PLUGIN_FILE ) . 'assets/js/public.min.js', [ 'jquery' ], WFACP_VERSION_DEV, true );
		}


		if ( apply_filters( 'wfacp_remove_woocommerce_style_dependency', true ) ) {
			wp_deregister_style( 'woocommerce-layout' );
			wp_deregister_style( 'woocommerce-smallscreen' );
			wp_deregister_style( 'woocommerce-general' );
		}

		if ( isset( $global_settings['wfacp_google_address_key'] ) && '' !== $global_settings['wfacp_google_address_key'] ) {
			$gmap_api_key  = $global_settings['wfacp_google_address_key'];
			$page_settings = WFACP_Common::get_page_settings( WFACP_Common::get_id() );
			if ( isset( $page_settings['enable_google_autocomplete'] ) && wc_string_to_bool( $page_settings['enable_google_autocomplete'] ) ) {
				add_filter( 'wfacp_autopopulatestate_fields', '__return_empty_string' );
				wp_enqueue_script( 'wfacp_google_js', 'https://maps.googleapis.com/maps/api/js?key=' . $gmap_api_key . '&libraries=places', [ 'wfacp_checkout_js' ], '', true );
			}
		}

	}

	public function localize_locals() {

		wp_localize_script( 'wfacp_checkout_js', 'wfacp_frontend', $this->get_localize_data() );
		wp_localize_script( 'wfacp_checkout_js', 'wfacp_analytics_data', $this->get_analytics_data() );
	}

	protected function get_localize_data() {

		$global_settings = WFACP_Common::global_settings( true );
		unset( $global_settings['wfacp_checkout_global_css'] );
		$wc_validation_fields = $this->get_wc_addr2_company_value();
		$page_settings        = WFACP_Common::get_page_settings( WFACP_Common::get_id() );

		$preview_field_head = [
			'address'             => __( 'Billing', 'woocommerce' ),
			'shipping-address'    => __( 'Ship to', 'woocommerce' ),
			'shipping_calculator' => __( 'Method', 'woocommerce' ),
			'billing_first_name'  => __( 'Name', 'woocommerce' ),
		];


		$autopopulate_fields = 'no';
		if ( wc_string_to_bool( $page_settings['enable_autopopulate_fields'] ) && ! is_user_logged_in() ) {
			$autopopulate_fields = 'yes';
		}

		$autopopulatestate = 'no';
		if ( wc_string_to_bool( $page_settings['enable_autopopulate_state'] ) && ! is_user_logged_in() ) {
			$autopopulatestate = 'yes';
		}


		$data           = $switcher_settings = WFACP_Common::get_product_switcher_data( WFACP_Common::get_id() );
		$track_facebook = 'yes';
		if ( WFACP_Common::is_theme_builder() ) {
			$track_facebook = 'no';
		}
		unset( $page_settings['header_script'], $data['settings']['header_script'] );
		unset( $page_settings['footer_script'], $data['settings']['footer_script'] );


		$gmap_data          = [ 'disallow_countries' => [] ];
		$checkout_countries = WC()->countries->get_allowed_countries();
		if ( isset( $page_settings['disallow_autocomplete_countries'] ) && is_array( $page_settings['disallow_autocomplete_countries'] ) && count( $page_settings['disallow_autocomplete_countries'] ) > 0 && count( $checkout_countries ) > 1 ) {

			$countries = $page_settings['disallow_autocomplete_countries'];
			foreach ( $countries as $country ) {
				if ( isset( $checkout_countries[ $country['id'] ] ) ) {
					$gmap_data['disallow_countries'][] = $country['id'];
				}
			}

		}
		$template_name = $this->get_template_slug();

		$textLocal = __( 'Change', 'woocommerce' );
		if ( isset( $page_settings['preview_field_preview_text'] ) && $page_settings['preview_field_preview_text'] != '' ) {
			$textLocal = $page_settings['preview_field_preview_text'];
		}


		$data = [
			'id'                                    => WFACP_Common::get_id(),
			'admin_ajax'                            => admin_url( 'admin-ajax.php' ),
			'wc_endpoints'                          => WFACP_AJAX_Controller::get_public_endpoints(),
			'wfacp_nonce'                           => wp_create_nonce( 'wfacp_secure_key' ),
			'cart_total'                            => WC()->cart->get_total( 'edit' ),
			'settings'                              => $global_settings,
			'products_in_cart'                      => WFACP_Core()->public->products_in_cart,
			'autopopulate'                          => apply_filters( 'wfacp_autopopulate_fields', $autopopulate_fields ),
			'autopopulatestate'                     => apply_filters( 'wfacp_autopopulatestate_fields', $autopopulatestate ),
			'is_global'                             => WFACP_Core()->public->is_checkout_override(),
			'is_registration_enabled'               => WC()->checkout()->is_registration_enabled(),
			'wc_customizer_validation_status'       => $wc_validation_fields,
			'switcher_settings'                     => $data['settings'],
			'cart_is_virtual'                       => WFACP_Common::is_cart_is_virtual(),
			'show_on_next_step_fields'              => $page_settings['show_on_next_step'],
			'change_text_preview_fields'            => apply_filters( 'wfacp_preview_change_text', $textLocal ),
			'fields_label'                          => apply_filters( 'wfacp_preview_headings', $preview_field_head ),
			'exchange_keys'                         => [ 'pre_built' => new stdClass() ],
			'shop_base_location'                    => apply_filters( 'wfacp_shop_base_location_filter', get_option( 'woocommerce_default_country' ) ),
			'track_facebook'                        => $track_facebook,
			'wfacp_is_checkout_override'            => ( WFACP_Core()->public->is_checkout_override() ) ? 'yes' : 'no',
			'add_to_cart_text'                      => __( 'Add to cart', 'woocommerce' ),
			'select_options_text'                   => __( 'Select options', 'woocommerce' ),
			'update_button_text'                    => __( 'Update', 'woocommerce' ),
			'cancel_page_url'                       => $this->get_cancel_page_link(),
			'enable_hashtag_for_multistep_checkout' => apply_filters( 'wfacp_enable_hashtag_for_multistep_checkout', 'yes', $this ),
			'gmaps'                                 => $gmap_data,
			'template_name'                         => $template_name,
			'base_country'                          => $this->base_country

		];

		$mobile = new WFACP_Mobile_Detect();
		if ( $mobile->isMobile() || $mobile->isTablet() ) {
			$data['is_desktop'] = 'no';
			$data['is_mobile']  = 'yes';
		} else {
			$data['is_desktop'] = 'yes';
			$data['is_mobile']  = 'no';
		}
		$data['smart_button_hide_timeout']    = apply_filters( 'wfacp_smart_button_hide_timeout', 2000, $this );
		$data['smart_button_hide_timeout_m']  = apply_filters( 'wfacp_smart_button_hide_timeout_m', 3000, $this );
		$data['stripe_smart_show_on_desktop'] = 'yes';

		return apply_filters( 'wfacp_template_localize_data', $data, $this );
	}

	public function get_cancel_page_link() {
		$current_page_url = get_the_permalink();

		$params = [
			'wfacp_is_checkout_override' => ( WFACP_Core()->public->is_checkout_override() ) ? 'yes' : 'no',
			'wfacp_id'                   => WFACP_Common::get_id(),
			'wfacp_canceled'             => 'yes'
		];

		$params = apply_filters( 'wfacp_cancel_url_arguments', $params, $this );
		$url    = add_query_arg( $params, $current_page_url );

		return apply_filters( 'cancel_page_url', $url, $params, $this );
	}

	protected function get_analytics_data() {
		$final    = [];
		$services = WFACP_Analytics::get_available_service();
		foreach ( $services as $service => $analytic ) {
			/**
			 * @var $analytic WFACP_Analytics;
			 */
			$final[ $service ] = $analytic->get_prepare_data();
		}

		return $final;
	}

	public function remove_woocommerce_js_css() {
		if ( WFACP_Common::is_theme_builder() ) {
			global $wp_scripts;

			$registered_script = $wp_scripts->registered;
			if ( ! empty( $registered_script ) ) {
				foreach ( $registered_script as $handle => $data ) {
					if ( false !== strpos( $data->src, '/plugins/woocommerce/' ) ) {
						unset( $wp_scripts->registered[ $handle ] );
						wp_dequeue_script( $handle );
					}
				}
			}
		}
	}

	public function remove_theme_css_and_scripts() {

		if ( false == apply_filters( 'wfacp_remove_theme_js_css_files', true, $this ) ) {
			return;
		}
		global $wp_scripts, $wp_styles;
		$registered_script = $wp_scripts->registered;
		if ( ! empty( $registered_script ) ) {
			foreach ( $registered_script as $handle => $data ) {
				if ( $this->find_js_css_handle( $data->src ) ) {
					unset( $wp_scripts->registered[ $handle ] );
					wp_dequeue_script( $handle );
				}
			}
		}

		$registered_style = $wp_styles->registered;
		if ( ! empty( $registered_style ) ) {
			foreach ( $registered_style as $handle => $data ) {
				if ( $this->find_js_css_handle( $data->src ) ) {
					unset( $wp_styles->registered[ $handle ] );
					wp_dequeue_script( $handle );
				}
			}
		}

	}

	/**
	 * Find removal folder path exist in enqueue js and css url
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	private final function find_js_css_handle( $url ) {
		$paths = [
			'/themes/',
			'/cache/',
			'cart-fragments.min.js',
			'cart-fragments.js',
			'/woo-advance-search/',
		];

		$template_type = $this->get_template_type();

		if ( 'pre_built' == $template_type ) {
			$plugins = [
				'revslider',
				'testimonial-slider-and-showcase',
				'woocommerce-product-addons',
				'contact-form-7',
				'wp-upg',
				'bonanza-',
				'affiliate-wp',
				'woofunnels-autobot',
				'woocommerce-quick-buy',
				'wp-admin/js/password-strength-meter.min.js',
				'woocommerce-product-bundles',
				'/fusion-styles/',
				'cart-fragments.min.js',
				'cart-fragments.js',
				'/uploads/oceanwp/main-style.css',
				'/uploads/dynamic_avia/',
				'/uploads/porto_styles/',
				'um-styles.css',
				'/fifu-premium/',
				'/uploads/bb-theme/',
				'/uploads/wp-less/pillar/style/css/',
				'/td-composer/legacy/common/wp_booster/js_dev'
			];
			$paths   = array_merge( $paths, $plugins );
		}

		$paths = apply_filters( 'wfacp_css_js_removal_paths', $paths, $this );
		if ( empty( $paths ) ) {
			return false;
		}
		foreach ( $paths as $path ) {
			if ( false !== strpos( $url, $path ) && true == apply_filters( 'wfacp_css_js_deque', true, $path, $url, $this ) ) {
				return true;

			}
		}

		return false;

	}

	public function add_header_script() {

		$settings = WFACP_Common::get_page_settings( WFACP_Common::get_id() );

		if ( isset( $settings['header_script'] ) && '' != $settings['header_script'] ) {
			echo sprintf( "\n \n %s \n \n", $settings['header_script'] );
		}
	}

	public function add_footer_script() {

		$settings = WFACP_Common::get_page_settings( WFACP_Common::get_id() );

		if ( false == $this->footer_js_printed && isset( $settings['footer_script'] ) && '' != $settings['footer_script'] ) {
			$this->footer_js_printed = true;
			echo sprintf( "\n \n %s \n\n", $settings['footer_script'] );
		}

		$_wfacp_global_settings = get_option( '_wfacp_global_settings' );

		if ( isset( $_wfacp_global_settings['wfacp_global_external_script'] ) && $_wfacp_global_settings['wfacp_global_external_script'] != '' ) {
			$global_script = $_wfacp_global_settings['wfacp_global_external_script'];
			echo $global_script;
		}

	}

	public function checkout_form_login() {
		if ( is_user_logged_in() || 'no' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
			return;
		}
		include WFACP_TEMPLATE_COMMON . '/checkout/form-login.php';
	}

	public function checkout_form_coupon() {
		$settings          = WFACP_Common::get_page_settings( WFACP_Common::get_id() );
		$is_disable_coupon = ( isset( $settings['disable_coupon'] ) && 'true' == $settings['disable_coupon'] );

		if ( ! $is_disable_coupon ) {
			include WFACP_TEMPLATE_COMMON . '/checkout/form-coupon.php';
		}
	}

	public function wfacp_default_field( $default, $index ) {
		if ( isset( $this->css_classes[ $index ] ) ) {
			return $this->css_classes[ $index ]['class'];
		} else {

			return 'wfacp-col-full';
		}
	}


	public function add_fragment_order_summary( $fragments ) {
		ob_start();
		include WFACP_TEMPLATE_COMMON . '/order-summary.php';
		$order_summary                     = ob_get_clean();
		$fragments['.wfacp_order_summary'] = $order_summary;

		return $fragments;
	}

	public function add_fragment_shipping_calculator( $fragments ) {
		if ( isset( $this->checkout_fields['advanced']['shipping_calculator'] ) ) {
			ob_start();
			include WFACP_TEMPLATE_COMMON . '/shipping-options.php';
			$order_shipping_calc                  = ob_get_clean();
			$fragments['.wfacp_shipping_options'] = $order_shipping_calc;
		}

		return $fragments;
	}

	public function add_fragment_product_switching( $fragments ) {

		if ( isset( WFACP_Common::$post_data['product_switcher_need_refresh'] ) && 0 == WFACP_Common::$post_data['product_switcher_need_refresh'] ) {
			return $fragments;
		}

		$fragments['.wfacp-product-switch-panel'] = WFACP_Common::get_product_switcher_table( true );

		return $fragments;
	}

	public function add_fragment_order_total( $fragments ) {
		$fragments['.wfacp_order_total'] = WFACP_Common::get_order_total_fields( true );

		return $fragments;
	}


	public function add_fragment_coupon( $fragments ) {
		if ( isset( $this->checkout_fields['advanced']['order_coupon'] ) ) {
			$messages        = '';
			$success_message = $this->checkout_fields['advanced']['order_coupon']['coupon_success_message_heading'];
			ob_start();
			foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
				$parse_message = WFACP_Product_Switcher_Merge_Tags::parse_coupon_merge_tag( $success_message, $coupon );
				$remove_link   = sprintf( "<a href='%s' class='wfacp_remove_coupon' data-coupon='%s'>%s</a>", add_query_arg( [
					'remove_coupon' => $code,
				], wc_get_checkout_url() ), $code, __( 'Remove', 'woocommerce' ) );
				$messages      .= sprintf( '<div class="wfacp_single_coupon_msg">%s %s</div>', $parse_message, $remove_link );
			}

			$fragments['.wfacp_coupon_field_msg'] = '<div class="wfacp_coupon_field_msg">' . $messages . '</div>';

		}

		return $fragments;
	}


	public function change_template_location_for_cart_shipping( $template, $template_name, $template_path ) {
		if ( 'cart/cart-shipping.php' === $template_name ) {
			$template = WFACP_TEMPLATE_COMMON . '/shipping-options-form.php';
		}

		return $template;
	}


	public function change_template_location_for_payment( $template, $template_name, $template_path ) {
		if ( 'checkout/payment.php' === $template_name ) {
			if ( apply_filters( 'wfacp_replace_payment_box_template', true, $template, $template_name, $template_path ) ) {
				$template = WFACP_TEMPLATE_COMMON . '/checkout/payment.php';
			}
		}

		return $template;
	}

	/**
	 * @param $fields
	 *
	 * @return array
	 * @since 1.0
	 */
	public function woocommerce_checkout_fields( $fields ) {

		$template_fields = $this->get_checkout_fields();
		if ( isset( $fields['account'] ) ) {
			$template_fields['account'] = $fields['account'];
		}

		$template_fields = apply_filters( 'wfacp_checkout_fields', $template_fields, $fields );
		$is_billing_only = wc_ship_to_billing_address_only();
		if ( true == $is_billing_only && ! isset( $template_fields['shipping'] ) ) {
			$template_fields['shipping'] = $fields['shipping'];

		}

		return $template_fields;
	}

	public function get_checkout_fields() {
		return apply_filters( 'wfacp_get_checkout_fields', $this->checkout_fields );
	}


	public function set_priority_of_form_fields( $template_fields, $fields ) {

		foreach ( $template_fields as $type => $sections ) {
			if ( empty( $sections ) ) {
				continue;
			}
			foreach ( $sections as $key => $field ) {
				$template_fields[ $type ][ $key ]['priority'] = 0;
				if ( isset( $field['type'] ) && ( 'wfacp_wysiwyg' == $field['type'] || 'hidden' == $field['type'] ) && isset( $field['required'] ) ) {
					unset( $template_fields[ $type ][ $key ]['required'] );
				}
			}
		}

		return $template_fields;
	}

	/**
	 * Handle first and last name of shipping and billing field
	 *
	 * @param $template_fields
	 *
	 * @return array
	 *
	 * @since 1.6.0
	 */
	public function handling_checkout_post_data( $template_fields ) {
		if ( isset( $_POST['ship_to_different_address'] ) ) {
			add_filter( 'woocommerce_cart_needs_shipping_address', [ $this, 'enable_need_shipping' ] );
		}

		if ( isset( $_POST['ship_to_different_address'] ) && isset( $_POST['wfacp_billing_same_as_shipping'] ) && $_POST['wfacp_billing_same_as_shipping'] == 0 ) {
			$address_fields = [ 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'postcode', 'country', 'state' ];
			foreach ( $address_fields as $key ) {
				$b_key = 'billing_' . $key;
				if ( isset( $template_fields['billing'][ $b_key ] ) && in_array( $b_key, [
						'billing_first_name',
						'billing_last_name',
					] ) && ! isset( $template_fields['billing'][ $b_key ]['address_group'] ) ) {

					continue;
				}

				if ( 'billing' == $this->get_shipping_billing_index() && isset( $template_fields['billing'][ $b_key ]['required'] ) ) {
					unset( $template_fields['billing'][ $b_key ]['required'] );
				}
				if ( $key == 'postcode' ) {
					unset( $template_fields['billing'][ $b_key ]['validate'] );
				}
			}
		}

		/**
		 * When billing address not present in form then we assign shipping field values to billing fields values
		 */
		if ( isset( $_POST['_wfacp_post_id'] ) && ! wc_string_to_bool( $this->have_billing_address ) && wc_string_to_bool( $this->have_shipping_address ) ) {

			$available_fields   = [ 'company', 'address_2', 'country', 'city', 'state', 'postcode', 'address_1' ];
			$billing_first_name = false;
			$billing_last_name  = false;
			if ( ! isset( $_POST['billing_first_name'] ) ) {
				$available_fields[] = 'first_name';

			} else {
				$billing_first_name = true;
			}

			if ( ! isset( $_POST['billing_last_name'] ) ) {
				$available_fields[] = 'last_name';

			} else {
				$billing_last_name = true;
			}

			foreach ( $available_fields as $key ) {
				$b_key = 'billing_' . $key;
				$s_key = 'shipping_' . $key;
				if ( isset( $template_fields['shipping'][ $s_key ] ) ) {
					$template_fields['billing'][ $b_key ]       = $template_fields['shipping'][ $s_key ];
					$template_fields['billing'][ $b_key ]['id'] = $b_key;
					if ( isset( $template_fields['billing'][ $b_key ]['required'] ) ) {
						unset( $template_fields['billing'][ $b_key ]['required'] );
					}
					$_POST[ $b_key ]    = wc_clean( $_POST[ $s_key ] );
					$_REQUEST[ $b_key ] = wc_clean( $_POST[ $s_key ] );
				}
			}

			if ( ! isset( $template_fields['shipping']['shipping_first_name'] ) && true == $billing_first_name ) {
				$template_fields['shipping']['shipping_first_name']       = $template_fields['billing']['billing_first_name'];
				$template_fields['shipping']['shipping_first_name']['id'] = 'shipping_first_name';
				if ( isset( $template_fields['shipping']['shipping_first_name']['required'] ) ) {
					unset( $template_fields['shipping']['shipping_first_name']['required'] );
				}
				$_POST['shipping_first_name']    = wc_clean( $_POST['billing_first_name'] );
				$_REQUEST['shipping_first_name'] = wc_clean( $_POST['billing_first_name'] );
			}

			if ( ! isset( $template_fields['shipping']['shipping_last_name'] ) && true == $billing_last_name ) {
				$template_fields['shipping']['shipping_last_name'] = $template_fields['billing']['billing_last_name'];
				if ( isset( $template_fields['shipping']['shipping_last_name']['required'] ) ) {
					unset( $template_fields['shipping']['shipping_last_name']['required'] );
				}
				$template_fields['shipping']['shipping_last_name']['id'] = 'shipping_last_name';
				$_POST['shipping_last_name']                             = wc_clean( $_POST['billing_last_name'] );
				$_REQUEST['shipping_last_name']                          = wc_clean( $_POST['billing_last_name'] );
			}
		}

		return $template_fields;
	}

	public function enable_need_shipping() {
		return true;
	}

	/**
	 * Update Address Field Vice versa
	 *
	 * @param $posted_data
	 *
	 * @return mixed
	 */
	public function set_checkout_posted_data( $posted_data ) {

		// assign billing email as account_username
		if ( apply_filters( 'wfacp_assign_email_as_a_username', true, $posted_data, $this ) ) {
			if ( ! isset( $posted_data['account_username'] ) ) {
				$posted_data['account_username'] = $posted_data['billing_email'];
			}
		}

		$this->address_keys = apply_filters( 'wfacp_update_posted_data_vice_versa_keys', $this->address_keys, $posted_data, $this );
		if ( ! empty( $this->address_keys ) ) {
			$index = $this->get_shipping_billing_index();

			if ( $index == 'shipping' && ! isset( $_REQUEST['ship_to_different_address'] ) ) {
				$_REQUEST['shipping_country'] = '';
				$_REQUEST['shipping_state']   = '';
			}

			if ( isset( $_REQUEST['billing_country'] ) && 'default' == trim( $_REQUEST['billing_country'] ) ) {
				$_REQUEST['billing_country'] = '';
			}
			if ( isset( $_REQUEST['shipping_country'] ) && 'default' == trim( $_REQUEST['shipping_country'] ) ) {
				$_REQUEST['shipping_country'] = '';
			}


			// Street Address 2 Condition check #2046
			if ( $this->have_billing_address() && $this->have_shipping_address() ) {
				$fields = $this->get_checkout_fields();
				if ( ! isset( $fields['billing']['billing_address_2'] ) && isset( $this->address_keys['billing_address_2'] ) ) {
					unset( $this->address_keys['billing_address_2'] );

				} else if ( ! isset( $fields['shipping']['shipping_address_2'] ) && isset( $this->address_keys['shipping_address_2'] ) ) {
					unset( $this->address_keys['shipping_address_2'] );
				}
			}


			foreach ( $this->address_keys as $first_key => $second_key ) {
				$input = '';
				if ( ( ! isset( $_REQUEST[ $first_key ] ) || empty( $_REQUEST[ $first_key ] ) ) && ( isset( $_REQUEST[ $second_key ] ) && ! empty( $_REQUEST[ $second_key ] ) ) ) {
					//Do not sanitize array field
					if ( is_array( $_REQUEST[ $second_key ] ) ) {
						$input = $_REQUEST[ $second_key ];
					} else {
						$input = wc_clean( $_REQUEST[ $second_key ] );
					}
				} elseif ( isset( $_REQUEST[ $first_key ] ) && empty( $_REQUEST[ $first_key ] ) && ( isset( $_REQUEST[ $second_key ] ) && ! empty( $_REQUEST[ $second_key ] ) ) ) {
					//Do not sanitize array field
					if ( is_array( $_REQUEST[ $second_key ] ) ) {
						$input = $_REQUEST[ $second_key ];
					} else {
						$input = wc_clean( $_REQUEST[ $second_key ] );
					}
				} elseif ( isset( $_REQUEST[ $first_key ] ) && ! empty( $_REQUEST[ $first_key ] ) ) {
					//Do not sanitize array field
					if ( is_array( $_REQUEST[ $first_key ] ) ) {
						$input = $_REQUEST[ $first_key ];
					} else {
						$input = wc_clean( $_REQUEST[ $first_key ] );
					}
				}
				if ( ! empty( $input ) ) {
					$posted_data[ $first_key ] = $input;
				}
			}
		}
		if ( ! wc_shipping_enabled() || WFACP_Common::is_cart_is_virtual() ) {


			$shipping_keys = [
				'shipping_first_name',
				'shipping_last_name',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_city',
				'shipping_postcode',
				'shipping_country',
				'shipping_state'
			];
			$shipping_keys = apply_filters( 'wfacp_unset_vice_versa_keys_shipping_keys', $shipping_keys, $this );
			foreach ( $shipping_keys as $shipping_key ) {
				if ( isset( $posted_data[ $shipping_key ] ) ) {
					unset( $posted_data[ $shipping_key ] );
					unset( $this->address_keys[ $shipping_key ] );
				}
			}
		}

		if ( isset( $_REQUEST['wfacp_source'] ) ) {
			$wfacp_source = wc_clean( $_REQUEST['wfacp_source'] );
			if ( filter_var( $wfacp_source, FILTER_VALIDATE_URL ) ) {
				$posted_data['wfacp_source'] = $wfacp_source;
			}
		}
		if ( isset( $_REQUEST['_wfacp_post_id'] ) ) {
			$posted_data['wfacp_post_id'] = wc_clean( $_REQUEST['_wfacp_post_id'] );
		}

		return $posted_data;
	}



	public function set_address_data( $order, $posted_data ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}
		$fields_prefix   = array(
			'shipping' => true,
			'billing'  => true,
		);
		$shipping_fields = array(
			'shipping_method' => true,
			'shipping_total'  => true,
			'shipping_tax'    => true,
		);


		foreach ( $this->address_keys as $key => $value ) {
			if ( isset( $posted_data[ $key ] ) ) {
				$value = $posted_data[ $key ];
				if ( is_callable( array( $order, "set_{$key}" ) ) ) {
					$order->{"set_{$key}"}( $value );
					// Store custom fields prefixed with wither shipping_ or billing_. This is for backwards compatibility with 2.6.x.
				} elseif ( isset( $fields_prefix[ current( explode( '_', $key ) ) ] ) ) {
					if ( ! isset( $shipping_fields[ $key ] ) ) {
						$order->update_meta_data( '_' . $key, $value );
					}
				}
			}
		}
	}

	public function update_custom_fields( $order_id, $posted_data ) {
		$wfacp_id = absint( $posted_data['wfacp_post_id'] );
		if ( $wfacp_id > 0 ) {
			update_post_meta( $order_id, '_wfacp_post_id', $wfacp_id );
			update_post_meta( $order_id, '_wfacp_source', $posted_data['wfacp_source'] );
			$cfields = WFACP_Common::get_page_custom_fields( $wfacp_id );
			if ( ! isset( $cfields['advanced'] ) ) {
				return;
			}
			$advancedFields = $cfields['advanced'];
			if ( ! is_array( $advancedFields ) || count( $advancedFields ) == 0 ) {
				return;
			}
			foreach ( $advancedFields as $field_key => $field ) {
				if ( isset( $_REQUEST[ $field_key ] ) ) {
					$field_value = $_REQUEST[ $field_key ];
					if ( ! empty( $field_value ) && $field['type'] == 'date' ) {
						$field_value = date( 'Y-m-d', strtotime( $field_value ) );
					} elseif ( ! empty( $field_value ) && $field['type'] == 'wfacp_dob' ) {
						$field_value = $_REQUEST[ $field_key ]['year'] . '-' . $_REQUEST[ $field_key ]['month'] . '-' . $_REQUEST[ $field_key ]['day'];
					}
					if ( $field['type'] != 'multiselect' ) {
						$field_value = wc_clean( $field_value );
					}
					update_post_meta( $order_id, $field_key, $field_value );
				}
			}
		}
	}

	/**
	 * Return shipping or billing
	 * get which address field is hidden in form Shipping or billing
	 * @return string
	 */
	public function get_shipping_billing_index() {

		if ( $this->have_shipping_address && $this->have_billing_address ) {
			$have_billing_address_index  = absint( $this->have_billing_address_index );
			$have_shipping_address_index = absint( $this->have_shipping_address_index );
			if ( $have_billing_address_index < $have_shipping_address_index ) {
				return 'shipping';
			} else {
				return 'billing';
			}
		}

		return '';
	}

	/**
	 * @param $template_fields
	 *
	 * @return mixed
	 * @since 1.6.0
	 */
	public function correct_country_state_locals( $template_fields ) {

		// check for billing country locale values
		if ( '' !== WC()->checkout->get_value( 'billing_country' ) ) {
			$locale  = WC()->countries->get_country_locale();
			$country = WC()->checkout->get_value( 'billing_country' );
			if ( isset( $locale[ $country ] ) && isset( $template_fields['billing'] ) ) {

				$array_without_key = [];
				foreach ( $template_fields['billing'] as $key => $value ) {
					$array_without_key[ str_replace( 'billing_', '', $key ) ] = $value;
				}
				$get_filtered_array = wc_array_overlay( $array_without_key, $locale[ $country ] );
				foreach ( $template_fields['billing'] as $key => $value ) {
					$truncated_key = str_replace( 'billing_', '', $key );
					if ( isset( $get_filtered_array[ $truncated_key ] ) ) {
						$template_fields['billing'][ $key ] = $get_filtered_array[ $truncated_key ];
					}
				}
			}
		}

		// check for shipping country locale values
		if ( '' !== WC()->checkout->get_value( 'shipping_country' ) ) {
			$locale  = WC()->countries->get_country_locale();
			$country = WC()->checkout->get_value( 'shipping_country' );

			if ( isset( $locale[ $country ] ) && isset( $template_fields['shipping'] ) ) {

				$array_without_key = [];
				foreach ( $template_fields['shipping'] as $key => $value ) {
					$array_without_key[ str_replace( 'shipping_', '', $key ) ] = $value;
				}
				$get_filtered_array = wc_array_overlay( $array_without_key, $locale[ $country ] );
				foreach ( $template_fields['shipping'] as $key => $value ) {
					$truncated_key = str_replace( 'shipping_', '', $key );
					if ( isset( $get_filtered_array[ $truncated_key ] ) ) {
						$template_fields['shipping'][ $key ] = $get_filtered_array[ $truncated_key ];
					}
				}
			}
		}

		return $template_fields;
	}

	public function get_google_webfonts() {
		$url    = 'https://www.googleapis.com/webfonts/v1/webfonts?key=key_here&&sort=alpha';
		$raw    = file_get_contents( $url, 0, null, null );
		$result = json_decode( $raw );

		$font_list = array();
		foreach ( $result->items as $font ) {
			$font_list[] .= $font->family;
		}

	}


	public function get_view( $template ) {
		extract( array( 'data' => $this->get_data() ) );
		do_action( 'wfacp_before_template_load' );
		include $this->get_template_url( $template );
		do_action( 'wfacp_after_template_load' );
		exit;
	}

	public function get_template_url( $template = '' ) {
		return $this->template_dir . '/views/view.php';
	}


	public function get_slug() {
		return $this->template_slug;
	}

	public function get_url() {
		return $this->url;
	}

	public function get_wfacp_id() {
		return $this->wfacp_id;
	}

	public function set_wfacp_id( $wfacp_id = false ) {
		if ( false !== $wfacp_id ) {
			$this->wfacp_id = $wfacp_id;
		}
	}

	public function get_fieldsets() {
		return apply_filters( 'wfacp_get_fieldsets', $this->fieldsets );
	}

	public function get_fields() {
		return $this->fields;
	}

	final public function set_data( $data = false ) {
		$data = WFACP_Common::get_fieldset_data( WFACP_Common::get_id() );

		foreach ( $data as $key => $val ) {
			$this->{$key} = $val;
		}
		$this->have_billing_address  = wc_string_to_bool( $data['have_billing_address'] );
		$this->have_shipping_address = wc_string_to_bool( $data['have_shipping_address'] );
		$this->have_coupon_field     = isset( $data['have_coupon_field'] ) ? wc_string_to_bool( $data['have_coupon_field'] ) : $this->have_coupon_field;
		$this->have_shipping_method  = isset( $data['have_shipping_method'] ) ? wc_string_to_bool( $data['have_shipping_method'] ) : $this->have_shipping_method;
		$this->checkout_fields       = WFACP_Common::get_checkout_fields( WFACP_Common::get_id() );

	}


	public function wfacp_get_header() {
		return $this->template_dir . '/views/template-parts/header.php';
	}

	public function wfacp_get_footer() {
		return $this->template_dir . '/views/template-parts/footer.php';
	}

	public function wfacp_get_sidebar() {

		return $this->template_dir . '/views/template-parts/sidebar.php';
	}

	public function wfacp_get_product() {

		return $this->template_dir . '/views/template-parts/product.php';
	}

	public function have_shipping_address() {

		return $this->have_shipping_address;
	}

	public function have_billing_address() {

		return $this->have_billing_address;
	}


	final public function wfacp_get_form() {

		$template = WFACP_TEMPLATE_COMMON . '/form.php';
		$temp     = apply_filters( 'wfacp_form_template', $template );

		if ( ! empty( $temp ) ) {
			$template = $temp;
		}

		return $template;
	}

	final public function get_back_button( $current_action, $formData = [] ) {
		include WFACP_TEMPLATE_COMMON . '/back-button.php';
	}

	final public function get_next_button( $current_action, $formData = [] ) {
		include WFACP_TEMPLATE_COMMON . '/next-button.php';
	}

	final public function get_payment_box() {
		do_action( 'wfacp_before_payment_section' );
		include WFACP_TEMPLATE_COMMON . '/payment.php';
		do_action( 'wfacp_after_payment_section' );
	}

	public function assign_first_last_name() {
		$arr = [
			'billing_first_name'  => 'shipping_first_name',
			'billing_last_name'   => 'shipping_last_name',
			'shipping_first_name' => 'billing_first_name',
			'shipping_last_name'  => 'billing_last_name',
		];
		foreach ( $arr as $a_key => $second_key ) {
			if ( isset( $_REQUEST[ $a_key ] ) && '' !== $_REQUEST[ $a_key ] ) {
				$_REQUEST[ $a_key ] = $_REQUEST[ $a_key ];
				continue;
			}
			if ( isset( $_REQUEST[ $second_key ] ) && '' !== $_REQUEST[ $second_key ] ) {
				$_REQUEST[ $a_key ] = $_REQUEST[ $second_key ];
				continue;
			}
		}

	}

	/**
	 * Prepopulate field data From URL
	 * if data not present in URL then we check default data and populate the data
	 *
	 * @param $value
	 * @param $key
	 * @param $field
	 *
	 * @return mixed|string
	 */
	public function pre_populate_from_get_parameter( $value, $key, $field ) {

		if ( '' == $key ) {
			return $value;
		}

		if ( isset( $_REQUEST[ $key ] ) ) {
			$new_value = urldecode( $_REQUEST[ $key ] );
		} else if ( isset( $field['default'] ) && '' !== $field['default'] ) {
			$new_value = $field['default'];
		} elseif ( isset( $field['type'] ) && 'select' == $field['type'] && ! empty( $field['options'] ) && ! isset( $field['org_type'] ) && ! isset( $field['multiselect_maximum'] ) ) {
			$options   = array_keys( $field['options'] );
			$new_value = $options[0];
		} else {
			$new_value = $value;
		}

		$value = apply_filters( 'wfacp_populate_default_value', $new_value, $value, $field, $this );

		return $value;
	}


	public function remove_form_billing_and_shipping_html( $template ) {

		if ( in_array( $template, [ 'checkout/form-billing.php', 'checkout/form-billing.php', 'cart/shipping-calculator.php' ] ) ) {
			return WFACP_TEMPLATE_DIR . '/empty.php';
		}

		return $template;

	}

	public function replace_recurring_total_shipping( $template, $template_name ) {

		if ( function_exists( 'wcs_cart_totals_subtotal_html' ) && in_array( $template_name, [ 'checkout/recurring-totals.php' ] ) ) {
			return WFACP_TEMPLATE_COMMON . '/checkout/recurring-totals.php';
		}

		return $template;
	}

	public function remove_admin_bar() {
		if ( WFACP_Common::is_theme_builder() || ! is_super_admin() ) {
			return false;
		}
		if ( is_super_admin() ) {
			$user_id = get_current_user_id();
			if ( $user_id > 0 ) {
				$show_admin_bar_front = get_user_meta( $user_id, 'show_admin_bar_front', true );

				return wc_string_to_bool( $show_admin_bar_front );
			}
		}

		return true;
	}


	public function show_account_fields( $key, $field, $dvalue ) {
		include WFACP_TEMPLATE_COMMON . '/account.php';
	}

	public function woocommerce_countries_shipping_countries( $countries ) {
		if ( is_array( $countries ) && count( $countries ) == 0 ) {
			$countries = WC()->countries->get_countries();
		}

		return $countries;
	}

	public function woocommerce_countries_allowed_countries( $countries ) {
		if ( is_array( $countries ) && count( $countries ) == 0 ) {
			$countries = WC()->countries->get_countries();
		}

		return $countries;
	}

	public function remove_add1_add2_local_field_selector( $locale_fields ) {
		if ( isset( $locale_fields['address_1'] ) ) {
			unset( $locale_fields['address_1'] );
		}
		if ( isset( $locale_fields['address_2'] ) ) {
			unset( $locale_fields['address_2'] );
		}

		return $locale_fields;
	}

	public function add_viewport_meta() {
		include WFACP_TEMPLATE_COMMON . '/meta.php';
	}

	public function reattach_necessary_hooks() {
		if ( ! has_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment' ) ) {
			add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment' );
		}

		if ( has_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review' ) ) {
			remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review' );
		}
	}


	public function display_hide_payment_box_heading() {

		if ( ! WC()->cart->needs_payment() ) {
			?>
            <style>
                .wfacp_payment .wfacp-comm-title {
                    display: none
                }
            </style>
			<?Php
		}

	}


	/**
	 * @param $total
	 *
	 * @return false|string
	 *
	 * check shipping total if its less then or zero and check shipping name
	 */
	public function wc_check_matched_rate( $total ) {

		$amt = (int) WC()->cart->get_shipping_total();

		if ( $amt == 0 ) {
			$label = $this->check_shipping_name();
			if ( $label != '' ) {
				return $label;
			} else {
				return $total;
			}
		}

		return $total;
	}

	/**
	 * @return false|string
	 *
	 * Return Shipping Name when Local Pickup Activate in shipping
	 */

	public function check_shipping_name() {
		$packages       = WC()->shipping->get_packages();
		$resultHtml     = '';
		$chooseShipping = wc_get_chosen_shipping_method_ids();

		foreach ( $packages as $i => $package ) {

			$available_methods = $package['rates'];

			if ( is_array( $available_methods ) && count( $available_methods ) > 0 ) {
				foreach ( $available_methods as $method ) {

					if ( strpos( $method->id, 'local_pickup' ) !== false && ( is_array( $chooseShipping ) && strpos( $chooseShipping[0], 'local_pickup' ) !== false ) ) {
						ob_start();
						printf( '<label style="font-weight: normal;" for="shipping_method_%1$s_%2$s">%3$s</label>', $i, esc_attr( sanitize_title( $method->id ) ), __( 'Free', 'woocommerce' ) );
						$resultHtml = ob_get_clean();
					}
				}
			}

			return $resultHtml;

		}
	}


	public function display_undo_message() {

		if ( ! wp_doing_ajax() ) {
			return;
		}

		$settings = WFACP_Core()->public->get_product_settings();
		if ( empty( $settings ) ) {
			return;
		}
		if ( isset( $settings['add_to_cart_setting'] ) && $settings['add_to_cart_setting'] != 1 && ! WFACP_Core()->public->is_checkout_override() ) {
			return;
		}

		WFACP_Common::get_cart_undo_message();

	}

	public function display_mini_cart_undo_message() {
		if ( ! wp_doing_ajax() ) {
			return;
		}
		WFACP_Common::get_cart_undo_message();
	}


	public function payment_button_text() {
		return '';
	}

	/**
	 * Forcefully change order button text for authorize and paypal express gateway
	 *
	 * @param $gateways
	 *
	 * @return mixed
	 */
	public function change_payment_gateway_text( $gateways ) {

		$orderText = $this->payment_button_text();
		if ( isset( $orderText ) && $orderText != '' ) {
			foreach ( $gateways as $gateway_id => $gateway ) {
				if ( in_array( $gateway_id, apply_filters( 'wfacp_allowed_gateway_order_button_text_change', [
					'authorize_net_cim_credit_card',
					'ppec_paypal',
					'square_credit_card',
					'braintree_credit_card',
					'braintree_cc',
					'nmi_gateway_woocommerce_credit_card'
				], $this ) ) ) {
					$gateways[ $gateway_id ]->order_button_text = $orderText;
				}
			}
		}

		return $gateways;
	}


	/**
	 * Change cancel url for dedicated only
	 *
	 * @param $url
	 *
	 * @return false|string
	 */
	public function change_cancel_url( $url ) {
		if ( WFACP_Core()->public->is_checkout_override() ) {
			return $url;
		}
		if ( ! WFACP_Core()->public->is_checkout_override() ) {
			$url = get_the_permalink( WFACP_Common::get_id() );
		}

		return $url;
	}

	public function have_coupon_field() {
		return $this->have_coupon_field;
	}

	public function have_shipping_method() {
		return $this->have_shipping_method;
	}

	public function get_wc_addr2_company_value() {

		$woocommerce_checkout_address_2_field = get_option( 'woocommerce_checkout_address_2_field', 'optional' );
		$woocommerce_checkout_company_field   = get_option( 'woocommerce_checkout_company_field', 'optional' );

		$get_wc_addr2_company = [
			'shipping_address_2_field' => 'wfacp_required_optional',
			'billing_address_2_field'  => 'wfacp_required_optional',
			'shipping_company_field'   => 'wfacp_required_optional',
			'billing_company_field'    => 'wfacp_required_optional',
		];

		if ( 'required' === $woocommerce_checkout_address_2_field ) {
			$get_wc_addr2_company['shipping_address_2_field'] = 'wfacp_required_active';
			$get_wc_addr2_company['billing_address_2_field']  = 'wfacp_required_active';
		}

		if ( 'required' === $woocommerce_checkout_company_field ) {
			$get_wc_addr2_company['shipping_company_field'] = 'wfacp_required_active';
			$get_wc_addr2_company['billing_company_field']  = 'wfacp_required_active';
		}

		return $get_wc_addr2_company;

	}


	public function check_cart_coupons( $fragments ) {
		if ( ! is_null( WC()->cart ) ) {
			WC()->cart->check_cart_coupons();
		}

		return $fragments;
	}


	/**
	 * Create account if subscription product present in cart but create account not allowed from settings
	 * then we forcefully create a account
	 *
	 */
	public function woocommerce_checkout_process() {
		if ( isset( $_POST['wfacp_cart_contains_subscription'] ) && '1' == $_POST['wfacp_cart_contains_subscription'] ) {

			if ( 'yes' !== get_option( 'woocommerce_enable_signup_and_login_from_checkout' ) ) {
				$_POST['createaccount'] = true;
				if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) {
					$_POST['account_username'] = $_POST['billing_email'];
				}
				if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) {
					$_POST['account_password'] = wp_generate_password();
				}
			}
		}
	}


	public function call_before_cart_link( $breadcrumb ) {

	}

	public function get_wfacp_version() {
		$pageID         = WFACP_Common::get_id();
		$_wfacp_version = WFACP_Common::get_post_meta_data( $pageID, '_wfacp_version' );

		if ( $_wfacp_version == WFACP_VERSION ) {
			$this->setting_new_version = true;

			return true;
		}

		return false;

	}

	public function add_styling_class_to_country_field( $field, $key ) {
		if ( in_array( $key, [ 'billing_country', 'shipping_country' ] ) ) {
			$billing_allowed_countries  = WC()->countries->get_allowed_countries();
			$shipping_allowed_countries = WC()->countries->get_shipping_countries();
			if ( count( $billing_allowed_countries ) == 1 || count( $shipping_allowed_countries ) == 1 ) {
				$field['class'][] = 'wfacp_allowed_countries';
				$field['class'][] = 'wfacp-anim-wrap';
			}
		}

		return $field;
	}

	public function wc_cart_totals_coupon_label( $coupon, $echo = true ) {
		if ( is_string( $coupon ) ) {
			$coupon = new WC_Coupon( $coupon );
		}

		$svg = '<svg id="668a2151-f22c-4f0f-8525-beec391fcabb" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 415.33">
<title>Untitled-2</title>
<path d="M222.67,0H270L47,223,213.67,389.67l-25,25L0,226Z" transform="translate(0 0)" style="fill:#999"/>
<path d="M318,0S94,222,95.33,222L288.67,415.33,512,192V0Zm97.67,133.33a41,41,0,1,1,41-41A41,41,0,0,1,415.67,133.33Z" transform="translate(0 0)" style="fill:#999"/>
</svg>';

		$coupon_text = apply_filters( 'wfacp_coupon_label_text', __( 'Coupon', 'woocommerce' ) );
		$label       = apply_filters( 'woocommerce_cart_totals_coupon_label', sprintf( esc_html__( $coupon_text . ' %1$s %2$s', 'woocommerce' ), $svg, "<span class='wfacp_coupon_code'>" . $coupon->get_code() . '</span>' ), $coupon );
		if ( $echo ) {
			echo $label;
		} else {
			return $label;
		}
	}

	public function get_class_from_body() {

		$wfacp_body_class = [
			'wfacp_main_wrapper',
			'wfacp_pre_built',
			'wfacp-' . $this->device_type,
			'wfacp_cls_' . $this->template_slug,
			'single_step',
			'woocommerce-checkout',
			'wfacp_do_not_show_block',
		];

		if ( isset( $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['make_button_sticky_on_mobile'] ) ) {
			$wfacp_body_class[] = $this->customizer_fields_data['wfacp_form']['form_data']['btn_details']['make_button_sticky_on_mobile'];
		}

		$wfacp_body_class = apply_filters( 'wfacp_body_class', $wfacp_body_class );
		$body_cls_str     = '';
		if ( ! empty( $wfacp_body_class ) ) {

			$wfacp_body_class = array_unique( $wfacp_body_class );
			$body_cls_str     = implode( ' ', $wfacp_body_class );
		}

		return $body_cls_str;

	}

	public function remove_extra_payment_gateways_in_customizer( $gateways ) {

		if ( WFACP_Common::is_theme_builder() ) {
			$gateways     = [];
			$payments     = WC_Payment_Gateways::instance();
			$all_gateways = $payments->payment_gateways();
			if ( isset( $all_gateways['cod'] ) ) {
				$gateways['cod']              = $all_gateways['cod'];
				$gateways['cod']->title       = __( 'Payment Gateway', 'woofunnels-aero-checkout' );
				$gateways['cod']->description = __( 'Enabled payment methods will display on the frontend.', 'woofunnels-aero-checkout' );

			}
		}

		return $gateways;
	}

	public function set_selected_template( $data ) {
		$this->selected_register_template = $data;
	}

	public function get_selected_register_template() {
		return $this->selected_register_template;
	}

	protected function get_field_css_ready( $template_slug, $field_index ) {
		return '';
	}

	public function merge_builder_data( $field, $field_index ) {

		$template_slug = $this->get_template_slug();
		$template_slug = sanitize_title( $template_slug );
		$css_ready     = $this->get_field_css_ready( $template_slug, $field_index );
		if ( '' !== $css_ready ) {
			$field['cssready'] = explode( ',', $css_ready );
		}

		$css_classes = $this->default_css_class();

		if ( isset( $this->css_classes[ $field_index ] ) ) {
			$css_classes = $this->css_classes[ $field_index ];
		}
		$wrapper_class = 'wfacp-form-control-wrapper ';

		if ( isset( $field['cssready'] ) && is_array( $field['cssready'] ) && count( $field['cssready'] ) > 0 ) {
			$wrapper_class .= implode( ' ', $field['cssready'] );
		} else {
			$wrapper_class .= ' ' . $css_classes['class'];
		}
		$input_class = 'wfacp-form-control';
		$label_class = 'wfacp-form-control-label';
		if ( isset( $field['input_class'] ) && ! is_array( $field['input_class'] ) ) {
			$field['input_class'] = [];
		}
		if ( isset( $field['input_class'] ) && ( ! isset( $field['label_class'] ) || ! is_array( $field['label_class'] ) ) ) {
			$field['label_class'] = [];
		}
		$field['class'][]       = $wrapper_class;
		$field['input_class'][] = $input_class;
		$field['label_class'][] = $label_class;
		$field['class']         = array_unique( $field['class'] );
		$field['input_class']   = array_unique( $field['input_class'] );
		$field['label_class']   = array_unique( $field['label_class'] );

		if ( $field_index == 'billing_address_2' || $field_index == 'street_address_2' ) {
			$search_index = array_search( 'screen-reader-text', $field['label_class'] );
			if ( false !== $search_index ) {
				unset( $field['label_class'][ $search_index ] );
			}
		}

		if ( isset( $field['type'] ) ) {


			if ( $field['type'] == 'multiselect' ) {

				$field['class'][]                       = 'wfacp_custom_field_multiselect';
				$field['type']                          = 'select';
				$field['name']                          = $field['id'] . '[]';
				$field['custom_attributes']['multiple'] = 'multiple';
				if ( isset( $field['multiselect_maximum'] ) ) {
					$field['custom_attributes']['data-max-selection'] = $field['multiselect_maximum'];
				}
				if ( isset( $field['multiselect_maximum_error'] ) ) {
					$field['custom_attributes']['data-max-error'] = $field['multiselect_maximum_error'];
				}

			} elseif ( 'email' == $field['type'] ) {
				$field['validate'][] = 'email';
			} elseif ( 'checkbox' == $field['type'] ) {
				$field['class'][] = 'wfacp_checkbox_field';
				unset( $field['label_class'][0] );
				if ( isset( $field['field_type'] ) && $field['field_type'] != 'advanced' ) {
					unset( $field['input_class'][0] );
				}
			} elseif ( 'select2' == $field['type'] ) {
				$field['class'][]       = 'wfacp_custom_field_select2';
				$field['org_type']      = $field['type'];
				$field['type']          = 'select';
				$field['input_class'][] = 'wfacp_select2_custom_field';
				$options                = $field['options'];
				$field['options']       = array_merge( [ '' => $field['placeholder'] ], $options );
			}

			if ( in_array( $field['type'], [ 'date' ] ) ) {
				$default = $field['default'];
				if ( '' !== $default ) {
					$default          = str_replace( '/', '-', $default );
					$field['default'] = date( 'Y-m-d', strtotime( $default ) );
				}
				unset( $default );
			}
		}

		if ( in_array( $field_index, [ 'billing_postcode', 'shipping_postcode', 'billing_city', 'shipping_city' ] ) ) {
			$field['class'][] = 'update_totals_on_change';
		}

		if ( in_array( $field_index, [ 'billing_country', 'shipping_country' ] ) ) {
			if ( ! empty( $this->base_country[ $field_index ] ) ) {
				$field['default'] = $this->base_country[ $field_index ];
			}
		}
		if ( in_array( $field_index, [ 'billing_state', 'shipping_state' ] ) ) {
			$field['class'][] = 'wfacp_state_wrap';

		}

		return $field;
	}

	public function get_preview_field_heading() {
		$global_setting = WFACP_Core()->public->get_settings();
		if ( isset( $global_setting['preview_section_heading'] ) && $global_setting['preview_section_heading'] != '' ) {
			return trim( $global_setting['preview_section_heading'] );

		}

		return '';
	}

	public function get_preview_field_sub_heading() {
		$global_setting = WFACP_Core()->public->get_settings();
		if ( isset( $global_setting['preview_section_subheading'] ) && $global_setting['preview_section_subheading'] != '' ) {
			return trim( $global_setting['preview_section_subheading'] );
		}

		return '';
	}


	public function set_current_open_step( $step = 'single_step' ) {
		$this->current_open_step = $step;
	}

	public function get_current_open_step() {
		return $this->current_open_step;
	}

	public function set_form_data( $settings ) {
		$this->form_data = $settings;
	}

	public function get_form_data() {

		return [];
	}

	public function get_heading_title_class() {

		return '';
	}

	public function get_heading_class() {

		return '';
	}

	public function get_sub_heading_class() {

		return '';
	}


	public function get_payment_desc() {
		return '';
	}

	public function payment_heading() {

		return '';
	}

	public function payment_sub_heading() {

		return esc_attr__( 'All transactions are secure and encrypted. Credit card information is never stored on our servers.', 'woofunnels-aero-checkout' );
	}

	public function change_single_step_label( $name, $current_action ) {
		return $name;
	}

	public function change_two_step_label( $name, $current_action ) {
		return $name;
	}

	public function change_place_order_button_text( $text ) {

		return $text;
	}

	public function payment_button_alignment() {

		return 'center';
	}

	public function display_back_button( $step, $current_step ) {

		$alignmentclass = '';
		$width_cls      = '';


		if ( $step == $current_step ) {
			return;
		}


		if ( 'single_step' != $step ) {

			$class = apply_filters( 'wfacp_blank_back_text', '', $step, $current_step );
			echo sprintf( '<div class="sec_text_wrap %s %s %s">', $alignmentclass, $width_cls, $class );

			echo '<div class="btm_btn_sec ">';

			$this->get_back_button( $step );
			echo '</div>';
		}
	}

	public function close_back_button_div( $step, $current_step ) {
		if ( 'single_step' != $step && $step != $current_step ) {
			echo '</div>';
		}
	}

	public function display_next_button( $step, $current_step ) {
		$form_data = $this->get_form_data();
		if ( 'single_step' != $current_step ) {
			$this->get_next_button( $step, $form_data );
		}
	}

	public function change_back_step_label( $text, $next_action, $current_action ) {

		return $text;
	}

	public function add_class_change_place_order( $btn_text ) {

		return $btn_text;
	}

	public function preview_field_generate( $step, $instance ) {
		include WFACP_TEMPLATE_COMMON . '/parts/preview_field.php';
	}

	public function get_template_type_px() {

		$template_type          = $this->get_template_type();
		$wfacp_templates_slug   = $this->wfacp_templates_slug;
		$selected_template_slug = $this->get_template_slug();
		if ( $template_type != '' ) {
			if ( is_array( $wfacp_templates_slug ) && isset( $wfacp_templates_slug[ $template_type ] ) ) {
				$templateDetails = $wfacp_templates_slug[ $template_type ];

				if ( $selected_template_slug != '' && is_array( $templateDetails ) && isset( $templateDetails[ $selected_template_slug ] ) ) {
					return $templateDetails[ $selected_template_slug ];
				}
			}
		}

		return 15;
	}

	public function get_mobile_mini_cart_collapsible_title() {
		return '';

	}

	public function enable_collapsed_coupon_field() {
		return '';
	}


	public function collapse_enable_coupon_collapsible() {
		return 'false';
	}

	public function enable_coupon_right_side_coupon() {
		return 'true';
	}

	public function get_mobile_mini_cart_expand_title() {
		return '';
	}

	public function get_product_switcher_mobile_style() {
		return '';
	}


	public function get_mobile_mini_cart( $input_data = [] ) {
		if ( WFACP_Core()->pay->is_order_pay() ) {
			return;
		}
		include WFACP_TEMPLATE_COMMON . '/template-parts/mobile-collapsible-mini-cart.php';
	}


	public function get_data() {
		return $this->data;
	}

	public function get_smart_buttons() {
		return $this->smart_buttons;
	}

	final public function display_smart_buttons() {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		include WFACP_TEMPLATE_COMMON . '/smart_buttons.php';

	}

	/**
	 *backward compatibility for header footer
	 */
	public function get_customizer_data() {

	}

	final public function get_container() {
		include $this->template_dir . '/views/container.php';
	}

	public function get_theme_header() {
		get_header();
		do_action( 'wfacp_header_print_in_head' );
	}

	public function get_theme_footer() {
		do_action( 'wfacp_footer_before_print_scripts' );
		get_footer();
		do_action( 'wfacp_footer_after_print_scripts' );
	}

	public function global_css() {
		$_wfacp_global_settings = get_option( '_wfacp_global_settings' );
		$page_settings          = WFACP_Common::get_page_settings( WFACP_Common::get_id() );

		if ( isset( $_wfacp_global_settings['wfacp_checkout_global_css'] ) && $_wfacp_global_settings['wfacp_checkout_global_css'] != '' ) {
			$global_custom_css = '<style>' . $_wfacp_global_settings['wfacp_checkout_global_css'] . '</style>';
			echo $global_custom_css;
		}
		if ( isset( $page_settings['header_css'] ) && $page_settings['header_css'] != '' ) {
			$header_css = '<style id="header_css">' . $page_settings['header_css'] . '</style>';
			echo $header_css;
		}
	}

	/**
	 * Override this when new template using theme template or aero checkout boxed template
	 * @return bool
	 */
	public function use_own_template() {
		return true;
	}

	public function remove_admin_bar_print_hook() {
		remove_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
		remove_action( 'in_admin_header', 'wp_admin_bar_render', 0 );
		add_action( 'wfacp_footer_after_print_scripts', 'wp_admin_bar_render' );
	}

	final public function include_front_files() {
		include __DIR__ . '/class-wfacp-autocomplete.php';
	}

	final public function remove_unused_js() {
		// this password strength js enqueue wordpress but not use by Woocommerce. Woocommerce enqueue password strength by own library
		wp_dequeue_script( 'password-strength-meter' );
		if ( ! is_product() ) {
			//this is extra js enqueue by woocommerce on every page if ajax add to cart enabled by woocommerce Settings at our checkout page no add to cart button present
			wp_dequeue_script( 'wc-add-to-cart' );
		}
	}

	public function get_order_pay_summary( $order ) {
		include WFACP_TEMPLATE_COMMON . '/order-pay-summary.php';
	}

	public function get_order_pay_summary_heading() {
		return apply_filters( 'wfacp_order_pay_summary_heading', __( 'Review Order Summary', 'woofunnels-aero-checkout' ) );
	}

	public function add_body_class( $class ) {
		$class[] = 'wfacp_do_not_show_block';
		if ( WFACP_Core()->pay->is_order_pay() ) {
			$class[] = 'woocommerce-order-pay';
		}

		return $class;
	}

	/**
	 * to avoid unserialize of the current class
	 */
	public function __wakeup() {
		throw new ErrorException( 'WFACP_Core classes can`t converted to string' );
	}

	/**
	 * to avoid serialize of the current class
	 */
	public function __sleep() {
		throw new ErrorException( 'WFACP_Core classes can`t converted to string' );
	}

	/**
	 * To avoid cloning of current template class
	 */
	protected function __clone() {
	}


}
