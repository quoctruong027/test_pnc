<?php
global $woocommerce;

$aero_link = add_query_arg( array(
	'utm_source'   => 'finale-pro',
	'utm_medium'   => 'sidebar',
	'utm_campaign' => 'other-products',
	'utm_term'     => 'aerocheckout',
), 'https://buildwoofunnels.com/woocommerce-checkout-pages-aero/' );
?>
<h3>Want High Converting Checkout pages?</h3>
<div class="postbox wcct_side_content wcct_xlplugins wcct_xlplugins_aero">
    <a href="<?php echo $aero_link; ?>" target="_blank"></a>
    <img src="<?php echo plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/aero.png'; ?>">
    <div class="wcct_plugin_head">AeroCheckout: Highly optimized checkout pages that win trust & lock the sale.</div>
    <div class="wcct_plugin_desc">Use Aero's pre-built templates to roll out your checkout pages in mere minutes. Build, deploy & get ready to count lost dollars.</div>
</div>
