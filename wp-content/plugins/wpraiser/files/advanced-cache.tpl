<?php

# Auto Generated file by WP RAISER (do not edit)

# get cache settings and exclusions
$wprs_settings = json_decode('###WPRS###', true);

# minimum requirements
if(!defined('ABSPATH')) { return false; }
if(!defined('WP_CONTENT_DIR')) { return false; }
if( defined('DONOTCACHEPAGE') && DONOTCACHEPAGE ){ return false; }
if(function_exists('http_response_code') && http_response_code() !== 200){ return false; }
if (!isset($_SERVER['REQUEST_METHOD'])) { return false; }
if (!isset($_SERVER['REQUEST_URI'])) { return false; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'HEAD') { return false; }

# aux functions

# open a multiline string, order, filter duplicates and return as array
function advc_wpraiser_string_toarray($value){
	$arr = explode(PHP_EOL, $value);
	$a = array_map('trim', $arr);
	$b = array_filter($a);
	$c = array_unique($b);
	sort($c);
	return $c;
}

# detect mobile, from wp-includes/vars.php
# https://developer.wordpress.org/reference/functions/wp_is_mobile/
function wp_is_mobile_alt() {
	if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
		$is_mobile = false;
	} elseif ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Mobile' ) !== false // Many mobile devices (all iPhone, iPad, etc.)
		|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Android' ) !== false
		|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Silk/' ) !== false
		|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Kindle' ) !== false
		|| strpos( $_SERVER['HTTP_USER_AGENT'], 'BlackBerry' ) !== false
		|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera Mini' ) !== false
		|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera Mobi' ) !== false ) {
			$is_mobile = true;
	} else {
		$is_mobile = false;
	}
	return $is_mobile;
}


