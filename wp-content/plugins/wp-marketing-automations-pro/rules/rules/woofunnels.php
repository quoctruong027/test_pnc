<?php
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WFACP_Common' ) ) {
	class BWFAN_Rule_Aerocheckout extends BWFAN_Rule_Base {

		public function __construct() {
			parent::__construct( 'aerocheckout' );
		}

		public function get_possible_rule_operators() {
			return array(
				'in'    => __( 'is', 'wp-marketing-automations' ),
				'notin' => __( 'is not', 'wp-marketing-automations' ),
			);
		}

		public function get_possible_rule_values() {
			$result = array();
			$data   = WFACP_Common::get_saved_pages();

			if ( is_array( $data ) && count( $data ) > 0 ) {

				foreach ( $data as $v ) {
					$result[ $v['ID'] ] = $v['post_title'] . " (#{$v['ID']})";
				}
			}

			return $result;
		}

		public function get_condition_input_type() {
			return 'Chosen_Select';
		}

		/**
		 * @param $rule_data
		 *
		 * @return bool
		 */
		public function is_match( $rule_data ) {
			$result = false;

			if ( isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

				/** could be abandonment event or order created event */
				$abandoned_data = BWFAN_Core()->rules->getRulesData( 'abandoned_data' );
				$order          = BWFAN_Core()->rules->getRulesData( 'wc_order' );

				$aero_id = false;

				if ( isset( $abandoned_data['checkout_data'] ) ) {
					/** Abandonment event */
					$checkout_data = json_decode( $abandoned_data['checkout_data'] );
					$aero_id       = ( isset( $checkout_data->aerocheckout_page_id ) && ! empty( $checkout_data->aerocheckout_page_id ) ) ? $checkout_data->aerocheckout_page_id : false;

				} elseif ( $order instanceof WC_Order ) {
					/** Order created event */
					$aero_id = $order->get_meta( '_wfacp_post_id', true );
				}

				if ( empty( $aero_id ) ) {
					return $this->return_is_match( $result, $rule_data );
				}

				$in = false;

				$rule_data['condition'] = array_map( 'intval', $rule_data['condition'] );

				if ( in_array( intval( $aero_id ), $rule_data['condition'], true ) ) {
					$in = true;
				}

				$result = 'in' === $rule_data['operator'] ? $in : ! $in;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			echo esc_html__( 'AeroCheckout Page', 'autonami-automations-pro' );
			?>
            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>
            <%= ops[operator] %> <% var chosen = []; %>
            <% _.each(condition, function( value, key ){ %>
            <% chosen.push(uiData[value]); %>
            <% }); %>
            <%= chosen.join("/") %>
			<?php
		}
	}
}

