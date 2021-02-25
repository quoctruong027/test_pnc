<?php
/**
 * WC_PRL_Amplifier_Others_Also_Bought class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_PRL_Amplifier_Others_Also_Bought class for amplifying products based on their price.
 *
 * @class    WC_PRL_Amplifier_Others_Also_Bought
 * @version  1.4.4
 */
class WC_PRL_Amplifier_Others_Also_Bought extends WC_PRL_Amplifier {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                     = 'others_also_bought';
		$this->title                  = __( 'Others Also Bought', 'woocommerce-product-recommendations' );
		$this->supported_modifiers    = array();
		$this->supported_engine_types = array( 'product' );
	}

	/**
	 * Apply the amplifier to the query args array.
	 *
	 * @param  array             $query_args
	 * @param  WC_PRL_Deployment $deployment
	 * @param  array             $data
	 * @return array
	 */
	public function amp( $query_args, $deployment, $data ) {

		add_filter( 'posts_clauses', array( $this, 'add_order_clauses' ) );

		return $query_args;
	}

	/**
	 * Alters the raw query in order to create divisions for significant terms.
	 *
	 * @param  array $args
	 * @return array
	 */
	public function add_order_clauses( $args ) {
		global $wpdb;
		$shared_clauses = WC_PRL()->db->get_shared_posts_clauses();
		WC_PRL()->db->set_shared_posts_clauses( null );
		if ( isset( $shared_clauses[ 'oab' ] ) ) {
			$args[ 'orderby' ] = "FIELD(ID," . implode( ',', $shared_clauses[ 'oab' ] ) . ")";
		}

		return $args;
	}

	/**
	 * Removes any global amp settings.
	 *
	 * @return void
	 */
	public function remove_amp() {
		remove_filter( 'posts_clauses', array( $this, 'add_order_clauses' ) );
	}

	/**
	 * Get amplifiers substeps count.
	 *
	 * @return int
	 */
	public function get_steps_count() {
		return 5;
	}

	/**
	 * Run a step.
	 * Hint: Keys in $args array represent the return values of each step (non-zero index).
	 *
	 * @param  int                $step_index
	 * @param  WC_PRL_Deployment  $deployment
	 * @param  array              $args Includes a set of all previous steps return values.
	 * @return int
	 */
	public function run_step( $step_index, $deployment, $args = array() ) {

		// Product source ID.
		$source_data          = $deployment->get_source_data();
		$source_id            = (int) array_pop( $source_data );
		$time_period_in_weeks = (int) apply_filters( 'woocommerce_prl_amp_oab_time_weeks', 0, $deployment );

		if ( wc_prl_debug_enabled() ) {
			$starttime = microtime( true );
		}

		switch ( $step_index ) {

			case 1:

				// Find total customers container source.
				global $wpdb;
				$sql = "SELECT count( distinct( `customer_id` ) )
						FROM `{$wpdb->prefix}wc_order_product_lookup`
						WHERE `product_id` = {$source_id}" .
						( $time_period_in_weeks > 0 ? " AND `date_created` >= DATE_SUB( NOW(), INTERVAL " . $time_period_in_weeks . " WEEK )" : "" )
						;
				$total_customers_bought_source = (int) $wpdb->get_var( $sql );

				if ( wc_prl_debug_enabled() ) {
					$endtime  = microtime( true );
					$timediff = $endtime - $starttime;
					WC_PRL()->log( sprintf( '[Benchmarking] Step "%d" run for %.4f seconds.', $step_index, $timediff ), 'info', 'wc_prl_generator_tasks' );
				}

				if ( empty( $total_customers_bought_source ) || $total_customers_bought_source < apply_filters( 'woocommerce_prl_amp_oab_foreground_customer_count_min', 10, $deployment ) ) {
					return null;
				}

				return $total_customers_bought_source;
				break;

			case 2:

				// Find the most significant products.
				$total_customers_bought_source  = (int) $args[ 1 ];
				$having_support_percentage      = (float) apply_filters( 'woocommerce_prl_amp_oab_foreground_support_pct_min', 10, $deployment );
				$support_number                 = max( (int) $total_customers_bought_source * $having_support_percentage / 100, apply_filters( 'woocommerce_prl_amp_oab_foreground_count_min', 10, $deployment ) );
				$results                        = array();
				if ( $total_customers_bought_source ) {
					global $wpdb;
					$sql = "SELECT `t1`.`product_id`, count( distinct( `t1`.`customer_id` ) ) AS `count`
							FROM `{$wpdb->prefix}wc_order_product_lookup` AS `t1`
							INNER JOIN `{$wpdb->prefix}wc_order_product_lookup` AS `t2` ON `t1`.`customer_id` = `t2`.`customer_id`
							WHERE `t1`.`product_id` <> {$source_id}" .
							( $time_period_in_weeks > 0 ? " AND `t1`.`date_created` >= DATE_SUB( NOW(), INTERVAL " . $time_period_in_weeks . " WEEK )" : "" ) .
							" AND `t2`.`product_id` = {$source_id}
							GROUP BY `t1`.`product_id`
							HAVING count > {$support_number}
							ORDER BY `count` DESC
							LIMIT 100
							";
					$results = $wpdb->get_results( $sql, ARRAY_A );
				}

				// Normalize return.
				$counts = array();
				foreach ( $results as $row ) {
					$counts[ $row[ 'product_id' ] ] = $row[ 'count' ];
				}

				if ( wc_prl_debug_enabled() ) {
					$endtime  = microtime( true );
					$timediff = $endtime - $starttime;
					WC_PRL()->log( sprintf( '[Benchmarking] Step "%d" run for %.4f seconds.', $step_index, $timediff ), 'info', 'wc_prl_generator_tasks' );
				}

				if ( empty( $counts ) ) {
					return null;
				}

				return $counts;
				break;

			case 3:

				// Find total customers.
				global $wpdb;
				$sql = "SELECT count( distinct( `customer_id` ) )
						FROM `{$wpdb->prefix}wc_order_stats` " .
						( $time_period_in_weeks > 0 ? " WHERE `date_created` >= DATE_SUB( NOW(), INTERVAL " . $time_period_in_weeks . " WEEK )" : "" )
						;
				$total_customers = (int) $wpdb->get_var( $sql );

				if ( wc_prl_debug_enabled() ) {
					$endtime  = microtime( true );
					$timediff = $endtime - $starttime;
					WC_PRL()->log( sprintf( '[Benchmarking] Step "%d" run for %.4f seconds.', $step_index, $timediff ), 'info', 'wc_prl_generator_tasks' );
				}

				if ( ! $total_customers ) {
					return null;
				}

				return $total_customers;
				break;

			case 4:

				// Find the background freq of the previous significant products.
				$background_support_min_count = (int) apply_filters( 'woocommerce_prl_amp_oab_background_count_min', 10, $deployment );

				// Get terms from step 2.
				$product_ids                  = array_filter( array_keys( $args[ 2 ] ) );

				// Init background frequencies.
				$background_frequencies       = array();

				// Has cached data?
				$cached_frequencies           = WC_PRL()->db->frequencies->query( array( 'product_id' => $product_ids, 'context' => 'customer', 'has_expired' => false ) );
				foreach ( $cached_frequencies as $freq ) {
					// Limit based on support numbers.
					if ( $freq[ 'count' ] >= $background_support_min_count ) {
						$background_frequencies[ $freq[ 'product_id' ] ] = $freq[ 'count' ] / $freq[ 'base_total' ];
					}
				}

				// What's left to calculate?
				$product_ids_to_calculate = implode( ',', array_diff( $product_ids, wp_list_pluck( $cached_frequencies, 'product_id' ) ) );
				if ( ! empty( $product_ids_to_calculate ) ) {
					global $wpdb;
					$total_customers = (int) $args[ 3 ];
					$sql             = "SELECT `product_id`, count( distinct( `customer_id` ) ) AS `count`
										FROM `{$wpdb->prefix}wc_order_product_lookup`
										WHERE `product_id` IN ( {$product_ids_to_calculate} )" .
										( $time_period_in_weeks > 0 ? " AND `date_created` >= DATE_SUB( NOW(), INTERVAL " . $time_period_in_weeks . " WEEK )" : "" ) .
										" GROUP BY `product_id`
										";
					$results         = $wpdb->get_results( $sql, ARRAY_A );
					if ( ! empty( $results ) ) {

						$revalidation_period             = (int) apply_filters( 'woocommerce_prl_amp_oab_background_cache_period', WEEK_IN_SECONDS, $deployment );
						$revalidation_diversity_interval = (int) apply_filters( 'woocommerce_prl_amp_oab_background_cache_interval', HOUR_IN_SECONDS, $deployment );
						$revalidation_timestamp          = time() + $revalidation_period;

						foreach ( $results as $result ) {

							// Limit based on support numbers.
							if ( $result[ 'count' ] >= $background_support_min_count ) {
								$background_frequencies[ $result[ 'product_id' ] ] = $result[ 'count' ] / $total_customers;
							}

							$freq_cache_args = array(
								'product_id'  => absint( $result[ 'product_id' ] ),
								'context'     => 'customer',
								'count'       => absint( $result[ 'count' ] ),
								'base_total'  => $total_customers,
								'expire_date' => $revalidation_timestamp
							);

							try {
								// Cache counts for products.
								WC_PRL()->db->frequencies->cache( $freq_cache_args );
							} catch ( Exception $e ) {
								WC_PRL()->log( sprintf( 'Failed to cache frequency. Input data: %s', print_r( $freq_cache_args, true ) ), 'warning', 'wc_prl_generator_tasks' );
							}

							// Diverse revalidation period to avoid bulk regeneration.
							$revalidation_timestamp = $revalidation_timestamp + $revalidation_diversity_interval;
						}
					}
				}

				if ( wc_prl_debug_enabled() ) {
					$endtime  = microtime( true );
					$timediff = $endtime - $starttime;
					WC_PRL()->log( sprintf( '[Benchmarking] Step "%d" run for %.4f seconds.', $step_index, $timediff ), 'info', 'wc_prl_generator_tasks' );
				}

				if ( empty( $background_frequencies ) ) {
					return null;
				}

				return $background_frequencies;
				break;

			case 5:

				$total_customers_bought_source  = (int) $args[ 1 ];
				$total_customers                = (int) $args[ 3 ];
				$foreground_counts              = $args[ 2 ];
				$background_frequencies         = $args[ 4 ];
				$products_to_calc               = array_intersect( array_keys( $foreground_counts ), array_keys( $background_frequencies ) );

				if ( ! empty( $products_to_calc ) && 0 != $total_customers_bought_source ) {

					// Run.
					$significant_products     = array();

					/**
					 * Significance scoring algorithm:
					 *
					 * - JLH
					 * - Google Normalized Distance
					 *
					 * @param  $scoring_algorithm  Values: 'jlh' | 'gnd'
					 */
					$significance_score = apply_filters( 'woocommerce_prl_oab_significance_score', 'jlh', $deployment );

					if ( 'jlh' === $significance_score ) {
						$jlh_threshold = (float) apply_filters( 'woocommerce_prl_amp_oab_jlh_threshold', 0.2, $deployment );
					} elseif ( 'gnd' === $significance_score ) {
						$gnd_threshold = (float) apply_filters( 'woocommerce_prl_amp_oab_gnd_threshold', 0.8, $deployment );
					}

					foreach ( $products_to_calc as $product_id ) {

						// Sanity.
						if ( ! isset( $foreground_counts[ $product_id ] ) || ! isset( $background_frequencies[ $product_id ] ) ) {
							continue;
						}

						if ( 'jlh' === $significance_score ) {

							$ff        = $foreground_counts[ $product_id ] / $total_customers_bought_source;
							$fk        = $background_frequencies[ $product_id ];
							$jlh_score = ( $ff - $fk ) * ( $ff / $fk );

							if ( $jlh_score > $jlh_threshold ) {
								$significant_products[ $product_id ] = $jlh_score;
							}

						} else if ( 'gnd' === $significance_score ) {

							$gnd_score = ( max( log( $total_customers_bought_source ), log( $background_frequencies[ $product_id ] * $total_customers ) ) - log( $foreground_counts[ $product_id ] ) ) / ( log( $total_customers ) - min( log( $total_customers_bought_source ), log( $background_frequencies[ $product_id ] * $total_customers ) ) );

							if ( $gnd_score < $gnd_threshold ) {
								$significant_products[ $product_id ] = $gnd_score;
							}
						}
					}

					if ( 'jlh' === $significance_score ) {
						arsort( $significant_products );
					} else if ( 'gnd' === $significance_score ) {
						asort( $significant_products );
					}

					WC_PRL()->db->set_shared_posts_clauses( array( 'oab' => array_keys( $significant_products ) ) );

					if ( wc_prl_debug_enabled() ) {
						$endtime  = microtime( true );
						$timediff = $endtime - $starttime;
						WC_PRL()->log( sprintf( '[Benchmarking] Step "%d" run for %.4f seconds.', $step_index, $timediff ), 'info', 'wc_prl_generator_tasks' );
					}

					return array_keys( $significant_products );
				}

				break;
		}
	}

	/*---------------------------------------------------*/
	/*  Force methods.                                   */
	/*---------------------------------------------------*/

	/**
	 * Get admin html for filter inputs.
	 *
	 * @param  string|null $post_name
	 * @param  int         $amplifier_index
	 * @param  array       $amplifier_data
	 * @return void
	 */
	public function get_admin_fields_html( $post_name, $amplifier_index, $amplifier_data ) {

		$post_name = ! is_null( $post_name ) ? $post_name : 'prl_engine';

		// Default weight.
		if ( ! empty( $amplifier_data[ 'weight' ] ) ) {
			$weight = absint( $amplifier_data[ 'weight' ] );
		} else {
			$weight = 4;
		}

		?>
		<input type="hidden" name="<?php echo $post_name; ?>[amplifiers][<?php echo $amplifier_index; ?>][id]" value="<?php echo $this->id; ?>" />
		<input type="hidden" name="<?php echo $post_name; ?>[amplifiers][<?php echo $amplifier_index; ?>][context]" value="yes" />
		<div class="os_row_inner">
			<div class="os_modifier">
				<div class="os--disabled"></div>
			</div>
			<div class="os_semi_value">
				<div class="os--disabled"></div>
			</div>
			<div class="os_slider column-wc_actions">
				<?php wc_prl_print_weight_select( $weight, $post_name . '[amplifiers][' . $amplifier_index . '][weight]' ) ?>
			</div>
		</div><?php
	}
}
