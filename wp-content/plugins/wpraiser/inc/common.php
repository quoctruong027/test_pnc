<?php

# Exit if accessed directly				
if (!defined('ABSPATH')){ exit(); }	


# functions needed for both frontend or backend

# top admin toolbar for cache purging
function wpraiser_admintoolbar() {
	if(current_user_can('manage_options')) {
		global $wp_admin_bar;

		# Add top menu to admin bar
		$wp_admin_bar->add_node(array(
			'id'    => 'wpraiser_menu',
			'title' => __("WP Raiser", 'wpraiser') . '</span>',
			'href'  => wp_nonce_url(add_query_arg('wpraiser_do', 'clear_all'), 'wpraiser_clear', '_wpnonce')
		));
		
		# Add submenu
		$wp_admin_bar->add_node(array(
			'id'    => 'wpraiser_submenu_purge_all',
			'parent'    => 'wpraiser_menu', 
			'title' => __("Clear Everything", 'wpraiser'),
			'href'  => wp_nonce_url(add_query_arg('wpraiser_do', 'clear_all'), 'wpraiser_clear', '_wpnonce')			
		));
		
		# Add submenu
		$wp_admin_bar->add_node(array(
			'id'    => 'wpraiser_submenu_purge_html',
			'parent'    => 'wpraiser_menu', 
			'title' => __("Clear Page Cache", 'wpraiser'),
			'href'  => wp_nonce_url(add_query_arg('wpraiser_do', 'clear_html'), 'wpraiser_clear', '_wpnonce')			
		));
		
		# Add submenu
		$wp_admin_bar->add_node(array(
			'id'    => 'wpraiser_submenu_settings',
			'parent'    => 'wpraiser_menu', 
			'title' => __("WP Raiser Settings", 'wpraiser'),
			'href'  => admin_url('options-general.php?page=wpraiser#dashboard')
		));

	}
}


# purge all caches when clicking the button on the admin bar
function wpraiser_process_cache_purge_request(){
	
	if(isset($_GET['wpraiser_do']) && isset($_GET['_wpnonce'])) {
		
		# must be able to cleanup cache
		if (!current_user_can('manage_options')) { 
			wp_die( __('You do not have sufficient permissions to access this page.'), __('Error:'), array('response'=>200)); 
		}
		
		# validate nonce
		if(!wp_verify_nonce($_GET['_wpnonce'], 'wpraiser_clear')) {
			wp_die( __('Invalid or expired request... please go back and refresh before trying again!'), __('Error:'), array('response'=>200)); 
		}
		
		# Purge All
		if($_GET['wpraiser_do'] == 'clear_all') {
			
			# purge everything
			$cache = wpraiser_purge_all();
			$others = wpraiser_purge_others();
			
			if(is_admin()) {
				
				# merge notices
				$notices = array();
				if(is_string($cache)) { $notices[] = $cache; }
				if(is_string($others)) { $notices[] = $others; }
				
				# save transient for after the redirect
				if(count($notices) == 0) { $notices[] = 'WP Raiser: All Caches are now cleared. ('.date("D, d M Y @ H:i:s e").')'; }
				set_transient( 'wpraiser_admin_notice', json_encode($notices), 10);
				
			}

		}
		
		# Purge Page Caching
		if($_GET['wpraiser_do'] == 'clear_html') {
			
			# purge everything
			$cache = wpraiser_purge_cache();
			$others = wpraiser_purge_others();

			if(is_admin()) {
				
				# merge notices
				$notices = array();
				if(is_string($cache)) { $notices[] = $cache; }
				if(is_string($others)) { $notices[] = $others; }
				
				# save transient for after the redirect
				if(count($notices) == 0) { $notices[] = 'WP Raiser: Page Cache is now cleared. ('.date("D, d M Y @ H:i:s e").')'; }
				set_transient( 'wpraiser_admin_notice', json_encode($notices), 10);
				
			}

		}
							
		# https://developer.wordpress.org/reference/functions/wp_safe_redirect/
		nocache_headers();
		wp_safe_redirect(remove_query_arg('_wpnonce', remove_query_arg('_wpraiser', wp_get_referer())));
		exit();
	}
}


# get cache directories and urls
function wpraiser_cachepath() {
	
	# must have
	if(!defined('WP_CONTENT_DIR')) { return false; }
	if(!defined('WP_CONTENT_URL')) { return false; }
	
	global $wpraiser_settings;
	
	# use uploads directory?
	if(isset($wpraiser_settings['cache']['uploads']) && $wpraiser_settings['cache']['uploads'] == true) {
		$chdir = 'uploads' . DIRECTORY_SEPARATOR . 'cache';
		$chdirurl = 'uploads/cache';
	} else {
		$chdir = 'cache';
		$chdirurl = 'cache';
	}
	
	# define cache directory
	$cache_dir    = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $chdir;
	$cache_base_dir    = $cache_dir . DIRECTORY_SEPARATOR .'wpraiser';
	$cache_base_dirurl = WP_CONTENT_URL . '/' . $chdirurl . '/' . 'wpraiser';
	
	# get requested hostname
	if (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    } elseif (isset($_SERVER['SERVER_NAME'])) {
        $host = $_SERVER['SERVER_NAME'];
	} else {
		$host = 'localhost';
	}
	
	# sanitize
	$host = str_replace('//', '/', str_replace('..', '', preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', $host)));
	
	$cache_dir_min  = $cache_base_dir . DIRECTORY_SEPARATOR . 'min' . DIRECTORY_SEPARATOR . $host;
	$cache_dir_html = $cache_base_dir . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $host;
	$cache_dir_img  = $cache_base_dir . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $host;
	$cache_url_min  = $cache_base_dirurl . '/min/' .$host;
	$cache_url_img  = $cache_base_dirurl . '/img/' .$host;
		
	# mkdir and check if umask requires chmod, but only for hosts matching the site_url'
	$dirs = array($cache_dir, $cache_base_dir, $cache_dir_min, $cache_dir_html, $cache_dir_img);
	foreach ($dirs as $d) {
		wpraiser_create_dir($d);
	}

	# return
	return array(
		'cache_base_dir'=>$cache_base_dir, 
		'cache_dir_min'=>$cache_dir_min, 
		'cache_dir_html'=>$cache_dir_html,
		'cache_dir_img'=>$cache_dir_img,
		'cache_url_min'=>$cache_url_min,
		'cache_url_img'=>$cache_url_img
		);
}


# Purge OPCache, Cache and Min files
function wpraiser_purge_all() {
	
	# flush opcache
	if(function_exists('opcache_reset')) { @opcache_reset(); }

	# increment cache file names
	$now = wpraiser_cache_increment();

	# truncate cache table
	global $wpdb;
	$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}wpraiser_cache");
	$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}wpraiser_logs");
	
	wpraiser_purge_cache();
	wpraiser_purge_minification();
	
	return false;	
}


# Purge Page Cache only
function wpraiser_purge_cache() {
	
	# flush opcache
	if(function_exists('opcache_reset')) { @opcache_reset(); }

	# get cache and min directories
	global $wpraiser_cache_paths;
	
	# purge html directory
	if(isset($wpraiser_cache_paths['cache_dir_html']) && is_dir($wpraiser_cache_paths['cache_dir_html']) && is_writable($wpraiser_cache_paths['cache_dir_html'])) {
		$result = wpraiser_rrmdir($wpraiser_cache_paths['cache_dir_html']);
		return $result;
	} else {
		return 'The cache directory is not rewritable!';
	}
	
	return false;	
}


# Purge minification only
function wpraiser_purge_minification() {
	
	# flush opcache
	if(function_exists('opcache_reset')) { @opcache_reset(); }

	# delete minification cache
	global $wpdb;
	$wpdb->query("DELETE FROM {$wpdb->prefix}wpraiser_cache WHERE type = 'css' OR type = 'js'");
	$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}wpraiser_logs");
	
	# get cache and min directories
	global $wpraiser_settings, $wpraiser_cache_paths;
	
	# purge html directory?
	if(isset($wpraiser_cache_paths['cache_dir_min']) && is_dir($wpraiser_cache_paths['cache_dir_min']) && is_writable($wpraiser_cache_paths['cache_dir_min']) && stripos($wpraiser_cache_paths['cache_dir_min'], '/wpraiser') !== false) {
		
		# purge css/js files instantly
		if(isset($wpraiser_settings['cache']['min_instant_purge']) && $wpraiser_settings['cache']['min_instant_purge'] == true) {
			$result = wpraiser_purge_minification_now();
			return $result;
		} else {
			# schedule purge for 24 hours later, only once
			add_action( 'wpraiser_purge_minification_later', 'wpraiser_purge_minification_expired' );
			wp_schedule_single_event(time() + 3600 * 24, 'wpraiser_purge_minification_later');
			return 'Expired minification files are set to expire in 24 hours from now.';
		}
		
	} else {
		return 'The cache directory is not rewritable!';
	}
	
	return false;	
}


# purge minified files right now
function wpraiser_purge_minification_now() {
	global $wpraiser_cache_paths;
	if(isset($wpraiser_cache_paths['cache_dir_min']) && stripos($wpraiser_cache_paths['cache_dir_min'], '/wpraiser') !== false) {
		$result = wpraiser_rrmdir($wpraiser_cache_paths['cache_dir_min']);
		return $result;
	} else {
		return 'The cache directory is not rewritable!';
	}
}

# purge expired minification files only
function wpraiser_purge_minification_expired() {
	global $wpraiser_cache_paths;
	if(isset($wpraiser_cache_paths['cache_dir_min']) && !empty($wpraiser_cache_paths['cache_dir_min']) && stripos($wpraiser_cache_paths['cache_dir_min'], '/wpraiser') !== false) {
		
		# must be on the allowed path
		$wd = $wpraiser_cache_paths['cache_dir_min'];
		if(empty($wd) || !defined('WP_CONTENT_DIR') || stripos($wd, '/wpraiser') === false) {
			return 'Requested purge path is not allowed!';
		}
		
		# prefix
		$skip = get_option('wpraiser_last_cache_update', '0');
		
		# purge only the expired cache that doesn't match the current cache version prefix
		clearstatcache();
		if(is_dir($wd)) {
			try {
				$i = new DirectoryIterator($wd);
				foreach($i as $f){
					if($f->isFile() && stripos(basename($f->getRealPath()), $skip) === false){ 
						@unlink($f->getRealPath());
					}
				}
			} catch (Exception $e) {
				return get_class($e) . ": " . $e->getMessage();
			}
		}
		
		return 'Expired Cache Deleted!';
	}
}


