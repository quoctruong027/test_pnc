<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_WC_ActiveCompaign {

	private $wc_ac_obj = null;
	private $field_arg = null;
	private $actives = [];

	public function __construct() {

		add_action( 'init', [ $this, 'init_class' ], 4 );
		add_filter( 'wfacp_advanced_fields', [ $this, 'add_ac_field' ] );

		add_filter( 'wfacp_html_fields_activecampaign_for_woocommerce_accepts_marketing', '__return_false' );

		add_action( 'process_wfacp_html', [ $this, 'call_wc_ac_hook' ], 10, 3 );

		add_action( 'wfacp_internal_css', [ $this, 'wac_css_func' ] );

	}


	public function init_class() {

		$instance = WFACP_Common::remove_actions( 'woocommerce_after_checkout_billing_form', 'Activecampaign_For_Woocommerce_Public', 'handle_woocommerce_checkout_form' );

		if ( ! $instance instanceof Activecampaign_For_Woocommerce_Public ) {
			return '';
		}

		if ( class_exists( 'Activecampaign_For_Woocommerce' ) ) {

			$this->actives['Activecampaign_For_Woocommerce'] = $instance;
		}

	}


	public function add_ac_field( $field ) {

		if ( $this->is_enable( 'Activecampaign_For_Woocommerce' ) ) {
			$field['activecampaign_for_woocommerce_accepts_marketing'] = [
				'type'       => 'wfacp_html',
				'class'      => [ 'activecampaign_for_woocommerce_accepts_marketing' ],
				'id'         => 'activecampaign_for_woocommerce_accepts_marketing',
				'field_type' => 'advanced',
				'label'      => __( 'ActiveCampaign', 'woocommerce' ),

			];
		}

		return $field;
	}

	public function is_enable( $slug ) {
		if ( isset( $this->actives[ $slug ] ) ) {
			return true;
		}

		return false;
	}

	public function call_wc_ac_hook( $field, $key, $args ) {

		if ( ! empty( $key ) && $key == 'activecampaign_for_woocommerce_accepts_marketing' ) {

			$this->wc_ac_obj                              = $this->actives['Activecampaign_For_Woocommerce'];
			$activecampaign_for_woocommerce_public_helper = $this->wc_ac_obj;

			$checkbox_display_option = get_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_DB_OPTION_NAME );

			if ( isset( $checkbox_display_option['checkbox_display_option'] ) && 'not_visible' === $checkbox_display_option['checkbox_display_option'] ) {
				return;
			}

			if ( ! class_exists( 'Activecampaign_For_Woocommerce' ) ) {
				return;

			}

			$activecampaign_for_woocommerce_is_checked = $activecampaign_for_woocommerce_public_helper->accepts_marketing_checkbox_is_checked();

			$activecampaign_for_woocommerce_accepts_marketing_label = $activecampaign_for_woocommerce_public_helper->label_for_accepts_marketing_checkbox();
			if ( ! is_null( $args ) ) {
				$all_cls = array_merge( [ 'form-row wfacp-form-control-wrapper wfacp_custom_field_cls wfacp_ac_wrap' ], $args['class'] );
				if ( isset( $this->field_arg['cssready'] ) && is_array( $args['cssready'] ) ) {
					$all_cls = array_merge( $all_cls, $args['cssready'] );
				}
				$args['class'] = $all_cls;

			}

			if ( ! is_null( $this->wc_ac_obj ) ) {

				ob_start();
				?>

                <p class="<?php echo implode( ' ', $args['class'] ); ?>">
                    <input
                            id="activecampaign_for_woocommerce_accepts_marketing"
                            class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
                            type="checkbox"
                            name="activecampaign_for_woocommerce_accepts_marketing"
                            value="1"
						<?php
						if ( $activecampaign_for_woocommerce_is_checked ) {
							echo 'checked="checked"';
						}
						?>
                    />

                    <label for="activecampaign_for_woocommerce_accepts_marketing" class="woocommerce-form__label woocommerce-form__label-for-checkbox inline">
                        <span><?php echo esc_html( $activecampaign_for_woocommerce_accepts_marketing_label ); ?></span>
                    </label>
                </p>

                <div class="clear"></div>
				<?php
				$result = ob_get_clean();
				echo $result;
			}
		}

	}


	public function wac_css_func() {

		if ( ! $this->is_enable( 'Activecampaign_For_Woocommerce' ) ) {
			return;
		}

		if ( function_exists( 'wfacp_template' ) ) {
			$instance = wfacp_template();
		}


		if ( ! is_null( $instance ) ) {
			$px = $instance->get_template_type_px();
		}


		?>
        <style>
            <?php
          if ( isset( $px ) ) {
            echo 'body .wfacp_main_form #activecampaign_for_woocommerce_accepts_marketing_field {padding: 0 '.$px.'px;}';
            echo 'body .wfacp_main_form.woocommerce .wfacp_ac_wrap input[type=checkbox] {left:'.$px.'px;}';
             }
            ?>
            body #activecampaign_for_woocommerce_accepts_marketing_field span.optional {
                display: inline-block !important;
            }

            body #activecampaign_for_woocommerce_accepts_marketing_field label {
                font-weight: normal;
            }

            body .wfacp_main_form.woocommerce .activecampaign_for_woocommerce_accepts_marketing input[type=checkbox] + label {
                padding-left: 25px !important;
            }

        </style>

		<?php

	}


}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_WC_ActiveCompaign(), 'wcac' );
