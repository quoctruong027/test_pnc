<?php

class WFCO_GS_Insert_Data extends WFCO_Call {

	private static $instance = null;

	public function __construct() {

		$this->required_fields = array( 'spreadsheet_id', 'worksheet_title', 'worksheet_data' );
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

		$service       = new Google_Service_Sheets( $client );
		$range         = $this->data['worksheet_title'] . '!A1:Z';
		$column_values = array();

		ksort( $this->data['worksheet_data'] );
		$temp_columns = $this->data['worksheet_data'];
		end( $temp_columns );
		$key = key( $temp_columns );

		foreach ( $this->get_all_columns() as $column ) {
			if ( array_key_exists( $column, $this->data['worksheet_data'] ) && ! empty( $this->data['worksheet_data'][ $column ] ) ) {
				$column_values[] = $this->data['worksheet_data'][ $column ];
			} else {
				$column_values[] = '-';
			}

			if ( $key === $column ) {
				break;
			}
		}

		$values = array( $column_values );
		$body   = new Google_Service_Sheets_ValueRange( array(
			'values' => $values,
		) );
		$params = array(
			'valueInputOption' => 'USER_ENTERED',
		);

		try {
			$service->spreadsheets_values->append( $this->data['spreadsheet_id'], $range, $body, $params );
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

return 'WFCO_GS_Insert_Data';
