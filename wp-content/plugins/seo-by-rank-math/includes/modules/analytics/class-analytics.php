<?php
/**
 * The Analytics Module
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

use Exception;
use RankMath\KB;
use RankMath\Helper;
use RankMath\Module\Base;
use RankMath\Google\Api;
use RankMath\SEO_Analysis\SEO_Analyzer;
use MyThemeShop\Admin\Page;
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Conditional;
use RankMath\Google\Console;
use RankMath\Google\Authentication;
use RankMath\Schema\Admin as SchemaHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Analytics class.
 */
class Analytics extends Base {

	/**
	 * The Constructor
	 */
	public function __construct() {
		if ( Conditional::is_heartbeat() ) {
			return;
		}

		$directory = dirname( __FILE__ );
		$this->config(
			[
				'id'        => 'analytics',
				'directory' => $directory,
				'help'      => [
					'title' => esc_html__( 'Analytics', 'rank-math' ),
					'view'  => $directory . '/views/help.php',
				],
			]
		);
		parent::__construct();

		new AJAX();
		Api::get();
		Data_Fetcher::get();
		Watcher::get();
		Stats::get();

		$this->action( 'admin_notices', 'render_notice' );
		$this->action( 'rank_math/admin/enqueue_scripts', 'enqueue' );
		$this->action( 'wp_helpers_notification_dismissed', 'analytic_first_fetch_dismiss' );

		if ( is_admin() ) {
			$this->filter( 'rank_math/database/tools', 'add_tools' );
			$this->filter( 'rank_math/settings/general', 'add_settings' );

			// Show Analytics block in the Dashboard widget only if account is connected or user has permissions.
			if ( Helper::has_cap( 'analytics' ) && Authentication::is_authorized() ) {
				$this->action( 'rank_math/dashboard/widget', 'dashboard_widget', 9 );
			}
		}
	}

	/**
	 * Hide fetch notice.
	 *
	 * @param  string $notification_id Notification id.
	 */
	public function analytic_first_fetch_dismiss( $notification_id ) {
		if ( 'rank_math_analytics_first_fetch' !== $notification_id ) {
			return;
		}

		update_option( 'rank_math_analytics_first_fetch', 'hidden' );
	}

	/**
	 * Add stats into admin dashboard.
	 *
	 * @codeCoverageIgnore
	 */
	public function dashboard_widget() {
		Stats::get()->set_date_range( '-30 days' );
		$data                   = Stats::get()->get_widget();
		$analytics              = get_option( 'rank_math_google_analytic_options' );
		$is_analytics_connected = ! empty( $analytics ) && ! empty( $analytics['view_id'] );
		?>
		<h3>
			<?php esc_html_e( 'Analytics', 'rank-math' ); ?>
			<span><?php esc_html_e( 'Last 30 Days', 'rank-math' ); ?></span>
			<a href="<?php echo esc_url( Helper::get_admin_url( 'analytics' ) ); ?>" class="rank-math-view-report" title="<?php esc_html_e( 'View Report', 'rank-math' ); ?>"><i class="dashicons dashicons-ellipsis"></i></a>
		</h3>
		<div class="rank-math-dashabord-block items-4">

			<?php if ( $is_analytics_connected && defined( 'RANK_MATH_PRO_FILE' ) ) : ?>
			<div>
				<h4>
					<?php esc_html_e( 'Search Traffic', 'rank-math' ); ?>
					<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span><?php esc_html_e( 'This is the number of pageviews carried out by visitors from Google.', 'rank-math' ); ?></span></span>
				</h4>
				<?php $this->get_analytic_block( $data->pageviews ); ?>
			</div>
			<?php endif; ?>

			<div>
				<h4>
					<?php esc_html_e( 'Total Impressions', 'rank-math' ); ?>
					<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span><?php esc_html_e( 'How many times your site showed up in the search results.', 'rank-math' ); ?></span></span>
				</h4>
				<?php $this->get_analytic_block( $data->impressions ); ?>
			</div>

			<?php if ( ! $is_analytics_connected || ( $is_analytics_connected && ! defined( 'RANK_MATH_PRO_FILE' ) ) ) : ?>
			<div>
				<h4>
					<?php esc_html_e( 'Total Clicks', 'rank-math' ); ?>
					<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span><?php esc_html_e( 'This is the number of pageviews carried out by visitors from Google.', 'rank-math' ); ?></span></span>
				</h4>
				<?php $this->get_analytic_block( $data->clicks ); ?>
			</div>
			<?php endif; ?>

			<div>
				<h4>
					<?php esc_html_e( 'Total Keywords', 'rank-math' ); ?>
					<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span><?php esc_html_e( 'Total number of keywords your site ranking below 100 position.', 'rank-math' ); ?></span></span>
				</h4>
				<?php $this->get_analytic_block( $data->keywords ); ?>
			</div>

			<div>
				<h4>
					<?php esc_html_e( 'Average Position', 'rank-math' ); ?>
					<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span><?php esc_html_e( 'This is the number of pageviews carried out by visitors from Google.', 'rank-math' ); ?></span></span>
				</h4>
				<?php $this->get_analytic_block( $data->position ); ?>
			</div>

		</div>
		<?php
	}

