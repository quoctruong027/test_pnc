<?php

class WFACP_Elementor {
	private static $ins = null;

	private $is_elementor = false;
	private static $front_locals = [];
	private $template_file = '';
	private $widget_dir = '';
	private $wfacp_id = 0;

	private function __construct() {

		$this->widget_dir    = WFACP_Core()->dir( 'builder/elementor/widgets' );
		$this->template_file = WFACP_Core()->dir( 'builder/elementor/template/template.php' );
		$this->register();
		add_action( 'wfacp_template_removed', [ $this, 'delete_elementor_data' ] );
		add_action( 'wfacp_duplicate_pages', [ $this, 'duplicate_template' ], 10, 3 );
		add_action( 'wfacp_update_page_design', [ $this, 'update_page_design' ], 10, 2 );
		add_action( 'wfacp_is_theme_builder', [ $this, 'remove_photoswipe' ], 10 );
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_widget_categories' ] );
	}


	public static function get_instance() {
		if ( is_null( self::$ins ) ) {
			self::$ins = new self();
		}

		return self::$ins;

	}


	public static function set_locals( $name, $id ) {
		self::$front_locals[ $name ] = $id;
	}

	public static function get_locals() {
		return self::$front_locals;
	}

	private function widgets() {
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'initialize_widgets' ] );

	}

	public function initialize_widgets() {

		include_once __DIR__ . '/class-abstract-wfacp-fields.php';
		include_once __DIR__ . '/class-wfacp-html-block-elementor.php';
		foreach ( glob( $this->widget_dir . '/class-elementor-*.php' ) as $_field_filename ) {
			require_once( $_field_filename );
		}
	}

	public function add_widget_categories( $elements_manager ) {
		$design = WFACP_Common::get_page_design( WFACP_Common::get_id() );
		if ( 'elementor' == $design['selected_type'] && class_exists( '\Elementor\Plugin' ) ) {
			$elements_manager->add_category( 'woofunnels-aero-checkout', [
				'title' => __( 'Aero Checkout', 'woofunnels-aero-checkout' ),
				'icon'  => 'fa fa-plug',
			] );
		}

	}


	private function register() {
		add_filter( 'wfacp_is_theme_builder', [ $this, 'is_elementor_page' ] );
		add_filter( 'wfacp_post', [ $this, 'check_current_page_is_aero_page' ] );
		add_action( 'wfacp_checkout_page_found', [ $this, 'initialize_elementor_widgets' ] );

		add_action( 'wfacp_register_template_types', [ $this, 'register_template_type' ], 11 );
		add_filter( 'wfacp_register_templates', [ $this, 'register_templates' ] );


		add_action( 'wfacp_template_load', [ $this, 'load_elementor_abs_class' ], 10, 2 );
		add_filter( 'wfacp_template_edit_link', [ $this, 'add_template_edit_link' ], 10, 2 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 101 );


	}

	public function is_elementor_page( $status ) {
		if ( isset( $_REQUEST['elementor-preview'] ) || ( isset( $_REQUEST['action'] ) && ( 'elementor' == $_REQUEST['action'] || 'elementor_ajax' == $_REQUEST['action'] ) ) ) {
			$this->is_elementor = true;
			$status             = true;

		}
		if ( isset( $_REQUEST['preview_id'] ) && isset( $_REQUEST['preview_nonce'] ) ) {
			$this->is_elementor = true;
			$status             = true;
		}

		return $status;
	}

	public function check_current_page_is_aero_page( $post ) {
		if ( WFACP_Common::is_theme_builder() && true == $this->is_elementor ) {

			if ( isset( $_REQUEST['post'] ) ) {
				$temp_id = absint( $_REQUEST['post'] );
			} elseif ( isset( $_REQUEST['editor_post_id'] ) ) {
				$temp_id = absint( $_REQUEST['editor_post_id'] );
			} else {
				$temp_id = 0;
			}

			$post = get_post( $temp_id );


		}

		return $post;
	}

	public function initialize_elementor_widgets( $post_id ) {
		$design = WFACP_Common::get_page_design( $post_id );
		if ( 'elementor' == $design['selected_type'] && class_exists( '\Elementor\Plugin' ) ) {
			$this->wfacp_id = $post_id;
			global $post;
			$post = get_post( $this->wfacp_id );
			$this->widgets();
			add_filter( 'the_content', [ $this, 'change_global_post_var_to_our_page_post' ], 5 );
			add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'custom_admin_style' ] );
			add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'register_custom_font' ] );
		}
	}

	public function change_global_post_var_to_our_page_post( $content ) {
		global $post;
		$post = get_post( $this->wfacp_id );

		return $content;

	}


	public function enqueue_scripts() {

		if ( isset( $_REQUEST['elementor-preview'] ) ) {
			wp_enqueue_script( 'wfacp_elementor_edit', WFACP_Core()->url( '/builder/elementor/js/elementor-preview-iframe.js' ), [ 'wfacp_checkout_js' ], WFACP_VERSION_DEV, true );
		}
	}


	/**
	 * @param $loader WFACP_Template_loader
	 */
	public function register_template_type( $loader ) {
		$template = [
			'slug'    => 'elementor',
			'title'   => __( 'Elementor', 'woofunnels-aero-checkout' ),
			'filters' => WFACP_Common::get_template_filter()
		];

		$loader->register_template_type( $template );
	}

	public function register_templates( $designs ) {
		$designs['elementor'] = [
			'elementor_1' => [
				'path'               => $this->template_file,
				'name'               => __( 'Build from Scratch', 'woofunnels-aero-checkout' ),
				'import'             => 'yes',
				'show_import_popup'  => 'no',
				'build_from_scratch' => 'yes',
				'import_button_text' => __( 'Apply', 'woofunnles-aero-checkout' ),
				'slug'               => 'elementor_1',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'support_embed_form' => 'embed_forms_1',
				'no_steps'           => '1'
			],
			'elementor_2' => [
				'path'               => $this->template_file,
				'name'               => __( 'Build from Scratch', 'woofunnels-aero-checkout' ),
				'import'             => 'yes',
				'show_import_popup'  => 'no',
				'build_from_scratch' => 'yes',
				'import_button_text' => __( 'Apply', 'woofunnles-aero-checkout' ),
				'slug'               => 'elementor_2',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'support_embed_form' => 'embed_forms_1',
				'no_steps'           => '2'
			],
			'elementor_3' => [
				'path'               => $this->template_file,
				'name'               => __( 'Build from Scratch', 'woofunnels-aero-checkout' ),
				'import'             => 'yes',
				'show_import_popup'  => 'no',
				'build_from_scratch' => 'yes',
				'import_button_text' => __( 'Apply', 'woofunnles-aero-checkout' ),
				'slug'               => 'elementor_3',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'support_embed_form' => 'embed_forms_1',
				'no_steps'           => '3'
			],

			'elementor-minimalist'        => [
				'path'               => $this->template_file,
				'name'               => __( 'Minimalist', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-minimalist.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-minimalist',
				'pro'                => 'yes',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13306&type=checkout',
				'no_steps'           => '1'
			],
			'elementor-minimalist-step-2' => [
				'path'               => $this->template_file,
				'name'               => __( 'Minimalist', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-minimalist-2.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-minimalist-step-2',
				'pro'                => 'yes',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13416&type=checkout',
				'no_steps'           => '2'
			],
			'elementor-minimalist-step-3' => [
				'path'               => $this->template_file,
				'name'               => __( 'Minimalist', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-minimalist-3.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-minimalist-step-3',
				'pro'                => 'yes',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13419&type=checkout',
				'no_steps'           => '3'
			],

			'elementor-shopcheckout'        => [
				'path'               => $this->template_file,
				'name'               => __( 'ShopCheckout', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-shopcheckout.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-shopcheckout',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13776&type=checkout',
				'no_steps'           => '1'
			],
			'elementor-shopcheckout-step-2' => [
				'path'               => $this->template_file,
				'name'               => __( 'ShopCheckout', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-shopcheckout-2.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-shopcheckout-step-2',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13777&type=checkout',
				'no_steps'           => '2'
			],
			'elementor-shopcheckout-step-3' => [
				'path'               => $this->template_file,
				'name'               => __( 'ShopCheckout', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-shopcheckout-3.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-shopcheckout-step-3',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13778&type=checkout',
				'no_steps'           => '3'
			],

			'elementor-persuader'        => [
				'path'               => $this->template_file,
				'name'               => __( 'Persuader', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-persuader.jpg',
				'import'             => 'yes',
				'show_import_popup'  => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'slug'               => 'elementor-persuader',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'support_embed_form' => 'embed_forms_1',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=12573&type=checkout',
				'no_steps'           => '1'
			],
			'elementor-persuader-step-2' => [
				'path'               => $this->template_file,
				'name'               => __( 'Persuader', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-persuader-2.jpg',
				'import'             => 'yes',
				'show_import_popup'  => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'slug'               => 'elementor-persuader-step-2',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'support_embed_form' => 'embed_forms_1',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13318&type=checkout',
				'no_steps'           => '2'
			],
			'elementor-persuader-step-3' => [
				'path'               => $this->template_file,
				'name'               => __( 'Persuader', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-persuader-3.jpg',
				'import'             => 'yes',
				'show_import_popup'  => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'slug'               => 'elementor-persuader-step-3',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'support_embed_form' => 'embed_forms_1',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13327&type=checkout',
				'no_steps'           => '3'
			],

			'elementor-closer'        => [
				'path'               => $this->template_file,
				'name'               => __( 'Closer', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-closer.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-closer',
				'pro'                => 'yes',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13310&type=checkout',
				'no_steps'           => '1'
			],
			'elementor-closer-step-2' => [
				'path'               => $this->template_file,
				'name'               => __( 'Closer', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-closer-2.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-closer-step-2',
				'pro'                => 'yes',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13426&type=checkout',
				'no_steps'           => '2'
			],
			'elementor-closer-step-3' => [
				'path'               => $this->template_file,
				'name'               => __( 'Closer', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-closer-3.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-closer-step-3',
				'pro'                => 'yes',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13443&type=checkout',
				'no_steps'           => '3'
			],

			'elementor-presenter'        => [
				'path'               => $this->template_file,
				'name'               => __( 'Presenter', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-presenter.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-presenter',
				'pro'                => 'yes',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=12600&type=checkout',
				'no_steps'           => '1'
			],
			'elementor-presenter-step-2' => [
				'path'               => $this->template_file,
				'name'               => __( 'Presenter', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-presenter-step-2.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-presenter-step-2',
				'pro'                => 'yes',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13399&type=checkout',
				'no_steps'           => '2'
			],
			'elementor-presenter-step-3' => [
				'path'               => $this->template_file,
				'name'               => __( 'Presenter', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-presenter-step-3.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-presenter-step-3',
				'pro'                => 'yes',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13407&type=checkout',
				'no_steps'           => '3'
			],

			'elementor-razor'        => [
				'path'               => $this->template_file,
				'name'               => __( 'Razor', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-razor.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-razor',
				'pro'                => 'yes',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=12611&type=checkout',
				'no_steps'           => '1'
			],
			'elementor-razor-step-2' => [
				'path'               => $this->template_file,
				'name'               => __( 'Razor', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-razor-step-2.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-razor-step-2',
				'pro'                => 'yes',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13453&type=checkout',
				'no_steps'           => '2'
			],
			'elementor-razor-step-3' => [
				'path'               => $this->template_file,
				'name'               => __( 'Razor', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-razor-3.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-razor-step-3',
				'pro'                => 'yes',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13467&type=checkout',
				'no_steps'           => '3'
			],

			'elementor-magnetic'        => [
				'path'               => $this->template_file,
				'name'               => __( 'Magnetic', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-magnetic.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-magnetic',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=12581&type=checkout',
				'no_steps'           => '1'
			],
			'elementor-magnetic-step-2' => [
				'path'               => $this->template_file,
				'name'               => __( 'Magnetic', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-magnetic-2.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-magnetic-step-2',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13335&type=checkout',
				'no_steps'           => '2'
			],
			'elementor-magnetic-step-3' => [
				'path'               => $this->template_file,
				'name'               => __( 'Magnetic', 'woofunnels-aero-checkout' ),
				'thumbnail'          => 'https://woofunnels.s3.amazonaws.com/templates/checkout/elementor/template-magnetic-3.jpg',
				'description'        => '',
				'import'             => 'yes',
				'import_button_text' => __( 'import', 'woofunnels-aero-checkout' ),
				'show_import_popup'  => 'yes',
				'slug'               => 'elementor-magnetic-step-3',
				'group'              => 'elementor',
				'builder'            => 'elementor',
				'preview_url'        => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13366&type=checkout',
				'no_steps'           => '3'
			],
		];

		return $designs;

	}


	public function load_elementor_abs_class( $wfacp_id, $template = [] ) {
		if ( empty( $template ) ) {
			return;
		}
		if ( 'elementor' == $template['selected_type'] ) {
			include_once WFACP_Core()->dir( 'builder/elementor/class-wfacp-elementor-template.php' );
		}
	}

	public function add_template_edit_link( $links, $admin ) {
		$url                = add_query_arg( [ 'post' => $admin->wfacp_id, 'action' => 'elementor' ], admin_url( 'post.php' ) );
		$links['elementor'] = [ 'url' => $url, 'button_text' => __( 'Edit', 'elementor' ) ];

		return $links;
	}

	public function custom_admin_style() {


		echo '<style>';
		include __DIR__ . '/css/custom_admin_style.css';
		echo '</style>';

	}

	public function register_custom_font() {

		wp_enqueue_style( 'wfacp-icons', WFACP_PLUGIN_URL . '/admin/assets/css/wfacp-font.css', null, WFACP_VERSION );

	}

	/**
	 * Delete Elementor saved data from postmeta of aerocheckout ID
	 */
	public function delete_elementor_data( $post_id ) {

		wp_update_post( [ 'ID' => $post_id, 'post_content' => '' ] );
		delete_post_meta( $post_id, '_elementor_version' );
		delete_post_meta( $post_id, '_elementor_template_type' );
		delete_post_meta( $post_id, '_elementor_edit_mode' );
		delete_post_meta( $post_id, '_elementor_data' );
		delete_post_meta( $post_id, '_elementor_controls_usage' );
		delete_post_meta( $post_id, '_elementor_css' );
	}

	public function update_page_design( $page_id, $data ) {


		if ( ! isset( $page_id ) ) {
			return;
		}

		if ( ! is_array( $data ) || count( $data ) == 0 ) {
			return;
		}
		if ( ! isset( $data['selected_type'] ) || $data['selected_type'] != 'elementor' ) {
			return;
		}


		//update_post_meta( $page_id, '_wp_page_template', 'wfacp-canvas.php' );


	}

	public function duplicate_template( $new_post_id, $post_id, $data ) {
		if ( 'elementor' == $data['_wfacp_selected_design']['selected_type'] ) {

			$contents = get_post_meta( $post_id, '_elementor_data', true );
			$data     = [
				'_elementor_version'       => get_post_meta( $post_id, '_elementor_version', true ),
				'_elementor_template_type' => get_post_meta( $post_id, '_elementor_template_type', true ),
				'_elementor_edit_mode'     => get_post_meta( $post_id, '_elementor_edit_mode', true ),

			];


			foreach ( $data as $meta_key => $meta_value ) {
				update_post_meta( $new_post_id, $meta_key, $meta_value );
			}

			/**
			 * @var $instance WFACP_Elementor_Importer
			 */
			$instance = new WFACP_Elementor_Importer();
			if ( ! is_null( $instance ) ) {
				if ( is_array( $contents ) ) {
					$contents = json_encode( $contents );

				}
				$instance->delete_page_meta = false;
				$instance->import_aero_template( $new_post_id, $contents );
			}
			update_post_meta( $new_post_id, '_wp_page_template', get_post_meta( $post_id, '_wp_page_template', true ) );

		}

	}

	public function remove_photoswipe( $status ) {


		if ( true === $status ) {
			add_filter( 'wfacp_wc_photoswipe_enable', [ $this, 'disable_photoswipe_js' ] );
		}

		return $status;
	}

	public function disable_photoswipe_js() {
		return false;
	}
}

WFACP_Elementor::get_instance();