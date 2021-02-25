<div id="status" class="wpraiser-page wpraiser-hidden">
    <div class="wpraiser-section-header">
        <h2 class="wpraiser-title1 wpraiser-icon-status">Status Page</h2>
    </div>

<?php if(!isset($wpraiser_settings['pref']['nodisclaimer']) || $wpraiser_settings['pref']['nodisclaimer'] != true) { ?>	
        <div class="wpraiser-notice-info">
        <div class="wpraiser-notice-container">
		<div class="wpraiser-notice-suptitle">Information</div>
            <h2 class="wpraiser-notice-title">The log list is limited to the latest 20 results for performance reasons</h2>
            <div class="wpraiser-notice-description">
				<div class="wpraiser-notice-suptitle">Notes & Tips</div>
				<ul>
				<li>When looking at the CSS or JS logs, look for the largest merged files</li>
				<li>Plugins responsible for large JS or CSS themes should be removed or replaced when possible </li>
				<li>The status page is useful for debugging JS and CSS sizes, finding the order or scripts, etc </li>
				</ul>
			</div>
		</div>
        </div>
<?php } ?>	
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Cache Statistics' ); ?> </h3>
				<span class="h3icon wpraiser-refresh-status wpraiser-icon-refresh" title="refresh"></span>
            </div>

			<div class="wpraiser-cache-stats"></div>
			<div class="row-log log-cache wpraiser-textarea"><textarea disabled></textarea></div>
			
		</div>
	</div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'CSS logs' ); ?></h3>
				<span class="h3icon wpraiser-refresh-status wpraiser-icon-refresh" title="refresh"></span>
            </div>
			
			<div class="row-log log-css wpraiser-textarea"><textarea disabled></textarea></div>

		</div>
	</div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'JS logs' ); ?></h3>
				<span class="h3icon wpraiser-refresh-status wpraiser-icon-refresh" title="refresh"></span>
            </div>

			<div class="row-log log-js wpraiser-textarea"><textarea disabled></textarea></div>
				
		</div>
	</div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Server Info' ); ?></h3>
				<span class="wpraiser-refresh-page wpraiser-icon-refresh" title="refresh"></span>
            </div>

			<div class="row-log log-server wpraiser-textarea"><textarea disabled><?php wpraiser_get_generalinfo(); ?></textarea></div>
				
		</div>
	</div>


	<div class="wpraiser-before-saving"></div>
</div>