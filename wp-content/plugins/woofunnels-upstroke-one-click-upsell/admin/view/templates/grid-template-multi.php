<div v-if="template_group==`<?php echo $temp_group; ?>`" v-bind:class="current_template==`<?php echo $temp_slug; ?>`?`wfocu_template_box <?php echo $template_class; ?> wfocu_template_box_multi wfocu_selected_template`:`wfocu_template_box <?php echo $template_class; ?> wfocu_template_box_multi`" data-slug="<?php echo $temp_slug; ?>" class="<?php echo (!$has_preview)? 'wfocu_empty_template':'' ?>">

	<div class="wfocu_template_box_inner">


		<?php if ( $has_preview ) { ?>
            <div class="wfocu_overlay">
                <div class="wfocu_template_btm_strip wfocu_clearfix">
                    <div class="wfocu_template_name x"><?php echo $temp_name;
						echo ( 'mp-grid' === $temp_slug ) ? '<span class="wfocu-default">' . __( ' Default', 'woofunnels-upstroke-one-click-upsell' ) . '</span>' : ''; ?>
                    </div>
                    <div class="wfocu_template_button">
                        <a href="<?php echo $preview_url; ?>" class="wfocu_btn wfocu_step_btn wfocu_steps_btn_success" target="_blank"><?php esc_html_e( 'Preview', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
						<?php
						/**
						 * Import status
						 *  no: license key activation required to access this template
						 *  Yes: Import allowed
						 *  null: does not require import
						 */
						if ( 'no' === $import_status ) {
							?>
                            <a href="<?php echo admin_url( 'admin.php?page=woofunnels' ); ?>" target="_blank" class="button wfocu_design_btn_no_license">
                                <i class="dashicons dashicons-lock"></i> <?php esc_html_e( 'Activate License', 'woofunnels-upstroke-one-click-upsell' ); ?></a>

							<?php
						} elseif ( null !== $import_status ) { ?>
                            <a href="javascript:void(0)" v-bind:class="getButtonClass(`<?php echo $temp_slug; ?>`)" class="wfocu_btn wfocu_step_btn wfocu_steps_btn_green" v-on:click="set_template(`<?php echo $temp_slug; ?>`,false)"><span class="dashicons dashicons-update"></span> {{getButtonText(`<?php echo $import_status; ?>`,`<?php echo $temp_slug; ?>`)}}</a>
							<?php
						} else { ?>
                            <a href="javascript:void(0)" v-bind:class="getButtonClass(`<?php echo $temp_slug; ?>`)" class="wfocu_btn wfocu_step_btn wfocu_steps_btn_green" v-on:click="set_template(`<?php echo $temp_slug; ?>`)"><span class="dashicons dashicons-update"></span> {{getButtonText(`<?php echo $import_status; ?>`,`<?php echo $temp_slug; ?>`)}}</a>							<?php
						} ?>
                    </div>
                </div>
                <div class="wfocu_overlay_icon"><?php echo $overlay_icon; ?></div>
            </div>
            <div class="wfocu_template_thumbnail">
                <div class="wfocu_img_thumbnail ">
                    <img src="<?php echo $prev_thumbnail; ?>" alt="<?php echo $temp_name; ?>"/>
                </div>
            </div>
		<?php } else { ?>
            <div class="wfocu_template_img_cover">
                <div class="wfocu_template_thumbnail">
                    <div class="wfocu_img_thumbnail ">
                        <div class="wfocu_vertical_mid">
                            <div class="wfocu_add_tmp_se">
                                <a v-on:click="set_template(`<?php echo $temp_slug; ?>`)" href="javascript:void(0);" class="wfacp_steps_btn_add">
                                    <span>+</span>
                                </a>
                            </div>
                            <div class="wfocu_clear_20"></div>
                            <div class="wfocu_clear_20"></div>
                            <div v-on:click="set_template(`<?php echo $temp_slug; ?>`, false)" class="wfocu_p"><?php esc_html_e( 'Build from scratch', 'woofunnels-upstroke-one-click-upsell' ); ?></div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		} ?>

    </div>
</div>
<?php if ( false === $preview_url ) { ?>
    <div style="display: none" id="modal-prev-template_<?php echo $temp_slug; ?>" class="modal-prev-temp" data-iziModal-title="<?php echo $temp_name; ?>" data-iziModal-icon="icon-home">
        <div class="sections wfocu_img_preview" style="height: 254px;">
            <img src="<?php echo $prev_full; ?>" alt="<?php echo $temp_name; ?>"/>
        </div>
    </div>
<?php }

