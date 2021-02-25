<?php
/*
 * WooCommerce PostNL
 * Author Name: PostNL
 * https://wordpress.org/plugins/woo-postnl/
 */

class WFACP_Compatibility_With_Wc_PostNL {


	public function __construct() {

		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );
		add_filter( 'wc_postnl_delivery_options_location', function () {
			return "wfacp_after_wfacp_divider_billing_end_field";
		} );
		add_action( 'wp_footer', [ $this, 'add_js' ] );

	}


	public function internal_css() {
		if ( ! class_exists( 'WooCommerce_PostNL' ) ) {
			return;
		}

		?>
        <style>

            #post-delivery-option-form {
                padding: 0 7px;
                margin-bottom: 20px;
            }

            #post-message {
                padding: 0 7px;
            }

            tr#header-delivery-options-title td {
                padding-left: 0;
                padding-right: 0;
            }

            tr#header-delivery-options-title td h3 {
                font-weight: normal;
            }

            #post-delivery-option-form .post-delivery-option-table {
                width: 100%;
            }


            #post-delivery-option-form .post-delivery-option-table h1,
            #post-delivery-option-form .post-delivery-option-table h2,
            #post-delivery-option-form .post-delivery-option-table h3,
            #post-delivery-option-form .post-delivery-option-table h4,
            #post-delivery-option-form .post-delivery-option-table h5 {
                margin: 0 0 15px;
            }

            #post-message h3 {
                margin: 0 0 15px;
                font-weight: normal;
            }

            #post-delivery-option-form .post-delivery-option-table label {
                padding: 0 !important;
                display: inline-block;
                margin: 0;
            }


            #post-delivery-option-form input[type="radio"],
            #post-delivery-option-form input[type="checkbox"] {
                position: relative;
                top: auto;
                bottom: auto;
                left: auto;
                right: auto;
                margin: 0 0 0 0px;
            }

            #post-delivery-option-form table td {
                padding: 15px 8px;
                border: none;
                border-bottom: 1px solid #E6E6E6;
                text-align: left;
                font-weight: inherit;
            }

            #post-delivery-option-form table td select {
                margin: 0;
                margin: 0;
                width: calc(100% - 25px) !important;
                display: inline-block;
            }

            #post-delivery-option-form table tr td:last-child {
                white-space: nowrap;
                vertical-align: top;
                width: 20px;
            }

            #post-spinner-model svg {
                width: auto;
                max-width: 100px;
                margin: auto;
                float: none;
            }

            #post-delivery-option-form .post-fa-clock {
                width: 16px;
                display: inline-block;
                margin-bottom: -30px;
                overflow: hidden;
                vertical-align: middle;
            }

            #header-delivery-options-title td {

                padding: 0 !important;
            }

        </style>
		<?php
	}

	public function add_js() {
		if ( ! class_exists( 'WooCommerce_PostNL' ) ) {
			return;
		}

		?>
        <script>

            window.addEventListener('load', function () {
                (function ($) {

                    setTimeout(function () {
                        add_aero_title_class();
                    }, 200);

                    function add_aero_title_class() {

                        if ($('#post-message h3').length > 0) {

                            $('#post-message h3').addClass('wfacp_section_title');
                        }
                        if ($('#header-delivery-options-title td h3').length > 0) {

                            $('#header-delivery-options-title td h3').addClass('wfacp_section_title');
                        }
                    }


                })(jQuery);
            });
        </script>
		<?php


	}


}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Wc_PostNL(), 'woocommerce-postnl' );