# purge supported hosting and plugins
function wpraiser_purge_others(){

	# third party plugins
		
	# Purge all W3 Total Cache
	if (function_exists('w3tc_pgcache_flush')) {
		w3tc_pgcache_flush();
		return __('All caches on <strong>W3 Total Cache</strong> have been purged.');
	}

	# Purge WP Super Cache
	if (function_exists('wp_cache_clear_cache')) {
		wp_cache_clear_cache();
		return __('All caches on <strong>WP Super Cache</strong> have been purged.');
	}

	# Purge WP Rocket
	if (function_exists('rocket_clean_domain')) {
		rocket_clean_domain();
		return __('All caches on <strong>WP Rocket</strong> have been purged.');
	}

	# Purge Cachify
	if (function_exists('cachify_flush_cache')) {
		cachify_flush_cache();
		return __('All caches on <strong>Cachify</strong> have been purged.');
	}

	# Purge Comet Cache
	if ( class_exists("comet_cache") ) {
		comet_cache::clear();
		return __('All caches on <strong>Comet Cache</strong> have been purged.');
	}

	# Purge Zen Cache
	if ( class_exists("zencache") ) {
		zencache::clear();
		return __('All caches on <strong>Comet Cache</strong> have been purged.');
	}

	# Purge LiteSpeed Cache 
	if (class_exists('LiteSpeed_Cache_Tags')) {
		LiteSpeed_Cache_Tags::add_purge_tag('*');
		return __('All caches on <strong>LiteSpeed Cache</strong> have been purged.');
	}

	# Purge Hyper Cache
	if (class_exists( 'HyperCache' )) {
		do_action( 'autoptimize_action_cachepurged' );
		return __( 'All caches on <strong>HyperCache</strong> have been purged.');
	}

	# purge cache enabler
	if ( has_action('ce_clear_cache') ) {
		do_action('ce_clear_cache');
		return __( 'All caches on <strong>Cache Enabler</strong> have been purged.');
	}

	# purge wpfc
	if (function_exists('wpfc_clear_all_cache')) {
		wpfc_clear_all_cache(true);
	}

	# add breeze cache purge support
	if (class_exists("Breeze_PurgeCache")) {
		Breeze_PurgeCache::breeze_cache_flush();
		return __( 'All caches on <strong>Breeze</strong> have been purged.');
	}


	# swift
	if (class_exists("Swift_Performance_Cache")) {
		Swift_Performance_Cache::clear_all_cache();
		return __( 'All caches on <strong>Swift Performance</strong> have been purged.');
	}


	# hosting companies

	# Purge SG Optimizer (Siteground)
	if (function_exists('sg_cachepress_purge_cache')) {
		sg_cachepress_purge_cache();
		return __('All caches on <strong>SG Optimizer</strong> have been purged.');
	}

	# Purge Godaddy Managed WordPress Hosting (Varnish + APC)
	if (class_exists('WPaaS\Plugin') && method_exists( 'WPass\Plugin', 'vip' )) {
		wpraiser_godaddy_request('BAN');
		return __('A cache purge request has been sent to <strong>Go Daddy Varnish</strong>');
	}


	# Purge WP Engine
	if (class_exists("WpeCommon")) {
		if (method_exists('WpeCommon', 'purge_memcached')) { WpeCommon::purge_memcached(); }
		if (method_exists('WpeCommon', 'purge_varnish_cache')) { WpeCommon::purge_varnish_cache(); }
		if (method_exists('WpeCommon', 'purge_memcached') || method_exists('WpeCommon', 'purge_varnish_cache')) {
			return __('A cache purge request has been sent to <strong>WP Engine</strong>');
		}
	}

	# Purge Kinsta
	global $kinsta_cache;
	if ( isset($kinsta_cache) && class_exists('\\Kinsta\\CDN_Enabler')) {
		if (!empty( $kinsta_cache->kinsta_cache_purge)){
			$kinsta_cache->kinsta_cache_purge->purge_complete_caches();
			return __('A cache purge request has been sent to <strong>Kinsta</strong>');
		}
	}

	# Purge Pagely
	if ( class_exists( 'PagelyCachePurge' ) ) {
		$purge_pagely = new PagelyCachePurge();
		$purge_pagely->purgeAll();
		return __('A cache purge request has been sent to <strong>Pagely</strong>');
	}

	# Purge Pressidum
	if (defined('WP_NINUKIS_WP_NAME') && class_exists('Ninukis_Plugin')){
		$purge_pressidum = Ninukis_Plugin::get_instance();
		$purge_pressidum->purgeAllCaches();
		return __('A cache purge request has been sent to <strong>Pressidium</strong>');
	}

	# Purge Savvii
	if (defined( '\Savvii\CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW')) {
		$purge_savvii = new \Savvii\CacheFlusherPlugin();
		if ( method_exists( $plugin, 'domainflush' ) ) {
			$purge_savvii->domainflush();
			return __('A cache purge request has been sent to <strong>Savvii</strong>');
		}
	}

	# Purge Pantheon Advanced Page Cache plugin
	if(function_exists('pantheon_wp_clear_edge_all')) {
		pantheon_wp_clear_edge_all();
	}

	# wordpress default cache
	if (function_exists('wp_cache_flush')) {
		wp_cache_flush();
	}
	
}


# Purge Godaddy Managed WordPress Hosting (Varnish)
function wpraiser_godaddy_request( $method, $url = null ) {
	$url  = empty( $url ) ? home_url() : $url;
	$host = parse_url( $url, PHP_URL_HOST );
	$url  = set_url_scheme( str_replace( $host, WPaas\Plugin::vip(), $url ), 'http' );
	update_option( 'gd_system_last_cache_flush', time(), 'no'); # purge apc
	wp_remote_request( esc_url_raw( $url ), array('method' => $method, 'blocking' => false, 'headers' => array('Host' => $host)) );
}


# purge everything on certain actions
function wpraiser_purge_all_action() {
	
	# check if user has rights
	if(!current_user_can('publish_posts')) {
		wpraiser_purge_all();
		wpraiser_purge_others();
		return true;
	}
	
	return false;	
}


# purge cache on certain actions
function wpraiser_purge_cache_action() {
	wpraiser_purge_cache();
	wpraiser_purge_others();
	return true;
}


# check if we can process the page, minimum filters
function wpraiser_can_process_query_string() {
	global $wpraiser_settings;
	
	# if there is an url
	if(isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
		
		# parse url (path, query)
		$parseurl = parse_url($_SERVER['REQUEST_URI']);
	
		# check query strings
		if(isset($parseurl["query"]) && !empty($parseurl["query"])) {
			
			# parse query string to array
			$query_string_arr = array(); 
			parse_str($parseurl["query"], $query_string_arr);

			# unless specifically allowed
			if(isset($wpraiser_settings['cache']['ignore_qs']) && !empty($wpraiser_settings['cache']['ignore_qs'])) {
				foreach ( wpraiser_string_toarray($wpraiser_settings['cache']['ignore_qs'] ) as $qs) {
					if(isset($query_string_arr[$qs])) { unset($query_string_arr[$qs]); }
				}
			}
				
			# always return false if there are any query strings left
			if(count($query_string_arr) > 0) {
				return false;
			}
			
		}
	
	}
	
	# default
	return true;
}


