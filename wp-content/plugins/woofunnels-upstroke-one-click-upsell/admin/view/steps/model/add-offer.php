<div class="wfocu_izimodal_default" id="modal-add-offer-step">
	<div class="sections">
		<form class="wfocu_forms_wrap" data-wfoaction="add_offer" novalidate>
			<div class="wfocu_vue_forms" id="part3">
				<vue-form-generator :schema="schema" :model="model" :options="formOptions" ref="addOfferForm"></vue-form-generator>
			</div>
			<fieldset>
				<div class="wfocu_form_submit">
                    <input type="hidden" name="_nonce" value="<?php echo wp_create_nonce( 'wfocu_add_offer' ); ?>"/>
					<input type="submit" value="<?php _e( 'Add Offer', 'woofunnels-upstroke-one-click-upsell' ) ?>" class="wfocu_btn_primary wfocu_btn"/>
				</div>
			</fieldset>
		</form>
	</div>
</div>