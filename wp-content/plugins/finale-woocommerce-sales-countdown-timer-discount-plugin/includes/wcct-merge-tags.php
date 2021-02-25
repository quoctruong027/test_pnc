<?php

class WCCT_Merge_Tags {

	public static $threshold_to_date = 30;

	/**
	 * Maybe try and parse content to found the wcct merge tags
	 * And converts them to the standard wp shortcode way
	 * So that it can be used as do_shortcode in future
	 *
	 * @param string $content
	 *
	 * @return mixed|string
	 */
	public static function maybe_parse_merge_tags( $content = '', $slug = '' ) {
		$get_all = self::get_all_tags();

		//iterating over all the merge tags
		if ( $get_all && is_array( $get_all ) && count( $get_all ) > 0 ) {
			foreach ( $get_all as $tag ) {
				$matches = array();
				$re      = sprintf( '/\{{%s(.*?)\}}/', $tag );
				$str     = $content;

				//trying to find match w.r.t current tag
				preg_match_all( $re, $str, $matches );

				//if match found
				if ( $matches && is_array( $matches ) && count( $matches ) > 0 ) {

					if ( ! isset( $matches[0] ) ) {
						return;
					}

					//iterate over the found matches
					foreach ( $matches[0] as $exact_match ) {

						//preserve old match
						$old_match = $exact_match;

						//replace the current tag with the square brackets [shortcode compatible]
						$exact_match = str_replace( '{{' . $tag, '[wcct_' . $tag, $exact_match );
						$exact_match = str_replace( '}}', ']', $exact_match );

						$content = str_replace( $old_match, $exact_match, $content );
					}
				}
			}
		}

		return $content;
	}

	public static function get_all_tags() {
		$tags = array(
			'current_time',
			'current_date',
			'current_day',
			'today',
			'wcct_countdown_timer_admin',
			'price_html',
			'sale_price',
			'regular_price',
			'campaign_coupon_name',
			'campaign_coupon_value',
			'custom_countdown_timer',
		);

		return $tags;
	}

	public static function init() {
		add_shortcode( 'wcct_current_time', array( __CLASS__, 'process_time' ) );
		add_shortcode( 'wcct_current_date', array( __CLASS__, 'process_date' ) );
		add_shortcode( 'wcct_today', array( __CLASS__, 'process_today' ) );
		add_shortcode( 'wcct_current_day', array( __CLASS__, 'process_day' ) );
		add_shortcode( 'wcct_wcct_countdown_timer_admin', array( __CLASS__, 'countdown_timer_admin' ) );
		add_shortcode( 'wcct_price_html', array( __CLASS__, 'maybe_show_price_html' ) );
		add_shortcode( 'wcct_sale_price', array( __CLASS__, 'maybe_show_sale_price' ) );
		add_shortcode( 'wcct_regular_price', array( __CLASS__, 'maybe_show_regular_price' ) );
		add_shortcode( 'wcct_campaign_coupon_name', array( __CLASS__, 'maybe_show_campaign_coupon_name' ) );
		add_shortcode( 'wcct_campaign_coupon_value', array( __CLASS__, 'maybe_show_campaign_coupon_value' ) );
		add_shortcode( 'wcct_custom_countdown_timer', array( __CLASS__, 'custom_countdown_timer' ) );
	}

