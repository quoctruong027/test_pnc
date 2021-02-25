<?php
/**
 * Status Report data.
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><table class="wc_status_table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Product Recommendations"><h2><?php esc_html_e( 'Product Recommendations', 'woocommerce-product-recommendations' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Database Version"><?php esc_html_e( 'Database version', 'woocommerce-product-recommendations' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( esc_html__( 'The version of Product Recommendations reported by the database. This should be the same as the plugin version.', 'woocommerce-product-recommendations' ) ); ?></td>
			<td>
			<?php

				if ( version_compare( $debug_data[ 'db_version' ], WC_PRL()->get_plugin_version( true ), '==' ) ) {
					echo '<mark class="yes">' . esc_html( $debug_data[ 'db_version' ] ) . '</mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html( $debug_data[ 'db_version' ] ) . ' - ' . __( 'Database version mismatch.', 'woocommerce-product-recommendations' ) . '</mark>';
				}
			?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Loopback Test"><?php esc_html_e( 'Loopback test', 'woocommerce-product-recommendations' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( esc_html__( 'Loopback requests are used by Product Recommendations to regenerate recommendations in the background.', 'woocommerce-product-recommendations' ) ); ?></td>
			<td>
			<?php

				if ( 'pass' === $debug_data[ 'loopback_test_result' ] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} elseif ( '' === $debug_data[ 'loopback_test_result' ] ) {
					echo '<mark class="no">&ndash;</mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Loopback test failed.', 'woocommerce-product-recommendations' ) . '</mark>';
				}
			?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Task Queueing Test"><?php esc_html_e( 'Task queueing test', 'woocommerce-product-recommendations' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( esc_html__( 'The task queue built into WooCommerce must be operating properly to process scheduled tasks and events.', 'woocommerce-product-recommendations' ) ); ?></td>
			<td>
			<?php

				if ( 'pass' === $debug_data[ 'queue_test_result' ] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Task queueing test failed.', 'woocommerce-product-recommendations' ) . '</mark>';
				}
			?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Page Cache Test"><?php esc_html_e( 'Page cache test', 'woocommerce-product-recommendations' ); ?>:</td>
			<td class="help"><?php echo wc_help_tip( esc_html__( 'Using a page cache ensures a snappier experience for your visitors, but also prevents some advanced features in Product Recommendations from working correctly. As a workaround, you may enable <strong>Deployments rendering > Use AJAX</strong> under <strong>WooCommerce > Settings > Recommendations</strong> – but please be aware that doing so will have an impact on server utilization and user experience.', 'woocommerce-product-recommendations' ) ); ?></td>
			<td>
			<?php

				if ( 'cached' === $debug_data[ 'page_cache_test_result' ] ) {
					echo __( 'Cache in use', 'woocommerce-product-recommendations' );
				} elseif ( 'not-cached' === $debug_data[ 'page_cache_test_result' ] ) {
					echo __( 'No cache detected', 'woocommerce-product-recommendations' );
				} elseif ( 'test-failed' === $debug_data[ 'page_cache_test_result' ] ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Test failed.', 'woocommerce-product-recommendations' ) . '</mark>';
				} else {
					echo '&ndash;';
				}
			?>
			</td>
		</tr>
	</tbody>
</table>
