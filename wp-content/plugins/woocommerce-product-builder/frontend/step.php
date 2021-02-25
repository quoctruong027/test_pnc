<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class VI_WPRODUCTBUILDER_FrontEnd_Step {
	protected $data;

	public function __construct() {
		$this->settings = new VI_WPRODUCTBUILDER_Data();
		/*Add Script*/
		add_action( 'wp_enqueue_scripts', array( $this, 'init_scripts' ) );
		/*Single template*/
		add_action( 'woocommerce_product_builder_single_content', array( $this, 'product_builder_content_single_page' ), 11 );
		add_action( 'woocommerce_product_builder_single_product_content_before', array( $this, 'sort_by' ) );
		add_action( 'woocommerce_product_builder_single_product_content_before', array( $this, 'require_notice' ) );
		add_action( 'woocommerce_product_builder_before_single_top', array( $this, 'description' ), 1 );
		add_action( 'woocommerce_product_builder_before_single_top', array( $this, 'step_html' ), 30 );
		add_action( 'woocommerce_product_builder_before_single_top', array( $this, 'step_title' ), 9 );
		add_action( 'woocommerce_product_builder_single_bottom', array( $this, 'woocommerce_product_builder_single_product_content_pagination' ), 10, 2 );
		/*Form send email to friend of review page*/
		if ( $this->settings->enable_email() ) {
			add_action( 'wp_footer', array( $this, 'share_popup_form' ) );
		}

		/*Product html*/
		add_action( 'woocommerce_product_builder_single_product_content', array( $this, 'product_thumb' ), 10 );
		add_action( 'woocommerce_product_builder_single_product_content', array( $this, 'product_title' ), 20 );
		add_action( 'woocommerce_product_builder_single_product_content', array( $this, 'product_price' ), 30, 2 );
		add_action( 'woocommerce_product_builder_single_product_content', array( $this, 'product_description' ), 35 );
		add_action( 'woocommerce_product_builder_single_product_content', array( $this, 'add_to_cart' ), 40 );
		add_action( 'woocommerce_product_builder_simple_add_to_cart', array( $this, 'simple_add_to_cart' ), 40 );
		add_action( 'woocommerce_product_builder_variable_add_to_cart', array( $this, 'variable_add_to_cart' ), 40 );
		add_action( 'woocommerce_product_builder_single_variation', array( $this, 'woocommerce_single_variation' ), 10 );
		add_action( 'woocommerce_product_builder_single_variation', array( $this, 'woocommerce_product_builder_single_variation' ), 20 );
		add_action( 'woocommerce_product_builder_quantity_field', array( $this, 'quantity_field' ), 10, 2 );

		/*Add Query var*/
		add_action( 'pre_get_posts', array( $this, 'add_vars' ) );
	}


	/*
	 *
	 */
	public function quantity_field( $product, $post_id ) {
		$enable_quantity = $this->get_data( $post_id, 'enable_quantity' );
		if ( $enable_quantity ) {
			woocommerce_quantity_input( array(
				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
				'input_value' => isset( $_POST['quantity'] ) ? 1 : $product->get_min_purchase_quantity(),
			), $product );
		}
	}

	public function share_popup_form() {
		global $wp_query;
		if ( isset( $wp_query->query_vars['woopb_preview'] ) ) {
			wc_get_template( 'content-product-builder-preview-popup.php', array( 'share_link' => $this->settings->get_share_link() ), '', VI_WPRODUCTBUILDER_TEMPLATES );
		}
	}

	/**
	 *
	 */
	public function woocommerce_product_builder_single_variation( $post_id ) {
		wc_get_template( 'single/variation-add-to-cart-button.php', array( 'post_id' => $post_id ), '', VI_WPRODUCTBUILDER_TEMPLATES );

	}

	/**
	 *
	 */
	public function woocommerce_single_variation() {
		echo '<div class="woocommerce-product-builder-variation single_variation"></div>';
	}

	public function step_title( $id ) {
		global $post;
		$post_id = is_woopb_shortcode() ? $id : $post->ID;

		/*Process Navigation button*/
		$step_id = get_query_var( 'step' );
		$tabs    = $this->get_data( $post_id, 'tab_title' );
		if ( ! is_array( $tabs ) ) {
			return;
		}
		$count_tabs = count( $tabs );
		$step_id    = $step_id ? $step_id : 1;
		$step_prev  = $step_next = 0;
		if ( $count_tabs > $step_id ) {
			$step_next = $step_id + 1;
			if ( $step_id > 1 ) {
				$step_prev = $step_id - 1;
			}
		} else {
			if ( $step_id > 1 ) {
				$step_prev = $step_id - 1;
			}
		}
		$review_url   = add_query_arg( array( 'woopb_preview' => 1 ), get_the_permalink() );
		$next_url     = add_query_arg( array( 'step' => $step_next ), get_the_permalink() );
		$previous_url = add_query_arg( array( 'step' => $step_prev ), get_the_permalink() );
		?>
        <div class="woopb-heading-navigation">
            <div class="woopb-heading">
				<?php $step_text = $this->get_data( $post_id, 'text_prefix' );
				if ( $step_text ) {
					echo '<span class="woopb-heading-step-prefix">' . esc_html( str_replace( '{step_number}', $step_id, $step_text ) ) . '</span>';
				}
				echo '<span class="woopb-heading-step-title">' . esc_html( $tabs[ $step_id - 1 ] ) . '</span>' ?>
            </div>

            <div class="woopb-navigation">
				<?php if ( $step_prev ) { ?>
                    <div class="woopb-navigation-previous">
                        <a href="<?php echo esc_url( $previous_url ) ?>"
                           class="woopb-link"><?php echo esc_html__( 'Previous', 'woocommerce-product-builder' ) ?></a>
                    </div>
				<?php } ?>
				<?php if ( $step_next ) { ?>
                    <div class="woopb-navigation-next">
                        <a href="<?php echo esc_url( $next_url ) ?>"
                           class="woopb-link"><?php echo esc_html__( 'Next', 'woocommerce-product-builder' ) ?></a>
                    </div>
				<?php }

				/*Check all steps that producted are added*/
				if ( ( ! $step_next && $this->get_data( $post_id, 'enable_preview' ) ) || $this->get_data( $post_id, 'enable_preview_always' ) ) { ?>
                    <div class="woopb-navigation-preview">
                        <a href="<?php echo esc_url( $review_url ) ?>"
                           class="woopb-link"><?php echo esc_html__( 'Preview', 'woocommerce-product-builder' ) ?></a>
                    </div>

					<?php
				}
				if ( $this->settings->has_step_added( $post_id ) ) {
					?>
                    <form method="POST" action="<?php echo wc_get_cart_url() ?>" class="woopb-form-cart-now">
						<?php wp_nonce_field( '_woopb_add_to_woocommerce', '_nonce' ) ?>
                        <input type="hidden" name="woopb_id" value="<?php echo esc_attr( $post_id ) ?>"/>
						<?php
						$btn = " <button class='woopb-button woopb-button-primary'>" . __( 'Add to cart', 'woocommerce-product-builder' ) . "</button>";
						printf( apply_filters( 'woopb_add_to_cart_button', $btn ) );
						?>
                    </form>
				<?php } ?>
            </div>
        </div>
	<?php }

	/**
	 * Sort by
	 */
	public function sort_by() {
		/*Process sort by*/
		global $post;
		$post_id = is_woopb_shortcode() ? VI_WPRODUCTBUILDER_FrontEnd_Shortcode::$woopb_id : $post->ID;

		$current     = get_query_var( 'sort_by' );
		$step        = get_query_var( 'step' );
		$search_form = $flex_class = '';
		if ( $this->get_data( $post_id, 'search_product_form' ) ) {
			$flex_class = "style='display:flex;'";
			ob_start();
			?>
            <div class="woopb-search-products-form">
                <input type="text" class="woopb-search-products-input" data-step="<?php echo esc_attr( $step ); ?>"
                       data-post="<?php echo esc_attr( $post_id ); ?>"
                       placeholder="<?php esc_html_e( 'Search products', 'woocommerce-product-builder' ); ?>">
                <div class="woopb-spinner">
                    <div class="woopb-spinner-inner woopb-hidden">
                    </div>
                </div>
            </div>
			<?php
			$search_form = ob_get_clean();
		}
		?>
        <div class="woopb-sort-by" <?php echo $flex_class ?>>
			<?php echo $search_form; ?>
            <div class="woopb-sort-by-inner">

				<?php $sort_by_events = apply_filters( 'woopb_sort_by_events', array(
//					''           => esc_html__( 'Default', 'woocommerce-product-builder' ),
					'title_az'   => esc_html__( 'Title A-Z', 'woocommerce-product-builder' ),
					'title_za'   => esc_html__( 'Title Z-A', 'woocommerce-product-builder' ),
					'price_low'  => esc_html__( 'Price low to high', 'woocommerce-product-builder' ),
					'price_high' => esc_html__( 'Price high to low', 'woocommerce-product-builder' ),
				) ); ?>
                <select class="woopb-sort-by-button woopb-button">
					<?php
					foreach ( $sort_by_events as $k => $sort_by_event ) { ?>
                        <option <?php selected( $current, $k ) ?>
                                value="<?php echo add_query_arg( array( 'sort_by' => $k ) ) ?>"><?php echo $sort_by_event ?></option>
					<?php } ?>
                </select>
            </div>
        </div>

	<?php }

	public function require_notice() {
		if ( isset( $_GET['notice'] ) && $_GET['notice'] == 1 ) {
			?>
            <div class="woopb-product-require-notice">
                <p><?php esc_html_e( 'Please select product for this step', 'woocommerce-product-builder' ); ?></p>
            </div>
			<?php
		}

	}

	/**
	 * Product Description
	 */
	public function product_description() {
		wc_get_template( 'single/product-short-description.php', '', '', VI_WPRODUCTBUILDER_TEMPLATES );
	}

	/**
	 * Add Query Var
	 *
	 * @param $wp_query
	 */
	function add_vars( &$wp_query ) {
		$step_id                               = filter_input( INPUT_GET, 'step', FILTER_SANITIZE_NUMBER_INT );
		$wp_query->query_vars['step']          = $step_id ? $step_id : 1;
		$page                                  = filter_input( INPUT_GET, 'ppaged', FILTER_SANITIZE_NUMBER_INT );
		$wp_query->query_vars['ppaged']        = $page ? $page : 1;
		$wp_query->query_vars['max_page']      = $step_id ? $step_id : 1;
		$wp_query->query_vars['rating_filter'] = filter_input( INPUT_GET, 'rating_filter', FILTER_SANITIZE_STRING );
		$wp_query->query_vars['sort_by']       = filter_input( INPUT_GET, 'sort_by', FILTER_SANITIZE_STRING );
		$wp_query->query_vars['name_filter']   = filter_input( INPUT_GET, 'name_filter', FILTER_SANITIZE_STRING );
	}

	/**
	 * Show step
	 */
	public function step_html( $id ) {
		global $post;
//		$post_id = $post->ID;
		$post_id = is_woopb_shortcode() ? $id : $post->ID;
		/*Get current step*/
		$step_titles = $this->get_data( $post_id, 'tab_title', array() );
		$step_id     = get_query_var( 'step' );
		$step_id     = $step_id ? $step_id : 1;
		?>
        <div class="woopb-steps">
			<?php if ( count( $step_titles ) ) {

				$total_price = 0;

				foreach ( $step_titles as $k => $step_title ) {
					$products_added = $this->settings->get_products_added( $post_id, $k + 1 );
					$arg            = array(
						'step' => $k + 1,
					);

					foreach ( array( 'min_price', 'max_price', 'name_filter', 'rating_filter' ) as $item ) {
						if ( $value = get_query_var( $item ) ) {
							$arg[ $item ] = $value;
						}
					}

					$current = $k == ( $step_id - 1 ) ? 1 : 0;
					?>
                    <div class="woopb-step-heading <?php echo $current ? 'woopb-step-heading-active' : ''; ?>">
                        <a href="<?php echo add_query_arg( $arg, get_the_permalink() ) ?>" class="woopb-step-link">
							<?php echo esc_html( $step_title ) ?>
                        </a>
                    </div>
					<?php if ( count( $products_added ) ) { ?>
                        <div class="woopb-step woopb-step-<?php echo esc_attr( $k ) ?> <?php echo $current ? 'woopb-step-active' : ''; ?>">
                            <div class="woopb-step-products-added">
								<?php foreach ( $products_added as $p_id => $quantity ) { ?>
                                    <div class="woopb-step-products-added-wrapper">
										<?php $product = wc_get_product( $p_id );
										$sub_price     = $product->get_price() * $quantity;
										$total_price   += $sub_price;
										if ( has_post_thumbnail( $p_id ) ) {
											?>
                                            <div class="woopb-step-product-thumb">
												<?php echo get_the_post_thumbnail( $p_id, 'shop_catalog' ) ?>
                                            </div>
										<?php } ?>
                                        <div class="woopb-step-product-added">
											<?php echo '<span class="woopb-step-product-added-title">' . $product->get_name() . '</span> x ' . $quantity ?>
                                            <div class="woopb-step-product-added-price"><?php echo apply_filters( 'woopb_added_price', wc_price( $sub_price ) ) ?></div>
											<?php
											$arg_remove = array(
												'stepp'      => ( $k + 1 ),
												'product_id' => $p_id,
												'post_id'    => $post_id
											);
											?>
                                            <a class="woopb-step-product-added-remove"
                                               href="<?php echo wp_nonce_url( add_query_arg( $arg_remove ), '_woopb_remove_product_step', '_nonce' ) ?>"><?php echo esc_html__( 'Remove', 'woocommerce-product-builder' ) ?></a>
                                        </div>
                                    </div>
								<?php } ?>
                            </div>
                        </div>
					<?php } ?>
				<?php } ?>
                <div class="woopb-step woopb-step-total">
					<?php echo esc_html__( 'Total:', 'woocommerce-product-builder' ) . ' ' . wc_price( $total_price ); ?>
                </div>
				<?php if ( $this->get_data( $post_id, 'remove_all_button' ) ) { ?>
                    <div class="woopb-step woopb-step-remove">
                        <a class="woopb-step-product-added-remove" href="<?php
						echo wp_nonce_url( add_query_arg( array( 'remove' => 'all', 'step' => 1, 'post_id' => $post_id ) ), '_woopb_remove_all_product_step', '_nonce' ) ?>">
							<?php echo esc_html__( 'Remove all', 'woocommerce-product-builder' ) ?>
                        </a>
                    </div>
				<?php }
			} ?>
        </div>
	<?php }

	/*
	 * Pagination
	 */
	public function woocommerce_product_builder_single_product_content_pagination( $products, $max_page ) {

		$step         = get_query_var( 'step' );
		$current_page = get_query_var( 'ppaged' );
		$paged        = $current_page ? $current_page : 1;
		if ( $max_page > 1 ) {
			?>
            <div class="woopb-products-pagination">
				<?php
				if ( $paged > 2 ) {
					$i   = 1;
					$arg = array(
						'ppaged' => $i,
						'step'   => $step
					);
					?>
                    <div class="woopb-page">
                        <a href="<?php echo add_query_arg( $arg ) ?>"><?php echo esc_html( $i ) ?></a>
                    </div>
					<?php
					if ( $paged - 2 > 1 ) {
						?>
                        <div class="woopb-page">
                            <span>...</span>
                        </div>
						<?php
					}
				}
				if ( $paged - 1 > 0 ) {
					$i   = $paged - 1;
					$arg = array(
						'ppaged' => $i,
						'step'   => $step
					);
					?>
                    <div class="woopb-page">
                        <a href="<?php echo add_query_arg( $arg ) ?>"><?php echo esc_html( $i ) ?></a>
                    </div>
					<?php
				}
				?>
                <div class="woopb-page woopb-active">
                    <span><?php echo esc_html( $paged ) ?></span>
                </div>
				<?php
				if ( $paged + 1 < $max_page ) {
					$i   = $paged + 1;
					$arg = array(
						'ppaged' => $i,
						'step'   => $step
					);
					?>
                    <div class="woopb-page">
                        <a href="<?php echo add_query_arg( $arg ) ?>"><?php echo esc_html( $i ) ?></a>
                    </div>
					<?php
				}
				if ( $paged < $max_page ) {
					if ( $paged < $max_page - 2 ) {
						?>
                        <div class="woopb-page">
                            <span>...</span>
                        </div>
						<?php
					}
					$i   = $max_page;
					$arg = array(
						'ppaged' => $i,
						'step'   => $step
					);
					?>
                    <div class="woopb-page">
                        <a href="<?php echo add_query_arg( $arg ) ?>"><?php echo esc_html( $i ) ?></a>
                    </div>
					<?php
				}
				?>
            </div>
			<?php
		}
	}

	/**
	 * Product variable
	 */
	public function variable_add_to_cart( $post_id ) {
		global $product;

		// Enqueue variation scripts.
		wp_enqueue_script( 'wc-add-to-cart-variation' );

		// Get Available variations?
		$get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

		// Load the template.
		wc_get_template( 'single/add-to-cart-variable.php', array(
			'available_variations' => $get_variations ? $product->get_available_variations() : false,
			'attributes'           => $product->get_variation_attributes(),
			'selected_attributes'  => $product->get_default_attributes(),
			'post_id'              => $post_id
		), '', VI_WPRODUCTBUILDER_TEMPLATES );
	}

	public function simple_add_to_cart( $post_id ) {
		wc_get_template( 'single/add-to-cart-simple.php', array( 'post_id' => $post_id ), '', VI_WPRODUCTBUILDER_TEMPLATES );
	}

	public function add_to_cart( $post_id ) {
		$enable_multi_select = $this->get_data( $post_id, 'enable_multi_select' );
		$step_id             = get_query_var( 'step' );
		$step_id             = $step_id ? $step_id : 1;
		$product_added       = $this->settings->get_products_added( $post_id, $step_id );
		if ( $enable_multi_select || count( $product_added ) < 1 ) {
			$allow_add_to_cart = 1;
		} else {
			$allow_add_to_cart = 0;
		}
		if ( $allow_add_to_cart ) {
			global $product;
			do_action( 'woocommerce_product_builder_' . $product->get_type() . '_add_to_cart', $post_id );
		}
		/*Create close div of right content*/
		echo '</div>';
	}

	/**
	 * Init Script
	 */
	public function init_scripts() {

		if ( $this->settings->get_button_icon() ) {
			wp_register_style( 'woocommerce-product-builder-icon', VI_WPRODUCTBUILDER_CSS . 'woocommerce-product-builder-icon.css', array(), VI_WPRODUCTBUILDER_VERSION );
		}
		if ( WP_DEBUG ) {
			wp_register_style( 'woocommerce-product-builder', VI_WPRODUCTBUILDER_CSS . 'woocommerce-product-builder.css', array(), VI_WPRODUCTBUILDER_VERSION );
		} else {
			wp_register_style( 'woocommerce-product-builder', VI_WPRODUCTBUILDER_CSS . 'woocommerce-product-builder.min.css', array(), VI_WPRODUCTBUILDER_VERSION );
		}
		if ( is_rtl() ) {
			wp_register_style( 'woocommerce-product-builder-rtl', VI_WPRODUCTBUILDER_CSS . 'woocommerce-product-builder-rtl.css', array(), VI_WPRODUCTBUILDER_VERSION );
		}

		/*Add script*/
		if ( WP_DEBUG ) {
			wp_register_script( 'woocommerce-product-builder', VI_WPRODUCTBUILDER_JS . 'woocommerce-product-builder.js', array( 'jquery' ), VI_WPRODUCTBUILDER_VERSION );
			wp_register_script( 'woocommerce-product-builder-search', VI_WPRODUCTBUILDER_JS . 'woocommerce-product-builder-search.js', array(
				'jquery',
				'wc-add-to-cart-variation'
			), VI_WPRODUCTBUILDER_VERSION );
		} else {
			wp_register_script( 'woocommerce-product-builder', VI_WPRODUCTBUILDER_JS . 'woocommerce-product-builder.min.js', array( 'jquery' ), VI_WPRODUCTBUILDER_VERSION );
			wp_register_script( 'woocommerce-product-builder-search', VI_WPRODUCTBUILDER_JS . 'woocommerce-product-builder-search.min.js', array(
				'jquery',
				'wc-add-to-cart-variation'
			), VI_WPRODUCTBUILDER_VERSION );
		}

		global $post;
		if ( $post && $post->post_type == 'woo_product_builder' ) {
			$this->settings->enqueue_scripts();
		}
	}


	/**
	 * Product Title
	 */
	public function product_price( $pb_id, $min_id ) {
		ob_start();
		wc_get_template( 'single/product-price.php', '', '', VI_WPRODUCTBUILDER_TEMPLATES );
		$price = apply_filters( 'woopb_price_each_step', ob_get_clean(), $pb_id, $min_id );

		echo $price;
	}

	/**
	 * Product Title
	 */
	public function product_thumb() {
		wc_get_template( 'single/product-image.php', '', '', VI_WPRODUCTBUILDER_TEMPLATES );
	}

	/**
	 * Product Title
	 */
	public function product_title() {
		/*Create div before title*/
		echo '<div class="woopb-product-right">';
		wc_get_template( 'single/product-title.php', '', '', VI_WPRODUCTBUILDER_TEMPLATES ); ?>
	<?php }


	/**
	 * Get Product Ids
	 */
	public function product_builder_content_single_page( $id ) {
		global $post, $wp_query;
		$post_id = is_woopb_shortcode() ? $id : $post->ID;

		$data     = $this->settings->get_product_filters( $post_id );
		$max_page = 1;
		$products = array();
		if ( isset( $wp_query->query_vars['woopb_preview'] ) ) {
			$products = $this->settings->get_products_added( $post_id );
			$settings = $this->settings;
			if ( is_array( $products ) && count( $products ) ) {
				wc_get_template( 'content-product-builder-preview.php', array(
					'id'       => $id,
					'products' => $products,
					'settings' => $settings
				), '', VI_WPRODUCTBUILDER_TEMPLATES );
			} else {
				if ( $data ) {
					$products = $data->posts;
					$max_page = $data->max_num_pages;
				}
				wc_get_template( 'content-product-builder-single.php', array(
					'id'       => $id,
					'products' => $products,
					'max_page' => $max_page
				), '', VI_WPRODUCTBUILDER_TEMPLATES );
			}
		} else {
			if ( $data ) {
				$products = $data->posts;
				$max_page = $data->max_num_pages;
			}
			wc_get_template( 'content-product-builder-single.php', array(
				'id'       => $id,
				'products' => $products,
				'max_page' => $max_page
			), '', VI_WPRODUCTBUILDER_TEMPLATES );
		}

	}

	/**
	 * Get Post Meta
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	private function get_data( $post_id, $field, $default = '' ) {
		if ( isset( $this->data[ $post_id ] ) && $this->data[ $post_id ] ) {
			$params = $this->data[ $post_id ];
		} else {
			$this->data[ $post_id ] = get_post_meta( $post_id, 'woopb-param', true );
			$params                 = $this->data[ $post_id ];
		}

		if ( isset( $params[ $field ] ) && $field ) {
			return $params[ $field ];
		} else {
			return $default;
		}
	}

	public function description() {
		global $post;
		echo sprintf( "<div class='woopb-description'>%s</div>", esc_html( $this->settings->get_data( $post->ID, 'description' ) ) );
	}
}