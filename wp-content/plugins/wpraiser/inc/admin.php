<?php

# Exit if accessed directly				
if (!defined('ABSPATH')){ exit(); }	


# functions needed only for backend

# return checked, or empty for checkboxes in admin
function wpraiser_get_settings_checkbox($value) {
	if($value == 1) { return 'checked'; }
	return '';
}

# return selected="selected" or empty for select boxes
function wpraiser_get_settings_select($setting, $value){
	if($setting == $value) { return 'selected="selected"'; }
	return '';
}


# save plugin settings on wp-admin
function wpraiser_save_settings() {
	
	# save license
	if(isset($_POST['wpraiser_action']) && isset($_POST['wpraiser_license_nonce']) && $_POST['wpraiser_action'] == 'license') {
		
		if(!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.'), __('Error:'), array('response'=>200)); 
		}
		
		if(!wp_verify_nonce($_POST['wpraiser_license_nonce'], 'wpraiser_license_nonce')) {
			wp_die( __('<h2>Invalid nounce.</h2> <br / >Please <a href="javascript:history.back()">go back</a> and try again.'), __('Error:'), array('response'=>200)); 
		}
		
		# save license
		if(isset($_POST['license']) && is_array($_POST['license'])) {
			if(isset($_POST['license']['serial']) || isset($_POST['license']['identifier'])) {
				
				# sanitize
				$wpr_serial_post = filter_var($_POST['license']['serial'], FILTER_SANITIZE_STRING);	
				$wpr_identifier_post = filter_var($_POST['license']['identifier'], FILTER_SANITIZE_EMAIL);
				$wpr_license = array('serial'=>$wpr_serial_post, 'identifier'=>$wpr_identifier_post);
				
				# delete if empty
				if(empty($wpr_serial_post) && empty($wpr_identifier_post)) {
					delete_option('wpraiser_license');
					add_settings_error( 'wpraiser_admin_notice', 'wpraiser_admin_notice', 'License has been deleted, as per your empty submission!', 'success' );
				}
				
				# show up on the form what was just submitted
				if(!empty($wpr_serial_post) && !empty($wpr_identifier_post)) {
					
					global $wpraiser_license;
					$wpraiser_license = json_encode($wpr_license);
						
					# verify serial
					if(strlen(str_replace('-', '', $wpr_serial_post)) != 40 || !ctype_xdigit(str_replace('-', '', $wpr_serial_post))) {
						add_settings_error( 'wpraiser_admin_notice', 'wpraiser_admin_notice', 'The <code>License Key</code> code inserted is invalid!', 'error' );
					}				
					
					# verify identifier
					if(!filter_var($wpr_identifier_post, FILTER_VALIDATE_EMAIL)) {
						add_settings_error( 'wpraiser_admin_notice', 'wpraiser_admin_notice', 'Your <code>Unique Identifier</code> code must be a valid email address!', 'error' );
					}
					
					# else sanitize and get expires data from server
					if(filter_var($wpr_identifier_post, FILTER_VALIDATE_EMAIL) && strlen(str_replace('-', '', $wpr_serial_post)) == 40 && ctype_xdigit(str_replace('-', '', $wpr_serial_post))) {
		
						# verify with the server
						$verify = wpraiser_license_activate($wpr_license);
						if($verify === true) {
							update_option('wpraiser_license', $wpraiser_license, false);
							add_settings_error( 'wpraiser_admin_notice', 'wpraiser_admin_notice', 'License saved. Thank you for updating!', 'success' );
						} else {
							
							# show error
							if($verify == false && is_string($verify)) { 
								$error = 'Your <code>License Key</code> and <code>Unique Identifier</code> codes are not authorized for this domain!'; 
							} else { 
								$error = $verify; 
							}
							add_settings_error( 'wpraiser_admin_notice', 'wpraiser_admin_notice', $error, 'error' );
						}
						
					}
				
				}
				
			}			
		}
		
	}

	
	# save settings
	if(isset($_POST['wpraiser_action']) && isset($_POST['wpraiser_settings_nonce']) && $_POST['wpraiser_action'] == 'save_settings') {
		
		if(!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.'), __('Error:'), array('response'=>200)); 
		}
		
		if(!wp_verify_nonce($_POST['wpraiser_settings_nonce'], 'wpraiser_settings_nonce')) {
			wp_die( __('<h2>Invalid nounce.</h2> <br / >Please <a href="javascript:history.back()">go back</a> and try again.'), __('Error:'), array('response'=>200)); 
		}
		
		# update wpraiser_settings in the global scope
		if(isset($_POST['wpraiser_settings']) && is_array($_POST['wpraiser_settings'])) {
			
			# sanitize recursively
			if(is_array($_POST['wpraiser_settings'])) {
				foreach ($_POST['wpraiser_settings'] as $group=>$arr) {
					if(is_array($arr)) {
						foreach ($arr as $k=>$v) {
							
							# only numeric, string or arrays allowed at this level
							if(!is_string($v) && !is_numeric($v) && !is_array($v)) { $_POST['wpraiser_settings'][$group][$k] = ''; }
							
							# numeric fields, only positive integers allowed 
							if(is_numeric($v)) { $_POST['wpraiser_settings'][$group][$k] = abs(intval($v)); }
							
							# sanitize text area content
							if(is_string($v)) { $_POST['wpraiser_settings'][$group][$k] = strip_tags($v); }
							
							# clean cdn url
							if($group == 'cdn' && $k == 'url') { 
								$_POST['wpraiser_settings'][$group][$k] = trim(trim(str_replace(array('http://', 'https://'), '', $v), '/'));
							}
												
						}
					}
				}
			}
			
			# get mandatory default exclusions
			global $wpraiser_settings;
			$wpraiser_settings = wpraiser_get_default_settings($_POST['wpraiser_settings']);
			
			# check if we have cache settings on form submission
			if( isset($wpraiser_settings['cache']['enable_page']) && isset($wpraiser_settings['cache']['lifespan']) && isset($wpraiser_settings['cache']['lifespan_unit']) ) {
				
				# default settings (disable cache)
				$enable_wp_config = false;
				$enable_advanced_cache = false;
				$enable_htaccess = false;
				
				# enable cache if defined on settings
				if($wpraiser_settings['cache']['enable_page'] == true) { 
					$enable_wp_config = true;
					$enable_advanced_cache = true;	
					$enable_htaccess = true;					
				}
				
				# disable .htaccess rules when needed
				if(isset($wpraiser_settings['cache']['nohtaccess']) && $wpraiser_settings['cache']['nohtaccess'] == true) {
					$enable_htaccess = false;
				}
				
				# disable .htaccess when vary cache on cookie name is active and we need to check the unique value hash
				if(isset($wpraiser_settings['cache']['enable_vary_cookie']) && $wpraiser_settings['cache']['enable_vary_cookie'] == true) {
					if(isset($wpraiser_settings['cache']['vary_cookie']) && !empty($wpraiser_settings['cache']['vary_cookie'])) {
						$enable_htaccess = false;
					}
				}
					
				# process cache settings
				wpraiser_edit_wp_config($enable_wp_config);
				wpraiser_edit_advanced_cache($enable_advanced_cache);
				wpraiser_edit_htaccess($enable_htaccess);
				
				# purge caches
				wpraiser_purge_cache();
				wpraiser_purge_others();				

			}
			
			
			# check if we have plugin filters on form submission
			if(isset($wpraiser_settings['unplug']) && !empty($wpraiser_settings['unplug'])) {
				
				# default settings (disable mu plugin)
				$enable_mu_plugin = false;
				
				# enable cache if defined on settings
				if($wpraiser_settings['unplug']['enable'] == true) { 
					$enable_mu_plugin = true;				
				}
					
				# process cache settings
				wpraiser_edit_mu_plugin($enable_mu_plugin);
				
				# purge caches
				wpraiser_purge_cache();
				wpraiser_purge_others();				

			}			
			
			
			# save settings
			update_option('wpraiser_settings', json_encode($wpraiser_settings), false);
			add_settings_error( 'wpraiser_admin_notice', 'wpraiser_admin_notice', 'Settings saved successfully!', 'success' );
		
		} else {
			wp_die( __('<h2>Invalid data!</h2> <br / >Please <a href="javascript:history.back()">go back</a> and try again.'), __('Error:'), array('response'=>200)); 
		}
	}
}


