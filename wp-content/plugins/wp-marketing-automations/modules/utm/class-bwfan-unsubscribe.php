<?php

class BWFAN_unsubscribe {

	private static $ins = null;

	public function __construct() {

		/** Shortcodes for unsubscribe */
		add_shortcode( 'bwfan_unsubscribe_button', array( $this, 'bwfan_unsubscribe_button' ) );
		add_shortcode( 'bwfan_subscriber_recipient', array( $this, 'bwfan_subscriber_recipient' ) );
		add_shortcode( 'bwfan_subscriber_name', array( $this, 'bwfan_subscriber_name' ) );

		add_action( 'bwfan_db_1_0_tables_created', array( $this, 'create_unsubscribe_sample_page' ) );

		add_action( 'wp_head', array( $this, 'unsubscribe_page_non_crawlable' ) );

		/** Ajax Calls */
		add_action( 'wp_ajax_bwfan_select_unsubscribe_page', array( $this, 'bwfan_select_unsubscribe_page' ) );
		add_action( 'wp_ajax_bwfan_unsubscribe_user', array( $this, 'bwfan_unsubscribe_user' ) );
		add_action( 'wp_ajax_nopriv_bwfan_unsubscribe_user', array( $this, 'bwfan_unsubscribe_user' ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function bwfan_unsubscribe_button( $attrs ) {
		$atts = shortcode_atts( array(
			'label' => 'Unsubscribe',
		), $attrs );

		if ( ! isset( $_GET['automation_id'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			return '';
		}

		ob_start();
		echo '<a id="bwfan_unsubscribe" class="button-primary button" href="#">' . esc_html__( $atts['label'] ) . '</a>';
		echo '<input type="hidden" id="bwfan_automation_id" value="' . esc_attr__( sanitize_text_field( $_GET['automation_id'] ) ) . '">'; // WordPress.CSRF.NonceVerification.NoNonceVerification
		echo '<input type="hidden" id="bwfan_unsubscribe_nonce" value="' . esc_attr( wp_create_nonce( 'bwfan-unsubscribe-nonce' ) ) . '" name="bwfan_unsubscribe_nonce">';

		return ob_get_clean();
	}

	public function bwfan_subscriber_recipient( $attrs ) {
		$atts = shortcode_atts( array(
			'fallback' => 'John@example.com',
		), $attrs );

		if ( isset( $_GET['subscriber_recipient'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			$atts['fallback'] = sanitize_text_field( $_GET['subscriber_recipient'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		return '<span id="bwfan_unsubscribe_recipient">' . $atts['fallback'] . '</span>';
	}

	/**
	 * Adding noindex, nofollow meta tag for unsubscribe page
	 */
	public function unsubscribe_page_non_crawlable() {
		$global_settings     = get_option( 'bwfan_global_settings' );
		$unsubscribe_page_id = isset( $global_settings['bwfan_unsubscribe_page'] ) ? $global_settings['bwfan_unsubscribe_page'] : 0;
		if ( ! empty( $unsubscribe_page_id ) && is_page( $unsubscribe_page_id ) ) {
			echo "\n<meta name='robots' content='noindex,nofollow' />\n";
		}
	}

	public function bwfan_subscriber_name( $attrs ) {
		$atts = shortcode_atts( array(
			'fallback' => 'John',
		), $attrs );

		if ( isset( $_GET['subscriber_name'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			$atts['fallback'] = sanitize_text_field( $_GET['subscriber_name'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		return '<span id="bwfan_unsubscribe_name">' . $atts['fallback'] . '</span>';
	}

	public function create_unsubscribe_sample_page() {
		$content = "Hi [bwfan_subscriber_name],\nSorry, to know you do not want to receive any promotional stuffs.\nUnsubscribe [bwfan_subscriber_recipient] from website promotional stuffs.\n[bwfan_unsubscribe_button]";

		$new_page = array(
			'post_title'   => 'Unsubscribe',
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);

		$post_id                                   = wp_insert_post( $new_page );
		$global_settings                           = get_option( 'bwfan_global_settings', array() );
		$global_settings['bwfan_unsubscribe_page'] = $post_id;
		update_option( 'bwfan_global_settings', $global_settings );
	}

	public function bwfan_select_unsubscribe_page() {
		global $wpdb;
		$term    = isset( $_POST['search_term']['term'] ) ? sanitize_text_field( $_POST['search_term']['term'] ) : ''; // WordPress.CSRF.NonceVerification.NoNonceVerification
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_title FROM {$wpdb->prefix}posts WHERE post_title LIKE %s AND post_type = %s", '%' . $term . '%', 'page' ) );

		$response = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$response[] = array(
					'id'   => $result->ID,
					'text' => $result->post_title,
				);
			}
		}

		wp_send_json( array(
			'results' => $response,
		) );
	}

	public function bwfan_unsubscribe_user() {
		global $wpdb;
		$nonce = ( isset( $_POST['_nonce'] ) ) ? sanitize_text_field( $_POST['_nonce'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification
		if ( ! wp_verify_nonce( $nonce, 'bwfan-unsubscribe-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['recipient'] ) || ! isset( $_POST['automation_id'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			wp_send_json( array(
				'success' => 0,
				'message' => __( 'Security check failed', 'wp-marketing-automations' ),
			) );
		}

		$global_settings = BWFAN_Common::get_global_settings();
		$recipient       = sanitize_text_field( $_POST['recipient'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		$automation_id   = (int) sanitize_text_field( $_POST['automation_id'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification

		if ( empty( $recipient ) || empty( $automation_id ) || ! is_numeric( $automation_id ) ) {
			wp_send_json( array(
				'success' => 0,
				'message' => $global_settings['bwfan_unsubscribe_data_error'],
			) );
		}

		if ( false !== filter_var( $recipient, FILTER_VALIDATE_EMAIL ) ) {
			$mode = 1;
		} elseif ( is_numeric( $recipient ) ) {
			$mode = 2;
		} else {
			wp_send_json( array(
				'success' => 0,
				'message' => $global_settings['bwfan_unsubscribe_data_error'],
			) );
		}

		/** @var  $where
		 *  checking if recipient already added to unsubscribe table
		 */
		$where         = "WHERE `recipient` = '" . sanitize_text_field( $recipient ) . "' and `mode` = '" . $mode . "'";
		$unsubscribers = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}bwfan_message_unsubscribe $where ORDER BY ID DESC LIMIT 0,1 " );//phpcs:ignore WordPress.DB.PreparedSQL

		if ( $unsubscribers > 0 ) {
			wp_send_json( array(
				'success' => 0,
				'message' => __( 'You have already unsubscribed', 'wp-marketing-automations' ),
			) );
		}

		$insert_data = array(
			'recipient'     => $recipient,
			'c_date'        => current_time( 'mysql' ),
			'mode'          => $mode,
			'automation_id' => $automation_id,
		);

		BWFAN_Model_Message_Unsubscribe::insert( $insert_data );

		wp_send_json( array(
			'success' => 1,
			'message' => $global_settings['bwfan_unsubscribe_data_success'],
		) );
	}


}

BWFAN_unsubscribe::get_instance();
