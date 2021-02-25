<?php

class WFCO_Ontraport_Get_Tags extends WFCO_Call {

	private static $ins = null;

	public function __construct() {

		$this->required_fields = array( 'app_id', 'api_key' );
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
		BWFCO_Ontraport::set_headers($this->data);
		$params = array();
		isset( $this->data['search'] ) ? $params['search'] = $this->data['search'] : null;
		isset( $this->data['start'] ) ? $params['start'] = absint( $this->data['start'] ) : null;
		isset( $this->data['order_by'] ) ? $params['orderBy'] = $this->data['order_by'] : null;
		isset( $this->data['limit'] ) ? $params['limit'] = absint( $this->data['limit'] ) : null;
		isset( $this->data['order_by_dir'] ) ? $params['orderByDir'] = 'desc' === $this->data['order_by_dir'] ? 'desc' : 'asc' : null;
		isset( $this->data['published_only'] ) ? $params['publishedOnly'] = false === $this->data['published_only'] ? false : true : null;
		isset( $this->data['minimal'] ) ? $params['minimal'] = false === $this->data['minimal'] ? false : true : null;

		$res = $this->make_wp_requests( $this->get_endpoint(), $params, BWFCO_Ontraport::get_headers(), BWF_CO::$GET );

		return $res;
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_Ontraport::get_endpoint() . '/objects?objectID=14';
	}

}

return 'WFCO_Ontraport_Get_Tags';
