<?php
/**
 * WC_Admin_Locations class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Admin_Locations Class.
 */
class WC_PRL_Admin_Locations {

	/**
	 * Page home URL.
	 * @const PAGE_URL
	 */
	const PAGE_URL = 'admin.php?page=prl_locations';

	/**
	 * Save the settings.
	 */
	public static function save() {
		//...
	}

	/**
	 * Deployments page.
	 *
	 * Handles the display of the pages list and the deployments accordion.
	 */
	public static function output() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		do_action( 'woocommerce_prl_locations_start' );

		self::handle_delete();

		$locations = WC_PRL()->locations->get_locations();
		$table     = new WC_PRL_Deployments_List_Table();
		$table->prepare_items();

		include dirname( __FILE__ ) . '/views/html-admin-locations.php';
	}

	private static function handle_delete() {

		if ( isset( $_GET[ 'delete' ] ) ) {

			$id_to_delete = absint( $_GET[ 'delete' ] );

			WC_PRL()->db->deployment->delete( $id_to_delete );

			WC_PRL_Admin_Notices::add_notice( __( 'Deployment deleted.', 'woocommerce-product-recommendations' ), 'success', true );

			wp_redirect( admin_url( self::PAGE_URL ) );
			exit();
		}
	}
}
