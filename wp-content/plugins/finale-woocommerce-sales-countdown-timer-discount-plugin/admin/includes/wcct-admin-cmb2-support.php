<?php

class WCCT_Admin_CMB2_Support {

	/**
	 * Callback function for groups
	 *
	 * @param $field_args CMB2 Field args
	 * @param $field
	 */
	public static function cmb2_wcct_before_call( $field_args, $field ) {
		$attributes = '';
		if ( ( $field_args['id'] == '_wcct_events' ) || ( $field_args['id'] == '_wcct_discount_custom_advanced' ) || ( $field_args['id'] == '_wcct_deal_custom_advanced' ) ) {
			$class_single = '';
			foreach ( $field_args['attributes'] as $attr => $val ) {
				if ( $attr == 'class' ) {
					$class_single .= ' ' . $val;
				}
				// if data attribute, use single quote wraps, else double
				$quotes     = false !== stripos( $attr, 'data-' ) ? "'" : '"';
				$attributes .= sprintf( ' %1$s=%3$s%2$s%3$s', $attr, $val, $quotes );
			}

			$class_single .= ' ' . $field_args['id'] . '-wcct_rep_wrap';
			echo '<div class="wcct_custom_wrapper_group' . $class_single . ' " ' . $attributes . '>';
		}
	}

	/**
	 * Callback function for groups
	 *
	 * @param $field_args CMB2 Field args
	 * @param $field
	 */
	public static function cmb2_wcct_after_call( $field_args, $field ) {
		$attributes = '';
		if ( ( $field_args['id'] == '_wcct_events' ) || ( $field_args['id'] == '_wcct_discount_custom_advanced' ) || ( $field_args['id'] == '_wcct_deal_custom_advanced' ) ) {
			echo '</div>';
		}
	}

	/**
	 * Output a message if the current page has the id of "2" (the about page)
	 *
	 * @param  object $field_args Current field args
	 * @param  object $field Current field object
	 */
	public static function cmb_after_row_cb( $field_args, $field ) {
		echo '</div></div>';
	}

