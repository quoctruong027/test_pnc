<?php

# Exit if accessed directly				
if (!defined('ABSPATH')){ exit(); }	

# add 5 minute schedule to cron_schedules
add_filter('cron_schedules','wpraiser_cron_schedules');
function wpraiser_cron_schedules($schedules){
    if(!isset($schedules["5min"])){
        $schedules["5min"] = array( 'interval' => 5*60, 'display' => __('Once every 5 minutes'));
    }
    return $schedules;
}


# delete files older than a specific timestamp
function wpraiser_cron_purge_expired() {
	
	global $wpraiser_settings, $wpraiser_cache_paths;
	
	# cache must be enabled
	if(!isset($wpraiser_settings['cache']['enable_page']) || !isset($wpraiser_settings['cache']['lifespan']) || !isset($wpraiser_settings['cache']['lifespan_unit'])) {
		return false; # cache not enabled
	}
	
	# must have
	if(!isset($wpraiser_cache_paths['cache_dir_html']) || !defined('WP_CONTENT_DIR')) { 
		return 'Requested purge path is not allowed!';
	}
	
	# must be on the allowed path
	if(empty($wpraiser_cache_paths['cache_dir_html']) || stripos($wpraiser_cache_paths['cache_dir_html'], WP_CONTENT_DIR) === false || stripos($wpraiser_cache_paths['cache_dir_html'], '/cache/') === false || stripos($wpraiser_cache_paths['cache_dir_html'], '/wpraiser') === false) {
		return 'Requested purge path is not allowed!';
	}
	
	# purge recursively
	clearstatcache();
	if(is_dir($wpraiser_cache_paths['cache_dir_html'])) {
		try {
			$i = new DirectoryIterator($wpraiser_cache_paths['cache_dir_html']);
			foreach($i as $f){
				if( $f->isFile() && ( null !== $f->getRealPath() && null !== $f->getMTime() && file_exists($f->getRealPath()) )){ 
					if(intval($f->getMTime()) + intval($wpraiser_settings['cache']['lifespan']) * intval($wpraiser_settings['cache']['lifespan_unit']) < time()){
						@unlink($f->getRealPath());
					}
				}
			}
		} catch (Exception $e) {
			return get_class($e) . ": " . $e->getMessage();
		}
		
		return 'Expired cache files from WP Raiser have been purged!';
	}
	
	return 'Cache directory does not exist!';
}

