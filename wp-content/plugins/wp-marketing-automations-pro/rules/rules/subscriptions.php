<?php

if ( bwfan_is_woocommerce_active() && bwfan_is_woocommerce_subscriptions_active() ) {
	class BWFAN_Rule_Active_Subscription extends BWFAN_Rule_Base {

		public $supports = array( 'order' );

		public function __construct() {
			parent::__construct( 'active_subscription' );
		}

		public function get_possible_rule_operators() {
			return null;
		}

		public function get_possible_rule_values() {
			return array(
				'yes' => __( 'Yes', 'autonami-automations-pro' ),
				'no'  => __( 'No', 'autonami-automations-pro' ),
			);
		}

		public function is_match( $rule_data ) {

			$result  = false;
			$order   = BWFAN_Core()->rules->getRulesData( 'wc_order' );
			$user_id = BWFAN_Core()->rules->getRulesData( 'user_id' );
			$email   = BWFAN_Core()->rules->getRulesData( 'email' );

			/** Fetching user id if not available from the order object */
			if ( empty( $user_id ) && $order instanceof WC_Order ) {
				$user_id = $order->get_user_id();
			}

			/** Fetching user id if not available from the user email */
			if ( empty( $user_id ) ) {
				$user_data = get_user_by( 'email', $email );
				if ( $user_data instanceof WP_User ) {
					$user_id = $user_data->ID;
				}
			}

			if ( absint( $user_id ) > 0 && function_exists( 'wcs_user_has_subscription' ) ) {
				$result = wcs_user_has_subscription( $user_id, '', 'active' );
			}

			return ( 'yes' === $rule_data['condition'] ) ? $result : ! $result;
		}

		public function ui_view() {
			esc_html_e( 'Customer', 'autonami-automations-pro' );
			?>
            <%  if (condition == "yes") { %> has <% } %>
            <% if (condition == "no") { %> doesn't have <% } %>

			<?php
			esc_html_e( 'an Active Subscriptions', 'autonami-automations-pro' );
		}
	}

	class BWFAN_Rule_Subscription_Parent_Order_Status extends BWFAN_Rule_Base {

		public $supports = array( 'order' );

		public function __construct() {
			parent::__construct( 'subscription_parent_order_status' );
		}

		public function get_possible_rule_operators() {
			return array(
				'is'     => __( 'is', 'autonami-automations' ),
				'is_not' => __( 'is not', 'autonami-automations' ),
			);
		}

		public function get_possible_rule_values() {
			return wc_get_order_statuses();
		}

		public function get_condition_input_type() {
			return 'Chosen_Select';
		}

		public function is_match( $rule_data ) {
			/** @var WC_Subscription $subscription */
			$subscription = BWFAN_Core()->rules->getRulesData( 'wc_subscription' );
			$order        = $subscription->get_parent();
			if ( ! $order instanceof WC_Order ) {
				return $this->return_is_match( false, $rule_data );
			}

			$order_status = 'wc-' . $order->get_status();
			$type         = $rule_data['operator'];
			switch ( $type ) {
				case 'is':
					if ( is_array( $rule_data['condition'] ) ) {
						$result = in_array( $order_status, $rule_data['condition'], true ) ? true : false;
					}
					break;
				case 'is_not':
					if ( is_array( $rule_data['condition'] ) ) {
						$result = ! in_array( $order_status, $rule_data['condition'], true ) ? true : false;
					}
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'Subscription parent order status ', 'autonami-automations-pro' )
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= condition %>
			<?php
		}
	}

	class BWFAN_Rule_Is_Order_Renewal extends BWFAN_Rule_Base {

		public $supports = array( 'order' );

		public function __construct() {
			parent::__construct( 'is_order_renewal' );
		}

		public function get_possible_rule_operators() {
			return null;
		}

		public function get_possible_rule_values() {
			return array(
				'yes' => __( 'Yes', 'autonami-automations-pro' ),
				'no'  => __( 'No', 'autonami-automations-pro' ),
			);
		}

		public function is_match( $rule_data ) {
			$order  = BWFAN_Core()->rules->getRulesData( 'wc_order' );
			$result = wcs_order_contains_subscription( $order, 'renewal' );

			return ( 'yes' === $rule_data['condition'] ) ? $result : ! $result;
		}

		public function ui_view() {
			esc_html_e( 'Order', 'autonami-automations-pro' );
			?>
            <%  if (condition == "yes") { %> is <% } %>
            <% if (condition == "no") { %> is not <% } %>

			<?php
			esc_html_e( 'a renewal', 'autonami-automations-pro' );
		}
	}

	class BWFAN_Rule_Subscription_Status extends BWFAN_Rule_Base {

		public function __construct() {
			parent::__construct( 'subscription_status' );
		}

		public function get_possible_rule_values() {
			return wcs_get_subscription_statuses();
		}

		public function get_condition_input_type() {
			return 'Chosen_Select';
		}

		public function is_match( $rule_data ) {
			$type                = $rule_data['operator'];
			$subscription        = BWFAN_Core()->rules->getRulesData( 'wc_subscription' );
			$subscription_status = 'wc-' . $subscription->get_status();

			switch ( $type ) {
				case 'is':
					$result = in_array( $subscription_status, $rule_data['condition'], true );
					break;
				case 'is_not':
					$result = ! in_array( $subscription_status, $rule_data['condition'], true );
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'Subscription\'s status ', 'autonami-automations-pro' );
			?>
            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <% var chosen = []; %>
            <% _.each(condition, function( value, key ){ %>
            <% chosen.push(uiData[value]); %>
            <% }); %>

            <%= chosen.join("/ ") %>
			<?php
		}

		public function get_possible_rule_operators() {
			return array(
				'is'     => __( 'is', 'autonami-automations-pro' ),
				'is_not' => __( 'is not', 'autonami-automations-pro' ),
			);
		}
	}

	class BWFAN_Rule_Subscription_Total extends BWFAN_Rule_Base {

		public function __construct() {
			parent::__construct( 'subscription_total' );
			$this->description = '';
		}

		public function get_condition_input_type() {
			return 'Text';
		}

		public function is_match( $rule_data ) {
			$subscription = BWFAN_Core()->rules->getRulesData( 'wc_subscription' );
			$price        = (float) $subscription->get_total();
			$value        = (float) $rule_data['condition'];

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

		public function ui_view() {
			?>
            Subscription Total
            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= condition %>
			<?php
		}

		public function get_possible_rule_operators() {
			return array(
				'==' => __( 'is equal to', 'autonami-automations-pro' ),
				'!=' => __( 'is not equal to', 'autonami-automations-pro' ),
				'>'  => __( 'is greater than', 'autonami-automations-pro' ),
				'<'  => __( 'is less than', 'autonami-automations-pro' ),
				'>=' => __( 'is greater or equal to', 'autonami-automations-pro' ),
				'<=' => __( 'is less or equal to', 'autonami-automations-pro' ),
			);
		}
	}

	class BWFAN_Rule_Subscription_Item extends BWFAN_Rule_Products {
		public $supports = array( 'subscriptions' );

		public function __construct() {
			parent::__construct( 'subscription_item' );
		}

		public function set_product_types_arr() {
			BWFAN_Common::$offer_product_types = [
				'subscription',
				'variable-subscription',
				'subscription_variation'
			];
		}

		public function get_search_results( $term ) {
			$this->set_product_types_arr();
			$array = BWFAN_Common::product_search( $term, true, true );
			wp_send_json( array(
				'results' => $array,
			) );
		}

		public function get_products() {
			$subscription = BWFAN_Core()->rules->getRulesData( 'wc_subscription' );
			$found_ids    = [];

			if ( $subscription->get_items() && is_array( $subscription->get_items() ) && count( $subscription->get_items() ) ) {
				foreach ( $subscription->get_items() as $cart_item ) {

					$product      = $cart_item->get_product();
					$product_id   = $product->get_id();
					$product_id   = ( $product->get_parent_id() ) ? $product->get_parent_id() : $product_id;
					$variation_id = $cart_item->get_variation_id();

					if ( ! empty( $variation_id ) ) {
						array_push( $found_ids, $variation_id );
						array_push( $found_ids, $product_id );
					} else {
						array_push( $found_ids, $product_id );
					}
				}
			}

			return $found_ids;
		}

		public function ui_view() {
			esc_html_e( 'Subscription\'s items ', 'autonami-automations-pro' );
			?>
            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %> <% var chosen = []; %>
            <% _.each(condition, function( value, key ){ %>
            <% chosen.push(uiData[value]); %>

            <% }); %>
            <%= chosen.join("/ ") %>
			<?php
		}

	}

	class BWFAN_Rule_Subscription_Payment_Gateway extends BWFAN_Rule_Base {

		public function __construct() {
			parent::__construct( 'subscription_payment_gateway' );
		}

		public function get_possible_rule_values() {
			$result           = array();
			$result['manual'] = __( 'Manual Renewal', 'woocommerce-subscriptions' );
			foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
				if ( 'yes' === $gateway->enabled && in_array( 'subscriptions', $gateway->supports, true ) ) {
					$result[ $gateway->id ] = $gateway->get_title();
				}
			}

			return $result;
		}

		public function get_condition_input_type() {
			return 'Chosen_Select';
		}

		public function is_match( $rule_data ) {
			$type = $rule_data['operator'];
			/** @var WC_Subscription $subscription */
			$subscription = BWFAN_Core()->rules->getRulesData( 'wc_subscription' );
			$payment      = $subscription->get_payment_method();

			/** if empty then check for manual **/
			if ( empty( $payment ) && $subscription->is_manual() ) {
				$payment = 'manual';
			}

			if ( empty( $payment ) ) {
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

		public function ui_view() {
			esc_html_e( 'Subscription\'s payment gateway ', 'autonami-automations-pro' );
			?>
            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <% var chosen = []; %>
            <% _.each(condition, function( value, key ){ %>
            <% chosen.push(uiData[value]); %>

            <% }); %>
            <%= chosen.join("/ ") %>
			<?php
		}

		public function get_possible_rule_operators() {
			return array(
				'is'     => __( 'is', 'autonami-automations-pro' ),
				'is_not' => __( 'is not', 'autonami-automations-pro' ),
			);
		}
	}

	class BWFAN_Rule_Subscription_Old_Status extends BWFAN_Rule_Base {

		public function __construct() {
			parent::__construct( 'subscription_old_status' );
		}

		public function get_possible_rule_values() {
			return wcs_get_subscription_statuses();
		}

		public function get_condition_input_type() {
			return 'Chosen_Select';
		}

		public function is_match( $rule_data ) {
			$type                    = $rule_data['operator'];
			$subscription_old_status = 'wc-' . BWFAN_Core()->rules->getRulesData( 'wcs_old_status' );

			switch ( $type ) {
				case 'is':
					$result = in_array( $subscription_old_status, $rule_data['condition'], true );
					break;
				case 'is_not':
					$result = ! in_array( $subscription_old_status, $rule_data['condition'], true );
					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			esc_html_e( 'Subscription\'s old status ', 'autonami-automations-pro' );
			?>
            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <% var chosen = []; %>
            <% _.each(condition, function( value, key ){ %>
            <% chosen.push(uiData[value]); %>
            <% }); %>

            <%= chosen.join("/ ") %>
			<?php
		}

		public function get_possible_rule_operators() {
			return array(
				'is'     => __( 'is', 'autonami-automations-pro' ),
				'is_not' => __( 'is not', 'autonami-automations-pro' ),
			);
		}
	}

	/** this rule will only show when retry failed payment option enabled **/
	if ( 'yes' === get_option( 'woocommerce_subscriptions_enable_retry' ) ) {

		class BWFAN_Rule_Subscription_Failed_Attempt extends BWFAN_Rule_Base {

			public function __construct() {
				parent::__construct( 'subscription_failed_attempt' );
				$this->description = '';
			}

			public function get_condition_input_type() {
				return 'Text';
			}

			public function is_match( $rule_data ) {
				/** @var WC_Subscription $subscription */
				$subscription = BWFAN_Core()->rules->getRulesData( 'wc_subscription' );

				$order_id = $subscription->get_last_order();
				if ( 0 === absint( $order_id ) ) {
					return $this->return_is_match( false, $rule_data );
				}

				/** @var WCS_Retry[] $retries */
				$retries = WCS_Retry_Manager::store()->get_retries_for_order( $order_id );

				if ( ! is_array( $retries ) || 0 === count( $retries ) ) {
					return $this->return_is_match( false, $rule_data );
				}

				$retries = array_filter( $retries, function ( $retry ) {
					return 'failed' === $retry->get_status();
				} );

				$retries = count( $retries );
				$value   = (int) $rule_data['condition'];

				switch ( $rule_data['operator'] ) {
					case '==':
						$result = $retries === $value;
						break;
					case '!=':
						$result = $retries !== $value;
						break;
					case '>':
						$result = $retries > $value;
						break;
					case '<':
						$result = $retries < $value;
						break;
					case '>=':
						$result = $retries >= $value;
						break;
					case '<=':
						$result = $retries <= $value;
						break;
					default:
						$result = false;
						break;
				}

				return $this->return_is_match( $result, $rule_data );
			}

			public function ui_view() {
				?>
                Subscription Failed Attempt
                <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

                <%= ops[operator] %>
                <%= condition %>
				<?php
			}

			public function get_possible_rule_operators() {
				return array(
					'==' => __( 'is equal to', 'autonami-automations-pro' ),
					'!=' => __( 'is not equal to', 'autonami-automations-pro' ),
					'>'  => __( 'is greater than', 'autonami-automations-pro' ),
					'<'  => __( 'is less than', 'autonami-automations-pro' ),
					'>=' => __( 'is greater or equal to', 'autonami-automations-pro' ),
					'<=' => __( 'is less or equal to', 'autonami-automations-pro' ),
				);
			}
		}
	}
}