# check for minimum requirements and prevent activation or disable if not fully compatible
function wpraiser_check_minimum_requirements() {
	if(current_user_can('manage_options')) {
		
		# defaults
		$error = '';

		# php version requirements
		if (version_compare( PHP_VERSION, '5.6', '<' )) { 
			$error = 'WP Raiser requires PHP 5.6 or higher. You’re still on '. PHP_VERSION; 
		}

		# php extension requirements	
		if (!extension_loaded('mbstring')) { 
			$error = 'WP Raiser requires the PHP mbstring module to be installed on the server.'; 
		}
		
		# wp version requirements
		if ( version_compare( $GLOBALS['wp_version'], '4.5', '<' ) ) {
			$error = 'WP Raiser requires WP 4.5 or higher. You’re still on ' . $GLOBALS['wp_version']; 
		}
		
		# cache permissions		
		global $wpraiser_cache_paths;
		if(is_dir($wpraiser_cache_paths['cache_base_dir']) && !is_writable($wpraiser_cache_paths['cache_base_dir'])) {
			$error = 'WP Raiser needs writing permissions on '.$wpraiser_cache_paths['cache_base_dir'];
		}
		
		# deactivate plugin forcefully
		global $wpraiser_var_basename;
		if ((is_plugin_active($wpraiser_var_basename) && !empty($error)) || !empty($error)) { 
		if (isset($_GET['activate'])) { unset($_GET['activate']); }
			deactivate_plugins($wpraiser_var_basename); 
			add_settings_error( 'wpraiser_admin_notice', 'wpraiser_admin_notice', $error, 'success' );
		}
		
	}
}


# add settings link on plugins listing page
add_filter("plugin_action_links_".$wpraiser_var_basename, 'wpraiser_min_settings_link' );
function wpraiser_min_settings_link($links) {
	global $wpraiser_var_basename;
	if (is_plugin_active($wpraiser_var_basename)) { 
		$settings_link = '<a href="'.admin_url('admin.php?page=wpraiser#dashboard').'">Settings</a>'; 
		array_unshift($links, $settings_link); 
	}
return $links;
}


# remove help tabs on our wp-admin pages
function wpraiser_remove_help_tabs() {
	if(current_user_can('manage_options')) {
		$screen = get_current_screen();
		if( $screen->base === "settings_page_wpraiser" ) {
		echo '<style type="text/css">#contextual-help-link-wrap {display: none !important;}</style>';
		}
	}
}


# Enqueue plugin UI CSS and JS files
function wpraiser_add_admin_jscss($hook) {
	if(current_user_can('manage_options')) {
		
		# logic
		if ('settings_page_wpraiser' != $hook) { return; }
		global $wpraiser_var_dir_path, $wpraiser_var_url_path;
		
		# style
		wp_enqueue_style('wpraiser', $wpraiser_var_url_path . 'assets/wpraiser.css', array(), filemtime($wpraiser_var_dir_path.'assets'. DIRECTORY_SEPARATOR .'wpraiser.css'));
		
		# ui
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		
		# js
		wp_enqueue_script('wpraiser', $wpraiser_var_url_path . 'assets/wpraiser.js', array('jquery'), filemtime($wpraiser_var_dir_path.'assets'. DIRECTORY_SEPARATOR .'wpraiser.js'));
	}
}


