<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$automation_id   = $this->get_automation_id();
$automation_meta = BWFAN_Model_Automations::get_automation_with_data( $automation_id );

if ( false === $automation_id || ! is_array( $automation_meta ) || 0 === count( $automation_meta ) ) {
	wp_die( esc_html__( 'Automation doesn\'t exists, something is wrong.', 'wp-marketing-automations' ) );
}

$trigger_events      = isset( $automation_meta['event'] ) ? $automation_meta['event'] : '';
$saved_integrations  = array();
$parent_source       = isset( $automation_meta['source'] ) ? $automation_meta['source'] : '';
$a_track_id          = isset( $automation_meta['meta']['a_track_id'] ) ? $automation_meta['meta']['a_track_id'] : '';
$trigger_events_meta = isset( $automation_meta['meta']['event_meta'] ) ? $automation_meta['meta']['event_meta'] : [];
$saved_integrations  = isset( $automation_meta['meta']['actions'] ) ? $automation_meta['meta']['actions'] : [];

$automation_sticky_line = __( 'Now Building', 'wp-marketing-automations' );
$automation_onboarding  = true;
$automation_title       = ( isset( $automation_meta['meta'] ) && isset( $automation_meta['meta']['title'] ) ) ? $automation_meta['meta']['title'] : '';
$status                 = ( 1 === absint( $automation_meta['status'] ) ) ? 'publish' : 'sandbox';
$automation_id          = ( $automation_id );

?>
<style>
    body {
        overflow: hidden !important;
        padding: 0 !important;
    }
</style>
<div class="bwfan_body bwfan_sec_automation">
    <div class="bwfan_fixed_header">
        <div class="bwfan_p20_wrap bwfan_box_size bwfan_table">
            <div class="bwfan_head_m bwfan_tl bwfan_table_cell">
                <div class="bwfan_head_mr" data-status="<?php echo ( 'publish' !== $status ) ? 'sandbox' : 'live'; ?>">
                    <div class="automation_state_toggle bwfan_toggle_btn">
                        <input name="offer_state" id="state<?php echo esc_html( $automation_id ); ?>" data-id="<?php echo esc_html( $automation_id ); ?>" type="checkbox" class="bwfan-tgl bwfan-tgl-ios" <?php echo ( 'publish' === $status ) ? 'checked="checked"' : ''; ?> <?php echo esc_html__( BWFAN_Core()->automations->current_automation_sync_state ); ?> />
                        <label for="state<?php echo esc_html( $automation_id ); ?>" class="bwfan-tgl-btn bwfan-tgl-btn-small"></label>
                    </div>
                    <span class="bwfan_head_automation_state_on" <?php echo ( 'publish' !== $status ) ? ' style="display:none"' : ''; ?>><?php esc_html_e( 'Live', 'wp-marketing-automations' ); ?></span>
                    <span class="bwfan_head_automation_state_off" <?php echo ( 'publish' === $status ) ? 'style="display:none"' : ''; ?>> <?php esc_html_e( 'Sandbox', 'wp-marketing-automations' ); ?></span>
                </div>
                <div class="bwfan_head_ml"><?php echo esc_html( $automation_sticky_line ); ?> <strong><span id="bwfan_automation_name"><?php echo esc_html( $automation_title ); ?></span></strong>
                    <a href="javascript:void(0)" data-izimodal-open="#modal-update-automation" data-izimodal-transitionin="comingIn"><i class="dashicons dashicons-edit"></i></a>
                </div>
            </div>
            <div style="display: none;" id="bwfan_automation_description"></div>
            <div class="bwfan_head_r bwfan_tr bwfan_table_cell">
                <a href="<?php echo admin_url( 'admin.php?page=autonami' ); //phpcs:ignore WordPress.Security.EscapeOutput ?>" class="dashicons dashicons-no-alt bwfan_btn_close"></a>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    <div class="bwfan_wrap bwfan_box_size">
        <div class="bwfan_p20 bwfan_box_size">
            <div class="bwfan_wrap_inner">
				<?php
				/**
				 * Any registered section should also apply an action in order to show the content inside the tab
				 * like if action is 'stats' then add_action('bwfan_dashboard_page_stats', __FUNCTION__);
				 */
				if ( false === has_action( 'bwfan_dashboard_page_' . $this->get_automation_section() ) ) {
					include_once( $this->admin_path . '/view/section-' . $this->get_automation_section() . '.php' );
				} else {
					/**
					 * Allow other add-ons to show the content
					 */
					do_action( 'bwfan_dashboard_page_' . $this->get_automation_section() );
				}
				do_action( 'bwfan_automation_page', $this->get_automation_section(), $automation_id );
				?>
                <div class="bwfan_clear"></div>
            </div>
        </div>
    </div>
</div>

<div class="bwfan_izimodal_default" style="display: none" id="modal-update-automation">
    <div class="sections">
        <form class="bwfan_update_automation" data-bwf-action="update_automation">
            <div class="bwfan_vue_forms" id="part-add-funnel">
                <div class="form-group featured field-input">
                    <label for="title"><?php esc_html( __( 'Automation Name', 'wp-marketing-automations' ) ); ?></label>
                    <div class="field-wrap">
                        <div class="wrapper">
                            <input id="title" type="text" name="title" placeholder="<?php echo esc_html( __( 'Enter Automation Name', 'wp-marketing-automations' ) ); ?>" class="form-control" value="<?php echo esc_html( $automation_title ); ?>" required>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="_wpnonce" value="<?php esc_attr_e( wp_create_nonce( 'bwfan-action-admin' ) ); ?>"/>
            </div>
            <fieldset>
                <div class="bwfan_form_submit">
                    <input type="hidden" name="automation_id" value="<?php echo esc_html( $automation_id ); ?>">
                    <input type="submit" class="bwfan-display-none" value="<?php echo esc_html( __( 'Update', 'wp-marketing-automations' ) ); ?>"/>
                    <a href="javascript:void(0)" class="bwfan_update_form_submit bwfan_btn_blue"><?php echo esc_html( __( 'Update', 'wp-marketing-automations' ) ); ?></a>
                </div>
                <div class="bwfan_form_response">
                </div>
            </fieldset>
        </form>
        <div class="bwfan-automation-create-success-wrap bwfan-display-none">
            <div class="bwfan-automation-create-success-logo">
                <div class="swal2-icon swal2-success swal2-animate-success-icon" style="display: flex;">
                    <span class="swal2-success-line-tip"></span>
                    <span class="swal2-success-line-long"></span>
                    <div class="swal2-success-ring"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bwfan_izimodal_default" style="display: none" id="modal-plus-icon-add">
    <div class="sections bwfan_add_block_wrap">
        <div class="bwfan_add_next_block" data-type="action">
            <div class="bwfan_add_block_icon"><i class="dashicons dashicons-networking"></i></div>
            <div class="bwfan_add_block_label">Direct Action</div>
            <div class="bwfan_add_block_desc">Run Actions directly.</div>
        </div>
        <div class="bwfan_add_next_block" data-type="conditional">
            <div class="bwfan_add_block_icon"><i class="dashicons dashicons-editor-help"></i></div>
            <div class="bwfan_add_block_label">Conditional Action</div>
            <div class="bwfan_add_block_desc">Add condition based action, apply rules which will be executed before Actions.</div>
        </div>
    </div>
</div>

<div class="bwfan_success_modal iziModal" style="display: none" id="modal_automation_success">
</div>
