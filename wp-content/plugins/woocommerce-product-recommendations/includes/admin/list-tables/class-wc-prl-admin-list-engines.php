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

/**
 * Modifies the custom's post type list table.
 *
 * @class    WC_PRL_Admin_List_Table_Engines
 * @version  1.0.0
 */
class WC_PRL_Admin_List_Table_Engines {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	private static $list_table_type = 'prl_engine';

	/**
	 * Object being shown on the row.
	 *
	 * @var object|null
	 */
	private static $engine = null;

	/**
	 * Hook in.
	 */
	public static function init() {

		// Remove quick/bulk edit actions.
		add_filter( 'post_row_actions', array( __CLASS__, 'disable_quick_edit' ), 10, 2 );
		add_filter( 'bulk_actions-edit-' . self::$list_table_type, array( __CLASS__, 'disable_bulk_actions' ) );

		// Custom engine filters.
		add_action( 'restrict_manage_posts', array( __CLASS__, 'add_engine_types_filter' ) );

		// Custom columns.
		add_filter( 'manage_edit-' . self::$list_table_type . '_sortable_columns', array( __CLASS__, 'engine_sortable_columns' ) );
		add_filter( 'manage_edit-' . self::$list_table_type . '_columns', array( __CLASS__, 'engine_custom_columns' ) ) ;
		add_action( 'manage_' . self::$list_table_type . '_posts_custom_column', array( __CLASS__, 'engine_render_columns' ), 10, 2 );

	}

	/**
	 * Remove Quick Edit action.
	 *
	 * @param  array   $actions
	 * @param  WP_Post $post
	 * @return array
	 */
	public static function disable_quick_edit( $actions, $post ) {

		if ( self::$list_table_type !== $post->post_type ) {
			return $actions;
		}

		// Remove the Quick Edit link
		if ( isset( $actions[ 'inline hide-if-no-js' ] ) ) {
			unset( $actions[ 'inline hide-if-no-js' ] );
		}

		return $actions;
	}

	/**
	 * Remove Bulk actions.
	 *
	 * @param  array $actions
	 * @return array
	 */
	public static function disable_bulk_actions( $actions ) {
		global $post;

		if ( self::$list_table_type !== $post->post_type ) {
			return $actions;
		}

		// Remove the Quick Edit link
		if ( isset( $actions[ 'edit' ] ) ) {
			unset( $actions[ 'edit' ] );
		}

		return $actions;
	}

	/**
	 * Setup custom table columns.
	 *
	 * @param  array $columns
	 * @return array
	 */
	public static function engine_custom_columns( $columns ) {

		if ( empty( $columns ) && ! is_array( $columns ) ) {
			$columns = array();
		}

		$show_columns                  = array();
		$show_columns[ 'cb' ]          = '<input type="checkbox" />';
		$show_columns[ 'title' ]       = __( 'Title', 'woocommerce-product-recommendations' );
		$show_columns[ 'type' ]        = __( 'Type', 'woocommerce-product-recommendations' );
		$show_columns[ 'deployments' ] = __( 'Deployments', 'woocommerce-product-recommendations' );
		$show_columns[ 'date' ]        = __( 'Date', 'woocommerce-product-recommendations' );
		$show_columns[ 'wc_actions' ]  = __( 'Actions', 'woocommerce-product-recommendations' );

		// Note: WP will auto-fill `title` & `date` columns. They are included in the list for ordering purposes.
		return $show_columns;
	}

	/**
	 * Define sortable table columns.
	 *
	 * @param  array $columns
	 * @return array
	 */
	public static function engine_sortable_columns( $columns ) {

		$custom = array(
			// ...
		);

		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Pre-fetch any data for the row each column has access to it.
	 *
	 * @param int $post_id
	 */
	private static function prepare_row_data( $post_id ) {

		if ( empty( self::$engine ) || ( self::$engine instanceof WC_PRL_Engine && self::$engine->get_id() !== $post_id ) ) {
			$the_engine   = new WC_PRL_Engine( $post_id );
			self::$engine = $the_engine;
		}
	}

	/**
	 * Display custom column content.
	 *
	 * @param  string $column
	 * @param  int    $post_id
	 * @return void
	 */
	public static function engine_render_columns( $column, $post_id ) {
		self::prepare_row_data( $post_id );

		if ( ! self::$engine ) {
			return;
		}

		if ( is_callable( array( __CLASS__, 'render_' . $column . '_column' ) ) ) {
			self::{"render_{$column}_column"}();
		}
	}

	/**
	 * Add a quick filter for custom engine types taxonomy.
	 */
	public static function add_engine_types_filter() {
		global $post;

		if ( $post && 'prl_engine' !== $post->post_type ) {
			return;
		}

		$taxonomy_slug = 'prl_engine_type';
		$types         = wc_prl_get_engine_types();

		?>
		<select name="<?php echo $taxonomy_slug ?>" id="<?php echo $taxonomy_slug ?>">
			<option value=""><?php _e( 'Filter by type', 'woocommerce-product-recommendations' ); ?></option>
			<?php
				$current_value = isset( $_GET[ $taxonomy_slug ] ) ? $_GET[ $taxonomy_slug ] : '';

				foreach ( $types as $slug => $name ) {
					printf
						(
						'<option value="%1$s" %2$s>%3$s</option>',
						$slug,
						$slug == $current_value ? ' selected="selected"' : '',
						$name
					);
				}
			?>
		</select>
		<?php
	}

	/**
	 * Render column: type.
	 */
	public static function render_type_column() {
		echo wc_prl_get_engine_type_label( self::$engine->get_type() );
	}

	/**
	 * Render column: deployments.
	 */
	public static function render_deployments_column() {
		$current_deployments = WC_PRL()->db->deployment->query( array( 'return' => 'ids', 'engine_id' => self::$engine->get_id() ) );
		echo count( $current_deployments );
	}

	/**
	 * Render column: wc_actions.
	 */
	public static function render_wc_actions_column() {
		$disabled = ! self::$engine->is_active() ? ' button-disabled' : '';
		?>
		<p>
			<a id="<?php echo self::$engine->get_id() ?>" class="button<?php echo $disabled ?> wc-action-button wc-action-button-regenerate" href="#" aria-label="<?php esc_attr_e( 'Regenerate recommendations', 'woocommerce-product-recommendations' ) ?>" title="<?php esc_attr_e( 'Regenerate recommendations', 'woocommerce-product-recommendations' ) ?>"><?php esc_html_e( 'Regenerate recommendations', 'woocommerce-product-recommendations' ) ?></a>
			<a class="button<?php echo $disabled ?> wc-action-button wc-action-button-deploy" href="<?php echo admin_url( 'admin.php?page=prl_locations&section=deploy&engine=' . self::$engine->get_id() ) ?>" aria-label="<?php esc_attr_e( 'Deploy', 'woocommerce-product-recommendations' ) ?>" title="<?php esc_attr_e( 'Deploy engine', 'woocommerce-product-recommendations' ) ?>"><?php esc_html_e( 'Deploy', 'woocommerce-product-recommendations' ) ?></a>
		</p>
		<?php
	}
}

WC_PRL_Admin_List_Table_Engines::init();
