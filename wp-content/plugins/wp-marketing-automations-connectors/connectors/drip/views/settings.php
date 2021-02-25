<?php

$saved_data   = WFCO_Common::$connectors_saved_data;
$old_data     = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();
$access_token = isset( $old_data['access_token'] ) ? $old_data['access_token'] : '';
$account_id   = isset( $old_data['account_id'] ) ? $old_data['account_id'] : '';
?>
<div class="wfco-form-group featured field-input">

    <label for="automation-name"><?php echo esc_html__( 'Enter API TOKEN', 'autonami-automations-connectors' ); ?></label>
    <div class="field-wrap">
        <div class="wrapper">
            <input type="text" name="access_token" placeholder="<?php echo esc_attr__( 'Enter API TOKEN', 'autonami-automations-connectors' ); ?>" class="form-control" required value="<?php echo esc_attr__( $access_token ); ?>">
        </div>
    </div>
</div>
<div class="wfco-form-group featured field-input">
    <label for="automation-name"><?php echo esc_html__( 'Enter ACCOUNT ID', 'autonami-automations-connectors' ); ?></label>
    <div class="field-wrap">
        <div class="wrapper">
            <input type="text" name="account_id" placeholder="<?php echo esc_attr__( 'Enter ACCOUNT ID', 'autonami-automations-connectors' ); ?>" class="form-control" required value="<?php echo esc_attr__( $account_id ); ?>">
        </div>
    </div>
</div>
<div class="wfco-form-groups wfco_form_submit">
	<?php
	if ( isset( $old_data['id'] ) && (int) $old_data['id'] > 0 ) {
		?>
        <input type="hidden" name="edit_nonce" value="<?php echo esc_attr__( wp_create_nonce( 'wfco-connector-edit' ) ); ?>"/>
        <input type="hidden" name="id" value="<?php echo esc_attr__( $old_data['id'] ); ?>"/>
        <input type="hidden" name="wfco_connector" value="<?php echo esc_attr__( $this->get_slug() ); ?>"/>
        <input type="submit" class="wfco_update_btn_style wfco_save_btn_style" name="autoresponderSubmit" value="<?php echo esc_attr__( 'Update', 'autonami-automations-connectors' ); ?>">
	<?php } else { ?>
        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr__( wp_create_nonce( 'wfco-connector' ) ); ?>">
        <input type="hidden" name="wfco_connector" value="<?php echo esc_attr__( $this->get_slug() ); ?>"/>
        <input type="submit" class="wfco_save_btn_style" name="autoresponderSubmit" value="<?php echo esc_attr__( 'Save', 'autonami-automations-connectors' ); ?>">
	<?php } ?>
</div>
<div class="wfco_form_response" style="text-align: center;font-size: 15px;margin-top: 10px;"></div>