# create sidebar admin menu and add templates to admin
function wpraiser_add_admin_menu() {
	if (current_user_can('manage_options')) {
		add_options_page('WP Raiser Settings', 'WP Raiser', 'manage_options', 'wpraiser', 'wpraiser_add_settings_admin');
	}
}


# print admin notices when needed (json)
function wpraiser_show_admin_notice_from_transient() {
	if(current_user_can('manage_options')) {
		$inf = get_transient('wpraiser_admin_notice');
		if($inf != false && !empty($inf)) {
			$jsonarr = json_decode($inf, true);
			if(!is_null($jsonarr) && is_array($jsonarr)){
				foreach ($jsonarr as $notice) {
					add_settings_error( 'wpraiser_admin_notice', 'wpraiser_admin_notice', $notice, 'info' );
				}
			}
			
			# remove
			delete_transient('wpraiser_admin_notice');
		}
	}
}


# manage settings page
function wpraiser_add_settings_admin() {
	
	# admin only
	if (!current_user_can('manage_options')) { 
		wp_die( __('You do not have sufficient permissions to access this page.'), __('Error:'), array('response'=>200)); 
	}

	# include admin html template
	global $wpraiser_cache_paths, $wpraiser_var_dir_path, $wpraiser_settings, $wpraiser_var_plugin_version;
	
	# admin html templates
	include($wpraiser_var_dir_path . 'layout' . DIRECTORY_SEPARATOR . 'admin-layout.php');

}


