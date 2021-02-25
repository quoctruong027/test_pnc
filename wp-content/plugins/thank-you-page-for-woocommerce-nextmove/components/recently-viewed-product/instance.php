<?php
defined( 'ABSPATH' ) || exit;

class XLWCTY_Recently_Viewed_products extends XLWCTY_Component {

	private static $instance = null;
	public $is_disable = true;
	public $viewpath = '';
	public $recently_product = array();
	public $grid_type = '2c';

	public function __construct( $order = false ) {

		parent::__construct();
		$this->viewpath = __DIR__ . '/views/view.php';
		add_action( 'xlwcty_after_component_data_setup_xlwcty_recently_viewed_product', array( $this, 'setup_style' ) );
		add_action( 'xlwcty_after_components_loaded', array( $this, 'setup_fields' ) );
	}

	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function setup_fields() {
		$this->fields = array(
			'heading'            => $this->get_slug() . '_heading',
			'heading_font_size'  => $this->get_slug() . '_heading_font_size',
			'heading_alignment'  => $this->get_slug() . '_heading_alignment',
			'desc'               => $this->get_slug() . '_desc',
			'desc_alignment'     => $this->get_slug() . '_desc_alignment',
			'layout'             => $this->get_slug() . '_layout',
			'grid_type'          => $this->get_slug() . '_grid_type',
			'display_count'      => $this->get_slug() . '_display_count',
			'display_rating'     => $this->get_slug() . '_display_rating',
			'border_style'       => $this->get_slug() . '_border_style',
			'border_width'       => $this->get_slug() . '_border_width',
			'border_color'       => $this->get_slug() . '_border_color',
			'component_bg_color' => $this->get_slug() . '_component_bg',
		);
	}

	public function prepare_out_put_data() {

		parent::prepare_out_put_data();

		if ( $this->data->layout == 'list' ) {
			$this->data->grid_type = $this->data->layout;
		}
		if ( $this->data->layout == 'grid_native' ) {
			$this->data->grid_type = 'native';
		}
	}

	public function get_view_data( $key = 'order' ) {

		if ( empty( $_COOKIE['xlwcty_recently_viewed_product'] ) ) {
			$viewed_products = array();
		} else {
			$viewed_products = (array) explode( '|', $_COOKIE['xlwcty_recently_viewed_product'] );
		}

		$this->recently_product = $viewed_products;

		return parent::get_view_data();
	}

	public function setup_style( $slug ) {
		if ( $this->is_enable() ) {

			if ( $this->data->layout === 'grid_native' && $this->data->display_rating == 'no' ) {
				$style['.xlwcty_product.recently_product .star-rating']['display'] = 'none';

			}
			if ( $this->data->heading_font_size != '' ) {
				$style['.xlwcty_product.recently_product .xlwcty_title']['font-size']   = $this->data->heading_font_size . 'px';
				$style['.xlwcty_product.recently_product .xlwcty_title']['line-height'] = ( $this->data->heading_font_size + 4 ) . 'px';
			}
			if ( $this->data->heading_alignment != '' ) {
				$style['.xlwcty_product.recently_product .xlwcty_title']['text-align'] = $this->data->heading_alignment;
			}

			if ( $this->data->border_style != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_product.recently_product']['border-style'] = $this->data->border_style;
			}
			if ( (int) $this->data->border_width >= 0 ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_product.recently_product']['border-width'] = (int) $this->data->border_width . 'px';
			}
			if ( $this->data->border_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_product.recently_product']['border-color'] = $this->data->border_color;
			}
			if ( $this->data->component_bg_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_product.recently_product']['background-color'] = $this->data->component_bg_color;
			}
			parent::push_css( $slug, $style );
		}
	}

}

return XLWCTY_Recently_Viewed_products::get_instance();


