<?php

class BWFAN_Connectors_Common {

	public static function init() {
		register_deactivation_hook( WFCO_AUTONAMI_CONNECTORS_PLUGIN_DIR, array( __CLASS__, 'deactivation' ) );
		add_action( 'admin_init', array( __CLASS__, 'schedule_midnight_sync' ) );
		add_action( 'bwfan_run_midnight_connectors_sync', array( __CLASS__, 'sync_connectors' ) );
	}

	public static function schedule_midnight_sync() {
		if ( ! bwf_has_action_scheduled( 'bwfan_run_midnight_connectors_sync' ) ) {
			$date = new DateTime();
			$date->modify( '+1 days' );
			$date->setTime( 0, 0, 0 );
			BWFAN_Common::convert_to_gmt( $date );

			bwf_schedule_recurring_action( $date->getTimestamp(), DAY_IN_SECONDS, 'bwfan_run_midnight_connectors_sync' );
		}
	}

	public static function sync_connectors() {
		BWFAN_Core()->logger->log( __( '-------------------' ), 'connector_midnight_sync' );
		BWFAN_Core()->logger->log( __( 'Midnight Sync START' ), 'connector_midnight_sync' );
		BWFAN_Core()->logger->log( __( '-------------------' ), 'connector_midnight_sync' );
		$active_connectors = WFCO_Load_Connectors::get_active_connectors();
		if ( empty( $active_connectors ) || ! is_array( $active_connectors ) ) {
			BWFAN_Core()->logger->log( __( 'No Active Connectors, Midnight Sync END' ), 'connector_midnight_sync' );

			return;
		}
		foreach ( $active_connectors as $connector ) {
			$connector_slug     = $connector->get_slug();
			$connector_settings = self::get_connector_settings( $connector_slug );
			if ( false === $connector_settings || ! isset( $connector_settings['id'] ) ) {
				BWFAN_Core()->logger->log( __( 'Unable to get the connector settings (Or not connected) for ' . $connector_slug ), 'connector_midnight_sync' );
				BWFAN_Core()->logger->log( __( '-------------------' ), 'connector_midnight_sync' );
				continue;
			}
			$connector_id = [ 'id' => $connector_settings['id'] ];
			$response     = self::sync_the_connector( $connector, $connector_id );
			$data_changed = isset( $response['data_changed'] ) ? $response['data_changed'] : 0;

			BWFAN_Core()->logger->log( __( 'Sync Report for ' . $connector_slug . ':' ), 'connector_midnight_sync' );
			if ( isset( $response['error'] ) ) {
				BWFAN_Core()->logger->log( __( 'Error: ' . $response['error'] ), 'connector_midnight_sync' );
				BWFAN_Core()->logger->log( __( '-------------------' ), 'connector_midnight_sync' );
				continue;
			}

			BWFAN_Core()->logger->log( __( 'Status: ' . $response['status'] ), 'connector_midnight_sync' );
			BWFAN_Core()->logger->log( __( 'Message: ' . $response['message'] ), 'connector_midnight_sync' );
			BWFAN_Core()->logger->log( __( 'Data Changed: ' . ( 1 === absint( $data_changed ) ? 'Yes' : 'No' ) ), 'connector_midnight_sync' );
			BWFAN_Core()->logger->log( __( '-------------------' ), 'connector_midnight_sync' );
		}
		BWFAN_Core()->logger->log( __( 'Midnight Sync END' ), 'connector_midnight_sync' );
		BWFAN_Core()->logger->log( __( '-------------------' ), 'connector_midnight_sync' );
	}

	/**
	 * @param $connector
	 * @param $ids
	 *
	 * @return array
	 */
	public static function sync_the_connector( $connector, $ids ) {
		try {
			$response = $connector->handle_settings_form( $ids, 'sync' );
		} catch ( Exception $exception ) {
			return array( 'error' => $exception->getMessage() );
		}

		return $response;
	}

	public static function get_connector_settings( $slug ) {
		if ( false === WFCO_Common::$saved_data ) {
			WFCO_Common::get_connectors_data();
		}
		$data = WFCO_Common::$connectors_saved_data;

		return ( isset( $data[ $slug ] ) && is_array( $data[ $slug ] ) ) ? $data[ $slug ] : false;
	}

	public static function deactivation() {
		bwf_unschedule_actions( 'bwfan_run_midnight_connectors_sync' );
	}

}