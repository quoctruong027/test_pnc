<?php

class WFCO_Mailerlite_Remove_Subscriber_From_Group extends WFCO_Mailerlite_Call {

	private static $ins = null;

	public function __construct() {
		parent::__construct( array( 'api_key', 'groups', 'email' ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process_mailerlite_call() {

		if ( ! is_email( $this->data['email'] ) ) {
			return $this->get_autonami_error( __( 'Email is not valid', 'autonami-automations-connectors' ) );
		}

		if ( empty( $this->data['groups'] ) || ! is_array( $this->data['groups'] ) ) {
			return $this->get_autonami_error( __( 'Groups data is invalid', 'autonami-automations-connectors' ) );
		}

		/** add Gruops */
		$connector = WFCO_Load_Connectors::get_instance();
		/** @var WFCO_Mailerlite_Add_Group $call */

		if ( ! empty( $this->data['groups']['names'] ) ) {
			$call = $connector->get_call( 'wfco_mailerlite_get_group_list' );
			$call->set_data( array(
				'api_key' => $this->data['api_key'],
			) );

			$response = $call->process();

			if ( ! empty( $response['payload'] ) ) {
				$groups = $response['payload'];
				foreach ( $groups as $group ) {
					if ( in_array( $group['name'], $this->data['groups']['names'] ) ) {
						$this->data['groups']['ids'][] = $group['id'];

						if (($key = array_search($group['name'], $this->data['groups']['names'])) !== false) {
						    unset($this->data['groups']['names'][$key]);
						}
					}
				}
			}
		}

		$remove_groups = $this->data['groups']['ids'];
		$remove_groups = !empty($this->data['groups']['names'])?array_merge($this->data['groups']['names'],$remove_groups):$remove_groups;
		
		if ( empty( $remove_groups ) ) {
			return $this->get_autonami_error( __( 'Groups are not available', 'autonami-automations-connectors' ) );
		}
		$params = $failed = $success = [];

		foreach ( $remove_groups as $group_id ) {
			$response = $this->do_mailerlite_call( $params, BWF_CO::$DELETE, $group_id );
			if ( 4 === $response['status'] ) {
				$failed[] = $group_id;
			} else {
				$success[] = $group_id;
			}
		}
		
		if ( count( $failed ) > 0 && count( $failed ) != count( $remove_groups ) ) {
			$success_ids = implode( ', ', $success );

			return $this->get_autonami_error( __( 'Contact is not available in some groups, but removed from these groups (' . $success_ids . ')', 'autonami-automations-connectors' ) );
		} elseif ( count( $failed ) == count( $remove_groups ) ) {
			$failed_ids = implode( ', ', $failed );
			return $this->get_autonami_error( __( 'Contact is not available in any of the specified groups ( '.$failed_ids.' )', 'autonami-automations-connectors' ) );
		}

		return $this->get_autonami_success( __( 'Remove from groups successfully', 'autonami-automations-connectors' ) );
	}

	/**
	 * Return the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint( $group_id = '' ) {
		return BWFCO_Mailerlite::$api_end_point . 'groups/' . $group_id . '/subscribers/' . $this->data['email'];
	}

}

return 'WFCO_Mailerlite_Remove_Subscriber_From_Group';
