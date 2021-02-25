<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class WFOCU_Remote_Template_Importer
 * @package WFOCU
 * @author XlPlugins
 */
class WFOCU_Remote_Template_Importer {

	private static $instance = null;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function get_error_message( $code ) {
		$messages = [
			'license-or-domain-invalid' => __( 'Sorry, we are unable to import this template. <br/> License or domain invalid' ),
			'unauthorized-access'       => sprintf( __( 'Sorry, we are unable to import this template. <br/> Please check if you have valid license key. <a href=%s target="_blank">Go to Licenses</a>', 'woofunnels-upstroke-one-click-upsell' ), esc_url( admin_url( 'admin.php?page=woofunnels' ) ) ),
			'template-not-exists'       => sprintf( __( 'Sorry, we are unable to import this template. <br/> Please contact <a href=%s target="_blank">Support</a>', 'woofunnels-upstroke-one-click-upsell' ), esc_url( 'https://buildwoofunnels.com/support' ) ),
		];
		if ( isset( $messages[ $code ] ) ) {
			return $messages[ $code ];
		}

		return $code;
	}

	/**
	 * Import template remotely.
	 * @return mixed
	 */
	public function get_remote_template( $template_id, $builder ) {
		if ( empty( $template_id ) || empty( $builder ) ) {
			return '';
		}

		$funnel_step        = 'wc_upsells';
		$template_file_path = $builder . '/' . $funnel_step . '/' . $template_id;
		$defined_wffn       = defined( 'WFFN_TEMPLATE_UPLOAD_DIR' );
		$file_exist         = ( $defined_wffn ) ? file_exists( WFFN_TEMPLATE_UPLOAD_DIR . $template_file_path . '.json' ) : false;

		if ( $defined_wffn && $file_exist ) {
			$content = file_get_contents( WFFN_TEMPLATE_UPLOAD_DIR . $template_file_path . '.json' );
			unlink( WFFN_TEMPLATE_UPLOAD_DIR . $template_file_path . '.json' );

			return WFOCU_Core()->template_loader->get_group( $builder )->handle_remote_import( $content );
		}

		$license = $this->get_license_key();

		$step = 'wc_upsells';

		if ( empty( $license ) && class_exists( 'WFFN_Pro_Core' ) ) {
			$license = WFFN_Pro_Core()->support->get_license_key();
		}
		$requestBody  = array(
			"step"     => $step,
			"domain"   => $this->get_domain(),
			"license"  => $license,
			"template" => $template_id,
			"builder"  => $builder
		);
		$requestBody  = wp_json_encode( $requestBody );
		$endpoint_url = $this->get_template_api_url();
		$response     = wp_remote_post( $endpoint_url, array(
			"body"    => $requestBody,
			"timeout" => 30, //phpcs:ignore
			'headers' => array(
				'content-type' => 'application/json'
			)
		) );

		if ( $response instanceof WP_Error ) {
			return [ 'error' => __( 'Unable to import template', 'woofunnels-flex-funnels' ) ];
		}

		$response = json_decode( $response['body'], true );
		if ( ! is_array( $response ) ) {
			return [ 'error' => __( 'Unable to import template', 'woofunnels-flex-funnels' ) ];
		}
		if ( isset( $response['error'] ) ) {
			return [ 'error' => self::get_error_message( $response['error'] ) ];
		}

		if ( ! isset( $response[ $step ] ) ) {
			return [ 'error' => __( 'No Template found', 'woofunnels-flex-funnels' ) ];
		}

//		if ( 'wc_upsells' === $step ) {
//			if ( $response['funnel'] && ! empty( $response['funnel'] ) ) {
//				foreach ( $response['funnel'][0]['steps'] as $key => $step_response ) {
//					if ( $step_response['import_exists'] === "yes" ) {
//						$directory = $builder . '/' . $step_response['type'] . '/' . $step_response['template'];
//
//						define( 'WFOCU_TEMPLATE_UPLOAD_DIR', WP_CONTENT_DIR . '/uploads/wfocu_templates/' );
//
//						if ( ! is_dir( WFOCU_TEMPLATE_UPLOAD_DIR ) ) {
//							mkdir( WFOCU_TEMPLATE_UPLOAD_DIR );  //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_mkdir
//						}
//
//						if ( ! is_dir( WFOCU_TEMPLATE_UPLOAD_DIR . '/' . $builder ) ) {
//							mkdir( WFOCU_TEMPLATE_UPLOAD_DIR . '/' . $builder ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_mkdir
//						}
//
//						if ( ! is_dir( WFOCU_TEMPLATE_UPLOAD_DIR . '/' . $builder . '/' . $step_response['type'] ) ) {
//							mkdir( WFOCU_TEMPLATE_UPLOAD_DIR . '/' . $builder . '/' . $step_response['type'] ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.directory_mkdir
//						}
//						$template_path = WFOCU_TEMPLATE_UPLOAD_DIR . $directory . '.json';
//						file_put_contents( $template_path, $response['steps'][ $key ] ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_file_put_contents
//					}
//				}
//			}
//
//			return $response[ $step ];
//		}

		return WFOCU_Core()->template_loader->get_group( $builder )->handle_remote_import( $response[ $step ] );
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
	 * Get license key.
	 * @return mixed
	 */
	public function get_license_key() {
		$licenseKey      = false;
		$woofunnels_data = get_option( 'woofunnels_plugins_info' );
		if ( is_array( $woofunnels_data ) && count( $woofunnels_data ) > 0 && defined( 'WFOCU_PLUGIN_BASENAME' ) ) {

			foreach ( $woofunnels_data as $key => $license ) {
				if ( is_array( $license ) && isset( $license['activated'] ) && $license['activated'] && sha1( WFOCU_PLUGIN_BASENAME ) === $key && $license['data_extra']['software_title'] === $this->get_software_title() ) {
					$licenseKey = $license['data_extra']['api_key'];
					break;
				}
			}
		}

		return $licenseKey;
	}

	public function get_software_title() {
		return "Upstroke: WooCommerce One Click Upsell";
	}

	public function get_template_api_url() {
		return 'http://gettemplates.buildwoofunnels.com/';
	}
}

//WFOCU_Core::register( 'remote_importer', 'WFOCU_Remote_Template_Importer' );