<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMathPro\Admin
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace RankMathPro\Admin;

use RankMathPro\Admin\Bulk_Actions;
use RankMathPro\Admin\Post_Filters;
use RankMathPro\Admin\Quick_Edit;
use RankMathPro\Admin\Trends_Tool;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 *
 * @codeCoverageIgnore
 */
class Admin {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function __construct() {
		$this->action( 'init', 'init_components' );
		$this->filter( 'rank_math/license/activate_url', 'add_query_arg' );
		add_filter( 'rank_math/analytics/classic/pro_notice', '__return_empty_string' );
	}

	/**
	 * Initialize the required components.
	 */
	public function init_components() {
		$components = [
			'bulk_actions'            => 'RankMathPro\\Admin\\Bulk_Actions',
			'post_filters'            => 'RankMathPro\\Admin\\Post_Filters',
			'media_filters'           => 'RankMathPro\\Admin\\Media_Filters',
			'quick_edit'              => 'RankMathPro\\Admin\\Quick_Edit',
			'trends_tool'             => 'RankMathPro\\Admin\\Trends_Tool',
			'setup_wizard'            => 'RankMathPro\\Admin\\Setup_Wizard',
			'redirection'             => 'RankMathPro\\Admin\\Redirection',
			'links'                   => 'RankMathPro\\Admin\\Links',
			'misc'                    => 'RankMathPro\\Admin\\Misc',
			'csv_import'              => 'RankMathPro\\Admin\\CSV_Import_Export\\CSV_Import_Export',
			'csv_import_redirections' => 'RankMathPro\\Admin\\CSV_Import_Export_Redirections\\CSV_Import_Export_Redirections',
		];

		if ( Helper::is_amp_active() ) {
			$components['amp'] = 'RankMathPro\\Admin\\Amp';
		}

		$components = apply_filters( 'rank_math/admin/pro_components', $components );
		foreach ( $components as $name => $component ) {
			$this->components[ $name ] = new $component();
		}
	}

	/**
	 * Load setup wizard.
	 */
	private function load_setup_wizard() {
		if ( filter_input( INPUT_GET, 'page' ) === 'rank-math-wizard' || filter_input( INPUT_POST, 'action' ) === 'rank_math_save_wizard' ) {
			new Setup_Wizard();
		}
	}

	/**
	 * Add query arg to activate url.
	 *
	 * @param string $url Activate URL.
	 */
	public function add_query_arg( $url ) {
		return Security::add_query_arg_raw( [ 'pro' => 1 ], $url );
	}
}
