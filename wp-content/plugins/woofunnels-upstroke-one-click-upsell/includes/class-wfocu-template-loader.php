<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class contains the basic functions responsible for front end views.
 * Class WFOCU_View
 */
class WFOCU_Template_loader {

	private static $ins = null;
	private $installed_plugins = null;
	public $current_template = null;
	public $customizer_key_prefix = '';
	public $offer_id = null;
	public $is_single = false;
	public $product_data = null;
	public $offer_data = null;
	public $invalidation_reason = null;
	public $multiple_p = false;
	/**
	 * @var WFOCU_Template_Group
	 */
	public $current_template_group;
	protected $customize_manager_ins = null;
	protected $template_groups = [];
	protected $templates = array();

	public function __construct() {

		add_action( 'template_redirect', function () {
			add_filter( 'template_include', array( $this, 'maybe_load' ), 98 ); //phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UndefinedVariable
		}, 999 );

		add_action( 'wfocu_header_print_in_head', array( $this, 'typography_custom_css' ) );

		$post_type = $this->get_post_type_slug();
		add_filter( "theme_{$post_type}_templates", [ $this, 'add_upstroke_page_templates' ], 99, 4 );

		add_action( 'wp', array( $this, 'initiate_offer_template_setup' ), 9 );
		/** Template common */
		add_action( 'wfocu_before_template_load', array( $this, 'add_common_scripts' ) );
		add_action( 'wfocu_header_print_in_head', array( $this, 'add_fonts' ) );
		add_filter( 'wfocu_offer_product_data', array( $this, 'maybe_add_variations' ), 1, 5 );
		add_action( 'init', array( $this, 'maybe_setup_offer' ), 15 );

		add_action( 'wfocu_front_template_after_validation_success', array( $this, 'set_data_object' ) );

		add_action( 'wfocu_footer_after_print_scripts', array( $this, 'maybe_print_notices_in_hidden' ) );
		add_action( 'wfocu_footer_after_print_scripts', array( $this, 'maybe_log_rendering_complete' ), 999 );
		add_action( 'wfocu_front_template_after_validation_success', array( $this, 'empty_shortcodes' ) );

		/**
		 * Modify template if necessary
		 */
		add_action( 'wfocu_offer_updated', array( $this, 'maybe_autoswitch_templates' ), 10, 3 );
		add_action( 'wp_footer', array( $this, 'maybe_render_variation_forms' ) );
		add_action( 'wfocu_header_print_in_head', array( $this, 'print_internal_css' ), 99 );
		add_action( 'wfocu_header_print_in_head', array( $this, 'print_internal_css' ), 99 );
		add_action( 'wp_footer', array( $this, 'maybe_render_css_for_offer_conf' ), 999 );

	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}


	/**
	 * @param $slug
	 * @param $data
	 * @param string $depriciated
	 */
	public function register_template( $slug, $data, $depriciated = '' ) {

		$data = wp_parse_args( $data, array(
			'name'        => __( 'No title Template', 'woofunnels-upstroke-one-click-upsell' ),
			'thumbnail'   => '',
			'is_multiple' => ( 'multiple' === $depriciated ) ? true : false,
		) );
		if ( '' !== $slug && ! empty( $data ) ) {
			$this->templates[ $slug ] = $data;

		}
	}

	/**
	 * @hooked over `template_include`
	 * This method checks for the current running funnels and controller to setup data & offer validation
	 * It also loads and echo/prints current template if the offer demands to.
	 *
	 * @param $template string current template in WordPress ecosystem
	 *
	 * @return mixed
	 */
	public function maybe_load( $template ) {

		if ( WFOCU_Core()->public->if_is_offer() ) {

			/**
			 * Run validation
			 */

			if ( ! WFOCU_Core()->public->if_is_preview() ) {
				$validation_result = WFOCU_Core()->offers->validate_product_offers( $this->product_data );

				$validation_result = apply_filters( 'wfocu_offer_validation_result', $validation_result, $this->product_data );

				if ( false === $validation_result ) {

					$get_current_offer = WFOCU_Core()->data->get_current_offer();
					$get_order         = WFOCU_Core()->data->get_current_order();

					$get_type_of_offer       = WFOCU_Core()->data->get( '_current_offer_type' );
					$get_type_index_of_offer = WFOCU_Core()->data->get( '_current_offer_type_index' );

					do_action( 'wfocu_offer_skipped_event', $get_current_offer, WFOCU_WC_Compatibility::get_order_id( $get_order ), WFOCU_Core()->data->get_funnel_id(), $get_type_of_offer, $get_type_index_of_offer, WFOCU_Core()->data->get( 'useremail' ), $this->invalidation_reason );

					$get_offer = WFOCU_Core()->offers->get_the_next_offer();
					$redirect  = WFOCU_Core()->public->get_the_upsell_url( $get_offer );

					WFOCU_Core()->log->log( 'Offer Validation failed, Moving to next offer/order-received, moving to ' . $get_offer );

					WFOCU_Core()->data->set( 'current_offer', $get_offer );
					WFOCU_Core()->data->save();
					wp_redirect( $redirect );
					die();
				}
			}
			/**
			 * If there is a custom page assign to the current loading offer, then discard here and fire a hook
			 */
			if ( WFOCU_Core()->offers->is_custom_page ) {

				do_action( 'wfocu_front_before_custom_offer_page' );
				add_action( 'wp_footer', array( $this, 'maybe_print_notices_in_hidden' ) );

				return $template;
			}

			if ( ! empty( $this->current_template_group ) ) {
				$template = $this->current_template_group->maybe_get_template( $template );
				$template = $this->may_be_change_template( $template );
			}
		}

		return $template;

	}


