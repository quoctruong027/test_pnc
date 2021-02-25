<?php
/**
 * WC_PRL_Deployments class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deployments Factory class.
 *
 * @class    WC_PRL_Deployments
 * @version  1.1.0
 */
class WC_PRL_Deployments {

	/**
	 * Cached Deployments per page.
	 *
	 * @var array
	 */
	private $deployments;

	/**
	 * Background generator.
	 *
	 * @var WC_PRL_Background_Generator
	 */
	private $background_generator;

	/**
	 * Array of deployment ID's to be processed by the background generator.
	 *
	 * @var array
	 */
	private $deployments_for_background_generation = array();

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-recommendations' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-recommendations' ), '1.0.0' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init_background_generator' ), 5 );
		add_action( 'current_screen', array( $this, 'check_background_generator_status' ), 10 );

		add_action( 'shutdown', array( $this, 'generate_background_results' ), 99 );

		// DB Cascade.
		add_action( 'woocommerce_prl_engine_object_updated_props', array( $this, 'bulk_clear_product_caches' ), 10, 2 );
	}

	/**
	 * Get deployments for a specific location.
	 *
	 * @param  string $hook
	 * @param  array  $args
	 * @param  string $context
	 * @return array
	 */
	public function get_deployments( $hook, $context = 'edit', $args = array() ) {

		$location = WC_PRL()->locations->get_location_by_hook( $hook );

		if ( ! $location ) {
			return array();
		}

		$hook = $location->get_current_hook();
		if ( $hook ) {

			// Reset.
			$this->deployments = null;

			$this->bulk_load_deployments( $location );

			// Load source data if the context is view.
			if ( 'view' === $context ) {
				$this->bulk_load_source_data( $location, $args );
			}

			// Return runtime cache deployments for the specific hook.
			if ( is_array( $this->deployments ) && isset( $this->deployments[ $location->get_location_id() ][ $hook ] ) ) {
				return $this->deployments[ $location->get_location_id() ][ $hook ];
			}
		}

		return array();
	}

	/**
	 * Bulk fetch source data for all deployments of a location.
	 *
	 * @param  string $hook
	 * @return void
	 */
	private function bulk_load_source_data( $location, $args ) {

		if ( ! is_array( $this->deployments ) ) {
			return;
		}

		if ( ! isset( $this->deployments[ $location->get_location_id() ] ) ) {
			return;
		}

		foreach ( $this->deployments[ $location->get_location_id() ] as $hook => $deployments ) {
			foreach ( $deployments as $deployment ) {
				$deployment->set_source_data( $location->get_source_data( $deployment, $args ) );
			}
		}
	}

	/**
	 * Bulk fetch deployments based on a location.
	 *
	 * @param  object $location
	 * @return void
	 */
	public function bulk_load_deployments( $location ) {

		if ( ! is_array( $this->deployments ) ) {
			$this->deployments = array();
		}

		$args = array(
			'return'      => 'objects',
			'active'      => 'on',
			'location_id' => $location->get_location_id(),
			'order_by'    => array( 'display_order' => 'ASC' )
		);

		$deployments = WC_PRL()->db->deployment->query( $args );

		foreach ( $deployments as $deployment ) {
			$this->deployments[ $location->get_location_id() ][ $deployment->get_hook() ][] = new WC_PRL_Deployment( $deployment );
		}
	}

	/**
	 * Does a DB clean up on product caches.
	 *
	 * @param  WC_PRL_Engine $engine
	 * @param  array $updated_props
	 * @return void
	 */
	public function bulk_clear_product_caches( $engine, $updated_props ) {
		global $wpdb;

		if ( array_intersect( $updated_props, array( 'filters_data', 'amplifiers_data' ) ) ) {

			$deployments = WC_PRL()->db->deployment->query( array( 'return' => 'ids', 'engine_id' => $engine->get_id() ) );

			if ( ! empty( $deployments ) ) {
				$deployments = array_map( 'absint', $deployments );
				// Delete caches.
				WC_PRL()->db->deployment->clear_caches( $deployments );
			}
		}
	}

	/**
	 * Get admin fields for accordion.
	 *
	 * @param  array $updated_props
	 * @return void
	 */
	public function get_admin_metaboxes_content( $index, $options = array(), $ajax = false ) {

		if ( ! isset( $options[ 'engine_type' ], $options[ 'filter_type' ] ) ) {
			return;
		}

		if ( ! isset( $options[ 'active' ] ) ) {
			$options[ 'active' ] = 'on';
		}

		if ( ! isset( $options[ 'id' ] ) ) {
			$options[ 'id' ] = 0;
		}

		if ( ! isset( $options[ 'display_order' ] ) ) {
			$options[ 'display_order' ] = $index + 1;
		}

		$form_index = isset( $options[ 'form_index' ] ) ? absint( $options[ 'form_index' ] ) : $index;

		$state = 'closed';

		if ( $ajax ) {
			$state = 'open';
		}
		?><div class="<?php echo $ajax ? 'wc-prl-deployments__row--added ' : '' ?>wc-prl-deployments__row wc-metabox <?php echo $state ?>" data-index="<?php echo $index + 1; ?>" data-deployment_id="<?php echo absint( $options[ 'id' ] ) ?>">

			<h3>
				<div class="deployment_title">
					<?php $toggle_class = 'on' === $options[ 'active' ] ? 'woocommerce-input-toggle--enabled' : 'woocommerce-input-toggle--disabled';?>
					<span id="active-toggle" class="woocommerce-input-toggle <?php echo $toggle_class; ?>"></span>
					<span class="deployment_title_index_container">#<span class="deployment_title_index"><?php echo $index + 1; ?></span></span>
					<span class="deployment_title_inner">
						<?php echo isset( $options[ 'title' ] ) ? $options[ 'title' ] : '' ?>
					</span>
					<?php
						echo isset( $options[ 'page_cache_tip' ] ) ? sprintf( '<span class="woocommerce-help-tip deployment-affected-by-page-cache-help-tip" data-tip="%s"></span>', $options[ 'page_cache_tip' ] ) : '';
					?>
				</div>
				<div class="handle">
					<div class="handle-item toggle-item" aria-label="<?php esc_attr_e( 'Click to toggle', 'woocommerce' ); ?>"></div>
					<div class="handle-item sort-item" aria-label="<?php esc_attr_e( 'Drag and drop to set order', 'woocommerce-product-recommendations' ); ?>"></div>
					<a href="#" id="remove_row" class="remove_row delete"><?php esc_html_e( 'Delete', 'woocommerce-product-recommendations' ); ?></a>
				</div>
			</h3>

			<div class="wc-prl-deployments__row__form wc-metabox-content" <?php echo $ajax ? '' : 'style="display: none;"' ?>>
				<input type="hidden" name="deployment[<?php echo $form_index; ?>][id]" value="<?php echo absint( $options[ 'id' ] ) ?>"/>
				<input type="hidden" name="deployment[<?php echo $form_index; ?>][form_index]" class="form_index" value="<?php echo $form_index; ?>"/>
				<input type="hidden" name="deployment[<?php echo $form_index; ?>][active]" class="form_active" value="<?php echo 'on' === $options[ 'active' ] ? 'on' : 'off'; ?>"/>
				<input type="hidden" name="deployment[<?php echo $form_index; ?>][display_order]" class="form_display_order" value="<?php echo $options[ 'display_order' ]; ?>"/>

				<div class="sw-form<?php echo $ajax ? ' sw-form--no-engine' : ''; ?>">

					<div class="sw-form-field">
						<label>
							<?php esc_html_e( 'Title', 'woocommerce-product-recommendations' ); ?>
						</label>
						<div class="sw-form-content">
							<input type="text" name="deployment[<?php echo $form_index; ?>][title]" class="form_deployment_title" placeholder="<?php _e( 'e.g. &quot;You may also like&hellip;&quot;', 'woocommerce-product-recommendations' ); ?>"<?php echo isset( $options[ 'title' ] ) ? ' value="' . esc_attr( $options[ 'title' ] ) . '"' : ''; ?>"/>
						</div>
					</div>

					<div class="sw-form-field">
						<label for="prl_deploy_description">
							<?php esc_html_e( 'Description', 'woocommerce-product-recommendations' ); ?>
						</label>
						<div class="sw-form-content">
							<textarea type="text" name="deployment[<?php echo $form_index; ?>][description]"><?php echo isset( $options[ 'description' ] ) ? esc_textarea( $options[ 'description' ] ) : ''; ?></textarea>
						</div>
					</div>

					<div class="sw-form-field">
						<label>
							<?php esc_html_e( 'Engine', 'woocommerce-product-recommendations' ); ?>
						</label>
						<div class="sw-form-content">
							<select class="wc-engine-search" name="deployment[<?php echo $form_index; ?>][engine_id]" data-placeholder="<?php _e( 'Search for an Engine&hellip;', 'woocommerce-product-recommendations' ); ?>" data-limit="100"<?php echo ( $options[ 'filter_type' ] ) ? ' data-filter_type="' . implode( ',', $options[ 'filter_type' ] ) . '"' : '' ?>>
								<?php
								$engine = isset( $options[ 'engine_id' ] ) ? wc_prl_get_engine( absint( $options[ 'engine_id' ] ) ) : false;
								if ( $engine ) {
									$title = $engine->get_name() ? $engine->get_name() : __( '(no title)', 'woocommerce-product-recommendations' );
									echo '<option value="' . $engine->get_id() . '" selected="selected">' . $title . '</option>';
								}
								?>
							</select>
							<span class="description">
								<?php echo sprintf( __( 'Supported Engine Types: %s', 'woocommerce-product-recommendations' ), '<strong>' . implode( ', ', array_map( 'wc_prl_get_engine_type_label', $options[ 'filter_type' ] ) ) . '</strong>' ); ?>
							</span>
							<input type="hidden" class="locations_type_select" value="<?php echo $options[ 'engine_type' ] ?>">
						</div>
					</div>

					<div class="sw-form-field sw-form-field--small">
						<label>
							<?php esc_html_e( 'Product columns', 'woocommerce-product-recommendations' ); ?>
						</label>
						<div class="sw-form-content">
							<input type="number" name="deployment[<?php echo $form_index; ?>][columns]" class="form_columns"<?php echo isset( $options[ 'columns' ] ) ? ' value="' . $options[ 'columns' ] . '"' : ''; ?> placeholder="4" />
						</div>
					</div>

					<div class="sw-form-field sw-form-field--small">
						<label>
							<?php esc_html_e( 'Product rows', 'woocommerce-product-recommendations' ); ?>
						</label>
						<div class="sw-form-content">
							<input type="number" name="deployment[<?php echo $form_index; ?>][rows]" class="form_rows"<?php echo isset( $options[ 'rows' ] ) ? ' value="' . $options[ 'rows' ] . '"' : ''; ?> placeholder="1" />
						</div>
					</div>

					<div class="sw-form-os">
						<?php
						$args                 = array();
						$args[ 'index' ]      = $form_index;
						$args[ 'post_name' ]  = 'deployment[' . $form_index . ']';
						$args[ 'conditions' ] = isset( $options[ 'conditions' ] ) ? $options[ 'conditions' ] : array();

						WC_PRL()->conditions->get_admin_conditions_html( $options[ 'engine_type' ], $args );
						?>
					</div>

				</div>

			</div>
		</div><?php
	}

	/**
	 * Used to generate results on demand.
	 *
	 * @see 'WC_PRL_Background_Generator::task'
	 *
	 * @return void
	 */
	public function generate_background_results() {

		if ( ! empty( $this->deployments_for_background_generation ) ) {

			if ( ! is_object( $this->background_generator ) ) {
				$this->init_background_generator();
			}

			if ( ! $this->background_generator->is_running() ) {

				// Throttle the bg generator dispatch.
				if ( false === get_site_transient( 'wc_prl_bg_generator_manual_lock' ) ) {

					$data                     = array();
					$data[ 'deployment_ids' ] = $this->deployments_for_background_generation;

					$this->background_generator->push_to_queue( $data );
					$this->background_generator->save();

					// Remote post to self.
					$this->background_generator->dispatch();

					// Dispatch every 10 seconds...
					set_site_transient( 'wc_prl_bg_generator_manual_lock', microtime(), apply_filters( 'woocommerce_prl_throttle_background_generator_interval', 10 ) );
				}
			}
		}
	}

	/**
	 * Adds a deployment ID for the background generator.
	 *
	 * @param  WC_PRL_Deployment $deployment
	 * @return void
	 */
	public function schedule_deployment_generation( $deployment, $force = false ) {

		foreach ( $this->deployments_for_background_generation as $task_info ) {
			if ( $task_info[ 'id' ] === $deployment->get_id() ) {
				return;
			}
		}

		$this->deployments_for_background_generation[] = array(
			'id'            => $deployment->get_id(),
			'source_data'   => $deployment->get_source_data(),
			'is_contextual' => $deployment->has_contextual_engine(),
			'force'         => $force
		);
	}

	/**
	 * Instantiates the background generator class.
	 *
	 * @return void
	 */
	public function init_background_generator() {
		$this->background_generator = new WC_PRL_Background_Generator();
	}

	/**
	 * Check background generator status.
	 *
	 * @return void
	 */
	public function check_background_generator_status() {

		if ( ! isset( $this->background_generator ) ) {
			return;
		}

		if ( ! WC_PRL()->is_current_screen() ) {
			return;
		}

		if ( is_admin() && $this->background_generator->is_queue_full() ) {
			WC_PRL_Admin_Notices::add_notice( __( 'The regeneration queue of WooCommerce Product Recommendations is full. This will prevent engines from regenerating recommendations. Please contact support for assistance.', 'woocommerce-product-recommendations' ), 'error' );
		}
	}
}
