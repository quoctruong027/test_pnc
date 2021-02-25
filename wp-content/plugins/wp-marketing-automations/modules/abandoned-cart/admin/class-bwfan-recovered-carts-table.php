<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BWFAN_Recovered_Cart_Table extends WP_List_Table {

	public static $per_page = 20;
	public static $current_page;
	public $data;
	public $date_format;
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
		echo wpautop( __( 'No recovered cart available.', 'wp-marketing-automations' ) ); //phpcs:ignore WordPress.Security.EscapeOutput
	}

	/** Made the data for recovered carts screen.
	 * @return array
	 */
	public function get_recovered_cart_table_data() {
		global $wpdb;
		$paged    = ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) ? sanitize_text_field( $_GET['paged'] ) : 1; //phpcs:ignore WordPress.Security.NonceVerification
		$per_page = ( isset( $_GET['posts_per_page'] ) && ! empty( $_GET['posts_per_page'] ) ) ? sanitize_text_field( $_GET['posts_per_page'] ) : self::$per_page; //phpcs:ignore WordPress.Security.NonceVerification
		$offset   = ( $paged - 1 ) * $per_page;
		$where    = '';

		/** Check for search query */
		if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			$where = 'AND m.meta_key = "_billing_email"';
			$where .= 'AND m.meta_value = ' . sanitize_text_field( $_GET['s'] ); //phpcs:ignore WordPress.Security.NonceVerification
		}

		$post_statuses = apply_filters( 'bwfan_recovered_cart_excluded_statuses', array( 'wc-pending', 'wc-failed', 'wc-cancelled', 'wc-refunded', 'trash', 'draft' ) );
		$post_status   = '(';
		foreach ( $post_statuses as $status ) {
			$post_status .= "'" . $status . "',";
		}
		$post_status .= "'')";

		$recovered_carts = $wpdb->get_results( $wpdb->prepare( "SELECT p.ID as id FROM {$wpdb->prefix}posts as p LEFT JOIN {$wpdb->prefix}postmeta as m ON p.ID = m.post_id WHERE p.post_type = %s AND p.post_status NOT IN $post_status AND m.meta_key = %s $where ORDER BY p.post_modified DESC LIMIT $offset,$per_page", 'shop_order', '_bwfan_ab_cart_recovered_a_id' ) );//phpcs:ignore WordPress.DB.PreparedSQL

		if ( empty( $recovered_carts ) ) {
			return array();
		}

		$found_posts = array();
		$items       = array();

		foreach ( $recovered_carts as $recovered_cart ) {
			$items[] = wc_get_order( $recovered_cart->id );
		}

		$found_posts['found_posts'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(p.ID) FROM {$wpdb->prefix}posts as p LEFT JOIN {$wpdb->prefix}postmeta as m ON p.ID = m.post_id WHERE p.post_type = %s AND p.post_status NOT IN $post_status AND m.meta_key = %s $where ", 'shop_order', '_bwfan_ab_cart_recovered_a_id' ) );//phpcs:ignore WordPress.DB.PreparedSQL
		$found_posts['items']       = $items;

		return $found_posts;
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
			'email'   => __( 'Email', 'wp-marketing-automations' ),
			'phone'   => __( 'Phone', 'wp-marketing-automations' ),
			'preview' => ' ',
			'date'    => __( 'Date', 'wp-marketing-automations' ),
			'status'  => __( 'Status', 'wp-marketing-automations' ) . ' ' . $this->get_status_tooltip(),
			'items'   => __( 'Items', 'wp-marketing-automations' ),
			'total'   => __( 'Total', 'wp-marketing-automations' ),
			'order'   => __( 'Order', 'wp-marketing-automations' ),
			'actions' => '',
		);

		return $columns;
	}

	public function get_status_tooltip() {
		if ( isset( $this->tooltip['status'] ) ) {
			return $this->tooltip['status'];
		}

		$this->tooltip['status'] = '<div class="bwfan_tooltip" data-size="5xl">
					<span class="bwfan_tooltip_text" data-position="top" style="text-align: left;">
						<b>Recovered</b> - Carts which are converted through are marked as Recovered.
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
				$column_temp = '<span data-status="recovered">' . __( 'Recovered', 'wp-marketing-automations' ) . '</span>';
				break;
			case 'total':
				$column_temp = $item[ $column_name ];
				break;
		}

		return $column_temp;
	}

	public function column_phone( $item ) {
		$phone_value = ( $item instanceof WC_Order ) ? $item->get_billing_phone() : __( 'N.A.', 'wp-marketing-automations' );

		$return = '<div class="bwfan-abandoned_email"><span class="phone">' . $phone_value . '</span>';
		$return .= '</div>';

		return $return;
	}

	public function column_email( $item ) {
		$return = '<div class="bwfan-recovered_cart_email"><span class="email">' . $item->get_billing_email() . '</span>';
		if ( $item->get_customer_id() ) {
			$user = get_user_by( 'ID', $item->get_customer_id() );
			if ( $user instanceof WP_User ) {
				$return .= '<p>(<a href="' . admin_url() . 'edit.php?post_type=shop_order&post_status=all&_customer_user=' . $user->ID . '&filter_action=Filter' . '">' . $user->display_name . '</a>)</p>';
			}
		}
		$return .= '</div>';

		return $return;
	}

	/**
	 * @param $item WC_Admin_Order
	 *
	 * @return string
	 */
	public function column_preview( $item ) {
		$data        = array();
		$others      = array();
		$products    = array();
		$order_items = $item->get_items();
		foreach ( $order_items as $product ) {
			$products[] = array(
				'name'  => $product->get_name(),
				'qty'   => $product->get_quantity(),
				'price' => number_format( $item->get_line_subtotal( $product ), 2, '.', '' ),
			);
		}
		$data['others']   = $others;
		$data['products'] = $products;
		$data['billing']  = $item->get_formatted_billing_address();
		$data['shipping'] = $item->get_formatted_shipping_address();
		$data['discount'] = $item->get_total_discount();
		$data['currency'] = get_woocommerce_currency_symbol( $item->get_currency() );
		$data['total']    = $item->get_total();

		$return = '<a href="javascript:void(0);" class="bwfan_recovered_cart_preview" data-order-id="' . $item->get_id() . '" title="' . __( 'Preview', 'wp-marketing-automations' ) . '" data-izimodal-open="#modal-show-recovered-cart-details"></a>';
		$return .= "<span class='bwfan-display-none bwfan_recovered_cart_preview_info' data-details='" . wp_json_encode( $data, JSON_HEX_APOS ) . "'></span>";

		return $return;
	}

	public function column_date( $item ) {
		$date = BWFAN_Common::get_human_readable_time( strtotime( $item->get_date_created() ), $item->get_date_created()->date( $this->date_format ) );

		return '<div class="bwfan-recovered_date">' . $date . '</div>';
	}

	public function column_items( $item ) {
		$names = [];
		foreach ( $item->get_items() as $value ) {
			$names[] = $value->get_name();
		}

		$names = implode( ', ', $names );

		return '<div class="bwfan-recovered_items">' . $names . '</div>';
	}

	public function column_total( $item ) {
		return '<div class="bwfan-recovered_total">' . $item->get_currency() . ' ' . $item->get_total() . '</div>';
	}

	public function column_order( $item ) {
		$buyer = '';

		if ( ! $item instanceof WC_Order ) {
			return '';
		}

		if ( $item->get_billing_first_name() || $item->get_billing_last_name() ) {
			/* translators: 1: first name 2: last name */
			$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), $item->get_billing_first_name(), $item->get_billing_last_name() ) );
		} elseif ( $item->get_billing_company() ) {
			$buyer = trim( $item->get_billing_company() );
		} elseif ( $item->get_customer_id() ) {
			$user  = get_user_by( 'id', $item->get_customer_id() );
			$buyer = ucwords( $user->display_name );
		}

		$buyer = apply_filters( 'woocommerce_admin_order_buyer_name', $buyer, $item );

		if ( $item->get_status() === 'trash' ) {
			$output = '<strong>#' . esc_attr( $item->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong>';
		} else {
			$output = '<a href="' . esc_url_raw( admin_url( 'post.php?post=' . absint( $item->get_id() ) ) . '&action=edit' ) . '" class="order-view"><strong>#' . esc_attr( $item->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong></a>';
		}

		return $output;
	}

	public function column_actions( $item ) {
		$view_task_url = add_query_arg( [
			'page'             => 'autonami',
			'tab'              => 'carts',
			'ab_section'       => 'recovered',
			'bwfan_cart_id'    => get_post_meta( $item->get_order_number(), '_bwfan_recovered_ab_id', true ),
			'bwfan_cart_email' => esc_attr( $item->get_billing_email() ),
		], admin_url( 'admin.php' ) );

		$output = '<div class="bwfan-abandoned_actions">';
		$output .= '<a href="' . $view_task_url . '" class="bwfan_view_abandoned_tasks" data-id="' . $item->get_order_number() . '">' . __( 'View Tasks', 'wp-marketing-automations' ) . '</a>';
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
		$input_id = $input_id . '-search-input';

		?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_js( $text ); ?>:</label>
            <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php
			submit_button( $text, '', '', false, array(
				'id' => 'search-submit',
			) );
			?>
        </p>
		<?php
	}


}