	public function add_upstroke_page_templates() {

		$box_template    = WFOCU_Common::get_boxed_template();
		$canvas_template = WFOCU_Common::get_canvas_template();

		return [
			$box_template    => __( 'Upstroke Boxed', 'woofunnels-upstroke-one-click-upsell' ),
			$canvas_template => __( 'Upstroke-Template for Page Builder', 'woofunnels-upstroke-one-click-upsell' )
		];
	}

	public function may_be_change_template( $template ) {
		global $post;
		if ( ! is_null( $post ) && $post->post_type === $this->get_post_type_slug() ) {
			$template = $this->get_template_url( $template );
		}

		return $template;
	}

	public function get_template_url( $main_template ) {
		global $post;
		$wfocu_id      = $post->ID;
		$page_template = apply_filters( 'bwf_page_template', get_post_meta( $wfocu_id, '_wp_page_template', true ), $wfocu_id );

		$file         = '';
		$body_classes = [];

		$box_template    = WFOCU_Common::get_boxed_template();
		$canvas_template = WFOCU_Common::get_canvas_template();

		switch ( $page_template ) {
			case $box_template:
				$file           = $this->get_module_path() . 'templates/' . $box_template;
				$body_classes[] = $page_template;
				break;

			case $canvas_template:
				$file           = $this->get_module_path() . 'templates/' . $canvas_template;
				$body_classes[] = $page_template;
				break;

			default:
				/**
				 * Remove Next/Prev Navigation
				 */ add_filter( 'next_post_link', '__return_empty_string' );
				add_filter( 'previous_post_link', '__return_empty_string' );

				$page = locate_template( array( 'page.php' ) );

				if ( ! empty( $page ) ) {
					$file = $page;
				}
				break;
		}
		if ( ! empty( $body_classes ) ) {
			add_filter( 'body_class', [ $this, 'wfocu_add_unique_class' ], 9999, 1 );
		}

		if ( file_exists( $file ) ) {
			return $file;
		}

		return $main_template;
	}

	public function wfocu_add_unique_class( $classes ) {
		array_push( $classes, 'wfocu-page-template' );

		return $classes;
	}

	public function get_module_path() {
		return plugin_dir_path( WFOCU_PLUGIN_FILE ) . 'includes/';
	}

	public function get_post_type_slug() {
		return WFOCU_Common::get_offer_post_type_slug();
	}


	public function load_footer() {
		$this->get_template_part( 'footer-end' );
	}

