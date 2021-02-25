<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class El_WFACP_Form_Lite extends \Elementor\Widget_Base {

	public function get_name() {

		return 'wfacp_form_lite';
	}

	public function get_title() {
		return __( 'Checkout Form', 'woofunnels-aero-checkout' );
	}

	public function get_icon() {
		return 'wfacp-icon-icon_checkout';
	}

	public function get_categories() {
		return [ 'woofunnels-aero-checkout' ];
	}

	protected function _register_controls() {


	}

	public function show_in_panel() {
		return false;
	}

	protected function render() {
		if ( isset( $_REQUEST['action'] ) && 'elementor' == $_REQUEST['action'] ) {
			return;
		}

		echo '<div style="height: 1px">&nbsp</div>';
		$id = WFACP_Common::get_id();
		if ( wp_doing_ajax() ) {
			do_action( 'wfacp_after_checkout_page_found', $id );
		}
		echo do_shortcode( '[wfacp_forms id="' . $id . '"]' );


	}
}

\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \El_WFACP_Form_Lite() );