# get user country geolocation
function advc_wpraiser_get_geolocation() {
	
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



# cache location
if(isset($wpraiser_settings['uploads']) && $wpraiser_settings['uploads'] == true) {
	$chdir = 'uploads' . DIRECTORY_SEPARATOR . 'cache';
} else {
	$chdir = 'cache';
}

# if cache path available
$wprs_path = rtrim(WP_CONTENT_DIR, '/\\') . DIRECTORY_SEPARATOR . $chdir. DIRECTORY_SEPARATOR .'wpraiser'. DIRECTORY_SEPARATOR .'html';
if(is_dir($wprs_path)) {
	
	# some defaults
	$msg = array();
	$request_scheme = '';
	$is_mobile = '';
	$vary_geo = '';
	$vary_cookie = '';
		
	# check our cookie exclusions
	if(isset($_COOKIE) && is_array($_COOKIE) && isset($wprs_settings['cookies'])) {
		$arr = advc_wpraiser_string_toarray($wprs_settings['cookies']);
		if(is_array($arr)) {
			foreach ($arr as $a) {
				if(substr($a, -1) == '*' || substr($a, 1) == '*') {
					foreach ($_COOKIE as $k=>$v) {
						if(stripos($k, trim($a, '*')) !== false) { return false; }
					}
				} else {
					if(isset($_COOKIE[$a])) { return false; }
				}
			}
		}	
	}
	
	# filter some stuff
	$ruri = str_replace('//', '/', str_replace('..', '', preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', strtok($_SERVER['REQUEST_URI'], '?'))));
	
	# check our URI Path exclusions, exact match without query strings
	if(!empty($ruri) && isset($wprs_settings['skip_url'])) {
		$arr = advc_wpraiser_string_toarray($wprs_settings['skip_url']);
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
	
	# check and serve the standard cache file for our allowed query strings
	$parseurl = parse_url($_SERVER['REQUEST_URI']);
	if(isset($parseurl["query"]) && !empty($parseurl["query"])) {
		
		# parse query string to array
		$query_string_arr = array(); 
		parse_str($parseurl["query"], $query_string_arr);

		# unless specifically allowed
		if(isset($wprs_settings['ignore_qs']) && !empty($wprs_settings['ignore_qs'])) {
			foreach ( advc_wpraiser_string_toarray($wprs_settings['ignore_qs'] ) as $qs) {
				if(isset($query_string_arr[$qs])) { unset($query_string_arr[$qs]); }
			}
		}
				
		# return false if there are any query strings left
		if(count($query_string_arr) > 0) {
			return false;
		}	
	}

	# windows support, reverse forward slashes if needed
	if(stripos($ruri, DIRECTORY_SEPARATOR) !== false) {
		$ruri = str_replace('/', DIRECTORY_SEPARATOR, $ruri);
	}
	
	# detect https
	if((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
		$request_scheme = '-https';
	}
	
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
	
		
	# detect mobile users
	if( isset($wprs_settings['enable_mobile']) && $wprs_settings['enable_mobile'] == true && ( (function_exists('wp_is_mobile') && wp_is_mobile() == true) || wp_is_mobile_alt() == true) ) { 
		$is_mobile = 'mobile_'; 
		$msg[] = 'Cache Source: Mobile'; 
	} else {
		$msg[] = 'Cache Source: Not Mobile';
	}
	
	# vary cache on geolocation
	if( isset($wprs_settings['enable_geolocation']) && $wprs_settings['enable_geolocation'] == true && isset($wprs_settings['vary_geo']) && !empty($wprs_settings['vary_geo'])) {
		$cc = advc_wpraiser_get_geolocation();
		if(!empty($cc)) {
			$arr = advc_wpraiser_string_toarray($wprs_settings['vary_geo']);
			if(is_array($arr) && in_array($cc, $arr)) {
				$vary_geo = $cc.'_';
				$msg[] = 'Vary Country: '.$cc;
			}	
		}
	}
	
	
	# vary cache on cookie name, with different values
	if(isset($wprs_settings['enable_vary_cookie']) && $wprs_settings['enable_vary_cookie'] == true) {
		if(isset($wprs_settings['vary_cookie']) && !empty($wprs_settings['vary_cookie'])) {
			if(isset($_COOKIE[$wprs_settings['vary_cookie']]) && strlen($_COOKIE[$wprs_settings['vary_cookie']]) > 0) {
				$hash_cookie = md5($_COOKIE[$wprs_settings['vary_cookie']]);
				$vary_cookie = $hash_cookie.'_';
				$msg[] = 'Vary Cookie: '.$hash_cookie;
			} else {
				$msg[] = 'Vary Cookie: cookie not found';
			}
		}
	}
		
	# get cache file name
	$wprcf = $wprs_path . DIRECTORY_SEPARATOR . $host . $ruri . $vary_cookie. $vary_geo. $is_mobile . 'index'.$request_scheme.'.html';
	
	# check if exists and hasn't expired
	if(file_exists($wprcf) && filemtime($wprcf) + intval($wprs_settings['lifespan']) * intval($wprs_settings['lifespan_unit']) < time()){ 
		@unlink($wprcf); 
		return false;
	}	
		
	# show cache file if still valid
	if(file_exists($wprcf)) {
		
		# get if-modified request headers
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			$http_if_modified_since = (isset($headers['If-Modified-Since'])) ? $headers['If-Modified-Since'] : '';
			$http_accept = (isset($headers['Accept'])) ? $headers['Accept'] : '';
			$http_accept_encoding = (isset( $headers['Accept-Encoding'])) ? $headers['Accept-Encoding'] : '';
		} else {
			$http_if_modified_since = (isset($_SERVER[ 'HTTP_IF_MODIFIED_SINCE'])) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : '';
			$http_accept = (isset($_SERVER['HTTP_ACCEPT'])) ? $_SERVER['HTTP_ACCEPT'] : '';
			$http_accept_encoding = (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
		}
			
		# check modified since with cached file and return 304 if no difference
		if ( $http_if_modified_since && ( strtotime( $http_if_modified_since ) >= filemtime( $wprcf ) ) ) {
			header( $_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304 );
			exit;
		}
		
		# add geolocation header
		if(!empty($vary_geo)) {
			header('X-Country-Code: ' . substr($vary_geo, 0, 2));
		}
		
		# vary headers for geolocation
		if( isset($wprs_settings['enable_geolocation']) && $wprs_settings['enable_geolocation'] == true ) {
			header('Vary: X-Country-Code');
		}
		
		# return cache file and stop wordpress execution
		header( 'Last-Modified: ' . gmdate("D, d M Y H:i:s", filemtime($wprcf)).' GMT' );
		readfile($wprcf);
		echo PHP_EOL . "<!-- Info: Cache file served with PHP! -->";
		foreach ($msg as $m) { echo PHP_EOL . "<!-- Info: $m -->"; }
		exit();
	}	
}

# default, no cache
return false;
