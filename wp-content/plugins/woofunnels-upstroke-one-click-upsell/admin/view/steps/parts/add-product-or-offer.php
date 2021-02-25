<div class="wfocu_p20">
    <div v-if="current_offer_id>0">

        <div class="wfocu_welcome_wrap" v-if="is_multi_product_allow?(true):(numberofproducts()==0)">
            <div class="wfocu_welcome_wrap_in">

                <div class="wfocu_first_product" v-if="numberofproducts()==0">
                    <div class="wfocu_welc_head">
                        <div class="wfocu_welc_icon"><img src="<?php echo WFOCU_PLUGIN_URL ?>/admin/assets/img/clap.png" alt="" title=""/></div>
                        <div class="wfocu_welc_title"> <?php _e( 'Add Product To This Offer', 'woofunnels-upstroke-one-click-upsell' ); ?>
                        </div>
                    </div>
                    <div class="wfocu_welc_text">
                        <p><?php _e( ' Add a product which is perfectly aligned with customer\'s main order. Greater the relevancy of offer, greater the chances of acceptance.', 'woofunnels-upstroke-one-click-upsell' ); ?></p>

                    </div>
                </div>
                <button type="button" class="wfocu_step wfocu_button_add wfocu_button_inline wfocu_modal_open wfocu_welc_btn" data-izimodal-open="#modal-add-product" data-iziModal-title="Create New Funnel Step" data-izimodal-transitionin="fadeInDown">
					<?php _e( '+ Add Product', 'woofunnels-upstroke-one-click-upsell' ); ?>
                </button>
            </div>
        </div>
    </div>
    <div v-else id="wfocu_funnel_steps_add_settings">
        <div class="wfocu_welcome_wrap">
            <div class="wfocu_welcome_wrap_in">
                <div class="wfocu_welc_head">
                    <div class="wfocu_welc_icon"><img src="<?php echo WFOCU_PLUGIN_URL ?>/admin/assets/img/clap.png" alt="" title=""/></div>
                    <div class="wfocu_welc_title"> <?php _e( 'Create Your First Upsell', 'woofunnels-upstroke-one-click-upsell' ); ?>
                    </div>
                </div>
                <div class="wfocu_welc_text">
                    <p><?php _e( 'Click on button below to create first upsell offer.', 'woofunnels-upstroke-one-click-upsell' ); ?></p>
                </div>
                <div class="wfocu_step wfocu_button_add wfocu_modal_open wfocu_welc_btn" data-izimodal-open="#modal-add-offer-step">
                     <?php echo __( 'Add First Offer', 'woofunnels-upstroke-one-click-upsell' ); ?>
                </div>
            </div>
        </div>
        <div class="wfocu_success_modal" style="display: none" id="modal-section_product_success" data-iziModal-icon="icon-home"></div>
    </div>
</div>