# function to list all cache files on the status page (js ajax code)
function wpraiser_get_logs_callback() {
		
	# must be able to cleanup cache
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.'), __('Error:'), array('response'=>200)); 
	}
	
	# must have
	if(!defined('WP_CONTENT_DIR')) { 
		wp_die( __('WP_CONTENT_DIR is undefined!'), __('Error:'), array('response'=>200)); 
	}
	
	# get info
	global $wpraiser_cache_paths;
	
	# must have valid cache paths
	if(isset($wpraiser_cache_paths['cache_dir_min']) && !empty($wpraiser_cache_paths['cache_dir_min']) && isset($wpraiser_cache_paths['cache_dir_html']) && !empty($wpraiser_cache_paths['cache_dir_html'])) {

		# must be on the allowed path
		if(stripos($wpraiser_cache_paths['cache_dir_min'], WP_CONTENT_DIR) === false || stripos($wpraiser_cache_paths['cache_dir_min'], '/wpraiser') === false || stripos($wpraiser_cache_paths['cache_dir_html'], WP_CONTENT_DIR) === false || stripos($wpraiser_cache_paths['cache_dir_html'], '/wpraiser') === false) {
			wp_die( __('Requested path is not allowed!'), __('Error:'), array('response'=>200)); 
		}
			
		# defaults
		$count_html = 0;
		$count_css = 0;
		$count_js = 0;
		$size_html = 0;
		$size_css = 0;
		$size_js = 0;
	
		# scan min directory recursively
		$errora = false;
		if(is_dir($wpraiser_cache_paths['cache_dir_min'])) {
			try {
				$i = new DirectoryIterator($wpraiser_cache_paths['cache_dir_min']);
				foreach($i as $f){
					if($f->isFile()){ 
					
						# javascript
						if(stripos($f->getRealPath(), '.js') !== false) {
							$count_js = $count_js + 1;
							$size_js = $size_js + intval($f->getSize());
						}
						
						# css
						if(stripos($f->getRealPath(), '.css') !== false) {
							$count_css = $count_css + 1;
							$size_css = $size_css + intval($f->getSize());
						}
						
					}
				}
			} catch (Exception $e) {
				$errora = get_class($e) . ": " . $e->getMessage();
			}
		}
		
		# scan html directory recursively
		$errorb = false;
		if(is_dir($wpraiser_cache_paths['cache_dir_html'])) {
			try {
				$i = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($wpraiser_cache_paths['cache_dir_html']));
				foreach($i as $f){
					if($f->isFile()){ 
						$count_html = $count_html + 1;
						$size_html = $size_html + intval($f->getSize());
					}
				}
			} catch (Exception $e) {
				$errorb = get_class($e) . ": " . $e->getMessage();
			}
		}
		
		
		# return early if errors
		if($errora != false || $errorb != false) {
			header('Content-Type: application/json');
			echo json_encode(array('success' => 'Error A: '.$errora.' / Error B: '.$errorb));
			exit();
		}
		
		
		# defaults
		global $wpdb;
		
		# initialize log
		$css_log = '';
		
		# build css logs from database
		$results = $wpdb->get_results("SELECT date, content, meta FROM ".$wpdb->prefix."wpraiser_logs WHERE type = 'css' ORDER BY id DESC LIMIT 20");
		
		# build second query
		foreach ($results as $log) {
			
			# get meta into an array
			$meta = json_decode($log->meta, true);
			
			# start log
			$css_log.= str_pad('+', 18, '+',STR_PAD_LEFT) . PHP_EOL;
			$css_log.= 'PROCESSED - ' . date('r', $log->date) . PHP_EOL . 'VIA - '. $meta['loc'] . PHP_EOL;
			$css_log.= 'GENERATED - ' . $meta['fl'] . PHP_EOL;
			$css_log.= 'MEDIATYPE - ' . $meta['mt'] . PHP_EOL;
			$css_log.= str_pad('-', 18, '-',STR_PAD_LEFT) . PHP_EOL;
			
			# generate uid's from json
			$list = array(); $list = json_decode($log->content);
			
			# we only want fixed length hashes for query
			$xtralist = array(); foreach($list as $klst=>$vlst) { if(strlen($vlst) != 40) { $xtralist[] = $vlst; unset($list[$klst]); } }
			
			# get rows to log file
			if(count($list) > 0) {
				
				# from database
				$listuids = implode(', ', array_fill(0, count($list), '%s'));
				$rs = array(); $rs = $wpdb->get_results($wpdb->prepare("SELECT meta FROM ".$wpdb->prefix."wpraiser_cache WHERE uid IN (".$listuids.") ORDER BY FIELD(uid, '".implode("', '", $list)."')", $list));
				$tot = 0;
				foreach ($rs as $r) {
					$imt = json_decode($r->meta, true);
					$tot = $tot + intval($imt['fs']);
					$size = '[Size: '.str_pad(wpraiser_format_filesize($imt['fs']), 10,' ',STR_PAD_LEFT).'] ';
					$css_log.= $size ."\t" . $imt['url'] . PHP_EOL;					
				}
				
			}
				
			# extra merged code
			foreach ($xtralist as $xt) {
				$css_log.= $xt . PHP_EOL;
			}
			
			# if list from database
			if(count($list) > 0) {
				$css_log.= str_pad('-', 18, '-',STR_PAD_LEFT) . PHP_EOL;
				$css_log.= '[Total: '.str_pad(wpraiser_format_filesize($tot), 9,' ',STR_PAD_LEFT).']' . PHP_EOL;
			}
			
			# always
			$css_log.= str_pad('-', 18, '-',STR_PAD_LEFT) . PHP_EOL . PHP_EOL;

		}
		
		# trim
		$css_log = trim($css_log);

		# initialize log
		$js_log = '';
		
		# build css logs from database
		$results = $wpdb->get_results("SELECT date, content, meta FROM ".$wpdb->prefix."wpraiser_logs WHERE type = 'js' ORDER BY id DESC LIMIT 20");
		
		# build second query
		foreach ($results as $log) {
			
			# get meta into an array
			$meta = json_decode($log->meta, true);
			
			# start log
			$js_log.= str_pad('+', 18, '+',STR_PAD_LEFT) . PHP_EOL;
			$js_log.= 'PROCESSED - ' . date('r', $log->date) . PHP_EOL . 'VIA - '. $meta['loc'] . PHP_EOL;
			$js_log.= 'GENERATED - ' . $meta['fl'] . PHP_EOL;
			$js_log.= str_pad('-', 18, '-',STR_PAD_LEFT) . PHP_EOL;
			
			# generate uid's from json
			$list = array(); $list = json_decode($log->content);
			
			# we only want fixed length hashes for query
			$xtralist = array(); foreach($list as $klst=>$vlst) { if(strlen($vlst) != 40) { $xtralist[] = $vlst; unset($list[$klst]); } }
			
			# get rows to log file
			if(count($list) > 0) {
				
				# from database
				$listuids = implode(', ', array_fill(0, count($list), '%s'));
				$rs = array(); $rs = $wpdb->get_results($wpdb->prepare("SELECT meta FROM ".$wpdb->prefix."wpraiser_cache WHERE uid IN (".$listuids.") ORDER BY FIELD(uid, '".implode("', '", $list)."')", $list));
				$tot = 0;
				foreach ($rs as $r) {
					$imt = json_decode($r->meta, true);
					$tot = $tot + intval($imt['fs']);
					$size = '[Size: '.str_pad(wpraiser_format_filesize($imt['fs']), 10,' ',STR_PAD_LEFT).'] ';
					$js_log.= $size ."\t" . $imt['url'] . PHP_EOL;		
				}
			}	
			
			# extra merged code
			foreach ($xtralist as $xt) {
				$js_log.= $xt . PHP_EOL;
			}
			
			# if list from database
			if(count($list) > 0) {
				$js_log.= str_pad('-', 18, '-',STR_PAD_LEFT) . PHP_EOL;
				$js_log.= '[Total: '.str_pad(wpraiser_format_filesize($tot), 9,' ',STR_PAD_LEFT).']' . PHP_EOL;
			}
			
			# always
			$js_log.= str_pad('-', 18, '-',STR_PAD_LEFT) . PHP_EOL . PHP_EOL;

		}
		
		# trim
		$js_log = trim($js_log);
		
		
		# cache and final stats
		$cache_log = str_pad('+', 18, '+',STR_PAD_LEFT) . PHP_EOL;
		$cache_log.= 'WP Raiser Cache Statistics:' . PHP_EOL;
		$cache_log.= str_pad('-', 18, '-',STR_PAD_LEFT) . PHP_EOL;
		$cache_log.= '[Size: '.str_pad(wpraiser_format_filesize($size_html), 10,' ',STR_PAD_LEFT).']'."\t". ' from '.$count_html.' HTML Page Caching files.'. PHP_EOL;
		$cache_log.= '[Size: '.str_pad(wpraiser_format_filesize($size_css), 10,' ',STR_PAD_LEFT).']'."\t". ' from '.$count_css.' CSS files.'. PHP_EOL;		
		$cache_log.= '[Size: '.str_pad(wpraiser_format_filesize($size_js), 10,' ',STR_PAD_LEFT).']'."\t". ' from '.$count_js.' JS files.'. PHP_EOL;
		$cache_log.= str_pad('-', 18, '-',STR_PAD_LEFT) . PHP_EOL;
		$cache_log.= '[Total: '.str_pad(wpraiser_format_filesize($size_html + $size_css + $size_js), 9,' ',STR_PAD_LEFT).']'."\t". ' on your site cache.' . PHP_EOL;
		$cache_log.= str_pad('-', 18, '-',STR_PAD_LEFT) . PHP_EOL . PHP_EOL;
		
		
		# default message
		if(empty($css_log)) { $css_log = 'No CSS files generated yet.'; }
		if(empty($js_log)) { $js_log = 'No JS files generated yet.'; }
		if(empty($cache_log)) { $cache_log = 'No cache files generated yet.'; }
		
		# build info
		$result = array(
			'cache_log' => $cache_log,
			'js_log' => $js_log,
			'css_log' => $css_log,
			'success' => 'OK'
		);
		
		# return result
		header('Content-Type: application/json');
		echo json_encode($result);
		exit();
		
	}
	
	# default
	wp_die( __('Unknown cache path!'), __('Error:'), array('response'=>200)); 
}


