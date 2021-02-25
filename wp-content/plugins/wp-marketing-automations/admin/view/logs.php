<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$heading = __( 'Completed Tasks', 'wp-marketing-automations' );
$status  = filter_input( INPUT_GET, 'status' );
if ( 'l_0' === $status ) {
	$heading = __( 'Failed Tasks', 'wp-marketing-automations' );
}
$table = new BWFAN_Logs_Table();
?>
    <div class="wrap bwfan_global bwfan_global_settings bwfan_page_tasks">
		<?php BWFAN_Core()->admin->make_main_tabs_ui(); ?>
        <div class="bwfan_clear_10"></div>
        <div class="bwfan_heading_inline"><?php echo esc_html__( $heading ); ?></div>
        <div id="poststuff">
            <div class="inside">
                <div class="bwfan_clearfix">
                    <div class="bwfan_page_left_wrap">
                        <form action="" method="get">
							<?php

							$table->process_bulk_action();
							$table->render_trigger_nav();
							$table->search_box( 'Search' );
							$table->data = $table->get_logs_table_data();
							$table->prepare_items();
							$table->display();
							?>
                            <input type="hidden" name="page" value="autonami"/>
                            <input type="hidden" name="tab" value="logs"/>
                            <input type="hidden" name="status" value="<?php echo ( isset( $_GET['status'] ) && ! empty( $_GET['status'] ) ) ? esc_attr__( sanitize_text_field( $_GET['status'] ) ) : ''; // WordPress.CSRF.NonceVerification.NoNonceVerification ?>"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bwfan_success_modal iziModal" style="display: none" id="modal_automation_success"></div>
<?php
$table->print_local_data();
include_once 'tasks-info-modal.php';
