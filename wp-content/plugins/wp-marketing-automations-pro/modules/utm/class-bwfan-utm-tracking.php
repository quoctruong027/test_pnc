<?php


class BWFAN_UTM_Tracking {

	private static $ins = null;
	private $utm_parameters = [];

	private function __construct() {
		add_filter( 'bwfan_before_send_email_body', [ $this, 'maybe_add_utm_parameters' ], 10, 2 );
		add_filter( 'bwfan_modify_send_sms_body', [ $this, 'maybe_add_utm_parameters' ], 10, 2 );
	}

	public static function get_instance() {
		if ( is_null( self::$ins ) ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function maybe_add_utm_parameters( $body, $data ) {
		$is_test = isset( $data['test'] ) ? true : false;
		if ( false === $is_test ) {
			$body = $this->add_tracking_code( $body, $data );
		}

		return $body;
	}

	public function add_tracking_code( $body, $data ) {
		/** Check for UTM parameters */
		if ( ! isset( $data['append_utm'] ) || 1 !== absint( $data['append_utm'] ) ) {
			return $body;
		}

		if ( isset( $data['utm_source'] ) && ! empty( $data['utm_source'] ) ) {
			$this->utm_parameters['utm_source'] = $data['utm_source'];
		}

		if ( isset( $data['utm_medium'] ) && ! empty( $data['utm_medium'] ) ) {
			$this->utm_parameters['utm_medium'] = $data['utm_medium'];
		}

		if ( isset( $data['utm_campaign'] ) && ! empty( $data['utm_campaign'] ) ) {
			$this->utm_parameters['utm_campaign'] = $data['utm_campaign'];
		}

		if ( isset( $data['utm_term'] ) && ! empty( $data['utm_term'] ) ) {
			$this->utm_parameters['utm_term'] = $data['utm_term'];
		}

		$this->utm_parameters = apply_filters( 'bwfan_utm_parameters', $this->utm_parameters, $data );
		$body                 = preg_replace_callback( '/(https?)+[:\/\/].*?(?=\s)/', [ $this, 'append_tracking_click_utm_parameters' ], $body );

		return $body;
	}

	public function append_tracking_click_utm_parameters( $matches ) {
		$string = $matches[0];
		if ( strstr( $string, 'bwfan-action=unsubscribe' ) ) {
			return $string;
		}

		$maybe_quote = strpos( $string, '"' );
		$maybe_html  = strpos( $string, '<' );
		$url         = '';
		if ( false === $maybe_quote && false === $maybe_html ) {
			$file_type = wp_check_filetype( $string );
			if ( ! isset( $file_type['ext'] ) || empty( $file_type['ext'] ) ) {
				$url = add_query_arg( $this->utm_parameters, $string );
			}
		} elseif ( false !== $maybe_quote ) {
			$arr       = explode( '"', $string );
			$file_type = wp_check_filetype( $arr[0] );
			if ( ! isset( $file_type['ext'] ) || empty( $file_type['ext'] ) ) {
				$url = add_query_arg( $this->utm_parameters, $arr[0] );
				$url = $url . '"' . $arr[1];
			}
		} elseif ( false !== $maybe_html ) {
			$arr       = explode( '<', $string );
			$file_type = wp_check_filetype( $arr[0] );
			if ( ! isset( $file_type['ext'] ) || empty( $file_type['ext'] ) ) {
				$url = add_query_arg( $this->utm_parameters, $arr[0] );

				/** append all the string which are not URL after the actual url */
				foreach ( $arr as $html_tag ) {
					if ( filter_var( $html_tag, FILTER_VALIDATE_URL ) ) {
						continue;
					}

					$url .= '<' . $html_tag;
				}
			}
		}

		if ( ! empty( $url ) ) {
			return apply_filters( 'bwfan_append_tracking_utm_parameters', $url );
		}

		return $string;
	}

}

BWFAN_UTM_Tracking::get_instance();
