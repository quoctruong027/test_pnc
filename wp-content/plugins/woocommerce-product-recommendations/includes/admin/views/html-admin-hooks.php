<?php
/**
 * Admin View: Hooks
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap woocommerce prl-locations-wrap <?php echo WC_PRL_Core_Compatibility::get_versions_class(); ?>">

	<h1><?php echo __( 'Locations', 'woocommerce-product-recommendations' ); ?></h1>

	<?php include dirname( __FILE__ ) . '/partials/html-admin-locations-tabs.php'; ?>

	<br class="clear">

	<div class="wc-prl-hooks">
		<?php foreach ( $hooks as $hook => $data ) {
			$types   = array_map( 'wc_prl_get_engine_type_label', (array) $data[ 'engine_type' ] );
			$current = $selected_hook === $hook ? ' wc-prl-hooks__tab--active' : '';
			$url     = add_query_arg( 'hook', $hook, $base_url );
			?>
			<a href="<?php echo $url; ?>" class="wc-prl-hooks__tab<?php echo $current; ?>">
				<h4><?php echo $data[ 'label' ]; ?></h4>
				<span class="status">
					<?php
					$count = sprintf(
						' <span class="current_count">%d</span> ',
						isset( $map[ $hook ] ) ? count( $map[ $hook ][ 'deployments' ] ) : 0
					);
					echo sprintf( esc_html__( 'Deployments: %s', 'woocommerce-product-recommendations' ), $count );
					?>
				</span>
			</a>
		<?php } ?>
	</div>
	<div class="wc-prl-deployments wc-metaboxes-wrapper">

		<div class="toolbar">
			<span class="bulk_toggle_wrapper<?php echo ! isset( $map[ $selected_hook ] ) ? ' disabled' : '' ; ?>">
				<a href="#" class="expand_all"><?php esc_html_e( 'Expand all', 'woocommerce' ); ?></a>
				<a href="#" class="close_all"><?php esc_html_e( 'Close all', 'woocommerce' ); ?></a>
			</span>
		</div>

		<form method="post" id="mainform" action="<?php echo add_query_arg( 'hook', $selected_hook, $base_url ); ?>" enctype="multipart/form-data">

			<div class="wc-prl-deployments__list wc-metaboxes ui-sortable" data-filter_type="<?php echo implode( ',', $selected_hook_data[ 'engine_type' ] ) ?>">

				<?php
				if ( ! empty( $map[ $selected_hook ] ) ) {

					foreach ( $map[ $selected_hook ][ 'deployments' ] as $index => $deployment ) {

						$options                      = array();
						$options[ 'id' ]              = $deployment->get_id();
						$options[ 'engine_id' ]       = $deployment->get_engine_id();
						$options[ 'engine_type' ]     = $deployment->get_engine_type();
						$options[ 'filter_type' ]     = $selected_hook_data[ 'engine_type' ];
						$options[ 'active' ]          = $deployment->is_active() ? 'on' : 'off';
						$options[ 'display_order' ]   = $deployment->get_display_order();
						$options[ 'title' ]           = $deployment->get_title();
						$options[ 'description' ]     = $deployment->get_description();
						$options[ 'rows' ]            = absint( $deployment->get_limit() / $deployment->get_columns() );
						$options[ 'columns' ]         = $deployment->get_columns();
						$options[ 'conditions' ]      = $deployment->get_conditions_data();

						/*
						 * Check if pages are served from a cache and add a tooltip.
						 */
						if ( ( $location = WC_PRL()->locations->get_location_by_hook( $selected_hook ) ) && $location->is_cacheable() && false === wc_prl_render_using_ajax( 'edit' ) && 'cached' === WC_PRL_Notices::get_page_cache_test_result() ) {

							if ( ! empty( $options[ 'engine_id' ] ) ) {
								$options[ 'engine' ] = wc_prl_get_engine( absint( $options[ 'engine_id' ] ) );
							}

							$is_engine_dynamic         = $options[ 'engine' ] && $options[ 'engine' ]->get_dynamic_filters_data();
							$is_deployment_conditional = $deployment->get_conditions_data();

							if ( $is_engine_dynamic || $is_deployment_conditional ) {
								$options[ 'page_cache_tip' ] = __( 'This deployment generates dynamic/personalized recommendations that some visitors may not be able to see correctly. As a workaround, you may enable <strong>Deployments rendering > Use AJAX</strong> under <strong>WooCommerce > Settings > Recommendations</strong> &ndash; but please be aware that doing so will have an impact on server utilization and user experience.', 'woocommerce-product-recommendations' );
							}
						}

						// Render.
						WC_PRL()->deployments->get_admin_metaboxes_content( $index, $options, false );
					}
				}
				?>

				<div class="wc-prl-deployments__boarding <?php echo ! empty( $map[ $selected_hook ] ) ? 'wc-prl-deployments__boarding--hidden' : '' ; ?>">
					<div class="wc-prl-deployments__boarding__message">
						<h3><?php echo __( 'No Engines found', 'woocommerce-product-recommendations' ); ?></h3>
						<p><?php echo __( 'You have not added Engines to this location. Deploy an Engine now?', 'woocommerce-product-recommendations' ); ?></p>
					</div>
				</div>

			</div>

			<div class="wc-prl-deployments__list__buttons <?php echo empty( $map[ $selected_hook ] ) ? 'wc-prl-deployments__list__buttons--empty' : '' ; ?>">
				<button class="wc-prl-deployments__add button"><?php esc_html_e( 'Deploy an Engine', 'woocommerce-product-recommendations' ); ?></button>
				<button type="submit" class="wc-prl-deployments__save button button-primary"><?php esc_html_e( 'Save changes', 'woocommerce-product-recommendations' ); ?></button>
			</div>
		</form>
	</div>
</div>
