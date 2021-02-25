<div id="html" class="wpraiser-page wpraiser-hidden">
    <div class="wpraiser-section-header">
        <h2 class="wpraiser-title1 wpraiser-icon-html">HTML</h2>
    </div>

	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'HTML Options' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="html_disable">
								<input type='hidden' name="wpraiser_settings[html][disable]" value="0">
								<input type="checkbox" id="html_disable" name="wpraiser_settings[html][disable]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'html', 'disable')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Disable the HTML Feature</span>
							</label>
						</div>
						<div class="wpraiser-field-description">When this option is enabled, all other settings in this page will be ignored..</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="html_minify">
								<input type='hidden' name="wpraiser_settings[html][minify]" value="0">
								<input type="checkbox" id="html_minify" name="wpraiser_settings[html][minify]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'html', 'minify')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable HTML minification</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Reduce the final file size by minifying your HTML page.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="html_remove_comments">
								<input type='hidden' name="wpraiser_settings[html][remove_comments]" value="0">
								<input type="checkbox" id="html_remove_comments" name="wpraiser_settings[html][remove_comments]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'html', 'remove_comments')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Remove HTML comments</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will remove html comments, including old IE 5 to 9 Conditionals.</div>
					</div>
				</fieldset>

			</div>
	
		</div>
	</div>
		
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Header Cleanup' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="html_remove_generator">
								<input type='hidden' name="wpraiser_settings[html][remove_generator]" value="0">
								<input type="checkbox" id="html_remove_generator" name="wpraiser_settings[html][remove_generator]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'html', 'remove_generator')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Remove generator tags</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Removing meta generator tags reduces the HTML size and helps protecting against version-targeted attacks.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="html_remove_shortlink">
								<input type='hidden' name="wpraiser_settings[html][remove_shortlink]" value="0">
								<input type="checkbox" id="html_remove_shortlink" name="wpraiser_settings[html][remove_shortlink]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'html', 'remove_shortlink')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Remove shortlink tag</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This is used to create an alternative shortlink to your pages and posts and it's usually, unnecessary.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="html_remove_hints">
								<input type='hidden' name="wpraiser_settings[html][remove_hints]" value="0">
								<input type="checkbox" id="html_remove_hints" name="wpraiser_settings[html][remove_hints]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'html', 'remove_hints')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Remove resource hints</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Removing the default resource hints reduces the HTML size and the amount of DNS requests.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="html_remove_favicon">
								<input type='hidden' name="wpraiser_settings[html][remove_favicon]" value="0">
								<input type="checkbox" id="html_remove_favicon" name="wpraiser_settings[html][remove_favicon]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'html', 'remove_favicon')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Remove extra favicon sizes</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will only remove alternative favicon sizes link tags, which are usually not needed.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="html_remove_rsd">
								<input type='hidden' name="wpraiser_settings[html][remove_rsd]" value="0">
								<input type="checkbox" id="html_remove_rsd" name="wpraiser_settings[html][remove_rsd]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'html', 'remove_rsd')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Remove RSD & WLW references</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This is only required if you are publishing content using third party apps or software.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="html_remove_rssref">
								<input type='hidden' name="wpraiser_settings[html][remove_rssref]" value="0">
								<input type="checkbox" id="html_remove_rssref" name="wpraiser_settings[html][remove_rssref]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'html', 'remove_rssref')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Remove RSS feed references</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will leave your RSS feeds enabled, but remove the links pointing to them.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="html_remove_restref">
								<input type='hidden' name="wpraiser_settings[html][remove_restref]" value="0">
								<input type="checkbox" id="html_remove_restref" name="wpraiser_settings[html][remove_restref]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'html', 'remove_restref')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Remove REST API references</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will leave your WordPress REST API enabled, but remove the info pointing to it.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="html_remove_oembed">
								<input type='hidden' name="wpraiser_settings[html][remove_oembed]" value="0">
								<input type="checkbox" id="html_remove_oembed" name="wpraiser_settings[html][remove_oembed]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'html', 'remove_oembed')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Remove oembed references</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will leave your oembed API enabled, but remove the info pointing to it.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="html_remove_emoji">
								<input type='hidden' name="wpraiser_settings[html][remove_emoji]" value="0">
								<input type="checkbox" id="html_remove_emoji" name="wpraiser_settings[html][remove_emoji]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'html', 'remove_emoji')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Remove Emoji default scripts</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will remove emoji support, which are usually not needed for business sites.</div>
					</div>
				</fieldset>

			</div>
	
		</div>
	</div>


	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Garbage Removal' ); ?></h3>
            </div>
			<div class="accordion">

				<h3 class="wpraiser-title2">Remove HTML tags</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Remove HTML tags</div>	
							<div class="wpraiser-field-description">Insert the HTML DOM elements that you wish to remove from the HTML on all pages.</div>						
							<textarea id="html_remove_garbage" name="wpraiser_settings[html][remove_garbage]" placeholder="div[id=remove]"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'html', 'remove_garbage'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use syntax from https://simplehtmldom.sourceforge.io/manual.htm<br />
								You can target a child of a specific html tag, an element with a specific attribute, class or id. 
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