<?php

defined( 'ABSPATH' ) || exit;

final class WFACP_Customizer {

	private static $ins = null;
	public static $is_checkout = false;
	/**
	 * @var WFACP_Pre_Built
	 */
	private $template_ins = null;
	private $template_path = '';
	private $wfacp_id = 0;
	private $is_customizer_page = false;
	public $customizer_key_prefix = '';
	public $template_assets = '';

	protected function __construct() {
		$this->wfacp_id        = WFACP_Common::get_id();
		$this->template_path   = WFACP_PLUGIN_DIR . '/templates';
		$this->template_assets = WFACP_PLUGIN_URL . '/assets';
		add_action( 'wfacp_register_template_types', [ $this, 'register_template_type' ] );
		add_filter( 'wfacp_register_templates', [ $this, 'register_templates' ] );
		add_action( 'wfacp_register_template_types', [ $this, 'register_wp_editor_template_type' ], 999 );
		add_filter( 'wfacp_register_templates', [ $this, 'register_wp_editor_templates' ], 999 );

		add_action( 'wfacp_checkout_page_found', [ $this, 'setup_after_page_found' ] );
		add_filter( 'wfacp_post', [ $this, 'may_be_customizer_page' ] );
		add_action( 'wfacp_after_template_found', [ $this, 'may_setup_data' ] );

		$this->maybe_load_customizer();
	}

	public function is_customizer_template( $type ) {
		return in_array( $type, [ 'embed_forms', 'pre_built', 'embed_form' ] );
	}

	/**
	 * @param $loader WFACP_Template_loader
	 */
	public function register_template_type( $loader ) {
		$loader->remove_template_type( 'embed_forms' );
		$loader->remove_all_templates( 'embed_forms' );

		$template = [
			'slug'    => 'pre_built',
			'title'   => __( 'Customizer', 'woofunnels-aero-checkout' ),
			'filters' => WFACP_Common::get_template_filter()
		];
		$loader->register_template_type( $template );


	}

	public function register_templates( $designs ) {
		$designs['pre_built'] = [

			'shopcheckout'       => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_9/template.php',
				'name'              => __( 'ShopCheckout', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/shopcheckout.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'default'           => 'yes',
				'slug'              => 'shopcheckout',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=12644&type=checkout',
				'no_steps'          => '1'
			],
			'shopcheckout-multi' => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_9/template.php',
				'name'              => __( 'ShopCheckout', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/shopcheckout_2_step.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'shopcheckout-multi',
				'multistep'         => 'yes',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13280&type=checkout',
				'no_steps'          => '2'
			],
			'shopcheckout-three' => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_9/template.php',
				'name'              => __( 'ShopCheckout', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/shopcheckout_3_step.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'shopcheckout-three',
				'multistep'         => 'yes',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13292&type=checkout',
				'no_steps'          => '3'
			],
			'classic'            => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_1/template.php',
				'name'              => __( 'Classic', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/classic.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'classic',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=12646&type=checkout',
				'no_steps'          => '1'
			],
			'salesletter'        => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_2/template.php',
				'name'              => __( 'SalesLetter', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/salesletter.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'salesletter',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=12647&type=checkout',
				'no_steps'          => '1'
			],
			'marketer'           => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_4/template.php',
				'name'              => __( 'Marketer', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/marketer.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'marketer',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=12648&type=checkout',
				'no_steps'          => '1'
			],


			'royale-step-2'      => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_9/template.php',
				'name'              => __( 'Royale', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/royale2_2_step.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'royale-step-2',
				'multistep'         => 'yes',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13282&type=checkout',
				'no_steps'          => '2'
			],
			'royale-step-3'      => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_9/template.php',
				'name'              => __( 'Royale', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/royale2_3_step.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'royale-step-3',
				'multistep'         => 'yes',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13295&type=checkout',
				'no_steps'          => '3'
			],
			'classic-step-2'     => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_1/template.php',
				'name'              => __( 'Classic', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/classic_2_step.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'classic-step-2',
				'multistep'         => 'yes',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13283&type=checkout',
				'no_steps'          => '2'
			],
			'salesletter-step-2' => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_2/template.php',
				'name'              => __( 'SalesLetter', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/salesletter_2_step.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'salesletter-step-2',
				'multistep'         => 'yes',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13286&type=checkout',
				'no_steps'          => '2'
			],
			'marketer-step-2'    => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_4/template.php',
				'name'              => __( 'Marketer', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/marketer_2_step.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'marketer-step-2',
				'multistep'         => 'yes',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13289&type=checkout',
				'no_steps'          => '2'
			],
			'classic-step-3'     => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_1/template.php',
				'name'              => __( 'Classic', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/classic_3_step.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'classic-step-3',
				'multistep'         => 'yes',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13297&type=checkout',
				'no_steps'          => '3'
			],
			'salesletter-step-3' => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_2/template.php',
				'name'              => __( 'SalesLetter', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/salesletter_3_step.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'salesletter-step-3',
				'multistep'         => 'yes',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13300&type=checkout',
				'no_steps'          => '3'
			],
			'marketer-step-3'    => [
				'path'              => WFACP_BUILDER_DIR . '/customizer/templates/layout_4/template.php',
				'name'              => __( 'Marketer', 'woofunnels-aero-checkout' ),
				'thumbnail'         => 'https://woofunnels.s3.amazonaws.com/templates/checkout/customizer/marketer_3_step.jpg',
				'type'              => 'view',
				'description'       => '',
				'import'            => 'yes',
				'show_import_popup' => 'yes',
				'group'             => 'pre_built',
				'builder'           => 'pre_built',
				'slug'              => 'marketer-step-3',
				'multistep'         => 'yes',
				'preview_url'       => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13303&type=checkout',
				'no_steps'          => '3'
			],

		];

		return $designs;

	}

