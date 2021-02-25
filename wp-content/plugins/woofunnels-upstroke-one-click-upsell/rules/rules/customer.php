<?php

class WFOCU_Rule_Is_First_Order extends WFOCU_Rule_Base {

	public $supports = array( 'order' );

	public function __construct() {
		parent::__construct( 'is_first_order' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_Rule_Is_First_Order';
	}

	public function is_match( $rule_data, $env = 'cart' ) {
		$is_first = false;

		$order_id      = WFOCU_Core()->rules->get_environment_var( 'order' );
		$order         = wc_get_order( $order_id );
		$billing_email = WFOCU_WC_Compatibility::get_order_data( $order, 'billing_email' );


		$orders = wc_get_orders( array(
			'customer' => $billing_email,
			'limit'    => 2,
			'return'   => 'ids',
		) );
		if ( count( $orders ) == 1 ) {
			return true;
		}

		return $is_first;
	}

}


class WFOCU_Rule_Customer_User extends WFOCU_Rule_Base {
	public $supports = array( 'order' );

	public function __construct() {
		parent::__construct( 'customer_user' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'woofunnels-upstroke-one-click-upsell' ),
			'notin' => __( 'is not', 'woofunnels-upstroke-one-click-upsell' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'User_Select';
	}

	public function is_match( $rule_data, $env = 'cart' ) {

		$order_id = WFOCU_Core()->rules->get_environment_var( 'order' );
		$order    = wc_get_order( $order_id );
		$id       = $order->get_user_id();

		$result = in_array( $id, $rule_data['condition'] );
		$result = $rule_data['operator'] == 'in' ? $result : ! $result;

		return $this->return_is_match( $result, $rule_data );
	}
}

class WFOCU_Rule_Customer_Role extends WFOCU_Rule_Base {

	public $supports = array( 'order' );

	public function __construct() {
		parent::__construct( 'customer_role' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'woofunnels-upstroke-one-click-upsell' ),
			'notin' => __( 'is not', 'woofunnels-upstroke-one-click-upsell' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();

		$editable_roles = get_editable_roles();

		if ( $editable_roles ) {
			foreach ( $editable_roles as $role => $details ) {
				$name = translate_user_role( $details['name'] );

				$result[ $role ] = $name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $env = 'cart' ) {
		$order_id = WFOCU_Core()->rules->get_environment_var( 'order' );
		$order    = wc_get_order( $order_id );
		$id       = $order->get_user_id();
		$count    = 0;
		if ( $rule_data['condition'] && is_array( $rule_data['condition'] ) ) {
			foreach ( $rule_data['condition'] as $role ) {

				/**
				 * This is a bitwise operator used below, it will true on any true returns.
				 */
				$count |= user_can( $id, $role );
			}
		}


		if ( $rule_data['operator'] == 'in' ) {
			return wc_string_to_bool( $count );
		} else {
			return ! wc_string_to_bool( $count );
		}

	}
}
class WFOCU_Rule_Is_Guest extends WFOCU_Rule_Base {
	public $supports = array( 'order' );

	public function __construct() {
		parent::__construct( 'is_guest' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		$operators = array(
			'yes' => __( 'Yes', 'wp-marketing-automations' ),
			'no'  => __( 'No', 'wp-marketing-automations' ),
		);

		return $operators;
	}

	public function is_match( $rule_data,$env = 'cart' ) {
		$order_id = WFOCU_Core()->rules->get_environment_var( 'order' );
		$order    = wc_get_order( $order_id );
		if ( ! empty( $order ) ) {
			$result = ( $order->get_user_id() === 0 );

			return ( 'yes' === $rule_data['condition'] ) ? $result : ! $result;
		}
		return true;

	}



}