	public function get_template_part( $slug, $args = '' ) {
		if ( ! empty( $args ) && ( is_array( $args ) || is_object( $args ) ) ) {
			extract( array( 'data' => $args ) ); //phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		if ( '/' !== substr( $slug, 0, 1 ) ) {
			$slug = '/' . $slug;
		}
		if ( file_exists( plugin_dir_path( WFOCU_PLUGIN_FILE ) . 'views' . $slug . '.php' ) ) {
			$located = plugin_dir_path( WFOCU_PLUGIN_FILE ) . 'views' . $slug . '.php';
		} else {
			$located = apply_filters( 'wfocu_get_template_part_path', plugin_dir_path( WFOCU_PLUGIN_FILE ) . 'views' . $slug . '.php', $slug, $args );

		}
		if ( ! file_exists( $located ) ) {
			/* translators: %s template */
			wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'woofunnels-upstroke-one-click-upsell' ), '<code>' . $located . '</code>' ), '2.1' );

			return;
		}
		include $located; //@codingStandardsIgnoreLine
	}

	public function load_header() {
		$this->get_template_part( 'header' );
	}

	/**
	 * @param string $is_single
	 *
	 * @return array
	 */
	public function get_templates() {
		return $this->templates;
	}

	public function get_buy_button_url( $product_hash, $product_data ) { //phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
		ob_start();
		?>
		<a class="wfocu_upsell" href="javascript:void(0);" data-key="<?php echo esc_attr( $product_hash ); ?>"> Add to my order</a>
		<?php
		return ob_get_clean();
	}

	public function get_skip_button_url( $product_hash, $product_data ) { //phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
		ob_start();

		?>
		<a class="wfocu_skip_offer" href="javascript:void(0);" data-key="<?php echo esc_attr( $product_hash ); ?>"> SKip the offer</a>
		<?php
		return ob_get_clean();
	}

	public function body_classes() {
		$body_classes = apply_filters( 'wfocu_view_body_classes', array() );

		return implode( ' ', $body_classes );
	}

	public function typography_custom_css() {
		$style_custom_css = WFOCU_Common::get_option( 'wfocu_custom_css_css_code' );

		printf( '<style>%s</style>', esc_html( $style_custom_css ) );
	}

	public function add_common_scripts() {
		if ( false === is_customize_preview() ) {
			WFOCU_Core()->assets->setup_assets( 'offer' );
		} else {
			WFOCU_Core()->assets->setup_assets( 'customizer-preview' );
		}
	}

	public function add_fonts() {
		?>
		<link href="//fonts.googleapis.com/css?family=Oswald:300,400,500,600,700" rel="stylesheet"> <?php //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
		<link href="//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet"> <?php //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
		<?php
	}

	public function maybe_add_variations( $product, $output, $offer_data, $is_front, $hash ) {

		if ( ( 'variable' === $product->data->get_type() || 'variable-subscription' === $product->data->get_type() ) && true === $is_front ) {

			$attributes = $product->data->get_variation_attributes();

			$attribute_keys             = array_keys( $attributes );
			$prices                     = array();
			$available_variation_stock  = array();
			$prepare_dimension_hash     = array();
			$weight_html                = array();
			$dimensions_html            = array();
			$available_variations       = array();
			$images                     = array();
			$all_common_attribute_slugs = array();
			$variation_objects          = array();
			if ( isset( $offer_data->variations ) && isset( $offer_data->variations->{$hash} ) && is_array( $offer_data->variations->{$hash} ) ) {
				add_filter( 'woocommerce_available_variation', array( $this, 'add_variation_object_in_custom_variation_key' ), 10, 3 );

				foreach ( $offer_data->variations->{$hash} as $variation => $variation_data ) {
					$variation = $product->data->get_available_variation( $variation );

					if ( false === $variation ) {
						continue;
					}

					// Enable or disable the add to cart button
					if ( ! $variation['is_purchasable'] || ! $variation['is_in_stock'] || ! $variation['variation_is_visible'] ) {

						continue;
					}

					$current_stock = null;
					if ( isset( $variation['is_in_stock'] ) && ( isset( $variation['max_qty'] ) && '' !== $variation['max_qty'] ) ) {
						$current_stock = $variation['max_qty'];
						$offer_qty     = (int) $product->quantity;

						if ( $current_stock < $offer_qty ) {

							continue;
						}
					}

					$attributes_json = array_combine( array_map( function ( $k ) {

						return '@' . WFOCU_Common::clean_ascii_characters( $k );
					}, array_keys( $variation['attributes'] ) ), array_map( function ( $k ) {

						return WFOCU_Common::handle_single_quote_variation( $k );
					}, $variation['attributes'] ) );

					$keys = array_keys( $variation['attributes'] );
					array_walk( $keys, function ( $k ) use ( &$all_common_attribute_slugs ) {

						$all_common_attribute_slugs[ WFOCU_Common::clean_ascii_characters( $k ) ] = $k;

					} );

					$attributes_json['id'] = $variation['variation_id'];

					$prepare_dimension_hash[ $variation['variation_id'] ]    = md5( wp_json_encode( array(
						$variation['dimensions_html'],
						$variation['weight_html'],
					) ) );
					$available_variations[ $variation['variation_id'] ]      = $attributes_json;
					$available_variation_stock[ $variation['variation_id'] ] = $current_stock;
					$weight_html[ $variation['variation_id'] ]               = $variation['weight_html'];
					$images[ $variation['variation_id'] ]                    = $variation['image_id'];
					$dimensions_html[ $variation['variation_id'] ]           = $variation['dimensions_html'];
					$variation_settings                                      = new stdClass();
					$variation_settings->quantity                            = $product->quantity;
					$variation_settings->discount_type                       = WFOCU_Common::get_discount_setting( $product->discount_type );
					$variation_settings->discount_amount                     = $variation_data->discount_amount;

					$prices[ $variation['variation_id'] ]            = apply_filters( 'wfocu_variation_prices', array(
						'price_incl_tax'             => WFOCU_Core()->offers->get_product_price( $variation['_wfocu_variation_object'], $variation_settings, true, $offer_data ),
						'price_incl_tax_raw'         => WFOCU_Core()->offers->get_product_price( $variation['_wfocu_variation_object'], $variation_settings, true, $offer_data ),
						'price_excl_tax'             => WFOCU_Core()->offers->get_product_price( $variation['_wfocu_variation_object'], $variation_settings, false, $offer_data ),
						'price_excl_tax_raw'         => WFOCU_Core()->offers->get_product_price( $variation['_wfocu_variation_object'], $variation_settings, false, $offer_data ),
						'regular_price_incl_tax'     => wc_get_price_including_tax( $variation['_wfocu_variation_object'], array( 'price' => $variation['_wfocu_variation_object']->get_regular_price() ) ) * $variation_settings->quantity,
						'regular_price_incl_tax_raw' => wc_get_price_including_tax( $variation['_wfocu_variation_object'], array( 'price' => $variation['_wfocu_variation_object']->get_regular_price() ) ) * $variation_settings->quantity,
						'regular_price_excl_tax'     => wc_get_price_excluding_tax( $variation['_wfocu_variation_object'], array( 'price' => $variation['_wfocu_variation_object']->get_regular_price() ) ) * $variation_settings->quantity,
						'regular_price_excl_tax_raw' => wc_get_price_excluding_tax( $variation['_wfocu_variation_object'], array( 'price' => $variation['_wfocu_variation_object']->get_regular_price() ) ) * $variation_settings->quantity,

					), $variation['_wfocu_variation_object'], $product );
					$variation_objects[ $variation['variation_id'] ] = $variation['_wfocu_variation_object'];

				}
			} else {
				add_filter( 'woocommerce_available_variation', array( $this, 'add_variation_object_in_custom_variation_key' ), 10, 3 );

				$all_variations = $product->data->get_available_variations();

				foreach ( $all_variations as $variation ) {

					if ( false === $variation ) {
						continue;
					}

					// Enable or disable the add to cart button
					if ( ! $variation['is_purchasable'] || ! $variation['is_in_stock'] || ! $variation['variation_is_visible'] ) {

						continue;
					}

					$current_stock = null;

					if ( isset( $variation['is_in_stock'] ) && ( isset( $variation['max_qty'] ) && '' !== $variation['max_qty'] ) ) {
						$current_stock = $variation['max_qty'];
						$offer_qty     = (int) $product->quantity;

						if ( $current_stock < $offer_qty ) {

							continue;
						}
					}

					$attributes_json = array_combine( array_map( function ( $k ) {

						return '@' . WFOCU_Common::clean_ascii_characters( $k );
					}, array_keys( $variation['attributes'] ) ), array_map( function ( $k ) {

						return WFOCU_Common::handle_single_quote_variation( $k );
					}, $variation['attributes'] ) );

					$keys = array_keys( $variation['attributes'] );
					array_walk( $keys, function ( $k ) use ( &$all_common_attribute_slugs ) {

						$all_common_attribute_slugs[ WFOCU_Common::clean_ascii_characters( $k ) ] = $k;

					} );

					$attributes_json['id'] = $variation['variation_id'];

					$prepare_dimension_hash[ $variation['variation_id'] ]    = md5( wp_json_encode( array(
						$variation['dimensions_html'],
						$variation['weight_html'],
					) ) );
					$available_variations[ $variation['variation_id'] ]      = $attributes_json;
					$available_variation_stock[ $variation['variation_id'] ] = $current_stock;
					$weight_html[ $variation['variation_id'] ]               = $variation['weight_html'];
					$images[ $variation['variation_id'] ]                    = $variation['image_id'];
					$dimensions_html[ $variation['variation_id'] ]           = $variation['dimensions_html'];
					$variation_settings                                      = new stdClass();
					$variation_settings->quantity                            = $product->quantity;
					$variation_settings->discount_type                       = WFOCU_Common::get_discount_setting( $product->discount_type );
					$variation_settings->discount_amount                     = $product->discount_amount;

					$prices[ $variation['variation_id'] ]            = apply_filters( 'wfocu_variation_prices', array(
						'price_incl_tax'             => WFOCU_Core()->offers->get_product_price( $variation['_wfocu_variation_object'], $variation_settings, true ),
						'price_incl_tax_raw'         => WFOCU_Core()->offers->get_product_price( $variation['_wfocu_variation_object'], $variation_settings, true ),
						'price_excl_tax'             => WFOCU_Core()->offers->get_product_price( $variation['_wfocu_variation_object'], $variation_settings, false ),
						'price_excl_tax_raw'         => WFOCU_Core()->offers->get_product_price( $variation['_wfocu_variation_object'], $variation_settings, false ),
						'regular_price_incl_tax'     => wc_get_price_including_tax( $variation['_wfocu_variation_object'], array( 'price' => $variation['_wfocu_variation_object']->get_regular_price() ) ) * $variation_settings->quantity,
						'regular_price_incl_tax_raw' => wc_get_price_including_tax( $variation['_wfocu_variation_object'], array( 'price' => $variation['_wfocu_variation_object']->get_regular_price() ) ) * $variation_settings->quantity,
						'regular_price_excl_tax'     => wc_get_price_excluding_tax( $variation['_wfocu_variation_object'], array( 'price' => $variation['_wfocu_variation_object']->get_regular_price() ) ) * $variation_settings->quantity,
						'regular_price_excl_tax_raw' => wc_get_price_excluding_tax( $variation['_wfocu_variation_object'], array( 'price' => $variation['_wfocu_variation_object']->get_regular_price() ) ) * $variation_settings->quantity,

					), $variation['_wfocu_variation_object'] );
					$variation_objects[ $variation['variation_id'] ] = $variation['_wfocu_variation_object'];

				}
			}
			WFOCU_Core()->data->set( 'attribute_variation_stock_' . $hash, $all_common_attribute_slugs, 'variations' );
			WFOCU_Core()->data->save( 'variations' );
			$product->variations_data = array(
				'available_variations'      => $available_variations,
				'attributes'                => $attributes,
				'attribute_keys'            => $attribute_keys,
				'available_variation_stock' => $available_variation_stock,
				'prices'                    => $prices,
				'default'                   => ( isset( $offer_data->fields->{$hash}->default_variation ) && ! empty( $offer_data->fields->{$hash}->default_variation ) && true === array_key_exists( $offer_data->fields->{$hash}->default_variation, $available_variations ) ) ? $offer_data->fields->{$hash}->default_variation : key( $available_variations ),
				'shipping_hash'             => $prepare_dimension_hash,
				'weight_htmls'              => $weight_html,
				'dimension_htmls'           => $dimensions_html,
				'images'                    => $images,
				'variation_objects'         => $variation_objects,
			);

		}

		return $product;

	}

	/**
	 * @param WP_Customize_Manager $customize_manager
	 */
	public function maybe_add_customize_preview_init( $customize_manager ) {
		$this->customize_manager_ins = $customize_manager;
	}

	public function load_customizer_styles() {
		if ( ( 'loaded' === filter_input( INPUT_GET, 'wfocu_customize', FILTER_SANITIZE_STRING ) ) && $this->customize_manager_ins instanceof WP_Customize_Manager ) {
			$this->customize_manager_ins->customize_preview_loading_style();
			$this->customize_manager_ins->remove_frameless_preview_messenger_channel();
		}
	}

	public function load_customizer_footer_before_scripts() {
		if ( ( 'loaded' === filter_input( INPUT_GET, 'wfocu_customize', FILTER_SANITIZE_STRING ) ) && $this->customize_manager_ins instanceof WP_Customize_Manager ) {
			$this->customize_manager_ins->customize_preview_settings();
			$this->customize_manager_ins->selective_refresh->export_preview_data();
		}
	}

	public function customizer_product_check() {

		if ( $this->is_valid_state_for_data_setup() ) {

			$variation_field = true;
			$offer_data      = $this->product_data;

			$temp_product = get_object_vars( $offer_data->products );
			if ( is_array( $temp_product ) && count( $temp_product ) > 0 ) {
				/** Checking for variation single product */
				if ( is_array( $temp_product ) && count( $temp_product ) > 1 ) {
					$variation_field = false;
				} else {
					/** Only 1 product */
					foreach ( $offer_data->products as $hash_key => $product ) {

						if ( isset( $product->id ) && $product->id > 0 ) {
							$product_obj = wc_get_product( $product->id );

							$this->current_template->products_data[ $hash_key ] = array(
								'id'  => $product->id,
								'obj' => $product_obj,
							);

							/** Checking if product variation and single product */
							$product_type = $product_obj->get_type();
							if ( ! in_array( $product_type, WFOCU_Common::get_variable_league_product_types(), true ) ) {
								$variation_field = false;
							}
						}
					}
				}
			} else {
				if ( $this->is_customizer_preview() ) {
					wp_die( esc_attr__( 'Your offer must have at least one product to show preview.', 'woofunnels-upstroke-one-click-upsell' ) );

				}
			}

			if ( ! is_null( $this->current_template ) ) {

				$this->current_template->variation_field = $variation_field;
			}
		}
	}

	/**
	 * Finds out if its safe to initiate data setup for the current request.
	 * Checks for the environmental conditions and provide results.
	 * @return bool true on success| false otherwise
	 * @see WFOCU_Template_loader::maybe_setup_offer()
	 */
	public function is_valid_state_for_data_setup() {

		if ( WFOCU_AJAX_Controller::is_wfocu_front_ajax() ) {
			return true;
		}

		if ( $this->is_customizer_preview() && ( null !== $this->offer_id && 0 !== $this->offer_id && false !== $this->offer_id ) ) {
			return true;
		}

		if ( true === WFOCU_Core()->public->is_front() && ( null !== $this->offer_id && 0 !== $this->offer_id && false !== $this->offer_id ) ) {
			return true;
		}

		return apply_filters( 'wfocu_valid_state_for_data_setup', false );

	}

	public function is_customizer_preview() {
		if ( isset( $_REQUEST['wfocu_customize'] ) && 'loaded' === $_REQUEST['wfocu_customize'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		return false;
	}

	public function print_internal_css() {
		if ( ! empty( $this->current_template->internal_css ) ) {
			include WFOCU_PLUGIN_DIR . '/views/internal-css.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
		}
	}

	/**
	 * @hooked over `woocommerce_available_variation`
	 * Adds a variation object in the variation array on the upsell pages
	 *
	 * @param $variation_array
	 * @param $variation_object
	 * @param $variation
	 *
	 * @return mixed
	 */
	public function add_variation_object_in_custom_variation_key( $variation_array, $variation_object, $variation ) {

		$variation_array['_wfocu_variation_object'] = $variation;

		return $variation_array;

	}

	/**
	 * @hooked over `footer_after_print_scripts`
	 * @hooked over `wp_footer` conditionaly
	 * We need to take care of the notices in our template so that it wont be visible to the user as well as it will not get forward by the WC for another page
	 * We are printing notices in the html in non display mode
	 */
	public function maybe_print_notices_in_hidden() {
		?>
		<div class="wfocu-wc-notice-wrap" style="display: none; !important;">
			<?php wc_print_notices(); ?>
		</div>
		<?php
	}

	public function empty_shortcodes() {
		if ( true === apply_filters( 'wfocu_allow_externals_on_customizer', false ) ) {
			return;
		}
		global $shortcode_tags;
		$new_shortcode_tags = $shortcode_tags;
		$tags_to_have       = array_keys( $new_shortcode_tags );
		foreach ( $tags_to_have as $tag ) {

			if ( false === strpos( $tag, 'wfocu_' ) ) {
				unset( $shortcode_tags[ $tag ] );
			}
		}

	}

	public function add_attributes_to_buy_button() {
		global $buy_button_count;
		if ( null === $buy_button_count ) {
			$buy_button_count = 1;
		} else {
			$buy_button_count ++;
		}
		$attributes     = apply_filters( 'wfocu_front_buy_button_attributes', array(), $buy_button_count );
		$attributes_str = '';
		foreach ( $attributes as $attr => $val ) {
			$attributes_str .= sprintf( ' %1$s=%3$s%2$s%3$s', $attr, $val, '"' );
		}

		echo $attributes_str; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function add_attributes_to_confirmation_button() {
		$attributes     = apply_filters( 'wfocu_front_confirmation_button_attributes', array() );
		$attributes_str = '';
		foreach ( $attributes as $attr => $val ) {
			$attributes_str .= sprintf( ' %1$s=%3$s%2$s%3$s', $attr, $val, '"' );
		}

		echo $attributes_str; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function maybe_autoswitch_templates( $offer_data, $offer_id, $funnel_id ) {

		/**
		 * Bail out if no products in there
		 */
		if ( count( get_object_vars( $offer_data->products ) ) === 0 ) {
			return;
		}

		$count = count( get_object_vars( $offer_data->products ) );

		/**
		 * return if we do not need any switching
		 */
		if ( ! isset( $offer_data->template ) ) {
			return;
		}

		if ( '' === $offer_data->template ) {
			return;
		}

		if ( false === $this->template_needs_switching( $count, $offer_data->template ) ) {
			return;
		}

		/**
		 * Get default templates
		 */
		if ( count( get_object_vars( $offer_data->products ) ) === 1 ) {
			$offer_data->template = $this->get_default_single_template();

		} else {
			$offer_data->template = $this->get_default_multiple_template();
		}

		/**
		 * Save the modified template
		 */
		WFOCU_Common::update_offer( $offer_id, $offer_data, $funnel_id );

	}

	/**
	 * Checks if current template set matches the product count
	 *
	 * @param $count
	 * @param $current_template
	 *
	 * @return bool
	 */
	public function template_needs_switching( $count, $current_template ) {

		/**
		 * if current template is mp and count is 1
		 */
		if ( 0 === strpos( $current_template, 'mp' ) && 1 === $count ) {
			return true;
		}

		/**
		 * if current template is sp and count is greater than one
		 */
		if ( 0 === strpos( $current_template, 'sp' ) && 1 < $count ) {
			return true;
		}

		return false;

	}

	public function get_default_single_template() {
		return 'sp-classic';
	}

	public function get_default_multiple_template() {
		return 'mp-grid';
	}

	/**
	 * Offer templates rendered successfully, let the system log it in the file
	 */
	public function maybe_log_rendering_complete() {
		$is_preview = WFOCU_Core()->public->if_is_preview();
		$offer_id   = WFOCU_Core()->template_loader->get_offer_id();
		if ( false === $is_preview ) {
			WFOCU_Core()->log->log( 'Offer: #' . $offer_id . ' Page Rendered successfully' );
		}
	}

	public function get_offer_id() {
		return $this->offer_id;
	}

	public function set_offer_id( $id ) {
		$this->offer_id = $id;
	}

	public function get_template( $slug ) {

		return $this->templates[ $slug ];
	}

	public function register_group( $group, $slug ) {

		if ( ! $group instanceof WFOCU_Template_Group ) {
			return;
		}
		$this->template_groups[ $slug ] = $group;

	}

	/**
	 * @hooked over WP:9
	 * Control setup of respective offer during wp hook to control asset loading as well as proper offer data setup
	 */
	public function initiate_offer_template_setup() {

		if ( false === is_admin() ) {
			global $post;

			if ( $this->is_customizer_preview() ) {
				return;
			}
			if ( false === is_object( $post ) ) {
				return;
			}

			if ( $post->post_type === WFOCU_Common::get_offer_post_type_slug() ) {
				$maybe_offer_id  = $post->ID;
				$this->is_single = true;
			} elseif ( $post->post_type === 'page' ) {
				$get_page_id    = $post->ID;
				$maybe_offer_id = get_post_meta( $get_page_id, '_wfocu_offer', true );
			}

			if ( empty( $maybe_offer_id ) ) {
				return;
			}

			$this->setup_complete_offer_setup_manual( $maybe_offer_id, true, true );

		}

	}

	/**
	 * By default when funnel runs after order, all the setup required methods hits accordingly
	 * When accessing the page directly Or in admin interface during customizing pages using any builder or native editors, we could setup data using this method
	 *
	 * @param mixed $offer_id Offer ID to set data against
	 * @param bool $is_preview should we setup data as preview setup or as live running funnel?
	 *
	 * @since 2.0
	 */
	public function setup_complete_offer_setup_manual( $offer_id = false, $is_preview = true, $load_assets = false ) {

		if ( empty( $offer_id ) ) {
			return;
		}

		if ( did_action( 'wfocu_offer_setup_completed' ) > 0 ) {

			/**
			 * When offer setup already completed we just need to register our assets as we know its either custom page or single offer page front request
			 */
			WFOCU_Core()->assets->maybe_register_assets( [], '', true );

			/**
			 * Check if its a customizer template or not
			 */
			if ( true === WFOCU_Core()->template_loader->get_template_ins() instanceof WFOCU_Customizer_Common ) {

				do_action( 'wfocu_front_before_customizer_page_load' );

			} elseif ( false === is_null( $this->current_template_group ) ) {
				/**
				 * its a single post request with a non-customier template
				 */
				do_action( 'wfocu_front_before_single_page_load' );
			}

			return;
		}

		/**
		 * Setoffer ID to the template loader
		 */
		WFOCU_Core()->template_loader->set_offer_id( $offer_id );

		/**
		 * Tell the class that is the valid state for the data setup
		 */
		add_filter( 'wfocu_valid_state_for_data_setup', '__return_true' );

		/**
		 * load the data as preview that will hold certain functionalities to run especially JS driven
		 */
		if ( $is_preview ) {
			WFOCU_Core()->public->is_preview = true;
		}

		/**
		 * Sets up template and data
		 */
		WFOCU_Core()->template_loader->maybe_setup_offer();

		/**
		 * if its a customizer request?
		 */
		if ( WFOCU_Core()->template_loader->get_template_ins() instanceof WFOCU_Customizer_Common || $this->is_multiple_customizer_template() ) {

			WFOCU_Core()->customizer->setup_offer_for_wfocukirki();
			WFOCU_Core()->customizer->customizer_product_check();
			WFOCU_Core()->template_loader->get_template_ins()->get_customizer_data();
			WFOCU_Core()->customizer->wfocu_wfocukirki_fields();

		} elseif ( WFOCU_Core()->template_loader->get_template_ins() instanceof WFOCU_Template_Common ) {

			/**
			 * When offer setup already completed we just need to register our assets as we know its either custom page or single offer page front request
			 */
			WFOCU_Core()->assets->maybe_register_assets( [], '', true );

			if ( $load_assets ) {

				do_action( 'wfocu_front_before_custom_offer_page' );
			}
		}
		do_action( 'wfocu_setup_offer_completed' );

	}

	public function get_template_ins() {
		return $this->current_template;
	}

	/**
	 * @hooked over `init`:15
	 * This method try and sets up the data for all the existing pages.
	 * customizer-admin | customizer-preview | front-end-funnel | front-end-ajax-request-during-funnel
	 * For the given environments we have our offer ID setup at this point. So its safe and necessary to set the data.
	 * This method does:
	 * 1. Fetches and sets up the offer data based on the offer id provided
	 * 2. Finds & loads the appropriate template.
	 * 3. loads offer data to the template instance
	 * 4. Build offer data for the current offer
	 */
	public function maybe_setup_offer() {

		if ( $this->is_valid_state_for_data_setup() ) {
			$id               = $this->get_offer_id();
			$this->offer_data = WFOCU_Core()->offers->get_offer( $id );
			WFOCU_Core()->funnels->maybe_set_funnel_from_offer( $id );
			$this->product_data = WFOCU_Core()->offers->build_offer_product( $this->offer_data, $id, true );

			$get_group = $this->maybe_select_template_to_load( $this->offer_data );

			if ( $get_group instanceof WFOCU_Template_Group ) {
				$this->current_template_group = $get_group;
				$get_group->set_up_template();

			}

			$this->set_data_object();
			do_action( 'wfocu_offer_setup_completed' );
		}

	}

	/**
	 * @param $offer_data
	 *
	 * @return WFOCU_Template_Group|null
	 */
	public function maybe_select_template_to_load( $offer_data ) {

		if ( ! empty( $offer_data ) ) {

			if ( count( get_object_vars( $offer_data ) ) > 0 ) {
				$this->template = $offer_data->template;

				if ( 'custom-page' === $this->template ) {

					return null;
				}
				if ( ! isset( $this->template ) || empty( $this->template ) ) {
					$this->template             = $this->get_default_template( $offer_data );
					$this->offer_data->template = $this->template;
				}

				$get_group = $this->get_template_group_by_template( $this->template );

				$group_instance = $this->get_group( $get_group );

				return $group_instance;
			}
		}

		return null;
	}

	public function get_default_template( $offer_data ) {

		if ( ! is_object( $offer_data ) ) {
			return $this->get_default_single_template();
		}
		if ( count( get_object_vars( $offer_data->products ) ) > 1 && class_exists( 'WFOCU_MultiProductCore' ) ) {
			return $this->get_default_multiple_template();
		} else {
			return $this->get_default_single_template();
		}
	}

	/**
	 * @param string $template
	 *
	 * @return string
	 */
	public function get_template_group_by_template( $template ) {
		$get_all_groups = $this->get_all_groups();

		foreach ( $get_all_groups as $key => $groups ) {
			if ( in_array( $template, $groups->get_templates(), true ) ) {
				return $key;
			}
		}

		return $this->get_default_group();
	}

	/**
	 * @return WFOCU_Template_Group[]
	 */
	public function get_all_groups() {
		return $this->template_groups;
	}

	public function get_default_group() {
		return 'customizer';
	}

	/**
	 * @param $group_slug
	 *
	 * @return WFOCU_Template_Group|null
	 */
	public function get_group( $group_slug ) {
		return array_key_exists( $group_slug, $this->template_groups ) ? $this->template_groups[ $group_slug ] : null;
	}

	/**
	 * Sets up the current offer data related data objects
	 * These objects will be later gets accessed using data class
	 *
	 */
	public function set_data_object() {

		WFOCU_Core()->data->set( '_current_offer_data', $this->product_data );
		WFOCU_Core()->data->set( '_current_offer', $this->offer_data );

		if ( ! $this->is_customizer_preview() ) {

			WFOCU_Core()->data->set( '_current_offer_type', WFOCU_Core()->offers->get_offer_attributes( WFOCU_Core()->data->get( 'current_offer' ), 'type' ) );
			WFOCU_Core()->data->set( '_current_offer_type_index', WFOCU_Core()->offers->get_offer_attributes( WFOCU_Core()->data->get( 'current_offer' ), 'index' ) );
		}
	}

	public function is_multiple_customizer_template() {
		if ( is_object( $this->get_template_ins() ) ) {

			if ( in_array( $this->get_template_ins()->get_slug(), [ 'mp-grid', 'mp-list' ], true ) ) {
				return true;
			}
		}

		return false;
	}

	public function maybe_render_variation_forms() {
		if ( ! isset( $this->product_data ) ) {
			return;
		}
		if ( ! isset( $this->product_data->products ) ) {
			return;
		}

		$product_data = $this->product_data->products;
		$temp_product = get_object_vars( $product_data );

		if ( is_array( $temp_product ) && count( $temp_product ) > 0 ) {
			$get_all_ids = array_keys( $temp_product );
			foreach ( $get_all_ids as $k ) {
				echo do_shortcode( '[wfocu_variation_selector_form key="' . $k . '" display="no"]' );
			}

		}
	}

	/**
	 * Get Plugins list by page builder.
	 *
	 * @return array Required Plugins list.
	 * @since 1.1.4
	 *
	 */
	public function get_plugins_groupby_page_builders() {

		$plugins = array(
			'elementor' => array(
				'title'  => 'Elementor',
				'slug'   => 'elementor', // For download from wordpress.org.
				'init'   => 'elementor/elementor.php',
				'status' => $this->get_plugin_status( 'elementor/elementor.php' ),
			),
		);

		return $plugins;
	}

	/**
	 * Get plugin status
	 *
	 * @param string $plugin_init_file plugin init file.
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function get_plugin_status( $plugin_init_file ) {

		if ( null === $this->installed_plugins ) {
			$this->installed_plugins = get_plugins();
		}

		if ( ! isset( $this->installed_plugins[ $plugin_init_file ] ) ) {
			return 'install';
		} elseif ( ! is_plugin_active( $plugin_init_file ) ) {
			return 'activate';
		}

		return;
	}

	public function localize_page_builder_texts() {
		$get_all_opted_page_builders = [ 'elementor' ];
		$pageBuildersTexts           = [];
		foreach ( $get_all_opted_page_builders as $builder ) {
			$page_builder = $this->get_dependent_plugins_for_page_builder( $builder );

			$plugin_string = 'We are unable to find ' . esc_html( $page_builder['title'] ) . ' installed on your setup. Please click the button below to activate ' . esc_html( $page_builder['title'] ) . '';
			$button_text   = __( 'Activate the plugin', 'woofunnels-upstroke-one-click-upsell' );
			$no_install    = 'no';
			$title         = __( 'Sorry, we cannot import this template!', 'woofunnels-upstroke-one-click-upsell' );
			$install_fail  = __( 'We are unable to install the page builder plugin.', 'woofunnels-upstroke-one-click-upsell' );
			$activate_fail = __( 'We are unable to activate the page builder plugin.', 'woofunnels-upstroke-one-click-upsell' );

			/**
			 * If its a divi builder we need to handle few cases down there for best user experience
			 */

			$pageBuildersTexts[ $builder ] = array(
				'text'              => $plugin_string,
				'confirmButtonText' => $button_text,
				'noInstall'         => $no_install,
				'title'             => $title,
				'install_fail'      => $install_fail,
				'activate_fail'     => $activate_fail,
				'close_btn'         => "Close",
			);
		}

		return $pageBuildersTexts;
	}

	public function get_dependent_plugins_for_page_builder( $page_builder_slug = '', $default = 'elementor' ) {
		$plugins = $this->get_plugins_groupby_page_builders();

		if ( array_key_exists( $page_builder_slug, $plugins ) ) {
			return $plugins[ $page_builder_slug ];
		}

		return $plugins[ $default ];
	}

	public function maybe_render_css_for_offer_conf() {
		if ( ! did_action( 'wfocu_front_before_single_page_load' ) && ! did_action( 'wfocu_front_before_custom_offer_page' ) ) {
			return;
		}
		$this->print_internal_css();

	}
}

if ( class_exists( 'WFOCU_Core' ) ) {
	WFOCU_Core::register( 'template_loader', 'WFOCU_Template_loader' );
}
