<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$autonami_notifications = BWFAN_Common::get_autonami_notifications();
$page_class             = array( 'bwfan_clearfix' );
if ( isset( $autonami_notifications['bwfan'] ) && is_array( $autonami_notifications['bwfan'] ) && 0 < count( $autonami_notifications['bwfan'] ) ) {
	$page_class[] = 'bwfan_page_col2_wrap';
}

$automations_count = BWFAN_Model_Automations::count_rows();

$automation_add_url = add_query_arg( array(
	'page'    => 'autonami',
	'section' => 'automation',
	'create'  => 'y',
), admin_url( 'admin.php' ) );
?>
<div class="wrap bwfan_page_automations bwfan_global">
	<?php BWFAN_Core()->admin->make_main_tabs_ui(); ?>
    <div class="bwfan_clear_10"></div>
    <div class="bwfan_heading_inline"><?php echo esc_html__( 'Automations', 'wp-marketing-automations' ); ?></div>
    <a href="<?php echo esc_url_raw( $automation_add_url ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'WordPress' ); ?></a>

	<?php
	if ( 0 === $automations_count ) {
		/** Welcome text */
		$import_url = admin_url( 'admin.php?page=autonami&tab=recipe' );
		?>
        <div id="poststuff">
            <div class="inside">
                <div class="bwfan_highlight_center">
                    <div class="bwfan_heading_wrap">
                        <div class="bwfan_welc_icon"><img src="<?php echo esc_url_raw( BWFAN_PLUGIN_URL . '/admin/assets/img/clap.png' ); ?>"></div>
                        <div class="bwfan_heading"><?php echo esc_html__( 'You\'re Ready To Go', 'wp-marketing-automations' ); ?></div>
                    </div>
                    <div class="bwfan_clear_20"></div>
                    <div class="bwfan_content">
                        <p><?php echo esc_html__( 'Build Automations from pre-made recipes or create from scratch.', 'wp-marketing-automations' ); ?></p>
                    </div>
                    <div class="bwfan_clear_30"></div>
                    <a class="bwfan_btn_blue_big bwfan_import_recipe" href="<?php echo esc_url_raw( $import_url ); ?>"><?php echo esc_html__( 'Import Pre-Built Recipe', 'wp-marketing-automations' ); ?></a>
                    <a class="bwfan_btn_blue_big bwfan_start_scratch" href="javascript:void(0)"><?php echo esc_html__( 'Start From Scratch', 'wp-marketing-automations' ); ?></a>
                </div>
            </div>
        </div>
		<?php
	} else {

		$woofunnels_transient_obj = WooFunnels_Transient::get_instance();

		/** Delete automation main transient and automation meta transient and active automation transient */
		$woofunnels_transient_obj->delete_transient( 'bwfan_active_automations', 'autonami' );

		?>
        <a href="<?php echo admin_url() . 'admin.php?page=autonami&action=import'; //phpcs:ignore WordPress.Security.EscapeOutput ?>" class="page-title-action"><?php esc_html_e( 'Import', 'WordPress' ); ?></a>
        <a href="<?php echo admin_url() . 'admin.php?page=autonami&action=export'; //phpcs:ignore WordPress.Security.EscapeOutput ?>" class="page-title-action"><?php esc_html_e( 'Export', 'WordPress' ); ?></a>
        <div class="bwfan_clear_10"></div>
        <div id="poststuff">
            <div class="inside">
                <div class="<?php echo esc_html( implode( ' ', $page_class ) ); ?>">
                    <div class="bwfan_page_left_wrap">
                        <form method="GET">
                            <input type="hidden" name="page" value="autonami"/>
                            <input type="hidden" name="status" value="<?php echo( isset( $_GET['status'] ) ? esc_attr( sanitize_text_field( $_GET['status'] ) ) : '' ); // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification ?>"/>
							<?php
							$table = new BWFAN_Post_Table();
							$table->render_trigger_nav();
							$table->data = $table->get_automations_data();
							$table->prepare_items();
							$table->display();
							?>
                        </form>
                    </div>
					<?php
					if ( 0 < count( $autonami_notifications ) ) {
						?>
                        <div class="bwfan_page_right_wrap">
							<?php do_action( 'bwfan_page_right_content' ); ?>
                        </div>
						<?php
					}
					?>
                </div>
            </div>
        </div>
		<?php
	}
	?>
</div>

<div class="bwfan_success_modal iziModal" style="display: none" id="modal_automation_success">
</div>