	/**
	 * @param $loader WFACP_Template_loader
	 */
	public function register_wp_editor_template_type( $loader ) {
		$template = [
			'slug'    => 'embed_forms',
			'title'   => __( 'Custom', 'woofunnels-aero-checkout' ),
			'filters' => WFACP_Common::get_template_filter()
		];
		$loader->register_template_type( $template );
	}

	public function register_wp_editor_templates( $designs ) {

		$designs['embed_forms'] = [

			'embed_forms_4'        => [
				'path'           => WFACP_BUILDER_DIR . '/customizer/templates/embed_forms_1/template.php',
				'name'           => __( 'Checkout Form', 'woofunnels-aero-checkout' ),
				'thumbnail'      => 'https://woofunnels.s3.amazonaws.com/templates/checkout/custom/custom-checkout-form-1.jpg',
				'import'         => 'yes',
				'show_shortcode' => 'yes',
				'group'          => 'embed_forms',
				'builder'        => 'embed_forms',
				'slug'           => 'embed_forms_4',
				'no_steps'       => '1',
				'preview_url'    => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13480&type=checkout',
			],
			'embed_forms_5'        => [
				'path'           => WFACP_BUILDER_DIR . '/customizer/templates/embed_forms_1/template.php',
				'name'           => __( 'Checkout Form', 'woofunnels-aero-checkout' ),
				'thumbnail'      => 'https://woofunnels.s3.amazonaws.com/templates/checkout/custom/custom-checkout-form-2.jpg',
				'import'         => 'yes',
				'show_shortcode' => 'yes',
				'group'          => 'embed_forms',
				'builder'        => 'embed_forms',
				'slug'           => 'embed_forms_5',
				'no_steps'       => '2',
				'preview_url'    => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13482&type=checkout',
			],
			'custom_funnel_form_2' => [
				'path'           => WFACP_BUILDER_DIR . '/customizer/templates/embed_forms_1/template.php',
				'name'           => __( 'Funnel Form', 'woofunnels-aero-checkout' ),
				'thumbnail'      => 'https://woofunnels.s3.amazonaws.com/templates/checkout/custom/custom-funnel-form-2.jpg',
				'import'         => 'yes',
				'show_shortcode' => 'yes',
				'group'          => 'embed_forms',
				'builder'        => 'embed_forms',
				'slug'           => 'custom_funnel_form_2',
				'no_steps'       => '2',
				'preview_url'    => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13579&type=checkout',
			],
			'embed_forms_6'        => [
				'path'           => WFACP_BUILDER_DIR . '/customizer/templates/embed_forms_1/template.php',
				'name'           => __( 'Checkout Form', 'woofunnels-aero-checkout' ),
				'thumbnail'      => 'https://woofunnels.s3.amazonaws.com/templates/checkout/custom/custom-checkout-form-3.jpg',
				'import'         => 'yes',
				'show_shortcode' => 'yes',
				'group'          => 'embed_forms',
				'builder'        => 'embed_forms',
				'slug'           => 'embed_forms_6',
				'no_steps'       => '3',
				'preview_url'    => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13486&type=checkout',
			],
			'custom_funnel_form_3' => [
				'path'           => WFACP_BUILDER_DIR . '/customizer/templates/embed_forms_1/template.php',
				'name'           => __( 'Funnel Form', 'woofunnels-aero-checkout' ),
				'thumbnail'      => 'https://woofunnels.s3.amazonaws.com/templates/checkout/custom/custom-funnel-form-3.jpg',
				'import'         => 'yes',
				'show_shortcode' => 'yes',
				'group'          => 'embed_forms',
				'builder'        => 'embed_forms',
				'slug'           => 'custom_funnel_form_3',
				'no_steps'       => '3',
				'preview_url'    => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13584&type=checkout',
			],
			'custom_funnel_form_1' => [
				'path'           => WFACP_BUILDER_DIR . '/customizer/templates/embed_forms_1/template.php',
				'name'           => __( 'Funnel Form', 'woofunnels-aero-checkout' ),
				'thumbnail'      => 'https://woofunnels.s3.amazonaws.com/templates/checkout/custom/custom-funnel-form-1.jpg',
				'import'         => 'yes',
				'show_shortcode' => 'yes',
				'group'          => 'embed_forms',
				'builder'        => 'embed_forms',
				'slug'           => 'custom_funnel_form_1',
				'no_steps'       => '1',
				'preview_url'    => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13574&type=checkout',
			],
			'embed_forms_1'        => [
				'path'           => WFACP_BUILDER_DIR . '/customizer/templates/embed_forms_1/template.php',
				'name'           => __( 'Embed Form', 'woofunnels-aero-checkout' ),
				'thumbnail'      => 'https://woofunnels.s3.amazonaws.com/templates/checkout/custom/custom-embed-form-1.jpg',
				'import'         => 'yes',
				'show_shortcode' => 'yes',
				'group'          => 'embed_forms',
				'builder'        => 'embed_forms',
				'slug'           => 'embed_forms_1',
				'no_steps'       => '1',
				'preview_url'    => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13491&type=checkout',
			],

			'embed_forms_2' => [
				'path'           => WFACP_BUILDER_DIR . '/customizer/templates/embed_forms_1/template.php',
				'name'           => __( 'Embed Form', 'woofunnels-aero-checkout' ),
				'thumbnail'      => 'https://woofunnels.s3.amazonaws.com/templates/checkout/custom/custom-embed-form-2.jpg',
				'import'         => 'yes',
				'show_shortcode' => 'yes',
				'group'          => 'embed_forms',
				'builder'        => 'embed_forms',
				'slug'           => 'embed_forms_2',
				'no_steps'       => '2',
				'preview_url'    => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13494&type=checkout',
			],
			'embed_forms_3' => [
				'path'           => WFACP_BUILDER_DIR . '/customizer/templates/embed_forms_1/template.php',
				'name'           => __( 'Embed Form', 'woofunnels-aero-checkout' ),
				'thumbnail'      => 'https://woofunnels.s3.amazonaws.com/templates/checkout/custom/custom-embed-form-3.jpg',
				'import'         => 'yes',
				'show_shortcode' => 'yes',
				'group'          => 'embed_forms',
				'builder'        => 'embed_forms',
				'slug'           => 'embed_forms_3',
				'no_steps'       => '3',
				'preview_url'    => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13499&type=checkout',
			],

		];

		return $designs;
	}


