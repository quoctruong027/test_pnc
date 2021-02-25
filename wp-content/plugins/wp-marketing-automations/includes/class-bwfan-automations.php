<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BWFAN_Automations {
	private static $ins = null;

	public $automation_id = null;
	public $return_all = false;
	public $per_page = 10;
	public $automation_transient_data = [];
	public $toggle_automation = false;
	public $current_automation_id = null;
	public $current_lifecycle_automation_id = false;
	public $current_automation_sync_state = 'data-sync-state="off"';
	private $automation_details = null;

	public function __construct() {
		add_action( 'bwfan_automation_deleted', [ $this, 'remove_automation_transient' ], 10, 1 );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Return automation id
	 * @return null
	 */
	public function get_automation_id() {
		return $this->automation_id;
	}

	/**
	 * Set automation id
	 *
	 * @param $automation_id
	 */
	public function set_automation_id( $automation_id ) {
		$this->automation_id = $automation_id;
	}

	/**
	 * Return automation details
	 * @return null
	 */
	public function get_automation_details() {
		return $this->automation_details;
	}

	/**
	 * Set automation details
	 */
	public function set_automation_details() {
		$this->automation_details = $this->get_automation_data_meta( $this->automation_id );
	}

	public function get_automation_data_meta( $automation_id ) {
		$data = BWFAN_Model_Automations::get( $automation_id );
		if ( ! is_array( $data ) || 0 === count( $data ) ) {
			return [];
		}
		$meta = BWFAN_Model_Automationmeta::get_automation_meta( $automation_id );

		return array_merge( $data, $meta );
	}

	/**
	 * Get only active automations.
	 * @return array|bool|mixed|object|null
	 */
	public function get_active_automations() {
		global $wpdb;

		$woofunnels_transient_obj = WooFunnels_Transient::get_instance();
		$active_automations       = $woofunnels_transient_obj->get_transient( 'bwfan_active_automations', 'autonami' );
		if ( false === $active_automations ) {
			$query              = $wpdb->prepare( 'Select ID,source,event from {table_name} WHERE status = %d', 1 );
			$active_automations = BWFAN_Model_Automations::get_results( $query );
			$woofunnels_transient_obj->set_transient( 'bwfan_active_automations', $active_automations, 86400, 'autonami' );
		}

		$final_automation_data = [];
		if ( count( $active_automations ) > 0 ) {
			foreach ( $active_automations as $automation ) {
				$id                           = $automation['ID'];
				$final_automation_data[ $id ] = [
					'id'     => $id,
					'source' => $automation['source'],
					'event'  => $automation['event'],
					'meta'   => BWFAN_Model_Automationmeta::get_automation_meta( $id ),
				];
			}
		}

		return $final_automation_data;
	}

	public function get_active_automations_for_event( $event_slug ) {
		$automations        = [];
		$active_automations = $this->get_active_automations();
		foreach ( $active_automations as $automation_id => $automation ) {
			if ( $event_slug !== $automation['event'] ) {
				continue;
			}
			$automations[ $automation_id ] = $automation;
		}

		return $automations;
	}

	public function create_automation() {
		$post = [
			'status' => 2,
		];

		BWFAN_Model_Automations::insert( $post );
		$automation_id = BWFAN_Model_Automations::insert_id();

		if ( 0 === $automation_id || is_wp_error( $automation_id ) ) {
			return false;
		}

		$meta = [
			'bwfan_automation_id' => $automation_id,
		];

		$meta['meta_key']   = 'title';
		$meta['meta_value'] = __( '(No title)', 'wp-marketing-automations' );
		BWFAN_Model_Automationmeta::insert( $meta );

		$meta['meta_key']   = 'c_date';
		$meta['meta_value'] = current_time( 'mysql', 1 );
		BWFAN_Model_Automationmeta::insert( $meta );

		$meta['meta_key']   = 'm_date';
		$meta['meta_value'] = current_time( 'mysql', 1 );
		BWFAN_Model_Automationmeta::insert( $meta );

		$meta['meta_key']   = 'requires_update';
		$meta['meta_value'] = 1;
		BWFAN_Model_Automationmeta::insert( $meta );

		do_action( 'bwfan_automation_saved', $automation_id );

		return $automation_id;
	}

	/**
	 * Delete automations from DB.
	 *
	 * @param $automation_ids
	 */
	public function delete_automation( $automation_ids ) {
		global $wpdb;
		$automation_count      = count( $automation_ids );
		$string_placeholders   = array_fill( 0, $automation_count, '%s' );
		$prepared_placeholders = implode( ', ', $string_placeholders );
		$sql_query             = "Delete FROM {table_name} WHERE ID IN ($prepared_placeholders)";
		$sql_query             = $wpdb->prepare( $sql_query, $automation_ids ); // WPCS: unprepared SQL OK
		BWFAN_Model_Automations::delete_multiple( $sql_query );
	}

	/**
	 * Delete automation meta from DB.
	 *
	 * @param $automation_ids
	 */
	public function delete_automationmeta( $automation_ids ) {
		global $wpdb;
		$automation_count      = count( $automation_ids );
		$string_placeholders   = array_fill( 0, $automation_count, '%s' );
		$prepared_placeholders = implode( ', ', $string_placeholders );
		$sql_query             = "Delete FROM {table_name} WHERE bwfan_automation_id IN ($prepared_placeholders)";
		$sql_query             = $wpdb->prepare( $sql_query, $automation_ids ); // WPCS: unprepared SQL OK
		BWFAN_Model_Automationmeta::delete_multiple( $sql_query );
	}

	/**
	 * Get all the unique actions which are present in a single automation.
	 *
	 * @param $all_actions
	 *
	 * @return array
	 */
	public function get_unique_automation_actions( $all_actions ) {
		$unique_actions = [];
		if ( ! is_array( $all_actions ) || count( $all_actions ) === 0 ) {
			return $unique_actions;
		}

		foreach ( $all_actions as $value1 ) {
			foreach ( $value1 as $value2 ) {
				if ( isset( $value2['action_slug'] ) && $value2['integration_slug'] ) {
					$unique_actions[ $value2['action_slug'] ] = $value2['integration_slug'];
				}
			}
		}

		return $unique_actions;
	}

	/**
	 * Return the group_id and action_id of all the actions made in a single automation.
	 *
	 * @param $all_actions
	 *
	 * @return array
	 */
	public function get_automation_actions_indexes( $all_actions ) {
		$unique_actions = [];
		foreach ( $all_actions as $row_index => $row_actions ) {
			foreach ( $row_actions as $action_index => $action_details ) {
				if ( isset( $unique_actions[ $action_details['action_slug'] ] ) && is_array( $unique_actions[ $action_details['action_slug'] ] ) ) {
					array_push( $unique_actions[ $action_details['action_slug'] ], $row_index . '_' . $action_index );
				} else {
					$unique_actions[ $action_details['action_slug'] ] = array( $row_index . '_' . $action_index );
				}
			}
		}

		return $unique_actions;
	}

	/**
	 * Return all the automations
	 * @return array
	 */
	public function get_all_automations( $no_limit = null, $return_all = false ) {
		global $wpdb;

		if ( class_exists( 'BWFAN_Post_Table' ) ) {
			$this->per_page = BWFAN_Post_Table::$per_page;
			$offset         = ( BWFAN_Post_Table::$current_page - 1 ) * $this->per_page;
		}

		$query = 'SELECT * FROM {table_name} ORDER BY ID DESC';

		if ( is_null( $no_limit ) && ( false === $this->return_all && false === $return_all ) ) {
			$query = $wpdb->prepare( 'SELECT * FROM {table_name} ORDER BY ID DESC LIMIT %d OFFSET %d', $this->per_page, $offset );

			if ( isset( $_GET['status'] ) && 'all' !== sanitize_text_field( $_GET['status'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
				$status = sanitize_text_field( $_GET['status'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
				$status = ( 'active' === $status ) ? 1 : 2;
				$query  = $wpdb->prepare( 'SELECT * FROM {table_name} WHERE status = %d ORDER BY ID DESC', $status );
			}
		} elseif ( $this->return_all || $return_all ) {
			$query = 'SELECT * FROM {table_name} ORDER BY ID DESC';
		}

		$all_automations       = BWFAN_Model_Automations::get_results( $query );
		$final_automation_data = [];
		if ( count( $all_automations ) > 0 ) {
			foreach ( $all_automations as $automation ) {
				$id                           = $automation['ID'];
				$final_automation_data[ $id ] = [
					'id'       => $id,
					'source'   => $automation['source'],
					'event'    => $automation['event'],
					'status'   => $automation['status'],
					'priority' => $automation['priority'],
					'meta'     => BWFAN_Model_Automationmeta::get_automation_meta( $id ),
				];
			}
		}

		return $final_automation_data;
	}

	public function remove_automation_transient( $automation_id ) {
		if ( empty( $automation_id ) ) {
			return;
		}
		$woofunnels_transient_obj = WooFunnels_Transient::get_instance();

		/** Delete automation main transient and automation meta transient and active automation transient */
		$woofunnels_transient_obj->delete_transient( 'bwfan_active_automations', 'autonami' );
	}

	/**
	 * Delete all the migrations which belongs to a single automation.
	 *
	 * @param $automation_id
	 */
	public function delete_migrations( $automation_id ) {
		global $wpdb;
		$query          = $wpdb->prepare( 'SELECT ID FROM {table_name} WHERE a_id = %d', $automation_id );
		$all_migrations = BWFAN_Model_Syncrecords::get_results( $query );
		if ( is_array( $all_migrations ) && count( $all_migrations ) > 0 ) {
			foreach ( $all_migrations as $details ) {
				BWFAN_Model_Syncrecords::delete( $details['ID'] );
			}
		}
	}

	/**
	 * In date time firstly the timezone offset is added to the store time and the store time is set. The UTC 0 time is saved in db
	 *
	 * @param $hours
	 * @param $minutes
	 *
	 * @return int
	 * @throws Exception
	 */
	public function get_automation_execution_time( $hours, $minutes ) {
		$date = new DateTime();
		$date->modify( '+' . BWFAN_Common::get_timezone_offset() * HOUR_IN_SECONDS . ' seconds' );
		$date->setTime( $hours, $minutes, 0 );
		$date->modify( '-' . BWFAN_Common::get_timezone_offset() * HOUR_IN_SECONDS . ' seconds' );

		return $date->getTimestamp();
	}

	/**
	 * Removing unnecessary html from the db saved data so that it doesn't break the json.
	 *
	 * @param $db_saved_value
	 *
	 * @return array|string
	 */
	public function get_filtered_automation_saved_data( $db_saved_value ) {
		$db_saved_value_filtered = '';

		if ( ! is_array( $db_saved_value ) || count( $db_saved_value ) === 0 ) {
			return $db_saved_value_filtered;
		}

		$all_actions = BWFAN_Core()->integration->get_actions();
		foreach ( $db_saved_value as $group_id => $group_actions ) {
			foreach ( $group_actions as $key1 => $value1 ) {
				if ( isset( $value1['integration_slug'] ) && isset( $all_actions[ $value1['action_slug'] ] ) && $all_actions[ $value1['action_slug'] ]->is_editor_supported() ) {
					unset( $db_saved_value[ $group_id ][ $key1 ]['data']['body'] );
				}
			}
		}

		$db_saved_value_filtered = $db_saved_value;

		return $db_saved_value_filtered;
	}

	/**
	 * Get all the merge tags from all actions from a single automation.
	 *
	 * @param $automation_data
	 *
	 * @return array
	 */
	public function get_merge_tags_from_automation_posted_data( $automation_data ) {
		$all_section_merge_tags = array();
		foreach ( $automation_data as $group_id => $single_section ) {
			$data_value = ( isset( $single_section['data'] ) ) ? $single_section['data'] : array();
			if ( ! is_array( $data_value ) || count( $data_value ) === 0 ) {
				$all_section_merge_tags[ $group_id ] = BWFAN_Common::get_merge_tags_from_text( $data_value );
				continue;
			}

			$merge_tags = array();
			foreach ( $data_value as $value2 ) {
				if ( ! is_array( $value2 ) || count( $value2 ) === 0 ) {
					$inner_merge_tags = BWFAN_Common::get_merge_tags_from_text( $value2 );
					if ( is_array( $inner_merge_tags ) && count( $inner_merge_tags ) > 0 ) {
						$merge_tags = array_merge( $merge_tags, $inner_merge_tags );
					}
					$all_section_merge_tags[ $group_id ] = $merge_tags;
					continue;
				}

				foreach ( $value2 as $value3 ) {
					if ( ! is_array( $value3 ) || count( $value3 ) === 0 ) {
						$inner_merge_tags = BWFAN_Common::get_merge_tags_from_text( $value3 );
						if ( is_array( $inner_merge_tags ) && count( $inner_merge_tags ) > 0 ) {
							$merge_tags = array_merge( $merge_tags, $inner_merge_tags );
						}
						continue;
					}

					foreach ( $value3 as $value4 ) {
						$sub_inner_merge_tags = BWFAN_Common::get_merge_tags_from_text( $value4 );
						if ( is_array( $sub_inner_merge_tags ) && count( $sub_inner_merge_tags ) > 0 ) {
							$merge_tags = array_merge( $merge_tags, $sub_inner_merge_tags );
						}
					}
				}
				$all_section_merge_tags[ $group_id ] = $merge_tags;

			}
		}

		return $all_section_merge_tags;
	}

	/**
	 * Increase the automation run count
	 *
	 * @param $automation_id
	 * @param bool $increment
	 * @param null $automation_meta
	 */
	public function update_automation_run_count( $automation_id ) {
		$run_count = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'run_count' );
		$update    = false;

		if ( ! empty( $run_count ) ) {
			$update = true;
		} else {
			$run_count = 0;
		}
		$run_count = intval( $run_count ) + 1;

		if ( $update ) {
			$meta_data               = array();
			$meta_data['meta_value'] = $run_count;
			$where                   = array(
				'bwfan_automation_id' => $automation_id,
				'meta_key'            => 'run_count',
			);
			BWFAN_Model_Automationmeta::update( $meta_data, $where );
		} else {
			$meta_data                        = array();
			$meta_data['bwfan_automation_id'] = $automation_id;
			$meta_data['meta_key']            = 'run_count';
			$meta_data['meta_value']          = $run_count;
			BWFAN_Model_Automationmeta::insert( $meta_data );
		}
	}

	public function set_automation_data( $key, $value1 ) {
		$this->automation_transient_data[ $key ] = $value1;
	}

	/**
	 * Returns all the migration's status of the automations.
	 * sync_status = 1 denotes active migrations
	 * sync_status = 2 denotes completed migrations
	 *
	 * @param $status
	 * @param $all_automations
	 *
	 * @return array
	 */
	/**
	 * Returns batch sync processes with automation id as key and status as sync
	 *
	 * @param string $status 1-active (in-process) and 2-complete
	 * @param array $all_automations
	 *
	 * @return array
	 */
	public function get_automations_sync_status( $status = '1', $all_automations = [] ) {
		global $wpdb;

		$sync_automations = [];

		$sql_query = 'Select `a_id`,`status` FROM {table_name} WHERE 1=1';
		$args      = [];

		if ( is_array( $all_automations ) && count( $all_automations ) > 0 ) {
			$automation_count      = count( $all_automations );
			$string_placeholders   = array_fill( 0, $automation_count, '%d' );
			$prepared_placeholders = implode( ', ', $string_placeholders );
			$sql_query             .= " AND `a_id` IN ($prepared_placeholders)";
			$args                  = array_merge( $args, $all_automations );
		}
		if ( '' !== $status ) {
			$sql_query .= ' AND `status` = %d';
			$args[]    = absint( $status );
		}
		$sql_query          = $wpdb->prepare( $sql_query, $args ); // WPCS: unprepared SQL OK
		$automations_result = BWFAN_Model_Syncrecords::get_results( $sql_query );
		if ( ! is_array( $automations_result ) || 0 === count( $automations_result ) ) {
			return $sync_automations;
		}

		return $automations_result;
	}
}

BWFAN_Core::register( 'automations', 'BWFAN_Automations' );