if ( class_exists( 'WFOCU_Common' ) ) {
	class BWFAN_Rule_Upstroke_Funnels extends BWFAN_Dynamic_Option_Base {

		public function __construct() {
			parent::__construct( 'upstroke_funnels' );
		}

		public function get_possible_rule_operators() {
			return array(
				'any'  => __( 'matches any of', 'wp-marketing-automations' ),
				'none' => __( 'matches none of', 'wp-marketing-automations' ),
			);
		}

		public function get_possible_rule_values() {
			$result = array();
			$data   = BWFAN_PRO_Common::get_upstroke_funnels();

			if ( is_array( $data ) && count( $data ) > 0 ) {

				foreach ( $data as $v ) {
					$result[ $v['id'] ] = $v['post_title'];
				}
			}

			return $result;
		}

		public function get_condition_input_type() {
			return 'Chosen_Select';
		}

		public function conditions_view() {
			$condition_input_type = $this->get_condition_input_type();
			$values               = $this->get_possible_rule_values();
			$value_args           = array(
				'input'       => $condition_input_type,
				'name'        => 'bwfan_rule[<%= groupId %>][<%= ruleId %>][condition]',
				'choices'     => $values,
				'search_type' => $this->get_search_type_name(),
				'rule_type'   => $this->rule_type,
			);

			bwfan_Input_Builder::create_input_field( $value_args );
		}

		public function is_match( $rule_data ) {
			$type                = $rule_data['operator'];
			$upstroke_funnel_id  = BWFAN_Core()->rules->getRulesData( 'upstroke_funnel_id' );
			$upstroke_funnel_ids = array( $upstroke_funnel_id );

			switch ( $type ) {
				case 'any':
					if ( is_array( $rule_data['condition'] ) && is_array( $upstroke_funnel_ids ) ) {
						$result = count( array_intersect( $rule_data['condition'], $upstroke_funnel_ids ) ) >= 1;
					}
					break;

				case 'none':
					if ( is_array( $rule_data['condition'] ) && is_array( $upstroke_funnel_ids ) ) {
						$result = count( array_intersect( $rule_data['condition'], $upstroke_funnel_ids ) ) === 0;
					}
					break;

				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			echo esc_html__( 'Funnels', 'autonami-automations-pro' );
			?>
            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %> <% var chosen = []; %>
            <% _.each(condition, function( value, key ){ %>
            <% chosen.push(uiData[value]); %>

            <% }); %>
            <%= chosen.join("/") %>
			<?php
		}

		public function get_condition_values_nice_names( $values ) {
			$return = [];
			if ( count( $values ) > 0 ) {
				$return = BWFAN_PRO_Common::get_upstroke_funnel_nice_name( $values );
			}

			return $return;
		}

	}

	class BWFAN_Rule_Upstroke_Offers extends BWFAN_Dynamic_Option_Base {

		public function __construct() {
			parent::__construct( 'upstroke_offers' );
		}

		public function get_possible_rule_operators() {
			return array(
				'any'  => __( 'matches any of', 'wp-marketing-automations' ),
				'none' => __( 'matches none of', 'wp-marketing-automations' ),
			);
		}

		public function get_possible_rule_values() {
			$result = array();
			$data   = BWFAN_PRO_Common::get_upstroke_offers();

			if ( is_array( $data ) && count( $data ) > 0 ) {

				foreach ( $data as $v ) {
					$result[ $v['id'] ] = $v['post_title'];
				}
			}

			return $result;
		}

		public function get_condition_input_type() {
			return 'Chosen_Select';
		}

		public function conditions_view() {
			$condition_input_type = $this->get_condition_input_type();
			$values               = $this->get_possible_rule_values();
			$value_args           = array(
				'input'       => $condition_input_type,
				'name'        => 'bwfan_rule[<%= groupId %>][<%= ruleId %>][condition]',
				'choices'     => $values,
				'search_type' => $this->get_search_type_name(),
				'rule_type'   => $this->rule_type,
			);

			bwfan_Input_Builder::create_input_field( $value_args );
		}

		public function is_match( $rule_data ) {
			$type               = $rule_data['operator'];
			$upstroke_offer_id  = BWFAN_Core()->rules->getRulesData( 'upstroke_offer_id' );
			$upstroke_offer_ids = array( $upstroke_offer_id );

			switch ( $type ) {
				case 'any':
					if ( is_array( $rule_data['condition'] ) && is_array( $upstroke_offer_ids ) ) {
						$result = count( array_intersect( $rule_data['condition'], $upstroke_offer_ids ) ) >= 1;
					}
					break;

				case 'none':
					if ( is_array( $rule_data['condition'] ) && is_array( $upstroke_offer_ids ) ) {
						$result = count( array_intersect( $rule_data['condition'], $upstroke_offer_ids ) ) === 0;
					}
					break;

				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			echo esc_html__( 'Offer', 'autonami-automations-pro' );
			?>
            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>
            <% var possible_values = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_values() ); ?>'); %>

            <%= ops[operator] %> <% var chosen = []; %>
            <% _.each(condition, function( value, key ){ %>
            <% chosen.push("'"+possible_values[value]+"'"); %>

            <% }); %>
            <%= chosen.join(", ") %>
			<?php
		}

		public function get_condition_values_nice_names( $values ) {
			$return = [];
			if ( count( $values ) > 0 ) {
				$return = BWFAN_PRO_Common::get_upstroke_offer_nice_name( $values );
			}

			return $return;
		}
	}
}
