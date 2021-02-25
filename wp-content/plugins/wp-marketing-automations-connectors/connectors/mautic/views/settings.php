<?php

$saved_data    = WFCO_Common::$connectors_saved_data;
$old_data      = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();
$site_url      = isset( $old_data['site_url'] ) ? $old_data['site_url'] : '';
$client_id     = isset( $old_data['client_id'] ) ? $old_data['client_id'] : '';
$client_secret = isset( $old_data['client_secret'] ) ? $old_data['client_secret'] : '';
?>
<div class="wfco-mautic-wrap">
	<div class="wfco-form-group featured field-input">

		<label for="automation-name"><?php esc_html_e( 'Enter Mautic Site URL', 'autonami-automations-connectors' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<input type="text" name="site_url" placeholder="<?php esc_attr_e( 'Enter Mautic Site URL', 'autonami-automations-connectors' ); ?>" class="form-control" required value="<?php echo esc_attr__( $site_url ); ?>">
			</div>
		</div>
	</div>
	<div class="wfco-form-group featured field-input">
		<label for="automation-name"><?php esc_html_e( 'Enter Client ID', 'autonami-automations-connectors' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<input type="text" name="client_id" placeholder="<?php esc_attr_e( 'Enter Client ID', 'autonami-automations-connectors' ); ?>" class="form-control" required value="<?php echo esc_attr__( $client_id ); ?>">
			</div>
		</div>
	</div>
	<div class="wfco-form-group featured field-input">

		<label for="automation-name"><?php esc_html_e( 'Enter Client Secret', 'autonami-automations-connectors' ); ?></label>
		<div class="field-wrap">
			<div class="wrapper">
				<input type="text" name="client_secret" placeholder="<?php esc_attr_e( 'Enter Client Secret', 'autonami-automations-connectors' ); ?>" class="form-control" required value="<?php echo esc_attr__( $client_secret ); ?>">
			</div>
		</div>
	</div>
	<div class="wfco-form-groups wfco_form_submit">
		<?php
		if ( isset( $old_data['id'] ) && (int) $old_data['id'] > 0 ) {
			?>
			<input type="hidden" name="edit_nonce" value="<?php esc_attr_e( wp_create_nonce( 'wfco-connector-edit' ) ); ?>"/>
			<input type="hidden" name="id" value="<?php esc_attr_e( $old_data['id'] ); ?>"/>
			<input type="hidden" name="wfco_connector" value="<?php esc_attr_e( $this->get_slug() ); ?>"/>
			<button class="wfco_save_btn_style wfco_connect_to_api"><?php esc_attr_e( 'Connect and Update', 'autonami-automations-connectors' ); ?></button>


		<?php } else { ?>
			<input type="hidden" name="_wpnonce" value="<?php esc_attr_e( wp_create_nonce( 'wfco-connector' ) ); ?>">
			<input type="hidden" name="wfco_connector" value="<?php esc_attr_e( $this->get_slug() ); ?>"/>
			<button class="wfco_save_btn_style wfco_connect_to_api"><?php esc_attr_e( 'Connect and Save', 'autonami-automations-connectors' ); ?></button>

		<?php } ?>
	</div>
	<div class="wfco_form_response" style="text-align: center;font-size: 15px;margin-top: 10px;"></div>
</div>
