<?php

class Finale_deal_batch_processing {

	public static $ins = null;
	public $is_bulk_run = 0;
	public $per_page = 100;
	public $data = array();
	public $post_type = 'wcct-deal-shortcode';
	public $shortcode_id;
	public $css = '';
	public $passed_campaigns = array();
	public $excluded_rules = array();
	public $yousave_text = '';
	public $yousave_value = '';
	public $hide_sale_badge = null;
	public $deal_timer_text_before = null;
	public $deal_timer_text_after = null;
	public $add_to_cart_btn = null;
	public $is_show_bar = null;
	public $is_show_timer = null;

	public function __construct() {
		$this->wcct_deal_url = untrailingslashit( WCCT_DEAL_PAGE_PLUGIN_URL );
		$this->file_json_api = new XlCore_file( 'dealpages' );
		add_filter( 'xlwcct_setting_option_tabs', array( $this, 'add_setting_menu' ) );
		add_filter( 'xlwcct_section_pages', array( $this, 'add_seting_page' ) );
		add_action( 'xlwcct_add_on_setting-deal_pages', array( $this, 'batch_process_page' ) );
		add_action( 'cmb2_admin_init', array( $this, 'bulk_actions' ), 9 );
		add_action( 'cmb2_admin_init', array( $this, 'initialize_shortcode_form' ), 11 );
		add_action( 'finale_deal_locomotive_init', array( $this, 'run_locomotive' ), 10 );

		add_action( 'wp_ajax_wcct_deal_deindex_batch', array( $this, 'deindex_batch' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wcct_deal_page_batch_page_right_content', array( $this, 'wcct_deal_page_right_content' ), 10 );
		add_action( 'wcct_deal_page_batch_page_right_content', array( $this, 'wcct_deal_page_right_posts_per_page' ), 12 );
		add_action( 'wcct_batch_bar_woocommerce_after_shop_loop_item', array( $this, 'wcct_shop_loop_bar_display' ) );

		add_action( 'wcct_batch_timer_woocommerce_after_shop_loop_item', array( $this, 'wcct_shop_loop_timer_display' ) );

		add_action( 'init', array( $this, 'register_finale_shortcode_post_type' ) );
		add_action( 'cmb2_admin_init', array( $this, 'maybe_deindex_all' ) );
		add_filter( 'wcct_addons', array( $this, 'wcct_addons' ) );
		add_filter( 'wcct_valid_admin_pages', array( $this, 'admin_pages_support_for_wcct' ) );
		add_action( 'admin_footer', array( $this, 'footer' ) );

		add_shortcode( 'finale_deal', array( $this, 'shortcode' ) );
		add_action( 'wp_footer', array( $this, 'render_css' ) );

		/**
		 * Setting up cron for regular license checks
		 */
		add_action( 'wp', array( $this, 'wcct_license_check_schedule' ) );
		add_action( 'wcct_deal_maybe_schedule_check_license', array( $this, 'check_license_state' ) );
		add_filter( 'plugin_action_links_' . WCCT_DEAL_PAGE_PLUGIN_BASENAME, array( $this, 'plugin_actions' ) );
		add_filter( 'wcct_deal_pages_main_query', array( $this, 'add_tax_query_to_filter_products' ), 10, 3 );

		$this->post_type = 'wcct-deal-shortcode';

		add_action( 'wcct_deal_pages_before_loop', function () {
			if ( $this->is_show_timer == '1' ) {
				add_filter( 'wcct_add_timer_to_grid', array( $this, 'custom_wcct_add_timer_to_grid' ), 1 );
			}
			if ( $this->is_show_bar == '1' ) {
				add_filter( 'wcct_add_bar_to_grid', array( $this, 'custom_wcct_add_bar_to_grid' ), 1 );
			}
			add_filter( 'wcct_campaign_data_force', array( $this, 'mark_force_data_setup' ), 10, 3 );
			add_filter( 'woocommerce_sale_flash', array( $this, 'change_sale_badge_text' ), 999, 3 );
		} );
		add_action( 'wcct_deal_pages_after_loop', function () {
			if ( $this->is_show_timer == '1' ) {
				remove_filter( 'wcct_add_timer_to_grid', array( $this, 'custom_wcct_add_timer_to_grid' ), 1 );
			}
			if ( $this->is_show_bar == '1' ) {
				remove_filter( 'wcct_add_bar_to_grid', array( $this, 'custom_wcct_add_bar_to_grid' ), 1 );
			}
			remove_filter( 'wcct_campaign_data_force', array( $this, 'mark_force_data_setup' ), 10, 3 );
			remove_filter( 'woocommerce_sale_flash', array( $this, 'change_sale_badge_text' ), 999, 3 );
		} );
	}

	/**
	 * @return Finale_deal_batch_processing|null
	 */
	public static function instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function wcct_addons() {
		return true;
	}

	public function admin_pages_support_for_wcct( $bool ) {
		$screen = get_current_screen();

		if ( ( $screen->base === 'post' && $screen->post_type == $this->post_type ) ) {
			return true;
		}

		return $bool;
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'wcct_deal_style', $this->wcct_deal_url . '/assets/css/wcct-deal.css', array(), WCCT_DEAL_PAGE_VERSION );
	}

	public function add_setting_menu( $setting_tabs ) {
		$setting_tabs[] = array(
			'link'  => admin_url( 'admin.php?page=wc-settings&tab=xl-countdown-timer&section=deal_pages' ),
			'title' => __( 'Index Campaigns', 'finale-woocommerce-deal-pages' ),
		);

		$setting_tabs[] = array(
			'link'  => admin_url( 'admin.php?page=wc-settings&tab=xl-countdown-timer&section=deal_pages&sub_page=shortcode' ),
			'title' => __( 'Deal Pages', 'finale-woocommerce-deal-pages' ),
		);

		return $setting_tabs;
	}

	public function add_seting_page( $setting_page ) {
		$setting_page['deal_pages'] = 'deal_pages';

		return $setting_page;
	}

	public function batch_process_page() {

		if ( isset( $_GET['sub_page'] ) && $_GET['sub_page'] == 'shortcode' ) {
			include __DIR__ . '/xl-finale-batch-shortcode.php';
		} else {
			include __DIR__ . '/xl-finale-batch-post-table.php';
		}
	}

	public function wcct_deal_page_right_content() {
		?>
        <div class="postbox wcct_side_content">
            <div class="inside">
                <h3><?php _e( 'Deal Pages', 'finale-woocommerce-deal-pages' ); ?></h3>
				<?php
				$index_c = $this->get_campaign_by_index();
				$cls     = '';
				$link    = admin_url( 'post-new.php?post_type=wcct-deal-shortcode' );
				if ( count( $index_c ) == 0 ) {
					$cls  = 'disabled';
					$link = 'javascript:void(0)';
				}
				?>
				<?php
				if ( $cls != '' ) {
					_e( sprintf( 'No indexed campaigns found.<br/> <a href="%s">Index the campaigns</a> to generate the shortcode.', admin_url( 'admin.php?page=wc-settings&tab=xl-countdown-timer&section=deal_pages' ) ), 'finale-woocommerce-deal-pages' );
				} else {
					?>
                    <a href="<?php echo $link; ?>"
                       class="button button-primary button-large <?php echo $cls; ?>"><?php _e( 'Generate A Deal Page', 'finale-woocommerce-deal-pages' ); ?></a>
					<?php
				}
				?>
            </div>
        </div>
		<?php
	}

	public function get_campaign_by_index() {
		$cp_data = WCCT_Common::get_post_table_data( 'all' );
		if ( isset( $cp_data['found_posts'] ) ) {
			unset( $cp_data['found_posts'] );
		}

		$output = array();
		if ( count( $cp_data ) > 0 ) {
			foreach ( $cp_data as $k => $cp ) {
				if ( ! isset( $cp['id'] ) ) {
					continue;
				}

				$id  = $cp['id'];
				$god = get_option( 'wcct-deal-process-action-' . $id );

				if ( is_array( $god ) && count( $god ) > 0 ) {
					if ( $god['action'] == 1 || $god['action'] == 3 ) {
						$output[ $id ] = get_the_title( $id );
					}
				}
			}
		}

		return $output;
	}

	public function bulk_actions() {
		if ( defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( is_admin() && filter_input( INPUT_GET, 'tab' ) == 'xl-countdown-timer' && filter_input( INPUT_GET, 'section' ) == 'deal_pages' ) {

			$index      = array();
			$dindex     = array();
			$this->data = WCCT_Common::get_post_table_data( 'all' );
			$cpdata     = $this->data;
			if ( isset( $cpdata['found_posts'] ) ) {
				unset( $cpdata['found_posts'] );
			}

			if ( is_array( $cpdata ) && count( $cpdata ) > 0 ) {
				foreach ( $cpdata as $k => $val ) {
					$index[] = $val['id'];
				}
			}
			if ( isset( $_POST['wcct_deal_batch_process_action'] ) && $_POST['wcct_deal_batch_process_action'] > - 1 ) {
				$action = $_POST['wcct_deal_batch_process_action'];

				if ( isset( $_POST['cp_id'] ) && count( $_POST['cp_id'] ) > 0 ) {
					$cp_ids = $_POST['cp_id'];
					foreach ( $cp_ids as $key => $cp_id ) {
						if ( $action == 1 ) {
							$index[]  = $cp_id;
							$dindex[] = $cp_id;
						}
						if ( $action == 2 ) {
							$dindex[] = $cp_id;
						}
						$data = array(
							'date'         => time(),
							'action'       => $action,
							'current_step' => 1,
						);
						update_option( 'wcct-deal-process-action-' . $cp_id, $data );
					}
				} else {
					$action = 3;
				}

				if ( $action == 1 ) {
					$this->is_bulk_run = 1;

					$this->register_batch_deindex( $dindex );
				}
				if ( $action == 2 ) {
					$this->is_bulk_run = 2;
					$this->register_batch_deindex( $dindex );
				}
			}
			$all_index = array(
				'index'   => $index,
				'deindex' => $dindex,
			);
			update_option( 'wcct_deal_batch_process', $all_index );
		}
	}

	public function register_batch_deindex( $cp_ids ) {
		if ( is_array( $cp_ids ) && count( $cp_ids ) > 0 ) {
			global $wpdb;
			foreach ( $cp_ids as $cp_id ) {

				delete_post_meta( $cp_id, '_wcct_deal_page_index_req' );
				$response = $wpdb->delete( $wpdb->prefix . 'postmeta', array( 'meta_key' => 'wcct_batch_index_id-' . $cp_id ), array( '%s' ) );
			}
		}
	}

	public function run_locomotive() {
		$options = get_option( 'wcct_deal_batch_process', array(
			'index'   => array(),
			'deindex' => array(),
		) );
		$value   = $this->per_page = get_option( 'wcct_deal_posts_per_page', '100' );
		if ( is_array( $options['index'] ) && count( $options['index'] ) > 0 ) {

			foreach ( $options['index'] as $cp_ids ) {
				wcct_register_deal_batch( array(
					'name'     => 'wcct-batch-process-single-' . $cp_ids,
					'type'     => 'product',
					'args'     => array(
						'posts_per_page' => $value,
						'cp_ids'         => array( $cp_ids ),
						'fields'         => 'ids',
						'post_status'    => 'publish',
					),
					'callback' => array( $this, 'register_batch_index' ),
				) );
			}
			wcct_register_deal_batch( array(
				'name'     => 'wcct-index-all-campaign',
				'type'     => 'product',
				'args'     => array(
					'posts_per_page' => $value,
					'cp_ids'         => $options['index'],
					'fields'         => 'ids',
					'post_status'    => 'publish',
				),
				'callback' => array( $this, 'register_batch_index' ),
			) );
		}
	}

	public function register_batch_index( $post, $args ) {
		global $wpdb;
		$post_insertion = array();

		add_action( 'wcct_before_apply_rules_deal_pages', array( $this, 'before_excludes_rules' ) );
		add_action( 'wcct_after_apply_rules_deal_pages', array( $this, 'after_excludes_rules' ) );
		$processed_product = array();

		if ( is_array( $post ) && count( $post ) > 0 ) {
			foreach ( $post as $post_id ) {
				if ( is_array( $args ) && count( $args['cp_ids'] ) > 0 ) {
					$cp_ids = $args['cp_ids'];
					foreach ( $cp_ids as $cp_id ) {
						if ( $this->match_groups( $cp_id, $post_id ) ) {
							$post_insertion[]              = "({$post_id},'wcct_batch_index_id-{$cp_id}',1)";
							$processed_product[ $cp_id ][] = $post_id;
						}
					}
				}
			}
		}

		if ( count( $processed_product ) > 0 ) {
			foreach ( $processed_product as $cp_id => $processed_pro ) {
				$previous_index_product = array();

				if ( count( $processed_pro ) > 0 ) {
					if ( $this->file_json_api->is_writable() ) {
						$previous_index_product = $this->file_json_api->get_data( "finale-{$cp_id}-products.json" );
						$merge_index_product    = array_merge( $previous_index_product, $processed_pro );
						$this->file_json_api->put_data( "finale-{$cp_id}-products.json", array_unique( $merge_index_product ) );
					} else {
						$index_product = get_post_meta( $cp_id, '_wcct_deal_index_product', true );
						if ( $index_product == '' ) {
							$previous_index_product = array();
						} else {
							$previous_index_product = maybe_unserialize( $index_product );
						}

						$merge_index_product = array_merge( $previous_index_product, $processed_pro );
						update_post_meta( $cp_id, '_wcct_deal_index_product', array_unique( $merge_index_product ) );
					}
				}
			}
		}

		remove_action( 'wcct_before_apply_rules_deal_pages', array( $this, 'before_excludes_rules' ) );
		remove_action( 'wcct_after_apply_rules_deal_pages', array( $this, 'after_excludes_rules' ) );

		if ( ! $this->file_json_api->is_writable() ) {
			$sql = "INSERT INTO `{$wpdb->prefix}postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ";
			if ( count( $post_insertion ) > 0 ) {
				$sql .= implode( ',', $post_insertion );
				$wpdb->query( $sql );
			}
		}
	}

	public function deindex_batch() {
		$cp_id = 0;
		extract( $_POST );
		$xl_transient_obj = XL_Transient::get_instance();
		$xl_transient_obj->delete_all_transients( 'finale' );
		if ( $cp_id > 0 ) {
			$data = array(
				'date'         => time(),
				'action'       => 2,
				'current_step' => 1,
			);
			if ( $this->file_json_api->is_writable() ) {
				$this->file_json_api->delete( "finale-{$cp_id}-products.json" );
			}
			update_option( 'wcct-deal-process-action-' . $cp_id, $data );
			$this->register_batch_deindex( array( $cp_id ) );
		}
		wp_send_json( array( 'success' => true ) );
	}

	public function before_excludes_rules() {
		$this->excluded_rules = apply_filters( 'wcct_deal_pages_rules_exclude_index', array(
			'general_front_page',
			'single_product_cat_tax',
			'general_all_product_tags',
			'general_all_pages',
			'general_all_product_cats',
			'single_page',
			'single_product_tags_tax',
			'day',
			'date',
			'time',
			'Date/Time',
			'users_user',
			'users_role',
			'users_wc_membership',
		) );
	}

	public function after_excludes_rules() {
		$this->excluded_rules = array();
	}

	public function before_excludes_rules_on_front() {
		$excluded_rules = apply_filters( 'wcct_deal_pages_rules_exclude_front', array(
			'general_front_page',
			'general_all_pages',
			'single_product_cat_tax',
			'general_all_product_cats',
			'single_page',
			'general_all_product_tags',
			'single_product_tags_tax',
			'general_all_products',
			'product_select',
			'product_type',
			'product_category',
			'product_tags',
			'product_price',
			'sale_status',
			'stock_level',
			'stock_status',
		) );

		/** Other WooCommerce products taxonomies */
		$wc_tax   = get_object_taxonomies( 'product', 'names' );
		$exc_cats = array( 'product_type', 'product_visibility', 'product_cat', 'product_tag', 'product_shipping_class' );
		$wc_tax   = array_diff( $wc_tax, $exc_cats );

		if ( is_array( $wc_tax ) && count( $wc_tax ) > 0 ) {
			$wc_tax = array_filter( $wc_tax, function ( $tax_name ) {
				return ( 'pa_' !== substr( $tax_name, 0, 3 ) );
			} );
			if ( is_array( $wc_tax ) && count( $wc_tax ) > 0 ) {
				$excluded_rules = array_merge( $excluded_rules, $wc_tax );
			}
		}

		$this->excluded_rules = $excluded_rules;
	}

	public function deal_products_exclude_rules_add() {
		WCCT_Common::$excluded_rules['product_type']     = 'Product Type';
		WCCT_Common::$excluded_rules['product_category'] = 'Product Category';
		WCCT_Common::$excluded_rules['product_tags']     = 'Product Tags';
		add_filter( 'finale_match_group_cached_result', array( $this, 'force_cache_disable' ) );
	}

	public function deal_products_exclude_rules_remove() {

		$keys_to_remove = array(
			'product_type',
			'product_category',
			'product_tags',
		);
		foreach ( $keys_to_remove as $v ) {
			if ( isset( WCCT_Common::$excluded_rules[ $v ] ) ) {
				unset( WCCT_Common::$excluded_rules[ $v ] );
			}
		}
		remove_filter( 'finale_match_group_cached_result', array( $this, 'force_cache_disable' ) );
	}

	public function force_cache_disable() {
		return false;
	}

	public function after_excludes_rules_on_front() {
		$this->excluded_rules = array();
	}

	public function get_thumbnail( $product_id ) {

		$image = '';
		if ( ! $product_id ) {
			return $image;
		}
		$feat_image_url = '';
		$thumbnail_Id   = get_post_meta( $product_id, '_thumbnail_id', true );
		$image          = '';
		if ( $thumbnail_Id > 0 ) {
			$feat_image = wp_get_attachment_image_src( $thumbnail_Id, $this->_wcct_deal_shop_thumbnail_size );
			if ( is_array( $feat_image ) && count( $feat_image ) > 0 ) {
				$feat_image_url = $feat_image[0];
			}
			if ( $feat_image_url !== '' ) {
				$image = "<img src='$feat_image_url' class='wcct_pro_img' alt=''>";
			}
		}

		if ( $image === '' ) {
			$width_str = '';

			$dimensions = wc_get_image_size( $this->_wcct_deal_shop_thumbnail_size );
			$width_str  = 'width="' . $dimensions['width'] . '"';

			$feat_image_url = wc_placeholder_img_src();
			$image          = "<img src='$feat_image_url' class='wcct_pro_img' alt='' " . $width_str . '>';
		}

		return $image;
	}

	public function get_templates( $temp = false ) {
		$templates = array(
			'row_fullwidth' => array(
				'name'  => 'Row Fullwidth',
				'path'  => WCCT_DEAL_PAGE_PLUGIN_DIR . 'templates/row-fullwidth.layout.php',
				'image' => WCCT_DEAL_PAGE_PLUGIN_URL . '/assets/img/grid_layout_1.jpg',
			),
			'row'           => array(
				'name'  => 'Row',
				'path'  => WCCT_DEAL_PAGE_PLUGIN_DIR . 'templates/row.layout.php',
				'image' => '',
			),
			'grid_layout1'  => array(
				'name'  => 'Grid 1',
				'path'  => WCCT_DEAL_PAGE_PLUGIN_DIR . 'templates/grid.layout.php',
				'image' => WCCT_DEAL_PAGE_PLUGIN_URL . '/assets/img/grid_layout_1.jpg',
			),
			'grid_layout2'  => array(
				'name'  => 'Grid 2',
				'path'  => WCCT_DEAL_PAGE_PLUGIN_DIR . 'templates/grid.layout2.php',
				'image' => WCCT_DEAL_PAGE_PLUGIN_URL . '/assets/img/grid_layout_2.jpg',
			),
			'list_layout1'  => array(
				'name'  => 'List 1',
				'path'  => WCCT_DEAL_PAGE_PLUGIN_DIR . 'templates/list.layout.php',
				'image' => WCCT_DEAL_PAGE_PLUGIN_URL . '/assets/img/layout_1.jpg',
			),
			'list_layout2'  => array(
				'name'  => 'List 2',
				'path'  => WCCT_DEAL_PAGE_PLUGIN_DIR . 'templates/list.layout2.php',
				'image' => WCCT_DEAL_PAGE_PLUGIN_URL . '/assets/img/layout_2.jpg',
			),
			'list_layout3'  => array(
				'name'  => 'List 3',
				'path'  => WCCT_DEAL_PAGE_PLUGIN_DIR . 'templates/list.layout3.php',
				'image' => WCCT_DEAL_PAGE_PLUGIN_URL . '/assets/img/layout_3.jpg',
			),
		);
		if ( $temp ) {
			return isset( $templates[ $temp ] ) ? $templates[ $temp ] : $templates['grid_layout1'];
		}

		return array_merge( $templates, apply_filters( 'wcct_deal_pages_template', $templates ) );
	}

	public function initialize_shortcode_form() {
		include __DIR__ . '/wcct-cmb2-shortcode-fields.php';
	}

	public function register_finale_shortcode_post_type() {
		$menu_name = _x( WCCT_FULL_NAME, 'Admin menu name', 'finale-woocommerce-deal-pages' );

		register_post_type( $this->post_type, array(
			'labels'              => array(
				'name'               => __( 'Finale Deal Shortcodes', 'finale-woocommerce-deal-pages' ),
				'singular_name'      => __( 'Finale Deal Shortcode', 'finale-woocommerce-deal-pages' ),
				'add_new'            => __( 'Add Finale Deal Shortcode', 'finale-woocommerce-deal-pages' ),
				'add_new_item'       => __( 'Add New Finale Deal Shortcode', 'finale-woocommerce-deal-pages' ),
				'edit'               => __( 'Edit', 'finale-woocommerce-deal-pages' ),
				'edit_item'          => __( 'Edit Finale Deal Shortcode', 'finale-woocommerce-deal-pages' ),
				'new_item'           => __( 'New Finale Deal Shortcode', 'finale-woocommerce-deal-pages' ),
				'view'               => __( 'View Finale Deal Shortcode', 'finale-woocommerce-deal-pages' ),
				'view_item'          => __( 'View Finale Deal Shortcode', 'finale-woocommerce-deal-pages' ),
				'search_items'       => __( 'Search Finale Deal Shortcode', 'finale-woocommerce-deal-pages' ),
				'not_found'          => __( 'No Finale Deal Shortcode', 'finale-woocommerce-deal-pages' ),
				'not_found_in_trash' => __( 'No Finale Deal Shortcode found in trash', 'finale-woocommerce-deal-pages' ),
				'parent'             => __( 'Parent Finale Deal Shortcode', 'finale-woocommerce-deal-pages' ),
				'menu_name'          => $menu_name,
			),
			'public'              => false,
			'show_ui'             => true,
			'capability_type'     => 'product',
			'map_meta_cap'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => false,
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'rewrite'             => false,
			'query_var'           => true,
			'supports'            => array( 'title' ),
			'has_archive'         => false,
		) );
	}

	public function footer() {
		?>
        <script>
            (
                function ($) {
                    $(window).on("load", function () {
                        if (typeof pagenow !== "undefined" && "wcct-deal-shortcode" === pagenow) {
                            WCCTCMB2ConditionalsInit('#post .cmb2-wrap.wcct_options_common', '#post .cmb2-wrap.wcct_options_common');
                            WCCT_CMB2ConditionalsInit('#post .cmb2-wrap.wcct_options_common', '#post  .cmb2-wrap.wcct_options_common');
                        }
                    });

                    if (typeof pagenow !== "undefined" && "wcct-deal-shortcode" === pagenow) {
                        jQuery("#titlediv .inside").append('<p class="wcct_inside_desc">Title will not be visible to site users. </p>');

                    }

                })(jQuery)
        </script>
		<?php
	}

	public function shortcode( $atts, $content = '' ) {
		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'id'         => '',
			'count'      => '9',
			'pagination' => 'no',
			'order'      => 'ASC',
			'orderby'    => 'title',
		), $atts );

		if ( empty( $atts['id'] ) ) {
			return ( $this->is_debug() === false ) ? '' : __( '<div class="wcct_deal_error">Shortcode Error : \'id\' attribute is missing or empty.<br/> <span>(This error is only visible to Administrator (user role))</div>', 'finale-woocommerce-deal-pages' );
		}

		$getpost = get_post( $atts['id'] );

		if ( ! $getpost ) {
			return ( $this->is_debug() === false ) ? '' : __( '<div class="wcct_deal_error">Shortcode Error : Unable to find any shortcode for the given ID.<br/> <span>(This error is only visible to Administrator (user role)) </div>', 'finale-woocommerce-deal-pages' );
		}

		$this->shortcode_id = $deal_id = $atts['id'];
		$meta               = get_post_meta( $deal_id );
		$group_campaign_ids = array();

		if ( isset( $meta['wcct_finale_deal_shortcode_campaign'] ) && is_serialized( $meta['wcct_finale_deal_shortcode_campaign'][0] ) ) {
			$group_campaign_ids = maybe_unserialize( $meta['wcct_finale_deal_shortcode_campaign'][0] );
		} elseif ( isset( $meta['wcct_finale_deal_shortcode_campaign'] ) && ! is_serialized( $meta['wcct_finale_deal_shortcode_campaign'][0] ) ) {
			$group_campaign_ids = array( $meta['wcct_finale_deal_shortcode_campaign'][0] );
		}

		$parse_meta                   = $this->meta_parse( $meta );
		$parsed_meta                  = wp_parse_args( $parse_meta, wcct_get_default_fields_value() );
		$yousave_text                 = $parsed_meta['_wcct_deal_you_save_text'];
		$yousave_value                = $parsed_meta['_wcct_deal_you_save_value'];
		$deal_index_type              = $parsed_meta['_wcct_finale_deal_choose_campaign'];
		$hide_desc                    = $parsed_meta['_wcct_deal_hide_description'];
		$hide_rating                  = $parsed_meta['_wcct_deal_hide_rating'];
		$hide_sale_badge              = $parsed_meta['_wcct_deal_hide_sale_badge'];
		$is_show_bar                  = $parsed_meta['_wcct_location_bar_show_single'];
		$is_show_timer                = $parsed_meta['_wcct_location_timer_show_single'];
		$add_to_cart_text             = $parsed_meta['_wcct_add_to_cart_btn_text'];
		$deal_timer_text_before       = $parsed_meta['_wcct_deal_view_deal_timer_text_before'];
		$deal_timer_text_after        = $parsed_meta['_wcct_deal_view_deal_timer_text_after'];
		$deal_bar_text_before         = $parsed_meta['_wcct_appearance_bar_single_display_before'];
		$deal_bar_text_after          = $parsed_meta['_wcct_appearance_bar_single_display_after'];
		$deal_end_message             = $parsed_meta['_wcct_action_after_campaign_expired_text'];
		$this->yousave_text           = $yousave_text;
		$this->yousave_value          = $yousave_value;
		$this->hide_sale_badge        = $hide_sale_badge;
		$this->deal_timer_text_before = $deal_timer_text_before;
		$this->deal_timer_text_after  = $deal_timer_text_after;
		$this->add_to_cart_btn        = $add_to_cart_text;
		$this->is_show_bar            = $is_show_bar;
		$this->is_show_timer          = $is_show_timer;
		$this->populate( $parsed_meta );

		if ( 'all' === $deal_index_type ) {
			$group_campaign_ids = array_keys( $this->get_campaign_by_index() );
		}

		$this->passed_campaigns = array();
		$this->init_hooks();

		//looping over all the found campaign by any selection of the user
		if ( is_array( $group_campaign_ids ) && count( $group_campaign_ids ) > 0 ) {
			$campaign_id           = $group_campaign_ids[0];
			$this->current_cp_id   = $campaign_id;
			$this->current_cp_meta = $parsed_meta;
			$temp_campaign_ids     = ( is_array( $group_campaign_ids ) && count( $group_campaign_ids ) > 0 ) ? $group_campaign_ids : array( $campaign_id );

			if ( count( $temp_campaign_ids ) > 0 ) {
				$cp_status                  = array();
				$cp_deactivated             = array();
				$cp_deal_end_deactivated    = array();
				$cp_not_running_deactivated = array();
				$cp_not_match_rules         = array();

				foreach ( $temp_campaign_ids as $tcpid ) {
					$passed_flag          = true;
					$campaign_index_state = get_option( 'wcct-deal-process-action-' . $tcpid );

					if ( is_array( $campaign_index_state ) && 'yes' === get_post_meta( $tcpid, '_wcct_deal_page_index_req', true ) ) {
						$cp_status[] = $tcpid;
						$passed_flag = false;
					}

					$get_campaign_post = get_post_status( $tcpid );

					if ( 'publish' !== $get_campaign_post ) {
						$cp_deactivated[] = $tcpid;
						$passed_flag      = false;
					}

					$status = WCCT_Common::wcct_get_campaign_status( $tcpid );

					if ( __( 'Running', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) !== $status && __( 'Activated', 'finale-evergreen-campaigns' ) !== $status ) {
						if ( ! empty( $meta['_wcct_action_after_campaign_expired'][0] ) ) {
							if ( ( 'display_text' === $meta['_wcct_action_after_campaign_expired'][0] ) && ! empty( $deal_end_message ) ) {
								$cp_deal_end_deactivated[] = $tcpid;
								$passed_flag               = false;
							} elseif ( 'hide_products' === $meta['_wcct_action_after_campaign_expired'][0] ) {
								$cp_not_running_deactivated[] = $tcpid;
								$passed_flag                  = false;
								continue;
							} elseif ( 'continue_products' === $meta['_wcct_action_after_campaign_expired'][0] ) {
								// continue
								$is_show_timer = false;
								$is_show_bar   = false;
							}
						} else {
							$cp_not_running_deactivated[] = $tcpid;
							$passed_flag                  = false;
						}
					}

					if ( $this->match_groups( $tcpid ) === false ) {
						$passed_flag          = false;
						$cp_not_match_rules[] = $tcpid;
					}

					if ( true === $passed_flag ) {
						$this->passed_campaigns[] = $tcpid;
					}
				}

				$tcpid_count    = count( $temp_campaign_ids );
				$is_any_error   = false;
				$collect_errors = array();

				if ( count( $cp_status ) > 0 ) {
					$is_any_error     = true;
					$collect_errors[] = ( $this->is_debug() === false ) ? '' : __( sprintf( '<div class="wcct_deal_error">Shortcode Error : The Rules for following campaign(s) were modified and would need re-indexing: %s<br/> <span>(This error is only visible to Administrator (user role)) </span></div>', implode( ',', $cp_status ) ), 'finale-woocommerce-deal-pages' );
				}
				if ( count( $cp_deactivated ) > 0 ) {
					$is_any_error     = true;
					$collect_errors[] = ( $this->is_debug() === false ) ? '' : __( sprintf( '<div class="wcct_deal_error">Shortcode Error : Following campaigns are deactivated : %s.<br/> <span>(This error is only visible to Administrator (user role))</div>', implode( ',', $cp_deactivated ) ), 'finale-woocommerce-deal-pages' );
				}
				if ( count( $cp_deal_end_deactivated ) === $tcpid_count ) {
					return '<div class="wcct_deal_message">' . $deal_end_message . '</div>';
				}
				if ( count( $cp_not_running_deactivated ) === $tcpid_count ) {
					$collect_errors[] = ( $this->is_debug() === false ) ? '' : __( sprintf( '<div class="wcct_deal_error">Shortcode Error : Following campaigns are not running : %s.<br/> <span>(This error is only visible to Administrator (user role))</div>', implode( ',', $cp_not_running_deactivated ) ), 'finale-woocommerce-deal-pages' );
				}
				if ( count( $cp_not_match_rules ) === $tcpid_count ) {
					$this->remove_hooks();

					$collect_errors[] = ( $this->is_debug() === false ) ? '' : __( '<div class="wcct_deal_error">Shortcode Error : Rules does not match for the provided campaign.<br/> <span>(This error is only visible to Administrator (user role))</div>', 'finale-woocommerce-deal-pages' );
				}
			}

			$query_args = array(
				'post_status' => 'publish',
				'post_type'   => 'product',
			);

			if ( empty( $this->passed_campaigns ) && count( $collect_errors ) === 0 ) {
				$collect_errors[] = ( $this->is_debug() === false ) ? '' : __( '<div class="wcct_deal_error">Shortcode Error : Rules does not match for the provided campaign.<br/> <span>(This error is only visible to Administrator (user role))</div>', 'finale-woocommerce-deal-pages' );

			}

			//checking upload directory writable then we fetch campaign product from file
			$products_ids = array();
			foreach ( $this->passed_campaigns as $cp_id ) {
				if ( $this->file_json_api->is_writable() ) {
					$processes_post = $this->file_json_api->get_data( "finale-{$cp_id}-products.json" );
					$products_ids   = array_merge( $products_ids, $processes_post );
				}
				$query_args['meta_query'][] = array(
					array(
						'key'     => 'wcct_batch_index_id-' . $cp_id,
						'value'   => 1,
						'compare' => '=',
					),
				);
			}

			// if product ids not found in file system then check for campaign meta
			if ( count( $products_ids ) === 0 ) {
				// check-for campaign meta;
				foreach ( $this->passed_campaigns as $cp_id ) {
					$cp_meta        = WCCT_Common::get_item_data( $cp_id );
					$processes_post = maybe_unserialize( $cp_meta['deal_index_product'] );
					if ( is_array( $processes_post ) && count( $processes_post ) > 0 ) {
						$products_ids = array_merge( $products_ids, $processes_post );
					}
				}
			}
			if ( count( $products_ids ) > 0 ) {
				$products_ids           = array_unique( $products_ids );
				$query_args['post__in'] = $products_ids;
				unset( $query_args['meta_query'] );
			}

			if ( isset( $query_args['meta_query'] ) && is_array( $query_args['meta_query'] ) && count( $query_args['meta_query'] ) > 1 ) {
				return ( $this->is_debug() === false ) ? '' : __( '<div class="wcct_deal_error">Shortcode Error : We are unable to find any associated products for selected campaign(s). <br/> <span>(This error is only visible to Administrator (user role)) </div>', 'finale-woocommerce-deal-pages' );
			}

			switch ( $atts['orderby'] ) {
				case 'rand':
					$query_args['orderby'] = 'rand';
					break;
				case 'date':
					$query_args['orderby'] = 'date ID';
					$query_args['order']   = ( 'ASC' === $atts['order'] ) ? 'ASC' : 'DESC';
					break;
				case 'price':
					if ( 'DESC' === $atts['order'] ) {
						add_filter( 'posts_clauses', array( $this, 'order_by_price_desc_post_clauses' ) );
					} else {
						add_filter( 'posts_clauses', array( $this, 'order_by_price_asc_post_clauses' ) );
					}
					break;
				case 'sales':
					$query_args['meta_key'] = 'total_sales';

					// Sorting handled later though a hook
					add_filter( 'posts_clauses', array( $this, 'order_by_popularity_post_clauses' ) );
					break;
				case 'rating':
					$query_args['meta_key'] = '_wc_average_rating';
					$query_args['orderby']  = array(
						'meta_value_num' => 'DESC',
						'ID'             => 'ASC',
					);
					break;
				case 'title':
					$query_args['orderby'] = 'title';
					$query_args['order']   = ( 'DESC' === $atts['order'] ) ? 'DESC' : 'ASC';
					break;
				case 'campaign_priority':
					$query_args['orderby'] = 'post__in';
					break;
				default:
					if ( ! empty( $atts['orderby'] ) ) {
						$query_args['orderby'] = $atts['orderby'];
						$query_args['order']   = ( 'DESC' === $atts['order'] ) ? 'DESC' : 'ASC';
					}
					break;
			}

			if ( 'yes' === $atts['pagination'] ) {
				$cur_page                     = get_query_var( 'paged' );
				$cur_page                     = empty( $cur_page ) ? get_query_var( 'page' ) : $cur_page;
				$query_args['paged']          = max( 1, $cur_page );
				$query_args['posts_per_page'] = $atts['count'];
			} else {
				$query_args['showposts'] = $atts['count'];
			}

			$load_template      = WCCT_DEAL_PAGE_PLUGIN_DIR . 'templates/grid.layout.php';
			$template_grid_cols = isset( $meta['_wcct_deal_grid_size'][0] ) ? $meta['_wcct_deal_grid_size'][0] : 3;

			if ( isset( $meta['_wcct_deal_template_layout'][0] ) && ! empty( $meta['_wcct_deal_template_layout'][0] ) ) {
				if ( 'grid' === $meta['_wcct_deal_template_layout'][0] ) {
					$load_template = $this->get_templates( $meta['_wcct_deal_layout_grid'][0] );
					$load_template = $load_template['path'];
				} elseif ( 'list' === $meta['_wcct_deal_template_layout'][0] ) {
					$load_template = $this->get_templates( $meta['_wcct_deal_layout_list'][0] );
					$load_template = $load_template['path'];
				} elseif ( 'native' === $meta['_wcct_deal_template_layout'][0] ) {
					$load_template = WCCT_DEAL_PAGE_PLUGIN_DIR . 'templates/grid.native.php';
				}
			}

			$query_args = apply_filters( 'wcct_deal_pages_main_query', $query_args, $this->passed_campaigns, $atts );
			$r          = new WP_Query( $query_args );

			$this->remove_query_hooks();
			if ( $this->is_debug() === true && 0 === intval( $r->found_posts ) ) {
				return __( '<div class="wcct_deal_error">Shortcode Error : We are unable to find any associated products for this campaign. <br/> <span>(This error is only visible to Administrator (user role))</div>', 'finale-woocommerce-deal-pages' );
			}

			if ( empty( $this->passed_campaigns ) && count( $collect_errors ) > 0 ) {
				ob_start();
				echo implode( '', $collect_errors );

				return ob_get_clean();
			}
			ob_start();
			if ( count( $collect_errors ) > 0 ) {

				echo implode( '', $collect_errors );
			}

			$shortcode_id = $this->shortcode_id;
			$class_unique = '.wcct_unique_' . $shortcode_id;
			?>
            <style>
                <?php
				if ( false === $is_show_timer && false === $is_show_bar ) {
					echo $class_unique . '.wcct_custom_pro_grid .wcct_pro_col .wcct_pro_price_wrap{margin-bottom:20px;}';
				}

				?>
                .wcct_wrap_grid<?php echo $class_unique; ?> .wcct_custom_pro_grid .wcct_pro_col .wcct_pro_cart_btn .wcct_pro_add_to_cart {
                <?php

				if ( ! empty( $meta['_wcct_add_to_cart_btn_text_bg_color'][0] ) ) {
					echo 'background:' . $meta['_wcct_add_to_cart_btn_text_bg_color'][0] . ';';
				}
				if ( ! empty( $meta['_wcct_add_to_cart_btn_text_font_size'][0] ) ) {
					echo 'font-size:' . $meta['_wcct_add_to_cart_btn_text_font_size'][0] . 'px;';
				}
				if ( ! empty( $meta['_wcct_add_to_cart_btn_text_color'][0] ) ) {
					echo 'color:' . $meta['_wcct_add_to_cart_btn_text_color'][0] . ';';
				}
				if ( ! empty( $meta['_wcct_add_to_cart_btn_width'][0] ) && ( 'inline' === $meta['_wcct_add_to_cart_btn_width'][0]) ) {
					if ( ! empty( $meta['_wcct_deal_template_layout'][0] ) && 'grid' === $meta['_wcct_deal_template_layout'][0] ) {
						echo 'display: inline-block;padding:6px 15px;';
					}
				}
				?>
                }

                .wcct_wrap_grid<?php echo $class_unique; ?> .wcct_custom_pro_grid .wcct_pro_col .wcct_pro_cart_btn .wcct_pro_add_to_cart:hover {
                <?php
				if ( ! empty( $meta['_wcct_add_to_cart_btn_text_bg_color_hover'][0] ) ) {
					echo 'background:' . $meta['_wcct_add_to_cart_btn_text_bg_color_hover'][0] . ';';
				}
				?>
                }

                .wcct_wrap_grid<?php echo $class_unique; ?> .wcct_custom_pro_grid .wcct_pro_col .wcct_pro_sale, .wcct_pro_sale {
                <?php
				echo 'background:' . $this->_wcct_sale_badge_color . ';';
				?>
                }

                .wcct_wrap_grid<?php echo $class_unique; ?> .wcct_custom_pro_grid .wcct_pro_col .wcct_pro_sale, .wcct_pro_sale {
                <?php
				echo 'color:' . $this->_wcct_sale_badge_text_color . ';';
				?>
                }

            </style>
            <div class="woocommerce wcct_clear">
                <div class="wcct_wrap_grid wcct_unique_<?php echo $this->shortcode_id; ?>" data_c_ids="<?php echo implode( ',', $this->passed_campaigns ); ?>">
					<?php include $load_template; ?>
                </div>
            </div>
			<?php
			$this->remove_hooks();

			return ob_get_clean();
		} else {
			return ( $this->is_debug() === false ) ? '' : __( '<div class="wcct_deal_error">Shortcode Error : No campaign is associated with this shortcode.<br/> <span>(This error is only visible to Administrator (user role))</div>', 'finale-woocommerce-deal-pages' );
		}
	}

