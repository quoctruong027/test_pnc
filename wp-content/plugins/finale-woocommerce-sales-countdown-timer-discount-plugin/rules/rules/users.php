<?php

class WCCT_Rule_Users_Role extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'users_role' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();

		$editable_roles = get_editable_roles();

		if ( $editable_roles ) {
			foreach ( $editable_roles as $role => $details ) {
				$name            = translate_user_role( $details['name'] );
				$result[ $role ] = $name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $productID ) {
		$result = false;

		if ( isset( $rule_data['condition'] ) && $rule_data['condition'] && is_array( $rule_data['condition'] ) ) {
			foreach ( $rule_data['condition'] as $role ) {
				$result |= current_user_can( $role );
			}
		}

		$result = $rule_data['operator'] == 'in' ? $result : ! $result;

		return $this->return_is_match( $result, $rule_data );
	}

	public function sort_attribute_taxonomies( $taxa, $taxb ) {
		return strcmp( $taxa->attribute_name, $taxb->attribute_name );
	}

}

class WCCT_Rule_Users_User extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'users_user' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();

		$users = get_users();

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

	public function is_match( $rule_data, $productID ) {
		$result = false;
		if ( isset( $rule_data['condition'] ) && $rule_data['condition'] && $rule_data['condition'] !== '' ) {
			$result = in_array( get_current_user_id(), $rule_data['condition'] );
			$result = $rule_data['operator'] == 'in' ? $result : ! $result;
		}

		return $this->return_is_match( $result, $rule_data );
	}
}

class WCCT_Rule_Users_Guest extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'users_guest' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_Guests';
	}

	public function is_match( $rule_data, $productID ) {
		if ( is_user_logged_in() && current_user_can( 'administrator' ) && is_admin() ) {
			return true;
		}

		return ! is_user_logged_in();
	}
}

if ( class_exists( 'WC_Memberships' ) ) {

	class WCCT_Rule_Users_WC_Membership extends WCCT_Rule_Base {

		public function __construct() {
			parent::__construct( 'users_wc_membership' );
		}

		public function get_possible_rule_operators() {
			$operators = array(
				'in'    => __( 'matches any of', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
				'notin' => __( 'matches none of', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			);

			return $operators;
		}

		public function get_possible_rule_values() {

			$all_memberships = wc_memberships()->get_plans_instance()->get_membership_plans();

			$result = array();

			if ( is_array( $all_memberships ) && $all_memberships && count( $all_memberships ) > 0 ) {
				foreach ( $all_memberships as $membership ) {
					$result[ $membership->slug ] = $membership->name;
				}
			}

			return $result;
		}

		public function get_condition_input_type() {
			return 'Chosen_Select';
		}

		public function is_match( $rule_data, $productID ) {

			$result = false;
			if ( isset( $rule_data['condition'] ) && $rule_data['condition'] && $rule_data['condition'] !== '' ) {

				foreach ( $rule_data['condition'] as $condition ) {
					$result_itr = wc_memberships_is_user_active_member( get_current_user_id(), $condition );
					if ( $result_itr ) {
						$result = true;
						break;
					}
				}
				$result = $rule_data['operator'] == 'in' ? $result : ! $result;
			}

			return $this->return_is_match( $result, $rule_data );
		}
	}
}