# run during activation
register_activation_hook($wpraiser_var_file, 'wpraiser_plugin_activate');
function wpraiser_plugin_activate() {
	
	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	# defauls
	$sql = array();
	$wpdb_collate = $wpdb->collate;
	
	# create cache table
	$sqla_table_name = $wpdb->prefix . 'wpraiser_cache';
	$sqla = "CREATE TABLE IF NOT EXISTS {$sqla_table_name} (
         `id` bigint(20) unsigned NOT NULL auto_increment ,
         `uid` varchar(60) NOT NULL,
		 `date` bigint(20) unsigned NOT NULL, 
		 `type` varchar(32) NOT NULL, 
		 `content` mediumtext NOT NULL, 
		 `meta` mediumtext NOT NULL,
         PRIMARY KEY  (id),
		 UNIQUE KEY uid (uid), 
		 KEY date (date), KEY type (type) 
         )
         COLLATE {$wpdb_collate}";
		 
	# create logs table
	$sqlb_table_name = $wpdb->prefix . 'wpraiser_logs';
	$sqlb = "CREATE TABLE IF NOT EXISTS {$sqlb_table_name} (
         `id` bigint(20) unsigned NOT NULL auto_increment, 
		 `uid` varchar(60) NOT NULL, 
		 `date` bigint(20) unsigned NOT NULL, 
		 `type` varchar(32) NOT NULL, 
		 `content` mediumtext NOT NULL, 
		 `meta` mediumtext NOT NULL, 
		 PRIMARY KEY  (id), 
		 UNIQUE KEY uid (uid), 
		 KEY date (date), 
		 KEY type (type)
         )
         COLLATE {$wpdb_collate}";

	# run sql
	dbDelta($sqla);
	dbDelta($sqlb);
	
	# truncate tables
	dbDelta(array('TRUNCATE TABLE '.$wpdb->prefix.'wpraiser_cache'));
	dbDelta(array('TRUNCATE TABLE '.$wpdb->prefix.'wpraiser_logs'));	
	
	# remove WP_CACHE from wp-config.php
	wpraiser_edit_wp_config(false);
	
	# remove advanced-cache.php
	$f = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'advanced-cache.php';
	if(file_exists($f) && is_writeable($f)) { unlink($f); }
	
	# initialize cache time
	wpraiser_cache_increment();
	
	# create expired cache cronjob, every 5 minutes
	if(!wp_next_scheduled('wpraiser_cron_purge_expired')){
		wp_schedule_event(time(), '5min', 'wpraiser_cron_purge_expired');
	}

}


# run during deactivation
register_deactivation_hook($wpraiser_var_file, 'wpraiser_plugin_deactivate');
function wpraiser_plugin_deactivate() {
	global $wpdb, $wpraiser_settings, $wpraiser_cache_paths;
	
	# remove WP_CACHE from wp-config.php
	wpraiser_edit_wp_config(false);
	
	# remove advanced-cache.php
	$f = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'advanced-cache.php';
	if(file_exists($f) && is_writeable($f)) { unlink($f); }
	
	# remove all caches on deactivation
	wpraiser_rrmdir($wpraiser_cache_paths['cache_base_dir']);
	
	# delete tables
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpraiser_cache");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpraiser_logs");
	
	# delete cronjob
	wp_clear_scheduled_hook('wpraiser_cron_purge_expired');

}

# run during uninstall
register_uninstall_hook($wpraiser_var_file, 'wpraiser_plugin_uninstall');	
function wpraiser_plugin_uninstall() {
	global $wpdb, $wpraiser_settings, $wpraiser_cache_paths;
	
	# remove options and tables
	$wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name = 'wpraiser_settings'");
	$wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name = 'wpraiser_last_cache_update'");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpraiser_cache");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpraiser_logs");
	
	# remove WP_CACHE from wp-config.php
	wpraiser_edit_wp_config(false);
	
	# remove advanced-cache.php
	$f = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'advanced-cache.php';
	if(file_exists($f) && is_writeable($f)) { unlink($f); }
	
	# remove all cache directories
	wpraiser_rrmdir($wpraiser_cache_paths['cache_base_dir']);
	
	# delete cronjob
	wp_clear_scheduled_hook('wpraiser_cron_purge_expired');
	
}


# add or remove WP_CACHE 
function wpraiser_edit_wp_config($action) {
	
	# must have
	if(!defined('ABSPATH')) { return false; }
	if(!defined('WP_CONTENT_DIR')) { return false; }
	
	# default wp-config.php location
	$wpcf = ABSPATH . DIRECTORY_SEPARATOR . 'wp-config.php';	
	
	# must exist and be writeable
	if (!file_exists($wpcf) || !is_writeable($wpcf)) {
		return false;
	}
	
	# cleanup
	$wpconfig = file_get_contents($wpcf);
	
	# read and delete our previous code
	$wpconfig = trim(preg_replace("/#\s?BEGIN\s?WPRAISER.*?#\s?END\s?WPRAISER/s", "", $wpconfig));
	
	# split into lines
	$lines = explode(PHP_EOL, $wpconfig);
	
	# remove all WP_CACHE references
	foreach($lines as $k=>$v) {
		if(stripos($v, 'WP_CACHE') !== false) {
			unset($lines[$k]);
		}
	}
	
	# add oour code again, if the action is true
	if($action == true && isset($lines[1])) { 
		$lines[1] = PHP_EOL . '# BEGIN WPRAISER'. PHP_EOL . "define('WP_CACHE', true);" . PHP_EOL . '# END WPRAISER' . PHP_EOL . $lines[1];
	}
	
	# remove excess white space
	$wpconfig = preg_replace('/(?:(?:\r\n|\r|\n)\s*){2}/s', "\n\n", implode(PHP_EOL, $lines));
	
	# resave file again
	file_put_contents($wpcf, $wpconfig);
	wpraiser_fix_permission_bits($wpcf);
	return true;
}


