<?php

/**
 * Functions for the Settings / Saved Tabs List Page
 */
class YIKES_Custom_Product_Tabs_Pro_Saved_Tabs_List {

	const SCREEN_BASE = 'toplevel_page_yikes-woo-settings';

	/**
	 * Define hooks.
	 */
	public function __construct() {

		// Saved tabs page - customize page title.
		add_filter( 'yikes-woo-settings-menu-title', array( $this, 'change_settings_page_title' ), 10 );

		// Settings page - Add a <th> to the saved tabs list for taxonomy & global.
		add_action( 'yikes-woo-saved-tabs-table-header', array( $this, 'add_taxonomy_th_to_saved_tabs_list' ), 10 );
		add_action( 'yikes-woo-saved-tabs-table-header', array( $this, 'add_global_th_to_saved_tabs_list' ), 10 );

		// Settings page - Add taxonomy & global data to the saved tabs list.
		add_action( 'yikes-woo-saved-tabs-table-column', array( $this, 'add_taxonomy_data_to_saved_tabs_list' ), 10, 9 );
		add_action( 'yikes-woo-saved-tabs-table-column', array( $this, 'add_global_data_to_saved_tabs_list' ), 10, 9 );

		// Saved tabs list & single pages - show a warning if the user has too many products.
		add_action( 'yikes-woo-display-too-many-products-warning', array( 'YIKES_Custom_Product_Tabs_Pro_Admin', 'display_too_many_products_warning' ), 10 );

		// Saved tabs list & single pages - add a class to the table.
		add_action( 'yikes-woo-saved-tabs-table-classes', array( 'YIKES_Custom_Product_Tabs_Pro_Admin', 'add_class_to_saved_tabs_table' ), 10 );

		// Enqueue.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 10, 1 );

		// Re-order tabs AJAX call.
		add_action( 'wp_ajax_reorder_yikes_tabs', array( $this, 'update_tab_order' ) );

		// Re-order tabs in the list table on page load.
		add_filter( 'yikes_woo_reorder_saved_tabs', array( $this, 'reorder_tabs_filter' ) );
	}

	/**
	 * Enqueue assets.
	 *
	 * @param string $hook The current screen's base.
	 */
	public function enqueue( $hook ) {
		if ( self::SCREEN_BASE === $hook ) {

			// Get our option.
			$settings         = get_option( 'cptpro_settings', array() );
			$ordering_enabled = isset( $settings['enable_ordering'] ) && $settings['enable_ordering'] === true;

			wp_enqueue_script( 'cptpro-list-table', YIKES_Custom_Product_Tabs_Pro_URI . 'js/saved-tabs-pro-list-table.min.js', array( 'jquery', 'jquery-ui-sortable' ), YIKES_Custom_Product_Tabs_Pro_Version, true );
			wp_localize_script(
				'cptpro-list-table',
				'cptpro_list_table',
				array(
					'reorder_tabs_action' => 'reorder_yikes_tabs',
					'reorder_tabs_nonce'  => wp_create_nonce( 'order_tabs_nonce' ),
					'ordering_enabled'    => $ordering_enabled,
				)
			);
		}
	}

	/**
	 * AJAX handler to update the tab order.
	 */
	public function update_tab_order() {

		// Verify the nonce.
		if ( ! check_ajax_referer( 'order_tabs_nonce', 'nonce', false ) ) {
		 	wp_send_json_error();
		}

		// Get our saved tab order.
		$order = isset( $_POST['tabs'] ) ? $_POST['tabs'] : array();

		if ( empty( $order ) ) {
			wp_send_json_error();
		}

		// Get our saved tabs option.
		$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs' );
		$saved_tabs = empty( $saved_tabs ) ? array() : $saved_tabs;

		foreach ( $order as $tab_id => $order ) {
			// Sanitize.
			$tab_id = filter_var( $tab_id, FILTER_SANITIZE_NUMBER_INT );
			$order  = filter_var( $order, FILTER_SANITIZE_NUMBER_INT );

			if ( empty( $tab_id ) || empty( $order ) || ! isset( $saved_tabs[ $tab_id ] ) ) {
				continue;
			}

			$saved_tabs[ $tab_id ]['tab_order'] = $order;
		}

		update_option( 'yikes_woo_reusable_products_tabs', $saved_tabs );

		wp_send_json_success();
	}

