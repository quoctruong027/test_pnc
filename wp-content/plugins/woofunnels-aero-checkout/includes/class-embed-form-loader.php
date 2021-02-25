<?php

/**
 * Created by PhpStorm.
 * User: sandeep
 * Date: 26/7/18
 * Time: 5:10 PM
 */
final class WFACP_Embed_Form_loader {
	private static $ins = null;
	private $has_shortcode = false;
	private $current_template = false;
	private $is_divi_builder_page = false;
	private $wfacp_id = 0;
	private $shortcode_content = '';
	private $shortcode_executed = false;
	public static $pop_up_trigger = false;
	public $current_page_id = 0;
	private $rest_api_run = false;
	private $page_is_editable = false;

	protected function __construct() {
		add_action( 'rest_jsonp_enabled', [ $this, 'enable_rest_jsonp' ] );
		add_action( 'wfacp_none_checkout_pages', [ $this, 'detect_shortcode' ], 1 );
		add_action( 'wfacp_none_checkout_pages', [ $this, 'active_woo_compatibility' ] );
		add_shortcode( 'wfacp_forms', [ $this, 'shortcode' ] );
		add_filter( 'wfacp_page_located', [ $this, 'detect_page_located' ], 10, 2 );
		add_filter( 'wfacp_do_not_check_for_global_checkout', [ $this, 'do_not_checkout_for_global_checkout' ], 10, 2 );
		add_filter( 'wfacp_do_not_execute_shortcode', [ $this, 'do_not_execute_shortcode' ] );
	}

	public function is_divi_builder_page() {
		return $this->is_divi_builder_page;
	}

