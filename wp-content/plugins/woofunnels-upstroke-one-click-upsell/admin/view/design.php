<?php
$allTemplates   = WFOCU_Core()->template_loader->get_templates();
$get_all_groups = WFOCU_Core()->template_loader->get_all_groups();
$offers         = WFOCU_Core()->funnels->get_funnel_offers_admin();

/** Moving custom at second last and divi at third last place **/
$custom_grp = $divi_grp = [];
$divi_found = $custom_found = false;
foreach ( $get_all_groups as $group_key => $temp_Group ) {
	if ( 'custom' === $group_key ) {
		$custom_grp = $temp_Group;
		unset( $get_all_groups[ $group_key ] );
		$custom_found = true;
		if ( $divi_found ) {
			break;
		}
	}
	if ( 'divi' === $group_key ) {
		$divi_grp = $temp_Group;
		unset( $get_all_groups[ $group_key ] );
		$divi_found = true;
		if ( $custom_found ) {
			break;
		}
	}
}
if ( $divi_found ) {
	$get_all_groups['divi'] = $divi_grp;
}
if ( $custom_found ) {
	$get_all_groups['custom'] = $custom_grp;
} ?>
    <div class="wfocu_wrap_l">
        <div class="wfocu_p15">
            <div class="wfocu_heading_l"><?php esc_html_e( 'Offers', 'woofunnels-upstroke-one-click-upsell' ); ?></div>
            <div class="wfocu_steps">
                <div class="wfocu_step">
					<?php esc_html_e( 'Checkout', 'woofunnels-upstroke-one-click-upsell' ); ?>
                    <span class="wfocu_down_arrow"></span>
                </div>
                <div class="wfocu_steps_sortable">
					<?php include __DIR__ . '/steps/offer-ladder.php'; ?>
                </div>
                <div class="wfocu_step">
					<?php esc_html_e( 'Thank You Page', 'woofunnels-upstroke-one-click-upsell' ); ?>
                    <span class="wfocu_up_arrow"></span>
                </div>
            </div>
        </div>
    </div>

<?php

