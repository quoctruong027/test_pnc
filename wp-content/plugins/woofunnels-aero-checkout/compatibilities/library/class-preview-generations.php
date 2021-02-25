<?php

class WFACP_Preview_Generations {
	public function __construct() {
		add_action( 'wfacp_add_to_cart_init', [ $this, 'push_dummy_products' ] );
	}

	/**
	 * @param $public WFACP_Public
	 */
	public function push_dummy_products( $public ) {


		$parameter = $public->aero_add_to_checkout_parameter();;

		if ( isset( $_GET[ $parameter ] ) ) {
			$public->add_to_cart_via_url();

			return '';
		}

		if ( ! isset( $_GET['wfacp_preview'] ) && ! WFACP_Common::is_theme_builder() ) {
			return '';
		}
		$product = $public->get_product_list();

		if ( ! empty( $product ) ) {
			return '';
		}
		$products = wc_get_products( [
			'limit'  => 2,
			'return' => 'ids',
		] );

		if ( empty( $products ) ) {
			$template = wfacp_template();
			if ( ! is_null( $template ) ) {
				remove_action( 'woocommerce_get_cart_page_permalink', [ $template, 'change_cancel_url' ], 999 );
				$url = wc_get_cart_url();
				wp_redirect( $url );
			}

			return '';
		}
		$_GET[ $parameter ] = implode( ',', $products );
		$public->add_to_cart_via_url();
		if ( ! is_super_admin() ) {

			return '';
		}
		add_action( 'woocommerce_after_checkout_form', [ $this, 'woocommerce_demo_store' ] );
		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );
	}

	function woocommerce_demo_store() {
		$notice    = __( 'Admin Notice: Sample product(s) added to the cart to generate the preview', 'woofunnels-aero-checkout' );
		$notice_id = md5( $notice );
		$pageID    = WFACP_Common::get_id();
		$key       = 'wfacp_notice_dismise_link_' . $pageID;

		$wfacp_notice_dismise_link = get_post_meta( $pageID, $key, true );
		if ( $wfacp_notice_dismise_link == '' ) {
			echo apply_filters( 'wfacp_dummy_preview_heading', '<p page-id="' . $pageID . '" data-notice-id=' . $key . ' class="wfacp_notice_dismise_link woocommerce-store-notice demo_store wfacp_dummy_preview_heading" data-notice-id="' . esc_attr( $notice_id ) . '" style="display:none;">' . wp_kses_post( $notice ) . '<a class="wfacp_close_icon" href="javascript:void(0)">x</a> </p>', $notice ); // WPCS: XSS ok.
		}
	}

	public function internal_css() {

		?>
        <style>
            body .wfacp_main_form.woocommerce p.woocommerce-store-notice.demo_store {
                color: #fff;
            }

            .woocommerce-store-notice, p.demo_store {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                margin: 0;
                width: 100%;
                font-size: 1em;
                padding: 1em 0;
                text-align: center;
                background-color: #a46497;
                color: #fff;
                z-index: 99998;
                box-shadow: 0 1px 1em rgba(0, 0, 0, .2);
                display: block
            }

            .woocommerce-store-notice a, p.demo_store a {
                color: #fff;
                text-decoration: underline
            }
        </style>
		<?php
	}
}

new WFACP_Preview_Generations();
