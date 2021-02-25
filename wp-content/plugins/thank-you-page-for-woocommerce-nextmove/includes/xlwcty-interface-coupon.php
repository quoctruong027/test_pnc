<?php
defined( 'ABSPATH' ) || exit;

interface xlwcty_coupon {

	public function generate_new_coupons( $or_id = 0 );

	public function get_formated_coupon();

	public function save_coupon_data();

	public function get_coupon_data( $coupon_id, $force = false );

	public function get_expiry_dates( $noOfdays = 0 );

	public function create_new_coupon( $coupon_name, $meta_data );

	public function check_coupon_exist( $coupon_code );

	public function get_locked_coupon();
}
