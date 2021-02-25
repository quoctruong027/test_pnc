<?php
/*
Plugin Name: WP Raise Plugin Control Extension
Description: Deactivate WordPress Plugins on specific pages
*/

# Auto Generated file by WP RAISER (do not edit)

# Exit if accessed directly				
if (!defined('ABSPATH')){ exit(); }	

# Invalidate OPCache for current file on WP 5.5+
if(function_exists('wp_opcache_invalidate') && stripos(__FILE__, '/wpr-plugin-control.php') !== false) {
	wp_opcache_invalidate(__FILE__, true);
}

# get unused code settings and exclusions
$wpr_disabling_rules = json_decode('###WPRS###', true);

# aux functions

# open a multiline string, order, filter duplicates and return as array
function mu_wpraiser_string_toarray($value){
	$arr = explode(PHP_EOL, $value);
	$a = array_map('trim', $arr);
	$b = array_filter($a);
	$c = array_unique($b);
	sort($c);
	return $c;
}


# start filtering
if(is_array($wpr_disabling_rules) && count($wpr_disabling_rules) > 0) {
	add_filter( 'option_active_plugins', 'wpraiser_disable_plugin_specific_pages' );
}

# disable plugin in specific pages
function wpraiser_disable_plugin_specific_pages($plugins) {
	
	# must have
	if(!isset($_SERVER['REQUEST_URI'])) {
		return $plugins;
	}
	
	# filter url
	$filtered_uri = str_replace('//', '/', str_replace('..', '', preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', strtok($_SERVER['REQUEST_URI'], '?'))));
	
	# If we are in the admin area do not touch anything
	if ( (function_exists('is_admin') && is_admin()) || substr($filtered_uri, 0, 10) == '/wp-admin/' ) {
		return $plugins;
	}
		
	# get rules
	global $wpr_disabling_rules;
	
	# loop through all active plugins 
	foreach ($plugins as $k=>$plugin) {
		
		# process
		$settings_key = dirname($plugin);
		if(isset($wpr_disabling_rules[$settings_key]) && !empty($wpr_disabling_rules[$settings_key])) {
			$arr = mu_wpraiser_string_toarray($wpr_disabling_rules[$settings_key]);
			if(is_array($arr) && count($arr) > 0) {
				foreach ($arr as $rule) {
					
					# exact match
					if($filtered_uri == $rule) { 
						if(isset($plugins[$k])) { 
							unset($plugins[$k]);
						}
					}
					
					# match middle
					if(substr($rule, -1) == '*' && substr($rule, 1) == '*') {
						if(stripos($filtered_uri, trim($rule, '*')) !== false) { 
							if(isset($plugins[$k])) { 
								unset($plugins[$k]);
							}
						}
					} 
					
					# match beginning 
					if(substr($rule, -1) == '*' && substr($rule, 1) != '*') {
						if(substr($filtered_uri, 0, strlen(trim($rule, '*'))) == trim($rule, '*')) { 
							if(isset($plugins[$k])) { 
								unset($plugins[$k]);
							}
						}
					}
					
					# match end 
					if(substr($rule, -1) != '*' && substr($rule, 1) == '*') {
						if(substr($filtered_uri, -strlen(trim($rule, '*'))) == trim($rule, '*')) { 
							if(isset($plugins[$k])) { 
								unset($plugins[$k]);
							}
						}
					}	
					
				}
			}
		}
		
	}
		
	# return
	return $plugins;
}

