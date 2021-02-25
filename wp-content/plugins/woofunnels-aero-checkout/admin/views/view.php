<?php
defined( 'ABSPATH' ) || exit;

/**
 * @var $this WFACP_admin
 */
$wfacp_id      = WFACP_Common::get_id();
$wfacp_section = WFACP_Common::get_current_step();
$wfacp_post    = get_post( $wfacp_id );

$localize_data = $this->get_localize_data();

$selected_design = $localize_data['design']['selected_type'];

if ( is_null( $wfacp_post ) ) {
	return;
}
$steps           = WFACP_Common::get_admin_menu();
$products        = WFACP_Common::get_page_product( WFACP_Common::get_id() );
$localize_data   = $this->get_localize_data();
$template_is_set = get_post_meta( $this->wfacp_id, '_wfacp_selected_design' );

$preview_url = get_the_permalink( $wfacp_id );
if ( empty( WFACP_Common::get_page_product( WFACP_Common::get_id() ) ) ) {
	$preview_url = add_query_arg( [ 'wfacp_preview' => true ], $preview_url );
}

$box_size_class = ( isset( $_GET['wffn_funnel_ref'] ) ) ? 'wfacp_bread' : '';
?>
<div class="wfacp_body wfacp_funnels" id="wfacp_control" data-id="<?php echo $wfacp_id; ?>" data-template-set="<?php echo empty( $template_is_set ) ? 'yes' : '' ?>">
    <div id="poststuff">
        <div class="wfacp_inside">
            <div class="wfacp_fixed_header">
                <div class="wfacp_box_size <?php echo $box_size_class; ?>">
                    <div class="wfacp_head_m wfacp_tl">
						<div class="wfacp_head_mr" data-status="live">
                                <div class="funnel_state_toggle wfacp_toggle_btn">
                                    <input name="offer_state" id="state_<?php echo $wfacp_id; ?>" data-id="<?php echo $wfacp_id; ?>" type="checkbox" class="wfacp-tgl wfacp-tgl-ios wfacp_checkout_page_status" <?php echo( $wfacp_post->post_status == 'publish' ? 'checked="checked"' : '' ); ?>>
                                    <label for="state_<?php echo $wfacp_id; ?>" class="wfacp-tgl-btn wfacp-tgl-btn-small"></label>
                                </div>
                            </div>
                        <div class="wfacp_head_ml">
							<?php BWF_Admin_Breadcrumbs::render(); ?>
                            <a href="javascript:void(0)" data-izimodal-open="#modal-checkout-page" data-iziModal-title="Create New Checkout page" data-izimodal-transitionin="fadeInDown">
                                <span class="dashicons dashicons-edit"></span>
                                <span><?php _e( 'Edit', 'wordpress' ) ?></span>
                            </a>
                            <a href="<?php echo $preview_url; ?>" target="_blank" class="wfacp-preview wfacp-preview-admin">
                                <i class="dashicons dashicons-visibility wfacp-dash-eye"></i>
                                <span class="preview_text"><?php _e( 'View', 'wordpress' ) ?></span>
                            </a>
                        </div>
                    </div>
					<?php
					if ( isset( $_GET['wffn_funnel_ref'] ) ) { ?>
                        <div class="bwf_type_tag"><?php esc_html_e( 'Step: Checkout', 'woofunnels-flex-funnels' ); ?></div>
						<?php
					}
					?>
                </div>
            </div>
            <div class="wfacp_primary_tabs_wrap">
                <div class="wfacp_fixed_sidebar">
					<?php
					foreach ( $steps as $step ) {
						$href       = BWF_Admin_Breadcrumbs::maybe_add_refs( add_query_arg( [
							'page'     => 'wfacp',
							'wfacp_id' => $wfacp_id,
							'section'  => $step['slug'],
						], admin_url( 'admin.php' ) ) );
						$stop_class = '';
						if ( count( $products ) == 0 ) {
							$stop_class = 'wfacp_stop_navigation';
						}
						?>
                        <a data-slug="<?php echo $step['slug']; ?>" class="wfacp_s_menu <?php echo( $step['slug'] == $wfacp_section ? 'active' : '' ); ?> wfacp_s_menu_rules <?php echo $stop_class; ?>" href="<?php echo $href; ?>">
				<span class="wfacp_s_menu_i">
					<?php
					if ( isset( $step['icon'] ) ) {
						echo $step['icon'];
					}
					?>
				</span>
                            <span class="wfacp_s_menu_n"><?php echo $step['name']; ?></span>
                        </a>
						<?php
					}
					?>
                </div>
            </div>
            <div class="wfacp_wrap wfacp_box_size <?php echo $wfacp_section; ?>">
                <div class="wfacp_loader"><span class="spinner"></span></div>
				<?php include_once $this->current_section; ?>
				<?php include_once __DIR__ . '/global/model.php'; ?>
            </div>
        </div>
    </div>
</div>
