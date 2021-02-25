<?php
defined( 'ABSPATH' ) || exit;

class XLWCTY_Text extends XLWCTY_Component {

	private static $instance = null;
	public $is_disable = true;
	public $viewpath = '';
	public $html_content = '';
	public $is_multiple = true;
	public $component_limit = 5;
	public $script = array();

	public function __construct( $order = false ) {
		parent::__construct();
		$this->viewpath = __DIR__ . '/views/view.php';
		add_action( 'xlwcty_after_component_data_setup_xlwcty_text', array( $this, 'setup_style' ), 10, 2 );
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
			'html_content'       => $this->get_slug() . '_editor',
			'border_style'       => $this->get_slug() . '_border_style',
			'border_width'       => $this->get_slug() . '_border_width',
			'border_color'       => $this->get_slug() . '_border_color',
			'component_bg_color' => $this->get_slug() . '_component_bg',
		);
	}

	public function setup_style( $slug, $index ) {
		if ( $this->is_enable( $index ) ) {
			if ( $this->data->{$index}->heading_font_size != '' ) {
				$style[".xlwcty_wrap .xlwcty_textBox.xlwcty_textBox_{$index} .xlwcty_title "]['font-size']   = $this->data->{$index}->heading_font_size . 'px';
				$style[".xlwcty_wrap .xlwcty_textBox.xlwcty_textBox_{$index} .xlwcty_title "]['line-height'] = ( $this->data->{$index}->heading_font_size + 4 ) . 'px';
			}
			if ( $this->data->{$index}->heading_alignment != '' ) {
				$style[".xlwcty_wrap .xlwcty_textBox.xlwcty_textBox_{$index} .xlwcty_title"]['text-align'] = $this->data->{$index}->heading_alignment;
			}
			if ( $this->data->{$index}->border_style != '' ) {
				$style[".xlwcty_wrap .xlwcty_Box.xlwcty_textBox.xlwcty_textBox_{$index}"]['border-style'] = $this->data->{$index}->border_style;
			}
			if ( (int) $this->data->{$index}->border_width >= 0 ) {
				$style[".xlwcty_wrap .xlwcty_Box.xlwcty_textBox.xlwcty_textBox_{$index}"]['border-width'] = (int) $this->data->{$index}->border_width . 'px';
			}
			if ( $this->data->{$index}->border_color != '' ) {
				$style[".xlwcty_wrap .xlwcty_Box.xlwcty_textBox.xlwcty_textBox_{$index}"]['border-color'] = $this->data->{$index}->border_color;
			}
			if ( $this->data->{$index}->component_bg_color != '' ) {
				$style[".xlwcty_wrap .xlwcty_Box.xlwcty_textBox.xlwcty_textBox_{$index}"]['background-color'] = $this->data->{$index}->component_bg_color;
			}
			parent::push_css( $slug . $index, $style );
		}
	}

}

return XLWCTY_Text::get_instance();