# add or remove advanced-cache.php 
function wpraiser_edit_advanced_cache($action) {
	
	# must have
	if(!defined('ABSPATH')) { return false; }
	if(!defined('WP_CONTENT_DIR')) { return false; }
	$acfile = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'advanced-cache.php';
		
	# remove advanced-cache.php
	if($action == false) {
		if(file_exists($acfile)) { @unlink($acfile); }
		return true;
	} else {
		
		# resave advanced-cache.php
		global $wpraiser_settings, $wpraiser_var_dir_path;
		$actpl = $wpraiser_var_dir_path . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'advanced-cache.tpl';
		if(file_exists($actpl)) {
			
			# open file
			$content = file_get_contents($actpl);
			
			# save compat settings
			if(isset($wpraiser_settings['cache'])) {
				$content = str_replace('###WPRS###', json_encode($wpraiser_settings['cache']), $content);
			}
			
			# save file
			file_put_contents($acfile, $content);
			wpraiser_fix_permission_bits($acfile);
			return true;
			
		}
	}
	
	return false;
}


# add or remove advanced-cache.php 
function wpraiser_edit_mu_plugin($action) {
	
	# must have
	if(!defined('ABSPATH')) { return false; }
	if(!defined('WPMU_PLUGIN_DIR')) { return false; }
	$acfile = WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'wpr-plugin-control.php';
			
	# remove advanced-cache.php
	if($action == false) {
		if(file_exists($acfile)) { @unlink($acfile); }
		return true;
	} else {
		
		# resave advanced-cache.php
		global $wpraiser_settings, $wpraiser_var_dir_path;
		$actpl = $wpraiser_var_dir_path . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'wpr-plugin-control.tpl';
		if(file_exists($actpl)) {
			
			# open file
			$content = file_get_contents($actpl);
			
			# save compat settings
			if(isset($wpraiser_settings['unplug']) && !empty($wpraiser_settings['unplug']) && is_array($wpraiser_settings['unplug'])) {
				
				# filter some stuff
				$save = array();
				foreach($wpraiser_settings['unplug'] as $k=>$v) {
					if($k == 'enable' || $k == 'wpraiser' || empty(trim($v))) { continue; }
					$save[$k] = trim($v);
				}
				
				# save array
				$content = str_replace('###WPRS###', json_encode($save), $content);
			
				# create directory if not available
				$mu_dir = WPMU_PLUGIN_DIR;
				if(!is_dir($mu_dir) && function_exists('wp_mkdir_p')) { 
					wp_mkdir_p($mu_dir);
				}
			
				# save file
				if(is_dir($mu_dir)) {
					file_put_contents($acfile, $content);
					return true;
				}
			
			}
			
		}
	}
	
	return false;
}


