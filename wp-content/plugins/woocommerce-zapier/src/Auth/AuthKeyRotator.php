<?php

namespace OM4\WooCommerceZapier\Auth;

use OM4\WooCommerceZapier\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Authentication Key Rotation utility that automatically deletes (revokes) WooCommerce Zapier Authentication keys.
 *
 * - Keys that have never been used are deleted daily.
 * - Previously used keys that haven't been used in the last 3 days are deleted daily.
 *
 * @since 2.0.0
 */
class AuthKeyRotator {

	/**
	 * The number of days that an unused Auth Key should be kept before being revoked.
	 */
	const KEY_LAST_ACCESSED_RETENTION_DAYS = '3';

	/**
	 * KeyDataStore instance.
	 *
	 * @var KeyDataStore
	 */
	protected $key_data_store;

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Constructor
	 *
	 * @param KeyDataStore $key_data_store KeyDataStore instance.
	 * @param Logger       $logger Logger instance.
	 */
	public function __construct( KeyDataStore $key_data_store, Logger $logger ) {
		$this->key_data_store = $key_data_store;
		$this->logger         = $logger;
	}

	/**
	 * Instructs the functionality to initialise itself.
	 *
	 * @return void
	 */
	public function initialise() {
		// Daily Cron Installation.
		add_action( 'wc_zapier_db_upgrade_v_10_to_11', array( $this, 'create_cron_jobs' ) );
		add_action( 'wc_zapier_plugin_deactivate', array( $this, 'delete_cron_jobs' ) );

		// Daily Cron Execution.
		add_action( 'wc_zapier_key_cleanup', array( $this, 'key_cleanup' ) );
	}

	/**
	 * Create Task History related Action Scheduler cron job(s).
	 * Executed during initial plugin activation, and when an existing user upgrades.
	 *
	 * @return void
	 */
	public function create_cron_jobs() {
		if ( ! did_action( 'init' ) ) {
			// Activation has ran too early before Action Scheduler is correctly initialised.
			return;
		}
		$this->delete_cron_jobs();
		WC()->queue()->schedule_recurring( time() + ( 12 * HOUR_IN_SECONDS ), DAY_IN_SECONDS, 'wc_zapier_key_cleanup', array(), 'wc-zapier' );
	}

	/**
	 * Delete Task History related Action Scheduler cron job(s).
	 * Executed during plugin deactivation.
	 *
	 * @return void
	 */
	public function delete_cron_jobs() {
		WC()->queue()->cancel( 'wc_zapier_key_cleanup' );
	}

	/**
	 * Unused Auth key cleanup
	 * Executed daily via the `wc_zapier_key_cleanup` scheduled job.
	 *
	 * @return void
	 */
	public function key_cleanup() {
		$counts = $this->key_data_store->get_key_user_counts();

		$retention        = '-' . self::KEY_LAST_ACCESSED_RETENTION_DAYS . 'days';
		$last_access_date = gmdate( 'Y-m-d H:i:s', strtotime( $retention ) );
		if ( ! is_string( $last_access_date ) ) {
			return;
		}

		foreach ( $counts as $user ) {
			if ( $user->num_keys > 1 ) {
				$this->logger->info(
					'User ID %d has %d authentication keys. Revoking key(s) that haven\'t been used since %s',
					array(
						$user->user_id,
						$user->num_keys,
						$last_access_date,
					)
				);
			}

			$keys = $this->key_data_store->get_existing_keys( $user->user_id );
			if ( is_null( $keys ) ) {
				continue;
			}

			foreach ( $keys as $key ) {
				if ( is_null( $key->last_access ) || $key->last_access < $last_access_date ) {
					$this->logger->debug(
						'Revoking Key ID %d for User ID %d with last_access date %s',
						array(
							$key->key_id,
							$user->user_id,
							is_null( $key->last_access ) ? 'NULL' : $key->last_access,
						)
					);
					$this->key_data_store->delete( $key->key_id );
				}
			}
		}
	}
}
