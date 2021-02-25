<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wpdb;
$message     = '';
$automations = $wpdb->get_results( $wpdb->prepare( "
                SELECT a.ID as id, a.event as event, m.meta_value as title 
                FROM {$wpdb->prefix}bwfan_automations as a
                LEFT JOIN {$wpdb->prefix}bwfan_automationmeta as m
                ON a.ID = m.bwfan_automation_id
                LEFT JOIN {$wpdb->prefix}bwfan_automationmeta as m1
                ON a.ID = m1.bwfan_automation_id
                WHERE a.status != %d 
                AND m.meta_key = %s
                AND m1.meta_key = %s
                AND m1.meta_value != %d
                ORDER BY ID DESC
                ", 2, 'title', 'requires_update', 1 ), ARRAY_A );

if ( empty( $automations ) ) {
	$message = __( 'There are no active automations that are syncable.', 'autonami-automations-pro' );
}

$options = [];
foreach ( $automations as $automation ) {
	$current_event_object = BWFAN_Core()->sources->get_event( $automation['event'] );
	if ( $current_event_object instanceof BWFAN_Event && $current_event_object->is_syncable() ) {
		$options[ $automation['id'] ] = $automation['title'];
	}
}
if ( empty( $options ) ) {
	$message = __( 'There are no active automations that are syncable.', 'autonami-automations-pro' );
}

$menu_url = add_query_arg( [
	'page'        => 'autonami',
	'tab'         => 'batch_process',
	'sub_section' => 'history_sync',
], admin_url( 'admin.php' ) );

?>
<div class="wrap bwfan_global bwfan_page_unsubscribers">
	<?php BWFAN_Core()->admin->make_main_tabs_ui(); ?>
    <div class="bwfan_clear_10"></div>
    <div class="wp-filter">
        <ul class="filter-links">
            <li><a href="<?php echo $menu_url; //phpcs:ignore WordPress.Security.EscapeOutput ?>" class="current"><?php echo esc_html__( 'WC Past Orders Sync', 'autonami-automations-pro' ); ?></a></li>
        </ul>
    </div>
    <div class="bwfan_heading_inline"><?php echo esc_html__( 'Adding New Process', 'autonami-automations-pro' ); ?></div>
    <div id="poststuff">
        <div class="inside bwfan_global">
            <div class="bwfan_page_col12_wrap bwfan_clearfix bwfan_forms_global_settings">
				<?php
				if ( ! empty( $message ) ) {
					echo esc_html__( $message );

					return;
				}
				?>
                <div class="form-group field-input">
                    <label><?php esc_html_e( 'Select Automation', 'autonami-automations-pro' ); ?></label>
                    <div class="field-wrap">
                        <div class="wrapper">
                            <select id="bwfan_select_sync_history_automation">
								<?php
								echo '<option value="0">' . esc_html__( 'Choose Automation', 'autonami-automations-pro' ) . '</option>';
								foreach ( $options as $key => $value ) {
									echo '<option value="' . esc_html__( $key ) . '">' . esc_html__( $value ) . '</option>';
								}
								?>
                            </select>
                        </div>
                        <span class="hint"><?php esc_html_e( 'Select syncable automation to sync its data.', 'autonami-automations-pro' ); ?></span>
                        <div class="bwfan_spinner" style="display: none"></div>
                    </div>
                </div>
                <div class="bwfan-response-failure"></div>
                <div class="bwfan-response-success"></div>
            </div>
        </div>
    </div>
</div>
