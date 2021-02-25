<?php

class WFCO_Mailchimp_Add_To_Automation extends WFCO_Call {

	private static $ins = null;

	public function __construct() {
		$this->required_fields = array( 'api_key', 'email', 'workflow_id' );
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

		if ( ! is_email( $this->data['email'] ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'Email is not valid' ),
			);
		}

		/** Sequence in an automation is also called Email. */
		$emails = $this->get_automations_emails();
		if ( ! is_array( $emails ) || empty( $emails ) ) {
			return array(
				'response' => 502,
				'body'     => array( 'No Emails are available in the Automation: ' . $this->data['workflow_id'] ),
			);
		}

		BWFCO_Mailchimp::set_headers( $this->data['api_key'] );
		$params  = array( 'email_address' => $this->data['email'] );
		$success = [];
		$failed  = [];
		foreach ( $emails as $email ) {
			$result = $this->make_wp_requests( $this->get_endpoint( $email ), $params, BWFCO_Mailchimp::get_headers(), BWF_CO::$POST );
			if ( 204 === absint( $result['response'] ) ) {
				$success[] = $email;
			} else {
				$failed[] = $email;
			}
		}

		if ( count( $failed ) > 0 && count( $success ) > 0 ) {
			return array(
				'response' => 502,
				'body'     => array( __( 'Added to Automation but Unable to add to these Automation\'s email', 'autonami-automations-connectors' ) . implode( ', ', $failed ) ),
			);
		}

		if ( count( $success ) === 0 ) {
			return array(
				'response' => 502,
				'body'     => array( __( 'Unable to add to automation', 'autonami-automations-connectors' ) ),
			);
		}

		return array(
			'response' => 200,
			'body'     => array( __( 'Added to Automation Successfully', 'autonami-automations-connectors' ) ),
		);
	}

	public function get_automations_emails() {
		$active_connectors = WFCO_Load_Connectors::get_active_connectors();
		/** @var BWFCO_Mailchimp $connector_ins */
		$connector_ins = $active_connectors[ WFCO_Mailchimp_Common::get_connector_slug() ];

		/** Sequence in an automation is also called Email. */
		return $connector_ins->fetch_automations_email( array(
			'api_key'       => $this->data['api_key'],
			'automation_id' => $this->data['workflow_id']
		) );
	}


	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint( $email_id ) {
		$data_center = BWFCO_Mailchimp::get_data_center( $this->data['api_key'] );

		return BWFCO_Mailchimp::get_endpoint( $data_center ) . "automations/" . $this->data['workflow_id'] . "/emails/$email_id/queue";
	}

}

return 'WFCO_Mailchimp_Add_To_Automation';
