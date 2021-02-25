<?php
if ( ! defined( 'WFACP_TEMPLATE_DIR' ) ) {
	return '';
}
$instance     = wfacp_template();
$current_open = $instance->get_current_open_step();
$count        = $instance->get_step_count();

echo '<style>';

if ( $template_type == 'elementor' && $template_type != 'elementor-minimalist-step-2' ) {
	?>
    body.wfacpef_page #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-section.wfacp_order_coupon_box,
    body.wfacpef_page #wfacp-e-form .wfacp_main_form.woocommerce .wfacp-section.wfacp_order_summary_box {
    margin-bottom: 0;
    }

	<?php
}
if ( $count > 1 ) {
	?>
    .wfacp_page.<?php echo $template_type; ?> {
    display: none;
    }
	<?php
}
?>
    html {
    overflow: auto !important;
    }

    span.wfacp_input_error_msg {
    color: red;
    font-size: 13px;
    }

    .wfacp_custom_field_multiselect select {
<?php
if ( ! WFACP_Common::is_theme_builder() ) {
	echo 'visibility: hidden;';
}
?>
    }

<?php

if ( 'single_step' !== $current_open ) {
	?>
    .wfacp_page.<?php echo $current_open ?> {
    display: block;
    }

	<?php
} else {
	?>
    .wfacp_page.single_step {
    display: block;
    }

	<?php
}
?>

    .wfacp_payment {
    display: block;

    }

    .wfacp_payment.wfacp_hide_payment_part {
    visibility: hidden;
    position: fixed;
    z-index: -600;
    left: -200%;
    }

    .wfacp_payment.wfacp_show_payment_part {
    visibility: visible;
    }

    .wfacp_error_message {
    display: none !important;
    clear: both;
    padding: 0 45px;
    color: red;
    }

    .wfacp_page.<?php echo $current_step; ?> .wfacp_payment {
    display: block;
    }

    .wfacp_page.<?php echo $current_step; ?> .wfacp_next_page_button {
    display: none;
    }

    p#shipping_same_as_billing_field .optional {
    display: none;
    }

    p#billing_same_as_shipping_field .optional {
    display: none;
    }

    .wfacp_shipping_fields.wfacp_shipping_field_hide {
    display: none !important;
    }

    .wfacp_billing_fields.wfacp_billing_field_hide {
    display: none !important;
    }


    span.wfacp_required_field_message {
    display: none;
    }

    .woocommerce-invalid-required-field span.wfacp_required_field_message {
    display: inline;
    }

    .wfacp_country_field_hide {
    display: none !important;
    }

    .wfacp_main_form .wfacp_shipping_table tr.shipping.wfacp_single_methods td.wfacp_shipping_package_name > p {
    padding: 0 0 10px;
    }

    .wfacp_main_form .wfacp_shipping_table tr.shipping.wfacp_single_methods td.wfacp_shipping_package_name {
    padding: 0 0 15px;
    }

<?php

if ( WFACP_Common::is_cart_is_virtual() ) {
	?>
    #shipping_same_as_billing_field {
    display: none;
    }
	<?php
}

$aero_version = WFACP_Common::get_checkout_page_version();
$asterisk     = get_option( 'woocommerce_checkout_highlight_required_fields', 'yes' );

if ( version_compare( $aero_version, '1.9.3.2', '<=' ) || wc_string_to_bool( $asterisk ) ) {
	echo "body .wfacp_main_form.woocommerce label.wfacp-form-control-label abbr.required{ display: inline;}";
}

if ( WFACP_Core()->pay->is_order_pay() ) {
	?>
    body.wfacp_main_wrapper.woocommerce-order-pay header.wfacp-header.wfacp_header {
    margin-bottom: 50px !important;
    }
	<?php
}
echo '</style>';
do_action( 'wfacp_internal_css', $selected_template_slug );
