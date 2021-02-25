<?php

class WFACP_wcnl_parcel_conflict_resolver {
	public function __construct() {
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_nl_post_hooks' ] );
	}

	private function is_enabled() {
		if ( class_exists( 'WooCommerce_MyParcel_Frontend' ) ) {
			return true;
		}

		return false;
	}

	public function remove_nl_post_hooks() {
		if ( $this->is_enabled() ) {
			$wcnl = WFACP_Plugin_Compatibilities::get_compatibility_class( 'wcnl' );
			if ( $wcnl instanceof WFACP_Compatibility_With_Wcnl_Postcode ) {
				remove_filter( 'wfacp_form_section', [ $wcnl, 'checkout_billing_sections' ], 8 );
				remove_filter( 'wfacp_form_section', [ $wcnl, 'checkout_shipping_sections' ], 12 );
				remove_action( 'wfacp_before_process_checkout_template_loader', [ $wcnl, 'validation_fields' ] );
				remove_action( 'wfacp_after_checkout_page_found', [ $wcnl, 'actions' ] );
				remove_action( 'wfacp_internal_css', [ $wcnl, 'internal_css' ] );
			}
		}
	}
}

new WFACP_wcnl_parcel_conflict_resolver();