<?php
$auth_url       = $this->get_url();
$disconnect_url = $this->get_disconnect_url();
$saved_data     = $this->get_saved_data();

if ( ! empty( $saved_data ) ) {
	?>
    <a href="<?php echo $disconnect_url; //phpcs:ignore WordPress.Security.EscapeOutput ?>" class="wfco-connector-delete" style="margin-bottom: 16px;"><i class="dashicons dashicons-no-alt"></i><?php echo esc_html__( ' Disconnect', 'autonami-automations-connectors' ); ?>
    </a>
	<?php
} else {
	?>
    <a href="<?php echo $auth_url; //phpcs:ignore WordPress.Security.EscapeOutput
	?>" class="wfco_save_btn_style"><?php echo esc_html__( 'Connect', 'autonami-automations-connectors' ); ?></a>
	<?php
}
?>
