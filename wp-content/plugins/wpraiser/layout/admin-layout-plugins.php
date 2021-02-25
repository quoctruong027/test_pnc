<div id="plugins" class="wpraiser-page wpraiser-hidden">
    <div class="wpraiser-section-header">
        <h2 class="wpraiser-title1 wpraiser-icon-plugins">Plugin Filters</h2>
    </div>
	
<?php if(!isset($wpraiser_settings['pref']['nodisclaimer']) || $wpraiser_settings['pref']['nodisclaimer'] != true) { ?>
		<div class="wpraiser-notice-info">
        <div class="wpraiser-notice-container">
		<div class="wpraiser-notice-suptitle">Disclaimer</div>
            <h2 class="wpraiser-notice-title">This is a feature for deactivating plugins per URL<br /> Do not use this feature if you are not sure how it works</h2>
            <div class="wpraiser-notice-description">
				<div class="wpraiser-notice-suptitle">Notes & Tips</div>
				<ul>
				<li>This will generate a MU Plugin (the mu-plugins directory must be writeable) and will deactivate each plugin completely per URI Path </li> 
				<li>Certain plugins may not have visible layout effects on one page, but may still be needed for price calculations or other data </li> 
				<li>On some servers with Disk Cache or PHP OPcache enabled, plugins may randomly get deactivated globally and affect your site functionality</li>
				<li>It is advisable to backup your database before trying this feature on important production sites.</li>
				</ul>
			</div>
        </div>
		</div>
<?php } ?>	

	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Plugin Filters Options' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="unused_disable" class="">
								<input type='hidden' name="wpraiser_settings[unplug][enable]" value="0">
								<input type="checkbox" id="unused_disable" name="wpraiser_settings[unplug][enable]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'unplug', 'enable')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable the Plugin Filters Feature</span>
							</label>
						</div>
						<div class="wpraiser-field-description">You need to enable this, for all other options in this page to work.</div>
					</div>

				</fieldset>
			</div>
			
		</div>
	</div>


	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Filter Rules' ); ?></h3>
            </div>
			<?php echo wpraiser_get_admin_plugin_filters(); ?>
		</div>
	</div>
		
	
	<div class="wpraiser-before-saving"></div>
</div>