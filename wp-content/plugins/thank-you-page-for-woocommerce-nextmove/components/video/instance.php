<?php
defined( 'ABSPATH' ) || exit;

class XLWCTY_Video extends XLWCTY_Component {

	private static $instance = null;
	public $is_disable = true;
	public $viewpath = '';
	public $source = '';
	public $height = '';
	public $width = '';
	public $component_limit = 5;

	public function __construct( $order = false ) {
		parent::__construct();
		$this->viewpath = __DIR__ . '/views/view.php';
		add_action( 'xlwcty_after_component_data_setup_xlwcty_video', array( $this, 'setup_style' ), 10, 2 );
		add_action( 'xlwcty_after_components_loaded', array( $this, 'setup_fields' ) );
	}

	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function prepare_out_put_data() {

		parent::prepare_out_put_data();
	}

	public function setup_fields() {
		$this->fields = array(
			'heading'            => $this->get_slug() . '_heading',
			'heading_font_size'  => $this->get_slug() . '_heading_font_size',
			'heading_alignment'  => $this->get_slug() . '_heading_alignment',
			'desc'               => $this->get_slug() . '_desc',
			'desc_alignment'     => $this->get_slug() . '_desc_alignment',
			'source'             => $this->get_slug() . '_src',
			'url'                => $this->get_slug() . '_url',
			'embed'              => $this->get_slug() . '_embed',
			'show_btn'           => $this->get_slug() . '_show_btn',
			'btn_text'           => $this->get_slug() . '_btn_text',
			'heading_alignment'  => $this->get_slug() . '_heading_alignment',
			'btn_link'           => $this->get_slug() . '_btn_link',
			'btn_font_size'      => $this->get_slug() . '_btn_font_size',
			'btn_color'          => $this->get_slug() . '_btn_color',
			'btn_bg_color'       => $this->get_slug() . '_btn_bg_color',
			'border_style'       => $this->get_slug() . '_border_style',
			'border_width'       => $this->get_slug() . '_border_width',
			'border_color'       => $this->get_slug() . '_border_color',
			'component_bg_color' => $this->get_slug() . '_component_bg',
		);
	}

	public function setup_style( $slug, $index ) {
		if ( $this->is_enable( $index ) ) {
			if ( $this->data->{$index}->heading_font_size != '' ) {
				$style[".xlwcty_wrap .xlwcty_videoBox.xlwcty_videoBox_{$index} .xlwcty_title"]['font-size']   = $this->data->{$index}->heading_font_size . 'px';
				$style[".xlwcty_wrap .xlwcty_videoBox.xlwcty_videoBox_{$index} .xlwcty_title"]['line-height'] = ( $this->data->{$index}->heading_font_size + 4 ) . 'px';
			}
			if ( $this->data->{$index}->heading_alignment != '' ) {
				$style[".xlwcty_wrap .xlwcty_videoBox.xlwcty_videoBox_{$index} .xlwcty_title"]['text-align'] = $this->data->{$index}->heading_alignment;
			}
			if ( $this->data->{$index}->border_style != '' ) {
				$style[".xlwcty_wrap .xlwcty_Box.xlwcty_videoBox.xlwcty_videoBox_{$index}"]['border-style'] = $this->data->{$index}->border_style;
			}
			if ( (int) $this->data->{$index}->border_width >= 0 ) {
				$style[".xlwcty_wrap .xlwcty_Box.xlwcty_videoBox.xlwcty_videoBox_{$index}"]['border-width'] = (int) $this->data->{$index}->border_width . 'px';
			}
			if ( $this->data->{$index}->border_color != '' ) {
				$style[".xlwcty_wrap .xlwcty_Box.xlwcty_videoBox.xlwcty_videoBox_{$index}"]['border-color'] = $this->data->{$index}->border_color;
			}
			if ( $this->data->{$index}->component_bg_color != '' ) {
				$style[".xlwcty_wrap .xlwcty_Box.xlwcty_videoBox.xlwcty_videoBox_{$index}"]['background-color'] = $this->data->{$index}->component_bg_color;
			}
			if ( $this->data->{$index}->btn_font_size != '' ) {
				$style[".xlwcty_wrap .xlwcty_videoBox.xlwcty_videoBox_{$index} .xlwcty_btn"]['font-size'] = $this->data->{$index}->btn_font_size . 'px';
			}
			if ( $this->data->{$index}->btn_color != '' ) {
				$style[".xlwcty_wrap .xlwcty_videoBox.xlwcty_videoBox_{$index} .xlwcty_btn"]['color'] = $this->data->{$index}->btn_color;
			}
			if ( $this->data->{$index}->btn_bg_color != '' ) {
				$style[".xlwcty_wrap .xlwcty_videoBox.xlwcty_videoBox_{$index} .xlwcty_btn"]['background'] = $this->data->{$index}->btn_bg_color;
				$rgba                                                                                      = XLWCTY_Common::hex2rgb( $this->data->{$index}->btn_bg_color, true );
				if ( $rgba != '' ) {
					$style[".xlwcty_wrap .xlwcty_videoBox.xlwcty_videoBox_{$index} .xlwcty_btn:hover"]['background'] = "rgba({$rgba},0.70)";
				}
			}
			parent::push_css( $slug . $index, $style );
		}
	}

}

return XLWCTY_Video::get_instance();
