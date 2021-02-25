<div class="wfocu_fsetting_table_head">
    <div class="wfocu_fsetting_table_head_in wfocu_clearfix">
        <div class="wfocu_fsetting_table_title">
            <div class="offer_state wfocu_toggle_btn" v-if="Object.keys(products).length>0">
                <input v-model="offer_state" name="offer_state" class="wfocu-tgl wfocu-tgl-ios" v-bind:id="'state'+current_offer_id" type="checkbox" v-on:change="update_offer_state($event)">
                <label class="wfocu-tgl-btn wfocu-tgl-btn-small" v-bind:for="'state'+current_offer_id"></label>
            </div>

			<?php echo __( 'Offer', 'woofunnels-upstroke-one-click-upsell' ); ?>: {{current_offer}}
            <a href="javacript:void()" data-izimodal-open="#modal-update-offer" data-iziModal-title="<?php _e( 'Update Offer', 'woofunnels-upstroke-one-click-upsell' ); ?>" data-izimodal-transitionin="fadeInDown"><i class="dashicons dashicons-edit"></i></a>
            <span class="wfocu-offer-id"><?php esc_html_e( '(ID: ' ); ?>{{current_offer_id}})</span>
        </div>
        <div class="offer_save_buttons wfocu_form_submit" v-if="Object.keys(products).length>0">
            <input type="submit" value="Save changes" name="submit" class="wfocu_save_btn_style" v-bind:data-offer_id="current_offer_id"/>
        </div>
    </div>
</div>