	public function meta_parse( $meta ) {

		$parsed_meta = array();
		if ( is_array( $meta ) && count( $meta ) > 0 ) {
			foreach ( $meta as $key => $arr ) {
				$parsed_meta[ $key ] = $arr[0];
			}
		}

		return $parsed_meta;
	}

	protected function init_hooks() {
		add_action( 'wcct_before_apply_rules_deal_pages', array( $this, 'before_excludes_rules_on_front' ) );
		add_action( 'wcct_after_apply_rules_deal_pages', array( $this, 'after_excludes_rules_on_front' ) );
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_bar_and_timer_at_native_layout' ), 6 );

	}

	protected function remove_hooks() {
		remove_action( 'wcct_before_apply_rules_deal_pages', array( $this, 'before_excludes_rules_on_front' ) );
		remove_action( 'wcct_after_apply_rules_deal_pages', array( $this, 'after_excludes_rules_on_front' ) );
		remove_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'change_add_to_cart_text_for_native' ), 10, 2 );
		remove_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_bar_and_timer_at_native_layout' ), 6 );

	}

	public function display_bar_and_timer_at_native_layout() {
		global $product;
		if ( $this->yousave_text != '' && $this->yousave_value != '' ) {
			?>
            <div class="wcct_pro_price_wrap">
				<span class="wcct_pro_save">
					<span class="wcct_save_text"><?php printf( '%s', nl2br( $this->yousave_text ) ); ?></span>
					<span class="wcct_save_percent"><?php echo wcct_get_product_savings( $product, $this->yousave_value ); ?></span>
				</span>
            </div>
			<?php
		}
	}

	public function custom_wcct_add_timer_to_grid() {
		$meta = $this->current_cp_meta;

		$single_grid = array();
		$core_data   = $single_grid;

		if ( $meta['_wcct_appearance_timer_single_skin'] != '' ) {
			$core_data['skin'] = $meta['_wcct_appearance_timer_single_skin'];
		}

		if ( $meta['_wcct_appearance_timer_single_bg_color'] != '' ) {
			$core_data['bg_color'] = $meta['_wcct_appearance_timer_single_bg_color'];
		}

		if ( $meta['_wcct_appearance_timer_single_text_color'] != '' ) {
			$core_data['lable_color'] = $meta['_wcct_appearance_timer_single_text_color'];
		}

		if ( $meta['_wcct_appearance_timer_single_font_size_timer'] != '' ) {
			$core_data['timer_font'] = $meta['_wcct_appearance_timer_single_font_size_timer'];
		}

		if ( $meta['_wcct_appearance_timer_single_font_size'] != '' ) {
			$core_data['label_font'] = $meta['_wcct_appearance_timer_single_font_size'];
		}
		if ( $meta['_wcct_appearance_timer_single_text_color'] != '' ) {
			$core_data['label_color'] = $meta['_wcct_appearance_timer_single_text_color'];
		}

		$core_data['display'] = '{{countdown_timer}}';

		if ( $meta['_wcct_appearance_timer_single_label_days'] != '' ) {
			$core_data['label_days'] = $meta['_wcct_appearance_timer_single_label_days'];
		}
		if ( $meta['_wcct_appearance_timer_single_label_hrs'] != '' ) {
			$core_data['label_hrs'] = $meta['_wcct_appearance_timer_single_label_hrs'];
		}
		if ( $meta['_wcct_appearance_timer_single_label_mins'] != '' ) {
			$core_data['label_mins'] = $meta['_wcct_appearance_timer_single_label_mins'];
		}
		$core_data['border_style'] = 'none';
		$core_data['label_secs']   = $meta['_wcct_appearance_timer_single_label_secs'];

		return $core_data;
	}

	public function custom_wcct_add_bar_to_grid() {
		$meta      = $this->current_cp_meta;
		$core_data = array();
		if ( $meta['_wcct_appearance_bar_single_skin'] != '' ) {
			$core_data['skin'] = $meta['_wcct_appearance_bar_single_skin'];
		}
		if ( $meta['_wcct_appearance_bar_single_edges'] != '' ) {
			$core_data['edge'] = $meta['_wcct_appearance_bar_single_edges'];
		}
		if ( $meta['_wcct_appearance_bar_single_orientation'] != '' ) {
			$core_data['orientation'] = $meta['_wcct_appearance_bar_single_orientation'];
		}
		if ( $meta['_wcct_appearance_bar_single_bg_color'] != '' ) {
			$core_data['bg_color'] = $meta['_wcct_appearance_bar_single_bg_color'];
		}

		if ( $meta['_wcct_appearance_bar_single_active_color'] != '' ) {
			$core_data['active_color'] = $meta['_wcct_appearance_bar_single_active_color'];
		}

		if ( $meta['_wcct_appearance_bar_single_height'] != '' ) {
			$core_data['height'] = $meta['_wcct_appearance_bar_single_height'];
		}
		$core_data['border_style'] = 'none';
		$core_data['display']      = '' . $meta['_wcct_appearance_bar_single_display_before'] . '' . '{{counter_bar}}' . '' . $meta['_wcct_appearance_bar_single_display_after'] . '';

		return $core_data;
	}

	function wcct_shop_loop_bar_display() {
		WCCT_Core()->appearance->wcct_bar_timer_show_on_grid( 'bar' );
	}

	function wcct_shop_loop_timer_display() {
		WCCT_Core()->appearance->wcct_bar_timer_show_on_grid( 'timer' );
	}

	public function change_add_to_cart_text_for_native( $text, $product ) {
		if ( $this->add_to_cart_btn != '' ) {

			if ( isset( $this->_wcct_deal_pages_add_to_cart_exclude ) && is_array( $this->_wcct_deal_pages_add_to_cart_exclude ) && in_array( $product->get_type(), $this->_wcct_deal_pages_add_to_cart_exclude ) ) {
				return $text;
			} else {
				return $this->add_to_cart_btn;
			}
		}

		return $text;
	}

	public function change_sale_badge_text( $sale_html, $post, $product ) {
		if ( 'on' !== $this->hide_sale_badge ) {
			$sale_html = '<span class="wcct_pro_sale">' . $this->_wcct_deal_sale_badge_text . '</span>';
		}

		return $sale_html;
	}

	public function wcct_deal_page_right_posts_per_page() {
		$value = get_option( 'wcct_deal_posts_per_page', '100' );
		?>
        <div class="postbox wcct_side_content">
            <div class="inside">
                <h3><?php _e( 'Batch Size', WCCT_SLUG ); ?></h3>
                <form method="POST">
                    <input type="number" pattern="\d*" min="1" max="500" name='wcct_deal_posts_per_page' value="<?php echo $value; ?>"/>
                    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'woocommerce-settings' ); ?>"/>
                    <input type="submit" class="button" value="<?php _e( 'Save', WCCT_SLUG ); ?>"/>
                </form>
                <div class="wcct_font_small">
                    <p>Number of products that will be picked up and processed in one iteration.
                        <a href="javascript:void(0);"
                           onclick="wcct_modal_show('<?php echo __( 'Batch Size Help', WCCT_SLUG ); ?>','#WCCT_MB_inline?height=500&amp;width=1000&amp;inlineId=wcct_deal_batch_size_help')"><?php echo __( 'Click here', WCCT_SLUG ); ?></a>
                        to learn about ideal batch size settings.</p>
                    <div style="display:none;" class="wcct_tb_content" id="wcct_deal_batch_size_help">
                        <p>Number of products that will be picked up and processed in one iteration.</p>
                        <p>If you set this as 10, a lot of 10 products will be picked up at a time and indexed. Greater the batch size, the lesser time it would take to index the campigns.</p>
                        <h3>What is the ideal settings?</h3>
                        <p>It totally depends on these factors:</p>
                        <ul type="disc">
                            <li>- Number of products</li>
                            <li>- Kind of rules you have set up in your campaigns</li>
                            <li>- Processing power of your server</li>
                        </ul>
                        <h3>How do I figure out the ideal Batch size?</h3>
                        <ul>
                            <li>- On shared server start with Batch Size of 50. And increase or decrease based on results you get.</li>
                            <li>- On VPS servers we have tested Batch Size to be as high as 500 and it has worked well.</li>
                        </ul>
                        <p>If you are not sure keep it to safe limit of 10.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="postbox wcct_side_content">
            <div class="inside">
                <h3>De-Index</h3>
                <div class="wcct_font_small">
                    <p>Sometimes you may need to de-index all the campaigns or remove all the data saved in the database or want to do a fresh start.</p>
                    <a onclick="
                            var resp = confirm('This action will De-Index all the campaigns.');
                            if(resp === true){
                            window.location.href=  '<?php echo admin_url( 'admin.php?page=wc-settings&tab=xl-countdown-timer&section=deal_pages&deindex=all' ); ?>' }" href="javascript:void(0);"
                       class="button button-secondary button-large">De-Index
                        All</a>
                </div>
            </div>
        </div>
		<?php
	}

	protected function populate( $data = array() ) {

		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $value ) {
				$this->{$key} = maybe_unserialize( $value );
			}
		}
	}

	public function add_css( $css ) {
		$this->css .= $css;
	}

	public function render_css() {
		?>
        <style type="text/css"> <?php echo $this->css; ?></style>
		<?php
	}

	public function wcct_license_check_schedule() {
		if ( ! wp_next_scheduled( 'wcct_deal_maybe_schedule_check_license' ) ) {
			$resp = wp_schedule_event( current_time( 'timestamp' ), 'daily', 'wcct_deal_maybe_schedule_check_license' );
		}
	}

	public function check_license_state() {
		$license = new WCCT_EDD_License( WCCT_DEAL_PAGE_PLUGIN_FILE, WCCT_DEAL_FULL_NAME, WCCT_DEAL_PAGE_VERSION, 'xlplugins', null, apply_filters( 'wcct_edd_api_url', 'https://xlplugins.com/' ) );
		$license->weekly_license_check();
	}

	/**
	 * Hooked over 'plugin_action_links_{PLUGIN_BASENAME}' WordPress hook to add deactivate popup support
	 *
	 * @param array $links array of exising links
	 *
	 * @return array modified array
	 */
	public function plugin_actions( $links ) {
		$links['deactivate'] .= '<i class="xl-slug" data-slug="' . WCCT_DEAL_PAGE_PLUGIN_BASENAME . '"></i>';

		return $links;
	}

	public function is_debug() {
		if ( 'yes' === filter_input( INPUT_GET, 'finale_deal_debug' ) ) {
			return true;
		}

		if ( is_user_logged_in() && current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		return false;
	}

	public function match_groups( $content_id, $productID = 0 ) {
		$iteration_results = array();
		do_action( 'wcct_before_apply_rules_deal_pages', $content_id, $productID );
		WCCT_Common::$is_executing_rule = true;

		//allowing rules to get manipulated using external logic
		$external_rules = apply_filters( 'wcct_modify_rules', true, $content_id, $productID );
		if ( ! $external_rules ) {
			WCCT_Common::$is_executing_rule = false;

			return false;
		}

		$groups = get_post_meta( $content_id, 'wcct_rule', true );
		if ( $groups && is_array( $groups ) && count( $groups ) ) {
			foreach ( $groups as $group_id => $group ) {
				$result = null;

				foreach ( $group as $rule_id => $rule ) {
					//just skipping the rule if excluded, so that it wont play any role in final judgement
					if ( in_array( $rule['rule_type'], $this->excluded_rules ) ) {

						continue;
					}
					$rule_object = WCCT_Common::woocommerce_wcct_rule_get_rule_object( $rule['rule_type'] );

					if ( is_object( $rule_object ) ) {

						$match = $rule_object->is_match( $rule, $productID );

						//assigning values to the array.
						//on false, as this is single group (bing by AND), one false would be enough to declare whole result as false so breaking on that point
						if ( $match === false ) {
							$iteration_results[ $group_id ] = 0;
							break;
						} else {
							$iteration_results[ $group_id ] = 1;
						}
					}
				}

				//checking if current group iteration combine returns true, if its true, no need to iterate other groups
				if ( isset( $iteration_results[ $group_id ] ) && $iteration_results[ $group_id ] === 1 ) {
					break;
				}
			}

			//checking count of all the groups iteration
			if ( count( $iteration_results ) > 0 ) {

				//checking for the any true in the groups
				if ( array_sum( $iteration_results ) > 0 ) {
					$display = true;
				} else {
					$display = false;
				}
			} else {

				//handling the case where all the rules got skipped
				$display = true;
			}
		} else {
			$display = true; //Always display the content if no rules have been configured.
		}

		do_action( 'wcct_after_apply_rules_deal_pages', $content_id, $productID );

		WCCT_Common::$is_executing_rule = false;

		return $display;
	}

	public function remove_query_hooks() {
		remove_filter( 'posts_clauses', array( $this, 'order_by_price_asc_post_clauses' ) );
		remove_filter( 'posts_clauses', array( $this, 'order_by_price_desc_post_clauses' ) );
		remove_filter( 'posts_clauses', array( $this, 'order_by_popularity_post_clauses' ) );
		remove_filter( 'posts_clauses', array( $this, 'order_by_rating_post_clauses' ) );

	}

	/**
	 * Handle numeric price sorting.
	 *
	 * @access public
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function order_by_price_asc_post_clauses( $args ) {
		global $wpdb;
		$args['join']    .= " INNER JOIN ( SELECT post_id, min( meta_value+0 ) price FROM $wpdb->postmeta WHERE meta_key='_price' GROUP BY post_id ) as price_query ON $wpdb->posts.ID = price_query.post_id ";
		$args['orderby'] = ' price_query.price ASC ';

		return $args;
	}

	/**
	 * Handle numeric price sorting.
	 *
	 * @access public
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function order_by_price_desc_post_clauses(
		$args
	) {
		global $wpdb, $wp_query;

		if ( isset( $wp_query->queried_object, $wp_query->queried_object->term_taxonomy_id, $wp_query->queried_object->taxonomy ) && is_a( $wp_query->queried_object, 'WP_Term' ) ) {
			$search_within_terms   = get_term_children( $wp_query->queried_object->term_taxonomy_id, $wp_query->queried_object->taxonomy );
			$search_within_terms[] = $wp_query->queried_object->term_taxonomy_id;
			$args['join']          .= " INNER JOIN (
				SELECT post_id, max( meta_value+0 ) price
				FROM $wpdb->postmeta
				INNER JOIN (
					SELECT $wpdb->term_relationships.object_id
					FROM $wpdb->term_relationships
					WHERE 1=1
					AND $wpdb->term_relationships.term_taxonomy_id IN (" . implode( ',', array_map( 'absint', $search_within_terms ) ) . ")
				) as products_within_terms ON $wpdb->postmeta.post_id = products_within_terms.object_id
				WHERE meta_key='_price' GROUP BY post_id ) as price_query ON $wpdb->posts.ID = price_query.post_id ";
		} else {
			$args['join'] .= " INNER JOIN ( SELECT post_id, max( meta_value+0 ) price FROM $wpdb->postmeta WHERE meta_key='_price' GROUP BY post_id ) as price_query ON $wpdb->posts.ID = price_query.post_id ";
		}

		$args['orderby'] = ' price_query.price DESC ';

		return $args;
	}

	/**
	 * WP Core doens't let us change the sort direction for individual orderby params - https://core.trac.wordpress.org/ticket/17065.
	 *
	 * This lets us sort by meta value desc, and have a second orderby param.
	 *
	 * @access public
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function order_by_popularity_post_clauses( $args ) {
		global $wpdb;
		$args['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_date DESC";

		return $args;
	}

	/**
	 * Order by rating post clauses.
	 *
	 * @param array $args
	 *
	 * @return array
	 * @deprecated 3.0.0
	 *
	 */
	public function order_by_rating_post_clauses( $args ) {
		global $wpdb;

		wc_deprecated_function( 'order_by_rating_post_clauses', '3.0' );

		$args['fields']  .= ", AVG( $wpdb->commentmeta.meta_value ) as average_rating ";
		$args['where']   .= " AND ( $wpdb->commentmeta.meta_key = 'rating' OR $wpdb->commentmeta.meta_key IS null ) ";
		$args['join']    .= "
			LEFT OUTER JOIN $wpdb->comments ON($wpdb->posts.ID = $wpdb->comments.comment_post_ID)
			LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)
		";
		$args['orderby'] = "average_rating DESC, $wpdb->posts.post_date DESC";
		$args['groupby'] = "$wpdb->posts.ID";

		return $args;
	}


	/**
	 * @hooked over `wcct_deal_pages_main_query`
	 * Modifies shortcode product query and filter out products against woocommerce visibility options
	 *
	 * @param Array $args query arguments
	 * @param String $campaign_id Campaign ID
	 * @param array $attributes Shortcode attributes
	 *
	 * @return array modified arguments
	 */
	public function add_tax_query_to_filter_products( $args, $campaign_id, $attributes ) {

		$tax_query = array();

		$product_visibility_terms  = wc_get_product_visibility_term_ids();
		$product_visibility_not_in = array( $product_visibility_terms['exclude-from-catalog'] );

		// Hide out of stock products.
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$product_visibility_not_in[] = $product_visibility_terms['outofstock'];
		}

		if ( ! empty( $product_visibility_not_in ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $product_visibility_not_in,
				'operator' => 'NOT IN',
			);
		}
		$args['tax_query'] = $tax_query;

		return $args;
	}

	/**
	 * Hooked over `cmb2_admin_init`: 14
	 * Restore to a state where deal pages started
	 * Deindex all campaigns
	 * Remove All the log
	 * Remove All the shortcodes
	 *
	 */
	public function maybe_deindex_all() {
		if ( is_admin() && filter_input( INPUT_GET, 'tab' ) === 'xl-countdown-timer' && filter_input( INPUT_GET, 'section' ) === 'deal_pages' && filter_input( INPUT_GET, 'deindex' ) === 'all' ) {
			$args     = array(
				'numberposts' => - 1,
				'orderby'     => 'ID',
				'order'       => 'DESC',
				'meta_key'    => '_wcct_campaign_menu_order',
				'post_type'   => WCCT_Common::get_campaign_post_type_slug(),
				'post_status' => array( 'publish', WCCT_SHORT_SLUG . 'disabled' ),
			);
			$get_data = get_posts( $args );

			if ( is_array( $get_data ) && count( $get_data ) > 0 ) {
				$get_ids = wp_list_pluck( $get_data, 'ID' );
				$this->register_batch_deindex( $get_ids );

				foreach ( $get_ids as $id ) {
					delete_option( 'wcct-deal-process-action-' . $id );
				}
			}

			wp_redirect( admin_url( 'admin.php?page=wc-settings&tab=xl-countdown-timer&section=deal_pages' ) );
		}
	}

	/**
	 * @hooked into `wcct_campaign_data_force`
	 * Forces data setup in finale
	 *
	 * @param Boolean $is_force parameter to return
	 * @param Integer $product_id Current Product ID
	 * @param Boolean $the_post Boolean to check if its a post setup call
	 *
	 * @return bool
	 */
	public function mark_force_data_setup( $is_force, $product_id, $the_post ) {
		if ( true === $the_post ) {
			return true;
		}

		return $is_force;
	}


}

Finale_deal_batch_processing::instance();
