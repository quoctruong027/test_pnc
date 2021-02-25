<?php
defined( 'ABSPATH' ) || exit;

class XLWCTY_Order_Share extends xlwcty_component {

	private static $instance = null;
	public $viewpath = '';
	public $is_disable = true;

	public function __construct( $order = false ) {
		parent::__construct();
		$this->viewpath = __DIR__ . '/views/view.php';
		add_action( 'xlwcty_after_component_data_setup_xlwcty_share_order', array( $this, 'setup_style' ) );
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
			'heading'           => $this->get_slug() . '_heading',
			'heading_font_size' => $this->get_slug() . '_heading_font_size',
			'heading_alignment' => $this->get_slug() . '_heading_alignment',
			'desc'              => $this->get_slug() . '_desc',
			'desc_alignment'    => $this->get_slug() . '_desc_alignment',
			'fb'                => $this->get_slug() . '_fb',
			'fb_message'        => $this->get_slug() . '_fb_message',
			'tw'                => $this->get_slug() . '_tw',
			'tw_message'        => $this->get_slug() . '_tw_message',
			'share_link'        => $this->get_slug() . '_share_link',
			'share_custom_link' => $this->get_slug() . '_share_custom_link',
			'ins'               => $this->get_slug() . '_ins',
			'ins_message'       => $this->get_slug() . '_ins_message',
			'btn_text'          => $this->get_slug() . '_btn_text',

			'btn_font_size'      => $this->get_slug() . '_btn_font_size',
			'btn_color'          => $this->get_slug() . '_btn_color',
			'btn_bg_color'       => $this->get_slug() . '_btn_bg_color',
			'border_style'       => $this->get_slug() . '_border_style',
			'border_width'       => $this->get_slug() . '_border_width',
			'border_color'       => $this->get_slug() . '_border_color',
			'component_bg_color' => $this->get_slug() . '_component_bg',
		);
	}

	public function setup_style( $slug ) {
		if ( $this->is_enable() ) {
			if ( $this->data->heading_font_size != '' ) {
				$style['.xlwcty_wrap .xlwcty_share .xlwcty_title']['font-size']   = $this->data->heading_font_size . 'px';
				$style['.xlwcty_wrap .xlwcty_share .xlwcty_title']['line-height'] = ( $this->data->heading_font_size + 4 ) . 'px';
			}
			if ( $this->data->heading_alignment != '' ) {
				$style['.xlwcty_wrap .xlwcty_share .xlwcty_title']['text-align'] = $this->data->heading_alignment;
			}
			if ( $this->data->btn_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_share .xlwcty_btn']['color'] = $this->data->btn_color;
			}
			if ( $this->data->btn_bg_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_share .xlwcty_btn']['background'] = $this->data->btn_bg_color;
				$rgba                                                          = XLWCTY_Common::hex2rgb( $this->data->btn_bg_color, true );
				if ( $rgba != '' ) {
					$style['.xlwcty_wrap .xlwcty_share .xlwcty_btn:hover']['background'] = "rgba({$rgba},0.70)";
				}
			}
			if ( $this->data->btn_font_size != '' ) {
				$style['.xlwcty_wrap .xlwcty_share .xlwcty_btn']['font-size'] = $this->data->btn_font_size . 'px';
			}
			if ( $this->data->border_style != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_share']['border-style'] = $this->data->border_style;
			}
			if ( (int) $this->data->border_width >= 0 ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_share']['border-width'] = (int) $this->data->border_width . 'px';
			}
			if ( $this->data->border_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_share']['border-color'] = $this->data->border_color;
			}
			if ( $this->data->component_bg_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_share']['background-color'] = $this->data->component_bg_color;
			}
			parent::push_css( $slug, $style );
		}
	}

}

return XLWCTY_Order_Share::get_instance();
