<?php

/**
 * Class YIKES_Custom_Product_Tabs_Pro_Support.
 */
class YIKES_Custom_Product_Tabs_Pro_Support {

	/**
	 * Define hooks.
	 */
	public function __construct() {

		// Add our support page content.
		add_action( 'yikes-woo-support-page-pro', array( $this, 'render_support_page' ), 20 );

		// Enqueue scripts & styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );

		// AJAX call to send support request.
		add_action( 'wp_ajax_cptpro_send_support_request', array( $this, 'cptpro_send_support_request' ) );
	}

	/**
	 * Enqueue our scripts and styes
	 *
	 * @param string $page The slug of the page we're currently on.
	 */
	public function enqueue_scripts( $page ) {

		if ( $page === 'custom-product-tabs-pro_page_' . YIKES_Custom_Product_Tabs_Support_Page ) {

			wp_enqueue_script( 'icheck', YIKES_Custom_Product_Tabs_Pro_URI . 'js/icheck.min.js', array( 'jquery' ), YIKES_Custom_Product_Tabs_Pro_Version, true );
			wp_enqueue_style( 'icheck-flat-blue-styles', YIKES_Custom_Product_Tabs_Pro_URI . 'css/flat/blue.css', array(), YIKES_Custom_Product_Tabs_Pro_Version );
			wp_enqueue_style( 'cptpro-settings-styles', YIKES_Custom_Product_Tabs_Pro_URI . 'css/settings.min.css', array(), YIKES_Custom_Product_Tabs_Pro_Version );
			wp_enqueue_script( 'cptpro-shared-scripts', YIKES_Custom_Product_Tabs_Pro_URI . 'js/shared.min.js', array( 'jquery' ), YIKES_Custom_Product_Tabs_Pro_Version, true );
			wp_enqueue_script( 'cptpro-settings-scripts', YIKES_Custom_Product_Tabs_Pro_URI . 'js/support.min.js', array( 'icheck' ), YIKES_Custom_Product_Tabs_Pro_Version, true );
			wp_localize_script(
				'cptpro-settings-scripts',
				'support_data',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'cptpro_send_support_request' ),
					'action'   => 'cptpro_send_support_request',
				)
			);
		}
	}

	/**
	 * Ping our yikesplugins API to create a Fresh Desk support ticket [AJAX].
	 */
	public function cptpro_send_support_request() {

		// Verify the nonce.
		if ( ! check_ajax_referer( 'cptpro_send_support_request', 'nonce', false ) ) {
			wp_send_json_error();
		}

		$name     = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email    = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
		$topic    = isset( $_POST['topic'] ) ? sanitize_text_field( wp_unslash( $_POST['topic'] ) ) : '';
		$issue    = isset( $_POST['issue'] ) ? sanitize_text_field( wp_unslash( $_POST['issue'] ) ) : '';
		$priority = isset( $_POST['priority'] ) ? sanitize_text_field( wp_unslash( $_POST['priority'] ) ) : 1;
		$license  = isset( $_POST['license'] ) ? sanitize_text_field( wp_unslash( $_POST['license'] ) ) : '';

		$ticket_array = array(
			'action'           => 'yikes-support-request',
			'license_key'      => base64_encode( $license ),
			'plugin_name'      => 'Custom Product Tabs Pro',
			'edd_item_id'      => YIKES_Custom_Product_Tabs_Pro_License_Item_ID,
			'user_email'       => $email,
			'site_url'         => esc_url( home_url() ),
			'support_name'     => $name,
			'support_topic'    => $topic,
			'support_priority' => $priority,
			'support_content'  => $issue,
			'api_version'      => '2',
		);

		// Call the custom API.
		$response = wp_remote_post(
			'https://yikesplugins.com',
			array(
				'timeout'   => 30,
				'sslverify' => false,
				'body'      => $ticket_array,
			)
		);

		// Catch the error.
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->getMessage() );
		}

		// Retrieve our body.
		$response_body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( isset( $response_body->success ) && $response_body->success === true ) {
			wp_send_json_success(
				array(
					'redirect_url' => add_query_arg( array( 'success' => 'success', 'page' => YIKES_Custom_Product_Tabs_Support_Page ), admin_url( 'admin.php' ) ),
				)
			);
		} else {
			wp_send_json_error( array( 'message' => $response_body->message ) );
		}
	}

	/**
	 * Include the support page.
	 */
	public function render_support_page() {
		include YIKES_Custom_Product_Tabs_Pro_Path . 'partials/page-support.php';
	}
}

new YIKES_Custom_Product_Tabs_Pro_Support();
