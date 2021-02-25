<?php

/**
 * WC Dependency Checker
 */
class BWFAN_PRO_Plugin_Dependency {

	private static $active_plugins;

	public static function init() {
		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
	}

	/** checking paid membership pro is active
	 * @return bool
	 */
	public static function paid_membership_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		return in_array( 'paid-memberships-pro/paid-memberships-pro.php', self::$active_plugins, true ) || array_key_exists( 'paid-memberships-pro/paid-memberships-pro.php', self::$active_plugins );
	}

	/**
	 * check if thrive plugin active
	 * @return bool
	 */
	public static function tve_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		return in_array( 'thrive-leads/thrive-leads.php', self::$active_plugins, true ) || array_key_exists( 'thrive-leads/thrive-leads.php', self::$active_plugins );
	}

	/**
	 * Checking if learndash plugin active
	 * @return bool
	 */
	public static function learndash_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( defined( 'LEARNDASH_VERSION' ) ) {
			return true;
		}

		return in_array( 'sfwd-lms/sfwd_lms.php', self::$active_plugins, true ) || array_key_exists( 'sfwd-lms/sfwd_lms.php', self::$active_plugins );
	}

	/**
	 * Checking if ninja form plugin active
	 * @return bool
	 */
	public static function ninja_forms_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( function_exists( 'Ninja_Forms' ) ) {
			return true;
		}

		return in_array( 'ninja-forms/ninja-forms.php', self::$active_plugins, true ) || array_key_exists( 'ninja-forms/ninja-forms.php', self::$active_plugins );
	}

	/**
	 * Checking if fluent form plugin active
	 * @return bool
	 */
	public static function fluent_forms_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( function_exists( 'wpFluent' ) ) {
			return true;
		}

		return in_array( 'fluentform/fluentform.php', self::$active_plugins, true ) || array_key_exists( 'fluentform/fluentform.php', self::$active_plugins );
	}

	/**
	 * Checking if caldera form plugin active
	 * @return bool
	 */
	public static function caldera_forms_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( class_exists( 'Caldera_Forms' ) ) {
			return true;
		}

		return in_array( 'caldera-forms/caldera-core.php', self::$active_plugins, true ) || array_key_exists( 'caldera-forms/caldera-core.php', self::$active_plugins );
	}

	/**
	 * Checking if utm grabber plugin active
	 * @return bool
	 */
	public static function handle_utm_grabber_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		return in_array( 'handl-utm-grabber/handl-utm-grabber.php', self::$active_plugins, true ) || array_key_exists( 'handl-utm-grabber/handl-utm-grabber.php', self::$active_plugins );
	}
}
