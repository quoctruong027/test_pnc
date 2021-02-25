<?php
/**
 * WC_PRL_Admin_Deploy class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_PRL_Admin_Deploy Class.
 *
 * @version  1.3.4
 */
class WC_PRL_Admin_Deploy {

	/**
	 * Page home URL.
	 * @const PAGE_URL
	 */
	const PAGE_URL = 'admin.php?page=prl_locations&section=deploy';

	/**
	 * Save the settings.
	 */
	public static function save() {

		check_admin_referer( 'woocommerce-prl-deploy' );

		if ( empty( $_POST ) ) {
			return false;
		}

		$post_prl_deploy = $_POST[ 'prl_deploy' ];
		if ( ! $post_prl_deploy[ 'hook' ] ) {
			WC_PRL_Admin_Notices::add_notice( __( 'Please choose a Location.', 'woocommerce-product-recommendations' ), 'error', true );
			wp_redirect( add_query_arg( 'engine', absint( $post_prl_deploy[ 'engine_id' ] ), admin_url( self::PAGE_URL ) ) );
			exit();
		}

		$location = WC_PRL()->locations->get_location_by_hook( $post_prl_deploy[ 'hook' ] );
		if ( ! $location ) {
			WC_PRL_Admin_Notices::add_notice( __( 'Ops! Something went wrong while trying to save a Location for this Engine.', 'woocommerce-product-recommendations' ), 'error', true );
			wp_redirect( add_query_arg( 'engine', absint( $post_prl_deploy[ 'engine_id' ] ), admin_url( self::PAGE_URL ) ) );
			exit();
		}

		$engine = ! empty( $post_prl_deploy[ 'engine_id' ] ) ? new WC_PRL_Engine( absint( $post_prl_deploy[ 'engine_id' ] ) ) : false;
		if ( ! $engine ) {
			WC_PRL_Admin_Notices::add_notice( __( 'Engine not found.', 'woocommerce-product-recommendations' ), 'error', true );
			wp_redirect( admin_url( 'admin.php?page=prl_locations' ) );
			exit();
		}

		$deployment = false;
		if ( isset( $post_prl_deploy[ 'id' ] ) ) {
			$deployment = new WC_PRL_Deployment( absint( $post_prl_deploy[ 'id' ] ) );
		}

		$args = array(
			'engine_id'       => absint( $post_prl_deploy[ 'engine_id' ] ),
			'engine_type'     => $post_prl_deploy[ 'engine_type' ],
			'title'           => strip_tags( wp_unslash( $post_prl_deploy[ 'title' ] ) ),
			'description'     => wp_kses_post( wp_unslash( $post_prl_deploy[ 'description' ] ) ),
			'location_id'     => $location->get_location_id(),
			'hook'            => wc_clean( $post_prl_deploy[ 'hook' ] ),
			'conditions_data' => isset( $post_prl_deploy[ 'conditions' ] ) && is_array( $post_prl_deploy[ 'conditions' ] ) ? array_values( $post_prl_deploy[ 'conditions' ] ) : array(),
			'display_order'   => 1
		);

		if ( isset( $post_prl_deploy[ 'active' ] ) ) {
			$args[ 'active' ] = 'on';
		} elseif( $deployment ) {
			$args[ 'active' ] = 'off';
		}

		if ( absint( $post_prl_deploy[ 'columns' ] ) ) {
			$args[ 'columns' ] = absint( $post_prl_deploy[ 'columns' ] );
		}

		if ( absint( $post_prl_deploy[ 'rows' ] ) ) {
			$args[ 'rows' ] = absint( $post_prl_deploy[ 'rows' ] );
		}

		// Add to database.
		try {

			if ( $deployment ) {
				WC_PRL()->db->deployment->update( $deployment->data, $args );
				WC_PRL_Admin_Notices::add_notice( __( 'Deployment edited successfully.', 'woocommerce-product-recommendations' ), 'success', true );
			} else {
				WC_PRL()->db->deployment->add( $args );
				WC_PRL_Admin_Notices::add_notice( sprintf( __( 'Engine deployed successfully. Your recommendations will be generated shortly after the first time they are requested, and will be refreshed every %s.', 'woocommerce-product-recommendations' ), human_time_diff( current_time( 'timestamp' ) + $engine->refresh_interval_in_seconds ) ), 'success', true );
			}

			if ( $location->is_cacheable() && false === wc_prl_render_using_ajax( 'edit' ) && 'cached' === WC_PRL_Notices::get_page_cache_test_result() ) {

				$is_engine_dynamic         = $engine->get_dynamic_filters_data();
				$is_deployment_conditional = $deployment ? $deployment->get_conditions_data() : $args[ 'conditions_data' ];

				if ( $is_engine_dynamic || $is_deployment_conditional ) {

					if ( $deployment ) {
						$notice = sprintf( __( 'This deployment generates dynamic/personalized recommendations that some visitors <a href="%1$s" target="_blank">may not be able to see correctly</a>, as the page that contains them appears to be served from a cache. As a workaround, you may enable <strong>Deployments rendering > Use AJAX</strong> under <strong>WooCommerce > Settings > Recommendations</strong> &ndash; but please be aware that doing so will have an impact on server utilization and user experience.', 'woocommerce-product-recommendations' ), WC_PRL()->get_resource_url( 'page-caching' ) );
					} else {
						$notice = sprintf( __( 'The engine you just deployed generates dynamic/personalized recommendations that some visitors <a href="%1$s" target="_blank">may not be able to see correctly</a>, as the page that contains them appears to be served from a cache. As a workaround, you may enable <strong>Deployments rendering > Use AJAX</strong> under <strong>WooCommerce > Settings > Recommendations</strong> &ndash; but please be aware that doing so will have an impact on server utilization and user experience.', 'woocommerce-product-recommendations' ), WC_PRL()->get_resource_url( 'page-caching' ) );
					}

					WC_PRL_Admin_Notices::add_notice( $notice, 'warning', true );
				}
			}

		} catch ( Exception $e ) {
			WC_PRL_Admin_Notices::add_notice( $e->getMessage(), 'error', true );
		}

		$redirect = $deployment ? add_query_arg( 'deployment', absint( $post_prl_deploy[ 'id' ] ), self::PAGE_URL ) : 'admin.php?page=prl_locations';
		wp_redirect( admin_url( $redirect ) );
		exit;
	}

	/**
	 * Deployments page.
	 *
	 * Handles the display of the pages list and the deployments accordion.
	 */
	public static function output() {

		do_action( 'woocommerce_prl_deploy_start' );

		$deployment    = false;
		$deployment_id = isset( $_GET[ 'deployment' ] ) ? absint( $_GET[ 'deployment' ] ) : false;
		$is_quick      = isset( $_GET[ 'quick' ] ) && 1 === absint( $_GET[ 'quick' ] ) ? true : false;

		if ( $deployment_id ) {
			$deployment = new WC_PRL_Deployment( $deployment_id );
			$engine_id  = $deployment->get_engine_id();
		} else {
			$engine_id = isset( $_GET[ 'engine' ] ) ? absint( $_GET[ 'engine' ] ) : false;
		}

		$engine = new WC_PRL_Engine( $engine_id );
		if ( ! $engine->get_id() ) {
			WC_PRL_Admin_Notices::add_notice( __( 'Engine not found.', 'woocommerce-product-recommendations' ), 'error', true );
			wp_redirect( admin_url( 'admin.php?page=prl_locations' ) );
			exit;
		}
		$locations = WC_PRL()->locations->get_hooks_for_deployment( $engine->get_type() );

		include dirname( __FILE__ ) . '/views/html-admin-deploy.php';
	}
}
