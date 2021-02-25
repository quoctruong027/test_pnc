<?php

class BWF_AS {
	private static $instance;

	public function __construct() {
		global $wpdb;
		$wpdb->bwf_actions      = $wpdb->prefix . 'bwf_actions';
		$wpdb->bwf_action_claim = $wpdb->prefix . 'bwf_action_claim';
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Change the data store
	 */
	public function change_data_store() {
		/** Removing all action data store change filter and then assign ours */
		remove_all_filters( 'action_scheduler_store_class' );
		add_filter( 'action_scheduler_store_class', [ $this, 'set_store_class' ], 1000000, 1 );

		/** Removing all log data store change filter and then assign ours */
		remove_all_filters( 'action_scheduler_logger_class' );
		add_filter( 'action_scheduler_logger_class', [ $this, 'set_logger_class' ], 1000000, 1 );
	}

	/**
	 * Override the action store with our own
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	public function set_store_class( $class ) {
		return BWF_AS_Action_Store::class;
	}

	/**
	 * Override the logger with our own
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	public function set_logger_class( $class ) {
		return BWF_AS_Log_Store::class;
	}
}