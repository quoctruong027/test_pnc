<?php
defined( 'ABSPATH' ) || exit;

class XLWCTY_remove_builder {
	private static $ins = null;
	private $editor = '';
	private $post_type = 'xlwcty_thankyou';


	public function __construct() {
		add_filter( 'xlwcty_redirect_preview_link', array( $this, 'change_preview_link_for_builder' ) );
	}

	public static function get_instance() {
		if ( self::$ins == null ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function change_preview_link_for_builder( $link ) {
		if ( isset( $_REQUEST['fl_builder'] ) ) {
			$link = add_query_arg( array( 'fl_builder' => '' ), $link );
		}

		return $link;
	}

}

XLWCTY_remove_builder::get_instance();
