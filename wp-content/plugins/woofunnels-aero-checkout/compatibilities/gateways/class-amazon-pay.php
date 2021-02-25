<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_Active_AmzPay {
	public $is_amazon_active = false;

	public function __construct() {

		add_filter( 'wfacp_skip_common_loading', [ $this, 'skip_common_loading' ] );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'actions' ] );
		add_action( 'wfacp_before_process_checkout_template_loader', [ $this, 'ajax_actions' ] );
		add_filter( 'wfacp_smart_buttons', [ $this, 'add_buttons' ] );
		add_action( 'wfacp_smart_button_container_amazon_pay', [ $this, 'add_amazon_pay_buttons' ] );
		add_filter( 'wfacp_css_js_removal_paths', [ $this, 'remove_some_js' ], 15 );
		add_filter( 'wfacp_display_quantity_increment', [ $this, 'hide_quantity_switcher' ] );
		add_filter( 'wfacp_mini_cart_enable_delete_item', [ $this, 'hide_delete_icon' ] );

	}

	public function skip_common_loading( $status ) {
		if ( isset( $_REQUEST['wfacp_id'] ) && $_REQUEST['wfacp_id'] > 0 && isset( $_REQUEST['amazon_payments_advanced'] ) && wp_doing_ajax() ) {
			return true;
		}

		return $status;
	}

	/**
	 * Check Payment gateway enabled
	 * @return bool
	 */
	private function is_enabled() {
		if ( class_exists( 'WC_Amazon_Payments_Advanced' ) && class_exists( 'WC_Amazon_Payments_Advanced_API' ) ) {
			return true;
		}

		return false;
	}

	public function amazon_internal_css( $selected_template_slug ) {

		if ( $selected_template_slug == 'layout_9' ) {
			?>

            <style>

                .wfacp_custom_breadcrumb .wfacp_steps_sec ul li.wfacp_bred_active.wfacp_bred_visited.amazone_list_wrap:nth-last-child(2):before {
                    background: #000;
                }

                .wfacp_custom_breadcrumb .wfacp_steps_sec ul li.wfacp_bred_active.wfacp_bred_visited.amazone_list_wrap:before {
                    background: #fff;
                }
            </style>
			<?php
		}
	}


	public function actions() {

		if ( $this->is_enabled() && $this->is_active_payment() ) {
			$template = wfacp_template();
			remove_filter( 'woocommerce_checkout_fields', [ $template, 'woocommerce_checkout_fields' ], 0 );
			add_filter( 'wfacp_form_template', [ $this, 'replace_form_template' ] );
			add_filter( 'wfacp_layout_9_active_progress_bar', [ $this, 'active_progress_bar' ], 10, 2 );
			add_filter( 'wfacp_embed_active_progress_bar', [ $this, 'embedd_active_progress_bar' ], 10, 3 );
			add_filter( 'wfacp_checkout_fields', [ $this, 'add_custom_class_amazon_fileds' ], - 1, 2 );
			add_filter( 'wfacp_checkout_fields', array( $this, 'override_checkout_fields_in_amazone_sec' ) );
			add_action( 'wfacp_internal_css', [ $this, 'amazon_internal_css' ] );

			WFACP_Core()->public->is_amazon_express_active_session = true;
		}
	}

	public function remove_some_js( $paths ) {
		//Remved Woo-postnl JS due Payment Gateway stuck in loop
		if ( $this->is_enabled() && $this->is_active_payment() ) {
			$paths[] = 'js/wcmp-frontend';
		}

		return $paths;
	}

	public function ajax_actions() {

		if ( $this->is_enabled() && $this->is_active_payment() ) {
			$template = wfacp_template();
			remove_filter( 'woocommerce_checkout_fields', [ $template, 'woocommerce_checkout_fields' ], 0 );
		}
	}

	public function is_active_payment() {
		if ( ( '' !== WC_Amazon_Payments_Advanced_API::get_reference_id() || '' !== WC_Amazon_Payments_Advanced_API::get_access_token() ) && WFACP_Common::get_id() > 0 ) {
			return true;
		}

		return false;
	}

	public function add_custom_class_amazon_fileds( $template_fields, $fields ) {

		add_action( 'woocommerce_before_checkout_form', function () {
			wp_enqueue_script( 'wfacp_amazone_pay_js', WFACP_PLUGIN_URL . '/compatibilities/js/amazone-pay.min.js', [ 'wfacp_checkout_js' ], WFACP_VERSION );
		} );
		$billing_details = [
			'billing' => [
				'billing_first_name',
				'billing_last_name',
				'billing_email',
			],
			'account' => [
				'account_password',
			],
		];

		foreach ( $billing_details as $section => $fields ) {
			if ( ! isset( $template_fields[ $section ] ) ) {
				continue;
			}
			foreach ( $fields as $key ) {

				if ( ! isset( $template_fields[ $section ][ $key ] ) ) {
					continue;
				}
				$template_fields[ $section ][ $key ]['class'][]       = 'wfacp-form-control-wrapper';
				$template_fields[ $section ][ $key ]['label_class'][] = 'wfacp-form-control-label';
				$template_fields[ $section ][ $key ]['input_class'][] = 'wfacp-form-control';
			}
		}

		return $template_fields;

	}

	public function override_checkout_fields_in_amazone_sec( $fields_data ) {
		if ( isset( $fields_data['account']['join_referral_program'] ) ) {
			$fields_data['account']['join_referral_program']['class'][] = 'wfacp-form-control-wrapper';
		}

		if ( isset( $fields_data['account']['termsandconditions'] ) ) {
			$fields_data['account']['termsandconditions']['class'][] = 'wfacp-form-control-wrapper';
		}
		if ( isset( $fields_data['account']['referral_code'] ) ) {
			$fields_data['account']['referral_code']['class'][]       = 'wfacp-form-control-wrapper';
			$fields_data['account']['referral_code']['label_class'][] = 'wfacp-form-control-label';
			$fields_data['account']['referral_code']['input_class'][] = 'wfacp-form-control';
		}

		return $fields_data;

	}

	public function active_progress_bar( $active, $step ) {

		if ( $step != '' && $step != null ) {
			$active = 'wfacp_bred_active wfacp_bred_visited ppec_express_checkout_m amazone_list_wrap';
		}

		return $active;
	}

	public function embedd_active_progress_bar( $active, $step_count, $num_of_steps ) {

		$active = '';
		if ( $step_count != '' && $step_count == $num_of_steps ) {

			$active = 'wfacp-active';
		}

		return $active;
	}

	public function replace_form_template( $template ) {
		$template = WFACP_TEMPLATE_COMMON . '/form-amazon-checkout.php';

		return $template;
	}


	public function add_buttons( $buttons ) {

		if ( ! $this->is_enabled() ) {
			return $buttons;
		}
		// if amazon payment session is active then we removed all smart buttons because of no need to display
		if ( $this->is_active_payment() ) {
			return [];
		}
		$settings = WC_Amazon_Payments_Advanced_API::get_settings();
		if ( 'yes' != $settings['enabled'] ) {
			return $buttons;
		}

		add_action( 'wfacp_internal_css', function () {
			remove_action( 'woocommerce_before_checkout_form', [ wc_apa(), 'checkout_message' ], 5 );
		} );
		$buttons['amazon_pay'] = [
			'iframe' => true,
			'name'   => __( 'Amazon Pay', 'woocommerce-gateway-amazon-payments-advanced' ),
		];
		add_filter( 'woocommerce_amazon_pa_checkout_message', '__return_empty_string' );

		return $buttons;
	}

	public function add_amazon_pay_buttons() {
		wc_apa()->checkout_message();
	}

	public function hide_quantity_switcher( $status ) {
		if ( ! $this->is_enabled() ) {
			return $status;
		}
		if ( $this->is_active_payment() ) {
			$status = false;
		}

		return $status;
	}

	public function hide_delete_icon( $status ) {
		if ( ! $this->is_enabled() ) {
			return $status;
		}
		if ( $this->is_active_payment() ) {
			$status = false;
		}

		return $status;
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Active_AmzPay(), 'AmzPay' );