	/**
	 * Output a message if the current page has the id of "2" (the about page)
	 *
	 * @param  object $field_args Current field args
	 * @param  object $field Current field object
	 */
	public static function cmb_before_row_cb( $field_args, $field ) {
		$default = array(
			'wcct_accordion_title'     => __( 'Untitled', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'wcct_is_accordion_opened' => false,
		);

		$field_args = wp_parse_args( $field_args, $default );

		$is_active       = ( $field_args['wcct_is_accordion_opened'] ) ? 'active' : '';
		$is_display_none = ( ! $field_args['wcct_is_accordion_opened'] ) ? "style='display:none'" : '';
		echo '<div class="cmb2_wcct_wrapper_ac"><div class="cmb2_wcct_acc_head ' . $is_active . ' "><a href="javascript:void(0);">' . $field_args['wcct_accordion_title'] . '</a> <div class="toggleArrow"></div></div><div class="cmb2_wcct_wrapper_ac_data" ' . $is_display_none . '>';
	}

	/**
	 * Hooked over `xl_cmb2_add_conditional_script_page` so that we can load conditional logic scripts
	 *
	 * @param $options Pages
	 *
	 * @return mixed
	 */
	public static function wcct_push_support_form_cmb_conditionals( $pages ) {

		return $pages;
	}

	public static function row_classes_inline_desc( $field_args, $field ) {
		return array( 'wcct_field_inline_desc' );
	}

	public static function row_date_classes( $field_args, $field ) {
		return array( 'wcct_field_date_range' );
	}

	public static function render_trigger_nav() {
		$get_campaign_statuses = apply_filters( 'wcct_admin_trigger_nav', WCCT_Common::get_campaign_statuses() );
		$html                  = '<ul class="subsubsub subsubsub_wcct">';
		$html_inside           = array();
		$html_inside[]         = sprintf( '<li><a href="%s" class="%s">%s</a></li>', admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=all' ), self::active_class( 'all' ), __( 'All', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
		foreach ( $get_campaign_statuses as $status ) {
			$html_inside[] = sprintf( '<li><a href="%s" class="%s">%s</a></li>', admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=' . $status['slug'] ), self::active_class( $status['slug'] ), $status['name'] );
		}

		if ( is_array( $html_inside ) && count( $html_inside ) > 0 ) {
			$html .= implode( '', $html_inside );
		}
		$html .= '</ul>';

		echo $html;
	}

	public static function active_class( $trigger_slug ) {

		if ( self::get_current_trigger() == $trigger_slug ) {
			return 'current';
		}

		return '';
	}

	public static function get_current_trigger() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' && isset( $_GET['section'] ) ) {
			return $_GET['section'];
		}

		return 'all';
	}

	public static function cmb_opt_groups( $args, $defaults, $field_object, $field_types_object ) {

		// Only do this for the field we want (vs all select fields)
		if ( '_wcct_data_choose_trigger' != $field_types_object->_id() ) {
			return $args;
		}

		$option_array = WCCT_Common::get_campaign_status_select();

		$saved_value = $field_object->escaped_value();
		$value       = $saved_value ? $saved_value : $field_object->args( 'default' );

		$options_string = '';

		$args = array(
			'label'   => __( 'Select an Option', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'value'   => '',
			'checked' => ! $value,
		);

		if ( $field_object->args['show_option_none'] ) {
			$options_string .= $field_types_object->select_option( $args );
		}

		foreach ( $option_array as $group_label => $group ) {

			$options_string .= '<optgroup label="' . $group_label . '">';

			foreach ( $group as $key => $label ) {

				$args           = array(
					'label'   => $label,
					'value'   => $key,
					'checked' => $value == $key,
				);
				$options_string .= $field_types_object->select_option( $args );
			}
			$options_string .= '</optgroup>';
		}

		// Ok, replace the options value
		$defaults['options'] = $options_string;

		return $defaults;
	}


	public static function maybe_show_no_coupon() {
		$posts = array();

		$coupon_count = wp_count_posts( 'shop_coupon' );

		if ( ! is_object( $coupon_count ) || (int) $coupon_count->publish > 0 ) {
			return '<p class="cmb2-metabox-description">' . __( 'Don\'t forget to check this coupon\'s <a href="{coupon_link}">usage restrictions</a>. Finale applies these coupons during the campaign, it does not restrict coupons based on campaign rules. This responsibility lies with native coupon settings.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</p>';
		}

		return '<p class="cmb2-metabox-description" style="margin-top: 5px;">' . __( 'No coupons available', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . ' <a href="#">' . __( 'Add a Coupon', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) . '</a></p>';
	}


	public static function get_coupons_selected( $field ) {
		$value       = $field->escaped_value();
		$get_coupons = WCCT_Common::get_coupons();

		if ( $value && $value !== '' ) {
			$get_post = WCCT_Common::get_post_data( $value );

			if ( $get_post instanceof WP_Post && $get_post->post_type === 'shop_coupon' ) {
				$get_coupons[ $get_post->ID ] = WCCT_Common::get_the_title( $get_post->ID );
			}
		}

		return $get_coupons;
	}

	public static function wcct_coupons_set_field_data_attr( $args, $field ) {
		$field->args['attributes']['data-pre-data'] = wp_json_encode( WCCT_Common::get_coupons( true ) );

	}

	public static function wcct_pages_set_field_data_attr( $args, $field ) {
		$field->args['attributes']['data-pre-data'] = wp_json_encode( WCCT_Common::get_pages( true ) );

	}

	public static function get_pages_selected( $field ) {
		$value       = $field->escaped_value();
		$get_coupons = WCCT_Common::get_pages();

		if ( $value && $value !== '' ) {
			$get_post = WCCT_Common::get_post_data( $value );

			if ( $get_post instanceof WP_Post && $get_post->post_type === 'page' ) {
				$get_coupons[ $get_post->ID ] = WCCT_Common::get_the_title( $get_post );
			}
		}

		return $get_coupons;
	}

	public static function cmb2_product_title( $id ) {
		$product      = wc_get_product( $id );
		$product_name = WCCT_Compatibility::woocommerce_get_formatted_product_name( $product );

		return $product_name;
	}

	public static function get_product_types() {
		$array = wc_get_product_types();

		return $array;
	}
}
