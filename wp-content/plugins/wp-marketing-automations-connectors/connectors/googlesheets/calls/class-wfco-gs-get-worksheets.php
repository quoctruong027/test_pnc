<?php

class WFCO_GS_Get_Worksheets extends WFCO_Call {

	private static $instance = null;

	public function __construct() {

		$this->required_fields = array( 'spreadsheet_id' );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		$client = BWFCO_Google_Sheets::get_google_client();
		if ( false === $client ) {
			return false;
		}
		$service = new Google_Service_Sheets( $client );

		try {
			$sheets = $service->spreadsheets->get( $this->data['spreadsheet_id'] )->getSheets();
		} catch ( Exception $exception ) {
			$errors = $exception->getErrors();

			return array(
				0 => $exception->getCode(),
				1 => $errors[0]['message'],
				3 => false,
			);
		}

		$worksheets = array();
		foreach ( $sheets as $obj ) {
			$worksheets[ $obj->properties->sheetId ] = $obj->properties->title;
		}

		return $worksheets;
	}


}

return 'WFCO_GS_Get_Worksheets';
