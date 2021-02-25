<div id="css" class="wpraiser-page wpraiser-hidden">
    <div class="wpraiser-section-header">
        <h2 class="wpraiser-title1 wpraiser-icon-css">CSS & Styles</h2>
    </div>

	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'CSS Options' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="css_disable" class="">
								<input type='hidden' name="wpraiser_settings[css][disable]" value="0">
								<input type="checkbox" id="css_disable" name="wpraiser_settings[css][disable]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'css', 'disable')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Disable the CSS Feature</span>
							</label>
						</div>
						<div class="wpraiser-field-description">When this option is enabled, all other settings in this page will be ignored.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="css_remove_print" class="">
								<input type='hidden' name="wpraiser_settings[css][remove_print]" value="0">
								<input type="checkbox" id="css_remove_print" name="wpraiser_settings[css][remove_print]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'css', 'remove_print')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Remove "Print" related styles</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Remove an CSS code of mediatype "print" (used only for printing documents).</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="css_remove_gutenberg" class="">
								<input type='hidden' name="wpraiser_settings[css][remove_gutenberg]" value="0">
								<input type="checkbox" id="css_remove_gutenberg" name="wpraiser_settings[css][remove_gutenberg]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'css', 'remove_gutenberg')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Remove the Gutenberg Block Library CSS</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Remove unnecessary CSS files if you are not using the Gutenberg Editor.</div>
					</div>
				</fieldset>
			</div>
	
		</div>
	</div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'CSS Minification' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="css_min_files" class="">
								<input type='hidden' name="wpraiser_settings[css][min_files]" value="0">
								<input type="checkbox" id="css_min_files" name="wpraiser_settings[css][min_files]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'css', 'min_files')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable minification for merged CSS files</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Reduce the final file size by minifying the generated CSS files.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="css_min_inline" class="">
								<input type='hidden' name="wpraiser_settings[css][min_inline]" value="0">
								<input type="checkbox" id="css_min_inline" name="wpraiser_settings[css][min_inline]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'css', 'min_inline')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable minification for inlined CSS styles</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Reduce the page size by minifying your inlined style tags.</div>
					</div>
				</fieldset>
			</div>

		</div>
	</div>
	

	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'CSS Optimization' ); ?></h3>
            </div>
			<div class="accordion">

				<h3 class="wpraiser-title2">Ignore CSS files</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Do not merge these CSS files</div>	
							<div class="wpraiser-field-description">This will prevent merging of specified CSS files, in a partial and case insensitive manner.</div>						
							<textarea id="css_ignore" name="wpraiser_settings[css][ignore]" placeholder="/css/somefile.css"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'css', 'ignore'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use partial paths such as an "unique" directory path or partial url.<br />
								Will match using <code>PHP stripos</code> against the <code>href</code> attribute on the <code>link</code> tag
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Merge Inline Styles</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Inline styles to merge</div>	
							<div class="wpraiser-field-description">This will forcefully merge inlined styles that match any of the strings below, in a partial and case insensitive manner.</div>						
							<textarea id="css_merge_style" name="wpraiser_settings[css][merge_style]" placeholder=".class {some:rule;}"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'css', 'merge_style'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use a code snippet, unique to the inlined code you wish to process.
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Low Priority CSS files</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Low Priority CSS files</div>	
							<div class="wpraiser-field-description">This will merge external CSS files into a separate low priority CSS file, when they match any of the strings below, in a partial and case insensitive manner.</div>						
							<textarea id="css_lowp_files" name="wpraiser_settings[css][lowp_files]" placeholder="/css/somefile.css"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'css', 'lowp_files'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use partial paths here, such as an "unique" directory path or partial url.<br />
								Will match using <code>PHP stripos</code> against the <code>href</code> attribute on the <code>link</code> tag
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
			</div>
		</div>
	</div>

	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Garbage Removal' ); ?></h3>
            </div>
			<div class="accordion">

				<h3 class="wpraiser-title2">Remove CSS files</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Remove CSS files</div>	
							<div class="wpraiser-field-description">This will remove CSS files when the href attribute match any of the strings below, in a partial and case insensitive manner.</div>					
							<textarea id="css_remove_file" name="wpraiser_settings[css][remove_file]" placeholder="/css/somefile.css"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'css', 'remove_file'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use partial paths such as an "unique" directory path or partial url.<br />
								Will match using <code>PHP stripos</code> against the <code>href</code> attribute on the <code>link</code> tag
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Strip from Minified CSS Code</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Strip from Minified CSS Code</div>	
							<div class="wpraiser-field-description">This will remove the exact minified code from styles or from inside processed and merged files.</div>						
							<textarea id="css_remove_code" name="wpraiser_settings[css][remove_code]" placeholder=".class {some:rule;}"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'css', 'remove_code'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use a code snippet, unique to the inlined code you wish to process.
							</div>
						</div>
					</div>
				</fieldset>
				</div>

			</div>
		</div>
	</div>
	
	
	<div class="wpraiser-before-saving"></div>
</div>