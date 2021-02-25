<?php

if ( bwfan_is_woocommerce_active() && bwfan_is_woocommerce_membership_active() ) {
	class BWFAN_Rule_Membership_Has_Status extends BWFAN_Rule_Base {
		public function __construct() {
			parent::__construct( 'membership_has_status' );
		}

		public function get_possible_rule_operators() {
			return array(
				'is'     => __( 'is', 'autonami-automations' ),
				'is_not' => __( 'is not', 'autonami-automations' ),
			);
		}

		public function get_condition_input_type() {
			return 'Chosen_Select';
		}

		public function get_possible_rule_values() {
			$statuses           = wc_memberships_get_user_membership_statuses( false, false );
			$statuses_to_return = array();
			foreach ( $statuses as $status ) {
				$statuses_to_return[ $status ] = wc_memberships_get_user_membership_status_name( $status );
			}

			return $statuses_to_return;
		}

		public function is_match( $rule_data ) {
			$status = BWFAN_Core()->rules->getRulesData( 'wc_user_membership_status' );
			if ( empty( $status ) ) {
				$membership_id = BWFAN_Core()->rules->getRulesData( 'wc_user_membership_id' );
				/** @var WC_Memberships_User_Membership $membership */
				$membership = ! empty( $membership_id ) ? wc_memberships_get_user_membership( $membership_id ) : '';
				if ( empty( $membership ) ) {
					return $this->return_is_match( false, $rule_data );
				}

				$status = $membership->get_status();
			}

			$type = $rule_data['operator'];

			switch ( $type ) {
				case 'is':
					if ( is_array( $rule_data['condition'] ) ) {
						$result = in_array( $status, $rule_data['condition'], true ) ? true : false;
					}
					break;
				case 'is_not':
					if ( is_array( $rule_data['condition'] ) ) {
						$result = ! in_array( $status, $rule_data['condition'], true ) ? true : false;
					}
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'Membership status ', 'autonami-automations-pro' )
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= condition %>
			<?php
		}
	}

	class BWFAN_Rule_Active_Membership_Plans extends BWFAN_Rule_Base {
		public function __construct() {
			parent::__construct( 'active_membership_plans' );
		}

		public function get_possible_rule_operators() {
			return array(
				'is'     => __( 'is', 'autonami-automations' ),
				'is_not' => __( 'is not', 'autonami-automations' ),
			);
		}

		public function get_condition_input_type() {
			return 'Chosen_Select';
		}

		public function get_possible_rule_values() {
			$plans           = wc_memberships_get_membership_plans();
			$plans_to_return = array();
			foreach ( $plans as $plan ) {
				$plans_to_return[ $plan->get_id() ] = $plan->get_formatted_name();
			}

			return $plans_to_return;
		}

		public function is_match( $rule_data ) {
			$plan_id = BWFAN_Core()->rules->getRulesData( 'wc_membership_plan_id' );
			$plan    = ! empty( $plan_id ) ? wc_memberships_get_membership_plan( $plan_id ) : '';
			if ( empty( $plan ) ) {
				$membership_id = BWFAN_Core()->rules->getRulesData( 'wc_user_membership_id' );
				/** @var WC_Memberships_User_Membership $membership */
				$membership = ! empty( $membership_id ) ? wc_memberships_get_user_membership( $membership_id ) : '';
				if ( empty( $membership ) ) {
					return $this->return_is_match( false, $rule_data );
				}

				$plan = $membership->get_plan();
			}

			$plan = $plan->get_id();
			$type = $rule_data['operator'];

			switch ( $type ) {
				case 'is':
					if ( is_array( $rule_data['condition'] ) ) {
						$condition = array_map( 'absint', $rule_data['condition'] );
						$result    = in_array( absint( $plan ), $condition, true ) ? true : false;
					}
					break;
				case 'is_not':
					if ( is_array( $rule_data['condition'] ) ) {
						$result = ! in_array( $plan, $rule_data['condition'], true ) ? true : false;
					}
					break;
				default:
					$result = false;
					break;

			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'Membership plan ', 'autonami-automations-pro' )
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= condition %>
			<?php
		}
	}
}
