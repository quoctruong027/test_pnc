<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
define( 'VI_WPRODUCTBUILDER_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-product-builder" . DIRECTORY_SEPARATOR );
define( 'VI_WPRODUCTBUILDER_ADMIN', VI_WPRODUCTBUILDER_DIR . "admin" . DIRECTORY_SEPARATOR );
define( 'VI_WPRODUCTBUILDER_FRONTEND', VI_WPRODUCTBUILDER_DIR . "frontend" . DIRECTORY_SEPARATOR );
define( 'VI_WPRODUCTBUILDER_LANGUAGES', VI_WPRODUCTBUILDER_DIR . "languages" . DIRECTORY_SEPARATOR );
define( 'VI_WPRODUCTBUILDER_INCLUDES', VI_WPRODUCTBUILDER_DIR . "includes" . DIRECTORY_SEPARATOR );
//$plugin_url = plugins_url( 'woocommerce-product-builder' );
$plugin_url = plugins_url( '', __FILE__ );
$plugin_url = str_replace( '/includes', '', $plugin_url );
define( 'VI_WPRODUCTBUILDER_CSS', $plugin_url . "/css/" );
define( 'VI_WPRODUCTBUILDER_CSS_DIR', VI_WPRODUCTBUILDER_DIR . "css" . DIRECTORY_SEPARATOR );
define( 'VI_WPRODUCTBUILDER_JS', $plugin_url . "/js/" );
define( 'VI_WPRODUCTBUILDER_JS_DIR', VI_WPRODUCTBUILDER_DIR . "js" . DIRECTORY_SEPARATOR );
define( 'VI_WPRODUCTBUILDER_IMAGES', $plugin_url . "/images/" );

// Override default templates path to get them from the active theme folder
//define( 'VI_WPRODUCTBUILDER_TEMPLATES', get_template_directory() . DIRECTORY_SEPARATOR . 'templates/woocommerce-product-builder' . DIRECTORY_SEPARATOR );
define( 'VI_WPRODUCTBUILDER_TEMPLATES_CUSTOM', get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'templates/woocommerce-product-builder' . DIRECTORY_SEPARATOR );

// Check if templates folder exists in current active theme and get the templates from the theme
if ( file_exists( VI_WPRODUCTBUILDER_TEMPLATES_CUSTOM . 'single-product-builder.php' ) ) {
	define( 'VI_WPRODUCTBUILDER_TEMPLATES', get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'templates/woocommerce-product-builder' . DIRECTORY_SEPARATOR );
} else {
	define( 'VI_WPRODUCTBUILDER_TEMPLATES', VI_WPRODUCTBUILDER_DIR . "templates" . DIRECTORY_SEPARATOR );
}
define( 'VI_WPRODUCTBUILDER_SHORTCODE_TEMPS', VI_WPRODUCTBUILDER_TEMPLATES . "shortcode" . DIRECTORY_SEPARATOR );

/*Include functions file*/
if ( is_file( VI_WPRODUCTBUILDER_INCLUDES . "functions.php" ) ) {
	require_once VI_WPRODUCTBUILDER_INCLUDES . "functions.php";
}

/*Update file*/
if ( is_file( VI_WPRODUCTBUILDER_INCLUDES . "update.php" ) ) {
	require_once VI_WPRODUCTBUILDER_INCLUDES . "update.php";
}
/*Check update file*/
if ( is_file( VI_WPRODUCTBUILDER_INCLUDES . "check_update.php" ) ) {
	require_once VI_WPRODUCTBUILDER_INCLUDES . "check_update.php";
}

/*Include functions file*/
if ( is_file( VI_WPRODUCTBUILDER_INCLUDES . "mobile_detect.php" ) ) {
	require_once VI_WPRODUCTBUILDER_INCLUDES . "mobile_detect.php";
}
/*Include functions file*/
if ( is_file( VI_WPRODUCTBUILDER_INCLUDES . "data.php" ) ) {
	require_once VI_WPRODUCTBUILDER_INCLUDES . "data.php";
}
/*Include functions file*/
if ( is_file( VI_WPRODUCTBUILDER_INCLUDES . "support.php" ) ) {
	require_once VI_WPRODUCTBUILDER_INCLUDES . "support.php";
}

/*Include elementor file*/
if ( is_file( VI_WPRODUCTBUILDER_INCLUDES . "elementor/elementor.php" ) ) {
	require_once VI_WPRODUCTBUILDER_INCLUDES . "elementor/elementor.php";
}

vi_include_folder( VI_WPRODUCTBUILDER_ADMIN, 'VI_WPRODUCTBUILDER_Admin_' );
vi_include_folder( VI_WPRODUCTBUILDER_FRONTEND, 'VI_WPRODUCTBUILDER_FrontEnd_' );

if ( class_exists( 'VillaTheme_Support_Pro' ) ) {
	new VillaTheme_Support_Pro(
		array(
			'support'   => 'https://villatheme.com/supports/forum/plugins/woocommerce-product-builder/',
			'docs'      => 'http://docs.villatheme.com/?item=woocommerce-product-builder',
			'review'    => 'https://codecanyon.net/downloads',
			'css'       => VI_WPRODUCTBUILDER_CSS,
			'image'     => VI_WPRODUCTBUILDER_IMAGES,
			'slug'      => 'woocommerce-product-builder',
			'menu_slug' => 'edit.php?post_type=woo_product_builder',
			'version'   => VI_WPRODUCTBUILDER_VERSION
		)
	);
}
