<?php

$sidebar_menu        = WFOCU_Common::get_sidebar_menu();
$funnel_sticky_line  = __( 'Now Building', 'woofunnels-upstroke-one-click-upsell' );
$funnel_sticky_title = '';
$funnel_onboarding   = true;
if ( isset( $_GET['edit'] ) && ! empty( $_GET['edit'] ) ) {   // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$funnel_sticky_title = get_the_title( wc_clean( $_GET['edit'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Missing

	$funnel_onboarding_status = get_post_meta( wc_clean( $_GET['edit'] ), '_wfocu_is_rules_saved', true );  // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( 'yes' === $funnel_onboarding_status ) {
		$funnel_onboarding  = false;
		$funnel_sticky_line = '';
	}
}

$funnel_status = get_post_status( wc_clean( $_GET['edit'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Missing
$funnel_id     = wc_clean( $_GET['edit'] );  // phpcs:ignore WordPress.Security.NonceVerification.Missing
BWF_Admin_Breadcrumbs::render_sticky_bar();
?>
<div class="wrap wfocu_funnels_listing">
    <div id="poststuff">
        <div class="inside">
            <div class="bwf_breadcrumb">
				<div class="bwf_before_bre">
                    <div class="wfocu_head_mr" data-status="<?php echo ( $funnel_status !== 'publish' ) ? 'sandbox' : 'live'; ?>">
                        <div class="funnel_state_toggle wfocu_toggle_btn">
                            <input name="offer_state" id="state<?php echo esc_attr( $funnel_id ); ?>" data-id="<?php echo esc_attr( $funnel_id ); ?>" type="checkbox" class="wfocu-tgl wfocu-tgl-ios" <?php echo ( $funnel_status === 'publish' ) ? 'checked="checked"' : ''; ?> />
                            <label for="state<?php echo esc_attr( $funnel_id ); ?>" class="wfocu-tgl-btn wfocu-tgl-btn-small"></label>
                        </div>
                     </div>
                </div>
				<?php echo BWF_Admin_Breadcrumbs::render(); ?>
                <div class="bwf_after_bre">
                    <a data-izimodal-open="#modal-update-funnel" data-izimodal-transitionin="fadeInDown" href="javascript:void(0);" class="bwf_edt">
                        <i class="dashicons dashicons-edit"></i> <?php esc_html_e( 'Edit', 'woofunnels' ); ?>
                    </a>
                </div>
            </div>

			<?php
			if ( is_array( $sidebar_menu ) && count( $sidebar_menu ) > 0 ) {
				ksort( $sidebar_menu );
				$funnel_data = WFOCU_Core()->funnels->get_funnel_offers_admin();
				?>

                <div class="bwf_menu_list_primary">
                    <ul>

						<?php
						foreach ( $sidebar_menu as $menu ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
							$menu_icon = ( isset( $menu['icon'] ) && ! empty( $menu['icon'] ) ) ? $menu['icon'] : 'dashicons dashicons-admin-generic';
							if ( isset( $menu['name'] ) && ! empty( $menu['name'] ) ) {

								$section_url = BWF_Admin_Breadcrumbs::maybe_add_refs( add_query_arg( array(
									'page'    => 'upstroke',
									'section' => $menu['key'],
									'edit'    => WFOCU_Core()->funnels->get_funnel_id(),
								), admin_url( 'admin.php' ) ) );

								$class = '';
								if ( isset( $_GET['section'] ) && $menu['key'] === wc_clean( $_GET['section'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
									$class = 'active';
								}

								global $wfocu_is_rules_saved;

								$main_url = $section_url;

								?>
                                <li class="<?php echo $class ?>">
                                    <a href="<?php echo esc_url_raw( $main_url ) ?>">
                                        <span class="<?php echo esc_attr( $menu_icon ); ?>"></span>
										<?php echo esc_attr( $menu['name'] ); ?>
                                    </a>
                                </li>


								<?php
							}
						}
						?>
                    </ul>
                </div>
				<?php
			}
			?>
            <div class="wfocu_wrap wfocu_box_size <?php echo ( isset( $_REQUEST['section'] ) &&  $_REQUEST['section'] === 'settings' ) ? 'wfocu_wrap_inner_design ' : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing ?>">
                 <div class="wfocu_box_size">
                    <div class="wfocu_wrap_inner <?php echo ( isset( $_REQUEST['section'] ) ) ? 'wfocu_wrap_inner_' . esc_attr( wc_clean( $_REQUEST['section'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing ?>">

						<?php
						$get_keys = wp_list_pluck( $sidebar_menu, 'key' );


						/**
						 * Redirect if any unregistered action found
						 */
						if ( false === in_array( $this->section_page, $get_keys, true ) ) {
							wp_redirect( admin_url( 'admin.php?page=upstroke&section=offers&edit=' . wc_clean( $_GET['edit'] ) ) );   // phpcs:ignore WordPress.Security.NonceVerification.Missing
							exit;
						} else {

							/**
							 * Any registered section should also apply an action in order to show the content inside the tab
							 * like if action is 'stats' then add_action('wfocu_dashboard_page_stats', __FUNCTION__);
							 */
							if ( false === has_action( 'wfocu_dashboard_page_' . $this->section_page ) ) {
								include_once( $this->admin_path . '/view/' . $this->section_page . '.php' );  // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

							} else {
								/**
								 * Allow other add-ons to show the content
								 */
								do_action( 'wfocu_dashboard_page_' . $this->section_page );
							}
						}


						do_action( 'wfocu_funnel_page', $this->section_page, WFOCU_Core()->funnels->get_funnel_id() );
						?>

                        <div class="wfocu_clear"></div>
                    </div>
                </div>
            </div>

            <div class="wfocu_izimodal_default" id="modal-update-funnel" style="display: none;">
                <div class="sections">
                    <form class="wfocu_forms_update_funnel" data-wfoaction="update_funnel" novalidate>
                        <div class="wfocu_vue_forms" id="part-update-funnel">
                            <div class="vue-form-generator">
                                <fieldset>
                                    <div class="form-group featured required field-input"><label for="funnel-name">Name<!----></label>
                                        <div class="field-wrap">
                                            <div class="wrapper"><input id="funnel-name" type="text" name="funnel_name" required="required" class="form-control"><!----></div>
                                            <!---->
                                        </div><!----><!----></div>
                                    <div class="form-group featured field-textArea"><label for="funnel-desc">Description<!----></label>
                                        <div class="field-wrap"><textarea id="funnel-desc" rows="3" name="funnel_desc" class="form-control"></textarea>
                                            <!----></div>
                                        <!----><!----></div>
                                </fieldset>
                            </div>
                        </div>
                        <fieldset>
                            <div class="wfocu_form_submit">
                                <input type="hidden" name="_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wfocu_update_funnel' ) ); ?>"/>
                                <button type="submit" class="wfocu_btn_primary wfocu_btn" value="add_new">Update</button>
                            </div>
                            <div class="wfocu_form_response">

                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
