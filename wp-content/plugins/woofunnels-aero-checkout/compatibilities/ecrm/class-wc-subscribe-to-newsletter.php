<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_WC_Subscribe_To_Newsletter {

	private $wc_news_obj = null;
	private $field_arg = null;
	private $actives = [];

	public function __construct() {

		add_action( 'init', [ $this, 'init_class' ], 4 );
		add_filter( 'wfacp_advanced_fields', [ $this, 'add_news_field' ] );

		add_filter( 'wfacp_html_fields_wc_subscribe_to_newsletter', '__return_false' );

		add_action( 'process_wfacp_html', [ $this, 'call_wc_news_hook' ], 10, 3 );
		add_filter( 'woocommerce_form_field_args', [ $this, 'add_default_wfacp_styling' ], 10, 2 );

	}


	public function init_class() {

		if ( ! isset( $GLOBALS['WC_Subscribe_To_Newsletter'] ) || ! $GLOBALS['WC_Subscribe_To_Newsletter'] instanceof WC_Subscribe_To_Newsletter ) {
			return '';
		}

		if ( class_exists( 'WC_Subscribe_To_Newsletter' ) ) {
			$this->wc_news_obj = $GLOBALS['WC_Subscribe_To_Newsletter'];

			$this->actives['WC_Subscribe_To_Newsletter'] = $GLOBALS['WC_Subscribe_To_Newsletter'];
		}

	}


	public function add_news_field( $field ) {

		if ( $this->is_enable( 'WC_Subscribe_To_Newsletter' ) ) {
			$field['wc_subscribe_to_newsletter'] = [
				'type'       => 'wfacp_html',
				'class'      => [ 'form-row-wide' ],
				'id'         => 'wc_subscribe_to_newsletter',
				'field_type' => 'advanced',
				'label'      => __( 'Subscribe to Newsletter', 'woocommerce' ),

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

	public function call_wc_news_hook( $field, $key, $args ) {

		if ( ! empty( $key ) && $key == 'wc_subscribe_to_newsletter' ) {

			$this->field_arg = $args;
			if ( ! is_null( $this->wc_news_obj ) ) {

				$this->wc_news_obj->newsletter_field( WC()->checkout() );
			}
		}

	}

	public function add_default_wfacp_styling( $args, $key ) {

		if ( $key == 'subscribe_to_newsletter' && $this->is_enable( 'WC_Subscribe_To_Newsletter' ) ) {

			if ( ! is_null( $this->field_arg ) ) {
				$all_cls = array_merge( [ 'wfacp-form-control-wrapper wfacp_custom_field_cls wfacp_drip_wrap' ], $args['class'] );
				if ( isset( $this->field_arg['cssready'] ) && is_array( $this->field_arg['cssready'] ) ) {
					$all_cls = array_merge( $all_cls, $this->field_arg['cssready'] );
				}
				$args['class'] = $all_cls;

			}
		}

		return $args;
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_WC_Subscribe_To_Newsletter(), 'wcac' );
