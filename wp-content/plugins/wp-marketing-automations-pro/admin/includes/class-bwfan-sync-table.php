<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BWFAN_Sync_Table extends WP_List_Table {

	public static $per_page = 20;
	public static $current_page;
	public $data;
	public $sync_status = null;

	/**
	 * Constructor.
	 * @since  1.0.0
	 */
	public function __construct( $args = array() ) {
		self::$current_page = $this->get_pagenum();
		$this->data         = array();
		$this->sync_status  = array(
			0 => __( 'Failed', 'autonami-automations-pro' ),
			1 => __( 'Running', 'autonami-automations-pro' ),
			2 => __( 'Completed', 'autonami-automations-pro' ),
			3 => __( 'Stopped', 'autonami-automations-pro' ),
		);
		// Make sure this file is loaded, so we have access to plugins_api(), etc.
		require_once( ABSPATH . '/wp-admin/includes/plugin-install.php' );

		parent::__construct( $args );
	}

	public static function render_trigger_nav() {
		$get_campaign_statuses = apply_filters( 'bwfan_admin_trigger_nav', array(
			'all'      => __( 'All', 'autonami-automations-pro' ),
			'active'   => __( 'Active', 'autonami-automations-pro' ),
			'inactive' => __( 'Inactive', 'autonami-automations-pro' ),
		) );
		$html                  = '<ul class="subsubsub subsubsub_bwfan">';
		$html_inside           = array();
		$current_status        = 'all';

		if ( isset( $_GET['status'] ) && '' !== sanitize_text_field( $_GET['status'] ) ) { // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$current_status = sanitize_text_field( $_GET['status'] ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		foreach ( $get_campaign_statuses as $slug => $status ) {
			$need_class = '';
			if ( $slug === $current_status ) {
				$need_class = 'current';
			}

			$url           = add_query_arg( array(
				'status' => $slug,
			), admin_url( 'admin.php?page=autonami' ) );
			$html_inside[] = sprintf( '<li><a href="%s" class="%s">%s</a> </li>', $url, $need_class, $status );
		}

		if ( is_array( $html_inside ) && count( $html_inside ) > 0 ) {
			$html .= implode( '', $html_inside );
		}
		$html .= '</ul>';

		echo $html; //phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Text to display if no items are present.
	 * @return  void
	 * @since  1.0.0
	 */
	public function no_items() {
		echo wpautop( esc_html__( 'No Sync Record Available.', 'autonami-automations-pro' ) ); //phpcs:ignore WordPress.Security.EscapeOutput
	}

	/** Made the data for Tasks screen.
	 * @return array
	 */
	public function get_sync_table_data() {
		global $wpdb;

		$per_page     = self::$per_page;
		$offset       = ( self::$current_page - 1 ) * $per_page;
		$query        = $wpdb->prepare( 'Select * from {table_name} ORDER BY sync_date DESC LIMIT %d OFFSET %d', $per_page, $offset ); // WPCS: unprepared SQL OK
		$sync_records = BWFAN_Model_Syncrecords::get_results( $query );

		if ( ! is_array( $sync_records ) || 0 === count( $sync_records ) ) {
			return array();
		}

		$found_posts                = array();
		$found_posts['found_posts'] = BWFAN_Common::get_sync_records_count();
		$date_format                = get_option( 'date_format' );
		$time_format                = get_option( 'time_format' );
		$wp_date_format             = $date_format . ' ' . $time_format;
		$items                      = [];

		foreach ( $sync_records as $record_details ) {
			$actions_names = [];
			$extra_data    = (array) json_decode( $record_details['sync_data'] );
			$source_slug   = $extra_data['automation_source'];
			$event_slug    = $extra_data['automation_event'];

			/** source name */
			$source_instance = BWFAN_Core()->sources->get_source( $source_slug );
			if ( $source_instance instanceof BWFAN_Source ) {
				$source_nice_name = $source_instance->get_name();
			} else {
				$source_name_raw  = BWFAN_Common::get_entity_nice_name( 'source', $source_slug );
				$source_nice_name = ( ! empty( $source_name_raw ) ) ? $source_name_raw : $source_slug;
			}

			/** event name */
			$event_instance = BWFAN_Core()->sources->get_event( $event_slug );
			if ( $event_instance instanceof BWFAN_Event ) {
				$event_nice_name = $event_instance->get_name();
			} else {
				$event_name_raw  = BWFAN_Common::get_entity_nice_name( 'event', $event_slug );
				$event_nice_name = ( ! empty( $event_name_raw ) ) ? $event_name_raw : $event_slug;
			}

			$event_name         = $source_nice_name . ': ' . $event_nice_name;
			$automation_actions = $extra_data['automation_actions'];

			foreach ( $automation_actions as $single_record ) {
				$indexes          = explode( ':', $single_record );
				$integration_slug = $indexes[0];
				$action_slug      = $indexes[1];

				/** integration name */
				$integration = BWFAN_Core()->integration->get_integration( $integration_slug );
				if ( $integration instanceof BWFAN_Integration ) {
					$integration_name = $integration->get_name();
				} else {
					$integration_name_raw = BWFAN_Common::get_entity_nice_name( 'integration', $integration_slug );
					$integration_name     = ( ! empty( $integration_name_raw ) ) ? $integration_name_raw : $integration_slug;
				}

				/** action name */
				$action = BWFAN_Core()->integration->get_action( $action_slug );
				if ( $action instanceof BWFAN_Action ) {
					$action_name = $action->get_name();
				} else {
					$action_name_raw = BWFAN_Common::get_entity_nice_name( 'action', $action_slug );
					$action_name     = ( ! empty( $action_name_raw ) ) ? $action_name_raw : $action_slug;
				}

				$actions_names[] = $integration_name . ': ' . $action_name;
			}

			$actions_names = array_unique( $actions_names );
			$status        = $this->sync_status[ $record_details['status'] ];

			//Get tasks and logs created via this sync id
			$tasks_count         = BWFAN_Model_Taskmeta::get_sync_count( $record_details['ID'] );
			$logs_count          = BWFAN_Model_Logmeta::get_sync_count( $record_details['ID'] );
			$pending_tasks_count = isset( $tasks_count['pending'] ) ? $tasks_count['pending'] : 0;
			$paused_tasks_count  = isset( $tasks_count['paused'] ) ? $tasks_count['paused'] : 0;
			$success_logs_count  = isset( $logs_count['success'] ) ? $logs_count['success'] : 0;
			$failed_logs_count   = isset( $logs_count['failed'] ) ? $logs_count['failed'] : 0;
			$total_tasks         = $pending_tasks_count + $paused_tasks_count + $success_logs_count + $failed_logs_count;

			$items[] = array(
				'automation_id'       => $record_details['a_id'],
				'sync_date'           => get_date_from_gmt( date( 'Y-m-d H:i:s', $record_details['sync_date'] ), $wp_date_format ),
				'date_from'           => date( $date_format, strtotime( $extra_data['date_from'] ) ),
				'date_to'             => date( $date_format, strtotime( $extra_data['date_to'] ) ),
				'status'              => $status,
				'total_records'       => $record_details['total'],
				'processed_records'   => $record_details['processed'],
				'selected_actions'    => $actions_names,
				'event_name'          => $event_name,
				'pending_tasks_count' => $pending_tasks_count,
				'paused_tasks_count'  => $paused_tasks_count,
				'success_logs_count'  => $success_logs_count,
				'failed_logs_count'   => $failed_logs_count,
				'total_tasks'         => $total_tasks,
				'id'                  => $record_details['ID']
			);
		}

		$found_posts['items'] = $items;

		return $found_posts;
	}

	public function get_sync_records( $automation_id, $no_limit = null ) {
		global $wpdb;

		$per_page = self::$per_page;
		$offset   = ( self::$current_page - 1 ) * $per_page;
		$query    = $wpdb->prepare( 'Select * from {table_name} WHERE `a_id` = %d ORDER BY sync_date DESC', $automation_id ); // WPCS: unprepared SQL OK

		if ( is_null( $no_limit ) ) {
			$query = $wpdb->prepare( 'Select * from {table_name} WHERE `a_id` = %d ORDER BY sync_date DESC LIMIT %d OFFSET %d', $automation_id, $per_page, $offset ); // WPCS: unprepared SQL OK
		}
		$result = BWFAN_Model_Syncrecords::get_results( $query );

		return $result;
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

	public function column_automation( $item ) {
		$automation_name = BWFAN_Model_Automationmeta::get_meta( $item['automation_id'], 'title' );
		if ( empty( $automation_name ) ) {
			return 'N.A.';
		}
		$automation_url = add_query_arg( array(
			'page'    => 'autonami',
			'section' => 'automation',
			'edit'    => $item['automation_id'],
		), admin_url( 'admin.php' ) );

		$column_string = '<a href="' . $automation_url . '" class="row-title">' . $automation_name . ' (#' . $item['automation_id'] . ')</a>';

		/** when status is completed**/
		if ( $this->sync_status[2] === $item['status'] ) {
			$column_string .= '<div class="bwfan_clear"></div>';
			$column_string .= '<div class="row-actions">';
			$column_string .= '<span class="delete"><a href="javascript:void(0)" class="bwfan-delete-batch-process" data-id="' . $item['id'] . '">' . __( 'Delete', 'wp-marketing-automations' ) . '</a></span>';
			$column_string .= '</div>';
		}

		/** when status is running **/
		if ( $this->sync_status[1] === $item['status'] ) {
			$column_string .= '<div class="bwfan_clear"></div>';
			$column_string .= '<div class="row-actions">';
			$column_string .= '<span class="terminate"><a href="javascript:void(0)" class="bwfan-terminate-batch-process" data-id="' . $item['id'] . '">' . __( 'Stop', 'wp-marketing-automations' ) . '</a></span>';
			$column_string .= '</div>';
		}

		return $column_string;
	}

	public function column_event( $item ) {
		$column_string = $item['event_name'];

		return $column_string;
	}

	public function column_date_range( $item ) {
		return $item['date_from'] . ' - ' . $item['date_to'];
	}

	public function column_date( $item ) {
		return $item['sync_date'];
	}

	public function column_total_records( $item ) {
		$message = __( 'Total Records', 'autonami-automations-pro' ) . ': ' . $item['total_records'] . '<br>';
		$message .= __( 'Processed Records', 'autonami-automations-pro' ) . ': ' . $item['processed_records'] . '<br>';
		$message .= __( 'Total Tasks', 'autonami-automations-pro' ) . ': ' . $item['total_tasks'] . '<br>';

		if ( ! empty( $item['pending_tasks_count'] ) ) {
			$message .= __( 'Tasks Scheduled', 'autonami-automations-pro' ) . ': ' . $item['pending_tasks_count'] . '<br>';
		}
		if ( ! empty( $item['paused_tasks_count'] ) ) {
			$message .= __( 'Tasks Paused', 'autonami-automations-pro' ) . ': ' . $item['paused_tasks_count'] . '<br>';
		}
		if ( ! empty( $item['success_logs_count'] ) ) {
			$message .= __( 'Tasks Completed', 'autonami-automations-pro' ) . ': ' . $item['success_logs_count'] . '<br>';
		}
		if ( ! empty( $item['failed_logs_count'] ) ) {
			$message .= __( 'Tasks Failed', 'autonami-automations-pro' ) . ': ' . $item['failed_logs_count'] . '<br>';
		}

		return $message;
	}

	public function column_selected_actions( $item ) {
		return implode( '<br>', $item['selected_actions'] );
	}

	/**
	 * Prepare an array of items to be listed.
	 * @since  1.0.0
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$total_items           = ( isset( $this->data['found_posts'] ) ) ? $this->data['found_posts'] : 0;

		$this->set_pagination_args( array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => self::$per_page, //WE have to determine how many items to show on a page
		) );
		$this->items = ( isset( $this->data['items'] ) ) ? $this->data['items'] : array();
	}

	/**
	 * Retrieve an array of columns for the list table.
	 * @return array Key => Value pairs.
	 * @since  1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'automation'       => __( 'Automation', 'autonami-automations-pro' ),
			'event'            => __( 'Event', 'autonami-automations-pro' ),
			'selected_actions' => __( 'Selected Actions', 'autonami-automations-pro' ),
			'date_range'       => __( 'Date Range', 'autonami-automations-pro' ),
			'date'             => __( 'Date Created', 'autonami-automations-pro' ),
			'total_records'    => __( 'Records', 'autonami-automations-pro' ),
			'status'           => __( 'Status', 'autonami-automations-pro' ),
		);

		return $columns;
	}

	public function display() {
		$singular = $this->_args['singular'];
		$this->screen->render_screen_reader_content( 'heading_list' );
		$classes = implode( ' ', $this->get_table_classes() );

		?>
        <table class="wp-list-table <?php esc_attr_e( $classes ); ?>">
            <thead>
            <tr>
				<?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"
				<?php
				if ( $singular ) {
					echo " data-wp-lists='list:" . esc_attr__( $singular ) . "'";
				}
				?>
            >
			<?php $this->display_rows_or_placeholder(); ?>
            </tbody>

        </table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	public function get_table_classes() {
		$get_default_classes = parent::get_table_classes();
		array_push( $get_default_classes, 'bwfan-instance-table' );
		array_push( $get_default_classes, 'bwfan-list_sync_records' );

		return $get_default_classes;
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @param string $which
	 *
	 * @since 3.1.0
	 *
	 */
	protected function display_tablenav( $which ) {
		?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php
			$this->extra_tablenav( $which );
			?>

            <br class="clear"/>
        </div>
		<?php
	}

	public function single_row( $item ) {
		$tr_class = 'bwfan_automation list_tasks';
		echo '<tr class="' . esc_attr__( $tr_class ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}


}
