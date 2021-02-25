<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WFOCU_Compatibility_With_Divi
 */
class WFOCU_Compatibility_With_Divi {

	public function __construct() {
		add_filter( 'et_builder_enabled_builder_post_type_options', function ( $options ) {
			$options[ WFOCU_Common::get_offer_post_type_slug() ] = 'on';

			return $options;
		}, 999 );
		add_filter( 'wfocu_should_render_script_jquery', array( $this, 'should_prevent_jq_on_editor' ), 10 );
		add_filter( 'wfocu_container_attrs', array( $this, 'add_id_for_wfocu_container' ) );
	}

	public function is_enable() {
		if ( defined( 'ET_CORE_VERSION' ) ) {
			return true;
		}

		return false;
	}

	public function should_prevent_jq_on_editor( $bool ) {
		if ( isset( $_GET['et_fb'] ) ) {
			return false;
		}

		return $bool;
	}

	/**
	 * @param $attrs
	 *
	 * @return mixed
	 */
	public function add_id_for_wfocu_container( $attrs ) {

		$attrs['id'] = 'page-container';

		return $attrs;
	}


}

WFOCU_Plugin_Compatibilities::register( new WFOCU_Compatibility_With_Divi(), 'divi' );