# check if we can process the page, minimum filters
function wpraiser_can_process_common() {
	global $wpraiser_settings, $wpraiser_urls;
	
	# only GET requests allowed
	if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
		return false;
	}
	
	# always skip on these tasks
	if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){ return false; }
	if( defined('WP_INSTALLING') && WP_INSTALLING ){ return false; }
	if( defined('WP_REPAIRING') && WP_REPAIRING ){ return false; }
	if( defined('WP_IMPORTING') && WP_IMPORTING ){ return false; }
	if( defined('DOING_AJAX') && DOING_AJAX ){ return false; }
	if( defined('WP_CLI') && WP_CLI ){ return false; }
	if( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ){ return false; }
	if( defined('WP_ADMIN') && WP_ADMIN ){ return false; }
	if( defined('SHORTINIT') && SHORTINIT ){ return false; }
	if( defined('IFRAME_REQUEST') && IFRAME_REQUEST ){ return false; }
	
	# compatibility with DONOTCACHEPAGE
	if( defined('DONOTCACHEPAGE') && DONOTCACHEPAGE ){ return false; }
	
	# detect api requests (only defined after parse_request hook)
	if( defined('REST_REQUEST') && REST_REQUEST ){ return false; } 
	
	# don't minify specific WordPress areas
	if(function_exists('is_404') && is_404()){ return false; }
	if(function_exists('is_feed') && is_feed()){ return false; }
	if(function_exists('is_comment_feed') && is_comment_feed()){ return false; }
	if(function_exists('is_attachment') && is_attachment()){ return false; }
	if(function_exists('is_trackback') && is_trackback()){ return false; }
	if(function_exists('is_robots') && is_robots()){ return false; }
	if(function_exists('is_preview') && is_preview()){ return false; }
	if(function_exists('is_customize_preview') && is_customize_preview()){ return false; }	
	if(function_exists('is_embed') && is_embed()){ return false; }
	if(function_exists('is_admin') && is_admin()){ return false; }
	if(function_exists('is_blog_admin') && is_blog_admin()){ return false; }
	if(function_exists('is_network_admin') && is_network_admin()){ return false; }
	
	# don't minify specific WooCommerce areas
	if(function_exists('is_checkout') && is_checkout()){ return false; }
	if(function_exists('is_account_page') && is_account_page()){ return false; }
	if(function_exists('is_ajax') && is_ajax()){ return false; }
	if(function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()){ return false; }
	
	# don't minify amp pages by the amp plugin
	if(function_exists('is_amp_endpoint') && is_amp_endpoint()){ return false; }
	if(function_exists('ampforwp_is_amp_endpoint') && ampforwp_is_amp_endpoint()){ return false; }
	
	
	# get requested hostname
	if (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    } elseif (isset($_SERVER['SERVER_NAME'])) {
        $host = $_SERVER['SERVER_NAME'];
	} else {
		$host = 'localhost';
	}
	
	# only for hosts matching the site_url
	if(isset($wpraiser_urls['wp_domain']) && !empty($wpraiser_urls['wp_domain'])) {
		if($host != $wpraiser_urls['wp_domain']) {
			return false;
		}
	}
	
	# if there is an url, skip common static files
	if(isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
		
		# parse url (path, query)
		$ruri = str_replace('//', '/', str_replace('..', '', preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', strtok($_SERVER['REQUEST_URI'], '?'))));
			
		# no cache by extension as well, such as robots.txt and other situations
		$noext = array('.txt', '.xml', '.map', '.css', '.js', '.png', '.jpeg', '.jpg', '.gif', '.webp', '.ico', '.php', '.htaccess', '.json', '.pdf', '.mp4', '.webm');
		foreach ($noext as $ext) {
			if(substr($ruri, -strlen($ext)) == $ext) {
				return false;
			}
		}		
		
	}
	
		
	# default
	return true;
}


# check if the user is logged in, and if the user role allows optimization
function wpraiser_user_role_processing_allowed($group) {	
	if(function_exists('is_user_logged_in') && function_exists('wp_get_current_user')) {
		if(is_user_logged_in()) {
			
			# get user roles
			global $wpraiser_settings;
			$user = wp_get_current_user();
			$roles = (array) $user->roles;
			foreach($roles as $role) {
				if(isset($wpraiser_settings['roles'][$group.'-'.$role]) && $wpraiser_settings['roles'][$group.'-'.$role] == true) { 
					return true; 
				}
			}
			
			# disable for other logged in users by default
			return false;
		}
	}
	
	# allow by default
	return true;
}


# check if we can minify js on this page
function wpraiser_can_minify_js() {
	
	# check if we hit any exclusions from the compatibility page
	if(!wpraiser_can_process_common()) { return false; }
	if(!wpraiser_can_process_query_string()) { return false; } 
	
	# disable script optimization on cart
	if(function_exists('is_cart') && is_cart()){ return false; }
	
	# settings
	global $wpraiser_settings;
	
	# disabled?
	if(isset($wpraiser_settings['js']['disable']) && $wpraiser_settings['js']['disable'] == true) { return false; }
	
	# if there is an url, check some paths
	if(isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
		
		# parse url (path, query)
		$ruri = str_replace('//', '/', str_replace('..', '', preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', strtok($_SERVER['REQUEST_URI'], '?'))));
	
		# check our exclusions list against the URI Path
		if(!empty($ruri) && isset($wpraiser_settings['js']['skip_url'])) {
			$arr = wpraiser_string_toarray($wpraiser_settings['js']['skip_url']);
			if(is_array($arr)) {
				foreach ($arr as $a) {
					
					# exact match
					if($ruri == $a) { return false; }
					
					# match middle
					if(substr($a, -1) == '*' && substr($a, 1) == '*') {
						if(stripos($ruri, trim($a, '*')) !== false) { return false; }
					} 
					
					# match beginning 
					if(substr($a, -1) == '*' && substr($a, 1) != '*') {
						if(substr($ruri, 0, strlen(trim($a, '*'))) == trim($a, '*')) { return false; }
					}
					
					# match end 
					if(substr($a, -1) != '*' && substr($a, 1) == '*') {
						if(substr($ruri, -strlen(trim($a, '*'))) == trim($a, '*')) { return false; }
					}
				}
			}
		}
			
	}
	
	# check if user role is allowed
    if(!wpraiser_user_role_processing_allowed('js')) { return false; } 
	
	# default
	return true;
}


# check if we can minify css on this page
function wpraiser_can_minify_css() {
	
	# check if we hit any exclusions from the compatibility page
	if(!wpraiser_can_process_common()) { return false; }
	if(!wpraiser_can_process_query_string()) { return false; }
	
	# settings
	global $wpraiser_settings;
	
	# disabled?
	if(isset($wpraiser_settings['css']['disable']) && $wpraiser_settings['css']['disable'] == true) { return false; }
	
	# check if user role is allowed
    if(!wpraiser_user_role_processing_allowed('css')) { return false; } 
	
	# default
	return true;
}



# check if we can minify js on this page
function wpraiser_can_minify_html() {
	
	# check if we hit any exclusions from the compatibility page
	if(!wpraiser_can_process_common()) { return false; }
	if(!wpraiser_can_process_query_string()) { return false; } 
	
	# settings
	global $wpraiser_settings;
	
	# disabled?
	if(isset($wpraiser_settings['html']['disable']) && $wpraiser_settings['html']['disable'] == true) { return false; }
	
	# check if user role is allowed
    if(!wpraiser_user_role_processing_allowed('html')) { return false; } 
			
	# default
	return true;
}


# check if we can integrate the CDN on this page
function wpraiser_can_process_cdn() {
	
	# check if we hit any exclusions from the compatibility page
	if(!wpraiser_can_process_common()) { return false; }
	if(!wpraiser_can_process_query_string()) { return false; } 	
	
	# check if user role is allowed
    if(!wpraiser_user_role_processing_allowed('cdn')) { return false; } 
		
	# default
	return true;
}

# check if we can lazy load on this page
function wpraiser_can_process_lazyload() {
	
	# check if we hit any exclusions from the compatibility page
	if(!wpraiser_can_process_common()) { return false; }
	if(!wpraiser_can_process_query_string()) { return false; }
	
	# check if user role is allowed
    if(!wpraiser_user_role_processing_allowed('lazy')) { return false; } 
		
	# default
	return true;
}



# check if we can cache the page
function wpraiser_can_cache() {

	# compatibility with DONOTCACHEPAGE
	if( defined('DONOTCACHEPAGE') && DONOTCACHEPAGE ){ return false; }
	
	# detect api requests (only defined after parse_request hook)
	if( defined('REST_REQUEST') && REST_REQUEST ){ return false; } 
	
	# only cache when there are 200 headers
	if(function_exists('http_response_code') && http_response_code() !== 200){ return false; }
	
	# don't cache specific WordPress areas
	if(function_exists('is_search') && is_search()){ return false; }
	if(function_exists('is_404') && is_404()){ return false; }
	if(function_exists('is_preview') && is_preview()){ return false; }
	if(function_exists('is_customize_preview') && is_customize_preview()){ return false; }	
	if(function_exists('is_admin') && is_admin()){ return false; }
	if(function_exists('is_blog_admin') && is_blog_admin()){ return false; }
	if(function_exists('is_network_admin') && is_network_admin()){ return false; }
	if(function_exists('is_robots') && is_robots()){ return false; }
		
	# no point caching pagination above page one
	if(function_exists('is_paged') && is_paged()){ return false; }
	
	# don't cache specific WooCommerce areas
	if(function_exists('is_checkout') && is_checkout()){ return false; }
	if(function_exists('is_account_page') && is_account_page()){ return false; }
	if(function_exists('is_ajax') && is_ajax()){ return false; }
	if(function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()){ return false; }	
	
	# specifically allow caching for amp pages
	if(function_exists('is_amp_endpoint') && is_amp_endpoint()){ return true; }
	if(function_exists('ampforwp_is_amp_endpoint') && ampforwp_is_amp_endpoint()){ return true; }
	
	# check if we hit any exclusions from the compatibility page
	if(!wpraiser_can_process_common()) { return false; }
	if(!wpraiser_can_process_query_string()) { return false; } 
	
	# globals
	global $wpraiser_settings;
	
	# check our exclusions list against the user cookies
	if(isset($_COOKIE) && is_array($_COOKIE) && isset($wpraiser_settings['cache']['cookies'])) {
		$arr = wpraiser_string_toarray($wpraiser_settings['cache']['cookies']);
		if(is_array($arr)) {
			foreach ($arr as $a) {
				foreach ($_COOKIE as $k=>$v) {
					
					# exact match
					if(isset($_COOKIE[$a])) { return false; }
					
					# match middle
					if(substr($a, -1) == '*' && substr($a, 1) == '*') {
						if(stripos($k, trim($a, '*')) !== false) { return false; }
					}
					
					# match beginning 
					else if(substr($a, -1) == '*' && substr($a, 1) != '*') {
						if(substr($k, 0, strlen(trim($a, '*'))) == trim($a, '*')) { return false; }
					}
					
					# match end 
					else if(substr($a, -1) != '*' && substr($a, 1) == '*') {
						if(substr($k, -strlen(trim($a, '*'))) == trim($a, '*')) { return false; }
					}					
				}
			}
		}	
	}
		
	# if there is an url, check some paths
	if(isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
		
		# parse url (path, query)
		$ruri = str_replace('//', '/', str_replace('..', '', preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', strtok($_SERVER['REQUEST_URI'], '?'))));
	
		# check our exclusions list against the URI Path
		if(!empty($ruri) && isset($wpraiser_settings['cache']['skip_url'])) {
			$arr = wpraiser_string_toarray($wpraiser_settings['cache']['skip_url']);
			if(is_array($arr)) {
				foreach ($arr as $a) {
					
					# exact match
					if($ruri == $a) { return false; }
					
					# match middle
					if(substr($a, -1) == '*' && substr($a, 1) == '*') {
						if(stripos($ruri, trim($a, '*')) !== false) { return false; }
					} 
					
					# match beginning 
					if(substr($a, -1) == '*' && substr($a, 1) != '*') {
						if(substr($ruri, 0, strlen(trim($a, '*'))) == trim($a, '*')) { return false; }
					}
					
					# match end 
					if(substr($a, -1) != '*' && substr($a, 1) == '*') {
						if(substr($ruri, -strlen(trim($a, '*'))) == trim($a, '*')) { return false; }
					}
				}
			}
		}
			
	}	
	
	# default
	return true;
}

	
# get file path for the current page html cache
function wpraiser_get_page_cache_location() {
	global $wpraiser_cache_paths, $wpraiser_settings;
	
	# directory exists
	if(is_dir($wpraiser_cache_paths['cache_dir_html']) && is_writable($wpraiser_cache_paths['cache_dir_html'])) {
		
		# get scheme and uri path
		$request_scheme = '';
		$request_uri = str_replace('//', '/', str_replace('..', '', preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', strtok($_SERVER['REQUEST_URI'], '?'))));
		
		# windows support, reverse forward slashes
		if(stripos($request_uri, DIRECTORY_SEPARATOR) !== false) {
			$request_uri = str_replace('/', DIRECTORY_SEPARATOR, $request_uri);
		}	
		
		# detect https
		if((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
			$request_scheme = '-https';
		}
		
		# detect mobile and amp users
		$is_mobile = '';
		
		# mobile users
		if(isset($wpraiser_settings['cache']['enable_mobile']) && $wpraiser_settings['cache']['enable_mobile'] == true && function_exists('wp_is_mobile') && wp_is_mobile() == true) { $is_mobile = 'mobile_'; }
		
		# detect geolocation
		$is_country = '';
		
		if( isset($wpraiser_settings['cache']['enable_geolocation']) && $wpraiser_settings['cache']['enable_geolocation'] == true && isset($wpraiser_settings['cache']['vary_geo']) && !empty($wpraiser_settings['cache']['vary_geo'])) {
			$cc = wpraiser_get_geolocation();
			if(!empty($cc)) {
				$arr = wpraiser_string_toarray($wpraiser_settings['cache']['vary_geo']);
				if(is_array($arr) && in_array($cc, $arr)) {
					$is_country = $cc.'_';
					$msg[] = 'Country: '.$cc;
				}	
			}
		}
		
		# vary cache on cookie name, with different values
		$vary_cookie = '';
		
		if(isset($wpraiser_settings['cache']['enable_vary_cookie']) && $wpraiser_settings['cache']['enable_vary_cookie'] == true) {
			if(isset($wpraiser_settings['cache']['vary_cookie']) && !empty($wpraiser_settings['cache']['vary_cookie'])) {
				if(isset($_COOKIE[$wpraiser_settings['cache']['vary_cookie']]) && strlen($_COOKIE[$wpraiser_settings['cache']['vary_cookie']]) > 0) {
					$vary_cookie = md5($_COOKIE[$wpraiser_settings['cache']['vary_cookie']]).'_';
				}
			}
		}
		
		# get cache file name
		return $wpraiser_cache_paths['cache_dir_html'] . $request_uri . $vary_cookie . $is_country . $is_mobile . 'index'.$request_scheme.'.html';

	}
	
	# no cache
	return false;
}


# save cache file, if allowed
function wpraiser_save_cache_file($html, $wpraiser_settings) {
	
	# is cache enabled with a valid lifespan?
	if( 
		(isset($wpraiser_settings['cache']['lifespan']) && is_numeric($wpraiser_settings['cache']['lifespan'])) && 
		(isset($wpraiser_settings['cache']['lifespan_unit']) && is_numeric($wpraiser_settings['cache']['lifespan_unit'])) && 
		(isset($wpraiser_settings['cache']['enable_page']) && $wpraiser_settings['cache']['enable_page'] == true) 
	){	
		$file = wpraiser_get_page_cache_location();
		if($file != false) {
			if(!file_exists($file) || (file_exists($file) && filemtime($file) + intval($wpraiser_settings['cache']['lifespan']) * intval($wpraiser_settings['cache']['lifespan_unit']) < time())) {
				wpraiser_save_file($file, $html . PHP_EOL . '<!-- Cache Date: '. date("D, d M Y H:i:s e").' -->');
				return true;
			}	
		}
	}
	
	return false;
}



# remove Gutenberg styles
function wpraiser_remove_wp_block_library_css() {
	global $wpraiser_settings;
	if(isset($wpraiser_settings['css']['remove_gutenberg']) && $wpraiser_settings['css']['remove_gutenberg'] == true) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'wc-block-style' );
	}
}







# get options into an array
function wpraiser_get_settings() {
	$wpraiser_settings = json_decode(get_option('wpraiser_settings'), true);

	# mandatory default exclusions
	$wpraiser_settings_default = wpraiser_get_default_settings($wpraiser_settings);
	
	# check if there are any pending field update routines
	$wpraiser_settings_default = wpraiser_get_updated_field_routines($wpraiser_settings_default);
	
	# update database if needed
	if($wpraiser_settings != $wpraiser_settings_default) {
		update_option('wpraiser_settings', json_encode($wpraiser_settings_default), false);
	}
	
	# return
	return $wpraiser_settings;

}


# return value from section and key name
function wpraiser_get_settings_value($wpraiser_settings, $section, $key) {
	if($wpraiser_settings != false && is_array($wpraiser_settings) && count($wpraiser_settings) > 1) {
		if(isset($wpraiser_settings[$section][$key])) {
			return $wpraiser_settings[$section][$key]; 
		}
	}
	return '';
}


# default exclusions by seting name
function wpraiser_get_default_settings($wpraiser_settings) {

	# default initial settings, if nothing set yet
	if(is_null($wpraiser_settings) || !is_array($wpraiser_settings) || count($wpraiser_settings) == 0){
		
		# initialize
		$wpraiser_settings = array();
		
		# html
		$wpraiser_settings['html']['remove_comments'] = 1;
		$wpraiser_settings['html']['minify'] = 1;
		$wpraiser_settings['html']['remove_generator'] = 1;
		$wpraiser_settings['html']['remove_shortlink'] = 1;
		$wpraiser_settings['html']['remove_hints'] = 1;
		$wpraiser_settings['html']['remove_favicon'] = 1;
		$wpraiser_settings['html']['remove_rsd'] = 1;
		$wpraiser_settings['html']['remove_rssref'] = 1;
		$wpraiser_settings['html']['remove_restref'] = 1;
		$wpraiser_settings['html']['remove_oembed'] = 1;
		$wpraiser_settings['html']['remove_emoji'] = 1;
		
		# css
		$wpraiser_settings['css']['remove_print'] = 1;
		$wpraiser_settings['css']['remove_gutenberg'] = 1;
		$wpraiser_settings['css']['min_files'] = 1;
		$wpraiser_settings['css']['min_inline'] = 1;
		
		# js
		$wpraiser_settings['js']['min_files'] = 1;
		$wpraiser_settings['js']['min_inline'] = 1;
		$wpraiser_settings['js']['use_phpminify'] = 1;
		$wpraiser_settings['js']['min_fallback'] = 1;
		
		# cache
		$wpraiser_settings['cache']['lifespan'] = 1;
		$wpraiser_settings['cache']['lifespan_unit'] = 3600;
		
		# lazy
		$wpraiser_settings['lazy']['enable_img'] = 1;
		$wpraiser_settings['lazy']['enable_bg'] = 1;
		$wpraiser_settings['lazy']['enable_iframe'] = 1;
		$wpraiser_settings['lazy']['enable_gravatar'] = 1;
		$wpraiser_settings['lazy']['video_wrap_on'] = 1;
		
		
		# minimum exclusions for cookies
		$arr = array('wordpress_logged_in_*', 'wordpress_no_cache', 'wp-postpass', 'woocommerce_cart_hash', 'woocommerce_items_in_cart', 'wp_woocommerce_session_*');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['cache']['cookies']));
		$wpraiser_settings['cache']['cookies'] = implode(PHP_EOL, wpraiser_array_order($arr));
		
		# minimum exclusions for urls
		$arr = array('/wp-content/*', '/wp-includes/*', '/wp-admin/*', '/wp-json/*', '/wc-api/*', '/downloads/*', '*/pdf/*');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['cache']['skip_url']));
		$wpraiser_settings['cache']['skip_url'] = implode(PHP_EOL, wpraiser_array_order($arr));
		
		# default Ignore Query Strings
		$arr = array('utm_source', 'utm_campaign', 'utm_medium', 'utm_expid', 'utm_term', 'utm_content', 'fb_action_ids', 'fb_action_types', 'fb_source', 'fbclid', '_ga', 'gclid', 'age-verified', 'ao_noptimize', 'usqp', 'cn-reloaded');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['cache']['ignore_qs']));
		$wpraiser_settings['cache']['ignore_qs'] = implode(PHP_EOL, wpraiser_array_order($arr));
		
		
		# CDN replacements, but only if empty
		$arr = array('img[src*=/wp-content/], img[data-src*=/wp-content/], img[data-srcset*=/wp-content/]', 'picture source[srcset*=/wp-content/]', 'video source[type*=video]', 'image[height]', 'link[rel=icon], link[rel=apple-touch-icon]', 'meta[name=msapplication-TileImage]', 'a[data-interchange*=/wp-content/]', 'rs-slide[data-thumb]', 'form[data-product_variations]');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['cdn']['integration']));
		$wpraiser_settings['cdn']['integration'] = implode(PHP_EOL, wpraiser_array_order($arr));

		
		# image exclusions
		$arr = array('div[id=logo] img[src], img[class*=logo], img[id*=logo], img[id*=site-logo]', 'img[class*=hidden-lg], img[class*=hidden-md], img[class*=hidden-sm], img[class*=hidden-xs]', 'img[class*=skip-lazy], img[data-lazy-original], img[data-lazy-src], img[data-lazyload], img[class*=lazypse], img[data-lazysrc], img[data-no-lazy]', 'img[class*=gazette-featured-content-thumbnail]', 'img[class*=ls-bg], img[class*=ls-l]', 'img[class*=portfolio-image], img[class*=pt-cv-thumbnail]', 'img[data-bgposition], img[data-height-percentage]', 'img[swatch-img], img[data-src], img[data-srcset], img[fullurl]', 'img[class*=rev-slidebg], img[lazy-slider-img], img[soliloquy-image], img[data-envira-src]', 'script img[src], noscript img[src]', 'ul[class*=masonry-items] img[src]', 'video img[src]');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['lazy']['img_exc']));
		$wpraiser_settings['lazy']['img_exc'] = implode(PHP_EOL, wpraiser_array_order($arr));

		# background image exclusions
		$arr = array('*[class*=videoThumb]');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['lazy']['bg_exc']));
		$wpraiser_settings['lazy']['bg_exc'] = implode(PHP_EOL, wpraiser_array_order($arr));
		
		# iframe exclusions
		$arr = array('iframe[class*=iframe-lazypse], iframe[class*=lazy], iframe[class*=skip-lazy]');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['lazy']['iframe_exc']));
		$wpraiser_settings['lazy']['iframe_exc'] = implode(PHP_EOL, wpraiser_array_order($arr));

		# responsive 16:9 div wrapper domains, but only if empty excl
		$arr = array('youtu.be', 'youtube.com', 'youtube-nocookie.com', 'vimeo.com', 'player.vimeo.com/video/', 'google.com/maps/');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['lazy']['video_wrap']));
		$wpraiser_settings['lazy']['video_wrap'] = implode(PHP_EOL, wpraiser_array_order($arr));


		# Low Priority CSS files
		$arr = array('/fonts.googleapis.com', '/animate.css', '/animate.min.css', '/icomoon.css', '/animations/', '/eicons/css/', 'font-awesome', '/flag-icon.min.css', '/fonts.css', '/pe-icon-7-stroke.css', '/fontello.css', '/dashicons.min.css', '/fl-icons.css', '/genericons.css', '/vector-icons.css', '/linecon.css', '/steadysets.css');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['css']['lowp_files']));
		$wpraiser_settings['css']['lowp_files'] = implode(PHP_EOL, wpraiser_array_order($arr));	

		# js, minimum exclusions for urls
		$arr = array('/wp-content/*', '/wp-includes/*', '/wp-admin/*', '/wp-json/*', '/wc-api/*', '/downloads/*', '*/pdf/*');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['js']['skip_url']));
		$wpraiser_settings['js']['skip_url'] = implode(PHP_EOL, wpraiser_array_order($arr));		
		
		# js header
		$arr = array('/jquery.js', '/jquery.min.js', '/jquery-migrate-', '/jquery-migrate.min.js', '/jquery-migrate.js');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['js']['merge_files_header']));
		$wpraiser_settings['js']['merge_files_header'] = implode(PHP_EOL, wpraiser_array_order($arr));
			
		# js footer
		$arr = array('/wp-content/', '/wp-includes/', '/wp-admin/', '/cdnjs.cloudflare.com/ajax/libs/', '/ajax.googleapis.com/ajax/libs/', '/ajax.aspnetcdn.com/ajax/');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['js']['merge_files_footer']));
		$wpraiser_settings['js']['merge_files_footer'] = implode(PHP_EOL, wpraiser_array_order($arr));
		
		# js footer dependencies
		$arr = array('wp.i18n');
		$arr = array_merge($arr, wpraiser_string_toarray($wpraiser_settings['js']['delay_inline_footer']));
		$wpraiser_settings['js']['delay_inline_footer'] = implode(PHP_EOL, wpraiser_array_order($arr));

	}
	
	
	# unescape and trim recursively
	if(is_array($wpraiser_settings)) {
		foreach ($wpraiser_settings as $group=>$arr) {
			if(is_array($arr)) {
				foreach ($arr as $k=>$v) {
					
					# only numeric or string values allowed at this level
					if(!is_string($v) && !is_numeric($v) && !is_array($v)) { $wpraiser_settings[$group][$k] = ''; }
					
					# numeric fields, only positive integers allowed 
					if(is_numeric($v)) { $wpraiser_settings[$group][$k] = trim(abs(intval($v))); }
					
					# unescape text area content
					if(is_string($v)) { $wpraiser_settings[$group][$k] = trim(stripslashes($v)); }
					
					# handle unused code arrays, prevent saving invalid rules
					if(is_array($v) && $k == 'rule') { 
						foreach ($v as $sk=>$sv) {
							if(empty(trim($sv['cond_one'])) && empty(trim($sv['cond_two']))) {
								unset($_POST['wpraiser_settings'][$group][$k][$sk]);
							}
						}
					}
									
				}
			}
		}
	}	
	
	# return	
	return $wpraiser_settings;
}


