<?php
/**
 * WC_PRL_Post_Data class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modifies the custom's post type list table.
 *
 * @class    WC_PRL_Post_Data
 * @version  1.0.0
 */
class WC_PRL_Post_Data {

	/**
	 * Hook in methods.
	 */
	public static function init() {

		// Status transitions.
		add_action( 'pre_delete_post', array( __CLASS__, 'pre_delete_post' ), 10, 3 );
		add_action( 'woocommerce_prl_before_delete_engine', array( __CLASS__, 'prl_engine_delete_post' ), 10 );
	}

	/**
	 * Prevents delete engine if there are deployments.
	 *
	 * @param  mixed  $delete Whether to proceed to deletion. Default: null
	 * @param  WP_Post  $post
	 * @param  bool  $force_delete
	 * @return bool|null
	 */
	public static function pre_delete_post( $delete, $post, $force_delete ) {
		if ( ! current_user_can( 'delete_posts' ) || ! $post ) {
			return;
		}

		switch ( $post->post_type ) {
			case 'prl_engine':
				// Check if there are any active deployments.
				$current_deployments = WC_PRL()->db->deployment->query( array( 'return' => 'ids', 'engine_id' => $post->ID ) );

				if ( ! empty( $current_deployments ) ) {

					$delete = false;

					WC_PRL_Admin_Notices::add_notice( sprintf( __( 'Engine #%d has active deployments and cannot be deleted.', 'woocommerce-product-recommendations' ), $post->ID ), 'error', true );
					wp_safe_redirect( 'edit.php?post_status=trash&post_type=prl_engine' );
					exit;
				}
				break;
		}

		return $delete;
	}

	/**
	 * Prevents delete engine if there are deployments when using custom data store.
	 *
	 * @throws exception
	 *
	 * @param  int  $id
	 * @return void
	 */
	public static function prl_engine_delete_post( $id ) {
		if ( ! current_user_can( 'delete_posts' ) || ! $id ) {
			return;
		}

		// Check if there are any active deployments.
		$current_deployments = WC_PRL()->db->deployment->query( array( 'return' => 'ids', 'engine_id' => $id ) );

		if ( ! empty( $current_deployments ) ) {
			throw new Exception( sprintf( __( 'Engine #%d has active deployments and cannot be deleted.', 'woocommerce-product-recommendations' ), $id ) );
		}
	}
}

WC_PRL_Post_Data::init();
