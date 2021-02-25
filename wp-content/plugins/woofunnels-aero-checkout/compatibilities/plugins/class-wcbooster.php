<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Active_WCJ {

	public function __construct() {
		add_filter( 'wfacp_custom_field_order_id', function ( $order_id ) {
			if ( isset( $_REQUEST['create_invoice_for_order_id'] ) && $_REQUEST['create_invoice_for_order_id'] > 0 ) {
				$order_id = absint( $_REQUEST['create_invoice_for_order_id'] );
			}

			return $order_id;
		} );
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Active_WCJ(), 'wcj' );
