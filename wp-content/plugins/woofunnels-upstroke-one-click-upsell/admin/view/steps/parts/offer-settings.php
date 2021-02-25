<div class="offer_step_setting" id="offer_settings" v-if="current_offer_id>0 && product_count>0">
    <div class="wfocu_p25" style="padding-bottom: 0px;">
        <p>
            <a href="https://buildwoofunnels.com/docs/upstroke/faqs/offer-price/?utm_source=wfocu-pro-documentation&utm_medium=text-click&utm_campaign=resource&utm_term=offer-pricing" target="_blank" style="font-style: italic;"><?php _e('Learn more about how we calculate offer prices', 'woofunnels-upstroke-one-click-upsell');?><span class="dashicons dashicons-external"></span></a>
        </p></div>
    <div class="wfocu_p25">
        <h2><?php echo __( 'Settings', 'woofunnels-upstroke-one-click-upsell' ); ?></h2>
        <hr/>
        <form class="wfocu_forms_wrap wfocu_forms_offer_settings" v-on:change="setting_changes">
            <fieldset>
                <vue-form-generator :schema="schema" :model="model" :options="formOptions"></vue-form-generator>
            </fieldset>
        </form>
    </div>
</div>