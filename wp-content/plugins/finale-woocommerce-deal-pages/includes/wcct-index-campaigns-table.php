<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WCCT_Batch_Post_Table extends WCCT_Post_Table {

	public $per_page = 20;
	public $data;
	public $meta_data;
	public $batch_data = array();
	public $date_format;
	public $time_format;
	public $get_all_shortcodes_all = null;

	/**
	 * Constructor.
	 * @since  1.0.0
	 */
	public function __construct( $args = array() ) {
		global $status, $page;
		parent::__construct( array(
			'singular' => 'campaign',
			'plural'   => 'campaigns',
			'ajax'     => false,
		) );
		$this->date_format = get_option( 'date_format' );
		$this->time_format = get_option( 'time_format' );
	}

	public function get_cp_batch_data( $cp_id ) {
		$output = array(
			'date'   => '',
			'action' => '',
		);
		if ( isset( $this->batch_data[ $cp_id ] ) ) {
			$output = $this->batch_data[ $cp_id ];
		} else {
			$output                     = get_option( 'wcct-deal-process-action-' . $cp_id, array(
				'date'         => '',
				'action'       => '',
				'current_step' => 1,
			) );
			$this->batch_data[ $cp_id ] = $output;
		}

		return $output;
	}

	/**
	 * Retrieve an array of possible bulk actions.
	 * @return array
	 * @since  1.0.0
	 */
	public function get_bulk_actions() {
		$actions = array(
			'1' => __( 'Index All', 'finale-woocommerce-deal-pages' ),
		);

		return $actions;
	}

	public function get_columns() {

		$columns        = array(
			'xlwcctbatch_check_column' => __( '<input class="cb-select-xlwcctbatch" type="checkbox">', 'finale-woocommerce-deal-pages' ),
			'name'                     => __( 'Campaign', 'finale-woocommerce-deal-pages' ),
			'activity'                 => __( 'Activity', 'finale-woocommerce-deal-pages' ),
			'deal_pages'               => __( 'Deal Pages', 'finale-woocommerce-deal-pages' ),
			'status_index'             => __( 'Status', 'finale-woocommerce-deal-pages' ),
			'action'                   => __( 'Action', 'finale-woocommerce-deal-pages' ),
		);
		$parent_columns = parent::get_columns();
		unset( $parent_columns['name'] );
		unset( $parent_columns['campaign'] );
		unset( $parent_columns['deals'] );
		unset( $parent_columns['appearance'] );
		unset( $parent_columns['priority'] );
		unset( $parent_columns['status'] );

		$colums_new = array_merge( $columns, $parent_columns );

		//getting all columns
		if ( $this->get_all_shortcodes_all == null ) {

			global $wpdb;

			$get_results = $wpdb->get_results( $wpdb->prepare( "select post_id from $wpdb->postmeta where meta_key = %s AND meta_value = %s", '_wcct_finale_deal_choose_campaign', 'all' ), ARRAY_A );

			if ( $get_results && is_array( $get_results ) && count( $get_results ) > 0 ) {
				$this->get_all_shortcodes_all = wp_list_pluck( $get_results, 'post_id' );
			}
		}

		return $colums_new;
	}

	public function column_name( $item ) {
		$edit_link     = get_edit_post_link( $item['id'] );
		$column_string = '<strong>';
		$column_string .= '<a href="' . $edit_link . '">';
		$column_string .= '' . _draft_or_post_title( $item['id'] ) . '</strong> </a>';
		$column_string .= $this->column_status( $item );

		return wpautop( $column_string );
	}

	public function column_activity( $item ) {
		if ( $item['id'] > 0 ) {
			$options = $this->get_cp_batch_data( $item['id'] );
			$posts   = get_post( $item['id'] );
			$output  = '';
			if ( $posts ) {
				$dateTime = new DateTime( $posts->post_modified_gmt );
				$dateTime->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
				$output .= '<strong>Modified:</strong> ' . $dateTime->format( $this->date_format . ' ' . $this->time_format );
			}
			if ( '' != $options['date'] ) {
				$dateTime = new DateTime();
				$dateTime->setTimezone( new DateTimeZone( WCCT_Common::wc_timezone_string() ) );
				$dateTime->setTimestamp( $options['date'] );
				$date   = $dateTime->format( $this->date_format . ' ' . $this->time_format );
				$output .= '<br/><strong>Indexed:</strong> ' . $date;
				if ( 1 == $options['action'] ) {
					$output .= '<br/><strong>Action:</strong> Index';
				} else {
					$output .= '<br/><strong>Action:</strong> Re-Index';
				}
			}

			return $output;
		}
	}

	public function column_deal_pages( $item ) {
		global $wpdb;

		$options = $this->get_cp_batch_data( $item['id'] );
		$message = '';
		if ( '' == $options['action'] ) {
			$message = __( 'none', 'finale-woocommerce-deal-pages' );
		} else {

			$query = 'SELECT DISTINCT `post_id` FROM `' . $wpdb->postmeta . "` WHERE `meta_key` = 'wcct_finale_deal_shortcode_campaign' AND `meta_value` LIKE '%" . $item['id'] . "%'";

			$query_result = $wpdb->get_results( $query, ARRAY_A );
			$all_deals    = array();
			if ( count( $query_result ) > 0 ) {
				// has deal pages
				foreach ( $query_result as $deal_id ) {
					if ( isset( $deal_id['post_id'] ) ) {
						$message .= '<a href="' . get_edit_post_link( $deal_id['post_id'] ) . '">' . get_the_title( $deal_id['post_id'] ) . '</a><br/>';
						array_push( $all_deals, $deal_id['post_id'] );
					}
				}
			}

			if ( $this->get_all_shortcodes_all ) {

				foreach ( $this->get_all_shortcodes_all as $deal_id ) {

					if ( is_array( $all_deals ) && count( $all_deals ) > 0 && in_array( $deal_id, $all_deals ) ) {
						continue;
					}
					$message .= '<a href="' . get_edit_post_link( $deal_id ) . '">' . get_the_title( $deal_id ) . '</a><br/>';
				}
			}
			if ( count( $query_result ) == 0 && null == $this->get_all_shortcodes_all ) {
				if ( '' !== $options['action'] && get_post_meta( $item['id'], '_wcct_deal_page_index_req', true ) == 'yes' ) {
					$message = __( 'Campaign modified, needs re-indexing', 'finale-woocommerce-deal-pages' );
				} else {
					// don't have deal pages
					$message = __( 'Campaign is indexed.', 'finale-woocommerce-deal-pages' ) . '<br/><a href="' . admin_url( 'post-new.php?post_type=wcct-deal-shortcode' ) . '"><em>' . __( 'Generate a Deal Page', 'finale-woocommerce-deal-pages' ) . '</em></a>';
				}
			}
		}

		return wpautop( $message );
	}

	public function column_status_index( $item ) {
		$options = $this->get_cp_batch_data( $item['id'] );

		if ( '' !== $options['action'] && get_post_meta( $item['id'], '_wcct_deal_page_index_req', true ) == 'yes' ) {
			$status = 'red';
			$title  = __( 'Needs Re-Indexing', 'finale-woocommerce-deal-pages' );
		} elseif ( '' == $options['action'] ) {
			$status = 'gray';
			$title  = __( 'Ready to Index', 'finale-woocommerce-deal-pages' );
		} else {
			$status = 'green';
			$title  = __( 'Good', 'finale-woocommerce-deal-pages' );
		}
		$column_string = '<div aria-hidden="true" title="' . $title . '" class="wcct_deal_status ' . $status . '"></div>';

		return wpautop( $column_string );
	}

	public function column_action( $item ) {
		$options = $this->get_cp_batch_data( $item['id'] );

		$message     = '';
		$action_name = 'wcct-batch-process-single-' . $item['id'];

		$btn_class = '';
		if ( '' !== $options['action'] && get_post_meta( $item['id'], '_wcct_deal_page_index_req', true ) == 'yes' ) {
			$btn_class = ' xlwcty-bg-orange';
		}

		if ( '' == $options['date'] ) {
			$message = "<button href='javascript:void(0)'  class='button button-secondary wcct_deal_add_index wcct_deal_run_index' data-action='" . $action_name . "'  data-type='1' data-id='" . $item['id'] . "' >" . __( 'Index', 'finale-woocommerce-deal-pages' ) . '</button>';
		} else {
			if ( 1 == $options['action'] || 3 == $options['action'] ) {
				$message = "<button href='javascript:void(0)' class='button button-secondary wcct_deal_add_index wcct_deal_run_index " . $btn_class . "' data-action='" . $action_name . "'  data-type='3' data-id='" . $item['id'] . "'>" . __( 'Re-Index', 'finale-woocommerce-deal-pages' ) . '</button> &nbsp;';
			} else {
				$message = "<button href='javascript:void(0)'  class='button button-secondary wcct_deal_add_index wcct_deal_run_index' data-action='" . $action_name . "'  data-type='1' data-id='" . $item['id'] . "'>" . __( 'Index', 'finale-woocommerce-deal-pages' ) . '</button>';
			}
		}
		$message .= sprintf( '<img src="%s" style="display:none; margin-left: 5px;"/>', plugin_dir_url( WCCT_DEAL_PAGES_BASENAME ) . '/assets/img/ajax-loader.gif' );

		if ( '' !== $options['action'] && get_post_meta( $item['id'], '_wcct_deal_page_index_req', true ) == 'yes' ) {
			$message .= '<p class="xlwcty-text-red">' . __( 'Recommend re-indexing', 'finale-woocommerce-deal-pages' ) . '</p>';
		} elseif ( '' == $options['action'] ) {
			$message .= '<p class="xlwcty_first_index">' . __( 'Ready to index', 'finale-woocommerce-deal-pages' ) . '</p>';
		}

		return wpautop( $message );
	}

	public function column_shortcode( $item ) {
		$message = "[wcct_campaign id='" . $item['id'] . "' limit=50 ]";

		return wpautop( $message );
	}

	public function column_xlwcctbatch_check_column( $item ) {

		return ' <input id="cb-select-' . $item['id'] . '" class="xlwcctbatch_columns" type="checkbox" name="cp_id[]" value="' . $item['id'] . '" style="margin-left:8px;">';
	}

	public function get_table_classes() {
		$get_default_classes = parent::get_table_classes();
		array_push( $get_default_classes, 'wcct-deal-batch-table' );

		return $get_default_classes;
	}

	/**
	 * Content for the "product_name" column.
	 *
	 * @param array $item The current item.
	 *
	 * @return string       The content of this column.
	 * @since  1.0.0
	 */
	public function column_status( $item ) {
		$output = '';

		$output .= '<div class="row-actions-static">';

		$output .= sprintf( '<span class="">ID: %s | </span>', $item['id'] );

		if ( $item['trigger_status'] == WCCT_SHORT_SLUG . 'disabled' ) {
			$output .= __( '<span class="deactivated">Deactivated</span>', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
		} else {

			$state = WCCT_Common::wcct_set_campaign_status( $item['id'] );

			$output .= __( sprintf( '<span class="%s">%s</span>', sanitize_title( $state ), $state ), 'finale-woocommerce-sales-countdown-timer-discount-plugin' );

		}

		return wpautop( $output );
	}


}