# rewrite httaccess, create advanced-cache.php, edit wp-config.php
function wpraiser_edit_htaccess($action){
	
	# must have
	if(!defined('ABSPATH')) { return false; }
	if(!defined('WP_CONTENT_DIR')) { return false; }
	
	# action, depends on mobile settings
	global $wpraiser_settings;
	if($wpraiser_settings['cache']['enable_mobile'] == true) { 
		#$action = false; # fallback to php cache
	}
	
	# action also depends on geolocation
	$country = '';
	
	# detect geolocation
	if( isset($wpraiser_settings['cache']['enable_geolocation']) && $wpraiser_settings['cache']['enable_geolocation'] == true && isset($wpraiser_settings['cache']['vary_geo']) && !empty($wpraiser_settings['cache']['vary_geo'])) {
		#$action = false; # fallback to php cache
	}	
	
	# htaccess location
	$wpht = ABSPATH . DIRECTORY_SEPARATOR . '.htaccess';
	
	# htaccess
	if(is_file($wpht) && is_writable($wpht)){
		
		# read and delete our previous code
		$htaccess = file_get_contents($wpht);
		$htaccess = trim(preg_replace("/#\s?BEGIN\s?WPRAISER\s?PAGE\s?CACHE.*?#\s?END\s?WPRAISER\s?PAGE\s?CACHE/s", "", $htaccess));
		
		if($action == true) { 
		
			# get settings
			global $wpraiser_settings, $wpraiser_var_dir_path;
			
			# note, REQUEST_URI includes query strings
			
			# start building htaccess code
			$new = '# BEGIN WPRAISER PAGE CACHE' . PHP_EOL;
			$new.= '<IfModule mod_rewrite.c>' . PHP_EOL;
			$new.= 'RewriteEngine On' . PHP_EOL;
			$new.= 'RewriteBase /' . PHP_EOL;
			$new.= '# https support' . PHP_EOL;
			$new.= 'RewriteCond %{HTTPS} on [OR]' . PHP_EOL;
			$new.= 'RewriteCond %{SERVER_PORT} ^443$ [OR]' . PHP_EOL;
			$new.= 'RewriteCond %{HTTP:X-Forwarded-Proto} https' . PHP_EOL;
			$new.= 'RewriteRule .* - [E=WPR_SSL:-https]' . PHP_EOL;
			
			# geolocation
			if( isset($wpraiser_settings['cache']['enable_geolocation']) && $wpraiser_settings['cache']['enable_geolocation'] == true && isset($wpraiser_settings['cache']['vary_geo']) && !empty($wpraiser_settings['cache']['vary_geo'])) {
				$arr = wpraiser_string_toarray($wpraiser_settings['cache']['vary_geo']);
				if(is_array($arr) && count($arr) > 0) {
					$new.= '# geolocation support' . PHP_EOL;
					foreach ($arr as $cc) {
						if(strlen($cc) == 2) {
							$new.= 'RewriteCond %{HTTP:CF-IPCountry} "'.$cc.'" [OR,NC]' . PHP_EOL;
							$new.= 'RewriteCond %{ENV:GEOIP_COUNTRY_CODE} "'.$cc.'" [OR,NC]' . PHP_EOL;
							$new.= 'RewriteCond %{HTTP:HTTP_X_COUNTRY_CODE} "'.$cc.'" [NC]' . PHP_EOL;
							$new.= 'RewriteRule .* - [E=WPR_GEO:'.$cc.'_]' . PHP_EOL;
						}
					}
				}
			}
			
			# mobile support by useragent (list from wp_is_mobile() function)
			# https://developer.wordpress.org/reference/functions/wp_is_mobile/
			if( isset($wpraiser_settings['cache']['enable_mobile']) && $wpraiser_settings['cache']['enable_mobile'] == true) {
				$new.= '# mobile support' . PHP_EOL;
				$new.= 'RewriteCond %{HTTP_USER_AGENT} "Mobile|Android|Silk\/|Kindle|BlackBerry|Opera Mini|Opera Mobi" [NC]' . PHP_EOL;
				$new.= 'RewriteRule .* - [E=WPR_MOB:mobile_]' . PHP_EOL;
			}			
			
			# cookie exclusions
			if(isset($wpraiser_settings['cache']['cookies']) && !empty($wpraiser_settings['cache']['cookies'])) {
				$new.= '# cookies support' . PHP_EOL;
				$compat_cookies = implode('|', wpraiser_string_toarray(str_replace('*', '', $wpraiser_settings['cache']['cookies'])));
				$new.= 'RewriteCond %{HTTP:Cookie} !('.$compat_cookies.') [NC]' . PHP_EOL;
			}
			
			# continue building htaccess code
			$new.= '# request methods allowed' . PHP_EOL;
			$new.= 'RewriteCond %{REQUEST_METHOD} ^(GET|HEAD)' . PHP_EOL;
			$new.= '# bypass cache for query strings' . PHP_EOL;
			$new.= 'RewriteCond %{QUERY_STRING} =""' . PHP_EOL;
			$new.= '# bypass cache for certain urls' . PHP_EOL;
			$new.= 'RewriteCond %{REQUEST_URI} !^(/(.+/)?feed/?|/(?:.+/)?embed/|/(index\.php/)?wp\-json(/.*|$))$ [NC]' . PHP_EOL;
			$new.= 'RewriteCond "%{DOCUMENT_ROOT}/wp-content/cache/wpraiser/html/%{HTTP_HOST}%{REQUEST_URI}%{ENV:WPR_GEO}%{ENV:WPR_MOB}index%{ENV:WPR_SSL}.html" -f' . PHP_EOL;
			$new.= 'RewriteRule .* "/wp-content/cache/wpraiser/html/%{HTTP_HOST}%{REQUEST_URI}%{ENV:WPR_GEO}%{ENV:WPR_MOB}index%{ENV:WPR_SSL}.html" [L]' . PHP_EOL;
			$new.= '</IfModule>' . PHP_EOL;
			$new.= '# END WPRAISER PAGE CACHE' . PHP_EOL;
			$htaccess = $new . PHP_EOL . $htaccess;	
		
		}		
		
		# resave file
		file_put_contents($wpht, trim($htaccess));
		wpraiser_fix_permission_bits($wpht);
		return true;

	}	
}


# process settings import
function wpraiser_settings_import() {
	
	if(isset($_POST['wpraiser_action']) && isset($_POST['wpraiser_import_nonce']) && $_POST['wpraiser_import_nonce'] && $_POST['wpraiser_action'] == 'import') {
	
		if(!wp_verify_nonce($_POST['wpraiser_import_nonce'], 'wpraiser_import_nonce')) {
			wp_die( __('<h2>Invalid nounce.</h2> <br / >Please <a href="javascript:history.back()">go back</a> and try again.'), __('Error:'), array('response'=>200)); 
		}
		
		if(!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.'), __('Error:'), array('response'=>200)); 
		}

		if(!isset($_FILES['import_wpr_settings'])) {
			wp_die( __('<h2>Invalid data!</h2> <br / >Please <a href="javascript:history.back()">go back</a> and try again.'), __('Error:'), array('response'=>200)); 
		}
	
		$import_wpr_settings = $_FILES['import_wpr_settings']['tmp_name'];
		if(empty( $import_wpr_settings)) {
			wp_die( __('<h2>Empty file!</h2> <br / >Please <a href="javascript:history.back()">go back</a> and try again.'), __('Error:'), array('response'=>200)); 
		}
		
		$extarr = explode('.', $_FILES['import_wpr_settings']['name']);
		if(end($extarr) != 'json') {
			wp_die( __('<h2>Invalid file format!</h2> <br / >Please <a href="javascript:history.back()">go back</a> and try again.'), __('Error:'), array('response'=>200)); 
		}

		# Retrieve the settings from the file and convert the json object to an array.
		$import = json_decode(base64_decode(file_get_contents($import_wpr_settings)), true);

		# save settings as json
		# update wpraiser_settings in the global scope
		if(isset($import) && is_array($import)) {
			
			# get mandatory default exclusions
			global $wpraiser_settings;
			$wpraiser_settings = wpraiser_get_default_settings($import);
			
			# check if we have cache settings on form submission
			if( isset($wpraiser_settings['cache']['enable_page']) && isset($wpraiser_settings['cache']['lifespan']) && isset($wpraiser_settings['cache']['lifespan_unit']) ) {
				
				# action, depends on settings
				$action = false;
				if($wpraiser_settings['cache']['enable_page'] == true) { 
					$action = true;
				}
				
				# process cache settings
				wpraiser_edit_wp_config($action);
				wpraiser_edit_advanced_cache($action);
				wpraiser_edit_htaccess($action);
							
				# purge caches
				wpraiser_purge_cache();
				wpraiser_purge_others();				

			}
			
			# save settings
			update_option('wpraiser_settings', json_encode($import), false);
			add_settings_error( 'wpraiser_admin_notice', 'wpraiser_admin_notice', 'All options have been imported successfully!', 'success' );
			
		} else {
			wp_die( __('<h2>Invalid data!</h2> <br / >Please <a href="javascript:history.back()">go back</a> and try again.'), __('Error:'), array('response'=>200)); 
		}	
	}
}


