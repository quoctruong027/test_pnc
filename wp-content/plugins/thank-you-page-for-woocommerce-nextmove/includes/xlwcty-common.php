<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class XLWCTY_Common
 * Handles Common Functions For admins as well as front end interface
 * @package NextMove
 * @author XlPlugins
 */
class XLWCTY_Common {

	public static $xlwcty_post;
	public static $xlwcty_query;
	public static $is_front_page = false;
	public static $is_executing_rule = false;
	public static $is_force_debug = false;
	public static $info_generated = false;
	protected static $default;
	public static $custom_ty_pages = null;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_status' ), 5 );
		/** Necessary Hooks For Rules functionality */
		add_action( 'init', array( __CLASS__, 'register_wcthankyou_post_type' ) );
		add_action( 'init', array( __CLASS__, 'load_rules_classes' ) );
		add_filter( 'xlwcty_xlwcty_rule_get_rule_types', array( __CLASS__, 'default_rule_types' ), 1 );
		add_action( 'wp_ajax_xlwcty_change_rule_type', array( __CLASS__, 'ajax_render_rule_choice' ) );
		add_action( 'save_post', array( __CLASS__, 'save_data' ), 10, 2 );

		/**
		 * Checking xlwcty query params
		 */
		add_action( 'init', array( __CLASS__, 'check_query_params' ), 1 );

		/**
		 * Loading XL core
		 */
		add_action( 'plugins_loaded', array( __CLASS__, 'xlwcty_xl_init' ), 99 );

		/**
		 * Containing current Page State using wp hook
		 * using priority 0 to make sure it is not changed by that moment
		 */
		add_action( 'wp', array( __CLASS__, 'xlwcty_contain_current_query' ), 1 );

		// ajax
		add_action( 'wp_ajax_xlwcty_close_sticky_bar', array( __CLASS__, 'xlwcty_close_sticky_bar' ) );
		add_action( 'wp_ajax_nopriv_xlwcty_close_sticky_bar', array( __CLASS__, 'xlwcty_close_sticky_bar' ) );
		add_action( 'wp_ajax_get_coupons_cmb2', array( __CLASS__, 'get_coupons_cmb2' ) );
		add_action( 'wp_ajax_nopriv_get_coupons_cmb2', array( __CLASS__, 'get_coupons_cmb2' ) );
		add_action( 'wp_ajax_get_product_cmb2', array( __CLASS__, 'get_product_cmb2' ) );
		add_action( 'wp_ajax_nopriv_get_product_cmb2', array( __CLASS__, 'get_product_cmb2' ) );
		add_action( 'wp_ajax_xlwcty_get_orders_cmb2', array( __CLASS__, 'xlwcty_get_orders_cmb2' ) );
		add_action( 'wp_ajax_xlwcty_get_pages_for_order', array( __CLASS__, 'xlwcty_get_pages_for_order' ) );
		add_action( 'wp_ajax_nopriv_xlwcty_get_pages_for_order', array( __CLASS__, 'xlwcty_get_pages_for_order' ) );
		/**
		 * Adding shortcode cb, responsible for front end loading
		 */
		add_shortcode( 'xlwcty_load', array( __CLASS__, 'maybe_render_elements' ) );
		/**
		 *
		 */
		add_action( 'xlwcty_maybe_schedule_check_license', array( __CLASS__, 'check_license_state' ) );
		add_action( 'xlwcty_installed', 'flush_rewrite_rules' );
		add_action( 'xlwcty_loaded', array( __CLASS__, 'setup_global_options' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'toolbar_link_to_xlplugins' ), 999 );

		/*
		 * Updates user order count to the user meta just after user logged in
		 */
		add_action( 'wp_login', array( __CLASS__, 'update_user_order_count' ), 10, 2 );

