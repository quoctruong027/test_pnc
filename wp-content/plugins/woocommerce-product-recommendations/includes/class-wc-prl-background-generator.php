<?php
/**
 * WC_PRL_Background_Generator class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	include_once( WC_ABSPATH . 'includes/libraries/wp-async-request.php' );
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	include_once( WC_ABSPATH . 'includes/libraries/wp-background-process.php' );
}

/**
 * Background Generator.
 *
 * @class    WC_PRL_Background_Generator
 * @version  1.4.0
 */
class WC_PRL_Background_Generator extends WP_Background_Process {

	/**
	 * Throttle cron check every day.
	 *
	 * @var int
	 */
	protected $cron_interval = 24 * 60;

	/**
	 * Max size of the queue. After that number, the system no longer saves the task request.
	 *
	 * @var int
	 */
	protected $max_queue_allowed;

	/**
	 * Initiate new background process.
	 */
	public function __construct() {

		// Uses unique prefix per blog so each blog has its own queue.
		$this->prefix            = 'wp_' . get_current_blog_id();
		$this->action            = 'wc_prl_generator';
		$this->max_queue_allowed = apply_filters( 'woocommerce_prl_max_background_generator_queue_size_allowed', 1 );

		parent::__construct();
	}

	/**
	 * Returns the cron action identifier.
	 *
	 * @return string
	 */
	public function get_cron_hook_identifier() {
		return $this->cron_hook_identifier;
	}

	/**
	 * Returns the cron action identifier.
	 *
	 * @return string
	 */
	public function get_cron_interval_identifier() {
		return $this->cron_interval_identifier;
	}

	/**
	 * Dispatch updater.
	 */
	public function dispatch() {

		$dispatched = parent::dispatch();

		if ( is_wp_error( $dispatched ) ) {
			WC_PRL()->log( sprintf( 'Unable to generate WooCommerce Product Recommendations deployments: %s', $dispatched->get_error_message() ), 'error', 'wc_prl_generator_tasks' );
		}

		return $dispatched;
	}

	/**
	 * Schedule cron healthcheck
	 *
	 * @access public
	 * @param mixed $schedules Schedules.
	 * @return mixed
	 */
	public function schedule_cron_healthcheck( $schedules ) {

		if ( WC_PRL_Core_Compatibility::is_wc_version_gte( '3.5' ) ) {
			return parent::schedule_cron_healthcheck( $schedules );
		}

		$interval = apply_filters( $this->identifier . '_cron_interval', 5 );

		if ( property_exists( $this, 'cron_interval' ) ) {
			$interval = apply_filters( $this->identifier . '_cron_interval', $this->cron_interval );
		}

		// Adds every 5 minutes to the existing schedules.
		$schedules[ $this->identifier . '_cron_interval' ] = array(
			'interval' => MINUTE_IN_SECONDS * $interval,
			'display'  => sprintf( __( 'Every %d Minutes', 'woocommerce-product-recommendations' ), $interval ),
		);

		return $schedules;
	}

	/**
	 * Handle cron healthcheck.
	 *
	 * Restart the background process if not already running and data exists in the queue.
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();
			return;
		}

		$this->handle();
	}

	/**
	 * Schedule event.
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Any work to do?
	 *
	 * @return boolean
	 */
	public function is_queued() {
		return false === $this->is_queue_empty();
	}

	/**
	 * Is the updater actually running?
	 *
	 * @return boolean
	 */
	public function is_running() {
		return parent::is_process_running();
	}

	/**
	 * Time exceeded.
	 *
	 * Ensures the batch never exceeds a sensible time limit.
	 * A timeout limit of 30s is common on shared hosting.
	 *
	 * @return bool
	 */
	public function time_exceeded() {
		return parent::time_exceeded();
	}

	/**
	 * Memory exceeded.
	 *
	 * Ensures the batch process never exceeds 90%
	 * of the maximum WordPress memory.
	 *
	 * @return bool
	 */
	public function memory_exceeded() {
		return parent::memory_exceeded();
	}

	/**
	 * Set up data for the next deployment loop.
	 *
	 * @param  array &$data
	 * @return array
	 */
	protected function next_deployment( &$data ) {

		array_pop( $data[ 'deployment_ids' ] );
		$data[ 'filtered_query_args' ] = array();
		$data[ 'results' ]             = array();
		$data[ 'amplifiers' ]          = array();
		$data[ 'step' ]                = '1';

		return $data;
	}

