<?php

class WFACP_handle_billing_address {

	private $temp_field = [
		'first_name',
		'last_name',
		'address_1',
		'address_2',
		'city',
		'postcode',
		'state',
		'house_number',
		'street_name',
		'house_number_suffix',
	];
	private $add_fields = [
		'address_1',
		'address_2',
		'city',
		'postcode',
		'state',
	];

	public function __construct() {
		add_action( 'wfacp_outside_header', [ $this, 'attach_hooks' ] );
	}

	public function attach_hooks() {
		if ( ! WFACP_Core()->pay->is_order_pay() ) {
			add_action( 'woocommerce_checkout_after_customer_details', [ $this, 'print_billing_fields' ] );
			add_action( 'wp_footer', [ $this, 'enable_js' ], 256 );
		}
	}

	public function print_billing_fields() {
		$instance = wfacp_template();
		$fields   = $instance->get_checkout_fields();

		if ( false == $instance->have_billing_address() && $instance->have_shipping_address() ) {
			$address_prefix = 'billing_';
			if ( isset( $fields['billing']['billing_first_name'] ) ) {
				unset( $this->temp_field[0] );
			}
			if ( isset( $fields['billing']['billing_last_name'] ) ) {
				unset( $this->temp_field[1] );
			}
			foreach ( $this->temp_field as $item ) {
				echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', $address_prefix . $item, $address_prefix . $item );
				echo "\n";
			}
		} elseif ( $instance->have_shipping_address() && $instance->have_billing_address() ) {

			if ( isset( $fields['shipping']['shipping_first_name'] ) && ! isset( $fields['billing']['billing_first_name'] ) ) {
				echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', 'billing_first_name', 'billing_first_name' );
				echo "\n";
			}
			if ( isset( $fields['shipping']['shipping_last_name'] ) && ! isset( $fields['billing']['billing_last_name'] ) ) {
				echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', 'billing_last_name', 'billing_last_name' );
				echo "\n";
			}
			if ( isset( $fields['billing']['billing_first_name'] ) && ! isset( $fields['shipping']['shipping_first_name'] ) && ! isset( $fields['advanced']['shipping_first_name'] ) ) {
				echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', 'shipping_first_name', 'shipping_first_name' );
				echo "\n";
			}
			if ( isset( $fields['billing']['billing_last_name'] ) && ! isset( $fields['shipping']['shipping_last_name'] ) && ! isset( $fields['advanced']['shipping_last_name'] ) ) {
				echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', 'shipping_last_name', 'shipping_last_name' );
				echo "\n";
			}

			foreach ( $this->add_fields as $key ) {
				$b_key = 'billing_' . $key;
				$s_key = 'shipping_' . $key;
				if ( ! isset( $fields['billing'][ $b_key ] ) ) {
					echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', $b_key, $b_key );
					echo "\n";
				}
				if ( ! isset( $fields['shipping'][ $s_key ] ) ) {
					echo sprintf( '<input type="hidden" name="%s" id="%s" class="wfacp_hidden_fields">', $s_key, $s_key );
					echo "\n";
				}
			}
		}
	}

	public function enable_js() {
		?>
        <script>
            (function ($) {
                var fields = {
                    'shipping_first_name': 'billing_first_name',
                    'shipping_last_name': 'billing_last_name',
                    'shipping_address_1': 'billing_address_1',
                    'shipping_address_2': 'billing_address_2',
                    'shipping_city': 'billing_city',
                    'shipping_postcode': 'billing_postcode',
                    'shipping_country': 'billing_country',
                    'shipping_state': 'billing_state',
                    'billing_first_name': 'shipping_first_name',
                    'billing_last_name': 'shipping_last_name',
                    'shipping_house_number': 'billing_house_number',
                    'shipping_street_name': 'billing_street_name',
                    'shipping_house_number_suffix': 'billing_house_number_suffix',
                    //'billing_country': 'shipping_country',
                };

                function refill() {
                    for (var i in fields) {
                        setTimeout(function (i) {
                            fillers_field($('#' + i));
                        }, 100, i);
                    }
                }

                function unset() {
                    for (var i in fields) {
                        if ('billing_country' == fields[i] || 'billing_state' == fields[i]) {
                            continue;
                        }
                        var b = $('#' + fields[i]);
                        if (true == b.is(":visible")) {
                            continue;
                        }
                        if (b.length > 0) {
                            b.val('');
                        }
                    }
                }

                $('#billing_same_as_shipping').on('change', function () {
                    if ($(this).is(':checked')) {
                        //unset();
                    } else {
                        refill();
                    }
                });


                function fillers_field(el) {
                    var id = el.attr('id');
                    var filler = $('#' + fields[id]);
                    if (filler.length === 0) {
                        return;
                    }

                    var filler_field_type = filler.attr('type');
                    var field_value = el.val();
                    //Means Filler Input not a hidden
                    if (filler.parents('.wfacp_divider_field.wfacp_divider_billing').length > 0) {
                        if ($('#billing_same_as_shipping').length > 0 && $('#billing_same_as_shipping:checked').length === 0) {
                            filler.val(field_value);
                        }
                        return;
                    }
                    if (filler.parents('.wfacp_divider_field.wfacp_divider_shipping').length > 0) {
                        if ($('#shipping_same_as_billing').length > 0 && $('#shipping_same_as_billing:checked').length === 0) {
                            filler.val(field_value);
                        }
                        return;
                    }
                    if ('text' == filler_field_type) {
                        return;
                    }
                    if (false === filler.is(':visible')) {
                        filler.val(field_value);
                    }
                }

                for (var i in fields) {
                    $('#' + i).on('change', function () {
                        fillers_field($(this));
                    });
                }
                $(window).on('load', function () {
                    refill();
                });
                $(document.body).on('wfacp_gmap_address_selected', function () {
                    refill();
                });
            })(jQuery);
        </script>
		<?php
	}
}

new WFACP_handle_billing_address();

