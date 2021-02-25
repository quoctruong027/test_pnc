<?php
/**
 * Admin View: Performance
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap woocommerce prl-performance-wrap">

	<h1>
		<?php esc_html_e( 'Performance', 'woocommerce-product-recommendations' ); ?>
		<div class="range_container">
			<?php echo sprintf( esc_html__( '%1$s (%2$s &mdash; %3$s) vs. previous week', 'woocommerce-product-recommendations' ), '<span class="current_period">' . esc_html__( 'Last 7 days', 'woocommerce-product-recommendations' ) . '</span>', date_i18n( 'M j', $range[ 'start_date' ] ), date_i18n( 'M j', strtotime( '-1 day', $range[ 'end_date' ] ) ) ); ?>
		</div>
	</h1>

	<br class="clear">

	<div class="wc-prl-perf">

		<?php if ( $glance_data[ 'gross' ] ): ?>
			<a class="wc-prl-perf__tab" href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=prl_recommendations&report=sales' ) ?>">
				<div class="title"><?php esc_html_e( 'Gross Revenue', 'woocommerce-product-recommendations' ) ?></div>
				<h4><?php echo wc_prl_print_currency_amount( $glance_data[ 'gross' ][ 'current' ] ) ?></h4>
				<?php self::print_difference( $glance_data[ 'gross' ][ 'current' ], $glance_data[ 'gross' ][ 'previous' ] ) ?>
				<div class="previous_data">
					<?php esc_html_e( 'Previous week:', 'woocommerce-product-recommendations' ) ?>
					<span><?php echo wc_prl_print_currency_amount( $glance_data[ 'gross' ][ 'previous' ] ) ?></span>
				</div>

				<div class="sparkline_container">
					<?php
					echo '<span style="display: block;width: 100%;height: 25px;" class="wc_sparkline bars tips" data-color="#ccd0d4" data-barwidth="' . 60 * 60 * 16 * 1000 . '" data-sparkline="' . WC_PRL_Core_Compatibility::wc_esc_json( wp_json_encode( $glance_data[ 'gross' ][ 'data' ] ) ) . '" data-tip="' . esc_attr__( 'Gross Revenue in last 7 days', 'woocommerce-product-recommendations' ) . '"></span>';
					?>
				</div>
			</a>
		<?php endif; ?>

		<?php if ( $glance_data[ 'net' ] ): ?>
			<a class="wc-prl-perf__tab" href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=prl_recommendations&report=sales' ) ?>">
				<div class="title"><?php esc_html_e( 'Net Revenue', 'woocommerce-product-recommendations' ) ?></div>
				<h4><?php echo wc_prl_print_currency_amount( $glance_data[ 'net' ][ 'current' ] ) ?></h4>
				<?php self::print_difference( $glance_data[ 'net' ][ 'current' ], $glance_data[ 'net' ][ 'previous' ] ) ?>
				<div class="previous_data">
					<?php esc_html_e( 'Previous week:', 'woocommerce-product-recommendations' ) ?>
					<span><?php echo wc_prl_print_currency_amount( $glance_data[ 'net' ][ 'previous' ] ) ?></span>
				</div>

				<div class="sparkline_container">
					<?php
					echo '<span style="display: block;width: 100%;height: 25px;" class="wc_sparkline bars tips" data-color="#ccd0d4" data-barwidth="' . 60 * 60 * 16 * 1000 . '" data-sparkline="' . WC_PRL_Core_Compatibility::wc_esc_json( wp_json_encode( $glance_data[ 'net' ][ 'data' ] ) ) . '" data-tip="' . esc_attr__( 'Net Revenue in last 7 days', 'woocommerce-product-recommendations' ) . '"></span>';
					?>
				</div>
			</a>
		<?php endif; ?>

		<?php if ( $glance_data[ 'views' ] ): ?>
			<a class="wc-prl-perf__tab" href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=prl_recommendations&report=events' ) ?>">

				<div class="title"><?php esc_html_e( 'Unique Views', 'woocommerce-product-recommendations' ) ?></div>
				<h4><?php echo $glance_data[ 'views' ][ 'current' ] ?></h4>
				<?php self::print_difference( $glance_data[ 'views' ][ 'current' ], $glance_data[ 'views' ][ 'previous' ] ) ?>
				<div class="previous_data">
					<?php esc_html_e( 'Previous week:', 'woocommerce-product-recommendations' ) ?>
					<span><?php echo $glance_data[ 'views' ][ 'previous' ] ?></span>
				</div>

				<div class="sparkline_container">
					<?php
					echo '<span style="display: block;width: 100%;height: 25px;" class="wc_sparkline bars tips" data-color="#ccd0d4" data-barwidth="' . 60 * 60 * 16 * 1000 . '" data-sparkline="' . WC_PRL_Core_Compatibility::wc_esc_json( wp_json_encode( $glance_data[ 'views' ][ 'data' ] ) ) . '" data-tip="' . esc_attr__( 'Unique Views in last 7 days', 'woocommerce-product-recommendations' ) . '"></span>';
					?>
				</div>

			</a>
		<?php endif; ?>

		<?php if ( $glance_data[ 'clicks' ] ): ?>
			<a class="wc-prl-perf__tab" href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=prl_recommendations&report=events' ) ?>">

				<div class="title"><?php esc_html_e( 'Unique Clicks', 'woocommerce-product-recommendations' ) ?></div>
				<h4><?php echo $glance_data[ 'clicks' ][ 'current' ] ?></h4>
				<?php self::print_difference( $glance_data[ 'clicks' ][ 'current' ], $glance_data[ 'clicks' ][ 'previous' ] ) ?>
				<div class="previous_data">
					<?php esc_html_e( 'Previous week:', 'woocommerce-product-recommendations' ) ?>
					<span><?php echo $glance_data[ 'clicks' ][ 'previous' ] ?></span>
				</div>

				<div class="sparkline_container">
					<?php
					echo '<span style="display: block;width: 100%;height: 25px;" class="wc_sparkline bars tips" data-color="#ccd0d4" data-barwidth="' . 60 * 60 * 16 * 1000 . '" data-sparkline="' . WC_PRL_Core_Compatibility::wc_esc_json( wp_json_encode( $glance_data[ 'clicks' ][ 'data' ] ) ) . '" data-tip="' . esc_attr__( 'Unique Clicks in last 7 days', 'woocommerce-product-recommendations' ) . '"></span>';
					?>
				</div>

			</a>
		<?php endif; ?>

		<?php if ( $glance_data[ 'conversions' ] ): ?>
			<a class="wc-prl-perf__tab" href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=prl_recommendations&report=events' ) ?>">

				<div class="title"><?php esc_html_e( 'Conversions', 'woocommerce-product-recommendations' ) ?></div>
				<h4><?php echo $glance_data[ 'conversions' ][ 'current' ] ?></h4>
				<?php self::print_difference( $glance_data[ 'conversions' ][ 'current' ], $glance_data[ 'conversions' ][ 'previous' ] ) ?>
				<div class="previous_data">
					<?php esc_html_e( 'Previous week:', 'woocommerce-product-recommendations' ) ?>
					<span><?php echo $glance_data[ 'conversions' ][ 'previous' ] ?></span>
				</div>

				<div class="sparkline_container">
					<?php
					echo '<span style="display: block;width: 100%;height: 25px;" class="wc_sparkline bars tips" data-color="#ccd0d4" data-barwidth="' . 60 * 60 * 16 * 1000 . '" data-sparkline="' . WC_PRL_Core_Compatibility::wc_esc_json( wp_json_encode( $glance_data[ 'conversions' ][ 'data' ] ) ) . '" data-tip="' . esc_attr__( 'Conversions in last 7 days', 'woocommerce-product-recommendations' ) . '"></span>';
					?>
				</div>

			</a>
		<?php endif; ?>

		<?php if ( $glance_data[ 'clicks_per_view' ] ): ?>
			<a class="wc-prl-perf__tab" href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=prl_recommendations&report=conversions' ) ?>">

				<div class="title"><?php esc_html_e( 'Clicks per view', 'woocommerce-product-recommendations' ) ?></div>
				<h4><?php echo $glance_data[ 'clicks_per_view' ][ 'current' ] ?></h4>
				<?php self::print_difference( $glance_data[ 'clicks_per_view' ][ 'current' ], $glance_data[ 'clicks_per_view' ][ 'previous' ] ) ?>
				<div class="previous_data">
					<?php esc_html_e( 'Previous week:', 'woocommerce-product-recommendations' ) ?>
					<span><?php echo $glance_data[ 'clicks_per_view' ][ 'previous' ] ?></span>
				</div>

				<div class="sparkline_container">
					<?php
					echo '<span style="display: block;width: 100%;height: 25px;" class="wc_sparkline bars tips" data-color="#ccd0d4" data-barwidth="' . 60 * 60 * 16 * 1000 . '" data-sparkline="' . WC_PRL_Core_Compatibility::wc_esc_json( wp_json_encode( $glance_data[ 'clicks_per_view' ][ 'data' ] ) ) . '" data-tip="' . esc_attr__( 'Clicks per View in last 7 days', 'woocommerce-product-recommendations' ) . '"></span>';
					?>
				</div>

			</a>
		<?php endif; ?>

		<?php if ( $glance_data[ 'cr' ] ): ?>
			<a class="wc-prl-perf__tab" href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=prl_recommendations&report=conversions' ) ?>">

				<div class="title"><?php esc_html_e( 'Conversion rate', 'woocommerce-product-recommendations' ) ?></div>
				<h4><?php echo $glance_data[ 'cr' ][ 'current' ] ?>%</h4>
				<?php self::print_difference( $glance_data[ 'cr' ][ 'current' ], $glance_data[ 'cr' ][ 'previous' ] ) ?>
				<div class="previous_data">
					<?php esc_html_e( 'Previous week:', 'woocommerce-product-recommendations' ) ?>
					<span><?php echo $glance_data[ 'cr' ][ 'previous' ] ?>%</span>
				</div>

				<div class="sparkline_container">
					<?php
					echo '<span style="display: block;width: 100%;height: 25px;" class="wc_sparkline bars tips" data-color="#ccd0d4" data-barwidth="' . 60 * 60 * 16 * 1000 . '" data-sparkline="' . WC_PRL_Core_Compatibility::wc_esc_json( wp_json_encode( $glance_data[ 'cr' ][ 'data' ] ) ) . '" data-tip="' . esc_attr__( 'Conversion Rate in last 7 days', 'woocommerce-product-recommendations' ) . '"></span>';
					?>
				</div>

			</a>
		<?php endif; ?>

	</div>

	<h2><?php esc_html_e( 'Top Products', 'woocommerce-product-recommendations' ); ?></h2>

	<div class="wc-prl-perf-top">
		<table>
			<thead>
				<tr class="head">
					<th colspan="2"><?php esc_html_e( 'Top Grossing', 'woocommerce-product-recommendations' ); ?></th>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Product', 'woocommerce-product-recommendations' ); ?></th>
					<th><?php esc_html_e( 'Revenue', 'woocommerce-product-recommendations' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( ! empty( $top_products[ 'top_grossing' ] ) ) {
					foreach ( $top_products[ 'top_grossing' ] as $index => $data ) {
						$product = self::get_product( absint( $data[ 'product_id' ] ) );
						if ( ! ( $product instanceof WC_Product ) ) {
							continue;
						}
						echo '<tr>';
						echo '<td><a href="' . admin_url( "admin.php?show_products%5B0%5D={$product->get_id()}&page=wc-reports&tab=prl_recommendations&report=sales" ) . '">' . $product->get_title() . '</a></td>';
						echo '<td>' . wc_prl_print_currency_amount( $data[ 'rate' ] ) . '</td>';
						echo '</tr>';
					}
				} else {
					echo '<tr><td colspan="2" class="empty">' . esc_html__( 'No data recorded in the last 7 days.', 'woocommerce-product-recommendations' ) . '</td></tr>';
				}
				?>
			</tbody>
		</table>
		<table>
			<thead>
				<tr class="head">
					<th colspan="2"><?php esc_html_e( 'Most Clicked', 'woocommerce-product-recommendations' ); ?></th>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Product', 'woocommerce-product-recommendations' ); ?></th>
					<th><?php esc_html_e( 'Clicks', 'woocommerce-product-recommendations' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( ! empty( $top_products[ 'most_clicked' ] ) ) {
					foreach ( $top_products[ 'most_clicked' ] as $index => $data ) {
						$product = self::get_product( absint( $data[ 'product_id' ] ) );
						if ( ! ( $product instanceof WC_Product ) ) {
							continue;
						}
						echo '<tr>';
						echo '<td><a href="' . admin_url( "admin.php?show_products%5B0%5D={$product->get_id()}&page=wc-reports&tab=prl_recommendations&report=events" ) . '">' . $product->get_title() . '</a></td>';
						echo '<td>' . $data[ 'rate' ] . '</td>';
						echo '</tr>';
					}
				} else {
					echo '<tr><td colspan="2" class="empty">' . esc_html__( 'No data recorded in the last 7 days.', 'woocommerce-product-recommendations' ) . '</td></tr>';
				}
				?>
			</tbody>
		</table>
		<table>
			<thead>
				<tr class="head">
					<th colspan="2"><?php esc_html_e( 'Highest Converting', 'woocommerce-product-recommendations' ); ?></th>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Product', 'woocommerce-product-recommendations' ); ?></th>
					<th><?php esc_html_e( 'Conversion', 'woocommerce-product-recommendations' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( ! empty( $top_products[ 'best_converting' ] ) ) {
					foreach ( $top_products[ 'best_converting' ] as $index => $data ) {
						$product = self::get_product( absint( $data[ 'product_id' ] ) );
						if ( ! ( $product instanceof WC_Product ) ) {
							continue;
						}
						echo '<tr>';
						echo '<td><a href="' . admin_url( "admin.php?show_products%5B0%5D={$product->get_id()}&page=wc-reports&tab=prl_recommendations&report=conversions" ) . '">' . $product->get_title() . '</a></td>';
						echo '<td>' . wc_format_decimal( $data[ 'cr' ], 0 ) . '%</td>';
						echo '</tr>';
					}
				} else {
					echo '<tr><td colspan="2" class="empty">' . esc_html__( 'No data recorded in the last 7 days.', 'woocommerce-product-recommendations' ) . '</td></tr>';
				}
				?>
			</tbody>
		</table>
	</div>

	<h2><?php esc_html_e( 'Top Locations', 'woocommerce-product-recommendations' ); ?></h2>

	<div class="wc-prl-perf-top">
		<table>
			<thead>
				<tr class="head">
					<th colspan="2"><?php esc_html_e( 'Top Grossing', 'woocommerce-product-recommendations' ); ?></th>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Location', 'woocommerce-product-recommendations' ); ?></th>
					<th><?php esc_html_e( 'Revenue', 'woocommerce-product-recommendations' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( ! empty( $top_locations[ 'top_grossing' ] ) ) {
					foreach ( $top_locations[ 'top_grossing' ] as $index => $data ) {
						$location = self::get_location_by_hash( $data[ 'location_hash' ] );
						echo '<tr>';
						echo '<td><a href="' . admin_url( "admin.php?show_locations%5B0%5D={$location[ 'hook' ]}&page=wc-reports&tab=prl_recommendations&report=sales" ) . '">' . $location[ 'title' ] . ' - ' . $location[ 'label' ] .'</a></td>';
						echo '<td>' . wc_prl_print_currency_amount( $data[ 'rate' ] ) . '</td>';
						echo '</tr>';
					}
				} else {
					echo '<tr><td colspan="2" class="empty">' . esc_html__( 'No data recorded in the last 7 days.', 'woocommerce-product-recommendations' ) . '</td></tr>';
				}
				?>
			</tbody>
		</table>
		<table>
			<thead>
				<tr class="head">
					<th colspan="2"><?php esc_html_e( 'Most Clicked', 'woocommerce-product-recommendations' ); ?></th>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Location', 'woocommerce-product-recommendations' ); ?></th>
					<th><?php esc_html_e( 'Clicks', 'woocommerce-product-recommendations' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( ! empty( $top_locations[ 'most_clicked' ] ) ) {
					foreach ( $top_locations[ 'most_clicked' ] as $index => $data ) {
						$location = self::get_location_by_hash( $data[ 'location_hash' ] );
						echo '<tr>';
						echo '<td><a href="' . admin_url( "admin.php?show_locations%5B0%5D={$location[ 'hook' ]}&page=wc-reports&tab=prl_recommendations&report=events" ) . '">' . $location[ 'title' ] . ' - ' . $location[ 'label' ] .'</a></td>';
						echo '<td>' . $data[ 'rate' ] . '</td>';
						echo '</tr>';
					}
				} else {
					echo '<tr><td colspan="2" class="empty">' . esc_html__( 'No data recorded in the last 7 days.', 'woocommerce-product-recommendations' ) . '</td></tr>';
				}
			?>
			</tbody>
		</table>
		<table>
			<thead>
				<tr class="head">
					<th colspan="2"><?php esc_html_e( 'Highest Converting', 'woocommerce-product-recommendations' ); ?></th>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Location', 'woocommerce-product-recommendations' ); ?></th>
					<th><?php esc_html_e( 'Conversion', 'woocommerce-product-recommendations' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( ! empty( $top_locations[ 'best_converting' ] ) ) {
					foreach ( $top_locations[ 'best_converting' ] as $index => $data ) {
						$location = self::get_location_by_hash( $data[ 'location_hash' ] );
						echo '<tr>';
						echo '<td><a href="' . admin_url( "admin.php?show_locations%5B0%5D={$location[ 'hook' ]}&page=wc-reports&tab=prl_recommendations&report=conversions" ) . '">' . $location[ 'title' ] . ' - ' . $location[ 'label' ] .'</a></td>';
						echo '<td>' . wc_format_decimal( $data[ 'cr' ], 0 ) . '%</td>';
						echo '</tr>';
					}
				} else {
					echo '<tr><td colspan="2" class="empty">' . esc_html__( 'No data recorded in the last 7 days.', 'woocommerce-product-recommendations' ) . '</td></tr>';
				}
				?>
			</tbody>
		</table>
	</div>

	<?php self::get_location_by_hash('asd'); ?>
</div>