	public function may_be_customizer_page( $post ) {
		if ( WFACP_Common::is_customizer() ) {
			$temp_id = absint( $_REQUEST['wfacp_id'] );
			$post    = get_post( $temp_id );
			//Setup kirki for customizer preview
			$this->customizer_editor();
		}

		return $post;
	}

	public function maybe_load_customizer() {
		add_filter( 'wfacpkirki/config', array( $this, 'wfacp_wfacpkirki_configuration' ), 9999 );
		if ( WFACP_Common::is_customizer() ) {

			add_filter( 'customize_loaded_components', 'WFACP_Common::remove_menu_support', 99 );
			add_filter( 'customize_register', [ $this, 'remove_sections' ], 110 );
			add_action( 'customize_controls_print_styles', [ $this, 'print_customizer_styles' ] );
			add_filter( 'customize_control_active', [ $this, 'control_filter' ], 10, 2 );
			add_action( 'customize_controls_enqueue_scripts', [ $this, 'enqueue_scripts' ], 9999 );
			add_action( 'customize_controls_enqueue_scripts', [ $this, 'maybe_remove_script_customizer' ], 10000 );
			add_filter( 'customize_register', [ $this, 'add_sections' ], 101 );
			add_action( 'customize_save_validation_before', [ $this, 'add_sections' ], 101 );
			add_action( 'wfacp_footer_before_print_scripts', [ $this, 'add_loader_to_customizer' ] );
			add_action( 'admin_enqueue_scripts', array( $this, 'dequeue_unnecessary_customizer_scripts' ), 999 );
			/** Kirki */
			require WFACP_PLUGIN_DIR . '/admin/includes/wfacpkirki/wfacpkirki.php';
			/** wfacpkirki custom controls */
			require WFACP_PLUGIN_DIR . '/includes/class-wfacp-wfacpkirki.php';
		}

	}


