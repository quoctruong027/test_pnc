<?php
defined( 'ABSPATH' ) || exit;

class XLWCTY_Social_Sharing extends XLWCTY_Component {

	private static $instance = null;
	public $viewpath = '';
	public $is_disable = true;

	public function __construct( $order = false ) {
		parent::__construct();
		$this->viewpath = __DIR__ . '/views/view.php';
		add_action( 'xlwcty_after_component_data_setup_xlwcty_social_sharing', array( $this, 'setup_style' ) );
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
			'icon_style'         => $this->get_slug() . '_icon_style',
			'fb'                 => $this->get_slug() . '_fb',
			'tw'                 => $this->get_slug() . '_tw',
			'pin'                => $this->get_slug() . '_pin',
			'gp'                 => $this->get_slug() . '_gp',
			'insta'              => $this->get_slug() . '_insta',
			'linkedin'           => $this->get_slug() . '_linkedin',
			'youtube'            => $this->get_slug() . '_youtube',
			'border_style'       => $this->get_slug() . '_border_style',
			'border_width'       => $this->get_slug() . '_border_width',
			'border_color'       => $this->get_slug() . '_border_color',
			'component_bg_color' => $this->get_slug() . '_component_bg',
		);
	}

	public function prepare_out_put_data() {
		parent::prepare_out_put_data();
	}

	public function setup_style( $slug ) {
		if ( $this->is_enable() ) {
			if ( $this->data->heading_font_size != '' ) {
				$style['.xlwcty_wrap .xlwcty_socialBox.xlwcty_joinus .xlwcty_title']['font-size']   = $this->data->heading_font_size . 'px';
				$style['.xlwcty_wrap .xlwcty_socialBox.xlwcty_joinus .xlwcty_title']['line-height'] = ( $this->data->heading_font_size + 4 ) . 'px';
			}
			if ( $this->data->heading_alignment != '' ) {
				$style['.xlwcty_wrap .xlwcty_socialBox.xlwcty_joinus .xlwcty_title']['text-align'] = $this->data->heading_alignment;
			}
			if ( $this->data->desc_alignment != '' ) {
				$style['.xlwcty_wrap .xlwcty_socialBox.xlwcty_joinus .xlwcty_join_us_desc']['text-align'] = $this->data->desc_alignment;
			}
			if ( $this->data->border_style != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_socialBox.xlwcty_joinus']['border-style'] = $this->data->border_style;
			}
			if ( (int) $this->data->border_width >= 0 ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_socialBox.xlwcty_joinus']['border-width'] = (int) $this->data->border_width . 'px';
			}
			if ( $this->data->border_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_socialBox.xlwcty_joinus']['border-color'] = $this->data->border_color;
			}
			if ( $this->data->component_bg_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_socialBox.xlwcty_joinus']['background-color'] = $this->data->component_bg_color;
			}
			parent::push_css( $slug, $style );
		}
	}

}

return XLWCTY_Social_Sharing::get_instance();
