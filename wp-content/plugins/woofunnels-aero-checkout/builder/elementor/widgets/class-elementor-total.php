<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager as Control_Manager;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class El_WFACP_Form_Total extends WFACP_Elementor_HTML_BLOCK {

	public function get_name() {
		return 'wfacp_form_total';
	}

	public function get_title() {
		return __( 'Order Total', 'woofunnels-aero-checkout' );
	}

	public function get_icon() {
		return 'fa fa-shopping-cart';
	}

	public function get_categories() {
		return [ 'woofunnels-aero-checkout' ];
	}

	protected function _register_controls() {
		$condition = [ 'enable_factor' => 'yes' ];
		$this->add_tab( $this->get_title() );
		$this->add_text( 'title', __( 'Title Heading', 'woofunnels-aero-checkout' ), __( 'Order Total', 'woocommerce' ) );
		$this->add_switcher( 'enable_factor', __( 'Detailed Summary', 'woofunnels-aero-checkout' ) );
		$this->add_text( 'subtotal_title', __( 'Subtotal Text', 'woofunnels-aero-checkout' ), __( 'Subtotal', 'woocommerce' ), $condition );
		$this->end_tab();


		$this->add_tab( 'Sub Total', 2, $condition );
		$this->add_typography( 'sub_total_text_typo', '{{WRAPPER}} .wfacp_order_total_widget.wfacp_order_total_field table.wfacp_order_total_label td' );
		$selector = '{{WRAPPER}} .wfacp_order_total_widget.wfacp_order_subtotal';
		$this->add_padding( 'sub_total_padding', $selector );
		$this->add_border( 'sub_total_border', $selector);
		$this->end_tab();


		$this->add_tab( 'Total', 2 );
		$this->add_typography( 'text_typo', '{{WRAPPER}} .wfacp_order_total_widget.wfacp_order_total_field table.wfacp_order_total_wrap td' );
		$selector = '{{WRAPPER}} .wfacp_order_total_widget.wfacp_order_total_field';
		$this->add_padding( 'total_padding', $selector );
		$this->add_padding( 'total_margin', $selector );
		$this->add_border( 'total_border', $selector);
		$this->end_tab();

	}

	protected function html() {
		echo '<div style="height: 1px">&nbsp</div>';

		/**
		 * @var $template WFACP_Elementor_Template;
		 */
		$template  = wfacp_template();
		$key       = 'wfacp_order_total_widgets';
		$widgets   = WFACP_Common::get_session( $key );
		$widgets[] = $this->get_id();
		WFACP_Common::set_session( $key, $widgets );
		$template->get_order_total_widget( $this->get_id() );

	}
}

//\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \El_WFACP_Form_Total() );