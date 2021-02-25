<?php

# Exit if accessed directly				
if (!defined('ABSPATH')){ exit(); }	

# update routines for new fields and replacements
function wpraiser_get_updated_field_routines($wpraiser_settings) {
	
	# must have
	if(!is_array($wpraiser_settings)) { return $wpraiser_settings; }
	global $wpraiser_var_plugin_version, $wpraiser_var_dir_path;
	
	# Version 4.1.1 routines start
	
	# merge fields: ("unmergeable_js_lowp" + "unmergeable_inlined_lowp") into "thirdparty_hide
	if(isset($wpraiser_settings['js']) && (isset($wpraiser_settings['js']['unmergeable_js_lowp']) || isset($wpraiser_settings['js']['unmergeable_inlined_lowp']))) {
		$arr = array();
		
		# new field
		if(isset($wpraiser_settings['js']['thirdparty_hide'])) {
			$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['js']['thirdparty_hide']));
		}
		
		# merge 
		if(isset($wpraiser_settings['js']['unmergeable_js_lowp'])) {
			$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['js']['unmergeable_js_lowp']));
		}
		
		# merge 
		if(isset($wpraiser_settings['js']['unmergeable_inlined_lowp'])) {
			$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['js']['unmergeable_inlined_lowp']));
		}
		
		# output
		$wpraiser_settings['js']['thirdparty_hide'] = implode(PHP_EOL, wpraiser_array_order($arr));
		
		# remove utils file
		global $wpraiser_var_inc_dir;
		if(file_exists($wpraiser_var_inc_dir.'utils.php')) { @unlink($wpraiser_var_inc_dir.'utils.php'); }
		
	}
	
	# split page cache uri_path exclusion into js + page cache and rename variable
	if(isset($wpraiser_settings['cache']) && (isset($wpraiser_settings['cache']['uri_path']) || isset($wpraiser_settings['cache']['uri_path']))) {
		
		# new field for js exclusions
		$wpraiser_settings['js']['skip_url'] = implode(PHP_EOL, wpraiser_array_order(wpraiser_string_toarray($wpraiser_settings['cache']['uri_path'])));
		
		# new field for page cache exclusions
		$wpraiser_settings['cache']['skip_url'] = implode(PHP_EOL, wpraiser_array_order(wpraiser_string_toarray($wpraiser_settings['cache']['uri_path'])));
		
	}
	
	# Version 4.1.1 routines end
	
	
	# Version 4.1.4 routines start
	if(isset($wpraiser_settings['css']) && (isset($wpraiser_settings['css']['lowp_append']) || isset($wpraiser_settings['js']['lowp_append']))) {
		
		# remove option
		if(isset($wpraiser_settings['css']['lowp_append'])) {
			unset($wpraiser_settings['css']['lowp_append']);
		}
		
		# remove option
		if(isset($wpraiser_settings['css']['unmergeable_css_lowp'])) {
			unset($wpraiser_settings['css']['unmergeable_css_lowp']);
		}
	
		# remove admin-layout-support.php file
		$fdelp = $wpraiser_var_dir_path . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR; 
		if(file_exists($fdelp.'admin-layout-support.php')) { @unlink($fdelp.'admin-layout-support.php'); }
		if(file_exists($fdelp.'admin-layout-unused.php')) { @unlink($fdelp.'admin-layout-unused.php'); }
	
	}
	
	# Version 4.1.4 routines end
	
	
	# Version 4.1.6 routines start
	if (version_compare($wpraiser_var_plugin_version, '4.1.6', '>=')) {
		$fdelp = $wpraiser_var_dir_path . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR;
		if(file_exists($fdelp.'admin-layout-network.php')) { @unlink($fdelp.'admin-layout-network.php'); }
	}
	# Version 4.1.6 routines end
	
	
	
	# return settings array
	return $wpraiser_settings;
}


