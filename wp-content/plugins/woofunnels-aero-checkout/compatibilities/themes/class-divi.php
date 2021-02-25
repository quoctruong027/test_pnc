<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Divi {

	public function __construct() {

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'wfacp' ) {

			add_action( 'after_setup_theme', function () {

				remove_action( 'init', 'et_add_divi_support_center' );
			} );

		}

		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );


	}

	public function internal_css() {

		if ( ! defined( 'ET_CORE_VERSION' ) ) {
			return;
		}
		?>

        <style>
            #wfacp-e-form .wfacp-form .woocommerce-form-login-toggle .woocommerce-info a.showlogin,
            #wfacp-e-form .wfacp-form .woocommerce-form-login-toggle .woocommerce-info a {
                color: #dd7575 !important;;

            }

            #wfacp-e-form .wfacp_main_form .woocommerce-form-login-toggle .woocommerce-info a:hover,
            #wfacp-e-form .wfacp_main_form a span:hover, #wfacp-e-form .wfacp_main_form label a:hover,
            #wfacp-e-form .wfacp_main_form table tr td a:hover, body:not(.wfacpef_page)
            #wfacp-e-form .wfacp_main_form a:not(.wfacp_breadcrumb_link):hover,
            body:not(.wfacpef_page) #wfacp-e-form .wfacp_main_form ul li a:not(.wfacp_breadcrumb_link):hover {
                color: #965d5d !important;
            }
        </style>
		<?php
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Divi(), 'divi' );
