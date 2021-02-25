<?php

/**
 * Developed by SomewhereWarm
 * Class WFACP_WOOCOMMERCE_Gift_Card
 */
class WFACP_WOOCOMMERCE_Gift_Card_Compatiblity {
	protected $gift_card_matches = [];
	protected $apply_gift_card = false;

	public function __construct() {
		add_action( 'wfacp_internal_css', [ $this, 'print_js' ] );
		add_filter( 'wfacp_apply_coupon_via_ajax', [ $this, 'stop_normal_coupon_apply' ], 10, 2 );
		add_action( 'wfacp_apply_coupon_via_ajax_placeholder', [ $this, 'apply_gift_card' ], 10, 2 );
	}

	public function stop_normal_coupon_apply( $status, $post ) {
		if ( ! function_exists( 'WC_GC' ) || ! class_exists( 'WC_GC_Gift_Card' ) ) {
			return $status;
		}
		$coupon_code = $post['coupon_code'];
		$match       = preg_match( apply_filters( 'woocommerce_gc_coupon_input_pattern', '/(?>[a-zA-Z0-9]{4}\-){3}[a-zA-Z0-9]{4}/', $coupon_code ), $coupon_code, $matches );
		if ( $match && ! empty( $matches ) ) {
			$status                  = false;
			$this->apply_gift_card   = true;
			$this->gift_card_matches = $matches;
		}

		return $status;
	}

	public function apply_gift_card() {
		if ( ! function_exists( 'WC_GC' ) || ! class_exists( 'WC_GC_Gift_Card' ) ) {
			return false;
		}

		if ( $this->apply_gift_card ) {
			$giftcard_code = array_pop( $this->gift_card_matches );
			$results       = WC_GC()->db->giftcards->query( array( 'return' => 'objects', 'code' => $giftcard_code, 'limit' => 1 ) );
			$giftcard_data = count( $results ) ? array_shift( $results ) : false;

			if ( $giftcard_data ) {

				$giftcard = new WC_GC_Gift_Card( $giftcard_data );

				try {

					// If logged in check if auto-redeem is on.
					if ( get_current_user_id() && apply_filters( 'woocommerce_gc_auto_redeem', false ) ) {
						$giftcard->redeem( get_current_user_id() );
					} else {
						WC_GC()->giftcards->apply_giftcard_to_session( $giftcard );
					}

					wc_add_notice( __( 'Gift Card code applied successfully!', 'woocommerce-gift-cards' ) );

				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}

			} else {
				wc_add_notice( __( 'Gift Card not found.', 'woocommerce-gift-cards' ), 'error' );
			}

		}
	}

	public function print_js() {
		if ( ! function_exists( 'WC_GC' ) ) {
			return false;
		}
		?>
        <script>
            window.addEventListener('load', function () {
                (function ($) {
                    if (typeof wc_gc_params != "object") {
                        return;
                    }
                    var get_url = function (endpoint) {

                        return wc_gc_params.wc_ajax_url.toString().replace(
                            '%%endpoint%%',
                            endpoint
                        );
                    };
                    $(document.body).on('click', '.wc_gc_remove_gift_card', function (e) {
                        e.preventDefault();

                        var $el = $(this), giftcard_id = $el.data('giftcard');
                        var parent = $el.parents('.shop_table');
                        parent.block({

                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });
                        $.ajax({
                            type: 'post',
                            url: get_url('remove_gift_card_from_session'),
                            data: 'wc_gc_cart_id=' + giftcard_id + '&render_cart_fragments=1&security=' + wc_gc_params.security_remove_card_nonce,
                            dataType: 'html',
                            complete: function () {
                                $(document.body).trigger('update_checkout');
                            }
                        });

                        return false;
                    });
                    $(document.body).on('click', '#wc_gc_cart_redeem_send', function (e) {
                        e.preventDefault();

                        var code = $('#wc_gc_cart_code').val();

                        if (!code) {
                            return false;
                        }
                        $(document.body).trigger('update_checkout');
                        return false;
                    });
                })(jQuery)
            });

        </script>
		<?php
	}
}

new WFACP_WOOCOMMERCE_Gift_Card_Compatiblity();