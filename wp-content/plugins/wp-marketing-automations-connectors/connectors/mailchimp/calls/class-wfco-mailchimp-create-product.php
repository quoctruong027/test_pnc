<?php

class WFCO_Mailchimp_Create_Product extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'api_key', 'product_name', 'product_id', 'store_id' );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		BWFCO_Mailchimp::set_headers( $this->data['api_key'] );
		$params = array(
			'id'       => 'bwfan_product_' . $this->data['product_id'],
			'title'    => $this->data['product_name'],
			'variants' => array(
				array(
					'id'    => 'bwfan_product_' . $this->data['product_id'],
					'title' => $this->data['product_name']
				)
			)
		);

		return $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Mailchimp::get_headers(), BWF_CO::$POST );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		$data_center = BWFCO_Mailchimp::get_data_center( $this->data['api_key'] );

		return BWFCO_Mailchimp::get_endpoint( $data_center ) . 'ecommerce/stores/' . $this->data['store_id'] . '/products';
	}

}

return 'WFCO_Mailchimp_Create_Product';