	/**
	 * Get analytic block
	 *
	 * @param object $item Item.
	 */
	private function get_analytic_block( $item ) {
		$is_negative = absint( $item['difference'] ) !== $item['difference'];
		$diff_class  = $is_negative ? 'down' : 'up';
		if ( ! $is_negative && $item['difference'] > 0 ) {
			$diff_class = 'up';
		}
		?>
		<div class="rank-math-item-numbers">
			<strong class="text-large" title="<?php echo esc_html( Str::human_number( $item['total'] ) ); ?>"><?php echo esc_html( Str::human_number( $item['total'] ) ); ?></strong>
			<span class="rank-math-item-difference <?php echo esc_attr( $diff_class ); ?>" title="<?php echo esc_html( Str::human_number( $item['difference'] ) ); ?>"><?php echo esc_html( Str::human_number( $item['difference'] ) ); ?></span>
		</div>
		<?php
	}

	/**
	 * Admin init.
	 */
	public function render_notice() {
		$this->remove_action( 'admin_notices', 'render_notice' );
		if ( 'fetching' === get_option( 'rank_math_analytics_first_fetch' ) ) {
			$actions = as_get_scheduled_actions(
				[
					'order'  => 'DESC',
					'hook'   => 'rank_math/analytics/get_analytics',
					'status' => \ActionScheduler_Store::STATUS_PENDING,
				]
			);
			if ( empty( $actions ) ) {
				update_option( 'rank_math_analytics_first_fetch', 'hidden' );
				return;
			}

			$action         = current( $actions );
			$schedule       = $action->get_schedule();
			$next_timestamp = $schedule->get_date()->getTimestamp();
			$notification   = new \MyThemeShop\Notification(
				/* translators: delete counter */
				sprintf( '<i class="rm-icon rm-icon-rank-math"></i>' . esc_html__( 'Rank Math is importing latest data from connected Google Services, %s remaining.', 'rank-math' ), $this->human_interval( $next_timestamp - gmdate( 'U' ) ) ),
				[
					'type'    => 'info',
					'id'      => 'rank_math_analytics_first_fetch',
					'classes' => 'rank-math-notice',
				]
			);

			echo $notification; // phpcs:ignore
		}
	}

