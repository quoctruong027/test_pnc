<?php

class WFOCU_Compatibility_With_WC_Germanized {

	public function __construct() {

		if ( ! $this->is_enable() ) {
			return;
		}
		add_action( 'wfocu_front_init_funnel_hooks', array( $this, 'maybe_reorder_email_hooks' ) );
		add_action( 'wfocu_after_normalize_order_status', array( $this, 'maybe_send_email_after_nomralize' ), 10, 2 );
		/**
		 * Normalizing the order statuses on funnel end for bacs and cheque
		 */
		add_action( 'wfocu_funnel_ended_event', array( $this, 'maybe_send_pending_email_for_bacs_for_cheque' ), 9, 3 );
		add_action( 'wfocu_offer_new_order_created', array( $this, 'maybe_send_pending_emails_new_order' ), 9 );
	}


	public function is_enable() {

		if ( true === defined( 'WC_GERMANIZED_PLUGIN_FILE' ) ) {
			return true;
		}

		return false;
	}

	public function maybe_reorder_email_hooks( $order ) {

		$order_behavior   = WFOCU_Core()->funnels->get_funnel_option( 'order_behavior' );
		$batching_mail_on = WFOCU_Core()->data->get_option( 'send_processing_mail_on' );
		/**
		 * return on these scenarios so that we do not prevent germanized to process
		 */
		if ( 'batching' === $order_behavior && 'start' === $batching_mail_on && ( in_array( $order->get_payment_method(), [ 'bacs', 'cheque' ], true ) ) ) {
			return;
		} elseif ( 'batching' !== $order_behavior ) {
			return;
		}


		remove_filter( 'woocommerce_payment_successful_result', array( WC_germanized()->emails, 'send_order_confirmation_mails' ), 0 );
	}

	/**
	 * @param WC_Order $order
	 * @param $order_status
	 *
	 */
	public function maybe_send_email_after_nomralize( $order, $order_status ) {

		$funnel_id = $order->get_meta( '_wfocu_funnel_id', true );
		if ( empty( $funnel_id ) ) {
			return;
		}

		WFOCU_Core()->funnels->setup_funnel_options( $funnel_id );
		$order_behavior   = WFOCU_Core()->funnels->get_funnel_option( 'order_behavior' );
		$batching_mail_on = WFOCU_Core()->data->get_option( 'send_processing_mail_on' );

		/**
		 * return on these scenarios so that we do not prevent germanized to process
		 */
		if ( 'batching' === $order_behavior && 'start' === $batching_mail_on ) {
			return;
		} elseif ( 'batching' !== $order_behavior ) {
			return;
		}
		do_action( 'woocommerce_gzd_order_confirmation', $order );
	}

	public function maybe_send_pending_email_for_bacs_for_cheque( $funnel_or_order_id, $order_id = '' ) {

		$get_parent = wc_get_order( $order_id );

		if ( ! $get_parent instanceof WC_Order ) {
			return;
		}

		$get_parent = WFOCU_Core()->data->get_parent_order();

		$order_behavior = WFOCU_Core()->funnels->get_funnel_option( 'order_behavior' );

		$has_meta = $get_parent->get_meta( '_wfocu_status_schedule_for_cb' );
		/**
		 * If scheduled event not registered then do not proceed.
		 * it means this order does not require any pending mails to fore from upstroke side
		 * OR funnel is getting ended after mail sent for bacs and cheque
		 */
		if ( ! empty( $has_meta ) ) {
			if ( in_array( $get_parent->get_payment_method(), array( 'bacs', 'cheque' ), true ) ) {
				/**
				 * case 1: If batching is on and we have to send mail on start
				 */
				if ( 'batching' === $order_behavior && 'end' === WFOCU_Core()->data->get_option( 'send_processing_mail_on' ) ) {
					/**initiate payment gateways so that they could contribute in emails **/
					WC()->payment_gateways();
					WC()->mailer();

					/**
					 * For cased when status is on hold but not processing , bank and cheque
					 */
					do_action( 'woocommerce_gzd_order_confirmation', $get_parent );
					$get_parent->delete_meta_data( '_wfocu_status_schedule_for_cb' );
					$get_parent->save_meta_data();
				}

				return;
			}
		}

	}


	public function maybe_send_pending_emails_new_order( $new_order ) {
		do_action( 'woocommerce_gzd_order_confirmation', $new_order );

	}


}

WFOCU_Plugin_Compatibilities::register( new WFOCU_Compatibility_With_WC_Germanized(), 'wc_germanized' );
