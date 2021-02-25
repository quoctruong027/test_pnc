<?php

class WFCO_GS_Update_Data extends WFCO_Call {

	private static $instance = null;

	public function __construct() {

		$this->required_fields = array( 'spreadsheet_id', 'worksheet_title', 'worksheet_search_data', 'worksheet_data' );
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
		if ( is_null( $client ) ) {
			return false;
		}

		$service = new Google_Service_Sheets( $client );
		$range   = $this->data['worksheet_title'] . '!' . $this->data['worksheet_search_data']['key'] . '1:' . $this->data['worksheet_search_data']['key'];

		try {
			$response = $service->spreadsheets_values->get( $this->data['spreadsheet_id'], $range );
		} catch ( Exception $exception ) {
			$errors = $exception->getErrors();

			return array(
				0 => $exception->getCode(),
				1 => $errors[0]['message'],
			);
		}

		$get_values = $response->getValues();
		$row        = 0;

		foreach ( $get_values as $key => $data ) {
			if ( in_array( $this->data['worksheet_search_data']['value'], $data, true ) ) {
				$row = $key + 1;
			}
		}

		$values = array();
		foreach ( $this->data['worksheet_data'] as $key => $val ) {
			$values[] = array(
				'range'          => $this->data['worksheet_title'] . '!' . $key . $row,
				'majorDimension' => 'ROWS',
				'values'         => array( array( $val ) ),
			);
		}

		$body   = new Google_Service_Sheets_BatchUpdateValuesRequest( array(
			'valueInputOption' => 'USER_ENTERED',
			'data'             => $values,
		) );
		$params = array();

		try {
			$service->spreadsheets_values->batchUpdate( $this->data['spreadsheet_id'], $body, $params );
		} catch ( Exception $exception ) {
			$errors = $exception->getErrors();

			return array(
				0 => $exception->getCode(),
				1 => $errors[0]['message'],
			);
		}

		return true;
	}

	public function get_all_columns() {
		return array(
			'A' => 'A',
			'B' => 'B',
			'C' => 'C',
			'D' => 'D',
			'E' => 'E',
			'F' => 'F',
			'G' => 'G',
			'H' => 'H',
			'I' => 'I',
			'J' => 'J',
			'K' => 'K',
			'L' => 'L',
			'M' => 'M',
			'N' => 'N',
			'O' => 'O',
			'P' => 'P',
			'Q' => 'Q',
			'R' => 'R',
			'S' => 'S',
			'T' => 'T',
			'U' => 'U',
			'V' => 'V',
			'W' => 'W',
			'X' => 'X',
			'Y' => 'Y',
			'Z' => 'Z',
		);
	}


}

return 'WFCO_GS_Update_Data';
