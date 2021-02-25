<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BWFAN_Lost_Cart_Table extends WP_List_Table {

	public static $per_page = 20;
	public static $current_page;
	public $data;
	public $date_format;
	public $timezone_offset;
	public $tooltip = [];

	/**meta_data
	 * Constructor.
	 * @since  1.0.0
	 */
	public function __construct( $args = array() ) {
		self::$current_page = $this->get_pagenum();
		$this->data         = array();
		$this->date_format  = BWFAN_Common::get_date_format();

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
		echo wpautop( __( 'No lost cart available.', 'wp-marketing-automations' ) ); //phpcs:ignore WordPress.Security.EscapeOutput
	}

	/** Made the data for lost carts screen.
	 * @return array
	 */
	public function get_lost_cart_table_data() {
		global $wpdb;
		$paged    = ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) ? sanitize_text_field( $_GET['paged'] ) : 1; //phpcs:ignore WordPress.Security.NonceVerification
		$per_page = ( isset( $_GET['posts_per_page'] ) && ! empty( $_GET['posts_per_page'] ) ) ? sanitize_text_field( $_GET['posts_per_page'] ) : self::$per_page; //phpcs:ignore WordPress.Security.NonceVerification
		$offset   = ( $paged - 1 ) * $per_page;
		$where    = 'WHERE status = 2';

		/** Check for search query */
		if ( isset( $_REQUEST['search_term'] ) && ! empty( $_REQUEST['search_term'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			$search_term = sanitize_text_field( $_REQUEST['search_term'] ); //phpcs:ignore WordPress.Security.NonceVerification
			$search_by   = 'email';
			if ( isset( $_REQUEST['bwfanc_search_by'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
				$search_by = sanitize_text_field( $_REQUEST['bwfanc_search_by'] ); //phpcs:ignore WordPress.Security.NonceVerification
			}
			$where .= sprintf( " AND %s='%s'", $search_by, $search_term ); //phpcs:ignore WordPress.Security.NonceVerification
		}
		$abandoned_carts = BWFAN_Model_Abandonedcarts::get_abandoned_data( $where, $offset, $per_page, 'last_modified' );
		if ( empty( $abandoned_carts ) ) {
			return array();
		}

		$found_posts = array();
		$items       = array();

		foreach ( $abandoned_carts as $abandoned ) {
			$items[] = $abandoned;
		}

		$found_posts['found_posts'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}bwfan_abandonedcarts $where" ); // WPCS: unprepared SQL OK
		$found_posts['items']       = $items;

		return $found_posts;
	}

	public function process_bulk_action() {
		if ( isset( $_GET['bwfan_delete_lost_cart'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			$where = array(
				'ID' => sanitize_text_field( $_GET['bwfan_delete_lost_cart'] ), //phpcs:ignore WordPress.Security.NonceVerification
			);
			BWFAN_Model_Abandonedcarts::delete_abandoned_cart_row( $where );

			return;
		}

		if ( ! isset( $_GET['action'] ) || ! isset( $_GET['action2'] ) || ! isset( $_GET['bwfan_abandoned_ids'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		if ( 'bwfan_delete_lost_abandoned' !== $_GET['action'] && 'bwfan_delete_lost_abandoned' !== sanitize_text_field( $_GET['action2'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		$ids = $_GET['bwfan_abandoned_ids']; //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		if ( empty( $ids ) || ! is_array( $ids ) ) {
			return;
		}

		/** Bulk Delete Abandoned Cart */
		foreach ( $ids as $id ) {
			$where = array(
				'ID' => $id,
			);
			BWFAN_Model_Abandonedcarts::delete_abandoned_cart_row( $where );
		}

		do_action( 'bwfan_bulk_delete_lost_carts' );
	}

	/**
	 * Retrieve an array of possible bulk actions.
	 * @return array
	 * @since  1.0.0
	 */
	public function get_bulk_actions() {
		return array(
			'bwfan_delete_lost_abandoned' => 'Delete',
		);
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
			'total_pages' => ceil( $total_items / self::$per_page ),
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
			'cb'      => '<input type="checkbox" />',
			'email'   => __( 'Email', 'wp-marketing-automations' ),
			'phone'   => __( 'Phone', 'wp-marketing-automations' ),
			'preview' => ' ',
			'date'    => __( 'Last Active', 'wp-marketing-automations' ),
			'status'  => __( 'Status', 'wp-marketing-automations' ) . ' ' . $this->get_status_tooltip(),
			'items'   => __( 'Items', 'wp-marketing-automations' ),
			'total'   => __( 'Total', 'wp-marketing-automations' ),
			'order'   => __( 'Order', 'wp-marketing-automations' ),
			'actions' => ' ',
		);

		return $columns;
	}

	public function get_status_tooltip() {
		if ( isset( $this->tooltip['status'] ) ) {
			return $this->tooltip['status'];
		}

		$global_settings = BWFAN_Common::get_global_settings();
		$lost_time       = ( isset( $global_settings['bwfan_ab_mark_lost_cart'] ) ) ? $global_settings['bwfan_ab_mark_lost_cart'] : 15;
		$lost_time       = absint( $lost_time );
		$lost_time       = ( 1 === $lost_time ) ? $lost_time . ' day' : $lost_time . ' days';

		$this->tooltip['status'] = '<div class="bwfan_tooltip" data-size="5xl">
					<span class="bwfan_tooltip_text" data-position="top" style="text-align: left;">
						<b>Lost</b> - Carts where automation sequence is complete and did not complete the order within ' . $lost_time . ' are marked as Lost.
					</span>
				</div>';

		return $this->tooltip['status'];
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
			case 'email':
				$column_temp = $item[ $column_name ];
				break;
			case 'date':
				$column_temp = $item[ $column_name ];
				break;
			case 'status':
				$column_temp = '<span data-status="lost">' . __( 'Lost', 'wp-marketing-automations' ) . '</span>';
				break;
			case 'total':
				$column_temp = $item[ $column_name ];
				break;
		}

		return $column_temp;
	}

	public function column_phone( $item ) {
		$checkout_data = json_decode( $item->checkout_data, true );
		$phone_value   = ( is_array( $checkout_data ) && isset( $checkout_data['fields'] ) && is_array( $checkout_data['fields'] ) && isset( $checkout_data['fields']['billing_phone'] ) && ! empty( $checkout_data['fields']['billing_phone'] ) ) ? $checkout_data['fields']['billing_phone'] : __( 'N.A.', 'wp-marketing-automations' );

		$return = '<div class="bwfan-abandoned_email"><span class="phone">' . $phone_value . '</span>';
		$return .= '</div>';

		return $return;
	}

	public function column_cb( $item ) {
		?>
        <div class='bwfan_fsetting_table_title'>
            <div class=''>
                <input name='bwfan_abandoned_ids[]' data-id="<?php echo esc_attr( $item->ID ); ?>" value="<?php echo esc_attr( $item->ID ); ?>" type='checkbox' class=''>
                <label for='' class=''></label>
            </div>
        </div>
		<?php
	}

	public function column_email( $item ) {
		$return = '<div class="bwfan-abandoned_email"><span class="email">' . $item->email . '</span>';
		if ( ! empty( $item->user_id ) ) {
			$user = get_user_by( 'ID', $item->user_id );
			if ( $user instanceof WP_User ) {
				$return .= '<p>(<a href="' . admin_url() . 'edit.php?post_type=shop_order&post_status=all&_customer_user=' . $user->ID . '&filter_action=Filter' . '">' . $user->display_name . '</a>)</p>';
			}
		}
		$return .= '</div>';

		return $return;
	}

	public function column_preview( $item ) {
		$data          = array();
		$billing       = array();
		$shipping      = array();
		$others        = array();
		$products      = array();
		$products_data = maybe_unserialize( $item->items );
		$nice_names    = BWFAN_Abandoned_Cart::get_woocommerce_default_checkout_nice_names();
		$checkout_data = json_decode( $item->checkout_data, true );

		if ( is_array( $checkout_data ) && count( $checkout_data ) > 0 ) {
			$fields             = ( isset( $checkout_data['fields'] ) ) ? $checkout_data['fields'] : [];
			$available_gateways = WC()->payment_gateways->payment_gateways();

			if ( ! empty( $fields ) ) {
				foreach ( $fields as $key => $value ) {
					if ( 'billing_phone' === $key ) {
						$others[ $nice_names[ $key ] ] = $value;
						continue;
					}
					if ( false !== strpos( $key, 'billing' ) && isset( $nice_names[ $key ] ) ) {
						$key             = str_replace( 'billing_', '', $key );
						$billing[ $key ] = $value;
						continue;
					}
					if ( false !== strpos( $key, 'shipping' ) && isset( $nice_names[ $key ] ) ) {
						$key              = str_replace( 'shipping_', '', $key );
						$shipping[ $key ] = $value;
						continue;
					}
					if ( 'payment_method' === $key ) {
						if ( isset( $available_gateways[ $value ] ) && 'yes' === $available_gateways[ $value ]->enabled ) {
							$value = $available_gateways[ $value ]->method_title;
						}
						if ( isset( $nice_names[ $key ] ) ) {
							$others[ $nice_names[ $key ] ] = $value;
						}
						continue;
					}
					if ( isset( $nice_names[ $key ] ) ) {
						$others[ $nice_names[ $key ] ] = $value;
					}
				}
			}

			/** Remove WordPress page id in abandoned preview if WordPress page id is same as Aero page id. */
			if ( isset( $checkout_data['current_page_id'] ) && isset( $checkout_data['aerocheckout_page_id'] ) && $checkout_data['current_page_id'] === $checkout_data['aerocheckout_page_id'] ) {
				unset( $checkout_data['current_page_id'] );
			}

			foreach ( $checkout_data as $key => $value ) {
				if ( isset( $nice_names[ $key ] ) ) {
					$others[ $nice_names[ $key ] ] = $value;
				}
			}
		}

		if ( is_array( $products_data ) ) {
			$hide_free_products = BWFAN_Common::hide_free_products_cart_order_items();
			foreach ( $products_data as $product ) {
				if ( true === $hide_free_products && empty( $product['line_total'] ) ) {
					continue;
				}
				$products[] = array(
					'name'  => $product['data']->get_formatted_name(),
					'qty'   => $product['quantity'],
					'price' => $product['line_total'],
				);
			}
		}

		$data['billing']  = WC()->countries->get_formatted_address( $billing );
		$data['shipping'] = WC()->countries->get_formatted_address( $shipping );
		$data['others']   = $others;
		$data['products'] = $products;

		$return = '<a href="javascript:void(0);" class="bwfan_abandoned_preview" data-abandoned-id="' . esc_attr( $item->ID ) . '" title="' . esc_attr( 'Preview', 'wp-marketing-automations' ) . '" data-izimodal-open="#modal-show-abandoned-cart-details"></a>';
		$return .= "<span class='bwfan-display-none bwfan_abandoned_preview_info' data-details='" . wp_json_encode( $data, JSON_HEX_APOS ) . "'></span>";

		return $return;
	}

	public function column_date( $item ) {
		$timestamp = strtotime( $item->last_modified );
		$date      = BWFAN_Common::get_human_readable_time( $timestamp, get_date_from_gmt( date( 'Y-m-d H:i:s', $timestamp ), $this->date_format ) );

		return '<div class="bwfan-abandoned_date">' . $date . '</div>';
	}

	public function column_items( $item ) {
		$items = maybe_unserialize( $item->items );
		if ( empty( $items ) ) {
			return '';
		}

		$hide_free_products = BWFAN_Common::hide_free_products_cart_order_items();
		$names              = [];
		foreach ( $items as $value ) {
			if ( true === $hide_free_products && empty( $value['line_total'] ) ) {
				continue;
			}
			$names[] = $value['data']->get_name();
		}

		if ( empty( $names ) ) {
			return '';
		}

		$names = implode( ', ', $names );

		return '<div class="bwfan-abandoned_items">' . $names . '</div>';
	}

	public function column_total( $item ) {
		return '<div class="bwfan-abandoned_count">' . $item->currency . ' ' . $item->total . '</div>';
	}

	public function column_order( $item ) {
		if ( 0 === intval( $item->order_id ) ) {
			return '<div class="bwfan-abandoned_order">' . __( 'N.A.', 'wp-marketing-automations' ) . '</div>';
		}

		$obj   = wc_get_order( $item->order_id );
		$buyer = '';

		if ( ! $obj instanceof WC_Order ) {
			return '';
		}

		if ( $obj->get_billing_first_name() || $obj->get_billing_last_name() ) {
			/* translators: 1: first name 2: last name */
			$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), $obj->get_billing_first_name(), $obj->get_billing_last_name() ) );
		} elseif ( $obj->get_billing_company() ) {
			$buyer = trim( $obj->get_billing_company() );
		} elseif ( $obj->get_customer_id() ) {
			$user  = get_user_by( 'id', $obj->get_customer_id() );
			$buyer = ucwords( $user->display_name );
		}

		$buyer = apply_filters( 'woocommerce_admin_order_buyer_name', $buyer, $obj );

		if ( $obj->get_status() === 'trash' ) {
			$output = '<strong>#' . esc_attr( $obj->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong>';
		} else {
			$output = '<a href="' . esc_url_raw( admin_url( 'post.php?post=' . absint( $obj->get_id() ) ) . '&action=edit' ) . '" class="order-view"><strong>#' . esc_attr( $obj->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong></a>';
		}

		return $output;
	}

	public function column_actions( $item ) {
		$delete_url = add_query_arg( [
			'page'                   => 'autonami',
			'tab'                    => 'carts',
			'ab_section'             => 'lost',
			'bwfan_delete_lost_cart' => esc_attr( $item->ID ),
		], admin_url( 'admin.php' ) );

		$view_task_url = add_query_arg( [
			'page'             => 'autonami',
			'tab'              => 'carts',
			'ab_section'       => 'lost',
			'bwfan_cart_id'    => esc_attr( $item->ID ),
			'bwfan_cart_email' => esc_attr( $item->email ),
		], admin_url( 'admin.php' ) );

		$output = '<div class="bwfan-abandoned_actions">';
		$output .= '<a href="' . $view_task_url . '" class="bwfan_view_abandoned_tasks" data-id="' . $item->ID . '">' . __( 'View Tasks', 'wp-marketing-automations' ) . '</a>';
		$output .= ' | ';
		$output .= '<a href="' . $delete_url . '">' . __( 'Delete', 'wp-marketing-automations' ) . '</a>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Displays the search box.
	 *
	 * @param string $text The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 *
	 * @since 3.1.0
	 *
	 */
	public function search_box( $text = '', $input_id = 'bwfan' ) {
		?>
        <form method="post">
            <p class="search-box">
                <input type="search" name="search_term" value="<?php echo isset( $_REQUEST['search_term'] ) ? esc_attr( wp_unslash( sanitize_text_field( $_REQUEST['search_term'] ) ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification ?>" required/>
                <select name="bwfanc_search_by">
					<?php

					$options = [
						'email'            => __( 'Email', 'autonami-automation' ),
						'checkout_page_id' => __( 'Checkout Page ID', 'autonami-automation' ),
					];

					foreach ( $options as $id => $option ) {
						$selected = '';
						if ( isset( $_REQUEST['bwfanc_search_by'] ) && $id === sanitize_text_field( $_REQUEST['bwfanc_search_by'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
							$selected = 'selected';
						}
						echo '<option value="' . esc_attr( $id ) . '" ' . esc_attr( $selected ) . ' >' . esc_attr( $option ) . '</option>';
					}

					?>
                </select>
				<?php
				submit_button( $text, '', '', false );
				?>
            </p>
        </form>
		<?php
	}


}
