<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Paid Membership Pro Detection
 */
if ( ! function_exists( 'bwfan_is_paid_membership_pro_active' ) ) {
	function bwfan_is_paid_membership_pro_active() {
		return BWFAN_PRO_Plugin_Dependency::paid_membership_active_check();
	}
}

/**
 * Thrive Lead Form Detection
 */
if ( ! function_exists( 'bwfan_is_tve_active' ) ) {
	function bwfan_is_tve_active() {
		return BWFAN_PRO_Plugin_Dependency::tve_active_check();
	}
}

/**
 * Learndash Detection
 */
if ( ! function_exists( 'bwfan_is_learndash_active' ) ) {
	function bwfan_is_learndash_active() {
		return BWFAN_PRO_Plugin_Dependency::learndash_active_check();
	}
}

/**
 * Ninja Forms Detection
 */
if ( ! function_exists( 'bwfan_is_ninja_forms_active' ) ) {
	function bwfan_is_ninja_forms_active() {
		return BWFAN_PRO_Plugin_Dependency::ninja_forms_active_check();
	}
}

/**
 * Fluent Forms Detection
 */
if ( ! function_exists( 'bwfan_is_fluent_forms_active' ) ) {
	function bwfan_is_fluent_forms_active() {
		return BWFAN_PRO_Plugin_Dependency::fluent_forms_active_check();
	}
}

/**
 * Caldera Forms Detection
 */
if ( ! function_exists( 'bwfan_is_caldera_forms_active' ) ) {
	function bwfan_is_caldera_forms_active() {
		return BWFAN_PRO_Plugin_Dependency::caldera_forms_active_check();
	}
}

/**
 *  grabber plugin
 */
if ( ! function_exists( 'bwfan_is_handle_utm_grabber_active' ) ) {
	function bwfan_is_handle_utm_grabber_active() {
		return BWFAN_PRO_Plugin_Dependency::handle_utm_grabber_active_check();
	}
}
