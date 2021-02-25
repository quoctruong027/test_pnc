<?php

# Exit if accessed directly				
if (!defined('ABSPATH')){ exit(); }	

# only for wp-cli
if ( defined( 'WP_CLI' ) && WP_CLI ) {


###################################################
# extend wp-cli to purge cache, usage: wp wpraiser purge
###################################################

class wpraiser_WPCLI {

	# purge files + cache
	public function purge() {
		wpraiser_purge_all();
		wpraiser_purge_others();
		WP_CLI::success('PSE and other caches were purged.');
	}
	}

# add commands
WP_CLI::add_command( 'wpraiser', 'wpraiser_WPCLI' );



###################################################
}