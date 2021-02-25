<div id="js" class="wpraiser-page wpraiser-hidden">
    <div class="wpraiser-section-header">
        <h2 class="wpraiser-title1 wpraiser-icon-js">JavaScript</h2>
    </div>

<?php if(!isset($wpraiser_settings['pref']['nodisclaimer']) || $wpraiser_settings['pref']['nodisclaimer'] != true) { ?>	
        <div class="wpraiser-notice-info">
        <div class="wpraiser-notice-container">
		<div class="wpraiser-notice-suptitle">Disclaimer</div>
            <h2 class="wpraiser-notice-title">Merging and minifying JS files may sometimes cause errors<br />Please be careful with your changes and read the notes below</h2>
            <div class="wpraiser-notice-description">
				<div class="wpraiser-notice-suptitle">Notes & Tips</div>
				<ul>
				<li>Some JS files cannot be deferred or loaded async, else they may stop doing what needs to be done when forcefully deferred </li>
				<li>For compatibility reasons, it's always advisable to load jQuery and jQuery Migrate render blocking in the header</li>
				<li>For better performance, try to remove the plugins with the largest JS files as well as to keep third party scripts to minimum</li>
				<li>Use incognito mode on Google Chrome and look at the console log for potential errors with merging and minification</li>
				<li>Some scripts may have mixed encodings or complex regex rules that break with PHP Minify, but likely can still be merged without minification</li>
				</ul>
			</div>
		</div>
        </div>
<?php } ?>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'JS Options' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="js_disable" class="">
								<input type='hidden' name="wpraiser_settings[js][disable]" value="0">
								<input type="checkbox" id="js_disable" name="wpraiser_settings[js][disable]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'js', 'disable')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Disable the JS Feature</span>
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
                <h3 class="wpraiser-title2"><?php _e( 'jQuery Options' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="js_upgrade_jquery" class="">
								<input type='hidden' name="wpraiser_settings[js][upgrade_jquery]" value="0">
								<input type="checkbox" id="js_upgrade_jquery" name="wpraiser_settings[js][upgrade_jquery]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'js', 'upgrade_jquery')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Upgrade jQuery and jQuery Migrate to version v3+</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This can cause errors or loss of functionality, so it needs to be tested thoroughly after enabling.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="js_passive_listeners" class="">
								<input type='hidden' name="wpraiser_settings[js][passive_listeners]" value="0">
								<input type="checkbox" id="js_passive_listeners" name="wpraiser_settings[js][passive_listeners]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'js', 'passive_listeners')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Force use of <code>touchstart</code> passive listeners on the jQuery library</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Please note that there <a target="_blank" href="https://github.com/jquery/jquery/issues/2871">are cases</a> where you need a non-passive listener</div>
					</div>
				</fieldset>
			</div>
			
		</div>
	</div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'JS Minification' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="js_min_files" class="">
								<input type='hidden' name="wpraiser_settings[js][min_files]" value="0">
								<input type="checkbox" id="js_min_files" name="wpraiser_settings[js][min_files]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'js', 'min_files')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable minification for merged JS files</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will minify all merged scripts that you specifically add to the header, footer or low priority sections, unless you explicitly exclude them from minification.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="js_min_inline" class="">
								<input type='hidden' name="wpraiser_settings[js][min_inline]" value="0">
								<input type="checkbox" id="js_min_inline" name="wpraiser_settings[js][min_inline]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'js', 'min_inline')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable minification for Inline JavaScript</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will minify all inlined scripts it finds on the page, unless you explicitly exclude them from minification.</div>
					</div>
				</fieldset>
			</div>	
	
		</div>
	</div>
	
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Exclusions' ); ?></h3>
            </div>
			<div class="accordion">

				<h3 class="wpraiser-title2">Disable JS processing by URI Path</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">No JS processing by URI Path</div>	
							<div class="wpraiser-field-description">Disable JS processing when the URI Path match the following paths</div>
							<textarea id="js_skip_url" name="wpraiser_settings[js][skip_url]" placeholder="/checkout/"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'js', 'skip_url'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use exact URI Paths for exact matches, or (prepend/append) the * char for a case insensitive substring match.<br />
								The * wildcard is only supported at the beginning / end of the URI Path you insert.
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Skip Minification for Merged JS files</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Skip Minification for Merged JS files</div>	
							<div class="wpraiser-field-description">Will allow merging but you can prevent minification for specific JS files that match the paths below</div>						
							<textarea id="js_skip_min" name="wpraiser_settings[js][skip_min]" placeholder="some js code or /path/to/file.js"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'js', 'skip_min'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use partial paths such as an "unique" directory path or partial url.<br />
								Will match using <code>PHP stripos</code> against the <code>src</code> attribute on the <code>script</code> tag
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Skip Minification for Inlined Scripts</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Skip Minification for Inlined Scripts</div>	
							<div class="wpraiser-field-description">Will prevent minification for Inlined Scripts that match the code below</div>						
							<textarea id="skip_min_inline" name="wpraiser_settings[js][skip_min_inline]" placeholder="some js code"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'js', 'skip_min_inline'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use a code snippet, unique to the inlined code you wish to process.<br />
								Will match using <code>PHP stripos</code> against the <code>innerHTML</code> of the <code>script</code> tag
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
                <h3 class="wpraiser-title2"><?php _e( 'Merge and Defer Scripts' ); ?></h3>
            </div>
			<div class="accordion">
			
				<h3 class="wpraiser-title2">Merge render blocking JS files</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Merge render blocking JS files</div>	
							<div class="wpraiser-field-description">This will merge all JS files that match the paths below</div>			
							<textarea id="js_merge_files_header" name="wpraiser_settings[js][merge_files_header]" placeholder="/path/to/file.js"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'js', 'merge_files_header'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use partial paths such as an "unique" directory path or partial url.<br />
								Will match using <code>PHP stripos</code> against the <code>src</code> attribute on the <code>script</code> tag
							</div>
						</div>
					</div>
				</fieldset>
				</div>

				<h3 class="wpraiser-title2">Merge and Defer JS files</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Merge and Defer JS files</div>	
							<div class="wpraiser-field-description">This will merge JS files matching any of the strings below, in a partial and case insensitive manner</div>						
							<textarea id="js_merge_files_footer" name="wpraiser_settings[js][merge_files_footer]" placeholder="/path/to/file.js"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'js', 'merge_files_footer'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use partial paths such as an "unique" directory path or partial url.<br />
								Will match using <code>PHP stripos</code> against the <code>src</code> attribute on the <code>script</code> tag
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Inline JavaScript Deferred Dependencies</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Inline JavaScript Deferred Dependencies</div>	
							<div class="wpraiser-field-description">Inline JavaScript that needs to wait until after the <code>window.load</code> event</div>						
							<textarea id="js_delay_inline_footer" name="wpraiser_settings[js][delay_inline_footer]" placeholder="some js code"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'js', 'delay_inline_footer'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Delay Inline JavaScript code until right after deferred and async scripts finish loading.<br />
								Will match using <code>PHP stripos</code> against the <code>innerHTML</code> of the <code>script</code> tag
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
                <h3 class="wpraiser-title2"><?php _e( 'Third Party Scripts' ); ?></h3>
            </div>
			<div class="accordion">

				<h3 class="wpraiser-title2">Delay JavaScript Execution for Scripts</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Delay JavaScript Execution for Scripts</div>	
							<div class="wpraiser-field-description">Prevent JavaScript execution before user interaction (this will still load the scripts without user interaction, within 5 seconds after page load).</div>						
							<textarea id="js_thirdparty_delay" name="wpraiser_settings[js][thirdparty_delay]" placeholder="some js code"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'js', 'thirdparty_delay'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Will match using <code>PHP stripos</code> against the <code>src</code> attribute and the <code>innerHTML</code> on the <code>script</code> tag
								</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Remove JavaScript Execution on Inline Scripts</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Remove JavaScript Execution on Inline Scripts</div>	
							<div class="wpraiser-field-description">Will exclude JavaScript execution for JS files by <code>src attribute</code> or inline code on the <code>innerHTML</code> for anonymous users.</div>						
							<textarea id="js_thirdparty_hide" name="wpraiser_settings[js][thirdparty_hide]" placeholder="some js code"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'js', 'thirdparty_hide'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								This method overwrites the delay method for any inline JavaScript code it finds.<br />
								Will match using <code>PHP stripos</code> against the <code>src</code> attribute on the <code>script</code> tag
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Remove JavaScript Execution inside merged JS Files</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Remove JavaScript Execution inside merged JS Files</div>	
							<div class="wpraiser-field-description">Will remove JavaScript execution for the specified, merged JS files  by <code>src attribute</code> while merging.</div>						
							<textarea id="js_thirdparty_merge_hiden" name="wpraiser_settings[js][thirdparty_merge_hiden]" placeholder="some js code"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'js', 'thirdparty_merge_hiden'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Will match using <code>PHP stripos</code> against the <code>src</code> attribute on the <code>script</code> tag		
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

				<h3 class="wpraiser-title2">Remove JS files</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Remove JS files</div>	
							<div class="wpraiser-field-description">This will remove JS files when the href attribute match any of the strings below, in a partial and case insensitive manner.</div>						
							<textarea id="js_remove_scripts" name="wpraiser_settings[js][remove_scripts]" placeholder="/path/to/file.js"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'js', 'remove_scripts'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								This takes priority over anything else.<br />
								Will match using <code>PHP stripos</code> against the <code>src</code> attribute on the <code>script</code> tag
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Remove Inline JavaScript</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Remove Inline JavaScript</div>	
							<div class="wpraiser-field-description">This will remove any Inlined Scripts tags when the innerHTML match any of the strings below, in a partial and case insensitive manner.</div>						
							<textarea id="js_remove_inlined" name="wpraiser_settings[js][js_remove_inlined]" placeholder="some js code"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'js', 'js_remove_inlined'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use a code snippet, unique to the inlined code you wish to process.<br />
								Will match using <code>PHP stripos</code> against the <code>innerHTML</code> of the <code>script</code> tag
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