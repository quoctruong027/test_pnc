<div id="cache" class="wpraiser-page wpraiser-hidden">
    <div class="wpraiser-section-header">
        <h2 class="wpraiser-title1 wpraiser-icon-cache">Page Cache</h2>
    </div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Cache Settings' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="cache_enable_page" class="">
								<input type='hidden' name="wpraiser_settings[cache][enable_page]" value="0">
								<input type="checkbox" id="cache_enable_page" name="wpraiser_settings[cache][enable_page]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'cache', 'enable_page')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable the Page Cache</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Creates a static cache file for every different URL</div>
					</div>

					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="cache_enable_mobile" class="">
								<input type='hidden' name="wpraiser_settings[cache][enable_mobile]" value="0">
								<input type="checkbox" id="cache_enable_mobile" name="wpraiser_settings[cache][enable_mobile]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'cache', 'enable_mobile')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable Separate Mobile Cache </span>
							</label>
						</div>
						<div class="wpraiser-field-description">This will use <code>wp_is_mobile()</code> to serve a different cache file for mobile users</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="cache_enable_vary_cookie" class="">
								<input type='hidden' name="wpraiser_settings[cache][enable_vary_cookie]" value="0">
								<input type="checkbox" id="cache_enable_vary_cookie" name="wpraiser_settings[cache][enable_vary_cookie]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'cache', 'enable_vary_cookie')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable Separate Cookie Cache</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Will allow you to define a cookie name and create an unique cache depending on it's value</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="cache_enable_geolocation" class="">
								<input type='hidden' name="wpraiser_settings[cache][enable_geolocation]" value="0">
								<input type="checkbox" id="cache_enable_geolocation" name="wpraiser_settings[cache][enable_geolocation]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'cache', 'enable_geolocation')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable Separate Geolocation Cache</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Will allow you to define countries that need to be cached separately</div>
					</div>
				</fieldset>
			</div>
			
		</div>
	</div>
	
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Cache Lifespan' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-number">
						<input type="number" min="1" id="cache_lifespan" name="wpraiser_settings[cache][lifespan]" value="<?php echo wpraiser_get_settings_value($wpraiser_settings, 'cache', 'lifespan'); ?>">
					</div>
					<div class="wpraiser-select wpraiser-select-lifespan">
						<select id="cache_lifespan_unit" name="wpraiser_settings[cache][lifespan_unit]">
							<option value="60" <?php echo wpraiser_get_settings_select(wpraiser_get_settings_value($wpraiser_settings, 'cache', 'lifespan_unit'), 60); ?>>Minutes</option>
							<option value="3600" <?php echo wpraiser_get_settings_select(wpraiser_get_settings_value($wpraiser_settings, 'cache', 'lifespan_unit'), 3600); ?>>Hours</option>
							<option value="86400" <?php echo wpraiser_get_settings_select(wpraiser_get_settings_value($wpraiser_settings, 'cache', 'lifespan_unit'), 86400); ?>>Days</option>
						</select>
					</div>
					<div class="wpraiser-field-description wpraiser-field-description-left">Cache files older than the specified lifespan will be deleted.</div>
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

				<h3 class="wpraiser-title2">Disable Page Cache by URI Path</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">No page cache by URI Path</div>	
							<div class="wpraiser-field-description">Disable page caching when the URI Path match the following paths</div>
							<textarea id="cache_skip_url" name="wpraiser_settings[cache][skip_url]" placeholder="/checkout/"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'cache', 'skip_url'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use exact URI Paths for exact matches, or (prepend/append) the * char for a case insensitive substring match.<br />
								The * wildcard is only supported at the beginning / end of the URI Path you insert.
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Disable Page Cache by Cookie Name</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Cookie Names</div>	
							<div class="wpraiser-field-description">Disable page caching and other optimizations when a cookie with the following name exists in the visitor's browser.</div>
							<textarea id="cache_cookies" name="wpraiser_settings[cache][cookies]" placeholder="wordpress_logged_in"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'cache', 'cookies'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use exact cookie names for exact matches, or (prepend/append) the * char for a case insensitive substring match.<br />
								The * wildcard is only supported at the beginning / end of the cookie name you insert.
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Ignore Special Query Strings</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Ignore Query Strings</div>	
							<div class="wpraiser-field-description">Ignored query string parameters with standard caching</div>						
							<textarea id="cache_ignore_qs" name="wpraiser_settings[cache][ignore_qs]" placeholder="utm_source"><?php echo wpraiser_get_settings_value($wpraiser_settings, 'cache', 'ignore_qs'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								For all of the parameters listed above, we will ignore the query string and serve the standard cache file.<br />
								If there are other query strings in the url, the plugin not serve a cache file for compatibility reasons.
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
                <h3 class="wpraiser-title2"><?php _e( 'Vary Cache' ); ?></h3>
            </div>
			<div class="accordion">

				<h3 class="wpraiser-title2">Different Page Cache by Cookie Name and Value</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Cookie Name</div>
							<div class="wpraiser-field-description">Use the exact cookie name and the cache file name will depend on it's value.</div>	
							<div class="wpraiser-text-limited">
								<input type="text" id="cache_vary_cookie" name="wpraiser_settings[cache][vary_cookie]" placeholder="your_cookie_name" value="<?php echo wpraiser_get_settings_value($wpraiser_settings, 'cache', 'vary_cookie'); ?>">
							</div>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								The Cache Key used for generating the Page Cache, will be an md5 hash of it's value.
							</div>	
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Different Page Cache by Country Code</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Country Cache</div>	
							<div class="wpraiser-field-description">Use the same cache for all countries, and create an separate version for the ones below</div>
							<textarea id="cache_vary_geo" name="wpraiser_settings[cache][vary_geo]" placeholder=""><?php echo wpraiser_get_settings_value($wpraiser_settings, 'cache', 'vary_geo'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Please use two letter country codes as specified by <a target="_blank" href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2">ISO 3166-1 Alpha 2</a> format.<br />
								All other countries will be served the default standard cache file.								
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