<?php

$cart_enabled    = true;
$global_settings = BWFAN_Common::get_global_settings();
if ( empty( $global_settings['bwfan_ab_enable'] ) ) {
	$cart_enabled = false;
}

$email = isset( $_GET['bwfan_cart_email'] ) ? sanitize_email( $_GET['bwfan_cart_email'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification

?>
    <style>
        li.bwfan_selected_menu a {
            font-weight: bold;
        }
    </style>
    <div class="wrap bwfan_global bwfan_global_settings bwfan_tab_carts">
		<?php BWFAN_Core()->admin->make_main_tabs_ui(); ?>
        <div id="poststuff">
            <div class="inside">
				<?php
				if ( false === $cart_enabled ) {
					$enable_link = add_query_arg( [
						'page'            => 'autonami',
						'tab'             => 'carts',
						'enable_tracking' => 'cart',
						'cart_nonce'      => wp_create_nonce( 'bwfan_tab_cart_tracking_enable' ),
					], admin_url( 'admin.php' ) );
					?>
                    <div class="bwfan_highlight_center">
                        <div class="bwfan_heading"><?php esc_attr_e( 'Oops! Unable to capture emails, Cart Tracking is disabled.', 'wp-marketing-automations' ); ?></div>
                        <div class="bwfan_clear_20"></div>
                        <div class="bwfan_content">
                            <p><?php esc_attr_e( 'Click on the button below to go to Settings > Carts to enable cart tracking. Once activated, you will be able to capture emails as soon buyer enters it.', 'wp-marketing-automations' ); ?></p>
                        </div>
                        <div class="bwfan_clear_30"></div>
                        <a class="bwfan_btn_blue_big bwfan_enable_tracking" href="<?php echo esc_url_raw( $enable_link ); ?>">Enable Tracking</a>
                    </div>
					<?php
				} else {
					?>
                    <div class="bwfan_heading_inline">
						<?php
						$message = sprintf( __( "Tasks for cart: %s", 'wp-marketing-automations' ), $email );
						esc_html_e( $message );
						?>
                    </div>
                    <div class="bwfan_clearfix">
						<?php
						do_action( 'bwfan_before_abandoned_view_tasks' );
						$table       = new BWFAN_Abandoned_View_Tasks_Table();
						$table->data = $table->get_abandoned_view_tasks_table_data();
						$table->prepare_items();
						$table->display();
						do_action( 'bwfan_after_abandoned_view_tasks' );
						?>
                    </div>
					<?php
				}
				?>
            </div>
        </div>
    </div>
<?php
if ( false !== $cart_enabled ) {
	$table->print_local_data();
}
include_once( BWFAN_PLUGIN_DIR . '/admin/view/tasks-info-modal.php' );
