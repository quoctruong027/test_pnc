<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_WC_EU_Vat {
	private $actives = [];

	public function __construct() {

		$this->init();
		add_filter( 'wfacp_advanced_fields', [ $this, 'add_eu_fields' ] );
		add_filter( 'wfacp_html_fields_wc_eu_vat_compliance_vat_number', '__return_false' );
		add_action( 'process_wfacp_html', [ $this, 'process_wfacp_html' ], 10, 2 );
		add_filter( 'woocommerce_form_field_args', [ $this, 'add_default_wfacp_styling' ], 10, 2 );
		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );
	}

	public function init() {
		global $woocommerce_eu_vat_compliance_classes;
		if ( ! is_null( $woocommerce_eu_vat_compliance_classes ) && isset( $woocommerce_eu_vat_compliance_classes['WC_EU_VAT_Compliance_VAT_Number'] ) && ( $woocommerce_eu_vat_compliance_classes['WC_EU_VAT_Compliance_VAT_Number'] instanceof WC_EU_VAT_Compliance_VAT_Number ) ) {
			$this->actives['WC_EU_VAT_Compliance_VAT_Number'] = $woocommerce_eu_vat_compliance_classes['WC_EU_VAT_Compliance_VAT_Number'];
		}

	}

	public function internal_css( $selected_template_slug ) {
		if ( $this->is_enable( 'WC_EU_VAT_Compliance_VAT_Number' ) ) {
			$array_class = [
				'layout_1' => 15,
				'layout_2' => 15,
				'layout_4' => 15,
				'layout_9' => 7,
			];
			?>
			<style>

				/* WC EU Vats*/
				body .wfacp_main_form #woocommerce_eu_vat_compliance {
					float: none;
					clear: both;

				}

				body .wfacp_main_form #woocommerce_eu_vat_compliance #woocommerce_eu_vat_compliance_vat_number h3 {
					margin: 0 0 5px;
					padding: 0 <?php echo $array_class[ $selected_template_slug ]; ?>px;
				}

				body .wfacp_main_form #woocommerce_eu_vat_compliance .form-row {

					margin-bottom: 15px;
					padding: 0 <?php echo $array_class[ $selected_template_slug ]; ?>px;
				}

				body .wfacp_main_form #woocommerce_eu_vat_compliance #woocommerce_eu_vat_compliance_vat_number h3 + p {
					margin-bottom: 15px;
					padding: 0 <?php echo $array_class[ $selected_template_slug ]; ?>px;
				}

			</style>
			<?php
		}
	}

	public function is_enable( $slug ) {

		if ( isset( $this->actives[ $slug ] ) ) {
			return true;
		}

		return false;

	}

	public function add_eu_fields( $field ) {

		if ( $this->is_enable( 'WC_EU_VAT_Compliance_VAT_Number' ) ) {
			$field['wc_eu_vat_compliance_vat_number'] = [
				'type'       => 'wfacp_html',
				'field_type' => 'advanced',
				'class'      => [ 'wfacp_wc_eu_vat_compliance_vat_number' ],
				'id'         => 'wc_eu_vat_compliance_vat_number',
				'label'      => __( 'EU VAT', 'woocommerce' ),
			];
		}

		return $field;
	}

	public function process_wfacp_html( $field, $key ) {

		global $woocommerce_eu_vat_compliance_classes;
		if ( ! empty( $key ) && $key == 'wc_eu_vat_compliance_vat_number' && class_exists( 'WC_EU_VAT_Compliance_VAT_Number' ) ) {
			$woocommerce_eu_vat_compliance_classes['WC_EU_VAT_Compliance_VAT_Number']->vat_number_field();
		}

	}

	public function add_default_wfacp_styling( $args, $key ) {

		if ( $key == 'vat_number' && $this->is_enable( 'WC_EU_VAT_Compliance_VAT_Number' ) ) {

			$all_cls     = array_merge( [ 'wfacp-form-control-wrapper wfacp-col-full ' ], $args['class'] );
			$input_class = array_merge( [ 'wfacp-form-control' ], $args['input_class'] );
			$label_class = array_merge( [ 'wfacp-form-control-label' ], $args['label_class'] );

			$args['class']       = $all_cls;
			$args['cssready']    = [ 'wfacp-col-full' ];
			$args['input_class'] = $input_class;
			$args['label_class'] = $label_class;

		}

		return $args;
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_WC_EU_Vat(), 'wc-eu-vats' );
