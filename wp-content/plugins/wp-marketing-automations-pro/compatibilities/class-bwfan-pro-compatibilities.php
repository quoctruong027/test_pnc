<?php

/**
 * Class BWFAN_Pro_Compatibilities
 * Loads all the compatibilities files we have in Autonami against plugins
 */
class BWFAN_Pro_Compatibilities {

	public static function load_merge_tags_compatibilities() {

		foreach ( glob( plugin_dir_path( BWFAN_PRO_PLUGIN_FILE ) . 'compatibilities/merge_tags/*.php' ) as $_field_filename ) {
			if ( strpos( $_field_filename, 'index.php' ) !== false ) {
				continue;
			}
			require_once( $_field_filename );
		}
	}
}

add_action( 'bwfan_merge_tags_loaded', array( 'BWFAN_Pro_Compatibilities', 'load_merge_tags_compatibilities' ), 999 );