	/**
	 * Filter the tabs before they're shown in the list table.
	 *
	 * @param array $tabs The array of saved tabs.
	 */
	public function reorder_tabs_filter( $tabs ) {
		usort( $tabs, array( $this, 'reorder_tabs' ) );
		return $tabs;
	}

	/**
	 * Sort the tabs by the 'tab_order' key.
	 *
	 * @param array $tab_a The first tab to compare.
	 * @param array $tab_b The second tab to compare.
	 *
	 * @return int The difference between tab orders if the key exists otherwise return 0 to keep the order the same as it was.
	 */
	public function reorder_tabs( $tab_a, $tab_b ) {
		if ( isset( $tab_a['tab_order'] ) && isset( $tab_b['tab_order'] ) ) {
			return $tab_a['tab_order'] - $tab_b['tab_order'];
		}

		return 0;
	}

	/**
	 * Display a different title for the Settings/Saved Tabs List page
	 */
	public function change_settings_page_title() {
		return __( 'Custom Product Tabs Pro', 'custom-product-tabs-pro' );
	}

	/**
	 * Display a new `<th>` in the saved tabs table's `<thead>` and `<tfoot>`.
	 */
	public function add_taxonomy_th_to_saved_tabs_list() {
		?>
			<th class="manage-column column-taxonomy" scope="col">
				<?php esc_html_e( 'Taxonomy', 'custom-product-tabs-pro' ); ?>
			</th>
		<?php
	}

	/**
	 * Show a tab's taxonomies in the saved tabs table
	 *
	 * @param array $tab Array of tab data.
	 */
	public function add_taxonomy_data_to_saved_tabs_list( $tab ) {
		?>
			<td class="column-taxonomy">
				<?php
				if ( isset( $tab['taxonomies'] ) && ! empty( $tab['taxonomies'] ) ) {

					foreach ( $tab['taxonomies'] as $taxonomy_slug => $terms ) {

						if ( is_array( $terms ) && ! empty( $terms ) ) {

							$taxonomy       = get_taxonomy( $taxonomy_slug );
							$taxonomy_label = is_object( $taxonomy ) && isset( $taxonomy->label ) ? $taxonomy->label : '';
							$ii             = 0;
							?>
								<div class="saved-tabs-list-taxonomy">
									<span class="saved-tabs-list-taxonomy-label"><?php echo esc_html( $taxonomy_label ); ?>: </span>
									<?php
									foreach ( $terms as $term_slug ) {
										$term_object = get_term_by( 'slug', $term_slug, $taxonomy_slug );

										if ( ! empty( $term_object ) ) {
											$product_term_url = add_query_arg( array(
												$taxonomy_slug => $term_object->slug,
												'post_type'    => 'product',
											), admin_url( 'edit.php' ) );
											?>
											<a href="<?php echo esc_url( $product_term_url ); ?>" class="saved-tabs-list-taxonomy-terms"><?php echo esc_html( $term_object->name ); ?></a>
											<?php
											$ii++;
											if ( $ii !== count( $terms ) ) {
												echo ',';
											}
										}
									}
									?>
								</div>
							<?php
						}
					}
				}
				?>
			</td>
		<?php
	}

	/**
	 * Display a new `<th>` in the saved tabs table's `<thead>` and `<tfoot>`
	 */
	public function add_global_th_to_saved_tabs_list() {
		?>
			<th class="manage-column column-global" scope="col">
				<?php esc_html_e( 'Global', 'custom-product-tabs-pro' ); ?>
			</th>
		<?php
	}

	/**
	 * Show a tab's global status in the saved tabs table
	 *
	 * @param array | $tab | Array of tab data
	 */
	public function add_global_data_to_saved_tabs_list( $tab ) {
		?>
		<td class="column-global">
			<?php if ( isset( $tab['global_tab'] ) && $tab['global_tab'] === true ) : ?>
				<span class="dashicons dashicons-yes"></span>
			<?php endif; ?>
		</td>
		<?php
	}

}

new YIKES_Custom_Product_Tabs_Pro_Saved_Tabs_List();
