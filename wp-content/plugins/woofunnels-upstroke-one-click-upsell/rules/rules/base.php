<?php

/**
 * Base class for a Conditional_Content rule.
 */
class WFOCU_Rule_Base {

	public $supports = array();

	public function __construct( $name ) {

	}

	/**
	 * Get's the list of possibile values for the rule.
	 *
	 * Override to return the correct list of possibile values for your rule object.
	 * @return array
	 */
	public function get_possible_rule_values() {
		return array();
	}

	/**
	 * Gets the list of possibile rule operators available for this rule object.
	 *
	 * Override to return your own list of operators.
	 *
	 * @return array
	 */
	public function get_possible_rule_operators() {
		return array(
			'==' => __( "is equal to", 'woofunnels-upstroke-one-click-upsell' ),
			'!=' => __( "is not equal to", 'woofunnels-upstroke-one-click-upsell' ),
		);
	}

	/*
	 * Gets the input object type slug for this rule object. 
	 * 
	 * @return string
	 */

	public function get_condition_input_type() {
		return 'Select';
	}

	/**
	 * Checks if the conditions defined for this rule object have been met.
	 *
	 * @return boolean
	 */
	public function is_match( $rule_data, $env = 'cart' ) {
		return false;
	}

	/**
	 * Helper function to wrap the return value from is_match and apply filters or other modifications in sub classes.
	 *
	 * @param boolean $result The result that should be returned.
	 * @param array $rule_data The array config object for the current rule.
	 *
	 * @return boolean
	 */
	public function return_is_match( $result, $rule_data ) {
		return apply_filters( 'wfocu_rules_is_match', $result, $rule_data );
	}


	public function supports( $env ) {

		return in_array( $env, $this->supports );
	}

}