		add_filter( 'xlwcty_localize_js_data', array( __CLASS__, 'add_license_info' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_duplicate_post' ) );

		add_action( 'wp_ajax_xlwcty_quick_view', array( __CLASS__, 'handle_quick_view' ) );
		add_action( 'xlwcty_options_page_right_content', array( __CLASS__, 'render_quick_view_metabox' ), 9 );

		add_filter( 'xlwcty_woocommerce_get_checkout_order_received_url', array( __CLASS__, 'xlwcty_alter_thank_you_url' ), 20, 2 );

		/** Set cookie for recently viewed products */
		add_action( 'wp_footer', array( __CLASS__, 'xlwcty_woocommerce_recently_viewed_product' ) );

		/** before delete post check for thankyou page than delete custom pages from options table */
		add_action( 'before_delete_post', array( __CLASS__, 'xlwcty_delete_custom_thankyou_options' ) );
	}

	public static function xlwcty_get_date_format() {
		$date_format = get_option( 'date_format', true );
		$date_format = $date_format ? $date_format : 'M d, Y';

		return $date_format;
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

	public static function register_wcthankyou_post_type() {
		$menu_name = _x( XLWCTY_FULL_NAME, 'Admin menu name', 'thank-you-page-for-woocommerce-nextmove' );
		if ( filter_input( INPUT_POST, 'woocommerce_checkout_order_received_endpoint' ) !== null ) {
			$rewrite_slug = filter_input( INPUT_POST, 'woocommerce_checkout_order_received_endpoint' );
		} else {
			$rewrite_slug = get_option( 'woocommerce_checkout_order_received_endpoint', 'order-received' );
		}

		register_post_type( self::get_thank_you_page_post_type_slug(), apply_filters( 'xlwcty_post_type_args', array(
			'labels'               => array(
				'name'               => __( 'Thank You Page', 'thank-you-page-for-woocommerce-nextmove' ),
				'singular_name'      => __( 'Thank You Page', 'thank-you-page-for-woocommerce-nextmove' ),
				'add_new'            => __( 'Add New', 'thank-you-page-for-woocommerce-nextmove' ),
				'add_new_item'       => __( 'Add New', 'thank-you-page-for-woocommerce-nextmove' ),
				'edit'               => __( 'Edit', 'thank-you-page-for-woocommerce-nextmove' ),
				'edit_item'          => __( 'Edit', 'thank-you-page-for-woocommerce-nextmove' ),
				'new_item'           => __( 'New', 'thank-you-page-for-woocommerce-nextmove' ),
				'view'               => __( 'View', 'thank-you-page-for-woocommerce-nextmove' ),
				'view_item'          => __( 'View', 'thank-you-page-for-woocommerce-nextmove' ),
				'search_items'       => __( 'Search', 'thank-you-page-for-woocommerce-nextmove' ),
				'not_found'          => __( 'No Thank You Page', 'thank-you-page-for-woocommerce-nextmove' ),
				'not_found_in_trash' => __( 'No Thank You Page found in trash', 'thank-you-page-for-woocommerce-nextmove' ),
				'parent'             => __( 'Parent', 'thank-you-page-for-woocommerce-nextmove' ),
				'menu_name'          => $menu_name,
			),
			'public'               => true,
			'show_ui'              => true,
			'capability_type'      => 'product',
			'map_meta_cap'         => true,
			'publicly_queryable'   => true,
			'exclude_from_search'  => true,
			'show_in_menu'         => false,
			'hierarchical'         => false,
			'show_in_nav_menus'    => false,
			'rewrite'              => array(
				'slug'       => $rewrite_slug,
				'with_front' => false,
			),
			'query_var'            => true,
			'supports'             => array(
				'title',
				'editor',
			),
			'has_archive'          => false,
			'register_meta_box_cb' => array( 'xlwcty_Admin', 'add_metaboxes' ),
		) ) );
	}

	public static function get_thank_you_page_post_type_slug() {
		return 'xlwcty_thankyou';
	}

	public static function load_rules_classes() {
		//Include the compatibility class
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/class-xlwcty-compatibility.php';
		//Include our default rule classes
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/base.php';
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/general.php';
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/page.php';
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/products.php';
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/stock.php';
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/sales.php';
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/users.php';
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/date-time.php';
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/geo.php';
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/cart.php';
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/order.php';
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/customer.php';
		include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/rules/woofunnels.php';

		if ( is_admin() || defined( 'DOING_AJAX' ) ) {
			//Include the admin interface builder
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/class-xlwcty-input-builder.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/html-always.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/text.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/select.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/product-select.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/chosen-select.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/cart-category-select.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/cart-product-select.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/html-rule-is-renewal.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/html-rule-is-first-order.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/html-rule-is-guest.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/date.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/time.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/html-rule-is-upgrade.php';
			include plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'rules/inputs/html-rule-is-downgrade.php';
		}
	}

	/**
	 * Creates an instance of an input object
	 *
	 * @param type $input_type The slug of the input type to load
	 *
	 * @return type An instance of an xlwcty_Input object type
	 * @global type $woocommerce_xlwcty_rule_inputs
	 *
	 */
	public static function woocommerce_xlwcty_rule_get_input_object( $input_type ) {
		global $woocommerce_xlwcty_rule_inputs;
		if ( isset( $woocommerce_xlwcty_rule_inputs[ $input_type ] ) ) {
			return $woocommerce_xlwcty_rule_inputs[ $input_type ];
		}
		$class = 'xlwcty_Input_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $input_type ) ) );
		if ( class_exists( $class ) ) {
			$woocommerce_xlwcty_rule_inputs[ $input_type ] = new $class;
		} else {
			$woocommerce_xlwcty_rule_inputs[ $input_type ] = apply_filters( 'woocommerce_xlwcty_rule_get_input_object', $input_type );
		}

		return $woocommerce_xlwcty_rule_inputs[ $input_type ];
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
		$is_ajax  = false;
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'xlwcty_change_rule_type' ) {
			$is_ajax = true;
		}
		if ( $is_ajax ) {
			if ( ! check_ajax_referer( 'xlwctyaction-admin', 'security' ) ) {
				die();
			}
			$options = array_merge( $defaults, $_POST );
		} else {
			$options = array_merge( $defaults, $options );
		}
		$rule_object = self::woocommerce_xlwcty_rule_get_rule_object( $options['rule_type'] );
		if ( ! empty( $rule_object ) ) {
			$values               = $rule_object->get_possibile_rule_values();
			$operators            = $rule_object->get_possibile_rule_operators();
			$condition_input_type = $rule_object->get_condition_input_type();
			// create operators field
			$operator_args = array(
				'input'   => 'select',
				'name'    => 'xlwcty_rule[' . $options['group_id'] . '][' . $options['rule_id'] . '][operator]',
				'choices' => $operators,
			);
			echo '<td class="operator">';
			if ( ! empty( $operators ) ) {
				xlwcty_Input_Builder::create_input_field( $operator_args, $options['operator'] );
			} else {
				echo '<input type="hidden" name="' . $operator_args['name'] . '" value="==" />';
			}
			echo '</td>';
			// create values field
			$value_args = array(
				'input'   => $condition_input_type,
				'name'    => 'xlwcty_rule[' . $options['group_id'] . '][' . $options['rule_id'] . '][condition]',
				'choices' => $values,
			);
			echo '<td class="condition">';
			xlwcty_Input_Builder::create_input_field( $value_args, $options['condition'] );
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
	 * @param type $rule_type The slug of the rule type to load.
	 *
	 * @return xlwcty_Rule_Base or superclass of xlwcty_Rule_Base
	 * @global array $woocommerce_xlwcty_rule_rules
	 *
	 */
	public static function woocommerce_xlwcty_rule_get_rule_object( $rule_type ) {
		global $woocommerce_xlwcty_rule_rules;
		if ( isset( $woocommerce_xlwcty_rule_rules[ $rule_type ] ) ) {
			return $woocommerce_xlwcty_rule_rules[ $rule_type ];
		}
		$class = 'xlwcty_Rule_' . $rule_type;
		if ( class_exists( $class ) ) {
			$woocommerce_xlwcty_rule_rules[ $rule_type ] = new $class;

			return $woocommerce_xlwcty_rule_rules[ $rule_type ];
		} else {
			return null;
		}
	}

	public static function get_coupons_cmb2() {
		$array = array();
		if ( isset( $_POST['term'] ) && $_POST['term'] !== '' ) {
			$args               = array(
				'post_type'     => 'shop_coupon',
				'post_per_page' => 2,
				'paged'         => 1,
				's'             => $_POST['term'],
			);
			$args['meta_query'] = array(
				array(
					'key'     => 'is_xlwcty_coupon',
					'compare' => 'NOT EXISTS',
				),
			);
			$posts              = get_posts( $args );
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
		if ( ! is_admin() || ! isset( $_GET['page'] ) || $_GET['page'] !== 'xlwcty_builder' ) {
			return;
		}
		$args               = array(
			'post_type' => 'shop_coupon',
			'showposts' => 3,
			'paged'     => 1,
		);
		$args['meta_query'] = array(
			array(
				'key'     => 'is_xlwcty_coupon',
				'compare' => 'NOT EXISTS',
			),
		);
		$posts              = get_posts( $args );
		$array              = array();
		if ( $is_ajax ) {
			$array[] = array(
				'value' => '',
				'text'  => __( 'Choose a Coupon', 'thank-you-page-for-woocommerce-nextmove' ),
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

	public static function get_product_cmb2() {
		$array = array();
		if ( isset( $_POST['term'] ) && $_POST['term'] !== '' ) {
			$args  = array(
				'post_type'     => 'product',
				'post_per_page' => 20,
				'paged'         => 1,
				's'             => $_POST['term'],
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

	public static function xlwcty_get_orders_cmb2() {
		global $wpdb;
		$array = array();
		if ( isset( $_POST['term'] ) && $_POST['term'] !== '' ) {
			$order_stasuses = XLWCTY_Core()->data->get_option( 'allowed_order_statuses' );
			$query          = "SELECT *  FROM $wpdb->posts WHERE `ID` LIKE '%" . $_POST['term'] . "%' AND `post_status` IN ('" . implode( '\',\'', $order_stasuses ) . "' AND `post_type` = `shop_order`) LIMIT 0,10";
			$posts          = $wpdb->get_results( $query );
			if ( $posts && is_array( $posts ) && count( $posts ) > 0 ) {
				foreach ( $posts as $post ) :
					setup_postdata( $post );
					$order        = wc_get_order( $post->ID );
					$order_key    = XLWCTY_Compatibility::get_order_data( $order, 'order_key' );
					$order_status = wc_get_order_status_name( $order->get_status() );
					$label        = '#' . XLWCTY_Compatibility::get_order_id( $order ) . ' (' . $order_status . ') ' . XLWCTY_Compatibility::get_order_data( $order, 'billing_email' ) . '';
					$array[]      = array(
						'value' => $order_key . '||' . $post->ID,
						'text'  => $label,
					);
				endforeach;
			}
		}
		wp_send_json( $array );
	}

	public static function get_product( $is_ajax = false ) {
		$args  = array(
			'post_type' => 'product',
			'showposts' => 10,
			'paged'     => 1,
		);
		$posts = get_posts( $args );
		$array = array();
		if ( $is_ajax ) {
			$array[] = array(
				'value' => '',
				'text'  => __( 'Choose a Product', 'thank-you-page-for-woocommerce-nextmove' ),
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

	/**
	 * Called from the metabox_settings.php screen.  Renders the template for a rule group that has already been saved.
	 *
	 * @param array $options The group config options to render the template with.
	 */
	public static function render_rule_choice_template( $options ) {
		// defaults
		$defaults             = array(
			'group_id'  => 0,
			'rule_id'   => 0,
			'rule_type' => null,
			'condition' => null,
			'operator'  => null,
		);
		$options              = array_merge( $defaults, $options );
		$rule_object          = self::woocommerce_xlwcty_rule_get_rule_object( $options['rule_type'] );
		$values               = $rule_object->get_possibile_rule_values();
		$operators            = $rule_object->get_possibile_rule_operators();
		$condition_input_type = $rule_object->get_condition_input_type();
		// create operators field
		$operator_args = array(
			'input'   => 'select',
			'name'    => 'xlwcty_rule[<%= groupId %>][<%= ruleId %>][operator]',
			'choices' => $operators,
		);
		echo '<td class="operator">';
		if ( ! empty( $operators ) ) {
			xlwcty_Input_Builder::create_input_field( $operator_args, $options['operator'] );
		} else {
			echo '<input type="hidden" name="' . $operator_args['name'] . '" value="==" />';
		}
		echo '</td>';
		// create values field
		$value_args = array(
			'input'   => $condition_input_type,
			'name'    => 'xlwcty_rule[<%= groupId %>][<%= ruleId %>][condition]',
			'choices' => $values,
		);
		echo '<td class="condition">';
		xlwcty_Input_Builder::create_input_field( $value_args, $options['condition'] );
		echo '</td>';
	}

	public static function get_thank_you_page_status_select() {
		$triggers            = self::get_thank_you_page_statuses();
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
	public static function get_thank_you_page_statuses() {
		return array(
			'activated'   => array(
				'name'     => __( 'Activated', 'thank-you-page-for-woocommerce-nextmove' ),
				'slug'     => 'activated',
				'position' => 5,
			),
			'deactivated' => array(
				'name'     => __( 'Deactivated', 'thank-you-page-for-woocommerce-nextmove' ),
				'slug'     => 'deactivated',
				'position' => 9,
			),
		);
	}

	public static function match_groups( $content_id, $order_id = 0 ) {
		$display = false;
		if ( $order_id ) {
			$cache_key = 'xlwcty_thankyou_match_groups_' . $content_id . '_' . $order_id;
		} else {
			$cache_key = 'xlwcty_thankyou_match_groups_' . $content_id;
		}
		$results = XLWCTY_Core()->data->get_cache( $cache_key );
		if ( $results === false ) {
			self::$is_executing_rule = true;

			//allowing rules to get manipulated using external logic
			$external_rules = apply_filters( 'xlwcty_before_rules', true, $content_id, $order_id );
			if ( ! $external_rules ) {
				XLWCTY_Core()->data->set_cache( $cache_key, 'no' );
				self::$is_executing_rule = false;

				return false;
			}

			$groups = get_post_meta( $content_id, 'xlwcty_rule', true );
			if ( $groups && is_array( $groups ) && count( $groups ) ) {
				foreach ( $groups as $group_id => $group ) {
					$result = null;
					foreach ( $group as $rule_id => $rule ) {
						$rule_object = self::woocommerce_xlwcty_rule_get_rule_object( $rule['rule_type'] );
						if ( is_object( $rule_object ) ) {
							$match = $rule_object->is_match( $rule, $order_id );
							if ( false === $match ) {
								$result = false;
								break;
							}
							$result = ( null !== $result ? ( $result & $match ) : $match );
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

			$display = apply_filters( 'xlwcty_after_rules', $display, $content_id, $order_id );
			XLWCTY_Core()->data->set_cache( $cache_key, ( $display ) ? 'yes' : 'no' );
		} else {
			$display = ( 'yes' === $results ) ? true : false;
		}
		self::$is_executing_rule = false;

		return $display;
	}

	/**
	 * Hooked into xlwcty_get_rule_types to get the default list of rule types.
	 *
	 * @param array $types Current list, if any, of rule types.
	 *
	 * @return array the list of rule types.
	 */
	public static function default_rule_types( $types ) {
		$types = array(
			__( 'General', 'thank-you-page-for-woocommerce-nextmove' )   => array(
				'general_always' => __( 'Always', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			__( 'Order', 'thank-you-page-for-woocommerce-nextmove' )     => array(
				'order_total'           => __( 'Order Total', 'thank-you-page-for-woocommerce-nextmove' ),
				'order_item'            => __( 'Order Item(s)', 'thank-you-page-for-woocommerce-nextmove' ),
				'order_category'        => __( 'Order Category(s)', 'thank-you-page-for-woocommerce-nextmove' ),
				'order_item_count'      => __( 'Order Item Count', 'thank-you-page-for-woocommerce-nextmove' ),
				'order_item_type'       => __( 'Order Item Type', 'thank-you-page-for-woocommerce-nextmove' ),
				'order_coupons'         => __( 'Order Coupons', 'thank-you-page-for-woocommerce-nextmove' ),
				'order_payment_gateway' => __( 'Order Payment Gateway', 'thank-you-page-for-woocommerce-nextmove' ),
				'order_shipping_method' => __( 'Order Shipping Method', 'thank-you-page-for-woocommerce-nextmove' ),
				'is_first_order'        => __( 'Is First Order', 'thank-you-page-for-woocommerce-nextmove' ),
				'order_is_renewal'      => __( 'Is Order Renewal', 'thank-you-page-for-woocommerce-nextmove' ),
				'order_is_upgrade'      => __( 'Is Order Upgrade', 'thank-you-page-for-woocommerce-nextmove' ),
				'order_is_downgrade'    => __( 'Is Order Downgrade', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			__( 'Customer', 'thank-you-page-for-woocommerce-nextmove' )  => array(
				'customer_user'        => __( 'Customer', 'thank-you-page-for-woocommerce-nextmove' ),
				'customer_role'        => __( 'Customer User Role', 'thank-you-page-for-woocommerce-nextmove' ),
				'customer_order_count' => __( 'Customer Order Count', 'thank-you-page-for-woocommerce-nextmove' ),
				'customer_total_spent' => __( 'Customer Total Spent', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			__( 'Guest', 'thank-you-page-for-woocommerce-nextmove' )     => array(
				'is_guest'          => __( 'Is Customer Guest', 'thank-you-page-for-woocommerce-nextmove' ),
				'guest_order_count' => __( 'Guest Order Count', 'thank-you-page-for-woocommerce-nextmove' ),
				'guest_total_spent' => __( 'Guest Total Spent', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			__( 'Geography', 'thank-you-page-for-woocommerce-nextmove' ) => array(
				'order_shipping_country' => __( 'Order Shipping Country', 'thank-you-page-for-woocommerce-nextmove' ),
				'order_billing_country'  => __( 'Order Billing Country', 'thank-you-page-for-woocommerce-nextmove' ),
			),
			__( 'Date/Time', 'thank-you-page-for-woocommerce-nextmove' ) => array(
				'day'  => __( 'Day', 'thank-you-page-for-woocommerce-nextmove' ),
				'date' => __( 'Date', 'thank-you-page-for-woocommerce-nextmove' ),
				'time' => __( 'Time', 'thank-you-page-for-woocommerce-nextmove' ),
			),
		);

		$woofunnels = array();
		if ( class_exists( 'WFOCU_Common' ) ) {
			$woofunnels['upstroke'] = __( 'Upstroke Funnel', 'thank-you-page-for-woocommerce-nextmove' );
		}
		if ( class_exists( 'WFACP_Core' ) ) {
			$woofunnels['aerocheckout'] = __( 'AeroCheckout Page', 'thank-you-page-for-woocommerce-nextmove' );
		}
		if ( ! empty( $woofunnels ) ) {
			$types[ __( 'WooFunnels', 'thank-you-page-for-woocommerce-nextmove' ) ] = $woofunnels;
		}

		return $types;
	}

	/**
	 * Saves the data for the xlwcty post type.
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
		if ( is_numeric( wp_is_post_revision( $post ) ) ) {
			return;
		}
		if ( is_numeric( wp_is_post_autosave( $post ) ) ) {
			return;
		}
		if ( $post->post_type !== self::get_thank_you_page_post_type_slug() ) {
			return;
		}

		$key = 'xlwcty_instances';
		if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE !== '' ) {
			$key .= '_' . ICL_LANGUAGE_CODE;
		}
		delete_transient( $key );

		if ( isset( $_POST['xlwcty_settings_location'] ) ) {
			$location = explode( ':', $_POST['xlwcty_settings_location'] );
			$settings = array(
				'location' => $location[0],
				'hook'     => $location[1],
			);
			if ( $settings['hook'] === 'custom' ) {
				$settings['custom_hook']     = $_POST['xlwcty_settings_location_custom_hook'];
				$settings['custom_priority'] = $_POST['xlwcty_settings_location_custom_priority'];
			} else {
				$settings['custom_hook']     = '';
				$settings['custom_priority'] = '';
			}
			$settings['type'] = $_POST['xlwcty_settings_type'];
			update_post_meta( $post_id, '_xlwcty_settings', $settings );
		}
		if ( isset( $_POST['xlwcty_rule'] ) ) {
			update_post_meta( $post_id, 'xlwcty_rule', $_POST['xlwcty_rule'] );
		}
	}

	public static function get_post_table_data( $trigger = 'all' ) {
		if ( $trigger === 'all' ) {
			$args = array(
				'post_type'      => self::get_thank_you_page_post_type_slug(),
				'post_status'    => array( 'publish', XLWCTY_SHORT_SLUG . 'disabled' ),
				'posts_per_page' => 20,
				'paged'          => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			);
		} else {
			$meta_q      = array();
			$post_status = '';
			if ( $trigger === 'deactivated' ) {
				$post_status = XLWCTY_SHORT_SLUG . 'disabled';
			}
			$args = array(
				'post_type'      => self::get_thank_you_page_post_type_slug(),
				'post_status'    => array( 'publish', XLWCTY_SHORT_SLUG . 'disabled' ),
				'posts_per_page' => 20,
				'paged'          => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			);

			if ( $post_status !== '' ) {
				$args['post_status'] = $post_status;
			} else {
				$args['post_status'] = 'publish';
			}
			if ( is_array( $meta_q ) && count( $meta_q ) > 0 ) {
				$args['meta_query'] = $meta_q;
			}
		}

		$q           = new WP_Query( $args );
		$found_posts = array();

		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$status           = get_post_status( get_the_ID() );
				$row_actions      = array();
				$deactivation_url = wp_nonce_url( add_query_arg( 'page', 'wc-settings', add_query_arg( 'tab', self::get_wc_settings_tab_slug(), add_query_arg( 'action', 'xlwcty-post-deactivate', add_query_arg( 'postid', get_the_ID(), add_query_arg( 'trigger', $trigger ) ), network_admin_url( 'admin.php' ) ) ) ), 'xlwcty-post-deactivate' );

				$row_actions[] = array(
					'action' => 'manage_components',
					'text'   => __( 'Components', 'thank-you-page-for-woocommerce-nextmove' ),
					'link'   => self::get_builder_link( get_the_ID() ),
					'attrs'  => '',
				);
				$row_actions[] = array(
					'action' => 'edit',
					'text'   => __( 'Edit', 'thank-you-page-for-woocommerce-nextmove' ),
					'link'   => get_edit_post_link( get_the_ID() ),
					'attrs'  => '',
				);

				if ( $status === XLWCTY_SHORT_SLUG . 'disabled' ) {
					$activation_url = wp_nonce_url( add_query_arg( 'page', 'wc-settings', add_query_arg( 'tab', self::get_wc_settings_tab_slug(), add_query_arg( 'action', 'xlwcty-post-activate', add_query_arg( 'postid', get_the_ID(), add_query_arg( 'trigger', $trigger ) ), network_admin_url( 'admin.php' ) ) ) ), 'xlwcty-post-activate' );
					$row_actions[]  = array(
						'action' => 'activate',
						'text'   => __( 'Activate', 'thank-you-page-for-woocommerce-nextmove' ),
						'link'   => $activation_url,
						'attrs'  => '',
					);
				} else {
					$row_actions[] = array(
						'action' => 'deactivate',
						'text'   => __( 'Deactivate', 'thank-you-page-for-woocommerce-nextmove' ),
						'link'   => $deactivation_url,
						'attrs'  => '',
					);
				}
				$row_actions[] = array(
					'action' => 'xlwcty_duplicate',
					'text'   => __( 'Duplicate', 'thank-you-page-for-woocommerce-nextmove' ),
					'link'   => wp_nonce_url( add_query_arg( 'page', 'wc-settings', add_query_arg( 'tab', self::get_wc_settings_tab_slug(), add_query_arg( 'action', 'xlwcty-duplicate', add_query_arg( 'postid', get_the_ID(), add_query_arg( 'trigger', $trigger ) ), network_admin_url( 'admin.php' ) ) ) ), 'xlwcty-duplicate' ),
					'attrs'  => '',
				);

				$row_actions[] = array(
					'action' => 'delete',
					'text'   => __( 'Delete Permanently', 'thank-you-page-for-woocommerce-nextmove' ),
					'link'   => get_delete_post_link( get_the_ID(), '', true ),
					'attrs'  => '',
				);
				array_push( $found_posts, array(
					'id'             => get_the_ID(),
					'trigger_status' => $status,
					'row_actions'    => $row_actions,
				) );
			}
		}
		$found_posts['found_posts'] = $q->found_posts;

		return $found_posts;
	}

	public static function get_wc_settings_tab_slug() {
		return 'xl-thank-you';
	}

	public static function get_builder_link( $id ) {
		return admin_url( 'admin.php' ) . '?page=xlwcty_builder&id=' . $id;
	}

	/*
	 *  register_post_status
	 *
	 *  This function will register custom post statuses
	 *
	 *  @type   function
	 *  @date   22/10/2015
	 *  @since  5.3.2
	 *
	 *  @param  $post_id (int)
	 *  @return $post_id (int)
	 */

	public static function pr( $arr ) {
		echo '<pre>';
		print_r( $arr );
		echo '</pre>';
	}

	public static function register_post_status() {
		// acf-disabled
		register_post_status( XLWCTY_SHORT_SLUG . 'disabled', array(
			'label'                     => __( 'Disabled', 'thank-you-page-for-woocommerce-nextmove' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Disabled <span class="count">(%s)</span>', 'Disabled <span class="count">(%s)</span>', 'thank-you-page-for-woocommerce-nextmove' ),
		) );
	}

	public static function get_parent_slug( $slug ) {
		foreach ( self::get_thank_you_page_statuses() as $key => $trigger_list ) {
			if ( isset( $trigger_list['triggers'] ) && is_array( $trigger_list['triggers'] ) && count( $trigger_list['triggers'] ) > 0 ) {
				foreach ( $trigger_list['triggers'] as $trigger ) {
					if ( $trigger['slug'] === $slug ) {
						return $key;
					}
				}
			}
		}
	}

	public static function xlwcty_get_between( $content, $start, $end ) {
		$r = explode( $start, $content );
		if ( isset( $r[1] ) ) {
			$r = explode( $end, $r[1] );

			return $r[0];
		}

		return '';
	}

	public static function xlwcty_xl_init() {
		XL_Common::include_xl_core();
	}

	public static function xlwcty_contain_current_query() {
		global $post, $wp_query;
		self::$xlwcty_post  = $post;
		self::$xlwcty_query = $wp_query;
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
		return XLWCTY_Common::get_timezone_by_offset( $utc_offset );
	}

	/**
	 * Function to get timezone string based on specified offset
	 *
	 * @param $offset
	 *
	 * @return string
	 * @see XLWCTY_Common::wc_timezone_string()
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

	/**
	 * Function to get timezone string by checking wp settings
	 * @return false|mixed|string|void
	 * @deprecated
	 */
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

	public static function get_loop_count( $start_date_timestamp, $todayDate, $total_gap ) {
		$incre = 0;
		if ( $total_gap > 0 ) {
			$incre = ( ( $todayDate - $start_date_timestamp ) / ( $total_gap * 3600 ) );
			$incre = ceil( $incre ) + 1;
		}

		return (int) $incre;
	}

	public static function get_options_defaults() {
		return array(
			'xlwcty_preview_mode'              => 'live',
			'wrap_left_right_padding'          => '0',
			'allowed_order_statuses'           => array( 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-pending' ),
			'fb_app_id'                        => '',
			'google_map_api'                   => '',
			'google_map_error_txt'             => 'Map unable to load, something wrong with address.',
			'shop_thumbnail_size'              => 'shop_catalog',
			'shop_button_bg_color'             => '#1291ff',
			'shop_button_text_color'           => '#ffffff',
			'allow_free_shipping'              => '',
			'restrict_free_shipping'           => 'no',
			'allowed_order_statuses_coupons'   => array( 'wc-processing' ),
			'coupon_blur'                      => '3',
			'append_sku_order_url'             => '',
			'append_sku_order_url_key'         => 'sku',
			'append_order_total_order_url'     => '',
			'append_order_total_order_url_key' => 'order-total',
		);
	}

	public static function check_query_params() {
		$force_debug = filter_input( INPUT_GET, 'xlwcty_force_debug' );
		if ( $force_debug === 'yes' ) {
			self::$is_force_debug = true;
		}
	}

	public static function is_load_admin_assets( $cur_screen = 'single' ) {
		$screen       = get_current_screen();
		$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );

		if ( $cur_screen == 'all' ) {
			if ( is_object( $screen ) && ( $screen->base == 'post' && $screen->post_type == XLWCTY_Common::get_thank_you_page_post_type_slug() ) ) {
				return true;
			}
			if ( is_object( $screen ) && ( $screen->base == $wc_screen_id . '_page_wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == self::get_wc_settings_tab_slug() ) ) {
				return true;
			}
			if ( filter_input( INPUT_GET, 'page' ) == 'xlwcty_builder' && filter_input( INPUT_GET, 'id' ) !== '' ) {
				return true;
			}
			if ( filter_input( INPUT_GET, 'page' ) == 'xlwcty_settings_admin_menu' ) {
				return true;
			}
		} elseif ( $cur_screen == 'single' ) {
			if ( is_object( $screen ) && ( $screen->base == 'post' && $screen->post_type == XLWCTY_Common::get_thank_you_page_post_type_slug() ) ) {
				return true;
			}

			if ( is_object( $screen ) && ( $screen->base == "toplevel_page_xlwcty_builder" ) ) {
				return true;
			}
		} elseif ( $cur_screen == 'listing' ) {
			if ( is_object( $screen ) && ( $screen->base == $wc_screen_id . '_page_wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == self::get_wc_settings_tab_slug() ) ) {
				return true;
			}
		} elseif ( $cur_screen == 'builder' ) {
			if ( filter_input( INPUT_GET, 'page' ) == 'xlwcty_builder' && filter_input( INPUT_GET, 'id' ) !== '' ) {
				return true;
			}
		} elseif ( $cur_screen == 'settings' ) {
			if ( is_object( $screen ) && ( $screen->base == $wc_screen_id . '_page_wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == self::get_wc_settings_tab_slug() ) && filter_input( INPUT_GET, 'section' ) == 'settings' ) {
				return true;
			}
		} elseif ( $cur_screen == 'debug' ) {
			if ( is_object( $screen ) && ( $screen->base == $wc_screen_id . '_page_wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == self::get_wc_settings_tab_slug() ) && filter_input( INPUT_GET, 'section' ) == 'debug' ) {
				return true;
			}
		}

		return apply_filters( 'xlwcty_enqueue_scripts', false, $cur_screen );
	}

	public static function get_item_data( $item_id ) {
		global $wpdb;

		$xl_cache_obj     = XL_Cache::get_instance();
		$xl_transient_obj = XL_Transient::get_instance();
		$meta_query       = apply_filters( 'xlwcty_product_meta_query', $wpdb->prepare( "SELECT meta_key,meta_value  FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s", $item_id, '%_xlwcty_%' ) );
		$cache_key        = 'xlwcty_thankyou_meta_' . $item_id;

		/**
		 * Setting xl cache and transient for NextMove page meta
		 */
		$cache_data = $xl_cache_obj->get_cache( $cache_key, 'nextmove' );
		if ( false !== $cache_data ) {
			$parseObj = $cache_data;
		} else {
			$transient_data = $xl_transient_obj->get_transient( $cache_key, 'nextmove' );

			if ( false !== $transient_data ) {
				$parseObj = $transient_data;
			} else {
				$product_meta                    = $wpdb->get_results( $meta_query, ARRAY_A );
				$product_meta                    = self::get_parsed_query_results_meta( $product_meta );
				$get_product_xlwcty_meta_default = self::parse_default_args_by_trigger( $product_meta );
				$parseObj                        = wp_parse_args( $product_meta, $get_product_xlwcty_meta_default );
				$xl_transient_obj->set_transient( $cache_key, $parseObj, 21600, 'nextmove' );
			}
			$xl_cache_obj->set_cache( $cache_key, $parseObj, 'nextmove' );
		}

		$fields = array();
		if ( $parseObj && is_array( $parseObj ) && count( $parseObj ) > 0 ) {
			foreach ( $parseObj as $key => $val ) {
				$newKey = $key;
				if ( strpos( $key, '_xlwcty_' ) !== false ) {
					$newKey = str_replace( '_xlwcty_', '', $key );
				}
				$fields[ $newKey ] = $val;
			}
		}

		return $fields;
	}

	public static function get_parsed_query_results_meta( $results ) {
		$parsed_results = array();
		if ( is_array( $results ) && count( $results ) > 0 ) {
			foreach ( $results as $key => $result ) {
				if ( is_array( $result ) && isset( $result['meta_key'] ) ) {
					$parsed_results[ $result['meta_key'] ] = $result['meta_value'];
				} else {
					$parsed_results[ $key ] = $result;
				}
			}
		}

		return $parsed_results;
	}

	public static function parse_default_args_by_trigger( $data ) {
		$field_option_data = self::get_default_settings();
		foreach ( $field_option_data as $slug => $value ) {
			if ( strpos( $slug, '_xlwcty_' ) !== false ) {
				$data[ $slug ] = $value;
			}
		}

		return $data;
	}

	public static function get_default_settings() {
		self::$default = array();

		return self::$default;
	}

	public static function xlwcty_get_timestamp_wc_native( $dt ) {
		$timezone      = self::wc_timezone_string();
		$date          = new DateTime( $dt, new DateTimeZone( $timezone ) );
		$ret_timestamp = $date->getTimestamp();

		return $ret_timestamp;
	}

	public static function maybe_render_elements() {
		if ( is_admin() ) {
			return __return_empty_string();
		}

		return XLWCTY_Core()->public->maybe_render_elements();
	}

	public static function maype_parse_merge_tags( $content = '', $obj = false ) {
		if ( empty( $content ) ) {
			return;
		}
		$content = XLWCTY_ShortCode_Merge_Tags::maybe_parse_merge_tags( $content, $obj );
		$content = XLWCTY_Dynamic_Merge_Tags::maybe_parse_merge_tags( $content, $obj );
		$content = XLWCTY_Static_Merge_Tags::maybe_parse_merge_tags( $content, $obj );
		$content = apply_filters( 'xlwcty_decode_coupon_merge_tags', $content, $obj );
		$content = apply_filters( 'xlwcty_parse_shortcode', $content );

		return $content;
	}

	public static function check_license_state() {
		$license = new XLWCTY_EDD_License( XLWCTY_PLUGIN_FILE, XLWCTY_FULL_NAME, XLWCTY_VERSION, 'xlplugins', null, apply_filters( 'xlwcty_edd_api_url', 'https://xlplugins.com/' ) );
		$license->weekly_license_check();
	}

	public static function get_builder_layouts() {
		return array(
			array(
				'name'    => __( 'One Column', 'thank-you-page-for-woocommerce-nextmove' ),
				'slug'    => 'basic',
				'preview' => '//storage.googleapis.com/xl-nextmove/img/layout-1.jpg',
			),
			array(
				'name'    => __( 'Two Column', 'thank-you-page-for-woocommerce-nextmove' ),
				'slug'    => 'two_column',
				'preview' => '//storage.googleapis.com/xl-nextmove/img/layout-2.jpg',
			),
		);
	}

	public static function setup_global_options() {
		if ( ! XLWCTY_Core()->data instanceof XLWCTY_Data ) {
			return;
		}
		XLWCTY_Core()->data->setup_options();
	}

	public static function toolbar_link_to_xlplugins( $wp_admin_bar ) {
		if ( is_admin() ) {
			return;
		}
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$upload_dir = wp_upload_dir();
		$base_url   = $upload_dir['baseurl'] . '/' . XLWCTY_SHORT_SLUG;
		$args       = array(
			'id'    => 'xlwcty_admin_page_node',
			'title' => 'XL ' . XLWCTY_NAME,
			'href'  => admin_url( 'admin.php?page=wc-settings&tab=' . XLWCTY_Common::get_wc_settings_tab_slug() ),
			'meta'  => array(
				'class' => 'xlwcty_admin_page_node',
			),
		);
		$wp_admin_bar->add_node( $args );
		if ( is_singular( self::get_thank_you_page_post_type_slug() ) ) {
			$args = array(
				'id'     => 'xlwcty_admin_page_node_1',
				'title'  => 'See Log',
				'href'   => $base_url . '/force.txt',
				'parent' => 'xlwcty_admin_page_node',
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	public static function get_formatted_date_from_date( $date, $format = 'Y-m-d' ) {
		if ( ! $date instanceof DateTime ) {
			if ( is_numeric( $date ) ) {
				$time = $date;
				$date = new DateTime();
				$date->setTimestamp( $time );
			} else {
				$date = new DateTime( $date );
			}
		}

		return date_i18n( $format, $date->getTimestamp() );
	}

	/**
	 * Convert a hexa decimal color code to its RGB equivalent
	 *
	 * @param string $hexStr (hexadecimal color value)
	 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
	 * @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
	 *
	 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
	 */
	public static function hex2rgb( $hexStr, $returnAsString = false, $seperator = ',' ) {
		$hexStr   = preg_replace( '/[^0-9A-Fa-f]/', '', $hexStr );
		$rgbArray = array();
		if ( strlen( $hexStr ) == 6 ) {
			$colorVal          = hexdec( $hexStr );
			$rgbArray['red']   = 0xFF & ( $colorVal >> 0x10 );
			$rgbArray['green'] = 0xFF & ( $colorVal >> 0x8 );
			$rgbArray['blue']  = 0xFF & $colorVal;
		} elseif ( strlen( $hexStr ) == 3 ) {
			$rgbArray['red']   = hexdec( str_repeat( substr( $hexStr, 0, 1 ), 2 ) );
			$rgbArray['green'] = hexdec( str_repeat( substr( $hexStr, 1, 1 ), 2 ) );
			$rgbArray['blue']  = hexdec( str_repeat( substr( $hexStr, 2, 1 ), 2 ) );
		} else {
			return false;
		}

		return $returnAsString ? implode( $seperator, $rgbArray ) : $rgbArray; // returns the rgb string or the associative array
	}

	/**
	 * Hooked over `wp_login`
	 * Resets recently logged in users order count
	 *
	 * @param string $user_login
	 * @param WP_User $user
	 */
	public static function update_user_order_count( $user_login, $user ) {
		self::get_customer_order_count( $user->ID, true );
		self::get_customer_total_spent( $user->ID, true );
	}

	/**
	 * Check for the user order count , if not found in the usermeta then fires wpdb query
	 *
	 * @param int $user_id
	 *
	 * @return int
	 * @since 2.7.1
	 *
	 */
	public static function get_customer_order_count( $user_id, $force = false ) {

		global $wpdb;
		$query = $wpdb->prepare( "SELECT COUNT(*)
            FROM $wpdb->posts as posts
            LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
            WHERE   meta.meta_key       = '_customer_user'
            AND     posts.post_type     IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "')
            AND     posts.post_status   NOT IN ('" . implode( "','", self::get_order_trashed_statuses() ) . "')
            AND     meta_value          = %d
        ", $user_id );

		$count = $wpdb->get_var( $query );

		if ( $wpdb->last_error !== '' ) {
			return 0;
		}

		update_user_meta( $user_id, '_xl_customer_order_count', absint( $count ) );

		return absint( $count );
	}

	public static function get_order_trashed_statuses() {
		return apply_filters( 'get_order_trashed_statuses', array( 'wc-cancelled', 'wc-refunded', 'wc-failed', 'trash', 'draft' ) );
	}

	/**
	 * Get total spent by customer.
	 *
	 * @param int $user_id
	 * @param int $force
	 *
	 * @return string
	 */
	public static function get_customer_total_spent( $user_id, $force = false ) {
		$spent = get_user_meta( $user_id, '_money_spent', true );
		if ( $spent === false || '' === $spent || $force === true ) {
			global $wpdb;

			$query = $wpdb->prepare( "SELECT SUM(meta2.meta_value)
            FROM $wpdb->posts as posts
            LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
            LEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id
            WHERE   meta.meta_key       = '_customer_user'
            AND     meta.meta_value     = %d
            AND     posts.post_type     IN ('" . implode( "','", wc_get_order_types( 'reports' ) ) . "')
            AND     posts.post_status   NOT IN ('" . implode( "','", self::get_order_trashed_statuses() ) . "')
            AND     meta2.meta_key      = '_order_total'
        ", $user_id );

			$spent = $wpdb->get_var( $query );

			if ( $wpdb->last_error !== '' ) {
				return 0;
			}
			update_user_meta( $user_id, '_money_spent', $spent );
		}

		return $spent;
	}

	/**
	 * filters and returned valid order statuses for order queries
	 * @return mixed|void
	 */
	public static function get_order_statuses() {
		return apply_filters( 'xlwcty_get_order_statuses', array( 'wc-processing', 'wc-on-hold', 'wc-completed' ) );
	}

	public static function get_guest_order_count( $email = '' ) {
		if ( $email === '' ) {
			return 0;
		}
		global $wpdb;

		$query = $wpdb->prepare( "SELECT COUNT(*)
        FROM $wpdb->posts as posts
        LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
        WHERE   meta.meta_key       = '_billing_email'
        AND     posts.post_type     IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "')
        AND     posts.post_status   NOT IN ('" . implode( "','", self::get_order_trashed_statuses() ) . "')
        AND     meta_value          = %s
    ", $email );

		$count = $wpdb->get_var( $query );

		if ( $wpdb->last_error !== '' ) {
			return 0;
		}

		return absint( $count );
	}

	/**
	 * Get total spent by customer.
	 *
	 * @param int $user_id
	 * @param int $force
	 *
	 * @return string
	 */
	public static function get_guest_total_spent( $email = '' ) {
		if ( '' !== $email ) {
			global $wpdb;

			$query = $wpdb->prepare( "SELECT SUM(meta2.meta_value)
            FROM $wpdb->posts as posts
            LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
            LEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id
            WHERE   meta.meta_key       = '_billing_email'
            AND     meta.meta_value     = %s
            AND     posts.post_type     IN ('" . implode( "','", wc_get_order_types( 'reports' ) ) . "')
            AND     posts.post_status   NOT IN ('" . implode( "','", self::get_order_trashed_statuses() ) . "')
            AND     meta2.meta_key      = '_order_total'
        ", $email );

			$spent = $wpdb->get_var( $query );

			if ( $wpdb->last_error !== '' ) {
				return 0;
			}
		}

		return $spent;
	}

	public static function xlwcty_get_pages_for_order() {
		sleep( 2 );

		if ( ! wp_verify_nonce( filter_input( INPUT_POST, 'nonce' ), filter_input( INPUT_POST, 'action' ) ) ) {
			wp_send_json( array(
				'result'     => 'error',
				'error_text' => __( 'Unauthorized access. ', 'thank-you-page-for-woocommerce-nextmove' ),
			) );
		}
		if ( filter_input( INPUT_POST, 'order' ) !== null && filter_input( INPUT_POST, 'order' ) !== '' ) {

			XLWCTY_Core()->data->setup_thankyou_post( (int) $_POST['order'] );

			XLWCTY_Core()->data->load_order( (int) $_POST['order'] );

			$page      = XLWCTY_Core()->data->get_page();
			$page_link = XLWCTY_Core()->data->get_page_link();
			if ( is_numeric( $page ) ) {

				$page = get_post( $page );

				$tem = get_post_meta( $page->ID, '_wp_page_template', true );
				XLWCTY_Core()->data->load_thankyou_metadata();
				$page->xlwcty_template = $tem;

				$get_layout = XLWCTY_Core()->data->get_layout();

				$file_data                   = get_file_data( plugin_dir_path( XLWCTY_PLUGIN_FILE ) . 'templates/' . $get_layout . '.php', array( 'XLWCTY Template Name' ) );
				$page->xlwcty_layout         = ( ! empty( $file_data ) ) ? $file_data[0] : '';
				$page->xlwcty_componets_html = '';
				ob_start();
				try {
					XLWCTY_Core()->public->include_template();
				} catch ( Exception $ex ) {
					echo '';
				}
				$contain = ob_get_clean();
				unset( $contain );
				$page->xlwcty_componets_html = '<strong>Page Layout</strong>: ' . $page->xlwcty_layout . '</li><li><strong>Page Components</strong>:';
				$page->xlwcty_componets_html .= '<ul>';
				foreach ( XLWCTY_Core()->public->header_info as $header_logs ) {
					if ( strpos( $header_logs, 'Template:' ) === false ) {
						$page->xlwcty_componets_html .= '<li>' . $header_logs . '</li>';
					}
				}
				$page->xlwcty_componets_html .= '</ul>';
				$page->public_link           = self::prepare_single_post_url( $page_link, wc_get_order( $_POST['order'] ) );
				wp_send_json( array(
					'result' => 'success',
					'page'   => get_post( $page ),
				) );
			}
		}

		wp_send_json( array(
			'result'     => 'error',
			'error_text' => __( 'No Thank You Page found. Create one or check rules for the existing page to see if Rules set for the page match selected order.', 'thank-you-page-for-woocommerce-nextmove' ),
		) );
	}

	/**
	 * Prepares single post url and add query arg to that woocommerce pick that url
	 *
	 * @param $link
	 * @param $order
	 *
	 * @return mixed|void
	 */
	public static function prepare_single_post_url( $link, $order ) {
		$link = add_query_arg( 'key', XLWCTY_Compatibility::get_order_data( $order, 'order_key' ), $link );
		$link = add_query_arg( 'order_id', XLWCTY_Compatibility::get_order_id( $order ), $link );

		return apply_filters( 'xlwcty_woocommerce_get_checkout_order_received_url', $link, $order );
	}

	public static function add_license_info( $localized_data ) {
		$license_state = 'Invalid';
		$support_ins   = XLWCTY_Core()->xl_support;
		$state         = get_option( $support_ins->edd_slugify_module_name( $support_ins->full_name ) . '_license_active', 'invalid' );

		if ( 'valid' === $state ) {
			$license_state = 'Valid';
		}

		$localized_data['l'] = $license_state;

		return $localized_data;
	}

	public static function get_wc_order_formmated_name( $order_status ) {
		$order_statuses = wc_get_order_statuses();
		if ( isset( $order_statuses[ $order_status ] ) ) {
			return $order_statuses[ $order_status ];
		} else {
			$mod_order_statuses = str_replace( 'wc-', '', $order_statuses );
			$mod_order_statuses = str_replace( '-', ' ', $mod_order_statuses );
			$mod_order_statuses = ucwords( $mod_order_statuses );

			return $mod_order_statuses;
		}
	}

	public static function maybe_duplicate_post() {
		global $wpdb;
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'xlwcty-duplicate' ) {

			if ( wp_verify_nonce( $_GET['_wpnonce'], 'xlwcty-duplicate' ) ) {

				$original_id = filter_input( INPUT_GET, 'postid' );
				$section     = filter_input( INPUT_GET, 'trigger' );
				if ( $original_id ) {

					// Get the post as an array
					$duplicate = get_post( $original_id, 'ARRAY_A' );
					$settings  = $defaults = array(
						'status'                => 'same',
						'type'                  => 'same',
						'timestamp'             => 'current',
						'title'                 => __( 'Copy', 'post-duplicator' ),
						'slug'                  => 'copy',
						'time_offset'           => false,
						'time_offset_days'      => 0,
						'time_offset_hours'     => 0,
						'time_offset_minutes'   => 0,
						'time_offset_seconds'   => 0,
						'time_offset_direction' => 'newer',
					);

					// Modify some of the elements
					$appended                = ( $settings['title'] != '' ) ? ' ' . $settings['title'] : '';
					$duplicate['post_title'] = $duplicate['post_title'] . ' ' . $appended;
					$duplicate['post_name']  = sanitize_title( $duplicate['post_name'] . '-' . $settings['slug'] );

					// Set the status
					if ( $settings['status'] != 'same' ) {
						$duplicate['post_status'] = $settings['status'];
					}

					// Set the type
					if ( $settings['type'] != 'same' ) {
						$duplicate['post_type'] = $settings['type'];
					}

					// Set the post date
					$timestamp     = ( $settings['timestamp'] == 'duplicate' ) ? strtotime( $duplicate['post_date'] ) : current_time( 'timestamp', 0 );
					$timestamp_gmt = ( $settings['timestamp'] == 'duplicate' ) ? strtotime( $duplicate['post_date_gmt'] ) : current_time( 'timestamp', 1 );

					if ( $settings['time_offset'] ) {
						$offset = intval( $settings['time_offset_seconds'] + $settings['time_offset_minutes'] * 60 + $settings['time_offset_hours'] * 3600 + $settings['time_offset_days'] * 86400 );
						if ( $settings['time_offset_direction'] == 'newer' ) {
							$timestamp     = intval( $timestamp + $offset );
							$timestamp_gmt = intval( $timestamp_gmt + $offset );
						} else {
							$timestamp     = intval( $timestamp - $offset );
							$timestamp_gmt = intval( $timestamp_gmt - $offset );
						}
					}
					$duplicate['post_date']         = date( 'Y-m-d H:i:s', $timestamp );
					$duplicate['post_date_gmt']     = date( 'Y-m-d H:i:s', $timestamp_gmt );
					$duplicate['post_modified']     = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
					$duplicate['post_modified_gmt'] = date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) );

					// Remove some of the keys
					unset( $duplicate['ID'] );
					unset( $duplicate['guid'] );
					unset( $duplicate['comment_count'] );

					// Insert the post into the database
					$duplicate_id = wp_insert_post( $duplicate );

					// Duplicate all the taxonomies/terms
					$taxonomies = get_object_taxonomies( $duplicate['post_type'] );
					foreach ( $taxonomies as $taxonomy ) {
						$terms = wp_get_post_terms( $original_id, $taxonomy, array(
							'fields' => 'names',
						) );
						wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
					}

					// Duplicate all the custom fields
					$custom_fields = get_post_custom( $original_id );
					foreach ( $custom_fields as $key => $value ) {
						if ( is_array( $value ) && count( $value ) > 0 ) {
							foreach ( $value as $i => $v ) {
								$result = $wpdb->insert( $wpdb->prefix . 'postmeta', array(
									'post_id'    => $duplicate_id,
									'meta_key'   => $key,
									'meta_value' => $v,
								) );
							}
						}
					}

					do_action( 'xlwcty_post_duplicated', $original_id, $duplicate_id, $settings );

					wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=' . XLWCTY_Common::get_wc_settings_tab_slug() . '&section=' . $section ) );
				}
			} else {
				die( __( 'Unable to Duplicate', 'thank-you-page-for-woocommerce-nextmove' ) );
			}
		}
	}

	public static function handle_quick_view() {

		$permalink_state         = false;
		$available_thankyou_page = false;
		$recent_order_state      = false;
		$mode                    = XLWCTY_Core()->data->get_option( 'xlwcty_preview_mode' );
		$args                    = array(
			'post_type'   => XLWCTY_Common::get_thank_you_page_post_type_slug(),
			'post_status' => 'publish',
			'nopaging'    => true,
			'meta_key'    => '_xlwcty_menu_order',
			'orderby'     => 'meta_value_num',
			'order'       => 'ASC',
			'fields'      => 'ids',
			'showposts'   => 1,
		);

		$get_posts_all = get_posts( $args );

		if ( $get_posts_all && is_array( $get_posts_all ) && count( $get_posts_all ) > 0 ) {
			$available_thankyou_page = true;
			$get_link                = get_permalink( $get_posts_all[0] );
		}

		$get_posts_check = get_posts( array(
			'post_type'   => XLWCTY_Common::get_thank_you_page_post_type_slug(),
			'fields'      => 'ids',
			'post_status' => array( 'publish', XLWCTY_SHORT_SLUG . 'disabled' ),
			'showposts'   => 1,
		) );

		if ( $get_posts_check && is_array( $get_posts_check ) && count( $get_posts_check ) > 0 ) {
			$get_link_check = get_permalink( $get_posts_check[0] );
			$get_link_check = self::parse_url_for_ssl( $get_link_check );

			$remote        = wp_remote_get( add_query_arg( array(
				'permalink_check' => 'yes',
			), $get_link_check ), array(
				'sslverify' => false,
			) );
			$response_code = wp_remote_retrieve_response_code( $remote );
			if ( is_wp_error( $remote ) ) {
				// $remote->get_error_message();
				$permalink_state = true; // we are assuming permalink state ok as curl didn't able to connect
			} elseif ( 404 != $response_code ) {
				// if response not 404 then all ok
				$permalink_state = true;
			}

			$matches     = array();
			$api_respnse = preg_match( '/{"status.*}/', wp_remote_retrieve_body( $remote ), $matches );
			$api_respnse = current( $matches );
			$api_respnse = json_decode( $api_respnse, true );
			if ( is_array( $api_respnse ) && isset( $api_respnse['status'] ) && $api_respnse['status'] == 'success' ) {
				$permalink_state = true;
			}
		}

		$nm_allowed_order_statuses = XLWCTY_Core()->data->get_option( 'allowed_order_statuses' );

		$recent_order = get_posts( array(
			'post_type'   => 'shop_order',
			'fields'      => 'ids',
			'post_status' => $nm_allowed_order_statuses,
			'showposts'   => 1,
		) );
		if ( is_array( $recent_order ) && count( $recent_order ) > 0 ) {
			$recent_order_state = true;
		}

		$nextmove_state = 'failed';

		$check_preview = '<p>' . __( 'Something is wrong with settings. Thank You page won\'t appear until all points turn green.', 'thank-you-page-for-woocommerce-nextmove' ) . '</p><h3>Possible Solutions</h3>';
		$check_preview .= '<ul>';
		if ( 'sandbox' == $mode ) {
			$change_mod_link = ' <a href="' . admin_url( 'admin.php?page=wc-settings&tab=xl-thank-you&section=settings' ) . '" target="_blank">' . __( 'Live', 'thank-you-page-for-woocommerce-nextmove' ) . '</a>';
			$check_preview   .= '<li>' . __( 'NextMove mode is Sandbox. Click here to change it to', 'thank-you-page-for-woocommerce-nextmove' ) . $change_mod_link . '</li>';
		}
		if ( false === $permalink_state ) {
			$reset_link    = ' <a href="' . admin_url( 'options-permalink.php' ) . '" target="_blank">' . __( 'Reset', 'thank-you-page-for-woocommerce-nextmove' ) . '</a>';
			$check_preview .= '<li>' . __( 'Permalink needs reset. Click here to', 'thank-you-page-for-woocommerce-nextmove' ) . $reset_link . __( ' it.', 'thank-you-page-for-woocommerce-nextmove' ) . '</li>';
		}
		if ( false === $available_thankyou_page ) {
			$check_preview .= '<li>' . __( 'There are no Active Thank You pages. Create a New page or Activate existing one.', 'thank-you-page-for-woocommerce-nextmove' ) . '</li>';
		}
		if ( false === $recent_order_state ) {
			$check_preview .= '<li>' . __( 'There are no Active WooCommerce Orders with selected order states', 'thank-you-page-for-woocommerce-nextmove' ) . ' (' . self::order_status_label_output( $nm_allowed_order_statuses ) . '). ' . __( 'Kindly create an order to see the preview.', 'thank-you-page-for-woocommerce-nextmove' ) . '</li>';
		}
		$check_preview .= '</ul>';

		$check_preview .= '<p><strong>' . __( 'If still unable to setup Thank You page. Create a', 'thank-you-page-for-woocommerce-nextmove' ) . ' <a target="_blank" href="' . admin_url( 'admin.php?page=xlplugins&tab=support' ) . '">support ticket</a>.</strong></p>';
		if ( $mode === 'live' && $available_thankyou_page === true && $permalink_state === true && $recent_order_state === true ) {
			$nextmove_state = 'success';
			$check_preview  = '<p>' . __( sprintf( 'Looks Good! <a target="_blank" href="%s" target="_blank">See Order Preview</a>', $get_link ) ) . '</p>';

			$change_settings_link = admin_url( 'admin.php?page=wc-settings&tab=xl-thank-you&section=settings' );

			$check_preview .= '<p>' . __( sprintf( '<strong>Thank You Page(s) will show on <em>Order Status: %s</em> only.</strong> <a href="%s">Check Settings</a>.', self::order_status_label_output( $nm_allowed_order_statuses ), $change_settings_link ) ) . '</p>';
		}

		$html = sprintf( '<li><i class="xl_circle_%s"></i>Mode: %s (<a href="%s" target="_blank">Change</a>)</li>', ( $mode == 'live' ) ? 'success' : 'error', ucfirst( $mode ), admin_url( 'admin.php?page=wc-settings&tab=xl-thank-you&section=settings' ) );
		$html .= sprintf( '<li><i class="xl_circle_%s"></i>Permalink State: %s %s</li>', ( $permalink_state == true ) ? 'success' : 'error', ( $permalink_state == true ) ? 'OK' : 'Needs ', ( $permalink_state == true ) ? '' : '<a target="_blank" href="' . admin_url( 'options-permalink.php' ) . '">Reset</a>' );
		$html .= sprintf( '<li><i class="xl_circle_%s"></i>Active Pages: %s</li>', ( $available_thankyou_page == true ) ? 'success' : 'error', ( $available_thankyou_page == true ) ? 'Yes' : 'No' );
		$html .= sprintf( '<li><i class="xl_circle_%s"></i>Active WC Orders: %s</li>', ( $recent_order_state == true ) ? 'success' : 'error', ( $recent_order_state == true ) ? 'Yes' : 'No' );

		wp_send_json( array(
			'status'         => 'success',
			'nextmove_state' => $nextmove_state,
			'html'           => $html,
			'after_text'     => $check_preview,
		) );
	}

	public static function parse_url_for_ssl( $url ) {
		if ( ! empty( $url ) ) {
			$replace_to = 'http://';
			if ( is_ssl() ) {
				$replace_to = 'https://';
			}
			$url = preg_replace( '(^https?://)', $replace_to, $url );

			return $url;
		}

		return false;
	}

	public static function order_status_label_output( $order_statuses ) {
		$all_order_statuses = wc_get_order_statuses();
		$output             = '';
		$order_single       = array();

		if ( is_array( $order_statuses ) && count( $order_statuses ) > 0 ) {
			foreach ( $order_statuses as $order_status_single ) {
				if ( isset( $all_order_statuses[ $order_status_single ] ) ) {
					$order_single[] = $all_order_statuses[ $order_status_single ];
				}
			}
		}

		if ( is_array( $order_single ) && count( $order_single ) > 0 ) {
			$last   = array_slice( $order_single, - 1 );
			$first  = join( ', ', array_slice( $order_single, 0, - 1 ) );
			$both   = array_filter( array_merge( array( $first ), $last ), 'strlen' );
			$output = join( ' & ', $both );
		}

		return $output;
	}

	public static function render_quick_view_metabox() {
		?>
        <div class="postbox xlwcty_side_content">
            <div class="inside">
                <h3 class="xlwcty_first_elem">NextMove Summary</h3>
                <ul class="xlwcty_quick_view xlwcty-quick-view-ajaxwrap">
                    <script type="text/html" id="tmpl-xlwcty-quick-view-template">{{{data.html}}}</script>
                </ul>
                <div class="xlwcty_status_support_text"><img style="margin-left: 40%" src="<?php echo plugin_dir_url( XLWCTY_PLUGIN_FILE ) . '/admin/assets/img/spinner.gif'; ?>"></div>
            </div>
        </div>

		<?php
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

	public static function xlwcty_alter_thank_you_url( $link, $order ) {
		$nm_options = XLWCTY_Core()->data->get_option();

		if ( $order instanceof WC_Order ) {
			$array_jointer = apply_filters( 'xlwcty_nextmove_url_jointer', '+' );

			// sku
			if ( isset( $nm_options['append_sku_order_url'] ) && isset( $nm_options['append_sku_order_url_key'] ) && 'on' == $nm_options['append_sku_order_url'] && ! empty( $nm_options['append_sku_order_url_key'] ) ) {
				$items = $order->get_items();
				$sku   = array();
				foreach ( $items as $item ) {

					if ( isset( $item['variation_id'] ) && $item['variation_id'] !== '0' ) {
						$product = wc_get_product( $item['product_id'] );
					} else {
						$product = XLWCTY_Compatibility::get_product_from_item( $order, $item );
					}
					if ( $product === false ) {
						continue;
					}
					$product_sku = $product->get_sku();
					if ( ! empty( $product_sku ) ) {
						$sku[] = preg_replace( '/[\s_]/', '-', $product_sku );
					}
					unset( $product_sku );
				}
				if ( is_array( $sku ) && count( $sku ) > 0 ) {
					$link = add_query_arg( $nm_options['append_sku_order_url_key'], implode( $array_jointer, $sku ), $link );
				}
			}

			// order total
			if ( isset( $nm_options['append_order_total_order_url'] ) && isset( $nm_options['append_order_total_order_url_key'] ) && 'on' == $nm_options['append_order_total_order_url'] && ! empty( $nm_options['append_order_total_order_url_key'] ) ) {
				$order_total = $order->get_total();
				if ( ! empty( $order_total ) ) {
					$link = add_query_arg( $nm_options['append_order_total_order_url_key'], $order_total, $link );
				}
			}
		}

		return $link;
	}

	public static function cmb2_sanitize_text( $text ) {
		if ( empty( $text ) ) {
			return;
		}

		$sanitize_text = sanitize_title( $text );

		return $sanitize_text;

	}

	/**
	 * Get Product parent id  for both version of woocommerce 2.6 and >3.0
	 *
	 * @param WC_Product $product
	 *
	 * @return integer
	 */
	public static function get_product_parent_id( $product ) {
		$parent_id = 0;

		if ( $product instanceof WC_Product ) {
			$parent_id = XLWCTY_Compatibility::get_product_parent_id( $product );
			if ( $parent_id == false ) {
				$parent_id = $product->get_id();
			}
		} elseif ( 0 !== $product ) {
			$parent_id = wp_get_post_parent_id( $product );

			if ( $parent_id == false ) {
				$parent_id = (int) $product;
			}
		}

		return $parent_id;
	}

	/**
	 * @param $url
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function get_video_id( $url, $type = 'youtube' ) {
		if ( empty( $url ) ) {
			return;
		}
		if ( 'youtube' == $type ) {
			preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match );
			if ( isset( $match[1] ) && ! empty( $match[1] ) ) {
				return $match[1];
			}

			return;
		} elseif ( 'vimeo' == $type ) {
			preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $url, $match );
			if ( isset( $match[3] ) && ! empty( $match[3] ) ) {
				return $match[3];
			}

			return;
		}

		return $url;

	}


	/**
	 * Return modified local store time or formatted time if format given
	 *
	 * @param $time unix timestamp
	 * @param string $format
	 *
	 * @return int|string
	 */
	public static function modified_timestamp_by_local_time( $time, $format = '' ) {
		$local_time = current_time( 'timestamp' );
		$gmt0_time  = time();
		$diff       = $local_time - $gmt0_time;

		$mod_time = $time + $diff;

		if ( empty( $format ) ) {
			return $mod_time;
		} else {
			return date_i18n( $format, $mod_time );
		}

	}

	/**
	 * Set 'woocommerce_recently_viewed_product' cookie client side
	 * hooked at wp_footer
	 */
	public static function xlwcty_woocommerce_recently_viewed_product() {
		if ( ! is_singular( 'product' ) ) {
			return;
		}

		?>
        <script type="text/javascript">

            jQuery(document).ready(function () {
                function xlwctysetCookie(cname, cvalue) {
                    document.cookie = cname + "=" + cvalue + ";" + ";path=/";
                }

                function xlwctygetCookie(cname) {
                    var name = cname + "=";

                    try {
                        var decodedCookie = decodeURIComponent(document.cookie);
                    } catch (err) {
                        return '';
                    }

                    var ca = decodedCookie.split(';');

                    for (var i = 0; i < ca.length; i++) {
                        var c = ca[i];
                        while (c.charAt(0) == ' ') {
                            c = c.substring(1);
                        }
                        if (c.indexOf(name) == 0) {
                            return c.substring(name.length, c.length);
                        }
                    }

                    return '';
                }

                var woocommerce_recently_viewed = xlwctygetCookie('xlwcty_recently_viewed_product');
                var current_product_id = jQuery('[name="add-to-cart"]').val();

                if ('' === woocommerce_recently_viewed) {
                    xlwctysetCookie('xlwcty_recently_viewed_product', current_product_id);
                } else {
                    var all_pids = woocommerce_recently_viewed.split('|');
                    if (all_pids.indexOf(current_product_id) === -1 && all_pids.length < 15) {
                        all_pids.push(current_product_id);
                        xlwctysetCookie('xlwcty_recently_viewed_product', all_pids.join('|'));
                    }
                }
            });
        </script>
		<?php
	}

	public static function get_thankyou_page_id( $page_id ) {
		if ( is_null( self::$custom_ty_pages ) ) {
			$custom_pages = get_option( 'xlwcty_custom_thank_you_pages', array() );

			if ( empty( $custom_pages ) ) {
				self::$custom_ty_pages = [];

				return;
			}
			self::$custom_ty_pages = $custom_pages;
		}

		if ( empty( self::$custom_ty_pages ) ) {
			return;
		}

		foreach ( self::$custom_ty_pages as $key => $value ) {
			if ( $value == $page_id ) {
				/** @var checking if the  $thankyou_post not exists than continue */
				$thankyou_post = get_post( $key );
				if ( ! $thankyou_post instanceof WP_POST ) {
					continue;
				}
				XLWCTY_Core()->public->xlwcty_is_thankyou = true;
				XLWCTY_Core()->data->page_id              = $key;

				return $key;
			}
		}
	}

	/**
	 * delete custom thankyou page from thankyou options
	 */
	public static function xlwcty_delete_custom_thankyou_options( $post_id ) {
		$post_type = get_post_field( 'post_type', $post_id );
		if ( XLWCTY_Common::get_thank_you_page_post_type_slug() !== $post_type ) {
			return;
		}
		$custom_pages = get_option( 'xlwcty_custom_thank_you_pages', array() );
		if ( empty( $custom_pages ) ) {
			return;
		}

		if ( array_key_exists( $post_id, $custom_pages ) ) {
			unset( $custom_pages[ $post_id ] );
		}

		update_option( 'xlwcty_custom_thank_you_pages', $custom_pages );
	}

}
