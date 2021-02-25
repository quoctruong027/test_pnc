<div class="wfacp_tab_container" v-if="'no'==template_active" style="display: block">

    <div class="wfacp_tabs_templates_part">
        <div class="wfacp_temp_anchor" v-for="(design_name,type) in design_types" v-if="wfacp.tools.ol(designs[type])>0" v-on:click="setTemplateType(type)" v-bind:data-select="(current_template_type==type)?'selected':''">
            <input type="radio" name="wfacp_tabs">
            <label> <span>{{design_name}}</span></label>
        </div>
    </div>

    <section id="wfacp_content1" class="wfacp_tab-content" style="display: block" v-for="(templates,type) in designs" v-if="(current_template_type==type) && (wfacp.tools.ol(templates)>0)">

        <div class="wfacp_filter_container" v-if="undefined!==wfacp_data.design.design_type_data[type]['filters']">
            <div v-for="(name,i) in wfacp_data.design.design_type_data[type]['filters']" :data-filter-type="i" v-bind:class="'wfacp_filter_container_inner'+(1==i?' wfacp_selected_filter':'')">
                <div class="wfacp_template_filters" v-html="name"></div>
            </div>
        </div>

        <div class="wfacp_pick_template">
            <div v-for="(template,slug) in templates" :data-slug="slug" :data-steps="template.no_steps" class="wfacp_temp_card wfacp_single_template">

                <div class="wfacp_template_sec wfacp_build_from_scratch" v-if="template.build_from_scratch">
                    <div class="wfacp_template_sec_design">
                        <div class="wfacp_temp_overlay">
                            <div class="wfacp_temp_middle_align">
                                <div class="wfacp_add_tmp_se">
                                    <a href="javascript:void(0)" class="wfacp_steps_btn_add" v-on:click="triggerImport(template,slug,type)"><?php _e( '<span>+</span>', 'woofunnels-aero-checkout' ) ?></a>
                                </div>
                                <div class="wfacp_clear_30"></div>
                                <div class="wfacp_clear_10"></div>
                                <div class="wfacp_p" v-on:click="triggerImport(template,slug,type)"><?php _e( 'Build from scratch', 'woofunnels-aero-checkout' ); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wfacp_template_sec" v-else>
<!--                    <div class="wfacp_template_sec_ribbon wfacp_pro" v-if="`yes`===template.pro">--><?php //_e( 'PRO', 'woofunnels-aero-checkout' ); ?><!--</div>-->
                    <div>  <!-- USE THIS CLASS FOR PRO   and Use This Template btn will be Get Pro -->
                        <img v-bind:src="template.thumbnail" class="wfacp_img_temp">
                        <div class="wfacp_temp_overlay">
                            <div class="wfacp_temp_middle_align">


                                <div class="wfacp_pro_template" v-if="template.pro && `no` === template.license_exist">
                                    <a class="wfacp_btn_white wfacp_display_block">{{template.name}}</a>
                                    <a v-bind:href="template.preview_url" target="_blank" class="wfacp_steps_btn wfacp_steps_btn_success"><?php _e( 'Preview', 'woofunnels-aero-checkout' ) ?></a>
                                    <a href="javascript:void(0)" v-if="`yes` === template.import" class="wfacp_steps_btn wfacp_steps_btn_danger"><?php _e( 'Get PRO', 'woofunnels-aero-checkout' ) ?></a>
                                    <a href="javascript:void(0)" v-else class="wfacp_steps_btn  wfacp_steps_btn_green" v-on:click="triggerImport(template,slug,type)"><?php _e( 'Apply', 'woofunnels-aero-checkout' ) ?></a>
                                </div>
                                <div class="wfacp_pro_template" v-else>
                                    <a class="wfacp_btn_white wfacp_display_block">{{template.name}}</a>
                                    <a v-bind:href="template.preview_url" target="_blank" class="wfacp_steps_btn wfacp_steps_btn_success"><?php _e( 'Preview', 'woofunnels-aero-checkout' ) ?></a>
                                    <a href="javascript:void(0)" v-if="`yes` === template.import" class="wfacp_steps_btn  wfacp_steps_btn_green" v-on:click="triggerImport(template,slug,type)"><?php _e( 'Import', 'woofunnels-aero-checkout' ) ?></a>
                                    <a href="javascript:void(0)" v-else class="wfacp_steps_btn  wfacp_steps_btn_green" v-on:click="triggerImport(template,slug,type)"><?php _e( 'Apply', 'woofunnels-aero-checkout' ) ?></a>
                                </div>

                            </div>

                        </div>


                    </div>
                </div>
            </div>
        </div>
</div>
<div class="wfacp_clear_20"></div>
<div class="wfacp_clear_20"></div>
</section>
</div>
