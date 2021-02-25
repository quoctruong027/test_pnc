<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$menu_url = add_query_arg( [
	'page'        => 'autonami',
	'tab'         => 'batch_process',
	'sub_section' => 'history_sync',
], admin_url( 'admin.php' ) );

?>
<div class="wrap bwfan_global bwfan_page_batch_process">
	<?php BWFAN_Core()->admin->make_main_tabs_ui(); ?>
    <div class="bwfan_clear_10"></div>
    <div class="wp-filter">
        <ul class="filter-links">
            <li><a href="<?php echo $menu_url; //phpcs:ignore WordPress.Security.EscapeOutput ?>" class="current"><?php echo esc_html__( 'WC Past Orders Sync', 'autonami-automations-pro' ); ?></a></li>
        </ul>
    </div>
    <div class="bwfan_heading_inline"><?php echo esc_html__( 'Process', 'autonami-automations-pro' ); ?></div>
    <a href="<?php echo admin_url( 'admin.php?page=autonami&tab=batch_process&sub_section=history_sync&action=add_new' ); //phpcs:ignore WordPress.Security.EscapeOutput ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'WordPress' ); ?></a>
    <div id="poststuff">
        <div class="inside">
            <div class="bwfan_page_col12_wrap bwfan_clearfix">
				<?php
				$table       = new BWFAN_Sync_Table();
				$table->data = $table->get_sync_table_data();
				$table->prepare_items();
				$table->display();
				?>
            </div>
        </div>
    </div>
</div>