	/**
	 * Convert an interval of seconds into a two part human friendly string.
	 *
	 * The WordPress human_time_diff() function only calculates the time difference to one degree, meaning
	 * even if an action is 1 day and 11 hours away, it will display "1 day". This function goes one step
	 * further to display two degrees of accuracy.
	 *
	 * Inspired by the Crontrol::interval() function by Edward Dale: https://wordpress.org/plugins/wp-crontrol/
	 *
	 * @param int $interval A interval in seconds.
	 * @param int $periods_to_include Depth of time periods to include, e.g. for an interval of 70, and $periods_to_include of 2, both minutes and seconds would be included. With a value of 1, only minutes would be included.
	 * @return string A human friendly string representation of the interval.
	 */
	private function human_interval( $interval, $periods_to_include = 2 ) {
		$time_periods = [
			[
				'seconds' => YEAR_IN_SECONDS,
				/* translators: %s: amount of time */
				'names'   => _n_noop( '%s year', '%s years', 'rank-math' ),
			],
			[
				'seconds' => MONTH_IN_SECONDS,
				/* translators: %s: amount of time */
				'names'   => _n_noop( '%s month', '%s months', 'rank-math' ),
			],
			[
				'seconds' => WEEK_IN_SECONDS,
				/* translators: %s: amount of time */
				'names'   => _n_noop( '%s week', '%s weeks', 'rank-math' ),
			],
			[
				'seconds' => DAY_IN_SECONDS,
				/* translators: %s: amount of time */
				'names'   => _n_noop( '%s day', '%s days', 'rank-math' ),
			],
			[
				'seconds' => HOUR_IN_SECONDS,
				/* translators: %s: amount of time */
				'names'   => _n_noop( '%s hour', '%s hours', 'rank-math' ),
			],
			[
				'seconds' => MINUTE_IN_SECONDS,
				/* translators: %s: amount of time */
				'names'   => _n_noop( '%s minute', '%s minutes', 'rank-math' ),
			],
			[
				'seconds' => 1,
				/* translators: %s: amount of time */
				'names'   => _n_noop( '%s second', '%s seconds', 'rank-math' ),
			],
		];

		if ( $interval <= 0 ) {
			return __( 'Now!', 'rank-math' );
		}

		$output = '';

		for ( $time_period_index = 0, $periods_included = 0, $seconds_remaining = $interval; $time_period_index < count( $time_periods ) && $seconds_remaining > 0 && $periods_included < $periods_to_include; $time_period_index++ ) { // phpcs:ignore

			$periods_in_interval = floor( $seconds_remaining / $time_periods[ $time_period_index ]['seconds'] );

			if ( $periods_in_interval > 0 ) {
				if ( ! empty( $output ) ) {
					$output .= ' ';
				}
				$output .= sprintf( _n( $time_periods[ $time_period_index ]['names'][0], $time_periods[ $time_period_index ]['names'][1], $periods_in_interval, 'rank-math' ), $periods_in_interval ); // phpcs:ignore
				$seconds_remaining -= $periods_in_interval * $time_periods[ $time_period_index ]['seconds'];
				$periods_included++;
			}
		}

		return $output;
	}

	/**
	 * Enqueue scripts for the metabox.
	 */
	public function enqueue() {
		$screen = get_current_screen();
		if ( 'rank-math_page_rank-math-analytics' !== $screen->id ) {
			return;
		}

		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );

		wp_enqueue_style(
			'rank-math-analytics',
			$uri . '/assets/css/stats.css',
			[],
			rank_math()->version
		);

		wp_register_script(
			'rank-math-analytics',
			$uri . '/assets/js/stats.js',
			[
				'wp-components',
				'wp-element',
				'wp-i18n',
				'wp-date',
				'wp-api-fetch',
				'wp-html-entities',
			],
			rank_math()->version,
			true
		);

		$this->action( 'admin_footer', 'dequeue_cmb2' );

		$preference = apply_filters(
			'rank_math/analytics/user_preference',
			[
				'topPosts'        => [
					'seo_score'       => false,
					'schemas_in_use'  => false,
					'impressions'     => true,
					'pageviews'       => true,
					'clicks'          => false,
					'position'        => true,
					'positionHistory' => true,
				],
				'siteAnalytics'   => [
					'seo_score'       => true,
					'schemas_in_use'  => true,
					'impressions'     => false,
					'pageviews'       => true,
					'links'           => true,
					'clicks'          => false,
					'position'        => false,
					'positionHistory' => false,
				],
				'performance'     => [
					'seo_score'       => true,
					'schemas_in_use'  => true,
					'impressions'     => true,
					'pageviews'       => true,
					'ctr'             => false,
					'clicks'          => true,
					'position'        => true,
					'positionHistory' => true,
				],
				'keywords'        => [
					'impressions'     => true,
					'ctr'             => false,
					'clicks'          => true,
					'position'        => true,
					'positionHistory' => true,
				],
				'topKeywords'     => [
					'impressions'     => true,
					'ctr'             => true,
					'clicks'          => true,
					'position'        => true,
					'positionHistory' => true,
				],
				'trackKeywords'   => [
					'impressions'     => true,
					'ctr'             => false,
					'clicks'          => true,
					'position'        => true,
					'positionHistory' => true,
				],
				'rankingKeywords' => [
					'impressions'     => true,
					'ctr'             => false,
					'clicks'          => true,
					'position'        => true,
					'positionHistory' => true,
				],
			]
		);

		$user_id = get_current_user_id();
		if ( metadata_exists( 'user', $user_id, 'rank_math_analytics_table_columns' ) ) {
			$preference = wp_parse_args(
				get_user_meta( $user_id, 'rank_math_analytics_table_columns', true ),
				$preference
			);
		}

