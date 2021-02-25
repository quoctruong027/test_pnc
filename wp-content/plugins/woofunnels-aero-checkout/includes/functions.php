<?php
function wfacp_is_elementor() {

	if ( defined( 'ELEMENTOR_VERSION' ) ) {
		return \Elementor\Plugin::$instance->db->is_built_with_elementor( WFACP_Common::get_id() );
	}

	return false;
}


/**
 * Return instance of Current Template Class
 * @return WFACP_Template_Common
 */
function wfacp_template() {
	if ( is_null( WFACP_Core()->template_loader ) ) {
		return null;
	}

	return WFACP_Core()->template_loader->get_template_ins();
}
