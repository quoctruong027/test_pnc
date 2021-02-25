<?php
defined( 'ABSPATH' ) || exit;

class xlwcty_Rule_Is_First_Order extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'is_first_order' );
	}

	public function get_possibile_rule_operators() {
		return null;
	}

	public function get_possibile_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_Rule_Is_First_Order';
	}

	public function is_match( $rule_data, $order_id ) {

		$order         = wc_get_order( $order_id );
		$user_id       = $order->get_user_id();
		$billing_email = XLWCTY_Compatibility::get_order_data( $order, 'billing_email' );

		$orders = wc_get_orders( array(
			'customer' => $user_id ? $user_id : $billing_email,
			'limit'    => 1,
			'return'   => 'ids',
			'exclude'  => array( $order->id ),
		) );

		$is_first = empty( $orders );

		return $is_first;
	}

}

class xlwcty_Rule_Is_Guest extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'is_guest' );
	}

	public function get_possibile_rule_operators() {
		return null;
	}

	public function get_possibile_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_Rule_Is_Guest';
	}

	public function is_match( $rule_data, $order_id ) {

		$order    = wc_get_order( $order_id );
		$is_guest = $order->get_user_id() === 0;

		return $is_guest;
	}

}

class xlwcty_Rule_Customer_Order_Count extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'customer_order_count' );
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

	public function is_match( $rule_data, $order_id ) {
		$order  = wc_get_order( $order_id );
		$result = false;
		$id     = absint( $order->get_user_id() );

		if ( $id === 0 ) {
			return $this->return_is_match( $result, $rule_data );
		}

		$count = xlwcty_Common::get_customer_order_count( $id );
		$value = (float) $rule_data['condition'];
		switch ( $rule_data['operator'] ) {
			case '==':
				$result = $count == $value;
				break;
			case '!=':
				$result = $count != $value;
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


class xlwcty_Rule_Customer_User extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'customer_user' );
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
		$users  = get_users();

		if ( $users ) {
			foreach ( $users as $user ) {
				$result[ $user->ID ] = $user->display_name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $order_id ) {
		$order  = wc_get_order( $order_id );
		$id     = $order->get_user_id();
		$result = in_array( $id, $rule_data['condition'] );
		$result = $rule_data['operator'] == 'in' ? $result : ! $result;

		return $this->return_is_match( $result, $rule_data );
	}
}

class xlwcty_Rule_Customer_Role extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'customer_role' );
	}

	public function get_possibile_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'thank-you-page-for-woocommerce-nextmove' ),
			'notin' => __( 'is not', 'thank-you-page-for-woocommerce-nextmove' ),
		);

		return $operators;
	}

	public function get_possibile_rule_values() {
		$result         = array();
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

	public function is_match( $rule_data, $order_id ) {
		$order  = wc_get_order( $order_id );
		$result = false;
		$id     = $order->get_user_id();
		if ( $rule_data['condition'] && is_array( $rule_data['condition'] ) ) {
			foreach ( $rule_data['condition'] as $role ) {
				$result |= user_can( $id, $role );
			}
		}

		$result = $rule_data['operator'] === 'in' ? $result : ! $result;

		return $this->return_is_match( $result, $rule_data );
	}
}


class xlwcty_Rule_Guest_Order_Count extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'guest_order_count' );
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

	public function is_match( $rule_data, $order_id ) {
		$order    = wc_get_order( $order_id );
		$result   = false;
		$is_guest = $order->get_user_id() === 0;

		$email = XLWCTY_Compatibility::get_order_data( $order, 'billing_email' );
		if ( ! $is_guest ) {
			$this->return_is_match( $result, $rule_data );
		}

		$count = xlwcty_Common::get_guest_order_count( $email );
		$value = (float) $rule_data['condition'];
		switch ( $rule_data['operator'] ) {
			case '==':
				$result = $count == $value;
				break;
			case '!=':
				$result = $count != $value;
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

class xlwcty_Rule_Customer_Total_Spent extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'customer_total_spent' );
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

	public function is_match( $rule_data, $order_id ) {
		$order = wc_get_order( $order_id );
		$id    = $order->get_user_id();
		$count = xlwcty_Common::get_customer_total_spent( $id );
		$value = (float) $rule_data['condition'];
		switch ( $rule_data['operator'] ) {
			case '==':
				$result = $count == $value;
				break;
			case '!=':
				$result = $count != $value;
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

class xlwcty_Rule_Guest_Total_Spent extends xlwcty_Rule_Base {

	public function __construct() {
		parent::__construct( 'guest_total_spent' );
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

	public function is_match( $rule_data, $order_id ) {
		$order    = wc_get_order( $order_id );
		$result   = false;
		$is_guest = $order->get_user_id() === 0;

		if ( ! $is_guest ) {
			$this->return_is_match( $result, $rule_data );
		}

		$email = XLWCTY_Compatibility::get_order_data( $order, 'billing_email' );
		$count = xlwcty_Common::get_guest_total_spent( $email );
		$value = (float) $rule_data['condition'];
		switch ( $rule_data['operator'] ) {
			case '==':
				$result = $count == $value;
				break;
			case '!=':
				$result = $count != $value;
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
