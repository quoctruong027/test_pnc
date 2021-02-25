<div id="unused" class="wpraiser-page wpraiser-hidden">
    <div class="wpraiser-section-header">
        <h2 class="wpraiser-title1 wpraiser-icon-unused">Unused Code</h2>
    </div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Unused Code Options' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="unused_disable" class="">
								<input type='hidden' name="wpraiser_settings[unused][disable]" value="0">
								<input type="checkbox" id="unused_disable" name="wpraiser_settings[unused][disable]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'unused', 'disable')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Disable unused code processing</span>
							</label>
						</div>
						<div class="wpraiser-field-description">When this option is enabled, all other settings in this page will be ignored.</div>
					</div>
				</fieldset>
			</div>
			
		</div>
	</div>



	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Page Rules' ); ?></h3>
				<span class="h3icon wpraiser-unused-get-new-row-button wpraiser-icon-add-row" title="Add New"></span>
            </div>
			
			<div class="wpraiser-fields-container-description">Add rules to remove unused JS or CSS files, as well as Inlined Scripts or Styles from the specified URI Path patterns.</div>

			<div id="wpraiser-page-rules-new"></div>
			
			<?php echo wpraiser_show_unused_code_rules(); ?>
			
		</div>
	</div>

<div class="wpraiser-before-saving"></div>
</div>
