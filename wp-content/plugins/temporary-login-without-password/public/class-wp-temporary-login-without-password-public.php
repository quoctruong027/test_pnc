<?php
/**
 * Public face of Temporary Login Without Password
 *
 * @package Temporary Login Without Password
 */

/**
 * Class Wp_Temporary_Login_Without_Password_Public
 *
 * @package Temporary Login Without Password
 */
class Wp_Temporary_Login_Without_Password_Public {

	/**
	 * Plugin Name
	 *
	 * @var string $plugin_name
	 */
	private $plugin_name;

	/**
	 * Plugin Version
	 *
	 * @var string $version
	 */
	private $version;

	/**
	 * Wp_Temporary_Login_Without_Password_Public constructor.
	 *
	 * @param string $plugin_name Plugin Name.
	 * @param string $version Plugin Version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Get Error Message
	 *
	 * @param string $error_code Error Code.
	 *
	 * @return array|mixed|string
	 */
	public static function get_error_messages( $error_code ) {

		$error_messages = array(
			'token'  => __( 'Token empty', 'temporary-login-without-password' ),
			'unauth' => __( 'Authentication failed', 'temporary-login-without-password' ),
		);

		if ( ! empty( $error_code ) ) {
			return ( isset( $error_messages[ $error_code ] ) ? $error_messages[ $error_code ] : '' );
		}

		return $error_messages;
	}

	/**
	 * Initialize Temporary Login
	 *
	 * Hooked to init action to initilize tlwp
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function init_wtlwp() {

		if ( ! empty( $_GET['wtlwp_token'] ) ) {

			$wtlwp_token = sanitize_key( $_GET['wtlwp_token'] );  // Input var okay.
			$users       = Wp_Temporary_Login_Without_Password_Common::get_valid_user_based_on_wtlwp_token( $wtlwp_token );

			$temporary_user = '';
			if ( ! empty( $users ) ) {
				$temporary_user = $users[0];
			}

			if ( ! empty( $temporary_user ) ) {

				$temporary_user_id = $temporary_user->ID;
				$do_login          = true;
				if ( is_user_logged_in() ) {
					$current_user_id = get_current_user_id();
					if ( $temporary_user_id !== $current_user_id ) {
						wp_logout();
					} else {
						$do_login = false;
					}
				}

				if ( $do_login ) {
					$temporary_user_login = $temporary_user->login;
					update_user_meta( $temporary_user_id, '_wtlwp_last_login', Wp_Temporary_Login_Without_Password_Common::get_current_gmt_timestamp() ); // phpcs:ignore
					wp_set_current_user( $temporary_user_id, $temporary_user_login );
					wp_set_auth_cookie( $temporary_user_id );

					// Set login count
					$login_count_key = '_wtlwp_login_count';
					$login_count     = get_user_meta( $temporary_user_id, $login_count_key, true );

					// If we already have a count, increment by 1
					if ( ! empty( $login_count ) ) {
						$login_count ++;
					} else {
						$login_count = 1;
					}

					update_user_meta( $temporary_user_id, $login_count_key, $login_count );
					do_action( 'wp_login', $temporary_user_login, $temporary_user );
				}

				/**
				 * There is an issue with WordPress which installed in sub directory
				 *
				 * e.g WordPress installed at https://wpm.stg/wpmsub
				 *
				 * So, when we are preparing redirect url from current request, we are getting
				 * https://wpm.stg/wpmsub/wpmsub url. Which leads to 404 Not Found.
				 *
				 * So, We need to remove extra "wpmsub" from url.
				 *
				 * If it's Multi site installation, we don't need to remove.
				 * We only need to remove it for WordPress sub directory installation.
				 */

				// Get current request
				$request_uri = $_SERVER['REQUEST_URI'];

				if ( ! is_multisite() ) {
					$component = trim(parse_url( get_site_url(), PHP_URL_PATH ));

					if ( ! empty( $component ) ) {
						/**
						 * Someone may have subdirectory name as 'wp'.
						 *
						 * So, in this scenario, $component would be '/wp' and request uri will
						 * be /wp/wp-admin/?...
						 *
						 * We want to replace only subdirectory. So, if we do str_replace($component, '', $request_uri)
						 * it will result '-admin/?...' which is wrong.
						 *
						 * So, instead of replacing only '/wp', we will replace '/wp/' (end slash) which
						 * results in 'wp-admin/?...'.
						 *
						 * So, we are adding '/' to $component
						 */
						$component .= '/';
						$request_uri = str_replace( $component, '', $request_uri );
					}
				}

				$redirect_to = ( isset( $_REQUEST['redirect_to'] ) ) ? $_REQUEST['redirect_to'] : apply_filters( 'login_redirect', network_site_url( remove_query_arg( 'wtlwp_token', $request_uri ) ), false, $temporary_user ); // phpcs:ignore

			} else {
				// Temporary user not found?? Redirect to home page.
				$redirect_to = home_url();
			}

			wp_safe_redirect( $redirect_to ); // Redirect to given url after successful login.
			exit();

		}

		// Restrict unauthorized page access for temporary users
		if ( is_user_logged_in() ) {

			$user_id = get_current_user_id();
			if ( ! empty( $user_id ) && Wp_Temporary_Login_Without_Password_Common::is_valid_temporary_login( $user_id, false ) ) {
				if ( Wp_Temporary_Login_Without_Password_Common::is_login_expired( $user_id ) ) {
					wp_logout();
					wp_safe_redirect( home_url() );
					exit();
				} else {

					global $pagenow;
					$bloked_pages = Wp_Temporary_Login_Without_Password_Common::get_blocked_pages();
					$page         = ! empty( $_GET['page'] ) ? $_GET['page'] : ''; //phpcs:ignore

					if ( ! empty( $page ) && in_array( $page, $bloked_pages ) || ( ! empty( $pagenow ) && ( in_array( $pagenow, $bloked_pages ) ) ) || ( ! empty( $pagenow ) && ( 'users.php' === $pagenow && isset( $_GET['action'] ) && ( 'deleteuser' === $_GET['action'] || 'delete' === $_GET['action'] ) ) ) ) { //phpcs:ignore
						wp_die( esc_attr__( "You don't have permission to access this page", 'temporary-login-without-password' ) );
					}

				}
			}
		}

	}

	/**
	 * Hooked to wp_authenticate_user filter to disable login for temporary user using username/email and password
	 *
	 * @param WP_User $user WP_User object.
	 * @param string $password password of a user.
	 *
	 * @return WP_Error
	 */
	public function disable_temporary_user_login( $user, $password ) {

		if ( $user instanceof WP_User ) {
			$check_expiry             = false;
			$is_valid_temporary_login = Wp_Temporary_Login_Without_Password_Common::is_valid_temporary_login( $user->ID, $check_expiry );

			// Is temporary user? Disable Login by throwing error.
			if ( $is_valid_temporary_login ) {
				$user = new WP_Error( 'denied', __( "ERROR: User can't find." ) );
			}
		}

		return $user;
	}

	/**
	 * Hooked to allow_password_reset filter to disable reset password for temporary user
	 *
	 * @param boolean $allow allow to reset password.
	 * @param int $user_id user_id of a user.
	 *
	 * @return boolean
	 */
	public function disable_password_reset( $allow, $user_id ) {

		if ( is_int( $user_id ) ) {
			$check_expiry             = false;
			$is_valid_temporary_login = Wp_Temporary_Login_Without_Password_Common::is_valid_temporary_login( $user_id, $check_expiry );
			if ( $is_valid_temporary_login ) {
				$allow = false;
			}
		}

		return $allow;
	}

}
