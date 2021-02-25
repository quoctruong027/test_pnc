<?php
/*
Plugin Name: WP Raiser
Plugin URI: https://www.upwork.com/fl/raulpeixoto
Description: Improve your speed score on GTmetrix, Pingdom Tools and Google PageSpeed Insights by merging and minifying CSS and JavaScript files into groups, compressing HTML and other speed optimizations. 
Author: Raul Peixoto
Author URI: https://www.upwork.com/fl/raulpeixoto
Version: 4.1.7
License: GPL2

------------------------------------------------------------------------
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

# Exit if accessed directly				
if (!defined('ABSPATH')){ exit(); }	

# Invalidate OPCache for current file on WP 5.5+
if(function_exists('wp_opcache_invalidate') && stripos(__FILE__, '/wpr.php') !== false) {
	wp_opcache_invalidate(__FILE__, true);
}

# info, variables, paths    
$wpraiser_var_file = __FILE__;                                                # /home/path/plugins/pluginname/wpr.php
$wpraiser_var_basename = plugin_basename($wpraiser_var_file);                 # pluginname/wpr.php
$wpraiser_var_dir_path = plugin_dir_path($wpraiser_var_file);                 # /home/path/plugins/pluginname/
$wpraiser_var_url_path = plugins_url(dirname($wpraiser_var_basename)) . '/';  # https://example.com/wp-content/plugins/pluginname/
$wpraiser_var_plugin_version = get_file_data($wpraiser_var_file, array('Version' => 'Version'), false)['Version'];
$wpraiser_var_inc_dir = $wpraiser_var_dir_path . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR;  # /home/path/plugins/pluginname/inc/
$wpraiser_var_inc_lib = $wpraiser_var_dir_path . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR; # /home/path/plugins/pluginname/libs/

# global functions for backend, frontend, ajax, etc
require_once($wpraiser_var_inc_dir . 'updates.php');
require_once($wpraiser_var_inc_dir . 'common.php');
require_once($wpraiser_var_inc_dir . 'cron.php');

# wp-cli support
if (defined('WP_CLI') && WP_CLI) {
	require_once($wpraiser_var_inc_dir . 'wp-cli.php');
}

# get all options from database, cache paths, domains
$wpraiser_settings = wpraiser_get_settings();
$wpraiser_cache_paths = wpraiser_cachepath();
$wpraiser_urls = array('wp_home'=>site_url(), 'wp_domain'=>str_ireplace(array('http://', 'https://'), '', site_url()));


# only on backend
if(is_admin()) {
	
	# admin functionality
	require_once($wpraiser_var_inc_dir . 'admin.php');
	require_once($wpraiser_var_inc_dir . 'serverinfo.php');
	require_once($wpraiser_var_inc_dir . 'license.php');
	
	# both back and front, as long as the option is enabled
	add_action('init', 'wpraiser_disable_emojis');
	add_action('init', 'wpraiser_stop_heartbeat', 1);
	add_action('plugins_loaded', 'wpraiser_ajax_optimizer', 0);
	add_filter('pre_http_request', 'wpraiser_block_remote_requests', PHP_INT_MAX, 3);
	
	# both backend and frontend, as long as user can manage options
	add_action('admin_bar_menu', 'wpraiser_admintoolbar', 100);
	add_action('init', 'wpraiser_process_cache_purge_request');
		
	# do admin stuff, as long as user can manage options
	add_action('admin_init', 'wpraiser_save_settings');
	add_action('admin_init', 'wpraiser_settings_export');
	add_action('admin_init', 'wpraiser_settings_import');
	add_action('admin_init', 'wpraiser_check_minimum_requirements');
	add_action('admin_head', 'wpraiser_remove_help_tabs');
	add_action('admin_enqueue_scripts', 'wpraiser_add_admin_jscss');
	add_action('admin_menu', 'wpraiser_add_admin_menu');
	add_action('admin_notices', 'wpraiser_show_admin_notice_from_transient');
	add_action('wp_ajax_wpraiser_get_logs', 'wpraiser_get_logs_callback');
	add_action('wp_ajax_wpraiser_get_unused_code_row', 'wpraiser_get_unused_code_row_callback');
		
	# purge everything
	add_action('switch_theme', 'wpraiser_purge_all_action');
	add_action('customize_save', 'wpraiser_purge_all_action');
	add_action('avada_clear_dynamic_css_cache', 'wpraiser_purge_all_action');
	add_action('upgrader_process_complete', 'wpraiser_purge_all_action');
	add_action('update_option_theme_mods_' . get_option('stylesheet'), 'wpraiser_purge_all_action');

	# purge page cache only
	add_action('save_post', 'wpraiser_purge_cache_action');
	add_action('edit_post', 'wpraiser_purge_cache_action');
	add_action('delete_post', 'wpraiser_purge_cache_action');
	add_action('clean_post_cache', 'wpraiser_purge_cache_action');
		
}



# frontend only, any user permissions
if(!is_admin()) {
	
	# frontend functionality
	require_once($wpraiser_var_inc_dir . 'frontend.php');
	require_once($wpraiser_var_inc_dir . 'media.php');
	
	# both back and front, as long as the option is enabled
	add_action('init', 'wpraiser_disable_emojis');
	add_action('init', 'wpraiser_stop_heartbeat', 1);
	add_filter('pre_http_request', 'wpraiser_block_remote_requests', PHP_INT_MAX, 3);
	add_action('wp_enqueue_scripts', 'wpraiser_remove_wp_block_library_css', 100 );
	
	# both backend and frontend, as long as user can manage options
	add_action('admin_bar_menu', 'wpraiser_admintoolbar', 100);
	add_action('init', 'wpraiser_process_cache_purge_request');
		
	# actions for frontend only
	# priority must be lower than zero, for all in one seopack compatibility with title meta tags
	add_action('template_redirect', 'wpraiser_start_buffer', -PHP_INT_MAX);
	
}

