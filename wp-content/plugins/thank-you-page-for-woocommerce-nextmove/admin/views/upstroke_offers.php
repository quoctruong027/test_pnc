<?php

$upstroke_link = add_query_arg( array(
	'utm_source'   => 'nextmove-pro',
	'utm_medium'   => 'sidebar',
	'utm_campaign' => 'other-products',
	'utm_term'     => 'upstroke',
), 'https://buildwoofunnels.com/woocommerce-one-click-upsells-upstroke/' );

$available_payment_gateways = WC()->payment_gateways->payment_gateways();
if ( ! is_array( $available_payment_gateways ) || count( $available_payment_gateways ) === 0 ) {
	return;
}
$available_payment_gateways = array_keys( $available_payment_gateways );

$supported_gateways = array(
	'stripe',
	'paypal',
	'paypal_express',
	'ppec',
	'authorize_net_cim_credit_card',
	'bacs',
	'cheque',
	'cod',
	'braintree_credit_card',
	'braintree_paypal',
);

/** Addon gateways */
$supported_gateways = array_merge( $supported_gateways, array(
	'ebanx-credit-card-ar',
	'ebanx-credit-card-br',
	'ebanx-credit-card-co',
	'ebanx-credit-card-mx',
	'mollie_wc_gateway_bancontact',
	'mollie_wc_gateway_creditcard',
	'mollie_wc_gateway_ideal',
	'mollie_wc_gateway_sofort',
	'sagepaydirect',
	'omise',
	'nmi',
) );

$upstroke_supported_gateways = array_intersect( $available_payment_gateways, $supported_gateways );
if ( ! is_array( $upstroke_supported_gateways ) || count( $upstroke_supported_gateways ) === 0 ) {
	return;
}
?>
<h3>Want to Increase Average Order Value?</h3>
<div class="postbox xlwcty_side_content xlwcty_xlplugins xlwcty_xlplugins_upstroke">
    <a href="<?php echo $upstroke_link; ?>" target="_blank"></a>
    <img src="<?php echo plugin_dir_url( XLWCTY_PLUGIN_FILE ) . 'admin/assets/img/upstroke.png'; ?>">
    <div class="xlwcty_plugin_head">Use UpStroke: Post-Purchase One Click Upsell Funnels</div>
    <div class="xlwcty_plugin_desc">A proven tactic used by smart marketers to make more money from the same traffic.</div>
</div>