# create a directory, recursively
function wpraiser_create_dir($d) {
	
	# must have
	if(!defined('WP_CONTENT_DIR')) { return false; }
	
	# get permissions from cache directory, or default to 777
	$ch = 0777;
	$cache_dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache';
	if(is_dir($cache_dir) && function_exists('stat') && wpraiser_function_available('stat')) {
		if ($stat = @stat($cache_dir)) { $ch = $stat['mode'] & 0007777; }
	}
	
	# create recursively
	if(!is_dir($d)) {
		if ( @mkdir($d, $ch, true) ) {
			if ( $ch != ($ch & ~umask()) ) {
				$p = explode(DIRECTORY_SEPARATOR, substr($d, strlen(dirname($d)) + 1 ));
					for ($i = 1, $c = count($p ); $i <= $c; $i++) {
						@chmod(dirname($d) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array_slice($p, 0, $i)), $ch);
				}
			}
		} else {
			# fallback
			wp_mkdir_p($d);
		}
	}
	
	return true;
}


# return true if code should be removed, based on our method and exceptions 
function wpraise_unusedcode_remove_when_except_match_uri($except, $method) {
	
	# must have
	if(!isset($_SERVER['REQUEST_URI'])) { 
		return false;
	}
	
	# current uri path
	$ruri = str_replace('//', '/', str_replace('..', '', preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', strtok($_SERVER['REQUEST_URI'], '?'))));
	
	# not empty
	if(empty($ruri)) {
		return false;
	}
	
	# check if current url match any exception
	# if method is allow, it removes the file on exception
	# if method is remove, it allows the file on exception
	$arr = wpraiser_string_toarray($except);
	if(is_array($arr) && count($arr) > 0) {
		foreach ($arr as $a) {

			# exact match
			if($ruri == $a) { 
				if($method == 'on') { return true; } else { return false; }
			}
				
			# match middle
			if(substr($a, -1) == '*' && substr($a, 1) == '*') {
				if(stripos($ruri, trim($a, '*')) !== false) { 
					if($method == 'on') { return true; } else { return false; }
				}
			} 
				
			# match beginning 
			if(substr($a, -1) == '*' && substr($a, 1) != '*') {
				if(substr($ruri, 0, strlen(trim($a, '*'))) == trim($a, '*')) { 
					if($method == 'on') { return true; } else { return false; }
				}
			}
			
			# match end 
			if(substr($a, -1) != '*' && substr($a, 1) == '*') {
				if(substr($ruri, -strlen(trim($a, '*'))) == trim($a, '*')) { 
					if($method == 'on') { return true; } else { return false; }
				}
			}

		}
	}
	
	# fallback, remove based on method
	if($method == 'off') { return true; } else { return false; }
}






# remove a director, recursively
function wpraiser_rrmdir($path) {

	# must be on the allowed path
	if(empty($path)  || !defined('WP_CONTENT_DIR') || stripos($path, '/wpraiser') === false) {
		return 'Requested purge path is not allowed!';
	}
	
	# purge recursively
	clearstatcache();
	if(is_dir($path)) {
		try {
			$i = new DirectoryIterator($path);
			foreach($i as $f){
				if($f->isFile()){ @unlink($f->getRealPath());
				} else if(!$f->isDot() && $f->isDir()){
					wpraiser_rrmdir($f->getRealPath());
					if(is_dir($f->getRealPath())) { @rmdir($f->getRealPath()); }
				}
			}
		} catch (Exception $e) {
			return get_class($e) . ": " . $e->getMessage();
		}
		
		# self
		if(is_dir($path)) { @rmdir($path); }
	}
	
}


# Fix the permission bits on generated files
function wpraiser_fix_permission_bits($file){
				
	# must be on the allowed path
	if(empty($file) || !defined('WP_CONTENT_DIR') || stripos($file, '/wpraiser') === false) {
		return 'Requested path is not allowed!';
	}
	
	if(function_exists('stat') && wpraiser_function_available('stat')) {
		if ($stat = @stat(dirname($file))) {
			$perms = $stat['mode'] & 0007777;
			@chmod($file, $perms);
			clearstatcache();
			return true;
		}
	}
	
	# get permissions from parent directory
	$perms = 0777; 
	if(function_exists('stat') && wpraiser_function_available('stat')) {
		if ($stat = @stat(dirname($file))) { $perms = $stat['mode'] & 0007777; }
	}
	
	if (file_exists($file)){
		if ($perms != ($perms & ~umask())){
			$folder_parts = explode( DIRECTORY_SEPARATOR, substr( $file, strlen(dirname($file)) + 1 ) );
				for ( $i = 1, $c = count( $folder_parts ); $i <= $c; $i++ ) {
				@chmod(dirname($file) . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, array_slice( $folder_parts, 0, $i ) ), $perms );
			}
		}
		return true;
	}

	return false;
}


# check if PHP has some functions disabled
function wpraiser_function_available($func) {
	if (ini_get('safe_mode')) return false;
	$disabled = ini_get('disable_functions');
	if ($disabled) {
		$disabled = explode(',', $disabled);
		$disabled = array_map('trim', $disabled);
		return !in_array($func, $disabled);
	}
	return true;
}


# open a multiline string, order, filter duplicates and return as array
function wpraiser_string_toarray($value){
	$arr = explode(PHP_EOL, $value);
	return wpraiser_array_order($arr);}

# filter duplicates, order and return array
function wpraiser_array_order($arr){
	if(!is_array($arr)) { return array(); }
	$a = array_map('trim', $arr);
	$b = array_filter($a);
	$c = array_unique($b);
	sort($c);
	return $c;
}


# return size in human format
function wpraiser_format_filesize($bytes, $decimals = 2) {
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
    for ($i = 0; ($bytes / 1024) > 0.9; $i++, $bytes /= 1024) {}
	if($i == 0) { $i = 1; $bytes = $bytes / 1024; } # KB+ only
    return sprintf( "%1.{$decimals}f %s", round( $bytes, $decimals ), $units[$i] );
}


# increment file names
function wpraiser_cache_increment() {
	$now = time();
	update_option('wpraiser_last_cache_update', $now, 'no');
	return $now;
}


# save cache file, if allowed
function wpraiser_save_file($file, $content) {

	# get directory
	$path = dirname($file);
				
	# must have
	if(empty($path) ) { 
		return 'Requested path is not allowed!';
	}
				
	# must be on the allowed path
	if(empty($path) || !defined('WP_CONTENT_DIR') || stripos($path, '/wpraiser') === false) {
		return 'Requested path is not allowed!';
	}
											
	# create directory structure
	wpraiser_create_dir($path);
		
	# save file
	file_put_contents($file, $content);
	wpraiser_fix_permission_bits($file);
	return true;

}


# functions, get full url
function wpraiser_normalize_url($src, $wp_domain, $wp_home) {
	
	# preserve empty source handles
	$hurl = trim($src); if(empty($hurl)) { return $hurl; }      

	# some fixes
	$hurl = str_replace(array('&#038;', '&amp;'), '&', $hurl);

	#make sure wp_home doesn't have a forward slash
	$wp_home = rtrim($wp_home, '/');
	
	# protocol scheme
	$scheme = parse_url($wp_home)['scheme'].'://';

	# apply some filters
	if (substr($hurl, 0, 2) === "//") { $hurl = $scheme.ltrim($hurl, "/"); }  # protocol only
	if (substr($hurl, 0, 4) === "http" && stripos($hurl, $wp_domain) === false) { return $hurl; } # return if external domain
	if (substr($hurl, 0, 4) !== "http" && stripos($hurl, $wp_domain) !== false) { $hurl = $wp_home.'/'.ltrim($hurl, "/"); } # protocol + home

	# prevent double forward slashes in the middle
	$hurl = str_replace('###', '://', str_replace('//', '/', str_replace('://', '###', $hurl)));

	# consider different wp-content directory for relative paths
	$proceed = 0; 
	if(!empty($wp_home)) { 
		$alt_wp_content = basename($wp_home); 
		if(substr($hurl, 0, strlen($alt_wp_content)) === $alt_wp_content) { $proceed = 1; } 
	}

	# protocol + home for relative paths
	if (substr($hurl, 0, 12) === "/wp-includes" || substr($hurl, 0, 9) === "/wp-admin" || substr($hurl, 0, 11) === "/wp-content" || $proceed == 1) { 
		$hurl = $wp_home.'/'.ltrim($hurl, "/"); 
	}

	# make sure there is a protocol prefix as required
	$hurl = $scheme.str_replace(array('http://', 'https://'), '', $hurl); # enforce protocol

	# no query strings on css and js files
	if (stripos($hurl, '.js?') !== false) { $hurl = stristr($hurl, '.js?', true).'.js'; } # no query strings
	if (stripos($hurl, '.css?') !== false) { $hurl = stristr($hurl, '.css?', true).'.css'; } # no query strings

	return wpraiser_remove_cssjs_ver($hurl);	
}


# Remove default wordpress query string from static files
function wpraiser_remove_cssjs_ver($href) {
	if (stripos($href, '?ver=') !== false) {
		$href = stristr($href, '?ver=', true);  
	}
	if (stripos($href, '&ver=') !== false) {
		$href = stristr($href, '&ver=', true);  
	}
	return $href;
}


# get transients
function wpraiser_get_transient($key, $check=null) {
	
	global $wpdb;
	
	# normalize unknown keys
	if(strlen($key) != 40) { $key = hash('sha1', $key); }
	
	# check or fetch
	if($check) {
		$sql = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}wpraiser_cache WHERE uid = %s LIMIT 1", $key);
	} else {
		$sql = $wpdb->prepare("SELECT content FROM {$wpdb->prefix}wpraiser_cache WHERE uid = %s LIMIT 1", $key);
	}

	# get result from database
	$result = $wpdb->get_row($sql);
	
	# return true if just checking if it exists
	if(isset($result->id)) {
		return true;
	}
	
	# return content if found
	if(isset($result->content)) {
		return $result->content;
	}
	
	# fallback
	return false;
}

