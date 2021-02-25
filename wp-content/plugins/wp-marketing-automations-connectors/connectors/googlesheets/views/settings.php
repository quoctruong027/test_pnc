<?php

$saved_data   = WFCO_Common::$connectors_saved_data;
$old_data     = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();
$gs_token     = isset( $old_data['api_data']['gs_token'] ) ? $old_data['api_data']['gs_token'] : '';
$redirect_uri = urlencode( WFCO_GS_REDIRECT_URI );
$auth_url     = WFCO_GS_AUTH_URI . '?access_type=offline&approval_prompt=force&client_id=' . WFCO_GS_CLIENT_ID . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=https://spreadsheets.google.com/feeds/';

if ( ! empty( $gs_token ) ) {
	echo '<p>' . __( 'Access Token is already saved.', 'autonami-automations-connectors' ) . '</p>';
} else {
	?>
    <div class="form-group featured field-input">
        <div class="field-wrap">
            <input type="text" name="gs_token" placeholder="<?php echo __( 'Enter Token', 'autonami-automations-connectors' ); ?>" class="form-control" required value="<?php echo $gs_token; ?>">
            <div class="bwfan_clear_10"></div>
            <a href="<?php echo $auth_url; ?>" target="_blank" class="button"><?php _e( 'Click to get Token', 'autonami-automations-connectors' ); ?></a>
        </div>
        <div class="bwfan_clear_10"></div>
    </div>
	<?php
}

?>
<div class="wfco-form-groups wfco_form_submit">
	<?php
	if ( isset( $old_data['id'] ) && (int) $old_data['id'] > 0 ) {
		//
	} else {
		?>
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'wfco-connector' ); ?>">
        <input type="hidden" name="wfco_connector" value="<?php echo $this->get_slug(); ?>"/>
        <input type="submit" class="wfco_save_btn_style" name="autoresponderSubmit" value="<?php echo __( 'Save', 'autonami-automations-connectors' ); ?>">
	<?php } ?>
</div>
<div class="wfco_form_response" style="text-align: center;font-size: 15px;margin-top: 10px;"></div>
