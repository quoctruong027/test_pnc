
<div class="wrap">

<div id="wpraiser-top-notices"><h1 class="screen-reader-text">WP Raiser Settings</h1></div>

<div id="wpraiser-wrapper-out">
<div id="wpraiser-wrapper">

	<header class="wpraiser-menu">
		<div class="wpraiser-header-logo">
			<img class="wpraiser-logo" src="<?php echo plugins_url('assets/img/logo.png', dirname(__FILE__)); ?>" alt="Logo PSE Optimizer" class="wpraiser-header-logo-desktop" width="163" height="44">
			<img class="wpraiser-logo-mobile" src="<?php echo plugins_url('assets/img/logo-mobile.png', dirname(__FILE__)); ?>" alt="Logo PSE Optimizer" class="wpraiser-header-logo-desktop" width="30" height="30">
		</div>
		<div class="wpraiser-header-nav">
			<a href="#dashboard" id="wpraiser-nav-dashboard" class="wpraiser-menu-item wpraiser-menu-active">
				<div class="wpraiser-menu-item-title">Dashboard</div>
				<div class="wpraiser-menu-item-description">Info & License</div>
			</a>
			<a href="#cache" id="wpraiser-nav-cache" class="wpraiser-menu-item">
				<div class="wpraiser-menu-item-title">Page Cache</div>
				<div class="wpraiser-menu-item-description">Time to First Byte</div>
			</a>
			<a href="#html" id="wpraiser-nav-html" class="wpraiser-menu-item">
				<div class="wpraiser-menu-item-title">HTML</div>
				<div class="wpraiser-menu-item-description">Clean & Minify HTML</div>
			</a>
			<a href="#css" id="wpraiser-nav-css" class="wpraiser-menu-item">
				<div class="wpraiser-menu-item-title">CSS / Styles</div>
				<div class="wpraiser-menu-item-description">Merge & Minify CSS</div>
			</a>
			<a href="#js" id="wpraiser-nav-js" class="wpraiser-menu-item">
				<div class="wpraiser-menu-item-title">JavaScript</div>
				<div class="wpraiser-menu-item-description">Merge & Minify Scripts</div>
			</a>
			<a href="#lazy" id="wpraiser-nav-lazy" class="wpraiser-menu-item">
				<div class="wpraiser-menu-item-title">Lazy Loading</div>
				<div class="wpraiser-menu-item-description">Images, iframes, videos</div>
			</a>
			<a href="#cdn" id="wpraiser-nav-cdn" class="wpraiser-menu-item">
				<div class="wpraiser-menu-item-title">CDN Integration</div>
				<div class="wpraiser-menu-item-description">WebP & Loading Time</div>
			</a>		
			<a href="#plugins" id="wpraiser-nav-plugins" class="wpraiser-menu-item">
				<div class="wpraiser-menu-item-title">Plugin Filters</div>
				<div class="wpraiser-menu-item-description">Disable on URL Match</div>
			</a>
			<a href="#roles" id="wpraiser-nav-roles" class="wpraiser-menu-item">
				<div class="wpraiser-menu-item-title">User Roles</div>
				<div class="wpraiser-menu-item-description">User Optimization</div>
			</a>
			<a href="#settings" id="wpraiser-nav-settings" class="wpraiser-menu-item">
				<div class="wpraiser-menu-item-title">Settings</div>
				<div class="wpraiser-menu-item-description">Special settings</div>
			</a>
			<a href="#status" id="wpraiser-nav-status" class="wpraiser-menu-item">
				<div class="wpraiser-menu-item-title">Status</div>
				<div class="wpraiser-menu-item-description">Logs, Developers</div>
			</a>
		</div>
	</header>

	<section class="wpraiser-content">

		<form method="post" id="wpraiser-save-changes">
			
			<?php
				# nounce
				wp_nonce_field('wpraiser_settings_nonce', 'wpraiser_settings_nonce');
				
				# include tab sections
				include_once($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout-cache.php');
				include_once($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout-html.php');
				include_once($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout-css.php');
				include_once($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout-js.php');
				include_once($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout-lazy.php');
				include_once($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout-cdn.php');
				include_once($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout-network.php');
				include_once($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout-plugins.php');
				include_once($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout-roles.php');
				include_once($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout-settings.php');
			?>
			<input type="hidden" name="wpraiser_action" value="save_settings" />
			<input type="submit" class="wpraiser-button wpraiser-save-changes wpraiser-hidden" value="Save Changes">
		</form>
		
	<?php
		# include other tabs
		include_once($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout-dashboard.php');
		include_once($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout-status.php');	
	?>
	
	</section>
</div>
</div>
<div class="clear"></div>
