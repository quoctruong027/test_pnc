<?php

class WFOCU_LearnDash_Compatibility {

	public function __construct() {
		add_filter( 'wfocu_offer_product_types', array( $this, 'add_course_in_product_type_support' ), 10, 1 );
	}

	public function is_enable() {
		if ( class_exists( 'learndash_woocommerce' ) ) {
			return true;
		}

		return false;
	}

	public function add_course_in_product_type_support( $types ) {
		array_push( $types, 'course' );

		return $types;
	}


}

WFOCU_Plugin_Compatibilities::register( new WFOCU_LearnDash_Compatibility(), 'wfocu_learndash_compatibility' );