	private function customizer_editor() {
		add_action( 'init', array( $this, 'setup_page_for_wfacpkirki' ), 21 );
		add_action( 'init', array( $this, 'wfacp_wfacpkirki_fields' ), 30 );

	}

	public static function get_instance() {
		if ( self::$ins == null ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Setup kirki data after page found
	 */
	public function setup_after_page_found( $page_id ) {
		$page_data = WFACP_Common::get_page_design( $page_id );

		if ( $this->is_customizer_template( $page_data['selected_type'] ) ) {

			$this->is_customizer_page = true;
		}
	}


	/**
	 * Remove any unwanted default controls.
	 *
	 * @param object $wp_customize
	 *
	 * @return bool
	 */
	public function remove_sections( $wp_customize ) {
		global $wp_customize;
		/**
		 * @var $wp_customize WP_Customize_Manager
		 */

		$wp_customize->remove_panel( 'themes' );
		$wp_customize->remove_control( 'active_theme' );
		/** Mesmerize theme */
		$wp_customize->remove_section( 'mesmerize-pro' );

		do_action( 'wfacp_remove_panel_section' );

		return true;
	}


	public function control_filter( $active, $control ) {
		return $this->template_ins->control_filter( $control );
	}

	public function enqueue_scripts() {

		wp_enqueue_style( 'wfacp_customizer_common_style', $this->template_assets . '/css/wfacp-customizer-style.css', array(), WFACP_VERSION_DEV );
		wp_enqueue_script( 'wfacp_customizer_common', $this->template_assets . '/js/customizer-common.js', array( 'customize-controls' ), WFACP_VERSION_DEV, true );
		$template_fields = $this->template_ins->get_customizer_fields();
		$pd              = array();

		wp_localize_script( 'wfacp_customizer_common', 'wfacp_customizer', array(
			'is_loaded'   => 'yes',
			'wfacp_id'    => $this->wfacp_id,
			'fields'      => $template_fields,
			'preview_msg' => __( 'This is a checkout preview for styling purposes. Some of the checkout functions such as showing payment methods or applying coupons  or updating of prices based on shipping methods are restricted. Click here to see the checkout. <a href="' . get_the_permalink( WFACP_Common::get_id() ) . '" target="__blank">Click here to see the checkout.</a>', 'woofunnels-aero-checkout' ),
			'pd'          => $pd,

		) );
	}

	public function maybe_remove_script_customizer() {
		global $wp_scripts, $wp_styles;
		$accepted_scripts = [
			0  => 'heartbeat',
			1  => 'customize-controls',
			2  => 'wfacpkirki_field_dependencies',
			3  => 'customize-widgets',
			4  => 'storefront-plugin-install',
			7  => 'jquery-ui-button',
			8  => 'customize-views',
			9  => 'media-editor',
			10 => 'media-audiovideo',
			11 => 'mce-view',
			12 => 'image-edit',
			13 => 'code-editor',
			14 => 'csslint',
			15 => 'wp-color-picker',
			16 => 'wp-color-picker-alpha',
			17 => 'selectWoo',
			18 => 'wfacpkirki-script',
			19 => 'wfacp-control-responsive-js',
			20 => 'updates',
			21 => 'wfacpkirki_panel_and_section_icons',
			22 => 'wfacpkirki-custom-sections',
			23 => 'wfacp_customizer_common',
			24 => 'acf-input',
			25 => 'code-editor',
		];

		$accepted_styles = [
			0  => 'customize-controls',
			1  => 'customize-widgets',
			2  => 'storefront-plugin-install',
			3  => 'woocommerce_admin_menu_styles',
			4  => 'wfacp-admin-font',
			7  => 'media-views',
			8  => 'imgareaselect',
			9  => 'code-editor',
			10 => 'wp-color-picker',
			11 => 'selectWoo',
			12 => 'wfacpkirki-selectWoo',
			13 => 'wfacpkirki-styles',
			14 => 'wfacp-control-responsive-css',
			15 => 'wfacpkirki-custom-sections',
			16 => 'code-editor',
			17 => 'editor-buttons',
		];

		$wp_scripts->queue = $accepted_scripts;
		$wp_styles->queue  = $accepted_styles;
	}

	public function print_customizer_styles() {
		echo '<style>#customize-theme-controls li#accordion-panel-nav_menus,#customize-theme-controls li#accordion-panel-widgets,#customize-theme-controls li#accordion-section-astra-pro,#customize-controls .customize-info .customize-help-toggle,.ast-control-tooltip {display: none !important;}</style>';
	}

	public function add_sections( $wp_customize ) {
		if ( $this->template_ins instanceof WFACP_Pre_Built ) {
			$this->template_ins->get_section( $wp_customize );
		}
	}


	/**
	 * @return WFACP_Template_Common
	 */
	public function get_template_instance() {
		return $this->template_ins;
	}

	public function add_loader_to_customizer() {
		?>
        <div class="wfacpkirki-customizer-loading-wrapper wfacp_customizer_loader">
            <span class="wfacpkirki-customizer-loading"></span>
        </div>
		<?php
	}

	public function dequeue_unnecessary_customizer_scripts() {

		if ( isset( $_REQUEST['wfacp_customize'] ) && $_REQUEST['wfacp_customize'] == 'loaded' && isset( $_REQUEST['wfacp_id'] ) && $_REQUEST['wfacp_id'] > 0 ) {

			/**
			 * wp-titan framework add these color pickers, that breaks our customizer page
			 */

			wp_deregister_script( 'wp-color-picker-alpha' );
			wp_dequeue_script( 'wp-color-picker-alpha' );

		}

	}

	public function may_setup_data( $instance ) {
		if ( $instance instanceof WFACP_Template_Common ) {
			$this->template_ins = $instance;
			if ( $this->is_customizer_template( $instance->get_template_type() ) ) {
				//Pre Built
				$this->template_ins->get_customizer_data();
			}
		}
	}

	public function wfacp_wfacpkirki_configuration( $path ) {
		if ( true == $this->is_customizer_page ) {
			return array(
				'url_path' => WFACP_PLUGIN_URL . '/admin/includes/wfacpkirki/',
			);
		}

		return $path;
	}


	public function setup_page_for_wfacpkirki() {


		$this->customizer_key_prefix = WFACP_SLUG . '_c_' . WFACP_Common::get_id();

		/** wfacpkirki */
		if ( class_exists( 'wfacpkirki' ) ) {
			wfacpkirki::add_config( WFACP_SLUG, array(
				'option_type' => 'option',
				'option_name' => $this->customizer_key_prefix,
			) );
		}
	}


	public function wfacp_wfacpkirki_fields() {
		$temp_ins = $this->template_ins;
		/** if ! customizer */
		if ( apply_filters( 'wfacp_setup_field_data', false ) ) {
			if ( ! WFACP_Common::is_customizer() ) {
				return;
			}
		}

		if ( ! $temp_ins instanceof WFACP_Template_Common || ! is_array( $temp_ins->customizer_data() ) || 0 == count( $temp_ins->customizer_data() ) ) {

			return;
		}
		foreach ( $temp_ins->customizer_data() as $panel_single ) {
			if ( count( $panel_single ) == 0 ) {
				continue;
			}
			/** Panel */
			foreach ( $panel_single as $panel_key => $panel_arr ) {
				/** Section */

				if ( ! is_array( $panel_arr['sections'] ) || count( $panel_arr['sections'] ) == 0 ) {
					continue;
				}


				foreach ( $panel_arr['sections'] as $section_key => $section_arr ) {
					$section_key_final = $panel_key . '_' . $section_key;
					/** Fields */
					if ( ! is_array( $section_arr['fields'] ) || count( $section_arr['fields'] ) == 0 ) {
						continue;
					}

					foreach ( $section_arr['fields'] as $field_key => $field_data ) {
						$field_key_final = $section_key_final . '_' . $field_key;

						$field_data = array_merge( $field_data, array(
							'settings' => $field_key_final,
							'section'  => $section_key_final,
						) );

						/** unset wfacp_partial key if present as not required for wfacpkirki */
						if ( isset( $field_data['wfacp_partial'] ) ) {
							unset( $field_data['wfacp_partial'] );
						}

						wfacpkirki::add_field( WFACP_SLUG, $field_data );

						/** Setting fields: type and element class for live preview */
						if ( isset( $field_data['wfacp_transport'] ) && is_array( $field_data['wfacp_transport'] ) ) {
							$field_key_final = $this->customizer_key_prefix . '[' . $field_key_final . ']';

							$temp_ins->customizer_fields[ $field_key_final ] = $field_data['wfacp_transport'];
						}
					}
				}
			}
		}
	}


	/**
	 * to avoid unserialize of the current class
	 */
	public function __wakeup() {
		throw new ErrorException( 'WFACP_Core can`t converted to string' );
	}

	/**
	 * to avoid serialize of the current class
	 */
	public function __sleep() {
		throw new ErrorException( 'WFACP_Core can`t converted to string' );
	}

	/**
	 * To avoid cloning of current template class
	 */
	protected function __clone() {

	}

}

if ( class_exists( 'WFACP_Core' ) && ! WFACP_Common::is_disabled() ) {
	WFACP_Core::register( 'customizer', 'WFACP_Customizer' );
}
