
<div id="dashboard" class="wpraiser-page wpraiser-hidden">
    <div class="wpraiser-section-header">
        <h2 class="wpraiser-title1 wpraiser-icon-dashboard">Dashboard</h2>
    </div>
 
<?php			
	# message
	$lic = wpraiser_support_license_info('support');	
											
	# default form values
	$wpr_serial = wpraiser_get_license_string('serial');
	$wpr_identifier = wpraiser_get_license_string('identifier');
	$lic_color = 'danger';
						
	# populate on form submission
	if(isset($_POST['license'])) {
		if(isset($wpr_serial_post)) { $wpr_serial = $wpr_serial_post; }
		if(isset($wpr_identifier_post)) { $wpr_identifier = $wpr_identifier_post; }
	}
	
	# update widget color
	if(!empty(wpraiser_get_license_string('serial'))) { 
		$lic_color = 'success'; 
	}
	
?>
 
 
        <div class="wpraiser-notice-<?php echo $lic_color; ?>">
        <div class="wpraiser-notice-container">
		<div class="wpraiser-notice-suptitle"><?php _e( 'Plugin Updates' ); ?></div>
		<h2 class="wpraiser-notice-title">Your License Status</h2>
            <div class="wpraiser-notice-description">
				<form method="post">
					<?php wp_nonce_field('wpraiser_license_nonce', 'wpraiser_license_nonce'); ?>
					
								<div class="wpraiser-textarea">
									<div class="wpraiser-license-row">
										<div class="wpraiser-field-title">License Key</div>
										<div class="wpraiser-text-limited">
											<input type="text" <?php if(!empty($wpr_serial)) { echo 'class="filled"'; } ?> id="license_serial" name="license[serial]" placeholder="insert your license here" value="<?php if(!empty($wpr_serial)) { echo $wpr_serial; } ?>">
										</div>
									</div>
									<div class="wpraiser-license-row">
										<div class="wpraiser-field-title">Unique Identifier</div>
										<div class="wpraiser-text-limited">
											<input type="text" <?php if(!empty($wpr_identifier)) { echo 'class="filled"'; } ?> id="license_identifier" name="license[identifier]" placeholder="your@email.com" value="<?php if(!empty($wpr_identifier)) { echo $wpr_identifier; } ?>">
										</div>
									</div>
								</div>
					
					
					
					<input type="hidden" name="wpraiser_action" value="license" />
					<?php submit_button( __( 'Save Settings' ), 'primary', 'submit', false ); ?>
				</form>	
			</div>
            <div class="wpraiser-notice-continue"><?php echo wpraiser_support_license_info('dashboard'); ?></div>
		</div>
        </div>
 
	
        <div class="wpraiser-notice-info">
        <div class="wpraiser-notice-container">
		<div class="wpraiser-notice-suptitle">Export Settings</div>
            <div class="wpraiser-notice-description">
				<form method="post">
					<?php wp_nonce_field('wpraiser_export_nonce', 'wpraiser_export_nonce'); ?>
					<input type="hidden" name="wpraiser_action" value="export" />
					<?php submit_button( __( 'Export' ), 'primary', 'submit', false ); ?>
				</form>
			</div>
            <div class="wpraiser-notice-continue">You can export your current settings and restore them later on this page.</div>
		</div>
        </div>
			
        <div class="wpraiser-notice-info">
        <div class="wpraiser-notice-container">
		<div class="wpraiser-notice-suptitle">Import Settings</div>
            <div class="wpraiser-notice-description">
				<form method="post" enctype="multipart/form-data">
					<?php wp_nonce_field('wpraiser_import_nonce', 'wpraiser_import_nonce'); ?>
					<p><input type="file" name="import_wpr_settings"/></p>
					<input type="hidden" name="wpraiser_action" value="import" />
					<?php submit_button( __( 'Import' ), 'primary', 'submit', false ); ?>
				</form>
			</div>
            <div class="wpraiser-notice-continue">You can restore your downloaded settings here.</div>
		</div>
        </div>	
			
			
			
			<div class="wpraiser-footer-text">Plugin Version: <?php echo $wpraiser_var_plugin_version; ?></div>
	

	
</div>
