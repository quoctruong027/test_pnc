<?php
$design_url = add_query_arg( [ 'page' => 'wfacp', 'wfacp_id' => $this->wfacp_id, 'section' => 'design' ], admin_url( 'admin.php' ) );
?>
<div class="wfacp_welcome_card" v-if="isEmpty()">
    <div class="wfacp_clear_30"></div>
    <div class="wfacp_clear_30"></div>
    <div class="wfacp_welc_icon"><img src="<?php echo WFACP_PLUGIN_URL; ?>/admin/assets/img/clap.png" alt="" title=""/>
        <span class="wfacp_product_h wfacp_product_text_align"> <?php _e( 'Please Choose Design', 'woofunnels-aero-checkout' ) ?> </span>
    </div>

    <div class="wfacp_clear_10"></div>
    <div class="wfacp_product_p"><?php _e( 'To generate a checkout page add a product.<br>Tip: You can also mark this page as a Global Checkout when page is ready!', 'woofunnels-aero-checkout' ) ?></div>
    <div class="wfacp_clear_10"></div>
    <div class="wfacp_clear_30"></div>
    <div class="wfacp_btns_welcome_card">
        <a href="<?php echo $design_url ?>" class="wfacp_btn wfacp_btn_primary"> <?php _e( 'Choose Design', 'woofunnels-aero-checkout' ) ?> </a>
        <div class="wfacp_clear_20"></div>
        <div class="wfacp_clear_20"></div>
    </div>
</div>