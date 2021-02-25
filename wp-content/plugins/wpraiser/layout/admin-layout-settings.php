<div id="settings" class="wpraiser-page wpraiser-hidden">
    <div class="wpraiser-section-header">
        <h2 class="wpraiser-title1 wpraiser-icon-settings">Global Settings</h2>
    </div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Cache Preferences' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="cache_uploads" class="">
								<input type='hidden' name="wpraiser_settings[cache][uploads]" value="0">
								<input type="checkbox" id="cache_uploads" name="wpraiser_settings[cache][uploads]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'cache', 'uploads')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Store cache files on the uploads directory</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Use the <code>uploads/cache</code> directory, instead of the default <code>wp-content/cache</code> location</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="cache_nohtaccess" class="">
								<input type='hidden' name="wpraiser_settings[cache][nohtaccess]" value="0">
								<input type="checkbox" id="cache_nohtaccess" name="wpraiser_settings[cache][nohtaccess]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'cache', 'nohtaccess')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Disable Page Cache Rewrite Rules</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Will disable the <code>.htaccess</code> rewrite rules and fallback to PHP for serving the cache files</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="cache_min_instant_purge" class="">
								<input type='hidden' name="wpraiser_settings[cache][min_instant_purge]" value="0">
								<input type="checkbox" id="cache_min_instant_purge" name="wpraiser_settings[cache][min_instant_purge]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'cache', 'min_instant_purge')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Purge Minified CSS/JS files instantly</span>
							</label>
						</div>
						<div class="wpraiser-field-description">If enabled, expired cache files will be purged immediately, instead of only after 24 hours</div>
					</div>
				</fieldset>
			</div>
			
		</div>
	</div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Minification Library' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="js_use_phpminify" class="">
								<input type='hidden' name="wpraiser_settings[js][use_phpminify]" value="0">
								<input type="checkbox" id="js_use_phpminify" name="wpraiser_settings[js][use_phpminify]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'js', 'use_phpminify')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Use PHP Minify for minification</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will use the PHP Minify library for JS minification instead of the whitespace only default method.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="js_min_fallback" class="">
								<input type='hidden' name="wpraiser_settings[js][min_fallback]" value="0">
								<input type="checkbox" id="js_min_fallback" name="wpraiser_settings[js][min_fallback]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'js', 'min_fallback')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Use default minification for exclusions</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will try to remove white space for scripts in the minification exclusions.</div>
					</div>
				</fieldset>
			</div>

		</div>
	</div>

	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Lazy Loading Preferences' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="lazy_enable_polyfill" class="">
								<input type='hidden' name="wpraiser_settings[lazy][enable_polyfill]" value="0">
								<input type="checkbox" id="lazy_enable_polyfill" name="wpraiser_settings[lazy][enable_polyfill]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'lazy', 'enable_polyfill')); ?> >
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Add Lazy Loading Polyfill</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This is needed if you need to support Internet Explorer 11 or older browsers</div>
					</div>
				</fieldset>
			</div>
			
		</div>
	</div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Network Requests' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="network_heartbeat_off" class="">
								<input type='hidden' name="wpraiser_settings[network][heartbeat_off]" value="0">
								<input type="checkbox" id="network_heartbeat_off" name="wpraiser_settings[network][heartbeat_off]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'network', 'heartbeat_off')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Disable Heartbeat API in WordPress</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Recommended if you are the only user on your site editing or posting new content.</div>
					</div>
				</fieldset>
			</div>
			
			<div class="accordion">

				<h3 class="wpraiser-title2">Block HTTP or API requests by domain name</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Block HTTP or API requests for the domains below</div>	
							<div class="wpraiser-field-description">You can use this to prevent WordPress from making remote requests that are slowing your page.</div>						
							<textarea id="network_block_req" name="wpraiser_settings[network][block_req]" placeholder="//example.com"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'network', 'block_req'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use a plugin such as <a target="_blank" href="https://wordpress.org/plugins/query-monitor/">Query Monitor</a> to determine which requests are slowing down your site.<br />
								Remote requests can only be detected if plugins and themes make use of the official <a target="_blank" href="https://developer.wordpress.org/plugins/http-api/">WordPress HTTP API</a> for requests.<br />
								This doesn't block any PHP code making remote requests that are not using the official WordPress method. 
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
                <h3 class="wpraiser-title2"><?php _e( 'Plugin Preferences' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="pref_nodisclaimer" class="">
								<input type='hidden' name="wpraiser_settings[pref][nodisclaimer]" value="0">
								<input type="checkbox" id="pref_nodisclaimer" name="wpraiser_settings[pref][nodisclaimer]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'pref', 'nodisclaimer')); ?> >
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Disable Info Widgets</span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will disable disclaimer and notes on top of each settings page</div>
					</div>
				</fieldset>
			</div>
			
		</div>
	</div>


<div class="wpraiser-before-saving"></div>
</div>
