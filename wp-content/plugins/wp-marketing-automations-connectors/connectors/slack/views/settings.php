<?php
$auth_url   = $this->get_access_right_url();
$saved_data = WFCO_Common::$connectors_saved_data;
$old_data   = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();

if ( is_array( $old_data ) && count( $old_data ) > 0 ) {
	$msg = "<p>You are already connected with Slack. If needs reconnecting, click again on 'Connect Me' button.</p>";
} else {
	$msg = "<p>Click on 'Connect Me' button to connect the Slack account with your <strong>" . get_bloginfo( 'name' ) . '</strong> store.</p>';
}
echo $msg; //phpcs:ignore WordPress.Security.EscapeOutput
?>
<a href="<?php echo $auth_url; //phpcs:ignore WordPress.Security.EscapeOutput ?>" class="wfco_save_btn_style"><?php echo esc_html__( 'Connect Me', 'autonami-automations-connectors' ); ?></a>