	public static function process_date( $shortcode_attrs ) {
		$default_f = WCCT_Common::wcct_get_date_format();
		$atts      = shortcode_atts( array(
			'format'        => $default_f, //has to be user friendly , user will not understand 12:45 PM (g:i A) (https://codex.wordpress.org/Formatting_Date_and_Time)
			'adjustment'    => '',
			'cutoff'        => '',
			'exclude_days'  => '',
			'exclude_dates' => '',
		), $shortcode_attrs );

		$date_obj = new DateTime( 'now', new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

		/** cutoff functionality starts */
		if ( '' !== $atts['cutoff'] ) {
			$date_obj_cutoff = new DateTime();
			$parsed_date     = date_parse( $atts['cutoff'] );
			$date_defaults   = array(
				'year'   => $date_obj_cutoff->format( 'Y' ),
				'month'  => $date_obj_cutoff->format( 'm' ),
				'day'    => $date_obj_cutoff->format( 'd' ),
				'hour'   => $date_obj_cutoff->format( 'H' ),
				'minute' => $date_obj_cutoff->format( 'i' ),
				'second' => '00',
			);
			foreach ( $parsed_date as $attrs => &$date_elements ) {
				if ( false === $date_elements && isset( $date_defaults[ $attrs ] ) ) {
					$parsed_date[ $attrs ] = $date_defaults[ $attrs ];
				}
			}

			$parsed_date = wp_parse_args( $parsed_date, $date_defaults );
			$date_obj_cutoff->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
			$date_obj_cutoff->setDate( $parsed_date['year'], $parsed_date['month'], $parsed_date['day'] );
			$date_obj_cutoff->setTime( $parsed_date['hour'], $parsed_date['minute'], $parsed_date['second'] );

			if ( $date_obj->getTimestamp() > $date_obj_cutoff->getTimestamp() ) {
				$date_obj->modify( '+1 days' );
			}
		}

		/**
		 * Pre check
		 */
		$itr = 0;
		while ( $itr < self::$threshold_to_date && ( ( ( '' !== $atts['exclude_dates'] ) && ( self::is_not_excluded_date( $date_obj, $atts['exclude_dates'] ) === false ) ) || ( ( '' !== $atts['exclude_days'] ) && ( self::is_not_excluded_day( $date_obj, $atts['exclude_days'] ) === false ) ) ) ) {
			$date_obj->modify( '+1 day' );
			$itr ++;
		}

		/** Cut-Off functionality Ends */
		if ( '' !== $atts['adjustment'] ) {
			$date_obj->modify( trim( $atts['adjustment'] ) );
		}

		/**
		 * After check
		 */
		$itr = 0;
		while ( $itr < self::$threshold_to_date && ( ( ( '' !== $atts['exclude_dates'] ) && ( self::is_not_excluded_date( $date_obj, $atts['exclude_dates'] ) === false ) ) || ( ( '' !== $atts['exclude_days'] ) && ( self::is_not_excluded_day( $date_obj, $atts['exclude_days'] ) === false ) ) ) ) {

			$date_obj->modify( '+1 day' );
			$itr ++;
		}

		return date_i18n( $atts['format'], $date_obj->getTimestamp() );
	}

	protected static function is_not_excluded_date( $date, $exclusions ) {
		$exclusions         = str_replace( ' ', '', $exclusions );
		$explode_exclusions = explode( ',', $exclusions );
		$explode_exclusions = apply_filters( 'wcct_merge_tags_date_exclude_dates', $explode_exclusions, $date );

		if ( in_array( strtolower( $date->format( 'Y-m-d' ) ), $explode_exclusions, true ) ) {
			return false;
		}

		return true;
	}

	protected static function is_not_excluded_day( $date, $exclusions ) {
		$exclusions         = str_replace( ' ', '', $exclusions );
		$explode_exclusions = explode( ',', $exclusions );
		$explode_exclusions = apply_filters( 'wcct_merge_tags_date_exclude_days', $explode_exclusions, $date );

		if ( in_array( strtolower( $date->format( 'l' ) ), $explode_exclusions, true ) ) {

			return false;
		}

		return true;
	}

	public static function process_day( $shortcode_attrs ) {
		$atts = shortcode_atts( array(
			'adjustment'    => '',
			'cutoff'        => '',
			'exclude_days'  => '',
			'exclude_dates' => '',
		), $shortcode_attrs );

		$date_obj = new DateTime();
		$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

		/** cutoff functionality starts */
		if ( '' !== $atts['cutoff'] ) {
			$date_obj_cutoff = new DateTime();
			$parsed_date     = date_parse( $atts['cutoff'] );
			$date_defaults   = array(
				'year'   => $date_obj_cutoff->format( 'Y' ),
				'month'  => $date_obj_cutoff->format( 'm' ),
				'day'    => $date_obj_cutoff->format( 'd' ),
				'hour'   => $date_obj_cutoff->format( 'H' ),
				'minute' => $date_obj_cutoff->format( 'i' ),
				'second' => '00',
			);
			foreach ( $parsed_date as $attrs => &$date_elements ) {
				if ( false === $date_elements && isset( $date_defaults[ $attrs ] ) ) {
					$parsed_date[ $attrs ] = $date_defaults[ $attrs ];
				}
			}

			$parsed_date = wp_parse_args( $parsed_date, $date_defaults );
			$date_obj_cutoff->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
			$date_obj_cutoff->setDate( $parsed_date['year'], $parsed_date['month'], $parsed_date['day'] );
			$date_obj_cutoff->setTime( $parsed_date['hour'], $parsed_date['minute'], $parsed_date['second'] );

			if ( $date_obj->getTimestamp() > $date_obj_cutoff->getTimestamp() ) {
				$date_obj->modify( '+1 days' );
			}
		}

		//pre check
		$itr = 0;
		/**
		 * iterating all over the recursive check for a valid date
		 */
		while ( $itr < self::$threshold_to_date && ( ( ( '' !== $atts['exclude_days'] ) && ( self::is_not_excluded_date( $date_obj, $atts['exclude_dates'] ) === false ) ) || ( ( '' !== $atts['exclude_days'] ) && ( self::is_not_excluded_day( $date_obj, $atts['exclude_days'] ) === false ) ) ) ) {
			$date_obj->modify( '+1 day' );
			$itr ++;
		}
		/** Cut-Off functionality Ends */
		if ( '' !== $atts['adjustment'] ) {
			$date_obj->modify( $atts['adjustment'] );
		}
		$itr = 0;
		/**
		 * iterating all over the recursive check for a valid date
		 */
		while ( $itr < self::$threshold_to_date && ( ( ( '' !== $atts['exclude_days'] ) && ( self::is_not_excluded_date( $date_obj, $atts['exclude_dates'] ) === false ) ) || ( ( '' !== $atts['exclude_days'] ) && ( self::is_not_excluded_day( $date_obj, $atts['exclude_days'] ) === false ) ) ) ) {
			$date_obj->modify( '+1 day' );
			$itr ++;
		}

		return date_i18n( 'l', $date_obj->getTimestamp() );
	}

	public static function process_today( $shortcode_attrs ) {
		$atts = shortcode_atts( array(
			'cutoff'        => '',
			'exclude_days'  => '',
			'exclude_dates' => '',
		), $shortcode_attrs );

		$date_obj = new DateTime();
		$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
		$date_obj_cutoff = new DateTime();

		/** cutoff functionlity starts */
		if ( '' !== $atts['cutoff'] ) {
			$parsed_date   = date_parse( $atts['cutoff'] );
			$date_defaults = array(
				'year'   => $date_obj_cutoff->format( 'Y' ),
				'month'  => $date_obj_cutoff->format( 'm' ),
				'day'    => $date_obj_cutoff->format( 'd' ),
				'hour'   => $date_obj_cutoff->format( 'H' ),
				'minute' => $date_obj_cutoff->format( 'i' ),
				'second' => '00',
			);
			foreach ( $parsed_date as $attrs => &$date_elements ) {
				if ( false === $date_elements && isset( $date_defaults[ $attrs ] ) ) {
					$parsed_date[ $attrs ] = $date_defaults[ $attrs ];
				}
			}
			$parsed_date = wp_parse_args( $parsed_date, $date_defaults );
			$date_obj_cutoff->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
			$date_obj_cutoff->setDate( $parsed_date['year'], $parsed_date['month'], $parsed_date['day'] );
			$date_obj_cutoff->setTime( $parsed_date['hour'], $parsed_date['minute'], $parsed_date['second'] );
		}

		if ( $date_obj->getTimestamp() > $date_obj_cutoff->getTimestamp() ) {

			$date_obj->modify( '+1 days' );
			$is_excluded = false;

			/**
			 * iterating all over the recursive check for a valid date
			 */
			$itr = 0;
			while ( $itr < self::$threshold_to_date && ( ( ( '' !== $atts['exclude_days'] ) && ( self::is_not_excluded_date( $date_obj, $atts['exclude_dates'] ) === false ) ) || ( ( '' !== $atts['exclude_days'] ) && ( self::is_not_excluded_day( $date_obj, $atts['exclude_days'] ) === false ) ) ) ) {
				;
				$date_obj->modify( '+1 day' );
				$itr ++;
				$is_excluded = true;
			}

			if ( $is_excluded ) {
				return date_i18n( 'l', $date_obj->getTimestamp() );
			} else {
				return __( 'tomorrow', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			}
		} else {
			$is_excluded = false;
			/**
			 * iterating all over the recursive check for a valid date
			 */
			$itr = 0;
			while ( $itr < self::$threshold_to_date && ( ( ( '' !== $atts['exclude_days'] ) && ( self::is_not_excluded_date( $date_obj, $atts['exclude_dates'] ) === false ) ) || ( ( '' !== $atts['exclude_days'] ) && ( self::is_not_excluded_day( $date_obj, $atts['exclude_days'] ) === false ) ) ) ) {
				$date_obj->modify( '+1 day' );
				$is_excluded = true;
				$itr ++;
			}
			if ( $is_excluded ) {
				return date_i18n( 'l', $date_obj->getTimestamp() );
			} else {
				return __( 'today', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			}
		}
	}

	public static function process_time( $shortcode_attrs ) {
		$default_f = WCCT_Common::wcct_get_time_format();
		$atts      = shortcode_atts( array(
			'format'     => $default_f, //has to be user friendly , user will not understand 12:45 PM (g:i A) (https://codex.wordpress.org/Formatting_Date_and_Time)
			'adjustment' => '',
		), $shortcode_attrs );

		$date_obj = new DateTime();
		$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
		if ( '' !== $atts['adjustment'] ) {
			$date_obj->modify( $atts['adjustment'] );
		}

		return date_i18n( $atts['format'], $date_obj->getTimestamp() );
	}

	public static function countdown_timer_admin( $shortcode_attrs ) {
		return __( '<div class="wcct_countdown_timer_admin" data-timer="3600"></div>' );
	}

	public static function maybe_show_price_html( $attr ) {
		return WCCT_Core()->shortcode->wcct_finale_price_html( $attr );
	}

	public static function maybe_show_regular_price( $attr ) {
		return WCCT_Core()->shortcode->wcct_finale_regular_price( $attr );
	}

	public static function maybe_show_sale_price( $attr ) {
		return WCCT_Core()->shortcode->wcct_finale_sale_price( $attr );
	}

	public static function maybe_show_campaign_coupon_name( $attr ) {
		return WCCT_Core()->shortcode->wcct_finale_campaign_coupon_name( $attr );
	}

	public static function maybe_show_campaign_coupon_value( $attr ) {
		return WCCT_Core()->shortcode->wcct_finale_campaign_coupon_value( $attr );
	}

	public static function custom_countdown_timer( $shortcode_atts ) {
		$atts = shortcode_atts( array(
			'cutoff'        => '',
			'exclude_days'  => '',
			'adjustment'    => '',
			'exclude_dates' => '',
		), $shortcode_atts );

		/** cutoff functionality starts */
		if ( empty( $atts['cutoff'] ) ) {
			return __( 'Please mention cutoff details in custom countdown timer', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
		}

		$labels['d']     = 'days';
		$labels['h']     = 'hrs';
		$labels['m']     = 'mins';
		$labels['s']     = 'secs';
		$date_obj_cutoff = new DateTime();
		$parsed_date     = date_parse( $atts['cutoff'] );
		$date_defaults   = array(
			'year'   => $date_obj_cutoff->format( 'Y' ),
			'month'  => $date_obj_cutoff->format( 'm' ),
			'day'    => $date_obj_cutoff->format( 'd' ),
			'hour'   => $date_obj_cutoff->format( 'H' ),
			'minute' => $date_obj_cutoff->format( 'i' ),
			'second' => '00',
		);

		foreach ( $parsed_date as $attrs => &$date_elements ) {
			if ( false === $date_elements && isset( $date_defaults[ $attrs ] ) ) {
				$parsed_date[ $attrs ] = $date_defaults[ $attrs ];
			}
		}

		$parsed_date = wp_parse_args( $parsed_date, $date_defaults );
		$date_obj_cutoff->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
		$date_obj_cutoff->setDate( $parsed_date['year'], $parsed_date['month'], $parsed_date['day'] );
		$date_obj_cutoff->setTime( $parsed_date['hour'], $parsed_date['minute'], $parsed_date['second'] );
		$date_obj = new DateTime();
		$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

		if ( $date_obj->getTimestamp() > $date_obj_cutoff->getTimestamp() ) {
			$date_obj_cutoff->modify( '+1 days' );
		}

		$itr = 0;
		/** check adjustment if any */
		if ( '' !== $atts['adjustment'] ) {
			$date_obj_cutoff->modify( $atts['adjustment'] );
		}
		/**
		 * iterating all over the recursive check for a valid date
		 */
		while ( $itr < self::$threshold_to_date && ( ( ( '' !== $atts['exclude_days'] ) && ( self::is_not_excluded_date( $date_obj_cutoff, $atts['exclude_dates'] ) === false ) ) || ( ( '' !== $atts['exclude_days'] ) && ( self::is_not_excluded_day( $date_obj_cutoff, $atts['exclude_days'] ) === false ) ) ) ) {
			$date_obj_cutoff->modify( '+1 day' );
			$itr ++;
		}

		$interval           = $date_obj_cutoff->diff( $date_obj );
		$end_timestamp      = $date_obj_cutoff->getTimestamp();
		$class              = is_admin() ? 'wcct_is_admin_timer' : '';
		$output             = '<div class="wcct_countdown_timer wcct_timer wcct_countdown_default ' . $class . '" data-days="' . $labels['d'] . '" data-hrs="' . $labels['h'] . '" data-mins="' . $labels['m'] . '" data-secs="' . $labels['s'] . '" >';
		$total_seconds_left = 0;
		$total_seconds_left = $total_seconds_left + ( YEAR_IN_SECONDS * $interval->y );
		$total_seconds_left = $total_seconds_left + ( MONTH_IN_SECONDS * $interval->m );
		$total_seconds_left = $total_seconds_left + ( DAY_IN_SECONDS * $interval->d );
		$total_seconds_left = $total_seconds_left + ( HOUR_IN_SECONDS * $interval->h );
		$total_seconds_left = $total_seconds_left + ( MINUTE_IN_SECONDS * $interval->i );
		$total_seconds_left = $total_seconds_left + $interval->s;

		$output .= "<div class='wcct_timer_wrap' data-date='" . $end_timestamp . "' data-is-days='yes' data-is-hrs='yes' data-left='" . $total_seconds_left . "' data-timer-skin='default' >";
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

}

WCCT_Merge_Tags::init();
