<div class="wfocu_wrap_l">
    <div class="wfocu_p15">
        <div class="wfocu_heading_l"><?php _e('Offers', 'woofunnels-upstroke-one-click-upsell'); ?></div>
        <div class="wfocu_steps">
            <div class="wfocu_step">
				<?php echo __( "Checkout",'woofunnels-upstroke-one-click-upsell' ); ?>
                <span class="wfocu_down_arrow"></span>
            </div>
            <div class="wfocu_steps_sortable">
				<?php include __DIR__ . '/steps/offer-ladder.php'; ?>
            </div>
            <div class="wfocu_step wfocu_button_add wfocu_modal_open" data-izimodal-open="#modal-add-offer-step">
                + <?php echo __( "Add New Offer",'woofunnels-upstroke-one-click-upsell' ); ?>
            </div>

            <div class="wfocu_step">
				<?php echo __( "Thank You Page",'woofunnels-upstroke-one-click-upsell' ); ?>
                <span class="wfocu_up_arrow"></span>
            </div>
        </div>
    </div>
</div>
<div class="wfocu_wrap_r">
	<?php include __DIR__ . "/steps/section-product.php"; ?>
    <div class="wfocu_p20" style="display: none;">
        <form class="wfocu_forms_wrap" data-wfoaction="save_funnel_description">
            <div class="wfocu_vue_forms" id="part1">
                <vue-form-generator :schema="schema" :model="model" :options="formOptions"></vue-form-generator>
            </div>
            <fieldset>
                <div class="wfocu_form_submit">
                    <input type="submit" class="wfocu_submit_btn_style"/>
                </div>
            </fieldset>
        </form>
        <div class="wfocu_clear_30"></div>

        <form class="wfocu_forms_wrap">
            <div class="wfocu_vue_forms" id="part5">
                <vue-form-generator :schema="schema" :model="model" :options="formOptions"></vue-form-generator>
            </div>
            <fieldset>
                <div class="wfocu_form_submit">
                    <input type="submit" class="wfocu_submit_btn_style"/>
                </div>
            </fieldset>
        </form>
    </div>

</div>