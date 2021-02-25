<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Aelia_WC_EU_VAT_Assistant_RequirementsChecks' ) ) {
	//return;
}

use Aelia\WC\EU_VAT_Assistant\Settings as Settings;
use Aelia\WC\EU_VAT_Assistant\WC_Aelia_EU_VAT_Assistant as EU_VAT_ASSISTANT;

class WFACP_Compatibility_With_Aliea_vat {

	private $instance = null;

	public function __construct() {

		/* checkout page */
		$this->init();
		add_filter( 'wfacp_advanced_fields', [ $this, 'add_eu_fields' ] );
		add_filter( 'woocommerce_form_field_args', [ $this, 'add_default_wfacp_styling' ], 10, 2 );

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'frontend' ] );

		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ], 10 );

		add_filter( 'wfacp_global_dependency_messages', [ $this, 'add_dependency_messages' ] );

	}

	public function init() {
		if ( isset( $GLOBALS['wc-aelia-eu-vat-assistant'] ) ) {
			$this->instance = $GLOBALS['wc-aelia-eu-vat-assistant'];
		}
	}

	public function add_default_wfacp_styling( $args, $key ) {
		if ( $key == 'vat_number' ) {
			if ( ! is_null( $this->instance ) ) {
				$args['description'] = __( EU_VAT_ASSISTANT::settings()->get( 'eu_vat_field_description' ), EU_VAT_ASSISTANT::$text_domain );
			}
		}

		return $args;
	}

	public function add_eu_fields( $field ) {

		if ( ! is_null( $this->instance ) ) {
			$is_vat_field_required = EU_VAT_ASSISTANT::settings()->get( Settings::FIELD_EU_VAT_NUMBER_FIELD_REQUIRED );
			// Allow 3rd parties to show or hide the VAT number field
			if ( apply_filters( 'wc_aelia_eu_vat_assistant_show_checkout_field_vat_number', $is_vat_field_required != Settings::OPTION_EU_VAT_NUMBER_FIELD_HIDDEN, $is_vat_field_required ) ) {
				// Add the new VAT number field
				$field['vat_number'] = [
					'type'                   => 'text',
					'default'                => '',
					'label'                  => __( 'VAT Number', EU_VAT_ASSISTANT::$text_domain ),
					'placeholder'            => __( 'VAT Number', EU_VAT_ASSISTANT::$text_domain ),
					'description'            => __( EU_VAT_ASSISTANT::settings()->get( 'eu_vat_field_description' ), EU_VAT_ASSISTANT::$text_domain ),
					'validate'               => [],
					'id'                     => 'vat_number',
					'aelia_self_certificate' => 'false',
					'required'               => false,
					'wrapper_class'          => [ 'aelia_eu_vat_assistant checkout_field' ],
					'class'                  => [ 'aelia_wc_eu_vat_assistant vat_number update_totals_on_change address-field form-row-wide' ],
					'custom_attributes'      => [
						'valid' => 0,
					],
				];
			}
		}

		return $field;
	}

	public function frontend() {
		if ( ! is_null( $this->instance ) ) {
			add_filter( 'woocommerce_form_field', [ $this, 'woocommerce_form_field' ], 10, 4 );
		}
	}

	public function woocommerce_form_field( $field, $key, $args, $value ) {

		if ( 'vat_number' === $key ) {
			$default_css = [
				'wfacp-col-full',
				'aelia_wc_eu_vat_assistant',
				'location_self_certification',
				'update_totals_on_change',
				'wfacp-form-control-wrapper',
				'wfacp_custom_field_cls',
			];

			$instance       = wfacp_template();
			$checkout_field = $instance->get_checkout_fields();
			if ( ! isset( $checkout_field['advanced']['vat_number'] ) ) {
				return $field;
			}
			$show_self_certification_field_setting = EU_VAT_ASSISTANT::settings()->get( Settings::FIELD_SHOW_SELF_CERTIFICATION_FIELD );
			if ( apply_filters( 'wc_aelia_eu_vat_assistant_show_checkout_field_self_certification', $show_self_certification_field_setting != Settings::OPTION_SELF_CERTIFICATION_FIELD_NO, $show_self_certification_field_setting ) ) {
				$current_user      = wp_get_current_user();
				$self_certificates = [
					'type'              => 'checkbox',
					'default'           => is_object( $current_user ) ? $current_user->vat_number : '',
					'id'                => 'customer_location_self_certified',
					'label'             => '<span class="self_certification_label">' . __( EU_VAT_ASSISTANT::settings()->get( Settings::FIELD_SELF_CERTIFICATION_FIELD_TITLE ), EU_VAT_ASSISTANT::$text_domain ) . '</span>',
					'description'       => __( 'Due to European regulations, we have to ask you to confirm your location.', EU_VAT_ASSISTANT::$text_domain ),
					'validate'          => [],
					'required'          => false,
					'wrapper_class'     => [ 'aelia_eu_vat_assistant checkout_field ' ],
					'class'             => $default_css,
					'custom_attributes' => [
						'valid' => 0,
					],
					'return'            => true,
				];
				$self              = woocommerce_form_field( 'customer_location_self_certified', $self_certificates );
				$field             .= $self;
			}
		}

		return $field;
	}

	public function internal_css( $selected_template_slug ) {

		$instance = wfacp_template();
		$px       = $instance->get_template_type_px();

		?>
        <style>


            @media (min-width: 768px) {
                body .wfacp_main_form .aelia_wc_eu_vat_assistant.wfacp-col-full #vat_number-description {
                    position: absolute;
                    bottom: -22px;
                }

                body .wfacp_main_form .aelia_wc_eu_vat_assistant.wfacp-col-full #vat_number-description {
                    left: <?php echo $px; ?>px;
                }

            }

            body .wfacp_main_form #customer_location_self_certified_field {
                padding: 0 <?php echo $px; ?>px;
            }


        </style>
		<?php

	}

	public function add_dependency_messages( $messages ) {
		if ( ! class_exists( 'Aelia\WC\EU_VAT_Assistant\WC_Aelia_EU_VAT_Assistant' ) ) {
			return $messages;
		}
		$messages[] = [
			'message'     => __( 'EU VAT field requires Billing Address field to present in checkout. Please drag Billing Address to place it in form.', 'woofunnels-aero-checkout' ),
			'id'          => 'address',
			'show'        => 'yes',
			'dismissible' => true,
			'is_global'   => false,
			'type'        => 'wfacp_error',
		];

		return $messages;
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Aliea_vat(), 'aelia_vat' );
