<?php
defined( 'ABSPATH' ) || exit;

class xlwcty_Rule_Product_Select extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'product_select' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'thank-you-page-for-woocommerce-nextmove' ),
			'notin' => __( 'is not', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Product_Select';
	}

	public function is_match( $rule_data, $productID ) {
		$result  = false;
		$product = wc_get_product( $productID );

		if ( $product && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$in     = in_array( $product->get_id(), $rule_data['condition'] );
			$result = $rule_data['operator'] === 'in' ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class xlwcty_Rule_Product_Type extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'product_type' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'thank-you-page-for-woocommerce-nextmove' ),
			'notin' => __( 'is not', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$result = array();
		$terms  = get_terms( 'product_type', array(
			'hide_empty' => false,
		) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $product_id ) {
		$result = false;

		if ( $product_id && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$product_types = wp_get_post_terms( $product_id, 'product_type', array(
				'fields' => 'ids',
			) );
			if ( is_array( $product_types ) && is_array( $rule_data['condition'] ) ) {
				$in = count( array_intersect( $product_types, $rule_data['condition'] ) ) > 0;
			}
			$result = 'in' === $rule_data['operator'] ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class xlwcty_Rule_Product_Category extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'product_category' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'thank-you-page-for-woocommerce-nextmove' ),
			'notin' => __( 'is not', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$result = array();
		$terms  = get_terms( 'product_cat', array(
			'hide_empty' => false,
		) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $productID ) {
		$product = wc_get_product( $productID );
		$result  = false;

		if ( $product && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$product_types = wp_get_post_terms( $product->get_id(), 'product_cat', array(
				'fields' => 'ids',
			) );
			if ( is_array( $product_types ) && is_array( $rule_data['condition'] ) ) {
				$in = count( array_intersect( $product_types, $rule_data['condition'] ) ) > 0;
			}
			$result = $rule_data['operator'] === 'in' ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class xlwcty_Rule_Product_Attribute extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'product_attribute' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'in'    => __( 'has', 'thank-you-page-for-woocommerce-nextmove' ),
			'notin' => __( 'does not have', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$result               = array();
		$attribute_taxonomies = XLWCTY_Compatibility::wc_get_attribute_taxonomies();

		if ( $attribute_taxonomies ) {
			foreach ( $attribute_taxonomies as $tax ) {
				$attribute_taxonomy_name = XLWCTY_Compatibility::wc_attribute_taxonomy_name( $tax->attribute_name );
				if ( taxonomy_exists( $attribute_taxonomy_name ) ) {
					$terms = get_terms( $attribute_taxonomy_name, 'orderby=name&hide_empty=0' );
					if ( $terms ) {
						foreach ( $terms as $term ) {
							$result[ $attribute_taxonomy_name . '|' . $term->term_id ] = $tax->attribute_name . ': ' . $term->name;
						}
					}
				}
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function sort_attribute_taxonomies( $taxa, $taxb ) {
		return strcmp( $taxa->attribute_name, $taxb->attribute_name );
	}

	public function is_match( $rule_data, $productID ) {
		$product = wc_get_product( $productID );
		$result  = false;

		if ( $product && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			foreach ( $rule_data['condition'] as $condition ) {
				$term_data               = explode( '|', $condition );
				$attribute_taxonomy_name = $term_data[0];
				$term_id                 = $term_data[1];
				$post_terms              = wp_get_post_terms( $product->get_id(), $attribute_taxonomy_name, array(
					'fields' => 'ids',
				) );
				$in                      = in_array( $term_id, $post_terms );
				$result                  = $rule_data['operator'] === 'in' ? $in : ! $in;
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class xlwcty_Rule_Product_Price extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'product_price' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'!=' => __( 'is not equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'>'  => __( 'is greater than', 'thank-you-page-for-woocommerce-nextmove' ),
			'<'  => __( 'is less than', 'thank-you-page-for-woocommerce-nextmove' ),
			'>=' => __( 'is greater or equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'=<' => __( 'is less or equal to', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data, $productID ) {
		$result  = false;
		$product = wc_get_product( $productID );
		if ( $product && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$value = (float) $rule_data['condition'];

			if ( $product->get_type() === 'grouped' ) {

				foreach ( $product->get_children() as $child_id ) {
					$child = wc_get_product( $child_id );
					if ( '' !== $child->get_price() ) {

						if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {

							$child_prices[] = wc_get_price_excluding_tax( $child );
						} else {
							$child_prices[] = $child->get_price_excluding_tax();
						}
					}
				}

				if ( ! empty( $child_prices ) ) {
					$min = min( $child_prices );
					$max = max( $child_prices );
				} else {
					$min = '';
					$max = '';
				}

				switch ( $rule_data['operator'] ) {
					case '==':
						$result = ( $min <= $value && $value <= $max );
						break;
					case '!=':
						$result = ( ! ( $min <= $value && $value <= $max ) );
						break;
					case '>':
						//check if is range
						if ( ( $min <= $value && $value < $max ) ) {

							$result = true;
						} else {

							if ( $min > $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}

						break;
					case '<':
						//check if is range
						if ( ( $min < $value && $value <= $max ) ) {

							$result = true;
						} else {

							if ( $max < $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}

						break;
					case '>=':
						if ( ( $min <= $value && $value <= $max ) ) {

							$result = true;
						} else {

							if ( $min >= $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}

						break;
					case '=<':
						if ( ( $min <= $value && $value <= $max ) ) {

							$result = true;
						} else {

							if ( $max < $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}

						break;
					default:
						$result = false;
						break;
				}
			} elseif ( in_array( $product->get_type(), XLWCTY_Common::xlwcty_woocommerce_product_type_variations(), true ) ) {

				$prices = $product->get_variation_prices();
				$min    = (float) current( $prices['price'] );
				$max    = (float) end( $prices['price'] );

				switch ( $rule_data['operator'] ) {
					case '==':
						$result = ( $min <= $value && $value <= $max );
						break;
					case '!=':
						$result = ( ! ( $min <= $value && $value <= $max ) );
						break;
					case '>':
						//check if is range
						if ( ( $min <= $value && $value < $max ) ) {

							$result = true;
						} else {

							if ( $min > $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}

						break;
					case '<':
						//check if is range
						if ( ( $min < $value && $value <= $max ) ) {

							$result = true;
						} else {

							if ( $max < $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}

						break;
					case '>=':
						if ( ( $min <= $value && $value <= $max ) ) {

							$result = true;
						} else {

							if ( $min >= $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}

						break;
					case '=<':
						if ( ( $min <= $value && $value <= $max ) ) {

							$result = true;
						} else {

							if ( $max < $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}

						break;
					default:
						$result = false;
						break;
				}
			} else {
				$price = $product->get_price();

				switch ( $rule_data['operator'] ) {
					case '==':
						$result = $price == $value;
						break;
					case '!=':
						$result = $price != $value;
						break;
					case '>':
						$result = $price > $value;
						break;
					case '<':
						$result = $price < $value;
						break;
					case '>=':
						$result = $price >= $value;
						break;
					case '<=':
						$result = $price <= $value;
						break;
					default:
						$result = false;
						break;
				}
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}
