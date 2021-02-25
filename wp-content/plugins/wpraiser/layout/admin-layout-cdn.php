<div id="cdn" class="wpraiser-page wpraiser-hidden">
    <div class="wpraiser-section-header">
        <h2 class="wpraiser-title1 wpraiser-icon-cdn">CDN Integration</h2>
    </div>
		
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'CDN Options' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">

				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="cdn_enable" class="">
								<input type='hidden' name="wpraiser_settings[cdn][enable]" value="0">
								<input type="checkbox" id="cdn_enable" name="wpraiser_settings[cdn][enable]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'cdn', 'enable')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable the CDN Feature</span>
							</label>
						</div>
						<div class="wpraiser-field-description">You must specify a valid CDN domain name and fill up the integration section for it to work.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="cdn_enable_css" class="">
								<input type='hidden' name="wpraiser_settings[cdn][enable_css]" value="0">
								<input type="checkbox" id="cdn_enable_css" name="wpraiser_settings[cdn][enable_css]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'cdn', 'enable_css')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable CDN for generated CSS files</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will load our CSS cache files from the CDN, along with all relative resources inside it.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="cdn_enable_js" class="">
								<input type='hidden' name="wpraiser_settings[cdn][enable_js]" value="0">
								<input type="checkbox" id="cdn_enable_js" name="wpraiser_settings[cdn][enable_js]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'cdn', 'enable_js')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable CDN for generated JS files</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will load our JS cache files from the CDN, along with all relative resources inside it.</div>
					</div>
				</fieldset>
			</div>
	
		</div>
	</div>
	
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'CDN Integration' ); ?></h3>
            </div>
			<div class="accordion">
			
				<h3 class="wpraiser-title2">CDN URL</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Your CDN domain</div>
							<div class="wpraiser-field-description">Not required for services like Cloudflare or Sucuri.</div>	
							<div class="wpraiser-text-limited">
								<input type="text" id="cdn_url" name="wpraiser_settings[cdn][url]" placeholder="cdn.example.com" value="<?php echo wpraiser_get_settings_value($wpraiser_settings, 'cdn', 'url'); ?>">
							</div>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								We recommend using <a target="_blank" href="https://bunnycdn.com/?ref=b9dy9ec340">BunnyCDN</a> service (with the Optimizer feature enabled) to efficiently encode and serve images in next-gen formats.<br />
								Alternatively, any paid plan from <a target="_blank" href="https://cloudflare.com/">Cloudflare</a> will allow also allow you serve images in next-gen formats without any changes here.
							</div>	
						</div>
					</div>
				</fieldset>
				</div>

				<h3 class="wpraiser-title2">CDN Replacements</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">CDN Replacements</div>	
							<div class="wpraiser-field-description">Insert the HTML DOM elements where you wish to replace your domain with the CDN domain.</div>
							<textarea id="cdn_integration" name="wpraiser_settings[cdn][integration]" placeholder="img[src], img[data-src], img[data-srcset], image"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'cdn', 'integration'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use syntax from https://simplehtmldom.sourceforge.io/manual.htm<br />
								You can target a child of a specific html tag, an element with a specific attribute, class or id.<br />
								If you need to exclude a specific resource from the CDN, you can create a <code>bypass rule</code> on the CDN provider.
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">CDN Exclusions</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Ignore Files by URL</div>	
							<div class="wpraiser-field-description">Disable CDN processing when the URI Path of a static assets matches the following paths or filenames</div>
							<textarea id="cdn_skip_asset" name="wpraiser_settings[cdn][skip_asset]" placeholder="/uploads/tmp/"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'cdn', 'skip_asset'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use exact URI Paths for exact matches, or (prepend/append) the * char for a case insensitive substring match.<br />
								The * wildcard is only supported at the beginning / end of the URI Path you insert.
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