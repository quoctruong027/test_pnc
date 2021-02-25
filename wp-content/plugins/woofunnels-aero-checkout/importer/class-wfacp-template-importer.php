<?php

class WFACP_Template_Importer {

	private static $instance = null;
	private static $importer = [];

	private function __construct() {
	}

	public static function get_error_message( $code ) {
		$messages = [
			'license-or-domain-invalid' => __( 'License or domain invalid' ),
			'unauthorized-access'       => sprintf( __( 'Please check if you have valid license key. <a href=%s target="_blank">Go to Licenses</a>', 'woofunnels-aero' ), esc_url( admin_url( 'admin.php?page=woofunnels' ) ) ),
		];
		if ( isset( $messages[ $code ] ) ) {
			return $messages[ $code ];
		}

		return $code;
	}

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function register( $builder, $importer ) {
		if ( ! isset( self::$importer[ $builder ] ) && $importer instanceof WFACP_Import_Export ) {
			self::$importer[ $builder ] = $importer;
		}
	}

	/**
	 * @param $aero_id
	 * @param $builder
	 * @param $slug
	 *
	 * @return bool
	 */
	public function import( $aero_id, $builder, $slug, $is_multi = 'no' ) {

		if ( isset( self::$importer[ $builder ] ) && self::$importer[ $builder ] instanceof WFACP_Import_Export ) {

			$importer = self::$importer[ $builder ];
			$status   = $importer->import( $aero_id, $slug, $is_multi );

			return $status;
		}

		if ( $builder == 'elementor' ) {
			if ( ( ! version_compare( get_bloginfo( 'version' ), '5.0', '>=' ) && ( version_compare( ELEMENTOR_VERSION, '2.8.0', '>=' ) ) ) ) {
				$message = sprintf( esc_html__( 'Elementor requires WordPress version %s+. please update the wordpress version to import the template.', 'woofunnels-aero-checkout' ), '5.0' );

				return [ 'error' => $message ];
			}
		}


		return false;
	}

	/**
	 * @param $aero_id
	 * @param $builder
	 * @param $slug
	 *
	 * @return array||null
	 */
	public function export( $aero_id, $builder, $slug ) {
		if ( isset( self::$importer[ $builder ] ) && self::$importer[ $builder ] instanceof WFACP_Import_Export ) {
			$importer    = self::$importer[ $builder ];
			$export_data = $importer->export( $aero_id, $builder, $slug );

			return $export_data;
		}

		return null;
	}

	public function get_importer( $builder ) {
		if ( isset( self::$importer[ $builder ] ) && self::$importer[ $builder ] instanceof WFACP_Import_Export ) {
			return self::$importer[ $builder ];
		}

		return null;
	}

	public function get_domain() {
		global $sitepress;
		$domain = site_url();

		if ( isset( $sitepress ) && ! is_null( $sitepress ) ) {
			$default_language = $sitepress->get_default_language();
			$domain           = $sitepress->convert_url( $sitepress->get_wp_api()->get_home_url(), $default_language );
		}
		$domain = str_replace( [ 'https://', 'http://' ], '', $domain );
		$domain = trim( $domain, '/' );

		return $domain;
	}

	/**
	 * Import template remotely.
	 * @return mixed
	 */
	public function get_remote_template( $template_id, $builder ) {
		if ( empty( $template_id ) || empty( $builder ) ) {
			return '';
		}
		$funnel_step = 'wc_checkout';


		$template_file_path = $builder . '/' . $funnel_step . '/' . $template_id;
		$defined_wffn       = defined( 'WFFN_TEMPLATE_UPLOAD_DIR' );
		$file_exist         = ( $defined_wffn ) ? file_exists( WFFN_TEMPLATE_UPLOAD_DIR . $template_file_path . '.json' ) : false;

		if ( $defined_wffn && $file_exist ) {
			$content = file_get_contents( WFFN_TEMPLATE_UPLOAD_DIR . $template_file_path . '.json' );
			unlink( WFFN_TEMPLATE_UPLOAD_DIR . $template_file_path . '.json' );

			return [ 'success' => true, 'data' => $content ];
		}


		$license     = $this->get_license_key();
		$requestBody = array(
			'step'     => $funnel_step,
			"domain"   => $this->get_domain(),
			"license"  => $license,
			"template" => $template_id,
			"builder"  => $builder

		);
		if ( empty( $license ) ) {
			return [ 'error' => self::get_error_message( 'unauthorized-access' ) ];
		}

		$requestBody = wp_json_encode( $requestBody );

		$endpoint_url = $this->get_template_api_url();
		$response     = wp_remote_post( $endpoint_url, array(
			"body"    => $requestBody,
			"timeout" => 30,
			'headers' => [
				'content-type' => 'application/json'
			]
		) );

		if ( $response instanceof WP_Error ) {
			return false;
		}

		$response = json_decode( $response['body'], true );
		if ( ! is_array( $response ) ) {
			return [ 'error' => __( 'Server Down', 'woofunnels-aero-checkout' ) ];
		}

		if ( isset( $response['error'] ) ) {
			return [ 'error' => self::get_error_message( $response['error'] ) ];
		}
		$checkout_data = $response[ $funnel_step ];

		if ( empty( $checkout_data ) ) {
			return [ 'error' => __( 'No Template found', 'woofunnels-aero-checkout' ) ];
		}

		return [ 'success' => true, 'data' => $checkout_data ];
	}

	/**
	 * Get license key.
	 * @return mixed
	 */
	public function get_license_key() {
		$licenseKey      = false;
		$woofunnels_data = get_option( 'woofunnels_plugins_info' );
		if ( is_array( $woofunnels_data ) && count( $woofunnels_data ) > 0 ) {

			foreach ( $woofunnels_data as $key => $license ) {
				if ( is_array( $license ) && isset( $license['activated'] ) && $license['activated'] && $key === sha1( WFACP_PLUGIN_BASENAME ) && $license['data_extra']['software_title'] === $this->get_software_title() ) {

					$licenseKey = $license['data_extra']['api_key'];
					break;
				}
			}
		}

		return $licenseKey;
	}

	public function get_software_title() {
		return WFACP_FULL_NAME;
	}

	public function get_template_api_url() {
		return 'http://gettemplates.buildwoofunnels.com/';
	}

	public static function update_import_page_settings( $aero_id, $import_page_settings ) {
		if ( ! empty( $import_page_settings ) ) {
			$page_settings = WFACP_Common::get_page_settings( $aero_id );
			foreach ( $import_page_settings as $key => $setting ) {
				$page_settings[ $key ] = $import_page_settings[ $key ];
			}
			update_post_meta( $aero_id, '_wfacp_page_settings', $page_settings );
		}
	}

}

WFACP_Core::register( 'importer', WFACP_Template_Importer::get_instance() );
