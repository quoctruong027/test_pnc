<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<script type="text/template" id="tmpl-bwfan-recovered-cart-popup">

    <div id="bwfan-abandoned-section " dir='<?php echo is_rtl() ? 'text-align:right' : 'text-align:left'; ?>'>
        <div class="bwfan-abandoned-addresses">

            <# if( _.size(data.billing) > 0 ){ #>
            <div class="bwfan-abandoned-address" style='<?php echo is_rtl() ? 'float:right;' : 'float:left;'; ?>'>
                <h2><?php esc_html_e( 'Billing Details', 'wp-marketing-automations' ); ?></h2>
                <div class="">
                    {{{data.billing}}}
                </div>
            </div>
            <# } #>

            <# if( _.size(data.shipping) > 0 ){ #>
            <div class="bwfan-abandoned-address" style='<?php echo is_rtl() ? 'float:right;' : 'float:left;'; ?>'>
                <h2><?php esc_html_e( 'Shipping Details', 'wp-marketing-automations' ); ?></h2>
                <div class="">
                    {{{data.shipping}}}
                </div>
            </div>
            <# } #>

            <# if( _.size(data.others) > 0 ){ #>
            <div class="bwfan-abandoned-address bwfan-others-sec">
                <h2><?php esc_html_e( 'Others', 'wp-marketing-automations' ); ?></h2>
                <ul>
                    <# _.each( data.others, function( value, key ){ if(_.isEmpty(value) == false) { #>
                    <li>{{key}} - {{value}}</li>
                    <# }
                    }) #>
                </ul>
            </div>
            <# } #>

        </div>

        <# if( _.size(data.products) > 0 ){ #>
        <table cellspacing="0" class="bwfan-abandoned-items-table" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
            <thead>
            <tr>
                <th style="<?php echo is_rtl() ? 'text-align:right;' : 'text-align:left;'; ?>"><?php esc_html_e( 'Cart Items', 'wp-marketing-automations' ); ?></th>
                <th style="<?php echo is_rtl() ? 'text-align:right;' : 'text-align:left;'; ?>"><?php esc_html_e( 'Quantity', 'wp-marketing-automations' ); ?></th>
                <th style="<?php echo is_rtl() ? 'text-align:right;' : 'text-align:left;'; ?>"><?php esc_html_e( 'SubTotal', 'wp-marketing-automations' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <# _.each( data.products, function( value ){ #>
            <tr>
                <td style="<?php echo is_rtl() ? 'text-align:right' : 'text-align:left'; ?>">{{{_.unescape(value.name)}}}</td>
                <td>{{value.qty}}</td>
                <td>{{data.currency}}{{value.price}}</td>
            </tr>
            <# }) #>
            </tbody>
            <tfoot>
            <# if(data.discount >0 ) {#>
            <tr>
                <td colspan="2">Discount:</td>
                <td>{{data.currency}}{{data.discount}} <?php echo ( bwfan_is_woocommerce_active() && wc_tax_enabled() ) ? esc_html( '(inc.tax)', 'wp-marketing-automations' ) : ''; ?></td>
            </tr>
            <# }#>
            <tr>
                <td colspan="2" style="<?php echo is_rtl() ? 'text-align:right;' : 'text-align:left;'; ?>">
                    Total:
                </td>
                <td>
                    {{data.currency}}{{data.total}}
                </td>
            </tr>
            </tfoot>
        </table>
        <# } #>
    </div>

</script>

<div class="bwfan_izimodal_default" style="display: none" id="modal-show-recovered-cart-details">
    <div class="sections bwfan-bg-white">
        <div id="bwfan-abandoned-section">

        </div>
    </div>
</div>
