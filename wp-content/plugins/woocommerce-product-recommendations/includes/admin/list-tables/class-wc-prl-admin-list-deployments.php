<?php
/**
 * WC_PRL_Admin_List_Table_Engines class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Adds a custom deployments list table.
 *
 * @class    WC_PRL_Admin_List_Table_Engines
 * @version  1.3.0
 */
class WC_PRL_Deployments_List_Table extends WP_List_Table {

	/**
	 * Page home URL.
	 * @const PAGE_URL
	 */
	const PAGE_URL = 'admin.php?page=prl_locations';

	public function __construct() {
		global $status, $page;

		parent::__construct( array(
			'singular' => 'deployment',
			'plural'   => 'deployments',
		) );
	}

	/**
	 * This is a default column renderer
	 *
	 * @param $item - row (key, value array)
	 * @param $column_name - string (key)
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '-';
	}

	/**
	 * Handles the title column output.
	 *
	 * @param array $item
	 */
	public function column_title( $item ) {

		$actions = array(
            'edit'       => sprintf( '<a href="' . admin_url( 'admin.php?page=prl_locations&section=deploy&deployment=%d' ) . '">%s</a>', $item[ 'id' ], __( 'Edit', 'woocommerce-product-recommendations' ) ),
            'regenerate' => sprintf( '<a id="%d" href="' . admin_url( 'admin.php?page=prl_locations&delete=%d' ) . '">%s</a>', $item[ 'id' ], $item[ 'id' ], __( 'Regenerate', 'woocommerce-product-recommendations' ) ),
            'delete'     => sprintf( '<a href="' . admin_url( 'admin.php?page=prl_locations&delete=%d' ) . '">%s</a>', $item[ 'id' ], __( 'Delete', 'woocommerce-product-recommendations' ) ),
        );

		$title          = $item[ 'title' ] ? $item[ 'title' ] : esc_html__( '(no title)', 'woocommerce-product-recommendations' );
		$inactive_label = sprintf(
				'<i>&nbsp;&mdash;&nbsp;%s</i>',
				esc_html__( 'inactive', 'woocommerce-product-recommendations' )
			);

		printf(
			'<a class="row-title" href="%s" aria-label="%s">%s</a>%s%s',
			admin_url( 'admin.php?page=prl_locations&section=deploy&deployment=' . $item[ 'id' ] ),
			esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)', 'woocommerce-product-recommendations' ), $title ) ),
			$title,
			isset( $item[ 'active' ] ) && 'on' !== $item[ 'active' ] ? $inactive_label : '',
			$this->row_actions( $actions )
		);
	}

	/**
	 * Handles the checkbox column output.
	 *
	 * @param array $item
	 */
	public function column_cb( $item ) {
		?><label class="screen-reader-text" for="cb-select-<?php the_ID(); ?>"><?php
			printf( __( 'Select %s', 'woocommerce-product-recommendations' ), $item[ 'title' ] );
		?></label>
		<input id="cb-select-<?php echo $item[ 'id' ]; ?>" type="checkbox" name="deployment[]" value="<?php echo $item[ 'id' ]; ?>" />
		<?php
	}

	/**
	 * Handles the page column output.
	 *
	 * @param array $item
	 */
	public function column_page( $item ) {
		$location_id = $item[ 'location_id' ] ? $item[ 'location_id' ] : '';
		$locations   = WC_PRL()->locations->get_locations();

		if ( $location_id && isset( $locations[ $location_id ] ) ) {
			echo $locations[ $location_id ]->get_title();
		} else {
			echo __( 'N/A', 'woocommerce-product-recommendations' );
		}
	}

	/**
	 * Handles the location column output.
	 *
	 * @param array $item
	 */
	public function column_location( $item ) {
		$hook     = $item[ 'hook' ] ? $item[ 'hook' ] : '';
		$location = WC_PRL()->locations->get_location_by_hook( $hook );
		if ( ! $location ) {
			echo __( 'N/A', 'woocommerce-product-recommendations' );
			return;
		}

		$hooks = $location->get_hooks();
		if ( ! isset( $hooks[ $hook ] ) ) {
			echo __( 'N/A', 'woocommerce-product-recommendations' );
			return;
		}

		printf(
			'<a href="%s">%s</a>',
			admin_url( 'admin.php?page=prl_locations&section=hooks&location=' . $item[ 'location_id' ] . '&hook=' . $item[ 'hook' ] ),
			$hooks[ $hook ][ 'label' ]
		);
	}

	/**
	 * Handles the location column output.
	 *
	 * @param array $item
	 */
	public function column_engine( $item ) {
		$engine_id = $item[ 'engine_id' ] ? $item[ 'engine_id' ] : 0;
		$engine    = new WC_PRL_Engine( absint( $engine_id ) );

		echo '<a href="' . admin_url( sprintf( 'post.php?post=%d&action=edit', $engine->get_id() ) ) . '" title="' . esc_attr__( 'Edit engine', 'woocommerce-product-recommendations' ) . '">' . ( $engine->get_name() ? $engine->get_name() : __( '(untitled)', 'woocommerce-product-recommendations' ) ) . '</a>';
	}

	/**
	 * Handles the location column output.
	 *
	 * @param array $item
	 */
	public function column_wc_actions( $item ) {
		?>
		<p>
			<a class="button wc-action-button edit" href="<?php echo admin_url( 'admin.php?page=prl_locations&section=deploy&deployment=' . $item[ 'id' ] ) ?>" aria-label="<?php esc_attr_e( 'Edit deployment', 'woocommerce-product-recommendations' ) ?>" title="<?php esc_attr_e( 'Edit', 'woocommerce-product-recommendations' ) ?>"><?php esc_html_e( 'Edit deployment', 'woocommerce-product-recommendations' ) ?></a>
			<a id="<?php echo $item[ 'id' ] ?>" class="button wc-action-button wc-action-button-regenerate" href="#" aria-label="<?php esc_attr_e( 'Regenerate recommendations', 'woocommerce-product-recommendations' ) ?>" title="<?php esc_attr_e( 'Regenerate recommendations', 'woocommerce-product-recommendations' ) ?>"><?php esc_html_e( 'Regenerate recommendations', 'woocommerce-product-recommendations' ) ?></a>
			<a class="button wc-action-button delete" href="<?php echo admin_url( 'admin.php?page=prl_locations&delete=' . $item[ 'id' ] ) ?>" aria-label="<?php esc_attr_e( 'Delete deployment', 'woocommerce-product-recommendations' ) ?>" title="<?php esc_attr_e( 'Delete', 'woocommerce-product-recommendations' ) ?>"><?php esc_html_e( 'Delete deployment', 'woocommerce-product-recommendations' ) ?></a>
		</p>
		<?php
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 */
	public function get_columns() {

		$columns                 = array();
		$columns[ 'cb' ]         = '<input type="checkbox" />';
		$columns[ 'title' ]      = _x( 'Title', 'column_name', 'woocommerce-product-recommendations' );
		$columns[ 'page' ]       = _x( 'Page', 'column_name', 'woocommerce-product-recommendations' );
		$columns[ 'location' ]   = _x( 'Location', 'column_name', 'woocommerce-product-recommendations' );
		$columns[ 'engine' ]     = _x( 'Engine', 'column_name', 'woocommerce-product-recommendations' );
		$columns[ 'wc_actions' ] = _x( 'Actions', 'column_name', 'woocommerce-product-recommendations' );

		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'title'    => array( 'title', true ),
			'page'     => array( 'location_id', true ),
			'location' => array( 'hook', true ),
			'engine'   => array( 'engine_id', true )
		);

		return $sortable_columns;
	}

	protected function get_bulk_actions() {
		$actions           = array();
		$actions['delete'] = __( 'Delete Permanently', 'woocommerce-product-recommendations' );
		return $actions;
	}

	private function process_bulk_action() {

		if ( $this->current_action() ) {

			$deployments = is_array( $_GET[ 'deployment' ] ) ? array_map( 'absint', $_GET[ 'deployment' ] ) : array();

			if ( ! empty( $deployments ) && 'delete' === $this->current_action() ) {

				foreach ( $deployments as $id ){
					WC_PRL()->db->deployment->delete( $id );
				}

				WC_PRL_Admin_Notices::add_notice( __( 'Deployments deleted.', 'woocommerce-product-recommendations' ), 'success', true );
			}

			wp_redirect( admin_url( self::PAGE_URL ) );
			exit();
		}
	}

	public function prepare_items() {

		$per_page = 10;

		// Table columns;
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$total_items = WC_PRL()->db->deployment->count();

		$paged   = isset( $_REQUEST[ 'paged' ] ) ? max( 0, intval( $_REQUEST[ 'paged' ] ) - 1 ) : 0;
		$orderby = ( isset( $_REQUEST[ 'orderby' ] ) && in_array( $_REQUEST[ 'orderby' ], array_keys( $this->get_sortable_columns() ) ) ) ? $_REQUEST[ 'orderby' ] : 'id';

		$order = ( isset( $_REQUEST[ 'order' ] ) && in_array( $_REQUEST[ 'order' ], array( 'asc', 'desc' ) ) ) ? $_REQUEST[ 'order' ] : 'desc';

		$this->items = WC_PRL()->db->deployment->query( array(
			'order_by' => array( $orderby => $order ),
			'limit'    => $per_page,
			'offset'   => $paged * $per_page
		) );

		// [REQUIRED] configure pagination
		$this->set_pagination_args( array(
			'total_items' => $total_items, // total items defined above
			'per_page'    => $per_page, // per page constant defined at top of method
			'total_pages' => ceil( $total_items / $per_page ) // calculate pages count
		) );
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 */
	public function no_items() {
		// Show a boarding based on deployments and engines....
		$engines_count = wp_count_posts( 'prl_engine', 'readable' );
		if ( 0 < absint( $engines_count->publish ) ) {
			?><div class="prl-deployments-empty-state">
				<p class="main">
					<?php esc_html_e( 'Ready to start your Engines?', 'woocommerce-product-recommendations' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Deploy an Engine now to offer product recommendations at a Location of your store.', 'woocommerce-product-recommendations' ); ?>
				</p>
				<div class="quick-deploy__search" data-action="<?php echo admin_url( 'admin.php?page=prl_locations&section=deploy&quick=1&engine=%%engine_id%%' ); ?>">
					<select class="wc-engine-search" data-swtheme="woo" data-placeholder="<?php _e( 'Search for an Engine&hellip;', 'woocommerce-product-recommendations' ); ?>" data-limit="100" name="engine">
					</select>
				</div>
			</div><?php
		} else {
			?><div class="prl-engines-empty-state">
				<p class="main">
					<?php esc_html_e( 'Create an Engine', 'woocommerce-product-recommendations' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Want to add product recommendations to a Location of your store?', 'woocommerce-product-recommendations' ); ?>
					<br/>
					<?php esc_html_e( 'Start by creating an Engine &mdash; then, return here to deploy it.', 'woocommerce-product-recommendations' ); ?>
				</p>
				<a class="button sw-button-primary sw-button-primary--woo" id="sw-button-primary" href="<?php echo admin_url( 'post-new.php?post_type=prl_engine' ); ?>"><?php esc_html_e( 'Create engine', 'woocommerce-product-recommendations' ); ?></a>
			</div><?php
		}
	}
}