		Helper::add_json( 'userColumnPreference', $preference );

		// Last Updated.
		$updated = get_option( 'rank_math_analytics_last_updated', false );
		$updated = $updated ? date_i18n( get_option( 'date_format' ), $updated ) : '';
		Helper::add_json( 'lastUpdated', $updated );

		Helper::add_json( 'singleImage', rank_math()->plugin_url() . 'includes/modules/analytics/assets/img/single-post-report.jpg' );

		// Global Schema.
		$post_types = Helper::get_accessible_post_types();
		$global_schemas = [];
		foreach ( $post_types as $post_type ) {
			$global_schemas[ $post_type ] = SchemaHelper::sanitize_schema_title(
				Helper::get_default_schema_type( $post_type )
			);
		}
		Helper::add_json( 'globalSchemaTypes', array_filter( $global_schemas ) );
	}

	/**
	 * Dequeue cmb2.
	 */
	public function dequeue_cmb2() {
		wp_dequeue_script( 'cmb2-scripts' );
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {
		$dot_color = '#ed5e5e';
		if ( Console::is_console_connected() ) {
			$dot_color = '#11ac84';
		}

		$this->page = new Page(
			'rank-math-analytics',
			esc_html__( 'Analytics', 'rank-math' ) . '<span class="rm-menu-new update-plugins" style="background: ' . $dot_color . '; margin-left: 5px;min-width: 10px;height: 10px;margin-top: 5px;"><span class="plugin-count"></span></span>',
			[
				'position'   => 5,
				'parent'     => 'rank-math',
				'capability' => 'rank_math_analytics',
				'render'     => $this->directory . '/views/dashboard.php',
				'classes'    => [ 'rank-math-page' ],
				'assets'     => [
					'styles'  => [
						'rank-math-common'    => '',
						'rank-math-analytics' => '',
					],
					'scripts' => [
						'rank-math-analytics' => '',
					],
				],
			]
		);
	}

	/**
	 * Add module settings into general optional panel.
	 *
	 * @param array $tabs Array of option panel tabs.
	 *
	 * @return array
	 */
	public function add_settings( $tabs ) {
		Arr::insert(
			$tabs,
			[
				'analytics' => [
					'icon'  => 'rm-icon rm-icon-search-console',
					'title' => esc_html__( 'Analytics', 'rank-math' ),
					/* translators: Link to kb article */
					'desc'  => sprintf( esc_html__( 'See your Google Search Console, Analyitcs and AdSense data without leaving your WP dashboard. %s.', 'rank-math' ), '<a href="' . KB::get( 'analytics-settings' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
					'file'  => $this->directory . '/views/options.php',
				],
			],
			9
		);

		return $tabs;
	}

	/**
	 * Add database tools.
	 *
	 * @param array $tools Array of tools.
	 *
	 * @return array
	 */
	public function add_tools( $tools ) {
		Arr::insert(
			$tools,
			[
				'analytics_clear_caches'  => [
					'title'       => __( 'Purge Analytics Cache', 'rank-math' ),
					/* translators: 1. Review Schema documentation link */
					'description' => sprintf( __( 'Clear analytics cache to re-calculate all the stats again.', 'rank-math' ), '<a href="https://rankmath.com/kb/how-to-fix-review-schema-errors/" target="_blank">' . esc_attr__( 'here', 'rank-math' ) . '</a>' ),
					'button_text' => __( 'Clear Cache', 'rank-math' ),
				],
				'analytics_reindex_posts' => [
					'title'       => __( 'Rebuild Index for Analytics', 'rank-math' ),
					/* translators: 1. Review Schema documentation link */
					'description' => sprintf( __( 'Missing some posts/pages in the Analytics data? Clear the index and build a new one for more accurate stats.', 'rank-math' ), '<a href="https://rankmath.com/kb/how-to-fix-review-schema-errors/" target="_blank">' . esc_attr__( 'here', 'rank-math' ) . '</a>' ),
					'button_text' => __( 'Rebuild Index', 'rank-math' ),
				],
				'analytics_recreate_table' => [
					'title'       => __( 'Re-create database Table', 'rank-math' ),
					/* translators: 1. Review Schema documentation link */
					'description' => __( 'Create database tables if missing.', 'rank-math' ),
					'button_text' => __( 'Re-create Tables', 'rank-math' ),
				],
			],
			3
		);

		return $tools;
	}
}
