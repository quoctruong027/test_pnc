<?php

if ( class_exists( 'WooCommerce' ) ) {
	class BWFAN_Rule_Customer_Order_Count extends BWFAN_Rule_Base {

		public $supports = array( 'order' );

		public function __construct() {
			$this->need_order_sync = true;
			parent::__construct( 'customer_order_count' );
		}

		public function get_condition_input_type() {
			return 'Text';
		}

		public function is_match( $rule_data ) {
			/**
			 * @var Woofunnels_Customer $customer
			 */
			$customer = BWFAN_Core()->rules->getRulesData( 'bwf_customer' );

			if ( ! $customer instanceof WooFunnels_Customer ) {
				$email = BWFAN_Core()->rules->getRulesData( 'email' );
				if ( ! is_email( $email ) ) {
					$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
					$email = $order instanceof WC_Order ? $order->get_billing_email() : false;
				}

				if ( ! is_email( $email ) ) {
					return $this->return_is_match( false, $rule_data );
				}

				$customer = BWFAN_PRO_Common::get_bwf_customer_by_email( $email );

				if ( ! $customer instanceof WooFunnels_Customer ) {
					return $this->return_is_match( false, $rule_data );
				}
			}

			$count = absint( $customer->get_total_order_count() );
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

		public function ui_view() {
			esc_html_e( 'Order count', 'autonami-automations-pro' );
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= condition %>
			<?php
		}

		public function get_possible_rule_operators() {
			return array(
				'==' => __( 'is equal to', 'wp-marketing-automations' ),
				'!=' => __( 'is not equal to', 'wp-marketing-automations' ),
				'>'  => __( 'is greater than', 'wp-marketing-automations' ),
				'<'  => __( 'is less than', 'wp-marketing-automations' ),
				'>=' => __( 'is greater or equal to', 'wp-marketing-automations' ),
				'<=' => __( 'is less or equal to', 'wp-marketing-automations' ),
			);
		}

	}

	class BWFAN_Rule_Customer_Total_Spent extends BWFAN_Rule_Base {
		public $supports = array( 'order' );

		public function __construct() {
			parent::__construct( 'customer_total_spent' );
		}

		public function get_condition_input_type() {
			return 'Text';
		}

		public function is_match( $rule_data ) {
			/**
			 * @var Woofunnels_Customer $customer
			 */
			$customer = BWFAN_Core()->rules->getRulesData( 'bwf_customer' );

			if ( ! $customer instanceof WooFunnels_Customer ) {
				$email = BWFAN_Core()->rules->getRulesData( 'email' );
				if ( ! is_email( $email ) ) {
					$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
					$email = $order instanceof WC_Order ? $order->get_billing_email() : false;
				}

				if ( ! is_email( $email ) ) {
					return $this->return_is_match( false, $rule_data );
				}

				$customer = BWFAN_PRO_Common::get_bwf_customer_by_email( $email );

				if ( ! $customer instanceof WooFunnels_Customer ) {
					return $this->return_is_match( false, $rule_data );
				}
			}

			$count = (float) $customer->get_total_order_value();
			$value = (float) $rule_data['condition'];

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

		public function ui_view() {
			esc_html_e( 'Customer total spent', 'autonami-automations-pro' );
			?>

            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <%= condition %>
			<?php
		}

		public function get_possible_rule_operators() {
			return array(
				'==' => __( 'is equal to', 'wp-marketing-automations' ),
				'!=' => __( 'is not equal to', 'wp-marketing-automations' ),
				'>'  => __( 'is greater than', 'wp-marketing-automations' ),
				'<'  => __( 'is less than', 'wp-marketing-automations' ),
				'>=' => __( 'is greater or equal to', 'wp-marketing-automations' ),
				'<=' => __( 'is less or equal to', 'wp-marketing-automations' ),
			);
		}

	}

	class BWFAN_Rule_Customer_Purchased_Products extends BWFAN_Rule_Products {

		public function __construct() {
			parent::__construct( 'customer_purchased_products' );
		}

		public function is_match( $rule_data ) {
			/**
			 * @var Woofunnels_Customer $customer
			 */
			$customer = BWFAN_Core()->rules->getRulesData( 'bwf_customer' );

			if ( ! $customer instanceof WooFunnels_Customer ) {
				$email = BWFAN_Core()->rules->getRulesData( 'email' );
				if ( ! is_email( $email ) ) {
					$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
					$email = $order instanceof WC_Order ? $order->get_billing_email() : false;
				}

				if ( ! is_email( $email ) ) {
					return $this->return_is_match( false, $rule_data );
				}

				$customer = BWFAN_PRO_Common::get_bwf_customer_by_email( $email );

				if ( ! $customer instanceof WooFunnels_Customer ) {
					return $this->return_is_match( false, $rule_data );
				}
			}

			$products      = $customer->get_purchased_products();
			$rule_products = array_map( 'absint', $rule_data['condition'] );
			$result        = $this->validate_set( $rule_products, $products, $rule_data['operator'] );

			return $this->return_is_match( $result, $rule_data );
		}

		public function validate_set( $products, $found_products, $operator ) {
			switch ( $operator ) {
				case 'any':
					$result = count( array_intersect( $products, $found_products ) ) > 0;
					break;
				case 'all':
					$result = count( array_intersect( $products, $found_products ) ) === count( $products );
					break;
				case 'none':
					$result = count( array_intersect( $products, $found_products ) ) === 0;
					break;

				default:
					$result = false;
					break;
			}

			return $result;
		}

		public function ui_view() {
			esc_html_e( 'Customer\'s Past Purchased Products', 'autonami-automations-pro' );
			?>
            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %> <% var chosen = []; %>
            <% _.each(condition, function( value, key ){ %>
            <% chosen.push(uiData[value]); %>

            <% }); %>
            <%= chosen.join(" | ") %>
			<?php
		}

	}

	class BWFAN_Rule_Customer_Purchased_Cat extends BWFAN_Rule_Term_Taxonomy {

		public $taxonomy_name = 'product_cat';

		public function __construct() {
			parent::__construct( 'customer_purchased_cat' );
		}

		public function get_term_ids() {
			/**
			 * @var Woofunnels_Customer $customer
			 */
			/**
			 * @var Woofunnels_Customer $customer
			 */
			$customer = BWFAN_Core()->rules->getRulesData( 'bwf_customer' );

			if ( ! $customer instanceof WooFunnels_Customer ) {
				$email = BWFAN_Core()->rules->getRulesData( 'email' );
				if ( ! is_email( $email ) ) {
					$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
					$email = $order instanceof WC_Order ? $order->get_billing_email() : false;
				}

				if ( ! is_email( $email ) ) {
					return array();
				}

				$customer = BWFAN_PRO_Common::get_bwf_customer_by_email( $email );

				if ( ! $customer instanceof WooFunnels_Customer ) {
					return array();
				}
			}

			return $customer->get_purchased_products_cats();
		}

		public function get_possible_rule_operators() {
			return array(
				'any'  => __( 'matches any of', 'wp-marketing-automations' ),
				'all'  => __( 'matches all of ', 'wp-marketing-automations' ),
				'none' => __( 'matches none of ', 'wp-marketing-automations' ),
			);
		}

		public function ui_view() {
			esc_html_e( 'Customer\'s Ever Purchased Product\'s Category', 'autonami-automations-pro' );
			?>
            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %><% var chosen = []; %>
            <% _.each(condition, function( value, key ){ %>
            <% chosen.push(uiData[value]); %>

            <% }); %>
            <%= chosen.join("/") %>
			<?php

		}

	}

	class BWFAN_Rule_Customer_Purchased_Tags extends BWFAN_Rule_Term_Taxonomy {

		public $taxonomy_name = 'product_tag';

		public function __construct() {
			parent::__construct( 'customer_purchased_tags' );
		}

		public function get_term_ids() {
			/**
			 * @var Woofunnels_Customer $customer
			 */
			$customer = BWFAN_Core()->rules->getRulesData( 'bwf_customer' );

			if ( ! $customer instanceof WooFunnels_Customer ) {
				$email = BWFAN_Core()->rules->getRulesData( 'email' );
				if ( ! is_email( $email ) ) {
					$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
					$email = $order instanceof WC_Order ? $order->get_billing_email() : false;
				}

				if ( ! is_email( $email ) ) {
					return array();
				}

				$customer = BWFAN_PRO_Common::get_bwf_customer_by_email( $email );

				if ( ! $customer instanceof WooFunnels_Customer ) {
					return array();
				}
			}

			return $customer->get_purchased_products_tags();
		}


	}

	class BWFAN_Rule_Customer_Country extends BWFAN_Rule_Country {

		public function __construct() {
			parent::__construct( 'customer_country' );
		}

		public function get_objects_country() {
			/**
			 * @var Woofunnels_Customer $customer
			 */
			$customer = BWFAN_Core()->rules->getRulesData( 'bwf_customer' );
			$contact  = $customer instanceof WooFunnels_Customer ? $customer->contact : false;

			if ( ! $contact instanceof WooFunnels_Contact ) {
				$email = BWFAN_Core()->rules->getRulesData( 'email' );
				if ( ! is_email( $email ) ) {
					$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
					$email = $order instanceof WC_Order ? $order->get_billing_email() : false;
				}

				if ( ! is_email( $email ) ) {
					return false;
				}

				$contact = BWFAN_PRO_Common::get_bwf_contact_by_email( $email );

				if ( ! $contact instanceof WooFunnels_Contact ) {
					return false;
				}
			}

			$billing_country = $contact->meta->country;
			if ( empty( $billing_country ) ) {
				return false;
			}

			$billing_country = array( $billing_country );

			return $billing_country;
		}

		public function ui_view() {
			esc_html_e( 'Customer Country', 'autonami-automations-pro' );
			?>
            <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

            <%= ops[operator] %>
            <% var chosen = []; %>
            <% _.each(condition, function( value, key ){ %>
            <% chosen.push(uiData[value]); %>

            <% }); %>
            <%= chosen.join("/") %>
			<?php
		}

	}
}

class BWFAN_Rule_Customer_Is_WP_User extends BWFAN_Rule_Base {
	public $supports = array( 'order' );

	public function __construct() {
		parent::__construct( 'customer_is_wp_user' );
	}

	public function get_possible_rule_values() {
		$operators = array(
			'yes' => __( 'Yes', 'wp-marketing-automations' ),
			'no'  => __( 'No', 'wp-marketing-automations' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Select';
	}

	public function is_match( $rule_data ) {
		$value = false;
		/**
		 * @var Woofunnels_Customer $customer
		 */
		$customer = BWFAN_Core()->rules->getRulesData( 'bwf_customer' );
		$contact  = $customer instanceof WooFunnels_Customer ? $customer->contact : false;

		if ( ! $contact instanceof WooFunnels_Contact ) {
			$email = BWFAN_Core()->rules->getRulesData( 'email' );
			if ( ! is_email( $email ) && class_exists( 'WooCommerce' ) ) {
				$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
				$email = $order instanceof WC_Order ? $order->get_billing_email() : false;
			}

			if ( is_email( $email ) ) {
				$contact = BWFAN_PRO_Common::get_bwf_contact_by_email( $email );
			}
		}

		if ( $contact instanceof WooFunnels_Contact && $contact->get_wpid() > 0 ) {
			$value = true;
		}

		return $this->return_is_match( ( 'yes' === $rule_data['condition'] ) ? $value : ! $value, $rule_data );
	}

	public function ui_view() {
		esc_html_e( 'Customer ', 'autonami-automations-pro' );
		?>
        <% if (condition == "yes") { %>is<% } %>
        <% if (condition == "no") { %>is not<% } %>
		<?php
		esc_html_e( ' a WordPress User', 'autonami-automations-pro' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

}

class BWFAN_Rule_Customer_Custom_Field extends BWFAN_Rule_Custom_Field {

	public function __construct() {
		parent::__construct( 'customer_custom_field' );
	}

	public function get_possible_value( $key ) {
		/**
		 * @var Woofunnels_Customer $customer
		 */
		$customer = BWFAN_Core()->rules->getRulesData( 'bwf_customer' );
		$contact  = $customer instanceof WooFunnels_Customer ? $customer->contact : false;

		if ( ! $contact instanceof WooFunnels_Contact ) {
			$email = BWFAN_Core()->rules->getRulesData( 'email' );
			if ( ! is_email( $email ) && class_exists( 'WooCommerce' ) ) {
				$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
				$email = $order instanceof WC_Order ? $order->get_billing_email() : false;
			}

			if ( ! is_email( $email ) ) {
				return false;
			}

			$contact = BWFAN_PRO_Common::get_bwf_contact_by_email( $email );

			if ( ! $contact instanceof WooFunnels_Contact ) {
				return false;
			}
		}

		return ( false !== $contact ) ? BWFAN_PRO_Common::get_contact_meta( $contact->id, $key ) : false;
	}

	public function ui_view() {
		esc_html_e( 'Customer Custom Field', 'autonami-automations-pro' );
		?>
        '<%= condition['key'] %>' <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>
        <%= ops[operator] %> '<%= condition['value'] %>'
		<?php
	}

	public function get_possible_rule_operators() {
		return array(
			'is'     => __( 'is', 'wp-marketing-automations' ),
			'is_not' => __( 'is not', 'wp-marketing-automations' ),
		);
	}

}

class BWFAN_Rule_Contact_Role extends BWFAN_Rule_Base {

	public function __construct() {
		parent::__construct( 'contact_role' );
	}

	public function get_possible_rule_values() {
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

	public function is_match( $rule_data ) {
		$id = BWFAN_Core()->rules->getRulesData( 'user_id' );
		if ( empty( $id ) && class_exists( 'WooCommerce' ) ) {
			$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
			$id    = $order instanceof WC_Order ? $order->get_user_id() : $id;
		}

		if ( empty( $id ) ) {
			$email       = BWFAN_Core()->rules->getRulesData( 'email' );
			$contact_db  = WooFunnels_DB_Operations::get_instance();
			$contact_obj = $contact_db->get_contact_by_email( $email );

			if ( isset( $contact_obj->wpid ) && absint( $contact_obj->wpid ) > 0 ) {
				$id = absint( $contact_obj->wpid );
			}
		}

		$result = false;

		if ( $rule_data['condition'] && is_array( $rule_data['condition'] ) ) {
			$user = get_user_by( 'id', $id );

			foreach ( $rule_data['condition'] as $role ) {
				if ( in_array( $role, $user->roles ) ) {
					$result = true;
					break;
				}
			}
		}

		if ( 'in' === $rule_data['operator'] ) {
			return $result;
		} else {
			return ! $result;
		}

	}

	public function ui_view() {
		esc_html_e( 'Contact role', 'wp-marketing-automations' );
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
			'in'    => __( 'is', 'wp-marketing-automations' ),
			'notin' => __( 'is not', 'wp-marketing-automations' ),
		);
	}
}
