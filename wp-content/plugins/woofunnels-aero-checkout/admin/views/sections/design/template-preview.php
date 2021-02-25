<?php
$admin_instance = WFACP_admin::get_instance();

$preview_url = get_the_permalink( $wfacp_id );
if ( empty( WFACP_Common::get_page_product( WFACP_Common::get_id() ) ) ) {
	$preview_url = add_query_arg( [ 'wfacp_preview' => true ], $preview_url );
}
?>
<div class="wfacp_template_preview_container">
    <div class="wfacp_form_templates_outer" v-if="'yes'==template_active">
        <div class="wfacp_heading_choosen_template">
            <div class="wfacp_clear_20"></div>
			<?php _e( 'Customizing', 'wordpress' ) ?> <b class="wfacp_page_title">{{get_page_title()}}</b> <?php _e( 'using', 'wordpress' ) ?> <b>{{wfacp_data.design.design_types[selected_type]}}</b>
            <div class="wfacp_clear_20"></div>
        </div>
        <div class="wfacp_clear_30"></div>
        <div class="wfacp_clear_20"></div>
        <div class="wfacp_templates_inner wfacp_selected_designed">
            <div class="wfacp_templates_design" v-if="'yes'==selected_template.build_from_scratch">
                <div class="wfacp_temp_card">
                    <div class="wfacp_template_sec wfacp_build_from_scratch">
                        <div class="wfacp_template_sec_design">
                            <div class="wfacp_temp_overlay">
                                <div class="wfacp_temp_middle_align">
                                    <div class="wfacp_p">{{selected_template.name}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wfacp_templates_design wfacp_center_align" v-else="">
                <div class="wfacp_img" style="position: relative">
                    <div class="wfacp_template_importing_loader" style="display: none">
                        <span class="spinner"></span>
                    </div>
                    <div>
                        <img v-bind:src="selected_template.thumbnail">
                    </div>
                </div>
            </div>
            <div class="wfacp_templates_action wfacp_center_align">
                <div class="wfacp_clear_5"></div>
                <div class="wfacp_temp"><b><?php _e( 'Template', 'woofunnels-aero-checkout' ) ?>: </b> {{selected_template.name}}
                    ({{wfacp_data.layout.current_step=='third_step'?wfacp_localization.design.preview_step.third_step:(wfacp_data.layout.current_step=='two_step'?wfacp_localization.design.preview_step.two_step:wfacp_localization.design.preview_step.single_step)}})
                </div>
                <div class="wfacp_clear_20"></div>
                <a v-if="'embed_forms'!==selected_template.template_type" target="_blank" class="wfacp_btn wfacp_btn_primary" v-bind:href="get_edit_link()"><?php _e( 'Edit', 'woofunnels-aero-checkout' ) ?></a>
                <a target="_blank" class="wfacp_btn wfacp_btn_primary" v-else-if="'yes'==selected_template.show_shortcode" v-bind:href="wfacp_data.template_edit_url.embed_forms.url"><?php _e( 'Edit', 'woofunnels-aero-checkout' ) ?></a>
                <div class="wfacp_clear_10"></div>
                <a target="_blank" href="<?php echo $preview_url; ?>" class="wfacp_btn wfacp_btn_success"><?php _e( 'Preview', 'woofunnels-aero-checkout' ) ?></a>
            </div>
            <div class="wfacp_clear_30"></div>

        </div>
        <div class="clear"></div>

        <div class="wfacp_template_bottom wfacp_temp_link_bottom_wrap">
            <div class="wfacp_clear_20"></div>
            <div class="wfacp_edit_post_links">
                <a class="wfacp_link wfacp_blue_link" target="_blank" href=" <?php echo admin_url( 'post.php?post=' . $wfacp_id . '&action=edit' ); ?>"><?php _e( 'Switch to WordPress Editor', 'woofunnels-aero-checkout' ) ?></a>
            </div>
            <div class="wfacp_template_links">

                <a href="javascript:void(0)" class="wfacp_link wfacp_red_link" v-on:click="get_remove_template()"><?php _e( 'Remove', 'woofunnels-aero-checkout' ) ?></a>


            </div>
            <div class="wfacp_clear_20"></div>
        </div>
		<?php
		do_action( 'wfacp_builder_design_after_template' );
		?>

    </div>
</div>
