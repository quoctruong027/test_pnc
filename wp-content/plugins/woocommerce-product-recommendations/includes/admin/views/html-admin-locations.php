<?php
/**
 * Admin View: Locations
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap woocommerce prl-locations-wrap">

	<h1><?php echo __( 'Locations', 'woocommerce-product-recommendations' ); ?></h1>

	<?php include dirname( __FILE__ ) . '/partials/html-admin-locations-tabs.php'; ?>

	<br class="clear">

	<?php if ( $table->has_items() ) { ?>

		<h2><?php esc_html_e( 'Deploy Engine', 'woocommerce-product-recommendations' ); ?></h2>
		<div class="prl-desciption"><?php printf( __( 'To display product recommendations, deploy an Engine to a Location or create a <a href="%s">new Engine</a>.', 'woocommerce-product-recommendations' ), admin_url( 'post-new.php?post_type=prl_engine' ) ); ?></div>

		<div class="quick-deploy">
			<div class="quick-deploy__search" data-action="<?php echo admin_url( 'admin.php?page=prl_locations&section=deploy&quick=1&engine=%%engine_id%%' ); ?>">
				<select class="wc-engine-search" data-placeholder="<?php _e( 'Search for an Engine&hellip;', 'woocommerce-product-recommendations' ); ?>" data-limit="100" name="engine">
				</select>
			</div>
		</div>

	<h2><?php esc_html_e( 'Engine Deployments', 'woocommerce-product-recommendations' ); ?></h2>

	<?php } ?>

	<form id="deployments-table" method="GET">
		<?php wp_nonce_field( 'woocommerce-prl-locations' ); ?>
        <input type="hidden" name="page" value="<?php echo $_REQUEST[ 'page' ] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
