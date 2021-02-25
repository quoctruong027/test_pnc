<?php

/**
 * This class take care of ecommerce tracking setup
 * It renders necessary javascript code to fire events as well as creates dynamic data for the tracking
 * @author woofunnels.
 */
class WFOCU_Schedules {
	private static $ins = null;


	public function __construct() {

		add_action( 'init', array( $this, 'maybe_schedule_recurring_events' ), 9999 );
	}

	public static function get_instance() {
		if ( self::$ins === null ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function maybe_setup_cron_schedules( $schedules_original ) {

		$schedules = $this->get_cron_schedules();

		return array_merge( $schedules_original, $schedules );
	}

	public function get_cron_schedules() {
		return array(
			'wfocu_cron_schedule_times' => array(
				'interval' => ( MINUTE_IN_SECONDS ) * apply_filters( 'wfocu_cron_schedules_timing', 4 ),

				'display' => sprintf( __( 'Once Every %d minutes', 'woofunnels-upstroke-one-click-upsell' ), apply_filters( 'wfocu_cron_schedules_timing', 4 ) ),
			),
		);
	}

	public function maybe_schedule_recurring_events() {
		add_filter( 'cron_schedules', array( $this, 'maybe_setup_cron_schedules' ) ); //phpcs:ignore WordPress.WP.CronInterval.ChangeDetected
		$get_schedules = $this->get_frequency_for_schedules();

		foreach ( $get_schedules as $hook => $schedule ) {
			if ( false === wp_next_scheduled( $hook ) ) {
				wp_schedule_event( time(), $schedule, $hook );
			}
		}

	}

	public function get_frequency_for_schedules() {
		$schedules_times = [
			'wfocu_schedule_normalize_order_statuses'  => 'wfocu_cron_schedule_times',
			'wfocu_schedule_mails_for_bacs_and_cheque' => 'wfocu_cron_schedule_times',
			'wfocu_schedule_pending_emails'            => 'wfocu_cron_schedule_times',
			'wfocu_schedule_thankyou_action'           => 'wfocu_cron_schedule_times',
		];

		return $schedules_times;
	}

}


if ( class_exists( 'WFOCU_Core' ) ) {
	WFOCU_Core::register( 'schedules', 'WFOCU_Schedules' );
}

