<?php
/**
 * WC_PRL_Templates class
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
 * Display functions and filters.
 *
 * @class    WC_PRL_Templates
 * @version  1.4.6
 */
class WC_PRL_Templates {

	/**
	 * Holds an instance of the WC_PRL_Deployment during runtime proccessing.
	 * @var WC_PRL_Deployment|NULL
	 */
	private $current_deployment;

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
	 * Setup hooks and functions.
	 */
	public function __construct() {

		$this->add_hooks();

		// Single product template functions and hooks.
		require_once( WC_PRL_ABSPATH . 'includes/wc-prl-template-functions.php' );
		require_once( WC_PRL_ABSPATH . 'includes/wc-prl-template-hooks.php' );

		// Front end scripts and JS templates.
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
	}

	/**
	 * Hook in.
	 */
	public function add_hooks() {
		add_action( 'wp', array( $this, 'add_deployment_hooks' ), 100 );
		add_action( 'woocommerce_prl_render_recommendations', array( $this, 'render_deployment' ), 10 );
	}

	/**
	 * Get the current deployment.
	 *
	 * @return WC_PRL_Deployment
	 */
	public function get_current_deployment() {
		return $this->current_deployment;
	}

	/**
	 * Check if there are any active deployments and hook them in.
	 */
	public function add_deployment_hooks() {

		foreach ( WC_PRL()->locations->get_locations() as $location ) {

			$hooks = $location->get_hooks( 'view' );

			foreach ( $hooks as $hook => $data ) {
				add_action( $hook, array( $this , 'display_recommendations' ), $data[ 'priority' ], $data[ 'args_number' ] );
			}

			if ( ! empty( $hooks ) ) {
				$location->set_load_status( true );
			}
		}
	}

	/**
	 * Render engines on each location.
	 */
	public function display_recommendations() {

		// Get action's current args.
		$args = func_get_args();

		// Transform shop hook.
		$hook = is_shop() ? current_action() . '_generic' : current_action();

		// Process.
		$this->process_hook( $hook, false, $args );
	}

	/**
	 * Process Location.
	 *
	 * @since 1.1.0
	 *
	 * @param  string  $hook
	 * @param  bool    $ajax
	 * @param  array   $args
	 * @return void
	 */
	public function process_hook( $hook, $ajax = false, $args = array() ) {

		// Prevent errors in Preview mode.
		if ( is_admin() ) {
			return;
		}

		// Load Location.
		$location = WC_PRL()->locations->get_location_by_hook( $hook );
		if ( ! $location ) {
			return;
		}

		// If this isn't ajax and shortcode/block and no_render mode is active, exit.
		if ( ! $ajax && ! isset( $args[ 'is_shortcode' ] ) && apply_filters( 'woocommerce_prl_prevent_rendering', false, $location ) ) {
			return;
		}

		// Display placeholder if HTML cache is enabled.
		if ( ! $ajax && wc_prl_render_using_ajax() ) {

			$environment = WC_PRL()->locations->get_environment();
			echo sprintf( '<div class="wc-prl-ajax-placeholder" id="%s" data-env="%s"></div>', $hook, esc_attr( json_encode( $environment ) ) );

			return;
		}

		// Load deployments.
		$deployments         = WC_PRL()->deployments->get_deployments( $hook, 'view', $args );
		$hook_data           = $location->get_hook_data();
		$max_deployments     = absint( $hook_data[ 'max_deployments' ] );
		$visible_deployments = 0;

		// Render deployments.
		foreach ( $deployments as $deployment ) {

			$conditions_applied = array();

			foreach ( $deployment->get_conditions_data() as $condition_data ) {

				$condition = WC_PRL()->conditions->get_condition( $condition_data[ 'id' ] );

				if ( $condition instanceof WC_PRL_Condition ) {

					$value = array(
						'object' => $condition,
						'data'   => $condition_data
					);

					$conditions_applied[] = $value;
				}
			}

			/**
			* Sort conditions by complexity value.
			*/
			usort( $conditions_applied, 'wc_prl_complexity_cmp' );

			$skip_deployment = false;

			foreach ( $conditions_applied as $condition_map ) {

				$condition      = $condition_map[ 'object' ];
				$condition_data = $condition_map[ 'data' ];

				if ( $condition && ! $condition->apply( $condition_data, $deployment ) ) {
					$skip_deployment = true;
					break;
				}
			}

			if ( $skip_deployment ) {
				if ( wc_prl_debug_enabled() ) {
					echo '<!-- Deployment #' . $deployment->get_id() . ' visibility blocked -->';
				}

				continue;
			}

			// Pre-Fetch and early exit.
			$products = $deployment->get_products();

			// Init.
			$this->current_deployment = $deployment;

			/*
			 * Render deployment
			 *
			 * @see WC_PRL_Templates::render_deployment()
			 */
			do_action( 'woocommerce_prl_render_recommendations', $products );

			// Reset.
			$this->current_deployment = null;

			$visible_deployments++;
			if ( $visible_deployments >= $max_deployments ) {
				break;
			}
		}
	}

