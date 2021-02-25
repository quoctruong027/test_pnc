<div id="wfacp_optimization_container" class="wfacp_inner_setting_wrap">
    <div class="wfacp_p20_noside wfacp_box_size clearfix">
        <div class="wfacp_wrap_inner wfacp_wrap_inner_offers" style="margin-left: 0px;">
            <div class="wfacp_wrap_r">
                <form v-on:submit.prevent="save()" v-on:change="changed">
                    <div class="wfacp_fsetting_table_head">
                        <div class="wfacp_fsetting_table_head_in wfacp_clearfix">
                            <div class="wfacp_fsetting_table_title">
                                <strong><?php _e( 'Optimizations', 'woofunnels-aero-checkout' ); ?></strong>
                            </div>
                            <div class="bwf_ajax_save_buttons bwf_form_submit">
                                <span class="wfacp_spinner spinner"></span>
                                <button type="submit" class="wfacp_save_btn_style" style="margin: 0px;width: auto;"><?php _e( 'Save changes', 'woofunnels-aero-checkout' ); ?></button>
                            </div>
                        </div>
                    </div>

                    <div class="wfacp_settings_sections">
                        <vue-form-generator :schema="schema" :model="model" :options="formOptions"></vue-form-generator>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>