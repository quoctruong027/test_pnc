<?php

class WFCO_Mailerlite_Add_Subscriber_To_Group extends WFCO_Mailerlite_Call {

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

		if ( ! is_array( $this->data['groups'] ) || ( ! isset( $this->data['groups']['new'] ) || ! isset( $this->data['groups']['existing'] ) ) ) {
			return $this->get_autonami_error( __( 'Group Data is invalid', 'autonami-automations-connectors' ) );
		}

		if ( ! empty( $this->data['groups']['new'] ) ) {
			$groups_to_create = $this->get_groups_to_create();
			foreach ( $groups_to_create as $group_title ) {
				$connector = WFCO_Load_Connectors::get_instance();
				/** Add new Group */
				$call = $connector->get_call( 'wfco_mailerlite_add_group' );
				$call->set_data( array(
					'api_key' => $this->data['api_key'],
					'name'    => $group_title
				) );

				$response = $call->process();
				if ( isset( $response['payload'] ) && ! empty( $response['payload']['id'] ) ) {
					$this->data['groups']['existing'][] = $response['payload']['id'];
				}
			}
		}
		$groups_to_assign = $this->data['groups']['existing'];

		$params = [ 'email' => $this->data['email'] ];
		$failed = [];
		foreach ( $groups_to_assign as $group_id ) {
			$response = $this->do_mailerlite_call( $params, BWF_CO::$POST, $group_id );
			if ( 4 === $response['status'] ) {
				$failed[] = $group_id;
			}
		}

		if ( 0 === count( $failed ) ) {
			return $this->get_autonami_success( __( 'Added to groups successfully', 'autonami-automations-connectors' ) );
		}

		if ( count( $failed ) != count( $groups_to_assign ) ) {
			$failed_ids = implode( ', ', $failed );

			return $this->get_autonami_error( __( 'Subscriber not added to some groups (group ids are ' . $failed_ids . ').', 'autonami-automations-connectors' ) );
		}

		return $this->get_autonami_error( __( 'Subscriber not added to Groups', 'autonami-automations-connectors' ) );

	}

	public function get_groups_to_create() {
		$groups_to_create = $this->data['groups']['new'];
		$connector        = WFCO_Load_Connectors::get_instance();

		$call = $connector->get_call( 'wfco_mailerlite_get_group_list' );
		$call->set_data( array(
			'api_key' => $this->data['api_key']
		) );
		$response = $call->process();
		if ( isset( $response['payload'] ) && ! empty( $response['payload'] ) ) {
			$group_list = $response['payload'];
			foreach ( $group_list as $group ) {
				if ( in_array( $group['name'], $this->data['groups']['new'] ) ) {
					$this->data['groups']['existing'][] = $group['id'];
				}
				$index = array_search( $group['name'], $groups_to_create );
				if ( false !== $index ) {
					unset( $groups_to_create[ $index ] );
				}
			}
		}

		return $groups_to_create;
	}

	public function get_endpoint( $group_id = '' ) {
		return BWFCO_Mailerlite::$api_end_point . 'groups/' . $group_id . '/subscribers';
	}

}

return 'WFCO_Mailerlite_Add_Subscriber_To_Group';
