<?php
/**
 * WC_PRL_Admin_Ajax class
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
 * Admin AJAX meta-box handlers.
 *
 * @class    WC_PRL_Admin_Ajax
 * @version  1.4.6
 */
class WC_PRL_Admin_Ajax {

	/**
	 * Hook in.
	 */
	public static function init() {

		/*
		 * Notices.
		 */

		// Dismiss notices.
		add_action( 'wp_ajax_wc_prl_dismiss_notice', array( __CLASS__ , 'dismiss_notice' ) );

		// Ajax handler for performing loopback tests.
		add_action( 'wp_ajax_wc_prl_loopback_test', array( __CLASS__, 'ajax_loopback_test' ) );

		/*
		 * Deployments.
		 */
		add_action( 'wp_ajax_wc_prl_delete_deployment', array( __CLASS__ , 'handle_delete' ) );
		add_action( 'wp_ajax_wc_prl_toggle_deployment', array( __CLASS__ , 'toggle_deployment' ) );
		add_action( 'wp_ajax_wc_prl_add_deployment', array( __CLASS__ , 'add_deployment' ) );
		add_action( 'wp_ajax_woocommerce_prl_regenerate_deployment', array( __CLASS__ , 'regenerate_deployment_products' ) );

		/*
		 * Engines.
		 */
		add_action( 'wp_ajax_woocommerce_prl_json_search_engines', array( __CLASS__ , 'search_engines' ) );
		add_action( 'wp_ajax_woocommerce_prl_regenerate_engine', array( __CLASS__ , 'regenerate_engine_products' ) );

		/*
		 * Attributes.
		 */
		add_action( 'wp_ajax_woocommerce_prl_json_get_attribute_terms', array( __CLASS__ , 'get_attribute_terms' ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Deployments.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Handles adding deployments via Ajax.
	 *
	 * @return void
	 */
	public static function add_deployment() {

		check_ajax_referer( 'wc_prl_add_deployment', 'security' );

		// Init containers.
		$errors      = array();

		// Parse POST data.
		$index       = isset( $_POST[ 'index' ] ) ? absint( $_POST[ 'index' ] ) : -1;
		$form_index  = isset( $_POST[ 'form_index' ] ) ? absint( $_POST[ 'form_index' ] ) : -1;
		$filter_type = isset( $_POST[ 'filter_type' ] ) ? explode( ',', $_POST[ 'filter_type' ] ) : array();

		if ( -1 === $index || -1 === $form_index ) {
			$errors[] = __( 'Failed to save deployment. Please refresh your browser and try again.', 'woocommerce-product-recommendations' );
		}

		ob_start();

		if ( empty( $errors ) ) {

			$options                  = array();
			$options[ 'form_index' ]  = $form_index;
			$options[ 'filter_type' ] = $filter_type;
			$options[ 'engine_type' ] = reset( $filter_type );

			WC_PRL()->deployments->get_admin_metaboxes_content( $index, $options, true );
		}

		$markup = ob_get_clean();

		wp_send_json( array(
			'markup' => $markup,
			'errors' => $errors
		) );
	}

	/**
	 * Handles toggling deployments via Ajax.
	 *
	 * @return void
	 */
	public static function toggle_deployment() {

		check_ajax_referer( 'wc_prl_toggle_deployment', 'security' );

		// Get POST data.
		$deployment_id = absint( $_POST[ 'deployment_id' ] );
		$active        = 'on' === $_POST[ 'value' ] ? 'off' : 'on'; // Revert input value.

		// Init containers.
		$errors   = array();
		$rules    = array();

		$deployment = new WC_PRL_Deployment_Data( $deployment_id );

		if ( ! $deployment ) {
			$errors[] = __( 'Deployment not found.', 'woocommerce-product-recommendations' );
		}

		// If no errors, proceed to change and save.
		if ( empty( $errors ) ) {
			$deployment->set_active( $active );
			$deployment->save();
		}

		wp_send_json( array(
			'status' => $active,
			'errors' => $errors
		) );
	}

	/**
	 * Handles deployment deletion via Ajax.
	 *
	 * @return void
	 */
	public static function handle_delete() {

		check_ajax_referer( 'wc_prl_delete_deployment', 'security' );

		// Init containers.
		$errors = array();

		if ( isset( $_POST[ 'deployment_id' ] ) ) {

			$id_to_delete = absint( $_POST[ 'deployment_id' ] );
			WC_PRL()->db->deployment->delete( $id_to_delete );

		} else {
			$errors[] = __( 'Deployment not found.', 'woocommerce-product-recommendations' );
		}

		wp_send_json( array(
			'errors' => $errors
		) );
	}

	public static function regenerate_deployment_products() {
		check_ajax_referer( 'wc_prl_regenerate_deployment', 'security' );

		$deployment_id = absint( $_POST[ 'engine_id' ] );
		$errors        = array();

		if ( 0 !== $deployment_id ) {
			// Delete caches.
			WC_PRL()->db->deployment->clear_caches( array( $deployment_id ) );
		} else {
			$errors[] = __( 'Deployment not found. Please refresh the page and try again.', 'woocommerce-product-recommendations' );
		}

		wp_send_json( array(
			'errors' => $errors
		) );
	}

	/*
	|--------------------------------------------------------------------------
	| Engines.
	|--------------------------------------------------------------------------
	*/

	public static function regenerate_engine_products() {
		check_ajax_referer( 'wc_prl_regenerate_engine', 'security' );

		$engine_id   = absint( $_POST[ 'engine_id' ] );
		$deployments = WC_PRL()->db->deployment->query( array( 'return' => 'ids', 'engine_id' => $engine_id ) );
		$errors      = array();

		if ( 0 !== $engine_id && ! empty( $deployments ) ) {
			$deployments = array_map( 'absint', $deployments );
			// Delete caches.
			WC_PRL()->db->deployment->clear_caches( $deployments );
		} else {
			$errors[] = __( 'No deployments found for regeneration.', 'woocommerce-product-recommendations' );
		}

		wp_send_json( array(
			'errors' => $errors
		) );
	}

	/**
	 * Search for product variations and return json.
	 *
	 * @see WC_AJAX::json_search_engines()
	 */
	public static function search_engines() {
		check_ajax_referer( 'wc_prl_search_engine', 'security' );

		self::json_search_engines( '' );
	}

	/**
	 * Search for engines and echo json.
	 *
	 * @param string $term (default: '')
	 * @param array  $type
	 */
	public static function json_search_engines( $term = '', $type = array() ) {

		$term = wc_clean( empty( $term ) ? wp_unslash( $_GET[ 'term' ] ) : $term );

		if ( empty( $term ) ) {
			wp_die();
		}

		if ( ! empty( $_GET[ 'limit' ] ) ) {
			$limit = absint( $_GET[ 'limit' ] );
		} else {
			$limit = absint( apply_filters( 'woocommerce_json_search_limit', 30 ) );
		}

		if ( ! empty( $_GET[ 'filter_type' ] ) ) {
			$type = explode( ',', $_GET[ 'filter_type' ] );
			$type = array_map( 'wc_clean', $type );
		}

		$data_store = WC_Data_Store::load( 'prl_engine' );
		$ids        = $data_store->search_engines( $term, $type, $limit );

		if ( ! empty( $_GET['include'] ) ) {
			$ids = array_intersect( $ids, (array) $_GET['include'] );
		}

		if ( ! empty( $_GET[ 'exclude' ] ) ) {
			$ids = array_diff( $ids, (array) $_GET[ 'exclude' ] );
		}

		$engine_objects = array_map( 'wc_prl_get_engine', $ids );
		$engines        = array();

		foreach ( $engine_objects as $engine_object ) {

			if ( ! $engine_object || ! ( $engine_object instanceof WC_PRL_Engine ) ) {
				continue;
			}

			$title          = $engine_object->get_name() ? $engine_object->get_name() : __( '(no title)', 'woocommerce-product-recommendations' );
			$formatted_name = $title;

			// Add to results.
			$engines[ $engine_object->get_id() ] = array( 'text' => rawurldecode( $formatted_name ), 'type' => $engine_object->get_type() );
		}

		wp_send_json( apply_filters( 'woocommerce_prl_json_search_found_engines', $engines ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Terms.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get terms for a given attribute slug.
	 *
	 * @since  1.3.0
	 *
	 * @return void
	 */
	public static function get_attribute_terms() {
		check_ajax_referer( 'wc_prl_attributes_form', 'security' );

		$taxonomy = sanitize_text_field( wp_unslash( $_GET[ 'taxonomy' ] ) );

		if ( empty( $taxonomy ) ) {
			wp_die();
		}

		$terms = get_terms( array(
			'taxonomy' => 'pa_' . $taxonomy,
			'hide_empty' => false,
		) );

		$results = false;
		if ( ! empty( $terms ) ) {
			$results = array();
			foreach ( $terms as $term ) {
				$results[] = array( 'id' => $term->slug, 'text' => $term->name );
			}
		}

		wp_send_json( $results );
	}

	/*
	|--------------------------------------------------------------------------
	| Notices.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Dismisses notices.
	 *
	 * @return void
	 */
	public static function dismiss_notice() {

		$failure = array(
			'result' => 'failure'
		);

		if ( ! check_ajax_referer( 'wc_prl_dismiss_notice_nonce', 'security', false ) ) {
			wp_send_json( $failure );
		}

		if ( empty( $_POST[ 'notice' ] ) ) {
			wp_send_json( $failure );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json( $failure );
		}

		$dismissed = WC_PRL_Admin_Notices::dismiss_notice( wc_clean( $_POST[ 'notice' ] ) );

		if ( ! $dismissed ) {
			wp_send_json( $failure );
		}

		$response = array(
			'result' => 'success'
		);

		wp_send_json( $response );
	}

	/**
	 * Checks if loopback requests work.
	 *
	 * @since  1.3.0
	 *
	 * @return void
	 */
	public static function ajax_loopback_test() {

		$failure = array(
			'result' => 'failure',
			'reason' => ''
		);

		if ( ! check_ajax_referer( 'wc_prl_loopback_notice_nonce', 'security', false ) ) {
			$failure[ 'reason' ] = 'nonce';
			wp_send_json( $failure );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			$failure[ 'reason' ] = 'user_role';
			wp_send_json( $failure );
		}

		if ( ! class_exists( 'WP_Site_Health' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-site-health.php' );
		}

		$site_health = method_exists( 'WP_Site_Health', 'get_instance' ) ? WP_Site_Health::get_instance() : new WP_Site_Health();
		$result      = $site_health->can_perform_loopback();
		$passes_test = 'good' === $result->status;

		WC_PRL_Admin_Notices::set_notice_option( 'loopback', 'last_tested', gmdate( 'U' ) );
		WC_PRL_Admin_Notices::set_notice_option( 'loopback', 'last_result', $passes_test ? 'pass' : 'fail' );

		if ( ! $passes_test ) {
			$failure[ 'reason' ]  = 'status';
			$failure[ 'status' ]  = $result->status;
			$failure[ 'message' ] = $result->message;
			wp_send_json( $failure );
		}

		WC_PRL_Admin_Notices::remove_maintenance_notice( 'loopback' );

		$response = array(
			'result' => 'success'
		);

		wp_send_json( $response );
	}
}

WC_PRL_Admin_Ajax::init();
