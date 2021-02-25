<?php
/**
 * WC_PRL_Admin_Hooks class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_PRL_Admin_Hooks Class.
 */
class WC_PRL_Admin_Hooks {

	/**
	 * Page home URL.
	 * @const PAGE_URL
	 */
	const PAGE_URL = 'admin.php?page=prl_locations&section=hooks';

	/**
	 * Save the settings.
	 */
	public static function save() {
		$deployments = (array) $_POST[ 'deployment' ];

		if ( empty( $deployments ) ) {
			return false;
		}

		// Parse GET.
		$locations = WC_PRL()->locations->get_locations();
		$hook      = isset( $_GET[ 'hook' ] ) ? wc_clean( $_GET[ 'hook' ] ) : false;
		$location  = WC_PRL()->locations->get_location_by_hook( $hook );

		if ( ! $location ) {
			wp_redirect( admin_url( self::PAGE_URL ) );
			exit();
		}

		// Generate URL.
		$url = self::PAGE_URL;
		$url = add_query_arg( 'location', $location->get_location_id(), $url );
		$url = add_query_arg( 'hook', $hook, $url );

		foreach ( $deployments as $data ) {

			$deployment = false;
			if ( isset( $data[ 'id' ] ) && $data[ 'id' ] != 0 ) {
				$deployment = new WC_PRL_Deployment( absint( $data[ 'id' ] ) );
			}

			$args = array(
				'engine_id'       => isset( $data[ 'engine_id' ] ) ? absint( $data[ 'engine_id' ] ) : 0,
				'title'           => isset( $data[ 'title' ] ) ? strip_tags( wp_unslash( $data[ 'title' ] ) ) : '',
				'description'     => wp_kses_post( wp_unslash( $data[ 'description' ] ) ),
				'location_id'     => $location->get_location_id(),
				'hook'            => $hook,
				'conditions_data' => isset( $data[ 'conditions' ] ) && is_array( $data[ 'conditions' ] ) ? array_values( $data[ 'conditions' ] ) : array(),
				'display_order'   => isset( $data[ 'display_order' ] ) ? absint( $data[ 'display_order' ] ) : 1
			);

			if ( isset( $data[ 'active' ] ) ) {
				$args[ 'active' ] = $data[ 'active' ];
			}

			if ( isset( $data[ 'columns' ] ) && $data[ 'columns' ] > 0 ) { // If is not set or 0 let the default.
				$args[ 'columns' ] = absint( $data[ 'columns' ] );
			}

			if ( isset( $data[ 'rows' ] ) && $data[ 'rows' ] > 0 ) { // If is not set or 0 let the default.
				$args[ 'rows' ] = absint( $data[ 'rows' ] );
			}

			// Add to database.
			try {

				if ( $deployment ) {
					WC_PRL()->db->deployment->update( $deployment->data, $args );
				} else {
					WC_PRL()->db->deployment->add( $args );
				}

			} catch ( Exception $e ) {
				WC_PRL_Admin_Notices::add_notice( $e->getMessage(), 'error', true );
			}
		}

		WC_PRL_Admin_Notices::add_notice( __( 'Deployments saved.', 'woocommerce-product-recommendations' ), 'success', true );

		wp_redirect( admin_url( $url ) );
		exit();
	}

	/**
	 * Hooks page.
	 *
	 * Handles the display of the hooks tabs.
	 */
	public static function output() {

		do_action( 'woocommerce_prl_hooks_start' );

		$location = isset( $_GET[ 'location' ] ) ? wc_clean( $_GET[ 'location' ] ) : false;

		if ( ! $location ) {
			wp_redirect( admin_url( remove_query_arg( 'section', self::PAGE_URL ) ) );
			exit();
		}

		$locations          = WC_PRL()->locations->get_locations();
		$location           = WC_PRL()->locations->get_location( $location );
		// Check location object.
		if ( ! $location ) {
			wp_redirect( admin_url( remove_query_arg( 'section', self::PAGE_URL ) ) );
			exit();
		}

		$hooks              = $location->get_hooks();
		$hook_keys          = array_keys( $hooks );
		$selected_hook      = isset( $_GET[ 'hook' ] ) ? wc_clean( $_GET[ 'hook' ] ) : $hook_keys[ 0 ]; // Get the first hook.
		$selected_hook_data = $hooks[ $selected_hook ];

		$base_url           = add_query_arg( 'location', $location->get_location_id(), self::PAGE_URL );

		// Fetch deployments.
		$args               = array(
			'return'      => 'objects',
			'location_id' => $location->get_location_id(),
			'order_by'    => array( 'display_order' => 'ASC' )
		);

		$deployments        = WC_PRL()->db->deployment->query( $args );
		$map                = array();

		foreach ( $deployments as $index => $deployment ) {

			if ( ! isset( $map[ $deployment->get_hook() ] ) ) {
				$map[ $deployment->get_hook() ] = array(
					'deployments' => array(),
					'active'      => 0
				);
			}

			$map[ $deployment->get_hook() ][ 'deployments' ][] = $deployment;
			$map[ $deployment->get_hook() ][ 'active' ] = $deployment->is_active() ? $map[ $deployment->get_hook() ][ 'active' ] + 1 : $map[ $deployment->get_hook() ][ 'active' ];
		}

		// Check GET.
		if ( ! isset( $hooks[ $selected_hook ] ) ) {
			wp_redirect( admin_url( remove_query_arg( 'section', self::PAGE_URL ) ) );
			exit();
		}

		include dirname( __FILE__ ) . '/views/html-admin-hooks.php';
	}
}
