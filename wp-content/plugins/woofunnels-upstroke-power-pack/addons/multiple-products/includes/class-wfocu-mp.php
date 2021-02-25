<?php
defined( 'ABSPATH' ) || exit; //Exit if accessed directly

/**
 * Class WFOCU_MultiProductCore
 */
class WFOCU_MultiProductCore {


	private static $_instance = null;

	/**
	 * WFOCU_MultiProductCore constructor.
	 */
	public function __construct() {
		add_filter( 'wfocu_templates_group_customizer', function ( $templates ) {
			return array_merge( $templates, [ 'mp-grid', 'mp-list' ] );
		} );
		$this->register_templates();

	}

	public function register_templates() {
		$template = array(
			'path'        => WFOCU_MP_TEMPLATE_DIR . '/mp-grid/template.php',
			'name'        => __( 'Multi Product Grid', 'woofunnels-upstroke-power-pack' ),
			'thumbnail'   => 'https://woofunnels.s3.amazonaws.com/templates/upsell/multi-product-grid-three-column.jpg',
			'preview_url' => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13878&type=upsell',
			'is_multiple' => true,
		);
		WFOCU_Core()->template_loader->register_template( 'mp-grid', $template );
		$template = array(
			'path'        => WFOCU_MP_TEMPLATE_DIR . '/mp-list/template.php',
			'name'        => __( 'Multi Product List', 'woofunnels-upstroke-power-pack' ),
			'preview_url' => 'https://templates.buildwoofunnels.com/template-preview/?bwf_id=13879&type=upsell',
			'thumbnail'   => 'https://woofunnels.s3.amazonaws.com/templates/upsell/multi-product-list.jpg',
			'is_multiple' => true,
		);

		WFOCU_Core()->template_loader->register_template( 'mp-list', $template );
	}

	/**
	 * Creating instance
	 *
	 * @return WFOCU_MultiProductCore|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}