	/**
	 * Renders the products template.
	 *
	 * @return void
	 */
	public function render_deployment( $products ) {

		$deployment = $this->get_current_deployment();
		if ( is_null( $deployment ) ) {
			return;
		}

		if ( empty( $products ) && ! is_null( $products ) ) {

			/**
			 * 'woocommerce_prl_disable_frontend_empty_status' filter.
			 *
			 * Whether or not to display a notice to store managers when the recommendation block has no results.
			 *
			 * @param bool false
			 */
			if ( apply_filters( 'woocommerce_prl_disable_frontend_empty_status', false ) ) {
				return;
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}

			ob_start();

			wc_get_template( 'global/placeholder.php', array(
				'deployment'            => $deployment,
				'message_class'         => 'woocommerce-noreviews',
				'message'               => sprintf(
					__( 'No products found to recommend in %s. The specified engine Filters or Amplifiers did not generate any results. Most amplifiers require a history of data to work. To ensure that some results will be generated when using these Amplifiers, please add a low-weight Freshness amplifier as fallback. <strong>Note:</strong> This message is visible to store managers only.', 'woocommerce-product-recommendations' ),
					$deployment->get_title() ? '"' . $deployment->get_title() . '"' : sprintf( __( 'Deployment #%d', 'woocommerce-product-recommendations' ), $deployment->get_id() )
				),
				'container_class'       => $this->get_deployment_container_class(),
				'container_attributes'  => $this->get_deployment_container_attributes()
			), false, WC_PRL()->get_plugin_path() . '/templates/' );

			$output = ob_get_clean();
			echo $output;

			return;

		} else if ( is_null( $products ) ) {

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}

			ob_start();

			wc_get_template( 'global/placeholder.php', array(
				'deployment'            => $deployment,
				'message_class'         => 'woocommerce-info',
				'message'               => sprintf(
					__( 'Now generating %s recommendations in the background. <strong>Note:</strong> This message is visible to store managers only.', 'woocommerce-product-recommendations' ),
					$deployment->get_title() ? '"' . $deployment->get_title() . '"' : sprintf( __( 'Deployment #%d', 'woocommerce-product-recommendations' ), $deployment->get_id() )
				),
				'container_class'       => $this->get_deployment_container_class(),
				'container_attributes'  => $this->get_deployment_container_attributes()
			), false, WC_PRL()->get_plugin_path() . '/templates/' );

			$output = ob_get_clean();
			echo $output;

			return;
		}

		// Alter global wc loop props.
		if ( ! isset( $GLOBALS[ 'woocommerce_loop' ] ) ) {
			wc_setup_loop();
		}

		$original_loop    = $GLOBALS[ 'woocommerce_loop' ];
		if ( isset( $GLOBALS[ 'product' ] ) ) {
			$original_product = $GLOBALS[ 'product' ];
		}

		wc_set_loop_prop( 'columns', $deployment->get_columns() );
		wc_set_loop_prop( 'is_shortcode', wc_string_to_bool( true ) );

