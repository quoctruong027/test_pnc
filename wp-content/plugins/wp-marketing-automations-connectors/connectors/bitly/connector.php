<?php

class BWFCO_Bitly extends BWF_CO {

	private static $ins = null;
	private $option_key = 'bwfan_bitly_service_data';
	private $api_url = 'https://api-ssl.bitly.com/v3/shorten';

	private function __construct() {
		$this->dir              = __DIR__;
		$this->is_setting       = true;
		$this->autonami_int_slug = 'bitly_service';
		$this->connector_url    = WFCO_AUTONAMI_CONNECTORS_PLUGIN_URL . '/connectors/bitly';
		$this->nice_name        = __( 'Bitly', 'autonami-automations-connectors' );

		add_action( 'admin_menu', [ $this, 'process_url' ] );
		add_action( 'admin_menu', [ $this, 'disconnect' ] );
		add_shortcode( 'bwfan_bitly_shorten', [ $this, 'bwfan_shorten' ] );

		add_filter( 'wfco_do_not_print_connector_button', [ $this, 'dont_print_native_card_buttons' ], 10, 2 );
		add_action( 'wfco_print_connector_button_placeholder', [ $this, 'print_own_button_in_card_ui' ] );
		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
	}

	public static function get_instance() {
		if ( is_null( self::$ins ) ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function dont_print_native_card_buttons( $status, $slug ) {
		if ( $this->get_slug() === $slug ) {
			$status = true;
		}

		return $status;
	}

	public function print_own_button_in_card_ui( $instance ) {
		include __DIR__ . '/views/settings.php';
	}

	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_bitly'] = array(
			'name'            => 'Bitly',
			'desc'            => __( 'Shorten the URLs to get increased character limit for SMS.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFAN_Service_Bitly',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}

	public function get_url() {
		$url = 'https://secure-auth.buildwoofunnels.com/bitly/';
		$url = add_query_arg( [
			'redirect_uri' => urlencode( admin_url( 'admin.php?page=autonami&tab=connector' ) ),
		], $url ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions

		return $url;
	}

	public function process_url() {
		//phpcs:disable WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
		if ( isset( $_REQUEST['access_token'] ) && isset( $_REQUEST['login'] ) && isset( $_REQUEST['bitly'] ) ) {
			$data = [
				'access_token' => sanitize_text_field( $_REQUEST['access_token'] ),
				'login'        => sanitize_text_field( $_REQUEST['login'] ),
			];
			update_option( $this->option_key, $data );
		}
		//phpcs:enable WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
	}

	public function disconnect() {
		if ( isset( $_REQUEST['bitly_disconnect'] ) ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
			update_option( $this->option_key, [] );
			wp_safe_redirect( admin_url( 'admin.php?page=autonami&tab=connector' ) );
			exit;
		}
	}

	public function get_disconnect_url() {
		return admin_url( 'admin.php?page=autonami&tab=connector&bitly_disconnect=true' );
	}

	public function bwfan_shorten( $attr, $content = '' ) {
		$content = do_shortcode( $content );
		$content = $this->generate_shorten_url( $content );

		return $content;
	}

	public function generate_shorten_url( $link ) {
		if ( '' === $link ) {
			return '';
		}
		$link = trim( $link );
		if ( false !== filter_var( $link, FILTER_VALIDATE_URL ) ) {
			$data = $this->get_saved_data();
			if ( empty( $data ) ) {
				return $link;
			}

			$source_link = add_query_arg( [
				'access_token' => $data['access_token'],
				'longUrl'      => urlencode( $link ), //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
			], $this->api_url );
			$response    = wp_remote_get( $source_link );
			if ( ! is_wp_error( $response ) ) {
				$body = $response['body'];
				$json = json_decode( $body, true );
				if ( is_array( $json ) && '200' == $json['status_code'] && 'OK' === $json['status_txt'] ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput,WordPress.PHP.StrictComparisons
					$link = $json['data']['url'];
				}
			}
		}

		return $link;
	}

	public function get_saved_data() {
		return get_option( 'bwfan_bitly_service_data', [] );
	}

}

WFCO_Load_Connectors::register( 'BWFCO_Bitly' );
