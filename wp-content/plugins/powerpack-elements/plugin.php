<?php
namespace PowerpackElements;

use Elementor\Utils;
use PowerpackElements\Classes\PP_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; } // Exit if accessed directly

/**
 * Main class plugin
 */
class Powerpackplugin {

	/**
	 * @var Plugin
	 */
	public static $instance = null;

	/**
	 * @var Manager
	 */
	private $_extensions_manager;

	/**
	 * @var Manager
	 */
	public $modules_manager;

	/**
	 * @var array
	 */
	private $_localize_settings = array();

	private $_settings = array();

	/**
	 * @return string
	 */
	public function get_version() {
		return POWERPACK_ELEMENTS_VER;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'powerpack' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'powerpack' ), '1.0.0' );
	}

	/**
	 * @return Plugin
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Include components
	 *
	 * @since 2.0.3
	 *
	 * @access private
	 */
	private function includes() {

		// FAQ Schema
		pp_plugin_include( 'classes/class-pp-faq-schema.php' );

		// Posts Helper Functions
		pp_plugin_include( 'classes/class-pp-posts-helper.php' );

		// WooCommerce Helper Functions
		pp_plugin_include( 'classes/class-pp-woo-helper.php' );

		// WPML Compatibility
		pp_plugin_include( 'classes/class-pp-wpml.php' );

		// Managers
		pp_plugin_include( 'includes/extensions-manager.php' );
		pp_plugin_include( 'includes/modules-manager.php' );

		// Magic Wand Functinoality
		pp_plugin_include( 'classes/class-pp-magic-wand.php' );
	}

	public function autoload( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		$filename = strtolower(
			preg_replace(
				array( '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ),
				array( '', '$1-$2', '-', DIRECTORY_SEPARATOR ),
				$class
			)
		);
		$filename = POWERPACK_ELEMENTS_PATH . $filename . '.php';

		if ( is_readable( $filename ) ) {
			include $filename;
		}
	}

	public function get_localize_settings() {
		return $this->_localize_settings;
	}

	public function add_localize_settings( $setting_key, $setting_value = null ) {
		if ( is_array( $setting_key ) ) {
			$this->_localize_settings = array_replace_recursive( $this->_localize_settings, $setting_key );

			return;
		}

		if ( ! is_array( $setting_value ) || ! isset( $this->_localize_settings[ $setting_key ] ) || ! is_array( $this->_localize_settings[ $setting_key ] ) ) {
			$this->_localize_settings[ $setting_key ] = $setting_value;

			return;
		}

		$this->_localize_settings[ $setting_key ] = array_replace_recursive( $this->_localize_settings[ $setting_key ], $setting_value );
	}

	public function register_style_scripts() {
		$settings         = \PowerpackElements\Classes\PP_Admin_Settings::get_settings();
		$suffix           = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$direction_suffix = is_rtl() ? '-rtl' : '';

		wp_register_style(
			'tablesaw',
			POWERPACK_ELEMENTS_URL . 'assets/lib/tablesaw/tablesaw.css',
			array(),
			POWERPACK_ELEMENTS_VER
		);

		wp_register_style(
			'odometer',
			POWERPACK_ELEMENTS_URL . 'assets/lib/odometer/odometer-theme-default.css',
			array(),
			POWERPACK_ELEMENTS_VER
		);

		wp_register_style(
			'pp-twentytwenty',
			POWERPACK_ELEMENTS_URL . 'assets/lib/twentytwenty/twentytwenty.css',
			array(),
			POWERPACK_ELEMENTS_VER
		);

		wp_register_style(
			'pp-magnific-popup',
			POWERPACK_ELEMENTS_URL . 'assets/lib/magnific-popup/magnific-popup' . $suffix . '.css',
			array(),
			POWERPACK_ELEMENTS_VER
		);

		wp_register_style(
			'fancybox',
			POWERPACK_ELEMENTS_URL . 'assets/lib/fancybox/jquery.fancybox' . $suffix . '.css',
			array(),
			POWERPACK_ELEMENTS_VER
		);

		wp_register_style(
			'pp-hamburgers',
			POWERPACK_ELEMENTS_URL . 'assets/lib/hamburgers/hamburgers' . $suffix . '.css',
			array(),
			POWERPACK_ELEMENTS_VER
		);

		wp_register_style(
			'fancybox',
			POWERPACK_ELEMENTS_URL . 'assets/lib/fancybox/jquery.fancybox' . $suffix . '.css',
			array(),
			POWERPACK_ELEMENTS_VER
		);

		wp_register_script(
			'instafeed',
			POWERPACK_ELEMENTS_URL . 'assets/lib/instafeed/instafeed' . $suffix . '.js',
			array(
				'jquery',
			),
			'1.4.1',
			true
		);

		wp_register_script(
			'pp-instagram',
			POWERPACK_ELEMENTS_URL . 'assets/js/pp-instagram.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_localize_script(
			'pp-instagram',
			'ppInsta',
			array(
				'invalid_username' => __( 'The <b>username</b> added is not a valid Instagram Username. Check browser console for more details.', 'powerpack' ),
				'private_account'  => __( 'This account is private.', 'powerpack' ),
				'no_images' 	   => __( 'No <i>images</i> were found in the Instagram profile for <b>', 'powerpack' ),
			)
		);

		wp_register_script(
			'pp-login-form',
			POWERPACK_ELEMENTS_URL . 'assets/js/pp-login-form.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_localize_script(
			'powerpack-frontend',
			'ppLogin',
			array(
				'empty_username'   => __( 'Enter a username or email address.', 'powerpack' ),
				'empty_password'   => __( 'Enter password.', 'powerpack' ),
				'empty_password_1' => __( 'Enter a password.', 'powerpack' ),
				'empty_password_2' => __( 'Re-enter password.', 'powerpack' ),
				'empty_recaptcha'  => __( 'Please check the captcha to verify you are not a robot.', 'powerpack' ),
				'email_sent'       => __( 'A password reset email has been sent to the email address for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.', 'powerpack' ),
				'reset_success'    => __( 'You password has been reset successfully.', 'powerpack' ),
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_register_script( 'pp-google-login', 'https://apis.google.com/js/api:client.js', array( 'jquery' ), POWERPACK_ELEMENTS_VER, true );

		wp_register_script( 'pp-google-recaptcha', 'https://www.google.com/recaptcha/api.js?onload=onLoadPPReCaptcha&render=explicit', array( 'jquery', 'pp-login-form' ), POWERPACK_ELEMENTS_VER, true );

		wp_register_script(
			'pp-registration-form',
			POWERPACK_ELEMENTS_URL . 'assets/js/pp-registration-form.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_localize_script(
			'powerpack-frontend',
			'ppRegistration',
			array(
				'invalid_username'      => __( 'This username is invalid because it uses illegal characters. Please enter a valid username.', 'powerpack' ),
				'username_exists'       => __( 'This username is already registered. Please choose another one.', 'powerpack' ),
				'empty_email'           => __( 'Please type your email address.', 'powerpack' ),
				'invalid_email'         => __( 'The email address isn&#8217;t correct!', 'powerpack' ),
				'email_exists'          => __( 'The email is already registered, please choose another one.', 'powerpack' ),
				// translators: %s denotes forward slash character.
				'password'              => sprintf( __( 'Password must not contain the character %s', 'powerpack' ), json_encode( '\\' ) ),
				// translators: %d denotes password length.
				'password_length'       => sprintf( __( 'Your password should be at least %d characters long.', 'powerpack' ), 8 ),
				'password_mismatch'     => __( 'Password does not match.', 'powerpack' ),
				'invalid_url'           => __( 'URL seems to be invalid.', 'powerpack' ),
				'recaptcha_php_ver'     => __( 'reCAPTCHA API requires PHP version 5.3 or above.', 'powerpack' ),
				'recaptcha_missing_key' => __( 'Your reCAPTCHA Site or Secret Key is missing!', 'powerpack' ),
				'show_password'         => __( 'Show password', 'powerpack' ),
				'hide_password'         => __( 'Hide password', 'powerpack' ),
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_register_script(
			'twentytwenty',
			POWERPACK_ELEMENTS_URL . 'assets/lib/twentytwenty/jquery.twentytwenty.js',
			array(
				'jquery',
			),
			'2.0.0',
			true
		);

		wp_register_script(
			'jquery-event-move',
			POWERPACK_ELEMENTS_URL . 'assets/js/jquery.event.move.js',
			array(
				'jquery',
			),
			'2.0.0',
			true
		);

		wp_register_script(
			'pp-magnific-popup',
			POWERPACK_ELEMENTS_URL . 'assets/lib/magnific-popup/jquery.magnific-popup' . $suffix . '.js',
			array(
				'jquery',
			),
			'2.2.1',
			true
		);

		wp_register_script(
			'jquery-cookie',
			POWERPACK_ELEMENTS_URL . 'assets/js/jquery.cookie.js',
			array(
				'jquery',
			),
			'1.4.1',
			true
		);

		wp_register_script(
			'waypoints',
			POWERPACK_ELEMENTS_URL . 'assets/lib/waypoints/waypoints.min.js',
			array(
				'jquery',
			),
			'4.0.1',
			true
		);

		wp_register_script(
			'odometer',
			POWERPACK_ELEMENTS_URL . 'assets/lib/odometer/odometer.min.js',
			array(
				'jquery',
			),
			'0.4.8',
			true
		);

		wp_register_script(
			'jquery-powerpack-dot-nav',
			POWERPACK_ELEMENTS_URL . 'assets/js/one-page-nav.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);

		$language = '';
		$api_url  = 'https://maps.googleapis.com';

		if ( isset( $settings['google_map_lang'] ) && '' !== $settings['google_map_lang'] ) {
			$language = 'language=' . $settings['google_map_lang'];

			// This checks for Chinese language.
			// The Maps JavaScript API is served within China from http://maps.google.cn.
			if (
				'zh' === $settings['google_map_lang'] ||
				'zh-CN' === $settings['google_map_lang'] ||
				'zh-HK' === $settings['google_map_lang'] ||
				'zh-TW' === $settings['google_map_lang']
			) {
				$api_url = 'http://maps.googleapis.cn';
			}
		}

		if ( isset( $settings['google_map_api'] ) && '' !== $settings['google_map_api'] ) {
			$language = '&' . $language;
			$maps_url = $api_url . '/maps/api/js?key=' . $settings['google_map_api'] . $language;
		} else {
			$maps_url = $api_url . '/maps/api/js?' . $language;
		}

		wp_register_script( 'powerpack-google-maps', $maps_url, array(), wp_rand(), true );

		wp_register_script(
			'pp-google-maps',
			POWERPACK_ELEMENTS_URL . 'assets/js/pp-google-maps.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_register_script(
			'pp-advanced-tabs',
			POWERPACK_ELEMENTS_URL . 'assets/js/pp-advanced-tabs.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_register_script(
			'pp-jquery-plugin',
			POWERPACK_ELEMENTS_URL . 'assets/js/jquery.plugin.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);

		wp_register_script(
			'pp-countdown-plugin',
			POWERPACK_ELEMENTS_URL . 'assets/lib/countdown/jquery.countdown.js',
			array(
				'jquery',
			),
			'2.0.2',
			true
		);

		wp_register_script(
			'pp-frontend-countdown',
			POWERPACK_ELEMENTS_URL . 'assets/js/frontend-countdown.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);

		wp_localize_script(
			'pp-toc',
			'ppToc',
			array(
				'no_headings_found'   => __( 'No headings were found on this page.', 'powerpack' )
			)
		);

		wp_register_script(
			'pp-toc',
			POWERPACK_ELEMENTS_URL . 'assets/js/pp-toc.js',
			array(
				'elementor-frontend',
			),
			'1.0.0',
			true
		);

		wp_register_script(
			'pp-product-tabs',
			POWERPACK_ELEMENTS_URL . 'assets/js/pp-product-tabs.js',
			array(
				'elementor-frontend',
			),
			'1.0.0',
			true
		);

		wp_register_script(
			'jquery-smartmenu',
			POWERPACK_ELEMENTS_URL . 'assets/lib/smartmenu/jquery-smartmenu.js',
			array(
				'jquery',
			),
			'1.0.1',
			true
		);

		wp_register_script(
			'pp-advanced-menu',
			POWERPACK_ELEMENTS_URL . 'assets/js/frontend-advanced-menu.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_register_script(
			'pp-timeline',
			POWERPACK_ELEMENTS_URL . 'assets/js/frontend-timeline.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_register_script(
			'scotch-panels',
			POWERPACK_ELEMENTS_URL . 'assets/lib/scotchPanels.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_register_script(
			'tablesaw',
			POWERPACK_ELEMENTS_URL . 'assets/lib/tablesaw/tablesaw.jquery.js',
			array(
				'jquery',
			),
			'3.0.3',
			true
		);

		wp_register_script(
			'tablesaw-init',
			POWERPACK_ELEMENTS_URL . 'assets/lib/tablesaw/tablesaw-init.js',
			array(
				'jquery',
			),
			'3.0.3',
			true
		);

		wp_register_script(
			'isotope',
			POWERPACK_ELEMENTS_URL . 'assets/lib/isotope/isotope.pkgd' . $suffix . '.js',
			array(
				'jquery',
			),
			'0.5.3',
			true
		);

		wp_register_script(
			'tilt',
			POWERPACK_ELEMENTS_URL . 'assets/lib/tilt/tilt.jquery' . $suffix . '.js',
			array(
				'jquery',
			),
			'1.1.19',
			true
		);

		wp_register_script(
			'jquery-resize',
			POWERPACK_ELEMENTS_URL . 'assets/lib/jquery-resize/jquery.resize' . $suffix . '.js',
			array(
				'jquery',
			),
			'0.5.3',
			true
		);

		wp_register_script(
			'pp-justified-gallery',
			POWERPACK_ELEMENTS_URL . 'assets/lib/justified-gallery/jquery.justifiedGallery.min.js',
			array(
				'jquery',
			),
			'3.7.0',
			true
		);

		wp_register_script(
			'pp-offcanvas-content',
			POWERPACK_ELEMENTS_URL . 'assets/js/frontend-offcanvas-content.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_register_script(
			'pp-tooltip',
			POWERPACK_ELEMENTS_URL . 'assets/js/tooltip.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_register_script(
			'jquery-fancybox',
			POWERPACK_ELEMENTS_URL . 'assets/lib/fancybox/jquery.fancybox' . $suffix . '.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_register_script(
			'twitter-widgets',
			POWERPACK_ELEMENTS_URL . 'assets/js/twitter-widgets.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);

		wp_register_script(
			'powerpack-pp-posts',
			POWERPACK_ELEMENTS_URL . 'assets/js/pp-posts.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_localize_script(
			'powerpack-pp-posts',
			'pp_posts_script',
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'posts_nonce' => wp_create_nonce( 'pp-posts-widget-nonce' ),
			)
		);

		wp_register_script(
			'powerpack-devices',
			POWERPACK_ELEMENTS_URL . 'assets/js/frontend-devices.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_register_script(
			'powerpack-frontend',
			POWERPACK_ELEMENTS_URL . 'assets/js/frontend.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		if ( is_pp_woocommerce() ) {
			wp_register_style(
				'pp-woocommerce',
				POWERPACK_ELEMENTS_URL . 'assets/css/pp-woocommerce.css',
				array(),
				POWERPACK_ELEMENTS_VER
			);

			wp_register_script(
				'pp-mini-cart',
				POWERPACK_ELEMENTS_URL . 'assets/js/frontend-mini-cart.js',
				array(
					'jquery',
				),
				POWERPACK_ELEMENTS_VER,
				true
			);

			wp_register_script(
				'pp-woocommerce',
				POWERPACK_ELEMENTS_URL . 'assets/js/pp-woocommerce.js',
				array(
					'jquery',
				),
				POWERPACK_ELEMENTS_VER,
				true
			);

			$pp_woo_localize = apply_filters(
				'pp_woo_elements_js_localize',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);
			wp_localize_script( 'pp-woocommerce', 'pp_woo_products_script', $pp_woo_localize );
		}

		wp_register_script(
			'particles',
			POWERPACK_ELEMENTS_URL . 'assets/lib/particles/particles.min.js',
			array(
				'jquery',
			),
			'2.0.0',
			true
		);
		wp_register_script(
			'three-r92',
			POWERPACK_ELEMENTS_URL . 'assets/lib/three-vanta/three.r92.min.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);
		wp_register_script(
			'vanta-birds',
			POWERPACK_ELEMENTS_URL . 'assets/lib/three-vanta/vanta.birds.min.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);
		wp_register_script(
			'vanta-dots',
			POWERPACK_ELEMENTS_URL . 'assets/lib/three-vanta/vanta.dots.min.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);
		wp_register_script(
			'vanta-fog',
			POWERPACK_ELEMENTS_URL . 'assets/lib/three-vanta/vanta.fog.min.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);
		wp_register_script(
			'vanta-net',
			POWERPACK_ELEMENTS_URL . 'assets/lib/three-vanta/vanta.net.min.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);
		wp_register_script(
			'vanta-waves',
			POWERPACK_ELEMENTS_URL . 'assets/lib/three-vanta/vanta.waves.min.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);
		wp_register_script(
			'pp-bg-effects',
			POWERPACK_ELEMENTS_URL . 'assets/js/pp-bg-effects.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);
		wp_register_script(
			'pp-treeview',
			POWERPACK_ELEMENTS_URL . 'assets/js/jquery.treeview.js',
			array(
				'jquery',
			),
			'1.0.0',
			true
		);
		wp_register_script(
			'pp-treeview',
			POWERPACK_ELEMENTS_URL . 'assets/js/jquery.treeview.js',
			[
				'jquery',
			],
			'1.0.0',
			true
		);

		$pp_localize = apply_filters(
			'pp_elements_js_localize',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
		wp_localize_script( 'jquery', 'pp', $pp_localize );
	}

	/**
	 * Enqueue frontend styles
	 *
	 * @since 1.3.3
	 *
	 * @access public
	 */
	public function enqueue_frontend_styles() {
		$debug_suffix     = ( PP_Helper::is_script_debug() ) ? '' : '.min';
		$direction_suffix = is_rtl() ? '-rtl' : '';
		$suffix           = $direction_suffix . $debug_suffix;
		$path             = ( PP_Helper::is_script_debug() ) ? 'assets/css/' : 'assets/css/min/';

		wp_enqueue_style(
			'powerpack-frontend',
			POWERPACK_ELEMENTS_URL . $path . 'frontend' . $suffix . '.css',
			array(),
			POWERPACK_ELEMENTS_VER
		);

		if ( class_exists( 'GFCommon' ) ) {
			$gf_forms = \RGFormsModel::get_forms( null, 'title' );
			foreach ( $gf_forms as $form ) {
				if ( '0' !== $form->id ) {
					wp_enqueue_script( 'gform_gravityforms' );
					gravity_form_enqueue_scripts( $form->id );
				}
			}
		}

		if ( class_exists( '\Ninja_Forms' ) && class_exists( '\NF_Display_Render' ) ) {
			add_action(
				'elementor/preview/enqueue_styles',
				function () {
					ob_start();
					\NF_Display_Render::localize( 0 );
					ob_clean();

					wp_add_inline_script( 'nf-front-end', 'var nfForms = nfForms || [];' );
				}
			);
		}

		if ( function_exists( 'wpforms' ) ) {
			wpforms()->frontend->assets_css();
		}
	}

	/**
	 * Enqueue frontend scripts
	 *
	 * @since 1.3.3
	 *
	 * @access public
	 */
	public function enqueue_frontend_scripts() {
	}

	/**
	 * Enqueue editor styles
	 *
	 * @since 1.3.3
	 *
	 * @access public
	 */
	public function enqueue_editor_styles() {
		wp_enqueue_style(
			'powerpack-icons',
			POWERPACK_ELEMENTS_URL . 'assets/lib/ppicons/css/powerpack-icons.css',
			array(),
			POWERPACK_ELEMENTS_VER
		);

		wp_enqueue_style(
			'powerpack-editor',
			POWERPACK_ELEMENTS_URL . 'assets/css/editor.css',
			array(),
			POWERPACK_ELEMENTS_VER
		);

		wp_enqueue_style( 'pp-hamburgers' );
	}

	/**
	 * Enqueue editor scripts
	 *
	 * @since 1.3.3
	 *
	 * @access public
	 */
	public function enqueue_editor_scripts() {
		wp_enqueue_script(
			'powerpack-editor',
			POWERPACK_ELEMENTS_URL . 'assets/js/editor.js',
			array(
				'jquery',
			),
			POWERPACK_ELEMENTS_VER,
			true
		);

		wp_enqueue_script(
			'pp-magnific-popup'
		);
	}

	/**
	 * Enqueue preview styles
	 *
	 * @since 1.3.8
	 *
	 * @access public
	 */
	public function enqueue_editor_preview_styles() {
		wp_enqueue_style(
			'powerpack-editor',
			POWERPACK_ELEMENTS_URL . 'assets/css/editor.css',
			array(),
			POWERPACK_ELEMENTS_VER
		);

		if ( is_pp_woocommerce() ) {
			wp_enqueue_style( 'pp-woocommerce' );
		}
		wp_enqueue_style( 'pp-hamburgers' );
		wp_enqueue_style( 'odometer' );
		wp_enqueue_style( 'tablesaw' );
		wp_enqueue_style( 'pp-magnific-popup' );
		wp_enqueue_style( 'fancybox' );
		wp_enqueue_style( 'pp-twentytwenty' );

		if ( function_exists( 'wpFluentForm' ) ) {
			wp_enqueue_style(
				'fluent-form-styles',
				WP_PLUGIN_URL . '/fluentform/public/css/fluent-forms-public.css',
				array(),
				FLUENTFORM_VERSION
			);

			wp_enqueue_style(
				'fluentform-public-default',
				WP_PLUGIN_URL . '/fluentform/public/css/fluentform-public-default.css',
				array(),
				FLUENTFORM_VERSION
			);
		}

		$extensions         = get_option( 'pp_elementor_extensions' );
		$background_effects = ( is_array( $extensions ) && in_array( 'pp-background-effects', $extensions ) );

		if ( $background_effects ) {
			wp_enqueue_script( 'particles' );
			wp_enqueue_script( 'three-r92' );
			wp_enqueue_script( 'vanta-birds' );
			wp_enqueue_script( 'vanta-dots' );
			wp_enqueue_script( 'vanta-fog' );
			wp_enqueue_script( 'vanta-net' );
			wp_enqueue_script( 'vanta-waves' );
		}
	}

	/**
	 * Register Group Controls
	 *
	 * @since 1.1.4
	 */
	public function include_group_controls() {
		// Include Control Groups
		require POWERPACK_ELEMENTS_PATH . 'includes/controls/groups/transition.php';
		require POWERPACK_ELEMENTS_PATH . 'includes/controls/groups/toc_typo_control.php';

		// Add Control Groups
		\Elementor\Plugin::instance()->controls_manager->add_group_control( 'pp-transition', new Group_Control_Transition() );
		\Elementor\Plugin::instance()->controls_manager->add_group_control( 'pp-toc-typography-control', new Group_Control_Toc() );
	}

	/**
	 * Register Controls
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 */
	public function register_controls() {

		// Include Controls
		require POWERPACK_ELEMENTS_PATH . 'includes/controls/query.php';

		// Register Controls
		\Elementor\Plugin::instance()->controls_manager->register_control( 'pp-query', new Control_Query() );
	}

	public function elementor_init() {
		$this->includes();

		$this->_extensions_manager = new Extensions_Manager();
		$this->modules_manager     = new Modules_Manager();

		if ( empty( $this->_settings ) && class_exists( 'PowerpackElements\\Classes\\PP_Admin_Settings' ) ) {
			$this->_settings = Classes\PP_Admin_Settings::get_settings();
		}

		$title = 'PowerPack Elements';
		if ( ! empty( $this->_settings ) && isset( $this->_settings['admin_label'] ) ) {
			$title = ! empty( $this->_settings['admin_label'] ) ? $this->_settings['admin_label'] : $title;
		}

		// Add element category in panel
		\Elementor\Plugin::instance()->elements_manager->add_category(
			'powerpack-elements', // This is the name of your addon's category and will be used to group your widgets/elements in the Edit sidebar pane!
			array(
				'title' => $title, // The title of your modules category - keep it simple and short!
				'icon'  => 'font',
			),
			1
		);

		// Add element category in panel
		\Elementor\Plugin::instance()->elements_manager->add_category(
			'powerpack-woocommerce', // This is the name of your addon's category and will be used to group your widgets/elements in the Edit sidebar pane!
			array(
				'title' => 'PowerPack - WooCommerce', // The title of your modules category - keep it simple and short!
				'icon'  => 'font',
			),
			1
		);
	}

	protected function add_actions() {
		add_action( 'elementor/init', array( $this, 'elementor_init' ) );

		add_action( 'elementor/controls/controls_registered', array( $this, 'register_controls' ) );
		add_action( 'elementor/controls/controls_registered', array( $this, 'include_group_controls' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'register_style_scripts' ) );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'register_style_scripts' ) );
		add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'register_style_scripts' ) );

		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ) );

		add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue_editor_preview_styles' ) );

		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_frontend_styles' ) );
	}

	/**
	 * Plugin constructor.
	 */
	private function __construct() {
		spl_autoload_register( array( $this, 'autoload' ) );

		$this->add_actions();
	}

}

if ( ! defined( 'POWERPACK_ELEMENTS_TESTS' ) ) {
	// In tests we run the instance manually.
	Powerpackplugin::instance();
}
