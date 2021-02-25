<?php
defined( 'ABSPATH' ) || exit;


if ( 'yes' === $this->data->hide_for_repeat_customers ) {
	$user_id       = $order_data->get_user_id();
	$billing_email = XLWCTY_Compatibility::get_order_data( $order_data, 'billing_email' );

	$orders = wc_get_orders( array(
		'customer' => $user_id ? $user_id : $billing_email,
		'limit'    => 1,
		'return'   => 'ids',
		'exclude'  => array( $order_data->id ),
	) );

	if ( ! empty( $orders ) ) {
		return false;
	}
}

if ( $this->data->selected_coupon > 0 ) {
	XLWCTY_Core()->public->add_header_logs( sprintf( ' %s - %s', $this->get_component_property( 'title' ), 'On' ) );

	$or_id       = XLWCTY_Compatibility::get_order_id( $order_data );
	$cookie_data = ( isset( $_COOKIE["xlwcty_generate_new_coupons_disaplayed_{$or_id}"] ) ? $_COOKIE["xlwcty_generate_new_coupons_disaplayed_{$or_id}"] : '' );
	if ( isset( $cookie_data ) && ! empty( $cookie_data ) ) {
		$data          = json_decode( stripslashes( $cookie_data ), true );
		$data_order_id = (int) $data['or'];
		if ( $data_order_id == $or_id ) {
			$coupon_data = $this->generate_new_coupons( $data_order_id );
			extract( $coupon_data );
			if ( '' !== $coupon_data['coupon_code'] ) {
				if ( 'button' === $this->data->display_coupon ) {
					include __DIR__ . '/coupon_view.php';
				} else {
					include __DIR__ . '/open_coupon.php';
				}
			}
		}
	} else {
		if ( 'button' === $this->data->display_coupon ) {
			include __DIR__ . '/lock_coupon.php';
		} else {
			include __DIR__ . '/open_coupon.php';
		}
	}
} else {
	XLWCTY_Core()->public->add_header_logs( sprintf( '%s - %s', $this->get_component_property( 'title' ), __( 'Data not set', 'thank-you-page-for-woocommerce-nextmove' ) ) );

}