	/**
	 * Set up data for the next amplifier loop.
	 *
	 * @param  array &$data
	 * @return void
	 */
	protected function next_amplifier( &$data ) {
		unset( $data[ 'current_amp_data' ] );
		unset( $data[ 'current_amp_substep' ] );
		unset( $data[ 'current_amp_substep_return' ] );
	}

	/**
	 * Checks the current queue for weird numbers.
	 *
	 * @return bool
	 */
	public function is_queue_full() {
		global $wpdb;

		$table  = $wpdb->options;
		$column = 'option_name';

		if ( is_multisite() ) {
			$table  = $wpdb->sitemeta;
			$column = 'meta_key';
		}

		$key = $this->identifier . '_batch_%';

		$count = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(*)
			FROM {$table}
			WHERE {$column} LIKE %s
		", $key ) );

		return $count > $this->max_queue_allowed;
	}

	/**
	 * Overrides the save method in order to add a `queue is full` checking.
	 *
	 * @return bool
	 */
	public function save() {
		if ( ! $this->is_queue_full() ) {
			parent::save();
		}
	}

	/**
	 * Generates deployment results.
	 *
	 * @param  array  $data  {
	 *     @type  array  $deployment_ids       This is the main queue for deployment ids. As this array has IDs this task will continue to repeat itself.
	 *
	 *         Structure:
	 *         - id: The deployment id.
	 *         - source_data: Array of source data.
	 *         - is_contextual: Boolean.
	 *
	 *     @type  array  $filtered_query_args  Indicates whether the filters have been applied to the current deployment.
	 *     @type  array  $amplifiers           The amplifiers queue for the current deployment.
	 *     @type  array  $results              Keeps the progress data of the amplifiers.
	 *     @type  bool   $force                Force the regeneration of the deployment batch.
	 *     @type  int    $step                 Enum( 1, 2, 3 ). Used for keeping track of what needs to run.
	 *
	 *         - 1: Apply filters and cache amplifiers.
	 *         - 2: Run amplifiers one by one.
	 *         - 3: Combine results.
	 *
	 * }
	 * @return mixed
	 */
	protected function task( $data ) {

		if ( ! empty( $data[ 'deployment_ids' ] ) ) {

			if ( empty( $data[ 'step' ] ) ) {
				$data[ 'step' ] = '1';
				$ids = wp_list_pluck( $data[ 'deployment_ids' ], 'id' );
				WC_PRL()->log( sprintf( 'Generating recommendations for deployments `%s`.', implode( ', ', $ids ) ), 'info', 'wc_prl_generator_tasks' );
			}

			// Get the first id.
			$task_info     = end( $data[ 'deployment_ids' ] );
			$deployment_id = $task_info[ 'id' ];
			$force         = isset( $task_info[ 'force' ] ) ? $task_info[ 'force' ] : false;

			////////////////////////////////////////////////
			// Step 0: Fetch current deployment and rebuild it.
			////////////////////////////////////////////////
			$deployment = new WC_PRL_Deployment( $deployment_id );
			$deployment->set_source_data( $task_info[ 'source_data' ] );
			$deployment->set_contextual_engine_state( $task_info[ 'is_contextual' ] );

			if ( $deployment->get_id() ) {

				// Engine instance.
				$engine = new WC_PRL_Engine( $deployment->get_engine_id() );

				if ( ! $engine ) {
					WC_PRL()->log( 'Engine not found for deployment `#' . $deployment_id . '`. Moving on...', 'info', 'wc_prl_generator_tasks' );
					return $this->next_deployment( $data );
				}

				// Check expiration...
				$cached_products = $deployment->data->get_meta( $deployment->get_cache_key() );

				if ( ! empty( $cached_products ) ) {
					if ( ! $force && time() < absint( $cached_products[ 'created_at' ] ) + $engine->refresh_interval_in_seconds ) {
						if ( wc_prl_debug_enabled() ) {
							WC_PRL()->log( 'Newly created deployment. Moving on...', 'info', 'wc_prl_generator_tasks' );
						}
						return $this->next_deployment( $data );
					}
				}

				switch ( $data[ 'step' ] ) {

					////////////////////////////////////////////////
					// Step 1: Filter arguments.
					////////////////////////////////////////////////
					case '1':

						if ( wc_prl_debug_enabled() ) {
							WC_PRL()->log( sprintf( 'Filtering the deployment `#%d`.', $deployment_id ), 'info', 'wc_prl_generator_tasks' );
						}

						if ( empty( $data[ 'filtered_query_args' ] ) ) {

							$data[ 'filtered_query_args' ] = $engine->get_filtered_args( $deployment );
							$data[ 'amplifiers' ]          = $engine->get_amplifiers_data();
							$data[ 'step' ]                = '2';

							if ( empty( $data[ 'amplifiers' ] ) ) {
								$data[ 'step' ] = '3';
							}
						}

						// Silent Fail, proceed to step 3.
						if ( isset( $data[ 'filtered_query_args' ][ 'force_empty_set' ] ) ) {
							$data[ 'step' ] = '3';
						}

						break;

					////////////////////////////////////////////////
					// Step 2: Loop $data[ 'amplifiers' ].
					////////////////////////////////////////////////
					case '2':

						// Is current amp set to be multistep?
						if ( isset( $data[ 'current_amp_data' ] ) ) {

							$amp = WC_PRL()->amplifiers->get_amplifier( $data[ 'current_amp_data' ][ 'id' ] );

							if ( ! isset( $data[ 'current_amp_substep' ] ) ) {
								$data[ 'current_amp_substep' ] = 1;
							}

							if ( ! isset( $data[ 'current_amp_substep_return' ] ) ) {
								$data[ 'current_amp_substep_return' ] = array();
							}

							// Run substep.
							$substep_return = $amp->run_step( $data[ 'current_amp_substep' ], $deployment, $data[ 'current_amp_substep_return' ] );

							if ( is_null( $substep_return ) ) {
								// Force an empty set on this amp only.
								$data[ 'results' ][ $amp->get_id() ][ 'products' ] = array();
								$data[ 'results' ][ $amp->get_id() ][ 'weight' ]   = isset( $data[ 'current_amp_data' ][ 'weight' ] ) ? absint( $data[ 'current_amp_data' ][ 'weight' ] ) : 1;
								$this->next_amplifier( $data );
								break;
							}

							// Save substep return value...
							if ( ! is_object( $substep_return ) || ( is_array( $substep_return ) && ! is_object( $substep_return[ 0 ] ) ) ) {
								$data[ 'current_amp_substep_return' ][ (int) $data[ 'current_amp_substep' ] ] = $substep_return;
							}

							if ( $data[ 'current_amp_substep' ] == $amp->get_steps_count() ) {

								// Before applying filters make sure that the last step has limited the products that need to be included.
								// Last step should always return a set of products to be included in filters.
								if ( ! empty( $data[ 'current_amp_substep_return' ][ $amp->get_steps_count() ] ) ) {

									$products_from_amp = $data[ 'current_amp_substep_return' ][ $amp->get_steps_count() ];

									// If set exclude array_diff it.
									if ( ! empty( $data[ 'filtered_query_args' ][ 'exclude' ] ) ) {
										$products_from_amp = array_diff( $products_from_amp, $data[ 'filtered_query_args' ][ 'exclude' ] );
									}

									// If set include array_intersect it.
									if ( ! empty( $data[ 'filtered_query_args' ][ 'include' ] ) ) {
										$data[ 'filtered_query_args' ][ 'include' ] = array_intersect( $products_from_amp, $data[ 'filtered_query_args' ][ 'include' ] );

										// Intersection emptied the include array?
										if ( empty( $data[ 'filtered_query_args' ][ 'include' ] ) ) {
											// Force an empty set on this amp only.
											$data[ 'results' ][ $amp->get_id() ][ 'products' ] = array();
											$data[ 'results' ][ $amp->get_id() ][ 'weight' ]   = isset( $data[ 'current_amp_data' ][ 'weight' ] ) ? absint( $data[ 'current_amp_data' ][ 'weight' ] ) : 1;
											// Move on to the next amp...
											$this->next_amplifier( $data );
											break;
										}

									} else {
										$data[ 'filtered_query_args' ][ 'include' ] = $products_from_amp;
									}

									// Finish it off...
									$amp_args = $amp->amplify( $data[ 'filtered_query_args' ], $deployment, $data[ 'current_amp_data' ] );

									// Do the query.
									$data[ 'results' ][ $amp->get_id() ][ 'products' ] = $amp->query( $amp_args );
									$data[ 'results' ][ $amp->get_id() ][ 'weight' ]   = isset( $data[ 'current_amp_data' ][ 'weight' ] ) ? absint( $data[ 'current_amp_data' ][ 'weight' ] ) : 1;

								} else {
									// Force an empty set on this amp only.
									$data[ 'results' ][ $amp->get_id() ][ 'products' ] = array();
									$data[ 'results' ][ $amp->get_id() ][ 'weight' ]   = isset( $data[ 'current_amp_data' ][ 'weight' ] ) ? absint( $data[ 'current_amp_data' ][ 'weight' ] ) : 1;
								}

								// Move on to the next amp...
								$this->next_amplifier( $data );
							} else {

								// Next step...
								$data[ 'current_amp_substep' ]++;
								break;
							}
						}

						if ( ! empty( $data[ 'amplifiers' ] ) ) {

							$amp_data = array_pop( $data[ 'amplifiers' ] );
							$amp      = WC_PRL()->amplifiers->get_amplifier( $amp_data[ 'id' ] );

							if ( 1 < $amp->get_steps_count() && ! isset( $data[ 'current_amp_data' ] ) ) {
								// Mark the amp request as `current` and re-run to start calculating multisteps.
								$data[ 'current_amp_data' ] = $amp_data;
								if ( wc_prl_debug_enabled() ) {
									WC_PRL()->log( sprintf( 'Marking amplifier `%s` as multistep.', $amp_data[ 'id' ] ), 'info', 'wc_prl_generator_tasks' );
								}
								break;
							}

							if ( wc_prl_debug_enabled() ) {
								WC_PRL()->log( sprintf( 'Generating amplifier `%s` for deployment `#%d`.', $amp_data[ 'id' ], $deployment_id ), 'info', 'wc_prl_generator_tasks' );
							}

							if ( $amp ) {

								$amp_args = $amp->amplify( $data[ 'filtered_query_args' ], $deployment, $amp_data );

								// Do the query.
								$data[ 'results' ][ $amp->get_id() ][ 'products' ] = $amp->query( $amp_args );
								$data[ 'results' ][ $amp->get_id() ][ 'weight' ]   = isset( $amp_data[ 'weight' ] ) ? absint( $amp_data[ 'weight' ] ) : 1;
							}
						}

						// If no more, continue.
						if ( empty( $data[ 'amplifiers' ] ) ) {
							$data[ 'step' ] = '3';
						}

						break;

					////////////////////////////////////////////////
					// Step 3: Combine results and save in cache.
					////////////////////////////////////////////////
					case '3':

						// Engine results.
						$products = array();

						if ( isset( $data[ 'filtered_query_args' ][ 'force_empty_set' ] ) ) {
							// Silent fail, write empty products.
							$products = array();
						} else if ( empty( $data[ 'results' ] ) ) {
							// By default the "None" amplifier.
							$products = $engine->query( $data[ 'filtered_query_args' ] );
						} else if ( 1 === count( $data[ 'results' ] ) ) {
							// No need for weight merging here.
							$single_results = array_pop( $data[ 'results' ] );
							$products       = $single_results[ 'products' ];
						} else {
							// Do a weight merge.
							$products = $engine->weight_merge( $data[ 'results' ], $engine->sampling_max_index );
						}

						// Limit products before saving.
						$limit    = min( $engine->caching_max_index, $engine->sampling_max_index );
						$products = array_slice( $products, 0, $limit );

						// Add to cache.
						$cache = array(
							'products'   => $products,
							'created_at' => time()
						);

						$deployment->data->update_meta( $deployment->get_cache_key(), $cache );
						$deployment->data->save();

						if ( wc_prl_debug_enabled() ) {
							WC_PRL()->log( sprintf( 'Combine and save for the deployment `#%d` in `%s` meta key.', $deployment_id, $deployment->get_cache_key() ), 'info', 'wc_prl_generator_tasks' );
						}

						////////////////////////////////////////////////
						// Next deployment...
						////////////////////////////////////////////////
						$this->next_deployment( $data );
						break;
				}
			}
		}

		////////////////////////////////////////////////
		// Continue running with caution...
		////////////////////////////////////////////////
		if ( empty( $data[ 'safety_bridge' ] ) || ! is_numeric( $data[ 'safety_bridge' ] ) ) {
			$data[ 'safety_bridge' ] = 0;
		}

		$data[ 'safety_bridge' ]++;

		$max_repeats_per_task = 3 * 10; // Hint: 3 steps * 10 deployments at time.

		if ( ! empty( $data[ 'deployment_ids' ] ) && $data[ 'safety_bridge' ] <= $max_repeats_per_task ) {
			return $data;
		}

		////////////////////////////////////////////////
		// Terminate.
		////////////////////////////////////////////////
		return false;
	}

	/**
	 * When all tasks complete, create a log entry.
	 */
	protected function complete() {
		WC_PRL()->log( 'Recommendations generation completed.', 'info', 'wc_prl_generator_tasks' );
		parent::complete();
	}
}
