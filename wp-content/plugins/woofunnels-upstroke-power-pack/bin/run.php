<?php

/**
 * Generates an array of feature flags, based on the config used by the client application.
 *
 * @package WooCommerce Admin
 */

/**
 * Get phase for feature flags
 * - development: All features should be enabled in development.
 * - plugin: For the standalone feature plugin, for GitHub and WordPress.org.
 * - core: Stable features for WooCommerce core merge.
 */
$phase = isset( $_SERVER['WFOCU_PACK_MODE'] ) ? $_SERVER['WFOCU_PACK_MODE'] : ''; // WPCS: sanitization ok.

if ( ! in_array( $phase, array( 'site', 'wc' ), true ) ) {
	$phase = 'site'; // Default to plugin when running `npm run build`.
}

/**
 * PLUGIN FILE GENERATION STARTS
 */
$PLUGIN_FILE_NAME = 'woofunnels-upstroke-power-pack.php';

$get_merge_file = file_get_contents( __DIR__ . '/plugin-file.txt' );
$get_phase_file = include_once __DIR__ . '/' . $phase . '/plugin-file.txt';

foreach ( $get_phase_file as $tag => $value ) {
	$get_merge_file = preg_replace( "/$tag/", $value, $get_merge_file );
}

$config_file = fopen( './' . $PLUGIN_FILE_NAME . '', 'w' );
fwrite( $config_file, $get_merge_file );
fclose( $config_file );
/**
 * PLUGIN FILE GENERATION ENDS
 */


/**
 * SUPPORT FILE GENERATION ENDS
 */
$PLUGIN_FILE_NAME = 'class-woofunnels-support-wfocu-power-pack.php';

$get_merge_file = file_get_contents( __DIR__ . '/woofunnel-support.txt' );
$get_phase_file = include_once __DIR__ . '/' . $phase . '/woofunnel-support.txt';

foreach ( $get_phase_file as $tag => $value ) {
	$get_merge_file = preg_replace( "/$tag/", $value, $get_merge_file );
}

$config_file = fopen( './' . $PLUGIN_FILE_NAME . '', 'w' );
fwrite( $config_file, $get_merge_file );
fclose( $config_file );





