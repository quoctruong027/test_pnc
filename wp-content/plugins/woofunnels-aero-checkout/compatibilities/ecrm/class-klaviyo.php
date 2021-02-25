<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Klaviyo {
	public function __construct() {
		add_filter( 'wfacp_advanced_fields', [ $this, 'add_fields' ] );
		add_action( 'wfacp_internal_css', [ $this, 'actions' ] );
		add_filter( 'wfacp_checkout_data', [ $this, 'prepare_checkout_data' ], 10, 2 );
	}

	public function actions() {
		add_filter( 'woocommerce_form_field_args', [ $this, 'add_default_wfacp_styling' ], 10, 2 );
		add_action( 'wp_footer', [ $this, 'js_event' ], 100 );
	}

	public function add_fields( $field ) {
		if ( $this->is_enable() ) {
			$field['kl_newsletter_checkbox'] = [
				'type'          => 'checkbox',
				'default'       => true,
				'label'         => __( 'Klaviyo', 'woocommerce-klaviyo' ),
				'validate'      => [],
				'id'            => 'kl_newsletter_checkbox',
				'required'      => false,
				'wrapper_class' => [],
				'class'         => [ 'kl_newsletter_checkbox_field' ],
			];

		}

		return $field;
	}

	public function is_enable() {
		if ( class_exists( 'WooCommerceKlaviyo' ) ) {
			return true;
		}

		return false;
	}

	public function add_default_wfacp_styling( $args, $key ) {
		if ( $key == 'kl_newsletter_checkbox' && $this->is_enable() ) {
			$klaviyo_settings = get_option( 'klaviyo_settings' );
			if ( isset( $klaviyo_settings['klaviyo_newsletter_text'] ) && '' !== $klaviyo_settings['klaviyo_newsletter_text'] ) {
				$args['label'] = $klaviyo_settings['klaviyo_newsletter_text'];
			}
		}

		return $args;
	}

	/**
	 * @param $checkout_data
	 * @param $cart WC_Cart;
	 *
	 * @return mixed
	 */
	public function prepare_checkout_data( $checkout_data, $cart ) {

		if ( ! $this->is_enable() ) {
			return $checkout_data;
		}

		$items = $cart->get_cart_contents();
		if ( empty( $items ) ) {
			return $checkout_data;
		}
		$event_data = array(
			'$service' => 'woocommerce',
			'$value'   => $cart->total,
			'$extra'   => array(
				'Items'         => array(),
				'SubTotal'      => $cart->subtotal,
				'ShippingTotal' => $cart->shipping_total,
				'TaxTotal'      => $cart->tax_total,
				'GrandTotal'    => $cart->total,
			),
		);

		foreach ( $cart->get_cart() as $cart_item_key => $values ) {
			/**
			 * @var $product WC_Product;
			 */
			$product = $values['data'];

			$event_data['$extra']['Items'] [] = array(
				'Quantity'     => $values['quantity'],
				'ProductID'    => $product->get_id(),
				'Name'         => $product->get_title(),
				'URL'          => $product->get_permalink(),
				'Images'       => [
					[
						'URL' => wp_get_attachment_url( get_post_thumbnail_id( $product->get_id() ) ),
					],
				],
				'Categories'   => wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) ),
				'Description'  => $product->get_description(),
				'Variation'    => $values['variation'],
				'SubTotal'     => $values['line_subtotal'],
				'Total'        => $values['line_subtotal_tax'],
				'LineTotal'    => $values['line_total'],
				'Tax'          => $values['line_tax'],
				'TotalWithTax' => $values['line_total'] + $values['line_tax'],
			);
		}
		$checkout_data['klaviyo'] = $event_data;

		return $checkout_data;

	}


	public function js_event() {
		if ( ! $this->is_enable() ) {
			return;
		}
		?>
        <script>
            window.addEventListener('load', function () {
                (function ($) {

                    if (typeof WCK == "undefined" || _learnq == "undefined") {
                        return;
                    }

                    $(document.body).on('change', '#billing_email', function () {
                        if (typeof wfacp_storage.klaviyo != 'undefined') {
                            _learnq.push(["track", "$started_checkout", wfacp_storage.klaviyo])
                        }
                    });


                    $(document.body).on('wfacp_checkout_data', function (e, v) {
                        if (typeof v !== "object") {
                            return;
                        }
                        if (!v.hasOwnProperty('checkout')) {
                            return;
                        }

                        if (!v.checkout.hasOwnProperty('klaviyo')) {
                            return;
                        }
                        wfacp_storage.klaviyo = v.checkout.klaviyo;
                        _learnq.push(["track", "$started_checkout", v.checkout.klaviyo])
                    });
                })(jQuery);
            });
        </script>
		<?php

	}


}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Klaviyo(), 'klaviyo' );
