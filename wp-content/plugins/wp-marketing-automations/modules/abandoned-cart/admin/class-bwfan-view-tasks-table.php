<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BWFAN_Abandoned_View_Tasks_Table extends WP_List_Table {

	public $data;
	public $localize_data;
	public $date_format;
	public $abandoned_id;

	/**
	 * Constructor.
	 * @since  1.0.0
	 */
	public function __construct( $args = array() ) {
		$this->data          = array();
		$this->localize_data = array();
		$this->date_format   = BWFAN_Common::get_date_format();
		$this->abandoned_id  = isset( $_GET['bwfan_cart_id'] ) && ! empty( sanitize_text_field( $_GET['bwfan_cart_id'] ) ) ? absint( sanitize_text_field( $_GET['bwfan_cart_id'] ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification

		// Make sure this file is loaded, so we have access to plugins_api(), etc.
		require_once( ABSPATH . '/wp-admin/includes/plugin-install.php' );

		parent::__construct( $args );
	}

	/**
	 * Text to display if no items are present.
	 * @return  void
	 * @since  1.0.0
	 */
	public function no_items() {
		echo wpautop( __( 'No task available for this email.', 'wp-marketing-automations' ) ); //phpcs:ignore WordPress.Security.EscapeOutput
	}

	/** Made the data for abandoned carts screen.
	 * @return array
	 */
	public function get_abandoned_view_tasks_table_data() {
		if ( 0 === $this->abandoned_id ) {
			return array();
		}

		global $wpdb;
		$abandoned_tasks = $wpdb->get_results( $wpdb->prepare( "
								SELECT t.ID as id, t.integration_slug as slug, t.integration_action as action, t.automation_id as a_id, t.status as status, t.e_date as date
								FROM {$wpdb->prefix}bwfan_tasks as t
								LEFT JOIN {$wpdb->prefix}bwfan_taskmeta as m
								ON t.ID = m.bwfan_task_id
								WHERE m.meta_key = %s
								AND m.meta_value = %d
								ORDER BY t.e_date DESC
								", 'c_a_id', $this->abandoned_id ), ARRAY_A );
		$abandoned_logs  = $wpdb->get_results( $wpdb->prepare( "
								SELECT l.ID as id, l.integration_slug as slug, l.integration_action as action, l.automation_id as a_id, l.status as status, l.e_date as date
								FROM {$wpdb->prefix}bwfan_logs as l
								LEFT JOIN {$wpdb->prefix}bwfan_logmeta as m
								ON l.ID = m.bwfan_log_id
								WHERE m.meta_key = %s
								AND m.meta_value = %d
								ORDER BY l.e_date DESC
								", 'c_a_id', $this->abandoned_id ), ARRAY_A );

		if ( empty( $abandoned_tasks ) && empty( $abandoned_logs ) ) {
			return array();
		}

		BWFAN_Core()->automations->return_all = true;
		$active_automations                   = BWFAN_Core()->automations->get_all_automations();
		BWFAN_Core()->automations->return_all = false;

		if ( ! empty( $abandoned_tasks ) ) {
			$tasks = $this->get_tasks_data( $active_automations, $abandoned_tasks );
			if ( is_array( $tasks ) && count( $tasks ) > 0 ) {
				$this->get_tasks_items( $active_automations, $tasks );
			}
		}
		if ( ! empty( $abandoned_logs ) ) {
			$logs = $this->get_tasks_data( $active_automations, $abandoned_logs, 'logs' );
			if ( is_array( $logs ) && count( $logs ) > 0 ) {
				$this->get_tasks_items( $active_automations, $logs, 'logs' );
			}
		}

		if ( empty( $this->localize_data ) ) {
			return array();
		}

		krsort( $this->localize_data['result'] );
		$items = [];
		foreach ( $this->localize_data['result'] as $value ) {
			$items[] = $this->localize_data[ $value['type'] ][ $value['id'] ];
		}

		return array(
			'items' => $items,
		);
	}

	public function get_tasks_data( $active_automations, $data, $type = 'task' ) {
		$result = [];
		foreach ( $data as $tasks ) {
			$task_id       = $tasks['id'];
			$automation_id = $tasks['a_id'];
			if ( ! isset( $active_automations[ $automation_id ] ) ) {
				continue;
			}
			$tasks['title'] = $active_automations[ $automation_id ]['meta']['title'];
			if ( 'task' === $type ) {
				$tasks['meta'] = BWFAN_Model_Taskmeta::get_task_meta( $task_id );
			}
			if ( 'logs' === $type ) {
				$tasks['meta'] = BWFAN_Model_Logmeta::get_log_meta( $task_id );
			}
			$result[ $task_id ] = $tasks;

			unset( $tasks );
		}

		return $result;
	}

	public function get_tasks_items( $active_automations, $tasks, $type = 'tasks' ) {
		$items = [];
		$gif   = admin_url() . 'images/wpspin_light.gif';

		foreach ( $tasks as $task_id => $task ) {
			$automation_id = $task['a_id'];
			if ( ! isset( $active_automations[ $automation_id ] ) ) {
				continue;
			}

			$source_slug      = isset( $task['meta']['integration_data']['event_data'] ) ? $task['meta']['integration_data']['event_data']['event_source'] : null;
			$event_slug       = isset( $task['meta']['integration_data']['event_data'] ) ? $task['meta']['integration_data']['event_data']['event_slug'] : null;
			$integration_slug = $task['slug'];

			// Event plugin is deactivated, so don't show the automations
			$source_instance = BWFAN_Core()->sources->get_source( $source_slug );

			/**
			 * @var $event_instance BWFAN_Event
			 */
			$event_instance = BWFAN_Core()->sources->get_event( $event_slug );

			$task_details   = isset( $task['meta']['integration_data']['global'] ) ? $task['meta']['integration_data']['global'] : array();
			$message        = ( isset( $task['meta']['task_message'] ) ) ? BWFAN_Common::get_parsed_time( $this->date_format, maybe_unserialize( $task['meta']['task_message'] ) ) : array();
			$status         = $task['status'];
			$automation_url = add_query_arg( array(
				'page'    => 'autonami',
				'section' => 'automation',
				'edit'    => $automation_id,
			), admin_url( 'admin.php' ) );

			$action_slug                = $task['action'];
			$items[ $type ][ $task_id ] = array(
				'id'                      => $task_id,
				'automation_id'           => $automation_id,
				'automation_name'         => $task['title'],
				'automation_url'          => $automation_url,
				'automation_source'       => ! is_null( $source_instance ) ? $source_instance->get_name() : __( 'Data unavailable. Contact Support.', 'wp-marketing-automations' ),
				'automation_event'        => ! is_null( $event_instance ) ? $event_instance->get_name() : __( 'Data unavailable. Contact Support.', 'wp-marketing-automations' ),
				'task_integration'        => esc_html__( 'Not Found', 'wp-marketing-automations' ),
				'task_integration_action' => esc_html__( 'Not Found', 'wp-marketing-automations' ),
				'task_date'               => BWFAN_Common::get_human_readable_time( $task['date'], get_date_from_gmt( date( 'Y-m-d H:i:s', $task['date'] ), $this->date_format ) ),
				'status'                  => $status,
				'gif'                     => $gif,
				'task_message'            => $message,
				'task_details'            => '',
				'task_corrupted'          => false
			);

			if ( 'logs' === $type ) {
				$items[ $type ][ $task_id ]['task_id'] = $task['meta']['task_id'];
			}
			/**
			 * @var $action_instance BWFAN_Action
			 */
			$action_instance = BWFAN_Core()->integration->get_action( $action_slug );
			if ( ! is_null( $action_instance ) ) {
				$items[ $type ][ $task_id ]['task_integration_action'] = $action_instance->get_name();
			} else {
				$action_name = BWFAN_Common::get_entity_nice_name( 'action', $action_slug );
				if ( ! empty( $action_name ) ) {
					$items[ $type ][ $task_id ]['task_integration_action'] = $action_name;
				}
			}

			/**
			 * @var $event_instance BWFAN_Event
			 */
			$integration_instance = BWFAN_Core()->integration->get_integration( $integration_slug );
			if ( ! is_null( $integration_instance ) ) {
				$items[ $type ][ $task_id ]['task_integration'] = $integration_instance->get_name();
				$task_details['task_integration']               = $integration_instance->get_name();
			} else {
				$integration_name = BWFAN_Common::get_entity_nice_name( 'integration', $integration_slug );
				if ( ! empty( $integration_name ) ) {
					$items[ $type ][ $task_id ]['task_integration'] = $integration_name;
					$task_details['task_integration']               = $integration_name;
				}
			}
			$items[ $type ][ $task_id ]['task_details']   = ! is_null( $event_instance ) ? $event_instance->get_task_view( $task_details ) : '<b>' . __( 'Data unavailable. Contact Support.', 'wp-marketing-automations' ) . '</b>';
			$items[ $type ][ $task_id ]['task_corrupted'] = is_null( $event_instance ) || is_null( $source_instance );

			$this->localize_data[ $type ][ $task_id ] = $items[ $type ][ $task_id ];

			if ( isset( $this->localize_data['result'] ) && isset( $this->localize_data['result'][ $task['date'] ] ) ) {
				$task['date'] = absint( $task['date'] ) + 1;
			}
			$this->localize_data['result'][ $task['date'] ] = array(
				'id'   => $task_id,
				'type' => $type,
			);
		}
	}

	/**
	 * Prepare an array of items to be listed.
	 * @since  1.0.0
	 */
	public function prepare_items() {
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$this->items           = ( isset( $this->data['items'] ) ) ? $this->data['items'] : array();
	}

	/**
	 * Retrieve an array of columns for the list table.
	 * @return array Key => Value pairs.
	 * @since  1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'task'       => esc_html__( 'Task', 'wp-marketing-automations' ),
			'action'     => esc_html__( 'Action', 'wp-marketing-automations' ),
			'details'    => esc_html__( 'Data', 'wp-marketing-automations' ),
			'automation' => esc_html__( 'Automation', 'wp-marketing-automations' ),
			'time'       => esc_html__( 'Date', 'wp-marketing-automations' ),
			'status'     => esc_html__( 'Status', 'wp-marketing-automations' ),
			'execute'    => ' ',
		);

		return $columns;
	}

	/**
	 * The content of each column.
	 *
	 * @param array $item The current item in the list.
	 * @param string $column_name The key of the current column.
	 *
	 * @return string              Output for the current column.
	 * @since  1.0.0
	 */
	public function column_default( $item, $column_name ) {
		$column_temp = '';
		switch ( $column_name ) {
			case 'status':
				$column_temp = $item[ $column_name ];
				break;
		}

		return $column_temp;
	}

	public function column_task( $item ) {
		$column_string = isset( $item['task_id'] ) ? '#' . $item['task_id'] : '#' . $item['id'];

		return $column_string;
	}

	public function column_details( $item ) {
		return '<div class="bwfan-extra-details">' . $item['task_details'] . '</div>';
	}

	public function column_automation( $item ) {
		$column_string = '<a href="' . $item['automation_url'] . '" class="row-title">' . $item['automation_name'] . ' (#' . $item['automation_id'] . ')</a>';

		return $column_string;
	}

	public function column_action( $item ) {
		$type = ( ! isset( $item['task_id'] ) ) ? 'tasks' : 'logs';

		$column_string = sprintf( '<a href="javascript:void(0);" class="bwfan-preview" data-task-id="%d" data-task-type="%s" title="Preview" data-izimodal-open="#modal-show-task-details" data-iziModal-title="Task Details" data-izimodal-transitionin="comingIn">%s</a>', $item['id'], $type, esc_html__( 'Preview', 'wp-marketing-automations' ) );

		return $column_string . $item['task_integration'] . ': ' . $item['task_integration_action'];
	}

	public function column_time( $item ) {
		return '<span class="time-run-' . $item['id'] . '">' . $item['task_date'] . '</span>';
	}

	public function column_status( $item ) {
		if ( ! isset( $item['task_id'] ) ) {
			$column_string = __( 'Scheduled', 'wp-marketing-automations' );
		} else {
			$column_string = ( '0' === $item['status'] ) ? __( 'Failed', 'wp-marketing-automations' ) : __( 'Completed', 'wp-marketing-automations' );
		}

		return $column_string;
	}

	public function column_execute( $item ) {
		$column_string = '';

		if ( ! isset( $item['task_id'] ) ) {
			if ( 'Not Found' !== $item['task_integration'] && '1' !== $item['status'] && $item['task_corrupted'] ) {
				$column_string = sprintf( '<a href="javascript:void(0);" class="bwfan-run-task" data-task-id="%d" title="' . esc_html__( 'Run Now', 'wp-marketing-automations' ) . '">%s</a>', $item['id'], esc_html__( 'Run Now', 'wp-marketing-automations' ) );
				$column_string .= ' | ';
			}
			$column_string .= sprintf( '<a href="javascript:void(0);" class="bwfan-delete-task" data-task-id="%d" title="' . esc_html__( 'Delete', 'wp-marketing-automations' ) . '">%s</a>', $item['id'], esc_html__( 'Delete', 'wp-marketing-automations' ) );
			$column_string .= '<span class="bwfan-gif-task-delete bwfan-display-none"><img src=" ' . $item['gif'] . '"></span>';
		} else {
			$column_string = sprintf( '<a href="javascript:void(0);" class="bwfan-delete-log" data-log-id="%d" title="Delete">%s</a>', $item['id'], esc_html__( 'Delete', 'wp-marketing-automations' ) );
			$column_string .= '<span class="bwfan-gif-task-delete bwfan-display-none"><img src=" ' . $item['gif'] . '"></span>';
		}

		return $column_string;
	}

	public function single_row( $item ) {
		echo '<tr class="bwfan_automation">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	public function print_local_data() {
		?>
        <script>
            var bwfan_task_table_local =<?php echo count( $this->localize_data ) > 0 ? wp_json_encode( $this->localize_data ) : '{}'; ?>;
        </script>
		<?php
	}

}
