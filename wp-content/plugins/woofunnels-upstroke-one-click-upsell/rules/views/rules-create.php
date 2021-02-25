<?php
global $wfocu_is_rules_saved;
?>
<div id="wfocu_funnel_rule_add_settings" data-is_rules_saved="<?php echo ( "yes" === $wfocu_is_rules_saved ) ? "yes" : "no"; ?>">
    <div class="wfocu_welcome_wrap">
        <div class="wfocu_welcome_wrap_in">
            <div class="wfocu_welc_head">
                <div class="wfocu_welc_icon"><img src="<?php echo esc_url( WFOCU_PLUGIN_URL ) ?>/admin/assets/img/clap.png" alt="" title=""/></div>
                <div class="wfocu_welc_title"> <?php esc_html_e( 'You’re Ready To Go', 'woofunnels-upstroke-one-click-upsell' ); ?></div>
            </div>
            <div class="wfocu_welc_text">
                <p><?php esc_html_e( 'Setup rules to trigger this upsell funnel.', 'woofunnels-upstroke-one-click-upsell' ); ?></p></div>
            <button class="wfocu_funnel_rule_add_settings wfocu_btn_primary wfocu_btn"><?php esc_html_e( " Add Rules", 'woofunnels-upstroke-one-click-upsell' ); ?></button>
        </div>
    </div>
</div>