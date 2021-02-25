<?php

if ( bwfan_is_gforms_active() ) {

	class BWFAN_Rule_GF_Form_Field extends BWFAN_Rule_Base {

		public function __construct() {
			parent::__construct( 'gf_form_field' );
		}

		public function get_possible_rule_operators() {
			return array(
				'is'     => __( 'is', 'wp-marketing-automations' ),
				'is_not' => __( 'is not', 'wp-marketing-automations' ),
			);
		}

		public function get_condition_input_type() {
			return 'Text';
		}

		public function conditions_view() {
			$values     = $this->get_possible_rule_values();
			$value_args = array(
				'input'       => 'select',
				'name'        => 'bwfan_rule[<%= groupId %>][<%= ruleId %>][condition][key]',
				'choices'     => $values,
				'class'       => 'bwfan_field_one_half bwfan_gform_form_fields',
				'placeholder' => __( 'Field', 'autonami-automations-pro' ),
			);

			bwfan_Input_Builder::create_input_field( $value_args );

			$condition_input_type = $this->get_condition_input_type();
			$values               = $this->get_possible_rule_values();
			$value_args           = array(
				'input'       => $condition_input_type,
				'name'        => 'bwfan_rule[<%= groupId %>][<%= ruleId %>][condition][value]',
				'choices'     => $values,
				'class'       => 'bwfan_field_one_half',
				'placeholder' => __( 'Value', 'autonami-automations-pro' ),
			);

			bwfan_Input_Builder::create_input_field( $value_args );
		}

		public function is_match( $rule_data ) {
			$entry = BWFAN_Core()->rules->getRulesData( 'entry' );
			$type  = $rule_data['operator'];
			$value = isset( $entry[ $rule_data['condition']['key'] ] ) ? $entry[ $rule_data['condition']['key'] ] : '';

			switch ( $type ) {
				case 'is':
					$result = ( strtolower( $value ) === strtolower( $rule_data['condition']['value'] ) );

					break;
				case 'is_not':
					$result = ( strtolower( $value ) !== strtolower( $rule_data['condition']['value'] ) );

					break;
				default:
					$result = false;
					break;
			}

			return $this->return_is_match( $result, $rule_data );
		}

		public function ui_view() {
			?>
            Form Field
            '<%= bwfan_events_js_data["gf_form_submit"]["selected_form_fields"][condition['key']] %>' <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>
            <%= ops[operator] %> '<%= condition['value'] %>'
			<?php
		}
	}
}
