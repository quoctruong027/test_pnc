<?php

/**
 * Get additional system & plugin specific information for feedback
 *
 */
if ( ! function_exists( 'tlwp_get_additional_info' ) ) {

	/**
	 * Get TLWP specific information
	 *
	 * @param $additional_info
	 * @param bool $system_info
	 *
	 * @return mixed
	 *
	 * @since 1.5.17
	 */
	function tlwp_get_additional_info( $additional_info, $system_info = false ) {
		global $tlwp_tracker;

		$additional_info['version'] = WTLWP_PLUGIN_VERSION;

		if ( $system_info ) {

			$additional_info['active_plugins']   = implode( ', ', $tlwp_tracker::get_active_plugins() );
			$additional_info['inactive_plugins'] = implode( ', ', $tlwp_tracker::get_inactive_plugins() );
			$additional_info['current_theme']    = $tlwp_tracker::get_current_theme_info();
			$additional_info['wp_info']          = $tlwp_tracker::get_wp_info();
			$additional_info['server_info']      = $tlwp_tracker::get_server_info();

			// TLWP Specific information
			//$additional_info['plugin_meta_info'] = Wp_Temporary_Login_Without_Password_Common::get_tlwp_meta_info();
		}

		return $additional_info;

	}

}

add_filter( 'tlwp_additional_feedback_meta_info', 'tlwp_get_additional_info', 10, 2 );

if ( ! function_exists( 'tlwp_can_ask_user_for_review' ) ) {
	/**
	 * Can we ask user for 5 star review?
	 *
	 * @return bool
	 *
	 * @since 1.5.22
	 */
	function tlwp_can_ask_user_for_review( $enable, $review_data ) {

		if ( $enable ) {

			$current_user_id = get_current_user_id();

			// Don't show 5 star review notice to temporary user
			if ( ! empty( $current_user_id ) && Wp_Temporary_Login_Without_Password_Common::is_valid_temporary_login( $current_user_id ) ) {
				return false;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			$temporary_logins = Wp_Temporary_Login_Without_Password_Common::get_temporary_logins();
			$total_logins     = count( $temporary_logins );

			// Is user fall in love with our plugin in 60 days after they said no for the review?
			// But, make sure we are asking user only after 60 days.
			// We are good people. Respect the user decision.
			if ( $total_logins < 1 ) {
				return false;
			}
		}

		return $enable;

	}
}

add_filter( 'tlwp_can_ask_user_for_review', 'tlwp_can_ask_user_for_review', 10, 2 );


if ( ! function_exists( 'tlwp_review_message_data' ) ) {
	/**
	 * Filter 5 star review data
	 *
	 * @param $review_data
	 *
	 * @return mixed
	 *
	 * @since 1.5.22
	 */
	function tlwp_review_message_data( $review_data ) {

		$icon_url = WTLWP_PLUGIN_URL . 'admin/assets/images/icon-64.png';

		$review_data['icon_url'] = $icon_url;

		return $review_data;
	}
}

add_filter( 'tlwp_review_message_data', 'tlwp_review_message_data', 10 );

if ( ! function_exists('tlwp_can_load_sweetalert_js') ) {
	/**
	 * Can load sweetalert js
	 *
	 * @param bool $load
	 *
	 * @return bool
	 *
	 * @since 1.5.24
	 */
	function tlwp_can_load_sweetalert_js( $load = false ) {

		if ( Wp_Temporary_Login_Without_Password_Common::is_tlwp_admin_page() ) {
			return true;
		}

		return $load;
	}
}

add_filter( 'tlwp_can_load_sweetalert_js', 'tlwp_can_load_sweetalert_js', 10, 1 );