		ob_start();

		wc_get_template( 'global/recommendations.php', array(
			'products'              => $products,
			'deployment'            => $deployment,
			'container_class'       => $this->get_deployment_container_class(),
			'container_attributes'  => $this->get_deployment_container_attributes(),
			'title_class'           => $this->get_deployment_title_class(),
			'title_level'           => $this->get_deployment_title_level()
		), false, WC_PRL()->get_plugin_path() . '/templates/' );

		$output = ob_get_clean();

		// Restore default loop props.
		$GLOBALS[ 'woocommerce_loop' ] = $original_loop;
		if ( isset( $original_product ) ) {
			$GLOBALS[ 'product' ] = $original_product;
		}

		/**
		 * 'woocommerce_prl_recommendations_html' filter.
		 *
		 * Alters the html output for every deployment.
		 *
		 * @param  string             $output
		 * @param  WC_PRL_Deployment  $deployment
		 * @param  array              $products
		 */
		$output = apply_filters( 'woocommerce_prl_recommendations_html', $output, $deployment, $products );

		echo $output;
	}

	/**
	 * Generate classes for the product grid container.
	 *
	 * @return string
	 */
	public function get_deployment_container_class() {

		$deployment = $this->get_current_deployment();

		if ( is_null( $deployment ) ) {
			return '';
		}

		$classes = array( 'wc-prl-recommendations' );

		// Add location based classes.
		$location = WC_PRL()->locations->get_location_by_hook( $deployment->get_hook() );
		if ( $location instanceof WC_PRL_Location ) {
			$data      = $location->get_hook_data();
			$classes[] = 'wc-prl-location-' . $data[ 'id' ];
			$classes[] = 'wc-prl-page-' . $location->get_location_id();

			// Add specific hook container classes.
			if ( ! empty( $data[ 'class' ] ) && is_array( $data[ 'class' ] ) ) {
				$classes = array_merge( $classes, $data[ 'class' ] );
			}
		}

		if ( is_null( $deployment->get_products() ) || empty( $deployment->get_products() ) ) {
			$classes[] = 'placeholder';
		}

		if ( $deployment->has_expired() ) {
			$classes[] = 'wc-prl-expired';
		}

		/**
		 * 'woocommerce_prl_recommendations_container_classes' filter.
		 *
		 * Alters the array of classes for every deployment container element.
		 *
		 * @param  array              $classes
		 * @param  WC_PRL_Deployment  $deployment
		 */
		$classes = (array) apply_filters( 'woocommerce_prl_recommendations_container_classes', $classes, $deployment );

		return implode( ' ', $classes );
	}

	/**
	 * Generate classes for the block title.
	 *
	 * @since  1.1.1
	 * @return string
	 */
	public function get_deployment_title_level() {

		$deployment = $this->get_current_deployment();

		if ( is_null( $deployment ) ) {
			return '';
		}

		$level = 2;

		// Add location based classes.
		$location = WC_PRL()->locations->get_location_by_hook( $deployment->get_hook() );
		if ( $location instanceof WC_PRL_Location ) {
			$data = $location->get_hook_data();

			// Add specific hook container classes.
			if ( ! empty( $data[ 'title_level' ] ) && is_numeric( $data[ 'title_level' ] ) ) {
				$level = (int) $data[ 'title_level' ];
			}
		}

		/**
		 * 'woocommerce_prl_recommendations_title_class' filter.
		 *
		 * Heading Level for the title of the deployment.
		 *
		 * @param  int                $level
		 * @param  WC_PRL_Deployment  $deployment
		 */
		$level = (int) apply_filters( 'woocommerce_prl_recommendations_title_level', $level, $deployment );

		return $level;
	}

	/**
	 * Generate classes for the block title.
	 *
	 * @since  1.1.1
	 * @return string
	 */
	public function get_deployment_title_class() {

		$deployment = $this->get_current_deployment();

		if ( is_null( $deployment ) ) {
			return '';
		}

		$classes = array( 'wc-prl-title' );

		// Add location based classes.
		$location = WC_PRL()->locations->get_location_by_hook( $deployment->get_hook() );
		if ( $location instanceof WC_PRL_Location ) {
			$data = $location->get_hook_data();

			// Add specific hook container classes.
			if ( ! empty( $data[ 'title_class' ] ) && is_array( $data[ 'title_class' ] ) ) {
				$classes = array_merge( $classes, $data[ 'title_class' ] );
			}
		}

		/**
		 * 'woocommerce_prl_recommendations_title_class' filter.
		 *
		 * Classes for the title class of the deployment.
		 *
		 * @param  array              $classes
		 * @param  WC_PRL_Deployment  $deployment
		 */
		$classes = apply_filters( 'woocommerce_prl_recommendations_title_class', $classes, $deployment );

		return implode( ' ', $classes );
	}

	/**
	 * Generate data attributes for the product grid container.
	 *
	 * @return string
	 */
	public function get_deployment_container_attributes() {

		$deployment = $this->get_current_deployment();

		if ( is_null( $deployment ) ) {
			return '';
		}

		$attributes = array(
			'data-engine'        => $deployment->get_engine_id(),
			'data-location-hash' => substr( md5( $deployment->get_hook() ), 0, 7 ),
			'data-source-hash'   => $deployment->get_tracking_source_hash()
		);

		/**
		 * 'woocommerce_prl_recommendations_container_attributes' filter.
		 *
		 * Data attributes for the container element.
		 *
		 * @param  array              $attributes
		 * @param  WC_PRL_Deployment  $deployment
		 */
		$attributes = apply_filters( 'woocommerce_prl_recommendations_container_attributes', $attributes, $deployment );

		$outputs = array();
		foreach ( $attributes as $key => $value ) {
			$outputs[] = esc_html( $key ) . '="' . wc_clean( $value ) . '"';
		}
		return implode( ' ', $outputs );
	}

	/**
	 * Front-end styles and scripts.
	 */
	public function frontend_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Styles
		wp_register_style( 'wc-prl-css', WC_PRL()->get_plugin_url() . '/assets/css/frontend/woocommerce.css', false, WC_PRL()->get_plugin_version(), 'all' );
		wp_style_add_data( 'wc-prl-css', 'rtl', 'replace' );
		wp_enqueue_style( 'wc-prl-css' );

		$dependencies = array( 'jquery' );

		/**
		 * Filter to allow adding custom script dependencies here.
		 *
		 * @param  array  $dependencies
		 */
		$dependencies = apply_filters( 'woocommerce_prl_script_dependencies', $dependencies );

		wp_register_script( 'wc-prl-main', WC_PRL()->get_plugin_url() . '/assets/js/frontend/wc-prl-main' . $suffix . '.js', $dependencies, WC_PRL()->get_plugin_version(), true );

		wp_enqueue_script( 'wc-prl-main' );

		/**
		 * Filter front-end params.
		 *
		 * @param  array  $params
		 */
		$params = apply_filters( 'woocommerce_prl_front_end_params', array(
			'version'                        => WC_PRL()->get_plugin_version(),
			'security_view_event_nonce'      => wp_create_nonce( 'write-view-event' ),
			'security_click_event_nonce'     => wp_create_nonce( 'write-click-event' ),
			'shopping_session_seconds'       => wc_prl_get_shopping_session_interval(),
			'deployment_interest_time'       => wc_prl_get_view_tracking_threshold(),
			'views_max_cookie_num'           => wc_prl_get_views_max_cookie_num(),
			'clicks_max_cookie_num'          => wc_prl_get_clicks_max_cookie_num(),
			'recently_views_max_cookie_num'  => wc_prl_get_recently_views_max_cookie_num(),
			'tracking_enabled'               => wc_prl_tracking_enabled() ? 'yes' : 'no',
			'ajax_add_to_cart'               => 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ? 'yes' : 'no',
			'script_debug'                   => 'no'
		) );

		wp_localize_script( 'wc-prl-main', 'wc_prl_params', $params );
	}
}
