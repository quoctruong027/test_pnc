<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XLWCTY_PP_Public {

	private static $ins = null;

	public function __construct() {
		add_filter( 'xlwcty_is_component_enabled', array( $this, 'xlwcty_check_order_status_to_display_component' ), 10, 3 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'xlwcty_generate_thankyou_page_id' ), 10, 3 );
	}

	public static function instance() {
		if ( self::$ins == null ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	/**
	 * @param $flag
	 * @param $slug
	 * @param $index
	 *
	 * @return bool
	 * Function to show or hide component according to the order status.
	 * Hooked in Nextmove Pro to check if the component is enabled or not
	 */
	public function xlwcty_check_order_status_to_display_component( $flag, $slug, $index ) {
		if ( ! isset( $_REQUEST['order_id'] ) || empty( $_REQUEST['order_id'] ) ) {
			return $flag;
		}

		$order_id     = $_REQUEST['order_id'];
		$order        = wc_get_order( $order_id );
		$order_status = $order->get_status();

		if ( false === $index ) {
			$hide_statuses = XLWCTY_Core()->data->get_meta( $slug . '_hide_order_status', 'raw' );
			$hide_statuses = maybe_serialize( $hide_statuses ) ? maybe_unserialize( $hide_statuses ) : $hide_statuses;
		} else {
			$hide_statuses = XLWCTY_Core()->data->get_meta( $slug . '_hide_order_status_' . $index, 'raw' );
			$hide_statuses = maybe_serialize( $hide_statuses ) ? maybe_unserialize( $hide_statuses ) : $hide_statuses;
		}

		if ( is_array( $hide_statuses ) && ! empty( $hide_statuses ) && in_array( $order_status, $hide_statuses ) ) {
			return false;
		}

		return $flag;
	}

	/**
	 * @param $order_id
	 * @param $posted_data
	 * @param $order
	 *
	 * Function to generate thankyou page id and save it in order meta
	 */
	public function xlwcty_generate_thankyou_page_id( $order_id, $posted_data, $order ) {
		$default_settings = XLWCTY_Core()->data->get_option();
		if ( isset( $default_settings['xlwcty_preview_mode'] ) && ( 'sandbox' == $default_settings['xlwcty_preview_mode'] ) ) {
			return;
		}

		if ( isset( $_REQUEST['mode'] ) && 'preview' == $_REQUEST['mode'] ) {
			return;
		}

		$page_id = XLWCTY_Core()->data->setup_thankyou_post( $order_id, false )->get_page();
		if ( ! empty( $page_id ) ) {
			update_post_meta( $order_id, '_xlwcty_thankyou_page', $page_id );
		}
	}

}

XLWCTY_PP_Public::instance();
