<?php

class WFCO_Ontraport_Get_Product_By_ID extends WFCO_Call {

	private static $ins = null;
	private $site_url = null;

	public function __construct() {

		$this->required_fields = array( 'app_id', 'api_key', 'product_id' );
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
		$params = array(
			'id' => $this->data['product_id'],
		);

		BWFCO_Ontraport::set_headers( $this->data );
		$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Ontraport::get_headers(), BWF_CO::$GET );
		if ( ! isset( $res['response'] ) || 200 !== $res['response'] || ! isset( $res['body']['data'] ) || ! isset( $res['body']['data'][0]['id'] ) ) {
			return $res;
		}

		return $res['body']['data'][0]['id'];
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

return 'WFCO_Ontraport_Get_Product_By_ID';
