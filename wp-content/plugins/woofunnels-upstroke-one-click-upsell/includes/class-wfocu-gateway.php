<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Class for all the Gateway Support Class
 * Class WFOCU_Gateway
 */
abstract class WFOCU_Gateway extends WFOCU_SV_API_Base {


	public $amount = 0;
	public $token = null;
	public $refund_supported = false;
	protected $key = '';

	public function __construct() {

	}


	/**
	 * @return WC_Payment_Gateway
	 */
	public function get_wc_gateway() {
		global $woocommerce;
		$gateways = $woocommerce->payment_gateways->payment_gateways();

		return $gateways[ $this->key ];
	}

	public function get_amount() {
		return $this->amount;
	}

	public function set_amount( $amount ) {
		$this->amount = $amount;
	}

	public function get_key() {
		return $this->key;
	}

	/**
	 * This function checks for the need to do the tokenization.
	 * We have to fetch the funnel to decide whether to tokenize the user or not.
	 * @return int|false funnel ID on success false otherwise
	 *
	 */
	public function should_tokenize() {

		return WFOCU_Core()->data->is_funnel_exists();
	}


	/**
	 * Try and get the payment token saved by the gateway
	 *
	 * @param WC_Order $order
	 *
	 * @return true on success false otherwise
	 */
	public function has_token( $order ) {
		return false;

	}


	/**
	 * Try and get the payment token saved by the gateway
	 *
	 * @param WC_Order $order
	 *
	 * @return true on success false otherwise
	 */
	public function get_token( $order ) {
		return false;

	}

	/**
	 * Charge the upsell and capture payments
	 *
	 * @param WC_Order $order
	 *
	 * @return true on success false otherwise
	 */
	public function process_charge( $order ) {
		return false;

	}

	public function handle_result( $result, $message = '' ) {
		if ( $result ) {
			WFOCU_Core()->data->set( '_transaction_status', 'successful' );

			WFOCU_Core()->data->set( '_transaction_message', __( 'Your order is updated.', 'woofunnels-upstroke-one-click-upsell' ) );

		} else {
			WFOCU_Core()->data->set( '_transaction_status', 'failed' );

			WFOCU_Core()->data->set( '_transaction_message', ( ! empty( $message ) ) ? $message : __( 'Unable to process at the moment.', 'woofunnels-upstroke-one-click-upsell' ) );

		}

		return $result;
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	public function is_enabled( $order = false ) {
		$get_chosen_gateways = WFOCU_Core()->data->get_option( 'gateways' );
		if ( is_array( $get_chosen_gateways ) && in_array( $this->key, $get_chosen_gateways, true ) ) {

			return apply_filters( 'wfocu_front_payment_gateway_integration_enabled', true, $order );
		}

		return false;
	}


	public function get_order_number( $order ) {

		$get_offer_id = WFOCU_Core()->data->get( 'current_offer' );

		if ( ! empty( $get_offer_id ) ) {
			return apply_filters( 'wfocu_payments_get_order_number', WFOCU_WC_Compatibility::get_order_id( $order ) . '_' . $get_offer_id, $this );
		} else {
			return WFOCU_WC_Compatibility::get_order_id( $order );
		}

	}

	/**
	 * Tell the system to run without a token or not
	 * @return bool
	 */
	public function is_run_without_token() {
		return false;
	}

	/**
	 * Allow gateways to declare whether they support offer refund
	 *
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	public function is_refund_supported( $order = false ) {

		if ( $this->refund_supported ) {

			return apply_filters( 'wfocu_payment_gateway_refund_supported', true, $order );
		}

		return false;
	}

	/**
	 * Processing refund request
	 *
	 * @param $order
	 *
	 * @return bool
	 */
	public function process_refund_offer( $order ) {
		return false;
	}

	/**
	 * Providing refund button html for amdin order edit page
	 *
	 * @param $funnel_id
	 * @param $offer_id
	 * @param $total_charge
	 * @param $transaction_id
	 * @param $refunded
	 *
	 * @return string
	 */
	public function get_refund_button_html( $funnel_id, $offer_id, $total_charge, $transaction_id, $refunded, $event_id ) {
		$button_class = ( $refunded ) ? 'disabled' : 'wfocu-refund';
		$button_text  = ( $refunded ) ? __( 'Refunded', 'woofunnels-upstroke-one-click-upsell' ) : __( 'Refund', 'woofunnels-upstroke-one-click-upsell' );

		$button_html = sprintf( '<a href="javascript:void(0);" data-event_id="%s" data-funnel_id="%s" data-offer_id="%s" data-amount="%s" data-txn="%s" class="button %s">%s</a>', $event_id, $funnel_id, $offer_id, $total_charge, $transaction_id, $button_class, $button_text );

		return $button_html;
	}

	/**
	 * Adding common order in a standard format for offer refunds
	 *
	 * @param $order
	 * @param $amnt
	 * @param $refund_id
	 * @param $offer_id
	 * @param $refund_reason
	 */
	public function wfocu_add_order_note( $order, $amnt, $refund_id, $offer_id, $refund_reason ) {
		/* translators: 1) dollar amount 2) transaction id 3) refund message */
		$refund_note = sprintf( __( 'Refunded %1$s Refund ID: %2$s <br/>Offer: %3$s(#%4$s) %5$s', 'woofunnels-upstroke-one-click-upsell' ), $amnt, $refund_id, get_the_title( $offer_id ), $offer_id, $refund_reason );

		$order->add_order_note( $refund_note );
	}

	/**
	 *  Creating transaction test/URL
	 *
	 * @param $transaction_id
	 * @param $order_id
	 *
	 * @return string
	 */
	public function get_transaction_link( $transaction_id, $order_id ) {
		return $transaction_id;
	}


	public function handle_client_error() {
		$get_error = $this->get_client_error();
		if ( ! empty( $get_error ) ) {
			throw new WFOCU_Payment_Gateway_Exception( $get_error, 105 );
		}
	}

	public function get_client_error() {
		$get_package = WFOCU_Core()->data->get( '_upsell_package' );
		if ( isset( $get_package['_client_error'] ) ) {
			return $get_package['_client_error'];
		}

		return '';
	}

	/**
	 * Handle API Error during the client integration
	 *
	 * @param $order_note string Order note to add
	 * @param $log string
	 * @param $order WC_Order
	 */
	public function handle_api_error( $order_note, $log, $order, $create_failed_order = false ) {
		if ( ! empty( $order_note ) ) {
			$order->add_order_note( $order_note );
		}
		if ( ! empty( $log ) ) {
			WFOCU_Core()->log->log( 'Order #' . $order->get_id() . " - " . print_r( $log, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
		if ( true === $create_failed_order ) {
			$data = WFOCU_Core()->process_offer->_handle_upsell_charge( false );
			wp_send_json( array(
				'result'   => 'error',
				'response' => $data,
			) );
		}
	}
}
