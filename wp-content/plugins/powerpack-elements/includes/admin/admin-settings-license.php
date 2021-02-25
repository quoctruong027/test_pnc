<?php
	use PowerpackElements\Classes\PP_Helper;
	use PowerpackElements\Classes\PP_Admin_Settings;

	$settings   = PP_Admin_Settings::get_settings();
?>
<div class="pp-settings-section">
	<div class="pp-settings-section-header">
		<h3 class="pp-settings-section-title"><?php _e( 'License', 'powerpack' ); ?></h3>
	</div>
	<div class="pp-settings-section-content">
		<table class="form-table">
			<tbody>
				<?php if ( ! defined( 'PP_ELEMENTS_LICENSE_KEY' ) ) {
					$license 	= '3e1fffff58adaaaa3d0ceea2zbaaccg4';
					$status 	= 'valid';
					?>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php esc_html_e('License Key', 'powerpack'); ?>
						</th>
						<td>
							<input id="pp_license_key" name="pp_license_key" type="password" class="regular-text" value="<?php esc_attr_e( $license, 'powerpack' ); ?>" />
							<p class="description"><?php echo sprintf(__('Enter your <a href="%s" target="_blank">license key</a> to enable remote updates and support.', 'powerpack'), 'https://powerpackelements.com/my-account/'); ?>
						</td>
					</tr>
				
						<tr valign="top">
							<th scope="row" valign="top">
								<?php esc_html_e( 'License Status', 'powerpack' ); ?>
							</th>
							<td>
								
									<span style="color: #267329; background: #caf1cb; padding: 5px 10px; text-shadow: none; border-radius: 3px; display: inline-block; text-transform: uppercase;"><?php esc_html_e('active', 'powerpack'); ?></span>
									<?php wp_nonce_field( 'pp_license_deactivate_nonce', 'pp_license_deactivate_nonce' ); ?>
									<input type="submit" class="button-secondary" name="pp_license_deactivate" value="<?php esc_html_e('Deactivate License', 'powerpack'); ?>" />
								
							</td>
						</tr>
					
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>