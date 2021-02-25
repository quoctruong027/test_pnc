<?php

class BWFAN_Rule_Automation_Run_Count extends BWFAN_Rule_Base {

	public function __construct() {
		parent::__construct( 'automation_run_count' );
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data ) {
		/**
		 * @var Woofunnels_Customer $customer
		 */
		$automation_id = BWFAN_Core()->rules->getRulesData( 'automation_id' );
		$customer_id   = BWFAN_Core()->rules->getRulesData( 'bwf_customer' );

		if ( empty( $customer_id ) ) {
			return $this->return_is_match( false, $rule_data );
		}

		$count = absint( BWFAN_Model_Contact_Automations::get_contact_automations_run_count( $customer_id, $automation_id ) );
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
		esc_html_e( 'Automation run count', 'autonami-automations-pro' );
		?>
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %>
        <%= condition %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'wp-marketing-automations' ),
			'!=' => __( 'is not equal to', 'wp-marketing-automations' ),
			'>'  => __( 'is greater than', 'wp-marketing-automations' ),
			'<'  => __( 'is less than', 'wp-marketing-automations' ),
			'>=' => __( 'is greater or equal to', 'wp-marketing-automations' ),
			'<=' => __( 'is less or equal to', 'wp-marketing-automations' ),
		);

		return $operators;
	}

}
