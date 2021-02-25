<?php
defined( 'ABSPATH' ) || exit;

class xlwcty_Rule_Order_Total extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_total' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'!=' => __( 'is not equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'>'  => __( 'is greater than', 'thank-you-page-for-woocommerce-nextmove' ),
			'<'  => __( 'is less than', 'thank-you-page-for-woocommerce-nextmove' ),
			'>=' => __( 'is greater or equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'<=' => __( 'is less or equal to', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data, $order_id ) {

		$order = wc_get_order( $order_id );
		$price = absint( $order->get_total() );
		$value = absint( $rule_data['condition'] );

		switch ( $rule_data['operator'] ) {
			case '==':
				$result = $price === $value;
				break;
			case '!=':
				$result = $price !== $value;
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

		return $this->return_is_match( $result, $rule_data );
	}

}


class xlwcty_Rule_Order_Item_Count extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_item_count' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'!=' => __( 'is not equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'>'  => __( 'is greater than', 'thank-you-page-for-woocommerce-nextmove' ),
			'<'  => __( 'is less than', 'thank-you-page-for-woocommerce-nextmove' ),
			'>=' => __( 'is greater or equal to', 'thank-you-page-for-woocommerce-nextmove' ),
			'<=' => __( 'is less or equal to', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data, $order_id ) {

		$order = wc_get_order( $order_id );
		$count = absint( $order->get_item_count() );
		$value = absint( $rule_data['condition'] );

		switch ( $rule_data['operator'] ) {
			case '==':
				$result = $count === $value;
				break;
			case '!=':
				$result = $count !== $value;
				break;
			case '>':
				$result = $count > $value;
				break;
			case '<':
				$result = $count < $value;
				break;
			case '>=':
				$result = $count >= $value;
				break;
			case '<=':
				$result = $count <= $value;
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class xlwcty_Rule_Order_Item extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_item' );
	}

	public function get_possibile_rule_operators() {

		$operators = array(
			'>'  => __( 'contains at least', 'thank-you-page-for-woocommerce-nextmove' ),
			'<'  => __( 'contains at most', 'thank-you-page-for-woocommerce-nextmove' ),
			'==' => __( 'contains exactly', 'thank-you-page-for-woocommerce-nextmove' ),
			'!=' => __( 'does not contains at least', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Cart_Product_Select';
	}

	public function is_match( $rule_data, $order_id ) {
		$order          = wc_get_order( $order_id );
		$products       = $rule_data['condition']['products'];
		$quantity       = absint( $rule_data['condition']['qty'] );
		$type           = $rule_data['operator'];
		$found_quantity = 0;

		if ( $order->get_items() && is_array( $order->get_items() ) && count( $order->get_items() ) ) {
			foreach ( $order->get_items() as $item_key => $cart_item ) {

				$product   = XLWCTY_Compatibility::get_product_from_item( $order, $cart_item );
				$productID = $product->get_id();

				$productID = ( XLWCTY_Common::get_product_parent_id( $product ) ) ? XLWCTY_Common::get_product_parent_id( $product ) : $productID;

				if ( version_compare( WC()->version, '3.0', '>=' ) ) {
					$variationID = $cart_item->get_variation_id();
				} else {
					$variationID = ( is_array( $cart_item['variation_id'] ) && count( $cart_item['variation_id'] ) > 0 ) ? $cart_item['variation_id'][0] : 0;
				}

				if ( in_array( $productID, $products ) || ( ( $productID ) && in_array( $variationID, $products ) ) ) {

					$found_quantity += absint( $cart_item['qty'] );
				}
			}
		}

		if ( 0 === $found_quantity ) {
			if ( '!=' === $type ) {
				return $this->return_is_match( true, $rule_data );
			}

			return $this->return_is_match( false, $rule_data );
		}
		switch ( $type ) {
			case '<':
				$result = ( $quantity >= $found_quantity );
				break;
			case '>':
				$result = ( $quantity <= $found_quantity );
				break;
			case '==':
				$result = ( $quantity === $found_quantity );
				break;
			case '!=':
				$result = ! ( $quantity <= $found_quantity );
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class xlwcty_Rule_Order_Category extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_category' );
	}

	public function get_possibile_rule_operators() {

		$operators = array(
			'any' => __( 'matched any of', 'thank-you-page-for-woocommerce-nextmove' ),
			'all' => __( 'matches all of ', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$result = array();

		$terms = get_terms( 'product_cat', array(
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

	public function is_match( $rule_data, $order_id ) {
		$type      = $rule_data['operator'];
		$order     = wc_get_order( $order_id );
		$all_terms = array();

		if ( $order->get_items() && is_array( $order->get_items() ) && count( $order->get_items() ) ) {
			foreach ( $order->get_items() as $item_key => $cart_item ) {
				$product = XLWCTY_Compatibility::get_product_from_item( $order, $cart_item );

				$productID = $product->get_id();
				$productID = ( XLWCTY_Common::get_product_parent_id( $product ) ) ? XLWCTY_Common::get_product_parent_id( $product ) : $productID;

				$terms = wp_get_object_terms( $productID, 'product_cat', array(
					'fields' => 'ids',
				) );

				$all_terms = array_merge( $all_terms, $terms );

			}
		}
		$all_terms = array_filter( $all_terms );
		if ( empty( $all_terms ) ) {
			return $this->return_is_match( false, $rule_data );
		}

		switch ( $type ) {
			case 'all':
				if ( is_array( $rule_data['condition'] ) && is_array( $all_terms ) ) {
					$result = count( array_intersect( $rule_data['condition'], $all_terms ) ) === count( $rule_data['condition'] );
				}
				break;
			case 'any':
				if ( is_array( $rule_data['condition'] ) && is_array( $all_terms ) ) {
					$result = count( array_intersect( $rule_data['condition'], $all_terms ) ) >= 1;
				}
				break;

			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class xlwcty_Rule_Order_Item_Type extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_item_type' );
	}

	public function get_possibile_rule_operators() {

		$operators = array(
			'any' => __( 'matched any of', 'thank-you-page-for-woocommerce-nextmove' ),
			'all' => __( 'matches all of ', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$result = array();

		$terms = get_terms( 'product_type', array(
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

	public function is_match( $rule_data, $order_id ) {
		$type      = $rule_data['operator'];
		$order     = wc_get_order( $order_id );
		$all_types = array();

		if ( $order->get_items() && count( $order->get_items() ) ) {
			foreach ( $order->get_items() as $item_key => $cart_item ) {
				$product   = XLWCTY_Compatibility::get_product_from_item( $order, $cart_item );
				$productID = $product->get_id();
				$productID = ( XLWCTY_Common::get_product_parent_id( $product ) ) ? XLWCTY_Common::get_product_parent_id( $product ) : $productID;

				$product_types = wp_get_post_terms( $productID, 'product_type', array(
					'fields' => 'ids',
				) );

				$all_types = array_merge( $all_types, $product_types );
			}
		}

		$all_types = array_filter( $all_types );
		if ( empty( $all_types ) ) {
			return $this->return_is_match( false, $rule_data );
		}

		switch ( $type ) {
			case 'all':
				if ( is_array( $rule_data['condition'] ) && is_array( $all_types ) ) {
					$result = count( array_intersect( $rule_data['condition'], $all_types ) ) === count( $rule_data['condition'] );
				}
				break;
			case 'any':
				if ( is_array( $rule_data['condition'] ) && is_array( $all_types ) ) {
					$result = count( array_intersect( $rule_data['condition'], $all_types ) ) >= 1;
				}
				break;

			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}


class xlwcty_Rule_Order_Coupons extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_coupons' );
	}

	public function get_possibile_rule_operators() {

		$operators = array(
			'any' => __( 'matched any of', 'thank-you-page-for-woocommerce-nextmove' ),
			'all' => __( 'matches all of ', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$result  = array();
		$coupons = get_posts( array(
			'post_type'      => 'shop_coupon',
			'posts_per_page' => - 1,

		) );

		foreach ( $coupons as $coupon ) {
			$result[ sanitize_title( $coupon->post_title ) ] = $coupon->post_title;
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $order_id ) {
		$type  = $rule_data['operator'];
		$order = wc_get_order( $order_id );

		if ( version_compare( WC()->version, 3.7, '>=' ) ) {
			$used_coupons = $order->get_coupon_codes();
		} else {
			$used_coupons = $order->get_used_coupons();
		}

		if ( empty( $used_coupons ) ) {
			return $this->return_is_match( false, $rule_data );
		}

		switch ( $type ) {
			case 'all':
				if ( is_array( $rule_data['condition'] ) && is_array( $used_coupons ) ) {
					$result = count( array_intersect( $rule_data['condition'], $used_coupons ) ) === count( $rule_data['condition'] );
				}
				break;
			case 'any':
				if ( is_array( $rule_data['condition'] ) && is_array( $used_coupons ) ) {
					$result = count( array_intersect( $rule_data['condition'], $used_coupons ) ) >= 1;
				}
				break;

			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class xlwcty_Rule_Order_Payment_Gateway extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_payment_gateway' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'is'     => __( 'is', 'thank-you-page-for-woocommerce-nextmove' ),
			'is_not' => __( 'is not', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$result = array();

		foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
			if ( 'yes' === $gateway->enabled ) {
				$result[ $gateway->id ] = $gateway->get_title();
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $order_id ) {
		$type    = $rule_data['operator'];
		$order   = wc_get_order( $order_id );
		$payment = XLWCTY_Compatibility::get_payment_gateway_from_order( $order );

		if ( empty( $payment ) ) {
			if ( 'is_not' === $type ) {
				return $this->return_is_match( true, $rule_data );
			}

			return $this->return_is_match( false, $rule_data );
		}

		switch ( $type ) {
			case 'is':
				$result = in_array( $payment, $rule_data['condition'], true );
				break;
			case 'is_not':
				$result = ! in_array( $payment, $rule_data['condition'], true );
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}


class xlwcty_Rule_Order_Shipping_Country extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_shipping_country' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'any'  => __( 'matched any of', 'thank-you-page-for-woocommerce-nextmove' ),
			'none' => __( 'matches none of ', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$result = WC()->countries->get_allowed_countries();

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $order_id ) {
		$type             = $rule_data['operator'];
		$order            = wc_get_order( $order_id );
		$shipping_country = XLWCTY_Compatibility::get_shipping_country_from_order( $order );

		if ( empty( $shipping_country ) ) {
			return $this->return_is_match( false, $rule_data );
		}

		$shipping_country = array( $shipping_country );

		switch ( $type ) {
			case 'any':
				if ( is_array( $rule_data['condition'] ) && is_array( $shipping_country ) ) {
					$result = count( array_intersect( $rule_data['condition'], $shipping_country ) ) >= 1;
				}
				break;
			case 'none':
				if ( is_array( $rule_data['condition'] ) && is_array( $shipping_country ) ) {
					$result = count( array_intersect( $rule_data['condition'], $shipping_country ) ) === 0;
				}
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}


class xlwcty_Rule_Order_Shipping_Method extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_shipping_method' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'any'  => __( 'matched any of', 'thank-you-page-for-woocommerce-nextmove' ),
			'none' => __( 'matches none of ', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$result = array();

		foreach ( WC()->shipping()->get_shipping_methods() as $method_id => $method ) {
			// get_method_title() added in WC 2.6
			$result[ $method_id ] = is_callable( array( $method, 'get_method_title' ) ) ? $method->get_method_title() : $method->get_title();
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $order_id ) {
		$type    = $rule_data['operator'];
		$order   = wc_get_order( $order_id );
		$methods = array();

		foreach ( $order->get_shipping_methods() as $method ) {
			// extract method slug only, discard instance id
			if ( $split = strpos( $method['method_id'], ':' ) ) {
				$methods[] = substr( $method['method_id'], 0, $split );
			} else {
				$methods[] = $method['method_id'];
			}
		}

		switch ( $type ) {
			case 'any':
				if ( is_array( $rule_data['condition'] ) && is_array( $methods ) ) {
					$result = count( array_intersect( $rule_data['condition'], $methods ) ) >= 1;
				}
				break;
			case 'none':
				if ( is_array( $rule_data['condition'] ) && is_array( $methods ) ) {
					$result = count( array_intersect( $rule_data['condition'], $methods ) ) === 0;
				}
				break;

			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class xlwcty_Rule_Order_Is_Renewal extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_is_renewal' );
	}

	public function get_possibile_rule_operators() {
		return null;
	}

	public function get_possibile_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_Rule_Is_Renewal';
	}

	public function is_match( $rule_data, $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( 'shop_order' === $order->order_type && isset( $order->subscription_renewal ) && $order->subscription_renewal > 0 ) { // It's a parent order or original order
			$is_renewal = true;
		} else {
			$is_renewal = false;
		}

		return apply_filters( 'woocommerce_subscriptions_is_renewal_order', $is_renewal, $order );
	}

}

class xlwcty_Rule_Order_Billing_Country extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_billing_country' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'any'  => __( 'matched any of', 'thank-you-page-for-woocommerce-nextmove' ),
			'none' => __( 'matches none of ', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$result = WC()->countries->get_allowed_countries();

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $order_id ) {
		$type            = $rule_data['operator'];
		$order           = wc_get_order( $order_id );
		$billing_country = XLWCTY_Compatibility::get_billing_country_from_order( $order );

		if ( empty( $billing_country ) ) {
			return $this->return_is_match( false, $rule_data );
		}

		$billing_country = array( $billing_country );

		switch ( $type ) {
			case 'any':
				if ( is_array( $rule_data['condition'] ) && is_array( $billing_country ) ) {
					$result = count( array_intersect( $rule_data['condition'], $billing_country ) ) >= 1;
				}
				break;
			case 'none':
				if ( is_array( $rule_data['condition'] ) && is_array( $billing_country ) ) {
					$result = count( array_intersect( $rule_data['condition'], $billing_country ) ) === 0;
				}
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class xlwcty_Rule_Order_Is_Upgrade extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_is_upgrade' );
	}

	public function get_possibile_rule_operators() {
		return null;
	}

	public function get_possibile_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_Rule_Is_Upgrade';
	}

	public function is_match( $rule_data, $order_id ) {
		$wc_subscription_comp = new XLWCTY_Wc_Subscriptions();
		$result               = $wc_subscription_comp->xl_find_if_order_is_upgrade_or_downgrade( $order_id );
		if ( 'upgrade' === $result ) {
			$is_upgrade = true;
		} else {
			$is_upgrade = false;
		}

		return apply_filters( 'woocommerce_subscriptions_is_order_upgrade', $is_upgrade, $order_id );
	}

}

class xlwcty_Rule_Order_Is_Downgrade extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_is_downgrade' );
	}

	public function get_possibile_rule_operators() {
		return null;
	}

	public function get_possibile_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_Rule_Is_Downgrade';
	}

	public function is_match( $rule_data, $order_id ) {
		$wc_subscription_comp = new XLWCTY_Wc_Subscriptions();
		$result               = $wc_subscription_comp->xl_find_if_order_is_upgrade_or_downgrade( $order_id );
		if ( 'downgrade' === $result ) {
			$is_downgrade = true;
		} else {
			$is_downgrade = false;
		}

		return apply_filters( 'woocommerce_subscriptions_is_order_downgrade', $is_downgrade, $order_id );
	}

}

class xlwcty_Rule_Order_Status extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_status' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'any'  => __( 'matched any of', 'thank-you-page-for-woocommerce-nextmove' ),
			'none' => __( 'matched none of', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$result = array();

		foreach ( wc_get_order_statuses() as $order_key => $order_val ) {
			$result[ $order_key ] = $order_val;
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $order_id ) {
		$type        = $rule_data['operator'];
		$order       = wc_get_order( $order_id );
		$order_state = XLWCTY_Compatibility::get_order_status( $order );

		if ( empty( $order_state ) ) {
			return $this->return_is_match( false, $rule_data );
		}

		$order_state = array( $order_state );

		switch ( $type ) {
			case 'none':
				$result = count( array_intersect( $rule_data['condition'], $order_state ) ) === 0;
				break;
			case 'any':
				$result = count( array_intersect( $rule_data['condition'], $order_state ) ) >= 1;
				break;

			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}