# set cache
function wpraiser_set_transient($arr) {
	
	# must have
	if(!is_array($arr) || (is_array($arr) && (count($arr) == 0 || empty($arr)))) { return false; }
	if(!isset($arr['uid']) || !isset($arr['date']) || !isset($arr['type']) || !isset($arr['content']) || !isset($arr['meta'])) { return false; }
	
	# normalize unknown keys
	if(strlen($arr['uid']) != 40) { $arr['uid'] = hash('sha1', $arr['uid']); }
	
	# check if it already exists and return early if it does
	$status = wpraiser_get_transient($arr['uid'], true);
	if($status) { return true; }
	
	# else insert
	global $wpdb;
	
	# initialize arrays (fields, types, values)
	$fld = array();
	$tpe = array();
	$vls = array();
	
	# define possible data types
	$str = array('uid', 'type', 'content', 'meta');
	$int = array('date');
	$all = array_merge($str, $int);
	
	# process only recognized columns
	foreach($arr as $k=>$v) {
		if(in_array($k, $all)) {
			if(in_array($k, $str)) { $tpe[] = '%s'; } else { $tpe[] = '%d'; }
			if($k == 'meta') { $v = json_encode($v); }
			$fld[] = $k;
			$vls[] = $v;
		}
	}
		
	# prepare and insert to database
	$sql = $wpdb->prepare("INSERT IGNORE INTO ".$wpdb->prefix."wpraiser_cache (".implode(', ', $fld).") VALUES (".implode(', ', $tpe).")", $vls);
	$result = $wpdb->query($sql);
	
	# check if it already exists
	if($result) {
		return true;
	}
	
	# fallback
	return false;
	
}