# process settings export
function wpraiser_settings_export() {

	if(isset($_POST['wpraiser_action']) && isset($_POST['wpraiser_export_nonce']) && $_POST['wpraiser_action'] == 'export') {
		
		if(!wp_verify_nonce($_POST['wpraiser_export_nonce'], 'wpraiser_export_nonce')) {
			wp_die( __('<h2>Invalid nounce.</h2> <br / >Please <a href="javascript:history.back()">go back</a> and try again.'), __('Error:'), array('response'=>200)); 
		}
		
		if(!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.'), __('Error:'), array('response'=>200)); 
		}
		
		# no cache headers (wp)
		nocache_headers();
		
		# query
		global $wpdb, $wpraiser_urls;
		$res = $wpdb->get_row("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'wpraiser_settings' LIMIT 1");
		if($res->option_value) {
		
			$exp = base64_encode($res->option_value);
			$fn = time().'-wpraiser-'.$wpraiser_urls['wp_domain'].'.json';
				
			# output
			header("Content-Disposition: attachment; filename=\"" . $fn . "\"");
			header('Content-Type: text/json');
			header("Content-Length: " . strlen($exp));
			header("Connection: close");
			echo $exp;
			exit();
		}		
	}
}



# get all known roles
function wpraiser_get_user_roles_checkboxes($group, $label) {
	
	global $wp_roles, $wpraiser_settings;
	$roles_list = array();

	if(is_object($wp_roles)) {
		$roles = (array) $wp_roles->get_names();
		foreach ($roles as $role=>$rname) {
			
			# exclude some
			if(in_array($role, array('administrator', 'editor', 'shop_manager', 'wpseo_editor', 'wpseo_manager'))) { continue; }
						
			$roles_list[] = '
			<div class="wpraiser-field">
			<div class="wpraiser-checkbox">
			<label for="roles_'.$group.'_'.$role.'" class="">
			<input type="hidden" name="wpraiser_settings[roles][cdn-'.$role.']" value="0">
			<input type="checkbox" id="roles_'.$group.'_'.$role.'" name="wpraiser_settings[roles]['.$group.'-'.$role.']" value="1" '. wpraiser_get_settings_checkbox(wpraiser_get_settings_value($wpraiser_settings, 'roles', ''.$group.'-'.$role.'')).'>
			<span class="wpraiser-checkmark"></span>
			<span class="wpraiser-checkmark-label">'.$rname.'</span>
			</label>
			</div>
			<div class="wpraiser-field-description">Enable '.$label.' optimization for this user role.</div>
			</div>
			';
		
		}
	}
	
	# return
	if(!empty($roles_list)) { 
		return '<div class="wpraiser-fields-container"><fieldset class="wpraiser-fields-container-fieldset">'.implode(PHP_EOL, $roles_list).'</fieldset></div>'; 
	} else { 
		return 'No roles detected!'; 
	}

}


# get all active plugins for wp-admin settings
function wpraiser_get_admin_plugin_filters() {

	# get all active plugins 
	$all_plugins = get_plugins();
	$active_plugins = get_option('active_plugins');
	
	# only unique arrays
	$active_plugins = array_unique($active_plugins);
	
	# globals and defaults
	global $wpraiser_settings;
	$output = '';
	
	# has active plugins
	if(count($active_plugins) > 0) {
		
		# start
		$output.= '<div class="accordion">';

		# loop
		foreach ($active_plugins as $k=>$pl) {
			if (array_key_exists($pl, $all_plugins)) {
				$pn = $all_plugins[$pl]['Name'];
				$pd = dirname($pl);
				
				# not self
				if($pd == 'wpraiser' || $pn == 'WP Raiser') { continue; }
				
				# rows
				$output.= '<h3 class="wpraiser-title2">'.$pn.'</h3>';
				$output.= '<div class="wpraiser-fields-container-collapsible">';
				$output.= '<fieldset class="wpraiser-fields-container-fieldset">';
					$output.= '<div class="wpraiser-field">';
					$output.= '<div class="wpraiser-textarea">';
						$output.= '<div class="wpraiser-field-title">Plugin: <code>'.$pn.'</code></div>';
						$output.= '<div class="wpraiser-field-description">Deactivate the plugin when the URI Path match the following paths</div>';
						$output.= '<textarea id="unplug_field_'.$pd.'" name="wpraiser_settings[unplug]['.$pd.']" placeholder="/example-url/">'.wpraiser_get_settings_value($wpraiser_settings, 'unplug', $pd).'</textarea>';
						$output.= '<div class="wpraiser-field-description wpraiser-field-description-helper">';
						$output.= 'Use exact URI Paths for exact matches, or (prepend/append) the * char for a case insensitive substring match.<br />';
						$output.= 'The * wildcard is only supported at the beginning / end of the URI Path you insert.';
						$output.= '</div>';
					$output.= '</div>';
					$output.= '</div>';
				$output.= '</fieldset>';
				$output.= '</div>';	
				
			}
		}
		
		# end
		$output.= '</div>';
	} else {
		$output.= '<div class="wpraiser-fields-container-description">No active plugins detected!</div>';
	}
	
	# return
	return $output;

}

