<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 09-04-19
 * Time: 2:00 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WPRODUCTBUILDER_Admin_Attribute_Depend {

	public function __construct() {
		if ( class_exists( 'WPB_ATTR_DP\Exe' ) ) { // fix for custom work
			return;
		}
		add_action( 'woopb_product_options_setting', array( $this, 'add_setting_area' ) );
		add_filter( 'woopb_default_data', array( $this, 'add_default_data' ) );
		add_filter( 'woopb_list_attrs_depend', array( $this, 'filter_attributes' ), 10, 2 );
		add_filter( 'woopb_list_product_ids', array( $this, 'filter_products' ), 10, 3 );
	}

	public function add_default_data( $data ) {
		$args = array( 'enable_attr_depend' => 0 );
		$data = wp_parse_args( $args, $data );

		return $data;
	}

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

	public static function get_field( $field, $default = '' ) {
		global $post;
		$params = get_post_meta( $post->ID, 'woopb-param', true );
		if ( isset( $params[ $field ] ) && $field ) {
			return $params[ $field ];
		} else {
			return $default;
		}
	}

	public function add_setting_area( $key ) {
		$compatible      = self::get_field( 'attr_compatible', array() );
		$list_contents   = self::get_field( 'list_content', array() );
		$step_compatible = isset( $compatible[ $key ] ) ? $compatible[ $key ] : array();
		$list_products   = $attr_names = array();

		for ( $i = 0; $i < $key; $i ++ ) {
			if ( is_array( $list_contents[ $i ] ) && count( $list_contents[ $i ] ) ) {
				foreach ( $list_contents[ $i ] as $id ) {
					$check_cate = strpos( trim( $id ), 'cate_' );
					if ( $check_cate === 0 ) {
						$cat_id   = str_replace( 'cate_', '', $id );
						$cat      = get_the_category_by_ID( $cat_id );
						$args     = array(
							'category' => array( $cat ),
						);
						$products = wc_get_products( $args );
						foreach ( $products as $product ) {
							$list_products[] = $product->get_id();
						}
					} else {
						$list_products[] = $id;
					}
				}
			}
		}
		if ( count( $list_products ) ) {
			foreach ( $list_products as $pid ) {
				$product = wc_get_product( $pid );
				$attrs   = $product->get_attributes();
				foreach ( $attrs as $attr ) {
					$attr_id = $attr->get_id();
					if ( $attr_id ) {
						$attr_names[ $attr_id ] = wc_get_attribute( $attr_id )->name;
					}
				}
			}
		}
		?>
        <select class="woopb-attr-compatible-field" multiple="multiple"
                name="<?php echo self::set_field( 'attr_compatible' ) ?>[<?php echo esc_attr( $key ) ?>][]">
			<?php foreach ( $attr_names as $attr_key => $attr_name ) {
				?>
                <option <?php selected( in_array( $attr_key, $step_compatible ), 1 ) ?>
                        value="<?php echo esc_attr( $attr_key ) ?>"><?php echo esc_html( $attr_name ) ?></option>
			<?php } ?>
        </select>
		<?php
	}

	public function get_attributes( $pid ) {
		$product   = wc_get_product( $pid );
		$attrs     = $product->get_attributes();
		$attr_name = array();
		foreach ( $attrs as $key => $attr ) {
			$attr_id = $attr->get_id();
			if ( $attr_id ) {
				$attr_name[] = wc_get_attribute( $attr_id )->name;
			}
		}

		return $attr_name;
	}

	public function filter_attributes( $list_attrs, $step_id ) {
		$attrs_compatible         = self::get_field( 'attr_compatible', array() );
		$current_compatible_steps = isset( $attrs_compatible[ $step_id - 1 ] ) ? $attrs_compatible[ $step_id - 1 ] : array();
		$term_ids                 = array();

		if ( is_array( $current_compatible_steps ) && count( $current_compatible_steps ) ) {
			foreach ( $current_compatible_steps as $attr_id ) {
				$slug  = wc_get_attribute( $attr_id )->slug;
				$terms = get_terms( array(
					'taxonomy'   => $slug,
					'hide_empty' => false,
				) );
				foreach ( $terms as $term ) {
					$term_ids[] = $term->term_id;
				}
			}
			$list_attrs = array_intersect( $term_ids, $list_attrs );
		}

		return $list_attrs;
	}


	public function filter_products( $product_ids, $step_id, $list_attrs ) {
		if ( empty( $list_attrs ) ) {
			return $product_ids;
		}
		$attrs_compatible         = self::get_field( 'attr_compatible', array() );
		$current_compatible_steps = isset( $attrs_compatible[ $step_id - 1 ] ) ? $attrs_compatible[ $step_id - 1 ] : array();

		$final_product_ids = array();
		if ( is_array( $product_ids ) && count( $product_ids ) ) {
			foreach ( $product_ids as $pid ) {
				$count      = 0;
				$product    = wc_get_product( $pid );
				$attributes = $product->get_attributes();
				if ( is_array( $attributes ) && count( $attributes ) ) {
					foreach ( $attributes as $attr ) {
						$attr_id = $attr->get_id();
						if ( in_array( $attr_id, $current_compatible_steps ) ) {
							$options = $attr->get_options();
							if ( is_array( $options ) && count( $options ) ) {
								foreach ( $options as $option ) {
									if ( ! in_array( $option, $list_attrs ) ) {
										$count ++;
									}
								}
							}
						}
					}
				}

				if ( $count == 0 ) {
					$final_product_ids[] = $pid;
				}
			}
		}

		return $final_product_ids;
	}
}