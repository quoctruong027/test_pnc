<div class="wfocu_funnel_setting" id="wfocu_funnel_setting_vue">
    <div class="wfocu_funnel_setting_inner">
<!--        <div class="wfocu_fsetting_table_head">
            <div class="wfocu_fsetting_table_head_in wfocu_clearfix">
                <div class="wfocu_fsetting_table_title "><?php echo __( 'Settings', 'woofunnels-upstroke-one-click-upsell' ); ?>
                </div>
                <div class="setting_save_buttons wfocu_form_submit">
                    <span class="wfocu_save_funnel_setting_ajax_loader spinner"></span>
                    <button v-on:click.self="onSubmit" class="wfocu_save_btn_style"><?php _e( 'Save changes', 'woofunnels-upstroke-one-click-upsell' ) ?></button>
                </div>
            </div>
        </div>-->
        <div class="wfocu-tabs-view-vertical wfocu-widget-tabs">
            <div class="wfocu-tabs-wrapper wfocu-tabs-style-line wfocu-funnel-setting-tabs" role="tablist">
				<div class="wfocu-tab-title wfocu-tab-behav-title basic_tab wfocu-active" data-tab="1" role="tab" aria-controls="wfocu-tab-content-basic">
		            <?php _e( 'Behavioural', 'woofunnels-upstroke-one-click-upsell' ); ?>
				</div>
	            <div class="wfocu-tab-title wfocu-tab-priority-title basic_tab" data-tab="2" role="tab" aria-controls="wfocu-tab-content-basic">
		            <?php _e( 'Priority', 'woofunnels-upstroke-one-click-upsell' ); ?>
	            </div>
	            <div class="wfocu-tab-title wfocu-tab-prices-title basic_tab" data-tab="3" role="tab" aria-controls="wfocu-tab-content-basic">
		            <?php _e( 'Prices', 'woofunnels-upstroke-one-click-upsell' ); ?>
	            </div>
	            <div class="wfocu-tab-title wfocu-tab-messages-title basic_tab" data-tab="4" role="tab" aria-controls="wfocu-tab-content-basic">
		            <?php _e( 'Confirmation Messages', 'woofunnels-upstroke-one-click-upsell' ); ?>
	            </div>
	            <div class="wfocu-tab-title wfocu-tab-external-title basic_tab" data-tab="5" role="tab" aria-controls="wfocu-tab-content-basic">
		            <?php _e( 'External Tracking Code', 'woofunnels-upstroke-one-click-upsell' ); ?>
	            </div>

				<div class="wfocu-tab-title class_hide_btn wfocu-tab-setting-title advanced " id="tab-title-advanced" data-tab="6" role="tab" aria-controls="wfocu-tab-content-description">
					<?php _e( 'Thank You Page', 'woofunnels-upstroke-one-click-upsell' ); ?>
				</div>
            </div>

            <!--	ADD CLASS "wfocu_hr_gap" to class form-group to add separator-->
            <div class="wfocu-tabs-content-wrapper wfocu-funnel-setting-tabs-content">
	                <div class="wfocu_forms_fields_settings">
		                <div class="wfocu_forms_conatiner">
							<form class="wfocu_forms_wrap wfocu_forms_global_settings">
								<fieldset class="fieldsets">
									<vue-form-generator :schema="schema" :model="model" :options="formOptions">
									</vue-form-generator>
								</fieldset>
							</form>
			                <div class="wfocu-tabs-content-btn wfocu_form_submit wfocu_btm_grey_area wfocu_clearfix">
				                <div class="wfocu_btm_save_wrap wfocu_clearfix" style="display:none">
					                <button v-on:click.self="onSubmit" class="wfocu_save_btn_style"><?php _e( 'Save changes', 'woofunnels-upstroke-one-click-upsell' ) ?></button>
					                <span class="wfocu_save_funnel_setting_ajax_loader spinner"></span>
				                </div>
			                </div>
		                </div>
		                <fieldset class="fieldsets">
			                <fieldset class="wfocu_hide">
				                <div class="wfocu-content-tab wfocu-funnel-advanced-settings ">
					                <div class="advanced_settings">
						                <?php
						                $show        = 0;
						                $plugin_slug = 'woo-thank-you-page-nextmove-lite';

						                if ( true === file_exists( WP_PLUGIN_DIR . '/thank-you-page-for-woocommerce-nextmove/woocommerce-thankyou-pages.php' ) ) {
							                $plugin_slug = 'thank-you-page-for-woocommerce-nextmove';
						                }

						                $plugin_url = wp_nonce_url( add_query_arg( array(
							                'action' => 'install-plugin',
							                'plugin' => $plugin_slug,
							                'from'   => 'import',
						                ), self_admin_url( 'update.php' ) ), 'install-plugin_' . $plugin_slug );
						                ?>
						                <div class="wfocu_install_nextmove_wrap">
							                <div class="wfocu_setup_nextmove" v-if="nextMoveState == `ready_to_install` || nextMoveState == `ready_to_activate`">
								                <h3><?php _e( 'Setup Custom Thank you Page', 'woofunnels-upstroke-one-click-upsell' ) ?></h3>
								                <p><?php _e( 'Install NextMove to create custom thank you pages and trigger them based on specific Rules.', 'woofunnels-upstroke-one-click-upsell' ) ?></p>
								                <p><?php _e( '<a target="_blank" href="https://wordpress.org/plugins/woo-thank-you-page-nextmove-lite/">Learn More about NextMove</a>', 'woofunnels-upstroke-one-click-upsell' ) ?></p>

								                <button v-if="nextMoveState == `ready_to_install`" v-on:click="wfocu_next_move_process($event)" href="javascript:void(0)" data-slug="<?php echo $plugin_slug; ?>" class="install-now wfocu_btn_primary wfocu_btn wfocu_btn_next_move_install" href="#">
									                {{nextMoveCtaText}}
								                </button>
								                <button v-else v-on:click="activate_next_move_request(`<?php echo $plugin_slug; ?>`)" href="javascript:void(0)" data-slug="<?php echo $plugin_slug; ?>" class=" wfocu_btn_primary wfocu_btn wfocu_btn_next_move_install" href="#">
									                {{nextMoveCtaText}}
								                </button>
								                <span class="wfocu_install_nextmove_ajax_loader spinner"></span>
							                </div>
							                <div class="wfocu_setup_nextmove" v-if="nextMoveState == `unable_to_configure`">

								                <h3><?php _e( 'Feature Unavailable', 'woofunnels-upstroke-one-click-upsell' ) ?></h3>
								                <p><?php _e( 'It looks like NextMove is active but not upto date. Please update nextmove plugin from your plugin dashboard to connect with funnels.', 'woofunnels-upstroke-one-click-upsell' ) ?></p>

							                </div>
							                <div class="wfocu_nextmove_activated" v-if="nextMoveState == `ready_to_configure`">

								                <h3><?php _e( 'NextMove Activated!', 'woofunnels-upstroke-one-click-upsell' ) ?></h3>
								                <p>
									                <?php
									                $funnel_id = filter_input( INPUT_GET, 'edit', FILTER_SANITIZE_NUMBER_INT );
									                echo sprintf( __( ' Use its powerful Rule engine to trigger custom thank you pages</br>To connect the thank you page <i>use rule</i> <strong>"Upsell Funnel is %s"</strong>', 'woofunnels-upstroke-one-click-upsell' ), esc_html( get_the_title( $funnel_id ) ) ); ?>
								                </p>
								                <div class="wfocu_nextmove_img_wrap">
									                <img src="<?php echo WFOCU_PLUGIN_URL . '/assets/img/upstroke_page_rules.jpg' ?>"/>
								                </div>

								                <a v-if="nextMoveInstallState == `1`" target="_blank" class=" wfocu_btn_primary wfocu_btn" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=xlwcty_thankyou' ) ); ?>"><?php _e( 'Add Thank you page', 'woofunnels-upstroke-one-click-upsell' ) ?></a>
								                <a v-else class=" wfocu_btn_primary wfocu_btn" target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=xl-thank-you' ) ); ?>"><?php _e( 'Create a Thank You Page', 'woofunnels-upstroke-one-click-upsell' ) ?></a>

							                </div>
							                <div class="wfocu_nextmove_thankyou" v-if="nextMoveState == `configured`">
								                <div class="wfocu_thankyou_settings">

									                <h3><?php _e( 'Thank You page Settings', 'woofunnels-upstroke-one-click-upsell' ) ?></h3>
									                <div class="wfocu_thankyou_label"><strong><?php _e( 'Associated Thank You page', 'woofunnels-upstroke-one-click-upsell' ) ?></strong></div>
									                <?php
									                $nextmove_page_titles = [];
									                if ( ! is_null( WFOCU_Core()->admin->thank_you_page_posts ) ) {
										                foreach ( WFOCU_Core()->admin->thank_you_page_posts as $thankyou_post ) {

											                if ( ! is_null( $thankyou_post ) ) {
												                $nextmove_page_titles[] = '<a target="_blank" href="' . get_edit_post_link( $thankyou_post->ID ) . '">' . $thankyou_post->post_title . '</a>';
											                }
										                }
									                }
									                ?>
									                <div class="wfocu_thankyou_pg"><?php echo implode( ", ", $nextmove_page_titles ); ?></div>
								                </div>

							                </div>
						                </div>
					                </div>
				                </div>
			                </fieldset>
		                </fieldset>


	                </div>
            </div>

            <div class="wfocu_success_modal" style="display: none" id="modal-settings_success" data-iziModal-icon="icon-home">
            </div>

        </div>
        <div style="display: none" class="wfocu-funnel-settings-help-messages" data-iziModal-title="<?php echo __( 'Offer Success/Failure Messages Help', 'woofunnels-upstroke-one-click-upsell' ) ?>" data-iziModal-icon="icon-home">
            <div class="sections wfocu_img_preview" style="height: 254px;">
                <img src="<?php echo WFOCU_PLUGIN_URL . '/assets/img/funnel-settings-prop.jpg' ?>"/>
            </div>
        </div>
    </div>
