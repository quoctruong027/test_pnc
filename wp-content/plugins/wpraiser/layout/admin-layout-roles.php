<div id="roles" class="wpraiser-page wpraiser-hidden">
    <div class="wpraiser-section-header">
        <h2 class="wpraiser-title1 wpraiser-icon-roles">User Roles</h2>
    </div>

<?php if(!isset($wpraiser_settings['pref']['nodisclaimer']) || $wpraiser_settings['pref']['nodisclaimer'] != true) { ?>	
        <div class="wpraiser-notice-info">
        <div class="wpraiser-notice-container">
		<div class="wpraiser-notice-suptitle">Information</div>
        <h2 class="wpraiser-notice-title">Anonymous users are always optimized by default</h2>
            <div class="wpraiser-notice-description">
			<div class="wpraiser-notice-suptitle">Notes & Tips</div>				
				<ul>
				<li>You can enable specific optimization features based on the user role</li>  
				<li>These should be disabled for anyone using page editors, for compatibility reasons </li>
				<li>You cannot force scripts optimization and merging, for compatibility reasons </li>
				<li>It is advisable to login with each user role and test all features, once you enable this feature </li>
				</ul>
			</div>
		</div>
        </div>
<?php } ?>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'HTML Optimization' ); ?></h3>
            </div>

			<?php echo wpraiser_get_user_roles_checkboxes('html', 'HTML'); ?>
			
		</div>
	</div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'CSS Optimization' ); ?></h3>
            </div>

			<?php echo wpraiser_get_user_roles_checkboxes('css', 'CSS'); ?>
			
		</div>
	</div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'Lazy Loading Optimization' ); ?></h3>
            </div>

			<?php echo wpraiser_get_user_roles_checkboxes('lazy', 'Lazy Loading'); ?>
			
		</div>
	</div>
	
	<div class="wpraiser-page-row">
        <div class="wpraiser-page-col">
            <div class="wpraiser-option-header">
                <h3 class="wpraiser-title2"><?php _e( 'CDN Optimization' ); ?></h3>
            </div>

			<?php echo wpraiser_get_user_roles_checkboxes('cdn', 'CDN'); ?>
			
		</div>
	</div>
		
	
	<div class="wpraiser-before-saving"></div>
</div>