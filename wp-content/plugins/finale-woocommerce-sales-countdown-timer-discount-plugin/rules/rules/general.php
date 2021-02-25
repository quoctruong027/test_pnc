<?php

class WCCT_Rule_General_Always extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'general_always' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_Always';
	}

	public function is_match( $rule_data, $product_id ) {
		return true;
	}

}

class WCCT_Rule_General_Front_Page extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'general_front_page' );
	}


	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_General_Front';
	}

	public function is_match( $rule_data, $product_id ) {
		return WCCT_Common::$is_front_page;
	}

}

class WCCT_Rule_General_All_Products extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'general_all_products' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_General_All_Products';
	}

	public function is_match( $rule_data, $product_id ) {
		$result = false;
		if ( 0 === $product_id ) {
			return $result;
		}
		$product = get_post( $product_id );

		if ( $product && is_object( $product ) && 'product' == $product->post_type ) {
			return true;
		}

		return $result;
	}

}


class WCCT_Rule_General_All_Pages extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'general_all_pages' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_General_All_Pages';
	}

	public function is_match( $rule_data, $productID ) {
		return ! is_singular( 'product' );
	}

}

class WCCT_Rule_General_All_Product_Cats extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'general_all_product_cats' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_General_All_Product_cats';
	}

	public function is_match( $rule_data, $productID ) {
		return is_tax( 'product_cat' );
	}

}

class WCCT_Rule_General_All_Product_Tags extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'general_all_product_tags' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_General_All_Product_Tags';
	}

	public function is_match( $rule_data, $productID ) {
		return is_tax( 'product_tag' );
	}

}