# delete transient
function wpraiser_del_transient($key) {
	
	global $wpdb;
	
	# normalize unknown keys
	if(strlen($key) != 40) { $key = hash('sha1', $key); }
	
	# delete
	$sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}wpraiser_cache WHERE uid = %s", $key);
	$result = $wpdb->get_row($sql);
	return true;
}





# save log to database
function wpraiser_save_log($arr) {
	
	# must have
	if(!is_array($arr) || (is_array($arr) && (count($arr) == 0 || empty($arr)))) { return false; }
	if(!isset($arr['uid']) || !isset($arr['date']) || !isset($arr['type']) || !isset($arr['content']) || !isset($arr['meta'])) { return false; }
	
	# normalize unknown keys
	if(strlen($arr['uid']) != 40) { $arr['uid'] = hash('sha1', $arr['uid']); }
	
	# else insert
	global $wpdb, $wpraiser_cache_paths;
	
	# initialize arrays (fields, types, values)
	$fld = array();
	$tpe = array();
	$vls = array();
	
	# define possible data types
	$str = array('uid', 'type', 'content', 'meta');
	$int = array('date');
	$all = array_merge($str, $int);
	
	# process only recognized columns
	foreach($arr as $k=>$v) {
		if(in_array($k, $all)) {
			if(in_array($k, $str)) { $tpe[] = '%s'; } else { $tpe[] = '%d'; }
			if($k == 'content') { $v = json_encode($v); }
			if($k == 'meta') { $v = json_encode($v); }
			if($k == 'uid') { $v = hash('sha1', $v); }
			
			# array for prepare
			$fld[] = $k;
			$vls[] = $v;
		}
	}
	
	# prepare and insert to database
	$sql = $wpdb->prepare("INSERT IGNORE INTO ".$wpdb->prefix."wpraiser_logs (".implode(', ', $fld).") VALUES (".implode(', ', $tpe).")", $vls);
	$result = $wpdb->query($sql);
	
	# check if it already exists
	if($result) {
		return true;
	}
	
	# fallback
	return false;
	
}




# try to open the file from the disk, before downloading
function wpraiser_maybe_download($url) {
	
	# must have
	if(is_null($url) || empty($url)) { return false; }
	
	# get domain
	global $wpraiser_urls;
	
	# check if we can open the file locally first
	if (stripos($url, $wpraiser_urls['wp_domain']) !== false && isset($_SERVER['DOCUMENT_ROOT'])) {
		
		# file path
		$f = str_replace(rtrim($wpraiser_urls['wp_home'], '/'), $_SERVER['DOCUMENT_ROOT'], $url);
		$f = str_replace('/', DIRECTORY_SEPARATOR, $f);	# windows compatibility
		
		# did it work?
		if (file_exists($f)) {
			return array('content'=>file_get_contents($f), 'src'=>'Disk');
		}
	}

	# fallback to downloading
	
	# this useragent is needed for google fonts (woff files only + hinted fonts)
	$uagent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586';

	# cache buster
	$query = 'cache='.time();
	$parsedUrl = parse_url($url);
	if ($parsedUrl['path'] === null) { $url .= '/'; }
	if ($parsedUrl['query'] === null) { $separator = '?'; } else { $separator = '&'; }
	$url .= $separator.$query;

	# fetch via wordpress functions
	$response = wp_remote_get($url, array('user-agent'=>$uagent, 'timeout' => 7, 'httpversion' => '1.1', 'sslverify'=>false)); 
	$res_code = wp_remote_retrieve_response_code($response);
	if($res_code == '200') {
		$content = wp_remote_retrieve_body($response);
		if(strlen($content) > 1) {
			return array('content'=>$content, 'src'=>'Web');
		}
	}
	
	# failed
	return array('error'=>'Could not read or fetch from '. $url);
}


# download thumbnails, avatars, etc into the cache directory	
function wpraiser_download_images($url) {
	
	global $wpraiser_cache_paths;
	$wcp = $wpraiser_cache_paths;
	
	# must have
	if(empty($url) || !isset($wcp['cache_dir_img'])) { return false; }
	
	# skip data urls
	if(stripos($url, 'data:image') !== false) {
		return false;
	}
	
	# uid and tkey
	$uid = hash('sha1', $url);
	
	# check transients first
	$public = wpraiser_get_transient($uid);
	if($public !== false && is_string($public)) {
		return $public;
	}
	
	# download and save
	$res = wp_remote_get(esc_url_raw(urldecode($url)), array('timeout' => 5)); 
	$res_code = wp_remote_retrieve_response_code($res);
	$res_headers = wp_remote_retrieve_headers($res);
	if($res_code == '200') {
		$data = wp_remote_retrieve_body($res);
		if(!empty($data) && isset($res_headers['content-type'])) {
			
			# allowed extensions
			$mimes = array('image/png'=>'png', 'image/jpeg'=>'jpg', 'image/webp'=>'webp');
			if(isset($mimes[$res_headers['content-type']])) {
							
				# filename
				$file = $wcp['cache_dir_img'] . DIRECTORY_SEPARATOR . $uid .'.'. $mimes[$res_headers['content-type']];
				$public = $wcp['cache_url_img'] . '/' . $uid .'.'. $mimes[$res_headers['content-type']];
						
				# save file
				file_put_contents($file, $data);
				wpraiser_fix_permission_bits($file);
				
				# if successful
				if(file_exists($file)) {
					$arr = array('uid'=>$uid, 'date'=>time(), 'type'=>'imgurl', 'content'=>$public, 'meta'=>array('fs'=>strlen($data)));
					wpraiser_set_transient($arr);
					return $public;
				}
			
			}
		}
	}
	
	# fallback
	return false;	
}


