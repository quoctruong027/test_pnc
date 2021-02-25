<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_WC_Select_City {


	public function __construct() {

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'actions_call' ] );

	}

	public function actions_call() {
		add_action( 'wp_footer', [ $this, 'add_custom_js' ] );
	}


	public function add_custom_js() {

		if ( ! class_exists( 'WC_City_Select' ) ) {
			return '';
		}


		?>
        <script>

            window.addEventListener('load', function () {
                (function ($) {
                    trigger_field();

                    function trigger_field() {
                        if (jQuery('#billing_state').length > 0) {
                            jQuery('#billing_state').trigger('change')
                        }
                        if (jQuery('#shipping_state').length > 0) {
                            jQuery('#shipping_state').trigger('change')
                        }
                    }


                })(jQuery);
            });
        </script>
		<?php

	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_WC_Select_City(), 'wsc' );
