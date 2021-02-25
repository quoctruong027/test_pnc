<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Woo_Delivery_Slots_Premium {

	public function __construct() {

		add_filter( 'wpsf_register_settings_jckwds', [ $this, 'active_setting' ], 12 );
		add_filter( 'woocommerce_form_field_args', [ $this, 'add_default_wfacp_styling' ], 50, 2 );
		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );
		add_filter( 'wfacp_css_js_deque', function ( $bool, $path, $url, $current ) {
			if ( ! class_exists( 'jckWooDeliverySlots' ) ) {
				return $bool;
			}
			if ( false !== strpos( $url, 'ajax.googleapis.com/ajax/libs/jqueryui/' ) ) {

				return false;
			}

			return $bool;
		}, 10, 4 );


		add_filter( 'wfacp_after_checkout_page_found', [ $this, 'action' ], 12 );
	}

	public function action() {
		if ( ! class_exists( 'jckWooDeliverySlots' ) ) {
			return;
		}

		add_filter( 'wfacp_print_shipping_hidden_fields', function () {
			return false;
		} );
		add_filter( 'wfacp_show_shipping_options', function () {
			return true;
		} );
	}

	public function active_setting( $wpsf_settings ) {

		global $jckwds;
		if ( ! $jckwds || ! function_exists( 'WC' ) ) {
			return $wpsf_settings;
		}
		if ( ! class_exists( 'WFACP_Core' ) ) {
			return $wpsf_settings;
		}
		if ( isset( $wpsf_settings['sections'] ) && is_array( $wpsf_settings['sections'] ) && count( $wpsf_settings['sections'] ) > 0 ) {
			foreach ( $wpsf_settings['sections'] as $key => $value ) {

				if ( ! isset( $value['tab_id'] ) || $value['tab_id'] != 'general' ) {
					continue;
				}
				if ( ! isset( $value['fields'] ) || ( ! is_array( $value['fields'] ) || count( $value['fields'] ) == 0 ) ) {
					continue;
				}

				foreach ( $value['fields'] as $field_key => $field_value ) {
					if ( isset( $field_value['id'] ) && $field_value['id'] == 'position' ) {
						$wpsf_settings['sections'][ $key ]['fields'][ $field_key ]['choices']['wfacp_after_wfacp_divider_shipping_end_field'] = "AeroCheckout After Shipping Fields";
						$wpsf_settings['sections'][ $key ]['fields'][ $field_key ]['choices']['wfacp_after_wfacp_divider_billing_end_field']  = "AeroCheckout After Billing Fields";
					}

				}


			}
		}

		return $wpsf_settings;
	}

	public function add_default_wfacp_styling( $args, $key ) {

		if ( $key == 'jckwds-delivery-date' || $key == 'jckwds-delivery-time' ) {
			$args['input_class'] = array_merge( $args['input_class'], [ 'wfacp-form-control' ] );
			$args['label_class'] = array_merge( $args['label_class'], [ 'wfacp-form-control-label' ] );
			$args['class']       = array_merge( $args['class'], [ 'wfacp-col-full', 'wfacp-form-control-wrapper' ] );
		}

		return $args;
	}

	public function internal_css() {

		if ( ! function_exists( 'jckwds_settings' ) ) {
			return;
		}
		?>
        <style>
            h3.iconic-wds-fields__title {
                padding: 0 7px;
                margin: 0 0 10px;
            }

            p#jckwds-delivery-date_field:not(.wfacp-anim-wrap) label {
                top: 30px;
                bottom: auto;
            }


        </style>
		<?php
	}


}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Woo_Delivery_Slots_Premium(), 'wdsp' );
