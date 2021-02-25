<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-product-variations-swatches" . DIRECTORY_SEPARATOR );
define( 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DIR . "includes" . DIRECTORY_SEPARATOR );
define( 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_ADMIN', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "admin" . DIRECTORY_SEPARATOR );
define( 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_FRONTEND', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "frontend" . DIRECTORY_SEPARATOR );
define( 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_LANGUAGES', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DIR . "languages" . DIRECTORY_SEPARATOR );
define( 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_TEMPLATES', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "templates" . DIRECTORY_SEPARATOR );
$plugin_url = plugins_url( 'woocommerce-product-variations-swatches' );
$plugin_url = str_replace( '/includes', '', $plugin_url );
define( 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS', $plugin_url . "/assets/css/" );
define( 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS_DIR', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DIR . "assets/css" . DIRECTORY_SEPARATOR );
define( 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS', $plugin_url . "/assets/js/" );
define( 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_JS_DIR', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_DIR . "assets/js" . DIRECTORY_SEPARATOR );
define( 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_IMAGES', $plugin_url . "/assets/images/" );

/*Include functions file*/
if ( is_file( VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "functions.php" ) ) {
	require_once VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "functions.php";
}
if ( is_file( VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "support.php" ) ) {
	require_once VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "support.php";
}
if ( is_file( VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "check_update.php" ) ) {
	require_once VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "check_update.php";
}
if ( is_file( VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "update.php" ) ) {
	require_once VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "update.php";
}
if ( is_file( VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "data.php" ) ) {
	require_once VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_INCLUDES . "data.php";
}
villatheme_include_folder( VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_ADMIN, 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Admin_' );
villatheme_include_folder( VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_FRONTEND, 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Frontend_' );