	/**
	 * @return WFACP_Embed_Form_loader;
	 */
	public static function get_instance() {
		if ( is_null( self::$ins ) ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function check_shortcode_exist( $post_data ) {
		$status = false;
		if ( false !== strpos( $post_data, '[wfacp_forms' ) || false !== strpos( $post_data, '[WFACP_FORMS' ) ) {
			$status = true;
		}

		return apply_filters( 'wfacp_shortcode_exist', $status );
	}

	public function detect_shortcode() {
		if ( is_admin() || true == $this->rest_api_run ) {
			return '';
		}
		if ( true == apply_filters( 'wfacp_do_not_execute_shortcode', false, $this ) ) {
			return '';
		}
		global $post;
		$is_true = WFACP_Common::is_customizer();
		if ( true == $is_true ) {
			return;
		}

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		$shortcode_exist       = false;
		$shortcode_content     = '';
		$this->current_page_id = $post->ID;
		if ( $this->check_shortcode_exist( $post->post_content ) ) {
			$shortcode_content = apply_filters( 'wfacp_detect_shortcode', $post->post_content );
			$shortcode_exist   = true;
		}

		if ( false === $shortcode_exist && $this->check_shortcode_exist( $post->post_excerpt ) ) {
			$shortcode_content = apply_filters( 'wfacp_detect_shortcode', $post->post_excerpt );
			$shortcode_exist   = true;
		}
		do_action( 'wfacp_run_shortcode_before', $shortcode_exist );

		/** Return if no shortcode exist */
		if ( false === $shortcode_exist ) {
			return;
		}

		/** Shortcode exist on a page */
		if ( is_order_received_page() || is_checkout_pay_page() ) {
			$post->post_content = '[woocommerce_checkout]';

			return;
		}

		if ( ! is_null( $post ) ) {
			$this->has_shortcode = true;
			remove_action( 'wp', [ WFACP_Core()->template_loader, 'maybe_setup_page' ], 7 );
			if ( is_cart() || is_shop() ) {
				return;
			}

			ob_start();
			do_shortcode( $shortcode_content );
			$this->shortcode_content = ob_get_clean();
		}
	}


	public function shortcode( $attributes ) {

		if ( is_admin() ) {
			return;

		}
		if ( true == $this->rest_api_run ) {
			return '';
		}
		if ( is_admin() && isset( $_GET['post'] ) && $_GET['post'] > 0 && isset( $_REQUEST['action'] ) ) {

			return '';
		}
		if ( true == apply_filters( 'wfacp_do_not_execute_shortcode', false, $this ) ) {
			return '';
		}

		$template = wfacp_template();

		if ( $template instanceof WFACP_Template_Common ) {
			$data = $template->get_selected_register_template();
			// checking template support Shortcode execution

			if ( ( isset( $data['support_embed_form'] ) && 'embed_forms' !== $data['template_type'] ) || apply_filters( 'wfacp_allow_printing_shortcode_direct', false, $template ) ) {
				ob_start();

				$this->get_form_shortcode_html( $template );

				return ob_get_clean();
			}
		}

		$attributes = shortcode_atts( [
			'id'           => 0,
			'lightbox'     => 'no',
			'width'        => 500,
			'mode'         => 'all',
			'product_ids'  => '',
			'product_qtys' => '',
		], $attributes );

		if ( empty( $attributes['id'] ) || 0 == $attributes['id'] ) {
			global $post;
			if ( ! is_null( $post ) && $post->post_type == WFACP_Common::get_post_type_slug() ) {
				$attributes['id'] = $post->ID;
			} else {
				return '';
			}
		}


		$wfacp_id = $attributes['id'];
		$lightbox = $attributes['lightbox'];

		if ( '' !== $attributes['product_ids'] ) {
			$aero_add_to_checkout_parameter          = WFACP_Core()->public->aero_add_to_checkout_parameter();
			$_GET[ $aero_add_to_checkout_parameter ] = trim( $attributes['product_ids'] );
		}
		if ( '' !== $attributes['product_qtys'] ) {
			$aero_add_to_checkout_product_quantity_parameter          = WFACP_Core()->public->aero_add_to_checkout_product_quantity_parameter();
			$_GET[ $aero_add_to_checkout_product_quantity_parameter ] = trim( $attributes['product_qtys'] );
		}

		$data = WFACP_Common::get_page_design( $wfacp_id );

		if ( empty( $data ) || 'embed_forms' !== $data['selected_type'] ) {
			return '';
		}

		if ( 0 === $this->wfacp_id ) {

			$this->wfacp_id = absint( $wfacp_id );

			if ( 0 == $this->wfacp_id ) {
				return '';
			}

			$post = get_post( $this->wfacp_id );

			if ( is_null( $post ) ) {
				return '';
			}
			if ( ! is_super_admin() ) {
				if ( ( 'publish' !== $post->post_status || $post->post_type !== WFACP_Common::get_post_type_slug() ) ) {

					return '';
				}
			}

			// Normal checkout page (Woocommerce setting checkout page)
			if ( ( is_checkout() || ( $this->current_page_id == WFACP_Common::get_checkout_page_id() ) ) && false == $this->page_is_editable ) {

				remove_action( 'wfacp_after_checkout_page_found', [ WFACP_Core()->public, 'add_to_cart' ], 2 );
				do_action( 'wfacp_changed_default_woocommerce_page' );
			}

			add_filter( 'wfacp_skip_add_to_cart', [ $this, 'skip_add_to_cart' ] );

			$this->remove_hooks();

			add_filter( 'wfacp_enqueue_global_script', '__return_true' );
			add_filter( 'wfacp_cancel_url_arguments', [ $this, 'add_embed_page_id' ] );
			add_filter( 'woocommerce_is_checkout', '__return_true' );
			add_filter( 'wfacp_remove_woocommerce_style_dependency', '__return_true' );
			add_filter( 'wfacp_skip_form_printing', '__return_true', 10 );
			add_filter( 'body_class', [ $this, 'add_body_class' ] );

			if ( 'yes' == $lightbox ) {
				add_action( 'wp_enqueue_scripts', [ $this, 'remove_select2_wc' ], 100 );
				self::$pop_up_trigger = true;
			}
			WFACP_Common::set_id( $this->wfacp_id );
			$get_template_loader = WFACP_Core()->template_loader;
			$get_template_loader->load_template( $this->wfacp_id );
			$this->current_template = $get_template_loader->get_template_ins();

			if ( ! is_null( $this->current_template ) && $this->current_template instanceof WFACP_Template_Common ) {
				remove_filter( 'template_redirect', array( $get_template_loader, 'setup_preview' ), 99 );
				add_action( 'wfacp_after_payment_section', [ $this, 'create_hidden_input_for_saving_current_page_id' ] );
				$this->current_template->get_customizer_data();
				do_action( 'wfacp_after_checkout_page_found', $this->wfacp_id );
			}

			return '';
		} else {

			/** Don't execute shortcode if mode is mobile and a user not came from mobile */
			if ( 'mobile' == $attributes['mode'] && ! wp_is_mobile() ) {
				return '';
			}

			/** Don't execute shortcode if mode is desktop and a user came from mobile */
			if ( 'desktop' == $attributes['mode'] && wp_is_mobile() ) {
				return '';
			}

			if ( ! $this->current_template instanceof WFACP_Template_Common ) {
				return '';
			}

			add_filter( 'wfacp_skip_form_printing', '__return_false' );
			ob_start();

			$this->shortcode_executed = true;

			if ( 'yes' == $lightbox ) {
				$this->wrap_in_light_box();
			} else {

				include $this->current_template->get_template_url();
			}

			return ob_get_clean();

		}
	}

	/**
	 * @param $instance WFACP_Template_Common
	 */
	protected function get_form_shortcode_html( $instance ) {
		include WFACP_Core()->dir( 'builder/customizer/templates/embed_forms_1/views/view.php' );
	}


	public function create_hidden_input_for_saving_current_page_id() {
		echo '<input type="hidden" name="wfacp_embed_form_page_id" id="wfacp_embed_form_page_id" value="' . $this->current_page_id . '">';
	}

	public function add_embed_page_id( $params ) {
		$params['wfacp_embed_page_id'] = $this->current_page_id;

		return $params;
	}

	private function wrap_in_light_box() {

		?>
        <div class="wfacp_pop_up_wrap" id="wfacp_pop_up_wrap">
            <div class="wfacp_modal_overlay wfacp_display_none"></div>
            <div class="wfacp_modal_outerwrap wfacp_display_none">
                <div class="wfacp_modal_innerwrap">
                    <div class="wfacp_modal_content" id="wfacp_modal_content">
                        <div class="wfacp_pop_sec">
                            <div class="wfacp_modal_container">
								<?php include $this->current_template->get_template_url(); ?>
                            </div>
                        </div><!-- product-container -->
                        <button title="Close (Esc)" type="button" class="wfacp_modal_close">x</button>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	public function enable_rest_jsonp( $status ) {
		$this->rest_api_run = true;

		return $status;
	}

	public function active_woo_compatibility() {

		if ( class_exists( 'WC_Active_Woo' ) ) {

			global $activewoo;
			remove_action( 'woocommerce_before_checkout_form', array( $activewoo->recover_cart, 'print_subscribe_form' ) );
			add_action( 'woocommerce_before_checkout_form', function () {
				wp_enqueue_script( 'aw_rc_cart_js' );
				wp_enqueue_script( 'wfacp_active_woo', WFACP_PLUGIN_URL . '/compatibilities/js/activewoo.min.js', [ 'wfacp_checkout_js' ], WFACP_VERSION, true );
			} );
		}
	}


	public function remove_select2_wc() {
		wp_dequeue_style( 'select2' );
		wp_dequeue_script( 'select2' );

	}

	public function skip_add_to_cart( $status ) {
		global $post;
		if ( ! is_null( $post ) && $post->post_type == WFACP_Common::get_post_type_slug() ) {
			return $status;
		}

		if ( ! WC()->cart->is_empty() && 0 == count( WFACP_Common::get_page_product( $this->wfacp_id ) ) ) {
			return true;
		}

		return $status;
	}

	private function remove_hooks() {

		if ( class_exists( 'Astra_Woocommerce' ) ) {
			$astra = Astra_Woocommerce::get_instance();
			remove_filter( 'astra_get_sidebar', [ $astra, 'replace_store_sidebar' ] );
			remove_filter( 'astra_page_layout', [ $astra, 'store_sidebar_layout' ] );
			remove_filter( 'astra_get_content_layout', [ $astra, 'store_content_layout' ] );

		}
		if ( function_exists( 'flatsome_woocommerce_add_notice' ) ) {
			remove_action( 'flatsome_after_header', 'flatsome_woocommerce_add_notice', 100 );
		}
	}

	public function add_body_class( $classes ) {
		$classes[] = 'wfacpef_page';

		if ( ( is_array( $classes ) && count( $classes ) > 0 ) && in_array( 'et_divi_builder', $classes ) ) {
			$this->is_divi_builder_page = true;
		}

		return $classes;
	}

	/**
	 * @param $status Boolean
	 * @param $post
	 * @param $loader WFACP_Template_loader
	 */
	public function detect_page_located( $status, $post ) {
		if ( ! is_null( $post ) && $post->post_type == WFACP_Common::get_post_type_slug() ) {
			$design_data = WFACP_Common::get_page_design( $post->ID );
			if ( 'embed_forms' == $design_data['selected_type'] && $this->check_shortcode_exist( $post->post_content ) ) {
				$status = false;

				$this->page_is_editable = true;


			}
		}

		return $status;
	}

	public function do_not_checkout_for_global_checkout( $status, $post ) {
		if ( $this->page_is_editable ) {

			if ( ! WFACP_Common::is_global_checkout( $post->ID ) ) {
				remove_filter( 'wfacp_changed_default_woocommerce_page', [ WFACP_Core()->public, 'wfacp_changed_default_woocommerce_page' ] );
			} else {
				$this->page_is_editable = false;
			}
			$status = true;
		}

		return $status;

	}

	public function do_not_execute_shortcode( $status ) {

		if ( is_admin() && isset( $_REQUEST['action'] ) && 'elementor' == $_REQUEST['action'] ) {
			return true;
		}

		if ( isset( $_REQUEST['elementor-preview'] ) && $_REQUEST['elementor-preview'] > 0 ) {

			return true;
		}

		return $status;

	}

	public function page_is_editable() {
		return $this->page_is_editable;
	}

	/**
	 * To avoid cloning of current class
	 */
	protected function __clone() {
	}

	/**
	 * to avoid unserialize of the current class
	 */
	public function __wakeup() {
		throw new ErrorException( 'WFACPEF_Core can`t converted to string' );
	}


	/**
	 * to avoid serialize of the current class
	 */
	public function __sleep() {
		throw new ErrorException( 'WFACPEF_Core can`t converted to string' );
	}


}

if ( class_exists( 'WFACP_Core' ) && ! WFACP_Common::is_disabled() ) {
	WFACP_Core::register( 'embed_forms', 'WFACP_Embed_Form_loader' );
}
