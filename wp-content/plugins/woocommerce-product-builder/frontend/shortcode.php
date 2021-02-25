<?php

class VI_WPRODUCTBUILDER_FrontEnd_Shortcode {
	public $settings;
	public static $woopb_id;

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_shortcode( 'woocommerce_product_builder', array( $this, 'shortcode' ) );
	}

	public function init() {
		$this->settings = new VI_WPRODUCTBUILDER_Data();
	}

	public function shortcode( $atts ) {
		if ( is_admin() && ! is_ajax() ) {
			return;
		}
		$check = true;
		extract( shortcode_atts( array( 'id' => '', ), $atts ) );

		$id = (int) $id;

		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'woo_product_builder' ) {
			return;
		}

		if ( ! self::$woopb_id && $check ) {
			self::$woopb_id = $id;
		} else {
			return;
		}

		$this->settings->enqueue_scripts();
		ob_start();
		wc_get_template( 'single-product-builder.php', array( 'id' => $id ), '', VI_WPRODUCTBUILDER_SHORTCODE_TEMPS );

		return ob_get_clean();
	}
}
