<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class VI_WPRODUCTBUILDER_FrontEnd_Search {
	public $settings;

	public function __construct() {
		add_action( 'wp_ajax_woopb_search_product_in_step', array( $this, 'search_products' ) );
		add_action( 'wp_ajax_nopriv_woopb_search_product_in_step', array( $this, 'search_products' ) );
	}

	public function search_products() {
		$this->settings = new VI_WPRODUCTBUILDER_Data();
		$post_id        = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
		$step           = isset( $_POST['step'] ) ? sanitize_text_field( $_POST['step'] ) : '';
		$search         = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
		$form_action    = isset( $_POST['form_action'] ) ? sanitize_text_field( $_POST['form_action'] ) : '';
		$referer        = isset( $_POST['referer'] ) ? sanitize_text_field( $_POST['referer'] ) : '';
		$nonce          = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
		$out            = array();
		if ( $post_id && $step ) {
			$product_ids = $this->get_products( $post_id, $step );
			$arg         = array(
				'limit'   => - 1,
				'status'  => 'publish',
				'include' => $product_ids,
				's'       => $search
			);
			$products    = wc_get_products( $arg );

			ob_start();

			if ( count( $products ) ) {
				foreach ( $products as $product_id ) {
					$product = wc_get_product( $product_id );
					?>
                    <div class="woopb-product">
                        <div class="woopb-product-left">
                            <div class="woopb-product-image">
								<?php echo $product->get_image( 'shop_catalog' ); ?>
                            </div>
                        </div>
                        <div class="woopb-product-right">
                            <div class="woopb-product-title">
                                <a href="<?php echo esc_url( $product->get_permalink() ) ?>"><?php echo esc_html( $product->get_name() ) ?></a>
                            </div>

                            <div class="woopb-product-price">
								<?php echo $product->get_price_html() ?>
                            </div>
                            <div class="woopb-product-short-description">
								<?php echo $product->get_short_description() ?>
                            </div>
							<?php if ( $product->is_type( 'simple' ) ) { ?>
                                <form class="cart" action="<?php echo $form_action ?>" method="post">
                                    <input type="hidden" id="_nonce" name="_nonce" value="<?php echo $nonce ?>">
                                    <input type="hidden" name="_wp_http_referer" value="<?php echo $referer ?>">
									<?php
									//									wp_nonce_field( '_woopb_add_to_cart', '_nonce' );

									$this->quantity_field( $product, $post_id );
									?>
                                    <input type="hidden" name="woopb_id" value="<?php echo $post_id ?>">
                                    <button type="submit" name="woopb-add-to-cart" value="<?php echo $product->get_id() ?>"
                                            class="single_add_to_cart_button button alt"><?php esc_html_e( 'Select', 'woocommerce-product-builder' ); ?>
                                    </button>

                                </form>
								<?php
							} elseif ( $product->is_type( 'variable' ) ) {
								$get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

								$available_variations = $get_variations ? $product->get_available_variations() : false;
								$attributes           = $product->get_variation_attributes();
								$selected_attributes  = $product->get_default_attributes();
								$attribute_keys       = array_keys( $attributes );
								?>
                                <form class="variations_form cart" action="<?php echo $form_action ?>"
                                      method="post" enctype='multipart/form-data'
                                      data-product_id="<?php echo absint( $product->get_id() ); ?>"
                                      data-product_variations="<?php echo htmlspecialchars( wp_json_encode( $available_variations ) ) ?>">
									<?php
									if ( empty( $available_variations ) && false !== $available_variations ) : ?>
                                        <p class="stock out-of-stock"><?php _e( 'This product is currently out of stock and unavailable.', 'woocommerce' ); ?></p>
									<?php else : ?>
                                        <table class="variations" cellspacing="0">
                                            <tbody>
											<?php foreach ( $attributes as $attribute_name => $options ) : ?>
                                                <tr>
                                                    <td class="label">
                                                        <label for="<?php echo sanitize_title( $attribute_name ); ?>">
															<?php echo wc_attribute_label( $attribute_name ); ?>
                                                        </label>
                                                    </td>
                                                    <td class="value">
														<?php
														$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
														wc_dropdown_variation_attribute_options( array(
															'options'   => $options,
															'attribute' => $attribute_name,
															'product'   => $product,
															'selected'  => $selected
														) );
														echo end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>' ) : '';
														?>
                                                    </td>
                                                </tr>
											<?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <div class="single_variation_wrap">
                                            <div class="woocommerce-product-builder-variation single_variation"></div>
                                            <div class="woocommerce-product-builder-variation-add-to-cart variations_button">
												<?php
												do_action( 'woocommerce_product_builder_quantity_field', $product, $post_id );
												//		                                        wp_nonce_field( '_woopb_add_to_cart', '_nonce' );
												$this->quantity_field( $product, $post_id );
												?>
                                                <input type="hidden" id="_nonce" name="_nonce" value="<?php echo $nonce ?>">
                                                <input type="hidden" name="_wp_http_referer" value="<?php echo $referer ?>">
                                                <button type="submit" class="single_add_to_cart_button button alt">
													<?php echo esc_html__( 'Select', 'woocommerce-product-builder' ) ?>
                                                </button>
                                                <input type="hidden" name="woopb-add-to-cart" value="<?php echo absint( $product->get_id() ); ?>"/>
                                                <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>"/>
                                                <input type="hidden" name="variation_id" class="variation_id" value="0"/>
                                                <input type="hidden" name="woopb_id" value="<?php echo esc_attr( $post_id ) ?>"/>
                                            </div>
                                        </div>
									<?php endif; ?>
                                </form>
								<?php
							} ?>
                        </div>
                    </div>
				<?php }
				wp_reset_postdata();
				?>
				<?php
			} else {
				echo '<h2>' . esc_html__( 'Products are not found.', 'woocommerce-product-builder' ) . '</h2>';
			}
			$out = ob_get_clean();
		}
		wp_send_json_success( $out );
		wp_die();
	}

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

	public function get_products( $post_id, $step_id ) {
		/*Get current step*/
		$items = $this->settings->get_data( $post_id, 'list_content', array() );
		if ( $step_id > count( $items ) ) {
			$step_id = count( $items ) - 1;
		}
		$item_data = isset( $items[ $step_id - 1 ] ) ? $items[ $step_id - 1 ] : array();
		$terms     = $product_ids = $product_ids_of_term = array();

		foreach ( $item_data as $item ) {
			if ( strpos( trim( $item ), 'cate_' ) === false ) {
				$product_ids[] = $item;
			} else {
				$terms[] = str_replace( 'cate_', '', trim( $item ) );
			}
		}

		$args      = array(
			'post_status'    => 'publish',
			'post_type'      => 'product',
			'posts_per_page' => - 1,
			'tax_query'      => array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => apply_filters( 'woopb_product_type', array( 'simple', 'variable' ) ),
					'operator' => 'IN'
				),
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => $terms,
					'operator' => 'IN'
				),
			),
			'fields'         => 'ids'
		);
		$the_query = new WP_Query( $args );

		if ( $the_query->have_posts() ) {
			$product_ids_of_term = $the_query->posts;
		}
		wp_reset_postdata();
		$product_ids = array_unique( array_merge( $product_ids, $product_ids_of_term ) );

		return $product_ids;
	}

}

