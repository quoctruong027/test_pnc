<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class VI_WPRODUCTBUILDER_Admin_Admin {
	public function __construct() {
		add_filter( 'plugin_action_links_woocommerce-product-builder/woocommerce-product-builder.php', array(
			$this,
			'settings_link'
		) );
		add_action( 'load-options-permalink.php', array( $this, 'woo_product_builder_load_permalinks' ), 11 );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_product_builder_metaboxes' ) );

		add_action( 'save_post', array( $this, 'save_post_metadata' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ) );

		/*Get list product or categories in edit page*/
		add_action( 'wp_ajax_woopb_get_data', array( $this, 'get_data' ) );

		add_filter( 'manage_woo_product_builder_posts_columns', array( $this, 'define_shortcode_columns' ) );
		add_action( 'manage_woo_product_builder_posts_custom_column', array( $this, 'shortcode_columns' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ) );

		add_action( 'edit_form_after_editor', array( $this, 'show_shortcode' ) );
	}


	/**
	 * Get Product via ajax
	 */
	public function get_data() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$type    = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_NUMBER_INT );
		$keyword = filter_input( INPUT_POST, 'keyword', FILTER_SANITIZE_STRING );
		$results = array();
		switch ( $type ) {
			case 1:
				$args      = array(
					'post_status'    => 'publish',
					'post_type'      => 'product',
					'posts_per_page' => 50,
					's'              => $keyword,
					'tax_query'      => array(
						array(
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => apply_filters( 'woopb_product_type', array( 'simple', 'variable' ) ),
							//custom work
							'operator' => 'IN'
						),
					)
				);
				$the_query = new WP_Query( $args );
				// The Loop
				if ( $the_query->have_posts() ) {
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						$data          = array();
						$data['id']    = get_the_ID();
						$data['title'] = get_the_title();
						if ( has_post_thumbnail() ) {
							$data['thumb_url'] = get_the_post_thumbnail_url();
						} else {
							$data['thumb_url'] = '';
						}
						$results[] = $data;
					}
				}
				// Reset Post Data
				wp_reset_postdata();
				break;
			default:
				$args  = array(
					'taxonomy'   => 'product_cat',
					'orderby'    => 'name',
					'hide_empty' => true,
					'number'     => 50,
					'search'     => $keyword
				);
				$cates = get_terms( $args );
				if ( count( $cates ) ) {
					foreach ( $cates as $cat ) {
						$data              = array();
						$data['id']        = $cat->term_id;
						$data['title']     = $cat->name;
						$data['thumb_url'] = '';
						$results[]         = $data;
					}
				}
		}
		wp_send_json( $results );
		die;
	}

	/**
	 * Register post type
	 */
	public function init() {
		load_plugin_textdomain( 'woocommerce-product-builder' );
		$this->load_plugin_textdomain();
		@session_start();
		register_post_type( 'woo_product_builder', array(
			'labels' => array(
				'name'               => __( 'Product Builders', 'woocommerce-product-builder' ),
				'singular_name'      => __( 'Product Builders', 'woocommerce-product-builder' ),
				'add_new'            => __( 'Add New', 'woocommerce-product-builder' ),
				'add_new_item'       => __( 'Add New Product Builder', 'woocommerce-product-builder' ),
				'edit'               => __( 'Edit', 'woocommerce-product-builder' ),
				'edit_item'          => __( 'Edit Product Builder', 'woocommerce-product-builder' ),
				'new_item'           => __( 'New Product Builder', 'woocommerce-product-builder' ),
				'view'               => __( 'View', 'woocommerce-product-builder' ),
				'view_item'          => __( 'View Product Builder', 'woocommerce-product-builder' ),
				'search_items'       => __( 'Search Product Builders', 'woocommerce-product-builder' ),
				'not_found'          => __( 'No Product Builders found', 'woocommerce-product-builder' ),
				'not_found_in_trash' => __( 'No Product Builders found in Trash', 'woocommerce-product-builder' )
			),

			'public'               => true,
			'menu_position'        => 2,
			'supports'             => array( 'title', 'thumbnail', 'revisions' ),
			'taxonomies'           => array( '' ),
			'menu_icon'            => 'dashicons-feedback',
			'has_archive'          => true,
			'register_meta_box_cb' => array( $this, 'add_product_builder_metaboxes' ),
			'rewrite'              => array( 'slug' => get_option( 'wpb2205_cpt_base' ), "with_front" => false )
		) );
		flush_rewrite_rules();
	}

	/**
	 * load Language translate
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-product-builder' );
		// Admin Locale
		if ( is_admin() ) {
			load_textdomain( 'woocommerce-product-builder', VI_WPRODUCTBUILDER_LANGUAGES . "woocommerce-product-builder-$locale.mo" );
		}

		// Global + Frontend Locale
		load_textdomain( 'woocommerce-product-builder', VI_WPRODUCTBUILDER_LANGUAGES . "woocommerce-product-builder-$locale.mo" );
		load_plugin_textdomain( 'woocommerce-product-builder', false, VI_WPRODUCTBUILDER_LANGUAGES );
	}

	public function woo_product_builder_load_permalinks() {
		if ( isset( $_POST['wpb2205_cpt_base'] ) ) {
			update_option( 'wpb2205_cpt_base', sanitize_title_with_dashes( $_POST['wpb2205_cpt_base'] ) );
		}

		// Add a settings field to the permalink page
		add_settings_field( 'wpb2205_cpt_base', __( 'Product builders' ), array(
			$this,
			'woo_product_builder_field_callback'
		), 'permalink', 'optional' );
	}

	public function woo_product_builder_field_callback() {
		$value = get_option( 'wpb2205_cpt_base' );
		echo '<input type="text" value="' . esc_attr( $value ) . '" name="wpb2205_cpt_base" id="wpb2205_cpt_base" class="regular-text" placeholder="product-builder" />';

	}

	/**
	 * Link to Settings
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	public function settings_link( $links ) {
		$settings_link = '<a href="edit.php?post_type=woo_product_builder&page=woocommerce-product-builder-setting" title="' . __( 'Settings', 'woocommerce-product-builder' ) . '">' . __( 'Settings', 'woocommerce-product-builder' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Enqueue scripts admin page
	 */
	public function admin_enqueue_script() {
		global $pagenow, $typenow;
		$page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';

		if ( is_admin() && $pagenow == 'post-new.php' or $pagenow == 'post.php' && $typenow == 'woo_product_builder' or $typenow == 'woo_product_builder' && $page == 'woocommerce-product-builder-setting' ) {
			global $wp_scripts;
			$scripts = $wp_scripts->registered;
			//			print_r($scripts);
			foreach ( $scripts as $k => $script ) {
				preg_match( '/^\/wp-/i', $script->src, $result );
				if ( count( array_filter( $result ) ) < 1 ) {
					if ( $script->handle != 'query-monitor' ) {
						wp_dequeue_script( $script->handle );
					}
				}
			}
			wp_enqueue_style( 'woocommerce-product-builder-form', VI_WPRODUCTBUILDER_CSS . 'form.min.css' );
			wp_enqueue_style( 'woocommerce-product-builder-table', VI_WPRODUCTBUILDER_CSS . 'table.min.css' );
			wp_enqueue_style( 'woocommerce-product-builder-dropdown', VI_WPRODUCTBUILDER_CSS . 'dropdown.min.css' );
			wp_enqueue_style( 'woocommerce-product-builder-checkbox', VI_WPRODUCTBUILDER_CSS . 'checkbox.min.css' );
			wp_enqueue_style( 'woocommerce-product-builder-menu', VI_WPRODUCTBUILDER_CSS . 'menu.min.css' );
			wp_enqueue_style( 'woocommerce-product-builder-segment', VI_WPRODUCTBUILDER_CSS . 'segment.min.css' );
			wp_enqueue_style( 'woocommerce-product-builder-button', VI_WPRODUCTBUILDER_CSS . 'button.min.css' );
			wp_enqueue_style( 'woocommerce-product-builder-transition', VI_WPRODUCTBUILDER_CSS . 'transition.min.css' );
			wp_enqueue_style( 'woocommerce-product-builder-tab', VI_WPRODUCTBUILDER_CSS . 'tab.css' );
			wp_enqueue_style( 'woocommerce-product-builder-input', VI_WPRODUCTBUILDER_CSS . 'input.min.css' );
			wp_enqueue_style( 'woocommerce-product-builder-icon', VI_WPRODUCTBUILDER_CSS . 'icon.min.css' );
			wp_enqueue_style( 'woocommerce-product-builder', VI_WPRODUCTBUILDER_CSS . 'woocommerce-product-builder-admin-product.css' );
			wp_enqueue_style( 'woocommerce-product-builder-select2', VI_WPRODUCTBUILDER_CSS . 'select2.min.css' );

			wp_enqueue_script( 'woocommerce-product-builder-transition', VI_WPRODUCTBUILDER_JS . 'transition.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'woocommerce-product-builder-checkbox', VI_WPRODUCTBUILDER_JS . 'checkbox.js', array( 'jquery' ) );
			wp_enqueue_script( 'woocommerce-product-builder-dropdown', VI_WPRODUCTBUILDER_JS . 'dropdown.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'woocommerce-product-builder-address', VI_WPRODUCTBUILDER_JS . 'jquery.address-1.6.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'woocommerce-product-builder-tab', VI_WPRODUCTBUILDER_JS . 'tab.js', array( 'jquery' ) );
			wp_enqueue_script( 'woocommerce-product-builder-select2', VI_WPRODUCTBUILDER_JS . 'select2.js', array( 'jquery' ) );
			/*Color picker*/
			wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array(
				'jquery-ui-draggable',
				'jquery-ui-slider',
				'jquery-touch-punch'
			), false, 1 );
			//			wp_enqueue_script( 'woocommerce-product-builder-depend-On', VI_WPRODUCTBUILDER_JS . 'dependsOn-1.0.2.min.js', array( 'jquery' ) );
			if ( $page == 'woocommerce-product-builder-setting' ) {
				wp_enqueue_script( 'woocommerce-product-builder-admin-product', VI_WPRODUCTBUILDER_JS . 'woocommerce-product-builder-admin.js', array( 'jquery' ) );

			} else {
				wp_enqueue_script( 'woocommerce-product-builder-admin-product', VI_WPRODUCTBUILDER_JS . 'woocommerce-product-builder-admin-product.js', array(
					'jquery',
					'jquery-ui-sortable'
				) );
			}
			$arg_scripts = array(
				'tab_title'                => esc_html__( 'Please fill your step title', 'woocommerce-product-builder' ),
				'tab_title_change'         => esc_html__( 'Please fill your tab title that you want to change.', 'woocommerce-product-builder' ),
				'tab_notice_remove'        => esc_html__( 'Do you want to remove this tab?', 'woocommerce-product-builder' ),
				'compatible_notice_remove' => esc_html__( 'Do you want to remove all compatible?', 'woocommerce-product-builder' ),
				'ajax_url'                 => esc_url( admin_url( 'admin-ajax.php' ) ),
			);
			wp_localize_script( 'woocommerce-product-builder-admin-product', '_woopb_params', $arg_scripts );
		}
	}


	/**
	 * Register metaboxes
	 */
	public function add_product_builder_metaboxes() {
		add_meta_box( 'vi_wpb_select_product', __( 'Products Configuration', 'woocommerce-product-builder' ), array(
			$this,
			'select_products_html'
		), 'woo_product_builder', 'normal', 'default' );
		add_meta_box( 'vi_wpb_side_bar', __( 'Garenal', 'woocommerce-product-builder' ), array(
			$this,
			'general_setting_html'
		), 'woo_product_builder', 'normal', 'default' );
		add_meta_box( 'vi_wpb_product_per_page', __( 'Products', 'woocommerce-product-builder' ), array(
			$this,
			'products_per_page_html'
		), 'woo_product_builder', 'normal', 'default' );
	}

	/**
	 * Register select product metaboxes
	 */
	public function select_products_html( $post ) {
		wp_nonce_field( 'woocommerce-product-builder_save', '_woopb_field_nonce' );
		?>
        <!--		Form search-->
        <div class="vi-ui form woopb-search-form">

            <div class="inline fields">
                <div class="three wide field">
                    <label for="<?php echo self::set_field( 'select_product' ) ?>"><?php esc_html_e( 'Select products', 'woocommerce-product-builder' ) ?></label>
                </div>
                <div class="three wide field">
                    <select class="vi-ui  dropdown woopb-type">
                        <option value="0"><?php esc_html_e( 'Categories', 'woocommerce-product-builder' ) ?></option>
                        <option value="1"><?php esc_html_e( 'Products', 'woocommerce-product-builder' ) ?></option>
                    </select>
                </div>
                <div class="one wide field">
                </div>
                <div class="eight wide field">
                    <div class="vi-ui action input">
                        <input class="wpb-search-field" type="text"
                               placeholder="<?php esc_attr_e( 'Fill your product title or category title', 'woocommerce-product-builder' ) ?>"/>
                        <span class="vi-ui button blue woopb-search-button"><?php esc_html_e( 'Search', 'woocommerce-product-builder' ) ?></span>
                    </div>
                </div>
            </div>

	        <?php do_action( 'woopb_after_woopb_search_form', $post ) ?>

            <script type="text/html" id="tmpl-woopb-item-template">
                <div class="woopb-item woopb-item-{{{data.item_class}}}" data-id="{{{data.id}}}">
                    <div class="woopb-item-top">{{{data.thumb}}}</div>
                    <div class="woopb-item-bottom">{{{data.name}}}</div>
                </div>
            </script>
            <div class="woopb-product-select">
                <div class="woopb-items">
			        <?php
			        $args  = array(
				        'taxonomy'   => 'product_cat',
				        'orderby'    => 'name',
				        'hide_empty' => true,
				        'number'     => 20
			        );
			        $cates = get_terms( $args );
			        if ( count( $cates ) ) {
				        foreach ( $cates as $cat ) { ?>
                            <div class="woopb-item woopb-item-category"
                                 data-id="<?php echo esc_attr( $cat->term_id ) ?>">
                                <div class="woopb-item-top"></div>
                                <div class="woopb-item-bottom"><?php echo esc_html( $cat->name ) ?></div>
                            </div>
				        <?php }
			        }
			        ?>
                </div>
            </div>
        </div>
		<?php
		$list_contents = self::get_field( 'list_content', array() );
		$tab_titles    = self::get_field( 'tab_title', array() );
		?>
        <div class="vi-ui form woopb-items-added">

            <div class="inline fields">
                <div class="five wide field woopb-tabs">
                    <div class="vi-ui vertical tabular menu woopb-sortable">
						<?php if ( count( $tab_titles ) ) {
							foreach ( $tab_titles as $k => $tab_title ) {
								?>
                                <a class="item <?php echo $k ? '' : 'active' ?>"
                                   data-tab="<?php echo esc_attr( $k ) ?>">
                                    <span class="woopb-remove"></span>
                                    <span class="woopb-edit"></span>
                                    <span class="woopb-tab-title"><?php echo esc_html( $tab_title ) ?></span>
                                    <input type="hidden" class="woopb-save-name" name="woopb-param[tab_title][<?php echo esc_attr( $k ) ?>]"
                                           value="<?php echo esc_attr( $tab_title ) ?>">
                                </a>
							<?php }
						} else { ?>
                            <a class="active item" data-tab="first">
                                <span class="woopb-tab-title"><?php esc_html_e( 'First step', 'woocommerce-product-builder' ) ?></span>
                                <span class="woopb-edit"></span>
                                <span class="woopb-remove"></span>
                                <input type="hidden" class="woopb-save-name" name="woopb-param[tab_title][first]" value="first">
                            </a>
						<?php } ?>
                    </div>
                </div>
                <div class="eleven wide field woopb-tabs-content">
					<?php if ( count( $list_contents ) ) {
						foreach ( $list_contents as $k => $list_content ) { ?>
                            <div class="vi-ui tab <?php echo $k ? '' : 'active' ?>"
                                 data-tab="<?php echo esc_attr( $k ) ?>">
								<?php

								if ( is_array( $list_content ) && count( $list_content ) ) {
									foreach ( $list_content as $item ) {

										$item_data     = array();
										$check_product = 0;
										if ( strpos( trim( $item ), 'cate_' ) === false ) {

											$item_data['title'] = get_post_field( 'post_title', $item );
											$item_data['id']    = get_post_field( 'ID', $item );

											$check_product = 1;
										} else {
											$term_id            = str_replace( 'cate_', '', trim( $item ) );
											$term_data          = get_term_by( 'id', $term_id, 'product_cat' );
											$item_data['title'] = $term_data->name;
											$item_data['id']    = $term_data->term_id;

										}

										?>
                                        <div class="woopb-item woopb-item-<?php echo $check_product ? 'product' : 'category' ?> <?php echo has_post_thumbnail( $item_data['id'] ) && $check_product ? 'woopb-img' : '' ?>"
                                             data-id="<?php echo esc_attr( $item_data['id'] ) ?>">
                                            <div class="woopb-item-top">
												<?php if ( $check_product ) {
													echo get_the_post_thumbnail( $item_data['id'] );
												} ?>
                                            </div>
                                            <div class="woopb-item-bottom"><?php echo esc_attr( $item_data['title'] ) ?></div>
                                            <input type="hidden"
                                                   name="woopb-param[list_content][<?php echo esc_attr( $k ) ?>][]"
                                                   value="<?php echo $check_product ? esc_attr( $item_data['id'] ) : 'cate_' . esc_attr( $item_data['id'] ) ?>">
                                        </div>
									<?php }

								}
								?>

                            </div>
						<?php }
					} else { ?>
                        <div class="vi-ui active tab" data-tab="first"></div>

					<?php } ?>
                </div>
            </div>
        </div>
        <p class="woopb-controls">
            <span class="vi-ui button green woopb-add-tab"><?php esc_html_e( 'Add New Step', 'woocommerce-product-builder' ) ?></span>
        </p>
		<?php
	}

	/**
	 * Set fields post meta
	 */
	public static function set_field( $field, $multi = false ) {
		if ( $field ) {
			if ( $multi ) {
				return 'woopb-param[' . $field . '][]';
			} else {
				return 'woopb-param[' . $field . ']';
			}

		} else {
			return '';
		}
	}

	/**
	 * Get fields post meta
	 */
	public static function get_field( $field, $default = '' ) {
		global $post;
		$params = get_post_meta( $post->ID, 'woopb-param', true );
		if ( isset( $params[ $field ] ) && $field ) {
			return $params[ $field ];
		} else {
			return $default;
		}
	}

	/**
	 * Register products per page metaboxes
	 */
	public function products_per_page_html() { ?>
        <table class="form-table vi-ui form">
            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo self::set_field( 'product_per_page' ) ?>"><?php esc_html_e( 'Product per page', 'woocommerce-product-builder' ) ?></label>
                </th>
                <td>
                    <input type="number" id="<?php echo self::set_field( 'product_per_page' ) ?>"
                           name="<?php echo self::set_field( 'product_per_page' ) ?>"
                           value="<?php echo self::get_field( 'product_per_page', 10 ) ?>" min="1"/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo self::set_field( 'product_compatible' ) ?>"><?php esc_html_e( 'Depend', 'woocommerce-product-builder' ) ?></label>
                </th>
                <td>
                    <div class="vi-ui checkbox toggle">
                        <input <?php checked( self::get_field( 'enable_compatible' ), 1 ) ?> type="checkbox"
                                                                                             id="<?php echo self::set_field( 'enable_compatible' ) ?>"
                                                                                             name="<?php echo self::set_field( 'enable_compatible' ) ?>"
                                                                                             value="1"/>
                        <label for="<?php echo self::set_field( 'enable_compatible' ) ?>"><?php esc_html_e( 'Enable', 'woocommerce-product-builder' ) ?></label>
                    </div>
                    <p class="description"><?php esc_html_e( 'Please save first to load all steps.', 'woocommerce-product-builder' ) ?></p>

                    <table class="vi-ui single line table green">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'STEP', 'woocommerce-product-builder' ) ?></th>
                            <th><?php esc_html_e( 'DEPENDING ON', 'woocommerce-product-builder' ) ?></th>
                        </tr>
                        </thead>
                        <tbody>
		                <?php $tabs = self::get_field( 'tab_title' );
		                $compatible = self::get_field( 'product_compatible', array() );
		                if ( is_array( $tabs ) && ! empty( $tabs ) ) {
			                foreach ( $tabs as $key => $title ) {
				                if ( ! $key ) {
					                continue;
				                }
				                $step_compatible = isset( $compatible[ $key ] ) ? $compatible[ $key ] : array();
				                ?>
                                <tr>
                                    <td><?php echo esc_html( $title ) ?></td>
                                    <td>
                                        <select class="woopb-compatible-field" multiple="multiple"
                                                name="<?php echo self::set_field( 'product_compatible' ) ?>[<?php echo esc_attr( $key ) ?>][]">
							                <?php foreach ( $tabs as $key_2 => $title_2 ) {
								                if ( $key <= $key_2 ) {
									                break;
								                }
								                ?>
                                                <option <?php selected( in_array( $key_2, $step_compatible ), 1 ) ?>
                                                        value="<?php echo esc_attr( $key_2 ) ?>"><?php echo esc_html( $title_2 ) ?></option>
							                <?php } ?>
                                        </select>
						                <?php do_action( 'woopb_product_options_setting', $key ); ?>
                                    </td>

                                </tr>
			                <?php }
		                } ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2">
								<span class="vi-ui button woopb-compatible-clear-all red">
								<?php esc_html_e( 'Clear all', 'woocommerce-product-builder' ) ?>
								</span>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        </table>
	<?php }

	/**
	 * General setting metaboxes
	 */
	public function general_setting_html() { ?>
        <table class="form-table vi-ui form">
            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo self::set_field( 'text_prefix' ); ?>"><?php esc_html_e( 'Text prefix each step', 'woocommerce-product-builder' ) ?></label>
                </th>
                <td>
                    <input type="text" name="<?php echo self::set_field( 'text_prefix' ); ?>"
                           id="<?php echo self::set_field( 'text_prefix' ); ?>"
                           value="<?php echo self::get_field( 'text_prefix', 'Step {step_number}' ); ?>">
                    <p class="description"><?php esc_html_e( '{step_number} - Number of current step', 'woocommerce-product-builder' ) ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo self::set_field( 'description' ); ?>"><?php esc_html_e( 'Description', 'woocommerce-product-builder' ) ?></label>
                </th>
                <td>
                    <input type="text" name="<?php echo self::set_field( 'description' ); ?>"
                           id="<?php echo self::set_field( 'description' ); ?>"
                           value="<?php echo self::get_field( 'description' ); ?>">
                    <!--                    <p class="description">--><?php //esc_html_e( '{step_number} - Number of current step', 'woocommerce-product-builder' ) ?><!--</p>-->
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo self::set_field( 'child_cat' ); ?>"><?php esc_html_e( 'Children categories', 'woocommerce-product-builder' ) ?></label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox checked">
                        <input type="checkbox" name="<?php echo self::set_field( 'child_cat' ); ?>"
                               id="<?php echo self::set_field( 'child_cat' ); ?>" <?php checked( self::get_field( 'child_cat' ), 1 ); ?>
                               value="1">
                        <label for="<?php echo self::set_field( 'child_cat' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-product-builder' ) ?></label>
                    </div>
                    <p class="description"><?php esc_html_e( 'Get all product in children categories', 'woocommerce-product-builder' ) ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo self::set_field( 'enable_multi_select' ); ?>"><?php esc_html_e( 'Add many products in step', 'woocommerce-product-builder' ) ?></label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox checked">
                        <input type="checkbox" name="<?php echo self::set_field( 'enable_multi_select' ); ?>"
                               id="<?php echo self::set_field( 'enable_multi_select' ); ?>" <?php checked( self::get_field( 'enable_multi_select' ), 1 ); ?>
                               value="1">
                        <label for="<?php echo self::set_field( 'enable_multi_select' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-product-builder' ) ?></label>
                    </div>
                    <p class="description"><?php esc_html_e( 'Select multiple products in a step', 'woocommerce-product-builder' ) ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo self::set_field( 'enable_quantity' ); ?>"><?php esc_html_e( 'Quantity field', 'woocommerce-product-builder' ) ?></label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox checked">
                        <input type="checkbox" name="<?php echo self::set_field( 'enable_quantity' ); ?>"
                               id="<?php echo self::set_field( 'enable_quantity' ); ?>" <?php checked( self::get_field( 'enable_quantity' ), 1 ); ?>
                               value="1">
                        <label for="<?php echo self::set_field( 'enable_quantity' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-product-builder' ) ?></label>
                    </div>
                    <p class="description"><?php esc_html_e( 'Default quantity is 1. Please enable if you want add more.', 'woocommerce-product-builder' ) ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo self::set_field( 'enable_preview' ); ?>"><?php esc_html_e( 'Preview button', 'woocommerce-product-builder' ) ?></label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox checked">
                        <input type="checkbox" name="<?php echo self::set_field( 'enable_preview' ); ?>"
                               id="<?php echo self::set_field( 'enable_preview' ); ?>" <?php checked( self::get_field( 'enable_preview' ), 1 ); ?>
                               value="1">
                        <label for="<?php echo self::set_field( 'enable_preview' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-product-builder' ) ?></label>
                    </div>
                    <p class="description"><?php esc_html_e( 'Display preview button when you have not reach to the final step', 'woocommerce-product-builder' ) ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo self::set_field( 'enable_preview_always' ); ?>"><?php esc_html_e( 'Preview button always show ', 'woocommerce-product-builder' ) ?></label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox checked">
                        <input type="checkbox" name="<?php echo self::set_field( 'enable_preview_always' ); ?>"
                               id="<?php echo self::set_field( 'enable_preview_always' ); ?>" <?php checked( self::get_field( 'enable_preview_always' ), 1 ); ?>
                               value="1">
                        <label for="<?php echo self::set_field( 'enable_preview_always' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-product-builder' ) ?></label>
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo self::set_field( 'remove_all_button' ); ?>"><?php esc_html_e( 'Remove all button ', 'woocommerce-product-builder' ) ?></label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox checked">
                        <input type="checkbox" name="<?php echo self::set_field( 'remove_all_button' ); ?>"
                               id="<?php echo self::set_field( 'remove_all_button' ); ?>" <?php checked( self::get_field( 'remove_all_button' ), 1 ); ?>
                               value="1">
                        <label for="<?php echo self::set_field( 'remove_all_button' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-product-builder' ) ?></label>
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo self::set_field( 'search_product_form' ); ?>"><?php esc_html_e( 'Search product form', 'woocommerce-product-builder' ) ?></label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox checked">
                        <input type="checkbox" name="<?php echo self::set_field( 'search_product_form' ); ?>"
                               id="<?php echo self::set_field( 'search_product_form' ); ?>" <?php checked( self::get_field( 'search_product_form' ), 1 ); ?>
                               value="1">
                        <label for="<?php echo self::set_field( 'search_product_form' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-product-builder' ) ?></label>
                    </div>
                    <p class="description"><?php esc_html_e( 'Display search products form by ajax', 'woocommerce-product-builder' ) ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo self::set_field( 'require_product' ); ?>"><?php esc_html_e( 'Product is required each step', 'woocommerce-product-builder' ) ?></label>
                </th>
                <td>
                    <div class="vi-ui toggle checkbox checked">
                        <input type="checkbox" name="<?php echo self::set_field( 'require_product' ); ?>"
                               id="<?php echo self::set_field( 'require_product' ); ?>" <?php checked( self::get_field( 'require_product' ), 1 ); ?>
                               value="1">
                        <label for="<?php echo self::set_field( 'require_product' ); ?>"><?php esc_html_e( 'Enable', 'woocommerce-product-builder' ) ?></label>
                    </div>
                    <p class="description"><?php esc_html_e( '', 'woocommerce-product-builder' ) ?></p>
                </td>
            </tr>
        </table>
	<?php }


	/**
	 * Save metaboxes
	 */
	public function save_post_metadata( $post_id ) {
		// verify nonce
		if ( ! isset( $_POST['_woopb_field_nonce'] ) || ! isset( $_POST['woopb-param'] ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return esc_html__( 'Cannot edit page', 'woocommerce-product-builder' );
		}

		$data = $_POST['woopb-param'];
		array_walk_recursive( $data, 'sanitize_text_field' );
		$temp = array();

		if ( is_array( $data['tab_title'] ) && ! empty( $data['tab_title'] ) ) {

			foreach ( $data['tab_title'] as $key => $title ) {
				if ( ! empty( $data['list_content'][ $key ] ) ) {
					$temp[ $key ] = $data['list_content'][ $key ];
				} else {
					unset( $data['tab_title'][ $key ] );
				}
			}

			$data['tab_title']    = array_values( $data['tab_title'] );
			$data['list_content'] = array_values( $temp );
		}

		update_post_meta( $post_id, 'woopb-param', $data );
	}

	public function define_shortcode_columns( $columns ) {
		unset( $columns['date'] );
		$columns['shortcode'] = esc_html__( ' Shortcode', 'woocommerce-product-builder' );
		$columns['date']      = esc_html__( ' Date', 'woocommerce-product-builder' );

		return $columns;
	}

	public function shortcode_columns( $column, $id ) {
		if ( $column == 'shortcode' ) {
			echo "<input class='woopb-shortcode' type='text' value='[woocommerce_product_builder id={$id}]' readonly onclick='this.select();document.execCommand(\"copy\");'>";
		}
	}

	public function enqueue_style() {
		if ( get_current_screen()->id == 'edit-woo_product_builder' ) {
			wp_register_style( 'woopb-inline-style', false );
			wp_enqueue_style( 'woopb-inline-style' );
			$css = ".woopb-shortcode{width:300px;}";
			wp_add_inline_style( 'woopb-inline-style', $css );
		}
	}

	public function show_shortcode() {
		global $post;

		if ( get_current_screen()->id !== 'woo_product_builder' ) {
			return;
		}
		?>
        <div class="woopb-shortcode-group">
            <strong>
				<?php esc_html_e( 'Shortcode:', 'woocommerce-product-builder' ); ?>
            </strong>
            <input class='woopb-shortcode' type='text' readonly
                   value='[woocommerce_product_builder id=<?php echo esc_attr( $post->ID ) ?>]' onclick='this.select();document.execCommand("copy");'>
            <span>
                <?php esc_html_e( '(Note: Use one shortcode per page only)', 'woocommerce-product-builder' ); ?>
            </span>
        </div>
		<?php
	}
}