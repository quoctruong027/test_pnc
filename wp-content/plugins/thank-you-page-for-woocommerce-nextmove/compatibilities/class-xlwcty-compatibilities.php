<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class WCCT_Compatibilities
 * Loads all the compatibilities files we have in nextmove against plugins
 */
class XLWCTY_Compatibilities {


	public static function load_all_compatibilities() {
		// load all the WCCT_Compatibilities files automatically
		foreach ( glob( plugin_dir_path( XLWCTY_PLUGIN_FILE ) . '/compatibilities/*.php' ) as $_field_filename ) {
			$file_data = pathinfo( $_field_filename );
			if ( isset( $file_data['basename'] ) && 'index.php' === $file_data['basename'] ) {
				continue;
			}
			require_once( $_field_filename );
		}
	}
}

XLWCTY_Compatibilities::load_all_compatibilities();