if ( false === is_array( $offers['steps'] ) || ( is_array( $offers['steps'] ) && count( $offers['steps'] ) < 1 ) ) {
	$funnel_id      = $offers['id'];
	$section_url    = add_query_arg( array(
		'page'    => 'upstroke',
		'section' => 'offers',
		'edit'    => $funnel_id,
	), admin_url( 'admin.php' ) );
	$offer_page_url = $section_url;
	?>
    <div class="wfocu_wrap_r wfocu_no_offer_wrap_r">
        <div class="wfocu_no_offers_wrapper wfocu_p20">
            <div class="wfocu_welcome_wrap">
                <div class="wfocu_welcome_wrap_in">
                    <div class="wfocu_no_offers_notice">
                        <div class="wfocu_welc_head">
                            <div class="wfocu_welc_icon"><img src="<?php echo WFOCU_PLUGIN_URL ?>/admin/assets/img/clap.png" alt="" title=""/></div>
                            <div class="wfocu_welc_title"> <?php esc_html_e( 'No offers in Current Funnel', 'woofunnels-upstroke-one-click-upsell' ); ?>
                            </div>
                        </div>
                        <div class="wfocu_welc_text">
                            <p><?php esc_html_e( ' You have to create some offers and add products in there.', 'woofunnels-upstroke-one-click-upsell' ); ?></p>

                        </div>
                    </div>
                    <a href="<?php echo esc_url( $offer_page_url ) ?>" class="wfocu_step wfocu_button_add wfocu_button_inline  wfocu_welc_btn">
						<?php esc_html_e( 'Create Your First Offer', 'woofunnels-upstroke-one-click-upsell' ); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
	<?php
} else {
	?>
    <div v-bind:class="`single`===mode?`wfocu_mode_single`:`wfocu_mode_choice`" class="wfocu_wrap_r" id="wfocu_step_design">
        <div class="wfocu-loader"><img src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ); ?>"/></div>
        <div class="wfocu_template">
            <div class="wfocu_fsetting_table_head">
                <div class="wfocu_fsetting_table_head_in wfocu_clearfix">
                    <div class="wfocu_template_holder_head2" v-if="mode==`choice`&&!isEmpty(products)">
                        <div class="wfocu_offer_design_mode">
							<?php
							foreach ( $get_all_groups as $key => $template_group ) {
								?>
                                <a data-template="<?php echo $key; ?>" class="wfocu_design_btn" v-bind:class="template_group == `<?php echo $key; ?>`?` wfocu_btn_selected`:``" v-on:click="template_group = `<?php echo $key; ?>`"><?php echo $template_group->get_nice_name(); ?></a>
								<?php
							}
							?>
                            <a class="wfocu_design_btn" style="display:none;" v-bind:class="template_group == `custom_page`?` wfocu_btn_selected`:``" v-on:click="template_group = `custom_page`"><?php esc_html_e( 'Custom Page', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
                        </div>
                    </div>

                </div>
            </div>

            <div v-if="!isEmpty(products)" class="wfocu_template_box_holder">

                <div class="wfocu_fsetting_table_title"><?php echo sprintf( __( 'Customizing Design for Offer <strong>%s</strong>', 'woofunnels-upstroke-one-click-upsell' ), '{{getOfferNameByID()}}' ); ?>
                    <span class="wfocu_template_group" v-if="mode==`single`"><?php esc_html_e( 'using ', 'woofunnels-upstroke-one-click-upsell' ) ?> <strong>{{getTemplateGroupNiceName()}}</strong></span>
                    <span class="wfocu-offer-id"><?php esc_html_e( '(ID: ' ); ?>{{current_offer_id}})</span>
                </div>

                <div class="wfocu_template_preview" v-if="mode==`single`">
                    <div class="wfocu_tp_wrap">
                        <div class="wfocu_wrap_i ">
							<div class="wfocu_build_scratch" v-if="(current_template == 'wfocu-'+template_group+'-empty') || (current_template == 'custom-page') ">
								<div class="wfocu_temp_middle_align">
									<div class="wfocu_p" v-if="current_template == 'wfocu-'+template_group+'-empty'"><?php esc_html_e('Build from scratch','woofunnels-upstroke-one-click-upsell'); ?></div>
									<div class="wfocu_p" v-if="current_template == 'custom-page'"><?php esc_html_e('Link to custom page','woofunnels-upstroke-one-click-upsell'); ?></div>
								</div>
							</div>
							<div v-if="(current_template != 'wfocu-'+template_group+'-empty' && current_template != 'custom-page')">
								<img v-bind:src="getTemplateImage()">
							</div>
                        </div>
                        <div class="wfocu_wrap_g"></div>
                        <div class="wfocu_wrap_c">
                            <div>
                                <div><strong><?php esc_html_e( 'Current Template', 'woofunnels-upstroke-one-click-upsell' ) ?></strong>: {{getTemplateNiceName()}}</div>
                                <div class="wfocu_clear_20"></div>
                                <div class="wfocu_btns">
                                    <a href="javascript:void(0)" class="wfocu_btn wfocu_btn_primary" v-on:click="customize_template(current_template)"><?php esc_attr_e( 'Edit', 'woofunnels-upstroke-one-click-upsell' ) ?></a>
                                    <div class="wfocu_clear_10"></div>
                                    <a href="javascript:void(0)" class="wfocu_btn wfocu_steps_btn_dark_green" v-on:click="preview_template(current_template)"><?php esc_attr_e( 'Preview', 'woofunnels-upstroke-one-click-upsell' ) ?></a>

                                </div>
                                <div class="ftr">

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="wfocu_btns wfocu_last_step">
                        <a class="wfocu_link wfocu_blue_link" v-bind:href="get_edit_link()"><?php esc_html_e( 'Switch to WordPress Editor', 'woofunnels-aero-checkout' ) ?></a>

                        <a href="javascript:void(0)" class="wfocu_rm_template" v-on:click="remove_template()"><?php esc_html_e( 'Remove', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
                    </div>

                </div>

                <div class="wfocu_template_type_holder_in" v-if="mode==`choice`">
                    <div class="wfocu_single_template_list wfocu_template_list wfocu_clearfix" v-if="have_multiple_product==1 || have_multiple_product==0">
						<?php
						foreach ( $get_all_groups as $key => $template_group ) {
							$get_templates = $template_group->get_templates();
							foreach ( $get_templates as $temp ) {
								$template = WFOCU_Core()->template_loader->get_template( $temp );

								if ( 'customizer' === $key && ! empty( $template['is_multiple'] ) ) {
									continue;
								}

								$temp_name = isset( $template['name'] ) ? $template['name'] : '';

								$prev_thumbnail = ( isset( $template['thumbnail'] ) ) ? $template['thumbnail'] : '';
								$prev_full      = isset( $template['large_img'] ) ? $template['large_img'] : '';

								$import_status = null;
								if ( isset( $template['group'] ) && $template['group'] !== 'customizer' ) {
									$import_status = 'no';
									if ( true === $template['import_allowed'] ) {
										$import_status = 'yes';
									}
								}
								$temp_slug      = $temp;
								$temp_group     = $key;
								$overlay_icon   = '<i class="dashicons dashicons-visibility"></i>';
								$template_class = 'wfocu_temp_box_normal';
								$has_preview    = ( isset( $template['large_img'] ) || isset( $template['preview_url'] ) ) ? true : false;
								$preview_url    = ( isset( $template['preview_url'] ) && ! empty( $template['preview_url'] ) ) ? $template['preview_url'] : false;
								include plugin_dir_path( WFOCU_PLUGIN_FILE ) . 'admin/view/templates/grid-template.php';
							}
						}
						include plugin_dir_path( WFOCU_PLUGIN_FILE ) . 'admin/view/templates/grid-template-custom-page.php'; ?>
                    </div>
                </div>
                <div class="wfocu_template_type_holder_in" v-if="mode==`choice`">
                    <div class="wfocu_multiple_template_list wfocu_template_list wfocu_clearfix" v-if="have_multiple_product==2">
						<?php
						foreach ( $get_all_groups as $key => $template_group ) {

							$get_templates = $template_group->get_templates();

							foreach ( $get_templates as $temp ) {

								$template = WFOCU_Core()->template_loader->get_template( $temp );

								if ( 'customizer' === $key && empty( $template['is_multiple'] ) ) {
									continue;
								}
								$import_status = null;

								if ( isset( $template['group'] ) && $template['group'] !== 'customizer' ) {
									$import_status = 'no';
									if ( true === $template['import_allowed'] ) {
										$import_status = 'yes';
									}
								}

								$temp_name      = isset( $template['name'] ) ? $template['name'] : '';
								$prev_thumbnail = isset( $template['thumbnail'] ) ? $template['thumbnail'] : '';
								$prev_full      = isset( $template['large_img'] ) ? $template['large_img'] : '';
								$temp_slug      = $temp;
								$temp_group     = $key;
								$overlay_icon   = '<i class="dashicons dashicons-visibility"></i>';
								$template_class = 'wfocu_temp_box_normal';
								$has_preview    = ( isset( $template['large_img'] ) || isset( $template['preview_url'] ) ) ? true : false;

								$preview_url = ( isset( $template['preview_url'] ) && ! empty( $template['preview_url'] ) ) ? $template['preview_url'] : false;

								include plugin_dir_path( WFOCU_PLUGIN_FILE ) . 'admin/view/templates/grid-template-multi.php';
							}
						}
						include plugin_dir_path( WFOCU_PLUGIN_FILE ) . 'admin/view/templates/grid-template-custom-page.php'; ?>

                        <div class="wfocu_success_modal" style="display: none" id="modal-template_success" data-iziModal-icon="icon-home">
                            <div class="wfocu_success_modal" style="display: none" id="modal-template_clear" data-iziModal-icon="icon-home"></div>
                        </div>
                    </div>
                    <div class="ftr" v-if="mode==`choice` && current_template !== ``">
                        <a href="javascript:void(0)" v-on:click="mode = `single`">< Go back to selection</a>
                    </div>
                </div>
            </div>



            <!------HERE----->
            <!-- Fallback when we do not have any products to show -->
            <div v-if="isEmpty(products)" class="wfocu-scodes-wrap">
                <!--<div class="wfocu-scodes-head"><?php /*_e( 'This offer does not have any products.', 'woofunnels-upstroke-one-click-upsell' ); */ ?></div>-->

                <div class="wfocu_welcome_wrap" v-if="isEmpty(products)">
                    <div class="wfocu_welcome_wrap_in">

                        <div class="wfocu_first_product" v-if="isEmpty(products)">
                            <div class="wfocu_welc_head">
                                <div class="wfocu_welc_icon"><img src="<?php echo WFOCU_PLUGIN_URL ?>/admin/assets/img/clap.png" alt="" title=""/></div>
                                <div class="wfocu_welc_title"> <?php esc_html_e( 'Add Product To This Offer', 'woofunnels-upstroke-one-click-upsell' ); ?>
                                </div>
                            </div>
                            <div class="wfocu_welc_text">
                                <p><?php _e( ' Add a product which is perfectly aligned with customer\'s main order. Greater the relevancy of offer, greater the chances of acceptance.', 'woofunnels-upstroke-one-click-upsell' ); ?></p>

                            </div>
                        </div>
                        <button type="button" style="cursor: pointer;" class="wfocu_step wfocu_button_inline wfocu_welc_btn" v-on:click="window.location = '<?php echo esc_url( admin_url( 'admin.php?page=upstroke&section=offer&edit=' . $offers['id'] . ' ' ) ); ?>'">
							<?php esc_html_e( '+ Add Product', 'woofunnels-upstroke-one-click-upsell' ); ?>
                        </button>
                    </div>
                </div>

            </div>


            <!-- Show shortcodes in case of custom pages.  -->
            <div v-else-if="true == shouldShowShortcodeUI()" class="wfocu-scodes-wrap">
                <div class="wfocu-scodes-head">
					<?php esc_html_e( 'Shortcodes', 'woofunnels-upstroke-one-click-upsell' ); ?>

                </div>
                <div class="wfocu-scodes-desc">
	                <?php
	                echo sprintf( __( 'Using page builders to build custom upsell pages? <a href=%s target="_blank">Read this guide to learn more</a> about using Button widgets of your page builder <a href=%s target="_blank">Personalization shortcodes</a>', 'woofunnels-upstroke-one-click-upsell' ), esc_url( 'https://buildwoofunnels.com/docs/upstroke/design/custom-designed-one-click-upsell-pages/' ), esc_url( 'https://buildwoofunnels.com/docs/upstroke/design/custom-designs/#order-personalization-shortcodes' ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	                ?>

                </div>


                <div v-for="shortcode in shortcodes" class="wfocu-scodes-inner-wrap">
                    <div class="wfocu-scodes-list-wrap">
                        <div class="wfocu-scode-product-head"><?php _e( 'Product - ', 'woofunnels-upstroke-one-click-upsell' ); ?> {{shortcode.name}}</div>
                        <div class="wfocu-scodes-products">
                            <div v-for="key in shortcode.shortcodes" class="wfocu-scodes-row">

                                <div class="wfocu-scodes-label">{{key.label}}</div>
                                <div class="wfocu-scodes-value">
                                    <div class="wfocu-scodes-value-in">
                                        <span class="wfocu-scode-text"><input readonly type="text" v-bind:value="key.value"></span>
                                        <a href="javascript:void(0)" v-on:click="copy" class="wfocu_copy_text"><?php _e( 'Copy', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-------------------------------->


            <!-- Show saved template to chose from here. -->
			<?php
			$template_names = get_option( 'wfocu_template_names', [] );
			if ( count( $template_names ) > 0 ) { ?>

            <div v-if="!isEmpty(products) && template_group == 'customizer' && mode==`single`" class="wfocu-scodes-wrap preset-holder">
                <div class="wfocu-scodes-head">
					<?php _e( 'Your Saved Presets', 'woofunnels-upstroke-one-click-upsell' ); ?>
                </div>
                <div class="wfocu-scodes-desc"> <?php _e( 'Click on the button to apply preset to the selected template. This will modify the default settings of the template and load it with settings of preset.', 'woofunnels-upstroke-one-click-upsell' ) ?>

                </div>
                <div class="wfocu-scodes-inner-wrap">
                    <div class="wfocu-scodes-list-wrap">
                        <div class="wfocu-scode-product-head"><?php _e( 'Apply and Customize Saved Presets', 'woofunnels-upstroke-one-click-upsell' ); ?> </div>
                        <div class="wfocu-scodes-products">

							<?php
							foreach ( $template_names as $template_slug => $template ) { ?>
                                <div class="customize-inside-control-row wfocu_template_holder wfocu-scodes-row">
                                    <div class="wfocu-scodes-label"><?php echo $template['name']; ?></div>
                                    <div class="wfocu-scodes-value-in wfocu-preset-right">
                                        <span class="wfocu-ajax-apply-preset-loader wfocu_hide"><img src="<?php echo admin_url( 'images/spinner.gif' ); ?>"></span>
                                        <a href="javascript:void(0);" class="wfocu_apply_template button-primary" data-slug="<?php echo $template_slug ?>"><?php _e( 'Apply', 'woofunnels-upstroke-one-click-upsell' ) ?></a>
                                        <a href="javascript:void(0)" class="wfocu_customize_template button-primary" style="display: none;"><?php echo __( 'Applied', 'woofunnels-upstroke-one-click-upsell' ); ?></a>
                                    </div>
                                </div>
								<?php
							} ?>
                        </div>
                    </div>
                </div>
            </div>


        </div>

		<?php } ?>
    </div>
<?php }
