<?php

class WFCO_Ontraport_Create_Product extends WFCO_Call {

	private static $ins = null;
	private $site_url = null;

	public function __construct() {

		$this->required_fields = array( 'app_id', 'api_key', 'product_name', 'product_price' );
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

		BWFCO_Ontraport::set_headers( $this->data );

		$params = [ 'name' => $this->data['product_name'], 'price' => $this->data['product_price'] ];

		return $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Ontraport::get_headers(), BWF_CO::$POST );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Ontraport::get_endpoint() . '/Products';
	}

}

return 'WFCO_Ontraport_Create_Product';
