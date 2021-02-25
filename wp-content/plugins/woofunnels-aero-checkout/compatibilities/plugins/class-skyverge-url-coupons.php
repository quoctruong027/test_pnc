<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_url_coupons_Sky_Verge {
	public function __construct() {

		add_filter( 'wc_url_coupons_url_matches_coupon', [ $this, 'disable_coupon_apply' ] );
		add_action( 'wfacp_changed_default_woocommerce_page', [ $this, 'skip_add_to_cart' ] );
		add_filter( 'wfacp_skip_add_to_cart', [ $this, 'skip_add_to_cart' ] );
	}

	public function skip_add_to_cart( $status ) {
		add_action( 'woocommerce_before_cart_emptied', [ $this, 'catch_applied_coupons' ] );
		add_action( 'wfacp_after_add_to_cart', [ $this, 're_apply_coupon_global' ], 10 );

		return $status;
	}

	public function catch_applied_coupons() {
		$this->coupons = WC()->cart->applied_coupons;
	}

	public function re_apply_coupon_global() {
		if ( ! empty( $this->coupons ) ) {
			wc_clear_notices();
			foreach ( $this->coupons as $coupon ) {
				WC()->cart->add_discount( $coupon );
			}
		}
	}


	public function disable_coupon_apply( $url_match ) {
		add_action( 'wp', [ $this, 're_apply_coupon' ], 10 );

		return false;
	}


	public function re_apply_coupon() {
		remove_filter( 'wc_url_coupons_url_matches_coupon', [ $this, 'disable_coupon_apply' ] );
		if ( function_exists( 'wc_url_coupons' ) && ! is_null( wc_url_coupons()->get_frontend_instance() ) ) {

			if ( is_checkout() ) {
				wc_clear_notices();
			}

			wc_url_coupons()->get_frontend_instance()->maybe_apply_coupon();
		}
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_url_coupons_Sky_Verge(), 'url_coupon_sky_verge' );
