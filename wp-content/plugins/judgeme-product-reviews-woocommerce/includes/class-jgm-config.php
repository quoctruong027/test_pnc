<?php
/**
 * Judge.me Config class
 *
 * @author   Judge.me
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class JGM_Config {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'judgeme_options_page' ) );
	}

	public function judgeme_options_page() {
		// add top level menu page
		$icon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA3XAAAN1wFCKJt4AAAAB3RJTUUH4QwUCDcSZurPigAAAiFJREFUWAljNJgx4z/DIAZMg9htYKeNOpDSGBr0IchCqQ/R9fOxs6MLYfA///zJQGzOpLoDDyYkYDgIXcBi7lyGn3/+oAtj5Q/6KB51INZ4I0Fw0Icg1TOJ7/Ll8PBREBBgmOzpCeeTw6C6A598+gR3BycrK5xNLmPQR/GoA8mNWpi+0RCEhQS59GgIkhtyMH2jIQgLCXLp0RAkN+Rg+gZ9CBLdWGAEeulkaioDKxOqn5I3bWI49/w5zMMoNC8bGwofxPn97x/DLyKb+yD1qLaBRHAAUCfn7bdvGLL28vIYYjABBwUFGBNOv/76legOE0gT0Q4EKT6LJaSidXUZjCUlQdIowFRKiiFKRwdFDMTBFdoYCqECREcxSP3uu3cZvFVVUcxiBkb5TB8fhhVXrzIcevgQLAcK1XBtbQaQHDrYfe8euhBePkkOBDng0suXDHri4iiGghwCCkkQxgeuvHrFcBjqCXzqkOUwvYgsi8YGpcPmQ4cYvv3+jSZDmPsVqKfhwAGS0h/IVJIcCNJw5907hrLdu0lyJMhDID13378HGUESINmBINOPPn7MkLhxI8ONN28IWnb99WuGBKDaY0A95ABGSgYwmRgZGZwUFRnclZUZDCUkGIS5uMBR+A5YHJ178YJhJzBT7b9/n+Hff2JHYjC9QJED0Y0DFeIgp/wBFsbUAiTlYkKWgmoJagOy0iC1HYHPvFEH4gsdYuQGfQgCAP9ljCIESyYOAAAAAElFTkSuQmCC';
		add_menu_page(
			'Judge.me Reviews',
			'Judge.me',
			'manage_options',
			'judgeme',
			array( $this, 'judgeme_export_reviews_page_html' ),
			$icon
		);
	}

	public function judgeme_export_reviews_page_html() {
		$domain     = constant( 'JGM_SHOP_DOMAIN' );
		$token      = get_option( 'judgeme_shop_token' );
		$hmac       = hash_hmac( 'sha256', "no_iframe=1&platform=woocommerce&shop_domain={$domain}", $token, false );
		$url        = JGM_CORE_HOST . "index?no_iframe=1&shop_domain={$domain}&platform=woocommerce&hmac={$hmac}";
		$import_url = JGM_CORE_HOST . "import?no_iframe=1&shop_domain={$domain}&platform=woocommerce&hmac={$hmac}";
		$setting_url = JGM_CORE_HOST . "settings?no_iframe=1&shop_domain={$domain}&platform=woocommerce&hmac={$hmac}";
		include JGM_PLUGIN_PATH . 'templates/admin/style.php';
		include JGM_PLUGIN_PATH . 'templates/admin/export-reviews-template.php';
	}

}
