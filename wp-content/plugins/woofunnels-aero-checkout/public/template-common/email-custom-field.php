<?php
/**
 * Created by PhpStorm.
 * User: sandeep
 * Date: 3/22/19
 * Time: 12:03 PM
 */
if ( ! defined( 'WFACP_TEMPLATE_DIR' ) ) {
	return '';
}
/**
 * @var $order WC_Order;
 */


$wfacp_id = $order->get_meta( '_wfacp_post_id' );
if ( empty( $wfacp_id ) || $wfacp_id == 0 ) {
	return;
}
$custom_field = WFACP_Common::get_checkout_fields( $wfacp_id );
if ( empty( $custom_field ) || ! isset( $custom_field['advanced'] ) || empty( $custom_field['advanced'] ) ) {
	return;
}
$html = '';
foreach ( $custom_field['advanced'] as $key => $field ) {

	$show_field = false;
	if ( isset( $field['show_custom_field_at_email'] ) && wc_string_to_bool( $field['show_custom_field_at_email'] ) ) {
		$show_field = true;
	}
	if ( false == $show_field ) {
		continue;
	}
	$meta_value = $order->get_meta( $key );
	if ( '' !== $meta_value || apply_filters( 'wfacp_print_blank_custom_field', false, $order, $wfacp_id ) ) {
		if ( is_array( $meta_value ) && count( $meta_value ) > 0 ) {
			$meta_value = implode( ',', $meta_value );
		}

		$html .= sprintf( '<tr class="woocommerce-table__line-item order_item"><td class="product-name"><b>%s</b></td><td class="product-total">%s</td>', esc_js( $field['label'] ), esc_js( $meta_value ) );
	}
}

if ( '' !== $html ) {
	?>
    <div style="margin-bottom: 40px;">
        <table class="woocommerce-table woocommerce-table--order-details shop_table order_details" style="width: 100%;">
            <tr>
                <td style="color: #69696a;border: 1px solid #e5e5e5;vertical-align: middle;text-align: left;padding: 0px;">
                    <table style="width: 100%;">
						<?php echo $html ?>
                    </table>

                </td>
            </tr>
        </table>
    </div>
	<?php
}
?>