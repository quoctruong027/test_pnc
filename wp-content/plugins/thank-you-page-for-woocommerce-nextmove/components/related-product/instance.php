<?php
defined( 'ABSPATH' ) || exit;

class XLWCTY_Related_Products extends XLWCTY_Component {

	private static $instance = null;
	public $viewpath = '';
	public $related_product = array();
	public $is_disable = true;
	public $grid_type = '2c';

	/** Using when product is added to cart to fetch the max count */
	public $related_pro_count = 9;

	public function __construct( $order = false ) {
		parent::__construct();
		add_action( 'woocommerce_add_to_cart', array( $this, 'woocommerce_add_to_cart' ), 10, 2 );
		$this->viewpath = __DIR__ . '/views/view.php';
		add_action( 'xlwcty_after_components_loaded', array( $this, 'setup_fields' ) );
		add_action( 'xlwcty_after_component_data_setup_xlwcty_related_product', array( $this, 'setup_style' ) );
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

	public function woocommerce_add_to_cart( $cart_item_key, $product_id ) {
		if ( $product_id > 0 ) {

			$xlwcty_related_product = WC()->session->get( 'xlwcty_related_product' );
			if ( ! is_array( $xlwcty_related_product ) ) {
				$xlwcty_related_product = array();
			}
			if ( ! XLWCTY_Core()->public->is_preview && ! $xlwcty_related_product ) {
				$xlwcty_related_product = array();
			}

			$number = ! empty( $this->related_pro_count ) ? absint( $this->related_pro_count ) : 4;
			$number = apply_filters( 'xlwcty_related_products_wc_count', $number );

			if ( version_compare( $this->wc_version(), '3.0', '<' ) ) {
				$product = wc_get_product( $product_id );

				$related = $product->get_related( $number );
			} else {
				$related = wc_get_related_products( $product_id, $number );
			}
			if ( ! empty( $related ) && is_array( $related ) > 0 ) {

				$xlwcty_related_product = array_merge( $xlwcty_related_product, $related );
				$xlwcty_related_product = array_unique( $xlwcty_related_product );
				WC()->session->set( 'xlwcty_related_product', $xlwcty_related_product );
			}
		}
	}

	public function setup_style( $slug ) {
		if ( $this->is_enable() ) {

			if ( $this->data->layout === 'grid_native' && $this->data->display_rating == 'no' ) {
				$style['.xlwcty_product.related_product .star-rating']['display'] = 'none';
			}
			if ( $this->data->heading_font_size != '' ) {
				$style['.xlwcty_product.related_product .xlwcty_title']['font-size']   = $this->data->heading_font_size . 'px';
				$style['.xlwcty_product.related_product .xlwcty_title']['line-height'] = ( $this->data->heading_font_size + 4 ) . 'px';
			}

			if ( $this->data->heading_alignment != '' ) {
				$style['.xlwcty_product.related_product .xlwcty_title']['text-align'] = $this->data->heading_alignment;
			}
			if ( $this->data->border_style != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_product.related_product']['border-style'] = $this->data->border_style;
			}
			if ( (int) $this->data->border_width >= 0 ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_product.related_product']['border-width'] = (int) $this->data->border_width . 'px';
			}
			if ( $this->data->border_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_product.related_product']['border-color'] = $this->data->border_color;
			}
			if ( $this->data->component_bg_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_product.related_product']['background-color'] = $this->data->component_bg_color;
			}
			parent::push_css( $slug, $style );
		}
	}

	public function get_view_data( $key = 'order' ) {
		$this->related_product = $this->get_related_product();

		return parent::get_view_data();
	}

	public function get_related_product() {
		$xlwcty_related_product = WC()->session->get( 'xlwcty_related_product' );
		if ( is_array( $xlwcty_related_product ) && count( $xlwcty_related_product ) > 0 ) {
			return $xlwcty_related_product;
		}

		//handling for the case where we do not have any data set in the session
		$order                  = XLWCTY_Core()->data->get_order();
		$xlwcty_related_product = array();
		if ( $order instanceof WC_Order ) {

			$items = $order->get_items();
			foreach ( $items as $item ) {
				if ( isset( $item['variation_id'] ) && $item['variation_id'] !== '0' ) {
					$product = wc_get_product( $item['product_id'] );
				} else {
					$product = XLWCTY_Compatibility::get_product_from_item( $order, $item );
				}
				if ( $product === false ) {
					continue;
				}
				if ( version_compare( $this->wc_version(), '3.0', '<' ) ) {

					$number  = 4;
					$related = $product->get_related( $number );
				} else {
					$related = wc_get_related_products( $product->get_id() );
				}
				if ( ! empty( $related ) && is_array( $related ) > 0 ) {
					$xlwcty_related_product = array_merge( $xlwcty_related_product, $related );
					$xlwcty_related_product = array_unique( $xlwcty_related_product );
				}
			}
		}

		return $xlwcty_related_product;
	}

}

return XLWCTY_Related_Products::get_instance();
