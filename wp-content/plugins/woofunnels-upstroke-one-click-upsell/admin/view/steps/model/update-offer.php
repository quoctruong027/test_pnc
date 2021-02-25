<div class="wfocu_izimodal_default" id="modal-update-offer">
    <div class="sections">
	<?php $offer_base_url =  WFOCU_Common::get_offer_base_url() ; ?>
	<style>
	    #modal-update-offer .wfocu_step_slug .wrapper:before {
		content: '<?php echo $offer_base_url?>/';
	    }
	</style>
        <form class="wfocu_forms_wrap" data-wfoaction="update_offer" novalidate>
        <div class="wfocu_vue_forms" id="part4">
            <vue-form-generator :schema="schema" :model="model" :options="formOptions"></vue-form-generator>
        </div>
        <fieldset>
            <div class="wfocu_form_submit">
                <input type="hidden" name="_nonce" value="<?php echo wp_create_nonce( 'wfocu_update_offer' ); ?>"/>
                <input type="submit" value="<?php _e( 'Update Offer', 'woofunnels-upstroke-one-click-upsell' ) ?>" class="wfocu_btn_primary wfocu_btn"/>
                <div class="wfocu_clear_10"></div>
            </div>
        </fieldset>
        </form>


    </div>
</div>