# download small images into cache and return a base64 encoded url data	
function wpraiser_download_images_base64($url) {
	
	global $wpraiser_cache_paths;
	$wcp = $wpraiser_cache_paths;
	
	# must have
	if(empty($url) || !isset($wcp['cache_dir_img'])) { return false; }
	
	# skip data urls
	if(stripos($url, 'data:image') !== false) {
		return false;
	}
	
	# uid and tkey
	$uid = hash('sha1', $url);
	
	# check transients first
	$public = wpraiser_get_transient($uid);
	if($public !== false && is_string($public)) {
		return $public;
	}
	
	# download and save
	$res = wp_remote_get(esc_url_raw(urldecode($url)), array('timeout' => 5)); 
	$res_code = wp_remote_retrieve_response_code($res);
	$res_headers = wp_remote_retrieve_headers($res);
	if($res_code == '200') {
		$data = wp_remote_retrieve_body($res);
		if(!empty($data) && isset($res_headers['content-type'])) {
			
			# allowed extensions
			$mimes = array('image/png'=>'png', 'image/jpeg'=>'jpg', 'image/webp'=>'webp', 'image/svg+xml'=>'svg');
			if(isset($mimes[$res_headers['content-type']])) {
							
				# base64 encode image
				$public = 'data:'.$res_headers['content-type'].';base64,'.base64_encode($data);	
				
				# save to cache
				$arr = array('uid'=>$uid, 'date'=>time(), 'type'=>'img64', 'content'=>$public, 'meta'=>array('fs'=>strlen($data)));
				wpraiser_set_transient($arr);
				
				# if successful
				return $public;
			
			}
		}
	}
	
	# fallback
	return false;	
}



# check for php or html, skip if found
function wpraiser_not_php_html($code) {
	if((strtolower(substr($code, 0, 2)) != "<?" && stripos($code, "<?php") === false) || strtolower(substr($code, 0, 9)) != "<!doctype") {
		return true;
	}
	return false;
}


# remove UTF8 BOM
function wpraiser_remove_utf8_bom($text) {
    $bom = pack('H*','EFBBBF');
	while (preg_match("/^$bom/", $text)) {
		$text = preg_replace("/^$bom/ui", '', $text);
	}
    return $text;
}


# validate and minify css
function wpraiser_maybe_minify_css_file($css, $url, $min) {
	
	# return early if empty
	if(empty($css) || $css == false) { return $css; }
		
	# process css only if it's not php or html
	if(wpraiser_not_php_html($css)) {
	
		# filtering
		$css = wpraiser_remove_utf8_bom($css); 
		$css = str_ireplace('@charset "UTF-8";', '', $css);
		
		# remove query strings from fonts
		$css = preg_replace('/(.eot|.woff2|.woff|.ttf)+[?+](.+?)(\)|\'|\")/ui', "$1"."$3", $css);

		# remove sourceMappingURL
		$css = preg_replace('/(\/\/\s*[#]\s*sourceMappingURL\s*[=]\s*)([a-zA-Z0-9-_\.\/]+)(\.map)/ui', '', $css);
		
		# fix url paths
		if(!empty($url)) {
			$matches = array(); preg_match_all("/url\(\s*['\"]?(?!data:)(?!http)(?![\/'\"])(.+?)['\"]?\s*\)/ui", $css, $matches);
			foreach($matches[1] as $a) { $b = trim($a); if($b != $a) { $css = str_replace($a, $b, $css); } }
			$css = preg_replace("/url\(\s*['\"]?(?!data:)(?!http)(?![\/'\"#])(.+?)['\"]?\s*\)/ui", "url(".dirname($url)."/$1)", $css);	
		}
	
		# minify string with relative urls
		if($min) {
			$css = wpraiser_minify_css_string($css, $url);
		}
		
		# add font-display for google fonts and fontawesome
		# https://developers.google.com/web/updates/2016/02/font-display
		if(!empty($url)) { 
			if(stripos($url, 'fonts.googleapis.com') !== false || stripos($url, 'fontawesome') !== false) {
			$css = str_ireplace('font-style:normal;', 'font-display:block;font-style:normal;', $css);
			}
		}

		# apply additional filters
		if(function_exists('wpraiser_filter_maybe_minify_css_file')) {
			$css = wpraiser_filter_maybe_minify_css_file($css);
		}
		
		# return css
		return trim($css);
	
	}

	return false;	
}


# validate and minify js
function wpraiser_maybe_minify_js($js, $url, $enable_js_minification) {

	# return early if empty
	if(empty($js) || $js == false) { return $js; }
		
	# process js only if it's not php or html
	if(wpraiser_not_php_html($js)) {
		
		# globals
		global $wpraiser_settings;
	
		# filtering
		$js = wpraiser_remove_utf8_bom($js); 
		
		# remove sourceMappingURL
		$js = preg_replace('/(\/\/\s*[#]\s*sourceMappingURL\s*[=]\s*)([a-zA-Z0-9-_\.\/]+)(\.map)/ui', '', $js);
		
		# check if the specific code or file is excluded from minification
		if(!is_null($url)) {
			# js files
			if(isset($wpraiser_settings['js']['skip_min']) && !empty($wpraiser_settings['js']['skip_min'])) {
				$arr = wpraiser_string_toarray($wpraiser_settings['js']['skip_min']);
				if(is_array($arr) && count($arr) > 0) {
					foreach ($arr as $a) { 
						if(stripos($url, $a) !== false) {
							
							# fallback to white space minification?
							if(isset($wpraiser_settings['js']['min_fallback']) && $wpraiser_settings['js']['min_fallback'] == true) {
								$js = wpraiser_raisermin_js($js);
							}
							
							# skip further minification
							$enable_js_minification = false;		
							break;									
						} 
					}
				}
			}	
		} else {
			# inlined code
			if(isset($wpraiser_settings['js']['skip_min_inline']) && !empty($wpraiser_settings['js']['skip_min_inline'])) {
				$arr = wpraiser_string_toarray($wpraiser_settings['js']['skip_min_inline']);
				if(is_array($arr) && count($arr) > 0) {
					foreach ($arr as $a) { 
						if(stripos($js, $a) !== false) {
							
							# fallback to white space minification?
							if(isset($wpraiser_settings['js']['min_fallback']) && $wpraiser_settings['js']['min_fallback'] == true) {
								$js = wpraiser_raisermin_js($js);
							}
							
							# skip further minification
							$enable_js_minification = false;
							break;									
						} 
					}
				}
			}
		}
		
		# skip minification when the js file ends with min.js
		if(!is_null($url) && substr($url, -7) == 'min.js') {
			$enable_js_minification = false;								
		}		
		
		# minify?
		if($enable_js_minification == true) {
			
			# minify
			if(isset($wpraiser_settings['js']['use_phpminify']) && $wpraiser_settings['js']['use_phpminify'] == true) {
				# PHP Minify from https://github.com/matthiasmullie/minify
				$minifier = new WPR\MatthiasMullie\Minify\JS($js);
				$min = $minifier->minify();
			} else {
				# white space only
				$min = wpraiser_raisermin_js($js);
			}
			
			# return if not empty
			if($min !== false && strlen(trim($min)) > 0) { 
				return $min;
			}
			
		}
		

		
		# return js
		return trim($js);
	
	}

	return false;	
}


# minify ld+json scripts
function wpraiser_minify_microdata($data) {
	$data = trim(preg_replace('/\s+/u', ' ', $data));
	$data = str_replace(array('" ', ' "'), '"', $data);
	$data = str_replace(array('[ ', ' ['), '[', $data);
	$data = str_replace(array('] ', ' ]'), ']', $data);
	return $data;
}
					

# minify css string with PHP Minify
function wpraiser_minify_css_string($css, $rq=null) {
	
	# return early if empty
	if(empty($css) || $css == false) { return $css; }
	
	# get domain
	global $wpraiser_urls, $wpraiser_settings;
	$wp_domain_short = str_ireplace('www.', '', $wpraiser_urls['wp_domain']);
	
	# minify	
	$minifier = new WPR\MatthiasMullie\Minify\CSS($css);
	$minifier->setMaxImportSize(10); # embed assets up to 10 Kb (default 5Kb) - processes gif, png, jpg, jpeg, svg & woff
	$min = $minifier->minify();
	


	# make relative urls
	$min = str_replace('http://'.$wpraiser_urls['wp_domain'], '', $min);
	$min = str_replace('https://'.$wpraiser_urls['wp_domain'], '', $min);
	$min = str_replace('//'.$wpraiser_urls['wp_domain'], '', $min);	
	$min = str_replace('http://'.str_ireplace('www.', '', $wpraiser_urls['wp_domain']), '', $min);
	$min = str_replace('https://'.str_ireplace('www.', '', $wpraiser_urls['wp_domain']), '', $min);
	$min = str_replace('//'.str_ireplace('www.', '', $wpraiser_urls['wp_domain']), '', $min);	
		
	# return
	if($min != false) { 
		return $min; 
	}
	
	# fallback
	return $css;
}


