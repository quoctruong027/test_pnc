<div class="wfacp-short-code-wrapper" v-if="'yes'==selected_template.show_shortcode">
    <div class="wfacp_fsetting_table_head wfacp-scodes-head wfacp_shotcode_tab_wrap">
        <div class="wfacp_clear_20"></div>
        <div class="wfacp-fsetting-header"><?php _e( 'Checkout Form', 'woofunnels-aero-checkout' ); ?></div>
        <div class="wfacp_clear_20"></div>
    </div>

    <!-----  NEW ADDED ACC TO DESIGN  ------->
    <div class=" wfacp_global_settings_wrap wfacp_page_col2_wrap wfacp_shortcodes_designs">
        <div class="wfacp_page_left_wrap" id="wfacp_global_setting_vue">
            <div class="wfacp_loader" style="display: none;"><span class="spinner"></span></div>
            <div class="wfacp-product-tabs-view-vertical wfacp-product-widget-tabs">
                <div class="wfacp-product-tabs-wrapper wfacp-tab-center">
                    <div class="wfacp_embed_form_tab wfacp-tab-title wfacp-tab-desktop-title wfacp-active" data-tab="1" role="tab" aria-controls="wfacp-shortcode-fieldset">
						<?php _e( 'Shortcode', 'wordpress' ) ?>
                    </div>

                </div>
                <div class="wfacp-product-widget-container">
                    <div class="wfacp-product-tabs wfacp-tabs-style-line" role="tablist">
                        <div class="wfacp-product-tabs-content-wrapper">
                            <div class="wfacp_global_setting_inner">
                                <div class="wfacp_vue_forms">
                                    <div class="vue-form-generator">                                        <!---->
                                        <fieldset class="wfacp_embed_fieldset wfacp-activeTab wfacp-shortcode-fieldset" style="display: block;">
                                            <legend><?php _e( 'Form Shortcode', 'wordpress' ) ?></legend>
                                            <div class="wfacp-scodes-row ">
                                                <div class="wfacp-scodes-value">
													<?php
													$wfacp_id = WFACP_Common::get_id();
													$url      = admin_url( 'post.php?post=' . $wfacp_id . '&action=edit' );
													$link     = "<a href='$url'>WordPress Editor</a>";
													?>
                                                    <div class="wfacp-scodes-value-in">
                                                        <div class="wfacp_description">
                                                            <input type="text" value="[wfacp_forms]" style="width:100%;" readonly>
                                                        </div>
                                                        <a href="javascript:void(0)" class="wfacp_copy_text"><?php _e( 'Copy' ); ?></a>
                                                    </div>
                                                    <p class="hint"><?php _e( 'Use this shortcode to embed the checkout form on this page. Switch to ' . $link . '.', 'woofunnels-aero-checkout' ) ?></p>
                                                </div>
                                            </div>

                                            <legend><?php _e( 'Embed Form Shortcode', 'woofunnel-aero-checkout' ) ?></legend>
                                            <div class="wfacp-scodes-row">
                                                <div class="wfacp-scodes-value">
                                                    <div class="wfacp-scodes-value-in">
                                                        <div class="wfacp_description">
                                                            <input type="text" value="[wfacp_forms id='<?php echo $wfacp_id ?>']" style="width:100%;" readonly>
                                                        </div>
                                                        <a href="javascript:void(0)" class="wfacp_copy_text"><?php _e( 'Copy' ); ?></a>
                                                    </div>
                                                    <p class="hint"><?php _e( 'Use this shortcode to embed the checkout form on other page(s).', 'woofunnels-aero-checkout' ) ?></p>
                                                </div>
                                            </div>

                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="wfacp_success_modal" style="display: none" id="modal-section-success_shortcodes6456" data-iziModal-icon="icon-home"></div>

