<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class VI_WPRODUCTBUILDER_Data {
	protected $data;
	protected $params;

	public function __construct() {
		global $woocommerce_product_builder_settings;

		if ( ! $woocommerce_product_builder_settings ) {
			$woocommerce_product_builder_settings = get_option( 'woopb_option-param', array() );
		}
		$this->params = $woocommerce_product_builder_settings;
		$args         = apply_filters( 'woopb_default_data', array(
			'enable_email'           => 0,
			'email_header'           => '',
			'email_from'             => '',
			'email_subject'          => '',
			'message_body'           => '',
			'message_success'        => '',
			'button_text_color'      => '#ffffff',
			'button_bg_color'        => '#04747a',
			'button_main_text_color' => '#ffffff',
			'button_main_bg_color'   => '#4b9989',
			'button_icon'            => '0',
			'share_link'             => 0,
			'custom_css'             => '',
			'remove_session'         => 0,
		) );
		$this->params = apply_filters( 'woocoommerce_product_builder_settings_args', wp_parse_args( $this->params, $args ) );
	}

	public static function check_step( $step_id ) {
		global $post;
//		$param = get_post_meta( $post->ID, 'woopb-param', true );

//		$param['require_product'] = true; //gia lap
//		if ( isset( $param['require_product'] ) && $param['require_product'] ) {
//			$session_id  = 'woopb_' . $post->ID;
//			$added_array = WC()->session->get( $session_id );
//			$count       = 0;
//			if ( ! empty( $added_array ) ) {
//				foreach ( $added_array as $array ) {
//					$count = is_array( $array ) && count( $array ) ? ++ $count : $count;
//				}
//			}
//			if ( $step_id > $count + 1 ) {
//				$step_id = $count + 1;
//			}
//		}

		return $step_id;
	}

	/**
	 * Get Custom CSS
	 * @return mixed|void
	 */
	public function get_custom_css() {
		return apply_filters( 'woocoommerce_product_builder_get_custom_css', $this->params['custom_css'] );

	}

	/**
	 * Change icon
	 * @return mixed|void
	 */
	public function get_button_icon() {
		return apply_filters( 'woocoommerce_product_builder_get_button_icon', $this->params['button_icon'] );

	}

	/**
	 * Check enable send email on review page
	 * @return mixed|void
	 */
	public function enable_email() {
		return apply_filters( 'woocoommerce_product_builder_enable_email', $this->params['enable_email'] );
	}

	/**
	 * Get main background color
	 * @return mixed|void
	 */
	public function get_button_text_color() {
		return apply_filters( 'woocoommerce_product_builder_get_button_text_color', $this->params['button_text_color'] );
	}

	/**
	 * Get  background color
	 * @return mixed|void
	 */
	public function get_button_bg_color() {
		return apply_filters( 'woocoommerce_product_builder_get_button_bg_color', $this->params['button_bg_color'] );
	}

	/**
	 * Get main text color
	 * @return mixed|void
	 */
	public function get_button_main_text_color() {
		return apply_filters( 'woocoommerce_product_builder_get_button_main_text_color', $this->params['button_main_text_color'] );
	}

	/**
	 * Get main background color
	 * @return mixed|void
	 */
	public function get_button_main_bg_color() {
		return apply_filters( 'woocoommerce_product_builder_get_button_main_bg_color', $this->params['button_main_bg_color'] );
	}

	/**
	 * Get message successful when send email to friends.
	 * @return mixed|void
	 */
	public function get_message_success() {
		return apply_filters( 'woocoommerce_product_builder_get_message_success', $this->params['message_success'] );
	}

	/**
	 * Get email body
	 * @return mixed|void
	 */
	public function get_message_body() {
		return apply_filters( 'woocoommerce_product_builder_get_message_body', $this->params['message_body'] );
	}

	/**
	 * Get email subject
	 * @return mixed|void
	 */
	public function get_email_subject() {
		return apply_filters( 'woocoommerce_product_builder_get_email_subject', $this->params['email_subject'] );
	}

	/**
	 * Get email from
	 * @return mixed|void
	 */
	public function get_email_header() {
		return apply_filters( 'woocoommerce_product_builder_get_email_header', $this->params['email_header'] );
	}


	public function get_email_from() {
		return apply_filters( 'woocoommerce_product_builder_get_email_from', $this->params['email_from'] );
	}

	public function get_remove_session() {
		return $this->params['remove_session'];
	}


	/**
	 * Check products added in all steps
	 *
	 * @param     $post_id
	 * @param int $step_id
	 *
	 * @return bool
	 */
	public function has_step_added( $post_id, $step_id = 0 ) {
		$session_id   = 'woopb_' . $post_id;
		$tabs         = $this->get_data( $post_id, 'tab_title' );
		$count        = count( array_filter( $tabs ) );
		$data_session = WC()->session->get( $session_id );
		if ( $step_id ) {
			if ( isset( $data_session[ $step_id ] ) && is_array( $data_session[ $step_id ] ) && count( array_filter( $data_session[ $step_id ] ) ) ) {
				$products_added = array_filter( $data_session[ $step_id ] );
			} else {
				return false;
			}
			if ( count( $products_added ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			if ( isset( $data_session ) && is_array( $data_session ) && count( array_filter( $data_session ) ) ) {
				$products_added = array_filter( $data_session );

			} else {
				return false;
			}
			if ( count( $products_added ) == $count ) {
				foreach ( $products_added as $step ) {
					if ( is_array( $step ) && count( array_filter( $step ) ) ) {
					} else {
						return false;
					}
				}

				return true;
			} else {
				return false;
			}
		}

	}

	/**
	 * Get Post Meta
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	public function get_data( $post_id, $field, $default = '' ) {

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

	/**
	 * Set Products added by Session
	 *
	 * @param     $post_id
	 * @param     $data
	 * @param int $step_id
	 *
	 * @return bool
	 */
	public function set_products_added( $post_id, $data, $step_id = 0 ) {
		if ( $post_id && is_array( $data ) && count( array_filter( $data ) ) ) {
			$session_id   = 'woopb_' . $post_id;
			$data_session = WC()->session->get( $session_id );
			if ( $step_id ) {
				$data_session[ $step_id ] = $data;
			} else {
				$data_session = $data;
			}
			WC()->session->set( $session_id, $data_session );

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Remove products in Product Builder
	 *
	 * @param $post_id
	 * @param $product_id
	 * @param $step_id
	 *
	 * @return bool
	 */
	public function remove_products( $post_id ) {
		if ( $post_id ) {
			$session_id = 'woopb_' . $post_id;
			WC()->session->__unset( $session_id );

			return true;
		}

		return false;
	}

	/**
	 * Remove product in Session
	 *
	 * @param $post_id
	 * @param $product_id
	 * @param $step_id
	 *
	 * @return bool
	 */
	public function remove_product( $post_id, $product_id, $step_id ) {
		if ( $post_id && $product_id && $step_id ) {
			$session_id   = 'woopb_' . $post_id;
			$data_session = WC()->session->get( $session_id );
			unset( $data_session[ $step_id ][ $product_id ] );
			WC()->session->set( $session_id, $data_session );

			$sum = 0;
			if ( is_array( $data_session ) && count( $data_session ) ) {
				foreach ( $data_session as $step ) {
					if ( is_array( $step ) && ! empty( $step ) ) {
						$sum += array_sum( $step );
					}
				}
			}
			if ( $sum ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check product added in Session
	 *
	 * @param $post_id
	 * @param $step_id
	 * @param $product_id
	 *
	 * @return bool
	 */
	public function check_product_added( $post_id, $step_id, $product_id ) {
		if ( ! $post_id || ! $step_id || ! $product_id ) {
			return false;
		}
		$products_added = $this->get_products_added( $post_id, $step_id );
		if ( isset( $products_added[ $product_id ] ) && $products_added[ $product_id ] > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get product added
	 *
	 * @param     $post_id Product Builder Page ID
	 * @param int $step_id
	 *
	 * @return array
	 */
	public function get_products_added( $post_id, $step_id = false ) {
		$session_id   = 'woopb_' . $post_id;
		$data_session = WC()->session->get( $session_id );
		if ( $step_id ) {
			if ( isset( $data_session[ $step_id ] ) && is_array( $data_session[ $step_id ] ) && count( array_filter( $data_session[ $step_id ] ) ) ) {
				$products_added = array_filter( $data_session[ $step_id ] );
			} else {
				$products_added = array();
			}
		} else {
			if ( isset( $data_session ) && is_array( $data_session ) && count( array_filter( $data_session ) ) ) {
				$products_added = array_filter( $data_session );
			} else {
				$products_added = array();
			}
		}

		return $products_added;
	}

	/**
	 * Get list product in Product Builder page
	 * @return array
	 */
	public function get_products( $post_id ) {
		/*Get current step*/
		$step_id = get_query_var( 'step' );
		if ( ! $step_id ) {
			$step_id = 1;
		}

		$items = $this->get_data( $post_id, 'list_content', array() );
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

	/**
	 * Get list product in Product Builder page
	 * @return array
	 */
	public function get_product_filters( $post_id, $pagination = true ) {

		global $wpdb;
		/*Get current step*/
		$step_id = get_query_var( 'step' );

		if ( ! $step_id ) {
			$step_id = 1;
		}

		/*Get pagination*/
		$paged = get_query_var( 'ppaged' );
		if ( ! $paged ) {
			$paged = 1;
		}
		$max_page      = 1;
		$post_per_page = $this->get_data( $post_id, 'product_per_page', 10 );
		$items         = $this->get_data( $post_id, 'list_content', array() );
		if ( $step_id > count( $items ) ) {
			$step_id = count( $items ) - 1;
		}
		$item_data = isset( $items[ $step_id - 1 ] ) ? $items[ $step_id - 1 ] : array();

		if ( count( $item_data ) ) {
			$terms = $product_ids = $product_ids_of_term = array();

			foreach ( $item_data as $item ) {
				if ( strpos( trim( $item ), 'cate_' ) === false ) {
					$product_ids[] = $item;
				} else {
					$terms[] = str_replace( 'cate_', '', trim( $item ) );
				}
			}

			$select[] = "SELECT p.ID FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->term_relationships} AS tr ON p.ID = tr.object_id LEFT JOIN {$wpdb->term_taxonomy} AS tt1 ON tt1.term_taxonomy_id = tr.term_taxonomy_id";
			$where[]  = "p.post_type = 'product' AND p.post_status = 'publish'";
			$order    = "GROUP BY p.ID ORDER BY p.post_date DESC ";

			/*Process attributes*/
			$list_attrs = array();
			if ( $this->get_data( $post_id, 'enable_compatible' ) ) {
				$compatible_data = $this->get_data( $post_id, 'product_compatible' );

				$current_compatible_steps = isset( $compatible_data[ $step_id - 1 ] ) ? $compatible_data[ $step_id - 1 ] : array();

				/*Get attributes from products added in Session*/
				if ( is_array( $current_compatible_steps ) && count( $current_compatible_steps ) ) {
					$compatible_products = array();
					foreach ( $current_compatible_steps as $compatible_step ) {
						$temp_data = $this->get_products_added( $post_id, $compatible_step + 1 );
						if ( is_array( $temp_data ) && count( $temp_data ) ) {
							$compatible_products = array_merge( $compatible_products, array_keys( $temp_data ) );
							if ( count( $compatible_products ) ) {
								$compatible_products = array_unique( $compatible_products );
							}
						}
					}

					if ( is_array( $compatible_products ) && count( $compatible_products ) ) {
						foreach ( $compatible_products as $compatible_product ) {
							$product_attr = wc_get_product( $compatible_product );
							$attrs        = $product_attr->get_attributes();

							if ( is_array( $attrs ) && count( $attrs ) ) {
								foreach ( $attrs as $key => $attr ) {
									if ( is_object( $attr ) && $attr->get_id() ) {
										$list_attrs[ $key ] = isset( $list_attrs[ $key ] ) ? array_unique( array_merge( $list_attrs[ $key ], $attr->get_options() ) ) : $attr->get_options();
									}
								}
							}
						}
						if ( is_array( $list_attrs ) && count( $list_attrs ) ) {
							$list_attrs = $this->convert_single_array( $list_attrs );
							$list_attrs = array_map( 'trim', $list_attrs );
							$list_attrs = apply_filters( 'woopb_list_attrs_depend', $list_attrs, $step_id );
							$where[]    = "tt1.term_id IN (" . implode( ',', $list_attrs ) . ")";
						}
					}

				}
			}

			$where_products      = $where;
			$product_ids_of_term = $result_product_ids = array();

			if ( is_array( $terms ) && count( $terms ) ) {
				$all_terms = array();
				if ( $this->get_data( $post_id, 'child_cat' ) ) {
					foreach ( $terms as $term ) {
						$children = get_term_children( $term, 'product_cat' );
						if ( is_array( $children ) && count( $children ) ) {
							foreach ( $children as $child ) {
								$all_terms[] = $child;
							}
						}
					}
					$all_terms = array_unique( array_merge( $all_terms, $terms ) );
				} else {
					$all_terms = $terms;
				}

				$select[]            = "LEFT JOIN {$wpdb->term_relationships} AS tr1 ON p.ID = tr1.object_id LEFT JOIN {$wpdb->term_taxonomy} AS tt2 ON tt2.term_taxonomy_id = tr1.term_taxonomy_id";
				$where[]             = $wpdb->prepare( "tt2.term_id IN (%1s)", implode( ',', $all_terms ) );
				$query               = implode( ' ', $select ) . ' WHERE ' . implode( ' AND ', $where ) . ' ' . $order;
				$product_ids_of_term = $wpdb->get_col( $query );
			}

			/*Process compatible with specify products*/
			if ( is_array( $product_ids ) && count( $product_ids ) ) {
				$where_products[]   = 'p.ID IN (' . implode( ',', $product_ids ) . ')';
				$query              = implode( ' ', $select ) . ' WHERE ' . implode( ' AND ', $where_products ) . ' ' . $order;
				$result_product_ids = $wpdb->get_col( $query );
			}

			if ( is_array( $product_ids_of_term ) && count( $product_ids_of_term ) && is_array( $result_product_ids ) && count( $result_product_ids ) ) {

				$product_ids = array_unique( array_merge( $result_product_ids, $product_ids_of_term ) );
			} else if ( is_array( $product_ids_of_term ) && count( $product_ids_of_term ) ) {
				$product_ids = array_unique( $product_ids_of_term );
			} else if ( is_array( $result_product_ids ) && count( $result_product_ids ) ) {
				$product_ids = array_unique( $result_product_ids );
			} else {
				return false;
			}

			$product_ids = apply_filters( 'woopb_list_product_ids', $product_ids, $step_id, $list_attrs );
			/*Show products on step*/
			if ( count( $product_ids ) < 1 ) {
				return false;
			} elseif ( $pagination ) {

				$product_args = array(
					'post_status'    => 'publish',
					'post_type'      => 'product',
					'posts_per_page' => $post_per_page,
					'post__in'       => $product_ids,
					'paged'          => $paged,
					'tax_query'      => array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => apply_filters( 'woopb_product_type', array( 'simple', 'variable' ) ),
							'operator' => 'IN'
						)
					),
					'fields'         => 'ids'
				);
			} else {
				$product_args = array(
					'post_status'    => 'publish',
					'post_type'      => 'product',
					'posts_per_page' => - 1,
					'post__in'       => $product_ids,
					'tax_query'      => array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => apply_filters( 'woopb_product_type', array( 'simple', 'variable' ) ),
							'operator' => 'IN'
						)
					),
					'fields'         => 'ids'
				);
			}

			$stock_visible = get_option( 'woocommerce_hide_out_of_stock_items' );
			if ( $stock_visible == 'yes' ) {
				$product_args['meta_query'] = array(
					array(
						'key'     => '_stock_status',
						'value'   => 'outofstock',
						'compare' => '!=',
					),
				);
			}

			/*Check filter price*/
			$filter_price[] = get_query_var( 'min_price' );
			$filter_price[] = get_query_var( 'max_price' );
			if ( count( array_filter( $filter_price ) ) ) {
				$product_args['meta_query'] = array(
					array(
						'key'     => '_price',
						'value'   => $filter_price,
						'compare' => 'BETWEEN',
						'type'    => 'NUMERIC'
					),
				);
			}
			/*Check filter by rating*/
			$filter_rating = get_query_var( 'rating_filter' );

			if ( $filter_rating ) {
				$filter_rating            = explode( ',', $filter_rating );
				$product_visibility_terms = wc_get_product_visibility_term_ids();
				$rate_ids                 = array();
				if ( count( $product_visibility_terms ) ) {
					foreach ( $filter_rating as $rate_value ) {
						$rate_id = trim( $rate_value );
						if ( isset( $product_visibility_terms[ 'rated-' . $rate_id ] ) && $product_visibility_terms[ 'rated-' . $rate_id ] ) {
							$rate_ids[] = $product_visibility_terms[ 'rated-' . $rate_value ];
						}
					}

					$product_args['tax_query'][] = array(
						array(
							'taxonomy' => 'product_visibility',
							'field'    => 'term_id',
							'terms'    => $rate_ids,
							'operator' => 'IN'
						)
					);
				}
			}

			/*Check Attribute filter*/
			$_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
			if ( count( $_chosen_attributes ) ) {
				foreach ( $_chosen_attributes as $taxonomy => $terms_slug ) {
					$product_args['tax_query'][] = array(
						array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $terms_slug['terms'],
							'operator' => 'IN'
						)
					);
				}
			}

			/*Sort by*/
			$sort_by = get_query_var( 'sort_by' );
			switch ( $sort_by ) {
//				case '':
//					$product_args = apply_filters( 'woopb_sort_default', $product_args );
//					break;
				case '':
				case 'title_az':
					$product_args['orderby'] = 'title';
					$product_args['order']   = 'ASC';
					break;

				case 'title_za':
					$product_args['orderby'] = 'title';
					$product_args['order']   = 'DESC';
					break;

				case 'price_low':
					$product_args['orderby']  = 'meta_value_num';
					$product_args['order']    = 'ASC';
					$product_args['meta_key'] = apply_filters( 'woopb_sort_query', '_price' );
					break;

				case 'price_high':
					$product_args['orderby']  = 'meta_value_num';
					$product_args['order']    = 'DESC';
					$product_args['meta_key'] = apply_filters( 'woopb_sort_query', '_price' );
					break;
			}

			/*Check filter by search name*/
			$filter_product = get_query_var( 'name_filter' );
			if ( $filter_product ) {
				$product_args['s'] = $filter_product;
			}

			$the_product = new WP_Query( $product_args );

			if ( $the_product->have_posts() ) {

				return $the_product;
			}
			wp_reset_postdata();
		}

		return false;
	}

	/**
	 * Convert multi array to single array keep number key
	 *
	 * @param $array
	 *
	 * @return array|bool
	 */
	private function convert_single_array( $array ) {
		if ( ! is_array( $array ) ) {
			return false;
		}
		$result = array();
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) && count( $value ) ) {
				foreach ( $value as $key_2 => $value_2 ) {
					$result[] = $value_2;
				}
			}
		}

		return $result;
	}

	public function get_share_link_enable() {
		return $this->params['share_link'];

	}

	public function get_share_link() {
		global $post;
		if ( empty( $post ) ) {
			return;
		}

		$woopb_id = is_woopb_shortcode() ? VI_WPRODUCTBUILDER_FrontEnd_Shortcode::$woopb_id : $post->ID;

		$data      = WC()->session->get( 'woopb_' . $woopb_id );
		$data      = array( 'page_id' => $post->ID, 'woopb_id' => $woopb_id, 'data' => $data );
		$share     = base64_encode( json_encode( $data ) );
		$share     = strtr( $share, '+/=', '-_,' );
		$share_url = site_url( "?woopb_share={$share}" );

		return $share_url;
	}

	public function enqueue_scripts() {

		wp_enqueue_style( 'woocommerce-product-builder-icon' );
		wp_enqueue_style( 'woocommerce-product-builder' );
		wp_enqueue_style( 'woocommerce-product-builder-rtl' );
		wp_enqueue_script( 'woocommerce-product-builder' );
		wp_enqueue_script( 'woocommerce-product-builder-search' );
		$button_text_color      = $this->get_button_text_color();
		$button_bg_color        = $this->get_button_bg_color();
		$button_main_text_color = $this->get_button_main_text_color();
		$button_main_bg_color   = $this->get_button_main_bg_color();
		$custom_css             = " .vi-wpb-wrapper .woopb-products-pagination .woopb-page.woopb-active,
                                        .vi-wpb-wrapper .woopb-products-pagination .woopb-page:hover,
                                        .vi-wpb-wrapper .woocommerce-product-builder-wrapper .woopb-product .woopb-product-right .cart button:hover,
                                        .woopb-button.woopb-button-primary,
                                        .woopb-button:hover,
                                        .woocommerce-product-builder-widget.widget_price_filter .ui-slider:hover .ui-slider-range, 
                                        .woocommerce-product-builder-widget.widget_price_filter .ui-slider:hover .ui-slider-handle,
                                        .vi-wpb-wrapper .entry-content .woopb-steps .woopb-step-heading.woopb-step-heading-active,
                                        .vi-wpb-wrapper .entry-content .woopb-steps .woopb-step-heading.woopb-step-heading-active a
                                        {	
                                        color:{$button_main_text_color};
                                        background-color:{$button_main_bg_color};
                                        }
                                        .vi-wpb-wrapper .woopb-products-pagination .woopb-page,
                                        .vi-wpb-wrapper .woocommerce-product-builder-wrapper .woopb-product .woopb-product-right .cart button,
                                        .woopb-button,
                                        .woocommerce-product-builder-widget.widget_price_filter .ui-slider .ui-slider-range, 
                                        .woocommerce-product-builder-widget.widget_price_filter .ui-slider .ui-slider-handle
                                        {
                                        color:{$button_text_color};
                                        background-color:{$button_bg_color};
                                        }
                                        .vi-wpb-wrapper .woocommerce-product-builder-wrapper .woopb-product .woopb-product-right .cart button:before, .woocommerce-product-builder-widget .woocommerce-widget-layered-nav-list li > a:hover::before, .woocommerce-product-builder-widget .woocommerce-widget-layered-nav-list li.chosen > a:before{
                                            color:$button_bg_color;
                                        }
                                        .vi-wpb-wrapper .woopb-navigation a,.vi-wpb-wrapper .woocommerce-product-builder-wrapper .woopb-product .woopb-product-right .cart button:hover:before,.vi-wpb-wrapper .woopb-step-heading-active a,.vi-wpb-wrapper a:not(.woopb-button):hover{
                                            color:$button_main_bg_color;
                                        }
                                        .vi-wpb-wrapper .entry-content .woopb-steps .woopb-step-heading.woopb-step-heading-active:before{
                                            background-color:$button_main_bg_color;
                                        }
                                        ";
		$custom_css             .= $this->get_custom_css();
		$custom_css             = apply_filters( 'woopb_custom_css_inline', $custom_css );
		wp_add_inline_style( 'woocommerce-product-builder', $custom_css );
		// Localize the script with new data
		$translation_array = array(
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
			'pluginURL' => plugins_url()
		);
		wp_localize_script( 'woocommerce-product-builder', '_woo_product_builder_params', $translation_array );
		wp_localize_script( 'woocommerce-product-builder-search', '_woo_product_builder_params', $translation_array );
	}

}

