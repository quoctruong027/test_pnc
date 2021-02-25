<?php
	
/** 
* Functions for the Single Saved Tab Page
*/
class YIKES_Custom_Product_Tabs_Pro_Saved_Tabs_Single {

	/**
	 * Define hooks.
	 */
	public function __construct() {

		// Single saved tab page - show other products using this tab.
		add_action( 'yikes-woo-saved-tab-after-save-buttons', array( $this, 'display_products_using_this_tab_container' ), 10, 1 );

		// Get the number of products using this tab.
		add_action( 'wp_ajax_get_num_products_using_this_tab', array( $this, 'get_num_products_using_this_tab' ), 10 );
		add_action( 'wp_ajax_get_products_using_this_tab', array( $this, 'get_products_using_this_tab' ), 10 );

		// AJAX functions to refresh the 'other products using this tab' HTML.
		add_action( 'wp_ajax_display_products_using_this_tab_ajax', array( $this, 'display_products_using_this_tab_ajax' ) );

		// Single saved tab page - global section.
		add_action( 'yikes-woo-saved-tab-before-save-buttons', array( $this, 'get_global_section' ), 10, 3 );

		// Single saved tab page - add taxonomies.
		add_action( 'yikes-woo-saved-tab-before-save-buttons', array( $this, 'get_applicable_taxonomies' ), 20, 3 );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue our scripts and styes.
	 *
	 * @param string $hook The current screen's base.
	 */
	public function enqueue_scripts( $hook ) {

		if ( $hook === 'toplevel_page_' . YIKES_Custom_Product_Tabs_Settings_Page ) {
			wp_register_script( 'cptpro-admin-scripts', YIKES_Custom_Product_Tabs_Pro_URI . 'js/saved-tabs-pro.min.js', array( 'jquery', 'jquery-ui-autocomplete' ), YIKES_Custom_Product_Tabs_Pro_Version, true );
			wp_localize_script(
				'cptpro-admin-scripts',
				'cptpro_admin_data',
				array(
					'ajaxurl'                           => admin_url( 'admin-ajax.php' ),
					'tab_id'                            => isset( $_GET['saved-tab-id'] ) ? filter_var( wp_unslash( $_GET['saved-tab-id'] ), FILTER_SANITIZE_NUMBER_INT ) : 0,
					'products_using_this_tab_nonce'     => wp_create_nonce( 'cptpro_display_products_using_tab' ),
					'num_products_using_this_tab_nonce' => wp_create_nonce( 'cptpro_num_products_using_tab' ),
					'get_products_using_this_tab_nonce' => wp_create_nonce( 'cptpro_get_products_using_tab' ),
				)
			);
			wp_enqueue_script( 'cptpro-admin-scripts' );
			wp_enqueue_style( 'cptopro-admin-styles', YIKES_Custom_Product_Tabs_Pro_URI . 'css/saved-tabs-pro.min.css', array(), YIKES_Custom_Product_Tabs_Pro_Version, 'all' );
		}
	}

	/**
	 * Display the products using this tab HTML
	 *
	 * @param int $saved_tab_id The ID of the current saved tab.
	 */
	public function display_products_using_this_tab_container( $saved_tab_id ) {

		// Get the number of products using this tab.
		$num_products = $this->fetch_all_products_for_saved_tab( $saved_tab_id, $count = true );
		?>
			<div class="yikes_woo_saved_tab_products">
				<h3 class="yikes_woo_saved_tab_header"><?php esc_html_e( 'Products Using this Tab', 'custom-product-tabs-pro' ); ?></h3>
					<div class="inside entry-details-overview">
					<?php if ( $num_products === 0 ) { ?>
						<p><?php esc_html_e( 'There are no products using this tab.', 'custom-product-tabs-pro' ); ?></p>
					<?php } else { ?>
						<p>
						<?php
						printf(
							/* translators: the placeholder is a number with some HTML. */
							esc_html( _n( 'This tab is currently used on %1s product.', 'This tab is currently used on %1s products.', $num_products, 'custom-product-tabs-pro' ) ),
							"<span class='yikes_woo_number_of_products'>{$num_products}</span>"
						);
						?>
						</p>

						<ul class="products-using-tab-ul"></ul>
					<?php } ?>
				</div>
			</div>
		<?php
	}

	/**
	 * Display the products using this tab HTML.
	 *
	 * @param int $saved_tab_id   The ID of the current saved tab.
	 * @param int $paged          The page/loop number.
	 * @param int $posts_per_page The number of posts per page.
	 */
	public function display_products_using_this_tab( $saved_tab_id, $paged = '1', $posts_per_page = '-1' ) {

		// Get all the products using this tab.
		$products     = $this->fetch_all_products_for_saved_tab( $saved_tab_id, $count = false, $paged, $posts_per_page );

		if ( $products->have_posts() ) :
			while ( $products->have_posts() ) :
				$products->the_post();
				$edit_product_url = add_query_arg( array( 'post' => get_the_ID(), 'action' => 'edit' ), esc_url_raw( admin_url( 'post.php' ) ) );
				?>
				<li><a href="<?php echo esc_url( $edit_product_url ); ?>"> <?php echo esc_html( get_the_title( get_the_ID() ) ); ?> </a></li>
				<?php
			endwhile;
		endif;
	}

	/**
	 * Fetch all product ids that are using the saved tab.
	 *
	 * @param int  $tab_id Unique identifier of a tab.
	 * @param bool $count Whether to return the count of products (true) or the full WP_Query (false).
	 *
	 * @return array $product_ids_array An array of product ids.
	 */
	private function fetch_all_products_for_saved_tab( $tab_id, $count = false, $paged = '1', $posts_per_page = '-1' ) {

		// Set up our return array.
		$product_ids_array = array();

		// Get all of the product IDs from the DB.
		$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs_applied', array() );

		// If the option returns an empty array, C YA.
		if ( empty( $saved_tabs ) ) {
			return 0;
		}

		// Loop through all of our applied tabs and get the product IDs.
		foreach ( $saved_tabs as $product_id => $saved_tabs_array ) {

			// This means the product is using this saved tab.
			if ( isset( $saved_tabs_array[ $tab_id ] ) && ! empty( $saved_tabs_array[ $tab_id ] ) ) {
				$product_ids_array[] = $product_id;
			}
		}

		if ( ! empty( $product_ids_array ) ) {

			// Remove duplicate product IDs.
			$product_ids_array = array_unique( $product_ids_array );

			$wp_query_args = array(
				'post_type'      => 'product',
				'post__in'       => $product_ids_array,
				'fields'         => 'ids',
				'paged'          => $paged,
				'posts_per_page' => $posts_per_page,
				'order'          => 'ASC',
				'orderby'        => 'title',
			);

			$products = new WP_Query( $wp_query_args );

			if ( $count === true ) {
				return $products->found_posts;
			} else {
				return $products;
			}
		}

		return 0;
	}

	/**
	 * AJAX function to display the 'products using this tab' HTML when a tab is saved.
	 */
	public function display_products_using_this_tab_ajax() {

		// Verify the nonce.
		if ( ! check_ajax_referer( 'cptpro_display_products_using_tab', 'nonce', false ) ) {
			wp_send_json_error();
		}

		// Make sure we have a tab ID.
		if ( ! isset( $_POST['tab_id'] ) || isset( $_POST['tab_id'] ) && empty( $_POST['tab_id'] ) ) {
			wp_send_json_error();
		}

		// Send the 'products using this tab' HTML directly back to the client.
		$tab_id = filter_var( wp_unslash( $_POST['tab_id'] ), FILTER_SANITIZE_NUMBER_INT );
		$this->display_products_using_this_tab_container( $tab_id );
		exit;
	}

	/**
	 * Get the products using this tab.
	 */
	public function get_products_using_this_tab() {

		// Verify the nonce.
		if ( ! check_ajax_referer( 'cptpro_get_products_using_tab', 'nonce', false ) ) {
			wp_send_json_error();
		}

		// Make sure we have a tab ID.
		if ( ! isset( $_POST['tab_id'] ) || isset( $_POST['tab_id'] ) && empty( $_POST['tab_id'] )
			|| ! isset( $_POST['paged'] ) || isset( $_POST['paged'] ) && empty( $_POST['paged'] ) ) {
			wp_send_json_error();
		}

		$tab_id = filter_var( wp_unslash( $_POST['tab_id'] ), FILTER_SANITIZE_NUMBER_INT );
		$paged  = filter_var( wp_unslash( $_POST['paged'] ), FILTER_SANITIZE_NUMBER_INT );

		$this->display_products_using_this_tab( $tab_id, $paged, 100 );
		exit;
	}

	/**
	 * Get the number of products using this tab.
	 */
	public function get_num_products_using_this_tab() {

		// Verify the nonce.
		if ( ! check_ajax_referer( 'cptpro_num_products_using_tab', 'nonce', false ) ) {
		 	wp_send_json_error();
		}

		// Make sure we have a tab ID.
		if ( ! isset( $_POST['tab_id'] ) || isset( $_POST['tab_id'] ) && empty( $_POST['tab_id'] ) ) {
			wp_send_json_error();
		}

		// Send the 'products using this tab' HTML directly back to the client.
		$tab_id       = filter_var( wp_unslash( $_POST['tab_id'] ), FILTER_SANITIZE_NUMBER_INT );
		$num_products = $this->fetch_all_products_for_saved_tab( $tab_id, true );
		wp_send_json_success( array( 'num_products' => $num_products ) );
	}

	/**
	 * Display the global tabs section on the saved tab's page.
	 *
	 * @param int   $saved_tab_id The ID of the saved tab we're currently on.
	 * @param array $tab          The saved tab we're currently on.
	 * @param bool  $global       Whether this tab is global.
	 */
	public function get_global_section( $saved_tab_id, $tab, $global ) {
		$checked = $global === true ? 'checked="checked"' : '';
		?>
			<h3><?php esc_html_e( 'Global Tab', 'custom-product-tabs-pro' ); ?></h3>
			<div class="global-section">
				<label for="global-checkbox">
					<input type="checkbox" value="global" name="global-checkbox" id="global-checkbox" <?php echo esc_attr( $checked ); ?>/>
					<?php esc_html_e( 'Check to add this tab to all of your products.', 'custom-product-tabs-pro' ); ?>
				</label>
				<div class="yikes_woo_reusable_tab_title_note">
					<?php
					printf(
						/* translators: The placeholders are HTML strong tags. */
						esc_html( '%1$1sNote:%2$2s This will remove any taxonomies currently attached to this tab.' ),
						'<strong>',
						'</strong>'
					);
					?>
				</div>
			</div>
		<?php
	}

	/**
	 * Get the taxonomies & terms that can be assigned to a custom tab.
	 *
	 * @param int   $saved_tab_id The ID of the current saved tab.
	 * @param array $taxonomies   The current tab's taxonomies.
	 * @param bool  $global       Whether this tab is global.
	 */
	public function get_applicable_taxonomies( $saved_tab_id, $taxonomies, $global ) {

		$pro_helper_class = new YIKES_Custom_Product_Tabs_Pro_Admin();

		$product_taxonomy_slugs = $pro_helper_class->cptpro_get_object_taxonomies( 'product' );
		$product_term_objects   = $pro_helper_class->cptpro_get_terms( $product_taxonomy_slugs );

		if ( empty( $product_term_objects ) ) {
			return;
		}

		$product_term_objects = $this->sort_terms( $product_term_objects );

		$this->display_terms( $product_term_objects, $taxonomies );
	}

	/**
	 * Sort all of the terms based on the their taxonomy.
	 *
	 * @param array $product_term_objects Array of term objects.
	 *
	 * @return array $product_term_objects Sorted array of term objects.
	 */
	private function sort_terms( $product_term_objects ) {

		// Sort the terms by their taxonomy.
		usort(
			$product_term_objects,
			function( WP_Term $a, WP_Term $b ) {
				return strcmp( $a->taxonomy, $b->taxonomy );
			}
		);

		return $product_term_objects;
	}

	/**
	 * Display the taxonomies and terms that can be assigned to a custom tab.
	 *
	 * @param array $product_term_objects The taxonomies assigned to products.
	 * @param array $selected_taxonomies  The current tab's taxonomies.
	 */
	private function display_terms( $product_term_objects, $selected_taxonomies ) {

		// Note: we use .cptpro-taxonomies in the free version of the plugin to tell if we have taxonomies. Don't remove this class.
		?>
			<div class="cptpro-taxonomies">
				<?php
				foreach ( $product_term_objects as $product_term ) {

					$first_time_in_loop    = isset( $first_time_in_loop ) ? false : true;
					$current_term_taxonomy = isset( $current_term_taxonomy ) ? $current_term_taxonomy : $product_term->taxonomy;

					if ( $first_time_in_loop === true || $current_term_taxonomy !== $product_term->taxonomy ) {

						$current_term_taxonomy = $product_term->taxonomy;

						$this->display_taxonomy_name( $product_term->taxonomy );
					}

					// Check if this taxonomy was already selected.
					$checked = false;
					if ( isset( $selected_taxonomies[ $current_term_taxonomy ] ) && ! empty( $selected_taxonomies[ $current_term_taxonomy ] ) ) {
						$selected_taxonomy_values = array_flip( $selected_taxonomies[ $current_term_taxonomy ] );
						$checked                  = isset( $selected_taxonomy_values[ $product_term->slug ] );
					}

					$this->display_taxonomy_term( $product_term, $checked );
				}
				?>
			</div>
		<?php
	}

	/**
	 * Display the taxonomy name.
	 *
	 * @param string $taxonomy_slug The taxonomy's slug.
	 */
	private function display_taxonomy_name( $taxonomy_slug ) {
		$taxonomy       = get_taxonomy( $taxonomy_slug );
		$taxonomy_label = is_object( $taxonomy ) && isset( $taxonomy->label ) ? $taxonomy->label : '';
		$taxonomy_name  = is_object( $taxonomy ) && isset( $taxonomy->name ) ? $taxonomy->name : '';
		?>
			<h3 class="taxonomy-label" data-taxonomy="<?php echo esc_attr( $taxonomy_name ); ?>"><?php echo esc_html( $taxonomy_label ); ?> </h3> 
			<div class="yikes_woo_reusable_tab_title_note">
				<?php /* translators: the placeholder is a taxonomy label */ ?>
				<?php printf( esc_html( 'Add this tab to products by %1s' ), esc_html( $taxonomy_label ) ); ?>
			</div>
			<input class="taxonomy-search <?php echo esc_attr( $taxonomy_name ); ?>" placeholder="Search for your <?php echo esc_attr( $taxonomy_label ); ?> terms"/> 
		<?php
	}

	/**
	 * Display the terms for a taxonomy.
	 *
	 * @param object $term    A WP_Term object.
	 * @param bool   $checked True if the current taxonomy is assigned to the current saved tab.
	 */
	private function display_taxonomy_term( WP_Term $term, $checked ) {
		$unique_term_slug = $term->taxonomy . '_' . $term->term_taxonomy_id;
		$display_html     = $checked === true ? '' : 'display: none;';
		$selected_class   = $checked === true ? 'selected' : '';
		$term_link        = add_query_arg( array( $term->taxonomy => $term->slug, 'post_type' => 'product' ), admin_url( 'edit.php' ) );
		?>
			<label style="<?php echo esc_attr( $display_html ); ?>" for="<?php echo esc_attr( $unique_term_slug ); ?>" class="taxonomy-term-label <?php echo esc_attr( $unique_term_slug ); ?>">
				<input data-tt-id="<?php echo esc_attr( $term->term_taxonomy_id ); ?>" data-term-unique-id="<?php echo esc_attr( $unique_term_slug ); ?>" type="hidden" id="<?php echo esc_attr( $unique_term_slug ); ?>" name="<?php echo esc_attr( $term->taxonomy ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>" class="<?php echo esc_attr( $selected_class ); ?>" data-term-name="<?php echo esc_attr( $term->name ); ?>" />
				<span class="dashicons dashicons-dismiss cptpro-dashicons-dismiss"></span>
				<a href="<?php echo esc_attr( $term_link ); ?>" target="_blank"><?php echo esc_html( $term->name ); ?></a>
			</label>
		<?php
	}

}

new YIKES_Custom_Product_Tabs_Pro_Saved_Tabs_Single();
