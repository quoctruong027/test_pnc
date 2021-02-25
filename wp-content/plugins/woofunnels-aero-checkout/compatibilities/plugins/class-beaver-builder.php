<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_Beaver_builder {

	public function __construct() {
		add_filter( 'fl_builder_post_types', function ( $post_types ) {
			array_push( $post_types, WFACP_Common::get_post_type_slug() );

			return $post_types;
		}, 999 );

	}

	public function is_enable() {
		return (class_exists('FLBuilderLoader'));
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Beaver_builder(), 'Beaver_builder' );
