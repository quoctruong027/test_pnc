<?php

class BWFAN_Pro_Rules {

	private static $ins = null;

	private function __construct() {
		add_action( 'bwfan_rules_included', [ $this, 'include_rules' ] );
		add_action( 'bwfan_rules_input_included', [ $this, 'include_inputs' ] );

		add_filter( 'bwfan_rules_groups', [ $this, 'add_rule_group' ] );
		add_filter( 'bwfan_rule_get_rule_types', [ $this, 'add_rule_type' ] );
	}

	public static function get_instance() {
		if ( is_null( self::$ins ) ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function add_rule_group( $group ) {
		if ( class_exists( 'WFACP_Core' ) || class_exists( 'WFOCU_Common' ) ) {
			$group['woofunnels'] = array(
				'title' => 'WooFunnels',
			);
		}
		if ( class_exists( 'WFOCU_Common' ) ) {
			$group['upstroke_funnel']        = array(
				'title' => 'WooFunnels',
			);
			$group['upstroke_funnel_offers'] = array(
				'title' => 'WooFunnels',
			);
		}
		if ( class_exists( 'WFACP_Core' ) ) {
			$group['aerocheckout'] = array(
				'title' => 'WooFunnels',
			);
		}
		if ( bwfan_is_affiliatewp_active() ) {
			$group['affiliatewp']      = array(
				'title' => __( 'AffiliateWP', 'autonami-automations-pro' ),
			);
			$group['affiliate_report'] = array(
				'title' => __( 'AffiliateWP Digest Range', 'autonami-automations-pro' ),
			);
		}
		if ( bwfan_is_gforms_active() ) {
			$group['gforms'] = array(
				'title' => __( 'Gravity Forms', 'autonami-automations-pro' ),
			);
		}

		if ( bwfan_is_elementorpro_active() ) {
			$group['elementor-forms'] = array(
				'title' => __( 'Elementor Forms', 'autonami-automations-pro' ),
			);
		}

		if ( bwfan_is_tve_active() ) {
			$group['thrive-forms'] = array(
				'title' => __( 'Thrive Leads', 'autonami-automations-pro' ),
			);
		}

		if ( bwfan_is_wpforms_active() ) {
			$group['wpforms'] = array(
				'title' => __( 'WPForms', 'autonami-automations-pro' ),
			);
		}

		if ( bwfan_is_woocommerce_membership_active() ) {
			$group['wc_member'] = array(
				'title' => __( 'WooCommerce Memberships', 'autonami-automations-pro' ),
			);
		}

		if ( bwfan_is_woocommerce_subscriptions_active() ) {
			$group['wc_subscription']        = array(
				'title' => __( 'WooCommerce Subscriptions', 'autonami-automations-pro' ),
			);
			$group['wc_subscription_status'] = array(
				'title' => __( 'WooCommerce Subscriptions', 'autonami-automations-pro' ),
			);
		}

		if ( bwfan_is_learndash_active() ) {
			$group['learndash_course']      = array(
				'title' => __( 'LearnDash Course', 'autonami-automations-pro' ),
			);
			$group['learndash_lesson']      = array(
				'title' => __( 'LearnDash Lesson', 'autonami-automations-pro' ),
			);
			$group['learndash_topic']       = array(
				'title' => __( 'LearnDash Topic', 'autonami-automations-pro' ),
			);
			$group['learndash_quiz_result'] = array(
				'title' => __( 'LearnDash Quiz Result', 'autonami-automations-pro' ),
			);
			$group['learndash_quiz']        = array(
				'title' => __( 'LearnDash Quiz', 'autonami-automations-pro' ),
			);
			$group['learndash_group']       = array(
				'title' => __( 'LearnDash Group', 'autonami-automations-pro' ),
			);
		}

		/** checking ninja exists **/
		if ( function_exists( 'Ninja_Forms' ) ) {
			$group['ninjaforms'] = array(
				'title' => __( 'Ninja Forms', 'autonami-automations-pro' ),
			);
		}

		/** checking fluent exists **/
		if ( bwfan_is_fluent_forms_active() ) {
			$group['fluentforms'] = array(
				'title' => __( 'Fluent Forms', 'autonami-automations-pro' ),
			);
		}

		/** checking Caldera exists **/
		if ( bwfan_is_caldera_forms_active() ) {
			$group['calderaforms'] = array(
				'title' => __( 'Caldera Forms', 'autonami-automations-pro' ),
			);
		}

		/** BWF_Contact Group */
		$group['bwf_contact'] = array(
			'title' => __( 'Contact', 'autonami-automations-pro' )
		);

		return $group;
	}

	public function add_rule_type( $types ) {
		if ( class_exists( 'WooCommerce' ) ) {
			$wc_customers = [];
			foreach ( $types['wc_customer'] as $key => $value ) {
				$wc_customers [ $key ] = $value;
				if ( 'is_first_order' === $key ) {
					$wc_customers ['customer_total_spent']        = __( 'Customer Total Spent', 'autonami-automations-pro' );
					$wc_customers ['customer_order_count']        = __( 'Customer Order Count', 'autonami-automations-pro' );
					$wc_customers ['customer_purchased_products'] = __( 'Customer Past Purchased Products', 'autonami-automations-pro' );
					$wc_customers ['customer_purchased_cat']      = __( 'Customer Purchased Products Category', 'autonami-automations-pro' );
					$wc_customers ['customer_country']            = __( 'Customer Billing Country', 'autonami-automations-pro' );
				}
			}

			$types['wc_customer'] = $wc_customers;
		}

		if ( class_exists( 'WFACP_Core' ) ) {
			$types['woofunnels']['aerocheckout'] = __( 'Aerocheckout Page', 'autonami-automations-pro' );
			$types['aerocheckout']               = array(
				'aerocheckout' => __( 'Aerocheckout Page', 'autonami-automations-pro' ),
			);
		}
		if ( class_exists( 'WFOCU_Common' ) ) {
			$types['woofunnels']['upstroke_funnels'] = __( 'Upstroke Funnels', 'autonami-automations-pro' );
			$types['woofunnels']['upstroke_offers']  = __( 'Upstroke Offers', 'autonami-automations-pro' );
			$types['upstroke_funnel']                = array(
				'upstroke_funnels' => __( 'Upstroke Funnels', 'autonami-automations-pro' ),
			);
			$types['upstroke_funnel_offers']         = array(
				'upstroke_funnels' => __( 'Upstroke Funnels', 'autonami-automations-pro' ),
				'upstroke_offers'  => __( 'Upstroke Offers', 'autonami-automations-pro' ),
			);
		}
		if ( class_exists( 'WooCommerce' ) && bwfan_is_woocommerce_subscriptions_active() ) {
			$types['wc_customer']['active_subscription']                  = __( 'Has Active Subscription', 'autonami-automations-pro' );
			$types['wc_order']['is_order_renewal']                        = __( 'Order Is Renewal', 'autonami-automations-pro' );
			$types['wc_subscription']['subscription_status']              = __( 'Subscription Status', 'autonami-automations-pro' );
			$types['wc_subscription']['subscription_total']               = __( 'Subscription Total', 'autonami-automations-pro' );
			$types['wc_subscription']['subscription_parent_order_status'] = __( 'Subscription Parent Order Status', 'autonami-automations-pro' );
			$types['wc_subscription']['subscription_item']                = __( 'Subscription Items', 'autonami-automations-pro' );
			$types['wc_subscription']['subscription_payment_gateway']     = __( 'Subscription Payment Gateway', 'autonami-automations-pro' );
			$types['wc_subscription_status']['subscription_old_status']   = __( 'Subscription Old Status', 'autonami-automations-pro' );
			if ( 'yes' === get_option( 'woocommerce_subscriptions_enable_retry' ) ) {
				$types['wc_subscription']['subscription_failed_attempt'] = __( 'Subscription Failed Attempt', 'autonami-automations-pro' );
			}
		}
		if ( bwfan_is_affiliatewp_active() ) {
			$types['affiliatewp']['affiliate_total_earnings']            = __( 'Affiliate Total Earnings', 'autonami-automations-pro' );
			$types['affiliatewp']['affiliate_unpaid_amount']             = __( 'Affiliate Unpaid Earnings', 'autonami-automations-pro' );
			$types['affiliatewp']['affiliate_total_visits']              = __( 'Affiliate Total Visits', 'autonami-automations-pro' );
			$types['affiliate_report']['selected_range_referrals_count'] = __( 'Referral Count (Selected Frequency)', 'autonami-automations-pro' );
			$types['affiliate_report']['selected_range_visits']          = __( 'Referral Visits (Selected Frequency)', 'autonami-automations-pro' );
			$types['affiliatewp']['affiliate_rate']                      = __( 'Affiliate Rate', 'autonami-automations-pro' );
		}
		if ( bwfan_is_gforms_active() ) {
			$types['gforms']['gf_form_field'] = __( 'Form Field', 'autonami-automations-pro' );
		}

		if ( bwfan_is_elementorpro_active() ) {
			$types['elementor-forms']['elementor_form_field'] = __( 'Form Field', 'autonami-automations-pro' );
		}

		if ( bwfan_is_wpforms_active() ) {
			$types['wpforms']['wpforms_form_field'] = __( 'Form Field', 'autonami-automations-pro' );
		}
		if ( bwfan_is_tve_active() ) {
			$types['thrive-forms']['tve_form_field'] = __( 'Form Field', 'autonami-automations-pro' );
		}

		if ( function_exists( 'Ninja_Forms' ) ) {
			$types['ninjaforms']['ninja_form_field'] = __( 'Form Field', 'autonami-automations-pro' );
		}

		if ( bwfan_is_fluent_forms_active() ) {
			$types['fluentforms']['fluent_form_field'] = __( 'Form Field', 'autonami-automations-pro' );
		}

		if ( bwfan_is_caldera_forms_active() ) {
			$types['calderaforms']['caldera_form_field'] = __( 'Form Field', 'autonami-automations-pro' );
		}

		if ( class_exists( 'WooCommerce' ) && bwfan_is_woocommerce_membership_active() ) {
			$types['wc_member']['membership_has_status']   = __( 'Membership Has Status', 'autonami-automations-pro' );
			$types['wc_member']['active_membership_plans'] = __( 'Membership Has Active Plans', 'autonami-automations-pro' );
		}

		/** BWF Contact */
		$types['bwf_contact'] = array(
			'customer_is_wp_user'   => __( 'Is WP User', 'wp-marketing-automations' ),
			'customer_custom_field' => __( 'Custom Field', 'wp-marketing-automations' ),
			'contact_role'          => __( 'WP Role', 'wp-marketing-automations' ),
		);
		if ( class_exists( 'WooCommerce' ) ) {
			$types['bwf_contact']['customer_total_spent']        = __( 'Total Spent', 'autonami-automations-pro' );
			$types['bwf_contact']['customer_order_count']        = __( 'Order Count', 'autonami-automations-pro' );
			$types['bwf_contact']['customer_purchased_products'] = __( 'Past Purchased Products', 'autonami-automations-pro' );
			$types['bwf_contact']['customer_purchased_cat']      = __( 'Purchased Products Category', 'autonami-automations-pro' );
			$types['bwf_contact']['customer_country']            = __( 'Billing Country', 'autonami-automations-pro' );
		}

		if ( bwfan_is_learndash_active() ) {
			$types['learndash_quiz_result']['learndash_quiz_percentage'] = __( 'Quiz Percentage', 'autonami-automations-pro' );
			$types['learndash_quiz_result']['learndash_quiz_points']     = __( 'Quiz Points', 'autonami-automations-pro' );
			$types['learndash_quiz_result']['learndash_quiz_score']      = __( 'Quiz Score (No. of correct answers)', 'autonami-automations-pro' );
			$types['learndash_quiz_result']['learndash_quiz_timespent']  = __( 'Quiz Time Spent (in seconds)', 'autonami-automations-pro' );
			$types['learndash_quiz_result']['learndash_quiz_result']     = __( 'User passed the Quiz', 'autonami-automations-pro' );
			$types['learndash_course']['learndash_course']               = __( 'Course', 'autonami-automations-pro' );
			$types['learndash_lesson']['learndash_lesson']               = __( 'Lesson', 'autonami-automations-pro' );
			$types['learndash_topic']['learndash_topic']                 = __( 'Topic', 'autonami-automations-pro' );
			$types['learndash_quiz']['learndash_quiz']                   = __( 'Quiz', 'autonami-automations-pro' );
			$types['learndash_group']['learndash_group']                 = __( 'Group', 'autonami-automations-pro' );
		}

		return $types;
	}

	public function include_rules() {
		include_once __DIR__ . '/rules/bwf-customer.php';

		if ( bwfan_is_woocommerce_subscriptions_active() ) {
			include_once __DIR__ . '/rules/subscriptions.php';
		}
		/** Include only if any of the woofunnel plugin activated */
		if ( class_exists( 'WFACP_Core' ) || class_exists( 'WFOCU_Common' ) ) {
			include_once __DIR__ . '/rules/woofunnels.php';
		}
		if ( bwfan_is_affiliatewp_active() ) {
			include_once __DIR__ . '/rules/affiliatewp.php';
		}
		if ( bwfan_is_gforms_active() ) {
			include_once __DIR__ . '/rules/gforms.php';
		}

		if ( bwfan_is_elementorpro_active() ) {
			include_once __DIR__ . '/rules/elementorforms.php';
		}

		if ( bwfan_is_wpforms_active() ) {
			include_once __DIR__ . '/rules/wpforms.php';
		}

		if ( function_exists( 'Ninja_Forms' ) ) {
			include_once __DIR__ . '/rules/ninjaforms.php';
		}

		if ( bwfan_is_fluent_forms_active() ) {
			include_once __DIR__ . '/rules/fluentforms.php';
		}

		if ( bwfan_is_caldera_forms_active() ) {
			include_once __DIR__ . '/rules/calderaforms.php';
		}

		if ( bwfan_is_tve_active() ) {
			include_once __DIR__ . '/rules/thriveforms.php';
		}

		if ( bwfan_is_woocommerce_membership_active() ) {
			include_once __DIR__ . '/rules/memberships.php';
		}

		if ( bwfan_is_learndash_active() ) {
			include_once __DIR__ . '/rules/learndash.php';
		}
	}

	public function include_inputs() {
		if ( class_exists( 'WFOCU_Common' ) ) {
			include_once __DIR__ . '/html/html-funnel-onetime.php';
			include_once __DIR__ . '/html/html-funnel-products.php';
		}
	}

}

BWFAN_Pro_Rules::get_instance();
