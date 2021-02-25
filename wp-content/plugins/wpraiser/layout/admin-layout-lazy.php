<div id="lazy" class="wpraiser-page wpraiser-hidden">
    <div class="wpraiser-section-header">
        <h2 class="wpraiser-title1 wpraiser-icon-lazy">Lazy Loading</h2>
    </div>

	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Lazy Loading Options' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">

				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="lazy_enable_img" class="">
								<input type='hidden' name="wpraiser_settings[lazy][enable_img]" value="0">
								<input type="checkbox" id="lazy_enable_img" name="wpraiser_settings[lazy][enable_img]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'lazy', 'enable_img')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable Lazy Loading for images</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Lazy Load images that don't match the exclusion filter on this page.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="lazy_enable_bg" class="">
								<input type='hidden' name="wpraiser_settings[lazy][enable_bg]" value="0">
								<input type="checkbox" id="lazy_enable_bg" name="wpraiser_settings[lazy][enable_bg]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'lazy', 'enable_bg')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable Lazy Loading for background images</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Lazy Load background images on inlined style attributes, that don't match the exclusion filter on this page.</div>
					</div>
				
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="lazy_enable_iframe" class="">
								<input type='hidden' name="wpraiser_settings[lazy][enable_iframe]" value="0">
								<input type="checkbox" id="lazy_enable_iframe" name="wpraiser_settings[lazy][enable_iframe]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'lazy', 'enable_iframe')); ?> >
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable Lazy Loading for iFrames</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Reduce initial load time by delivering content only when its in the viewport..</div>
					</div>
				</fieldset>
			</div>
			
		</div>
	</div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Gravatar Options' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="lazy_enable_gravatar" class="">
								<input type='hidden' name="wpraiser_settings[lazy][enable_gravatar]" value="0">
								<input type="checkbox" id="lazy_enable_gravatar" name="wpraiser_settings[lazy][enable_gravatar]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'lazy', 'enable_gravatar')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Cache Gravatars</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Download and cache gravatars locally to improve expires headers over the origin.</div>
					</div>	
				</fieldset>
			</div>
		</div>
	</div>
	
		
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Video & Maps' ); ?></h3>
            </div>

			<div class="wpraiser-fields-container">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-checkbox">
							<label for="lazy_video_wrap_on" class="">
								<input type='hidden' name="wpraiser_settings[lazy][video_wrap_on]" value="0">
								<input type="checkbox" id="lazy_video_wrap_on" name="wpraiser_settings[lazy][video_wrap_on]" value="1" <?php echo wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'lazy', 'video_wrap_on')); ?>>
								<span class="wpraiser-checkmark"></span>
								<span class="wpraiser-checkmark-label">Enable responsive 16:9 video sizes</span>
							</label>
						</div>
						<div class="wpraiser-field-description">Will stretch videos to 100% and force the a 16:9 aspect ratio</div>
					</div>
				</fieldset>

			</div>

			<div class="accordion">

				<h3 class="wpraiser-title2">Allowed Domain Names</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Allowed Domain Names</div>	
							<div class="wpraiser-field-description">Only iFrames matching the domains below can be lazy loaded</div>						
							<textarea id="lazy_video_wrap" name="wpraiser_settings[lazy][video_wrap]" placeholder=""><?php echo wpraiser_get_settings_value($wpraiser_settings, 'lazy', 'video_wrap'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								One domain per line
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
                <h3 class="wpraiser-title2"><?php _e( 'Lazy Loading Exclusions' ); ?></h3>
            </div>
			<div class="accordion">

				<h3 class="wpraiser-title2">Image Exclusions</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Image Exclusions</div>	
							<div class="wpraiser-field-description">Skip Lazy Load for Images matching the following rules</div>						
							<textarea id="lazy_img_exc" name="wpraiser_settings[lazy][img_exc]" placeholder=""><?php echo wpraiser_get_settings_value($wpraiser_settings, 'lazy', 'img_exc'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use syntax from https://simplehtmldom.sourceforge.io/manual.htm.<br />
								You can target a child of a specific html tag, an element with a specific attribute, class or id.
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">Background Image Exclusions</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">Background Image Exclusions</div>	
							<div class="wpraiser-field-description">Skip Lazy Load for Background Images matching the following rules</div>			
							<textarea id="lazy_bg_exc" name="wpraiser_settings[lazy][bg_exc]" placeholder=""><?php echo wpraiser_get_settings_value($wpraiser_settings, 'lazy', 'bg_exc'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use syntax from https://simplehtmldom.sourceforge.io/manual.htm.<br />
								You can target a child of a specific html tag, an element with a specific attribute, class or id.
							</div>
						</div>
					</div>
				</fieldset>
				</div>
				
				<h3 class="wpraiser-title2">iFrame and Video iFrame Exclusions</h3>
				<div class="wpraiser-fields-container-collapsible">
				<fieldset class="wpraiser-fields-container-fieldset">
					<div class="wpraiser-field">
						<div class="wpraiser-textarea">
							<div class="wpraiser-field-title">iFrame and Video iFrame Exclusions</div>	
							<div class="wpraiser-field-description">Skip Lazy Load for iFrames matching the following rules</div>						
							<textarea id="lazy_iframe_exc" name="wpraiser_settings[lazy][iframe_exc]" placeholder=""><?php echo wpraiser_get_settings_value($wpraiser_settings, 'lazy', 'iframe_exc'); ?></textarea>
							<div class="wpraiser-field-description wpraiser-field-description-helper">
								Use syntax from https://simplehtmldom.sourceforge.io/manual.htm.<br />
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