<?php

/**
 * Class WCCT_Common
 * Handles Common Functions For Admin as well as front end interface
 */
class WCCT_Common {

	public static $wcct_post;
	public static $wcct_query;
	public static $is_front_page = false;
	public static $is_executing_rule = false;
	public static $is_force_debug = false;
	public static $info_generated = false;
	public static $isheadeer_alert = false;
	public static $excluded_rules = array();
	public static $rule_page_label, $rule_product_label;
	public static $maybe_set_rule_valid_cache = false;
	protected static $default;

	public static function init() {

		add_action( 'init', array( __CLASS__, 'register_post_status' ), 5 );

		/** Necessary Hooks For Rules functionality */
		add_action( 'init', array( __CLASS__, 'register_countdown_post_type' ) );
		add_action( 'init', array( __CLASS__, 'load_rules_classes' ) );

		add_filter( 'wcct_wcct_rule_get_rule_types', array( __CLASS__, 'default_rule_types' ), 1 );
		add_filter( 'wcct_wcct_rule_get_rule_types', array( __CLASS__, 'product_tax_rule_types' ), 9 );

		add_action( 'wp_ajax_wcct_change_rule_type', array( __CLASS__, 'ajax_render_rule_choice' ) );

		add_action( 'save_post', array( __CLASS__, 'save_data' ), 10, 2 );

		/**
		 * Loading XL core
		 */
		add_action( 'plugins_loaded', array( __CLASS__, 'wcct_xl_init' ), 99 );

		/**
		 * Setting up cron for regular license checks
		 */
		add_action( 'wp', array( __CLASS__, 'wcct_license_check_schedule' ) );

		/**
		 *
		 */
		add_action( 'wcct_maybe_schedule_check_license', array( __CLASS__, 'check_license_state' ) );

		/**
		 * Containing current Page State using wp hook
		 * using priority 0 to make sure it is not changed by that moment
		 */
		add_action( 'wp', array( __CLASS__, 'wcct_contain_current_query' ), 1 );
		/**
		 * Checking wcct query params
		 */
		add_action( 'init', array( __CLASS__, 'check_query_params' ), 1 );

		add_action( 'wp_ajax_get_coupons_cmb2', array( __CLASS__, 'get_coupons_cmb2' ) );
		add_action( 'wp_ajax_nopriv_get_coupons_cmb2', array( __CLASS__, 'get_coupons_cmb2' ) );

		add_action( 'wp_ajax_get_wcct_countdown', array( __CLASS__, 'get_wcct_countdown' ) );
		add_action( 'wp_ajax_nopriv_get_wcct_countdown', array( __CLASS__, 'get_wcct_countdown' ) );

		add_action( 'wp_ajax_get_pages_cmb2', array( __CLASS__, 'get_pages_cmb2' ) );
		add_action( 'wp_ajax_nopriv_get_pages_cmb2', array( __CLASS__, 'get_pages_cmb2' ) );

		add_action( 'wp_ajax_wcct_get_button_ref', array( __CLASS__, 'get_button_ref' ) );
		add_action( 'wp_ajax_nopriv_wcct_get_button_ref', array( __CLASS__, 'get_button_ref' ) );

		add_action( 'admin_bar_menu', array( __CLASS__, 'toolbar_link_to_xlplugins' ), 999 );

		add_action( 'wp_ajax_wcct_quick_view_html', array( __CLASS__, 'wcct_quick_view_html' ) );

		add_action( 'wcct_data_setup_done', array( __CLASS__, 'init_header_logs' ), 999 );
		add_filter( 'wcct_localize_js_data', array( __CLASS__, 'add_license_info' ) );

		add_action( 'wcct_schedule_reset_state', array( __CLASS__, 'process_reset_state' ), 10, 1 );

		/** Hooked on Ajax callback, as doing PHP processing only */
		add_action( 'plugin_loaded', array( __CLASS__, 'wcct_refresh_timer_ajax_callback' ) );

		add_action( 'wp_ajax_wcct_clear_cache', array( __CLASS__, 'wcct_maybe_clear_cache' ) );
		add_action( 'wp_ajax_nopriv_wcct_clear_cache', array( __CLASS__, 'wcct_maybe_clear_cache' ) );

		add_filter( 'wcct_rules_options', array( __CLASS__, 'maybe_add_rule_for_wc_memberships' ) );

		add_action( 'wcct_before_apply_rules', array( __CLASS__, 'add_excluded_rules' ), 10, 2 );
		add_action( 'wcct_after_apply_rules', array( __CLASS__, 'remove_excluded_rules' ), 10, 2 );

		/**
		 * modifying stock status rule when WC 3.3 or higher
		 */
		add_filter( 'wcct_rule_stock_status', array( __CLASS__, 'wcct_rule_stock_status' ), 10 );

		/**
		 * Restoring stock on cancel order
		 */
		add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'wcct_restore_order_stock' ), 10 );

		/**
		 * Clear postmeta table for expired campaigns
		 */
		add_action( 'admin_init', array( __CLASS__, 'wcct_clear_post_meta_keys_for_expired_campaigns' ), 10 );
		add_action( 'wcct_clear_goaldeal_stock_meta_keys', array( __CLASS__, 'wcct_clear_goaldeal_stock_meta_keys' ), 10 );
		add_action( 'wcct_clear_inventory_range_meta_keys', array( __CLASS__, 'wcct_clear_inventory_range_meta_keys' ), 10 );

		add_action( 'heartbeat_tick', array( __CLASS__, 'run_cron_fallback' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'add_plugin_endpoint' ) );
	}

	public static function wcct_get_date_format() {
		$date_format = get_option( 'date_format', true );
		$date_format = $date_format ? $date_format : 'M d, Y';

		return $date_format;
	}

	public static function wcct_get_time_format() {
		$time_format = get_option( 'time_format', true );
		$time_format = $time_format ? $time_format : 'g:i a';

		return $time_format;
	}

	public static function array_flatten( $array ) {
		if ( ! is_array( $array ) ) {
			return false;
		}
		$result = iterator_to_array( new RecursiveIteratorIterator( new RecursiveArrayIterator( $array ) ), false );

		return $result;
	}

	public static function array_flat_mysql_results( $results, $expected_key, $expected_value_key ) {
		$array = array();
		foreach ( $results as $result ) {
			$array[ $result[ $expected_key ] ] = (int) $result[ $expected_value_key ];
		}

		return $array;
	}

	public static function get_date_modified( $mod, $format ) {
		$date_object = new DateTime();
		$date_object->setTimestamp( current_time( 'timestamp' ) );

		return $date_object->modify( $mod )->format( ( $format ) );
	}

	public static function get_current_date( $format ) {
		$date_object = new DateTime();
		$date_object->setTimestamp( current_time( 'timestamp' ) );

		return $date_object->format( $format );
	}

	public static function register_countdown_post_type() {
		$menu_name = _x( WCCT_FULL_NAME, 'Admin menu name', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );

		register_post_type(
			self::get_campaign_post_type_slug(), apply_filters(
				'wcct_post_type_args', array(
					'labels'               => array(
						'name'               => __( 'Countdown Timer', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'singular_name'      => __( 'Countdown Timer', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'add_new'            => __( 'Add Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'add_new_item'       => __( 'Add New Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'edit'               => __( 'Edit', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'edit_item'          => __( 'Edit Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'new_item'           => __( 'New Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'view'               => __( 'View Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'view_item'          => __( 'View Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'search_items'       => __( 'Search Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'not_found'          => __( 'No Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'not_found_in_trash' => __( 'No Campaign found in trash', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'parent'             => __( 'Parent Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'menu_name'          => $menu_name,
					),
					'public'               => false,
					'show_ui'              => true,
					'capability_type'      => 'product',
					'map_meta_cap'         => true,
					'publicly_queryable'   => false,
					'exclude_from_search'  => true,
					'show_in_menu'         => false,
					'hierarchical'         => false,
					'show_in_nav_menus'    => false,
					'rewrite'              => false,
					'query_var'            => true,
					'supports'             => array( 'title' ),
					'has_archive'          => false,
					'register_meta_box_cb' => array( 'XLWCCT_Admin', 'add_metaboxes' ),
				)
			)
		);
	}

	public static function get_campaign_post_type_slug() {
		return 'wcct_countdown';
	}

	public static function load_rules_classes() {
		//Include the compatibility class
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/class-wcct-compatibility.php';

		//Include our default rule classes
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/base.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/general.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/page.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/products.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/stock.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/sales.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/users.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/date-time.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/cart.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/geo.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/single-post.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/archive-pages.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/learndash.php';
		include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/rules/wfacp.php';

		if ( is_admin() || defined( 'DOING_AJAX' ) ) {
			//Include the admin interface builder
			include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/class-wcct-input-builder.php';
			include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/inputs/html-description.php';
			include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/inputs/text.php';
			include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/inputs/date.php';
			include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/inputs/select.php';
			include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/inputs/product-select.php';
			include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/inputs/cart-product-select.php';
			include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/inputs/cart-category-select.php';
			include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/inputs/chosen-select.php';
			include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/inputs/page-select.php';
			include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/inputs/term-select.php';
			include plugin_dir_path( WCCT_PLUGIN_FILE ) . '/rules/inputs/time.php';
		}
	}

	/**
	 * Creates an instance of an input object
	 *
	 * @param: $input_type The slug of the input type to load
	 *
	 * @return: An instance of an WCCT_Input object type
	 *@global: $woocommerce_wcct_rule_inputs
	 *
	 */
	public static function woocommerce_wcct_rule_get_input_object( $input_type ) {
		global $woocommerce_wcct_rule_inputs;

		if ( isset( $woocommerce_wcct_rule_inputs[ $input_type ] ) ) {
			return $woocommerce_wcct_rule_inputs[ $input_type ];
		}

		$class = 'WCCT_Input_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $input_type ) ) );

		if ( class_exists( $class ) ) {
			$woocommerce_wcct_rule_inputs[ $input_type ] = new $class;
		} else {
			$woocommerce_wcct_rule_inputs[ $input_type ] = apply_filters( 'woocommerce_wcct_rule_get_input_object', $input_type );
		}

		return $woocommerce_wcct_rule_inputs[ $input_type ];
	}

	/**
	 * Ajax and PHP Rendering Functions for Options.
	 *
	 * Renders the correct Operator and Values controls.
	 */
	public static function ajax_render_rule_choice( $options ) {
		// defaults
		$defaults = array(
			'group_id'  => 0,
			'rule_id'   => 0,
			'rule_type' => null,
			'condition' => null,
			'operator'  => null,
		);

		$is_ajax = false;

		if ( isset( $_POST['action'] ) && $_POST['action'] === 'wcct_change_rule_type' ) {
			$is_ajax = true;
		}

		if ( $is_ajax ) {
			if ( ! check_ajax_referer( 'wcctaction-admin', 'security' ) ) {
				die();
			}
			$options = array_merge( $defaults, $_POST );
		} else {
			$options = array_merge( $defaults, $options );
		}

		$rule_object = self::woocommerce_wcct_rule_get_rule_object( $options['rule_type'] );

		if ( ! empty( $rule_object ) ) {
			$values               = $rule_object->get_possible_rule_values();
			$operators            = $rule_object->get_possible_rule_operators();
			$condition_input_type = $rule_object->get_condition_input_type();
			// create operators field
			$operator_args = array(
				'input'   => 'select',
				'name'    => 'wcct_rule[' . $options['group_id'] . '][' . $options['rule_id'] . '][operator]',
				'choices' => $operators,
			);

			echo '<td class="operator">';
			if ( ! empty( $operators ) ) {
				WCCT_Input_Builder::create_input_field( $operator_args, $options['operator'] );
			} else {
				echo '<input type="hidden" name="' . $operator_args['name'] . '" value="==" />';
			}
			echo '</td>';

			// create values field
			$value_args = array(
				'input'   => $condition_input_type,
				'name'    => 'wcct_rule[' . $options['group_id'] . '][' . $options['rule_id'] . '][condition]',
				'choices' => $values,
			);

			echo '<td class="condition">';
			WCCT_Input_Builder::create_input_field( $value_args, $options['condition'] );
			echo '</td>';
		}

		// ajax?
		if ( $is_ajax ) {
			die();
		}
	}

	/**
	 * Creates an instance of a rule object
	 *
	 * @param $rule_type: The slug of the rule type to load.
	 *
	 * @return WCCT_Rule_Base or superclass of WCCT_Rule_Base
	 *@global array $woocommerce_wcct_rule_rules
	 *
	 */
	public static function woocommerce_wcct_rule_get_rule_object( $rule_type ) {
		global $woocommerce_wcct_rule_rules;

		if ( isset( $woocommerce_wcct_rule_rules[ $rule_type ] ) ) {
			return $woocommerce_wcct_rule_rules[ $rule_type ];
		}

		/** Other WooCommerce products taxonomies */
		$wc_tax   = get_object_taxonomies( 'product', 'names' );
		$exc_cats = array( 'product_type', 'product_visibility', 'product_cat', 'product_tag', 'product_shipping_class' );
		$wc_tax   = array_diff( $wc_tax, $exc_cats );

		if ( is_array( $wc_tax ) && count( $wc_tax ) > 0 ) {
			$wc_tax = array_filter(
				$wc_tax, function ( $tax_name ) {
					return ( 'pa_' !== substr( $tax_name, 0, 3 ) );
				}
			);
		}

		$class = 'WCCT_Rule_' . $rule_type;
		if ( class_exists( $class ) ) {
			$woocommerce_wcct_rule_rules[ $rule_type ] = new $class;

			return $woocommerce_wcct_rule_rules[ $rule_type ];
		} elseif ( is_array( $wc_tax ) && count( $wc_tax ) > 0 && in_array( $rule_type, $wc_tax, true ) ) {
			$tax_rule_class = new WCCT_Rule_WCCT_Product_Tax( $rule_type );

			return $tax_rule_class;
		} else {
			return null;
		}
	}

	/**
	 * Called from the metabox_settings.php screen.  Renders the template for a rule group that has already been saved.
	 *
	 * @param array $options The group config options to render the template with.
	 */
	public static function render_rule_choice_template( $options ) {
		// defaults
		$defaults = array(
			'group_id'  => 0,
			'rule_id'   => 0,
			'rule_type' => null,
			'condition' => null,
			'operator'  => null,
		);

		$options     = array_merge( $defaults, $options );
		$rule_object = self::woocommerce_wcct_rule_get_rule_object( $options['rule_type'] );

		$values               = $rule_object->get_possible_rule_values();
		$operators            = $rule_object->get_possible_rule_operators();
		$condition_input_type = $rule_object->get_condition_input_type();

		// create operators field
		$operator_args = array(
			'input'   => 'select',
			'name'    => 'wcct_rule[<%= groupId %>][<%= ruleId %>][operator]',
			'choices' => $operators,
		);

		echo '<td class="operator">';
		if ( ! empty( $operators ) ) {
			WCCT_Input_Builder::create_input_field( $operator_args, $options['operator'] );
		} else {
			echo '<input type="hidden" name="' . $operator_args['name'] . '" value="==" />';
		}
		echo '</td>';

		// create values field
		$value_args = array(
			'input'   => $condition_input_type,
			'name'    => 'wcct_rule[<%= groupId %>][<%= ruleId %>][condition]',
			'choices' => $values,
		);

		echo '<td class="condition">';
		WCCT_Input_Builder::create_input_field( $value_args, $options['condition'] );
		echo '</td>';
	}

	public static function get_campaign_status_select() {
		$triggers            = self::get_campaign_statuses();
		$create_select_array = array();
		if ( $triggers && is_array( $triggers ) && count( $triggers ) > 0 ) {
			foreach ( $triggers as $triggerlist ) {
				$create_select_array[ $triggerlist['name'] ] = array();

				foreach ( $triggerlist['triggers'] as $triggers_main ) {
					$create_select_array[ $triggerlist['name'] ][ $triggers_main['slug'] ] = $triggers_main['title'];
				}
			}
		}

		return $create_select_array;
	}

	/**
	 * Getting list of declared triggers in hierarchical order
	 * @return array array of triggers
	 */
	public static function get_campaign_statuses() {
		return array(
			'running'     => array(
				'name'     => __( 'Running', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'slug'     => 'running',
				'position' => 5,
			),
			'paused'      => array(
				'name'     => __( 'Paused', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'slug'     => 'paused',
				'position' => 6,
			),
			'schedule'    => array(
				'name'     => __( 'Scheduled', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'slug'     => 'schedule',
				'position' => 7,
			),
			'finished'    => array(
				'name'     => __( 'Finished', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'slug'     => 'finished',
				'position' => 8,
			),
			'deactivated' => array(
				'name'     => __( 'Deactivated', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'slug'     => 'deactivated',
				'position' => 9,
			),
		);
	}

	public static function match_groups( $content_id, $productID = 0 ) {
		$display      = false;
		$xl_cache_obj = XL_Cache::get_instance();

		if ( $productID ) {
			$cache_key = 'wcct_countdown_match_groups_' . $content_id . '_' . $productID;
		} else {
			$cache_key = 'wcct_countdown_match_groups_' . $content_id;
		}

		$cache_data = $xl_cache_obj->get_cache( $cache_key, 'finale' );
		$cache_data = apply_filters( 'finale_match_group_cached_result', $cache_data, $content_id, $productID );
		if ( false !== $cache_data ) {
			$display = ( 'yes' === $cache_data ) ? true : false;
		} else {
			do_action( 'wcct_before_apply_rules', $content_id, $productID );
			self::$is_executing_rule = true;

			//allowing rules to get manipulated using external logic
			$external_rules = apply_filters( 'wcct_modify_rules', true, $content_id, $productID );
			if ( ! $external_rules ) {
				$xl_cache_obj->set_cache( $cache_key, 'no', 'finale' );
				self::$is_executing_rule = false;

				return false;
			}

			$camp_meta = WCCT_Common::get_item_data( $content_id );
			$groups    = isset( $camp_meta['wcct_rule'] ) ? $camp_meta['wcct_rule'] : array();

			if ( is_array( $groups ) && count( $groups ) ) {
				foreach ( $groups as $group_id => $group ) {
					$result = null;
					foreach ( $group as $rule_id => $rule ) {
						if ( isset( self::$excluded_rules[ $rule['rule_type'] ] ) ) {
							continue;
						}
						$rule_object = self::woocommerce_wcct_rule_get_rule_object( $rule['rule_type'] );
						if ( is_object( $rule_object ) ) {
							$match = $rule_object->is_match( $rule, $productID );
							if ( false === $match ) {
								$result = false;
								break;
							}
							$result = ( $result !== null ? ( $result & $match ) : $match );
						}
					}
					if ( $result ) {
						$display = true;
						break;
					}
				}
			} else {
				$display = true; //Always display the content if no rules have been configured.
			}
			if ( true === self::$maybe_set_rule_valid_cache ) {
				$xl_cache_obj->set_cache( $cache_key, ( $display ) ? 'yes' : 'no', 'finale' );
			}
			do_action( 'wcct_after_apply_rules', $content_id, $productID );
		}
		self::$is_executing_rule = false;

		return $display;
	}

	public static function get_item_data( $item_id ) {
		global $wpdb;

		$xl_cache_obj     = XL_Cache::get_instance();
		$xl_transient_obj = XL_Transient::get_instance();

		$cache_key = 'wcct_countdown_post_meta_' . $item_id;

		/**
		 * Setting xl cache and transient for Finale single campaign meta
		 */
		$cache_data = $xl_cache_obj->get_cache( $cache_key, 'finale' );
		if ( false !== $cache_data ) {
			$parseObj = $cache_data;
		} else {
			$transient_data = $xl_transient_obj->get_transient( $cache_key, 'finale' );

			if ( false !== $transient_data ) {
				$parseObj = $transient_data;
			} else {
				$product_meta                  = get_post_meta( $item_id );
				$product_meta                  = self::get_parsed_query_results_meta( $product_meta );
				$get_product_wcct_meta_default = self::parse_default_args_by_trigger( $product_meta );
				$parseObj                      = wp_parse_args( $product_meta, $get_product_wcct_meta_default );
				$xl_transient_obj->set_transient( $cache_key, $parseObj, 7200, 'finale' );
			}
			$xl_cache_obj->set_cache( $cache_key, $parseObj, 'finale' );
		}

		$fields = array();
		if ( $parseObj && is_array( $parseObj ) && count( $parseObj ) > 0 ) {
			foreach ( $parseObj as $key => $val ) {
				$newKey = $key;
				if ( strpos( $key, '_wcct_' ) !== false ) {
					$newKey = str_replace( '_wcct_', '', $key );
				}
				$fields[ $newKey ] = maybe_unserialize( $val );
			}
		}

		return $fields;
	}

	public static function get_parsed_query_results_meta( $results ) {
		$parsed_results = array();

		if ( is_array( $results ) && count( $results ) > 0 ) {
			foreach ( $results as $key => $result ) {
				$parsed_results[ $key ] = maybe_unserialize( $result['0'] );
			}
		}

		return $parsed_results;
	}

	public static function parse_default_args_by_trigger( $data ) {
		$field_option_data = self::get_default_settings();
		foreach ( $field_option_data as $slug => $value ) {
			if ( strpos( $slug, '_wcct_' ) !== false ) {
				$data[ $slug ] = $value;
			}
		}

		return $data;
	}

	public static function get_default_settings() {
		self::$default = apply_filters(
			'wcct_campaign_fields_default', array(
				'_wcct_location_timer_show_grid'                        => '0',
				'_wcct_location_timer_show_cart'                        => '0',
				'_wcct_location_bar_show_grid'                          => '0',
				'_wcct_campaign_type'                                   => 'fixed_date',
				'_wcct_campaign_fixed_recurring_start_date'             => date( 'Y-m-d' ),
				'_wcct_campaign_fixed_recurring_start_time'             => '12:00 AM',
				'_wcct_campaign_fixed_end_date'                         => date( 'Y-m-d', strtotime( '+5 days', time() ) ),
				'_wcct_campaign_fixed_end_time'                         => '12:00 AM',
				'_wcct_campaign_recurring_duration_days'                => '1',
				'_wcct_campaign_recurring_duration_hrs'                 => '0',
				'_wcct_campaign_recurring_duration_min'                 => '0',
				'_wcct_campaign_recurring_gap_days'                     => '1',
				'_wcct_campaign_recurring_gap_hrs'                      => '0',
				'_wcct_campaign_recurring_gap_mins'                     => '0',
				'_wcct_campaign_recurring_ends'                         => 'never',
				'_wcct_campaign_recurring_ends_after_x_days'            => '5',
				'_wcct_data_end_date_of_deal'                           => date( 'Y-m-d', strtotime( '+30 days', time() ) ),
				'_wcct_data_end_time_of_deal'                           => '12:00 AM',
				'_wcct_deal_enable_price_discount'                      => '0',
				'_wcct_deal_amount'                                     => '5',
				'_wcct_deal_type'                                       => 'percentage',
				'_wcct_deal_mode'                                       => 'simple',
				'_wcct_discount_custom_advanced'                        => array(),
				'_wcct_deal_enable_goal'                                => '0',
				'_wcct_deal_custom_mode'                                => 'basic',
				'_wcct_deal_units'                                      => 'custom',
				'_wcct_deal_custom_units'                               => '8',
				'_wcct_deal_range_from_custom_units'                    => '8',
				'_wcct_deal_range_to_custom_units'                      => '16',
				'_wcct_deal_threshold_units'                            => '0',
				'_wcct_deal_end_campaign'                               => 'no',
				'_wcct_deal_inventory_goal_for'                         => 'campaign',
				'_wcct_deal_custom_units_allow_backorder'               => 'no',
				'_wcct_coupons_enable'                                  => '0',
				'_wcct_coupons'                                         => array(),
				'_wcct_coupons_apply_mode'                              => 'auto',
				'_wcct_coupons_is_expire'                               => 'yes',
				'_wcct_coupons_is_hide_errors'                          => 'yes',
				'_wcct_coupons_success_message'                         => __( 'Instant Offer: Congratulations you just unlocked a lower price. Claim this offer in {{countdown_timer}}', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'_wcct_coupons_failure_message'                         => __( 'Sorry! Instant Offer has expired.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'_wcct_coupons_cart_message'                            => __( 'Instant Offer Expires in {{countdown_timer}}', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'_wcct_coupons_empty_cart_message'                      => __( 'Please add the product(s) in your cart to avail Instant Offer.', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'_wcct_coupons_notice_after_add_to_cart'                => 'no',
				'_wcct_coupons_notice_show'                             => 'all',
				'_wcct_coupons_notice_pages'                            => array(),
				'_wcct_coupons_notice_products'                         => array(),
				'_wcct_location_timer_show_single'                      => '0',
				'_wcct_location_timer_single_location'                  => '4',
				'_wcct_appearance_timer_single_skin'                    => 'highlight_1',
				'_wcct_appearance_timer_single_bg_color'                => '#ffffff',
				'_wcct_appearance_timer_single_text_color'              => '#dd3333',
				'_wcct_appearance_timer_single_font_size_timer'         => '26',
				'_wcct_appearance_timer_single_font_size'               => '14',
				'_wcct_appearance_timer_single_label_days'              => 'days',
				'_wcct_appearance_timer_single_label_hrs'               => 'hrs',
				'_wcct_appearance_timer_single_label_mins'              => 'mins',
				'_wcct_appearance_timer_single_label_secs'              => 'secs',
				'_wcct_appearance_timer_single_display'                 => "{{countdown_timer}}\nPrices go up when the timer hits zero",
				'_wcct_appearance_timer_single_border_style'            => 'none',
				'_wcct_appearance_timer_single_border_width'            => '1',
				'_wcct_appearance_timer_single_border_color'            => '#dd3333',
				'_wcct_appearance_timer_mobile_reduction'               => '90',
				'_wcct_appearance_timer_single_delay_hrs'               => '48',
				'_wcct_location_bar_show_single'                        => '0',
				'_wcct_location_bar_single_location'                    => '4',
				'_wcct_appearance_bar_single_skin'                      => 'stripe_animate',
				'_wcct_appearance_bar_single_edges'                     => 'rounded',
				'_wcct_appearance_bar_single_orientation'               => 'rtl',
				'_wcct_appearance_bar_single_bg_color'                  => '#dddddd',
				'_wcct_appearance_bar_single_active_color'              => '#ee303c',
				'_wcct_appearance_bar_single_height'                    => '14',
				'_wcct_appearance_bar_single_display'                   => "Hurry up! Just <span>{{remaining_units}}</span> items left in stock\n{{counter_bar}}",
				'_wcct_appearance_bar_single_border_style'              => 'none',
				'_wcct_appearance_bar_single_border_width'              => '1',
				'_wcct_appearance_bar_single_border_color'              => '#dd3333',
				'_wcct_appearance_bar_single_delay_item'                => '0',
				'_wcct_appearance_bar_single_delay_item_remaining'      => '',
				'_wcct_location_timer_show_sticky_header'               => '0',
				'_wcct_appearance_sticky_header_wrap_bg'                => '#000000',
				'_wcct_appearance_sticky_header_headline'               => 'Great time to visit us today!',
				'_wcct_appearance_sticky_header_headline_font_size'     => '24',
				'_wcct_appearance_sticky_header_headline_color'         => '#ffffff',
				'_wcct_appearance_sticky_header_description'            => '',
				'_wcct_appearance_sticky_header_description_font_size'  => '15',
				'_wcct_appearance_sticky_header_description_color'      => '#ffffff',
				'_wcct_appearance_sticky_header_skin'                   => 'round_fill',
				'_wcct_appearance_sticky_header_bg_color'               => '#444444',
				'_wcct_appearance_sticky_header_text_color'             => '#ffffff',
				'_wcct_appearance_sticky_header_font_size_timer'        => '18',
				'_wcct_appearance_sticky_header_font_size'              => '13',
				'_wcct_appearance_sticky_header_label_days'             => 'days',
				'_wcct_appearance_sticky_header_label_hrs'              => 'hrs',
				'_wcct_appearance_sticky_header_label_mins'             => 'mins',
				'_wcct_appearance_sticky_header_label_secs'             => 'secs',
				'_wcct_appearance_sticky_header_timer_border_style'     => 'none',
				'_wcct_appearance_sticky_header_timer_border_width'     => '1',
				'_wcct_appearance_sticky_header_timer_border_color'     => '#444444',
				'_wcct_appearance_sticky_header_timer_mobile_reduction' => '90',
				'_wcct_appearance_sticky_header_timer_position'         => 'center',
				'_wcct_appearance_sticky_header_button_skin'            => 'button_1',
				'_wcct_appearance_sticky_header_button_text'            => 'Get the Offer',
				'_wcct_appearance_sticky_header_button_bg_color'        => '#d7fe3a',
				'_wcct_appearance_sticky_header_button_text_color'      => '#000000',
				'_wcct_appearance_sticky_header_delay'                  => '1',
				'_wcct_location_timer_show_sticky_footer'               => '0',
				'_wcct_appearance_sticky_footer_wrap_bg'                => '#000000',
				'_wcct_appearance_sticky_footer_headline'               => 'Grab this latest Offer only for YOU, Expiring soon!',
				'_wcct_appearance_sticky_footer_headline_font_size'     => '24',
				'_wcct_appearance_sticky_footer_headline_color'         => '#ffffff',
				'_wcct_appearance_sticky_footer_description'            => '',
				'_wcct_appearance_sticky_footer_description_font_size'  => '15',
				'_wcct_appearance_sticky_footer_description_color'      => '#ffffff',
				'_wcct_appearance_sticky_footer_skin'                   => 'round_fill',
				'_wcct_appearance_sticky_footer_bg_color'               => '#444444',
				'_wcct_appearance_sticky_footer_text_color'             => '#ffffff',
				'_wcct_appearance_sticky_footer_font_size_timer'        => '18',
				'_wcct_appearance_sticky_footer_font_size'              => '13',
				'_wcct_appearance_sticky_footer_label_days'             => 'days',
				'_wcct_appearance_sticky_footer_label_hrs'              => 'hrs',
				'_wcct_appearance_sticky_footer_label_mins'             => 'mins',
				'_wcct_appearance_sticky_footer_label_secs'             => 'secs',
				'_wcct_appearance_sticky_footer_timer_border_style'     => 'none',
				'_wcct_appearance_sticky_footer_timer_border_width'     => '1',
				'_wcct_appearance_sticky_footer_timer_border_color'     => '#444444',
				'_wcct_appearance_sticky_footer_timer_mobile_reduction' => '90',
				'_wcct_appearance_sticky_footer_timer_position'         => 'center',
				'_wcct_appearance_sticky_footer_button_skin'            => 'button_1',
				'_wcct_appearance_sticky_footer_button_text'            => 'Get the Offer',
				'_wcct_appearance_sticky_footer_button_bg_color'        => '#d7fe3a',
				'_wcct_appearance_sticky_footer_button_text_color'      => '#000000',
				'_wcct_appearance_sticky_footer_delay'                  => '1',
				'_wcct_location_show_custom_text'                       => '0',
				'_wcct_location_custom_text_location'                   => '4',
				'_wcct_appearance_custom_text_description'              => '',
				'_wcct_appearance_custom_text_text_color'               => '#444444',
				'_wcct_appearance_custom_text_font_size'                => '16',
				'_wcct_appearance_custom_text_border_style'             => 'dotted',
				'_wcct_appearance_custom_text_border_width'             => '3',
				'_wcct_appearance_custom_text_border_color'             => '#dd3333',
				'_wcct_actions_during_stock'                            => 'none',
				'_wcct_actions_during_product_visibility'               => 'none',
				'_wcct_actions_during_add_to_cart'                      => 'none',
				'_wcct_actions_after_end_stock'                         => 'none',
				'_wcct_actions_after_end_product_visibility'            => 'none',
				'_wcct_actions_after_end_add_to_cart'                   => 'none',
				'_wcct_misc_add_to_cart_btn_text'                       => 'Get it now',
				'_wcct_misc_add_to_cart_btn_exclude'                    => '',
				'_wcct_misc_cookie_expire_time'                         => '1800',
				'_wcct_misc_timer_label_days'                           => 'days',
				'_wcct_misc_timer_label_hrs'                            => 'hrs',
				'_wcct_misc_timer_label_mins'                           => 'mins',
				'_wcct_misc_timer_label_secs'                           => 'secs',
			)
		);

		return self::$default;
	}

	public static function product_tax_rule_types( $types ) {
		$arr        = array();
		$wc_tax_obj = get_object_taxonomies( 'product', 'object' );
		$wc_tax     = array_keys( $wc_tax_obj );
		$exc_cats   = array( 'product_type', 'product_visibility', 'product_cat', 'product_tag', 'product_shipping_class' );
		$wc_tax     = array_diff( $wc_tax, $exc_cats );

		if ( is_array( $wc_tax ) && count( $wc_tax ) > 0 ) {
			$wc_tax = array_filter(
				$wc_tax, function ( $tax_name ) {
					return ( 'pa_' !== substr( $tax_name, 0, 3 ) );
				}
			);
		}

		if ( ! is_array( $wc_tax ) || count( $wc_tax ) === 0 ) {
			return $types;
		}

		sort( $wc_tax );
		foreach ( $types[ self::$rule_product_label ] as $key => $name ) {
			$arr[ $key ] = $name;
			if ( 'product_tags' === $key ) {
				foreach ( $wc_tax as $new_tax ) {
					$arr[ $new_tax ] = ucwords( $wc_tax_obj[ $new_tax ]->label );
				}
			}
		}

		$types[ self::$rule_product_label ] = $arr;

		return $types;
	}

	/**
	 * Saves the data for the wcct post type.
	 *
	 * @param int $post_id Post ID
	 * @param WP_Post Post Object
	 *
	 */
	public static function save_data( $post_id, $post ) {
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( is_int( wp_is_post_revision( $post ) ) ) {
			return;
		}
		if ( is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}
		if ( $post->post_type !== self::get_campaign_post_type_slug() ) {
			return;
		}

		$key = 'WCCT_INSTANCES';
		if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE !== '' ) {
			$key .= '_' . ICL_LANGUAGE_CODE;
		}

		delete_transient( $key );

		if ( isset( $_POST['wcct_settings_location'] ) ) {
			$location = explode( ':', $_POST['wcct_settings_location'] );
			$settings = array(
				'location' => $location[0],
				'hook'     => $location[1],
			);

			if ( $settings['hook'] === 'custom' ) {
				$settings['custom_hook']     = $_POST['wcct_settings_location_custom_hook'];
				$settings['custom_priority'] = $_POST['wcct_settings_location_custom_priority'];
			} else {
				$settings['custom_hook']     = '';
				$settings['custom_priority'] = '';
			}

			$settings['type'] = $_POST['wcct_settings_type'];

			update_post_meta( $post_id, '_wcct_settings', $settings );
		}

		if ( isset( $_POST['wcct_rule'] ) ) {
			update_post_meta( $post_id, 'wcct_rule', $_POST['wcct_rule'] );
		}
	}

	/*
	 *  register_post_status
	 *
	 *  This function will register custom post statuses
	 *
	 *  @type	function
	 *  @date	22/10/2015
	 *  @since	5.3.2
	 *
	 *  @param	$post_id (int)
	 *  @return	$post_id (int)
	 */
	public static function get_post_table_data( $trigger = 'all', $filters = array() ) {
		if ( $trigger == 'all' ) {
			$args = array(
				'post_type'        => self::get_campaign_post_type_slug(),
				'post_status'      => array( 'publish', WCCT_SHORT_SLUG . 'disabled' ),
				'suppress_filters' => false,   //WPML Compatibility
				'meta_key'         => '_wcct_campaign_menu_order',
				'orderby'          => 'ID',
				'order'            => 'DESC',
				'posts_per_page'   => 20,
				'paged'            => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			);
		} else {
			$meta_q      = array();
			$post_status = '';
			if ( $trigger == 'deactivated' ) {
				$post_status = WCCT_SHORT_SLUG . 'disabled';
			} else {
				$meta_q[] = array(
					'key'     => '_wcct_current_status_timing',
					'value'   => $trigger,
					'compare' => '=',
				);
			}
			$args = array(
				'post_type'        => self::get_campaign_post_type_slug(),
				'post_status'      => array( 'publish', WCCT_SHORT_SLUG . 'disabled' ),
				'suppress_filters' => false,   //WPML Compatibility
				'meta_key'         => '_wcct_campaign_menu_order',
				'orderby'          => 'ID',
				'order'            => 'DESC',
				'posts_per_page'   => 20,
				'paged'            => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			);
			if ( $post_status != '' ) {
				$args['post_status'] = $post_status;
			} else {
				$args['post_status'] = 'publish';
			}
			if ( is_array( $meta_q ) && count( $meta_q ) > 0 ) {
				$args['meta_query'] = $meta_q;
			}
		}

		$args        = wp_parse_args( $filters, $args );
		$q           = new WP_Query( $args );
		$found_posts = array();

		if ( $q->have_posts() ) {

			while ( $q->have_posts() ) {
				$q->the_post();
				$status           = get_post_status( get_the_ID() );
				$row_actions      = array();
				$deactivation_url = wp_nonce_url( add_query_arg( 'page', 'wc-settings', add_query_arg( 'tab', self::get_wc_settings_tab_slug(), add_query_arg( 'action', 'wcct-post-deactivate', add_query_arg( 'postid', get_the_ID(), add_query_arg( 'trigger', $trigger ) ), network_admin_url( 'admin.php' ) ) ) ), 'wcct-post-deactivate' );

				if ( $status == WCCT_SHORT_SLUG . 'disabled' ) {

					$activation_url = wp_nonce_url( add_query_arg( 'page', 'wc-settings', add_query_arg( 'tab', self::get_wc_settings_tab_slug(), add_query_arg( 'action', 'wcct-post-activate', add_query_arg( 'postid', get_the_ID(), add_query_arg( 'trigger', $trigger ) ), network_admin_url( 'admin.php' ) ) ) ), 'wcct-post-activate' );
					$row_actions[]  = array(
						'action' => 'activate',
						'text'   => __( 'Activate', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'link'   => $activation_url,
						'attrs'  => '',
					);
				} else {
					$row_actions[] = array(
						'action' => 'edit',
						'text'   => __( 'Edit', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'link'   => self::get_edit_post_link( get_the_ID() ),
						'attrs'  => '',
					);

					$row_actions[] = array(
						'action' => 'deactivate',
						'text'   => __( 'Deactivate', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
						'link'   => $deactivation_url,
						'attrs'  => '',
					);
				}
				$row_actions[] = array(
					'action' => 'wcct_duplicate',
					'text'   => __( 'Duplicate Campaign', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'link'   => wp_nonce_url( add_query_arg( 'page', 'wc-settings', add_query_arg( 'tab', self::get_wc_settings_tab_slug(), add_query_arg( 'action', 'wcct-duplicate', add_query_arg( 'postid', get_the_ID(), add_query_arg( 'trigger', $trigger ) ), network_admin_url( 'admin.php' ) ) ) ), 'wcct-duplicate' ),
					'attrs'  => '',
				);
				$row_actions[] = array(
					'action' => 'delete',
					'text'   => __( 'Delete Permanently', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
					'link'   => get_delete_post_link( get_the_ID(), '', true ),
					'attrs'  => '',
				);

				array_push(
					$found_posts, array(
						'id'             => get_the_ID(),
						'trigger_status' => $status,
						'row_actions'    => $row_actions,
					)
				);
			}
		}
		$found_posts['found_posts'] = $q->found_posts;

		return $found_posts;
	}

	public static function get_wc_settings_tab_slug() {
		return 'xl-countdown-timer';
	}

	public static function get_edit_post_link( $id = 0, $context = 'display' ) {
		if ( ! $post = self::get_post_data( $id ) ) {
			return;
		}

		if ( 'revision' === $post->post_type ) {
			$action = '';
		} elseif ( 'display' == $context ) {
			$action = '&amp;action=edit';
		} else {
			$action = '&action=edit';
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! $post_type_object ) {
			return;
		}

		if ( $post_type_object->_edit_link ) {
			$link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
		} else {
			$link = '';
		}

		return $link;
	}

	/**
	 * Get post data by post id | product id
	 *
	 * @param $item_id
	 * @param bool $force
	 *
	 * @return WP_Post|bool
	 */
	public static function get_post_data( $item_id, $force = false ) {
		$xl_cache_obj     = XL_Cache::get_instance();
		$xl_transient_obj = XL_Transient::get_instance();

		$cache_key = 'post_data_' . $item_id;

		/** When force enabled */
		if ( true === $force ) {
			$post_data = get_post( $item_id );
			self::set_post_data( $item_id, $post_data );
		} else {
			/**
			 * Setting xl cache and transient for Gift data
			 */
			$cache_data = $xl_cache_obj->get_cache( $cache_key, 'xl-wp-posts' );
			if ( false !== $cache_data ) {
				$post_data = $cache_data;
			} else {
				$transient_data = $xl_transient_obj->get_transient( $cache_key, 'xl-wp-posts' );

				if ( false !== $transient_data ) {
					$post_data = $transient_data;
					$xl_cache_obj->set_cache( $cache_key, $post_data, 'xl-wp-posts' );
				} else {
					$post_data = get_post( $item_id );
					self::set_post_data( $item_id, $post_data );
				}
			}
		}

		return $post_data;
	}

	/**
	 * Save post data in cache & transient
	 *
	 * @param string $item_id
	 * @param string $post_data
	 */
	public static function set_post_data( $item_id = '', $post_data = '' ) {
		$xl_cache_obj     = XL_Cache::get_instance();
		$xl_transient_obj = XL_Transient::get_instance();

		if ( empty( $item_id ) || empty( $post_data ) ) {
			return;
		}

		$cache_key = 'post_data_' . $item_id;
		$xl_transient_obj->set_transient( $cache_key, $post_data, DAY_IN_SECONDS, 'xl-wp-posts' );
		$xl_cache_obj->set_cache( $cache_key, $post_data, 'xl-wp-posts' );
	}

	public static function register_post_status() {

		// acf-disabled
		register_post_status(
			WCCT_SHORT_SLUG . 'disabled', array(
				'label'                     => __( 'Disabled', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Disabled <span class="count">(%s)</span>', 'Disabled <span class="count">(%s)</span>', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			)
		);
	}

	public static function get_parent_slug( $slug ) {
		foreach ( self::get_campaign_statuses() as $key => $trigger_list ) {

			if ( isset( $trigger_list['triggers'] ) && is_array( $trigger_list['triggers'] ) && count( $trigger_list['triggers'] ) > 0 ) {
				foreach ( $trigger_list['triggers'] as $trigger ) {

					if ( $trigger['slug'] == $slug ) {
						return $key;
					}
				}
			}
		}

		return '';
	}

	public static function wcct_get_between( $content, $start, $end ) {
		$r = explode( $start, $content );
		if ( isset( $r[1] ) ) {
			$r = explode( $end, $r[1] );

			return $r[0];
		}

		return '';
	}

	public static function wcct_xl_init() {
		remove_action( 'xl_loaded', array( 'XL_Common', 'load_text_domain' ), 10 );
		XL_Common::include_xl_core();
	}

	public static function wcct_license_check_schedule() {
		if ( ! wp_next_scheduled( 'wcct_maybe_schedule_check_license' ) ) {
			$resp = wp_schedule_event( current_time( 'timestamp' ), 'daily', 'wcct_maybe_schedule_check_license' );
		}
	}

	public static function check_license_state() {
		$license = new WCCT_EDD_License( WCCT_PLUGIN_FILE, WCCT_FULL_NAME, WCCT_VERSION, 'xlplugins', null, apply_filters( 'wcct_edd_api_url', 'https://xlplugins.com/' ) );
		$license->weekly_license_check();
	}

	public static function wcct_contain_current_query() {
		global $post, $wp_query;

		self::$wcct_post  = $post;
		self::$wcct_query = $wp_query;

		if ( is_front_page() && is_home() ) {
			self::$is_front_page = true;
		} elseif ( is_front_page() ) {
			self::$is_front_page = true;
		}
	}

	public static function get_timezone_difference() {
		$date_obj_utc = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$diff         = timezone_offset_get( timezone_open( self::wc_timezone_string() ), $date_obj_utc );

		return $diff;
	}

	/**
	 * Function to get timezone string by checking WordPress timezone settings
	 * @return mixed|string|void
	 */
	public static function wc_timezone_string() {
		// if site timezone string exists, return it
		if ( $timezone = get_option( 'timezone_string' ) ) {
			return $timezone;
		}

		// get UTC offset, if it isn't set then return UTC
		if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) ) {
			return 'UTC';
		}

		// get timezone using offset manual
		return WCCT_Common::get_timezone_by_offset( $utc_offset );
	}

	/**
	 * Function to get timezone string based on specified offset
	 *
	 * @param $offset
	 *
	 * @return string
	 *@see WCCT_Common::wc_timezone_string()
	 *
	 */
	public static function get_timezone_by_offset( $offset ) {
		switch ( $offset ) {
			case '-12':
				return 'GMT-12';
				break;
			case '-11.5':
				return 'Pacific/Niue'; // 30 mins wrong
				break;
			case '-11':
				return 'Pacific/Niue';
				break;
			case '-10.5':
				return 'Pacific/Honolulu'; // 30 mins wrong
				break;
			case '-10':
				return 'Pacific/Tahiti';
				break;
			case '-9.5':
				return 'Pacific/Marquesas';
				break;
			case '-9':
				return 'Pacific/Gambier';
				break;
			case '-8.5':
				return 'Pacific/Pitcairn'; // 30 mins wrong
				break;
			case '-8':
				return 'Pacific/Pitcairn';
				break;
			case '-7.5':
				return 'America/Hermosillo'; // 30 mins wrong
				break;
			case '-7':
				return 'America/Hermosillo';
				break;
			case '-6.5':
				return 'America/Belize'; // 30 mins wrong
				break;
			case '-6':
				return 'America/Belize';
				break;
			case '-5.5':
				return 'America/Belize'; // 30 mins wrong
				break;
			case '-5':
				return 'America/Panama';
				break;
			case '-4.5':
				return 'America/Lower_Princes'; // 30 mins wrong
				break;
			case '-4':
				return 'America/Curacao';
				break;
			case '-3.5':
				return 'America/Paramaribo'; // 30 mins wrong
				break;
			case '-3':
				return 'America/Recife';
				break;
			case '-2.5':
				return 'America/St_Johns';
				break;
			case '-2':
				return 'America/Noronha';
				break;
			case '-1.5':
				return 'Atlantic/Cape_Verde'; // 30 mins wrong
				break;
			case '-1':
				return 'Atlantic/Cape_Verde';
				break;
			case '+1':
				return 'Africa/Luanda';
				break;
			case '+1.5':
				return 'Africa/Mbabane'; // 30 mins wrong
				break;
			case '+2':
				return 'Africa/Harare';
				break;
			case '+2.5':
				return 'Indian/Comoro'; // 30 mins wrong
				break;
			case '+3':
				return 'Asia/Baghdad';
				break;
			case '+3.5':
				return 'Indian/Mauritius'; // 30 mins wrong
				break;
			case '+4':
				return 'Indian/Mauritius';
				break;
			case '+4.5':
				return 'Asia/Kabul';
				break;
			case '+5':
				return 'Indian/Maldives';
				break;
			case '+5.5':
				return 'Asia/Kolkata';
				break;
			case '+5.75':
				return 'Asia/Kathmandu';
				break;
			case '+6':
				return 'Asia/Urumqi';
				break;
			case '+6.5':
				return 'Asia/Yangon';
				break;
			case '+7':
				return 'Antarctica/Davis';
				break;
			case '+7.5':
				return 'Asia/Jakarta'; // 30 mins wrong
				break;
			case '+8':
				return 'Asia/Manila';
				break;
			case '+8.5':
				return 'Asia/Pyongyang';
				break;
			case '+8.75':
				return 'Australia/Eucla';
				break;
			case '+9':
				return 'Asia/Tokyo';
				break;
			case '+9.5':
				return 'Australia/Darwin';
				break;
			case '+10':
				return 'Australia/Brisbane';
				break;
			case '+10.5':
				return 'Australia/Lord_Howe';
				break;
			case '+11':
				return 'Antarctica/Casey';
				break;
			case '+11.5':
				return 'Pacific/Auckland'; // 30 mins wrong
				break;
			case '+12':
				return 'Pacific/Wallis';
				break;
			case '+12.75':
				return 'Pacific/Chatham';
				break;
			case '+13':
				return 'Pacific/Fakaofo';
				break;
			case '+13.75':
				return 'Pacific/Chatham'; // 1 hr wrong
				break;
			case '+14':
				return 'Pacific/Kiritimati';
				break;
			default:
				return 'UTC';
				break;
		}
	}

	public static function wc_timezone_string_old() {

		// if site timezone string exists, return it
		if ( $timezone = get_option( 'timezone_string' ) ) {
			return $timezone;
		}

		// get UTC offset, if it isn't set then return UTC
		if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) ) {
			return 'UTC';
		}

		// adjust UTC offset from hours to seconds
		$utc_offset *= 3600;

		// attempt to guess the timezone string from the UTC offset
		$timezone = timezone_name_from_abbr( '', $utc_offset, 0 );

		// last try, guess timezone string manually
		if ( false === $timezone ) {
			$is_dst = date( 'I' );

			foreach ( timezone_abbreviations_list() as $abbr ) {
				foreach ( $abbr as $city ) {
					if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset ) {
						return $city['timezone_id'];
					}
				}
			}

			// fallback to UTC
			return 'UTC';
		}

		return $timezone;
	}

	public static function get_total_stock( $product ) {
		$total_stock = 0;
		$child_stock = 0;

		$WCCT_Campaign = WCCT_Campaign::get_instance();
		if ( $product->get_type() == 'variation' ) {
			$product = wc_get_product( $WCCT_Campaign->wcct_get_product_parent_id( $product ) );
		}

		$parent_stock = max( 0, $product->get_stock_quantity() );
		if ( sizeof( $product->get_children() ) > 0 ) {
			foreach ( $product->get_children() as $child_id ) {
				$stock_status = get_post_meta( $child_id, '_stock_status', true );
				if ( $stock_status == 'instock' ) {
					if ( 'yes' === get_post_meta( $child_id, '_manage_stock', true ) ) {
						$stock       = get_post_meta( $child_id, '_stock', true );
						$total_stock += max( 0, wc_stock_amount( $stock ) );
					} else {
						$child_stock ++;
					}
				}
			}
			if ( $child_stock > 0 ) {
				$total_stock += $parent_stock;
			}
		} else {
			$total_stock = $parent_stock;
		}

		return wc_stock_amount( $total_stock );
	}

	public static function get_sale_compatible_league_product_types() {
		return array(
			'simple',
			'subscription',
			'variation',
			'external',
			'bundle',
			'subscription_variation',
			'course',
			'composite',
			'yith_bundle',
			'course', //learndash course product type
			'webinar',
		);
	}

	public static function get_simple_league_product_types() {
		return array(
			'simple',
			'subscription',
			'course',
			'webinar',
		);
	}

	public static function get_variable_league_product_types() {
		return array(
			'variable',
			'variable-subscription',
		);
	}

	public static function get_add_to_cart_text_single_elegible_product_types() {
		return array(
			'simple',
			'variable',
			'external',
			'bundle',
			'grouped',
			'subscription',
			'variable-subscription',
			'course',
		);
	}

	public static function get_variation_league_product_types() {
		return array(
			'variation',
			'subscription_variation',
		);
	}

	public static function array_recursive( $aArray1, $aArray2 ) {
		$aReturn = array();

		if ( $aArray1 && is_array( $aArray1 ) && count( $aArray1 ) > 0 ) {
			foreach ( $aArray1 as $mKey => $mValue ) {
				if ( array_key_exists( $mKey, $aArray2 ) ) {
					if ( is_array( $mValue ) ) {
						$aRecursiveDiff = self::array_recursive( $mValue, $aArray2[ $mKey ] );
						if ( is_array( $aRecursiveDiff ) && count( $aRecursiveDiff ) ) {
							$aReturn[ $mKey ] = $aRecursiveDiff;
						}
					} else {
						if ( $mValue != $aArray2[ $mKey ] ) {
							$aReturn[ $mKey ] = $mValue;
						}
					}
				} else {
					$aReturn[ $mKey ] = $mValue;
				}
			}
		}

		return $aReturn;
	}

	public static function wcct_apply_opacity( $hex, $opacity ) {
		$hex      = str_replace( '#', '', $hex );
		$length   = strlen( $hex );
		$rgb['r'] = hexdec( $length == 6 ? substr( $hex, 0, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 0, 1 ), 2 ) : 0 ) );
		$rgb['g'] = hexdec( $length == 6 ? substr( $hex, 2, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 1, 1 ), 2 ) : 0 ) );
		$rgb['b'] = hexdec( $length == 6 ? substr( $hex, 4, 2 ) : ( $length == 3 ? str_repeat( substr( $hex, 2, 1 ), 2 ) : 0 ) );

		$return = '';
		if ( is_array( $rgb ) && count( $rgb ) > 0 ) {
			$return = 'rgba(';
			$return .= implode( ', ', $rgb );
			$return .= ', ' . ( ( $opacity > 1 ) ? 1 : $opacity );
			$return .= ')';
		}

		return $return;
	}

	public static function check_query_params() {

		$force_debug = filter_input( INPUT_GET, 'wcct_force_debug' );

		if ( $force_debug === 'yes' ) {
			self::$is_force_debug = true;
		}
	}

	public static function get_coupons_cmb2() {
		$array = array();
		if ( isset( $_POST['term'] ) && $_POST['term'] !== '' ) {

			$args = array(
				'post_type'     => 'shop_coupon',
				'numberposts'   => 15,
				'post_per_page' => 15,
				'paged'         => 1,
				's'             => $_POST['term'],
				'post_status'   => 'publish',
			);

			$posts = get_posts( $args );

			if ( $posts && is_array( $posts ) && count( $posts ) > 0 ) {
				foreach ( $posts as $post ) :
					setup_postdata( $post );

					$array[] = array(
						'value' => (string) $post->ID,
						'text'  => $post->post_title,
					);

				endforeach;
			}
		}

		wp_send_json( $array );
	}

	public static function get_wcct_countdown() {
		$array = array();


		if ( isset( $_POST['term'] ) && $_POST['term'] !== '' ) {

			$args = array(
				'post_type'     => 'wcct_countdown',
				'numberposts'   => 15,
				'post_per_page' => 15,
				'paged'         => 1,
				's'             => $_POST['term'],
				'post_status'   => 'publish',
			);

			$posts = get_posts( $args );

			if ( $posts && is_array( $posts ) && count( $posts ) > 0 ) {
				foreach ( $posts as $post ) :
					setup_postdata( $post );

					$array[] = array(
						'value' => (string) $post->ID,
						'text'  => $post->post_title,
					);

				endforeach;
			}
		}

		wp_send_json( $array );
	}

	public static function get_pages_cmb2() {
		$array = array();
		if ( isset( $_POST['term'] ) && $_POST['term'] !== '' ) {

			$args = array(
				'post_type'     => 'page',
				'post_per_page' => 10,
				'paged'         => 1,
				's'             => $_POST['term'],
				'post_status'   => 'publish',
			);

			$posts = get_posts( $args );

			if ( $posts && is_array( $posts ) && count( $posts ) > 0 ) {
				foreach ( $posts as $post ) :
					setup_postdata( $post );

					$array[] = array(
						'value' => (string) $post->ID,
						'text'  => $post->post_title,
					);

				endforeach;
			}
		}

		wp_send_json( $array );
	}

	public static function get_coupons( $is_ajax = false ) {
		$args = array(
			'post_type'   => 'shop_coupon',
			'showposts'   => 15,
			'post_status' => 'publish',
		);

		$posts = get_posts( $args );
		$array = array();

		if ( $is_ajax ) {
			$array[] = array(
				'value' => '',
				'text'  => __( 'Choose a Coupon', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			);
		}

		if ( $posts && is_array( $posts ) && count( $posts ) > 0 ) {
			foreach ( $posts as $post ) :
				setup_postdata( $post );

				if ( $is_ajax ) {
					$array[] = array(
						'value' => $post->ID,
						'text'  => $post->post_title,
					);
				} else {
					$array[ $post->ID ] = $post->post_title;
				}

			endforeach;
		}

		return $array;
	}

	public static function get_pages( $is_ajax = false ) {
		$args = array(
			'post_type'   => 'page',
			'showposts'   => - 1,
			'post_status' => 'publish',
		);

		$posts = get_posts( $args );
		$array = array();

		if ( $posts && is_array( $posts ) && count( $posts ) > 0 ) {
			foreach ( $posts as $post ) :
				setup_postdata( $post );

				if ( $is_ajax ) {
					$array[] = array(
						'value' => $post->ID,
						'text'  => $post->post_title,
					);
				} else {
					$array[ $post->ID ] = $post->post_title;
				}

			endforeach;
		}

		return $array;
	}

	public static function get_button_ref() {
		if ( ! wp_verify_nonce( $_POST['nonce'], $_POST['action'] ) ) {
			return;
		}

		$cookie_name = 'wcct_ck_header_ref';

		if ( setcookie( $cookie_name, $_POST['location'], time() + ( HOUR_IN_SECONDS * 1 ), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true ) ) {
			echo 'Cookie is set.';
		} else {
			echo 'Some problem in setting Cookie.';
		}

		exit;
	}

	public static function wcct_valid_admin_pages( $mode = 'all' ) {
		$screen       = get_current_screen();
		$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );
		$status       = false;

		if ( ! is_object( $screen ) ) {
			return $status;
		}

		if ( 'all' === $mode ) {
			if ( ( ( $screen->base == $wc_screen_id . '_page_wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == 'xl-countdown-timer' ) || ( $screen->base == 'post' && $screen->post_type == WCCT_Common::get_campaign_post_type_slug() ) ) ) {
				$status = true;
			}
		} elseif ( 'single' == $mode ) {
			if ( ( $screen->base == 'post' && $screen->post_type == WCCT_Common::get_campaign_post_type_slug() ) ) {
				$status = true;
			}
		}

		$status = apply_filters( 'wcct_valid_admin_pages', $status, $mode );

		return $status;
	}

	public static function toolbar_link_to_xlplugins( $wp_admin_bar ) {
		if ( is_admin() ) {
			return;
		}
		if ( ! is_user_logged_in() || ! current_user_can( 'administrator' ) ) {
			return;
		}

		$upload_dir = wp_upload_dir();
		$base_url   = $upload_dir['baseurl'] . '/' . 'finale-woocommerce-sales-countdown-timer-discount-plugin';
		$args       = array(
			'id'    => 'wcct_admin_page_node',
			'title' => 'XL Finale',
			'href'  => admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() ),
			'meta'  => array(
				'class' => 'wcct_admin_page_node',
			),
		);
		$wp_admin_bar->add_node( $args );

		if ( is_singular( 'product' ) ) {
			$args = array(
				'id'     => 'wcct_admin_page_node_1',
				'title'  => 'See Log',
				'href'   => $base_url . '/force.txt',
				'parent' => 'wcct_admin_page_node',
			);

			$wp_admin_bar->add_node( $args );
		}
	}

	public static function wcct_quick_view_html() {
		$data        = self::get_item_data( $_POST['ID'] );
		$camp_data   = get_post( $_POST['ID'] );
		$is_disabled = false;

		if ( is_object( $camp_data ) && isset( $camp_data->post_status ) && ( WCCT_SHORT_SLUG . 'disabled' == $camp_data->post_status ) ) {
			$is_disabled = true;
		}

		if ( isset( $data['campaign_fixed_recurring_start_date'] ) && $data['campaign_fixed_recurring_start_date'] != '' ) {

			$campaign_type = '';
			if ( $data['campaign_type'] == 'fixed_date' ) {
				$campaign_type = __( 'Fixed Date', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			} elseif ( $data['campaign_type'] == 'recurring' ) {
				$campaign_type = __( 'Recurring', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			}
		}

		$campaign_type = apply_filters( 'wcct_custom_campaign_type', $campaign_type, $data );
		$state         = self::wcct_get_campaign_status( $_POST['ID'] );
		$ticks         = array();
		$tick_views    = array();
		$discount      = 'Off';

		if ( isset( $data['deal_enable_price_discount'] ) && $data['deal_enable_price_discount'] == '1' ) {
			$discount = 'On';

			array_push( $ticks, 'discount' );

			$discount .= ' (';
			if ( $data['deal_mode'] == 'simple' ) {
				$discount .= __( 'Basic', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			} elseif ( $data['deal_mode'] == 'tiered' ) {
				$discount .= __( 'Advanced', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			} else {
				$discount .= apply_filters( 'wcct_discount_label_on_quick_view', '', $data );
			}
			$discount .= ')';
		}
		$tick_views['discount'] = 'Discount: ' . $discount;
		$inventory              = 'Off';

		if ( isset( $data['deal_enable_goal'] ) && $data['deal_enable_goal'] == '1' ) {
			$inventory = 'On';
			array_push( $ticks, 'inventory' );

			$inventory .= ' (';
			if ( $data['deal_units'] == 'custom' ) {
				$inventory .= __( 'Custom Stock', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			} elseif ( $data['deal_units'] == 'same' ) {
				$inventory .= __( 'Product Stock', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			}
			$inventory .= ')';
		}
		$tick_views['inventory'] = 'Inventory: ' . $inventory;
		$coupons                 = 'Off';

		if ( isset( $data['coupons_enable'] ) && $data['coupons_enable'] == '1' ) {
			$coupons = 'On';
			array_push( $ticks, 'coupons' );

			$coupons .= ' (';
			if ( $data['coupons_apply_mode'] == 'auto' ) {
				$coupons .= __( 'Auto', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			} elseif ( $data['coupons_apply_mode'] == 'manual' ) {
				$coupons .= __( 'Manual', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
			}
			$coupons .= ')';
		}
		$tick_views['coupons'] = 'Coupon: ' . $coupons;
		$countdown_timer       = 'Off';

		if ( isset( $data['location_timer_show_single'] ) && $data['location_timer_show_single'] == '1' ) {
			$countdown_timer = 'On';
			array_push( $ticks, 'countdown_timer' );
		}
		$tick_views['countdown_timer'] = 'Countdown Timer: ' . $countdown_timer;
		$counter_bar                   = 'Off';

		if ( isset( $data['location_bar_show_single'] ) && $data['location_bar_show_single'] == '1' ) {
			$counter_bar = 'On';
			array_push( $ticks, 'counter_bar' );
		}
		$tick_views['counter_bar'] = 'Counter Bar: ' . $counter_bar;

		$sticky_header = 'Off';

		if ( isset( $data['location_timer_show_sticky_header'] ) && $data['location_timer_show_sticky_header'] == '1' ) {
			$sticky_header = 'On';
			array_push( $ticks, 'sticky_header' );
		}
		$tick_views['sticky_header'] = 'Sticky Header: ' . $sticky_header;

		$sticky_footer = 'Off';

		if ( isset( $data['location_timer_show_sticky_footer'] ) && $data['location_timer_show_sticky_footer'] == '1' ) {
			$sticky_footer = 'On';
			array_push( $ticks, 'sticky_footer' );
		}
		$tick_views['sticky_footer'] = 'Sticky Footer: ' . $sticky_footer;

		$custom_text = 'Off';

		if ( isset( $data['location_show_custom_text'] ) && $data['location_show_custom_text'] == '1' ) {
			$custom_text = 'On';
			array_push( $ticks, 'custom_text' );
		}
		$tick_views['custom_text'] = 'Custom Text: ' . $custom_text;

		$icon_class_state = '';
		if ( $state == 'Paused' ) {
			$icon_class_state = 's-p';
		}
		if ( $state == 'Scheduled' ) {
			$icon_class_state = 's-s';
		}

		if ( $state == 'Running' ) {
			$icon_class_state = 's-r';
		}
		if ( $state == 'Finished' ) {
			$icon_class_state = 's-f';
		}

		$icon_class_state = apply_filters( 'wcct_custom_campaign_state_class', $icon_class_state, $data );

		// changing status if campaign disabled
		if ( true === $is_disabled ) {
			$state            = 'Deactivated';
			$icon_class_state = 's-f';
		}

		$tick_views = apply_filters( 'wcct_quick_view_html_for_ticks', $tick_views, $data );
		?>
		<ul class="wcct_quick_view">
		<li>
			<i class="flicon flicon-clock-circular-outline"></i>Campaign Type: <u><?php echo $campaign_type; ?></u>
		</li>
		<li>
			<i class="flicon flicon-weekly-calendar <?php echo $icon_class_state; ?>"></i>Campaign State:
			<u><?php echo $state; ?></u>
		</li>
		<?php
		foreach ( $tick_views as $key => $value ) {
			?>
			<li>
				<i class="flicon <?php echo ( in_array( $key, $ticks ) ) ? 'flicon-checkmark-for-verification' : 'flicon-cross-remove-sign'; ?>"></i><?php echo $value; ?>
			</li>
			<?php
		}

		exit;
	}

	public static function wcct_get_campaign_status( $item_id ) {
		$output  = '';
		$data    = self::get_item_data( $item_id );
		$timings = WCCT_Common::start_end_timestamp( $data );
		$output  = apply_filters( 'wcct_get_custom_campaign_status', $output, $item_id, $data, $timings );

		if ( ! empty( $output ) ) {
			return $output;
		}
		extract( $timings );
		$slug_timing = 'deactivated';
		if ( $end_date_timestamp > 0 ) {
			if ( $todayDate >= $start_date_timestamp && $todayDate < $end_date_timestamp ) {
				$output      = __( 'Running', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
				$slug_timing = 'running';
			} elseif ( $first_occur && $todayDate <= $rec_intial_end_time ) {
				$output      = __( 'Paused', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
				$slug_timing = 'paused';
			} elseif ( $todayDate > $end_date_timestamp ) {
				$output      = __( 'Finished', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
				$slug_timing = 'finished';
			} elseif ( $start_date_timestamp > $todayDate ) {
				$output      = __( 'Scheduled', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
				$slug_timing = 'schedule';
			}
		}

		return $output;
	}

	public static function start_end_timestamp( $data ) {
		$start_date_timestamp = $end_date_timestamp = 0;
		$todayDate            = time();
		$first_occur          = false;
		$rec_intial_end_time  = 0;

		if ( ($data['campaign_type'] == 'fixed_date' || $data['campaign_type'] == 'recurring') && isset( $data['campaign_fixed_recurring_start_date'] ) && $data['campaign_fixed_recurring_start_date'] != '' ) {
			$is_scheduled = false;
			$start_date   = $data['campaign_fixed_recurring_start_date'];
			$start_time   = $data['campaign_fixed_recurring_start_time'];

			$start_date_timestamp = self::wcct_get_timestamp_wc_native( $start_date . ' ' . $start_time );
			$first_occur          = false;
			$rec_intial_end_time  = 0;
			// calculate durations
			$durations_day      = (float) isset( $data['campaign_recurring_duration_days'] ) ? $data['campaign_recurring_duration_days'] * DAY_IN_SECONDS : 0;
			$durations_hrs      = (float) isset( $data['campaign_recurring_duration_hrs'] ) ? $data['campaign_recurring_duration_hrs'] * HOUR_IN_SECONDS : 0;
			$durations_min      = (float) isset( $data['campaign_recurring_duration_min'] ) ? $data['campaign_recurring_duration_min'] * MINUTE_IN_SECONDS : 0;
			$total_duration     = $durations_day + $durations_hrs + $durations_min;
			$end_date_timestamp = strtotime( "+{$total_duration} seconds", $start_date_timestamp );

			$rec_loop          = (int) isset( $data['campaign_recurring_ends_after_x_days'] ) ? $data['campaign_recurring_ends_after_x_days'] : 0;
			$rec_gap_days      = (float) isset( $data['campaign_recurring_gap_days'] ) ? $data['campaign_recurring_gap_days'] * DAY_IN_SECONDS : 0;
			$rec_gap_hrs       = (float) isset( $data['campaign_recurring_gap_hrs'] ) ? $data['campaign_recurring_gap_hrs'] * HOUR_IN_SECONDS : 0;
			$rec_gap_min       = (float) isset( $data['campaign_recurring_gap_mins'] ) ? $data['campaign_recurring_gap_mins'] * MINUTE_IN_SECONDS : 0;
			$total_gap_seconds = $rec_gap_days + $rec_gap_hrs + $rec_gap_min;

			if ( $data['campaign_type'] == 'fixed_date' ) {
				$end_date = $data['campaign_fixed_end_date'];
				$end_time = $data['campaign_fixed_end_time'];

				$end_date_timestamp = self::wcct_get_timestamp_wc_native( $end_date . ' ' . $end_time );
			} elseif ( $data['campaign_type'] == 'recurring' ) {
				$rec_intial_start_time = $start_date_timestamp;
				$rec_intial_end_time   = $end_date_timestamp;
				if ( isset( $data['campaign_recurring_ends'] ) && $data['campaign_recurring_ends'] == 'recurring' ) {
					if ( $todayDate >= $start_date_timestamp && $todayDate < $end_date_timestamp ) {
						//do nothing
					} else {
						// after set recurrences - this always run once if recurrence sets 0
						for ( $rec = 0; $rec < $rec_loop; $rec ++ ) {
							$first_occur           = true;
							$rec_intial_start_time = strtotime( "+{$total_gap_seconds} seconds", $rec_intial_end_time );
							$rec_intial_end_time   = strtotime( "+{$total_duration} seconds", $rec_intial_start_time );
							if ( $todayDate >= $rec_intial_start_time && $todayDate < $rec_intial_end_time ) {
								$is_scheduled         = false;
								$start_date_timestamp = $rec_intial_start_time;
								$end_date_timestamp   = $rec_intial_end_time;
								break;
							}

							if ( $rec_intial_start_time >= $todayDate ) {
								// breaking when recurrence start time is higher then current time. no need to continue loop.
								break;
							}

							if ( ( $rec + 1 ) == $rec_loop && $todayDate >= $rec_intial_start_time ) {
								$first_occur          = false; // case when campaign is finished. so hard passing first occr false to avoid showing paused
								$total_seconds        = $total_gap_seconds + $total_duration;
								$start_date_timestamp = strtotime( "-{$total_seconds} seconds", $rec_intial_start_time );
								$end_date_timestamp   = strtotime( "-{$total_seconds} seconds", $rec_intial_end_time );
							}
						}
					}
				} elseif ( isset( $data['campaign_recurring_ends'] ) && $data['campaign_recurring_ends'] == 'specific_time' ) {
					$end_date = $data['data_end_date_of_deal'];
					$end_time = $data['data_end_time_of_deal'];

					$end_specfic_timestamp = self::wcct_get_timestamp_wc_native( $end_date . ' ' . $end_time );

					$k         = 0;
					$total_gap = ( $total_gap_seconds + $total_duration );
					$incre     = self::get_loop_count( $start_date_timestamp, $end_specfic_timestamp, $total_gap );

					while ( $k < $incre ) {
						if ( $k > 0 ) {
							$first_occur = true;
						}

						$rec_intial_start_time = strtotime( "+{$total_gap_seconds} seconds", $rec_intial_end_time );
						$rec_intial_end_time   = strtotime( "+{$total_duration} seconds", $rec_intial_start_time );

						if ( $end_specfic_timestamp > $rec_intial_start_time ) {

							//let the campaign run
							//Checking occurrences
							$end_date_timestamp = $rec_intial_end_time;
							if ( $end_specfic_timestamp <= $rec_intial_end_time ) {

								$end_date_timestamp = $end_specfic_timestamp;
								if ( $todayDate >= $rec_intial_start_time && $todayDate <= $end_date_timestamp ) {

								} else {
									// end time
									$total_seconds        = $total_gap_seconds + $total_duration;
									$start_date_timestamp = strtotime( "-{$total_seconds} seconds", $rec_intial_start_time );
									$end_date_timestamp   = strtotime( "-{$total_seconds} seconds", $rec_intial_end_time );
								}
								break;
							} elseif ( $todayDate >= $rec_intial_start_time && $todayDate <= $rec_intial_end_time ) {
								$total_seconds = $total_gap_seconds + $total_duration;

								$start_date_timestamp = strtotime( "-{$total_seconds} seconds", $rec_intial_start_time );

								break;
							} elseif ( $todayDate <= $rec_intial_start_time ) {

								$total_seconds        = $total_gap_seconds + $total_duration;
								$start_date_timestamp = strtotime( "-{$total_seconds} seconds", $rec_intial_start_time );
								$end_date_timestamp   = strtotime( "-{$total_seconds} seconds", $rec_intial_end_time );
								break;
							}
						} else {

							$first_occur          = false; // case when campaign is finished. so hard passing first occr false to avoid showing paused
							$total_seconds        = $total_gap_seconds + $total_duration;
							$start_date_timestamp = strtotime( "-{$total_seconds} seconds", $rec_intial_start_time );
							$end_date_timestamp   = strtotime( "-{$total_seconds} seconds", $rec_intial_end_time );
							break;
						}
						$k ++;
					}
				} else {
					//never case
					$k         = 0;
					$total_gap = $total_gap_seconds + $total_duration;
					$incre     = self::get_loop_count( $start_date_timestamp, $todayDate, $total_gap );
					while ( $k < $incre ) {
						if ( $k > 0 ) {
							$first_occur = true;
						}
						$rec_intial_start_time = strtotime( "+{$total_gap_seconds} seconds", $rec_intial_end_time );
						$rec_intial_end_time   = strtotime( "+{$total_duration} seconds", $rec_intial_start_time );
						if ( $todayDate >= $rec_intial_start_time && $todayDate <= $rec_intial_end_time ) {
							$start_date_timestamp = $rec_intial_start_time;
							$end_date_timestamp   = $rec_intial_end_time;
							break;
						}
						$k ++;
					}
				}
			}
		}

		return array(
			'todayDate'            => (int) $todayDate,
			'start_date_timestamp' => (int) $start_date_timestamp,
			'end_date_timestamp'   => (int) $end_date_timestamp,
			'first_occur'          => $first_occur,
			'rec_intial_end_time'  => $rec_intial_end_time,
		);
	}

	public static function wcct_get_timestamp_wc_native( $dt ) {
		$timezone      = self::wc_timezone_string();
		$date          = new DateTime( $dt, new DateTimeZone( $timezone ) );
		$ret_timestamp = $date->getTimestamp();

		return $ret_timestamp;
	}

	public static function get_loop_count( $start_date_timestamp, $todayDate, $total_gap ) {
		$incre = 0;

		if ( $total_gap > 0 ) {
			$incre = ( ( $todayDate - $start_date_timestamp ) / ( $total_gap ) );
			$incre = ceil( $incre ) + 1;
		}

		return (int) $incre;
	}

	public static function get_global_default_settings() {
		$default    = array(
			'wcct_positions_approach'        => 'new',
			'wcct_timer_hide_days'           => 'no',
			'wcct_timer_hide_hrs'            => 'no',
			'wcct_timer_hide_multiple'       => 'no',
			'wcct_reload_page_on_timer_ends' => 'yes',
		);
		$default_db = get_option( 'wcct_global_options', array() );
		$default    = wp_parse_args( $default_db, $default );

		return $default;
	}

	public static function get_current_post_id() {
		return '{{wcct_current_post_id}}';
	}

	public static function pr( $arr ) {
		echo '<pre>';
		print_r( $arr );
		echo '</pre>';
	}

	/**
	 * Hooked in `wp`
	 * Prepares & Registers header info blocks to show in admin bar
	 * Process all the data we fetched for a single product and extract info to show to admin.
	 * @since 1.1
	 */
	public static function init_header_logs() {

		if ( is_admin() ) {
			return;
		}

		if ( ! self::$info_generated && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && is_object( self::$wcct_post ) && self::$wcct_post->post_type == 'product' ) {
			wcct_force_log( 'Initializing header info function  Product : ' . self::$wcct_post->ID );
			$getdata = WCCT_Core()->public->get_single_campaign_pro_data( self::$wcct_post->ID );

			WCCT_Core()->appearance->add_header_info( sprintf( __( 'Product #%1$d %2$s', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), self::$wcct_post->ID, self::$wcct_post->post_title ) );

			// running campaigns
			$timers = array();
			if ( isset( $getdata['running'] ) && ! empty( $getdata['running'] ) ) {
				foreach ( $getdata['running'] as $key => $camp ) {
					array_push( $timers, sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $camp ), self::get_the_title( $camp ), $camp ) );
				}
			}
			if ( isset( WCCT_Core()->public->sticky_header ) && is_array( WCCT_Core()->public->sticky_header ) && count( WCCT_Core()->public->sticky_header ) > 0 ) {
				foreach ( WCCT_Core()->public->sticky_header as $key => $camp ) {
					array_push( $timers, sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $key ), self::get_the_title( $key ), $key ) );
				}
			}
			if ( isset( WCCT_Core()->public->sticky_footer ) && is_array( WCCT_Core()->public->sticky_footer ) && count( WCCT_Core()->public->sticky_footer ) > 0 ) {
				foreach ( WCCT_Core()->public->sticky_footer as $key => $camp ) {
					array_push( $timers, sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $key ), self::get_the_title( $key ), $key ) );
				}
			}
			if ( count( $timers ) > 0 ) {
				$timers = array_unique( $timers );
				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Running Campaigns:  %s', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), implode( ', ', $timers ) ) );
				WCCT_Common::$isheadeer_alert = true;
			} else {
				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Running Campaigns:  None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) ) );
			}
			// running campaigns

			if ( isset( $getdata['expired'] ) && ! empty( $getdata['expired'] ) ) {
				$timers = array();
				foreach ( $getdata['expired'] as $key => $camp ) {
					array_push( $timers, sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $camp ), self::get_the_title( $camp ), $camp ) );
				}
				foreach ( $getdata['scheduled'] as $key => $camp ) {
					array_push( $timers, sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $camp ), self::get_the_title( $camp ), $camp ) );
				}
				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Non-running Campaigns:  %s', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), implode( ', ', $timers ) ) );
			} else {
				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Non-running Campaigns:  None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) ) );
			}

			if ( isset( $getdata['deals'] ) && ! empty( $getdata['deals'] ) ) {

				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Discounts : Yes (%s)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $getdata['deals']['campaign_id'] ), self::get_the_title( $getdata['deals']['campaign_id'] ), $getdata['deals']['campaign_id'] ) ) );
			} else {
				WCCT_Core()->appearance->add_header_info( __( 'Discounts: No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}

			if ( isset( $getdata['goals'] ) && ! empty( $getdata['goals'] ) ) {

				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Inventory: Yes (%s)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $getdata['goals']['campaign_id'] ), self::get_the_title( $getdata['goals']['campaign_id'] ), $getdata['goals']['campaign_id'] ) ) );
			} else {
				WCCT_Core()->appearance->add_header_info( __( 'Inventory: No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}

			if ( isset( $getdata['coupons'] ) && ! empty( $getdata['coupons'] ) ) {
				$timers = array();
				foreach ( $getdata['coupons'] as $key => $timer ) {
					array_push( $timers, sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $key ), self::get_the_title( $key ), $key ) );
				}
				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Coupons: Yes (%s)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), implode( ', ', $timers ) ) );
			} else {
				WCCT_Core()->appearance->add_header_info( __( 'Coupons: No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}

			if ( isset( $getdata['single_timer'] ) && ! empty( $getdata['single_timer'] ) ) {
				$timers = array();
				foreach ( $getdata['single_timer'] as $key => $timer ) {
					array_push( $timers, sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $key ), self::get_the_title( $key ), $key ) );
				}
				WCCT_Core()->appearance->add_header_info( sprintf( __( 'CountDown Timer: Yes (%s)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), implode( ', ', $timers ) ) );
			} else {
				WCCT_Core()->appearance->add_header_info( __( 'CountDown Timer: No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}
			if ( isset( $getdata['single_bar'] ) && ! empty( $getdata['single_bar'] ) ) {
				$timers = array();
				foreach ( $getdata['single_bar'] as $key => $timer ) {

					if ( $key !== $getdata['goals']['campaign_id'] ) {
						continue;
					}
					array_push( $timers, sprintf( '<a href="%s" target="_blank" title="%s">%s</a>', self::get_edit_post_link( $key ), self::get_the_title( $key ), $key ) );
				}
				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Counter Bar: Yes (%s)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), implode( ', ', $timers ) ) );
			} else {
				WCCT_Core()->appearance->add_header_info( __( 'Counter Bar: No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}

			// sticky header
			if ( isset( WCCT_Core()->public->sticky_header ) && is_array( WCCT_Core()->public->sticky_header ) && count( WCCT_Core()->public->sticky_header ) > 0 ) {
				$timers = array();
				foreach ( WCCT_Core()->public->sticky_header as $key => $timer ) {
					array_push( $timers, sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $key ), self::get_the_title( $key ), $key ) );
				}
				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Sticky Header: Yes (%s)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), implode( ', ', $timers ) ) );
			} else {
				WCCT_Core()->appearance->add_header_info( __( 'Sticky Header: No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}
			// sticky header

			// sticky footer
			if ( isset( WCCT_Core()->public->sticky_footer ) && is_array( WCCT_Core()->public->sticky_footer ) && count( WCCT_Core()->public->sticky_footer ) > 0 ) {
				$timers = array();
				foreach ( WCCT_Core()->public->sticky_footer as $key => $timer ) {
					array_push( $timers, sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $key ), self::get_the_title( $key ), $key ) );
				}
				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Sticky Footer : Yes (%s)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), implode( ', ', $timers ) ) );
			} else {
				WCCT_Core()->appearance->add_header_info( __( 'Sticky Footer : No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}
			// sticky footer

			if ( isset( $getdata['custom_text'] ) && ! empty( $getdata['custom_text'] ) ) {

				$timers = array();
				foreach ( $getdata['custom_text'] as $key => $timer ) {
					array_push( $timers, sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $key ), self::get_the_title( $key ), $key ) );
				}
				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Custom Text: Yes (%s)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), implode( ', ', $timers ) ) );
			} else {
				WCCT_Core()->appearance->add_header_info( __( 'Custom Text: No', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}

			if ( isset( $getdata['events'] ) && ! empty( $getdata['events'] ) ) {

				$timers = array();
				foreach ( $getdata['events'] as $key => $camp ) {
					array_push( $timers, sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $camp ), self::get_the_title( $camp ), $camp ) );
				}
				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Events:  %s', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), implode( ', ', $timers ) ) );
			} else {
				WCCT_Core()->appearance->add_header_info( __( 'Events:  None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}

			if ( isset( $getdata['during_campaign'] ) && ! empty( $getdata['during_campaign'] ) ) {

				$action_string = '';
				$stock         = false;

				if ( isset( $getdata['during_campaign']['stock'] ) && $getdata['during_campaign']['stock'] === 'in-stock' ) {
					$action_string .= 'InStock';
					$stock         = true;
				}

				if ( isset( $getdata['during_campaign']['stock'] ) && $getdata['during_campaign']['stock'] === 'out-of-stock' ) {
					$action_string .= 'OutofStock';
					$stock         = true;
				}

				if ( isset( $getdata['during_campaign']['add_to_cart'] ) && $getdata['during_campaign']['add_to_cart'] === 'hide' ) {
					if ( $stock ) {
						$action_string .= ' & ';
					}
					$action_string .= 'Add to Cart: Hide';
				}

				if ( isset( $getdata['during_campaign']['add_to_cart'] ) && $getdata['during_campaign']['add_to_cart'] === 'show' ) {
					if ( $stock ) {
						$action_string .= ' & ';
					}
					$action_string .= 'OutofStock';
				}
				$camplink = sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $getdata['during_campaign']['campID'] ), self::get_the_title( $getdata['during_campaign']['campID'] ), $getdata['during_campaign']['campID'] );

				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Actions (During Campaign):  %1$s -  %2$s', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), $action_string, $camplink ) );
			} else {
				WCCT_Core()->appearance->add_header_info( __( 'Actions (During Campaign): None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}

			if ( isset( $getdata['after_campaign'] ) && ! empty( $getdata['after_campaign'] ) ) {

				$action_string = '';

				$stock = false;
				if ( isset( $getdata['after_campaign']['stock'] ) && $getdata['after_campaign']['stock'] == 'in-stock' ) {
					$action_string .= 'InStock';
					$stock         = true;
				}

				if ( isset( $getdata['after_campaign']['stock'] ) && $getdata['after_campaign']['stock'] == 'out-of-stock' ) {
					$action_string .= 'OutofStock';
					$stock         = true;
				}

				if ( isset( $getdata['after_campaign']['add_to_cart'] ) && $getdata['after_campaign']['add_to_cart'] == 'hide' ) {
					if ( $stock ) {
						$action_string .= ' & ';
					}
					$action_string .= 'Add to Cart: Hide';
				}

				if ( isset( $getdata['after_campaign']['add_to_cart'] ) && $getdata['after_campaign']['add_to_cart'] == 'show' ) {
					if ( $stock ) {
						$action_string .= ' & ';
					}
					$action_string .= 'Add to Cart: Show';
				}
				$camplink = sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $getdata['after_campaign']['campaignID'] ), self::get_the_title( $getdata['after_campaign']['campaignID'] ), $getdata['after_campaign']['campaignID'] );

				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Actions (After Campaign):  %1$s -  %2$s', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), $action_string, $camplink ) );
			} else {
				WCCT_Core()->appearance->add_header_info( __( 'Actions (After Campaign): None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );
			}

			if ( ! empty( $getdata['add_to_cart_text'] ) ) {
				$get_id   = key( $getdata['add_to_cart_text'] );
				$camplink = sprintf( '<a target="_blank" href="%s" title="%s">%s</a>', self::get_edit_post_link( $get_id ), self::get_the_title( $get_id ), $get_id );

				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Custom Add to Cart Text: Yes (%s)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), $camplink ) );

			} else {
				WCCT_Core()->appearance->add_header_info( __( 'Custom Add to Cart Text: None', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );

			}

			if ( ! isset( $_GET['wcct_positions'] ) || $_GET['wcct_positions'] !== 'yes' ) {
				$page_link = add_query_arg(
					array(
						'wcct_positions' => 'yes',
					), get_permalink( self::$wcct_post->ID )
				);
				$camplink  = sprintf( '<a target="_blank" href="%s" title="%s">Click here to Troubleshoot Positions</a>', $page_link, self::get_the_title( self::$wcct_post->ID ) );

				WCCT_Core()->appearance->add_header_info( sprintf( __( 'Unable to see Finale elements? %s', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ), $camplink ) );
			}

			self::$info_generated = true;
		}
	}

	public static function get_the_title( $post = 0 ) {
		$post  = self::get_post_data( $post );
		$title = isset( $post->post_title ) ? $post->post_title : '';

		if ( ! is_admin() ) {
			if ( ! empty( $post->post_password ) ) {
				$protected_title_format = __( 'Protected: %s' );
				$title                  = sprintf( $protected_title_format, $title );
			} elseif ( isset( $post->post_status ) && 'private' == $post->post_status ) {
				$private_title_format = __( 'Private: %s' );
				$title                = sprintf( $private_title_format, $title );
			}
		}

		return $title;
	}

	public static function add_license_info( $localized_data ) {
		$license_state = 'Invalid';
		$support_ins   = WCCT_Core()->xl_support;
		$state         = get_option( $support_ins->edd_slugify_module_name( $support_ins->full_name ) . '_license_active', 'invalid' );

		if ( 'valid' == $state ) {
			$license_state = 'Valid';
		}

		$localized_data['l'] = $license_state;

		return $localized_data;
	}

	public static function process_reset_state( $id ) {
		self::wcct_set_campaign_status( $id );
	}

	public static function wcct_set_campaign_status( $item_id ) {
		$output = '';

		$data = WCCT_Common::get_item_data( $item_id );

		$timings = WCCT_Common::start_end_timestamp( $data );
		$output  = apply_filters( 'wcct_set_custom_campaign_status', $output, $item_id, $data, $timings );
		if ( ! empty( $output ) ) {
			return $output;
		}
		extract( $timings );
		$slug_timing = 'deactivated';
		if ( $end_date_timestamp > 0 ) {
			if ( $todayDate >= $start_date_timestamp && $todayDate < $end_date_timestamp ) {
				$output      = __( 'Running', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
				$slug_timing = 'running';
			} elseif ( $first_occur && $todayDate <= $rec_intial_end_time ) {
				$output      = __( 'Paused', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
				$slug_timing = 'paused';
			} elseif ( $todayDate > $end_date_timestamp ) {
				$output      = __( 'Finished', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
				$slug_timing = 'finished';
			} elseif ( $start_date_timestamp > $todayDate ) {
				$output      = __( 'Scheduled', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
				$slug_timing = 'schedule';
			}
			update_post_meta( $item_id, '_wcct_current_status_timing', $slug_timing );
		}

		return $output;
	}

	public static function wcct_refresh_timer_ajax_callback() {

		if ( ! isset( $_GET['wcct_action'] ) || 'wcct_refreshed_times' !== $_GET['wcct_action'] ) {
			return;
		}

		if ( null === filter_input( INPUT_GET, 'endDate' ) ) {
			wp_send_json( array() );
		}
		/**
		 * Comparing end timestamp with the current timestamp
		 * and getting difference
		 */
		$date_obj            = new DateTime();
		$current_Date_object = clone $date_obj;
		$current_Date_object->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
		$date_obj->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
		$date_obj->setTimestamp( $_GET['endDate'] );

		$interval = $current_Date_object->diff( $date_obj );
		$x        = $interval->format( '%R' );
		$is_left  = $x;

		if ( '+' === $is_left ) {
			$total_seconds_left = 0;
			$total_seconds_left = $total_seconds_left + ( YEAR_IN_SECONDS * $interval->y );
			$total_seconds_left = $total_seconds_left + ( MONTH_IN_SECONDS * $interval->m );
			$total_seconds_left = $total_seconds_left + ( DAY_IN_SECONDS * $interval->d );
			$total_seconds_left = $total_seconds_left + ( HOUR_IN_SECONDS * $interval->h );
			$total_seconds_left = $total_seconds_left + ( MINUTE_IN_SECONDS * $interval->i );
			$total_seconds_left = $total_seconds_left + $interval->s;
		} else {
			$total_seconds_left = 0;
		}

		wp_send_json(
			array(
				'diff' => $total_seconds_left,
				'id'   => filter_input( INPUT_GET, 'campID' ),
			)
		);

	}

	public static function wcct_maybe_clear_cache() {

	    /**
         * Clear wordpress cache
         */
	    if( function_exists('wp_cache_flush') ){
	        wp_cache_flush();
	    }

		/**
		 * Checking if wp fastest cache installed
		 * Clear cache of wp fastest cache
		 */
		if ( class_exists( 'WpFastestCache' ) ) {
			global $wp_fastest_cache;
			if ( method_exists( $wp_fastest_cache, 'deleteCache' ) ) {
				$wp_fastest_cache->deleteCache();
			}
		}

		/**
		 * Checking if wp Autoptimize installed
		 * Clear cache of Autoptimize
		 */
		if ( class_exists( 'autoptimizeCache' ) ) {
			autoptimizeCache::clearall();
		}

		/**
         * Checking if W3Total Cache plugin activated.
         * Clear cache of W3Total Cache plugin
         */
		if( function_exists( 'w3tc_flush_all' ) ) {
		    w3tc_flush_all();
		}

		/**
		 * Checking if wp rocket caching add on installed
		 * Cleaning the url for current opened URL
		 *
		 */
		if ( function_exists( 'rocket_clean_home' ) ) {
			$referer = wp_get_referer();


			if ( 0 !== strpos( $referer, 'http' ) ) {
			    $rocket_pass_url = get_rocket_parse_url( untrailingslashit( home_url() ) );

			    if(is_array($rocket_pass_url) && 0 < count($rocket_pass_url)){
			        list( $host, $path, $scheme, $query ) = $rocket_pass_url;
			        $referer = $scheme . '://' . $host . $referer;
			    }

			}

			if ( home_url( '/' ) === $referer ) {
				rocket_clean_home();
			} else {
				rocket_clean_files( $referer );
			}
		}
	}

	public static function maybe_add_rule_for_wc_memberships( $rules ) {
		if ( class_exists( 'WC_Memberships' ) ) {
			$rules['Membership']['users_wc_membership'] = __( 'User\'s Membership', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
		}

		return $rules;
	}

	public static function String2Hex( $string ) {
		$hex = '';
		for ( $i = 0; $i < strlen( $string ); $i ++ ) {
			$hex .= dechex( ord( $string[ $i ] ) );
		}

		return $hex;
	}

	public static function Hex2String( $hex ) {
		$string = '';
		for ( $i = 0; $i < strlen( $hex ) - 1; $i += 2 ) {
			$string .= chr( hexdec( $hex[ $i ] . $hex[ $i + 1 ] ) );
		}

		return $string;
	}

	public static function add_excluded_rules( $content_id, $productID ) {
		if ( 0 != $productID ) {
			$default_rules        = self::default_rule_types( '' );
			self::$excluded_rules = $default_rules[ self::$rule_page_label ];
		}
	}

	/**
	 * Hooked into wcct_get_rule_types to get the default list of rule types.
	 *
	 * @param array $types Current list, if any, of rule types.
	 *
	 * @return array the list of rule types.
	 */
	public static function default_rule_types( $types ) {
		self::$rule_product_label = __( 'Product (suitable when campaign has discounts, inventory etc)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
		self::$rule_page_label    = __( 'Page (these rules would work for sticky header or footer only)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
		$types                    = array(
			__( 'General', 'finale-woocommerce-sales-countdown-timer-discount-plugin' )    => array(
                'general_always' => __( 'Always', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
            ),
            self::$rule_product_label                                                      => array(
                'general_all_products' => __( 'All Products', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'product_select'       => __( 'Products', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'product_type'         => __( 'Product Type', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'product_category'     => __( 'Product Category', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'product_tags'         => __( 'Product Tags', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'product_price'        => __( 'Product Price', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'sale_status'          => __( 'Sale Status', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'stock_status'         => __( 'Stock Status', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'stock_level'          => __( 'Stock Quantity', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
            ),
            self::$rule_page_label                                                         => array(
                'general_all_pages'        => __( 'All Site Pages', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'post_type'                => __( 'Post Type', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'single_page'              => __( 'Specific Page(s)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'general_front_page'       => __( 'Home Page (Front Page)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'general_all_product_cats' => __( 'All Product Category Pages', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'single_product_cat_tax'   => __( 'Specific Product Category Page(s)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'general_all_product_tags' => __( 'All Product Tags Pages', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'single_product_tags_tax'  => __( 'Specific Product Tags Page(s)', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
            ),
            __( 'Geography', 'finale-woocommerce-sales-countdown-timer-discount-plugin' )  => array(
                'geo_country_code' => __( 'Country', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
            ),
            __( 'Date/Time', 'finale-woocommerce-sales-countdown-timer-discount-plugin' )  => array(
                'day'  => __( 'Day', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'date' => __( 'Date', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'time' => __( 'Time', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
            ),
            __( 'Membership', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) => array(
                'users_user'  => __( 'User', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'users_guest' => __( 'Guest Users', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'users_role'  => __( 'Role', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
            ),
		);

		if ( defined('LEARNDASH_VERSION') ) {
            $types[__( 'Learndash', 'finale-woocommerce-sales-countdown-timer-discount-plugin' )] = array(
                'learndash_single_course' => __( 'Course', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'learndash_single_lesson' => __( 'Lesson', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
                'learndash_single_topic'  => __( 'Topic', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
            );
		}

		if ( class_exists( 'WFACP_Core' ) ) {

			$types[ __( 'AeroCheckout', 'woofunnels-upstroke-one-click-upsell' ) ] = [
				'wfacp_page' => __( 'Aero Checkout Pages', 'woofunnels-upstroke-one-click-upsell' ),

			];
		}

		return apply_filters( 'wcct_rules_options', $types );
	}

	public static function remove_excluded_rules( $content_id, $productID ) {
		if ( 0 != $productID ) {
			self::$excluded_rules = array();
		}
	}

	public static function wcct_rule_stock_status( $options ) {
		if ( WCCT_Compatibility::is_wc_version_gt_eq( '3.0' ) ) {
			$options['2'] = __( 'On backorder', 'woocommerce' );
		}

		return $options;
	}

	/**
	 * Reversing Finale inventory when order status: pending, failed or cancelled
	 *
	 * @param WC_Order $order_id
	 */
	public static function wcct_goaldeal_sold_backup_callback( $order_id ) {
		if ( empty( $order_id ) ) {
			return;
		}

		$order        = wc_get_order( $order_id );
		$order_status = $order->get_status();

		if ( in_array(
			$order_status, apply_filters(
				'wcct_update_finale_inventory_order_status', array(
					'pending',
					'cancelled',
					'failed',
				)
			)
		) ) {
			$order_backup_data = get_post_meta( $order_id, '_wcct_goaldeal_sold_backup', true );
			if ( is_array( $order_backup_data ) && count( $order_backup_data ) > 0 ) {
				foreach ( $order_backup_data as $product_id => $product_data ) {
					if ( is_array( $product_data ) && count( $product_data ) > 0 ) {
						foreach ( $product_data as $meta_key => $value ) {
							$current = get_post_meta( (int) $product_id, $meta_key, true );
							$mod     = (int) $current - (int) $value;

							if ( 0 === $mod ) {
								delete_post_meta( (int) $product_id, $meta_key );
							} else {
								update_post_meta( (int) $product_id, $meta_key, $mod );
								wcct_force_log( "backup: key => {$meta_key} , product id => {$product_id} and updated value " . $mod, 'force1.txt' );
							}

							unset( $current );
							unset( $mod );
						}
					}
				}
			}

			delete_post_meta( $order_id, '_wcct_goaldeal_sold_backup' );
		}

		return;
	}

	public static function get_filter_args() {
		$args = array();

		$args = apply_filters( 'wcct_default_filter_args_campaigns_admin', $args );
		if ( null !== filter_input( INPUT_GET, 'wcct_sort' ) ) {

			$orderby = filter_input( INPUT_GET, 'wcct_sort' );
			switch ( $orderby ) {
				case 'date':
					$args['order'] = 'DESC';
					break;
				default:
					$args['order'] = 'ASC';

			}
			$args['orderby'] = filter_input( INPUT_GET, 'wcct_sort' );
		}

		return $args;
	}

	/**
	 * Restoring stock on cancellation of order
	 *
	 * @param $order_id
	 */
	public static function wcct_restore_order_stock( $order_id ) {
		$order = new WC_Order( $order_id );

		if ( ! get_option( 'woocommerce_manage_stock' ) == 'yes' && ! sizeof( $order->get_items() ) > 0 ) {
			return;
		}

		$order_sold_meta = get_post_meta( $order_id, '_wcct_goaldeal_sold_backup', true );

		if ( is_array( $order_sold_meta ) && count( $order_sold_meta ) > 0 && isset( $order_sold_meta['sold'] ) && 'y' == $order_sold_meta['sold'] ) {

		    if( isset( $order_sold_meta['stock_type'] ) && 'custom' != $order_sold_meta['stock_type'] ) {
		        return;
		    }

			/* Reducing sold unit */
			if ( is_array( $order_sold_meta ) && count( $order_sold_meta ) > 0 ) {
				foreach ( $order_sold_meta as $product_id => $product_data ) {
					if ( is_array( $product_data ) && count( $product_data ) > 0 ) {

					    if( isset( $product_data['stock_type'] ) && 'custom' != $product_data['stock_type'] ) {
					        continue;
					    }

						foreach ( $product_data as $meta_key => $value ) {
							$current = get_post_meta( (int) $product_id, $meta_key, true );
							$mod     = (int) $current - (int) $value;

							if ( 0 === $mod ) {
								delete_post_meta( (int) $product_id, $meta_key );
							} else {
								update_post_meta( (int) $product_id, $meta_key, $mod );
								wcct_force_log( "backup: key => {$meta_key} , product id => {$product_id} and updated value " . $mod, 'force1.txt' );
							}

							unset( $current );
							unset( $mod );
						}
					}
				}
			}
		}

	}

	/**
	 * Delete post data from transient
	 *
	 * @param string $item_id
	 */
	public static function delete_post_data( $item_id = '' ) {
		$xl_transient_obj = XL_Transient::get_instance();

		if ( empty( $item_id ) ) {
			return;
		}

		$cache_key = 'post_data_' . $item_id;

		$xl_transient_obj->delete_transient( $cache_key, 'xl-wp-posts' );
	}

	/**
	 * @param string $item_id
	 *
	 * @return bool|int|void
	 */
	public static function get_post_parent_id( $item_id = '' ) {
		if ( empty( $item_id ) ) {
			return false;
		}

		$post = self::get_post_data( $item_id );
		if ( ! $post || is_wp_error( $post ) ) {
			return false;
		}

		return (int) $post->post_parent;
	}

	/**
	 * Schedule cron to clear post meta table
	 */
	public static function wcct_clear_post_meta_keys_for_expired_campaigns() {
		if ( ! wp_next_scheduled( 'wcct_clear_goaldeal_stock_meta_keys' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'wcct_clear_goaldeal_stock_meta_keys' );
		}
		if ( ! wp_next_scheduled( 'wcct_clear_inventory_range_meta_keys' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'wcct_clear_inventory_range_meta_keys' );
		}
	}

	/**
	 * Delete goaldeal stock meta keys
	 */
	public static function wcct_clear_goaldeal_stock_meta_keys() {
		global $wpdb;

		$max_limit = apply_filters( 'wcct_delete_expired_post_meta_keys_limit', 1000 );
		$stocks    = $wpdb->get_results( $wpdb->prepare( "
                        SELECT meta_key, meta_id
                        FROM {$wpdb->prefix}postmeta
                        WHERE `meta_key` LIKE '%_wcct_goaldeal_%'
                        AND `meta_key` NOT LIKE '%_wcct_goaldeal_sold%'
                        ORDER BY meta_id ASC
                        LIMIT %d, %d
                        ", 0, $max_limit ) );

		if ( empty( $stocks ) ) {
			return;
		}

		$time = time();
		$ids  = [];
		foreach ( $stocks as $stock ) {
			$arr = explode( '_', $stock->meta_key );
			$arr = array_slice( $arr, - 3, 3 );

			if ( ! is_numeric( $arr[0] ) || ! is_numeric( $arr[1] ) || ! is_numeric( $arr[2] ) ) {
				continue;
			}

			if ( $time > $arr[2] ) {
				$ids[] = $stock->meta_id;
			}
		}

		if ( count( $ids ) > 0 ) {
			$id_count   = count( $ids );
			$delete_ids = array_fill( 0, $id_count, '%d' );
			$delete_ids = implode( ', ', $delete_ids );

			$wpdb->query( $wpdb->prepare( "
                        DELETE 
                        FROM {$wpdb->prefix}postmeta 
                        WHERE `meta_id` IN ($delete_ids)
                        ", $ids ) );
		}
	}

	/**
	 * Delete inventory range meta keys
	 */
	public static function wcct_clear_inventory_range_meta_keys() {
		global $wpdb;

		$max_limit = apply_filters( 'wcct_delete_expired_post_meta_keys_limit', 1000 );
		$ranges    = $wpdb->get_results( $wpdb->prepare( "
                        SELECT meta_key, meta_id
                        FROM {$wpdb->prefix}postmeta
                        WHERE `meta_key` LIKE '%_wcct_inventory_range_%'
                        ORDER BY meta_id ASC
                        LIMIT %d, %d
                        ", 0, $max_limit ) );

		if ( empty( $ranges ) ) {
			return;
		}

		$time = time();
		$ids  = [];
		foreach ( $ranges as $range ) {
			$arr = explode( '_', $range->meta_key );
			$to  = end( $arr );

			if ( $time > $to ) {
				/**
				 * Using this function because it will also delete the cache and there are do_actions on it as well
				 * Inside loop it makes sure that all meta keys are deleted
				 */
				$ids[] = $range->meta_id;
			}
		}

		if ( count( $ids ) > 0 ) {
			$id_count   = count( $ids );
			$delete_ids = array_fill( 0, $id_count, '%d' );
			$delete_ids = implode( ', ', $delete_ids );

			$wpdb->query( $wpdb->prepare( "
                        DELETE 
                        FROM {$wpdb->prefix}postmeta 
                        WHERE `meta_id` IN ($delete_ids)
                        ", $ids ) );
		}
	}

	public static function run_cron_fallback() {
		$time_key = 'wcct_heartbeat_run';

		if ( true === apply_filters( 'wcct_heartbeat_tick_disable', false ) ) {
			return;
		}

		$save_time    = get_option( $time_key, time() );
		$current_time = time();

		if ( $current_time < $save_time ) {
			return;
		}

		$url  = home_url() . '/?rest_route=/finale/v1/delete-finished-keys';
		$args = [
			'method'    => 'GET',
			'body'      => array(),
			'timeout'   => 0.01,
			'sslverify' => false,
		];

		wp_remote_post( $url, $args );
		update_option( $time_key, ( $current_time + ( 5 * MINUTE_IN_SECONDS ) ) );
	}

	public static function add_plugin_endpoint() {
		register_rest_route( 'finale/v1', '/delete-finished-keys', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array( __CLASS__, 'run_delete_finished_keys_cron_callbacks' ),
		) );
	}

	public static function run_delete_finished_keys_cron_callbacks( WP_REST_Request $request ) {
		self::wcct_clear_goaldeal_stock_meta_keys();
		self::wcct_clear_inventory_range_meta_keys();
	}


}