# replace css imports with origin css code
function wpraiser_replace_css_imports($css, $rq=null) {
	
	# globals
	global $wpraiser_urls, $wpraiser_settings;

	# handle import url rules
	$cssimports = array();
	preg_match_all ("/@import[ ]*['\"]{0,}(url\()*['\"]*([^;'\"\)]*)['\"\)]*[;]{0,}/ui", $css, $cssimports);
	if(isset($cssimports[0]) && isset($cssimports[2])) {
		foreach($cssimports[0] as $k=>$cssimport) {
				
			# if @import url rule, or guess full url
			if(stripos($cssimport, 'import url') !== false && isset($cssimports[2][$k])) {
				$url = trim($cssimports[2][$k]);
			} else {
				if(!is_null($rq) && !empty($rq)) {
					$url = dirname($rq) . '/' . trim($cssimports[2][$k]);	
				}
			}
			
			# must have
			if(!empty($url)) {
				
				# make sure we have a complete url
				$href = wpraiser_normalize_url($url, $wpraiser_urls['wp_domain'], $wpraiser_urls['wp_home']);

				# download, minify, cache (no ver query string)
				$tkey = hash('sha1', $href);
				$subcss = wpraiser_get_transient($tkey);
				if ($subcss === false) {
				
					# get minification settings for files
					if(isset($wpraiser_settings['css']['min_files'])) {
						$enable_css_minification = $wpraiser_settings['css']['min_files'];
					}					
					
					# force minification on google fonts
					if(stripos($href, 'fonts.googleapis.com') !== false) {
						$enable_css_minification = true;
					}
					
					# download file, get contents, merge
					$ddl = array();
					$ddl = wpraiser_maybe_download($href);
				
					# if success
					if(isset($ddl['content'])) {
							
						# contents
						$subcss = $ddl['content'];
						
						# minify
						$subcss = wpraiser_maybe_minify_css_file($subcss, $href, $enable_css_minification);

						# remove specific, minified CSS code
						if(isset($wpraiser_settings['css']['remove_code']) && !empty($wpraiser_settings['css']['remove_code'])) {
							$arr = wpraiser_string_toarray($wpraiser_settings['css']['remove_code']);
							if(is_array($arr) && count($arr) > 0) {
								foreach($arr as $str) {
									$subcss = str_replace($str, '', $subcss);
								}
							}
						}
							
						# trim code
						$subcss = trim($subcss);
							
						# size in bytes
						$fs = strlen($subcss);
						$ur = str_replace($wpraiser_urls['wp_home'], '', $href);
						$tkey_meta = array('fs'=>$fs, 'url'=>str_replace($wpraiser_cache_paths['cache_url_min'].'/', '', $ur), 'mt'=>$media);
								
						# save
						wpraiser_set_transient(array('uid'=>$tkey, 'date'=>$tvers, 'type'=>'css', 'content'=>$subcss, 'meta'=>$tkey_meta));
					}
				}

				# replace import rule with inline code
				if ($subcss !== false && !empty($subcss)) {
					$css = str_replace($cssimport, $subcss, $css);
				}
				
			}
		}
	}
	
	# return
	return $css;
	
}


# escape html tags for document.write
function wpraiser_escape_url_js($str) {
	$str = trim(preg_replace('/[\t\n\r\s]+/iu', ' ', $str));
	return str_replace(array('\\\\\"', '\\\\"', '\\\"', '\\"'), '\"', json_encode($str));
}


# try catch wrapper for merged javascript
function wpraiser_try_catch_wrap($js, $href=null) {
	$loc = ''; if(!empty($href)) { $loc = '[ Merged: '. $href . ' ] '; }
	return 'try{'.$js.'}catch(e){console.error("An error has occurred. '.$loc.'[ "+e.stack+" ]");}';
}


# wrap html tag in our function for low priority processing inplace
function wpraiser_wrap_script_inline($tag, $method=null) {
	
	# must be a valid type
	if(!is_object($tag) && !is_array($tag)) {
		return $tag;
	}
	
	# skip application/ld+json
	if(isset($tag->type) && $tag->type == 'application/ld+json') {
		return $tag;
	}

	# scripts with src
	if(isset($tag->src)) {
		
		# check for line breaks, skip if found and not empty code inside
		if(stripos(trim($tag->innertext), PHP_EOL) !== false) {
			return $tag;
		}
		
		# get all extra attributes into $rem
		$rem = '';
		foreach($tag->getAllAttributes() as $k=>$v){
			$k = trim($k); $v = trim($v);
			if($k != 'async' && $k != 'defer' && $k != 'src' && $k != 'type') {
				$rem.= "b.setAttribute('$k','$v');";
			}
		}
		
		# wrapper mode
		if(isset($method) && $method == 'hide') {
			# hide scripts
			$st = 'if(wpruag()){'; $en = '}';
		} else {
			# delay scripts
			$st = 'if(wpruag()){window.addEventListener("load",function(){var c=setTimeout(b,5E3),d=["mouseover","keydown","touchmove","touchstart"];d.forEach(function(a){window.addEventListener(a,e,{passive:!0})});function e(){b();clearTimeout(c);d.forEach(function(a){window.removeEventListener(a,e,{passive:!0})})}function b(){'; 
			$en = '};});}';
		}
		
		
				
		# rewrite scripts without document.write, for async scripts
		if(isset($tag->async)) {
			$tag->outertext = "<script data-cfasync='false'>".$st."(function(a){var b=a.createElement('script'),c=a.scripts[0];b.src='".trim($tag->src)."';".$rem."c.parentNode.insertBefore(b,c);}(document));".$en."</script>";
			return $tag;
		} 
		
		# rewrite scripts without document.write, for defer scripts
		if (isset($tag->defer)) {
			$tag->outertext = "<script data-cfasync='false'>".$st."(function(a){var b=a.createElement('script'),c=a.scripts[0];b.src='".trim($tag->src)."';b.async=false;".$rem."c.parentNode.insertBefore(b,c);}(document));".$en."</script>";
			return $tag;				
		}
		
		# fallback to document.write (outerHTML won't work)
		# delay method is also not supported
		$tag->outertext = '<script data-cfasync="false">if(wpruag()){document.write('.wpraiser_escape_url_js($tag->outertext).');}</script>';
		return $tag;
		
	}

	# fallback
	return $tag;
}


# add our function in the header
function wpraiser_add_header_function($html) {
	
	# create function
	$lst = array('x11.*fox\/54', 'oid\s4.*xus.*ome\/62', 'oobot', 'ighth', 'tmetr', 'eadles', 'ingdo', 'PTST');
	$raiserfunction = '<script data-cfasync="false">function wpruag(){var e=navigator.userAgent;if(e.match(/'.implode('|', $lst).'/i))return!1;if(e.match(/x11.*ome\/86\.0/i)){var r=screen.width;if("number"==typeof r&&1367==r)return!1}return!0}</script>';
	
	# remove duplicates
	if(stripos($html, $raiserfunction) !== false) { 
		$html = str_ireplace($raiserfunction, '', $html); 
	}
	
	# add function 
	$html = str_replace('<!-- h_header_function -->', $raiserfunction, $html);
	return $html;
}


# Disable the emoji's on the frontend
function wpraiser_disable_emojis() {
	global $wpraiser_settings;
	if(isset($wpraiser_settings['html']['disable']) && $wpraiser_settings['html']['disable'] == false) {
		if(isset($wpraiser_settings['html']['remove_emoji']) && $wpraiser_settings['html']['remove_emoji'] == true) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );	
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		}
	}
}

# disable wordpress heartbeat
function wpraiser_stop_heartbeat() {
	global $wpraiser_settings;
	if(isset($wpraiser_settings['network']['heartbeat_off']) && $wpraiser_settings['network']['heartbeat_off'] == true) {
		wp_deregister_script('heartbeat');
	}
}


# block outgoing api requests or other urls by domain name
function wpraiser_block_remote_requests($false, $args, $url) {
	global $wpraiser_settings;
	if(isset($wpraiser_settings['network']['block_req']) && !empty($wpraiser_settings['network']['block_req'])) {
		$arr = wpraiser_string_toarray($wpraiser_settings['network']['block_req']);
		if(is_array($arr) && count($arr) > 0) {
			$purl = parse_url($url);
			if(isset($purl['host']) && isset($purl['path'])) {
				foreach($arr as $c) {
					if (stripos($purl['host'].$purl['path'], $c) !== false) { return true; } 
				}
			}
		}
	}

	# default
	return false;
}


# stop slow ajax requests for bots
function wpraiser_ajax_optimizer() {
	if(isset($_SERVER['HTTP_USER_AGENT']) && (defined('DOING_AJAX') && DOING_AJAX) || (function_exists('is_ajax') && is_ajax()) || (function_exists('wp_doing_ajax') && wp_doing_ajax())){
		if (preg_match('/'.implode('|', array('x11.*fox\/54', 'oid\s4.*xus.*ome\/62', 'x11.*ome\/86\.0\.4', 'oobot', 'ighth', 'tmetr', 'eadles', 'ingdo', 'PTST')).'/i', $_SERVER['HTTP_USER_AGENT'])){ echo '0'; exit(); }
	}
}


# get user country geolocation
function wpraiser_get_geolocation() {
	
	# prefer cloudflare
	if(isset($_SERVER['HTTP_CF_IPCOUNTRY']) && strlen($_SERVER['HTTP_CF_IPCOUNTRY']) == 2) { return $_SERVER["HTTP_CF_IPCOUNTRY"]; }
	
	# check if maxmind is installed on the server
	if(isset($_SERVER['GEOIP_COUNTRY_CODE']) && strlen($_SERVER['GEOIP_COUNTRY_CODE']) == 2) { return $_SERVER['GEOIP_COUNTRY_CODE']; }
	if(isset($_SERVER['MM_COUNTRY_CODE']) && strlen($_SERVER['MM_COUNTRY_CODE']) == 2) { return $_SERVER['MM_COUNTRY_CODE']; }
	
	# alternative header on some providers
	if(isset($_SERVER['HTTP_X_COUNTRY_CODE']) && strlen($_SERVER['HTTP_X_COUNTRY_CODE']) == 2) { return $_SERVER['HTTP_X_COUNTRY_CODE']; }
	
	# use api or database (under development)
	
	# return default as empty
	return '';
	
}


# fixes "Does not use passive listeners to improve scrolling performance" on jquery
# should no longer be need on jQuery 4 and it can break stuff, if active listeners are actually required
# https://github.com/jquery/jquery/issues/2871
function wpaiser_passive_listeners_fix($src, $js) {
	global $wpraiser_settings;
	if(isset($wpraiser_settings['js']['passive_listeners']) && $wpraiser_settings['js']['passive_listeners'] == true) {
		if(stripos($src, '/jquery.js') !== false || stripos($src, '/jquery.min.js') !== false) {
			$js.= PHP_EOL . 'jQuery.event.special.touchstart={ setup:function(_,ns,handle){this.addEventListener("touchstart",handle,{passive:true})}};';
		}
	}
	return $js;
}


# compare srcset with width attribute, and return the closest image size url
function wpraiser_get_closest_image_size_from_srcset($str, $int) {

	$sets = array();
	$arr = explode(',', $str);
	foreach ($arr as $k=>$v) {
		$v = trim($v);
		$w = trim(substr($v, strrpos($v, ' ') + 1), 'w');
		$u = trim(substr($v, 0, strrpos($v, ' ') + 1));
		if(is_numeric($w)) {
			$sets[$w] = array('img' => $u, 'width' => $w); 
		}
	}
	
	# order
	$dif = array();
	foreach ($sets as $k=>$v) { $dif[$k] = abs($k - $int); }
	asort($dif);
	$closest = key($dif);
	
	# return url
	if(isset($sets[$closest]['img'])) {
		return $sets[$closest]['img'];
	}
	
	# fail
	return $closest;
}