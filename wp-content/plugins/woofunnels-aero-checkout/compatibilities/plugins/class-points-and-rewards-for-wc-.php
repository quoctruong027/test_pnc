<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Points and Rewards for WooCommerce By MakeWebBetter
 * Class WFACP_Compatibility_With_Point_Rewards_For_WC
 */
class WFACP_Compatibility_With_Point_Rewards_For_WC {
	public function __construct() {

		/* checkout page */
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'actions' ] );
		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );

	}

	public function actions() {
		if ( class_exists( 'Points_Rewards_For_WooCommerce_Public' ) ) {
			add_action( 'woocommerce_before_checkout_form', [ $this, 'add_points_rewards' ], 100 );
		}

	}

	public function add_points_rewards() {
		$public_obj = new Points_Rewards_For_WooCommerce_Public( 'points-rewads-for-woocommerce', '1.0.0' );
		echo "<div id='wfacp_custom_point_checkout_wrap' class='wfacp_clearfix'>";
		$public_obj->mwb_wpr_display_apply_points_checkout();
		echo "</div>";
	}

	public function internal_css( $slug ) {

		?>
        <style>
            #wfacp_custom_point_checkout_wrap .custom_point_checkout {
                width: 100%;
            }


            #wfacp_custom_point_checkout_wrap {
                margin: 20px 0;
            }

            #wfacp_custom_point_checkout_wrap .custom_point_checkout #mwb_cart_points {
                font-size: 14px;
                line-height: 1.5;
                background-color: #ffffff;
                border-radius: 4px;
                position: relative;
                color: #404040;
                display: block;
                padding: 12px 12px 10px;
                vertical-align: top;
                box-shadow: none;
                opacity: 1;
                border: 1px solid #bfbfbf;
                width: calc(100% - 170px);
                min-height: 52px;
            }

            #wfacp_custom_point_checkout_wrap .custom_point_checkout button#mwb_cart_points_apply {
                font-size: 14px;
                cursor: pointer;
                background-color: #999999;
                color: #ffffff;
                text-decoration: none;
                font-weight: normal;
                line-height: 18px;
                margin-bottom: 0;
                padding: 10px 20px;
                border: 1px solid rgba(0, 0, 0, 0.1);
                border-radius: 4px;
                width: 160px;
                max-width: 160px;
                min-height: 52px;
                margin-right: 0;
                display: inline-block;
            }

            #wfacp_custom_point_checkout_wrap #mwb_cart_points_apply ~ p {
                margin-top: 5px;
                color: #737373;
                float: none;
                clear: both;
            }

            #wfacp_custom_point_checkout_wrap .custom_point_checkout button#mwb_cart_points_apply:hover {
                background-color: #878484;
            }
        </style>

		<?php
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Point_Rewards_For_WC(), 'prfwc' );
