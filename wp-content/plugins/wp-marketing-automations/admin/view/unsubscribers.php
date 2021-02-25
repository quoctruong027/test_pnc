<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$unsubscribed = 0;
if ( isset( $_POST['bwfan_add_unsubscribers'] ) && ! empty( $_POST['bwfan_add_unsubscribers'] ) ) { // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
	$emails       = sanitize_text_field( $_POST['bwfan_add_unsubscribers'] ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
	$emails       = explode( ',', $emails );
	$unsubscribed = count( $emails );

	foreach ( $emails as $email ) {
		if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$insert_data = array(
				'recipient' => $email,
				'c_date'    => current_time( 'mysql' ),
			);

			BWFAN_Model_Message_Unsubscribe::insert( $insert_data );
		}
	}
}

if ( $unsubscribed > 0 ) {
	echo '<div class="notice notice-success"><p>' . esc_js( $unsubscribed ) . __( ' email(s) successfully unsubscribed.', 'wp-marketing-automations' ) . '</p></div>'; //phpcs:ignore WordPress.Security.EscapeOutput
}
if ( isset( $_GET['bwfan_unsubscriber_ids'] ) && ! empty( $_GET['bwfan_unsubscriber_ids'] ) ) { // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
	if ( ( isset( $_GET['action'] ) && 'bwfan_delete_unsubscribers' === $_GET['action'] ) || ( isset( $_GET['action2'] ) && 'bwfan_delete_unsubscribers' === $_GET['action2'] ) ) { // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification,WordPress.Security.ValidatedSanitizedInput
		$count = count( $_GET['bwfan_unsubscriber_ids'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.CSRF.NonceVerification.NoNonceVerification

		echo '<div class="notice notice-success"><p>' . esc_js( $count ) . __( ' email(s) successfully deleted from unsubscribers list.', 'wp-marketing-automations' ) . '</p></div>'; //phpcs:ignore WordPress.Security.EscapeOutput
	}
}

$menu_url = add_query_arg( [
	'page'        => 'autonami',
	'tab'         => 'contacts',
	'sub_section' => 'unsubscribers',
], admin_url( 'admin.php' ) );
?>
<div class="wrap bwfan_global bwfan_page_unsubscribers">
	<?php BWFAN_Core()->admin->make_main_tabs_ui(); ?>
    <div class="bwfan_clear_10"></div>
    <div id="poststuff">
        <div class="inside">
            <div class="bwfan_page_col2_wrap bwfan_clearfix">
                <div class="bwfan_page_left_wrap">
                    <form action="" method="get">
						<?php
						$table = new BWFAN_Unsubscribers_Table();
						$table->search_box( 'Search' );
						$table->process_bulk_action();
						$table->data = $table->get_unsubscribers_table_data();
						$table->prepare_items();
						$table->display();
						?>
                        <input type="hidden" name="page" value="autonami"/>
                        <input type="hidden" name="tab" value="contacts"/>
                    </form>
                </div>
                <div class="bwfan_page_right_wrap">
                    <h3 style="margin-top:0;"><?php esc_html_e( 'Unsubscribe Contacts', 'wp-marketing-automations' ); ?></h3>
                    <form method="POST">
                        <textarea name="bwfan_add_unsubscribers" rows="10" placeholder="Enter emails comma (,) separated to unsubscribe multiple emails." style="width:100%;"></textarea>
                        <p style="margin-top:0;"><i><?php esc_html_e( 'This will unsubscribe users from all emails marked as promotional.', 'wp-marketing-automations' ); ?></i></p>
                        <div class="bwfan_clear_10"></div>
                        <div class="bwfan_form_submit">
                            <button type="submit" class="bwfan_btn_blue" value="add_new"><?php esc_html_e( 'Unsubscribe', 'wp-marketing-automations' ); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
