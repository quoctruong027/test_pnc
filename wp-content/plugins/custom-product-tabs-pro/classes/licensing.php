<?php

/**
 * Class YIKES_Custom_Product_Tabs_Pro_Licensing.
 */
class YIKES_Custom_Product_Tabs_Pro_Licensing {

	/**
	 * Define hooks.
	 */
	public function __construct() {

		// Activation.
		add_action( 'wp_ajax_cptpro_activate_license', array( $this, 'activate_license' ) );

		// Check.
		add_action( 'wp_ajax_cptpro_check_license', array( $this, 'check_license' ) );

		// Deactivate.
		add_action( 'wp_ajax_cptpro_deactivate_license', array( $this, 'deactivate_license' ) );

		// Auto Updater.
		add_action( 'admin_init', array( $this, 'update_available_question_mark' ) );
	}

	/**
	 * Handle license activation [AJAX]
	 */
	public function activate_license() {

		// Verify the nonce.
		if ( ! check_ajax_referer( 'cptpro_activate_license', 'nonce', false ) ) {
			wp_send_json_error();
		}

		$license = isset( $_POST['license'] ) ? filter_var( wp_unslash( $_POST['license'] ), FILTER_SANITIZE_STRING ) : '';

		// Data to send in our API request.
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => trim( $license ),
			'item_id'    => YIKES_Custom_Product_Tabs_Pro_License_Item_ID,
			'url'        => esc_url( home_url() ),
		);

		// Call the custom API.
		$response = wp_remote_post(
			esc_url( YIKES_Custom_Product_Tabs_Pro_License_URL ),
			array(
				'timeout'   => 30,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( __( 'API Call Failed.', 'custom-product-tabs-pro' ) );
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $license_data['success'] ) || isset( $license_data['success'] ) && $license_data['success'] === false ) {
			wp_send_json_error( __( 'The license was invalid. Please wait a few minutes and try again. If the issue persists, please contact us at support@yikesinc.com', 'custom-product-tabs-pro' ) );
		}

		// Get our option.
		$settings = get_option( 'cptpro_settings', array() );

		// Add the license details to our option.
		$settings['licensing'] = array(
			'license'      => $license,
			'license_data' => $license_data,
		);

		// Save our option.
		update_option( 'cptpro_settings', $settings );

		if ( $license_data['expires'] !== 'lifetime' ) {
			$license_data['expires'] = date_format( new DateTime( $license_data['expires'] ), 'F jS, Y' );
		}

		wp_send_json_success( $license_data );
	}

	/**
	 * Check a license [AJAX].
	 */
	public function check_license() {

		// Verify the nonce.
		if ( ! check_ajax_referer( 'cptpro_check_license', 'nonce', false ) ) {
			wp_send_json_error();
		}

		$license_details = get_transient( 'cptpro-license-details' );

		if ( false !== $license_details ) {
			wp_send_json_success( $license_details );
		}

		$license = isset( $_POST['license'] ) ? filter_var( wp_unslash( $_POST['license'] ), FILTER_SANITIZE_STRING ) : '';

		// Data to send in our API request.
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => trim( $license ),
			'item_id'    => YIKES_Custom_Product_Tabs_Pro_License_Item_ID,
			'url'        => esc_url( home_url() ),
		);

		// Call the custom API.
		$response = wp_remote_post(
			esc_url( YIKES_Custom_Product_Tabs_Pro_License_URL ),
			array(
				'timeout'   => 30,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( __( 'API Call Failed.', 'custom-product-tabs-pro' ) );
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $license_data['success'] ) || isset( $license_data['success'] ) && $license_data['success'] === false ) {
			wp_send_json_error( __( 'The license was invalid. Please wait a few minutes and try again. If the issue persists, please contact us at support@yikesinc.com', 'custom-product-tabs-pro' ) );
		}

		if ( $license_data['expires'] !== 'lifetime' ) {
			$license_data['expires'] = date_format( new DateTime( $license_data['expires'] ), 'F jS, Y' );
		}

		set_transient( 'cptpro-license-details', $license_data, HOUR_IN_SECONDS );

		wp_send_json_success( $license_data );
	}

	/**
	 * Deactivate a license [AJAX].
	 */
	public function deactivate_license() {

		// Verify the nonce.
		if ( ! check_ajax_referer( 'cptpro_deactivate_license', 'nonce', false ) ) {
		 	wp_send_json_error();
		}

		$license = isset( $_POST['license'] ) ? filter_var( wp_unslash( $_POST['license'] ), FILTER_SANITIZE_STRING ) : '';

		// Data to send in our API request.
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => trim( $license ),
			'item_id'    => YIKES_Custom_Product_Tabs_Pro_License_Item_ID,
			'url'        => esc_url( home_url() ),
		);

		// Call the custom API.
		$response = wp_remote_post(
			esc_url( YIKES_Custom_Product_Tabs_Pro_License_URL ),
			array(
				'timeout'   => 30,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( __( 'API Call Failed.', 'custom-product-tabs-pro' ) );
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $license_data['success'] ) || isset( $license_data['success'] ) && $license_data['success'] === false ) {
			wp_send_json_error( __( 'Something went wrong. Please wait a few minutes and try again. If the issue persists, please contact us at support@yikesinc.com', 'custom-product-tabs-pro' ) );
		}

		// Get our option.
		$settings = get_option( 'cptpro_settings', array() );

		// Remove our licensing option.
		if ( isset( $settings['licensing'] ) ) {
			unset( $settings['licensing'] );
		}

		// Save our option.
		update_option( 'cptpro_settings', $settings );

		// Delete our licensing details transient.
		delete_transient( 'cptpro-license-details' );

		wp_send_json_success( $license_data );
	}

	/**
	 * Plugin update checks.
	 */
	public function update_available_question_mark() {

		// Get our option.
		$settings = get_option( 'cptpro_settings', array() );

		// Make sure we have a license.
		if ( ! isset( $settings['licensing'] ) || isset( $settings['licensing'] ) && ! isset( $settings['licensing']['license'] ) ) {
			return;
		}

		$license = $settings['licensing']['license'];

		// Run the updater.
		$edd_updater = new EDD_SL_Plugin_Updater_CPT_Pro(
			esc_url( YIKES_Custom_Product_Tabs_Pro_License_URL ),
			YIKES_Custom_Product_Tabs_Pro_License_Path,
			array(
				'version' => YIKES_Custom_Product_Tabs_Pro_Version,
				'license' => trim( $license ),
				'item_id' => YIKES_Custom_Product_Tabs_Pro_License_Item_ID,
				'author'  => 'YIKES, Inc.',
				'url'     => esc_url( home_url() ),
			)
		);
	}
}


new YIKES_Custom_Product_Tabs_Pro_Licensing();
