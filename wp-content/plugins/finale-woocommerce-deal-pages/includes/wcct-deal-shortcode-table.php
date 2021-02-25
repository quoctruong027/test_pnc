<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WCCT_Batch_Shortcode_Post_Table extends WP_List_Table {

	public $per_page = 20;
	public $data;
	public $meta_data;

	/**
	 * Constructor.
	 * @since  1.0.0
	 */
	public function __construct( $args = array() ) {
		global $status, $page;
		parent::__construct( array(
				'singular' => 'campaign', //singular name of the listed records
				'plural'   => 'campaigns', //plural name of the listed records
				'ajax'     => false,        //does this table support ajax?
			) );
		$status     = 'all';
		$page       = $this->get_pagenum();
		$this->data = $this->get_post_data();
		// Make sure this file is loaded, so we have access to plugins_api(), etc.
		require_once( ABSPATH . '/wp-admin/includes/plugin-install.php' );
		parent::__construct( $args );
	}

	public function get_post_data() {
		$args = array(
			'post_type'      => 'wcct-deal-shortcode',
			'posts_per_page' => - 1,
			'post_status'    => 'any',
		);

		$loop = new WP_Query( $args );
		if ( $loop->found_posts > 0 ) {
			return $loop->posts;
		}

		return false;
	}

	/**
	 * Text to display if no items are present.
	 * @since  1.0.0
	 * @return  void
	 */
	public function no_items() {
		$wcct_deal = Finale_deal_batch_processing::instance();
		$array     = $wcct_deal->get_campaign_by_index();
		if ( empty( $array ) ) {
			echo wpautop( __( sprintf( 'No Deal Page Available, You have to index atleast one campaign to create shortcode. <a href="%s">%s</a> ', admin_url( 'admin.php?page=wc-settings&tab=xl-countdown-timer&section=deal_pages' ), 'Index now' ), 'finale-woocommerce-deal-pages' ) );
		} else {
			echo wpautop( __( 'No Deal Page Available', 'finale-woocommerce-deal-pages' ) );
		}
	}

	/**
	 * The content of each column.
	 *
	 * @param  array $item The current item in the list.
	 * @param  string $column_name The key of the current column.
	 *
	 * @since  1.0.0
	 * @return string              Output for the current column.
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'check-column':
				return '&nbsp;';
			case 'status':
				return $item[ $column_name ];
				break;
		}
	}

	public function get_item_data( $item_id ) {
		global $wpdb;
		$data = array();

		if ( isset( $this->meta_data[ $item_id ] ) ) {
			$data = $this->meta_data[ $item_id ];
		} else {
			$this->meta_data[ $item_id ] = get_post_meta( $item_id );
			$data                        = $this->meta_data[ $item_id ];
		}

		return $data;
	}

	public function column_campaign( $item ) {
		$data = $this->get_item_data( (int) $item->ID );


		if ( isset( $data['_wcct_finale_deal_choose_campaign'][0] ) && $data['_wcct_finale_deal_choose_campaign'][0] === 'all' ) {
			$string = __( 'All', 'finale-woocommerce-deal-pages' );

		} else {

			$cp_id  = isset( $data['wcct_finale_deal_shortcode_campaign'] ) ? $data['wcct_finale_deal_shortcode_campaign'][0] : 0;
			$cp_id  = maybe_unserialize( $cp_id );
			$string = '';
			if ( is_array( $cp_id ) ) {
				foreach ( $cp_id as $id ) {
					if ( $id > 0 ) {
						$string .= '<a href="' . get_edit_post_link( $id ) . '">' . get_the_title( $id ) . '</a>';

						$string .= $this->columnn_status( $id );
						$string .= '<br/>';
					}
				}

				return $string;

			} else {

				if ( $cp_id > 0 ) {
					$string = '<a href="' . get_edit_post_link( $cp_id ) . '">' . get_the_title( $cp_id ) . '</a>';

					$string .= $this->columnn_status( $cp_id );
				}
			}

		}

		return wpautop( $string );
	}


	public function column_shortcode( $item ) {

		return wpautop( sprintf( '<textarea onclick="this.select()" style="font-size: 12px; margin-top: 0px; margin-bottom: 0px; height: 35px; width:300px;" rows="3" readonly="readonly">%s</textarea>', "[finale_deal id='{$item->ID}' count='12' pagination='yes']" ) );
	}

	public function get_table_classes() {
		$get_default_classes = parent::get_table_classes();
		array_push( $get_default_classes, 'wcct-deal-shortcode-table' );

		return $get_default_classes;
	}

	public function column_name( $item ) {
		$column_string = '';
		$column_string .= '<strong><a href="' . admin_url( "post.php?post={$item->ID}&action=edit" ) . '">' . $item->post_title . '</a></strong>';
		$column_string .= '<div class=\'row-action1s\'>';
		$column_string .= '<span class="" style="color:#999;">ID: ' . $item->ID . '|</span>  ';
		$column_string .= '<span class=""><a href="' . admin_url( "post.php?post={$item->ID}&action=edit" ) . '">Edit</a> | </span>  ';
		$column_string .= '<span class="duplicate"><a href="' . wp_nonce_url( add_query_arg( 'page', 'wc-settings', add_query_arg( 'tab', WCCT_Common::get_wc_settings_tab_slug(), add_query_arg( 'action', 'deal-pages-duplicate', add_query_arg( 'postid', $item->ID, add_query_arg( 'section', 'deal_pages' ) ), network_admin_url( 'admin.php' ) ) ) ), 'deal-pages-duplicate' ) . '">Duplicate</a></span> | ';
		$column_string .= '<span class="delete"><a style="color:orange;" href="' . get_delete_post_link( $item->ID, '', true ) . '">Delete Permanently</a></span>';
		$column_string .= '</div>';

		return wpautop( $column_string );
	}

	/**
	 * Retrieve an array of possible bulk actions.
	 * @since  1.0.0
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array();

		return $actions;
	}

	/**
	 * Prepare an array of items to be listed.
	 * @since  1.0.0
	 * @return array Prepared items.
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$total_items = count( $this->data );

		$this->set_pagination_args( array(
				'total_items' => $total_items, //WE have to calculate the total number of items
				'per_page'    => $this->per_page, //WE have to determine how many items to show on a page
			) );

		$current     = $this->get_pagenum();
		$offset      = ( $current - 1 ) * $this->per_page;
		$limit_data  = array_slice( $this->data, $offset, $this->per_page, true );
		$this->items = $limit_data;
	}

	/**
	 * Retrieve an array of columns for the list table.
	 * @since  1.0.0
	 * @return array Key => Value pairs.
	 */
	public function get_columns() {
		$columns = array(
			'name'      => __( 'Title', 'finale-woocommerce-deal-pages' ),
			'campaign'  => __( 'Campaign', 'finale-woocommerce-deal-pages' ),
			'shortcode' => __( 'Shortcode', 'finale-woocommerce-deal-pages' ),
		);

		return $columns;
	}

	public function columnn_status( $id ) {
		$output = '';

		$output .= '<div class="row-actions-static row-inline">';

		$output   .= sprintf( ' &nbsp;| <span class="">ID: %s | </span>', $id );
		$get_post = get_post( $id );
		if ( $get_post === null ) {
			return wpautop( __( 'Campaign No longer available', 'finale-woocommerce-deal-pages' ) );
		}
		if ( $get_post->post_status == WCCT_SHORT_SLUG . 'disabled' ) {
			$output .= __( '<span class="deactivated">Deactivated</span>', 'finale-woocommerce-sales-countdown-timer-discount-plugin' );
		} else {

			$state = WCCT_Common::wcct_set_campaign_status( $id );

			$output .= __( sprintf( '<span class="%s">%s</span>', sanitize_title( $state ), $state ), 'finale-woocommerce-sales-countdown-timer-discount-plugin' );

		}
		$output .= ' </div>';

		return wpautop( $output );
	}

}
